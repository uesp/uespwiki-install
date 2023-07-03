<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains mobile related settings.
# It is included by LocalSettings.php.
#

require(__DIR__  . "/../extensions/MobileFrontend/includes/MobileContext.php");

	# Force a mobile site display 
if ($uespIsMobile)
{
	MobileContext::singleton()->setForceMobileView( true );
}

	/* While this MobileFrontEnd setting may seem exactly what we want it tends to mess
	 * things up more than it helps. */ 
//$wgMobileUrlTemplate = '%h0.m.%h1.%h2';

	/* Do we need to set this? */
//$wgMFContentNamespace  

	/* The app should always show mobile content */
if ($uespIsApp)
{
	MobileContext::singleton()->setForceMobileView( true );
	$wgMobileUrlTemplate = 'app.uesp.net';
}


	/*
	 * The following is a hack to force a redirect to the mobile or desktop domain depending on which 
	 * version of the site needs to be rendered. Unfortunately, MobileFrontEnd doesn't seem to do it by itself
	 * and sometimes serves desktop content on the mobile domains and mobile content on the desktop domains.
	 * This pollutes the Varnish cache and can cause users to see the incorrect version of the page.
	 *  
	 */
$wgHooks['BeforeInitialize'][] = 'uespMobileInit';

function uespMobileInit (&$title, &$article, &$output, &$user, $request, $mediaWiki)
{
	global $uespIsApp;
	
	if ($uespIsApp) return;
	
	$mobileContext = MobileContext::singleton();
	$displayMobile = $mobileContext->shouldDisplayMobileView();
	$host = $_SERVER['HTTP_HOST'];
	
	$stopMobileRedirect = $request->getCookie('stopMobileRedirect', '');
	if ($stopMobileRedirect == "true") $displayMobile = false;
	
	$match = preg_match("#^([A-Za-z0-9]+)(?:\.([A-Za-z0-9]+))?\.uesp\.net$#", $host, $matches);
	if (!$match) return;
	
	$domainIsMobile = false;
	if ($matches[1] == "m" || $matches[2] == "m") $domainIsMobile = true;
	
	$shouldRedirectToMobile  = !$domainIsMobile && $displayMobile;
	$shouldRedirectToDesktop = false;
	
	$mobileAction = $request->getText('mobileaction');
	
	if ($mobileAction == "toggle_view_desktop")
	{
		MobileContext::singleton()->setForceMobileView(false);
		MobileContext::singleton()->setUseFormat('desktop');
		setcookie('mf_useformat', '', time() - 3600, '', 'uesp.net');
		
		if ($domainIsMobile) 
		{
			$shouldRedirectToDesktop = true;
			$shouldRedirectToMobile = false;
		}
		else 
		{
			$shouldRedirectToDesktop = false;
			$shouldRedirectToMobile = false;
		}
	}
	elseif ($mobileAction == "toggle_view_mobile")
	{
		MobileContext::singleton()->setUseFormat('mobile');
		
		if ($domainIsMobile) 
		{
			$shouldRedirectToDesktop = false;
			$shouldRedirectToMobile = false;
			
			setupUespMobileHooks();
		}
		else 
		{
			$shouldRedirectToDesktop = false;
			$shouldRedirectToMobile = true;
		}
	}
	else
	{
		if ($domainIsMobile)
		{
			setupUespMobileHooks();
		}
	}
	
	//error_log("TestMobile: $displayMobile:$shouldRedirectToMobile:$shouldRedirectToDesktop:$mobileAction:$stopMobileRedirect, $host:{$matches[1]}:{$matches[2]}");
	
	if ($domainIsMobile) MobileContext::singleton()->setForceMobileView( true );
	
	$subDomain = $matches[1];
	if ($matches[1] == "m" || $matches[1] == "www") $subDomain = "en";
	
	if ($shouldRedirectToMobile)
	{
		$redirectUrl = "//" . $subDomain . ".m.uesp.net" . $_SERVER['REQUEST_URI'];
	}
	elseif ($shouldRedirectToDesktop)
	{
		$redirectUrl = "//" . $subDomain . ".uesp.net" . $_SERVER['REQUEST_URI'];
	}
	else 
	{
		return;
	}

	//error_log("TestMobile: Redirect: $redirectUrl");

	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Location: $redirectUrl", true, 301);
	
		/*
		 * Flush the HTTP headers and exit the PHP script otherwise MobileFrontEnd may re-emit
		 * the redirect and mess things up.
		 * 
		 */
	flush();
    ob_flush();
    sleep(1);
    exit();
}
