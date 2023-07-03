<?php
// If this is run directly from the web die as this is not a valid entry point.
if ( !defined( 'MEDIAWIKI' ) ) die( 'Invalid entry point.' );

// Extension credits.
$wgExtensionCredits[ 'other' ][] = array(
		'path'           => __FILE__,
		'name'           => 'LogPageRenderTimes',
		'descriptionmsg' => 'logpagerendertimes-desc',
		'author'         => '[http://www.uesp.net/wiki/User:Daveh Daveh]',
		'url'            => 'http://www.uesp.net/wiki/UESPWiki:LogPageRenderTimes',
		'version'        => '0.1'
);

$wgExtensionMessagesFiles['LogPageRenderTimes'] =  dirname( __FILE__).'/LogPageRenderTimes.i18n.php';

$wgHooks['AfterFinalPageOutput'][] = 'LogPageRenderTimes::onAfterFinalPageOutput';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'LogPageRenderTimes::onLoadExtensionSchemaUpdates';

$wgLogPageRenderTimeEnable    = TRUE;
$wgLogPageRenderTimeSkipRatio = 2;


class LogPageRenderTimes
{

	public static function onAfterFinalPageOutput( $output )
	{
		global $wgRequestTime, $wgLogPageRenderTimeEnable, $wgLogPageRenderTimeSkipRatio;
		
		if (!$wgLogPageRenderTimeEnable) return;
		if ($wgLogPageRenderTimeSkipRatio > 1 && (rand() % $wgLogPageRenderTimeSkipRatio) != 0) return;
		
		$elapsed = intval((microtime( true ) - $wgRequestTime) * 1000);
		//error_log($output->getTitle() . " render time = " . $elapsed . "ms");
		
		$values = array(
			"page_title" => $output->getTitle(),
			"rendertimems" => $elapsed
		);
		
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert('pagerenderlog', $values);
	}
	
	public static function onLoadExtensionSchemaUpdates ( DatabaseUpdater $updater )
	{
		$updater->addExtensionTable( 'pagerenderlog', dirname( __FILE__ ) . '/pagerenderlog.sql', true );
		return true;
	}
	
}


?>