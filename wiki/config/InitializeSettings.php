<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file initializes UESP specific global variables used later on in the 
# configuration to determine which site is being viewed. 
#

# dev.uesp.net host detection
$uespIsDev = false;

if ($_SERVER['HTTP_HOST'] == "dev.uesp.net" || $_SERVER['HTTP_HOST'] == "dev.m.uesp.net" || $_SERVER['HTTP_HOST'] == "devit.uesp.net" ||
		$_SERVER['HTTP_HOST'] == "devar.uesp.net" || $_SERVER['HTTP_HOST'] == "devpt.uesp.net" || $_SERVER['HTTP_HOST'] == "devfr.uesp.net" ||
		$_SERVER['HTTP_HOST'] == "deven.uesp.net")
{
	$uespIsDev = true;
}

# backup1.uesp.net command line host detection
$uespIsBackup1 = false;

if (php_sapi_name() == "cli" && gethostname() == "backup1.uesp.net") 
{
	$uespIsBackup1 = true;
	if (defined('STDERR')) fwrite(STDERR, "\tAuto-detected UESP backup1 wiki!\n");	
}

# Mobile sites
$uespIsMobile = false;
$uespIsApp = false;

$UESP_MOBILE_SERVERS = array(
		'm.uesp.net',
		'ar.m.uesp.net',
		'en.m.uesp.net',
		'fr.m.uesp.net',
		'it.m.uesp.net',
		'pt.m.uesp.net',
		'www.m.uesp.net',
		'content1.m.uesp.net',
		'content2.m.uesp.net',
		'content3.m.uesp.net',
		'mobile.uesp.net',
		'mobile1.uesp.net',
		'mobile2.uesp.net',
		'mobile3.uesp.net',
		'dev.m.uesp.net',
);

if (in_array($_SERVER['HTTP_HOST'], $UESP_MOBILE_SERVERS, TRUE)) 
{
	$uespIsMobile = true;
}

$UESP_APP_SERVERS = array(
		'app.uesp.net',
		'appen.uesp.net',
		'appit.uesp.net',
		'apppt.uesp.net',
		'appar.uesp.net',
		'appfr.uesp.net',
		'en.app.uesp.net',
		'pt.app.uesp.net',
		'it.app.uesp.net',
		'ar.app.uesp.net',
		'fr.app.uesp.net',
		'www.app.uesp.net',
		'content1.app.uesp.net',
		'content2.app.uesp.net',
		'content3.app.uesp.net',
		'dev.app.uesp.net',
);

if (in_array($_SERVER['HTTP_HOST'], $UESP_APP_SERVERS, TRUE)) 
{
	$uespIsApp = true;
	$uespIsMobile = true;
}

# Translation Projects and Languages

# The language suffix is added to the end of database names and other
# settings for translation wiki projects. Set in Language.php if needed
$uespLanguageSuffix = "";

# TODO: More robust language detection
$wgLanguageCode = "en";

$host = $_SERVER['HTTP_HOST'];

if ($host == "pt.uesp.net" || $host == "pt.m.uesp.net" || $host == "pt.app.uesp.net" || $host == "apppt.uesp.net" || $host == "devpt.uesp.net")
{
	$wgLanguageCode = "pt";
}

if ($host == "it.uesp.net" || $host == "it.m.uesp.net" || $host == "it.app.uesp.net" || $host == "appit.uesp.net" || $host == "devit.uesp.net")
{
	$wgLanguageCode = "it";
}

if ($host == "ar.uesp.net" || $host == "ar.m.uesp.net" || $host == "ar.app.uesp.net" || $host == "appar.uesp.net" || $host == "devar.uesp.net") 
{
	$wgLanguageCode = "ar";
}

if ($host == "fr.uesp.net" || $host == "fr.m.uesp.net" || $host == "fr.app.uesp.net" || $host == "appfr.uesp.net" || $host == "fr.content3.uesp.net" || $host == "devfr.uesp.net") 
{
	$wgLanguageCode = "fr";
}

if ($wgLanguageCode != "en")
{
	$uespLanguageSuffix = "_" . $wgLanguageCode;
}

$wgLocalInterwikis = array( $wgLanguageCode );

# Set server according to environment and host name
if ($uespIsDev)
{
	$wgServer = "https://dev" . $wgLanguageCode . ".uesp.net";
	if ($wgLanguageCode == "en") $wgServer = "https://dev.uesp.net";
}
elseif ($uespIsApp)
{
	//$wgServer = "https://app" . $wgLanguageCode . ".uesp.net";
	$wgServer = "https://app.uesp.net";
}
elseif ($uespIsMobile)
{
	$wgServer = "https://" . $wgLanguageCode . ".m.uesp.net";
}
else 
{
	$wgServer = "https://" . $wgLanguageCode . ".uesp.net";
}

# Check command line arguments (this only parses long options related to the UESP).
if (php_sapi_name() == "cli") {
	
	function uespParseCommandArgs()
	{
		global $argv;
		
		$args = array();
		
		for ( $arg = reset( $argv ); $arg !== false; $arg = next( $argv ) ) 
		{
			
			if ( substr( $arg, 0, 2 ) == '--' ) 
			{
				$option = substr( $arg, 2 );
				$bits = explode( '=', $option, 2 );
				if ($bits[1] == null) $bits[1] = true;
				$args[$bits[0]] = $bits[1];
			}
		}
		
		return $args;
	}
	
	
	$uespArgs = uespParseCommandArgs();
	
	if ($uespArgs["uesplang"] != null)
	{
		$lang = $uespArgs["uesplang"];
		if ($lang == null || $lang == "") $lang = "en";
		
		if (defined('STDERR')) fwrite(STDERR, "\tUsing custom UESP language code '$lang'!\n");
		
		$wgLanguageCode = $lang;
		
		if ($wgLanguageCode == "en")
			$uespLanguageSuffix = "";
		else
			$uespLanguageSuffix = "_" . $wgLanguageCode;
		
		$wgServer = "https://" . $wgLanguageCode . ".uesp.net";
	}
	
	if ($uespArgs["uespdev"])
	{
		$wgServer = "https://dev" . $wgLanguageCode . ".uesp.net";
		if ($wgLanguageCode == "en") $wgServer = "https://dev.uesp.net"; 
		$uespIsDev = true;
		if (defined('STDERR')) fwrite(STDERR, "\tForcing UESP dev wiki!\n");
	}
	
	if ($uespArgs["uespbackup"])
	{
		$uespIsBackup1 = true;
		if (defined('STDERR')) fwrite(STDERR, "\tForcing UESP backup1 wiki!\n");
	}
}
