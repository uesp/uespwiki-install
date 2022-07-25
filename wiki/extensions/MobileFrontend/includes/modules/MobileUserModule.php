<?php
/**
 * MobileUserModule.php
 */

/**
 * Alternative of ResourceLoaderUserModule for mobile web.
 * Differs from the user module by not loading common.js,
 * which predate Minerva and may be incompatible.
 */
class MobileUserModule extends ResourceLoaderUserModule {
	// Should not be enabled on desktop which loads 'user' instead
	protected $targets = [ 'mobile' ];

	/**
	 * Gets list of pages used by this module.
	 * @param ResourceLoaderContext $context
	 * @return array
	 */
	protected function getPages( ResourceLoaderContext $context ) {
		$pages = parent::getPages( $context );
		// Remove $userpage/common.js
		foreach ( array_keys( $pages ) as $key ) {
			if ( preg_match( '/common\.js/', $key ) ) {
				unset( $pages[$key] );
			}
		}
		return $pages;
	}
}
