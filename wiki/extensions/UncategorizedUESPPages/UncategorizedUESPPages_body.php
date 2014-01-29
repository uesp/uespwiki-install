<?php
/**
 * @file
 * @ingroup SpecialPage
 */

/**
 * A special page looking for page without any category.
 * @ingroup SpecialPage
 */
class UncategorizedUESPPages extends PageQueryPage {
	var $requestedNamespace = NULL;

	function getName() {
		return "UncategorizedUESPPages";
	}

	function sortDescending() {
		return false;
	}

	function isExpensive() {
		return true;
	}
	function isSyndicated() { return false; }

	function getSQL() {
		$dbr = wfGetDB( DB_SLAVE );
		list( $page, $categorylinks ) = $dbr->tableNamesN( 'page', 'categorylinks' );
		$name = $dbr->addQuotes( $this->getName() );

		$sql = "SELECT
				$name as type,
				page_namespace AS namespace,
				page_title AS title,
				page_title AS value
			FROM $page
			LEFT JOIN $categorylinks ON page_id=cl_from
			WHERE cl_from IS NULL
			   AND page_is_redirect=0
                           AND page_namespace ";
		// default (NULL) is to show uncategorized pages from all content namespaces
		if (is_null($this->requestedNamespace)) {
			$sql .= Namespace::isContentQuery();
		}
		// otherwise, only show requested namespace (i.e., NS_CATEGORY or NS_TEMPLATE)
		else {
			$sql .= '='.$this->requestedNamespace;
		}

		return $sql;
			
	}
}

/**
 * constructor
 */
function wfSpecialUncategorizedUESPpages() {
	list( $limit, $offset ) = wfCheckLimits();

	$lpp = new UncategorizedUESPPages();

	return $lpp->doQuery( $offset, $limit );
}
