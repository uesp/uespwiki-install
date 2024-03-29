<?php

namespace CirrusSearch\Query;

use CirrusSearch\Search\SearchContext;

/**
 * Filters the result set based on pages containing outgoing wiki links to the
 * provided page title.
 *
 * Examples:
 *   linksto:"Wake Island"
 *   linksto:Wake_Island
 *   linksto:Shanghai
 */
class LinksToFeature extends SimpleKeywordFeature {
	/**
	 * @return string[]
	 */
	protected function getKeywords() {
		return ['linksto'];
	}

	/**
	 * @param SearchContext $context
	 * @param string $key The keyword
	 * @param string $value The value attached to the keyword with quotes stripped
	 * @param string $quotedValue The original value in the search string, including quotes if used
	 * @param bool $negated Is the search negated? Not used to generate the returned AbstractQuery,
	 *  that will be negated as necessary. Used for any other building/context necessary.
	 * @return array Two element array, first an AbstractQuery or null to apply to the
	 *  query. Second a boolean indicating if the quotedValue should be kept in the search
	 *  string.
	 */
	protected function doApply( SearchContext $context, $key, $value, $quotedValue, $negated ) {
		return [ QueryHelper::matchPage( 'outgoing_link', $value, true ), false ];
	}
}
