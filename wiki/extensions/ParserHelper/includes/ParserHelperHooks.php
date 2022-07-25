<?php

use MediaWiki\MediaWikiServices;

class ParserHelperHooks
{
	/**
	 * Register callbacks with the parser.
	 *
	 * @param Parser $parser The parser to register with.
	 *
	 * @return void
	 */
	public static function onParserFirstCallInit(Parser $parser)
	{
		ParserHelper::init();
	}
}
