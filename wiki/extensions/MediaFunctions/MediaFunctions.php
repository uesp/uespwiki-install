<?php
if ( !defined( 'MEDIAWIKI' ) ) die();
/**
 * Parser functions for MediaWiki providing information
 * about various media files
 *
 * @file
 * @ingroup Extensions
 * @author Rob Church <robchur@gmail.com>
 */

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'MediaFunctions',
	'version' => '1.3.0',
	'author' => 'Rob Church',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MediaFunctions',
	'descriptionmsg' => 'mediafunctions-desc',
);

$dir = dirname( __FILE__ ) . '/';
$wgMessagesDirs['MediaFunctions'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['MediaFunctions'] = $dir . 'MediaFunctions.i18n.php';
$wgExtensionMessagesFiles['MediaFunctionsMagic'] = $dir . 'MediaFunctions.i18n.magic.php';
$wgAutoloadClasses['MediaFunctions'] = $dir . 'MediaFunctions.class.php';
$wgHooks['ParserFirstCallInit'][] = 'efMediaFunctionsSetup';

/**
 * Register function callbacks and add error messages to
 * the message cache
 */
function efMediaFunctionsSetup( &$parser ) {
	$parser->setFunctionHook( 'mediamime', array( 'MediaFunctions', 'mediamime' ) );
	$parser->setFunctionHook( 'mediasize', array( 'MediaFunctions', 'mediasize' ) );
	$parser->setFunctionHook( 'mediaheight', array( 'MediaFunctions', 'mediaheight' ) );
	$parser->setFunctionHook( 'mediawidth', array( 'MediaFunctions', 'mediawidth' ) );
	$parser->setFunctionHook( 'mediadimensions', array( 'MediaFunctions', 'mediadimensions' ) );
	$parser->setFunctionHook( 'mediaexif', array( 'MediaFunctions', 'mediaexif' ) );
	$parser->setFunctionHook( 'mediapages', array( 'MediaFunctions', 'mediapages' ) );
	return true;
}
