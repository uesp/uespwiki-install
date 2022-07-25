<?php

namespace CirrusSearch\BuildDocument\Completion;

/**
 * Simple class for SuggestionsBuilder that needs to munge the title
 * into a list of "subphrases" suggestions.
 * Subphrases are only generated for title, redirects are not yet supported.
 * A set of new fields is used to insert these suggestions 'suggest-extra'
 * is used by default but can be overridden with string[] getExtraFields().
 */
class NaiveSubphrasesSuggestionsBuilder implements ExtraSuggestionsBuilder {
	/** @const string */
	const LANG_FIELD = 'language';

	/** @const int */
	const MAX_SUBPHRASES = 10;

	/** @const string subpage type */
	const SUBPAGE_TYPE = 'subpage';

	/** @const string subpage type */
	const STARTS_WITH_ANY_WORDS_TYPE = 'anywords';

	/**
	 * @var string[] list of regex char ranges indexed by type
	 */
	private static $RANGES_BY_TYPE = [
		self::SUBPAGE_TYPE => '\/',
		self::STARTS_WITH_ANY_WORDS_TYPE => '\/\s',
	];

	/** @var int */
	private $maxSubPhrases;

	/**
	 * @var string regex character range, this value must be a valid char
	 * range and will be used to build a regular expression like
	 * '[' . $charRange . ']'
	 */
	private $charRange;

	/**
	 * @param string $charRange character range used to split subphrases
	 * @param int $maxSubPhrases defaults to MAX_SUBPHRASES
	 */
	public function __construct( $charRange, $maxSubPhrases = self::MAX_SUBPHRASES ) {
		$this->charRange = $charRange;
		$this->maxSubPhrases = $maxSubPhrases;
	}

	public static function create( array $config ) {
		$limit = isset( $config['limit'] ) ? $config['limit'] : self::MAX_SUBPHRASES;
		if ( !isset( self::$RANGES_BY_TYPE[$config['type']] ) ) {
			throw new \Exception( "Unsupported NaiveSubphrasesSuggestionsBuilder type " .
				$config['type'] );
		}
		$cr = self::$RANGES_BY_TYPE[$config['type']];
		return new self( $cr, $limit );

	}

	/**
	 * Get the char range used by this builder
	 * to split and generate subphrase suggestions
	 * @return string a valid regex char range that will be inserted inside
	 * square brackets.
	 */
	protected function getCharRange() {
		return $this->charRange;
	}

	/**
	 * List of FST fields where the subphrase suggestions
	 * will be added.
	 * @return string[]
	 */
	protected function getExtraFields() {
		return ['suggest-subphrases'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getRequiredFields() {
		// This builder needs the language field
		// to exclude subpages generated by the translate
		// extension
		return [ self::LANG_FIELD ];
	}

	/**
	 * @param mixed[] $inputDoc
	 * @param string $suggestType (title or redirect)
	 * @param int $score
	 * @param \Elastica\Document $suggestDoc suggestion type (title or redirect)
	 * @param int $targetNamespace
	 */
	public function build( array $inputDoc, $suggestType, $score, \Elastica\Document $suggestDoc, $targetNamespace ) {
		if ( $suggestType === SuggestBuilder::REDIRECT_SUGGESTION ) {
			// It's unclear howto support redirects here.
			// Since we use Util::chooseBestRedirect at search time
			// It seems hard to retrieve the best redirect if
			// we destroy it with this builder. We would have to
			// add a special code at search time and apply the
			// same splitting strategy on retrieved redirects.
			return;
		}

		$language = "";
		if ( isset ( $inputDoc[self::LANG_FIELD] ) ) {
			$language = $inputDoc[self::LANG_FIELD];
		}

		$subPages = $this->tokenize( $inputDoc['title'], $language );
		$suggest = $suggestDoc->get( 'suggest' );
		$suggest['input'] = $subPages;
		foreach( $this->getExtraFields() as $field ) {
			$suggestDoc->set( $field, $suggest );
		}
	}

	/**
	 * Split a translated page title into an array
	 * with the title at offset 0 and the language
	 * subpage at offset 1.
	 *
	 * e.g. splitTranslatedPage("Hello/en", "en")
	 *  - will output [ "Hello", "/en" ]
	 * e.g. splitTranslatedPage("Hello/test", "en")
	 *  - will output [ "Hello/test", "" ]
	 *
	 * @param string $title
	 * @param string $language
	 * @return string[]
	 */
	public function splitTranslatedPage( $title, $language ) {
		$langSubPage = '/' . $language;
		if ( strlen( $langSubPage ) < strlen( $title ) &&
			substr_compare( $title, $langSubPage, -strlen( $langSubPage ) ) == 0
		) {
			return [ substr( $title, 0, -strlen( $langSubPage ) ), $langSubPage ];
		} else {
			return [ $title, "" ];
		}
	}

	/**
	 * Tokenize the input $title by generating phrases suited
	 * for completion search.
	 * e.g. :
	 * $title = "Hello Beautifull Word/en";
	 * $builder->tokenize( $title, "en", "\\s" );
	 * will generate the following array:
	 *   [ "Beautifull Word/en", "Word/en" ]
	 *
	 * @param string $title
	 * @param $language
	 * @param string $cr a character range suited to be used inside [$cr]
	 * @return string[] tokenized phrasal suggestions
	 */
	public function tokenize( $title, $language ) {
		list( $title, $langSubPage ) = $this->splitTranslatedPage( $title, $language );

		$cr = $this->getCharRange();
		$matches = preg_split( "/[$cr]+/", $title, $this->maxSubPhrases + 1,
			PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY );
		// Remove the first one because it's the whole title
		array_shift( $matches );
		$subphrases = [];
		foreach( $matches as $m ) {
			$subphrases[] = substr( $title, $m[1] ) . $langSubPage;
		}
		return $subphrases;
	}
}
