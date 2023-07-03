<?php

use MediaWiki\MediaWikiServices;

/**
 * See base class for documentation.
 */
class VersionHelper35 extends VersionHelper
{
	public function getMagicWord($id): MagicWord
	{
		return MediaWikiServices::getInstance()->getMagicWordFactory()->get($id);
	}

	public function getStripState(Parser $parser): StripState
	{
		return $parser->getStripState();
	}

	public function replaceLinkHoldersText(Parser $parser, string $output): string
	{
		// Make $parser->replaceLinkHoldersText() available via reflection. This is blatantly "a bad thing", but as of
		// MW 1.35, it's the only way to implement the functionality which was previously public. Failing this, it's
		// likely we'll have to go back to Regex. Alternatively, the better version may be possible to implement via
		// preprocessor methods as originally designed, but with a couple of adaptations...needs to be re-checked.
		// Code derived from top answer here: https://stackoverflow.com/questions/2738663/call-private-methods-and-private-properties-from-outside-a-class-in-php
		$reflector = new ReflectionObject($parser);
		$replaceLinks = $reflector->getMethod('replaceLinkHoldersText');
		$replaceLinks->setAccessible(true);
		return $replaceLinks->invoke($parser, $output);
	}
}
