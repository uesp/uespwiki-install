<?php

use MediaWiki\MediaWikiServices;

/* To disable tags, comment out lines in $tagInfo.
 * To disable variables, comment out lines in onMagicWordwgVariableIDs.
 * To disable parser functions, comment out lines in initParserFunctions.
 */

/**
 * MediaWiki hooks for Riven.
 */
class RivenHooks
{
	/** Tag hooks. To disable a tag, comment the line out. */
	private static $tagInfo = [
		Riven::TG_CLEANSPACE => 'Riven::doCleanSpace',
		Riven::TG_CLEANTABLE => 'Riven::doCleanTable'
	];

	/**
	 * Register variables.
	 *
	 * @param array $aCustomVariableIds The current list of variables.
	 *
	 * @return void
	 *
	 */
	public static function onMagicWordwgVariableIDs(array &$aCustomVariableIds)
	{
		$aCustomVariableIds[] = Riven::VR_SKINNAME;
	}

	/**
	 * Parser initialization.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return void
	 *
	 */
	public static function onParserFirstCallInit(Parser $parser)
	{
		ParserHelper::init();
		self::initParserFunctions($parser);
		self::initTagFunctions($parser);
		Riven::init();
	}

	/**
	 * Get variable values.
	 *
	 * @param Parser $parser The parser in use.
	 * @param array $variableCache The magic word variable cache.
	 * @param mixed $magicWordId The magic word id being sought.
	 * @param mixed $ret Return value.
	 * @param PPFrame $frame The frame in use.
	 *
	 * @return bool
	 *
	 */
	public static function onParserGetVariableValueSwitch(Parser $parser, array &$variableCache, $magicWordId, &$ret, PPFrame $frame)
	{
		switch ($magicWordId) {
			case Riven::VR_SKINNAME:
				$ret = Riven::doSkinName($parser);

				// Cached, but only for the current request (presumably), since user could change their settings at any
				// time.
				$variableCache[$magicWordId] = $ret;
				$parser->getOutput()->updateCacheExpiry(5);
		}

		return true;
	}

	/**
	 * Register all parser functions.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return void
	 *
	 */
	private static function initParserFunctions(Parser $parser)
	{
		$parser->setFunctionHook(Riven::PF_ARG, 'Riven::doArg', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_EXPLODEARGS, 'Riven::doExplodeargs', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_FINDFIRST, 'Riven::doFindFirst', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_IFEXISTX, 'Riven::doIfExistX', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_INCLUDE, 'Riven::doInclude', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_PICKFROM, 'Riven::doPickFrom', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_RAND, 'Riven::doRand', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_SPLITARGS, 'Riven::doSplitargs', SFH_OBJECT_ARGS);
		$parser->setFunctionHook(Riven::PF_TRIMLINKS, 'Riven::doTrimLinks', SFH_OBJECT_ARGS);
	}

	/**
	 * Register all tag functions.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return void
	 *
	 */
	private static function initTagFunctions(Parser $parser)
	{
		foreach (self::$tagInfo as $key => $value) {
			ParserHelper::setHookSynonyms($parser, $key, $value);
		}
	}
}
