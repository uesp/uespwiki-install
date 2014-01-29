<?php

	# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/MyExtension/MyExtension.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits['specialpage'][] = array(
        'name' => 'DailyEdits',
        'author' => 'Dave Humphrey',
        'url' => 'http://www.uesp.net/wiki/UESPWiki:DailyEdits',
        'description' => 'Displays the count of edits per day.',
        'descriptionmsg' => 'dailyedits-desc',
        'version' => '0.0.1',
);
 
$dir = dirname(__FILE__) . '/';
 
$wgAutoloadClasses['SpecialDailyEdits'] = $dir . 'SpecialDailyEdits.php'; 
$wgExtensionMessagesFiles['DailyEdits'] = $dir . 'DailyEdits.i18n.php';
$wgSpecialPages['DailyEdits'] = 'SpecialDailyEdits'; 
$wgSpecialPageGroups['DailyEdits'] = 'other';

$wgDailyEditsGraphFile = '';

?>
