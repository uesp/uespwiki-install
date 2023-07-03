<?php

	# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/DailyEdits/DailyEdits.php" );
EOT;
        exit( 1 );
}

$wgExtensionCredits['dailyedits'][] = array(
        "name" => "DailyEdits",
        "author" => "Dave Humphrey",
        "url" => "http://www.uesp.net/wiki/UESPWiki:DailyEdits",
        "description" => "Displays the count of edits per day.",
);
 
$wgAutoloadClasses['SpecialDailyEdits'] = __DIR__ . '/SpecialDailyEdits.php'; 
$wgMessagesDirs['DailyEdits'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['DailyEditsAlias'] = __DIR__ . '/DailyEdits.i18n.alias.php';
$wgSpecialPages['DailyEdits'] = 'SpecialDailyEdits'; 
$wgSpecialPageGroups['DailyEdits'] = 'other';

$wgDailyEditsGraphFile = '';

