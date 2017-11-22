<?php

/**
 * Hooks for MwEmbedSupport extension
 *
 * @file
 * @ingroup Extensions
 */

class MwEmbedSupportHooks {
	/**
	 * Update the page modules to include mwEmbed style
	 *
	 * TODO look into loading this on-demand instead of all pages.
	 * @param $out OutputPage
	 * @return bool
	 */
	static function updatePageModules( &$out ){
		$out->addModules( 'mw.MwEmbedSupport.style' );
		return true;
	}

	/**
	 * Add MwEmbedSupport modules to Startup:
	 * @param $modules
	 * @return bool
	 */
	static function addStartupModules( &$modules ){
		$modules[] = 'jquery.triggerQueueCallback';
		$modules[] = 'Spinner';
		$modules[] = 'jquery.loadingSpinner';
		$modules[] = 'jquery.mwEmbedUtil';
		$modules[] = 'mw.MwEmbedSupport';
		return true;
	}
}
