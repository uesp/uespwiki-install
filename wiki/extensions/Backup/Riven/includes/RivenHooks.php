<?php

/* To disable tags, comment out lines in $tagInfo.
 * To disable variables, comment out lines in onMagicWordwgVariableIDs.
 * To disable parser functions, comment out lines in initParserFunctions.
 */

/**
 * MediaWiki hooks for Riven.
 */
class RivenHooks /* implements
	\MediaWiki\Hook\ParserFirstCallInitHook */
{
	// Parser Functions
	public const PF_ARG         = 'arg'; // From DynamicFunctions
	public const PF_EXPLODEARGS = 'explodeargs';
	public const PF_FINDFIRST   = 'findfirst';
	public const PF_IFEXISTX    = 'ifexistx';
	public const PF_INCLUDE     = 'include';
	public const PF_LABEL       = 'label';
	public const PF_LABELNAME   = 'LABELNAME';
	public const PF_PICKFROM    = 'pickfrom';
	public const PF_RAND        = 'rand'; // From DynamicFunctions
	public const PF_SORTABLE    = 'sortable';
	public const PF_SPLITARGS   = 'splitargs';
	public const PF_TRIMLINKS   = 'trimlinks';

	// Tags
	public const TG_CLEANSPACE  = 'cleanspace';
	public const TG_CLEANTABLE  = 'cleantable';

	// Variables
	public const VR_SKINNAME    = 'skinname'; // From DynamicFunctions

	/**
	 * Register variables.
	 *
	 * @param array $aCustomVariableIds The current list of variables.
	 *
	 * @return void
	 *
	 */
	public static function onMagicWordwgVariableIDs(array &$aCustomVariableIds): void
	{
		$aCustomVariableIds[] = self::PF_LABELNAME;
		$aCustomVariableIds[] = self::VR_SKINNAME;
	}

	/**
	 * Parser initialization.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return void
	 *
	 */
	public static function onParserFirstCallInit(Parser $parser): void
	{
		/* Force parser to use Preprocessor_Hash if it isn't already since this entire extension is predicated on that
		 * assumption. In later versions, Preprocessor_Hash is the only built-in option anyway. This should work up to
		 * 1.34. After that, PPNode_DOM no longer exists, so PPNode_Hash should always be in use.
		 */
		if (get_class($parser->getPreprocessor()) === 'PPNode_DOM') {
			VersionHelper::getInstance()->setPreprocessor($parser, new Preprocessor_Hash($parser));
		}

		self::initParserFunctions($parser);
		self::initTagFunctions($parser);
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
	 * @return bool Always true, per Wikipedia documentation.
	 *
	 */
	public static function onParserGetVariableValueSwitch(Parser $parser, array &$variableCache, $magicWordId, &$ret, PPFrame $frame): bool
	{
		switch ($magicWordId) {
			case self::PF_LABELNAME:
				$ret = Riven::doLabelName($parser);
				break;
			case self::VR_SKINNAME:
				$ret = Riven::doSkinName($parser);
			default:
				return true;
		}

		$variableCache[$magicWordId] = $ret;
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
	private static function initParserFunctions(Parser $parser): void
	{
		$parser->setFunctionHook(self::PF_ARG,         'Riven::doArg');
		$parser->setFunctionHook(self::PF_EXPLODEARGS, 'Riven::doExplodeargs', Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_FINDFIRST,   'Riven::doFindFirst', Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_IFEXISTX,    'Riven::doIfExistX', Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_INCLUDE,     'Riven::doInclude', Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_LABEL,       'Riven::doLabel');
		$parser->setFunctionHook(self::PF_LABELNAME,   'Riven::doLabelName', Parser::SFH_NO_HASH);
		$parser->setFunctionHook(self::PF_PICKFROM,    'Riven::doPickFrom', Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_RAND,        'Riven::doRand', Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_SORTABLE,    'Riven::doSortable');
		$parser->setFunctionHook(self::PF_SPLITARGS,   'Riven::doSplitargs', Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_TRIMLINKS,   'Riven::doTrimLinks', Parser::SFH_OBJECT_ARGS);
	}

	/**
	 * Register all tag functions.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return void
	 *
	 */
	private static function initTagFunctions(Parser $parser): void
	{
		ParserHelper::setHookSynonyms($parser, self::TG_CLEANSPACE, 'Riven::doCleanSpace');
		ParserHelper::setHookSynonyms($parser, self::TG_CLEANTABLE, 'Riven::doCleanTable');
	}
}
