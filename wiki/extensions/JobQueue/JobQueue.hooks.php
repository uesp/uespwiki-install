<?php

class JobQueueStatHooks
{
	public static function onSpecialStatsAddExtra( &$extraStats ) 
	{
		global $wgJobQueuePrecisionCutoff;
		$numJobs = SiteStats::jobs();
		if ( $numJobs < $wgJobQueuePrecisionCutoff ) {
			$dbr = wfGetDB( DB_SLAVE );
			$numJobs = $dbr->selectField( 'job', 'COUNT(*)' );
		}
		$extraStats['jobqueue-mc'] = $numJobs;
		return true;
	}
}