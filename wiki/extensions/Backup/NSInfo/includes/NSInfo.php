<?php

/**
 * An extension to add data persistence and variable manipulation to MediaWiki.
 *
 * At this point, the code could easily be split into four separate extensions based on the SSTNG constants, but at as
 * they're likely to all be used together, with the possible exception of the Define group as of MW 1.35, it seems
 * easier to keep them together for easier maintenance.
 */
class NSInfo
{
	#region Public Constants
	public const NA_NS_BASE = 'ns_base';
	public const NA_NS_ID = 'ns_id';

	public const PF_ISGAMESPACE = 'ISGAMESPACE';
	public const PF_ISMODSPACE = 'ISMODSPACE';
	public const PF_MOD_NAME = 'MOD_NAME';
	public const PF_MOD_PARENT = 'MOD_PARENT';
	public const PF_NS_BASE = 'NS_BASE';
	public const PF_NS_CATEGORY = 'NS_CATEGORY';
	public const PF_NS_CATLINK = 'NS_CATLINK';
	public const PF_NS_FULL = 'NS_FULL';
	public const PF_NS_ID = 'NS_ID';
	public const PF_NS_MAINPAGE = 'NS_MAINPAGE';
	public const PF_NS_NAME = 'NS_NAME';
	public const PF_NS_PARENT = 'NS_PARENT';
	public const PF_NS_TRAIL = 'NS_TRAIL';

	private const NSLIST = 'MediaWiki:Nsinfo-namespacelist'; //Could be made into a variable if really needed.
	#endregion

	#region Private Static Variables
	/** @var $cache string[] */
	private static $cache = [];

	/** @var ?NSInfoNamespace[] $info */
	private static $info = null;
	#endregion

	#region Public Static Functions
	public static function doIsGameSpace(Parser $parser, PPFrame $frame, ?array $args = null): bool
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getIsGameSpace();
	}

	public static function doIsModSpace(Parser $parser, PPFrame $frame, ?array $args = null): bool
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getIsModSpace();
	}

	public static function doModName(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getModName();
	}

	public static function doModParent(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getModParent();
	}

	public static function doNsBase(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getBase();
	}

	public static function doNsCategory(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getCategory();
	}

	public static function doNsCatlink(Parser $parser, PPFrame $frame, array $args): string
	{
		$page = trim($frame->expand($args[0]));
		if ($page === '') {
			return ParserHelper::error('nsinfo-nslink-emptypagename');
		}

		$contLang = VersionHelper::getInstance()->getContentLanguage();
		$catspace = $contLang->getNsText(NS_CATEGORY);
		$ns = self::getNsInfo($parser, $frame);
		$prefix = $ns->getCategory();
		$sortkey = count($args) > 1
			? ('|' . $frame->expand($args[1]))
			: '';

		return "[[$catspace:$prefix-$page$sortkey]]";
	}

	public static function doNsFull(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getFull();
	}

	public static function doNsId(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getId();
	}

	public static function doNsMainPage(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getMainPage();
	}

	public static function doNsName(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getName();
	}

	public static function doNsParent(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getParent();
	}

	public static function doNsTrail(Parser $parser, PPFrame $frame, ?array $args = null): string
	{
		$ns = self::getNsInfo($parser, $frame, $args);
		return $ns->getTrail();
	}

	/**
	 * Gets the namespace info from the relevant MediaWiki-space message.
	 *
	 * @return NSInfoNamespace[]
	 */
	public static function getNsMessage(): array
	{
		$helper = VersionHelper::getInstance();
		$title = Title::newFromText(self::NSLIST);
		$text = $helper->getPageText($title) ?? '';
		$text = preg_match('/\bid=["\']?nsinfo-table["\']?\b.*\|}/s', $text, $matches)
			? substr(
				$matches[0],
				0,
				strlen($matches[0]) - 3
			)
			: '';
		$rows = explode("\n|-", $text);
		$retval = [];
		$pseudoSpaceSets = [];
		if ($rows) {
			array_shift($rows);
			foreach ($rows as $row) {
				$newRow = explode(
					"\n",
					$row,
					2
				);
				$newRow = $newRow[count($newRow) - 1];
				$ns = NSInfoNamespace::fromRow($newRow);
				$nsId = $ns->getNsId();
				if ($nsId !== false) {
					$retval[$ns->getId()] = $ns;
					if (
						$ns->getPageName() === ''
					) {
						$retval[$nsId] = $ns;
					} else {
						$pseudoSpaceSets[$nsId][] = $ns;
					}
				}
			}

			foreach ($pseudoSpaceSets as $nsId => $pseudoSpaces) {
				if ($nsId !== false) {
					$retval[$nsId]->addPseudoSpaces($pseudoSpaces);
				}
			}
		}

		#RHDebug::show('Retval', $retval);
		return $retval;
	}

	/**
	 * Tries to get an NSInfoNamespace from a namespace name, namespace index, ns_base, or ns_id. Returns the empty
	 * namespace if the argument doesn't correspond to a recognized value.
	 *
	 * @param string $arg The argument to check.
	 *
	 * @return NSInfoNamespace|false
	 *
	 */
	public static function nsFromArg(string $arg): NSInfoNamespace
	{
		// In case this is called externally, initialize info.
		if (is_null(self::$info)) {
			self::$info = self::getNsMessage();
		}

		// Quick check: is it a recognized ns_base/ns_id/namespace index?
		$upperArg = strtoupper($arg);
		if (isset(self::$info[$upperArg])) {
			return self::$info[$upperArg];
		}

		// Is it a recognized namespace name?
		$contLang = VersionHelper::getInstance()->getContentLanguage();
		$index = is_numeric($arg) ? (int)$arg : $contLang->getNsIndex(strtr($arg, ' ', '_'));
		if ($index === false) {
			return NSInfoNamespace::empty();
		}

		$index = VersionHelper::getNsSubject($index);
		// It was recognized by MediaWiki, but does NSInfo recognize it?
		if (isset(self::$info[$index])) {
			return self::$info[$index];
		}

		// It's a valid but previously unused namespace.
		$ns = NSInfoNamespace::fromNamespace($index);
		self::$info[$index] = $ns;

		return $ns;
	}

	/**
	 * Given a title, finds the appropriate (pseudo-)namespace for that title.
	 *
	 * @param Title|PageReference $title
	 *
	 * @return NSInfoNamespace
	 *
	 */
	public static function nsFromTitle($title): NSInfoNamespace
	{
		$id = $title->getArticleID();
		if (isset(self::$cache[$id])) {
			return self::$cache[$id];
		}

		$index = $title->getNamespace();
		$index = VersionHelper::getNsSubject($index);

		if (is_null(self::$info)) {
			self::$info = self::getNsMessage();
		}

		// It's a title, so check the namespace and pagename and see if we can find a match.
		if (isset(self::$info[$index])) {
			$ns = self::$info[$index];
		} else {
			$ns = NSInfoNamespace::fromNamespace($index);
			self::$info[$index] = $ns;
		}

		$pseudoSpaces = $ns->getPseudoSpaces();
		if (count($pseudoSpaces)) {
			$longest = 0;
			// Append slash to pagename and pseudospace so NS:ModSomething doesn't match NS:Mod. We use $dbKey for
			// compatibility with PageReference.
			$pageName = $title->getText() . '/';
			foreach ($pseudoSpaces as $pseudoSpace) {
				$pseudoSpaceName = $pseudoSpace->getPageName() . '/';
				$spaceLen = strlen($pseudoSpaceName);
				if ($spaceLen > $longest && strncmp($pageName, $pseudoSpaceName, $spaceLen) === 0) {
					$ns = $pseudoSpace;
				}
			}
		}

		if ($id) {
			self::$cache[$id] = $ns;
		}

		return $ns;
	}
	#endregion

	#region Private Static Functions
	/**
	 * Gets namespace information for the associated namespace or pseudo-namespace. Namespaces not found in the table
	 * will be set to default values.
	 *
	 * @param \Parser $parser The parser in use.
	 * @param \PPFrame $frame The frame in use.
	 *
	 * @return NSInfoNamespace
	 */
	private static function getNsInfo(Parser $parser, PPFrame $frame, ?array $args = null): NSInfoNamespace
	{
		$arg = is_null($args) ? '' : trim($frame->expand($args[0]));

		if ($arg === '') {
			$newArg = $frame->getArgument(self::NA_NS_BASE);
			if ($newArg === false) {
				$newArg = $frame->getArgument(self::NA_NS_ID);
			}

			if ($newArg !== false) {
				$arg = $newArg;
			}
		}

		if ($arg === '') {
			// We have no arguments or magic variables, so pull the info from the parser's Title object.
			$title = VersionHelper::getInstance()->getParserTitle($parser);
			if (!$title) {
				// In the rare event parser doesn't return a title, try the frame, since that's a required parameter in
				// the call chain to get here.
				while ($frame->parent) {
					$frame = $frame->parent;
				}

				$title = $frame->getTitle();
			}
		} else {
			$ns = self::nsFromArg($arg);
			if ($ns->getNsId() !== false) {
				return $ns;
			}

			// If none of the above, assume it's a Title.
			$title = Title::newFromText($arg);
		}

		if (!$title) {
			// If we somehow failed to get a title, abort.
			return NSInfoNamespace::empty();
		}

		$list = Title::newFromText(self::NSLIST);
		if ($list) {
			$parser->getOutput()->addTemplate($list, $list->getArticleID(), $list->getLatestRevID());
		}

		return self::nsFromTitle($title);
	}
	#endregion
}
