<?php

use MediaWiki\MediaWikiServices;

/**
 * See base class for documentation.
 */
class VersionHelper35 extends VersionHelper
{
	public function fileExists(Title $title): bool
	{
		return MediaWikiServices::getInstance()->getRepoGroup()->findFile($title);
	}

	public function findVariantLink(Parser $parser, string &$titleText, ?Title &$title): void
	{
		$lc = self::getLanguageConverter($parser->getContentLanguage());
		if ($lc->hasVariants()) {
			$lc->findVariantLink($titleText, $title, true);
		}
	}

	public function getMagicWord($id): MagicWord
	{
		return MediaWikiServices::getInstance()->getMagicWordFactory()->get($id);
	}

	public function getStripState(Parser $parser): StripState
	{
		return $parser->getStripState();
	}

	public function handleInternalLinks(Parser $parser, string $text): string
	{
		$reflector = new ReflectionObject($parser);
		$replaceLinks = $reflector->getMethod('handleInternalLinks');
		$replaceLinks->setAccessible(true);
		return $replaceLinks->invoke($parser, $text);
	}

	public function onArticleEdit(Title $title, Parser $parser): void
	{
		WikiPage::onArticleEdit($title, $parser->getRevisionRecordObject());
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

	public function updateBackLinks(Title $title, string $tableName): void
	{
		$jobs[] = HTMLCacheUpdateJob::newForBacklinks(
			$title,
			$tableName,
			['causeAction' => 'page-edit']
		);
	}

	/**
	 * @since 1.35
	 * @param Language $language
	 * @return ILanguageConverter
	 */
	private static function getLanguageConverter(Language $language): ILanguageConverter
	{
		return MediaWikiServices::getInstance()->getLanguageConverterFactory()->getLanguageConverter($language);
	}
}
