<?php
# Confirm it's being called from a valid entry point; skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install the User Edit Count extension, put the following line in LocalSettings.php:
require_once( \$IP . '/extensions/UsersEditCount/UsersEditCount.php' );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'UsersEditCount',
	'author' => 'Dave Humphrey',
	'url' => 'http://www.uesp.net/wiki/UESPWiki:Users_Edit_Count',
	'description' => 'Special page listing edit counts for all editors',
	'version' => '1.1',
);

$dir = dirname(__FILE__) . '/';

# Add to list of special pages
$wgSpecialPages['UsersEditCount'] = 'UsersEditCountPage';
$wgSpecialPageGroups['UsersEditCount'] = 'users';
$wgHooks['wgQueryPages'][] = 'onwgQueryPages';

# Tell Mediawiki where to find the file containing the extension's class
$wgAutoloadClasses['UsersEditCountPage'] = $dir . '/UsersEditCount_body.php';

# Load messages
$wgExtensionMessagesFiles['userseditcount'] = $dir . 'UsersEditCount.i18n.php';
$wgExtensionMessagesFiles['userseditcountAlias'] = $dir . 'UsersEditCount.alias.php';

function onwgQueryPages( &$wgQueryPages ) {
	$wgQueryPages[] = array('UsersEditCountPage', 'Userseditcount');
	
	return true;
}