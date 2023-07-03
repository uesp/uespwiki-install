<?php

use MediaWiki\MediaWikiServices;

/**
 * A collection of various routines that primarily help in template editing. These were split out from MetaTemplate
 * and/or DynamicFunctions since they don't rely on the preprocessor in order to work (or if they do, there are
 * non-preprocessor alternatives).
 *
 * The rarely used functions are all put into "Riven-Pages Using <feature>" tracking categories.
 *
 * @todo Rework relevant function to go back to using the pre-processor now that we know it's sticking around for a
 * while. This should be FAR faster than text-based methods.
 */
class Riven
{
	public const AV_ORIGINAL  = 'riven-original';
	public const AV_RECURSIVE = 'riven-recursive';
	public const AV_SMART     = 'riven-smart';
	public const AV_TOP       = 'riven-top';

	public const NA_ALLOWEMPTY = 'riven-allowempty';
	public const NA_CACHETIME   = 'riven-cachetime';
	public const NA_CLEANIMG   = 'riven-cleanimages';
	public const NA_DELIMITER  = 'riven-delimiter';
	public const NA_EXPLODE    = 'riven-explode';
	public const NA_MODE       = 'riven-mode';
	public const NA_SEED       = 'riven-seed';

	private const TRACKING_ARG      = 'riven-tracking-arg';
	private const TRACKING_PICKFROM = 'riven-tracking-pickfrom';
	private const TRACKING_RAND     = 'riven-tracking-rand';
	private const TRACKING_SKINNAME = 'riven-tracking-skinname';

	private const TAG_REGEX = '</?[0-9A-Za-z]+(\s[^>]*)?>';

	/**
	 * Retrieves an argument from the URL.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *     1: The name of the argument to look for.
	 *     2: If the argument above isn't found, return this value instead.
	 *
	 * @return ?string The value found or the default value. Failing all else,
	 */
	public static function doArg(Parser $parser, PPFrame $frame, array $args): ?string
	{
		$parser->addTrackingCategory(self::TRACKING_ARG);
		$parser->getOutput()->updateCacheExpiry(0);
		$arg = $frame->expand($args[0]);
		$default = isset($args[1]) ? $frame->expand($args[1]) : '';
		$request = RequestContext::getMain()->getRequest();
		return $request->getVal($arg, $default);
	}

	/**
	 * Removes whitespace surrounding HTML tags, links and other parser functions.
	 *
	 * @param string $content The content to clean.
	 * @param array $attributes The tag attributes:
	 *     debug: Set to PHP true to show the cleaned code on-screen during Show Preview. Set to 'always' to show even
	 *            when saved.
	 *     mode:  Select strategy for removal. Note that in the first two modes, this is an intelligent search and will
	 *            only match what the wiki identifies as links and templates.
	 *         top:       Only remove space at the top-most level...will not search inside links or templates (but can
	 *                    search inside tags).
	 *         recursive: (disabled for now) Search everything.
	 *         original:  This is the default, using The original regex-based search. This can sometimes result in
	 *                    unwanted matches.
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 *
	 * @return string Cleaned text.
	 *
	 */
	public static function doCleanSpace(string $content, array $attributes, Parser $parser, PPFrame $frame)
	{
		static $magicWords;
		static $modeWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_DEBUG,
			self::NA_MODE
		]);

		$args = ParserHelper::transformAttributes($attributes, $magicWords);

		if ($frame instanceof PPTemplateFrame_Hash) {
			$modeWords = $modeWords ?? new MagicWordArray([
				self::AV_RECURSIVE,
				self::AV_TOP,
				self::AV_ORIGINAL
			]);

			$match = $modeWords->matchStartToEnd($args[self::NA_MODE] ?? self::AV_ORIGINAL);
			$modeWord = $match === false ? self::AV_ORIGINAL : $match;
		} else {
			$modeWord = self::AV_ORIGINAL;
		}

		$retval = $content;
		if ($modeWord !== self::AV_ORIGINAL) {
			$retval = preg_replace('#<!--.*?-->#s', '', $retval);
		}

		$retval = trim($retval);
		switch ($modeWord) {
				/*
            case self::AV_RECURSIVE:
                $retval = self::cleanSpacePP($retval, $parser, $frame, true);
                break; */
			case self::AV_TOP:
				$retval = self::cleanSpacePP($parser, $frame, $retval);
				break;
			default:
				$retval = self::cleanSpaceOriginal($retval);
				break;
		}

		if (ParserHelper::checkDebugMagic($parser, $frame, $args)) {
			return ParserHelper::formatTagForDebug($retval, true);
		}

		// Categories and trails are stripped on ''any'' template page, not just when directly calling the template
		// (but not in preview mode).
		if ($parser->getTitle()->getNamespace() === NS_TEMPLATE) {
			// Save categories before processing.
			$precats = $parser->getOutput()->getCategories();
			$retval = $parser->recursiveTagParse($retval, $frame);
			// Reset categories to the pre-processing list to remove any new categories.
			$parser->getOutput()->setCategoryLinks($precats);
		} else {
			$retval = $parser->recursiveTagParse($retval, $frame);
		}

		return [$retval, 'markerType' => 'general'];
	}

	/**
	 * Cleans a table of all empty rows.
	 *
	 * @param string $content The text containing the tables to clean.
	 * @param array $attributes The tag attributes:
	 *     cleanimages: Whether to remove image-only cells or count them as content.
	 *           debug: Set to PHP true to show the cleaned table code on-screen during Show Preview. Set to 'always'
	 *                  to show even when saved.
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The templare frame in use.
	 *
	 * @return string Cleaned text.
	 *
	 */
	public static function doCleanTable(string $content, array $attributes, Parser $parser, PPFrame $frame): array
	{
		#RHshow('doCleanTable wiki text', $content);

		// This ensures that tables are not cleaned if being displayed directly on the Template page.
		// Previewing will process cleantable normally.
		if (
			$frame->depth == 0 &&
			$parser->getTitle()->getNamespace() == NS_TEMPLATE &&
			!$parser->getOptions()->getIsPreview()
		) {
			return $content;
		}

		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_DEBUG,
			self::NA_CLEANIMG
		]);

		#RHshow('Pre-transform', $attributes);
		$attributes = ParserHelper::transformAttributes($attributes, $magicWords);
		#RHshow('Post-transform', $attributes);
		$text = $parser->recursiveTagParse($content, $frame);
		#RHshow('Tag Parsed', $text);

		$text = VersionHelper::getInstance()->getStripState($parser)->unstripNoWiki($text);
		$offset = 0;
		$output = '';
		$lastVal = null;
		$cleanImages = intval($attributes[self::NA_CLEANIMG] ?? 1);
		do {
			$lastVal = self::parseTable($parser, $text, $offset, $cleanImages);
			$output .= $lastVal;
		} while ($lastVal);

		$output = VersionHelper::getInstance()->getStripState($parser)->unstripGeneral($output);
		$after = substr($text, $offset);
		$output .= $after;

		$debug = ParserHelper::checkDebugMagic($parser, $frame, $attributes);
		return $debug
			? ['<pre>' . htmlspecialchars($output) . '</pre>', 'markerType' => 'nowiki']
			: [$output, 'preprocessFlags' => PPFrame::RECOVER_ORIG];
	}

	/**
	 * A variant of #splitargs. See that function for details.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *              1: The template to call.
	 *              2: The number of parameters to split on.
	 *              3: The text to split.
	 *              4: (Optional) The delimiter. If specified, this overrides the named version of
	 *                 delimiter.
	 *     allowempty: If set to true, will display empty entries along with separators.
	 *          debug: Set to PHP true to show the cleaned code on-screen during Show Preview. Set to 'always' to show
	 *                 even when saved.
	 *      delimiter: The character(s) that separate one value from the next in the input text. Defaults to a comma.
	 *             if: A condition that must be true in order for this function to run.
	 *          ifnot: A condition that must be false in order for this function to run.
	 *      separator: The character(s) to display between each value in the output text. Defaults to an empty string.
	 *
	 * @return string The list of functions to call as a reuslt of the split.
	 *
	 */
	public static function doExplodeArgs(Parser $parser, PPFrame $frame, array $args)
	{
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			self::NA_ALLOWEMPTY,
			self::NA_DELIMITER,
			ParserHelper::NA_DEBUG,
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT,
			ParserHelper::NA_SEPARATOR
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (!ParserHelper::checkIfs($frame, $magicArgs)) {
			return '';
		}

		#RHshow('Magic Args', $magicArgs);

		// show("Passed if check:\n", $values, "\nDupes:\n", $dupes);
		/**
		 * @var array $named
		 * @var array $values
		 */
		[$named, $values] = ParserHelper::splitNamedArgs($frame, $values);
		$templateName = trim($frame->expand($values[0] ?? ''));
		$nargs = (int)trim($frame->expand($values[1] ?? '0'));
		$delimiter = $frame->expand(
			isset($values[3])
				? $values[3]
				: $magicArgs[self::NA_DELIMITER] ?? ','
		);

		$values = $frame->expand($values[2] ?? '');
		$values = explode($delimiter, $values);

		#RHshow('Template Name', $templateName);
		#RHshow('Num Args', $nargs);
		#RHshow('Delimiter', $delimiter);
		#RHshow('Explode Named', $named);
		#RHshow('Explode Values', $values);
		return self::splitArgsCommon($parser, $frame, $magicArgs, $templateName, $nargs, $named, $values);
	}

	/**
	 * Finds the first page that exists in the list of parameters.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *        1+: All unnamed parameters are page names to search.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return string The first title that exists.
	 *
	 */
	public static function doFindFirst(Parser $parser, PPFrame $frame, array $args): string
	{
		// This is just a loop over the core of #ifexistx. Timing tests on other methods have so far failed thanks to
		// the existing cache mechanisms behind title checks.
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (!ParserHelper::checkIfs($frame, $magicArgs)) {
			return '';
		}

		// This is a bit kludgey, but the idea is that we want to avoid this being counted as expensive, possibly
		// several times, so we first eliminate any duplicate titles, then check if they exist. Note that the
		// elimination algorithm is naive and ~O(n^2), but the number of values is expected to always be negligible. If
		// needed, a limiter could be added to make sure that's the case.
		$uniqueTitles = [];
		$titleTexts = [];
		foreach ($values as $value) {
			$titleText = trim($frame->expand($value));
			$title = Title::newFromText($titleText);
			VersionHelper::getInstance()->findVariantLink($parser, $titleText, $title, true);
			if ($title) {
				$found = false;
				foreach ($uniqueTitles as $unique) {
					if ($title->equals($unique)) {
						$found = true;
						break;
					}
				}

				if (!$found) {
					$titleTexts[] = $titleText;
				}
			}
		}

		foreach ($titleTexts as $titleText) {
			if (self::findTitle($parser, $titleText)) {
				return $titleText;
			}
		}

		return '';
	}

	/**
	 * Checks for the existence of a page without tagging it as a Wanted Page.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *         1: The page to look for.
	 *         2: The return value if the page is found.
	 *         3: The return value if the page is not found.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return array The result of the check or an empty string if the if/ifnot failed.
	 *
	 */
	public static function doIfExistX(Parser $parser, PPFrame $frame, array $args): string
	{
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (!ParserHelper::checkIfs($frame, $magicArgs)) {
			return '';
		}

		$titleText = trim($frame->expand($values[0] ?? ''));
		$index = is_null(self::findTitle($parser, $titleText)) ? 2 : 1;
		return isset($values[$index])
			? trim($frame->expand($values[$index]))
			: '';
	}

	/**
	 * Transcludes a page if it exists, but if the page doesn't exist, it will not create either red links or Wanted
	 * Templates entries.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *        1+: The names of the templates to include.
	 *     debug: Set to PHP true to show the cleaned code on-screen during Show Preview. Set to 'always' to show even
	 *            when saved.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return string The template name to call, if found.
	 *
	 */
	public static function doInclude(Parser $parser, PPFrame $frame, array $args): array
	{
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_DEBUG,
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (count($values) <= 0 || !ParserHelper::checkIfs($frame, $magicArgs)) {
			return [''];
		}

		$output = '';
		foreach ($values as $titleText) {
			$titleText = trim($frame->expand($titleText));
			$title = self::findTitle($parser, $titleText, NS_TEMPLATE);
			if (!is_null($title)) {
				// show('Exists!');
				$outTitle = $title->getNamespace() === NS_TEMPLATE
					? $title->getText()
					: $title->getPrefixedText();
				$output .= '{{' . $outTitle . '}}';
			}
		}

		$debug = ParserHelper::checkDebugMagic($parser, $frame, $magicArgs);
		return ParserHelper::formatPFForDebug($output, $debug, false);
	}

	/**
	 * Randomly picks one or more entries from a list and displays it.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *             1+: The values to pick from.
	 *     allowempty: If set to true, will display empty entries along with separators.
	 *             if: A condition that must be true in order for this function to run.
	 *          ifnot: A condition that must be false in order for this function to run.
	 *           seed: The number to use to initialize the random sequence.
	 *      separator: The separator to use between entries. Defaults to \n.
	 *
	 * @return string The randomized result.
	 *
	 */
	public static function doPickFrom(Parser $parser, PPFrame $frame, array $args): string
	{
		$parser->addTrackingCategory(self::TRACKING_PICKFROM);
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			self::NA_ALLOWEMPTY,
			self::NA_CACHETIME,
			self::NA_SEED,
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT,
			ParserHelper::NA_SEPARATOR
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (count($values) > 0) {
			$npick = (int)trim($values[0]);
			unset($values[0]);
		}

		if ($npick <= 0 || !count($values) || !ParserHelper::checkIfs($frame, $magicArgs)) {
			return '';
		}

		// We have to init every time otherwise previous seeds will affect current results (e.g., an hour-based
		// seed will cause all subsequent parameterless calls to mt_srand() to only generate hourly results).
		isset($seed) ? mt_srand((int)$seed) : mt_srand();

		// This makes the untested assumption that:
		//     shuffle(all values) then expand(needed values) will be significantly faster than
		//     expand(all values) then shuffle(needed values)
		shuffle($values);
		$allowEmpty = $magicArgs[self::NA_ALLOWEMPTY] ?? false;
		$retval = [];
		foreach ($values as $value) {
			$value = trim($frame->expand($value));
			if (strlen($value) > 0 || $allowEmpty) {
				$retval[] = $value;
				if (!--$npick) {
					break;
				}
			}
		}

		$cacheExpiry = isset($magicArgs[self::NA_CACHETIME]) ? (int)$magicArgs[self::NA_CACHETIME] : 0;
		$parser->getOutput()->updateCacheExpiry($cacheExpiry);
		$separator = ParserHelper::getSeparator($magicArgs);
		return implode($separator, $retval);
	}

	/**
	 * Picks a random number in the range provided.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *        1: (See return)
	 *        2: (See return)
	 *     seed: The number to use to initialize the random sequence.
	 *
	 * @return string
	 *     No parameters: Random number between 1-6.
	 *     One parameter: Random number between 1-{to}.
	 *     Both parameters: Random number between {from}-{to}.
	 *
	 */
	public static function doRand(Parser $parser, PPFrame $frame, array $args): string
	{
		$parser->addTrackingCategory(self::TRACKING_RAND);
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([self::NA_CACHETIME, self::NA_SEED]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (count($values) === 1) {
			$low = 1;
			$high = trim($frame->expand($values[0]));
		} else {
			$low = trim($frame->expand($values[0]));
			$high = trim($frame->expand($values[1]));
		}

		$low = strlen($low) ? (int)$low : 1;
		$high = strlen($high) ? (int)$high : 6;
		if ($low === $high) {
			return $low;
		}

		// We have to init every time otherwise previous seeds will affect current results (e.g., an hour-based seed
		// from a previous rand/pickfrom will cause all subsequent seedless calls to generate hourly results).
		isset($magicArgs[self::NA_SEED])
			? mt_srand((int)$magicArgs[self::NA_SEED])
			: mt_srand();
		$cacheExpiry = isset($magicArgs[self::NA_CACHETIME]) ? (int)$magicArgs[self::NA_CACHETIME] : 0;

		$parser->getOutput()->updateCacheExpiry($cacheExpiry);
		return ($low > $high)
			? mt_rand($high, $low)
			: mt_rand($low, $high);
	}

	/**
	 * Gets the user's current skin.
	 *
	 * @param Parser $parser The parser in use.
	 *
	 * @return string The name of the current skin.
	 */
	public static function doSkinName(Parser $parser): string
	{
		$parser->addTrackingCategory(self::TRACKING_SKINNAME);
		$parser->getOutput()->updateCacheExpiry(0); // Could be changed at any time so invalidate cache
		return RequestContext::getMain()->getSkin()->getSkinName();
	}

	/**
	 * Repetitively calls a template with different parameters for each call.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *              1: The template to call.
	 *              2: The number of parameters to split on.
	 *             3+: (Parameter variant) The values to split.
	 *     allowempty: If set to true, will display empty entries along with separators.
	 *          debug: Set to PHP true to show the cleaned code on-screen during Show Preview. Set to 'always' to show
	 *                 even when saved.
	 *      delimiter: The character(s) that separate one value from the next in the input text. Defaults to a comma.
	 *        explode: The text to explode when using that version of this function.
	 *             if: A condition that must be true in order for this function to run.
	 *          ifnot: A condition that must be false in order for this function to run.
	 *      separator: The character(s) to display between each value in the output text. Defaults to an empty string.
	 *
	 * @return string The text of all function calls after splitting.
	 */
	public static function doSplitArgs(Parser $parser, PPFrame $frame, array $args)
	{
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			self::NA_ALLOWEMPTY,
			self::NA_DELIMITER,
			self::NA_EXPLODE,
			ParserHelper::NA_DEBUG,
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT,
			ParserHelper::NA_SEPARATOR
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (!ParserHelper::checkIfs($frame, $magicArgs)) {
			return '';
		}

		// show("Passed if check:\n", $values, "\nDupes:\n", $dupes);
		[$named, $values] = ParserHelper::splitNamedArgs($frame, $values);
		if (!isset($values[1])) {
			return '';
		}

		// Figure out what we're dealing with and populate appropriately.
		$templateName = $frame->expand($values[0] ?? '');
		$nargs = (int)($frame->expand($values[1] ?? '0'));
		if (isset($magicArgs[self::NA_EXPLODE])) {
			// Explode
			#RHecho('Split Explode');
			$delimiter = $magicArgs[self::NA_DELIMITER] ?? ',';
			$orig = trim($frame->expand($magicArgs[self::NA_EXPLODE], PPFrame::RECOVER_ORIG));
			$values = explode($delimiter, $orig);
		} elseif (count($values) > 2) {
			#RHecho('Split Args');
			$slicedValues = array_slice($values, 2);
			$values = [];
			foreach ($slicedValues as $value) {
				$values[] = trim($frame->expand($value, PPFrame::NO_TEMPLATES));
			}
		} else {
			#RHecho('Split Frame');
			$values = [];
			// {{!}} now being a magic word broke MetaTemplate 1's handling of them, so for MetaTemplate 2, we reparse
			// the DOM values, leaving templates as text so that {{!}} and other such things work as expected. This
			// might break {{PAGENAMEx}}, though. If so, that seems like a corner case that probably can be written off
			// in the description as "don't do that".
			$flags = is_null($frame->parent)
				? PPFrame::NO_TEMPLATES | PPFrame::NO_ARGS
				: PPFrame::NO_TEMPLATES;
			$parent = $frame->parent ?? $frame;
			foreach ($frame->numberedArgs as $key => $value) {
				$values[$key - 1] = $parent->expand($value, $flags);
			}

			foreach ($frame->namedArgs as $key => $value) {
				$intKey = (int)$key;
				if ($intKey > 0 && !isset($values[$intKey])) {
					$values[$intKey - 1] = $parent->expand($value, $flags); // -1 because values is 0-based
				}
			}
		}

		#RHshow('Split Named', $named);
		#RHshow('Split Values', $values);
		return self::splitArgsCommon($parser, $frame, $magicArgs, $templateName, $nargs, $named, $values);
	}

	/**
	 * Trims links from a block of text.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *     mode: The only option currently is "smart", which uses the preprocessor to parse the code with near-perfect
	 *           results.
	 *
	 * @return string|array The resulting text after having links stripped.
	 */
	public static function doTrimLinks(Parser $parser, PPFrame $frame, array $args): array
	{
		if (!isset($args[0])) {
			return ['text' => '', 'noparse' => false];
		}

		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([self::NA_MODE]);
		$flag = Parser::PTD_FOR_INCLUSION; // was: $frame->depth ? Parser::PTD_FOR_INCLUSION : 0;

		/** @var array $magicArgs */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		$helper = VersionHelper::getInstance();
		$output = trim($frame->expand($values[0]));
		$smartMode = $frame instanceof PPFrame_Hash && ParserHelper::magicKeyEqualsValue($magicArgs, self::NA_MODE, self::AV_SMART);
		if ($smartMode) {
			/** @todo Have another look at this. The original approach may actually be doable. */
			// This was a lot simpler in the original implementation, working strictly by recursively parsing the root
			// node. MW 1.28 changed the preprocessor to be unresponsive to changes to its nodes, however,
			// necessitating this mess...which is still better than trying to create a new node structure.
			$rootNode = $parser->preprocessToDom($output, $flag);
			$output = self::trimLinksParseNode($parser, $frame, $rootNode);
			$output = $helper->getStripState($parser)->unstripBoth($output);
		} else {
			$output = $helper->getStripState($parser)->unstripBoth($output);
			$output = VersionHelper::getInstance()->handleInternalLinks($parser, $output);
			$output = preg_replace('#<a\ [^>]+selflink[^>]+>(.*?)</a>#', '$1', $output);
			$output = preg_replace('#<a\ href=[^>]+ title="(.*?)"><img\ [^>]+></a>#', '\1', $output);
			$output = preg_replace('#<a\ href=[^>]+ title="[^"]*?">(.+?)</a>#', '\1', $output);
			$output = preg_replace('#<a\ href=[^>]+>(<img\ [^>]+>)?</a>#', '', $output);
		}

		$output = $helper->replaceLinkHoldersText($parser, $output);
		return ['text' => $output, 'noparse' => false, 'preprocessFlags' => $flag];
	}

	/**
	 * Maps out a table and converts it to a collection of TableCells.
	 *
	 * @param string $input The HTML text of the table to convert.
	 *
	 * @return TableRow[] A collection of TableCells that represents the table provided.
	 */
	private static function buildMap(string $input): array
	{
		#RHshow('Input', $input);
		/** @var TableRow[] map */
		$map = [];
		preg_match_all('#(<tr[^>]*>)(.*?)</tr\s*>#is', $input, $rawRows, PREG_SET_ORDER);
		// Pre-create table so all rows are valid when indexed.
		foreach ($rawRows as $rawRow) {
			$map[] =  new TableRow($rawRow[1]);
		}

		foreach ($rawRows as $rowNum => $rawRow) {
			preg_match_all('#<(?<name>t[dh])\s*(?<attribs>[^>]*)>(?<content>.*?)</\1\s*>#s', $rawRow[0], $rawCells, PREG_SET_ORDER);
			$map[$rowNum]->addRawCells($map, $rowNum, $rawCells);
		}

		#RHshow('Map', $map);
		return $map;
	}

	/**
	 * Removes emptry rows from the output.
	 *
	 * @param TableRow[] $map The table map to work on.
	 * @param bool $cleanImages Whether to clean images in cells that aren't headers.
	 *
	 * @return string The cleaned table as HTML.
	 *
	 */
	private static function cleanRows(array $map, bool $cleanImages): string
	{
		#RHshow('Map', $map);
		$rowNum = count($map) - 1;
		$headerRows = 0;
		$prevWidth = PHP_INT_MAX;
		$lastWasHeader = false;
		$sectionHasContent = false; // Does the section have uncleaned rows?
		$sectionHasRows = false; // Does the section have *any* normal rows, whether or not we clean them?
		$prevWidth = PHP_INT_MAX;
		while ($rowNum >= 0) {
			$row = $map[$rowNum];
			$cleanType = $row->cleanType;
			if ($cleanType === 'auto') {
				$cleanType = $row->isHeader
					? (($rowNum === 0 && $row->cellCount === 1 && $row->getColumnCount() > 1)
						? 'tableheader'
						: 'header')
					: ($row->hasContent
						? 'normal'
						: 'clean');
			}

			if ($lastWasHeader && (!$row->isHeader || $row->cellCount > $prevWidth)) {
				// This looks like it's not part of the same group, so reset variables.
				#RHecho('Row reset');
				$sectionHasContent = false;
				$sectionHasRows = false;
				$prevWidth = PHP_INT_MAX;
			}

			#RHshow('Row ' . $cleanType, $row->toHtml(), "\nCell Count: ", $row->cellCount, '/', $prevWidth, ' ');
			$cleanRow = false;
			switch ($cleanType) {
				case 'clean':
				case 'keep':
				case 'normal':
					$sectionHasRows = true;
					if ($cleanType === 'normal') {
						$row->updateHasContent($cleanImages);
						$cleanRow = !$row->hasContent;
						$sectionHasContent |= $row->hasContent;
					} else {
						$cleanRow = $cleanType === 'clean';
					}

					break;
				case 'header':
					if ($row->cellCount < $prevWidth) {
						$prevWidth = $row->cellCount;
						#RHshow('Section has rows', $sectionHasRows, "\nSection has content: ", $sectionHasContent);
						$cleanRow = $sectionHasRows && !$sectionHasContent;
					}

					break;
				case 'tableheader':
					$headerRows++;
					break;
				default:
					break;
			}

			$lastWasHeader = $row->isHeader;
			if ($cleanRow) {
				$row->decrementRowspan();
				unset($map[$rowNum]);
			}

			$rowNum--;
		}

		if (count($map) === $headerRows) {
			$map = [];
		}

		#RHecho($map);
		return self::mapToTable($map);
	}

	/**
	 * Cleans the table using the MediaWiki pre-processor. This is used for both "top" and "recursive" modes.
	 *
	 * @param PPFrame $frame The template frame in use.
	 * @param PPNode $node The pre-processor node to clean.
	 * @param mixed $recurse Whether to recurse into the node.
	 *
	 * @return string The wiki text after cleaning it.
	 *
	 */
	private static function cleanSpaceNode(PPFrame $frame, PPNode $node): string
	{
		// This had been a fairly simple method but changes in MW 1.28 made it much more complex. The former
		// "recursive" mode was also abandoned for this reason.
		$output = '';
		$wantCloseNode = false;
		$doTrim = false;
		$node = $node->getFirstChild();
		while ($node) {
			$nextNode = $node->getNextSibling();
			if (self::isLink($node)) {
				$wantCloseNode = true;
				$value = $node->value;
				if ($doTrim) {
					$value = ltrim($value);
					$doTrim = false;
				}

				if ($wantCloseNode) {
					$offset = strpos($value, ']]');
					if ($offset) {
						$wantCloseNode = false;
						// show($nextNode);
						$linkEnd = substr($value, 0, $offset + 2);
						$remainder = substr($node->value, $offset + 2);
						$remainder = preg_replace('#\A\s+(' . self::TAG_REGEX . '|\Z)#', '$1', $remainder, 1);
						$doTrim = !strlen($remainder);
						$value = $linkEnd . $remainder;
						// DoTrim is set to true only
					}
				}
			} elseif ($doTrim && $node instanceof PPNode_Hash_Text && !strLen(trim($node->value)) && self::isTrimmable($nextNode)) {
				$value = '';
			} else {
				$doTrim = true;
				$value = $frame->expand($node, PPFrame::RECOVER_ORIG);
			}

			if ($nextNode && self::isLink($nextNode)) {
				$value = preg_replace('#(' . self::TAG_REGEX . ')\s*\Z#', '$1', $value, 1);
			}

			$output .= $value;
			$node = $nextNode;
		}

		return $output;
	}

	/**
	 * Cleans the text according to the original regex-based approach. This no longer includes the breadcrumb
	 * functionality from the original MetaTemplate, as that no longer seems to apply to the trails. Looking through
	 * the history, I'm not sure if it ever did.
	 *
	 * @param string $text The original text inside the <cleanspace> tags.
	 *
	 * @return string The replacement text.
	 *
	 */
	private static function cleanSpaceOriginal(string $text): string
	{
		return preg_replace('/([\]\}\>])\s+([\<\{\[])/s', '$1$2', $text);
	}

	/**
	 * Cleans the text using the pre-processor.
	 *
	 * @param mixed $text The text to clean.
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param mixed $recurse Whether to recurse into other templates and links. (Removed from code for now, but may be
	 *                       re-implemented later.)
	 *
	 * @return string The wiki text after cleaning it.
	 *
	 */
	private static function cleanSpacePP(Parser $parser, PPFrame $frame, $text): string
	{
		$rootNode = $parser->preprocessToDom($text);
		return self::cleanSpaceNode($frame, $rootNode);
	}

	/**
	 * Checks if a title by the name of $titleText exists.
	 *
	 * @param Parser $parser The parser in use.
	 * @param ?Title $title The title to search for.
	 *
	 * @return ?Title True if the file was found; otherwise, false.
	 */
	private static function findTitle(Parser $parser, string $titleText, int $defaultNs = NS_MAIN): ?Title
	{
		// Derived from ParserFunctions #ifexist code.
		$title = Title::newFromText($titleText, $defaultNs);
		VersionHelper::getInstance()->findVariantLink($parser, $titleText, $title, true);
		if (!$title || $title->isExternal()) {
			return null;
		}

		$ns = $title->getNamespace();
		switch ($ns) {
			case NS_SPECIAL:
				return VersionHelper::getInstance()->specialPageExists($title)
					? $title
					: null;
			case NS_MEDIA:
				return $parser->incrementExpensiveFunctionCount() && VersionHelper::getInstance()->fileExists($title)
					? $title
					: null;
			default:
				$pdbk = $title->getPrefixedDBkey();
				$linkCache = MediaWikiServices::getInstance()->getLinkCache();
				if ($linkCache->getGoodLinkID($pdbk)) {
					return $title;
				}

				return (!$linkCache->isBadLink($pdbk) && $parser->incrementExpensiveFunctionCount() && $title->getArticleID())
					? $title
					: null;
		}
	}

	/**
	 * Creates a list of template calls for #splitargs/#explodeargs
	 *
	 * @param PPFrame $frame The template frame in use.
	 * @param string $templateName The name of the template.
	 * @param int $nargs The number of arguments to divide everything up by.
	 * @param PPNode[] $named Named values that will be included in *every* template.
	 * @param PPNode[] $values Unnamed values to be split up and included with the template; 0-based.
	 * @param bool $allowEmpty Whether the list of templates should include empty inputs.
	 *
	 * @return array A string[] containing the individual template calls that #splitargs splits into.
	 *
	 */
	private static function getTemplates(PPFrame $frame, string $templateName, int $nargs, array $named, array $values, bool $allowEmpty): array
	{
		if (!$nargs) {
			$nargs = count($values);
		}

		if (count($values) === 0) {
			return [];
		}

		$templates = [];
		$templateBase = '{{' . $templateName;
		foreach ($named as $name => $value) {
			$value = $frame->expand($value);
			$templateBase .= "|$name=$value";
		}

		// Pre-dividing the template values allows handling missing values or values presented out of order (e.g., from
		// override parameters) gracefully.
		$rows = [];
		foreach ($values as $key => $value) {
			// $value = $parser->preprocessToDom($value);
			$rowNum = intdiv($key, $nargs);
			$colNum = $key % $nargs;
			$rows[$rowNum][$colNum + 1] = $value;
		}

		$templates = [];
		foreach ($rows as $row) {
			$numberedParameters = '';
			$blank = true;
			/** @var int $paramNum */
			/** @var PPNode $value */
			foreach ($row as $paramNum => $value) {
				// Unlike normal templates, we strip off spacing even for numbered arguments, so groups can be
				// formatted each on a new line. The purpose of the double-expansion is to first check if the entire
				// value evaluates to nothing. This allows template lookalikes like {{#if}} to take effect normally. If
				// it's non-blank, then we re-expand, but retaining templates so that things like {{!}} get processed
				// as expected.
				$dom = $frame->parser->preprocessToDom($value);
				$checkValue = trim($frame->expand($dom));
				if (strlen($checkValue)) {
					$blank = false;
				} else {
					$value = '';
				}

				// We have to use numbered arguments to avoid the possibility that $value is (or even looks like)
				// 'param=value'.
				$numberedParameters .= "|$paramNum=$value";
			}

			// show('Template: ', $template);
			if ($allowEmpty || !$blank) {
				$templates[] = $templateBase . $numberedParameters . '}}';
			}
		}

		return $templates;
	}

	/**
	 * Determines of the node provided is a link.
	 *
	 * @param PPNode $node The node to check.
	 *
	 * @return bool True if the node is a link; otherwise, false.
	 *
	 */
	private static function isLink(PPNode $node): bool
	{
		return $node instanceof PPNode_Hash_Text && substr($node->value, 0, 2) === '[[';
	}

	/**
	 * Indicates whether the node provided can be trimmed out of the table if the content is empty.
	 *
	 * @param ?PPNode $node The node to check.
	 *
	 * @return bool
	 *
	 */
	private static function isTrimmable(?PPNode $node = null): bool
	{
		// Is it a template?
		if ($node instanceof PPTemplateFrame_Hash) {
			return true;
		}

		if ($node instanceof PPNode_Hash_Text) {
			// Is it a link?
			if (substr($node->value, 0, 2) == '[[') {
				return true;
			}

			// Is it something that looks like an HTML tag?
			return preg_match('#\A\s*' . self::TAG_REGEX  . '#s', $node->value);
		}
	}

	/**
	 * Converts a cell map back to an HTML table.
	 *
	 * @param array $map The row/cell map provided by buildMap().
	 *
	 * @return string The HTML text for the table.
	 *
	 */
	private static function mapToTable(array $map): string
	{
		$output = '';
		/** @var TableRow $row */
		foreach ($map as $row) {
			$output .= $row->toHtml();
		}

		return $output;
	}

	/**
	 * Recursively searches for tables within the tags and cleans them.
	 *
	 * @param Parser $parser The parser in use.
	 * @param mixed $input The table to work on.
	 * @param mixed $offset Where in the table we're looking at. This is used in cleaning nested tables.
	 * @param ?string $open The table tag that was found during recursion. This can be null for the outermost table.
	 *
	 * @return string The cleaned results.
	 *
	 */
	private static function parseTable(Parser $parser, $input, int &$offset, bool $cleanImages, ?string $open = null)
	{
		#RHshow('Parse Table In', substr($input, $offset));
		$output = '';
		while (preg_match('#</?table[^>]*?>\s*#i', $input, $matches, PREG_OFFSET_CAPTURE, $offset)) {
			$match = $matches[0];
			$output .= substr($input, $offset, $match[1] - $offset);
			$offset = $match[1] + strlen($match[0]);
			if ($match[0][1] == '/') {
				$output = self::cleanRows(self::buildMap($output), $cleanImages);
				#RHshow('Clean Rows', $output);
				break;
			} else {
				$output .= self::parseTable($parser, $input, $offset, $cleanImages, $match[0]);
				// show("Parse Table Out:\n", $output);
			}
		}

		if (!is_null($open) && strlen($output) > 0) {
			$output = $open . $output . '</table>';
			// Insert as Strip item so we don't end up reparsing nested tables.
			$output = $parser->insertStripItem($output);
		}

		#RHshow('Output', $output);
		return $output;
	}

	/**
	 * Takes the input from the various forms of #splitargs and returns it as a cohesive set of variables.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $magicArgs The template arguments that contain a recognized keyword for the function in string key/PPNode value format.
	 * @param string $templateName The name of the template.
	 * @param int $nargs The number of arguments to split parameters into.
	 * @param array $named All named arguments not covered by $magicArgs. These will be passed to each template call.
	 * @param PPNode[] $values All numbered/anonymous arguments; should be 0-based, not 1-based.
	 *
	 * @return mixed The text of all the function calls.
	 *
	 */
	private static function splitArgsCommon(Parser $parser, PPFrame $frame, array $magicArgs, string $templateName, int $nargs, array $named, array $values): array
	{
		if ($nargs < 1 || !$templateName || !count($values)) {
			return [''];
		}

		$allowEmpty = $magicArgs[self::NA_ALLOWEMPTY] ?? false;
		$templates = self::getTemplates($frame, $templateName, $nargs, $named, $values, $allowEmpty);
		if (empty($templates)) {
			return [''];
		}

		// show("Templates:\n", $templates);
		$separator = ParserHelper::getSeparator($magicArgs);
		$output = implode($separator, $templates);
		// show("Output:\n", $output);

		$debug = ParserHelper::checkDebugMagic($parser, $frame, $magicArgs);
		return ParserHelper::formatPFForDebug($output, $debug);
	}

	/**
	 * Recursively parses a single PPNode and strips the relevant links from it.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param PPNode $node The node to work on.
	 *
	 * @return string The resulting text after all links have been trimmed.
	 */
	private static function trimLinksParseNode(Parser $parser, PPFrame $frame, PPNode $node): string
	{
		if (self::isLink($node)) {
			// show($node->value);
			$close = strrpos($node->value, ']]');
			$link = substr($node->value, 2, $close - 2);
			$split = explode('|', $link, 2);
			$titleText = trim($split[0]);
			$leadingColon = $titleText[0] === ':';
			$title = Title::newFromText($titleText);
			$ns = $title ? $title->getNamespace() : 0;
			if ($leadingColon) {
				$titleText = substr($titleText, 1);
				if ($ns === NS_MEDIA) {
					$leadingColon = false;
				}
			}

			if ($leadingColon || !$title->isExternal()) {
				if (!$leadingColon && in_array($ns, [NS_FILE, NS_MEDIA])) {
					$after = substr($node->value, $close + 2);
					$subText = $split[1] ?? null;
					if (!is_null($subText)) {
						$subDom = $parser->preprocessToDom($subText);
						$subText = self::trimLinksParseNode($parser, $frame, $subDom);
						return "[[$title|$subText]]$after";
					}
				} elseif ($leadingColon || !in_array($ns, [NS_CATEGORY, NS_SPECIAL])) {
					$after = substr($node->value, $close + 2);
					$subText = $split[1] ?? null;
					if (is_null($subText)) {
						// For title-only links, formatting should not be applied at all, so just surround the entire thing with nowiki tags.
						$subText = $title ? $title->getPrefixedText() : $titleText;
					}

					// If display text was provided, preserve formatting but put self-closed nowikis at each end to break any accidental formatting that results.
					return "<nowiki/>$subText<nowiki/>$after";
				}
			}

			return $frame->expand($node);
		} elseif ($node instanceof PPNode_Hash_Tree) {
			$child = $node->getFirstChild();
			$output = '';
			while ($child) {
				$output .= self::trimLinksParseNode($parser, $frame, $child);
				$child = $child->getNextSibling();
			}

			return $output;
		} elseif ($node instanceof PPNode_Hash_Text) {
			return $node->value;
		}

		return $frame->expand($node);
	}
}
