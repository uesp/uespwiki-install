<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains wiki settings common to *all* UESP wikis. Note that some settings
# may be overridden in a site dependant config file.
# It is included by LocalSettings.php.
#

$wgSitename = "UESPWiki";

$wgScriptPath       = "/w";
$wgScriptExtension  = ".php";
$wgStylePath = "$wgScriptPath/skins";

$wgLogo = "$wgScriptPath/extensions/UespCustomCode/files/UespLogo.jpg";

$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEnotifUserTalk = true;
$wgEnotifWatchlist = true;
$wgEmailAuthentication = true;

$wgEmergencyContact = "dave@uesp.net";
$wgPasswordSender   = "password@uesp.net";

$wgCacheDirectory = "/cache" . $uespLanguageSuffix;

$wgEnableUploads = true;
$wgUseImageMagick = true;
$wgImageMagickTempDir = "/imagetmp";
$wgImageMagickConvertCommand = '/usr/bin/convert';
$wgAllowExternalImages = true;
$wgUploadPath       = "//images.uesp.net/";
$wgUploadDirectory  = "$IP/images";
$wgUseInstantCommons = false;

$wgHashedUploadDirectory = true;
array_push($wgFileExtensions, 'ogg', 'zip', 'bmp', 'pcx', 'tga', 'svg');
$wgThumbnailEpoch = '20090624000000';
$wgUseSharedUploads = false;

# Special settings for translation wikis
if ($uespLanguageSuffix != "")
{
	$wgUseSharedUploads = true;
	$wgSharedUploadPath = '//images.uesp.net/';
	$wgSharedUploadDirectory = '/home/uesp/www/w/images/';
	$wgHashedSharedUploadDirectory = true;
	$wgUploadNavigationUrl = "//www.uesp.net/wiki/Special:Upload";
	$wgUploadMissingFileUrl= "//www.uesp.net/wiki/Special:Upload";
}

$wgShellLocale = "en_US.utf8";

$wgSecretKey = $uespWikiSecretKey;

$wgDefaultSkin = 'uespmonobook';

$wgRightsPage = "UESPWiki:Copyright_and_Ownership";
$wgRightsUrl = "http://creativecommons.org/licenses/by-sa/2.5/";
$wgRightsText = "Attribution-ShareAlike 2.5 License";
$wgRightsIcon = "//images.uesp.net/4/4d/Somerights.png";
$wgCopyrightIcon = "<a href=\"//en.uesp.net/wiki/UESPWiki:Copyright_and_Ownership\"><img src=\"//en.uesp.net/w/images/4/4d/Somerights.png\" style=\"border: none;\" alt=\"[Content is available under Attribution-ShareAlike]\" /></a>";

$wgDiff3 = "/usr/bin/diff3";
$wgExternalDiffEngine = "wikidiff2";

$wgAccountCreationThrottle = 1;
$wgAjaxSearch = true;
$wgAllowUserCss = true;
$wgAllowUserJs  = true;
$wgArticlePath  = "/wiki/$1";
$wgAutoConfirmAge = 3600*24*4;
$wgAutoConfirmCount = 10;
$wgBlockAllowsUTEdit = true;
$wgCookieDomain = ".uesp.net";
$wgCookiePrefix = "uesp_net_wiki5"; # Don't change as it affects the session name used
$wgDisableCounters = true;
$wgExpensiveParserFunctionLimit = 1000;
$wgFavicon = '/favicon.ico';
$wgJobRunRate = 0.1;
$wgLocaltimezone = "GMT";
$wgMaxShellMemory = 1310720;
$wgMaxShellFileSize = 1310720;
$wgRateLimits = array(20, 360); # RM: More advanced options are supported now. Do we need/want them?
$wgResourceLoaderMaxage = array(
		'versioned' => array(
				'server' => 30 * 24 * 60 * 60,
				'client' => 30 * 24 * 60 * 60,
		),
		'unversioned' => array(
				'server' => 86400,
				'client' => 86400,
		),
);
$wgTmpDirectory = "/imagetmp";
$wgUseETag = false;
$wgUsePathInfo  = true;
$wgUseSquid = true;
$wgUseTidy = true;
$wgUseXVO = true;

$wgAllowSiteCSSOnRestrictedPages = true;

# When you make changes to this configuration file, change this date if
# required to make sure that cached pages are cleared.
$wgCacheEpoch = '20150522151500';
$wgInvalidateCacheOnLocalSettingsChange = false;

$wgReadOnlyFile = "$wgUploadDirectory/UESP_LOCK_DB";
$wgApplyIpBlocksToXff = true;

set_time_limit(60);