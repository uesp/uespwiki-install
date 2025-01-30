<?php

class BreadCrumbHooks
{
	/* Note that these should match the words in the i18n file */
	const PF_INITTRAIL = 'inittrail';
	const PF_SETTRAIL = 'settrail';
	const PF_ADDTOTRAIL = 'addtotrail';

	public static function onOutputPageParserOutput(OutputPage $out, ParserOutput $parserOutput)
	{
		// Transfers the trail from the ParserOutput object to the OutputPage object.
		$prop = $parserOutput->getExtensionData(BreadCrumb::VAL_TRAIL) ?? '';
		if ($prop !== '') {
			$out->setProperty(BreadCrumb::VAL_TRAIL, $prop);
		}
	}

	public static function onParserAfterTidy(Parser $parser, string &$text)
	{
		// Parses the full trail and sets it as a page property.
		$output = $parser->getOutput();
		$trail = $output->getExtensionData(BreadCrumb::VAL_TRAIL) ?? '';
		if ($trail !== '') {
			$parsed = BreadCrumb::parse($parser, $trail);
			$output->setExtensionData(BreadCrumb::VAL_TRAIL, $parsed);
			switch (BreadCrumb::getSetting(BreadCrumb::STTNG_SAVEAS)) {
				case 'wiki':
				case 'wikitext':
					VersionHelper::getInstance()->setPageProperty($output, BreadCrumb::VAL_TRAIL, $trail);
					break;
				case 'html':
					VersionHelper::getInstance()->setPageProperty($output, BreadCrumb::VAL_TRAIL, $parsed);
					break;
				default:
					return;
			}
		}
	}

	public static function onParserFirstCallInit(Parser $parser)
	{
		$parser->setFunctionHook(self::PF_ADDTOTRAIL, [BreadCrumb::class, 'doAddToTrail'], Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_INITTRAIL, [BreadCrumb::class, 'doInitTrail'], Parser::SFH_OBJECT_ARGS);
		$parser->setFunctionHook(self::PF_SETTRAIL, [BreadCrumb::class, 'doSetTrail'], Parser::SFH_OBJECT_ARGS);
	}

	public static function onSkinSubPageSubtitle(string &$subpages, Skin $skin, OutputPage $out)
	{
		// Retrieves the trail from the OutputPage object and sets it for display.
		$subpages = $out->getProperty(BreadCrumb::VAL_TRAIL);
		return is_null($subpages);
	}
};
