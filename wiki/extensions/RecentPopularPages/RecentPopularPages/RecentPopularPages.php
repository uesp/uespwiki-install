<?php
# Confirm it's being called from a valid entry point; skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install the Recent Popular Pages extension, put the following line in LocalSettings.php:
require_once( \$IP . '/extensions/RecentPopularPages/RecentPopularPages.php' );
EOT;
        exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'name' => 'RecentPopularPages',
	'author' => 'Dave Humphrey',
	'url' => 'http://en.uesp.net/wiki/UESPWiki:Popular_Pages',
	'description' => 'Special page listing recent popular pages.',
	'version' => '1.1',
);

$dir = dirname(__FILE__) . '/';

# Add to list of special pages
$wgSpecialPages['RecentPopularPages'] = 'RecentPopularPagesPage';

# Tell Mediawiki where to find the file containing the extension's class
$wgAutoloadClasses['RecentPopularPagesPage'] = $dir . '/RecentPopularPages_body.php';

# Load messages
$wgExtensionMessagesFiles['recentpopularpages'] = $dir . 'RecentPopularPages.i18n.php';
$wgExtensionMessagesFiles['recentpopularpagesAlias'] = $dir . 'RecentPopularPages.alias.php';

# Hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'CRecentPopularPagesHooks::onLoadExtensionSchemaUpdates';


class CRecentPopularPagesHooks
{
	
	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater )
	{
			$updater->addExtensionTable(
					'popularPageCounts',
					 __DIR__ . '/sql/popularPageCounts_createTable.sql'
			);
			
			$updater->addExtensionTable(
					'popularPageSummaries',
					 __DIR__ . '/sql/popularPageSummaries_createTable.sql'
			);
			
			$updater->addExtensionTable(
					'popularPageInfo',
					 __DIR__ . '/sql/popularPageInfo_createTable.sql'
			);
	}
}
