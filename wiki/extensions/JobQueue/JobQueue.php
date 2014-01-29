<?php

$wgExtensionCredits['other'][] = array(
       'path' => __FILE__,
       'name' => '[http://www.uesp.net/wiki/UESPWiki:JobQueue JobQueue]',
       'description' => '',
       'descriptionmsg' => 'jobqueue-desc',
       'version'  => 0.1,
       'author' => '[[User:RobinHood70|RobinHood70]]', 
       );
$wgExtensionMessagesFiles['JobQueue'] = dirname( __FILE__ ) . '/JobQueue.i18n.php';

/**
 * If estimated job count is less than $wgJobQueuePrecisionCutoff, Job Queue
 * will requery the job table for a precise count.
 *
 * There is a very small chance that the job queue could jump enormously
 * between the estimate and the precision query. If this is unacceptable, set
 * $wgJobQueuePrecisionCutoff = 0.
 */
$wgJobQueuePrecisionCutoff = 20;
$wgAutoloadClasses['JobQueue'] = dirname(__FILE__) . '/JobQueue.body.php';

$wgHooks['SpecialStatsAddExtra'][] = 'JobQueue::onSpecialStatsAddExtra';
