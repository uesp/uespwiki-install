<?php

/*
 * PageSpeedLog -- by Dave Humphrey, dave@uesp.net, September 2019
 * 
 * Logs server response time for serving MediaWiki pages to a local file.
 *
 */


class PageSpeedLog
{
	public static $pslStartTime = 0;
	
	static function setHooks( $parser )
	{
		global $wgPageSpeedLogFile;
		
		register_shutdown_function(array("PageSpeedLog", 'onShutdown'), $wgPageSpeedLogFile, self::$pslStartTime);
	}
	
	
	public static function onInitialize()
	{
		self::$pslStartTime = microtime(true);
	}
	
	
	public static function onShutdown($filename, $startTime)
	{
		$pslEndTime = microtime(true);
		$diffTime = ($pslEndTime - $startTime)*1000;
		
		$url =  "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		
		$outputLine = "$startTime, $diffTime, $url\n";
		file_put_contents($filename, $outputLine, FILE_APPEND);
	}
	
};