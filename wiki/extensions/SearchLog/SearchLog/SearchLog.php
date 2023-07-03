<?php

	# Alert the user that this is not a valid entry point to MediaWiki if they try to access the special pages file directly.
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/MyExtension/MyExtension.php" );
EOT;
        exit( 1 );
}
 
$wgExtensionCredits['specialpage'][] = array(
        'name' => 'SearchLog',
        'author' => 'Dave Humphrey',
        'url' => 'http://www.uesp.net/wiki/UESPWiki:SearchLog',
        'description' => 'Logs all Wiki searches, accumulates and displays search statistics.',
        'descriptionmsg' => 'searchlog-desc',
        'version' => '0.0.4',
);
 
$dir = dirname(__FILE__) . '/';
 
$wgAutoloadClasses['SpecialSearchLog'] = $dir . 'SpecialSearchLog.php'; 
$wgExtensionMessagesFiles['SearchLog'] = $dir . 'SearchLog.i18n.php';
$wgSpecialPages['SearchLog'] = 'SpecialSearchLog'; 
$wgSpecialPageGroups['SearchLog'] = 'other';

$wgHooks['SpecialSearchResults'][]   = 'onSearchResults';
$wgHooks['SpecialSearchNoResults'][] = 'onSearchNoResults';
$wgHooks['SpecialSearchSetupEngine'][] = 'onSearchSetup';

function reorderTerm ($term)
{
	$termarray = explode(" ", strtolower(trim($term)));
	$termarray1 = array_filter($termarray);
	sort($termarray1);

	return implode(" ", $termarray1);
}


function updateSearchLogDB ($term, $titlecount, $textcount, $searchtime)
{
	if ( wfReadOnly() ) return;

	$dbw = wfGetDB(DB_MASTER);
	$newterm = reorderTerm($term);

	$insertdata = array( 	'term' => $newterm,
				'titlecount' => $titlecount,
				'textcount'  => $textcount,
				'searchdate' => date('Y-m-d H:i:s', time()),
				'searchtime' => $searchtime);

	$dbw->insert('searchlog', $insertdata);

	$safeterm = mysql_real_escape_string($newterm);
	$sql = "INSERT INTO searchlog_summary(term, count) VALUES('$safeterm', 1) ON DUPLICATE KEY UPDATE count=count+1";
	$dbw->query($sql, __METHOD__);
}

function onSearchSetup( $search, $profile, $engine )
{
	global $slStartTime;

	$slStartTime = microtime(true);

	return true;
}

function onSearchNoResults ($term)
{
	global $slStartTime;

	updateSearchLogDB($term, 0, 0, microtime(true) - $slStartTime);	
	return true;
}

function onSearchResults ($term, &$titleMatches, &$textMatches)
{
	global $slStartTime;

	$NumTitles = 0;
	$NumText   = 0;

	if (method_exists($titleMatches, 'numRows')) {
		$NumTitles = $titleMatches->numRows();
	} elseif (is_array($titleMatches)) {
		$NumTitles = count($titleMatches);
	}

	if (method_exists($textMatches, 'numRows')) {
		$NumText = $textMatches->numRows();
	} elseif (is_array($textMatches)) {
		$NumText = count($textMatches);
	}

	$searchtime = microtime(true) - $slStartTime;

	updateSearchLogDB($term, $NumTitles, $NumText, $searchtime);
	
	return true;
}



?>
