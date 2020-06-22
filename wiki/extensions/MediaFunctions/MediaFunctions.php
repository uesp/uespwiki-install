<?php
/**
 * The Media Functions extension allows to retrieve various information about media files
 *
 * @link https://www.mediawiki.org/wiki/Extension:MediaFunctions Documentation
 * @link https://www.mediawiki.org/wiki/Extension_talk:MediaFunctions Support
 * @link https://git.wikimedia.org/summary/mediawiki%2Fextensions%2FMediaFunctions.git Source Code
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @author Rob Church (Robchurch) <robchur@gmail.com>
 *
 * @license http://www.opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

// Ensure that the script cannot be executed outside of MediaWiki
if ( !defined( 'MEDIAWIKI' ) ) {
    die( 'This is an extension to MediaWiki and cannot be run standalone.' );
}

// Display extension's information on "Special:Version"
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'MediaFunctions',
	'version' => '1.3.2',
	'author' => array(
		'Rob Church',
		'...'
	),
	'url' => 'https://www.mediawiki.org/wiki/Extension:MediaFunctions',
	'descriptionmsg' => 'mediafunctions-desc',
	'license-name' => 'BSD-2-Clause'
);

// Register extension messages
$wgMessagesDirs['MediaFunctions'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['MediaFunctions'] = __DIR__ . '/MediaFunctions.i18n.php';
$wgExtensionMessagesFiles['MediaFunctionsMagic'] = __DIR__ . '/MediaFunctions.i18n.magic.php';

// Load classes
$wgAutoloadClasses['MediaFunctions'] = __DIR__ . '/MediaFunctions.class.php';

// Register hooks
$wgHooks['ParserFirstCallInit'][] = 'efMediaFunctionsSetup';

// Register function callbacks and add error messages to the message cache
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
