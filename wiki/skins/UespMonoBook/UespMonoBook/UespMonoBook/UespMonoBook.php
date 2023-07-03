<?php
/**
 * UespMonoBook skin
 *
 * @file
 * @ingroup Skins
 * @author Dave Humphrey (http://www.uesp.net/wiki/User:Daveh)
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'UespMonoBook',
	'version' => '1.0',
	'date' => '20140203', 
	'url' => "http://www.uesp.net/UESPWiki:UespMonoBook",
	'author' => '[http://www.uesp.net/wiki/User:Daveh Dave Humphrey]',
	'descriptionmsg' => 'uespmonobook-desc',
);

$wgValidSkinNames['uespmonobook'] = 'UespMonoBook';
$wgAutoloadClasses['SkinUespMonoBook'] = dirname(__FILE__).'/UespMonoBook.skin.php';
//$wgExtensionMessagesFiles['MySkin'] = dirname(__FILE__).'/MySkin.i18n.php';

error_log("UespMonoBook Skin");
 
$wgResourceModules['skins.uespmonobook'] = array(
	'styles' => array(
		'UespMonoBook/main.css' => array( 'media' => 'screen' ),
	),
	'remoteBasePath' => &$GLOBALS['wgStylePath'],
	'localBasePath' => &$GLOBALS['wgStyleDirectory'],
);
