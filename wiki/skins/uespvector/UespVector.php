<?php
/**
 * UespVector skin
 *
 * @file
 * @ingroup Skins
 * @author Robert Morley (http://www.uesp.net/wiki/User:RobinHood70)
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'UespVector',
	'version' => '1.0',
	'date' => '20170413', 
	'author' => '[http://www.uesp.net/wiki/User:RobinHood70 RobinHood70]',
	'descriptionmsg' => 'uespvector-desc',
);

$dir = dirname( __FILE__ );

$wgValidSkinNames['uespvector'] = 'UespVector';
$wgAutoloadClasses['SkinUespVector'] = "$dir/UespVector.skin.php";
$wgMessagesDirs['SkinUespVector'] = "$dir/i18n";
$wgExtensionMessagesFiles['SkinUespVector'] ="$dir/UespVector.i18n.php";

$wgResourceModules['skins.uespvector.styles'] = array(
		'styles' => array(
			'screen.less' => array( 'media' => 'screen' ),
			'screen-hd.less' => array( 'media' => 'screen and (min-width: 982px)' ),
		),
		'skinStyles' => array(
			'uespvector' => array(
				'skinStyles/jquery.ui.core.css',
				'skinStyles/jquery.ui.theme.css',
				'skinStyles/jquery.ui.resizable.css',
				'skinStyles/jquery.ui.selectable.css',
				'skinStyles/jquery.ui.accordion.css',
				'skinStyles/jquery.ui.autocomplete.css',
				'skinStyles/jquery.ui.button.css',
				'skinStyles/jquery.ui.datepicker.css',
				'skinStyles/jquery.ui.dialog.css',
				'skinStyles/jquery.ui.progressbar.css',
				'skinStyles/jquery.ui.slider.css',
				'skinStyles/jquery.ui.tabs.css',
				'special.less',
				'special.preferences.less',
				'skinStyles/vector.less',
				'skinStyles/buttons.less',
			),
		),
		'remoteExtPath' => 'uespvector',
		'localBasePath' => $dir,
);

$wgResourceModules['skins.uespvector.js'] = array(
		'scripts' => array(
			'collapsibleTabs.js',
			'vector.js',
		),
		'position' => 'top',
		'dependencies' => 'jquery.throttle-debounce',
		'remoteExtPath' => 'uespvector',
		'localBasePath' => $dir,
);

$wgResourceModules['skins.uespvector.collapsibleNav'] = array(
		'scripts' => array(
			'collapsibleNav.js',
		),
		'messages' => array(
			'vector-collapsiblenav-more',
		),
		'dependencies' => array(
			'jquery.client',
			'jquery.cookie',
			'jquery.tabIndex',
		),
		'remoteExtPath' => 'uespvector',
		'localBasePath' => $dir,
		'position' => 'bottom',
);