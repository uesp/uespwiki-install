<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains language related configuration.
# It is included by LocalSettings.php.
#

$wgLocalInterwikis = array ($wgLanguageCode);
if ($wgLanguageCode != "en")
{
	$uespLanguageSuffix = "_" . $wgLanguageCode;
}
