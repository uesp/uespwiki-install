<?php


/* In theory, this process could be optimized further by subdividing <catpagetemplate> into a section for pages and a
 * section for sets so that only the set portion is parsed inside the loop at the end of processTemplate(). Given the
 * syntax changes already being introduced in this version and the extra level of user knowledge that a pages/sets
 * style would require, I don't think it's especially useful.
 */

/**
 * This class wraps around the base CategoryViewer class to provide MetaTemplate's custom capabilities like altering
 * the title and showing set names on a page. Because the base class changes method signatures between versions, this
 * class overrides/adds what's common to all, but then there are further derived classes to handle version-specific
 * methods.
 */

use Wikimedia\Rdbms\IResultWrapper;

class MetaTemplateCategoryViewer extends CategoryViewer
{
	#region Public Constants
	// CategoryViewer does not define these despite wide-spread internal usage in later versions, so we do. If that
	// changes in the future, these can be removed and the code altered, or they can be made synonyms for the CV names.
	public const CV_FILE = 'file';
	public const CV_PAGE = 'page';
	public const CV_SUBCAT = 'subcat';

	public const NA_IMAGE = 'metatemplate-image';
	public const NA_PAGE = 'metatemplate-page';
	public const NA_PAGELENGTH = 'metatemplate-pagelength';
	public const NA_SORTKEY = 'metatemplate-sortkey';
	public const NA_SUBCAT = 'metatemplate-subcat';

	public const TG_CATPAGETEMPLATE = 'metatemplate-catpagetemplate';
	#endregion

	#region Private Constants
	/**
	 * Key for the value to store catpagetemplate data in for browser refresh.
	 *
	 * @var string ([PPFrame $frame, ?string[] $templates])
	 */
	private const KEY_TEMPLATES = MetaTemplate::KEY_METATEMPLATE . '#cptTemplates';
	#endregion

	#region Protected Static Varables
	/** @var Language */
	protected static $contLang = null;

	/** @var ?string[] */
	protected static $templates = null; // Must be null for proper init on refresh

	/** @var ?PPFrame[] */
	protected static $templateFrames = null;
	#endregion

	#region Private Static Varables
	/** @var ?string */
	private static $mwPageLength = null;

	/** @var ?string */
	private static $mwSortKey = null;
	#endregion

	#region Public Static Functions
	/**
	 * Creates an inline template to use with the different types of category entries.
	 *
	 * @param string $content The content of the tag.
	 * @param array $attributes The tag attributes.
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 */
	public static function doCatPageTemplate(string $content, array $attributes, Parser $parser, PPFrame $frame = NULL): string
	{
		$parser->addTrackingCategory('metatemplate-tracking-catpagetemplate');
		if ($parser->getTitle()->getNamespace() !== NS_CATEGORY || !strlen(trim($content))) {
			return '';
		}

		// The parser cache doesn't store our custom category data nor allow us to parse it in any way short of caching
		// the entire parser and bringing it back in parseCatPageTemplate(), which seems inadvisable. Caching later in
		// the process also isn't an option as the cache is already saved by then. Short of custom parser caching,
		// which is probably not advisable unless/until I understand the full dynamics of category generation, the only
		// option is to disable the cache for any page with <catpagetemplate> on it.
		$output = $parser->getOutput();
		$output->updateCacheExpiry(0);

		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			self::NA_IMAGE,
			self::NA_PAGE,
			self::NA_SUBCAT
		]);

		$attributes = ParserHelper::transformAttributes($attributes, $magicWords);
		$none = !isset($attributes[self::NA_IMAGE]) && !isset($attributes[self::NA_PAGE]) && !isset($attributes[self::NA_SUBCAT]);
		if (isset($attributes[self::NA_IMAGE]) || ($none && !self::$templates[self::CV_FILE])) {
			self::$templates[self::CV_FILE] = $content;
			self::$templateFrames[self::CV_FILE] = $frame;
		}

		if (isset($attributes[self::NA_PAGE]) || ($none && !self::$templates[self::CV_PAGE])) {
			self::$templates[self::CV_PAGE] = $content;
			self::$templateFrames[self::CV_PAGE] = $frame;
		}

		if (isset($attributes[self::NA_SUBCAT]) || ($none && !self::$templates[self::CV_SUBCAT])) {
			self::$templates[self::CV_SUBCAT] = $content;
			self::$templateFrames[self::CV_SUBCAT] = $frame;
		}

		#RHDebug::show('Templates', self::$templates);
		// We don't care about the results, just that any #preload gets parsed. Transferring the ignore_set option via
		// the parser output seemed like a better choice than doing it via a static, in the event that there's somehow
		// more than one parser active.
		$output->setExtensionData(MetaTemplateData::KEY_IGNORE_SET, true);
		$dom = $parser->preprocessToDom($content);
		$content = $frame->expand($dom);
		$output->setExtensionData(MetaTemplateData::KEY_IGNORE_SET, null);
		$output->setExtensionData(self::KEY_TEMPLATES, self::$templates);
		return '';
	}

	/**
	 * Indicates whether any custom templates have been defined on the page.
	 *
	 * @return bool True if at least one custom template has been defined; otherwise, false.
	 */
	public static function hasTemplate(): bool
	{
		return !empty(self::$templates);
	}

	/**
	 * @todo This is a HORRIBLE way to do this. Needs to be re-written to cache the data, not the parser and so forth.
	 * @todo Leave magic words as magic words and use all synonyms when setting the names.
	 * Initializes the class, accounting for possible parser caching.
	 *
	 * @param ?ParserOutput $parserOutput The current ParserOutput object if the page is retrieved from the cache.
	 */
	public static function init(ParserOutput $parserOutput = null): void
	{
		if (!MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLECPT)) {
			return;
		}

		if ($parserOutput) {
			// We got here via the parser cache (Article::view(), case 2), so reload everything we don't have.
			// Article::view();
			self::$templates = $parserOutput->getExtensionData(self::KEY_TEMPLATES);
		}

		// While we could just import the global $wgContLang here, the global function still works and isn't deprecated
		// as of MediaWiki 1.40. In 1.32, however, MediaWiki introduces the method used on the commented out line, and
		// it seems likely they'll eventually make that the official method. Given that it's valid for so much longer
		// via this method, however, there's little point in versioning it via VersionHelper unless this code is used
		// outside our own wikis; we can just switch once we get to 1.32.
		self::$contLang = self::$contLang ?? wfGetLangObj(true);
		// self::$contLang = self::$contLang ?? MediaWikiServices::getInstance()->getContentLanguage();
		$helper = VersionHelper::getInstance();
		self::$mwPageLength = self::$mwPageLength ?? $helper->getMagicWord(self::NA_PAGELENGTH)->getSynonym(0);
		self::$mwSortKey = self::$mwSortKey ?? $helper->getMagicWord(self::NA_SORTKEY)->getSynonym(0);
	}

	/**
	 * Gets any additional set variables requested.
	 *
	 * @param string $type The type of results ('page', 'subcat', 'image').
	 * @param IResultWrapper $result The database results.
	 */
	public static function onDoCategoryQuery(string $type, IResultWrapper $result): void
	{
		if (
			!self::$templateFrames[$type] || // No catpagetemplate
			$result->numRows() === 0 || // No categories
			!MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA) // No possible sets
		) {
			return;
		}

		/** @var MetaTemplatePage[] $pages */
		$pages = [];
		for ($row = $result->fetchRow(); $row; $row = $result->fetchRow()) {
			$pageId = $row['page_id'];
			$ns = $row['page_namespace'];
			$title = $row['page_title'];
			$pages[$pageId] = new MetaTemplatePage($ns, $title);
		}

		$result->rewind();
		$variables = MetaTemplateData::$preloadVarSets['']->variables ?? [];
		if (!empty($pages)) {
			MetaTemplateSql::getInstance()->catQuery($pages, array_keys($variables));
		}

		MetaTemplateData::$preloadCache = $pages;
	}
	#endregion

	#region Protected Functions
	/**
	 * Evaluates the template in the context of the category entry and each set on that page.
	 *
	 * @param string $template The template to be parsed.
	 * @param Title $title The title of the category entry.
	 * @param string|null $sortkey The current sortkey.
	 * @param int $pageLength The page length.
	 * @param MetaTemplateSet $set The current set on the entry page.
	 *
	 * @return MetaTemplateCategoryVars
	 */
	protected function parseCatPageTemplate(string $type, Title $title, ?string $sortkey, int $pageLength, MetaTemplateSet $set): ?MetaTemplateCategoryVars
	{
		/** @todo Pagename entry should be changed to getText() for consistency. */
		$frame = self::$templateFrames[$type];
		$child = $frame->newChild(false, $title, 0);
		MetaTemplate::setVar($child, MetaTemplate::$mwFullPageName, $title->getPrefixedText());
		MetaTemplate::setVar($child, MetaTemplate::$mwNamespace, $title->getNsText());
		MetaTemplate::setVar($child, MetaTemplate::$mwPageName, $title->getText());
		MetaTemplate::setVar($child, self::$mwPageLength, (string)$pageLength);
		MetaTemplate::setVar($child, self::$mwSortKey, explode("\n", $sortkey)[0]);
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)) {
			MetaTemplate::setVar($child, MetaTemplateData::$mwSet, $set->name);
		}

		$parser = $frame->parser;
		foreach ($set->variables as $varName => $varValue) {
			$dom = $parser->preprocessToDom($varValue);
			MetaTemplate::setVarDirect($child, $varName, $dom, $varValue);
		}

		$dom = $parser->preprocessToDom(self::$templates[$type], Parser::PTD_FOR_INCLUSION);
		$templateOutput = trim($child->expand($dom));
		return new MetaTemplateCategoryVars($child, $title, $templateOutput);
	}
	#endregion
}
