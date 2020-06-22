<?php

/* Disabling/Enabling the extension
 * To disable this entire extension, simply remove its entry from LocalSettings.php
 *
 * Many individual features can be disabled by commenting out individual lines of code below.  This
 * file contains all of the initialization functions that enable the various extension components,
 * and therefore is the best place to enable/disable extension features.
 *
 * However, several parts of the code overlap, so some individual features cannot be individually disabled
 * What can or cannot be disabled:
 *
 * Special:Wantedpages and Special:Lonelypages
 * - each can be individually disabled by commenting out relevant line in efSiteSpecialPageInit
 *
 * Magic words
 * - can be disabled as a group by commenting out MagicWordwgVariableIDs hook
 * - can be disabled individually by commenting out individual lines in efSiteDeclareMagicWord
 *
 * Parser function
 * - can be disabled by commenting out setFunctionHook call in efSiteCustomCode

 * Search features
 * - The special search page can be disabled by
 * - (a) commenting out relevant line in efSiteSpecialPageInit, and
 * - (b) undoing the change in Wiki.php (not necessary for MW1.14+)
 * - The search engine customizations can be disabled by commenting out the wgSearchType definition
 * - There are multiple interactions between the search page and the search engine, so they need to either
 *   both be enabled or both be disabled.  Some MW1.10/MW1.14 ugliness requires even more crosslinking
 *   than should really be necessary... once the site's updated to MW1.14, this code could be modified
 *   to provide better separation of features.
 *
 */
/* Version 0.4 updates:
   SiteSpecialLonelypages SQL query changed to work with SQL4
   SiteSearchMySQL bug fixed (quotes in "go" words do not cause SQL error), additional sanity checks on "go" words
   Parser::getDefaultSort changed to use SortableCorename
   add IsFileCacheable hook to try to fix problems with multi-page categories
   minor tweaks to functions in SiteCustomCode_body.php : allow some functions to be called with a parameter
   minor tweaks to SiteNamespace.php to allow namespace to be provided by a defined variable
*/
/* Version 0.5 updates:
   minor tweaks to google ad functions to allow them to be disabled from LocalSettings
   fix SiteNamespace.php errors (recognize mods again; use parser->getTitle instead of wgTitle; use better NS names)
*/
/* Version 0.6 updates:
   fix an error in SiteNamespace::getRelatedNamespaces that's making Tamriel Rebuilt articles top priority
   plus one tweak to getRelatedNamespaces in anticipation of future edits to sitenamespacelist
*/
/* Version 0.7 updates:
   make code more MediaWiki-compliant and efficient (in particular, move most functions inside classes)
   preliminary code updates in anticipation of future Mediawiki code changes (in particular, querypage)
   move all text into system messages for internationalization/easier customization
   add search options to user preferences
   add ranking to searches
   additional round of searching on 'go' to find matches for strings like 'oblivion artifact'
   add namespace to si_title
   fix bug in preg_replace of SearchUpdate
   add boolean option to searches
   add parser functions and hooks to process and display bread crumb trail
   fix bug in NS_FULL and MOD_NAME for sub-namespaces
*/
/* Version 0.8 updates
   All files and code changed to use generic 'Site' prefix
   Most MW.10 code removed
*/
/* Version 0.9
   Recent changes options: hide userspace edits, custom namespace selection
   Userspace patroller
   Searchindex changed to contain namespace, etc so searches don't have to tie up page table

   Note that for links from Recentchanges to my preferences to work properly, prefs.js needs to be modified
   For searching to work, some SQL changes need to be made.  I'll get the SQL added properly soon.
*/
/* Version 0.9.7
   Code updated according to http://www.mediawiki.org/wiki/Special:Code/MediaWiki/52503
*/
# Confirm it's being called from a valid entry point; skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
	echo <<<EOT
To install the Site Custom Code extension, put the following line in LocalSettings.php:
require_once( \$IP . '/extensions/SiteCustomCode/SiteCustomCode.php' );
EOT;
	exit(1);
}

require_once('SiteSpecialPreferences.php');
require_once('SiteSpecialWantedpages.php');
require_once('SiteSpecialSearch.php');

if ($wgSitename == 'UESPWiki')
	$egCustomSiteID = 'Uesp';
elseif ($wgSitename == 'ESPOWiki')
	$egCustomSiteID = 'Espo';
else
	$egCustomSiteID = 'Site';

$wgExtensionCredits['other'][] = array(
	'name' => $egCustomSiteID . 'CustomCode',
	'author' => 'Nephele',
	'url' => '//www.uesp.net/wiki/UESPWiki:UespCustomCode',
	'description' => 'Various code customizations that provide functionality specific to this site',
	'version' => '0.9.7',
);

/*
 * Extension definitions
 */
# definitions for magic words and parser functions
define('MAG_SITE_NS_BASE', 'NS_BASE');
define('MAG_SITE_NS_NAME', 'NS_NAME');
define('MAG_SITE_NS_FULL', 'NS_FULL');
define('MAG_SITE_NS_PARENT', 'NS_PARENT');
define('MAG_SITE_NS_MAINPAGE', 'NS_MAINPAGE');
define('MAG_SITE_NS_CATEGORY', 'NS_CATEGORY');
define('MAG_SITE_NS_TRAIL', 'NS_TRAIL');
define('MAG_SITE_NS_ID', 'NS_ID');
define('MAG_SITE_MOD_NAME', 'MOD_NAME');
define('MAG_SITE_CORENAME', 'CORENAME');
define('MAG_SITE_SORTABLECORENAME', 'SORTABLECORENAME');
define('MAG_SITE_LABELNAME', 'LABELNAME');
define('MAG_SITE_SORTABLE', 'sortable');
define('MAG_SITE_LABEL', 'label');
define('MAG_SITE_INITTRAIL', 'inittrail');
define('MAG_SITE_SETTRAIL', 'settrail');
define('MAG_SITE_ADDTOTRAIL', 'addtotrail');
# (0 means case-insensitive, 1 means case-sensitive)
$egSiteNamespaceMagicWords =
	array(MAG_SITE_NS_BASE => 1,
	      MAG_SITE_NS_NAME => 1,
	      MAG_SITE_NS_FULL => 1,
	      MAG_SITE_NS_PARENT => 1,
	      MAG_SITE_NS_MAINPAGE => 1,
	      MAG_SITE_NS_CATEGORY => 1,
	      MAG_SITE_NS_TRAIL => 1,
	      MAG_SITE_NS_ID => 1,
	      MAG_SITE_MOD_NAME => 1);
$egSiteOtherMagicWords =
	array(MAG_SITE_CORENAME => 0,
	      MAG_SITE_SORTABLECORENAME => 0,
	      MAG_SITE_LABELNAME => 0);
$egSiteParserFunctions =
	array(MAG_SITE_SORTABLE => 0,
	      MAG_SITE_LABEL => 0,
	      MAG_SITE_INITTRAIL => 0,
	      MAG_SITE_SETTRAIL => 0,
	      MAG_SITE_ADDTOTRAIL => 0);

# ParserFirstCallInit does not work correctly in all versions of MediaWiki
# Function is called too late so use below line all the time.
$wgExtensionFunctions[] = 'efSiteCustomCode';

$dir = dirname(__FILE__) . '/';

# Tell Mediawiki where to find files containing extension-specific classes
$wgAutoloadClasses['SiteNamespace'] = $dir . 'SiteNamespace.php';
# $wgAutoloadClasses['SiteSearchMySQL'] = $dir . 'SiteSearchMySQL.php';
$wgAutoloadClasses['SiteMonobook'] = $dir . 'SiteMonobook.php';
$wgAutoloadClasses['SiteMiscFunctions'] = $dir . 'SiteCustomCode_body.php';
$wgAutoloadClasses['SiteBreadCrumbTrail'] = $dir . 'SiteCustomCode_body.php';
$wgAutoloadClasses['SiteSpecialRecentChanges'] = $dir . 'SiteSpecialRecentchanges.php';
$wgAutoloadClasses['SiteOldChangesList'] = $dir . 'SiteChangesList.php';
$wgAutoloadClasses['SiteEnhancedChangesList'] = $dir . 'SiteChangesList.php';
$wgAutoloadClasses['SiteSpecialRandompage'] = $dir . 'SiteSpecialRandompage.php';
$wgAutoloadClasses['UespWebpHandler'] = $dir . 'UespWebpHandler.php';

$wgMediaHandlers['image/webp'] = 'UespWebpHandler';

/*
 * Add Hooks
 */
# This hook causes most of the changes to special pages, in particular overriding defaults for Wantedpages, Lonelypages, and Search
# Comment out this line to disable those features (see wgSearchType below, too, for Search)
$wgHooks['SpecialPage_initList'][] = 'efSiteSpecialPageInit';
$wgHooks['BeforePageDisplay'][] = 'UESP_beforePageDisplay';
//$wgHooks['SearchGetNearMatchBefore'][] = 'onSearchGetNearMatchBefore';
//$wgHooks['SpecialSearchCreateLink'][] = 'onSpecialSearchCreateLink';
$wgHooks['TitleSquidURLs'][] = 'onUespTitleSquidURLs';

	/* Mobile Specific */
$wgHooks['MinervaDiscoveryTools'][] = 'onUespMinervaDiscoveryTools';
$wgHooks['MobilePersonalTools'][] = 'onUespMobilePersonalTools';
$wgHooks['MobileMenu'][] = 'onUespMobileMenu';

$wgHooks['BeforeInitialize'][] = 'onUespBeforeInitialize';

# Load messages

$wgExtensionMessagesFiles['sitecustomcode'] = $dir . 'SiteCustomCode.i18n.php';
$wgExtensionMessagesFiles['sitecustomcodeAlias'] = $dir . 'SiteCustomCode.alias.php';
$wgExtensionMessagesFiles['sitecustomcodeMagic'] = $dir . 'SiteCustomCode.i18n.magic.php';

# Add magic words and parser functions
# Comment out the first line to disable all magic words
$wgHooks['MagicWordwgVariableIDs'][] = 'SiteMiscFunctions::declareMagicWords';
$wgHooks['ParserGetVariableValueSwitch'][] = 'SiteMiscFunctions::assignMagicWords';

# Hook for cacheable check: used to tell code not to cache category pages
$wgHooks['IsFileCacheable'][] = 'SiteMiscFunctions::isFileCacheable';

# Hooks for creating bread crumb trail and inserting trail where subpage normally appears
# To disable these features, change <site>'settrail' to 0 (system message)
$wgHooks['OutputPageParserOutput'][] = 'SiteBreadCrumbTrail::getCachedTrail';
$wgHooks['SkinSubPageSubtitle'][] = 'SiteBreadCrumbTrail::subpageHook';

# Hook to add a rel=canonical tag to the header
$wgHooks['OutputPageParserOutput'][] = 'SiteMiscFunctions::addCanonicalToHeader';

# Alternate way to deal with links
#$wgHooks['InternalParseBeforeLinks'][] = 'SiteMiscFunctions::parseLinks'; // ESPO
# Hook for adding search-related options to user preferences
$wgHooks['UserToggles'][] = 'SiteMiscFunctions::addUserToggles';

# Extension-specific hooks added to mediawiki code
$wgHooks['ParserDuringPreSaveTransform'][] = 'SiteMiscFunctions::preSaveTransform';  // UESP
// $wgHooks['ParserGetDefaultSort'][] = 'SiteMiscFunctions::getDefaultSort';
$wgHooks['GetDefaultSortkey'][] = 'SiteMiscFunctions::onGetDefaultSortkey';
$wgHooks['SanitizerAddHtml'][] = 'SiteMiscFunctions::sanitizerAddHtml';              // UESP
$wgHooks['SanitizerAddWhitelist'][] = 'SiteMiscFunctions::sanitizerAddWhitelist';    // UESP
$wgHooks['ParserBeforeMakeImage'][] = 'SiteMiscFunctions::addImageClear';

$wgHooks['MonoBookPageBottom'][] = 'SiteMonobook::GoogleAdBottom';                   // UESP
$wgHooks['MonoBookSearchButtonsSidebar'][] = 'SiteMonobook::SearchButtonsSidebar';

// NB 'patrolmarks' right == 'view recent changes patrol marks'
// only controls what's seen, not what's allowed
$wgHooks['MarkPatrolled'][] = 'SiteMiscFunctions::markPatrolled';
$wgHooks['FetchChangesList'][] = 'SiteMiscFunctions::fetchChangesList';
$wgHooks['userCan'][] = 'SiteMiscFunctions::userCan';
$wgAvailableRights[] = 'allspacepatrol';
$wgGroupPermissions['userpatroller']['patrol'] = true;
$wgGroupPermissions['userpatroller']['autopatrol'] = true;
$wgGroupPermissions['userpatroller']['skipcaptcha'] = true;
$wgGroupPermissions['patroller']['allspacepatrol'] = true;
$wgGroupPermissions['sysop']['allspacepatrol'] = true;
$wgGroupPermissions['bot']['allspacepatrol'] = true;
#$wgAddGroups['sysop'][] = 'userpatroller'; // Moved back to LocalSettings.php to avoid order of inclusion issues
#$wgRemoveGroups['sysop'][] = 'userpatroller';

// Blocking limitations
$egRestrictBlockLength = 21600;
$wgHooks['BlockIp'][] = 'SiteMiscFunctions::RestrictBlockHook';

$wgGroupPermissions['blockuser']['block'] = true;
#$wgAddGroups['sysop'][] = 'blockuser'; // Moved back to LocalSettings.php to avoid order of inclusion issues
#$wgRemoveGroups['sysop'][] = 'blockuser';
$wgGroupPermissions['sysop']['blocktalk'] = true;
$wgGroupPermissions['sysop']['unrestrictedblock'] = true;

# Global flag to enable/disable google ads
# (primarily for my convenience so I can disable ads without editing extension code)
$egSiteEnableGoogleAds = true;  // UESP
#$egSiteEnableGoogleAds = false; // ESPO

$wgResourceModules['ext.UespCustomCode.ad'] = array(
	'position' => 'top',
	'scripts' => array( 'modules/uespCurse.js' ),
	'styles' => array( 'modules/uespCurse.css' ),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespCustomCode/",
	'targets' => array( 'desktop', 'mobile' ),
);

$wgResourceModules['ext.UespCustomCode.app.styles'] = array(
	'position' => 'top',
	'styles' => array( 'modules/uespApp.css' ),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespCustomCode/",
	'targets' => array( 'mobile' ),
);

$wgResourceModules['ext.UespCustomCode.app.scripts'] = array(
	'position' => 'top',
	'scripts' => array( 'modules/uespApp.js' ),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespCustomCode/",
	'targets' => array( 'mobile' ),
);

/*
 * Initialization functions
 */
# This function is called as soon as setup is done
# Loads extension messages and does some other initialization that can be safely moved out of global

function efSiteCustomCode() {
	global $wgParser, $wgContLang, $wgDefaultUserOptions, $egCustomSiteID, $uespIsMobile, $wgOut, $uespIsApp;

	// Change search type so that new search class is loaded
	// To disable extension-specific search-related code (i.e., mechanics of how pages are looked up), this line could be commented out -- but some features will still be accessed by SiteSpecialSearch
	// $wgSearchType = 'SiteSearchMySQL';
	$hookoption = SFH_OBJECT_ARGS;

	// To disable the {{#sortable:}} parser function, comment out this line
	$wgParser->setFunctionHook(MAG_SITE_SORTABLE, array('SiteMiscFunctions', 'implementSortable'));
	// To disable the {{#label:}} parser function, comment out this line
	$wgParser->setFunctionHook(MAG_SITE_LABEL, array('SiteMiscFunctions', 'implementLabel'));

	// parser function versions of pagename variables
	$wgParser->setFunctionHook(MAG_SITE_CORENAME, array('SiteMiscFunctions', 'implementCorename'), SFH_NO_HASH);
	$wgParser->setFunctionHook(MAG_SITE_LABELNAME, array('SiteMiscFunctions', 'implementLabelname'), SFH_NO_HASH);
	$wgParser->setFunctionHook(MAG_SITE_SORTABLECORENAME, array('SiteMiscFunctions', 'implementSortableCorename'), SFH_NO_HASH);

	// {{#inittrail:}}, {{#settrail:}}, and {{#addtotrail:}} parser functions
	$wgParser->setFunctionHook(MAG_SITE_INITTRAIL, array('SiteBreadCrumbTrail', 'implementInitTrail'), $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_SETTRAIL, array('SiteBreadCrumbTrail', 'implementSetTrail'), $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_ADDTOTRAIL, array('SiteBreadCrumbTrail', 'implementAddToTrail'), $hookoption);

// parser function versions of Namespace variables (e.g., {{NS_FULL:SI}}, instead of just {{NS_FULL}})
	$wgParser->setFunctionHook(MAG_SITE_NS_BASE, array('SiteNamespace', 'parser_get_ns_base'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_NS_FULL, array('SiteNamespace', 'parser_get_ns_full'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_MOD_NAME, array('SiteNamespace', 'parser_get_mod_name'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_NS_ID, array('SiteNamespace', 'parser_get_ns_id'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_NS_PARENT, array('SiteNamespace', 'parser_get_ns_parent'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_NS_NAME, array('SiteNamespace', 'parser_get_ns_name'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_NS_MAINPAGE, array('SiteNamespace', 'parser_get_ns_mainpage'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_NS_CATEGORY, array('SiteNamespace', 'parser_get_ns_category'), SFH_NO_HASH | $hookoption);
	$wgParser->setFunctionHook(MAG_SITE_NS_TRAIL, array('SiteNamespace', 'parser_get_ns_trail'), SFH_NO_HASH | $hookoption);

	$prefix = strtolower($egCustomSiteID);
	# Default values for custom user preferences
	$wgDefaultUserOptions[$prefix . 'searchtitles'] = 1;   // UESP
#$wgDefaultUserOptions[$prefix.'searchtitles'] = 0;  // ESPO
	$wgDefaultUserOptions[$prefix . 'searchredirects'] = 1;
	$wgDefaultUserOptions[$prefix . 'searchtalk'] = 1;

	// while it would be nice to just have these all default to 0, it would then make
	// the user preferences page confusing
	$wgDefaultUserOptions['hideuserspace'] = 0;
	$wgDefaultUserOptions['usecustomns'] = 0;
	$wgDefaultUserOptions['userspaceunpatrolled'] = 0;
	$wgDefaultUserOptions['userspacewatchlist'] = 1;
	$wgDefaultUserOptions['userspaceownpage'] = 1;
	$wgDefaultUserOptions['userspaceownedit'] = 1;
	$wgDefaultUserOptions['userspaceanonedit'] = 0;
	$wgDefaultUserOptions['userspacewarning'] = 0;
	$wgDefaultUserOptions['userspacetalk'] = 0;
	$wgDefaultUserOptions['userspacelogs'] = 0;

	// defaults for which namespaces to display on recent changes
	foreach ($wgContLang->getNamespaces() as $ns => $name) {
		if ($ns < NS_MAIN)
			continue;
		$wgDefaultUserOptions['rcNs' . $ns] = 1;
	}
	$wgDefaultUserOptions['rcNsTalk'] = 1;
	
	if (class_exists("MobileContext"))
	{
		if (MobileContext::singleton()->isMobileDevice()) $uespIsMobile = true;
	}
	
	return true;
}


function onUespBeforeInitialize() 
{
	global $wgOut;
	
	if ($uespIsApp)
	{
		$wgOut->addModules( 'ext.UespCustomCode.app.scripts' );
		$wgOut->addModuleStyles( 'ext.UespCustomCode.app.styles' );
	}
	else
	{
		if (UESP_isShowAds()) 
		{
			$wgOut->addModules( 'ext.UespCustomCode.ad' );
		}
		else 
		{
		}
	}
	
}


function UESP_isShowAds() {
	global $wgUser;
	static $cachedUser = null;
		
	if (!$wgUser->isLoggedIn()) return true;
	
	if ($cachedUser == null) {
		$db = wfGetDB(DB_SLAVE);
	
		$res = $db->select('patreon_user', '*', ['user_id' => $wgUser->getId()]);
		
		if ($res->numRows() == 0) return true;
		
		$row = $res->fetchRow();
		
		if ($row == null) return true;
		
		$cachedUser = $row;
	}

	$hasPaid = ($cachedUser['has_donated'] > 0);
	
	//error_log("Has Donated: " . $cachedUser['has_donated']);
	
	if ($hasPaid) return false;
	return true;
}


function UESP_beforePageDisplay(&$out) {
	global $wgScriptPath;

	SetUespEsoMapSessionData();
	
	return true;
}


function SetUespEsoMapSessionData()
{
	global $_SESSION, $wgUser;
	
	$_SESSION['UESP_EsoMap_canEdit'] = false;
	
	if ($wgUser == null) return;
	
	if( $wgUser->isAllowed( 'esomapedit' ))
	{
		$_SESSION['UESP_EsoMap_canEdit'] = true;
	}
}

/**
 * SpecialPage_initList hook
 * Customize the list of special pages
 *   remove some that are pointless on site
 *   override some standard special pages with tweaked versions
 */
function efSiteSpecialPageInit(&$aSpecialPages) {
	$dir = dirname(__FILE__) . '/';
// remove unnecessary pages
	unset($aSpecialPages['Booksources']);
	unset($aSpecialPages['Withoutinterwiki']);
	unset($aSpecialPages['Mostinterwikis']);

// Override pages with customized versions
// Commmenting out individual lines will disable the customizations to that special page
// Note, however, that disabling Special:Search will probably also require a change in Wiki.php
//   (and disabling the special page will only disable some, not all, of the customizations to the search engine)
	// array values are genericclass for page, followed by the constructor arguments for that class
	// pagetype(class)=SpecialPage, $name = '', $restriction = '', $listed = true, $function = false, $file = 'default', $includable = false
	// pagetype(class)=IncludableSpecialPage, $name, $restriction = '', $listed = true, $function = false, $file = 'default'
	// all of these cases the page object itself is a SpecialPage; the customization is done via a customized Form class
	
#	$aSpecialPages['Lonelypages'] = array( 'SpecialPage', 'Lonelypages', '', true, 'efSiteSpecialLonelypages', $dir . 'SiteSpecialLonelypages.php');

	// $aSpecialPages['Search'] = array('SpecialPage', 'Search', '', true, 'efSiteSpecialSearch', $dir . 'SiteSpecialSearch.php');
	$aSpecialPages['Search'] = 'SiteSpecialSearch';
	$aSpecialPages['Preferences'] = 'SitePreferencesForm';
	$aSpecialPages['Wantedpages'] = 'SiteWantedPagesPage';	
	
	// recentchanges is different because it has its own class derived from SpecialPage
	$aSpecialPages['Recentchanges'] = 'SiteSpecialRecentChanges';
	
	$aSpecialPages['Randompage'] = 'SiteSpecialRandompage';

	return true;
}

function onSearchGetNearMatchBefore( $allSearchTerms, &$title ) {
	$title = Title::newFromText( $allSearchTerms[0] ); // Look for exact match before trying alternates
	if ( $title->exists() ) {
		return false; // false = match
	}
	
	$title = Title::newFromText( preg_replace('/\beso\b/i', 'Online', $allSearchTerms[0]) );
	if ( $title->exists() ) {
		return false; // false = match
	}
	
	$title = Title::newFromText( preg_replace('/\beso\b/i', 'online', $allSearchTerms[0]) );

	return !$title->exists();
}

function onSpecialSearchCreateLink( $t, &$params ) {
	$params[1] = preg_replace('/\((ESO) OR online\)/i', '$1', $params[1]);
	
	return true;
}


# Make sure all possible variants of an article is purged since it can be served from different URLs.
function onUespTitleSquidURLs( Title $title, array &$urls )
{
	$internalUrl = preg_replace('/(http(?:s)?:\/\/)([a-z_\-\.0-9A-Z]+)(\.uesp\.net\/.*)/i', '$1XXZZYY$3', $title->getInternalURL());
	
	$newUrl1 = str_replace("XXZZYY", "en", $internalUrl);
	$newUrl2 = str_replace("XXZZYY", "en.m", $internalUrl);
	$newUrl3 = str_replace("XXZZYY", "app", $internalUrl);
	$newUrl4 = str_replace("XXZZYY", "pt", $internalUrl);
	$newUrl5 = str_replace("XXZZYY", "pt.m", $internalUrl);
	$newUrl6 = str_replace("XXZZYY", "it", $internalUrl);
	$newUrl7 = str_replace("XXZZYY", "it.m", $internalUrl);
	$newUrl6 = str_replace("XXZZYY", "ar", $internalUrl);
	$newUrl7 = str_replace("XXZZYY", "ar.m", $internalUrl);
	
	//error_log("onUespTitleSquidURLs: $internalUrl, $newUrl1, $newUrl2, $newUrl3");
	
	$urls[] = $newUrl1;
	$urls[] = $newUrl2;
	$urls[] = $newUrl3;
	$urls[] = $newUrl4;
	$urls[] = $newUrl5;
	$urls[] = $newUrl6;
	$urls[] = $newUrl7;
}


function onUespMobileMenu($menuType, &$menu) 
{
	$items = array();
	
	if ($menuType == "personal") onUespMobilePersonalTools($items);
	if ($menuType == "discovery") onUespMinervaDiscoveryTools($items);
	
	foreach ($items as $item)
	{
		$comp = $item['components'][0];
		
		$menu->insert( $item['name'])
				->addComponent(
					$comp['text'],
					$comp['href'],
					$comp['class'],
					array($comp['data-event-name'])
				);
	}
}


function onUespMinervaDiscoveryTools (&$items)
{
	global $wgServer;
	
	$items[] = array(
			'name' => 'elderscrollsonline',
			'components' => array(
				array(
					'text' => 'ES Online',
					'href' => "$wgServer/wiki/Online:Online",
					'class' => MobileUI::iconClass( 'elderscrollsonline', 'before', 'menu-item-elderscrollsonline' ),
					'data-event-name' => 'elderscrollsonline',
				),
			),
		);
	
	$items[] = array(
			'name' => 'skyrim',
			'components' => array(
				array(
					'text' => 'Skyrim',
					'href' => "$wgServer/wiki/Skyrim:Skyrim",
					'class' => MobileUI::iconClass( 'skyrim', 'before', 'menu-item-skyrim' ),
					'data-event-name' => 'skyrim',
				),
			),
		);
	
	$items[] = array(
			'name' => 'oblivion',
			'components' => array(
				array(
					'text' => 'Oblivion',
					'href' => "$wgServer/wiki/Oblivion:Oblivion",
					'class' => MobileUI::iconClass( 'oblivion', 'before', 'menu-item-oblivion' ),
					'data-event-name' => 'oblivion',
				),
			),
		);
	
	$items[] = array(
			'name' => 'morrowind',
			'components' => array(
				array(
					'text' => 'Morrowind',
					'href' => "$wgServer/wiki/Morrowind:Morrowind",
					'class' => MobileUI::iconClass( 'morrowind', 'before', 'menu-item-morrowind' ),
					'data-event-name' => 'morrowind',
				),
			),
		);
	
	$items[] = array(
			'name' => 'othercontent',
			'components' => array(
				array(
					'text' => 'Other ES Games',
					'href' => "$wgServer/wiki/All_Content",
					'class' => MobileUI::iconClass( 'othercontent', 'before', 'menu-item-othercontent' ),
					'data-event-name' => 'othercontent',
				),
			),
		);
}


function onUespMobilePersonalTools (&$items)
{
	$items[] = array(
			'name' => 'viewdesktop',
			'components' => array(
				array(
					'text' => 'View Desktop',
					'href' => "$wgServer/wikiredirect.php",
					'class' => MobileUI::iconClass( 'viewdesktop', 'before', 'menu-item-viewdesktop' ),
					'data-event-name' => 'viewdesktop',
				),
			),
		);
}

// group pages appear under at Special:SpecialPages
// $wgSpecialPageGroups['Preferences'] = 'users';
// $wgSpecialPageGroups['Search'] = 'redirects';
// $wgSpecialPageGroups['Wantedpages'] = 'maintenance';

return true;