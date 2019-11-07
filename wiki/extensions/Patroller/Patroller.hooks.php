<?php
/**
 * Patroller
 * Patroller MediaWiki main hooks
 *
 * @author: Rob Church <robchur@gmail.com>, Kris Blair (Cblair91)
 * @copyright: 2006-2008 Rob Church, 2015 Kris Blair
 * @license: GPL General Public Licence 2.0
 * @package: Patroller
 * @link: https://mediawiki.org/wiki/Extension:Patroller
 */

class PatrollerHooks {
	/**
	 * Setup the database tables
	 *
	 * @access	public
	 * @param	class	The updater
	 * @return	void
	 */
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		$updater->addExtensionTable( 'patrollers', __DIR__ . '/sql/add-patrollers.sql' );
		return true;
	}
}
