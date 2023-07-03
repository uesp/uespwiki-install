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
	protected static $_lastright;
	/*
 * Functions for Magic words and parser functions
 */

	// This is the best place to disable individual magic words;
	// To disable all magic words, disable the hook that calls this function
	public static function declareMagicWords(&$aCustomVariableIds)
	{
		global $egSiteNamespaceMagicWords, $egSiteOtherMagicWords;
		foreach (array_merge($egSiteNamespaceMagicWords, $egSiteOtherMagicWords) as $magicword => $case) {
			$aCustomVariableIds[] = $magicword;
		}
		return true;
	}

	// Commenting out lines here will also disable the related magic word (but generally
	// requires commenting out more than one line)
	public static function assignMagicWords(&$parser, &$cache, &$magicWordId, &$ret, &$frame = NULL)
	{
		global $egSiteNamespaceMagicWords;
		if (array_key_exists($magicWordId, $egSiteNamespaceMagicWords)) {
			$ret = SiteNamespace::find_nsobj($parser, $frame)->get(strtolower($magicWordId));
		} elseif ($magicWordId == MAG_SITE_CORENAME) {
			$ret = self::implementCorename($parser);
		} elseif ($magicWordId == MAG_SITE_LABELNAME) {
			$ret = self::implementLabelname($parser);
		} elseif ($magicWordId == MAG_SITE_SORTABLECORENAME) {
			$ret = self::implementSortableCorename($parser);
		}
		return true;
	}

	// Implementation of CORENAME magic word (also used by SORTABLECORENAME and LABELNAME magic words)
	public static function implementCorename(&$parser, $page_title = NULL)
	{
		if (is_null($page_title) && is_object($parser))
			$page_title = $parser->getTitle();
		if (is_object($page_title))
			$page_title = $page_title->getText();
		$sections = explode('/', $page_title);
		if (count($sections) == 1)
			return $sections[0];

		$last = $sections[count($sections) - 1];
		if ($last == 'Description' || $last == 'Author' || $last == 'Desc' || $last == 'Directions')
			array_pop($sections);
		return $sections[count($sections) - 1];
	}

	// Implementation of SORTABLECORENAME magic word
	public static function implementSortableCorename(&$parser, $page_title = NULL)
	{
		$corename = self::implementCorename($parser, $page_title);
		return self::doSortable($corename);
	}

	// Implementation of LABELNAME magic word
	public static function implementLabelname(&$parser, $page_title = NULL)
	{
		$corename = self::implementCorename($parser, $page_title);
		return self::doLabel($corename);
	}

	// Implementation of {{#sortable}} parser function
	public static function implementSortable(&$parser, $pagename = '')
	{
		return self::doSortable($pagename);
	}

	// Used by both SORTABLECORENAME and {{#sortable}}
	public static function doSortable($page_title = '')
	{
		if (preg_match('/^\s*(A|An|The)\s+(.*)/', $page_title, $matches)) {
			return $matches[2] . ", " . $matches[1];
		} else {
			return $page_title;
		}
	}

	// Implementation of {{#label}} parser function
	public static function implementLabel(&$parser, $pagename = '')
	{
		return self::doLabel($pagename);
	}

	public static function doLabel($pagename = '')
	{
		$text = preg_replace('/\s*\([^\)]+\)\s*$/', '', $pagename);
		return $text;
	}

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

	/*
 * Function called from Parser.php
 * This function needs to be manually added to Parser.php
 * Adding "return;" at the start of the function will safely disable the customization
 */

	// function needs to be manually added to Parser.php:pstPass2, right before wiki code for context links
	public static function preSaveTransform(&$parser, &$text, $title = NULL)
	{
		/*
 * This customization is based on the fact that UESP keeps almost no content
 * in the main namespace. Therefore, links written as if they were main namespace links are
 * converted into links to pages within the current namespace:
 *    [[Creatures]] is changed to [[$namespace:Creatures|]]
 *    [[Talk:Creatures|talk]] is changed to [[$talkspace:Creatures|talk]]
 */
		global $wgLegalTitleChars, $wgContLang;
		if (is_null($title))
			$title = $parser->getTitle();

		# set names of talkspace and subjectspace based on current page's title
		$talkspace = $wgContLang->getNsText(MWNamespace::getTalk($title->mNamespace));
		$subjectspace = $wgContLang->getNsText(MWNamespace::getSubject($title->mNamespace));

		# list of valid characters for title, WITHOUT ":" which is used here to check
		# whether the existing link text uses a namespace or not
		# wgLegalTitleChars does NOT include []{}|# (which is what we want by default)
		# NB unlike $tc and $nc this does param does NOT have the enclosing braces
		$tcn = str_replace(':', '', $wgLegalTitleChars);
		# first character should not be / (# already excluded)
		$tcn0 = str_replace('\/', '', $tcn);
		#$tcn0 = " _.A-Za-z0-9";

		$q1 = "/\[\[\s*[Tt][Aa][Ll][Kk]\s*:([{$wgLegalTitleChars}#\\|]+?)]]/";  # [[talk:title]]
		$q2 = "/\[\[([{$tcn0}][{$tcn}]*?)]]/";	       # [[title]]
		$q3 = "/\[\[([{$tcn0}][{$tcn}#\\|]*?)]]/";      # [[title#anchor]], [[title|label]], or [[title#anchor|label]], but NOT [[#anchor]]

		# [[talk:A]] into [[$talkspace:A]] (never add | to the end in the talk case)
		$text = preg_replace($q1, '[[' . $talkspace . ':\\1]]', $text);
		# [[A]] into [[$subjectspace:A|]]: adding final | to make link label identical
		# to original link's label
		$text = preg_replace($q2, '[[' . $subjectspace . ':\\1|]]', $text);
		# [[A#B]] into [[$subjectspace:A#B]] or [[A|B]] into [[$subjectspace:A|B]]
		$text = preg_replace($q3, '[[' . $subjectspace . ':\\1]]', $text);

		# standard wikipedia code will effectively try to redo the following transformation
		# (but won't accomplish anything because there will no longer be any matches)
		# doing it here to change some details of the transformation
		# specifically, want links of the form [[ns:page, context|]] to be transformed into
		#  [[ns:page, context|page, context]] instead of [[ns:page, context|page]]
		$tc = "[$wgLegalTitleChars]";
		$nc = '[ _0-9A-Za-z\x80-\xff]'; # Namespaces can use non-ascii!
		$p1 = "/\[\[(:?$nc+:|:|)($tc+?)( \\($tc+\\)|)\\|]]/";           # [[ns:page (context)|]]
		# turn "[[A (B)|]]" into "[[A (B)|A]]"
		$text = preg_replace($p1, '[[\\1\\2\\3|\\2]]', $text);

		return true;
	}

	public static function parseLinks(&$parser, &$text)
	{
		global $wgLegalTitleChars, $wgContLang;
		$title = $parser->getTitle();
		# set names of talkspace and subjectspace based on current page's title
		$talkspace = $wgContLang->getNsText(MWNamespace::getTalk($title->mNamespace));
		$subjectspace = $wgContLang->getNsText(MWNamespace::getSubject($title->mNamespace));

		# list of valid characters for title, WITHOUT ":" which is used here to check
		# whether the existing link text uses a namespace or not
		# wgLegalTitleChars does NOT include []{}|# (which is what we want by default)
		# NB unlike $tc and $nc this does param does NOT have the enclosing braces
		$tcn = str_replace(':', '', $wgLegalTitleChars);
		# first character should not be / (# already excluded)
		$tcn0 = str_replace('\/', '', $tcn);
		#$tcn0 = " _.A-Za-z0-9";

		$q1 = "/\[\[\s*[Tt][Aa][Ll][Kk]\s*:([{$wgLegalTitleChars}#\\|]+?)]]/";  # [[talk:title]]
		$q2 = "/\[\[([{$tcn0}][{$tcn}]*?)]]/";	       # [[title]]
		$q3 = "/\[\[([{$tcn0}][{$tcn}#\\|]*?)]]/";      # [[title#anchor]], [[title|label]], or [[title#anchor|label]], but NOT [[#anchor]]

		# [[talk:A]] into [[$talkspace:A]] (never add | to the end in the talk case)
		$text = preg_replace($q1, '[[' . $talkspace . ':\\1]]', $text);
		# [[A]] into [[$subjectspace:A|A]] (should tweak label at some point)
		$text = preg_replace($q2, '[[' . $subjectspace . ':\\1|\\1]]', $text);
		# [[A#B]] into [[$subjectspace:A#B]] or [[A|B]] into [[$subjectspace:A|B]]
		$text = preg_replace($q3, '[[' . $subjectspace . ':\\1]]', $text);

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
   Mark all category pages as uncacheable
   Hopefully this will fix problems with prev/next on large categories such as Oblivion-Quests
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

	/* Pre-MW-1.19
	public static function getDefaultSort(&$parser, &$defaultSort) {
		$defaultSort = self::implementSortableCorename( $parser->getTitle()->getText() );
		return true;
	}
*/

	public static function onGetDefaultSortkey($title, &$sortkey)
	{
		// TODO: In theory, if the namespace supports subpages, this should break the entire title into sections and
		// run doSortable on each one individually, but that's an edge-case...is it worth the extra processing?
		$nsUesp = new SiteNamespace(null, null, $title);
		$nsFull = $nsUesp->get_ns_full();

		// This should be safe, since nsUesp should always return a namespace of at least this length.
		$name = substr($title->getPrefixedText(), strlen($nsFull));
		$sortkey = self::doSortable($name);
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
		//		if (self::$_lastright!='autopatrol') {
		// handle error output, since per Article::markpatrolled "The hook itself has handled any output"
		$wgOut->setPageTitle(wfMessage('markedaspatrollederror')->text());
		$wgOut->addWikiMsg('markedaspatrollederror-nonuserspace');
		$wgOut->returnToMain(false);
		//		}
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
			$ns = $title->getNamespace();
			// I'm only worrying about the extra condition that I'm adding ...
			// other processing should already be handling the rest of the patrol options
			if ($ns != NS_USER && $ns != (NS_USER + 1) && !$user->isAllowed('allspacepatrol')) {
				//self::$_lastright = $action;
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
		global $wgBlockAllowsUTEdit;
		if ($wgBlockAllowsUTEdit && !$ban->mAllowUsertalk && !$user->isAllowed('blocktalk')) {
			return wfMsgWikiHtml('restrictblock-denied-utalk');
		}
		//check for block length
		if (!$user->isAllowed('unrestrictedblock')) {
			global $egRestrictBlockLength;
			//infinity is right out
			if ($ban->mExpiry === 'infinity') {
				return wfMsgWikiHtml('restrictblock-denied', $egRestrictBlockLength);
			}
			$timediff = (wfTimestamp(TS_UNIX, $ban->mExpiry) - time());
			if ($timediff > $egRestrictBlockLength) {
				return wfMsgWikiHtml('restrictblock-denied', $egRestrictBlockLength);
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

/**
 * Functions to create, save, and display a breadcrumb trail
 */
class SiteBreadCrumbTrail
{
	protected static $_titles = array();
	protected $_titleid;
	protected $_parser;
	protected $_frame;
	protected $_trailtext = NULL;
	protected $_trailns = NULL;
	protected $_fulltrail = NULL;
	protected $_display = false;

	function __construct(&$titleid, &$parser = NULL)
	{
		$this->_titleid = $titleid;
		$this->_parser = $parser;
		$this->_frame = NULL;

		// don't bother to set hook if parser is NULL (page is being generated from cache, not parsed)
		if (!is_null($this->_parser)) {
			global $wgHooks;
			// ParserAfterTidy works on save and auto-update -- called after each individual article is processed
			$wgHooks['ParserAfterTidy'][] = array($this, 'finishTrail');
		}
	}

	static function newFromParser(&$parser)
	{
		$id = $parser->getTitle()->getArticleID();
		if (!array_key_exists($id, self::$_titles))
			self::$_titles[$id] = new SiteBreadCrumbTrail($id, $parser);
		return self::$_titles[$id];
	}

	static function newFromWgTitle($create = false)
	{
		global $wgTitle;
		$id = $wgTitle->getArticleID();
		if (array_key_exists($id, self::$_titles))
			return self::$_titles[$id];
		elseif (!$create)
			return NULL;
		else {
			self::$_titles[$id] = new SiteBreadCrumbTrail($id);
			return self::$_titles[$id];
		}
	}

	protected function getArgs($args, &$skip, &$separator)
	{
		global $egCustomSiteID;

		$this->_frame = $args[0];
		$origargs = $args[1];
		$args = array();
		if (is_array($origargs)) {
			foreach ($origargs as $arg) {
				$args[] = $this->_frame->expand($arg);
			}
		}

		$separator = wfMessage(strtolower($egCustomSiteID) . 'trailseparator')->inContentLanguage()->text();
		$output = array();
		$skip = false;
		foreach ($args as $arg) {
			$arg = trim($arg);
			if ($arg === false || $arg === '')
				continue;
			if (preg_match('/^([^\s=]+?)\s*=\s*(.*)/', $arg, $matches)) {
				if ($matches[1] == 'if')
					$skip = !($matches[2] == true);
				elseif ($matches[1] == 'ifnot')
					$skip = ($matches[2] == true);
				elseif ($matches[1] == 'ns' && !$skip)
					$this->_trailns = $matches[2];
				elseif ($matches[1] == 'separator')
					$separator = $matches[2];
				else
					$output[] = $arg;
			} else
				$output[] = $arg;
		}

		$separator = preg_replace('/:/', '&#058;', $separator);
		// make it possible to add vertical pipes -- which otherwise would get misread by the parsing
		$separator = preg_replace('/\!/', '|', $separator);
		if (strlen($separator) > 1 && $separator{
			0} == substr($separator, -1, 1) && ($separator{
			0} == '\'' || $separator{
			0} == '"'))
			$separator = substr($separator, 1, -1);
		return $output;
	}

	protected function initialize($use_ns = true)
	{
		if ($use_ns)
			$this->_trailtext = SiteNamespace::parser_get_value($this->_parser, 'ns_trail', $this->_frame, $this->_trailns);
		else
			$this->_trailtext = '';
	}

	protected function addlinks($data, $separator)
	{
		foreach ($data as $text) {
			if (!$text)
				continue;
			if (strpos($text, '[[') === false)
				$text = '[[:' . SiteNamespace::parser_get_value($this->_parser, 'ns_full', $this->_frame, $this->_trailns) . $text . '|' . $text . ']]';
			if ($this->_trailtext != '')
				$this->_trailtext .= $separator;
			$this->_trailtext .= $text;
		}
	}

	public static function implementInitTrail(&$parser)
	{
		$object = self::newFromParser($parser);
		$args = func_get_args();
		array_shift($args);
		$data = $object->getArgs($args, $skip, $separator);
		if ($skip)
			return '';
		$object->initialize(true);
		$object->addlinks($data, $separator);
		return '';
	}

	public static function implementSetTrail(&$parser)
	{
		$object = self::newFromParser($parser);
		$args = func_get_args();
		array_shift($args);
		$data = $object->getArgs($args, $skip, $separator);
		if ($skip)
			return '';
		// n.b. this should work even for a call such as {{#settrail:}} -> i.e., trail does end up
		// being erased, which should be an allowable option
		// note that an empty trail means that default subpage links will appear instead, if appropriate
		$object->initialize(false);
		$object->addlinks($data, $separator);
		return '';
	}

	public static function implementAddToTrail(&$parser)
	{
		$object = self::newFromParser($parser);
		$args = func_get_args();
		array_shift($args);
		$data = $object->getArgs($args, $skip, $separator);
		if ($skip)
			return '';
		if (is_null($object->_trailtext))
			$object->_trailtext = '';
		$object->addlinks($data, $separator);
		return '';
	}

	// This is being called by ParserAfterTidy
	// Note that ParserAfterTidy is normally called mutiple times on a page view -- once for each bit
	// of parsed text anywhere on the page
	// Therefore I have to be sure this function only takes effect after the real page contents have
	// been parsed, and does not take effect all the other times
	public function finishTrail(&$parser, &$text)
	{
		global $egCustomSiteID;
		$dotrail = wfMessage(strtolower($egCustomSiteID) . 'settrail')->inContentLanguage()->text();
		if (!$dotrail)
			return true;
		if (is_null($trail = $this->_trailtext))
			return true;
		// clear trail so that next call to this function doesn't repeat the processing
		$this->_trailtext = NULL;
		if (!$trail)
			return true;
		// never display bread crumb trail on template pages
		if ($parser->getTitle()->getNamespace() == NS_TEMPLATE)
			return true;

		// convert trail from wikitext to HTML
		$trail = $parser->recursiveTagParse($trail);
		// necessary to actually insert the links into the text
		$parser->replaceLinkHolders($trail);
		$trail = '&lt;&nbsp;' . $trail;
		// save processed trail to a different variable, so it can be accessed by subpageHook
		$this->_fulltrail = $trail;

		$parser->getOutput()->setProperty('breadCrumbTrail', $trail);

		return true;
	}

	// display bread crumb trail in subpage location
	public static function subpageHook(&$subpage)
	{
		global $egCustomSiteID;
		$dotrail = wfMessage(strtolower($egCustomSiteID) . 'settrail')->inContentLanguage()->text();
		// only use bread crumb trail if feature is enabled and if trail has been set
		if ($dotrail && (!is_null($object = self::newFromWgTitle())) && !is_null($object->_fulltrail)) {
			$subpage = $object->_fulltrail;
			return false;
		}
		// otherwise do default processing
		else
			return true;
	}

	// Use parserOutput->mProperties to allow customized information to be cached
	public static function getCachedTrail(&$out, $parserout)
	{
		if ($trail = $parserout->getProperty('breadCrumbTrail')) {
			$object = self::newFromWgTitle(true);
			$object->_fulltrail = $trail;
		}
		// Even more hacking... if Subtitle is completely empty, the empty <div id='subContent'></div>
		// tags mess up the location of siteSub, when siteSub is set to float:right (the siteSub tag
		// is displaced vertically). Forcing a nbsp to be displayed fixes the problem
		/* elseif ($out->getSubtitle()=='')
			$out->setSubtitle( '&nbsp;' ); */
		return true;
	}
}
