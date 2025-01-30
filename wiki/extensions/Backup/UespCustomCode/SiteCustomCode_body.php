<?php
/* Miscellaneous functions related to SiteCustomCode
 * This file is always loaded
 *
 * Basically, this is all of the functions that:
 * - are not part of a class, or directly tied to a single class
 * - cannot otherwise be triggered to only load when necessary
 * - are not initialization functions that can be used to enable/disable which features are active
 *
 * For many of the functions in this file, simply adding "return;" as the first line of the function
 * will effectively disable the function, and thus disable the related customization.  Cases where
 * this is NOT recommended are noted below for the relevant functions
 */

// All of the functions are public static functions; moved into a class to avoid cluttering the code
// namespace and also to allow class to be auto-loaded insted of being loaded every time.
// It would possibly be more logical to divide this mega-class into two or three classes, each
// only containing related functions.  However, there's no advantage to doing so (unless code is
// is split into more files).
class SiteMiscFunctions
{
	/*
	 * Functions called from Sanitizer.php
	 * These functions all need to be manually added to Sanitizer.php
	 * Adding "return;" at the start of each of the functions will safely disable the customization
	 */

	// add in removeHTMLtags, within (!$staticInitialized) block, before conversion to hashtables
	public static function sanitizerAddHtml(&$htmlpairs)
	{
		foreach (array('dfn', 'q', 'kbd', 'abbr', 'acronym') as $extraval) {
			if (!in_array($extraval, $htmlpairs)) {
				$htmlpairs[] = $extraval;
			}
		}
		return true;
	}

	// add in setupAttributeWhitelist, before end-of-function return
	public static function sanitizerAddWhitelist(&$whitelist, $common)
	{
		foreach (array('dfn', 'q', 'kbd', 'abbr', 'acronym') as $extraval) {
			if (!array_key_exists($extraval, $whitelist))
				$whitelist[$extraval] = $common;
		}
		return true;
	}

	public static function addImageClear(&$parser, &$title, &$options, &$holders = false, &$imagelink)
	{
		if (!preg_match('/(?:clear|class)\s*[:=]/i', $options))
			return true;

		$clearparam = NULL;
		$classparams = array();
		$sections = explode('|', $options);
		foreach ($sections as $i => $section) {
			// limit matchable characters to \w\s [a-z0-9_ ] to prevent possibility that this could allow any
			// malicious code through into HTML
			if (!preg_match('/^\s*(clear|class)\s*[:=]\s*([\w\s]*)\s*$/is', $section, $matches))
				continue;
			if (strtolower($matches[1]) == 'class') {
				$classparams[] = $matches[2];
			} else {
				$clearparam = 'clear:' . $matches[2];
			}
			unset($sections[$i]);
		}
		if (empty($clearparam) && empty($classparams))
			return true;

		$options = implode('|', $sections);
		$imagelink = $parser->makeImage($title, $options, $holders);

		if (preg_match('/^(<div.*?)(>.*)/is', $imagelink, $matches)) {
			$firsttag = $matches[1];
			$imagelink = $matches[2];
			if (!empty($classparams)) {
				if (preg_match('/class="/i', $firsttag)) {
					$firsttag = preg_replace('/(class="[^"]*)"/is', '$1 ' . implode(' ', $classparams) . '"', $firsttag);
				} else {
					$firsttag .= ' class="' . implode(' ', $classparams) . '"';
				}
			}
			if (!empty($clearparam)) {
				if (preg_match('/style="/i', $firsttag)) {
					$firsttag = preg_replace('/(style="[^"]*)"/is', '$1;' . $clearparam . ';"', $firsttag);
				} else {
					$firsttag .= ' style="' . $clearparam . ';"';
				}
			}
			$imagelink = $firsttag . $imagelink;
		}
		return false;
	}

	/* Cacheable files
	 * Mark all category pages as uncacheable
	 * Hopefully this will fix problems with prev/next on large categories such as Oblivion-Quests
	 */
	public static function isFileCacheable($article)
	{
		if ($article->mTitle->getNamespace() == NS_CATEGORY)
			return false;
		return true;
	}

	/* Add toggles to user preferences
		Although a lot of the toggle display is manually changed in SpecialPreferences,
		adding the toggles here takes care of various bookkeeping
	*/
	public static function addUserToggles(&$toggles)
	{
		global $egCustomSiteID;
		$prefix = strtolower($egCustomSiteID);

		// options related to searching
		$toggles[] = $prefix . 'searchtitles';
		$toggles[] = $prefix . 'searchredirects';
		$toggles[] = $prefix . 'searchtalk';

		// options related to recentchanges
		$toggles[] = 'hideuserspace';
		$toggles[] = 'usecustomns';
		$toggles[] = 'userspaceunpatrolled';
		$toggles[] = 'userspacewatchlist';
		$toggles[] = 'userspaceownpage';
		$toggles[] = 'userspaceownedit';
		$toggles[] = 'userspaceanonedit';
		$toggles[] = 'userspacewarning';
		$toggles[] = 'userspacetalk';
		$toggles[] = 'userspacelogs';

		return true;
	}

	/**
	 * Overrides default sort key using site's custom namespace logic.
	 *
	 * @param Title $title
	 * @param string $sortkey
	 * @todo In theory, if the namespace supports subpages, this should break the entire title into sections and run
	 * the regex on each one individually, but that's an edge-case...is it worth the extra processing?
	 *
	 */
	public static function onGetDefaultSortkey($title, &$sortkey)
	{
		// If class doesn't exist, the existing sortkey remains unchanged.
		if (class_exists('NSInfo')) {
			$nsUesp = NSInfo::nsFromTitle($title);
			$nsFull = $nsUesp->getFull();
			$name = substr($title->getPrefixedText(), strlen($nsFull));

			// Uses Riven's language-specific pattern if available, otherwise a default English pattern.
			$message = wfMessage('riven-sortable-pattern');
			$regex = $message->exists()
				? $message->inContentLanguage()->text()
				: '^\s*(A|An|The)\s+(.*)';
			$sortkey = preg_match("/$regex/", $name, $matches)
				? $matches[2] . ', ' . $matches[1]
				: $name;
		}
	}

	public static function markPatrolled($rcid, $user, $wcOnlySysopsCanPatrol)
	{
		//var_dump($user->getName() == $this->getAttribute( 'rc_user_text' ) && !$user->isAllowed( 'autopatrol' ));

		$rc = RecentChange::newFromId($rcid);
		$ns = $rc->getAttribute('rc_namespace');
		// if it is a userspace, then patrolling must be OK
		if ($ns == NS_USER || $ns == (NS_USER + 1))
			return true;

		if ($user->isAllowed('allspacepatrol'))
			return true;

		global $wgOut;
		$wgOut->setPageTitle(wfMessage('markedaspatrollederror')->text());
		$wgOut->addWikiMsg('markedaspatrollederror-nonuserspace');
		$wgOut->returnToMain(false);
		return false;
	}

	public static function fetchChangesList($user, $skin, &$list)
	{
		$list = $user->getOption('usenewrc') ?
			new SiteEnhancedChangesList($skin) : new SiteOldChangesList($skin);
		return false;
	}

	public static function userCan(&$title, &$user, $action, &$result)
	{
		if ($action == 'patrol' || $action == 'autopatrol') {
			$ns = MWNamespace::getSubject($title->getNamespace());
			// I'm only worrying about the extra condition that I'm adding ...
			// other processing should already be handling the rest of the patrol options
			if ($ns !== NS_USER && !$user->isAllowed('allspacepatrol')) {
				$result = false;
				return false;
			}
		}
		return true;
	}

	// Code for restricted blocking, written by [http://www.mediawiki.org/wiki/User:Nx Nx] as part of RestrictBlock extension, moved into UespCustomCode for simplicity
	// intercept blocks
	public static function RestrictBlockHook(&$ban, &$user)
	{
		//check if the ban disables talk page editing
		global $wgBlockAllowsUTEdit, $egRestrictBlockLength;

		if ($wgBlockAllowsUTEdit && !$ban->mAllowUsertalk && !$user->isAllowed('blocktalk')) {
			return wfMessage('restrictblock-denied-utalk')->text();
		}
		//check for block length
		if (!$user->isAllowed('unrestrictedblock')) {
			//infinity is right out
			if ($ban->mExpiry === 'infinity') {
				return wfMessage('restrictblock-denied', $egRestrictBlockLength)->text();
			}
			$timediff = (wfTimestamp(TS_UNIX, $ban->mExpiry) - time());
			if ($timediff > $egRestrictBlockLength) {
				return wfMessage('restrictblock-denied', $egRestrictBlockLength)->text();
			}
		}
		return true;
	}

	// Add some extra tags to all page headers to make search engines handle multiple servers better
	static function addCanonicalToHeader(&$out, $parserout)
	{
		// wgTitle contains post-redirect article title, not URL title
		// global $wgTitle;
		global $wgLanguageCode;
		// $url = $wgTitle->getLocalURL();
		if (!$_SERVER['QUERY_STRING']) {
			$url = $_SERVER['PHP_SELF'];

			// Note that this is no longer used and is handled by $wgCanonicalServer.
			//$out->addHeadItem('canonical', "\t\t<link rel=\"canonical\" href=\"https://{$wgLanguageCode}.uesp.net{$url}\" />\n");

			$out->addHeadItem('canonical-alternate', "\t\t<link rel=\"alternate\" media=\"only screen and (max-width: 640px)\"  href=\"https://{$wgLanguageCode}.m.uesp.net{$url}\" />\n");
		}
		return true;
	}
}
