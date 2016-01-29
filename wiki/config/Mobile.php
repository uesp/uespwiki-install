<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains mobile related settings.
# It is included by LocalSettings.php.
#

	# Force a mobile site display 
if ($uespIsMobile)
{
	if ($_COOKIE['mobileaction'] != 'toggle_view_desktop')
	{
		$_GET['useformat'] = 'mobile';
	}
	
	//MobileContext::singleton()->setForceMobileView( true );
}

$wgMobileUrlTemplate = '%h0.m.%h1.%h2';

//$wgMFContentNamespace  # Do we need to set this? 