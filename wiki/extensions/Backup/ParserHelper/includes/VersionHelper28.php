<?php

use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;

/**
 * See base class for documentation.
 */
class VersionHelper28 extends VersionHelper
{
	/** @param ?Revision $revision */
	public function doSecondaryDataUpdates(WikiPage $page, ParserOutput $parserOutput, ParserOptions $options): void
	{
		$title = $page->getTitle();
		$content = $page->getContent();

		// Even though we will in many cases have just parsed the output, there's no reliable way to get it from there
		// to here, so we ask for it again. The parser cache should make this relatively fast.
		$updates = $content->getSecondaryDataUpdates($title, null, true, $parserOutput);
		foreach ($updates as $update) {
			DeferredUpdates::addUpdate($update, DeferredUpdates::PRESEND);
		}

		try {
			MediaWikiServices::getInstance()->getParserCache()->save($parserOutput, $page, $options);
		} catch (Exception $e) {
		}
	}

	public function fileExists(Title $title): bool
	{
		$file = RepoGroup::singleton()->getLocalRepo()->newFile($title);
		return (bool)$file && $file->exists();
	}

	public function findVariantLink(Parser $parser, string &$titleText, ?Title &$title, $ignoreOtherCond = false): void
	{
		$language = $parser->getTargetLanguage();
		if ($language->hasVariants()) {
			$language->findVariantLink($titleText, $title, $ignoreOtherCond);
		}
	}

	public function getContentLanguage(): Language
	{
		global $wgContLang;
		return $wgContLang;
	}

	public function getLatestRevision(WikiPage $page)
	{
		return $page->getRevision();
	}

	public function getMagicWord(string $id): MagicWord
	{
		return MagicWord::get($id);
	}

	public function getPageProperty(ParserOutput $output, string $name): ?string
	{
		$retval = $output->getProperty($name);
		return $retval === false
			? null
			: $retval;
	}

	public function getPageText(LinkTarget $target): ?string
	{
		$title = $target instanceof Title ? $target : Title::newFromLinkTarget($target);
		$page = WikiPage::factory($title);
		return $page->getRevision()->getSerializedData();
	}

	public function getParserTitle(Parser $parser)
	{
		return $parser->getTitle();
	}

	public function getStripState(Parser $parser): StripState
	{
		return $parser->mStripState;
	}

	public function getWikiPage(LinkTarget $link): WikiPage
	{
		return $link instanceof Title
			? WikiPage::factory($link)
			: WikiPage::factory(Title::newFromLinkTarget($link));
	}

	public function handleInternalLinks(Parser $parser, string $text): string
	{
		return $parser->replaceInternalLinks($text);
	}

	/** @param ?Revision $revision */
	public function onArticleEdit(Title $title, $revId): void
	{
		if ($revId instanceof Parser) {
			$revision = $revId->getRevisionObject();
		} else {
			$revision = Revision::newFromId($revId);
		}

		WikiPage::onArticleEdit($title, $revision);
	}

	/** @param ?Revision $revision */
	public function purge($page, bool $recursive): void
	{
		$content = $page->getContent(Revision::RAW);
		if (!$content) {
			return;
		}

		// Even though we will in many cases have just parsed the output, there's no reliable way to get it from there
		// to here, so we ask for it again. The parser cache should make this relatively fast.
		$title = $page->getTitle();
		$popts = $page->makeParserOptions('canonical');
		$enableParserCache = MediaWikiServices::getInstance()->getMainConfig()->get('EnableParserCache');
		$parserOutput = $content->getParserOutput($title, $page->getLatest(), $popts, $enableParserCache);
		$updates = $content->getSecondaryDataUpdates($title, null, $recursive, $parserOutput);
		foreach ($updates as $update) {
			DeferredUpdates::addUpdate($update, DeferredUpdates::PRESEND);
		}

		if ($enableParserCache) {
			MediaWikiServices::getInstance()->getParserCache()->save($parserOutput, $page, $popts);
		}
	}

	public function replaceLinkHoldersText(Parser $parser, string $text): string
	{
		return $parser->replaceLinkHoldersText($text);
	}

	public function saveContent(LinkTarget $target, Content $content, string $editSummary, User $user, int $flags = 0)
	{
		$title = $target instanceof Title ? $target : Title::newFromLinkTarget($target);
		$page = WikiPage::factory($title);
		$page->doEditContent(
			$content,
			$editSummary,
			$flags,
			false,
			$user
		);
	}

	public function setPageProperty(ParserOutput $output, string $name, $value): void
	{
		$output->setProperty($name, $value);
	}

	public function setPreprocessor(Parser $parser, $preprocessor): void
	{
		$propName = 'mPreprocessorClass'; // Call by name to avoid error from property not being defined in Parser.
		$parser->$propName = get_class($preprocessor);
		$parser->mPreprocessor = $preprocessor;
	}

	public function specialPageExists(Title $title): bool
	{
		return SpecialPageFactory::exists($title->getDBkey());
	}

	public function unsetPageProperty(ParserOutput $output, string $name): void
	{
		$output->unsetProperty($name);
	}

	public function updateBackLinks(Title $title, string $tableName): void
	{
		LinksUpdate::queueRecursiveJobsForTable($title, $tableName);
	}
}
