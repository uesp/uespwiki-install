<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains mobile related settings.
# It is included by LocalSettings.php.
#

	# Force a mobile site display 
if ($uespIsMobile)
{
	if ($_COOKIE['stopMobileRedirect'] != 'true') $_GET['useformat'] = 'mobile';
	# $wgMobileUrlTemplate = 'mobile.uesp.com';
	# $wgCacheDirectory = '/uesp-mobile-cache'; # Shouldn't need a different cache directory
}