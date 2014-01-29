<?php

if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install the UncategorizedUespPages extension, put the following line in LocalSettings.php:
require_once( \$IP . '/extensions/UncategorizedUESPPages/UncategorizedUESPPages.php' );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path'		 => __FILE__,
	'name' 	 	 => 'UncategorizedUespPages',
	'author' 	 => 'Dave Humphrey',
	'url' 		 => 'http://www.uesp.net/wiki/User:Daveh',
	'description' 	 => 'TODO',
	'descriptionmsg' => 'uncategorizeduesppages_desc',
	'version' 	 => '0.1'
);

$dir = dirname(__FILE__) . '/';

$wgExtensionMessagesFiles['UncategorizedUespPages'] = $dir . 'UncategorizedUESPPages.i18n.php';
$wgExtensionMessagesFiles['UncategorizedUespPagesAlias'] = $dir . 'UncategorizedUESPPages.alias.php';

$wgAutoloadClasses['UncategorizedUespPages'] = $dir . 'UncategorizedUESPPages_body.php';

$wgSpecialPages['UncategorizedUespPages'] = 'UncategorizedUespPages';
$wgSpecialPageGroups['UncategorizedUespPages'] = 'maintenance';
?>
