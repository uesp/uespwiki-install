<?php
/**
 * This file contains a wide range of revamps to how the search engine actually works
 * (changes to the appearance of the Special:Search page and features that appear on that page are
 *  all in SiteSpecialSearch.php)
 *
 * The modifications include:
 * * Rework GetNearMatch/SiteGetNearMatch to deal with namespaces more intelligently and to
 *   increase the range of possible names that qualify as near matches
 * * Add ranking of searches (which includes secondary changes to make relevance available)
 * * Add boolean option that passes input text nearly directly to the SQL MATCH operator, allowing
 *   full use of MATCH functions
 * * Modify non-boolean searches to automatically match both plural and singular forms of words
 * * Add namespace to contents of si_title
 * * Save namespace and boolean in searchindex table so there's no need to join with page
 *   (since the join locks page while search sorts)
 *
 * This assumes that the site will only ever use a mysql database (that $wgDBtype=='mysql'), and overwrites 
 *  any other possibilities
 *  However, given how inheritance works, there's no way to insert this functionality earlier in the chain of classes
 *  (at least not without editing the existing classes to modify their inheritance)
 */
/*
To do :
cleanup code and make searchindex changes official
create a proper sql snippet with hooks to handle SQL change
change namespace condition to directly use si_namespace, instead of preg_replacing
check how to bypass saving title twice in searchindex
*/
global $IP;
require_once "$IP/includes/SearchMySQL4.php";

class SiteSearchMySQL extends SearchMySQL4 {
/* MW1.10 vs MW1.14 ugliness
 * This function is SUPPOSED to be getNearMatch
 * Once the site has upgraded to MW1.14+, it can safely be renamed to getNearMatch AND the calls in SiteSpecialSearch
 *  can be changed back to getNearMatch calls.
 * But in the meantime, this is only clean way to get the extension to work with MW1.10 and MW1.14
 * The underlying problem is that getNearMatch is not declared as a static function in MW1.10 (even though it technically
 *  is a static function -- it's just that it was written in PHP4 before the keyword existed), but it is declared static
 *  in MW1.14.  My extension cannot redefine the function's staticness -- which means if I include the static keyword,
 *  MW1.10 crashes, but if I don't include it, MW1.14 crashes.  Ugggggly....
 */
	static function SiteGetNearMatch( $searchterm , $altterm=NULL, $namespacelist=NULL, $extratext='', $nsprimary=NULL ) {
		global $wgContLang;

		if( is_null($altterm))
			$altterm = $searchterm;

		$allSearchTerms = array($altterm);

		if($wgContLang->hasVariants()){
			$allSearchTerms = array_merge($allSearchTerms,$wgContLang->convertLinkToAllVariants($searchterm));
		}

		$modsearch = $altterm;
		if (substr($modsearch,-2,2)!='ss')
			$modsearch = preg_replace('/e?s$/', '', $modsearch);
		$modsearch = preg_replace('/^(The|A|An)\s+/i', '', $modsearch);

		// Transformations that need to be done to original search string but NOT to extratext or other
		// additions to search string (in particular, transformations that alter regexp expressions
		// must be done here
// transform special characters
		$modsearch = Sanitizer::decodeCharReferences( $modsearch );
// escape characters with special meaning for regexps
		$modsearch = preg_replace( '/([\[\]\)\(\+\*\?])/', '\\\\$1', $modsearch );
// make quotes (single and double) interchangeable and optional
		$modsearch = preg_replace( '/[\'"]/', '[\'"]?', $modsearch );

		// Add to the search string
// need to have ending as "e?s?" instead of "(e?s)?" for cases where e was stripped above and needs to be added back
// (e.g., search term was "voyages" and match is "voyage", this needs to be "voyage?s?") 
		$modsearch = '^'.$extratext.'((The|A|An)_+)?'.$modsearch.'e?s?$';

		// Final transformations that need to be applied to the entire string
// transform spaces into underscore characters
		$modsearch = str_replace( ' ', '_', $modsearch );
		$modsearch = addslashes( $modsearch );
// page_title is varchar and therefore isn't supposed to be case-sensitive -- but in my tests it is
// so force everything to lower case
		$modsearch = strtolower($modsearch);
		$sqlstart = 'SELECT page_id, page_namespace, page_title FROM page';
// version of MYSQL on UESP does not recognize using latin1
//		$mysql = $sqlstart . " WHERE lower(convert(page_title using latin1)) REGEXP '$modsearch'";
		$mysql = $sqlstart . " WHERE lower(page_title) REGEXP '$modsearch'";
		if( !is_null($namespacelist) && count($namespacelist) ) {
			if( count($namespacelist)==1 )
				$mysql .= ' AND page_namespace='.$namespacelist[0];
			else
				$mysql .= ' AND page_namespace IN (' . implode( ', ', $namespacelist ) . ')';
		}
		$mysql .= " ORDER BY length(page_title)";
//				print "<pre>\n";
//				var_dump($mysql);
//				print "</pre>\n";
		$db = wfGetDB( DB_SLAVE );
		$resultSet = $db->resultObject( $db->query( $mysql ));
		// If there were no results, check whether first word is a namespace
		// It would also be useful to somehow strip punctuation from saved title -- but that is not done by si_title
		if ($resultSet->numRows()==0) {
			$words = explode(' ', $altterm);
			if (count($words>1) && (($nschk=$wgContLang->getNsIndex($words[0]))!==false)) {
				$modsearch = preg_replace('/'.$words[0].'_*/i', '', $modsearch, 1);
//				$mysql = $sqlstart . " WHERE lower(convert(page_title using latin1)) REGEXP '$modsearch' AND page_namespace=$nschk ";
				$mysql = $sqlstart . " WHERE lower(page_title) REGEXP '$modsearch' AND page_namespace=$nschk ";
				$mysql .= " ORDER BY length(page_title)";
				$resultSet = $db->resultObject( $db->query( $mysql ));
			}
		}

		if ($resultSet->numRows()==1) {
			$row = $resultSet->fetchRow();
			$title = Title::makeTitle( $row['page_namespace'], $row['page_title'] );

			return $title;
		}
		elseif ($resultSet->numRows()==0) {
		# conditions from original mediawiki version of searchEngine
			$title = Title::newFromText( $searchterm );

			# Entering an IP address goes to the contributions page
			if ( ( $title->getNamespace() == NS_USER && User::isIP($title->getText() ) )
				|| User::isIP( trim( $searchterm ) ) ) {
				return SpecialPage::getTitleFor( 'Contributions', $title->getDbkey() );
			}


			# Entering a user goes to the user page whether it's there or not
			if ( $title->getNamespace() == NS_USER ) {
				return $title;
			}
		
			# Go to images that exist even if there's no local page.
			# There may have been a funny upload, or it may be on a shared
			# file repository such as Wikimedia Commons.
			if( $title->getNamespace() == NS_IMAGE ) {
				$image = new Image( $title );
				if( $image->exists() ) {
					return $title;
				}
			}

			# MediaWiki namespace? Page may be "implied" if not customized.
			# Just return it, with caps forced as the message system likes it.
			if( $title->getNamespace() == NS_MEDIAWIKI ) {
				return Title::makeTitle( NS_MEDIAWIKI, $wgContLang->ucfirst( $title->getText() ) );
			}

			return NULL;
		}

		# only get here if there were multiple matches: need to determine whether one match is clearly preferable,
		# if only one match is in requested namespace, take it
		# if there is only one non-lore match, take it
		# if there are multiple matches in the same eligible namespace, and one is exact, take it

		$nslore = MWNamespace::getCanonicalIndex('lore');
		$listbytag = array( 'primary' => array(), 'other' => array(), 'lore' => array());
		$listbest = array( 'primary' => array(), 'other' => array(), 'lore' => array());
		$ntag = array( 'primary' => 0, 'other' => 0, 'lore' => 0);
		$nrow = 0;
		$nearMatches = array();
		while( $row = $resultSet->fetchRow() ) {
			$nsorder = -1;
			for ( $i=0; $i<count($namespacelist); $i++) {
				if ($namespacelist[$i]==$row['page_namespace']) {
					$nsorder = $i;
					break;
				}
			}
			$row['page_title'] = str_replace('_', ' ', $row['page_title']);
			if ($nsorder>=0) {
				if (!is_null($nsprimary) && $row['page_namespace']==$nsprimary)
					$tag = 'primary';
// lore only given lower priority if search was called from a recognized gamespace
				elseif (is_null($nsprimary) || $row['page_namespace'] != $nslore)
					$tag = 'other';
				else
					$tag = 'lore';
				$ntag[$tag]++;
				$listbytag[$tag][] = $nrow;
				if (strtolower($altterm)==strtolower($row['page_title'])) {
					$listbest[$tag][] = $nrow;
				}
					
			}
			$nearMatches[$nrow++] = array('nsorder' => $nsorder, 'title' => $row['page_title'], 'ns' => $row['page_namespace']);
		}
		$use = NULL;
		foreach (array('primary', 'other', 'lore') as $tag) {
			if (!$ntag[$tag])
				continue;
			if ($ntag[$tag]==1)
				$use = $listbytag[$tag][0];
			elseif (count($listbest[$tag])==1)
				$use = $listbest[$tag][0];
// only difference between these should be case, so change into a case-insensitive match
			elseif ($tag=='primary' && count($listbest[$tag])) {
				foreach ($listbest[$tag] as $i => $row) {
					$term = $nearMatches[$row]['title'];
					if ($altterm!==$term)
						unset($listbest[$tag][$i]);
					else
						$use = $row;
				}
				if (count($listbest[$tag])!=1)
					$use = NULL;
			}
			break;
		}

		if (!is_null($use)) {
			$title = Title::makeTitle( $nearMatches[$use]['ns'], $nearMatches[$use]['title'] );
			return $title;
		}

		return count($nearMatches);
	}

	// Three reasons for overriding here
	// (1) exclude directions/author/etc pages
	// (2) reorder query so that relevance is available
	// (3) add boolean option
	/* hopefully (1) won't even be needed by MW1.15, because hopefully UESP won't have any
		more of these subpages left */
	function queryMain( $filteredTerm, $fulltext, $boolean=false ) {
		global $egCustomSiteID;
		$match = $this->parseQuery( $filteredTerm, $fulltext, $boolean );
		// crudely convert match expresssion into one that provides relevance
		$matchrel = preg_replace('/IN BOOLEAN MODE/', '', $match);
		//		$matchrel = preg_replace('/\+/', '', $matchrel);
		/*		$page        = $this->db->tableName( 'page' );
		$searchindex = $this->db->tableName( 'searchindex' );
		
		$sql = 'SELECT page_id, page_namespace, page_title';
		if ($fulltext)
			$sql .= ', ' . $matchrel . 'AS relevance ';
		$sql .= " FROM $page,$searchindex " .
			' WHERE page_id=si_page AND ' . $match;*/

		// no such pages exist any more, so stop wasting server time (and don't mess up legit searches for such words)
		/*		if ($egCustomSiteID == 'Uesp') {
			$sql .= ' AND NOT (si_title REGEXP \' (directions|author|desc|description)$\') ';
		}*/
	
		$searchindex = $this->db->tableName( 'searchindex' );
		
		$sql = 'SELECT si_page as page_id, si_namespace as page_namespace, si_orig_title as page_title';
		if ($fulltext)
			$sql .= ', ' . $matchrel . 'AS relevance ';
		$sql .= " FROM $searchindex " .
			' WHERE ' . $match;
		return $sql;
	}
	
	function queryRedirect() {
		if( $this->showRedirects ) {
			return '';
		} else {
			return 'AND si_is_redirect=0';
		}
	}
	
	function queryRanking( $filteredTerm, $fulltext ) {
		if ($fulltext)
			// this only works because queryMain has also been changed -- otherwise relevance is not defined
			return 'ORDER BY relevance DESC';
		else
		// order in which titles are displayed: increasing title length, decreasing namespace
		// (shortest title lengths presumably closest match to words; highest namespaces are most recent
		//  games, and places gamespaces before wiki namespaces)
			return ' ORDER BY length(page_title), mod(page_namespace,2), page_namespace DESC ';
	}
	
	// these are overridden to recognize boolean option and, now, so that temporary table gets used
	function searchTitle( $term, $boolean ) {
		//		if (!$boolean)
		//		return parent::searchTitle($term);
		// remove "filter" and pass to getBooleanQuery instead of getQuery
		$resultSet = $this->db->resultObject( $this->db->query( $this->getBooleanQuery( $term , false, $boolean ) ) );
		return new MySQLSearchResultSet( $resultSet, $this->searchTerms );
	}
	
	function searchText( $term, $boolean ) {
		$resultSet = $this->db->resultObject( $this->db->query( $this->getBooleanQuery( $term , true, $boolean ) ) );
		return new MySQLSearchResultSet( $resultSet, $this->searchTerms );
	}
	
	// originally identical to getQuery except that it passes true to queryMain
	// but now this is where fulltext query is sent to a temporary table prior to sorting
	// * including an ORDER BY clause means that the tables in the query are locked while the sort happens
	//    ... if page is part of the query, then alot of other queries get locked out
	// temporary table needs to be created here, because needs to know the redirect and namespaces conditions
	//  before creating table, then needs to add ranking and limit afterwards
	// Hmm, even non-full text searches lock up DB for 1-2 seconds while sorting, so let's try this
	// for all searches
	function getBooleanQuery( $term, $fulltext, $boolean = true ) {
		if (!$boolean)
			$term = $this->filter($term);
		
		$qns = $this->queryNamespaces();
		$qns = preg_replace('/page_namespace/', 'si_namespace', $qns);
		$sql = $this->queryMain( $term, $fulltext, $boolean ) . ' ' .
			$this->queryRedirect() . ' ' .
			$qns . ' ' .
			$this->queryRanking( $term, $fulltext ) . ' ' .
			$this->queryLimit();
		//		if ($fulltext) {
			// I'm assuming DBSLAVE can create a temp table... 
			// if not, things get tricky, because temp table has to be created by same DB object that does
			// subsequent query
		/*			$tmpname = 'srchresults';
			if ($fulltext)
				$tmpname .= '_full';
		
			$sql = 'CREATE TEMPORARY TABLE '.$tmpname.' '.
				$this->queryMain( $term, $fulltext, $boolean ) . ' ' .
				$this->queryRedirect() . ' ' .
				$this->queryNamespaces() ;
			
			// should table be indexed?  does it make sense for a single query?
			// and if I'm indexing, shouldn't it wait until after table is written so that page table is freed up as soon as possible?
				$this->db->query($sql);
		
			$sql = 'SELECT page_id, page_namespace, page_title';
			if ($fulltext)
				$sql .= ', relevance';
			$sql .= ' FROM '.$tmpname.' ' .
				$this->queryRanking( $term, $fulltext ) . ' ' .
				$this->queryLimit();*/
		/*		}
		else {
			$sql = $this->queryMain( $term, $fulltext, $boolean ) . ' ' .
				$this->queryRedirect() . ' ' .
				$this->queryNamespaces() . ' ' .
				$this->queryRanking( $term, $fulltext ) . ' ' .
				$this->queryLimit();
		}*/
		return $sql;
	}
	
	// if boolean=false, this function works identically to standard version in SearchMySQL4.php,
	//   except that it strips a trailing 's' and adds '*' to the end of all words by default
	// one reason for this change is to make the standard search more compatible with the revisions
	//   to getNearMatch -- the title search should find the same pages as getNearMatch, otherwise
	//   results can be very confusing (getNearMatch saying "there are multiple matches" but title
	//   search showing no matches)
	//
	// if boolean=true, it basically copies input text directly to searchon
	function parseQuery( $filteredText, $fulltext, $boolean=false ) {
		global $wgContLang;
		$lc = SearchEngine::legalSearchChars();
		$searchon = '';
		$this->searchTerms = array();

		// this processing is still used with boolean=true, but only so that searchTerms array is populated
		// (searchon is overridden later)
		# FIXME: This doesn't handle parenthetical expressions.
		$m = array();
		if( preg_match_all( '/([-+<>~]?)(([' . $lc . ']+)(\*?)|"[^"]*")/',
			  $filteredText, $m, PREG_SET_ORDER ) ) {
			foreach( $m as $terms ) {
				if( $searchon !== '' ) $searchon .= ' ';
				// only do s/* treatment if request isn't using match operators
				// (assume that if someone is savvy enough to use match operators,
				//  then they also know how to do a wildcard if needed)
				if ($terms[1] == '' && (!isset($terms[4]) || $terms[4] == '') && strlen($terms[2])>4) {
					$terms[2] = preg_replace('/e?s$/', '', $terms[2]);
					$termex = '*';
				}
				else 
					$termex = '';
				if( $this->strictMatching && ($terms[1] == '') ) {
					$terms[1] = '+';
				}
				$searchon .= $terms[1] . $wgContLang->stripForSearch( $terms[2].$termex );
				if( !empty( $terms[3] ) ) {
					$regexp = preg_quote( $terms[3], '/' );
					if( $terms[4] ) $regexp .= "[0-9A-Za-z_]+";
				} else {
					$regexp = preg_quote( str_replace( '"', '', $terms[2] ), '/' );
				}
				$this->searchTerms[] = $regexp;
			}
			wfDebug( "Would search with '$searchon'\n" );
			wfDebug( 'Match with /\b' . implode( '\b|\b', $this->searchTerms ) . "\b/\n" );
		} else {
			wfDebug( "Can't understand search query '{$filteredText}'\n" );
		}

		// expression has _not_ been filtered if boolean is on
		if ($boolean) {
			// don't want to strip any characters that have meaning to match function
			$lcmod = $lc.'\(\)\+~<>\*"';
			$searchon = trim( preg_replace( "/[^{$lcmod}]/", " ", $filteredText ) );
		}
		
		$searchon = $this->db->strencode( $searchon );
		$field = $this->getIndexField( $fulltext );
		return " MATCH($field) AGAINST('$searchon' IN BOOLEAN MODE) ";
	}

	// Original purpose:
	// intercept update and updateTitle requests so that I can insert namespace before title
				            
	// add namespace and redirect columns to searchindex; index both variables
	// run a query to populate those columns
	// create the bit of wiki code that lets update.php know how to do the alter table
	// save namespace and redirect in these functions ... which means pulling all code from parent to here
	// change queries to eliminate use of page
	// see whether page_title is needed
	// remove temporary tables
	// test performance...
	/*
	function update( $id, $title, $text ) {
		$titleobj = Title::newFromID( $id );
		if (($ns=$titleobj->getNsText()))
			$title = strtolower($ns).' '.$title;
		parent::update( $id, $title, $text );
	}
	function updateTitle( $id, $title ) {
		$titleobj = Title::newFromID( $id );
		if (($ns=$titleobj->getNsText()))
			$title = strtolower($ns).' '.$title;
		parent::updateTitle( $id, $title );
	}*/
	function update( $id, $title, $text ) {
		self::updateEither($id, $title, $text);
	}
	function updateTitle( $id, $title ) {
		self::updateEither($id, $title, false);
	}
	
	function updateEither( $id, $title, $text=false ) {
		// perhaps doing this as part of DB call would be more efficient
		// but on the other hand, the info on this title has probably already been read somewhere upstream
		$titleobj = Title::newFromID( $id );
		if (($ns=$titleobj->getNsText()))
			$title = strtolower($ns).' '.$title;
		
		$vals = array('si_title' => $title,
		              'si_namespace' => $titleobj->getNamespace(),
		              'si_is_redirect' => $titleobj->isRedirect(),
		              'si_orig_title' => $titleobj->getDBKey());
		
		$dbw = wfGetDB( DB_MASTER );
		if ($text===false) {
			$dbw->update( 'searchindex',
			              $vals,
			              array( 'si_page'  => $id ),
			              __METHOD__,
			              array( $dbw->lowPriorityOption() ) );
		}
		else {
			$vals['si_page'] = $id;
			$vals['si_text'] = $text;
			$dbw->replace( 'searchindex',
			               array( 'si_page' ),
			               $vals, __METHOD__ );
		}
	}
}
