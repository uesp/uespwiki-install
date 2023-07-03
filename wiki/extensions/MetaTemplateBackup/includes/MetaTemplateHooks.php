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
	 * Enables MetaTemplate's variables.
	 *
	 * @param array $aCustomVariableIds The list of custom variables to add to.
	 */
	public static function onMagicWordwgVariableIDs(array &$aCustomVariableIds): void
	{
		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEPAGENAMES)) {
			$aCustomVariableIds[] = MetaTemplate::VR_FULLPAGENAME0;
			$aCustomVariableIds[] = MetaTemplate::VR_NAMESPACE0;
			$aCustomVariableIds[] = MetaTemplate::VR_NESTLEVEL;
			$aCustomVariableIds[] = MetaTemplate::VR_PAGENAME0;
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

	public static function onNewRevisionFromEditComplete(WikiPage $wikiPage, $rev, $baseID, User $user, &$tags = null)
	{
		// The intent here is to reduce or remove recursively updating an article twice (once by the MW software and
		// again by the onParserAfterTidy routine). This works in current versions of MW but should be tested again in
		// future, as this is significantly removed from where the recursive update actually takes place and MW's
		// update scheme may change.
		MetaTemplateData::$articleEditId = $wikiPage->getId();
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
			$titleOld = $old instanceof MediaWiki\Linker\LinkTarget
				? Title::newFromLinkTarget($old)
				: $old;
			MetaTemplateSql::getInstance()->deleteVariables($pageid);
			VersionHelper::getInstance()->updateBackLinks($titleOld, 'templatelinks');

			$titleNew = $new instanceof MediaWiki\Linker\LinkTarget
				? Title::newFromLinkTarget($new)
				: $new;
			WikiPage::onArticleEdit($titleNew, $revision);
		}
	}

	/**
	 * Writes all #saved data to the database.
	 *
	 * @param Parser $parser The parser in use.
	 * @param mixed $text The text of the article.
	 */
	public static function onParserAfterTidy(Parser $parser, &$text): void
	{
		MetaTemplateData::onParserAfterTidy($parser, $text);
	}

	/**
	 * Initialize parser and tag functions followed by MetaTemplate general initialization.
	 *
	 * @param Parser $parser The parser in use.
	 */
	public static function onParserFirstCallInit(Parser $parser): void
	{
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

	/**
	 * Gets the value of the specified variable.
	 *
	 * @param Parser $parser The parser in use.
	 * @param array $variableCache The variable cache. Can be used to store values for faster evaluation in subsequent calls.
	 * @param mixed $magicWordId The magic word ID to evaluate.
	 * @param mixed $ret The return value.
	 * @param PPFrame $frame The frame in use.
	 *
	 * @return bool Always true
	 */
	public static function onParserGetVariableValueSwitch(Parser $parser, array &$variableCache, $magicWordId, &$ret, PPFrame $frame): bool
	{
		if (!MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEPAGENAMES)) {
			return true;
		}

		switch ($magicWordId) {
			case MetaTemplate::VR_FULLPAGENAME0:
				$ret = MetaTemplate::doFullPageNameX($parser, $frame, null);
				break;
			case MetaTemplate::VR_NAMESPACE0:
				$ret = MetaTemplate::doNamespaceX($parser, $frame, null);
				break;
			case MetaTemplate::VR_NESTLEVEL:
				$ret = MetaTemplate::doNestLevel($parser, $frame);
				break;
			case MetaTemplate::VR_PAGENAME0:
				$ret = MetaTemplate::doPageNameX($parser, $frame, null);
				break;
		}

		return true;
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
			$parser->setFunctionHook(MetaTemplateData::PF_LISTSAVED, 'MetaTemplateData::doListsaved', SFH_OBJECT_ARGS); // listsaved
			$parser->setFunctionHook(MetaTemplateData::PF_LOAD, 'MetaTemplateData::doLoad', SFH_OBJECT_ARGS); // load
			$parser->setFunctionHook(MetaTemplateData::PF_PRELOAD, 'MetaTemplateData::doPreload', SFH_OBJECT_ARGS); // load
			$parser->setFunctionHook(MetaTemplateData::PF_SAVE, 'MetaTemplateData::doSave', SFH_OBJECT_ARGS); // save
		}

		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDEFINE)) {
			$parser->setFunctionHook(MetaTemplate::PF_DEFINE, 'MetaTemplate::doDefine', SFH_OBJECT_ARGS); // variables
			$parser->setFunctionHook(MetaTemplate::PF_INHERIT, 'MetaTemplate::doInherit', SFH_OBJECT_ARGS); // frames
			$parser->setFunctionHook(MetaTemplate::PF_LOCAL, 'MetaTemplate::doLocal', SFH_OBJECT_ARGS); // variables
			$parser->setFunctionHook(MetaTemplate::PF_PREVIEW, 'MetaTemplate::doPreview', SFH_OBJECT_ARGS); // variables
			$parser->setFunctionHook(MetaTemplate::PF_RETURN, 'MetaTemplate::doReturn', SFH_OBJECT_ARGS); // frames
			$parser->setFunctionHook(MetaTemplate::PF_UNSET, 'MetaTemplate::doUnset', SFH_OBJECT_ARGS); // variables
		}

		if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEPAGENAMES)) {
			$parser->setFunctionHook(MetaTemplate::PF_FULLPAGENAMEx, 'MetaTemplate::doFullPageNameX', SFH_OBJECT_ARGS | SFH_NO_HASH); // frames
			$parser->setFunctionHook(MetaTemplate::PF_NAMESPACEx, 'MetaTemplate::doNamespaceX', SFH_OBJECT_ARGS | SFH_NO_HASH); // frames
			$parser->setFunctionHook(MetaTemplate::PF_PAGENAMEx, 'MetaTemplate::doPageNameX', SFH_OBJECT_ARGS | SFH_NO_HASH); // frames
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
