<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.

# Protect against web entry
if ( !defined( 'MEDIAWIKI' ) ) 
{
	exit;
}

# All wiki related passwords and other secrets not intended for public release
require '/home/uesp/secrets/wiki.secrets';

# Set global variables depending on which site is being viewed
require './config/InitializeSettings.php';

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

# Optional includes (enable for testing as needed on select wikis). Be careful
# if/when enabling on the main live sites due to performance issues and the
# size of data collected.
# require './config/Profiler.php';
# require './config/Debug.php';
