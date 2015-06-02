<?php

# Confirm it's being called from a valid entry point; skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install the UespSiteStats extension, put the following line in LocalSettings.php:
require_once( \$IP . '/extensions/UespSiteStats/UespSiteStats.php' );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path'		 => __FILE__,
	'name' 	 	 => 'UespSiteStats',
	'author' 	 => 'Dave Humphrey',
	'url' 		 => 'http://www.uesp.net/wiki/UESPWiki:UespSiteStats',
	'description' 	 => 'TODO',
	'descriptionmsg' => 'uespsitestats_desc',
	'version' 	 => '0.1'
);

$dir = dirname(__FILE__) . '/';

$wgExtensionMessagesFiles['UespSiteStats'] = $dir . 'UespSiteStats.i18n.php';
$wgExtensionMessagesFiles['UespSiteStatsAlias'] = $dir . 'UespSiteStats.alias.php';

$wgAutoloadClasses['UespSiteStats'] = $dir . 'UespSiteStats_body.php';

$wgSpecialPages['UespSiteStats'] = 'UespSiteStats';
$wgSpecialPageGroups['UespSiteStats'] = 'wiki';
?>
