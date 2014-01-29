<?php
/*
 * Revamp search function to add extension-specific search page and alter multiple aspects of how search works
 */
global $IP;
require_once "$IP/includes/specials/SpecialSearch.php";

function efSiteSpecialSearch( $par = '') {
	global $wgRequest, $wgUser, $wgOut;

	efSiteLoadMessages();
	$search = $wgRequest->getText( 'search', $par );
	$searchPage = new SiteSpecialSearch( $wgRequest, $wgUser );

	if( $wgRequest->getVal( 'fulltext' ) ||
		!is_null( $wgRequest->getVal( 'offset' ) ) ||
		!is_null ($wgRequest->getVal( 'searchx' ) ) ) {

		$searchPage->showResults( $search );
	} else if ( $wgRequest->getVal( 'helpsearch' ) ) {
		$wgOut->redirect( "/wiki/Help:Searching" );
// just show page without doing any searching
	} else if ( $wgRequest->getVal( 'more' ) ) {
		$searchPage->setTitlesOnly(false);
		$searchPage->noSearch( $search );
		
	} else {
		$searchPage->goResult( $search );
	}
}

class SiteSpecialSearch extends SpecialSearch {
	protected $searchRedirects;
	protected $searchTalkPages;
	protected $selectAllNS;
	protected $deselectAllNS;
	protected $searchNsnum;
	protected $searchTitlesOnly;
	protected $searchBoolean;
	
	function __construct( &$request, &$user ) {
		global $wgDefaultUserOptions, $egCustomSiteID;
		
		// these initializations need to be done before parent, otherwise they won't
		// affect namespace selection
		$this->selectAllNS = $request->getcheck( 'selectallns' )    ? true : false;
		$this->deselectAllNS = $request->getcheck( 'deselectallns' ) ? true : false;
		$this->searchNsnum = $request->getVal( 'nsnum' );
		if ($request->getCheck( 'searchx' )) {
			// get values from search box
			$this->searchRedirects = $request->getVal( 'redirs' );
			$this->searchTalkPages = $request->getVal( 'searchtalk' );
			$this->searchTitlesOnly = $request->getVal( 'searchtitles' );
			$this->searchBoolean = $request->getVal( 'searchboolean' );
		}
		else {
			// get values from user settings
			$siteprefix = strtolower($egCustomSiteID);
			$this->searchRedirects = $user->getOption($siteprefix.'searchredirects', $wgDefaultUserOptions[$siteprefix.'searchredirects']);
			$this->searchTalkPages = $user->getOption($siteprefix.'searchtalk', $wgDefaultUserOptions[$siteprefix.'searchtalk']);
			$this->searchTitlesOnly = $user->getOption($siteprefix.'searchtitles', $wgDefaultUserOptions[$siteprefix.'searchtitles']);
			$this->searchBoolean = false;
		}

		$this->searchTitlesOnly = false;
		
		parent::__construct( $request, $user );
	}

	public function setTitlesOnly( $value=true) {
		$this->searchTitlesOnly = $value;
	}

	public function noSearch( $term='' ) {
		global $wgOut;

		$fname = 'SpecialSearch::showResults';
		wfProfileIn( $fname );

		$this->setupPage( '' );
		$wgOut->addWikiText( wfMsg( 'searchresulttext' ) );
		$wgOut->setSubtitle( '' );
		$wgOut->addHTML( $this->powerSearchBox( $term ) );

		wfProfileOut( $fname );

		return;
	}
/**
 * This function has been substantially rewritten to handle searches when nearly all of
 * the content is in custom namespaces instead of the main namespace.
 * Therefore, a "go" request should be changed into a request for an article within a content
 * namespace.  
 * Note that this is done after there's already been a check to see whether there's a match for
 * the original request; only comes into play if standard code could not find a match.
 */
	function goResult( $term ) {
		global $wgOut;
		global $wgGoToEdit;
		global $wgRequest;
		global $wgContLang;

		# Try to go to page as entered.
		$t = Title::newFromText( $term );

		# If the string cannot be used to create a title
		if( is_null( $t ) ){
			return $this->showResults( $term );
		}
		# If the title is already an exact match
		if ($t->getNamespace() == NS_SPECIAL || $t->exists() ) {
			$wgOut->redirect( $t->getFullURL() );
			return;
		}

		$altterm = NULL;
		$rawterm = $term;
		$colon = stripos($term, ':');
		$namespacelist = array();
		$nsprimary = NULL;

		# If request starts with a colon, treat as an explicit request for a
		# page in main namespace: removing leading colon
		if( $colon===0 ) {
			$namespacelist[] = 0;
			$altterm = substr( $term, 1 );
			$extratext = '';
			$nsprimary = 0;
		}
		# If request contains a colon elsewhere, check to see whether the prefix is a valid namespace
		elseif( $colon!==false ) {
			$prefix = trim( substr( $term, 0, $colon ));
			if( $nsnum=$wgContLang->getNsIndex( $prefix )) {
				$altterm = trim( substr( $term, $colon+1 ));
				$namespacelist[] = $nsnum;
				$nsprimary = $nsnum;
			}
		}

		$extratext = '';
		# If neither of previous checks have been successful and nsnum parameter has been set, then
		# put together a context-sensitive list of possible namespaces
		if( is_null( $altterm ) && !is_null( $this->searchNsnum )) {
			$tlist = SiteNamespace::getRelatedNamespaces( $this->searchNsnum , $extratext , $nsprimary);
			$namespacelist = array();
			# check all extra namespaces against user's preferences, and only add
			# if this user has selected the namespace
			foreach ( $tlist as $currnum ) {
				if( $currnum==$this->searchNsnum || $currnum==$nsprimary)
					$namespacelist[] = $currnum;
				# only check against user's preferences if user has selected more than just main namespace
				# (not sure where default preferences get set... on testwiki, default is just main)
				elseif (count($this->namespaces)>2) {
					foreach ( $this->namespaces as $chknum ) {
						if ($chknum == $currnum)
							$namespacelist[] = $currnum;
					}
				}
				else {
					$namespacelist[] = $currnum;
				}
			}
			$altterm = $term;
		}

		# If there's an exact or very near match, jump right there
		# N.B. Ideally, these would be calls to getNearMatch, not SiteGetNearMatch, but for now, this is
		#  necessary.  See notes in SiteSearchMySQL.php
		if( is_null( $altterm ))
			$t = SiteSearchMySQL::SiteGetNearMatch( $term );
		else
			$t = SiteSearchMySQL::SiteGetNearMatch( $term, $altterm, $namespacelist, $extratext, $nsprimary );
		$nfound = 0;
		if( !is_null( $t ) ) {
			if (is_object($t)) {
				$wgOut->redirect( $t->getFullURL() );
				return;
			}
			# if return value is not an object, then it's the number of matches that were found
			else
				$nfound = $t;
		}

		# wait until now to setup page:
		# other options until now caused redirects or other functions that do setup, so unnecessary
		# and at this point, page can be setup using a better search term

		global $egCustomSiteID;
		$siteprefix = strtolower($egCustomSiteID);
		if ($this->searchNsnum > 0) {
			# No match, generate an edit URL
			# This is not done on requests starting from main or special namespaces
			# (don't want to offer to create main namespace article, shouldn't force
			# user to create an Oblivion article if that's not what's appropriate)
			$newterm = $wgContLang->getNsText($namespacelist[0]).':'.$altterm;
			$this->setupPage( $newterm );
			$t = Title::newFromText( $newterm );
			if( ! is_null( $t ) ) {
				wfRunHooks( 'SpecialSearchNogomatch', array( &$t ) );
				# If the feature is enabled, go straight to the edit page
				if ( $wgGoToEdit ) {
					$wgOut->redirect( $t->getFullURL( 'action=edit' ) );
					return;
				} 
			}
			if ( !$nfound ) {
				$wgOut->addWikiText( wfMsg( 'noexactmatch', wfEscapeWikiText( $newterm ) ) );
			}
			else {
				$wgOut->addWikiText( wfMsg( $siteprefix.'searchmanyplus', wfEscapeWikiText( $altterm ), wfEscapeWikiText ( $newterm ) ) );
			}
		}
		else {
			$this->setupPage( $term );
			if ( !$nfound ) {
				$wgOut->addWikiText( wfMsg( $siteprefix.'searchmain', wfEscapeWikiText( $altterm ) ) );
			}
			else {
				$wgOut->addWikiText( wfMsg( $siteprefix.'searchmany', wfEscapeWikiText( $altterm ) ) );
			}
		}
		
		# No exact match, proceed to do standard search
		# Use $term (not $altterm) here in all cases: always do the search on the
		# original text (as distributed, search does not work properly if
		# namespace specified, also don't want to override user's namespace preferences)

		return $this->showResults( $term );
	}

	function showResults( $term ) {
		$fname = 'SiteSpecialSearch::showResults';
		wfProfileIn( $fname );

		$this->setupPage( $term );

		global $wgOut;
		$wgOut->addWikiText( wfMsg( 'searchresulttext' ) );

		if( '' === trim( $term ) ) {
			$wgOut->setSubtitle( '' );
			$wgOut->addHTML( $this->powerSearchBox( $term ) );
			wfProfileOut( $fname );
			return;
		}

// this doesn't need to be changed to be extension-specific, because create function will automatically create correct subclass
		$search = SearchEngine::create();
		$search->setLimitOffset( $this->limit, $this->offset );

/**
 * In original wiki-code, search request for "namespace:article" fails miserably, because the search
 * tries to look for "namespace" in the actual article text.  Instead take that
 * to mean that a search for "article" should be done within "namespace"
 */
		global $wgContLang;
		$nsdone = false;
		if ( preg_match ("/^\s*(\w+?)(\s*:\s*)(.+)/", $term, $matches) ) {
			$namespace = strtolower($matches[1]);
			$separator = $matches[2];
			$altterm = $matches[3];
			if ( $ns = $wgContLang->getNsIndex( $namespace ) ) {
				$search->setNamespaces( array($ns) );
				$nsdone = true;
			}
		}
		if (!$nsdone) {
			$search->setNamespaces( $this->namespaces );
			$altterm = $term;
		}
		$search->showRedirects = $this->searchRedirects;

		$starttime = microtime(TRUE);

		$titleMatches = $search->searchTitle( $altterm, $this->searchBoolean );
		$num = $titleMatches ? $titleMatches->numRows() : 0;
		if( !$this->searchTitlesOnly ) {
			$textMatches = $search->searchText( $altterm, $this->searchBoolean );
			$num += $textMatches ? $textMatches->numRows() : 0;
		}
		else
		{
			$textMatches = false;
		}

		$searchtime = microtime(TRUE) - $starttime;

		if ( $num > 0 ) {

			if ( $num >= $this->limit ) {
				$top = wfShowingResults( $this->offset, $this->limit );
			} else {
				$top = wfShowingResultsNum( $this->offset, $this->limit, $num );
			}
			$wgOut->addHTML( "<p>{$top}</p>\n" );
                        wfRunHooks( 'SpecialSearchResults', array( $term, &$titleMatches, &$textMatches, $searchtime ) );
		}
		else
		{
			wfRunHooks( 'SpecialSearchNoResults', array( $term ) );
		}

		if( $num || $this->offset ) {
			$prevnext = wfViewPrevNext( $this->offset, $this->limit,
				SpecialPage::getTitleFor( 'Search' ),
				wfArrayToCGI(
					$this->powerSearchOptions(),
					array( 'search' => $term ) ) );
			$wgOut->addHTML( "<br />{$prevnext}\n" );
		}

		if( $titleMatches ) {
			if( $titleMatches->numRows() ) {
				$wgOut->addWikiText( '==' . wfMsg( 'titlematches' ) . "==\n" );
				$wgOut->addHTML( $this->showMatches( $titleMatches ) );
			} else {
				$wgOut->addWikiText( '==' . wfMsg( 'notitlematches' ) . "==\n" );
			}
		}

		if( $textMatches ) {
			if( $textMatches->numRows() ) {
				$wgOut->addWikiText( '==' . wfMsg( 'textmatches' ) . "==\n" );
				$wgOut->addHTML( $this->showMatches( $textMatches ) );
			} elseif( $num == 0 ) {
				# Don't show the 'no text matches' if we received title matches
				$wgOut->addWikiText( '==' . wfMsg( 'notextmatches' ) . "==\n" );
			}
		}

		if ( $num == 0 ) {
			if(! $this->searchTitlesOnly )
				$wgOut->addWikiText( wfMsg( 'nonefound' ) );
		}
		if( $this->searchTitlesOnly ) {
			global $egCustomSiteID;
			$wgOut->addWikiText( wfMsg( strtolower($egCustomSiteID).'searchtitlesonly' ) );
		}

		if( $num || $this->offset ) {
			$wgOut->addHTML( "<p>{$prevnext}</p>\n" );
		}
		$wgOut->addHTML( $this->powerSearchBox( $term ) );
		wfProfileOut( $fname );
	}

/* original requests that the following be private functions, but I'm treating all private functions as protected functions */
	function powerSearch( &$request ) {
		$arr = array();
		foreach( SiteSearchMySQL::searchableNamespaces() as $ns => $name ) {
  			if (($ns % 2) == 1) {
			}
			else if( !$this->deselectAllNS &&
			         ($this->selectAllNS ||
				  $request->getCheck( 'ns' . $ns ))) {
				$arr[] = $ns;
  				if ($this->searchTalkPages) {
					$arr[] = $ns + 1;
 				}
			}
		}
		return $arr;
	}

	function powerSearchOptions() {
		$opt = array();
		foreach( $this->namespaces as $n ) {
			$opt['ns' . $n] = 1;
		}
		$opt['redirs'] = $this->searchRedirects ? 1 : 0;
		$opt['searchtalk'] = $this->searchTalkPages ? 1 : 0;
		$opt['searchtitles'] = $this->searchTitlesOnly ? 1 : 0;
		$opt['searchboolean'] = $this->searchBoolean ? 1 : 0;
		$opt['nsnum'] = $this->searchNsnum;
		$opt['searchx'] = 1;
		return $opt;
	}

	function powerSearchBox( $term ) {
		global $egCustomSiteID;
		$siteprefix = strtolower($egCustomSiteID);
# add javascript to do checked/unchecked boxes, specific for this page
		$powersearchtext = <<< End_Js
<script type='text/javascript'>
function SiteSelectAllNS( element ) {
	var form = element.form;
	if (element.checked)
		 SiteSetAllNS( form, true );
	form['deselectallns'].checked = false;
}
function SiteDeselectAllNS( element ) {
	var form = element.form;
	if (element.checked)
		 SiteSetAllNS( form, false );
	form['selectallns'].checked = false;
}
function SiteSetAllNS( form, setting ) {
	for (var i=0; i<form.elements.length; i++) {
	 	if (form.elements[i].name.substr(0,2)=='ns') {
			form.elements[i].checked = setting;
		}
	}
}
function SiteUnsetSelects(element ) {
	var form=element.form;
	form['selectallns'].checked = false;
	form['deselectallns'].checked = false;
}
</script>
End_Js;
		$boolean = wfMsgExt( $siteprefix.'powersearchboolean', array( 'parseinline' ) );
		$powersearchtext .= "\n<table>\n<tr>\n";
		$powersearchtext .= '<td';
		if ($boolean)
			$powersearchtext .= ' rowspan="2"';
		$powersearchtext .= '>'.wfMsgExt($siteprefix.'powersearchfor', array( 'parseinline') )."\n";
		$powersearchtext .= '<td><input type="text" name="search" value="' . htmlspecialchars( $term ) ."\" size=\"32\" />";
		$powersearchtext .= '<input type="submit" name="searchx" value="' . htmlspecialchars( wfMsg('powersearch') ) . "\" /></td>\n";
		$powersearchtext .= "</tr>\n";
		if ($boolean) {
			$checked = $this->searchBoolean ? ' checked="checked"' : '';
			$powersearchtext .= '<tr><td><input type="checkbox" value="1" name="searchboolean" '. $checked . '/>'.$boolean.'</input></td></tr>'."\n";
		}
		$powersearchtext .= "</table>\n";
		$powersearchtext .= "<table class=\"wikitable vtop centered\"><tr><td>\n";
		
		$lines = explode( "\n", wfMsgForContent( $siteprefix.'powersearchtable' ) );
		$ncols = 1;
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line=='') {
				$powersearchtext .= "</td><td>\n";
				$ncols++;
				continue;
			}
			if (substr($line,0,2)=='**')
				$powersearchtext .= '&nbsp; &nbsp; ';
			$line = trim($line, '* ');
			if (($div=strpos($line, '|'))!==false) {
				$nsname = trim(substr($line, 0, $div));
				$nsshowname = trim(substr($line, $div+1));
			}
			else {
				$nsname = $line;
				$nsshowname = NULL;
			}
			$powersearchtext .= $this->powerSearchBoxLine( $nsname, $nsshowname );
		}

		$powersearchtext .= "</td></tr><tr><td colspan='{$ncols}' style='text-align:center'>\n";

		$checked = $this->searchTitlesOnly ? ' checked="checked"' : '';
		$powersearchtext .= ' <input type="checkbox" value="1" name="searchtitles" ' . $checked . '/> '.wfMsgExt($siteprefix.'powersearchtitles', array( 'parseinline') )."</input>\n";
		$checked = $this->searchTalkPages ? ' checked="checked"' : '';
		$powersearchtext .= ' &nbsp; &nbsp; <input type="checkbox" value="1" name="searchtalk" ' . $checked . '/> '.wfMsgExt($siteprefix.'powersearchtalk', array( 'parseinline' ) )."</input>\n";
		$checked = $this->searchRedirects ? ' checked="checked"' : '';
		$powersearchtext .= ' &nbsp; &nbsp; <input type="checkbox" value="1" name="redirs" ' . $checked . '/> '.wfMsgExt($siteprefix.'powersearchredirects', array( 'parseinline') )."</input>\n";
# new boxes for select all/deselect all
# these are always shown unchecked: any requests made using these checkboxes end up
# changing all the individual namespace boxes, but then want these unchecked so user
# can make any further adjustments
		$powersearchtext  .= " &nbsp; &nbsp; <input type='checkbox' value='1' name='selectallns' onchange='SiteSelectAllNS(this)'/> ".wfMsgExt($siteprefix.'powersearchselect', array( 'parseinline') )."</input>\n";
		$powersearchtext  .= " &nbsp; &nbsp; <input type='checkbox' value='1' name='deselectallns' onchange='SiteDeselectAllNS(this)'/> ".wfMsgExt($siteprefix.'powersearchdeselect', array( 'parseinline') )."</input>\n";
		$powersearchtext .= "</td></tr></table>\n";
		$powersearchtext .= "<br/>\n";
		
		$title = SpecialPage::getTitleFor( 'Search' );
		$action = $title->escapeLocalURL();
		return "\n<br/><form id=\"powersearch\" method=\"get\" " .
		  "action=\"$action\">\n{$powersearchtext}\n</form>\n";
	}

	protected function powerSearchBoxLine( $nsname, $nsshowname=NULL ) {
		global $wgContLang;
		if ($nsname=='Main' || $nsname=='(Main)')
			$nsnum = NS_MAIN;
		else
			$nsnum = $wgContLang->getNsIndex( $nsname );
		if( is_null( $nsshowname ))
			$nsshowname = $nsname;
                $checked = in_array( $nsnum, $this->namespaces ) ? ' checked="checked"' : '';
		return "<label><input type='checkbox' value='1' name='ns{$nsnum}' {$checked} onchange='SiteUnsetSelects(this)'/>{$nsshowname}</label><br/>\n";
	}
}
