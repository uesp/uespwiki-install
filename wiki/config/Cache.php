<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains cache/Squid related settings.
# It is included by LocalSettings.php.
#

$wgMainCacheType = CACHE_MEMCACHED;
$wgSessionsInObjectCache = true;
$wgObjectCacheSessionExpiry = 100000;
$wgMemCachedServers = array($UESP_SERVER_MEMCACHED . ":11000");

if ($uespIsDev)
{
	$wgCacheDirectory = "/home/uesp/cache/dev";
	$wgMemCachedServers = array($UESP_SERVER_BACKUP1 . ":11000");
}
else
{
	$wgCacheDirectory = "/home/uesp/cache/" . $uespLanguageSuffix;
	$wgSquidMaxage = 86400;
	$wgSquidServers = array($UESP_SERVER_SQUID1);
	$wgUseSquid = true;
	$wgUsePrivateIPs = true;
}
