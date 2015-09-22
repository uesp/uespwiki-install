<?php

namespace CirrusSearch;
use Elastica;
use \CirrusSearch;
use \MWNamespace;
use \PoolCounterWorkViaCallback;
use \ProfileSection;
use \Sanitizer;
use \Status;
use \Title;
use \UsageException;

/**
 * Performs searches using Elasticsearch.  Note that each instance of this class
 * is single use only.
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
class Searcher extends ElasticsearchIntermediary {
	const SUGGESTION_NAME_TITLE = 'title';
	const SUGGESTION_NAME_REDIRECT = 'redirect';
	const SUGGESTION_NAME_TEXT = 'text_suggestion';
	const SUGGESTION_HIGHLIGHT_PRE = '<em>';
	const SUGGESTION_HIGHLIGHT_POST = '</em>';
	const HIGHLIGHT_PRE = '<span class="searchmatch">';
	const HIGHLIGHT_POST = '</span>';
	const HIGHLIGHT_REGEX = '/<span class="searchmatch">.*?<\/span>/';

	/**
	 * Maximum title length that we'll check in prefix and keyword searches.
	 * Since titles can be 255 bytes in length we're setting this to 255
	 * characters.
	 */
	const MAX_TITLE_SEARCH = 255;

	/**
	 * @var integer search offset
	 */
	private $offset;

	/**
	 * @var integer maximum number of result
	 */
	private $limit;

	/**
	 * @var array(integer) namespaces in which to search
	 */
	protected $namespaces;

	/**
	 * @var ResultsType|null type of results.  null defaults to FullTextResultsType
	 */
	private $resultsType;
	/**
	 * @var string sort type
	 */
	private $sort = 'relevance';
	/**
	 * @var array(string) prefixes that should be prepended to suggestions.  Can be added to externally and is added to
	 * during search syntax parsing.
	 */
	private $suggestPrefixes = array();
	/**
	 * @var array(string) suffixes that should be prepended to suggestions.  Can be added to externally and is added to
	 * during search syntax parsing.
	 */
	private $suggestSuffixes = array();


	// These fields are filled in by the particule search methods
	/**
	 * @var string term to search.
	 */
	private $term;
	/**
	 * @var \Elastica\Query\AbstractQuery|null main query.  null defaults to \Elastica\Query\MatchAll
	 */
	private $query = null;
	/**
	 * @var array(\Elastica\Filter\AbstractFilter) filters that MUST hold true of all results
	 */
	private $filters = array();
	/**
	 * @var array(\Elastica\Filter\AbstractFilter) filters that MUST NOT hold true of all results
	 */
	private $notFilters = array();
	private $suggest = null;
	/**
	 * @var null|array of rescore configuration as used by elasticsearch.  The query needs to be an Elastica query.
	 */
	private $rescore = null;
	/**
	 * @var float portion of article's score which decays with time.  Defaults to 0 meaning don't decay the score
	 * with time since the last update.
	 */
	private $preferRecentDecayPortion = 0;
	/**
	 * @var float number of days it takes an the portion of an article score that will decay with time
	 * since last update to decay half way.  Defaults to 0 meaning don't decay the score with time.
	 */
	private $preferRecentHalfLife = 0;
	/**
	 * @var boolean should the query results boost pages with more incoming links.  Default to false.
	 */
	private $boostLinks = false;
	/**
	 * @var array template name to boost multiplier for having a template.  Defaults to none but initialized by
	 * queries that use it to self::getDefaultBoostTemplates() if they need it.  That is too expensive to do by
	 * default though.
	 */
	private $boostTemplates = array();
	/**
	 * @var string index base name to use
	 */
	private $indexBaseName;
	/**
	 * @var bool should this search show redirects?
	 */
	private $showRedirects;

	/**
	 * @var boolean is this a fuzzy query?
	 */
	private $fuzzyQuery = false;
	/**
	 * @var boolean did this search contain any special search syntax?
	 */
	private $searchContainedSyntax = false;
	/**
	 * @var null|\Elastica\AbstractQuery query that should be used for highlighting if different from the
	 * query used for selecting.
	 */
	private $highlightQuery = null;

	/**
	 * @var array indexes to use, if not the default
	 */
	private $explicitIndexes;

	/**
	 * Constructor
	 * @param int $offset Offset the results by this much
	 * @param int $limit Limit the results to this many
	 * @param array $namespaces Namespace numbers to search
	 * @param User|null $user user for which this search is being performed.  Attached to slow request logs.
	 * @param string $index Base name for index to search from, defaults to wfWikiId()
	 */
	public function __construct( $offset, $limit, $namespaces, $user, $index = false ) {
		global $wgCirrusSearchSlowSearch;

		parent::__construct( $user, $wgCirrusSearchSlowSearch );
		$this->offset = $offset;
		$this->limit = $limit;
		$this->namespaces = $namespaces;
		$this->indexBaseName = $index ?: wfWikiId();
	}

	/**
	 * @param ResultsType $resultsType results type to return
	 */
	public function setResultsType( $resultsType ) {
		$this->resultsType = $resultsType;
	}

	/**
	 * Set the type of sort to perform.  Must be 'relevance', 'title_asc', 'title_desc'.
	 * @param string sort type
	 */
	public function setSort( $sort ) {
		$this->sort = $sort;
	}

	/**
	 * @param array $idx Indexes to use, explicitly
	 */
	public function setExplicitIndexes( $idxs ) {
		$this->explicitIndexes = $idxs;
	}

	/**
	 * Perform a "near match" title search which is pretty much a prefix match without the prefixes.
	 * @param string $search text by which to search
	 * @return Status(mixed) status containing results defined by resultsType on success
	 */
	public function nearMatchTitleSearch( $search ) {
		$profiler = new ProfileSection( __METHOD__ );

		self::checkTitleSearchRequestLength( $search );

		// Elasticsearch seems to have trouble extracting the proper terms to highlight
		// from the default query we make so we feed it exactly the right query to highlight.
		$this->highlightQuery = new \Elastica\Query\MultiMatch();
		$this->highlightQuery->setQuery( $search );
		$this->highlightQuery->setFields( array( 'title.near_match', 'redirect.title.near_match' ) );
		$this->filters[] = new \Elastica\Filter\Query( $this->highlightQuery );
		$this->boostLinks = ''; // No boost

		return $this->search( 'near_match', $search );
	}

	/**
	 * Perform a prefix search.
	 * @param string $search text by which to search
	 * @param Status(mixed) status containing results defined by resultsType on success
	 */
	public function prefixSearch( $search ) {
		global $wgCirrusSearchPrefixSearchStartsWithAnyWord;

		$profiler = new ProfileSection( __METHOD__ );

		self::checkTitleSearchRequestLength( $search );

		if ( $wgCirrusSearchPrefixSearchStartsWithAnyWord ) {
			$match = new \Elastica\Query\Match();
			$match->setField( 'title.word_prefix', array(
				'query' => $search,
				'analyzer' => 'plain',
				'operator' => 'and',
			) );
			$this->filters[] = new \Elastica\Filter\Query( $match );
		} else {
			// Elasticsearch seems to have trouble extracting the proper terms to highlight
			// from the default query we make so we feed it exactly the right query to highlight.
			$this->highlightQuery = $this->buildPrefixQuery( $search );
			$this->filters[] = new \Elastica\Filter\Query( $this->highlightQuery );
		}
		$this->boostTemplates = self::getDefaultBoostTemplates();
		// If there aren't any boost templates then we can use a sort for ordering
		// rather than a boost.
		if ( count( $this->boostTemplates ) === 0 ) {
			$this->sort = 'incoming_links_desc';
		}

		return $this->search( 'prefix', $search );
	}

	/**
	 * @param string $suggestPrefix prefix to be prepended to suggestions
	 */
	public function addSuggestPrefix( $suggestPrefix ) {
		$this->suggestPrefixes[] = $suggestPrefix;
	}

	/**
	 * Search articles with provided term.
	 * @param $term string term to search
	 * @param $showRedirects boolean should this request show redirects?
	 * @param boolean $showSuggestion should this search suggest alternative searches that might be better?
	 * @param Status(mixed) status containing results defined by resultsType on success
	 */
	public function searchText( $term, $showRedirects, $showSuggestion ) {
		global $wgCirrusSearchPhraseRescoreBoost,
			$wgCirrusSearchPhraseRescoreWindowSize,
			$wgCirrusSearchPhraseUseText,
			$wgCirrusSearchPreferRecentDefaultDecayPortion,
			$wgCirrusSearchPreferRecentDefaultHalfLife,
			$wgCirrusSearchNearMatchWeight,
			$wgCirrusSearchStemmedWeight;

		$profiler = new ProfileSection( __METHOD__ );

		// Transform Mediawiki specific syntax to filters and extra (pre-escaped) query string
		$searcher = $this;
		$originalTerm = $term;
		$searchContainedSyntax = false;
		$this->showRedirects = $showRedirects;
		$this->term = trim( $term );
		$this->boostLinks = true;
		// Handle title prefix notation
		wfProfileIn( __METHOD__ . '-prefix-filter' );
		$prefixPos = strpos( $this->term, 'prefix:' );
		if ( $prefixPos !== false ) {
			$value = substr( $this->term, 7 + $prefixPos );
			$value = trim( $value, '"' ); // Trim quotes in case the user wanted to quote the prefix
			if ( strlen( $value ) > 0 ) {
				$searchContainedSyntax = true;
				$this->term = substr( $this->term, 0, max( 0, $prefixPos - 1 ) );
				$this->suggestSuffixes[] = ' prefix:' . $value;
				// Suck namespaces out of $value
				$cirrusSearchEngine = new CirrusSearch();
				$value = trim( $cirrusSearchEngine->replacePrefixes( $value ) );
				$this->namespaces = $cirrusSearchEngine->namespaces;
				// If the namespace prefix wasn't the entire prefix filter then add a filter for the title
				if ( strpos( $value, ':' ) !== strlen( $value ) - 1 ) {
					$value = str_replace( '_', ' ', $value );
					$this->filters[] = new \Elastica\Filter\Query( $this->buildPrefixQuery( $value ) );
				}
			}
		}
		wfProfileOut( __METHOD__ . '-prefix-filter' );

		wfProfileIn( __METHOD__ . '-prefer-recent' );
		$preferRecentDecayPortion = $wgCirrusSearchPreferRecentDefaultDecayPortion;
		$preferRecentHalfLife = $wgCirrusSearchPreferRecentDefaultHalfLife;
		// Matches "prefer-recent:" and then an optional floating point number <= 1 but >= 0 (decay
		// portion) and then an optional comma followed by another floating point number >= 0 (half life)
		$this->extractSpecialSyntaxFromTerm(
			'/prefer-recent:(1|(?:0?(?:\.[0-9]+)?))?(?:,([0-9]*\.?[0-9]+))? ?/',
			function ( $matches ) use ( &$preferRecentDecayPortion, &$preferRecentHalfLife,
					&$searchContainedSyntax ) {
				global $wgCirrusSearchPreferRecentUnspecifiedDecayPortion;
				if ( isset( $matches[ 1 ] ) && strlen( $matches[ 1 ] ) ) {
					$preferRecentDecayPortion = floatval( $matches[ 1 ] );
				} else {
					$preferRecentDecayPortion = $wgCirrusSearchPreferRecentUnspecifiedDecayPortion;
				}
				if ( isset( $matches[ 2 ] ) ) {
					$preferRecentHalfLife = floatval( $matches[ 2 ] );
				}
				$searchContainedSyntax = true;
				return '';
			}
		);
		$this->preferRecentDecayPortion = $preferRecentDecayPortion;
		$this->preferRecentHalfLife = $preferRecentHalfLife;
		wfProfileOut( __METHOD__ . '-prefer-recent' );

		// Handle other filters
		wfProfileIn( __METHOD__ . '-other-filters' );
		$filters = $this->filters;
		$notFilters = $this->notFilters;
		$boostTemplates = self::getDefaultBoostTemplates();
		// Match filters that look like foobar:thing or foobar:"thing thing"
		// The {7,15} keeps this from having horrible performance on big strings
		$this->extractSpecialSyntaxFromTerm(
			'/(?<key>[a-z\\-]{7,15}):(?<value>(?:"[^"]+")|(?:[^ "]+)) ?/',
			function ( $matches ) use ( $searcher, &$filters, &$notFilters, &$boostTemplates,
					&$searchContainedSyntax ) {
				$key = $matches['key'];
				$value = $matches['value'];  // Note that if the user supplied quotes they are not removed
				$filterDestination = &$filters;
				$keepText = true;
				if ( $key[ 0 ] === '-' ) {
					$key = substr( $key, 1 );
					$filterDestination = &$notFilters;
					$keepText = false;
				}
				switch ( $key ) {
					case 'boost-templates':
						$boostTemplates = Searcher::parseBoostTemplates( trim( $value, '"' ) );
						if ( $boostTemplates === null ) {
							$boostTemplates = self::getDefaultBoostTemplates();
						}
						$searchContainedSyntax = true;
						return '';
					case 'hastemplate':
						$value = trim( $value, '"' );
						// We emulate template syntax here as best as possible,
						// so things in NS_MAIN are prefixed with ":" and things
						// in NS_TEMPLATE don't have a prefix at all. Since we
						// don't actually index templates like that, munge the
						// query here
						if ( strpos( $value, ':' ) === 0 ) {
							$value = substr( $value, 1 );
						} else {
							$title = Title::newFromText( $value );
							if ( $title && $title->getNamespace() == NS_MAIN ) {
								$value = Title::makeTitle( NS_TEMPLATE,
									$title->getDBkey() )->getPrefixedText();
							}
						}
						// Intentional fall through
					case 'incategory':
						$queryKey = str_replace( array( 'in', 'has' ), '', $key );
						$queryValue = str_replace( '_', ' ', trim( $value, '"' ) );
						$match = new \Elastica\Query\Match();
						$match->setFieldQuery( $queryKey, $queryValue );
						$filterDestination[] = new \Elastica\Filter\Query( $match );
						$searchContainedSyntax = true;
						return '';
					case 'intitle':
						$query = new \Elastica\Query\QueryString(
							$searcher->fixupWholeQueryString(
								$searcher->fixupQueryStringPart( $value )
							) );
						$query->setFields( array( 'title' ) );
						$query->setDefaultOperator( 'AND' );
						$query->setAllowLeadingWildcard( false );
						$query->setFuzzyPrefixLength( 2 );
						$filterDestination[] = new \Elastica\Filter\Query( $query );
						$searchContainedSyntax = true;
						return $keepText ? "$value " : '';
					default:
						return $matches[0];
				}
			}
		);
		$this->filters = $filters;
		$this->notFilters = $notFilters;
		$this->boostTemplates = $boostTemplates;
		$this->searchContainedSyntax = $searchContainedSyntax;
		wfProfileOut( __METHOD__ . '-other-filters' );
		wfProfileIn( __METHOD__ . '-find-phrase-queries' );
		// Match quoted phrases including those containing escaped quotes
		// Those phrases can optionally be followed by ~ then a number (this is the phrase slop)
		// That can optionally be followed by a ~ (this matches stemmed words in phrases)
		// The following all match: "a", "a boat", "a\"boat", "a boat"~, "a boat"~9, "a boat"~9~
		$query = self::replacePartsOfQuery( $this->term, '/(?<main>"((?:[^"]|(?:\"))+)"(?:~[0-9]+)?)(?<fuzzy>~)?/',
			function ( $matches ) use ( $searcher ) {
				$main = $searcher->fixupQueryStringPart( $matches[ 'main' ][ 0 ] );
				if ( !isset( $matches[ 'fuzzy' ] ) ) {
					$main = $searcher->switchSearchToExact( $main );
				}
				return array( 'escaped' => $main );
			} );
		wfProfileOut( __METHOD__ . '-find-phrase-queries' );
		wfProfileIn( __METHOD__ . '-switch-prefix-to-plain' );
		// Find prefix matches and force them to only match against the plain analyzed fields.  This
		// prevents prefix matches from getting confused by stemming.  Users really don't expect stemming
		// in prefix queries.
		$query = self::replaceAllPartsOfQuery( $query, '/\w*\*(?:\w*\*?)*/',
			function ( $matches ) use ( $searcher ) {
				$term = $searcher->fixupQueryStringPart( $matches[ 0 ][ 0 ] );
				return array( 'escaped' => $searcher->switchSearchToExact( $term ) );
			} );
		wfProfileOut( __METHOD__ . '-switch-prefix-to-plain' );

		wfProfileIn( __METHOD__ . '-escape' );
		$escapedQuery = array();
		foreach ( $query as $queryPart ) {
			if ( isset( $queryPart[ 'escaped' ] ) ) {
				$escapedQuery[] = $queryPart[ 'escaped' ];
				continue;
			}
			if ( isset( $queryPart[ 'raw' ] ) ) {
				$escapedQuery[] = $this->fixupQueryStringPart( $queryPart[ 'raw' ] );
				continue;
			}
			wfLogWarning( 'Unknown query part:  ' . serialize( $queryPart ) );
		}
		wfProfileOut( __METHOD__ . '-escape' );

		// Actual text query
		$queryStringQueryString = $this->fixupWholeQueryString( implode( ' ', $escapedQuery ) );
		if ( $queryStringQueryString !== '' ) {
			if ( $this->queryStringContainsSyntax( $queryStringQueryString ) ) {
				$this->searchContainedSyntax = true;
				// We're unlikey to make good suggestions for query string with special syntax in them....
				$showSuggestion = false;
			}
			wfProfileIn( __METHOD__ . '-build-query' );
			$fields = array_merge(
				$this->buildFullTextSearchFields( 1, '.plain' ),
				$this->buildFullTextSearchFields( $wgCirrusSearchStemmedWeight, '' ) );
			$nearMatchFields = $this->buildFullTextSearchFields( $wgCirrusSearchNearMatchWeight, '.near_match' );
			$this->query = $this->buildSearchTextQuery( $fields, $nearMatchFields, $queryStringQueryString );

			// Only do a phrase match rescore if the query doesn't include any quotes and has a space
			// TODO allow phrases without spaces to support things like words with dashes and languages
			// that don't use spaces.  The space check is really only important because it catches an
			// common class of slow queries: <<-foo>> which it only needs to catch because Elasticsearch
			// only supports a single rescore.  If it supported multiple rescores it would be worth
			// trying the phrase rescore because it wouldn't prevent us from having the script score in
			// a rescore.
			if ( $wgCirrusSearchPhraseRescoreBoost > 1.0 &&
					!$this->searchContainedSyntax &&
					strpos( $queryStringQueryString, '"' ) === false &&
					strpos( $queryStringQueryString, ' ' ) !== false ) {
				$this->rescore = array(
					'window_size' => $wgCirrusSearchPhraseRescoreWindowSize,
					'query' => array(
						'rescore_query' => $this->buildSearchTextQueryForFields( $fields, 
							'"' . $queryStringQueryString . '"' ),
						'query_weight' => 1.0,
						'rescore_query_weight' => $wgCirrusSearchPhraseRescoreBoost,
					)
				);
			}

			if ( $showSuggestion ) {
				$this->suggest = array(
					'text' => $this->term,
					self::SUGGESTION_NAME_TITLE => $this->buildSuggestConfig( 'title.suggest' ),
				);
				if ( $showRedirects ) {
					$this->suggest[ self::SUGGESTION_NAME_REDIRECT ] = $this->buildSuggestConfig( 'redirect.title.suggest' );
				}
				if ( $wgCirrusSearchPhraseUseText ) {
					$this->suggest[ self::SUGGESTION_NAME_TEXT ] = $this->buildSuggestConfig( 'text.suggest' );
				}
			}
			wfProfileOut( __METHOD__ . '-build-query' );

			$result = $this->search( 'full_text', $originalTerm );

			if ( !$result->isOK() && $this->isParseError( $result ) ) {
				wfProfileIn( __METHOD__ . '-degraded-query' );
				// Elasticsearch has reported a parse error and we've already logged it when we built the status
				// so at this point all we can do is retry the query as a simple query string query.
				$this->query = new \Elastica\Query\Simple( array( 'simple_query_string' => array(
					'fields' => $fields,
					'query' => $queryStringQueryString,
					'default_operator' => 'AND',
				) ) );
				$this->rescore = null; // Not worth trying in this state.
				$result = $this->search( 'degraded_full_text', $originalTerm );
				// If that doesn't work we're out of luck but it should.  There no guarantee it'll work properly
				// with the syntax we've built above but it'll do _something_ and we'll still work on fixing all
				// the parse errors that come in.
				wfProfileOut( __METHOD__ . '-degraded-query' );
			}
		} else {
			$result = $this->search( 'full_text', $originalTerm );
			// No need to check for a parse error here because we don't actually create a query for
			// Elasticsearch to parse
		}

		return $result;
	}

	/**
	 * @param $id article id to search
	 * @return Status(ResultSet|null)
	 */
	public function moreLikeThisArticle( $id ) {
		global $wgCirrusSearchMoreLikeThisConfig;

		$profiler = new ProfileSection( __METHOD__ );

		// It'd be better to be able to have Elasticsearch fetch this during the query rather than make
		// two passes but it doesn't support that at this point
		$found = $this->get( $id, array( 'text' ) );
		if ( !$found->isOk() ) {
			return $found;
		}
		$found = $found->getValue();
		if ( $found === null ) {
			// If the page doesn't exist we can't find any articles like it
			return Status::newGood( null );
		}

		$this->query = new \Elastica\Query\MoreLikeThis();
		$this->query->setParams( $wgCirrusSearchMoreLikeThisConfig );
		// TODO figure out why we strip tags here and document it.
		$this->query->setLikeText( Sanitizer::stripAllTags( $found->text ) );
		$this->query->setFields( array( 'text' ) );
		$idFilter = new \Elastica\Filter\Ids();
		$idFilter->addId( $id );
		$this->filters[] = new \Elastica\Filter\BoolNot( $idFilter );

		return $this->search( 'more_like', "$found->namespace:$found->title" );
	}

	/**
	 * Get the page with $id.
	 * @param $id int page id
	 * @param $fields array(string) fields to fetch
	 * @return Status containing page data, null if not found, or an error if there was an error
	 */
	public function get( $id, $fields ) {
		$profiler = new ProfileSection( __METHOD__ );

		$searcher = $this;
		$indexType = $this->pickIndexTypeFromNamespaces();
		$indexBaseName = $this->indexBaseName;
		$getWork = new PoolCounterWorkViaCallback( 'CirrusSearch-Search', "_elasticsearch", array(
			'doWork' => function() use ( $searcher, $id, $fields, $indexType, $indexBaseName ) {
				try {
					$searcher->start( "get of $indexType.$id" );
					$pageType = Connection::getPageType( $indexBaseName, $indexType );
					return $searcher->success( $pageType->getDocument( $id, array( 'fields' => $fields, ) ) );
				} catch ( \Elastica\Exception\NotFoundException $e ) {
					// NotFoundException just means the field didn't exist.
					// It is up to the called to decide if that is and error.
					return $searcher->success( null );
				} catch ( \Elastica\Exception\ExceptionInterface $e ) {
					return $searcher->failure( $e );
				}
			},
			'error' => function( $status ) {
				$status = $status->getErrorsArray();
				wfLogWarning( 'Pool error performing a get against Elasticsearch:  ' . $status[ 0 ][ 0 ] );
				return Status::newFatal( 'cirrussearch-backend-error' );
			}
		) );

		return $getWork->execute();
	}

	private function extractSpecialSyntaxFromTerm( $regex, $callback ) {
		$suggestPrefixes = $this->suggestPrefixes;
		$this->term = preg_replace_callback( $regex,
			function ( $matches ) use ( $callback, &$suggestPrefixes ) {
				$result = $callback( $matches );
				if ( $result === '' ) {
					$suggestPrefixes[] = $matches[ 0 ];
				}
				return $result;
			},
			$this->term
		);
		$this->suggestPrefixes = $suggestPrefixes;
	}

	private static function replaceAllPartsOfQuery( $query, $regex, $callable ) {
		$result = array();
		foreach ( $query as $queryPart ) {
			if ( isset( $queryPart[ 'raw' ] ) ) {
				$result = array_merge( $result, self::replacePartsOfQuery( $queryPart[ 'raw' ], $regex, $callable ) );
				continue;
			}
			$result[] = $queryPart;
		}
		return $result;
	}

	private static function replacePartsOfQuery( $queryPart, $regex, $callable ) {
		$destination = array();
		$matches = array();
		$offset = 0;
		while ( preg_match( $regex, $queryPart, $matches, PREG_OFFSET_CAPTURE, $offset ) ) {
			$startOffset = $matches[ 0 ][ 1 ];
			if ( $startOffset > $offset ) {
				$destination[] = array( 'raw' => substr( $queryPart, $offset, $startOffset - $offset ) );
			}

			$callableResult = call_user_func( $callable, $matches );
			if ( $callableResult ) {
				$destination[] = $callableResult;
			}

			$offset = $startOffset + strlen( $matches[ 0 ][ 0 ] );
		}
		if ( $offset < strlen( $queryPart ) ) {
			$destination[] = array( 'raw' => substr( $queryPart, $offset ) );
		}
		return $destination;
	}

	/**
	 * Get the version of Elasticsearch with which we're communicating.
	 * @return Status(string) version number as a string
	 */
	public function getElasticsearchVersion() {
		global $wgMemc;

		$profiler = new ProfileSection( __METHOD__ );

		$mcKey = wfMemcKey( 'CirrusSearch', 'Elasticsearch', 'version' );
		$result = $wgMemc->get( $mcKey );
		if ( !$result ) {
			try {
				$this->start( 'fetching elasticsearch version' );
				$result = Connection::getClient()->request( '' );
				$this->success();
			} catch ( \Elastica\Exception\ExceptionInterface $e ) {
				return $this->failure( $e );
			}
			$result = $result->getData();
			$result = $result[ 'version' ][ 'number' ];
			$wgMemc->set( $mcKey, $result, 3600 * 12 );
		}

		return Status::newGood( $result );
	}

	/**
	 * Powers full-text-like searches including prefix search.
	 * @return Status(ResultSet|null|array(String)) results, no results, or title results
	 */
	private function search( $type, $for ) {
		global $wgCirrusSearchMoreAccurateScoringMode;

		$profiler = new ProfileSection( __METHOD__ );

		if ( $this->resultsType === null ) {
			$this->resultsType = new FullTextResultsType();
		}
		// Default null queries now so the rest of the method can assume it is not null.
		if ( $this->query === null ) {
			$this->query = new \Elastica\Query\MatchAll();
		}

		$query = new Elastica\Query();
		$query->setFields( $this->resultsType->getFields() );
		$scriptFields = $this->resultsType->getScriptFields();
		if ( $scriptFields ) {
			$query->setScriptFields( $scriptFields );
		}

		$extraIndexes = array();
		if ( $this->namespaces ) {
			if ( count( $this->namespaces ) < count( MWNamespace::getValidNamespaces() ) ) {
				$this->filters[] = new \Elastica\Filter\Terms( 'namespace', $this->namespaces );
			}
			$extraIndexes = $this->getAndFilterExtraIndexes();
		}

		// Wrap $this->query in a filtered query if there are filters.
		$filterCount = count( $this->filters );
		$notFilterCount = count( $this->notFilters );
		if ( $filterCount > 0 || $notFilterCount > 0 ) {
			if ( $filterCount > 1 || $notFilterCount > 0 ) {
				$filter = new \Elastica\Filter\Bool();
				foreach ( $this->filters as $must ) {
					$filter->addMust( $must );
				}
				foreach ( $this->notFilters as $mustNot ) {
					$filter->addMustNot( $mustNot );
				}
			} else {
				$filter = $this->filters[ 0 ];
			}
			$this->query = new \Elastica\Query\Filtered( $this->query, $filter );
		}

		// Call installBoosts right after we're done munging the query to include filters
		// so any rescores installBoosts adds to the query are done against filtered results.
		$this->installBoosts();

		$query->setQuery( $this->query );

		$highlight = $this->resultsType->getHighlightingConfiguration();
		if ( $highlight ) {
			// Fuzzy queries work _terribly_ with the plain highlighter so just drop any field that is forcing
			// the plain highlighter all together.  Do this here because this works so badly that no
			// ResultsType should be able to use the plain highlighter for these queries.
			if ( $this->fuzzyQuery ) {
				$highlight[ 'fields' ] = array_filter( $highlight[ 'fields' ], function( $field ) {
					return $field[ 'type' ] !== 'plain';
				});
			}
			if ( $this->highlightQuery ) {
				$highlight[ 'highlight_query' ] = $this->highlightQuery->toArray();
			}
			$query->setHighlight( $highlight );
		}
		if ( $this->suggest ) {
			$query->setParam( 'suggest', $this->suggest );
			$query->addParam( 'stats', 'suggest' );
		}
		if( $this->offset ) {
			$query->setFrom( $this->offset );
		}
		if( $this->limit ) {
			$query->setSize( $this->limit );
		}
		if ( $this->rescore ) {
			// rescore_query has to be in array form before we send it to Elasticsearch but it is way easier to work
			// with if we leave it in query for until now
			$this->rescore[ 'query' ][ 'rescore_query' ] = $this->rescore[ 'query' ][ 'rescore_query' ]->toArray();
			$query->setParam( 'rescore', $this->rescore );
		}

		$query->addParam( 'stats', $type );
		switch ( $this->sort ) {
		case 'relevance':
			break;  // The default
		case 'title_asc':
			$query->setSort( array( 'title.keyword' => 'asc' ) );
			break;
		case 'title_desc':
			$query->setSort( array( 'title.keyword' => 'desc' ) );
			break;
		case 'incoming_links_asc':
			$query->setSort( array( 'incoming_links' => array(
				'order' => 'asc',
				'missing' => '_first',
			) ) );
			break;
		case 'incoming_links_desc':
			$query->setSort( array( 'incoming_links' => array(
				'order' => 'desc',
				'missing' => '_last',
			) ) );
			break;
		default:
			wfLogWarning( "Invalid sort type:  $this->sort" );
		}

		$queryOptions = array();
		if ( $wgCirrusSearchMoreAccurateScoringMode ) {
			$queryOptions[ 'search_type' ] = 'dfs_query_then_fetch';
		}

		// Setup the search
		if ( $this->explicitIndexes ) {
			$baseName = array_shift( $this->explicitIndexes );
			$extraIndexes = $this->explicitIndexes;
			$pageType = Connection::getPageType( $baseName );
				
		} else {
			$pageType = Connection::getPageType( $this->indexBaseName,
				$this->pickIndexTypeFromNamespaces() );
		}
		$search = $pageType->createSearch( $query, $queryOptions );
		foreach ( $extraIndexes as $i ) {
			$search->addIndex( $i );
		}

		$description = "$type search for '$for'";

		// Perform the search
		$searcher = $this;
		$work = new PoolCounterWorkViaCallback( 'CirrusSearch-Search', "_elasticsearch", array(
			'doWork' => function() use ( $searcher, $search, $description ) {
				try {
					$searcher->start( $description );
					return $searcher->success( $search->search() );
				} catch ( \Elastica\Exception\ExceptionInterface $e ) {
					return $searcher->failure( $e );
				}
			},
			'error' => function( $status ) {
				$status = $status->getErrorsArray();
				wfLogWarning( 'Pool error searching Elasticsearch:  ' . $status[ 0 ][ 0 ] );
				return Status::newFatal( 'cirrussearch-backend-error' );
			}
		) );
		$result = $work->execute();
		if ( $result->isOK() ) {
			$result->setResult( true, $this->resultsType->transformElasticsearchResult( $this->suggestPrefixes,
				$this->suggestSuffixes, $result->getValue(), $this->searchContainedSyntax ) );
		}

		return $result;
	}

	private function buildSearchTextQuery( $fields, $nearMatchFields, $queryString ) {
		// Build one query for the full text fields and one for the near match fields so that
		// the near match analyzer doesn't confuse the full text analyzers.
		$bool = new \Elastica\Query\Bool();
		$bool->setMinimumNumberShouldMatch( 1 );
		$bool->addShould( $this->buildSearchTextQueryForFields( $fields, $queryString ) );
		$bool->addShould( $this->buildSearchTextQueryForFields( $nearMatchFields, $queryString ) );
		return $bool;
	}

	private function buildSearchTextQueryForFields( $fields, $queryString ) {
		global $wgCirrusSearchPhraseSlop;
		$query = new \Elastica\Query\QueryString( $queryString );
		$query->setFields( $fields );
		$query->setAutoGeneratePhraseQueries( true );
		$query->setPhraseSlop( $wgCirrusSearchPhraseSlop );
		$query->setDefaultOperator( 'AND' );
		$query->setAllowLeadingWildcard( false );
		$query->setFuzzyPrefixLength( 2 );
		return $query;
	}

	/**
	 * Build suggest config for $field.
	 * @var $field string field to suggest against
	 * @return array of Elastica configuration
	 */
	private function buildSuggestConfig( $field ) {
		global $wgCirrusSearchPhraseSuggestMaxErrors;
		global $wgCirrusSearchPhraseSuggestConfidence;
		return array(
			'phrase' => array(
				'field' => $field,
				'size' => 1,
				'max_errors' => $wgCirrusSearchPhraseSuggestMaxErrors,
				'confidence' => $wgCirrusSearchPhraseSuggestConfidence,
				'direct_generator' => array(
					array(
						'field' => $field,
						'suggest_mode' => 'always', // Forces us to generate lots of phrases to try.
						// If a term appears in more then half the docs then don't try to correct it.  This really
						// shouldn't kick in much because we're not looking for misspellings.  We're looking for phrases
						// that can be might off.  Like "noble prize" ->  "nobel prize".  In any case, the default was
						// 0.01 which way too frequently decided not to correct some terms.
						'max_term_freq' => 0.5,
					),
				),
				'highlight' => array(
					'pre_tag' => self::SUGGESTION_HIGHLIGHT_PRE,
					'post_tag' => self::SUGGESTION_HIGHLIGHT_POST,
				),
			),
		);
	}

	public function switchSearchToExact( $term ) {
		$exact = join( ' OR ', $this->buildFullTextSearchFields( 1, ".plain:$term" ) );
		return "($exact)";
	}

	/**
	 * Build fields searched by full text search.
	 * @param float $weight weight to multiply by all fields
	 * @param string $fieldSuffix suffux to add to field names
	 * @return array(string) of fields to query
	 */
	public function buildFullTextSearchFields( $weight, $fieldSuffix ) {
		global $wgCirrusSearchWeights;
		$titleWeight = $weight * $wgCirrusSearchWeights[ 'title' ];
		$headingWeight = $weight * $wgCirrusSearchWeights[ 'heading' ];
		$fileTextWeight = $weight * $wgCirrusSearchWeights[ 'file_text' ];
		$fields = array();
		$fields[] = "title${fieldSuffix}^${titleWeight}";
		// Only title and redirect support near_match so skip it for everything else
		if ( $fieldSuffix !== '.near_match' ) {
			$fields[] = "heading${fieldSuffix}^${headingWeight}";
			$fields[] = "text${fieldSuffix}^${weight}";
			$fields[] = "file_text${fieldSuffix}^${fileTextWeight}";
		}
		if ( $this->showRedirects ) {
			$redirectWeight = $weight * $wgCirrusSearchWeights[ 'redirect' ];
			$fields[] = "redirect.title${fieldSuffix}^${redirectWeight}";
		}
		return $fields;
	}

	/**
	 * Pick the index type to search based on the list of namespaces to search.
	 * @return string|false either an index type or false to use all index types
	 */
	private function pickIndexTypeFromNamespaces() {
		if ( !$this->namespaces ) {
			return false; // False selects all index types
		}

		$indexTypes = array();
		foreach ( $this->namespaces as $namespace ) {
			$indexTypes[] =
				Connection::getIndexSuffixForNamespace( $namespace );
		}
		$indexTypes = array_unique( $indexTypes );
		return count( $indexTypes ) > 1 ? false : $indexTypes[0];
	}

	/**
	 * Retrieve the extra indexes for our searchable namespaces, if any
	 * exist. If they do exist, also add our wiki to our notFilters so
	 * we can filter out duplicates properly.
	 *
	 * @return array(string)
	 */
	private function getAndFilterExtraIndexes() {
		$extraIndexes = OtherIndexes::getExtraIndexesForNamespaces( $this->namespaces );
		if ( $extraIndexes ) {
			$this->notFilters[] = new \Elastica\Filter\Term(
				array( 'local_sites_with_dupe' => wfWikiId() ) );
		}
		return $extraIndexes;
	}

	/**
	 * Build the query that powers the prefix search and the prefix filter.
	 * @param string $search prefix search string
	 * @return \Elastica\AbstractQuery to perform the query
	 */
	private function buildPrefixQuery( $search ) {
		$match = new \Elastica\Query\MultiMatch();
		$match->setQuery( $search );
		$match->setFields( array( 'title.prefix', 'redirect.title.prefix' ) );
		return $match;
	}

	/**
	 * Make sure the the query string part is well formed by escaping some syntax that we don't
	 * want users to get direct access to and making sure quotes are balanced.
	 * These special characters _aren't_ escaped:
	 * *: Do a prefix or postfix search against the stemmed text which isn't strictly a good
	 * idea but this is so rarely used that adding extra code to flip prefix searches into
	 * real prefix searches isn't really worth it.  The same goes for postfix searches but
	 * doubly because we don't have a postfix index (backwards ngram.)
	 * ~: Do a fuzzy match against the stemmed text which isn't strictly a good idea but it
	 * gets the job done and fuzzy matches are a really rarely used feature to be creating an
	 * extra index for.
	 * ": Perform a phrase search for the quoted term.  If the "s aren't balanced we insert one
	 * at the end of the term to make sure elasticsearch doesn't barf at us.
	 * +/-/!/||/&&: Symbols meaning AND, NOT, NOT, OR, and AND respectively.  - was supported by
	 * LuceneSearch so we need to allow that one but there is no reason not to allow them all.
	 */
	public function fixupQueryStringPart( $string ) {
		$profiler = new ProfileSection( __METHOD__ );

		// Escape characters that can be escaped with \\
		$string = preg_replace( '/(
				\/|		(?# no regex searches allowed)
				\(|     (?# no user supplied groupings)
				\)|
				\{|     (?# no exclusive range queries)
				}|
				\[|     (?# no inclusive range queries either)
				]|
				\^|     (?# no user supplied boosts at this point, though I cant think why)
				:|		(?# no specifying your own fields)
				\\\
			)/x', '\\\$1', $string );

		// If the string doesn't have balanced quotes then add a quote on the end so Elasticsearch
		// can parse it.
		$inQuote = false;
		$inEscape = false;
		$len = strlen( $string );
		for ( $i = 0; $i < $len; $i++ ) {
			if ( $inEscape ) {
				$inEscape = false;
				continue;
			}
			switch ( $string[ $i ] ) {
			case '"':
				$inQuote = !$inQuote;
				break;
			case '\\':
				$inEscape = true;
			}
		}
		if ( $inQuote ) {
			$string = $string . '"';
		}

		return $string;
	}

	/**
	 * Make sure that all operators and lucene syntax is used correctly in the query string
	 * and store if this is a fuzzy query.
	 * If it isn't then the syntax escaped so it becomes part of the query text.
	 */
	public function fixupWholeQueryString( $string ) {
		$profiler = new ProfileSection( __METHOD__ );

		// Be careful when editing this method because the ordering of the replacements matters.

		// Escape ~ that don't follow a term or a quote
		$string = preg_replace_callback( '/(?<![\w"])~/',
			'CirrusSearch\Searcher::escapeBadSyntax', $string );

		// Escape ? and * that don't follow a term.  These are slow so we turned them off.
		$string = preg_replace_callback( '/(?<![\w])[?*]/',
			'CirrusSearch\Searcher::escapeBadSyntax', $string );

		// Reduce token ranges to bare tokens without the < or >
		$string = preg_replace( '/(?:<|>)([^\s])/', '$1', $string );

		// Turn bad fuzzy searches into searches that contain a ~ and set $this->fuzzyQuery for good ones.
		$searcher = $this;
		$fuzzyQuery = $this->fuzzyQuery;
		$string = preg_replace_callback( '/(?<leading>\w)~(?<trailing>\S*)/',
			function ( $matches ) use ( $searcher, &$fuzzyQuery ) {
				if ( preg_match( '/^(?:|0|(?:0?\.[0-9]+)|(?:1(?:\.0)?))$/', $matches[ 'trailing' ] ) ) {
					$fuzzyQuery = true;
					return $matches[ 0 ];
				} else {
					return $matches[ 'leading' ] . '\\~' .
						preg_replace( '/(?<!\\\\)~/', '\~', $matches[ 'trailing' ] );
				}
			}, $string );
		$this->fuzzyQuery = $fuzzyQuery;

		// Turn bad proximity searches into searches that contain a ~
		$string = preg_replace_callback( '/"~(?<trailing>\S*)/', function ( $matches ) {
			if ( preg_match( '/[0-9]+/', $matches[ 'trailing' ] ) ) {
				return $matches[ 0 ];
			} else {
				return '"\\~' . $matches[ 'trailing' ];
			}
		}, $string );

		// Escape +, -, and ! when not followed immediately by a term.
		$string = preg_replace_callback( '/[+\-!]+(?!\w)/',
			'CirrusSearch\Searcher::escapeBadSyntax', $string );

		// Escape || when not between terms
		$string = preg_replace_callback( '/^\s*\|\|/',
			'CirrusSearch\Searcher::escapeBadSyntax', $string );
		$string = preg_replace_callback( '/\|\|\s*$/',
			'CirrusSearch\Searcher::escapeBadSyntax', $string );

		// Lowercase AND and OR when not surrounded on both sides by a term.
		// Lowercase NOT when it doesn't have a term after it.
		$string = preg_replace_callback( '/^\s*(?:AND|OR)/',
			'CirrusSearch\Searcher::lowercaseMatched', $string );
		$string = preg_replace_callback( '/(?:AND|OR|NOT)\s*$/',
			'CirrusSearch\Searcher::lowercaseMatched', $string );

		return $string;
	}

	/**
	 * Does $string contain unescaped query string syntax?  Note that we're not
	 * careful about if the syntax is escaped - that still count.
	 * @param $string string query string to check
	 * @return boolean does it contain special syntax?
	 */
	private function queryStringContainsSyntax( $string ) {
		// Matches the upper case syntax and character syntax
		return preg_match( '/[?*+~"!|-]|AND|OR|NOT/', $string );
	}

	private static function escapeBadSyntax( $matches ) {
		return "\\" . implode( "\\", str_split( $matches[ 0 ] ) );
	}

	private static function lowercaseMatched( $matches ) {
		return strtolower( $matches[ 0 ] );
	}

	/**
	 * If there is any boosting to be done munge the the current query to get it right.
	 */
	private function installBoosts() {
		global $wgCirrusSearchFunctionRescoreWindowSize;

		// Quick note:  At the moment ".isEmpty()" is _much_ faster then ".empty".  Never
		// use ".empty".  See https://github.com/elasticsearch/elasticsearch/issues/5086

		if ( $this->sort !== 'relevance' ) {
			// Boosts are irrelevant if you aren't sorting by, well, relevance
			return;
		}

		$functionScore = new \Elastica\Query\FunctionScore();
		$useFunctionScore = false;

		// Customize score by boosting based on incoming links count
		if ( $this->boostLinks ) {
			$incomingLinks = "(doc['incoming_links'].isEmpty() ? 0 : doc['incoming_links'].value)";
			$scoreBoostMvel = "log10($incomingLinks + 2)";
			$functionScore->addScriptScoreFunction( new \Elastica\Script( $scoreBoostMvel ) );
			$useFunctionScore = true;
		}

		// Customize score by decaying a portion by time since last update
		if ( $this->preferRecentDecayPortion > 0 && $this->preferRecentHalfLife > 0 ) {
			// Convert half life for time in days to decay constant for time in milliseconds.
			$decayConstant = log( 2 ) / $this->preferRecentHalfLife / 86400000;
			// e^ct - 1 where t is last modified time - now which is negative
			$exponentialDecayMvel = "Math.expm1($decayConstant * (doc['timestamp'].value - time()))";
			// p(e^ct - 1)
			if ( $this->preferRecentDecayPortion !== 1.0 ) {
				$exponentialDecayMvel = "$exponentialDecayMvel * $this->preferRecentDecayPortion";
			}
			// p(e^ct - 1) + 1 which is easier to calculate than, but reduces to 1 - p + pe^ct
			// Which breaks the score into an unscaled portion (1 - p) and a scaled portion (p)
			$lastUpdateDecayMvel = "$exponentialDecayMvel + 1";
			$functionScore->addScriptScoreFunction( new \Elastica\Script( $lastUpdateDecayMvel ) );
			$useFunctionScore = true;
		}

		// Add boosts for pages that contain certain templates
		if ( $this->boostTemplates ) {
			foreach ( $this->boostTemplates as $name => $boost ) {
				$match = new \Elastica\Query\Match();
				$match->setFieldQuery( 'template', $name );
				$functionScore->addBoostFactorFunction( $boost, new \Elastica\Filter\Query( $match ) );
			}
			$useFunctionScore = true;
		}

		// Add boosts for namespaces
		$namespacesToBoost = $this->namespaces === null ? MWNamespace::getValidNamespaces() : $this->namespaces;
		if ( $namespacesToBoost ) {
			// Group common weights together and build a single filter per weight
			// to save on filters.
			$weightToNs = array();
			foreach ( $namespacesToBoost as $ns ) {
				$weight = $this->getBoostForNamespace( $ns );
				$weightToNs[ (string)$weight ][] = $ns;
			}
			if ( count( $weightToNs ) > 1 ) {
				unset( $weightToNs[ '1' ] );  // That'd be redundant.
				foreach ( $weightToNs as $weight => $namespaces ) {
					$filter = new \Elastica\Filter\Terms( 'namespace', $namespaces );
					$functionScore->addBoostFactorFunction( $weight, $filter );
					$useFunctionScore = true;
				}
			}
		}

		if ( !$useFunctionScore ) {
			// Nothing to do
			return;
		}

		// Since Elasticsearch doesn't support multiple rescores we have to pick a strategy here....
		// TODO just use multiple rescores when Elasticsearch supports it (1.x)

		// If there isn't already a rescore then we can just add the boosting as a multiply rescore
		if ( !$this->rescore ) {
			$this->rescore = array(
				'window_size' => $wgCirrusSearchFunctionRescoreWindowSize,
				'query' => array(
					'rescore_query' => $functionScore,
					'query_weight' => 1.0,
					'rescore_query_weight' => 1.0,
					'score_mode' => 'multiply',
				)
			);
			return;
		}

		// Since there is already a rescore we have to wrap _both_ the rescore and the query in our
		// function score query.  Nothing else really spits out the right numbers.  The problem
		// with this is that the function score isn't just in the rescore which means that it can
		// be slow if the main query finds lots of results.
		$functionScore->setQuery( $this->query );
		$this->query = new \Elastica\Query\Simple( $functionScore->toArray() );

		$functionScore->setQuery( $this->rescore[ 'query' ][ 'rescore_query' ] );
		$this->rescore[ 'query' ][ 'rescore_query' ] = $functionScore;
	}

	private static function getDefaultBoostTemplates() {
		static $defaultBoostTemplates = null;
		if ( $defaultBoostTemplates === null ) {
			$source = wfMessage( 'cirrussearch-boost-templates' )->inContentLanguage();
			if( $source->isDisabled() ) {
				$defaultBoostTemplates = array();
			} else {
				$lines = explode( "\n", $source->plain() );
				$lines = preg_replace( '/#.*$/', '', $lines ); // Remove comments
				$lines = array_map( 'trim', $lines );          // Remove extra spaces
				$lines = array_filter( $lines );               // Remove empty lines
				$defaultBoostTemplates = self::parseBoostTemplates(
					implode( ' ', $lines ) );                  // Now parse the templates
			}
		}
		return $defaultBoostTemplates;
	}

	/**
	 * Parse boosted templates.  Parse failures silently return no boosted templates.
	 * @param string $text text representation of boosted templates
	 * @return array of boosted templates.
	 */
	public static function parseBoostTemplates( $text ) {
		$boostTemplates = array();
		$templateMatches = array();
		if ( preg_match_all( '/([^|]+)\|([0-9]+)% ?/', $text, $templateMatches, PREG_SET_ORDER ) ) {
			foreach ( $templateMatches as $templateMatch ) {
				$boostTemplates[ $templateMatch[ 1 ] ] = floatval( $templateMatch[ 2 ] ) / 100;
			}
		}
		return $boostTemplates;
	}

	/**
	 * Get the weight of a namespace.  Public so it can be used in a callback.
	 * @param int $ns the namespace
	 * @return float the weight of the namespace
	 */
	private function getBoostForNamespace( $ns ) {
		global $wgCirrusSearchNamespaceWeights;
		global $wgCirrusSearchTalkNamespaceWeight;

		if ( isset( $wgCirrusSearchNamespaceWeights[ $ns ] ) ) {
			return $wgCirrusSearchNamespaceWeights[ $ns ];
		}
		if ( MWNamespace::isSubject( $ns ) ) {
			return 1;
		}
		$subjectNs = MWNamespace::getSubject( $ns );
		if ( isset( $wgCirrusSearchNamespaceWeights[ $subjectNs ] ) ) {
			return $wgCirrusSearchTalkNamespaceWeight * $wgCirrusSearchNamespaceWeights[ $subjectNs ];
		}
		return $wgCirrusSearchTalkNamespaceWeight;
	}

	private function checkTitleSearchRequestLength( $search ) {
		$requestLength = strlen( $search );
		if ( $requestLength > self::MAX_TITLE_SEARCH ) {
			throw new UsageException( 'Prefix search request was longer longer than the maximum allowed length.' .
				" ($requestLength > " . self::MAX_TITLE_SEARCH . ')', 'request_too_long', 400 );
		}
	}
}
