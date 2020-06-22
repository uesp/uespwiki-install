<?php
/**
 * Implement a tweak to the Lonelypages SQL query -- by creating an entire new class to replace the standard class
 **/
global $IP;
require_once "$IP/includes/specials/SpecialLonelypages.php";

class SiteLonelyPagesPage extends LonelyPagesPage {
	// get list of disambiguation pages
	// code copied from SpecialDisambiguations.php
	private function getDisambigSet() {
		$dbr = wfGetDB( DB_SLAVE );
		
		$dMsgText = wfMessage('disambiguationspage');
		
		$linkBatch = new LinkBatch;
		
		# If the text can be treated as a title, use it verbatim.
		# Otherwise, pull the titles from the links table
		$dp = Title::newFromText($dMsgText);
		if( $dp ) {
			if($dp->getNamespace() != NS_TEMPLATE) {
				# FIXME we assume the disambiguation message is a template but
				# the page can potentially be from another namespace :/
				wfDebug("Mediawiki:disambiguationspage message does not refer to a template!\n");
			}	
			$linkBatch->addObj( $dp );
		} else {
				# Get all the templates linked from the Mediawiki:Disambiguationspage
			$disPageObj = Title::makeTitleSafe( NS_MEDIAWIKI, 'disambiguationspage' );
			$res = $dbr->select(
			                     array('pagelinks', 'page'),
			                     'pl_title',
			                     array('page_id = pl_from', 'pl_namespace' => NS_TEMPLATE,
			                           'page_namespace' => $disPageObj->getNamespace(), 'page_title' => $disPageObj->getDBkey()),
			                     __METHOD__ );
			
			while ( $row = $dbr->fetchObject( $res ) ) {
				$linkBatch->addObj( Title::makeTitle( NS_TEMPLATE, $row->pl_title ));
			}
			
			$dbr->freeResult( $res );
		}
		
		$set = $linkBatch->constructSet( 'dtl.tl', $dbr );
		return $set;
	}
	
	/* Pre MW-1.15 version */
	function getSQL() {
		$set = $this->getDisambigSet();
		$sql = parent::getSQL();
		if ( $set !== false ) {
			$dbr = wfGetDB( DB_SLAVE );
			list( $templatelinks) = $dbr->tableNamesN( 'templatelinks' );
			$sql = preg_replace( '/([\s=])tl_/', '$1templatelinks.tl_', $sql );
			$sql = str_replace( ' WHERE', " LEFT JOIN {$templatelinks} as dtl ON (dtl.tl_from=page_id AND $set) WHERE ", $sql );
			$sql .= " AND dtl.tl_title IS NULL ";
				//			$sql .= "
				//		     AND (SELECT DISTINCT tl_from FROM {$templatelinks} AS dtl WHERE dtl.tl_from=page_id AND $set) IS NULL";
		}

		return $sql;
	}
	
	/* Post MW-1.15 (i.e., post-querypage merge) version */
	/* This code may have to be tweaked based on final querypage code ...
	   In particular, check to see final syntax used for joins, then check to see whether templatelinks alias works */
	function getQueryInfo() {
		$set = $this->getDisambigSet();
		$queryinfo = parent::getQueryInfo();
		
		if ( $set !== false ) {
		// fix existing templatelinks references so they're not ambiguous after templatelinks added for second time
			for ($i=0; $i<count($queryinfo['conds']); $i++)
				$queryinfo['conds'][$i] = preg_replace( '/^tl_/', 'templatelinks.tl_', $queryinfo['conds'][$i] );
			for ($i=0; $i<count($queryinfo['join_conds']['templatelinks'][1]); $i++)
				$queryinfo['join_conds']['templatelinks'][1][$i] = preg_replace( '/^tl_/', 'templatelinks.tl_', 	$queryinfo['join_conds']['templatelinks'][1][$i] );
			
			$queryinfo['tables'][] = 'templatelinks AS dtl';
			$queryinfo['join_conds']['templatelinks AS dtl'] =
				array('LEFT JOIN', array('dtl.tl_from' => 'page_id', $set));
			$queryinfo['conds'][] = 'dtl.tl_title IS NULL';
		}
		
		return $queryinfo;
	}
}

/**
 * Constructor
 */
function efSiteSpecialLonelypages() {
	list( $limit, $offset ) = wfCheckLimits();

	$lpp = new SiteLonelyPagesPage();

	return $lpp->doQuery( $offset, $limit );
}
