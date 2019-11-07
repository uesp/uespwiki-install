<?php

/*
 * PageSpeedLog -- by Dave Humphrey, dave@uesp.net, September 2019
 * 
 * Logs server response time for serving MediaWiki pages to a local file.
 *
 */


$pslStartTime = microtime(true);

$wgPageSpeedLogFile = "/var/log/httpd/pagespeed.log";

register_shutdown_function(array("PageSpeedLog", 'onShutdown'), $wgPageSpeedLogFile, $pslStartTime);


class PageSpeedLog 
{
	
	static function setHooks( $parser ) 
	{
		/* Empty...used to force loading of this file */
	}
	
	
	public static function onShutdown(&$filename, &$startTime)
	{
		$pslEndTime = microtime(true);
		$diffTime = ($pslEndTime - $startTime)*1000;
		
		$url =  "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		
		$outputLine = "$startTime, $diffTime, $url\n";
		file_put_contents($filename, $outputLine, FILE_APPEND);		
	}
	
};