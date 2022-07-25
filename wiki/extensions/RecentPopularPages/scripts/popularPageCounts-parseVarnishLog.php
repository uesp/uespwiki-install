<?php

if (php_sapi_name() != "cli") die("Can only be run from command line!");

	// UESP database information
require_once("/home/uesp/secrets/wiki.secrets");

require_once("popularPageCounts-parseLog.php");

	// Change to use your wiki database host/user/password/name
$parser = new CPopularPageCountsVarnishLogParser($UESP_SERVER_DB1, $uespWikiUser, $uespWikiPW, $uespWikiDB);

	/* Modify parameters as needed (see top of CPopularPageCountsVarnishLogParser class definition) */
$parser->SHOW_PROGRESS_LINECOUNT = 10000;
$parser->SHOW_UNMATCHED_LINES = false;
$parser->MIN_COUNT_FOR_DATABASE = 5;
$parser->HOST_REGEX = '/uesp\.net$/i';
$parser->ACCESS_LOG_DATEFORMAT = "d/M/Y:H:i:s O"; 
$parser->ACCESS_LOG_REGEX = '/(?P<ip>.*) \[(?P<time>.*)\] "(?P<action>[A-Za-z0-9\-_]+) (?P<url>.*) HTTP.*" (?P<code>.*) (?P<length>.*)/'; 

	/* Change log location/name */
$parser->ParseLog("/var/log/varnish/access.log");

	/* Optionally save raw counts to text files */
$parser->SavePageCountsFile("/home/uesp/pagecounts/varnishcounts.txt");
$parser->SaveSummaryCountsFile("/home/uesp/pagecounts/varnishsummary.txt");

	/* Save results to database */
$parser->SaveToDatabase();

