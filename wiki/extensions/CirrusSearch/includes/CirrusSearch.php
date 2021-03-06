<?php

use CirrusSearch\Connection;
use CirrusSearch\ElasticsearchIntermediary;
use CirrusSearch\InterwikiSearcher;
use CirrusSearch\Search\FullTextResultsType;
use CirrusSearch\Searcher;
use CirrusSearch\CompletionSuggester;
use CirrusSearch\Search\ResultSet;
use CirrusSearch\SearchConfig;
use CirrusSearch\Search\FancyTitleResultsType;
use CirrusSearch\Search\TitleResultsType;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

/**
 * SearchEngine implementation for CirrusSearch.  Delegates to
 * CirrusSearchSearcher for searches and CirrusSearchUpdater for updates.  Note
 * that lots of search behavior is hooked in CirrusSearchHooks rather than
 * overridden here.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */
class CirrusSearch extends SearchEngine {
	const MORE_LIKE_THIS_PREFIX = 'morelike:';
	const MORE_LIKE_THIS_JUST_WIKIBASE_PREFIX = 'morelikewithwikibase:';

	const COMPLETION_SUGGESTER_FEATURE = 'completionSuggester';

	/**
	 * @var string The last prefix substituted by replacePrefixes.
	 */
	private $lastNamespacePrefix;

	/**
	 * @var array metrics about the last thing we searched
	 */
	private $lastSearchMetrics;

	/**
	 * @var string
	 */
	private $indexBaseName;

	/**
	 * @var Connection
	 */
	private $connection;

	/**
	 * Search configuration.
	 * @var SearchConfig
	 */
	private $config;

	/**
	 * Current request.
	 * @var WebRequest
	 */
	private $request;

	public function __construct( $baseName = null ) {
		$this->indexBaseName = $baseName === null ? wfWikiID() : $baseName;
		$this->config = MediaWikiServices::getInstance()
				->getConfigFactory()
				->makeConfig( 'CirrusSearch' );
		$this->connection = new Connection( $this->config );
		$this->request = RequestContext::getMain()->getRequest();
	}

	public function setConnection( Connection $connection ) {
		$this->connection = $connection;
	}

	/**
	 * @return Connection
	 */
	public function getConnection() {
		return $this->connection;
	}

	/**
	 * Set search config
	 * @param SearchConfig $config
	 */
	public function setConfig( SearchConfig $config ) {
		$this->config = $config;
	}

	/**
	 * Get search config
	 * @return SearchConfig
	 */
	public function getConfig() {
		return $this->config;
	}

	/**
	 * Override supports to shut off updates to Cirrus via the SearchEngine infrastructure.  Page
	 * updates and additions are chained on the end of the links update job.  Deletes are noticed
	 * via the ArticleDeleteComplete hook.
	 * @param string $feature feature name
	 * @return bool is this feature supported?
	 */
	public function supports( $feature ) {
		switch ( $feature ) {
		case 'search-update':
		case 'list-redirects':
			return false;
		default:
			return parent::supports( $feature );
		}
	}

	/**
	 * Overridden to delegate prefix searching to Searcher.
	 * @param string $term text to search
	 * @return ResultSet|null|Status results, no results, or error respectively
	 */
	public function searchText( $term ) {
		$config = null;
		if ( $this->request && $this->request->getVal( 'cirrusLang' ) ) {
			$config = new SearchConfig( $this->request->getVal( 'cirrusLang' ) );
		}
		$matches = $this->searchTextReal( $term, $config );
		if (!$matches instanceof ResultSet) {
			return $matches;
		}

		if ( $this->isFeatureEnabled( 'rewrite' ) &&
				$matches->isQueryRewriteAllowed( $GLOBALS['wgCirrusSearchInterwikiThreshold'] ) ) {
			$matches = $this->searchTextSecondTry( $term, $matches );
		}
		ElasticsearchIntermediary::setResultPages( array( $matches ) );

		return $matches;
	}

	/**
	 * Check whether we want to try another language.
	 * @param string $term Search term
	 * @return string[]|null Array of (interwiki, dbname) for another wiki to try, or null
	 */
	private function hasSecondaryLanguage( $term ) {
		if ( empty( $GLOBALS['wgCirrusSearchLanguageToWikiMap'] ) ||
				empty( $GLOBALS['wgCirrusSearchWikiToNameMap'] ) ) {
			// map's empty - no need to bother with detection
			return null;
		}

		$detected = null;
		foreach ( $GLOBALS['wgCirrusSearchLanguageDetectors'] as $name => $klass ) {
			if ( !class_exists( $klass ) ) {
				LoggerFactory::getInstance( 'CirrusSearch' )->info(
					"Unknown detector class for {name}: {class}",
					array(
						"name" => $name,
						"class" => $klass,
					)
				);
				continue;

			}
			$detector = new $klass();
			if( !( $detector instanceof \CirrusSearch\LanguageDetector\Detector ) ) {
				LoggerFactory::getInstance( 'CirrusSearch' )->info(
					"Bad detector class for {name}: {class}",
					array(
						"name" => $name,
						"class" => $klass,
					)
				);
				continue;
			}
			$lang = $detector->detect( $this, $term );
			$wiki = self::wikiForLanguage( $lang );
			if ( $wiki !== null ) {
				// it might be more accurate to attach these to the 'next'
				// log context? It would be inconsistent with the
				// langdetect => false condition which does not have a next
				// request though.
				Searcher::appendLastLogContext( array(
					'langdetect' => $name,
				) );
				$detected = $wiki;
				break;
			}
		}
		if ( $detected === null ) {
			Searcher::appendLastLogContext( array(
				'langdetect' => 'failed',
			) );
		}

		// check whether we have second language functionality enabled.
		// This comes after the actual detection so we can include the
		// results of detection in AB test control buckets.
		if ( !$GLOBALS['wgCirrusSearchEnableAltLanguage'] ) {
			return null;
		}

		return $detected;
	}

	/**
	 * @param string $lang Language code to find wiki for
	 * @return string[]|null Array of (interwiki, dbname) for wiki related to specified language code
	 */
	private static function wikiForLanguage( $lang ) {
		if ( empty( $GLOBALS['wgCirrusSearchLanguageToWikiMap'][$lang] ) ) {
			return null;
		}
		$interwiki = $GLOBALS['wgCirrusSearchLanguageToWikiMap'][$lang];

		if ( empty( $GLOBALS['wgCirrusSearchWikiToNameMap'][$interwiki] ) ) {
			return null;
		}
		$interWikiId = $GLOBALS['wgCirrusSearchWikiToNameMap'][$interwiki];
		if ( $interWikiId == wfWikiID() ) {
			// we're back to the same wiki, no use to try again
			return null;
		}

		return array( $interwiki, $interWikiId );
	}

	/**
	 * @param string $feature
	 * @return bool
	 */
	private function isFeatureEnabled( $feature ) {
		return isset( $this->features[$feature] ) && $this->features[$feature];
	}

	/**
	 * @param string $term
	 * @param ResultSet $oldResult
	 * @return ResultSet
	 */
	private function searchTextSecondTry( $term, ResultSet $oldResult ) {
		// TODO: figure out who goes first - language or suggestion?
		if ( $oldResult->numRows() == 0 && $oldResult->hasSuggestion() ) {
			$rewritten = $oldResult->getSuggestionQuery();
			$rewrittenSnippet = $oldResult->getSuggestionSnippet();
			$this->showSuggestion = false;
			$rewrittenResult = $this->searchTextReal( $rewritten );
			if (
				$rewrittenResult instanceof ResultSet
				&& $rewrittenResult->numRows() > 0
			) {
				$rewrittenResult->setRewrittenQuery( $rewritten, $rewrittenSnippet );
				if ( $rewrittenResult->numRows() < $GLOBALS['wgCirrusSearchInterwikiThreshold'] ) {
					// replace the result but still try the alt language
					$oldResult = $rewrittenResult;
				} else {
					return $rewrittenResult;
				}
			}
		}
		$altWiki = $this->hasSecondaryLanguage( $term );
		if ( $altWiki ) {
			try {
				$config = new SearchConfig( $altWiki[0], $altWiki[1] );
			} catch ( MWException $e ) {
				LoggerFactory::getInstance( 'CirrusSearch' )->info(
					"Failed to get config for {interwiki}:{dbwiki}",
					array(
						"interwiki" => $altWiki[0],
						"dbwiki" => $altWiki[1],
						"exception" => $e
					)
				);
				$config = null;
			}
			if ( $config ) {
				$matches = $this->searchTextReal( $term, $config );
				if ( $matches instanceof ResultSet && $matches->numRows() > 0 ) {
					$oldResult->addInterwikiResults( $matches, SearchResultSet::INLINE_RESULTS, $altWiki[1] );
				}
			}
		}

		// Don't have any other options yet.
		return $oldResult;
	}

	/**
	 * Do the hard part of the searching - actual Searcher invocation
	 * @param string $term
	 * @param SearchConfig $config
	 * @return null|Status|ResultSet
	 */
	private function searchTextReal( $term, SearchConfig $config = null ) {
		global $wgCirrusSearchInterwikiSources;

		// Convert the unicode character 'ideographic whitespace' into standard
		// whitespace.  Cirrussearch treats them both as normal whitespace, but
		// the preceding isn't appropriately trimmed.
		$term = trim( str_replace( "\xE3\x80\x80", " ", $term) );
		// No searching for nothing! That takes forever!
		if ( $term === '' ) {
			return null;
		}

		if ( $config ) {
			$this->indexBaseName = $config->getWikiId();
		}

		$searcher = new Searcher( $this->connection, $this->offset, $this->limit, $config, $this->namespaces, null, $this->indexBaseName );

		// Ignore leading ~ because it is used to force displaying search results but not to effect them
		if ( substr( $term, 0, 1 ) === '~' )  {
			$term = substr( $term, 1 );
			$searcher->addSuggestPrefix( '~' );
		}

		// TODO remove this when we no longer have to support core versions without
		// Ie946150c6796139201221dfa6f7750c210e97166
		if ( method_exists( $this, 'getSort' ) ) {
			$searcher->setSort( $this->getSort() );
		}

		$dumpQuery = $this->request && $this->request->getVal( 'cirrusDumpQuery' ) !== null;
		$searcher->setReturnQuery( $dumpQuery );
		$dumpResult = $this->request && $this->request->getVal( 'cirrusDumpResult' ) !== null;
		$searcher->setDumpResult( $dumpResult );
		$returnExplain = $this->request && $this->request->getVal( 'cirrusExplain' ) !== null;
		$searcher->setReturnExplain( $returnExplain );

		// Delegate to either searchText or moreLikeThisArticle and dump the result into $status
		if ( substr( $term, 0, strlen( self::MORE_LIKE_THIS_PREFIX ) ) === self::MORE_LIKE_THIS_PREFIX ) {
			$term = substr( $term, strlen( self::MORE_LIKE_THIS_PREFIX ) );
			$status = $this->moreLikeThis( $term, $searcher, Searcher::MORE_LIKE_THESE_NONE );
		} else if ( substr( $term, 0, strlen( self::MORE_LIKE_THIS_JUST_WIKIBASE_PREFIX ) ) === self::MORE_LIKE_THIS_JUST_WIKIBASE_PREFIX ) {
			$term = substr( $term, strlen( self::MORE_LIKE_THIS_JUST_WIKIBASE_PREFIX ) );
			$status = $this->moreLikeThis( $term, $searcher, Searcher::MORE_LIKE_THESE_ONLY_WIKIBASE );
		} else {
			# Namespace lookup should not be done for morelike special syntax (T111244)
			if ( $this->lastNamespacePrefix ) {
				$searcher->addSuggestPrefix( $this->lastNamespacePrefix );
			} else {
				$searcher->updateNamespacesFromQuery( $term );
			}
			$highlightingConfig = FullTextResultsType::HIGHLIGHT_ALL;
			if ( $this->request ) {
				if ( $this->request->getVal( 'cirrusSuppressSuggest' ) !== null ) {
					$this->showSuggestion = false;
				}
				if ( $this->request->getVal( 'cirrusSuppressTitleHighlight' ) !== null ) {
					$highlightingConfig ^= FullTextResultsType::HIGHLIGHT_TITLE;
				}
				if ( $this->request->getVal( 'cirrusSuppressAltTitle' ) !== null ) {
					$highlightingConfig ^= FullTextResultsType::HIGHLIGHT_ALT_TITLE;
				}
				if ( $this->request->getVal( 'cirrusSuppressSnippet' ) !== null ) {
					$highlightingConfig ^= FullTextResultsType::HIGHLIGHT_SNIPPET;
				}
				if ( $this->request->getVal( 'cirrusHighlightDefaultSimilarity' ) === 'no' ) {
					$highlightingConfig ^= FullTextResultsType::HIGHLIGHT_WITH_DEFAULT_SIMILARITY;
				}
				if ( $this->request->getVal( 'cirrusHighlightAltTitleWithPostings' ) === 'no' ) {
					$highlightingConfig ^= FullTextResultsType::HIGHLIGHT_ALT_TITLES_WITH_POSTINGS;
				}
			}
			if ( $this->namespaces && !in_array( NS_FILE, $this->namespaces ) ) {
				$highlightingConfig ^= FullTextResultsType::HIGHLIGHT_FILE_TEXT;
			}

			$searcher->setResultsType( new FullTextResultsType( $highlightingConfig, $config ? $config->getWikiCode() : '') );
			$status = $searcher->searchText( $term, $this->showSuggestion );
		}
		if ( $dumpQuery || $dumpResult ) {
			// When dumping the query we skip _everything_ but echoing the query.
			RequestContext::getMain()->getOutput()->disable();
			if ( $this->request && $this->request->getVal( 'cirrusExplain' ) === 'pretty' ) {
				$printer = new CirrusSearch\ExplainPrinter();
				echo $printer->format( $status->getValue() );
			} else {
				$this->request->response()->header( 'Content-type: application/json; charset=UTF-8' );
				if ( $status->getValue() === null ) {
					echo '{}';
				} else {
					echo json_encode( $status->getValue() );
				}
			}
			exit();
		}

		$this->lastSearchMetrics = $searcher->getSearchMetrics();

		// Add interwiki results, if we have a sane result
		// Note that we have no way of sending warning back to the user.  In this case all warnings
		// are logged when they are added to the status object so we just ignore them here....
		if ( $status->isOK() && $wgCirrusSearchInterwikiSources && $status->getValue() &&
				method_exists( $status->getValue(), 'addInterwikiResults' ) ) {
			// @todo @fixme: This should absolutely be a multisearch. I knew this when I
			// wrote the code but Searcher needs some refactoring first.
			foreach ( $wgCirrusSearchInterwikiSources as $interwiki => $index ) {
				$iwSearch = new InterwikiSearcher( $this->connection, $this->namespaces, null, $index, $interwiki );
				$interwikiResult = $iwSearch->getInterwikiResults( $term );
				if ( $interwikiResult ) {
					$status->getValue()->addInterwikiResults( $interwikiResult, SearchResultSet::SECONDARY_RESULTS, $interwiki );
				}
			}
		}

		// For historical reasons all callers of searchText interpret any Status return as an error
		// so we must unwrap all OK statuses.  Note that $status can be "good" and still contain null
		// since that is interpreted as no results.
		return $status->isOK() ? $status->getValue() : $status;
	}

	/**
	 * Look for suggestions using ES completion suggester.
	 * @param string $search Search string
	 * @param string[]|null $variants Search term variants
	 * @param SearchConfig $config search configuration
	 * @return SearchSuggestionSet Set of suggested names
	 */
	protected function getSuggestions( $search, $variants, SearchConfig $config ) {
		// offset is omitted, searchSuggestion does not support
		// scrolling results
		$suggester = new CompletionSuggester( $this->connection, $this->limit,
				$this->offset, $config, $this->namespaces, null,
				$this->indexBaseName );

		$response = $suggester->suggest( $search, $variants );
		if ( $response->isOK() ) {
			// Errors will be logged, let's try the exact db match
			return $response->getValue();
		} else {
			return SearchSuggestionSet::emptySuggestionSet();
		}
	}

	/**
	 * @param string $term
	 * @param Searcher $searcher
	 * @param int $options A bitset of Searcher::MORE_LIKE_THESE_*
	 * @return Status<SearchResultSet>
	 */
	private function moreLikeThis( $term, $searcher, $options ) {
		// Expand titles chasing through redirects
		$titles = array();
		$found = array();
		foreach ( explode( '|', $term ) as $title ) {
			$title = Title::newFromText( trim( $title ) );
			while ( true ) {
				if ( !$title ) {
					continue 2;
				}
				$titleText = $title->getFullText();
				if ( in_array( $titleText, $found ) ) {
					continue 2;
				}
				$found[] = $titleText;
				if ( !$title->exists() ) {
					continue 2;
				}
				if ( $title->isRedirect() ) {
					$page = WikiPage::factory( $title );
					if ( !$page->exists() ) {
						continue 2;
					}
					$title = $page->getRedirectTarget();
				} else {
					break;
				}
			}
			$titles[] = $title;
		}
		if ( count( $titles ) ) {
			return $searcher->moreLikeTheseArticles( $titles, $options );
		}
		return Status::newGood( new SearchResultSet( true ) /* empty */ );
	}

	/**
	 * Merge the prefix into the query (if any).
	 * @param string $term search term
	 * @return string possibly with a prefix appended
	 */
	public function transformSearchTerm( $term ) {
		if ( $this->prefix != '' ) {
			// Slap the standard prefix notation onto the query
			$term = $term . ' prefix:' . $this->prefix;
		}
		return $term;
	}

	/**
	 * @param string $query
	 * @return string
	 */
	public function replacePrefixes( $query ) {
		$parsed = parent::replacePrefixes( $query );
		if ( $parsed !== $query ) {
			$this->lastNamespacePrefix = substr( $query, 0, strlen( $query ) - strlen( $parsed ) );
		} else {
			$this->lastNamespacePrefix = '';
		}
		return $parsed;
	}

	/**
	 * Get the sort of sorts we allow
	 * @return string[]
	 */
	public function getValidSorts() {
		return array( 'relevance', 'title_asc', 'title_desc' );
	}

	/**
	 * Get the metrics for the last search we performed. Null if we haven't done any.
	 * @return array
	 */
	public function getLastSearchMetrics() {
		return $this->lastSearchMetrics;
	}

	protected function completionSuggesterEnabled( SearchConfig $config ) {
		$useCompletion = $config->getElement( 'CirrusSearchUseCompletionSuggester' );
		if( $useCompletion !== 'yes' && $useCompletion !== 'beta' ) {
			return false;
		}

		// This way API can force-enable completion suggester
		if ( $this->isFeatureEnabled( self::COMPLETION_SUGGESTER_FEATURE ) ) {
			return true;
		}

		// Allow falling back to prefix search with query param
		if ( $this->request && $this->request->getVal( 'cirrusUseCompletionSuggester' ) === 'no' ) {
			return false;
		}

		// Allow experimentation with query parameters
		if ( $this->request && $this->request->getVal( 'cirrusUseCompletionSuggester' ) === 'yes' ) {
			return true;
		}

		if ( $useCompletion === 'beta' ) {
			return class_exists( '\BetaFeatures' ) &&
				\BetaFeatures::isFeatureEnabled( $GLOBALS['wgUser'], 'cirrussearch-completionsuggester' );
		}

		return true;
	}

	/**
	 * Perform a completion search.
	 * Does not resolve namespaces and does not check variants.
	 * We use parent search for:
	 * - Special: namespace
	 * We use old prefix search for:
	 * - Suggester not enabled
	 * -
	 * @param string $search
	 * @return SearchSuggestionSet
	 */
	protected function completionSearchBackend( $search ) {

		if ( in_array( NS_SPECIAL, $this->namespaces) ) {
			// delegate special search to parent
			return parent::completionSearchBackend( $search );
		}

		if ( !$this->completionSuggesterEnabled( $this->config ) ) {
			// Completion suggester is not enabled, fallback to
			// default implementation
			return $this->prefixSearch( $search );
		}

		if ( count( $this->namespaces ) != 1 ||
		     reset( $this->namespaces ) != NS_MAIN ) {
			// for now, suggester only works for main namespace
			return $this->prefixSearch( $search );
		}

		// Not really useful, mostly for testing purpose
		$variants = $this->request->getArray( 'cirrusCompletionSuggesterVariant' );
		if ( empty( $variants ) ) {
			global $wgContLang;
			$variants = $wgContLang->autoConvertToAllVariants( $search );
		} else if ( count( $variants ) > 3 ) {
			// We should not allow too many variants
			$variants = array_slice( $variants, 0, 3 );
		}

		return $this->getSuggestions( $search, $variants, $this->config );
	}

	/**
	 * Override variants function because we always do variants
	 * in the backend.
	 * @see SearchEngine::completionSearchWithVariants()
	 * @param string $search
	 * @return SearchSuggestionSet
	 */
	public function completionSearchWithVariants( $search ) {
		return $this->completionSearch( $search );
	}

	/**
	 * Older prefix search.
	 * @param string $search search text
	 * @return SearchSuggestionSet
	 */
	protected function prefixSearch( $search ) {
		$searcher = new Searcher( $this->connection, $this->offset, $this->limit, null, $this->namespaces );

		if ( $search ) {
			$searcher->setResultsType( new FancyTitleResultsType( 'prefix' ) );
		} else {
			// Empty searches always find the title.
			$searcher->setResultsType( new TitleResultsType() );
		}

		try {
			$status = $searcher->prefixSearch( $search );
		} catch ( UsageException $e ) {
			if ( defined( 'MW_API' ) ) {
				throw $e;
			}
			return SearchSuggestionSet::emptySuggestionSet();
		}

		// There is no way to send errors or warnings back to the caller here so we have to make do with
		// only sending results back if there are results and relying on the logging done at the status
		// construction site to log errors.
		if ( $status->isOK() ) {
			if ( !$search ) {
				// No need to unpack the simple title matches from non-fancy TitleResultsType
				return SearchSuggestionSet::fromTitles( $status->getValue() );
			}
			$results = array_filter( array_map( function( $match ) {
				if ( isset( $match[ 'titleMatch' ] ) ) {
					return $match[ 'titleMatch' ];
				} else {
					if ( isset( $match[ 'redirectMatches' ][ 0 ] ) ) {
						// TODO maybe dig around in the redirect matches and find the best one?
						return $match[ 'redirectMatches' ][0];
					}
				}
				return false;
			}, $status->getValue() ) );
			return SearchSuggestionSet::fromTitles( $results );
		}

		return SearchSuggestionSet::emptySuggestionSet();
	}
}
