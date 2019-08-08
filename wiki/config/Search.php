<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains search related settings.
# It is included by LocalSettings.php.
#

wfLoadExtension( "Elastica" );
require_once( "$IP/extensions/CirrusSearch/CirrusSearch.php" );
$wgDisableSearchUpdate = false;
$wgCirrusSearchServers = array( $UESP_SERVER_SEARCH );
$wgSearchType = 'CirrusSearch';
