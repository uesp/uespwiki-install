<?php

namespace CirrusSearch\Search;

use CirrusSearch\CirrusTestCase;

/**
 * Test escaping search strings.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @group CirrusSearch
 */
class EscaperTest extends CirrusTestCase {

	/**
	 * @dataProvider fuzzyEscapeTestCases
	 */
	public function testFuzzyEscape( $input, $expected, $isFuzzy ) {
		$escaper = new Escaper( 'unittest' );
		$actual = $escaper->fixupWholeQueryString( $input );
		$this->assertEquals( [ $expected, $isFuzzy], $actual );
	}

	public static function fuzzyEscapeTestCases() {
		return [
			'Default fuzziness is allowed' => [ 'fuzzy~', 'fuzzy~', true ],
			'No fuzziness is allowed' => [ 'fuzzy~0', 'fuzzy~0', true ],
			'One char edit distance is allowed' => [ 'fuzzy~1', 'fuzzy~1', true ],
			'Two char edit distance is allowed' => [ 'fuzzy~2', 'fuzzy~2', true ],
			'Three char edit distance is disallowed' => [ 'fuzzy~3', 'fuzzy\\~3', false ],
			'non-integer edit distance is disallowed' => [ 'fuzzy~1.0', 'fuzzy\\~1.0', false ],
			'Larger edit distances are disallowed' => [ 'fuzzy~10', 'fuzzy\\~10', false ],
			'Proximity searches are allowed' => [ '"fuzzy wuzzy"~10', '"fuzzy wuzzy"~10', false ],
			'Float fuzziness with leading 0 is disallowed' => [ 'fuzzy~0.8', 'fuzzy\\~0.8', false ],
			'Float fuzziness is disallowed' => [ 'fuzzy~.8', 'fuzzy\\~.8', false ],
		];
	}

	/**
	 * @dataProvider quoteEscapeTestCases
	 */
	public function testQuoteEscape( $language, $input, $expected ) {
		$escaper = new Escaper( $language );
		$actual = $escaper->escapeQuotes( $input );
		$this->assertEquals( $expected, $actual );
	}

	public static function quoteEscapeTestCases() {
		return [
			[ 'en', 'foo', 'foo' ],
			[ 'en', 'fo"o', 'fo"o' ],
			[ 'el', 'fo"o', 'fo"o' ],
			[ 'de', 'fo"o', 'fo"o' ],
			[ 'he', 'מלבי"ם', 'מלבי\"ם' ],
			[ 'he', '"מלבי"', '"מלבי"' ],
			[ 'he', '"מלבי"ם"', '"מלבי\"ם"' ],
			[ 'he', 'מַ"כִּית', 'מַ\"כִּית' ],
			[ 'he', 'הוּא שִׂרְטֵט עַיִ"ן', 'הוּא שִׂרְטֵט עַיִ\"ן' ],
			[ 'he', '"הוּא שִׂרְטֵט עַיִ"ן"', '"הוּא שִׂרְטֵט עַיִ\"ן"' ],
		];
	}

	/**
	 * @dataProvider balanceQuotesTestCases
	 */
	public function testBalanceQuotes( $input, $expected ) {
		$escaper = new Escaper( 'en' ); // Language doesn't matter here
		$actual = $escaper->balanceQuotes( $input);
		$this->assertEquals( $expected, $actual );
	}

	public static function balanceQuotesTestCases() {
		return [
			[ 'foo', 'foo' ],
			[ '"foo', '"foo"' ],
			[ '"foo" bar', '"foo" bar' ],
			[ '"foo" ba"r', '"foo" ba"r"' ],
			[ '"foo" ba\\"r', '"foo" ba\\"r' ],
			[ '"foo\\" ba\\"r', '"foo\\" ba\\"r"' ],
			[ '\\"foo\\" ba\\"r', '\\"foo\\" ba\\"r' ],
			[ '"fo\\o bar', '"fo\\o bar"' ],
		];
	}
}
