<?php
/**
 * MinervaTemplate.php
 */

/**
 * Extended Template class of BaseTemplate for mobile devices
 */
class MinervaTemplate extends BaseTemplate {
	/** @var boolean Specify whether the page is a special page */
	protected $isSpecialPage;

	/** @var boolean Whether or not the user is on the Special:MobileMenu page */
	protected $isSpecialMobileMenuPage;

	/** @var boolean Specify whether the page is main page */
	protected $isMainPage;

	/**
	 * Gets the header content for the top chrome.
	 * @param array $data Data used to build the page
	 * @return string
	 */
	protected function getChromeHeaderContentHtml( $data ) {
		return $this->getSearchForm( $data );
	}

	/**
	 * Generates the HTML required to render the search form.
	 *
	 * @param array $data The data used to render the page
	 * @return string
	 */
	protected function getSearchForm( $data ) {
		return Html::openElement( 'form',
				array(
					'action' => $data['wgScript'],
					'class' => 'search-box',
				)
			) .
			$this->makeSearchInput( $this->getSearchAttributes() ) .
			$this->makeSearchButton(
				'fulltext',
				array(
					'class' => MobileUI::buttonClass( 'progressive', 'fulltext-search no-js-only' ),
				)
			) .
			Html::closeElement( 'form' );
	}

	/**
	 * Start render the page in template
	 */
	public function execute() {
		$title = $this->getSkin()->getTitle();
		$this->isSpecialPage = $title->isSpecialPage();
		$this->isSpecialMobileMenuPage = $this->isSpecialPage &&
			$title->equals( SpecialPage::getTitleFor( 'MobileMenu' ) );
		$this->isMainPage = $title->isMainPage();
		Hooks::run( 'MinervaPreRender', array( $this ) );
		$this->render( $this->data );
	}

	/**
	 * Returns available page actions
	 * @return array
	 */
	public function getPageActions() {
		return $this->data['page_actions'];
	}

	/**
	 * Returns footer links
	 * @param string $option
	 * @return array
	 */
	public function getFooterLinks( $option = null ) {
		return $this->data['footerlinks'];
	}

	/**
	 * Get attributes to create search input
	 * @return array Array with attributes for search bar
	 */
	protected function getSearchAttributes() {
		$searchBox = array(
			'id' => 'searchInput',
			'class' => 'search',
			'autocomplete' => 'off',
			// The placeholder gets fed to HTML::element later which escapes all
			// attribute values, so need to escape the string here.
			'placeholder' => '',
		);
		return $searchBox;
	}

	/**
	 * Render Footer elements
	 * @param array $data Data used to build the footer
	 */
	protected function renderFooter( $data ) {
		?>
		<div id="footer">
			<?php
				foreach ( $this->getFooterLinks() as $category => $links ) {
			?>
				<ul class="footer-<?php echo $category; ?>">
					<?php
						foreach ( $links as $link ) {
							if ( isset( $this->data[$link] ) && $this->data[$link] !== '' ) {
								echo Html::openElement( 'li', array( 'id' => "footer-{$category}-{$link}" ) );
								$this->html( $link );
								echo Html::closeElement( 'li' );
							}
						}
					?>
				</ul>
			<?php
				}
			?>
		</div>
		<?php
	}

	/**
	 * Render available page actions
	 * @param array $data Data used to build page actions
	 */
	protected function renderPageActions( $data ) {
		$actions = $this->getPageActions();
		if ( $actions ) {
			?><ul id="page-actions" class="hlist"><?php
			foreach ( $actions as $key => $val ) {
				echo $this->makeListItem( $key, $val );
			}
			?></ul><?php
		}
	}

	/**
	 * Returns the 'Last edited' message, e.g. 'Last edited on...'
	 * @param array $data Data used to build the page
	 * @return string
	 */
	protected function getHistoryLinkHtml( $data ) {
		$action = Action::getActionName( RequestContext::getMain() );
		if ( isset( $data['historyLink'] ) && $action === 'view' ) {
			$historyLink = $data['historyLink'];
			$args = array(
				'isMainPage' => $this->getSkin()->getTitle()->isMainPage(),
				'link' => $historyLink['href'],
				'text' => $historyLink['text'],
				'username' => $historyLink['data-user-name'],
				'userGender' => $historyLink['data-user-gender'],
				'timestamp' => $historyLink['data-timestamp']
			);
			$templateParser = new TemplateParser( __DIR__ );
			return $templateParser->processTemplate( 'history', $args );
		} else {
			return '';
		}
	}

	/**
	 * Get page secondary actions
	 */
	protected function getSecondaryActions() {
		$result = $this->data['secondary_actions'];
		$hasLanguages = $this->data['content_navigation']['variants'] ||
			$this->data['language_urls'];

		// If languages are available, add a languages link
		if ( $hasLanguages ) {
			$languageUrl = SpecialPage::getTitleFor(
				'MobileLanguages',
				$this->getSkin()->getTitle()
			)->getLocalURL();

			$result['language'] = array(
				'attributes' => array(
					'class' => 'languageSelector',
					'href' => $languageUrl,
				),
				'label' => wfMessage( 'mobile-frontend-language-article-heading' )->text()
			);
		}

		return $result;
	}

	/**
	 * Get HTML representing secondary page actions like language selector
	 * @return string
	 */
	protected function getSecondaryActionsHtml() {
		$baseClass = MobileUI::buttonClass( '', 'button' );
		$html = Html::openElement( 'div', array(
			'class' => 'post-content',
			'id' => 'page-secondary-actions'
		) );

		foreach ( $this->getSecondaryActions() as $el ) {
			if ( isset( $el['attributes']['class'] ) ) {
				$el['attributes']['class'] .= ' ' . $baseClass;
			} else {
				$el['attributes']['class'] = $baseClass;
			}
			$html .= Html::element( 'a', $el['attributes'], $el['label'] );
		}

		return $html . Html::closeElement( 'div' );
	}

	/**
	 * Renders the content of a page
	 * @param array $data Data used to build the page
	 */
	protected function renderContent( $data ) {
		if ( !$data[ 'unstyledContent' ] ) {
			// Add a mw-content-ltr/rtl class to be able to style based on text direction
			$langClass = 'mw-content-' . $data['pageDir'];
			echo Html::openElement( 'div', array(
				'id' => 'bodyContent',
				'class' => 'content ' . $langClass,
				'lang' => $data['pageLang'],
				'dir' => $data['pageDir'],
			) );
			echo $data[ 'bodytext' ];
			if ( isset( $data['subject-page'] ) ) {
				echo $data['subject-page'];
			}
			?>
			</div>
			<?php
		} else {
			echo $data[ 'bodytext' ];
		}
	}

	/**
	 * Renders pre-content (e.g. heading)
	 * @param array $data Data used to build the page
	 */
	protected function renderPreContent( $data ) {
		$internalBanner = $data[ 'internalBanner' ];
		$preBodyText = isset( $data['prebodyhtml'] ) ? $data['prebodyhtml'] : '';
		$headingHtml = isset( $data['headinghtml'] ) ? $data['headinghtml'] : '';

		if ( $internalBanner || $preBodyText || isset( $data['page_actions'] ) ) {
			echo $preBodyText;
		?>
		<div class="pre-content heading-holder">
			<?php
				if ( !$this->isSpecialPage ){
					$this->renderPageActions( $data );
				}
				echo $headingHtml;
				echo $this->html( 'subtitle' );
				// FIXME: Temporary solution until we have design
				if ( isset( $data['_old_revision_warning'] ) ) {
					echo $data['_old_revision_warning'];
				}

				echo $internalBanner;
			?>
		</div>
		<?php
		}
	}

	/**
	 * Gets HTML that needs to come after the main content and before the secondary actions.
	 *
	 * @param array $data The data used to build the page
	 * @return string
	 */
	protected function getPostContentHtml( $data ) {
		return $this->renderBrowseTags( $data ) .
			$this->getSecondaryActionsHtml() .
			$this->getHistoryLinkHtml( $data );
	}

	/**
	 * Render wrapper for loading content
	 * @param array $data Data used to build the page
	 */
	protected function renderContentWrapper( $data ) {
		// Construct an inline script which emits header-loaded
		$headerLoaded = "mw.loader.using( 'mobile.head', function () {";
		$headerLoaded .= "mw.mobileFrontend.emit( 'header-loaded' );";
		$headerLoaded .= "} );";
		echo ResourceLoader::makeInlineScript( $headerLoaded );

		$this->renderPreContent( $data );
		$this->renderContent( $data );
		echo $this->getPostContentHtml( $data );
	}

	/**
	 * Gets the main menu only on Special:MobileMenu.
	 * On other pages the menu is rendered via JS.
	 * @param array [$data] Data used to build the page
	 * @return string
	 */
	protected function getMainMenuHtml( $data ) {
		if ( $this->isSpecialMobileMenuPage ) {
			$templateParser = new TemplateParser(
				__DIR__ . '/../../resources/mobile.mainMenu/' );

			return $templateParser->processTemplate( 'menu', $data['menu_data'] );
		} else {
			return '';
		}
	}

	/**
	 * Get HTML for header elements
	 * @param array $data Data used to build the header
	 * @return string
	 */
	protected function getHeaderHtml( $data ) {
		// Note these should be wrapped in divs
		// see https://phabricator.wikimedia.org/T98498 for details
		return '<div>' . $data['menuButton'] . '</div>'
			. $this->getChromeHeaderContentHtml( $data )
			. '<div>' . $data['secondaryButton'] . '</div>';
	}

	/**
	 * Render the entire page
	 * @param array $data Data used to build the page
	 * @todo replace with template engines
	 */
	protected function render( $data ) {
		$templateParser = new TemplateParser( __DIR__ );

		// begin rendering
		echo $data[ 'headelement' ];
		?>
		<div id="mw-mf-viewport">
			<nav id="mw-mf-page-left" class="navigation-drawer view-border-box">
				<?php echo $this->getMainMenuHtml( $data ); ?>
			</nav>
			<div id="mw-mf-page-center">
				<div class="banner-container">
				<?php
					echo $templateParser->processTemplate( 'banners', $data );
				?>
				</div>
				<div class="header">
					<?php
						echo $this->getHeaderHtml( $data );
					?>
				</div>
				<div id="content">
				<?php
					$this->renderContentWrapper( $data );
				?>
				</div>
				<?php
					$this->renderFooter( $data );
				?>
			</div>
		</div>
		<?php $this->printTrail(); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Renders the tags assigned to the page as part of the Browse experiment.
	 *
	 * @param array $data The data used to build the page
	 * @return string The HTML representing the tags section
	 */
	protected function renderBrowseTags( $data ) {
		if ( !isset( $data['browse_tags'] ) || !$data['browse_tags'] ) {
			return '';
		}

		$browseTags = $this->getSkin()->getMFConfig()->get( 'MFBrowseTags' );
		$baseLink = SpecialPage::getTitleFor( 'TopicTag' )->getLinkURL();

		// TODO: Create tag entity and view.
		$tags = array_map( function ( $rawTag ) use ( $browseTags, $baseLink ) {
			return array(
				'msg' => $rawTag,
				// replace spaces with underscores in the tag name
				'link' => $baseLink . '/' . str_replace( ' ', '_', $rawTag )
			);

		}, $data['browse_tags'] );

		// FIXME: This should be in MinervaTemplate#getTemplateParser.
		$templateParser = new TemplateParser( __DIR__ . '/../../resources' );

		return $templateParser->processTemplate( 'mobile.browse/tags', array(
			'headerMsg' => wfMessage( 'mobile-frontend-browse-tags-header' )->text(),
			'tags' => $tags,
		) );
	}
}
