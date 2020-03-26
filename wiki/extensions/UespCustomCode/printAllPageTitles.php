<?php

/*
 * Output all page titles to stdout in the format:
 * 
 * 	https://en.uesp.net/wiki/PAGETITLE
 * 
 * Page title is escaped like it would be in the browser address bar.
 *
 * Run from the root MediaWiki directory:
 * 		cd /home/uesp/www/w/
 * 		php ./extensions/UespCustomCode/printAllPageTitles.php
 * 
 */

require_once '/home/uesp/www/w/maintenance/Maintenance.php';
//require_once __DIR__ . '/Maintenance.php';


class PrintAllTitles extends Maintenance {
	
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Print all page titles to stdout";
		$this->setBatchSize( 100 );
	}

	
	public function execute() {
		$dbr = wfGetDB( DB_SLAVE );
		$startId = 0;
		
		$res = $dbr->select( 'page',
				array( 'page_id', 'page_namespace', 'page_title' ),
				array(  ),
				__METHOD__,
				array(
					'ORDER BY' => 'page_id'
				)
			);
		
		if ( !$res->numRows() ) return;
		
		$urls = array();
		
		foreach ( $res as $row ) {
			$title = Title::makeTitle( $row->page_namespace, $row->page_title );
			$url = $title->getInternalURL();
			$urls[] = $url;
			print ("$url\n");
		}
	}
};

$maintClass = "PrintAllTitles";
//require_once RUN_MAINTENANCE_IF_MAIN;
require_once "/home/uesp/www/w/maintenance/doMaintenance.php";