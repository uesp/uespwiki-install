<?php

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = __DIR__ . '/../../..';
}
require_once "$IP/maintenance/Maintenance.php";

use MediaWiki\MediaWikiServices;
use PageImages\Job\InitImageDataJob;

/**
 * @license WTFPL 2.0
 * @author Max Semenik
 */
class InitImageData extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = 'Initializes PageImages data';
		$this->addOption( 'namespaces',
			'Comma-separated list of namespace(s) to refresh', false, true );
		$this->addOption( 'earlier-than',
			'Run only on pages earlier than this timestamp', false, true );
		$this->addOption( 'start', 'Starting page ID', false, true );
		$this->addOption( 'queue-pressure', 'Maximum number of jobs to enqueue at a time. ' .
			'If not provided or 0 will be run in-process.', false, true );
		$this->addOption( 'quiet', "Don't report on job queue pressure" );
		$this->setBatchSize( 100 );
	}

	public function execute() {
		global $wgPageImagesNamespaces;

		$lastId = $this->getOption( 'start', 0 );
		$isQuiet = $this->getOption( 'quiet', false );
		$queue = null;
		$maxPressure = $this->getOption( 'queue-pressure', 0 );
		if ( $maxPressure > 0 ) {
			$queue = JobQueueGroup::singleton();
		}

		do {
			$tables = [ 'page', 'imagelinks' ];
			$conds = [
				'page_id > ' . (int)$lastId,
				'il_from IS NOT NULL',
				'page_is_redirect' => 0,
			];
			$fields = [ 'page_id' ];
			$joinConds = [ 'imagelinks' => [
				'LEFT JOIN', 'page_id = il_from',
			] ];

			$dbr = wfGetDB( DB_SLAVE );
			if ( $this->hasOption( 'namespaces' ) ) {
				$ns = explode( ',', $this->getOption( 'namespaces' ) );
				$conds['page_namespace'] = $ns;
			} else {
				$conds['page_namespace'] = $wgPageImagesNamespaces;
			}
			if ( $this->hasOption( 'earlier-than' ) ) {
				$conds[] = 'page_touched < '
					. $dbr->addQuotes( $this->getOption( 'earlier-than' ) );
			}
			$res = $dbr->select( $tables, $fields, $conds, __METHOD__,
				[ 'LIMIT' => $this->mBatchSize, 'ORDER_BY' => 'page_id', 'GROUP BY' => 'page_id' ],
				$joinConds
			);
			$pageIds = [];
			foreach ( $res as $row ) {
				$pageIds[] = $row->page_id;
			}
			$job = new InitImageDataJob( Title::newMainPage(), [ 'page_ids' => $pageIds ] );
			if ( $queue === null ) {
				$job->run();
			} else {
				$queue->push( $job );
				$this->waitForMaxPressure( $queue, $maxPressure, $isQuiet );
			}
			$lastId = end( $pageIds );
			$this->output( "$lastId\n" );
		} while ( $res->numRows() );
		$this->output( "done\n" );
	}

	/**
	 * @param JobQueueGroup $queue The job queue to fetch pressure from
	 * @param int $maxPressure The maximum number of queued + active
	 *  jobs that can exist when returning
	 * @param bool $isQuiet When false report on job queue pressure every 10s
	 */
	private function waitForMaxPressure( JobQueueGroup $queue, $maxPressure, $isQuiet ) {
		$group = $queue->get( 'InitImageDataJob' );
		$i = 0;
		do {
			sleep( 1 );
			$queued = $group->getSize();
			$running = $group->getAcquiredCount();
			$abandoned = $group->getAbandonedCount();

			if ( !$isQuiet && ++$i % 10 === 0 ) {
				$now = date( 'Y-m-d H:i:s T' );
				$this->output( "[$now] Queued: $queued Running: $running " .
					"Abandoned: $abandoned Max: $maxPressure\n" );
			}
		} while ( $queued + $running - $abandoned >= $maxPressure );
	}
}

$maintClass = 'InitImageData';
require_once DO_MAINTENANCE;
