<?php
/**
 * SpecialNearby.php
 */

/**
 * Provide the Special page "Nearby" with location based articles
 */
class SpecialNearby extends MobileSpecialPage {
	/** @var boolean $hasDesktopVersion Does this special page has a desktop version? */
	protected $hasDesktopVersion = true;

	/**
	 * Construct function
	 */
	public function __construct() {
		parent::__construct( 'Nearby' );
		$this->listed = true;
	}

	/**
	 * Render Special Page Nearby
	 * @param string $par Parameter submitted as subpage
	 */
	public function executeWhenAvailable( $par = '' ) {
		$this->setHeaders();

		$output = $this->getOutput();

		// set config
		$output->addJsConfigVars( 'wgMFNearbyRange', $this->getMFConfig()->get( 'MFNearbyRange' ) );
		$output->setPageTitle( wfMessage( 'mobile-frontend-nearby-title' )->escaped() );

		$html =
			Html::openElement( 'div',
				array(
					'class' => 'content-unstyled',
					'id' => 'mw-mf-nearby',
				)
			) .
			MobileUI::contentElement(
				MobileUI::errorBox(
					Html::element( 'h2', array(),
						wfMessage( 'mobile-frontend-nearby-requirements' ) ) .
					Html::element( 'p', array(),
						wfMessage( 'mobile-frontend-nearby-requirements-guidance' ) )
				),
				'noscript'
			) .
			Html::closeElement( 'div' ); // #mw-mf-nearby

		$output->addHTML( $html );
	}

	protected function getGroupName() {
		return 'pages';
	}
}
