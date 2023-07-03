<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains search related settings.
# It is included by LocalSettings.php.
#

wfLoadExtension( "Elastica" );
require_once( "$IP/extensions/CirrusSearch/CirrusSearch.php" );
$wgDisableSearchUpdate = false;

$wgCirrusSearchServers = [ [ 'host' => $UESP_SERVER_SEARCH1, 'port' => 9205 ] ];	// 1.29 uses ElasticSearch v5.3 on port 9205

$wgSearchType = 'CirrusSearch';

# Can't enable this until the search-highlighter plugin installed on the Elastica server
# $wgCirrusSearchUseExperimentalHighlighter = true;
# $wgCirrusSearchOptimizeIndexForExperimentalHighlighter = true;

$wgDebugLogGroups['CirrusSearch'] = "/var/log/httpd/cirrussearch.log";
