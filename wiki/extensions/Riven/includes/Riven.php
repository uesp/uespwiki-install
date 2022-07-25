<?php
/*
namespace MediaWiki\Extension\MetaTemplate;
*/

use MediaWiki\MediaWikiServices;

/**
 * A collection of various routines that primarily help in template editing. These were split out from MetaTemplate
 * and/or DynamicFunctions since they don't rely on the preprocessor in order to work (or if they do, there are
 * non-preprocessor alternatives).
 *
 * The rarely used functions are all put into "Riven-Pages Using <feature>" tracking categories.
 */
class Riven
{
    const AV_ORIGINAL  = 'riven-original';
    const AV_RECURSIVE = 'riven-recursive';
    const AV_SMART     = 'riven-smart';
    const AV_TOP       = 'riven-top';

    const NA_DELIMITER = 'riven-delimiter';
    const NA_EXPLODE   = 'riven-explode';
    const NA_MODE      = 'riven-mode';
    const NA_PROTROWS  = 'riven-protectrows';
    const NA_SEED      = 'riven-seed';

    // For whatever reason, MediaWiki did Magic Words differently from everything else, so parser functions are best
    // off with the "key" being the actual word you intend to use. That's why these ones don't have "riven-" prepended
    // to them.
    const PF_ARG         = 'arg'; // From DynamicFunctions
    const PF_EXPLODEARGS = 'explodeargs';
    const PF_FINDFIRST   = 'findfirst';
    const PF_IFEXISTX    = 'ifexistx';
    const PF_INCLUDE     = 'include';
    const PF_PICKFROM    = 'pickfrom';
    const PF_RAND        = 'rand'; // From DynamicFunctions
    const PF_SPLITARGS   = 'splitargs';
    const PF_TRIMLINKS   = 'trimlinks';

    const TG_CLEANSPACE = 'riven-cleanspace';
    const TG_CLEANTABLE = 'riven-cleantable';

    const TRACKING_ARG         = 'riven-tracking-arg';
    const TRACKING_EXPLODEARGS = 'riven-tracking-explodeargs';
    const TRACKING_PICKFROM    = 'riven-tracking-pickfrom';
    const TRACKING_RAND        = 'riven-tracking-rand';
    const TRACKING_SKINNAME    = 'riven-tracking-skinname';

    const VR_SKINNAME = 'riven-skinname'; // From DynamicFunctions

    const TAG_REGEX = '</?[0-9A-Za-z]+(\s[^>]*)?>';

    /**
     * Retrieves an argument from the URL.
     *
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The template frame in use.
     * @param array $args Function arguments:
     *     1: The name of the argument to look for.
     *     2: If the argument above isn't found, return this value instead.
     *
     * @return The value found or the default value. Failing all else,
     */
    public static function doArg(Parser $parser, PPFrame $frame, array $args)
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
     * @param mixed $text The text to clean.
     * @param array $args The tag arguments:
     *     mode:  Select strategy for removal. Note that in the first two modes, this is an intelligent search and will
     *            only match what the wiki identifies as links and templates.
     *         top:       Only remove space at the top-most level...will not search inside links or templates (but can
     *                    search inside tags).
     *         recursive: (disabled for now) Search everything.
     *         original:  This is the default, using The original regex-based search. This can sometimes result in
     *                    unwanted matches.
     *     debug: Set to PHP true to show the cleaned code on-screen during Show Preview. Set to 'always' to show even
     *            when saved.
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The templare frame in use.
     *
     * @return string Cleaned text.
     *
     */
    public static function doCleanSpace($text, array $args, Parser $parser, PPFrame $frame)
    {
        $args = ParserHelper::transformArgs($args);
        $mode = ParserHelper::arrayGet($args, self::NA_MODE);
        $modeWord = ParserHelper::findMagicID($mode, self::AV_ORIGINAL);
        $output = $text;
        if ($modeWord !== self::AV_ORIGINAL) {
            $output = preg_replace('#<!--.*?-->#s', '', $output);
        }

        $output = trim($output);
        switch ($modeWord) {
                /*
            case self::AV_RECURSIVE:
                $output = self::cleanSpacePP($output, $parser, $frame, true);
                break; */
            case self::AV_TOP:
                $output = self::cleanSpacePP($parser, $frame, $output);
                break;
            default:
                $output = self::cleanSpaceOriginal($output);
                break;
        }

        if (ParserHelper::checkDebugMagic($parser, $frame, $args)) {
            return ParserHelper::formatTagForDebug($output, true);
        }

        // Categories and trails are stripped on ''any'' template page, not just when directly calling the template
        // (but not in pre view mode).
        if (!$parser->getOptions()->getIsPreview() && $parser->getTitle()->getNamespace() == NS_TEMPLATE) {
            // save categories before processing
            $precats = $parser->getOutput()->getCategories();
            $output = $parser->recursiveTagParse($output, $frame);
            // reset categories to the pre-processing list to remove any new categories
            $parser->getOutput()->setCategoryLinks($precats);
            return $output;
        }

        $output = $parser->recursiveTagParse($output, $frame);
        return $output;
    }

    /**
     * Cleans a table of all empty rows.
     *
     * @param mixed $text The text containing the tables to clean.
     * @param array $args The tag arguments:
     *     protectrows: The number of rows at the top of the table that will not be removed no matter what.
     *     debug:       Set to PHP true to show the cleaned table code on-screen during Show Preview. Set to 'always'
     *                  to show even when saved.
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The templare frame in use.
     *
     * @return string Cleaned text.
     *
     */
    public static function doCleanTable($text, $args, Parser $parser, PPFrame $frame)
    {
        $args = ParserHelper::transformArgs($args);
        $input = $parser->recursiveTagParse($text, $frame);

        // This ensures that tables are not cleaned if being displayed directly on the Template page.
        // Previewing will process cleantable normally.
        if (
            $frame->depth == 0 &&
            $parser->getTitle()->getNamespace() == NS_TEMPLATE &&
            !$parser->getOptions()->getIsPreview()
        ) {
            return $input;
        }

        $offset = 0;
        $output = '';
        $lastVal = null;
        $protectRows = intval(ParserHelper::arrayGet($args, self::NA_PROTROWS, 1));
        do {
            $lastVal = self::parseTable($parser, $input, $offset, $protectRows);
            $output .= $lastVal;
        } while ($lastVal);

        $after = substr($input, $offset);
        $output .= $after;

        $debug = ParserHelper::checkDebugMagic($parser, $frame, $args);
        if (strlen($output) > 0 && $debug) {
            $output = $parser->recursiveTagParseFully($output);
        }

        return ParserHelper::formatTagForDebug($output, $debug);
    }

    public static function doExplodeArgs(Parser $parser, PPFrame $frame, array $args)
    {
        list($magicArgs, $values, $dupes) = ParserHelper::getMagicArgs(
            $frame,
            $args,
            ParserHelper::NA_ALLOWEMPTY,
            ParserHelper::NA_DEBUG,
            ParserHelper::NA_IF,
            ParserHelper::NA_IFNOT,
            ParserHelper::NA_SEPARATOR,
            self::NA_DELIMITER
        );

        if (!ParserHelper::checkIfs($frame, $magicArgs)) {
            return '';
        }

        // show("Passed if check:\n", $values, "\nDupes:\n", $dupes);
        list($named, $values) = self::splitNamedArgs($frame, $values);
        if (count($values) < 3 || !isset($values[1])) {
            return '';
        }

        $nargs = $frame->expand($values[1]);
        $templateName = $frame->expand($values[0]);
        $delimiter = $frame->expand(
            isset($values[3])
                ? $values[3]
                : ParserHelper::arrayGet($magicArgs, self::NA_DELIMITER, ',')
        );

        $values = explode($delimiter, $frame->expand($values[2]));

        // show($values);
        return self::splitArgsCommon($parser, $frame, $magicArgs, $templateName, $nargs, array_merge($named, $dupes), $values);
    }

    /**
     * Finds the first page that exists in the list of parameters.
     *
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The template frame in use.
     * @param array $args Function arguments:
     *       1-n: All unnamed parameters are page names to search.
     *        if: A condition that must be true in order for this function to run.
     *     ifnot: A condition that must be false in order for this function to run.
     * @return [type]
     *
     */
    public static function doFindFirst(Parser $parser, PPFrame $frame, array $args)
    {
        // This is just a loop over the core of #ifexistsx. Timing tests on other methods have so far failed thanks to
        // the existing cache mechanisms behind title checks.
        list($magicArgs, $values) = ParserHelper::getMagicArgs(
            $frame,
            $args,
            ParserHelper::NA_IF,
            ParserHelper::NA_IFNOT
        );

        if (!ParserHelper::checkIfs($frame, $magicArgs)) {
            return '';
        }

        // Build unique titles with simple nested loop, since count is unlikely to ever be large.
        $uniqueTitles = [];
        foreach ($values as $value) {
            $titleText = trim($frame->expand($value));
            $title = Title::newFromText($titleText);
            if ($title) {
                $found = false;
                foreach ($uniqueTitles as $unique) {
                    if ($title->equals($unique)) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $uniqueTitles[] = $title;
                }
            }
        }

        foreach ($uniqueTitles as $title) {
            if (self::existsCommon($parser, $title)) {
                return $title->getFullText();
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
     * @return string The result of the check or an empty string if the if/ifnot failed.
     *
     */
    public static function doIfExistX(Parser $parser, PPFrame $frame, array $args)
    {
        list($magicArgs, $values) = ParserHelper::getMagicArgs(
            $frame,
            $args,
            ParserHelper::NA_IF,
            ParserHelper::NA_IFNOT
        );

        if (!ParserHelper::checkIfs($frame, $magicArgs)) {
            return '';
        }

        $titleText = trim($frame->expand(ParserHelper::arrayGet($values, 0, '')));
        $result = self::existsCommon($parser, $titleText) ? 1 : 2;
        $result = ParserHelper::arrayGet($values, $result);
        return is_null($result) ? '' : trim($frame->expand($result));
    }

    /**
     * Transcludes a page if it exists, but if the page doesn't exist, it will not create either red links or Wanted
     * Templates entries.
     *
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The template frame in use.
     * @param array $args Function arguments:
     *     debug: Set to PHP true to show the cleaned code on-screen during Show Preview. Set to 'always' to show even
     *            when saved.
     *        if: A condition that must be true in order for this function to run.
     *     ifnot: A condition that must be false in order for this function to run.
     *
     * @return string
     *
     */
    public static function doInclude(Parser $parser, PPFrame $frame, array $args)
    {
        list($magicArgs, $values) = ParserHelper::getMagicArgs(
            $frame,
            $args,
            ParserHelper::NA_DEBUG,
            ParserHelper::NA_IF,
            ParserHelper::NA_IFNOT
        );

        if (count($values) <= 0 || !ParserHelper::checkIfs($frame, $magicArgs)) {
            return;
        }

        $output = '';
        foreach ($values as $titleText) {
            $titleText = trim($frame->expand($titleText));
            $title = Title::newFromText($titleText, NS_TEMPLATE);
            if (self::existsCommon($parser, $title)) {
                // show('Exists!');
                $outTitle = $title->getNamespace() == NS_TEMPLATE
                    ? $title->getText()
                    : $title->getFullText();
                $output .= '{{' . $outTitle . '}}';
            }
        }

        return ParserHelper::formatPFForDebug($output, ParserHelper::checkDebugMagic($parser, $frame, $magicArgs));
    }

    /**
     * Randomly picks one or more entries from a list and displays it.
     *
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The template frame in use.
     * @param array $args Function arguments:
     *            if: A condition that must be true in order for this function to run.
     *         ifnot: A condition that must be false in order for this function to run.
     *          seed: The number to use to initialize the random sequence.
     *     separator: The separator to use between entries. Defaults to \n.
     *
     * @return string
     *
     */
    public static function doPickFrom(Parser $parser, PPFrame $frame, array $args)
    {
        $parser->addTrackingCategory(self::TRACKING_PICKFROM);
        list($magicArgs, $values) = ParserHelper::getMagicArgs(
            $frame,
            $args,
            ParserHelper::NA_ALLOWEMPTY,
            ParserHelper::NA_IF,
            ParserHelper::NA_IFNOT,
            ParserHelper::NA_SEPARATOR,
            self::NA_SEED
        );

        $npick = intval(array_shift($values));
        if ($npick <= 0 || !count($values) || !ParserHelper::checkIfs($frame, $magicArgs)) {
            return '';
        }

        $values = ParserHelper::expandArray($frame, $values, 0, true);
        $allowEmpty = ParserHelper::arrayGet($magicArgs, ParserHelper::NA_ALLOWEMPTY, '');
        if (!$allowEmpty) {
            $values = array_values(array_filter($values, function ($value) {
                return strlen($value);
            }));

            if (!count($values)) {
                return '';
            }
        }

        $parser->getOutput()->updateCacheExpiry(0);

        // Shuffle uses the basic randomizer, so we seed with srand if requested.
        // As of PHP 7.1.0, shuffle uses the mt randomizer, but srand is then aliased to mt_srand, so no urgent need to
        // change it.
        $seed = ParserHelper::arrayGet($magicArgs, self::NA_SEED);
        if (is_null($seed)) {
            // We have to init every time otherwise previous seeds will affect current results (e.g., an hour-based
            // seed will cause all subsequent calls to srand to only generate hourly results).
            srand();
        } else {
            srand($seed);
        }

        shuffle($values); // randomize list

        if ($npick < count($values)) {
            $values = array_splice($values, 0, $npick); // cut off unwanted items
        }

        $separator = ParserHelper::getSeparator($frame, $magicArgs);
        return implode($separator, $values);
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
    public static function doRand(Parser $parser, PPFrame $frame, array $args)
    {
        $parser->addTrackingCategory(self::TRACKING_RAND);
        list($magicArgs, $values) = ParserHelper::getMagicArgs(
            $frame,
            $args,
            self::NA_SEED
        );

        if (count($values) == 1) {
            $low = 1;
            $high = trim($frame->expand(ParserHelper::arrayGet($values, 0)));
        } else {
            $low = trim($frame->expand(ParserHelper::arrayGet($values, 0)));
            $high = trim($frame->expand(ParserHelper::arrayGet($values, 1)));
        }

        $low = strlen($low) ? intval($low) : 1;
        $high = strlen($high) ? intval($high) : 6;
        if ($low == $high) {
            return $low;
        }

        if (isset($magicArgs[self::NA_SEED])) {
            mt_srand(($magicArgs[self::NA_SEED]));
        } else {
            mt_srand();
        }

        $parser->getOutput()->updateCacheExpiry(0);
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
    public static function doSkinName(Parser $parser)
    {
        $parser->addTrackingCategory(self::TRACKING_SKINNAME);
        return RequestContext::getMain()->getSkin()->getSkinName();
    }

    /**
     * Repetitively calls a template with different parameters for each call.
     *
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The template frame in use.
     * @param array $args Function arguments:
     *
     * @return string
     */
    public static function doSplitArgs(Parser $parser, PPFrame $frame, array $args)
    {
        $input = ParserHelper::getMagicArgs(
            $frame,
            $args,
            ParserHelper::NA_ALLOWEMPTY,
            ParserHelper::NA_DEBUG,
            ParserHelper::NA_IF,
            ParserHelper::NA_IFNOT,
            ParserHelper::NA_SEPARATOR,
            self::NA_DELIMITER,
            self::NA_EXPLODE
        );

        list($magicArgs, $values, $dupes) = $input;
        if (!ParserHelper::checkIfs($frame, $magicArgs)) {
            return '';
        }

        // show("Passed if check:\n", $values, "\nDupes:\n", $dupes);
        list($named, $values) = self::splitNamedArgs($frame, $values);
        if (!isset($values[1])) {
            return '';
        }

        // Figure out what we're dealing with and populate appropriately.
        $templateName = $frame->expand($values[0]);
        if (empty($templateName)) {
            return '';
        }

        $nargs = $frame->expand($values[1]);
        if (isset($magicArgs[self::NA_EXPLODE])) {
            // Explode
            $explode = $magicArgs[self::NA_EXPLODE];
            $delimiter = ParserHelper::arrayGet($magicArgs, self::NA_DELIMITER, ',');
            $values = explode($delimiter, $explode);
        } else {
            $newValues = array_slice($values, 2);
            if (count($newValues) == 0) {
                $newValues = $frame->getNumberedArguments();
                // show($newValues);
            }

            $values = [];
            foreach ($newValues as $value) {
                $values[] = str_replace('|', '{{!}}', trim($frame->expand($value)));
            }
        }

        return self::splitArgsCommon($parser, $frame, $magicArgs, $templateName, $nargs, array_merge($named, $dupes), $values);
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
     * @return string
     */
    public static function doTrimLinks(Parser $parser, PPFrame $frame, array $args)
    {
        if (!isset($args[0])) {
            return '';
        }

        list($magicArgs) = ParserHelper::getMagicArgs(
            $frame,
            $args,
            self::NA_MODE
        );

        if (ParserHelper::magicKeyEqualsValue($magicArgs, self::NA_MODE, self::AV_SMART)) {
            // This was a lot simpler in the original implementation, working strictly by recursively parsing the root
            // node. MW 1.28 changed the preprocessor to be unresponsive to changes to its nodes, however,
            // necessitating this mess...which is still better than trying to create a new node structure.
            $preprocessor = new Preprocessor_Hash($parser);
            $flag = $frame->depth ? Parser::PTD_FOR_INCLUSION : 0;
            $rootNode = $preprocessor->preprocessToObj($args[0], $flag);
            $output = self::trimLinksParseNode($parser, $frame, $rootNode);
            $output = $parser->mStripState->unstripBoth($output);
            $output = $parser->replaceLinkHoldersText($output);
            $newNode = $preprocessor->preprocessToObj($output, $flag);
            $output = $frame->expand($newNode);
            return [$output, 'noparse' => true];
        } else {
            $output = $parser->recursiveTagParse($args[0]);
            return $parser->replaceLinkHoldersText($output);
        }
    }

    /**
     * Initializes all the relevant aspects of Riven.
     *
     * @return void;
     *
     */
    public static function init()
    {
        ParserHelper::cacheMagicWords([
            self::AV_TOP,
            self::NA_DELIMITER,
            self::NA_EXPLODE,
            self::NA_MODE,
            self::NA_PROTROWS,
            self::NA_SEED,
        ]);
    }

    /**
     * Maps out a table and converts it to a collection of TableCells.
     *
     * @param mixed $input The text of the map to convert.
     *
     * @return array A collection of TableCells that represents the table provided.
     */
    private static function buildMap($input)
    {
        $map = [];
        $rowNum = 0;
        preg_match_all('#(<tr[^>]*>)(.*?)</tr\s*>#is', $input, $rawRows, PREG_SET_ORDER);
        foreach ($rawRows as $rawRow) {
            $map[$rowNum]['open'] = $rawRow[1];
            $cellNum = 0;
            preg_match_all('#<(?<name>t[dh])\s*(?<attribs>[^>]*)>(?<content>.*?)</\1\s*>#s', $rawRow[0], $rawCells, PREG_SET_ORDER);
            foreach ($rawCells as $rawCell) {
                $cell = new TableCell($rawCell);
                while (isset($map[$rowNum][$cellNum])) {
                    $cellNum++;
                }

                $map[$rowNum][$cellNum] = $cell;
                $rowspan = $cell->getRowspan();
                $colspan = $cell->getColspan();
                if ($rowspan > 1 || $colspan > 1) {
                    $spanCell = new TableCell($cell);
                    for ($r = 0; $r < $rowspan; $r++) {
                        for ($c = 0; $c < $colspan; $c++) {
                            if ($r != 0 || $c != 0) {
                                $map[$rowNum + $r][$cellNum + $c] = $spanCell;
                            }
                        }
                    }
                }
            }

            $rowNum++;
        }

        return $map;
    }

    /**
     * Removes emptry rows from the output.
     *
     * @param mixed $input The text to work on.
     * @param int $protectRows The number of rows to protect at the top of the table.
     *
     * @return TableCell[] A map of every cell in the table. Those with spans will appear as individual cells with a link
     * back to the home cell.
     *
     */
    private static function cleanRows($input, $protectRows = 1)
    {
        // show("Clean Rows In:\n", $input);
        $map = self::buildMap($input);
        // show($map);
        $sectionHasContent = false;
        $contentRows = false;
        for ($rowNum = count($map) - 1; $rowNum >= $protectRows; $rowNum--) {
            $row = $map[$rowNum];
            $rowHasContent = false;
            $allHeaders = true;
            $spans = [];

            foreach ($row as $cell) {
                // show($cell);
                if ($cell instanceof TableCell) {
                    $content = preg_replace('#\{\{\{[^\}]*\}\}\}#', '', html_entity_decode($cell->getContent()));
                    $rowHasContent |= strlen($content) > 0 && !$cell->isHeader() && !ctype_space($content);
                    $allHeaders &= $cell->isHeader();
                    if ($cell->getParent()) {
                        $spans[] = $cell->getParent();
                    }
                }
            }

            // show('Row: ', $rowNum, "\n", $rowHasContent, "\n", $row);
            $sectionHasContent |= $rowHasContent;
            if ($allHeaders) {
                // Rownum/proectRow check is a apecial allowance for the top-most header being the only row left in the
                // table. If so, and if full table deletion is allowed, then delete the "section" even if section has
                // no content. This can happen if the table starts with a main header followed immediately by a
                // sub-header.
                if ($contentRows || ($rowNum === 0 && $protectRows === 0)) {
                    // show($contentRows);
                    if ($sectionHasContent) {
                        $sectionHasContent = false;
                    } else {
                        // show('Removed Row: ', $rowNum, "\n", $rowHasContent, "\n", $row);
                        unset($map[$rowNum]);
                    }
                }

                $contentRows = false;
            } else {
                $contentRows =  true;
                if (!$rowHasContent) {
                    /** @var TableCell $cell */
                    foreach ($spans as $cell) {
                        $cell->decrementRowspan();
                        // show('RowCount: ', $cell->getRowspan());
                    }

                    unset($map[$rowNum]);
                }
            }
        }

        // show($map);
        return self::mapToTable($map);
    }

    /**
     * Cleans the table using the MediaWiki pre-processor. This is used for both "top" and "recursive" modes.
     *
     * @param PPNode $node The pre-processor node to clean.
     * @param mixed $recurse Whether to recurse into the node.
     *
     * @return string The wiki text after cleaning it.
     *
     */
    private static function cleanSpaceNode(PPFrame $frame, PPNode $node)
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

            // show('Value: ', $value);
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
     * @param mixed $text The original text inside the <cleanspace> tags.
     *
     * @return string The replacement text.
     *
     */
    private static function cleanSpaceOriginal($text)
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
     * @return [type]
     *
     */
    private static function cleanSpacePP(Parser $parser, PPFrame $frame, $text)
    {
        $rootNode = $parser->getPreprocessor()->preprocessToObj($text);
        return self::cleanSpaceNode($frame, $rootNode);
    }

    /**
     * Checks if a title by the name of $titleText exists.
     *
     * @param Parser $parser The parser in use.
     * @param mixed $titleText The title to search for.
     *
     * @return boolean True if the file was found; otherwise, false.
     *
     */
    private static function existsCommon(Parser $parser, $titleText)
    {
        if ($titleText instanceof Title) {
            $title = $titleText;
        } else {
            $title = Title::newFromText($titleText);
        }

        if (!$title || $title->isExternal()) {
            return false;
        }

        $parser->getFunctionLang()->findVariantLink($titleText, $title, true);
        if (!$title) {
            return false;
        }

        $ns = $title->getNamespace();
        switch ($ns) {
            case NS_SPECIAL:
                return SpecialPageFactory::exists($title->getDBkey());
            case NS_MEDIA:
                if ($parser->incrementExpensiveFunctionCount()) {
                    $file = RepoGroup::singleton()->getLocalRepo()->newFile($title);
                    if ($file) {
                        return $file->exists();
                    }
                }

                return false;
            default:
                $pdbk = $title->getPrefixedDBkey();
                $linkCache = MediaWikiServices::getInstance()->getLinkCache();
                return
                    $linkCache->getGoodLinkID($pdbk) !== 0 ||
                    (!$linkCache->isBadLink($pdbk) &&
                        $parser->incrementExpensiveFunctionCount() &&
                        $title->getArticleID() != 0);
        }
    }

    /**
     * Creates a list of template calls for #splitargs/#explodeargs
     *
     * @param PPFrame $frame The frame in use.
     * @param mixed $templateName The name of the template.
     * @param mixed $nargs The number of arguments to divide everything up by.
     * @param array $values Unnamed values to be split up and included with the template.
     * @param array $named Named values that will be included in *every* template.
     * @param mixed $allowEmpty Whether the list of templates should include empty inputs.
     *
     * @return array
     *
     */
    private static function getTemplates(PPFrame $frame, $templateName, $nargs, array $values, array $named, $allowEmpty)
    {
        $nargs = intval($nargs);
        if (!$nargs) {
            $nargs = count($values);
        }

        $templates = [];
        if (count($values) == 0) {
            return $templates;
        }

        for ($index = 0; $index < count($values); $index += $nargs) {
            $parameters = '';
            $blank = true;
            for ($paramNum = 0; $paramNum < $nargs; $paramNum++) {
                if (!is_array($values)) {
                    // show('Yes, this is a thing!');
                    $values = [trim($values)];
                }

                $value = ParserHelper::arrayGet($values, $index + $paramNum);
                if (!is_null($value)) {
                    if (strlen($value) > 0) {
                        $blank = false;
                    }

                    // We have to use numbered arguments to avoid the possibility that $value is (or even looks like)
                    // 'param=value'.
                    $displayNum = $paramNum + 1;
                    $parameters .= "|$displayNum=$value";
                }
            }

            if ($allowEmpty || !$blank) {
                foreach ($named as $name => $value) {
                    $value = $frame->expand($value, PPFrame::RECOVER_ORIG);
                    $parameters .= "|$name=$value";
                }

                $template = '{{' . $templateName . $parameters . '}}';
                // show('Template: ', $template);
                $templates[] = $template;
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
    private static function isLink(PPNode $node)
    {
        return $node instanceof PPNode_Hash_Text && substr($node->value, 0, 2) === '[[';
    }

    /**
     * Indicates whether the node provided can be trimmed out of the table if the content is empty.
     *
     * @param PPNode|null $node The node to check.
     *
     * @return bool
     *
     */
    private static function isTrimmable(PPNode $node = null)
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
     * @param mixed $map The cell map provided by buildMap().
     *
     * @return string The HTML text for the table.
     *
     */
    private static function mapToTable($map)
    {
        $output = '';
        foreach ($map as $row) {
            $output .= $row['open'] . "\n";
            /** @var TableCell $cell */
            foreach ($row as $name => $cell) {
                if ($name !== 'open') {
                    // Conditional is to avoid unwanted blank lines in output.
                    $html = $cell->toHtml();
                    if ($html) {
                        $output .= $html . "\n";
                    }
                }
            }

            $output .= "</tr>\n";
        }

        return $output;
    }

    /**
     * Recursively searches for tables within the tags and cleans them.
     *
     * @param Parser $parser The parser in use.
     * @param mixed $input The table to work on.
     * @param mixed $offset Where in the table we're looking at. This is used in cleaning nested tables.
     * @param mixed $protectRows The number of rows at the top of the table that should not be removed, no matter what.
     * @param null $open The table tag that was found during recursion. This can be null for the outermost table.
     *
     * @return string The cleaned results.
     *
     */
    private static function parseTable(Parser $parser, $input, &$offset, $protectRows, $open = null)
    {
        // show("Parse Table In:\n", substr($input, $offset));
        $output = '';
        $before = null;
        while (preg_match('#</?table[^>]*?>\s*#i', $input, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $match = $matches[0];
            $output .= substr($input, $offset, $match[1] - $offset);
            $offset = $match[1] + strlen($match[0]);
            if ($match[0][1] == '/') {
                $output = self::cleanRows($output, $protectRows);
                // show("Clean Rows Out:\n", $output);
                break;
            } else {
                $output .= self::parseTable($parser, $input, $offset, $protectRows, $match[0]);
                // show("Parse Table Out:\n", $output);
            }
        }

        if (!is_null($open) && strlen($output) > 0) {
            $output = $open . $output . '</table>';
            $output = $parser->insertStripItem($output);
        }

        // show("Before:\n", $before);
        // show("Output:\n", $output);
        return $before . $output;
    }

    /**
     * For debugging only. Shows results of splitArgs()
     *
     * @param PPFrame $frame
     * @param array $input
     *
     * @return [type]
     *
     */
    private static function showGetMagicArgs(PPFrame $frame, array $input)
    {
        list($magicArgs, $values, $dupes) = $input;
        $showText = '';
        $showText .= ("Magic Args:\n");
        foreach ($magicArgs as $key => $value) {
            $showText .= $key . ' = ' . $frame->expand($value) . "\n";
        }

        $showText .= ("\nValues:\n");
        foreach ($values as $value) {
            $showText .= $frame->expand($value) . "\n";
        }

        if (is_array($dupes)) {
            $showText .= gettype($dupes) . "\nDupes:\n";
            foreach ($dupes as $key => $value) {
                $showText .= $key . ' = ' . $frame->expand($value) . "\n";
            }
        }

        show($showText);
    }

    /**
     * Takes the input from the various forms of #splitargs and returns it as a cohesive set of variables.
     *
     * @param Parser $parser
     * @param PPFrame $frame
     * @param array $magicArgs
     * @param array $values
     *
     * @return [type]
     *
     */
    private static function splitArgsCommon(Parser $parser, PPFrame $frame, array $magicArgs, $templateName, $nargs, array $named, array $values)
    {
        if ($nargs < 1 || empty($templateName)) {
            return '';
        }

        $allowEmpty = ParserHelper::arrayGet($magicArgs, ParserHelper::NA_ALLOWEMPTY, false);
        $templates = self::getTemplates($frame, $templateName, $nargs, $values, $named, $allowEmpty);
        if (empty($templates)) {
            return '';
        }

        // show("Templates:\n", $templates);
        $separator = ParserHelper::getSeparator($frame, $magicArgs);
        $output = implode($separator, $templates);
        // show("Output:\n", $output);

        return ParserHelper::formatPFForDebug($output, ParserHelper::checkDebugMagic($parser, $frame, $magicArgs));
    }

    /**
     * Splits named arguments from unnamed.
     *
     * @param PPFrame $frame The template frame in use.
     * @param array|null $args The arguments to split.
     *
     * @return array
     */
    private static function splitNamedArgs(PPFrame $frame, array $args = null)
    {
        $named = [];
        $unnamed = [];
        if (!is_null($args)) {
            // $unnamed[] = $args[0];
            for ($i = 0; $i < count($args); $i++) {
                list($name, $value) = ParserHelper::getKeyValue($frame, $args[$i]);
                if (is_null($name)) {
                    $unnamed[] = $value;
                } else {
                    $named[(string)$name] = $value;
                }
            }
        }

        return [$named, $unnamed];
    }

    /**
     * Recursively parses a single PPNode and strips the relevant links from it.
     *
     * @param Parser $parser The parser in use.
     * @param PPFrame $frame The template frame in use.
     * @param PPNode $node The node to work on.
     *
     * @return string
     */
    private static function trimLinksParseNode(Parser $parser, PPFrame $frame, PPNode $node)
    {
        if (self::isLink($node)) {
            // show($node->value);
            $close = strpos($node->value, ']]');
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

            if ($leadingColon || (!$title->isExternal() && !in_array($ns, [NS_CATEGORY, NS_FILE, NS_MEDIA, NS_SPECIAL]))) {
                $after = substr($node->value, $close + 2);
                if (isset($split[1])) {
                    // If display text was provided, preserve formatting but put self-closed nowikis at each end to break any accidental formatting that results.
                    return "<nowiki/>{$split[1]}<nowiki/>$after";
                } else {
                    // For title-only links, formatting should not be applied at all, so just surround the entire thing with nowiki tags.
                    $text = $title ? $title->getPrefixedText() : $titleText;
                    return "<nowiki/>$text<nowiki/>$after";
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
        } elseif ($node instanceof PPNode_Hash_Attr) {
            return $frame->expand($node);
        } else {
            return $frame->expand($node);
        }
    }
}
