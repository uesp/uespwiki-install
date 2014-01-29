<?php

class JobQueue
{
	public static function onSpecialStatsAddExtra( &$extraStats ) 
	{
		global $wgJobQueuePrecisionCutoff;
		$numJobs = SiteStats::jobs();
		if ( $numJobs < $wgJobQueuePrecisionCutoff ) {
			$dbr = wfGetDB( DB_SLAVE );
			$cnt = $dbr->selectField( 'job', 'COUNT(*)' );
			$numJobs = $cnt;
		}
		$extraStats[wfMsg('jobqueuemc')] = $numJobs;
		return true;
	}
}