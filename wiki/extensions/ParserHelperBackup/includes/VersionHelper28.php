<?php

/**
 * See base class for documentation.
 */
class VersionHelper28 extends VersionHelper
{
	public function getMagicWord(string $id): MagicWord
	{
		return MagicWord::get($id);
	}

	public function getStripState(Parser $parser): StripState
	{
		return $parser->mStripState;
	}

	public function replaceLinkHoldersText(Parser $parser, string $output): string
	{
		return $parser->replaceLinkHoldersText($output);
	}
}
