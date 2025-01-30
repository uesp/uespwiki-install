<?php

use Wikimedia\Rdbms\IResultWrapper;

/** @todo Add {{#define/local/preview:a=b|c=d}} */
/** @todo
 * This is essentially four extensions in one and if desired in the future, could be fairly readily split for more of a
 * single-purpse feel to each extension. Catpagetemplate would need a bit of work, as it's is a bit too coupled with
 * the data features right now, but a few hooks would probably take care of that.
 */
class MetaTemplateHooks
{
	#region Public Static Functions
	/**
	 * Deletes all set-related data when a page is deleted.
	 *
	 * @param WikiPage $article The article that was deleted.
	 * @param User $user The user that deleted the article
	 * @param mixed $reason The reason the article was deleted.
	 * @param mixed $id The ID of the article that was deleted.
	 * @param mixed $content The content of the deleted article, or null in case of an error.
	 * @param LogEntry $logEntry The log entry used to record the deletion.
	 * @param mixed $archivedRevisionCount The number of revisions archived during the page delete.
	 */
	public static function onArticleDeleteComplete(WikiPage &$article, User &$user, $reason, $id, $content, LogEntry $logEntry, $archivedRevisionCount): void
	{
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)) {
			#RHlogFunctionText('Deleted: ', $article->getTitle()->getFullText());
			MetaTemplateSql::getInstance()->deleteVariables($article->getId());
		}
	}

	/**
	 * If in Category space, this creates a new CategoryPage derivative that will open one of:
	 *   - A MetaTemplateCategoryViewer if a <catpagetemplate> is present on the category page.
	 *   - A CategoryTreeCategoryViewer if the CategoryTree extension is detected.
	 *   - A regular CategoryViewer in all other cases.
	 *
	 * @param Title $title The category's title.
	 * @param ?Article $article The new article page.
	 * @param IContextSource $context The request context.
	 */
	public static function onArticleFromTitle(Title &$title, ?Article &$article, IContextSource $context): void
	{
		if ($title->getNamespace() === NS_CATEGORY) {
			$catTreeName = 'CategoryTreeCategoryPage';
			if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLECPT)) {
				$article = new MetaTemplateCategoryPage($title);
			} elseif (class_exists($catTreeName)) {
				$article = new $catTreeName($title);
			}
		}
	}

	public static function onArticlePurge(WikiPage $article)
	{
		#RHDebug::writeFile(__METHOD__, ': ', $article->getTitle()->getPrefixedText());
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_RESAVEONPURGE)) {
			MetaTemplateData::save($article);
		}
	}

	public static function onArticleSaveComplete(&$article, &$user, $text, $summary, $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId)
	{
		#RHDebug::writeFile(__METHOD__, ': ', $article->getTitle()->getPrefixedText());
		MetaTemplateData::save($article->getPage());
	}

	/**
	 * Passes the CategoryViewer::doCategoryQuery hook through to the category viewer, if enabled.
	 *
	 * @param string $type The type of results ('page', 'subcat', 'image').
	 * @param IResultWrapper $result The database results.
	 */
	public static function onDoCategoryQuery(string $type, IResultWrapper $result): void
	{
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLECPT)) {
			MetaTemplateCategoryViewer::onDoCategoryQuery($type, $result);
		}
	}

	/**
	 * Re-save the page on link update.
	 *
	 * @param LinksUpdate|MediaWiki\Deferred\LinksUpdate\LinksUpdate $linksUpdate
	 *
	 */
	public static function onLinksUpdateComplete(&$linksUpdate)
	{
		#RHDebug::writeFile(__METHOD__, ': ', $linksUpdate->getTitle()->getPrefixedText());
		$page = VersionHelper::getInstance()->getWikiPage($linksUpdate->getTitle());
		MetaTemplateData::save($page);
	}

	/**
	 * Migrates the old MetaTemplate tables to new ones. The basic functionality is the same, but names and indeces
	 * have been altered and the datestamp removed.
	 *
	 * @param DatabaseUpdater $updater
	 */
	public static function onLoadExtensionSchemaUpdates(DatabaseUpdater $updater): void
	{
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)) {
			MetaTemplateSql::getInstance()->onLoadExtensionSchemaUpdates($updater);
		}
	}

	/**
	 * Adds ns_base and ns_id to the list of parameters that bypass the normal limitations on parameter evaluation when
	 * viewing a template on its native page.
	 *
	 * @param array $bypassVars The active list of variable names to bypass.
	 */
	public static function onMetaTemplateSetBypassVars(array &$bypassVars): void
	{
		/** @todo This function is a placeholder until UespCustomCode is rewritten, at which point this can be
		 *  transferred there.
		 */

		/* Going with hard-coded values, since these are unlikely to change, even if we transfer them to other
		 * languages. If we do want to translate them, it's changed easily enough at that time.
		 */
		$bypassVars[] = 'ns_base';
		$bypassVars[] = 'ns_id';
	}

	/**
	 * Initializes the the category viewer when called from the parser cache.
	 *
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 */
	public static function onOutputPageParserOutput(OutputPage $out, ParserOutput $parserOutput): void
	{
		if ($out->getTitle()->getNamespace() == NS_CATEGORY) {
			MetaTemplate::getCatViewer()::init($parserOutput);
		}
	}

	public static function onPageContentSaveComplete($wikiPage, $user, $mainContent, $summaryText, $isMinor, $isWatch, $section, $flags, $revision, $status, $originalRevId, $undidRevId)
	{
		#RHDebug::writeFile(__METHOD__, ': ', $wikiPage->getTitle()->getPrefixedText());
		MetaTemplateData::save($wikiPage);
	}

	/**
	 * During a move, this function moves data from the original page to the new one, then forces re-evaluation of the
	 * new page to ensure all information is up to date.
	 *
	 * @param MediaWiki\Linker\LinkTarget|Title $old The original LinkTarget for the page.
	 * @param MediaWiki\Linker\LinkTarget|Title $new The new LinkTarget for the page.
	 * @param MediaWiki\User\UserIdentity|User $userIdentity The user performing the move.
	 * @param int $pageid The original page ID.
	 * @param int $redirid The new page ID.
	 * @param string $reason The reason for the move.
	 * @param MediaWiki\Revision\RevisionRecord|Revision $revision The RevisionRecord.
	 *
	 * @internal This hook handles both the MW 1.35+ PageMoveComplete and 1.34- TitleMoveComplete events. It takes
	 *     advantage of PHP's loose typing and the fact that both versions have the same number and order of
	 *     parameters with somewhat compatible object types.
	 */
	public static function onPageMoveComplete($old, $new, $userIdentity, int $pageid, int $redirid, string $reason, $revision): void
	{
		#RHlogFunctionText("Move $old ($pageid) to $new ($redirid)");
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)) {
			$helper = VersionHelper::getInstance();
			$titleOld = $old instanceof Title
				? $old
				: Title::newFromLinkTarget($old);
			$helper->updateBackLinks($titleOld, 'templatelinks');
			MetaTemplateData::save($helper->getWikiPage($new));
		}
	}

	public static function onPageSaveComplete(WikiPage $wikiPage, $user, string $summary, int $flags, $revisionRecord, $editResult)
	{
		#RHDebug::writeFile(__METHOD__, ': ', $wikiPage->getTitle()->getPrefixedText());
		MetaTemplateData::save($wikiPage);
	}

	/**
	 * Initialize parser and tag functions followed by MetaTemplate general initialization.
	 *
	 * @param Parser $parser The parser in use.
	 */
	public static function onParserFirstCallInit(Parser $parser): void
	{
		#if ($parser->getTitle()) {
		#	RHDebug::writeFile(__METHOD__, ': ', $parser->getTitle()->getPrefixedText());
		#}

		// This should work up to 1.35. In 1.36, they change mPreprocessor to private. At that point, we can probably
		// override this through reflection. It doesn't look like there are any other options, since even in a derived
		// class, we can't set the private mPreprocessor property.

		// This deliberately overrides mPreprocessor even if not using a custom preprocessor, as the default can still
		// end up being Preprocessor_DOM at this point, which isn't fully supported. In later versions,
		// Preprocessor_Hash is the only built-in option anyway.
		$useMtParser =
			MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA) ||
			MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDEFINE);
		$preprocessorClass = $useMtParser
			? MetaTemplatePreprocessor::class
			: Preprocessor_Hash::class;
		VersionHelper::getInstance()->setPreprocessor($parser, new $preprocessorClass($parser));

		self::initParserFunctions($parser);
		self::initTagFunctions($parser);
		MetaTemplate::init();
		MetaTemplateData::init();
		MetaTemplate::getCatViewer()::init();
	}
	#endregion

	#region Private Static Functions
	/**
	 * Initialize parser functions.
	 *
	 * @param Parser $parser The parser in use.
	 */
	private static function initParserFunctions(Parser $parser): void
	{
		// The // at the end of each function are the tracking categories the function is in.
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA) && MetaTemplateSql::getInstance()->tablesExist()) {
			$parser->setFunctionHook(MetaTemplateData::PF_LISTSAVED, 'MetaTemplateData::doListsaved', Parser::SFH_OBJECT_ARGS); // listsaved
			$parser->setFunctionHook(MetaTemplateData::PF_LOAD, 'MetaTemplateData::doLoad', Parser::SFH_OBJECT_ARGS); // load
			$parser->setFunctionHook(MetaTemplateData::PF_PRELOAD, 'MetaTemplateData::doPreload', Parser::SFH_OBJECT_ARGS); // load
			$parser->setFunctionHook(MetaTemplateData::PF_SAVE, 'MetaTemplateData::doSave', Parser::SFH_OBJECT_ARGS); // save
		}

		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDEFINE)) {
			$parser->setFunctionHook(MetaTemplate::PF_DEFINE, 'MetaTemplate::doDefine', Parser::SFH_OBJECT_ARGS); // variables
			$parser->setFunctionHook(MetaTemplate::PF_INHERIT, 'MetaTemplate::doInherit', Parser::SFH_OBJECT_ARGS); // frames
			$parser->setFunctionHook(MetaTemplate::PF_LOCAL, 'MetaTemplate::doLocal', Parser::SFH_OBJECT_ARGS); // variables
			$parser->setFunctionHook(MetaTemplate::PF_PREVIEW, 'MetaTemplate::doPreview', Parser::SFH_OBJECT_ARGS); // variables
			$parser->setFunctionHook(MetaTemplate::PF_RETURN, 'MetaTemplate::doReturn', Parser::SFH_OBJECT_ARGS); // frames
			$parser->setFunctionHook(MetaTemplate::PF_UNSET, 'MetaTemplate::doUnset', Parser::SFH_OBJECT_ARGS); // variables
		}

		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEPAGENAMES)) {
			$parser->setFunctionHook(MetaTemplate::PF_FULLPAGENAMEx, 'MetaTemplate::doFullPageNameX', Parser::SFH_OBJECT_ARGS | SFH_NO_HASH); // frames
			$parser->setFunctionHook(MetaTemplate::PF_NAMESPACEx, 'MetaTemplate::doNamespaceX', Parser::SFH_OBJECT_ARGS | SFH_NO_HASH); // frames
			$parser->setFunctionHook(MetaTemplate::PF_NESTLEVEL, 'MetaTemplate::doNestlevel', Parser::SFH_OBJECT_ARGS | SFH_NO_HASH); // frames
			$parser->setFunctionHook(MetaTemplate::PF_PAGENAMEx, 'MetaTemplate::doPageNameX', Parser::SFH_OBJECT_ARGS | SFH_NO_HASH); // frames
		}
	}

	/**
	 * Initialize tag functions.
	 *
	 * @param Parser $parser The parser in use.
	 */
	private static function initTagFunctions(Parser $parser): void
	{
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLECPT)) {
			ParserHelper::setHookSynonyms($parser, MetaTemplateCategoryViewer::TG_CATPAGETEMPLATE, 'MetaTemplateCategoryViewer::doCatPageTemplate'); // catpagetemplate
		}

		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)) {
			ParserHelper::setHookSynonyms($parser, MetaTemplateData::TG_SAVEMARKUP, 'MetaTemplateData::doSaveMarkupTag'); // save
		}
	}
	#endregion
}
