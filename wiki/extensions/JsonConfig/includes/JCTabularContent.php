<?php

namespace JsonConfig;

use ParserOptions;
use ParserOutput;
use Title;

/**
 * @package JsonConfig
 */
class JCTabularContent extends JCDataContent {

	protected function createDefaultView() {
		return new JCTabularContentView();
	}

	/**
	 * Returns wiki-table representation of the tabular data
	 *
	 * @return string|bool The raw text, or false if the conversion failed.
	 */
	public function getWikitextForTransclusion() {

		$toWiki = function( $value ) {
			if ( is_object( $value ) ) {
				global $wgLang;
				$value = JCUtils::pickLocalizedString( $value, $wgLang );
			}
			if ( preg_match('/^[ .\pL\pN]*$/i', $value ) ) {
				// Optimization: spaces, letters, numbers, and dots are returned without <nowiki>
				return $value;
			}
			return '<nowiki>' . htmlspecialchars( $value ) . '</nowiki>';
		};

		$data = $this->getData();
		$result = "{| class='wikitable sortable'\n";

		// Create header
		$result .= '!' . implode( "!!", array_map( $toWiki, $data->headers ) ) . "\n";

		// Create table content
		foreach ( $data->rows as $row ) {
			$result .= "|-\n|" . implode( '||', array_map( $toWiki, $row ) ) . "\n";
		}

		$result .= "\n|}\n";

		return $result;
	}

	/**
	 * Derived classes must implement this method to perform custom validation
	 * using the check(...) calls
	 */
	public function validateContent() {

		parent::validateContent();

		if ( !$this->thorough() ) {
			// We are not doing any modifications to the original, so no need to validate it
			return;
		}

		$validators = [ JCValidators::isList() ];
		if ( $this->test( 'headers', JCValidators::isList() ) &&
			 $this->testEach( 'headers', JCValidators::isHeaderString() ) &&
			 $this->test( 'headers', JCValidators::listHasUniqueStrings() )
		) {
			$headerCount = count( $this->getField( 'headers' )->getValue() );
			$countValidator = JCValidators::checkListSize( $headerCount, 'headers' );
			$validators[] = $countValidator;
		} else {
			$headerCount = false;
		}

		$makeDefaultTypes = function () use ( $headerCount ) {
			return $headerCount === false ? array() : array_fill( 0, $headerCount, 'string' );
		};
		$typeValidators = [];
		if ( $this->testOptional( 'types', $makeDefaultTypes, $validators ) ) {
			if ( !$this->testEach( 'types', JCValidators::validateDataType( $typeValidators ) ) ) {
				$typeValidators = false;
			}
		}

		$this->test( 'rows', JCValidators::isList() );
		$this->testEach( 'rows', $validators );
		if ( $typeValidators ) {
			$this->testEach( 'rows', function ( JCValue $v, array $path ) use ( $typeValidators ) {
				$isOk = true;
				$lastIdx = count( $path );
				foreach ( array_keys( $typeValidators ) as $k ) {
					$path[$lastIdx] = $k;
					$isOk &= $this->test( $path, $typeValidators[$k] );
				}
				return $isOk;
			} );
		}
	}
}
