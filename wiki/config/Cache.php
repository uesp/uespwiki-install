<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains cache/Squid related settings.
# It is included by LocalSettings.php.
#

$wgMainCacheType = CACHE_MEMCACHED;
$wgSessionsInObjectCache = true;
$wgObjectCacheSessionExpiry = 100000;
$wgMemCachedServers = array("10.7.143.70:11000");

if ($uespIsDev)
{
	$wgCacheDirectory = "/uesp-devwiki-cache";	
}
else
{
	$wgCacheDirectory = "/cache" . $uespLanguageSuffix;
	$wgSquidMaxage = 2678400;
	$wgSquidServers = array("10.7.143.40");
}
