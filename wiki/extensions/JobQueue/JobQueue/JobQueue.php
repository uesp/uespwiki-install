<?php

$wgExtensionCredits['other'][] = array(
       'name' => 'JobQueue',
	   'url' => 'http://www.uesp.net/wiki/UESPWiki:JobQueue',
       'description' => '',
       'descriptionmsg' => 'jobqueue-desc',
       'version'  => 0.1,
       'author' => '[[User:RobinHood70|RobinHood70]]', 
       );

/*
 * If estimated job count is less than $wgJobQueuePrecisionCutoff, Job Queue
 * will requery the job table for a precise count.
 *
 * There is a very small chance that the job queue could jump enormously
 * between the estimate and the precision query. If this is unacceptable, set
 * $wgJobQueuePrecisionCutoff = 0.
 */
$wgJobQueuePrecisionCutoff = 20;

$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['JobQueueStat'] = "$dir/JobQueue.i18n.php";
$wgAutoloadClasses['JobQueueStatHooks'] = "$dir/JobQueue.hooks.php";
$wgHooks['SpecialStatsAddExtra'][] = 'JobQueueStatHooks::onSpecialStatsAddExtra';