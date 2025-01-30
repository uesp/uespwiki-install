<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;

/**
 * See base class for documentation.
 */
class VersionHelper35 extends VersionHelper
{
	public function doSecondaryDataUpdates(WikiPage $page, ParserOutput $parserOutput, ParserOptions $options): void
	{
		$page->updateParserCache(['causeAction' => 'vh-doSecondary']);
		$page->doSecondaryDataUpdates([
			'recursive' => true,
			'causeAction' => 'vh-doSecondary',
			'defer' => DeferredUpdates::PRESEND,
		]);
	}

	public function fileExists(Title $title): bool
	{
		return (bool)MediaWikiServices::getInstance()->getRepoGroup()->findFile($title);
	}

	public function findVariantLink(Parser $parser, string &$titleText, ?Title &$title, $ignoreOtherCond = false): void
	{
		$lc = self::getLanguageConverter($parser->getContentLanguage());
		if ($lc->hasVariants()) {
			$lc->findVariantLink($titleText, $title, $ignoreOtherCond);
		}
	}

	public function getContentLanguage(): Language
	{
		return MediaWikiServices::getInstance()->getContentLanguage();
	}

	public function getLatestRevision(WikiPage $page)
	{
		return $page->getRevisionRecord();
	}

	public function getMagicWord($id): MagicWord
	{
		return MediaWikiServices::getInstance()->getMagicWordFactory()->get($id);
	}

	public function getPageText(LinkTarget $target): ?string
	{
		$title = $target instanceof Title ? $target : Title::newFromLinkTarget($target);
		$page = WikiPage::factory($title);
		$rev = $page->getRevisionRecord();
		if (!is_null($rev)) {
			$content = $rev->getContent(SlotRecord::MAIN, RevisionRecord::RAW);
			if ($content instanceof TextContent) {
				return $content->getText();
			}
		}

		return null;
	}

	public function getPageProperty(ParserOutput $output, string $name): ?string
	{
		$retval = $output->getProperty($name);
		return $retval === false
			? null
			: $retval;
	}

	public function getParserTitle(Parser $parser)
	{
		return $parser->getTitle();
	}

	public function getStripState(Parser $parser): StripState
	{
		return $parser->getStripState();
	}

	public function getWikiPage(LinkTarget $link): WikiPage
	{
		return $link instanceof Title
			? WikiPage::factory($link)
			: WikiPage::factory(Title::newFromLinkTarget($link));
	}

	public function handleInternalLinks(Parser $parser, string $text): string
	{
		$reflector = new ReflectionObject($parser);
		$replaceLinks = $reflector->getMethod('handleInternalLinks');
		$replaceLinks->setAccessible(true);
		return $replaceLinks->invoke($parser, $text);
	}

	public function onArticleEdit(Title $title, $revId): void
	{
		if ($revId instanceof Parser) {
			$revision = $revId->getRevisionRecordObject();
		} else {
			$revision = MediaWikiServices::getInstance()->getRevisionStore()->getRevisionById($revId);
		}

		WikiPage::onArticleEdit($title, $revision);
	}

	public function purge($page, bool $recursive): void
	{
		$page->doPurge();
		$page->updateParserCache(['causeAction' => 'api-purge']);
		$page->doSecondaryDataUpdates([
			'recursive' => $recursive,
			'causeAction' => 'api-purge',
			'defer' => DeferredUpdates::PRESEND,
		]);
	}

	public function replaceLinkHoldersText(Parser $parser, string $text): string
	{
		// Make $parser->replaceLinkHoldersText() available via reflection. This is blatantly "a bad thing", but as of
		// MW 1.35, it's the only way to implement the functionality which was previously public. Failing this, it's
		// likely we'll have to go back to Regex. Alternatively, the better version may be possible to implement via
		// preprocessor methods as originally designed, but with a couple of adaptations...needs to be re-checked.
		// Code derived from top answer here: https://stackoverflow.com/questions/2738663/call-private-methods-and-private-properties-from-outside-a-class-in-php
		$reflector = new ReflectionObject($parser);
		$replaceLinks = $reflector->getMethod('replaceLinkHoldersText');
		$replaceLinks->setAccessible(true);
		return $replaceLinks->invoke($parser, $text);
	}

	public function saveContent(LinkTarget $target, Content $content, string $editSummary, User $user, int $flags = 0)
	{
		$title = $target instanceof Title ? $target : Title::newFromLinkTarget($target);
		$page = WikiPage::factory($title);
		$updater = $page->newPageUpdater($user);
		$updater->setContent(SlotRecord::MAIN, $content);
		$updater->setFlags($flags);
		$comment = CommentStoreComment::newUnsavedComment($editSummary);
		$updater->saveRevision($comment, 0);
	}

	public function setPageProperty(ParserOutput $output, string $name, $value): void
	{
		$output->setProperty($name, $value);
	}

	public function setPreprocessor(Parser $parser, $preprocessor): void
	{
		$reflectionClass = new ReflectionClass('Parser');
		$reflectionProp = $reflectionClass->getProperty('mPreprocessor');
		$reflectionProp->setAccessible(true);
		$reflectionProp->setValue($parser, $preprocessor);
	}

	public function specialPageExists(Title $title): bool
	{
		return MediaWikiServices::getInstance()->getSpecialPageFactory()->exists($title->getDBkey());
	}

	public function unsetPageProperty(ParserOutput $output, string $name): void
	{
		$output->unsetProperty($name);
	}

	public function updateBackLinks(Title $title, string $tableName): void
	{
		$jobs[] = HTMLCacheUpdateJob::newForBacklinks(
			$title,
			$tableName,
			['causeAction' => 'page-edit']
		);
	}

	/**
	 * @param Language $language
	 * @return ILanguageConverter
	 */
	private static function getLanguageConverter(Language $language): ILanguageConverter
	{
		return MediaWikiServices::getInstance()->getLanguageConverterFactory()->getLanguageConverter($language);
	}
}
