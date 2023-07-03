<?php
/*
 * Revamp search function to add extension-specific search page and alter multiple aspects of how search works
 */
global $IP;
require_once "$IP/includes/specials/SpecialSearch.php";
require_once "SiteSearchFormWidget.php";


use MediaWiki\MediaWikiServices;
use MediaWiki\Widget\Search\BasicSearchResultSetWidget;
use MediaWiki\Widget\Search\FullSearchResultWidget;
use MediaWiki\Widget\Search\InterwikiSearchResultWidget;
use MediaWiki\Widget\Search\InterwikiSearchResultSetWidget;
use MediaWiki\Widget\Search\SimpleSearchResultWidget;
use MediaWiki\Widget\Search\SimpleSearchResultSetWidget;


class SiteSpecialSearch extends SpecialSearch
{

	protected $searchTalkPages;

	public function __construct() 
	{
		parent::__construct( 'Search' );
		$this->searchTalkPages = false;
	}

	public function load()
	{
		parent::load();
		
		$request = $this->getRequest();
		$default = $request->getBool( 'profile') ? 0 : 1;
		$this->searchTalkPages = $request->getBool('talkpages', $default ) ? 1 : 0;
		
		if ( $this->searchTalkPages )
		{
			$this->enableTalkPageSearch();
		}
		else
		{
			$this->setExtraParam ( 'talkpages', '0' );
		}
	}
	
	protected function enableTalkPageSearch ()
	{
		$orignamespaces = $this->namespaces;
		//$talknamespaces = array();
		
		foreach ( $orignamespaces as $namespace => $name )
		{
			$this->namespaces[] = $name | 1;
		}
		
		//error_log("SearchLog: C1=" . count($this->namespaces) . "  C2=" . count($talknamespaces));
		//$this->namespaces = array_merge( $this->namespaces, $talknamespaces );
		//$this->namespaces = $this->name
		//error_log("SearchLog: C3=". count($this->namespaces));
	}
	
	
	public static function StringEndsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return $length > 0 ? (strcasecmp(substr($haystack, -$length), $needle) == 0) : true;
	}
	
	
	protected function showCreateLink( $title, $num, $titleMatches, $textMatches ) 
	{
		if (($titleMatches && $titleMatches->numRows() > 0) || $title == null)
		{
			$this->getOutput()->addHTML( '<p></p>' );
			return;
		}
		
			// Don't display the "Create the page..." if there are any namespace matches with the exact title
		if ($textMatches)
		{
			$results = $textMatches->extractResults();
			$textMatches->rewind();
			$searchText = ':' . $title->getPrefixedText();
			
			foreach ($results as $result)
			{
				$thisTitle = $result->getTitle();
				$text = $thisTitle->getPrefixedText();
				
				if (self::StringEndsWith($text, $searchText)) 
				{
					$this->getOutput()->addHTML( '<p></p>' );
					return;
				}
			}
		}
		
		return parent::showCreateLink( $title, $num, $titleMatches, $textMatches );
	}
	
	
	/**
	 * @param string $term
	 */
	public function showResults( $term ) {
		global $wgContLang, $wgVersion;
		
			// Log notice to update code in future versions
		if (version_compare( $wgVersion, '1.29', '>' ))
		{
			wfWarn("Update SiteSpecialSearch::showResults() with v1.30 code!", 1, E_USER_WARNING);
		}
		
		if ( $this->searchEngineType !== null ) {
			$this->setExtraParam( 'srbackend', $this->searchEngineType );
		}

		$out = $this->getOutput();
		$formWidget = new SiteSearchFormWidget(
			$this,
			$this->searchConfig,
			$this->getSearchProfiles()
		);
		$filePrefix = $wgContLang->getFormattedNsText( NS_FILE ) . ':';
		if ( trim( $term ) === '' || $filePrefix === trim( $term ) ) {
			// Empty query -- straight view of search form
			if ( !Hooks::run( 'SpecialSearchResultsPrepend', [ $this, $out, $term ] ) ) {
				# Hook requested termination
				return;
			}
			$out->enableOOUI();
			// The form also contains the 'Showing results 0 - 20 of 1234' so we can
			// only do the form render here for the empty $term case. Rendering
			// the form when a search is provided is repeated below.
			$out->addHTML( $formWidget->render(
				$this->profile, $term, 0, 0, $this->offset, $this->isPowerSearch()
			) );
			return;
		}

		$search = $this->getSearchEngine();
		$search->setFeatureData( 'rewrite', $this->runSuggestion );
		$search->setLimitOffset( $this->limit, $this->offset );
		$search->setNamespaces( $this->namespaces );
		$search->prefix = $this->mPrefix;
		$term = $search->transformSearchTerm( $term );

		Hooks::run( 'SpecialSearchSetupEngine', [ $this, $this->profile, $search ] );
		if ( !Hooks::run( 'SpecialSearchResultsPrepend', [ $this, $out, $term ] ) ) {
			# Hook requested termination
			return;
		}

		$title = Title::newFromText( $term );
		$showSuggestion = $title === null || !$title->isKnown();
		$search->setShowSuggestion( $showSuggestion );

		// fetch search results
		$rewritten = $search->replacePrefixes( $term );

		$titleMatches = $search->searchTitle( $rewritten );
		$textMatches = $search->searchText( $rewritten );

		$textStatus = null;
		if ( $textMatches instanceof Status ) {
			$textStatus = $textMatches;
			$textMatches = $textStatus->getValue();
		}

		// Get number of results
		$titleMatchesNum = $textMatchesNum = $numTitleMatches = $numTextMatches = 0;
		if ( $titleMatches ) {
			$titleMatchesNum = $titleMatches->numRows();
			$numTitleMatches = $titleMatches->getTotalHits();
		}
		if ( $textMatches ) {
			$textMatchesNum = $textMatches->numRows();
			$numTextMatches = $textMatches->getTotalHits();
			if ( $textMatchesNum > 0 ) {
				$search->augmentSearchResults( $textMatches );
			}
		}
		$num = $titleMatchesNum + $textMatchesNum;
		$totalRes = $numTitleMatches + $numTextMatches;

		// start rendering the page
		$out->enableOOUI();
		$out->addHTML( $formWidget->render(
			$this->profile, $term, $num, $totalRes, $this->offset, $this->isPowerSearch()
		) );

		// did you mean... suggestions
		if ( $textMatches ) {
			$dymWidget = new MediaWiki\Widget\Search\DidYouMeanWidget( $this );
			$out->addHTML( $dymWidget->render( $term, $textMatches ) );
		}

		$out->addHTML( "<div class='searchresults'>" );

		$hasErrors = $textStatus && $textStatus->getErrors();
		$hasOtherResults = $textMatches &&
			$textMatches->hasInterwikiResults( SearchResultSet::INLINE_RESULTS );

		if ( $hasErrors ) {
			list( $error, $warning ) = $textStatus->splitByErrorType();
			if ( $error->getErrors() ) {
				$out->addHTML( Html::rawElement(
					'div',
					[ 'class' => 'errorbox' ],
					$error->getHTML( 'search-error' )
				) );
			}
			if ( $warning->getErrors() ) {
				$out->addHTML( Html::rawElement(
					'div',
					[ 'class' => 'warningbox' ],
					$warning->getHTML( 'search-warning' )
				) );
			}
		}

		// Show the create link ahead
		$this->showCreateLink( $title, $num, $titleMatches, $textMatches );

		Hooks::run( 'SpecialSearchResults', [ $term, &$titleMatches, &$textMatches ] );

		// If we have no results and have not already displayed an error message
		if ( $num === 0 && !$hasErrors ) {
			$out->wrapWikiMsg( "<p class=\"mw-search-nonefound\">\n$1</p>", [
				$hasOtherResults ? 'search-nonefound-thiswiki' : 'search-nonefound',
				wfEscapeWikiText( $term )
			] );
		}

		// Although $num might be 0 there can still be secondary or inline
		// results to display.
		$linkRenderer = $this->getLinkRenderer();
		$mainResultWidget = new FullSearchResultWidget( $this, $linkRenderer );

		if ( $search->getFeatureData( 'enable-new-crossproject-page' ) ) {

			$sidebarResultWidget = new InterwikiSearchResultWidget( $this, $linkRenderer );
			$sidebarResultsWidget = new InterwikiSearchResultSetWidget(
				$this,
				$sidebarResultWidget,
				$linkRenderer,
				MediaWikiServices::getInstance()->getInterwikiLookup()
			);
		} else {
			$sidebarResultWidget = new SimpleSearchResultWidget( $this, $linkRenderer );
			$sidebarResultsWidget = new SimpleSearchResultSetWidget(
				$this,
				$sidebarResultWidget,
				$linkRenderer,
				MediaWikiServices::getInstance()->getInterwikiLookup()
			);
		}

		$widget = new BasicSearchResultSetWidget( $this, $mainResultWidget, $sidebarResultsWidget );

		$out->addHTML( $widget->render(
			$term, $this->offset, $titleMatches, $textMatches
		) );

		if ( $titleMatches ) {
			$titleMatches->free();
		}

		if ( $textMatches ) {
			$textMatches->free();
		}

		$out->addHTML( '<div class="mw-search-visualclear"></div>' );

		// prev/next links
		if ( $totalRes > $this->limit || $this->offset ) {
			$prevnext = $this->getLanguage()->viewPrevNext(
				$this->getPageTitle(),
				$this->offset,
				$this->limit,
				$this->powerSearchOptions() + [ 'search' => $term ],
				$this->limit + $this->offset >= $totalRes
			);
			$out->addHTML( "<p class='mw-search-pager-bottom'>{$prevnext}</p>\n" );
		}

		// Close <div class='searchresults'>
		$out->addHTML( "</div>" );

		Hooks::run( 'SpecialSearchResultsAppend', [ $this, $out, $term ] );
	}
	

};


