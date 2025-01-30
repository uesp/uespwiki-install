<?php

require_once('RHDebug.php');
require_once('VersionHelper.php');

/**
 * Provides a number of library routines, mostly related to the parser along with a few generic global methods.
 */
class ParserHelper
{
	#region Public Constants
	public const AV_ALWAYS = 'parserhelper-always';

	public const NA_DEBUG = 'parserhelper-debug';
	public const NA_IF = 'parserhelper-if';
	public const NA_IFNOT = 'parserhelper-ifnot';
	public const NA_SEPARATOR  = 'parserhelper-separator';
	#endregion

	#region Public Static Functions
	/**
	 * Checks the debug argument to see if it's boolean or 'always'.Expects the keys to be magic word values rather
	 * than magic word IDs.
	 *
	 * @param Parser $parser The parser in use.
	 * @param array $magicArgs The magic-word arguments as created by getMagicArgs().
	 * @param array $magicArgsOld Temporary for backwards compatibility.
	 *
	 * @return bool
	 *
	 */
	public static function checkDebugMagic(Parser $parser, $magicArgs, $magicArgsOld = null): bool
	{
		// Convert old calls to new form. $magicArgs can be made an array instead of mixed once everything is updated.
		if ($magicArgs instanceof PPFrame) {
			$magicArgs = $magicArgsOld;
		}

		$debug = $magicArgs[self::NA_DEBUG] ?? false;
		$matched = VersionHelper::getInstance()->getMagicWord(self::AV_ALWAYS)->matchStartToEnd($debug);
		$preview = $parser->getOptions()->getIsPreview();
		$retval = $preview
			? (bool)$debug
			: $matched;
		#RHshow('Debug', $debug ? 'Yes' : 'No', "\nIn preview mode: ", $preview ? 'Yes' : 'No', "\nDebug word: ", $matched);
		return $retval;
	}

	/**
	 * Checks whether both the `if=` and `ifnot=` conditions have been satisfied.
	 *
	 * @param array $magicArgs The magic-word arguments as created by getMagicArgs().
	 * @param array $magicArgsOld Temporary for backwards compatibility.
	 *
	 * @return bool True if both conditions (if applicable) have been satisfied; otherwise, false.
	 *
	 */
	public static function checkIfs($magicArgs, $magicArgsOld = null): bool
	{
		// Convert old calls to new form. $magicArgs can be made an array instead of mixed once everything is updated.
		if ($magicArgs instanceof PPFrame) {
			$magicArgs = $magicArgsOld;
		}

		return ($magicArgs[self::NA_IF] ?? true) &&
			!($magicArgs[self::NA_IFNOT] ?? false);
	}

	/**
	 * Formats an error message for wikitext output.
	 *
	 * @param string $key The message key in en.json.
	 * @param mixed ...$args Any arguments to be passed to the message.
	 *
	 * @return string The error text wrapped in div tags with an error class.
	 *
	 */
	public static function error(string $key, ...$args): string
	{
		$msg = wfMessage($key)->params($args)->inContentLanguage()->escaped();
		return "<div class='error'>$msg</div>";
	}

	/**
	 * Standardizes debug text formatting for parser functions.
	 *
	 * @param string $output The original text being output.
	 * @param bool $debug Whether to return debug or regular text.
	 *
	 * @return array The modified text.
	 */
	public static function formatPFForDebug(string $output, bool $debug, bool $noparse = false, string $header = null): array
	{
		if (!$debug || !strlen($output)) {
			return [$output, 'noparse' => $noparse];
		}

		$out =  "<table style='background:transparent; border:0px none; margin:0px; border-collapse:collapse;'>";
		if ($header) {
			$out .= "<th style='background:transparent; border:0px none; margin:0px; text-align:center'>$header</th>";
		}

		$out .= '<tr><td style="background:transparent; border:0px none; margin:0px;"><pre>' . htmlspecialchars($output) . '</pre></td></tr></table>';
		return [$out, 'noparse' => false];
	}

	/**
	 * Standardizes debug text formatting for tags.
	 *
	 * @param string $output The original text being output.
	 * @param bool $debug Whether to return debug or regular text.
	 *
	 * @return string The modified text.
	 *
	 */
	public static function formatTagForDebug(string $output, bool $debug): array
	{
		if (!strlen($output)) {
			return [''];
		}

		return $debug
			// Noparse needs to be false for debugging so that <pre> tag correctly breaks all processing.
			? ['<pre>' . htmlspecialchars($output) . '</pre>', 'markerType' => 'nowiki', 'noparse' => false]
			: [$output, 'markerType' => 'none', 'noparse' => false];
	}

	/**
	 * Attempts to find an argument with the given key and returns it as a key/value pair with the key expanded into a
	 * string. The return value is always an array. If the argument is a value only or isn't a recognized key/value
	 * pair, the key will be returned as null and the value will be the original argument.
	 *
	 * @param PPFrame $frame The frame in use.
	 * @param string|PPNode_Hash_Tree $arg The argument to work on.
	 *
	 * @return array tuple<string, PPNode_Hash_Tree|string> The trimmed key and the native value. Because we don't know
	 *     what the caller wants to do, value is left in its native format and is untrimmed.
	 */
	public static function getKeyValue(PPFrame $frame, $arg): array
	{
		if (($arg instanceof PPNode_Hash_Tree && $arg->name === 'part')) {
			$split = $arg->splitArg();
			$key = $split['index'] ? null : trim($frame->expand($split['name']));
			$value = $split['value'];
			return [$key, $value];
		}

		if (is_string($arg)) {
			$split = explode('=', $arg, 2);
			if (count($split) == 2) {
				return [trim($split[0]), $split[1]];
			}
		}

		return [null, $arg];
	}

	/**
	 * Splits the standard parser function arguments into recognized parameters and all others.
	 *
	 * @param PPFrame $frame The frame in use.
	 * @param array $args The arguments to search.
	 * @param MagicWordArray|mixed ...$allowedArgs A list of arguments that should be expanded and returned in the
	 *     `$magic` portion of the returned array. All other arguments will be returned in the `$values` portion of
	 *     the returned array. Currently accepts either an array of magic words constants (e.g. ParserHelper::NA_IF)
	 *     or a MagicWordArray of the same constants. The former is now deprecated; MagicWordArrays should be used in
	 *     all future code.
	 *
	 * @return array The return value consists of three arrays:
	 *     Magic Arguments: contains the list of any named arguments where the key appears in $allowedArray. Keys and
	 *         values are pre-expanded under the assumption that they will be needed that way.
	 *     Values: The second array will contain any arguments that are anonymous or were not specified in
	 *         $allowedArgs. Although these are returned unaltered, anything before the first equals sign (if any) will
	 *         have been expanded as part of processing. If there's no equals sign in the value, none of it will have
	 *         been expanded.
	 *
	 */
	public static function getMagicArgs(PPFrame $frame, array $args, MagicWordArray $allowedArray): array
	{
		$magic = [];
		$values = [];

		foreach ($args as $arg) {
			[$name, $value] = self::getKeyValue($frame, $arg);
			if (is_null($name)) {
				$values[] = $value;
			} else {
				$magKey = $allowedArray->matchStartToEnd(trim($name));
				if ($magKey && !isset($magic[$magKey])) {
					$magic[$magKey] = trim($frame->expand($value));
				} else {
					$values[] = $arg;
				}
			}
		}

		return [$magic, $values];
	}

	/**
	 * Get separator from $magicArgs, if it exists, then parse it using parseSeparator().
	 *
	 * @param array $separator The separator to evaluate. For backwards compatibility, can also be the magic-word
	 * arguments as created by getMagicArgs().
	 *
	 * @return string The parsed string.
	 *
	 */
	public static function getSeparator(array $magicArgs): string
	{
		return isset($magicArgs[self::NA_SEPARATOR])
			? self::parseSeparator($magicArgs[self::NA_SEPARATOR])
			: '';
	}

	/**
	 * Determines if the word at a specific key matches a certain value after everything's converted to their
	 * respective IDs.
	 *
	 * @param array $magicArguments The arguments the key can be found in.
	 * @param string $key The key to search for.
	 * @param string $value The value to match with.
	 *
	 * @return bool True if the value at the specifc key was the same as the value specified.
	 *
	 */
	public static function magicKeyEqualsValue(array $magicArguments, string $key, string $value): bool
	{
		$arrayValue = $magicArguments[$key] ?? null;
		return
			!is_null($arrayValue) &&
			VersionHelper::getInstance()->getMagicWord($value)->matchStartToEnd($arrayValue);
	}

	/**
	 * Parse separator string for C-like character entities and surrounding quotes.
	 *
	 * @param string $separator The separator to evaluate. For backwards compatibility, can also be the magic-word
	 * arguments as created by getMagicArgs().
	 *
	 * @return string The parsed string.
	 *
	 */
	public static function parseSeparator(string $separator): string
	{
		if (strlen($separator) > 1) {
			$first = $separator[0];
			if (in_array($first, ['\'', '`', '"']) && $first === substr($separator, -1, 1)) {
				$separator = substr($separator, 1, -1);
			}

			$separator = stripcslashes($separator);
		}

		return $separator;
	}

	/**
	 * Calls setHook() for all synonyms of a tag.
	 *
	 * @param Parser $parser The parser to register the tag names with.
	 * @param string $id The magic word ID whose synonyms should be registered.
	 * @param callable $callback The function to call when the tag is used.
	 *
	 * @return void
	 *
	 */
	public static function setHookSynonyms(Parser $parser, string $id, callable $callback): void
	{
		$synonyms = VersionHelper::getInstance()->getMagicWord($id)->getSynonyms();
		foreach ($synonyms as $synonym) {
			$parser->setHook($synonym, $callback);
		}
	}

	/**
	 * Splits named arguments from unnamed.
	 *
	 * @param PPFrame $frame The template frame in use.
	 * @param ?array $args The arguments to split.
	 *
	 * @return array An array of arrays, the first element being the named values and the second element being the anonymous values.
	 */
	public static function splitNamedArgs(PPFrame $frame, ?array $args = null): array
	{
		$named = [];
		$unnamed = [];
		if (!empty($args)) {
			// $unnamed[] = $args[0];
			foreach ($args as $ignored => $arg) {
				[$name, $value] = self::getKeyValue($frame, $arg);
				if (is_null($name)) {
					$unnamed[] = $value;
				} else {
					$named[$name] = $value;
				}
			}
		}

		return [$named, $unnamed];
	}

	/**
	 * Transforms tag attributes so that only wanted elements are present and are represented by their qqq key rather
	 * than the language-specific word.
	 *
	 * @param array $attributes The attributes to transform.
	 * @param ?MagicWordArray The MagicWordArray to compare against. Defaults to previously registered magic words.
	 *
	 * @return array The filtered array.
	 *k
	 */
	public static function transformAttributes(array $attributes, MagicWordArray $magicWords): array
	{
		$retval = [];
		foreach ($attributes as $key => $value) {
			$match = $magicWords->matchStartToEnd($key);
			if ($match) {
				$retval[$match] = $value;
			}
		}

		return $retval;
	}

	/**
	 * Formats an error message for wikitext output. Any HTML in the final result will be escaped so it is displayed on screen.
	 *
	 * @param string $key The message key in en.json.
	 * @param mixed ...$args Any arguments to be passed to the message.
	 *
	 * @return string The error text wrapped in div tags with an error class.
	 *
	 */
	public static function unescapedError(string $key, ...$args): string
	{
		$msg = wfMessage($key)->params($args)->inContentLanguage()->text();
		return "<div class='error'>$msg</div>";
	}
	#endregion
}
