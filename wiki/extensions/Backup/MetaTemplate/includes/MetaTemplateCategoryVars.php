<?php
class MetaTemplateCategoryVars
{
	#region Public Constants
	public const VAR_CATGROUP = 'metatemplate-catgroup';
	public const VAR_CATLABEL = 'metatemplate-catlabel';
	public const VAR_CATTEXTPOST = 'metatemplate-cattextpost';
	public const VAR_CATTEXTPRE = 'metatemplate-cattextpre';

	public const VAR_SETANCHOR = 'metatemplate-setanchor';
	public const VAR_SETLABEL = 'metatemplate-setlabel';
	public const VAR_SETPAGE = 'metatemplate-setpage';
	public const VAR_SETREDIRECT = 'metatemplate-setredirect';
	public const VAR_SETSEPARATOR = 'metatemplate-setseparator';
	public const VAR_SETSKIP = 'metatemplate-setskip';
	public const VAR_SETSORTKEY = 'metatemplate-setsortkey';
	public const VAR_SETTEXTPOST = 'metatemplate-settextpost';
	public const VAR_SETTEXTPRE = 'metatemplate-settextpre';
	#endregion

	#region Public Properties
	/** @var string */
	public $catGroup;

	/** @var string */
	public $catLabel;

	/** @var string */
	public $catTextPost;

	/** @var string */
	public $catTextPre;

	/** @var string */
	public $setLabel;

	/** @var Title */
	public $setPage;

	/** @var bool */
	public $setRedirect;

	/** @var string */
	public $setSeparator;

	/** @var bool */
	public $setSkip;

	/** @var string */
	public $setSortKey;

	/** @var string */
	public $setTextPost;

	/** @var string */
	public $setTextPre;
	#endregion

	#region Constructor
	public function __construct(PPFrame $frame, Title $title, string $templateOutput)
	{
		static $magicWords;
		$magicWords = $magicWords ?? new MagicWordArray([
			self::VAR_CATGROUP,
			self::VAR_CATLABEL,
			self::VAR_CATTEXTPOST,
			self::VAR_CATTEXTPRE,
			self::VAR_SETANCHOR,
			self::VAR_SETLABEL,
			self::VAR_SETPAGE,
			self::VAR_SETREDIRECT,
			self::VAR_SETSEPARATOR,
			self::VAR_SETSKIP,
			self::VAR_SETSORTKEY,
			self::VAR_SETTEXTPOST,
			self::VAR_SETTEXTPRE
		]);

		// While these aren't actually attributes, the function does exactly what's needed.
		$args = ParserHelper::transformAttributes($frame->getNamedArguments(), $magicWords);
		#RHDebug::show('Args', $frame->getNamedArguments());
		$this->catGroup = $args[self::VAR_CATGROUP] ?? null;
		$this->catLabel = isset($args[self::VAR_CATLABEL])
			? Sanitizer::removeHTMLtags($args[self::VAR_CATLABEL])
			: ($templateOutput === ''
				? $title->getPrefixedText()
				: Sanitizer::removeHTMLtags($templateOutput));
		$this->catTextPost = isset($args[self::VAR_CATTEXTPOST])
			? Sanitizer::removeHTMLtags(ParserHelper::parseSeparator($args[self::VAR_CATTEXTPOST]))
			: '';
		$this->catTextPre = isset($args[self::VAR_CATTEXTPRE])
			? Sanitizer::removeHTMLtags(ParserHelper::parseSeparator($args[self::VAR_CATTEXTPRE]))
			: '';
		$this->setTextPost = isset($args[self::VAR_SETTEXTPOST])
			? Sanitizer::removeHTMLtags(ParserHelper::parseSeparator($args[self::VAR_SETTEXTPOST]))
			: '';
		$this->setTextPre = isset($args[self::VAR_SETTEXTPRE])
			? Sanitizer::removeHTMLtags(ParserHelper::parseSeparator($args[self::VAR_SETTEXTPRE]))
			: '';
		$this->setSeparator = isset($args[self::VAR_SETSEPARATOR])
			? Sanitizer::removeHTMLtags(ParserHelper::parseSeparator($args[self::VAR_SETSEPARATOR]))
			: '';
		$this->setSkip = $args[self::VAR_SETSKIP] ?? false;
		if ($this->setSkip) {
			return;
		}

		$setPageText = $args[self::VAR_SETPAGE] ?? null;
		$setAnchor = $args[self::VAR_SETANCHOR] ?? null;
		if (!is_null($setPageText ?? $setAnchor)) {
			if (is_null($setPageText)) {
				$setPageText = $title->getPrefixedText();
			}

			if (strlen($setAnchor ?? '') > 0) {
				$setPageText .= "#$setAnchor";
			}

			$this->setPage = Title::newFromText($setPageText);
		}

		// Take full text of setpagetemplate ($templateOutput) only if setlabel is not defined. If that's blank, use
		// the normal text.
		$setLabel =
			$args[self::VAR_SETLABEL] ??
			($templateOutput === ''
				? null
				: $templateOutput);
		if (!is_null($setLabel)) {
			$setLabel = Sanitizer::removeHTMLtags($setLabel);
		}

		$this->setLabel = $setLabel;
		$this->setRedirect = $args[self::VAR_SETREDIRECT] ?? null;
		$this->setSortKey = isset($args[self::VAR_SETSORTKEY])
			? Sanitizer::removeHTMLtags($args[self::VAR_SETSORTKEY])
			: $setLabel ?? $setPage ?? $title->getFullText();
	}
	#endregion
}
