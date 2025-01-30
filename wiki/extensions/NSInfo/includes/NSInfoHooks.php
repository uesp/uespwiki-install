<?php

class NSInfoHooks
{
	/**
	 * Enables NSInfo's variables.
	 *
	 * @param array $aCustomVariableIds The list of custom variables to add to.
	 */
	public static function onMagicWordwgVariableIDs(array &$aCustomVariableIds): void
	{
		$aCustomVariableIds[] = NSInfo::PF_ISGAMESPACE;
		$aCustomVariableIds[] = NSInfo::PF_ISMODSPACE;
		$aCustomVariableIds[] = NSInfo::PF_MOD_NAME;
		$aCustomVariableIds[] = NSInfo::PF_NS_BASE;
		$aCustomVariableIds[] = NSInfo::PF_NS_CATEGORY;
		$aCustomVariableIds[] = NSInfo::PF_NS_FULL;
		$aCustomVariableIds[] = NSInfo::PF_NS_ID;
		$aCustomVariableIds[] = NSInfo::PF_NS_MAINPAGE;
		$aCustomVariableIds[] = NSInfo::PF_NS_NAME;
		$aCustomVariableIds[] = NSInfo::PF_NS_PARENT;
		$aCustomVariableIds[] = NSInfo::PF_NS_TRAIL;
	}

	/**
	 * Initialize parser functions followed by NSInfo general initialization.
	 *
	 * @param Parser $parser The parser in use.
	 */
	public static function onParserFirstCallInit(Parser $parser): void
	{
		$parser->setFunctionHook(NSInfo::PF_ISGAMESPACE, 'NSInfo::doIsGameSpace', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_ISMODSPACE, 'NSInfo::doIsModSpace', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_MOD_NAME, 'NSInfo::doModName', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_MOD_PARENT, 'NSInfo::doModParent', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_BASE, 'NSInfo::doNsBase', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_CATEGORY, 'NSInfo::doNsCategory', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_CATLINK, 'NSInfo::doNsCatlink', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_FULL, 'NSInfo::doNsFull', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_ID, 'NSInfo::doNsId', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_MAINPAGE, 'NSInfo::doNsMainPage', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_NAME, 'NSInfo::doNsName', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_PARENT, 'NSInfo::doNsParent', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
		$parser->setFunctionHook(NSInfo::PF_NS_TRAIL, 'NSInfo::doNsTrail', Parser::SFH_OBJECT_ARGS | Parser::SFH_NO_HASH);
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
	 * @return true Always true
	 */
	public static function onParserGetVariableValueSwitch(Parser $parser, array &$variableCache, $magicWordId, &$ret, PPFrame $frame): bool
	{
		switch ($magicWordId) {
			case NSInfo::PF_ISGAMESPACE:
				$ret = NSInfo::doIsGameSpace($parser, $frame);
				break;
			case NSInfo::PF_ISMODSPACE:
				$ret = NSInfo::doIsModSpace($parser, $frame);
				break;
			case NSInfo::PF_MOD_NAME:
				$ret = NSInfo::doModName($parser, $frame);
				break;
			case NSInfo::PF_MOD_PARENT:
				$ret = NSInfo::doModParent($parser, $frame);
				break;
			case NSInfo::PF_NS_BASE:
				$ret = NSInfo::doNsBase($parser, $frame);
				break;
			case NSInfo::PF_NS_CATEGORY:
				$ret = NSInfo::doNsCategory($parser, $frame);
				break;
			case NSInfo::PF_NS_FULL:
				$ret = NSInfo::doNsFull($parser, $frame);
				break;
			case NSInfo::PF_NS_ID:
				$ret = NSInfo::doNsId($parser, $frame);
				break;
			case NSInfo::PF_NS_MAINPAGE:
				$ret = NSInfo::doNsMainPage($parser, $frame);
				break;
			case NSInfo::PF_NS_NAME:
				$ret = NSInfo::doNsName($parser, $frame);
				break;
			case NSInfo::PF_NS_PARENT:
				$ret = NSInfo::doNsParent($parser, $frame);
				break;
			case NSInfo::PF_NS_TRAIL:
				$ret = NSInfo::doNsTrail($parser, $frame);
				break;
			default:
				return true;
		}

		$parser->addTrackingCategory('nsinfo-tracking-variable');

		return true;
	}
	#endregion
}
