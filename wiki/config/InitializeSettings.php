<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file initializes UESP specific global variables used later on in the 
# configuration to determine which site is being viewed. 
#

# dev.uesp.net
$uespIsDev = false;
if ($_SERVER['HTTP_HOST'] == "dev.uesp.net") $uespIsDev = true;

# Mobile sites
$uespIsMobile = false;

$UESP_MOBILE_SERVERS = array(
		'm.uesp.net',
		'mobile.uesp.net',
		'mobile1.uesp.net',
		'mobile2.uesp.net',
		'mobile3.uesp.net',
);

if (in_array($_SERVER['HTTP_HOST'], $UESP_MOBILE_SERVERS, TRUE)) $uespIsMobile = true;

# Translation Projects and Languages

# The language suffix is added to the end of database names and other
# settings for translation wiki projects. Set in Language.php if needed
$uespLanguageSuffix = "";

# TODO: More robust language detection
$wgLanguageCode = "en";
if ($_SERVER['HTTP_HOST'] == "pt.uesp.net") $wgLanguageCode = "pt";

if ($wgLanguageCode != "en")
{
	$uespLanguageSuffix = "_" . $wgLanguageCode;
}
