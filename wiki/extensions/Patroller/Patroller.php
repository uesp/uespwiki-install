<?php
/**
 * Patroller
 * Patroller MediaWiki main loader
 *
 * @author: Rob Church <robchur@gmail.com>, Kris Blair (Cblair91)
 * @copyright: 2006-2008 Rob Church, 2015 Kris Blair
 * @license: GPL General Public Licence 2.0
 * @package: Patroller
 * @link: https://mediawiki.org/wiki/Extension:Patroller
 */

if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Patroller' );
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Patroller'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['PatrollerAlias'] = __DIR__ . '/Patroller.alias.php';
	/* wfWarn(
		'Deprecated PHP entry point used for Patroller extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	); */
	return true;
}

$wgExtensionCredits['specialpage'][] = array(
	'path'				=> __FILE__,
	'name'				=> 'Patroller',
	'descriptionmsg'	=> 'patrol-desc',
	'author'			=> 'Rob Church, Kris Blair (Cblair91)',
	'version'			=> '2.0',
	'url'				=> 'https://www.mediawiki.org/wiki/Extension:Patroller',
	'licence-name'		=> 'GPL-2.0'
);

$dir = dirname( __FILE__ ) . '/';

# Register hooks
$wgAutoloadClasses['PatrollerHooks']			= $dir . 'Patroller.hooks.php';
$wgAutoloadClasses['SpecialPatroller']					= $dir . 'SpecialPatroller.php';

$wgHooks['LoadExtensionSchemaUpdates'][]		= 'PatrollerHooks::onLoadExtensionSchemaUpdates';
$wgSpecialPages['Patrol']						= 'SpecialPatroller';

# Register messages
$wgMessagesDir['Patroller']						= $dir . 'i18n';
$wgExtensionMessagesFiles['Patroller']			= $dir . 'Patroller.i18n.php';
$wgExtensionMessagesFiles['PatrollerAlias']		= $dir . 'Patroller.alias.php';

# Register rights
$wgAvailableRights[]							= 'patroller';
$wgGroupPermissions['sysop']['patroller']		= true;
$wgGroupPermissions['patroller']['patroller']	= true;
