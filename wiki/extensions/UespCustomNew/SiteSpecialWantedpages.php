<?php
/**
 * Implement a tweak to the Wantedpages SQL query -- by creating an entire new class to replace the standard class
 **/
global $IP;
require_once "$IP/includes/specials/SpecialWantedpages.php";

class SiteWantedPagesPage extends WantedPagesPage {

        function __construct( $name = 'Wantedpages' ) {
                parent::__construct( $name );
	}


	/* Prevent redlinks that appear on user pages or user talk pages (namespaces 2 and 3) from appearing in Wantedpages list */
	
	function getQueryInfo() {
		$queryinfo = parent::getQueryInfo();
		for ($i=0; $i<count($queryinfo['conds']); $i++) {
			if (strpos($queryinfo['conds'][$i], 'pg2.page_namespace')!==false)
				$queryinfo['conds'][$i] = 'pg2.page_namespace NOT IN ('.NS_USER.','.NS_USER_TALK.','.NS_MEDIAWIKI.')';
		}
		return $queryinfo;
	}
}
