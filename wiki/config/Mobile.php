<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains mobile related settings.
# It is included by LocalSettings.php.
#

if (class_exists("MobileContext"))
{
	//if (MobileContext::singleton()->isMobileDevice()) $uespIsMobile = true; 
}

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

	//Hack to force redirect to mobile domains (MobileFrontEnd doesn't seem to do it by itself)
$wgHooks['BeforeInitialize'][] = 'uespMobileInit';


function uespMobileInit (&$title, &$article, &$output, &$user, $request, $mediaWiki)
{
	$mobileContext = MobileContext::singleton();
	$displayMobile = $mobileContext->shouldDisplayMobileView();
	$host = $_SERVER['HTTP_HOST'];

	//error_log("TestMobile: $displayMobile, $host");

	if (!$displayMobile) return;

	$match = preg_match("#^([A-Za-z0-9]+)\.uesp\.net$#", $host, $matches);
	if (!$match) return;

	if ($matches[1] == "m" || $matches[1] == "www")
		$redirectUrl = "//en.m.uesp.net" . $_SERVER['REQUEST_URI'];
	else
		$redirectUrl = "//" . $matches[1] . ".m.uesp.net" . $_SERVER['REQUEST_URI'];
	
	//error_log("TestMobile: Redirect: $redirectUrl");

	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	$output->redirect( $redirectUrl );
	
}