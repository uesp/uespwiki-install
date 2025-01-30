<?php

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

$egCustomSiteID = $wgSitename == 'UESPWiki'
	? 'Uesp'
	: 'Site';

$wgExtensionCredits['other'][] = array(
	'name' => $egCustomSiteID . 'CustomCode',
	'author' => 'Nephele',
	'url' => '//www.uesp.net/wiki/UESPWiki:UespCustomCode',
	'description' => 'Various code customizations that provide functionality specific to this site',
	'version' => '0.9.7',
);

# ParserFirstCallInit does not work correctly in all versions of MediaWiki
# Function is called too late so use below line all the time.
$wgExtensionFunctions[] = 'efSiteCustomCode';

$dir = dirname(__FILE__) . '/';

# Tell Mediawiki where to find files containing extension-specific classes
$wgAutoloadClasses['SiteMiscFunctions'] = $dir . 'SiteCustomCode_body.php';
$wgAutoloadClasses['SiteSpecialRecentChanges'] = $dir . 'SiteSpecialRecentchanges.php';
$wgAutoloadClasses['SiteOldChangesList'] = $dir . 'SiteChangesList.php';
$wgAutoloadClasses['SiteEnhancedChangesList'] = $dir . 'SiteChangesList.php';
$wgAutoloadClasses['SiteSpecialRandompage'] = $dir . 'SiteSpecialRandompage.php';
$wgAutoloadClasses['UespWebpHandler'] = $dir . 'UespWebpHandler.php';
$wgAutoloadClasses['SiteSpecialMobilePreferences'] = $dir . 'SpecialMobilePreferences.php';

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

$wgHooks['UserMailerTransformMessage'][] = 'onUespUserMailerTransformMessage';

/* Mobile Specific */
$wgHooks['MinervaDiscoveryTools'][] = 'onUespMinervaDiscoveryTools';
$wgHooks['MobilePersonalTools'][] = 'onUespMobilePersonalTools';
$wgHooks['MobileMenu'][] = 'onUespMobileMenu';
$wgHooks['OutputPageBeforeHTML'][] = 'uespMobileAddTopAdDiv';

$wgHooks['BeforeInitialize'][] = 'onUespBeforeInitialize';
$wgHooks['ParserPreSaveTransformComplete'][] = 'onPreSaveTransformCheckUploadWizard'; //Only works in 1.35+

$wgHooks['MediaWikiPerformAction'][] = 'onUespMediaWikiPerformAction';

# Load messages

$wgExtensionMessagesFiles['sitecustomcode'] = $dir . 'SiteCustomCode.i18n.php';
$wgExtensionMessagesFiles['sitecustomcodeAlias'] = $dir . 'SiteCustomCode.alias.php';

# Hook for cacheable check: used to tell code not to cache category pages
$wgHooks['IsFileCacheable'][] = 'SiteMiscFunctions::isFileCacheable';

# Hooks for creating bread crumb trail and inserting trail where subpage normally appears
# To disable these features, change <site>'settrail' to 0 (system message)
# $wgHooks['OutputPageParserOutput'][] = 'SiteBreadCrumbTrail::getCachedTrail';
# $wgHooks['SkinSubPageSubtitle'][] = 'SiteBreadCrumbTrail::subpageHook';


# Hook to add a rel=canonical tag to the header
$wgHooks['OutputPageParserOutput'][] = 'SiteMiscFunctions::addCanonicalToHeader';

# Alternate way to deal with links
# Hook for adding search-related options to user preferences
$wgHooks['UserToggles'][] = 'SiteMiscFunctions::addUserToggles';

# Extension-specific hooks added to mediawiki code
$wgHooks['GetDefaultSortkey'][] = 'SiteMiscFunctions::onGetDefaultSortkey';
$wgHooks['SanitizerAddHtml'][] = 'SiteMiscFunctions::sanitizerAddHtml';              // UESP
$wgHooks['SanitizerAddWhitelist'][] = 'SiteMiscFunctions::sanitizerAddWhitelist';    // UESP
$wgHooks['ParserBeforeMakeImage'][] = 'SiteMiscFunctions::addImageClear';

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

$wgResourceModules['ext.UespCustomCode.ad'] = array(
	'position' => 'top',
	'scripts' => array('modules/uespCurse.js'),
	'styles' => array('modules/uespCurse.css'),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespCustomCode/",
	'targets' => array('desktop', 'mobile'),
);

$wgResourceModules['ext.UespCustomCode.app.styles'] = array(
	'position' => 'top',
	'styles' => array('modules/uespApp.css'),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespCustomCode/",
	'targets' => array('mobile'),
);

$wgResourceModules['ext.UespCustomCode.app.scripts'] = array(
	'position' => 'top',
	'scripts' => array('modules/uespApp.js'),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespCustomCode/",
	'targets' => array('mobile'),
);

$wgResourceModules['ext.UespCustomCode.analytics'] = array(
	'position' => 'top',
	'scripts' => array('modules/uespGoogleAnalytics.js'),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespCustomCode/",
	'targets' => array('desktop', 'mobile'),
);

/*
 * Initialization functions
 */
# This function is called as soon as setup is done
# Loads extension messages and does some other initialization that can be safely moved out of global

function efSiteCustomCode()
{
	global $wgContLang, $wgDefaultUserOptions, $egCustomSiteID, $uespIsMobile, $wgOut, $uespIsApp;

	// Change search type so that new search class is loaded
	// To disable extension-specific search-related code (i.e., mechanics of how pages are looked up), this line could be commented out -- but some features will still be accessed by SiteSpecialSearch

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

	if (class_exists("MobileContext")) {
		if (MobileContext::singleton()->isMobileDevice()) {
			$uespIsMobile = true;

			// These hooks are here so as they are called after the same hook in MobileFrontEnd
			setupUespMobileHooks();
		}
	}

	return true;
}


function setupUespMobileHooks()
{
	global $wgHooks;

	$wgHooks['SpecialPage_initList'][] = 'efSiteMobilePrefsSpecialPageInit';
	$wgHooks['RequestContextCreateSkinMobile'][] = 'efSiteRequestContextCreateSkinMobile';
}


function onUespBeforeInitialize()
{
	global $wgOut;
	global $uespIsApp;

	if ($uespIsApp) {
		$wgOut->addModules('ext.UespCustomCode.app.scripts');
		$wgOut->addModuleStyles('ext.UespCustomCode.app.styles');
	} else {
		if (UESP_isShowAds()) {
			$wgOut->addModules('ext.UespCustomCode.analytics');

			// Old Curse Ads
			//$wgOut->addModules( 'ext.UespCustomCode.ad' );
		} else {
		}
	}
}


function UESP_isShowAds()
{
	global $wgUser;
	static $cachedUser = null;

	if (!$wgUser->isLoggedIn()) return true;

	if ($cachedUser == null) {
		$db = wfGetDB(DB_SLAVE);

		try {
			$res = $db->select('patreon_user', '*', ['wikiuser_id' => $wgUser->getId()]);
		} catch (Exception $e) {
			return true;
		}

		if ($res->numRows() == 0) return true;

		$row = $res->fetchRow();
		if ($row == null) return true;

		$cachedUser = $row;
	}

	$hasPaid = ($cachedUser['lifetimePledgeCents'] > 0);
	//error_log("Has Donated: " . $hasPaid);

	return !$hasPaid;
}


function UESP_beforePageDisplay(&$out)
{
	SetupUespFavIcons($out);

	SetupUespLongitudeAds($out);
	SetupUespTwitchEmbed($out);

	return true;
}


function SetupUespFavIcons(&$out)
{
	$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-16.png',  'sizes' => '16x16'));
	$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-32.png',  'sizes' => '32x32'));
	$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-48.png',  'sizes' => '48x48'));
	$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-64.png',  'sizes' => '64x64'));
	$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-96.png',  'sizes' => '96x96'));
	$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-128.png', 'sizes' => '128x128'));
	$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-256.png', 'sizes' => '256x256'));
}


function SetupUespLongitudeAds(&$out)
{
	if (UESP_isShowAds()) {
		$out->addInlineScript("var uesptopad = document.getElementById('topad'); if (uesptopad) uesptopad.style = 'height:90px;'; ");
		$out->addScriptFile('https://lngtd.com/uesp.js');
	} else {
		$out->addInlineScript("var uesptopad = document.getElementById('topad'); if (uesptopad) uesptopad.style = 'display:none;'; ");
	}
}


function SetupUespTwitchEmbed(&$out)
{

	$out->addScriptFile('https://player.twitch.tv/js/embed/v1.js');
}


function SetUespMapSessionData()
{
	//Note: This is no longer needed/used

	global $_SESSION, $wgUser;

	// TODO: Unsure when we have to migrate to the new session object (should have been 1.27/28)
	//$session = \MediaWiki\Session\SessionManager::getGlobalSession();

	$_SESSION['UESP_AllMap_canEdit'] = 0;
	$_SESSION['UESP_EsoMap_canEdit'] = 0;
	$_SESSION['UESP_TRMap_canEdit'] = 0;
	$_SESSION['UESP_OtherMap_canEdit'] = 0;

	//$session->set('UESP_AllMap_canEdit', 0);
	//$session->set('UESP_EsoMap_canEdit', 0);
	//$session->set('UESP_TRMap_canEdit', 0);
	//$session->set('UESP_OtherMap_canEdit', 0);

	if ($wgUser == null) return;

	if ($wgUser->isAllowed('esomapedit')) {
		$_SESSION['UESP_EsoMap_canEdit'] = 1;
		//$session->set('UESP_EsoMap_canEdit', 1);
	}

	if ($wgUser->isAllowed('mapedit') || $wgUser->isAllowed('othermapedit')) {
		$_SESSION['UESP_OtherMap_canEdit'] = 1;
		//$session->set('UESP_OtherMap_canEdit', 1);
	}

	if ($wgUser->isAllowed('trmapedit')) {
		$_SESSION['UESP_TRMap_canEdit'] = 1;
		//$session->set('UESP_TRMap_canEdit', 1);
	}
}


function efSiteMobilePrefsSpecialPageInit(&$aSpecialPages)
{
	$aSpecialPages['Preferences'] = 'SiteSpecialMobilePreferences';
}


function efSiteRequestContextCreateSkinMobile(MobileContext $mobileContext, Skin $skin)
{

	if ($skin instanceof SkinMinerva) {

		// Turn off the use of the Special:MobileOptions page for preferences
		$skin->setSkinOptions([
			SkinMinerva::OPTION_MOBILE_OPTIONS => false,
		]);
	}
}


/**
 * SpecialPage_initList hook
 * Customize the list of special pages
 *   remove some that are pointless on site
 *   override some standard special pages with tweaked versions
 */
function efSiteSpecialPageInit(&$aSpecialPages)
{

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

	// $aSpecialPages['Search'] = array('SpecialPage', 'Search', '', true, 'efSiteSpecialSearch', $dir . 'SiteSpecialSearch.php');
	$aSpecialPages['Search'] = 'SiteSpecialSearch';
	$aSpecialPages['Preferences'] = 'SitePreferencesForm';
	$aSpecialPages['Wantedpages'] = 'SiteWantedPagesPage';

	// recentchanges is different because it has its own class derived from SpecialPage
	$aSpecialPages['Recentchanges'] = 'SiteSpecialRecentChanges';

	$aSpecialPages['Randompage'] = 'SiteSpecialRandompage';

	return true;
}

function onSearchGetNearMatchBefore($allSearchTerms, &$title)
{
	$title = Title::newFromText($allSearchTerms[0]); // Look for exact match before trying alternates
	if ($title->exists()) {
		return false; // false = match
	}

	$title = Title::newFromText(preg_replace('/\beso\b/i', 'Online', $allSearchTerms[0]));
	if ($title->exists()) {
		return false; // false = match
	}

	$title = Title::newFromText(preg_replace('/\beso\b/i', 'online', $allSearchTerms[0]));

	return !$title->exists();
}


function onSpecialSearchCreateLink($t, &$params)
{
	$params[1] = preg_replace('/\((ESO) OR online\)/i', '$1', $params[1]);

	return true;
}


function onUespUserMailerTransformMessage(array $to, MailAddress $from, &$subject, &$headers, &$body, &$error)
{

	// Fix issue with body hash changing which breaks DKIM verification
	// Original 8bit encoding is changed to quoted-printable at some point in the mail chain.
	$headers['Content-transfer-encoding'] = 'quoted-printable';

	$body = quoted_printable_encode($body);

	return true;
}


# Make sure all possible variants of an article is purged since it can be served from different URLs.
function onUespTitleSquidURLs(Title $title, array &$urls)
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

	foreach ($items as $item) {
		$comp = $item['components'][0];

		$menu->insert($item['name'])
			->addComponent(
				$comp['text'],
				$comp['href'],
				$comp['class'],
				array($comp['data-event-name'])
			);
	}
}


function onUespMinervaDiscoveryTools(&$items)
{
	global $wgServer;

	$items[] = array(
		'name' => 'elderscrollsonline',
		'components' => array(
			array(
				'text' => 'ES Online',
				'href' => "$wgServer/wiki/Online:Online",
				'class' => MobileUI::iconClass('elderscrollsonline', 'before', 'menu-item-elderscrollsonline'),
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
				'class' => MobileUI::iconClass('skyrim', 'before', 'menu-item-skyrim'),
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
				'class' => MobileUI::iconClass('oblivion', 'before', 'menu-item-oblivion'),
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
				'class' => MobileUI::iconClass('morrowind', 'before', 'menu-item-morrowind'),
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
				'class' => MobileUI::iconClass('othercontent', 'before', 'menu-item-othercontent'),
				'data-event-name' => 'othercontent',
			),
		),
	);
}


function onUespMobilePersonalTools(&$items)
{
	global $wgServer;

	$items[] = array(
		'name' => 'viewdesktop',
		'components' => array(
			array(
				'text' => 'View Desktop',
				'href' => "$wgServer/wikiredirect.php",
				'class' => MobileUI::iconClass('viewdesktop', 'before', 'menu-item-viewdesktop'),
				'data-event-name' => 'viewdesktop',
			),
		),
	);
}


function uespMobileAddTopAdDiv(&$out, &$text)
{
	global $uespIsMobile;
	static $hasAddedDiv = false;

	if ($hasAddedDiv) return true;
	if (!$uespIsMobile) return true;

	$text = "<div id='uesp_M_1'></div>" . $text;

	$hasAddedDiv = true;
	return true;
}


function onPreSaveTransformCheckUploadWizard(Parser $parser, string &$text)
{
	$result = preg_match('/=={{int:filedesc}}==
{{Information
\|description=(.*)
\|date=(.*)
\|source=(.*)
\|author=(.*)
\|permission=(.*)
\|other versions=(.*)
}}

=={{int:license-header}}==
{{(.*)}}
*(.*)/', $text, $matches);
	if (!$result) return;

	$description = $matches[1];

	if (preg_match('/{{([a-zA-Z0-9_-])+\|1=(.*)}}/', $description, $descMatches)) {
		$description = $descMatches[2];
	}

	$date = $matches[2];
	$source = $matches[3];
	$author = $matches[4];
	$permission = $matches[5];
	$otherVersions = $matches[6];
	$license = $matches[7];
	$license = str_replace("self|", "", $license);
	$extra = $matches[8];

	$text = "== Summary ==
$description

== Licensing ==
{{{$license}}}

$extra";
}


function onUespMediaWikiPerformAction( $output, $article, $title, $user, $request, $wiki )
{
	$action = $request->getVal('action');
	$diff = $request->getVal('diff');
	
		//Block Anonymous diff requests
	if ($action == "" && $diff != "")
	{
		if (!$user || $user->isAnon())
		{
			$titleText = "?";
			if ($title) $titleText = $title->getPrefixedText();
			error_log("Blocked Anonymous Diff Request on $titleText : action=$action : diff=$diff");
			
				//Difference between revisions of "User:Daveh/ESO Update"
			$output->addHTML("<h1 id=\"firstHeading\" class=\"firstHeading\" lang=\"en\">Difference between revisions of \"$titleText\"</h1>");
			$output->addHTML("Article diff output is disabled for anonymous users. Please <a href='/wiki/Special:UserLogin'>Login</a> to view.");
			return false;
		}
	}
	
	return true;
}


// group pages appear under at Special:SpecialPages
// $wgSpecialPageGroups['Preferences'] = 'users';
// $wgSpecialPageGroups['Search'] = 'redirects';
// $wgSpecialPageGroups['Wantedpages'] = 'maintenance';

return true;
