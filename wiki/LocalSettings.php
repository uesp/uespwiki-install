<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL ^ E_NOTICE);

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) 
{
	exit;
}

# Set global variables depending on which site is being viewed
require './config/InitializeSettings.php';

# All wiki related passwords and other secrets not intended for public release
if ($uespIsDev)
	require '/home/uesp/secrets/devwiki.secrets';
else
	require '/home/uesp/secrets/wiki.secrets';

# Include dependant config files. Be *very* careful changing the order of these
# files as some files may require global parmeters defined/set in other files.
require './config/CommonSettings.php';
require './config/DB.php';
require './config/Cache.php';
require './config/Namespaces.php';
require './config/Permissions.php';
require './config/Search.php';
require './config/Extensions.php';
require './config/Mobile.php';

# Fudge Content3 to think it's running on pt.uesp.net
# $wgLanguageCode = "it";
# $uespLanguageSuffix = "_" . $wgLanguageCode;
# $wgServer = "//" . $wgLanguageCode . ".uesp.net";
# $wgServer = "//dev.uesp.net";

# Optional includes (enable for testing as needed on select wikis). Be careful
# if/when enabling on the main live sites due to performance issues and the
# size of data collected.
# require './config/Profiler.php';
require './config/Debug.php';

# $wgShowExceptionDetails = true;
