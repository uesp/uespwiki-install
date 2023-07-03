<?php

namespace FakeGraph;

use Parser;
use PPFrame;

/**
 * MediaWiki hooks for FakeGraph.
 */
class Hooks /* implements
	\MediaWiki\Hook\ParserFirstCallInitHook */
{
	/**
	 * Parser initialization.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return void
	 */
	public static function onParserFirstCallInit(Parser $parser): void
	{
		$parser->setHook('graph', [self::class, 'doGraph']);
	}

	/**
	 * Body of fake graph tag. Only adds page to tracking category.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return string
	 */
	public static function doGraph($input, array $args, Parser $parser, PPFrame $frame): string
	{
		$parser->addTrackingCategory('graph-disabled-category');
		return wfMessage('fakegraph-graph-notice')->inContentLanguage();
	}
}
