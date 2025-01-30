<?php

class SiteCustomCodeHooks
{
	#region Public Functions

	/*
 * Initialization functions
 */
	# This function is called as soon as setup is done
	# Loads extension messages and does some other initialization that can be safely moved out of global
	public static function efSiteCustomCode()
	{
		global $wgContLang, $wgDefaultUserOptions, $uespIsMobile;

		// defaults for which namespaces to display on recent changes
		foreach ($wgContLang->getNamespaces() as $ns => $name) {
			if ($ns < NS_MAIN)
				continue;
			$wgDefaultUserOptions['rcNs' . $ns] = 1;
		}

		if (class_exists("MobileContext") && MobileContext::singleton()->isMobileDevice()) {
			$uespIsMobile = true;

			// These hooks are here so as they are called after the same hook in MobileFrontEnd
			self::setupUespMobileHooks();
		}

		return true;
	}

	public static function efSiteMobilePrefsSpecialPageInit(&$aSpecialPages)
	{
		$aSpecialPages['Preferences'] = 'SpecialMobilePreferencesSCC';
	}

	public static function efSiteRequestContextCreateSkinMobile(MobileContext $mobileContext, Skin $skin)
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
	public static function efSiteSpecialPageInit(&$aSpecialPages)
	{
		// remove unnecessary pages
		unset($aSpecialPages['Booksources']);
		unset($aSpecialPages['Withoutinterwiki']);
		unset($aSpecialPages['Mostinterwikis']);
		$aSpecialPages['Preferences'] = (class_exists("MobileContext") && MobileContext::singleton()->isMobileDevice())
			? 'SpecialMobilePreferencesSCC'
			: 'SpecialPreferencesSCC';

		return true;
	}

	public static function onPreSaveTransformCheckUploadWizard(Parser $parser, string &$text) //Only works in 1.35+
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

	public static function onUespBeforeInitialize(&$title, $unused, $output, $user, $request, $mediaWiki)
	{
		global $uespIsApp;

		if ($uespIsApp) {
			$output->addModules('ext.UespCustomCode.app.scripts');
			$output->addModuleStyles('ext.UespCustomCode.app.styles');
		} elseif (self::UESP_isShowAds()) {
			$output->addModules('ext.UespCustomCode.analytics');

			// Old Curse Ads
			//$output->addModules( 'ext.UespCustomCode.ad' );
		}
	}

	public static function onUespMediaWikiPerformAction($output, $article, $title, $user, $request, $wiki)
	{
		$action = $request->getVal('action');
		$diff = $request->getVal('diff');

		//Block Anonymous diff requests
		if (
			$action == "" && $diff != ""
		) {
			if (!$user || $user->isAnon()) {
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

	public static function onUespMinervaDiscoveryTools(&$items)
	{
		global $wgServer;

		if (!class_exists('MobileUI')) {
			// Fail silently if MobileUI not found
			return;
		}

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

	public static function onUespMobileMenu($menuType, &$menu)
	{
		$items = array();

		if ($menuType == "personal") self::onUespMobilePersonalTools($items);
		if ($menuType == "discovery") self::onUespMinervaDiscoveryTools($items);

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

	public static function onUespMobilePersonalTools(&$items)
	{
		global $wgServer;

		if (!class_exists('MobileUI')) {
			// Fail silently if MobileUI not found
			return;
		}

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

	# Make sure all possible variants of an article is purged since it can be served from different URLs.
	public static function onUespTitleSquidURLs(Title $title, array &$urls)
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

	public static function onUespUserMailerTransformMessage(array $to, MailAddress $from, &$subject, &$headers, &$body, &$error)
	{

		// Fix issue with body hash changing which breaks DKIM verification
		// Original 8bit encoding is changed to quoted-printable at some point in the mail chain.
		$headers['Content-transfer-encoding'] = 'quoted-printable';

		$body = quoted_printable_encode($body);

		return true;
	}

	public static function setupUespMobileHooks()
	{
		// This method is called externally by config/Mobile.php, not just from efSiteCustomCode().
		global $wgHooks;
		static $hooked = false; // This function can be called multiple times, but we should only add the hooks once.

		if (!$hooked) {
			$wgHooks['SpecialPage_initList'][] = 'SiteCustomCodeHooks::efSiteMobilePrefsSpecialPageInit';
			$wgHooks['RequestContextCreateSkinMobile'][] = 'SiteCustomCodeHooks::efSiteRequestContextCreateSkinMobile';
			$hooked = true;
		}
	}

	public static function UESP_beforePageDisplay(&$out)
	{
		self::SetupUespFavIcons($out);
		self::SetupUespLongitudeAds($out);
		self::SetupUespTwitchEmbed($out);

		return true;
	}

	public static function uespMobileAddTopAdDiv(&$out, &$text)
	{
		global $uespIsMobile;
		static $hasAddedDiv = false;

		if ($hasAddedDiv) return true;
		if (!$uespIsMobile) return true;

		$text = "<div id='uesp_M_1'></div>" . $text;

		$hasAddedDiv = true;
		return true;
	}

	#endregion

	#region Private Functions
	private static function SetupUespFavIcons(&$out)
	{
		$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-16.png',  'sizes' => '16x16'));
		$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-32.png',  'sizes' => '32x32'));
		$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-48.png',  'sizes' => '48x48'));
		$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-64.png',  'sizes' => '64x64'));
		$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-96.png',  'sizes' => '96x96'));
		$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-128.png', 'sizes' => '128x128'));
		$out->addLink(array('rel' => 'icon', 'type' => 'image/png', 'href' => 'https://images.uesp.net/favicon-256.png', 'sizes' => '256x256'));
	}

	private static function SetupUespLongitudeAds(&$out)
	{
		if (self::UESP_isShowAds()) {
			$out->addInlineScript("var uesptopad = document.getElementById('topad'); if (uesptopad) uesptopad.style = 'height:90px;'; ");
			$out->addScriptFile('https://lngtd.com/uesp.js');
		} else {
			$out->addInlineScript("var uesptopad = document.getElementById('topad'); if (uesptopad) uesptopad.style = 'display:none;'; ");
		}
	}

	private static function SetupUespTwitchEmbed(&$out)
	{
		$out->addScriptFile('https://player.twitch.tv/js/embed/v1.js');
	}

	private static function UESP_isShowAds()
	{
		global $wgUser;
		static $cachedUser = null;

		if (!$wgUser->isLoggedIn()) return true;

		if ($cachedUser == null) {
			$db = wfGetDB(DB_REPLICA);

			try {
				$res = $db->select('patreon_user', '*', ['wikiuser_id' => $wgUser->getId()]);
			} catch (Exception $e) {
				return true;
			}

			if (
				$res->numRows() == 0
			) return true;

			$row = $res->fetchRow();
			if ($row == null) return true;

			$cachedUser = $row;
		}

		$hasPaid = ($cachedUser['lifetimePledgeCents'] > 0);
		//error_log("Has Donated: " . $hasPaid);

		return !$hasPaid;
	}
	#endregion
}
