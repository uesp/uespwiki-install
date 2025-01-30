<?php

use MediaWiki\Linker\LinkTarget;

/**
 * Provides version-specific methods for those calls that differ substantially across versions.
 */
abstract class VersionHelper
{
	// Copy of Parser::PTD_FOR_INCLUSION / Preprocessor::DOM_FOR_INCLUSION
	const FOR_INCLUSION = 1;

	// Copy of (Revision|RevisionRecord)::RAW
	const RAW_CONTENT = 3;

	#region Private Static Variables
	/**
	 * Instance variable for singleton.
	 *
	 * @var VersionHelper
	 */
	private static $instance;
	#endregion

	#region Public Static Functions
	/**
	 * Gets the singleton instance for this class.
	 *
	 * @return VersionHelper The singleton instance.
	 *
	 */
	public static function getInstance(): VersionHelper
	{
		if (!self::$instance) {
			$version = VersionHelper::getMWVersion();
			if (version_compare($version, '1.38', '>=')) {
				require_once(__DIR__ . '/VersionHelper38.php');
				self::$instance = new VersionHelper38();
			} elseif (version_compare($version, '1.35', '>=')) {
				require_once(__DIR__ . '/VersionHelper35.php');
				self::$instance = new VersionHelper35();
			} elseif (version_compare($version, '1.28', '>=')) {
				require_once(__DIR__ . '/VersionHelper28.php');
				self::$instance = new VersionHelper28();
			} else {
				throw new Exception('MediaWiki version could not be found or is too low.');
			}
		}

		return self::$instance;
	}

	/**
	 * Gets the MediaWiki version number as text. This is static rather than absttract so it can be used to determine
	 * the version without relying on the version.
	 *
	 * @return string
	 *
	 */
	public static function getMWVersion(): string
	{
		global $wgVersion;
		return defined('MW_VERSION') ? constant('MW_VERSION') : $wgVersion;
	}

	/**
	 * Get the subject space for a given namespace.
	 *
	 * @param int $id The original namespace.
	 *
	 * @return int The subject namespace.
	 *
	 */
	public static function getNsSubject(int $id): int
	{
		// This is consistent across all versions of MediaWiki and is unlikely to change, so it's a regular function rather than an abstract one.
		return $id < NS_MAIN
			? $id
			: $id & ~1;
	}

	/**
	 * Get the talk space for a given namespace.
	 *
	 * @param int $id The original namespace.
	 *
	 * @return int The talk namespace.
	 *
	 */
	public static function getNsTalk(int $id): int
	{
		// This is consistent across all versions of MediaWiki and is unlikely to change, so it's a regular function rather than an abstract one.
		return $id < NS_MAIN
			? $id
			: $id | 1;
	}
	#endregion

	#region Public Functions
	/**
	 * Gets the namespace ID from the parser.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return ?int The namespace ID.
	 */
	public function getParserNamespace(Parser $parser)
	{
		$title = $this->getParserTitle($parser);
		return $title
			? $title->getNamespace()
			: null;
	}
	#endregion

	#region Public Abstract Functions
	/**
	 * Recursively updates a page.
	 *
	 * @param WikiPage $page The page to purge. This is always a link-update purge, optionally recursive.
	 * @param ParserOutput $parserOutput The parser output from a prior call to $page->getParserOutput().
	 * @param ParserOptions $options The same parser options used with the above $parserOutput.
	 *
	 * @return void
	 *
	 */
	public abstract function doSecondaryDataUpdates(WikiPage $page, ParserOutput $parserOutput, ParserOptions $options): void;

	/**
	 * Determines if a File page exists on the local wiki.
	 *
	 * @param Title $title The file to search for.
	 *
	 * @return bool True if the file was found; otherwise, false.
	 */
	public abstract function fileExists(Title $title): bool;

	/**
	 * Finds a language-variant link, if appropriate. See https://www.mediawiki.org/wiki/LangConv.
	 *
	 * @param Parser $parser The parser in use.
	 * @param string $titleText The title to search for. (May be modified on exit.)
	 * @param Title $title The resultant title. (May be modified on exit.)
	 * @param bool $ignoreOtherCond Disable other conditions when transcluding a template or updating a category link.
	 */
	public abstract function findVariantLink(Parser $parser, string &$titleText, Title &$title, $ignoreOtherCond = false): void;

	/**
	 * Gets the wiki's content language.
	 *
	 * @return Language
	 */
	public abstract function getContentLanguage(): Language;

	/**
	 * Gets the latest revision of a page as the appropriate revision type for the version.
	 *
	 * @param WikiPage $page The page to get the revision of.
	 *
	 * @return Revision|RevisionRecord|null
	 * @deprecated No longer used by internal code.
	 */
	public abstract function getLatestRevision(WikiPage $page);

	/**
	 * Gets the magic word for the specified id.
	 *
	 * @param string $id The id of the magic word to get.
	 *
	 * @return MagicWord The magic word or null if not found.
	 */
	public abstract function getMagicWord(string $id): MagicWord;

	/**
	 * Gets a page property from the parser's output.
	 *
	 * @param ParserOutput $output The parser's output (typically $parser->getOutput()).
	 * @param string $name The name of the property to get.
	 *
	 * @return mixed
	 * @deprecated No longer used by internal code.
	 *
	 */
	public abstract function getPageProperty(ParserOutput $output, string $name);

	/**
	 * Gets the raw wikitext of a page.
	 *
	 * @param LinkTarget $target The page to get.
	 *
	 * @return string|null The text of the page or null if not found.
	 *
	 */
	public abstract function getPageText(LinkTarget $target): ?string;

	/**
	 * Gets the parser's Title/PageReference object
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return Title|PageReference|null
	 *
	 */
	public abstract function getParserTitle(Parser $parser);

	/**
	 * Retrieves the parser's strip state object.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return StripState
	 */
	public abstract function getStripState(Parser $parser): StripState;

	/**
	 * Gets a WikiPage object from a Title.
	 *
	 * @param LinkTarget $link
	 *
	 * @return WikiPage
	 *
	 */
	public abstract function getWikiPage(LinkTarget $link): WikiPage;

	/**
	 * Converts internal links to <!--LINK #--> objects.
	 *
	 * @param Parser $parser The parser in use.
	 * @param string $text The text to convert.
	 *
	 * @return string
	 */
	public abstract function handleInternalLinks(Parser $parser, string $text): string;

	/**
	 * Launches any recursive updates needed for the title passed to it.
	 *
	 * @param Title $title The title that was edited.
	 * @param mixed $revision The current revision. Can also be a Parser object for backwards compatibility.
	 * @deprecated No longer used by internal code.
	 */
	public abstract function onArticleEdit(Title $title, $revId): void;

	/**
	 * Recursively updates a page. This is always a link-update purge, optionally recursive.
	 *
	 * @param WikiPage $page The page to purge.
	 * @param bool $recursive Whether the purge should be recursive.
	 */
	public abstract function purge($page, bool $recursive);

	/**
	 * Calls $parser->replaceLinkHoldersText(), bypassing the private access modifier if needed.
	 *
	 * @param Parser $parser The parser in use.
	 * @param mixed $output The output text to replace in.
	 *
	 * @return string
	 */
	public abstract function replaceLinkHoldersText(Parser $parser, string $text): string;

	/**
	 * Saves a content object to the specified page.
	 *
	 * @param LinkTarget $pageName The location to save to.
	 * @param Content $content The content to save.
	 * @param string $editSummary The edit summary.
	 * @param User $user The user to save as.
	 * @param int $flags Flags that alter the save behaviour.
	 *
	 * @return [type]
	 *
	 */
	public abstract function saveContent(LinkTarget $pageName, Content $content, string $editSummary, User $user, int $flags = 0);

	/**
	 * Sets a page property in the parser's output.
	 *
	 * @param ParserOutput $output The parser's output (typically $parser->getOutput()).
	 * @param string $name The name of the property to set.
	 * @param mixed $value The value of the property.
	 *
	 */
	public abstract function setPageProperty(ParserOutput $output, string $name, $value): void;

	/**
	 * Sets the Parser's mPreprocessor variable.
	 *
	 * @param Parser $parser The parser in use.
	 */
	public abstract function setPreprocessor(Parser $parser, $preprocessor): void;

	/**
	 * Determines if a Special page exists on the local wiki.
	 *
	 * @param Title $title The Special page to search for.
	 *
	 * @return bool True if the page was found; otherwise, false.
	 */
	public abstract function specialPageExists(Title $title): bool;

	/**
	 * Sets a page property in the parser's output.
	 *
	 * @param ParserOutput $output The parser's output (typically $parser->getOutput()).
	 * @param string $name The name of the property to set.
	 *
	 */
	public abstract function unsetPageProperty(ParserOutput $output, string $name): void;

	/**
	 * Updates the backlinks for a specific page and specific type of backlink (based on table name).
	 *
	 * @param Title $title The title whose backlinks should be updated.
	 * @param string $tableName The table name of the links to update.
	 *
	 * @return void
	 *
	 */
	public abstract function updateBackLinks(Title $title, string $tableName): void;
	#endregion
}
