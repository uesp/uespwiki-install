<?php

use MediaWiki\MediaWikiServices;

/**
 * An extension to add data persistence and variable manipulation to MediaWiki.
 *
 * At this point, the code could easily be split into four separate extensions based on the SSTNG constants, but at as
 * they're likely to all be used together, with the possible exception of the Define group as of MW 1.35, it seems
 * easier to keep them together for easier maintenance.
 */
class MetaTemplate
{
	#region Public Constants
	public const AV_ANY = 'metatemplate-any';

	public const KEY_METATEMPLATE = '@metatemplate';

	public const NA_CASE = 'metatemplate-case';
	public const NA_FULLPAGENAME = 'metatemplate-fullpagename';
	public const NA_NAMESPACE = 'metatemplate-namespace';
	public const NA_PAGEID = 'metatemplate-pageid';
	public const NA_PAGENAME = 'metatemplate-pagename';
	public const NA_SHIFT = 'metatemplate-shift';

	public const PF_DEFINE = 'define';
	public const PF_FULLPAGENAMEx = 'fullpagenamex';
	public const PF_INHERIT = 'inherit';
	public const PF_LOCAL = 'local';
	public const PF_NAMESPACEx = 'namespacex';
	public const PF_PAGENAMEx = 'pagenamex';
	public const PF_PREVIEW = 'preview';
	public const PF_RETURN = 'return';
	public const PF_UNSET = 'unset';

	public const STTNG_ENABLECPT = 'EnableCatPageTemplate';
	public const STTNG_ENABLEDATA = 'EnableData';
	public const STTNG_ENABLEDEFINE = 'EnableDefine';
	public const STTNG_ENABLEPAGENAMES = 'EnablePageNames';

	public const VR_FULLPAGENAME0 = 'metatemplate-fullpagename0';
	public const VR_NAMESPACE0 = 'metatemplate-namespace0';
	public const VR_NESTLEVEL = 'metatemplate-nestlevel';
	public const VR_NESTLEVEL_VAR = 'metatemplate-nestlevel-var';
	public const VR_PAGENAME0 = 'metatemplate-pagename0';
	#endregion

	#region Public Static Variables
	/** @var string */
	public static $catViewer;

	/** @var ?string */
	public static $mwFullPageName = null;

	/** @var ?string */
	public static $mwNamespace = null;

	/** @var ?string */
	public static $mwPageId = null;

	/** @var ?string */
	public static $mwPageName = null;
	#endregion

	#region Private Static Variables
	/**
	 * An array of strings containing the names of parameters that should be passed through to a template, even if
	 * displayed on its own page.
	 *
	 * @var array $bypassVars // @ var MagicWordArray
	 */
	private static $bypassVars = null;

	/** @var Config $config */
	private static $config;
	#endregion

	#region Public Static Functions
	/**
	 * Checks the `case` parameter to see if it matches `case=any` or any of the localized equivalents.
	 *
	 * @param array $magicArgs The magic-word arguments as created by getMagicArgs().
	 *
	 * @return bool True if `case=any` or any localized equivalent was found in the argument list.
	 */
	public static function checkAnyCase(array $magicArgs): bool
	{
		return ParserHelper::magicKeyEqualsValue($magicArgs, self::NA_CASE, self::AV_ANY);
	}

	/**
	 * @return GlobalVarConfig The global variable configuration for MetaTemplate.
	 */
	public static function configBuilder(): GlobalVarConfig
	{
		return new GlobalVarConfig('egMetaTemplate');
	}

	/**
	 * Sets the value of a variable if it has not already been set. This is most often done to provide a default value
	 * for a parameter if it was not passed in the template call.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *         1: The variable name.
	 *         2: The variable value.
	 *      case: Whether the name matching should be case-sensitive or not. Currently, the only allowable value is
	 *            'any', along with any translations or synonyms of it.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return void
	 */
	public static function doDefine(Parser $parser, PPFrame $frame, array $args): void
	{
		// Show {{{parameter names}}} if on the actual template page and not previewing, but allow bypass variables
		// like ns_base/ns_id through at all times.
		$parser->addTrackingCategory('metatemplate-tracking-variables');
		if (!$frame->parent && $parser->getTitle()->getNamespace() === NS_TEMPLATE && !$parser->getOptions()->getIsPreview()) {
			if (!isset(self::$bypassVars)) {
				self::getBypassVariables();
			}

			$varName = trim($frame->expand($args[0]));
			if (!isset(self::$bypassVars[$varName])) {
				return;
			}
		}

		self::checkAndSetVar($frame, $args, false);
	}

	/**
	 * Gets the full page name at a given point in the stack.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *     depth: The stack depth to check.
	 *
	 * @return string The requested full page name.
	 */
	public static function doFullPageNameX(Parser $parser, PPFrame $frame, ?array $args): string
	{
		$parser->addTrackingCategory('metatemplate-tracking-frames');
		$title = self::getTitleAtDepth($parser, $frame, $args);
		return is_null($title) ? '' : $title->getPrefixedText();
	}

	/**
	 * Inherit variables from the calling template(s).
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *        1+: The variable(s) to unset.
	 *      case: Whether the name matching should be case-sensitive or not. Currently, the only allowable value is
	 *            'any', along with any translations or synonyms of it.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return void
	 */
	public static function doInherit(Parser $parser, PPFrame $frame, array $args)
	{
		$parser->addTrackingCategory('metatemplate-tracking-frames');
		if (!$frame->depth) {
			return;
		}

		/*
		// Debug code to show entire stack.
		$curFrame = $frame;
		do {
			RHshow("$curFrame->depth: Inherit args for {$curFrame->getTitle()->getPrefixedText()}", $curFrame->getArguments());
			$curFrame = $curFrame->parent;
		} while ($curFrame);
		*/

		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_DEBUG,
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT,
			self::NA_CASE
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (!$values || !ParserHelper::checkIfs($frame, $magicArgs)) {
			return;
		}

		$debug = ParserHelper::checkDebugMagic($parser, $frame, $magicArgs);
		$translations = self::getVariableTranslations($frame, $values);
		$inherited = [];
		foreach ($translations as $srcName => $destName) {
			if (!isset($frame->numberedArgs[$destName]) && !isset($frame->namedArgs[$destName])) {
				$anyCase = self::checkAnyCase($magicArgs);
				$varValue = self::inheritVar($frame, $srcName, $anyCase);
				if (!is_null($varValue)) {
					#RHshow($destName, $varValue);
					$varValue = trim($varValue);
					self::setVar($frame, $destName, $varValue, $anyCase);
					if ($debug) {
						$inherited[] = "$destName=$varValue";
					}
				}
			}
		}

		if ($debug) {
			$varList = implode("\n", $inherited);
			return ParserHelper::formatPFForDebug($varList, true, false, 'Inherited Variables');
		}
	}

	/**
	 * Sets the value of a variable. This is most often used to create local variables or modify a template parameter's
	 * value. Any previous value will be overwritten.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *         1: The variable name.
	 *         2: The variable value.
	 *      case: Whether the name matching should be case-sensitive or not. Currently, the only allowable value is
	 *            'any', along with any translations or synonyms of it.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return void
	 */
	public static function doLocal(Parser $parser, PPFrame $frame, array $args): void
	{
		$parser->addTrackingCategory('metatemplate-tracking-variables');
		self::checkAndSetVar($frame, $args, true);
	}

	/**
	 * Gets the namespace at a given point in the stack.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *     depth: The stack depth to check.
	 *
	 * @return string The requested namespace.
	 */
	public static function doNamespaceX(Parser $parser, PPFrame $frame, ?array $args): string
	{
		$parser->addTrackingCategory('metatemplate-tracking-frames');
		$title = self::getTitleAtDepth($parser, $frame, $args);
		return $title ? $title->getNsText() : '';
	}

	/**
	 * Gets the template stack depth.
	 *
	 * For example, if a page calls template {{A}} which in turn calls template {{B}}, then {{NESTLEVEL}} would report:
	 *     0 if used on the page itself,
	 *     1 if used in {{A}},
	 *     2 if used in {{B}}.
	 *
	 * @param PPFrame $frame The frame in use.
	 *
	 * @return int The frame depth.
	 */
	public static function doNestLevel(Parser $parser, PPFrame $frame): int
	{
		$parser->addTrackingCategory('metatemplate-tracking-frames');
		// Rely on internal magic word caching; ours would be a duplication of effort.
		$nestlevelVars = VersionHelper::getInstance()->getMagicWord(MetaTemplate::VR_NESTLEVEL_VAR);
		$lastVal = false;
		foreach ($frame->getNamedArguments() as $arg => $value) {
			// We do a matchStartToEnd() here rather than flipping the logic around and iterating through synonyms in
			// case someone overrides the declaration to be case-insensitive. Likewise, we always check all arguments,
			// regardless of case-sensitivity, so that the last one defined is always used in the event that there are
			// multiple qualifying values defined.
			if ($nestlevelVars->matchStartToEnd($arg)) {
				$lastVal = $value;
			}
		}

		return $lastVal !== false
			? $lastVal
			: $frame->depth;
	}

	/**
	 * Gets the page name at a given point in the stack.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *     depth: The stack depth to check.
	 *
	 * @return string The requested page name.
	 */
	public static function doPageNameX(Parser $parser, PPFrame $frame, ?array $args): string
	{
		$parser->addTrackingCategory('metatemplate-tracking-frames');
		$title = self::getTitleAtDepth($parser, $frame, $args);
		return is_null($title) ? '' : $title->getText();
	}

	/**
	 * Sets the value of a variable but only in Show Preview mode. This allows values to be specified as though the
	 * template had been called with those arguments. Like #define, #preview will not override any values that are
	 * already set.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The template frame in use.
	 * @param array $args Function arguments:
	 *     1: The variable name.
	 *     2: The variable value.
	 *
	 * @return void
	 */
	public static function doPreview(Parser $parser, PPFrame $frame, array $args): void
	{
		$parser->addTrackingCategory('metatemplate-tracking-variables');
		if (
			$frame->depth == 0 &&
			$parser->getOptions()->getIsPreview()
		) {
			self::checkAndSetVar($frame, $args, false);
		}
	}

	/**
	 * Returns values from a child template to its immediate parent. Unlike a traditional programming language, this
	 * has no effect on program flow.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *        1+: The variable(s) to return, optionally including an "into" specifier.
	 *      case: Whether the name matching should be case-sensitive or not. Currently, the only allowable value is
	 *            'any', along with any translations or synonyms of it. Case insensitivity only applies to the
	 *            receiving end. The variables listed in the #return statement must match exactly.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return void
	 */
	public static function doReturn(Parser $parser, PPFrame $frame, array $args): void
	{
		$parser->addTrackingCategory('metatemplate-tracking-frames');
		$parent = $frame->parent;
		if (!$parent) {
			return;
		}

		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT,
			self::NA_CASE
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (!$values || !ParserHelper::checkIfs($frame, $magicArgs)) {
			return;
		}

		$anyCase = self::checkAnyCase($magicArgs);
		$translations = self::getVariableTranslations($frame, $values);
		foreach ($translations as $srcName => $destName) {
			$result = self::getVarDirect($frame, $srcName, $anyCase);
			if ($result) {
				$dom = $result[1];
				$expand = $frame->expand($dom, PPFrame::NO_IGNORE | PPFrame::NO_TEMPLATES);
				$expand = VersionHelper::getInstance()->getStripState($frame->parser)->unstripBoth($expand);
				$expand = trim($expand);
				$dom = $frame->parser->preprocessToDom($expand, 0);
				self::setVarDirect($parent, $destName, $dom);
			}
		}
	}

	/**
	 * Unsets (removes) variables from the template.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *        1+: The variable(s) to unset.
	 *      case: Whether the name matching should be case-sensitive or not. Currently, the only allowable value is
	 *            'any', along with any translations or synonyms of it.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 *
	 * @return void
	 */
	public static function doUnset(Parser $parser, PPFrame $frame, array $args): void
	{
		$parser->addTrackingCategory('metatemplate-tracking-variables');

		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT,
			self::NA_CASE,
			self::NA_SHIFT
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		if (!count($values) || !ParserHelper::checkIfs($frame, $magicArgs)) {
			return;
		}

		$anyCase = self::checkAnyCase($magicArgs);
		$shift = (bool)($magicArgs[self::NA_SHIFT] ?? false);
		foreach ($values as $value) {
			$varName = trim($frame->expand($value));
			self::unsetVar($frame, $varName, $anyCase, $shift);
		}
	}

	public static function getCatViewer()
	{
		if (!isset(self::$catViewer)) {
			if (version_compare(VersionHelper::getMWVersion(), '1.37', '>=')) {
				$version = 37;
			} elseif (version_compare(VersionHelper::getMWVersion(), '1.28', '>=')) {
				$version = 28;
			} else {
				throw new Exception('MediaWiki version could not be found or is too low.');
			}

			$class = "MetaTemplateCategoryViewer$version";
			require_once(__DIR__ . "/$class.php");
			self::$catViewer = $class;
		}

		return self::$catViewer;
	}

	/**
	 * Gets a confiuration object, as required by modern versions of MediaWiki.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Configuration_for_developers
	 *
	 * @return Config
	 */
	public static function getConfig(): Config
	{
		if (is_null(self::$config)) {
			self::$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig(strtolower(__CLASS__));
		}

		return self::$config;
	}

	/**
	 * This low-level function determines how MetaTemplate should behave. Possible values can be found in the "config"
	 * section of extension.json. Prepend the names with $metatemplate to alter their values in LocalSettings.php.
	 * Currently, these include:
	 *
	 *     EnableCatPageTemplate (self::STTNG_ENABLECPT) - if set to false, the following features are disabled:
	 *         <catpagetemplate>
	 *     EnableData (self::STTNG_ENABLEDATA) - if set to false, the following features are disabled:
	 *         {{#listsaved}}, {{#load}}, {{#save}}, <savemarkup>
	 *     EnableDefine (self::STTNG_ENABLEDEFINE) - if set to false, the following features are disabled:
	 *         {{#define}}, {{#inherit}}, {{#local}}, {{#preview}}, {{#return}}, {{#unset}}
	 *     EnablePageNames (self::STTNG_ENABLEPAGENAMES) - if set to false, the following features are disabled:
	 *         {{FULLPAGENAME0}}, {{FULLPAGENAMEx}}, {{NAMESPACEx}}, {{NAMESPACE0}}, {{NESTLEVEL}}, {{PAGENAME0}},
	 *         {{PAGENAMEx}}
	 *     ListsavedMaxTemplateSize	- templates with lengths above the size listed here will not be exectued.
	 *
	 * @param string $setting
	 *
	 * @return bool Whether MetaTemplate can/should use a particular feature.
	 */
	public static function getSetting($setting): bool
	{
		$config = self::getConfig();
		return (bool)$config->get($setting);
	}

	/**
	 * Gets a variable from the frame in raw (DOM) format. This should only be used when you need to reparse the
	 * original DOM tree in some way, such as when saving markup. It should also be used when checking the existence of
	 * a variable, since it won't cause frame expansion when doing so. Use $frame->getargument() and its variants if all
	 * you need is the straight-up value.
	 *
	 * @param PPFrame $frame The frame to start at.
	 * @param int|string $varName The variable name.
	 * @param bool $anyCase Whether the variable's name is case-sensitive or not.
	 *
	 * @return array<string, ?PPNode> Returns the name of the value found (so calls using anyCase=true know where to
	 * look if they need to) and the value in raw format.
	 */
	public static function getVarDirect(PPFrame $frame, $varName, bool $anyCase = false): ?array
	{
		#RHshow('GetVar', $varName);
		// Try for an exact match without triggering expansion.

		$varValue = $frame->namedArgs[$varName] ?? $frame->numberedArgs[$varName] ?? null;
		if (!is_null($varValue)) {
			return [$varName, $varValue];
		}

		if ($anyCase && !self::isNumericVariable($varName)) {
			$lcName = $lcName ?? strtolower($varName);
			foreach ($frame->namedArgs as $key => $varValue) {
				if (strtolower($key) === $lcName) {
					return [$key, $varValue];
				}
			}
		}

		return null;
	}

	/**
	 * Splits a variable list of the form 'x->xPrime' to a proper associative array.
	 *
	 * @param PPFrame $frame If the variable names may need to be expanded, this should be set to the active frame;
	 *                       otherwise it should be null.
	 * @param array $variables The list of variables to work on.
	 * @param ?int $trimLength The maximum number of characters allowed for variable names.
	 *
	 * @return array
	 */
	public static function getVariableTranslations(PPFrame $frame, array $variables, ?int $trimLength = null): array
	{
		$retval = [];
		foreach ($variables as $varName) {
			$srcName = trim($frame->expand($varName));
			$varSplit = explode('->', $srcName, 2);
			$srcName = trim($varSplit[0]);
			// In PHP 8, this can be reduced to just substr(trim...)).
			$srcName = $trimLength ? substr($srcName, 0, $trimLength) : $srcName;
			if (count($varSplit) === 2) {
				$destName = trim($varSplit[1]);
				$destName = $trimLength ? substr($destName, 0, $trimLength) : $destName;
			} else {
				$destName = $srcName;
			}

			$retval[(string)$srcName] = $destName;
		}

		#RHshow('Translations', $retval);
		return $retval;
	}

	public static function init()
	{
		if (
			MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLECPT) ||
			MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)
		) {
			$helper = VersionHelper::getInstance();
			self::$mwFullPageName = $helper->getMagicWord(MetaTemplate::NA_FULLPAGENAME)->getSynonym(0);
			self::$mwNamespace = $helper->getMagicWord(MetaTemplate::NA_NAMESPACE)->getSynonym(0);
			self::$mwPageId = $helper->getMagicWord(MetaTemplate::NA_PAGEID)->getSynonym(0);
			self::$mwPageName = $helper->getMagicWord(MetaTemplate::NA_PAGENAME)->getSynonym(0);
		}
	}

	/**
	 * Takes the provided variable and adds it to the template frame as though it had been passed in. Automatically
	 * unsets any previous values, including case-variant values if $anyCase is true.
	 *
	 * @internal This also shifts any numeric-named arguments it touches from named to numeric. This should be
	 * inconsequential, but is mentioned in case there's something I've missed.
	 *
	 * @param PPFrame $frame The frame in use.
	 * @param string $varName The variable name. This should be pre-trimmed, if necessary.
	 * @param PPNode|string $value The variable value.
	 *     PPNode: use some variation of argument expansion before sending the node here.
	 *
	 *
	 * @return void
	 */
	public static function setVar(PPFrame $frame, string $varName, string $varValue, $anyCase = false): void
	{
		#RHshow('setVar', $varName, ' = ', is_object($varValue) ? ''  : '(' . gettype($varValue) . ')', $varValue);
		// self::checkFrameType($frame);
		self::unsetVar($frame, $varName, $anyCase);
		$dom = $frame->parser->preprocessToDom($varValue); // was: (..., $frame->depth ? Parser::PTD_FOR_INCLUSION : 0)
		$dom->name = 'value';
		// If the value is blank or text-only, which will be the case the vast majority of times, we can use it to set
		// the cache immediately and avoid future expansion.
		$child = $dom->getFirstChild();
		$varValue = ($child === false || ($child instanceof PPNode_Hash_Text && !$child->getNextSibling()))
			? $varValue
			: null;
		self::setVarDirect($frame, $varName, $dom, $varValue);
	}

	/**
	 * Takes the provided DOM tree and optional text representation and adds both to the template frame directly.
	 * Unlike the regular version, this DOES NOT UNSET any previous values. Do not use this to simply set a text value.
	 *
	 * @internal This also shifts any numeric-named arguments it touches from named to numeric. This should be
	 * inconsequential, but is mentioned in case there's something I've missed.
	 *
	 * @param PPFrame $frame The frame in use.
	 * @param int|string $varName The variable name. This should be pre-trimmed, if necessary. If an int is specified,
	 *     this will always treat the value as anonymous; otherwise, it will be inferred based on whether the name is
	 *     all digits.
	 * @param PPNode $dom The variable value as a tree.
	 * @param string $cacheValue The expanded value, if needed. Otherwise, the cache will be left uninitialized,
	 *     expanding only when needed.
	 *
	 * @return void
	 */
	public static function setVarDirect(PPFrame $frame, $varName, PPNode $dom, ?string $cacheValue = null): void
	{
		#RHshow('setVarDirect', $varName, ' = ', is_object($varValue) ? ''  : '(' . gettype($varValue) . ')', $varValue);
		// self::checkFrameType($frame);
		if (is_int($varName) || self::isNumericVariable($varName)) {
			$args = &$frame->numberedArgs;
			$cache = &$frame->numberedExpansionCache;
			$varName = (int)$varName;
		} else {
			$args = &$frame->namedArgs;
			$cache = &$frame->namedExpansionCache;
		}

		$args[$varName] = $dom;
		if (is_null($cacheValue)) {
			unset($cache[$varName]);
		} else {
			$cache[$varName] = $cacheValue;
		}
	}

	/**
	 * Sets a variable for all synonyms of a key.
	 *
	 * @param PPFrame $frame The frame in use
	 * @param iterable $synonyms
	 * @param string $value
	 */
	public static function setVarSynonyms(PPFrame $frame, iterable $synonyms, string $value): void
	{
		foreach ($synonyms as $synonym) {
			MetaTemplate::setVar($frame, $synonym, $value);
		}
	}

	/**
	 * Unsets a template variable.
	 *
	 * @param PPFrame $frame The frame in use.
	 * @param string $varName The variable to unset.
	 * @param bool $anyCase Whether the variable match should be case insensitive.
	 * @param bool $shift For numeric unsets, whether to shift everything above it down by one.
	 *
	 * @return void
	 */
	public static function unsetVar(PPFrame $frame, $varName, bool $anyCase, bool $shift = false): void
	{
		// self::checkFrameType($frame);
		unset(
			$frame->namedArgs[$varName],
			$frame->namedExpansionCache[$varName]
		);
		if (is_int($varName) || self::isNumericVariable($varName)) {
			unset(
				$frame->numberedArgs[$varName],
				$frame->numberedExpansionCache[$varName]
			);
			if ($shift) {
				self::shiftVars($frame, $varName);
			}
		} elseif ($anyCase) {
			$lcname = strtolower($varName);
			foreach ($frame->namedArgs as $key => $ignored) {
				if (strtolower($key) === $lcname) {
					unset(
						$frame->namedArgs[$key],
						$frame->namedExpansionCache[$key]
					);
				}
			}
		}
	}
	#endregion

	#region Private Static Functions
	/**
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *      case: Whether the name matching should be case-sensitive or not. Currently, the only allowable value is
	 *            'any', along with any translations or synonyms of it.
	 *        if: A condition that must be true in order for this function to run.
	 *     ifnot: A condition that must be false in order for this function to run.
	 * @param bool $overwrite Whether the incoming variable is allowed to overwrite any existing one.
	 *
	 * @return void
	 */
	private static function checkAndSetVar(PPFrame $frame, array $args, bool $overwrite): void
	{
		#RHshow('Args', $frame->getArguments());
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			ParserHelper::NA_IF,
			ParserHelper::NA_IFNOT,
			self::NA_CASE
		]);

		/** @var array $magicArgs */
		/** @var array $values */
		[$magicArgs, $values] = ParserHelper::getMagicArgs($frame, $args, $magicWords);
		// No values possible with, for example, {{#local:if=1}}
		if (!ParserHelper::checkIfs($frame, $magicArgs) || !count($values)) {
			return;
		}

		$varName = trim($frame->expand($values[0]));
		if (substr($varName, 0, strlen(MetaTemplate::KEY_METATEMPLATE)) === MetaTemplate::KEY_METATEMPLATE) {
			return;
		}

		$anyCase = self::checkAnyCase($magicArgs) && !self::isNumericVariable($varName);
		$dom = null;
		if (count($values) < 2) {
			if (!$anyCase) {
				return;
			}

			// We don't use unsetVar() here because we need the value of $dom if found.
			$lcname = strtolower($varName);
			foreach ($frame->namedArgs as $key => $varValue) {
				if (strtolower($key) === $lcname && $key !== $lcname) {
					$dom = $varValue;
					unset($key);
					self::setVarDirect($frame, $varName, $dom, $frame->namedExpansionCache[$key]);
				}
			}

			return;
		}

		// Set the variable if:
		//     * this is a #local;
		//     * a variable was found above via case=any, so we need to assign the existing value to the correct case;
		//     * there is no existing definition for the variable.
		if ($overwrite || is_null(self::getVarDirect($frame, $varName, $anyCase))) {
			$dom = $values[1] ?? null;
			if (!is_null($dom)) {
				$varDisplay = trim($frame->expand($dom));
				$prevMode = MetaTemplateData::$saveMode;
				MetaTemplateData::$saveMode = 3;
				// Because we have to expand variables, the generated dom tree can get misprocessed in the event of
				// something with an = or | (pipe) in it. I haven't found a good resolution for this. Should I a do
				// manual replace: = with {{=}} and similar? Since this should only affect variables saved within a
				// savemarkup context, it's an edge case and so, for now, I'm not worrying about it.
				//
				// Example: if mod=<sup class=example>TR</sup>
				// {{Echo|{{{mod}}}}} becomes
				// {{Echo|(variable: <sup class)(equals)(value: example>TR</sup>)}}
				$varValue = trim($frame->expand($dom, PPFrame::NO_IGNORE | PPFrame::NO_TEMPLATES));
				$dom = $frame->parser->preprocessToDom($varValue);
				MetaTemplateData::$saveMode = $prevMode; // Revert to previous before expanding for display.
				self::setVarDirect($frame, $varName, $dom, $varDisplay);
				#RHshow('varValue', $varValue, "\ngetArg(): ", $frame->getArgument($varName), "\ngetVar(): ", $frame->expand(self::getVarDirect($frame, $varName, false)[1], PPFrame::RECOVER_ORIG));
			}
		}
	}

	/**
	 * Gets a list of variables that can bypass the normal variable definition lockouts on a template page. This means
	 * that variables which would normally display as {{{ns_id}}}, for example, will instead take on the specified/
	 * default values.
	 *
	 * @return void
	 */
	private static function getBypassVariables(): void
	{
		$bypassList = [];
		Hooks::run('MetaTemplateSetBypassVars', [&$bypassList]);
		self::$bypassVars = [];
		foreach ($bypassList as $bypass) {
			self::$bypassVars[$bypass] = true;
		}
	}

	/**
	 * Gets the title at a specific depth in the template stack.
	 *
	 * @param Parser $parser The parser in use.
	 * @param PPFrame $frame The frame in use.
	 * @param array $args Function arguments:
	 *     1: The depth of the title to get. Negative numbers start at the top of the template stack instead of the
	 *        current depth. Common values include:
	 *            0 = the current page
	 *            1 = the parent page
	 *           -1 = the first page
	 *
	 * @return ?Title
	 */
	private static function getTitleAtDepth(Parser $parser, PPFrame $frame, ?array $args): ?Title
	{
		$level = empty($args[0])
			? 0
			: (int)$frame->expand($args[0]);
		$depth = $frame->depth;
		$level = ($level > 0) ? $depth - $level + 1 : -$level;
		if ($level < $depth) {
			while ($frame && $level > 0) {
				$frame = $frame->parent;
				$level--;
			}

			return isset($frame) ? $frame->title : null;
		}

		return $level === $depth
			? $parser->getTitle()
			: null;
	}

	/**
	 * Gets a raw variable by searching through the entire stack.
	 *
	 * @param PPFrame $frame The frame to start at.
	 * @param string $varName The variable name.
	 * @param bool $anyCase Whether the variable's name is case-sensitive or not.
	 * @param bool $checkAll Whether to look for the variable in this template only or climb through the entire stack.
	 * @internal This function is separated out from the main parser function so that it can potentially be used for
	 *     internal inheritance, such as possibly inheriting debug variables in the future.
	 *
	 * @return string The fully expanded value of the variable.
	 */
	private static function inheritVar(PPFrame $frame, string $varName, bool $anyCase): ?string
	{
		#RHshow('inhertVar', "$varName ", (int)(bool)($frame->numberedArgs[$varName] ?? $frame->namedArgs[$varName] ?? false));
		/** @var PPFrame|false $curFrame */
		$nextFrame = $frame->parent;
		$anyCase &= !self::isNumericVariable($varName);
		$dom = null;
		while ($nextFrame && is_null($dom)) {
			$curFrame = $nextFrame;
			$result = self::getVarDirect($curFrame, $varName, $anyCase);
			if ($result) {
				$varName = $result[0];
				$dom = $result[1];
			}

			$nextFrame = $nextFrame->parent;
		}

		// We have to expand the value fully or else variables will be mis-evaluated and functions like {{PAGENAMEx:#}}
		// will return incorrect results.
		$varValue = is_null($dom) ? null : $curFrame->getArgument($varName);
		return $varValue;
	}

	/**
	 * As the nane suggests, this determines if a variable name is numeric.
	 *
	 * @param mixed $varName The name to check.
	 *
	 * @return bool True if the name is numeric.
	 *
	 */
	private static function isNumericVariable($varName): bool
	{
		return ctype_digit($varName) && $varName !== '0';
	}

	/**
	 * Unsets a numeric variable and shifts everything above it down by one.
	 *
	 * @param PPFrame $frame The frame in use.
	 * @param string $varName The numeric variable to unset.
	 *
	 * @return void
	 */
	private static function shiftVars(PPFrame $frame, string $varName): void
	{
		$newArgs = [];
		$newCache = [];
		foreach ($frame->numberedArgs as $key => $value) {
			if ($varName != $key) {
				$newKey = ($key > $varName) ? $key - 1 : $key;
				$newArgs[$newKey] = $value;
				if (isset($frame->numberedExpansionCache[$key])) {
					$newCache[$newKey] = $frame->numberedExpansionCache[$key];
				}
			}
		}

		$frame->numberedArgs = $newArgs;
		$frame->numberedExpansionCache = $newCache;

		$newArgs = [];
		$newCache = [];
		foreach ($frame->namedArgs as $key => $value) {
			if ($varName != $key) {
				$newKey = self::isNumericVariable($key) && $key > $varName ? $key - 1 :  $key;
				$newArgs[$newKey] = $value;
				if (isset($frame->namedExpansionCache[$key])) {
					$newCache[$newKey] = $frame->namedExpansionCache[$key];
				}
			}
		}

		$frame->namedArgs = $newArgs;
		$frame->namedExpansionCache = $newCache;
	}
	#endregion
}
