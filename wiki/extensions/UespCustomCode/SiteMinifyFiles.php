<?php

/**
 * Command-line script for UespCustomCode to be run at installation time
 *
 * This script is designed so it can be run after any Mediawiki update / new installation
 * 
 * It minifies all of the css and js files, and also appends some UESP-specific customizations
 * to the appropriate files.  All original files are backed up to ".orig" filenames before
 * being minified.  The script can safely be rerun if necessary.
 * 
 * It may take a few minutes to run -- however most of the run time is typically just
 * built-in delays to prevent the script from overloading external websites.
 * If the delays are excessively annoying, decrease the value of $egWebRequestDelay
 *
**/

// check that script is being run from command line
$maint = dirname( dirname( __FILE__ ) ) . '/maintenance';
if( is_file( $maint . '/commandLine.inc' ) ) {
	require_once( $maint . '/commandLine.inc' );
} else {
	$maint = dirname( dirname( dirname( __FILE__ ) ) ) . '/maintenance';
	if( is_file( $maint . '/commandLine.inc' ) ) {
		require_once( $maint . '/commandLine.inc' );
	} else {
		# We can't find it, give up
		echo( "The installation script was unable to find the maintenance directories.\n\n" );
		die( 1 );
	}
}

$egWebRequestDelay = 10;
$dir = dirname(__FILE__) . '/';
/* This script uses the value of $IP from LocalSettings to determine the location of the to-be-modified
   files.  If you want this to be run on a non-installed set of directories, reset $IP here to point
   to the correct base directory */

// for any standard mediawiki file, provide a list of local files to merge with that standard file
$mergefiles = array(
// no longer merging UespCustom.css (keep with default clear settings for thumbnail images)
	$IP.'/skins/common/shared.css' => array('Common.css'),
// merge all .css files with monobook/main.css (common/common.css doesn't appear to be loaded on standard pages?)
	$IP.'/skins/monobook/main.css' => array('Monobook.css'),
// merging Monobook.js with wikibits.js because mediawiki says that Monobook.js is deprecated and Common.js should be used instead
// also, wikibits.js is the only .js file (other than ajax.js) that seems to be loaded on pages
	$IP.'/skins/common/wikibits.js' => array('Monobook.js', 'Common.js')
);

// all css and js files in the following directories will be minified
$checkdirs = array($IP.'/skins/common', $IP.'/skins/monobook');

$preminsize = 0;
$postminsize = 0;
$nfiles = 0;
foreach ($checkdirs as $dirname) {
	$dp = opendir($dirname);
	while ($filename=readdir($dp)) {
// this will skip .orig versions of any files -- which is what we want, since those get processed when base filename is processed
		if (!preg_match('/\.(css|js)$/i', $filename))
			continue;
		$filename = $dirname .'/' . $filename;
		print "processing $filename\n";
		$origfile = $filename . ".orig";
		if( !file_exists($origfile))
			rename($filename, $origfile);

		$fp = fopen($origfile, 'r');
		$text = fread($fp, 100000);
		fclose($fp);

		if (array_key_exists($filename, $mergefiles)) {
			foreach ($mergefiles[$filename] as $mergename) {
				if (!file_exists($dir.'files/'.$mergename))
					continue;
				$fp = fopen($dir.'files/'.$mergename, "r");
				$text .= fread($fp, 100000);
				fclose($fp);
			}
		}

		if (preg_match('/\.css$/i', $filename))
			$mintext = minify_css($text);
		else
			$mintext = minify_js($text);

		if (is_null($mintext))
			$mintext = $text;

		$fpout = fopen($filename, "w");
		fwrite($fpout, $mintext);
		fclose($fpout);

		$nfiles++;
		$preminsize += strlen($text);
		$postminsize += strlen($mintext);
	}
}

print "\n\n{$nfiles} files minified.\nOriginal total size of files: {$preminsize}\nFinal total size of files: {$postminsize}\n";
print "Overall size reduction: ".sprintf("%.1f", ($preminsize-$postminsize)/$preminsize*100)."%\n";
/*
 * Minification functions
 * These functions work via online sites that provide minification features and therefore do not
 * require any functions to be installed on the server
 * 
 * Caveats:
 * - I'm assuming the websites can be trusted to provide requested features
 * - The functions rely upon PHP's cURL module (which by default is bundled with PHP)
 * - Because the functions are exploting someone else's website, there is a built-in delay
 *   ($egWebRequestDelay) with each request so that this bot-style use doesn't annoy anyone
 * - These functions are sensitive to changes in the websites (in particular, changes in the
 *   names of the form input fields).  I'm not sure that the function's current checks will
 *   always pick up such changes (although hopefully the sites are stable enough, and simple
 *   enough, that such changes are unlikely).
 */

function minify_js($text) {
	global $egWebRequestDelay;

	if (strlen($text)==0)
		return $text;
	sleep($egWebRequestDelay);

	$ch = curl_init("http://jscompress.com/");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);

	$formfields = array();
	$formfields['js'] = $text;
	$formfields['jsmin'] = 'selected';
	$formfields['js_compression'] = 'jsmin';
	curl_setopt($ch, CURLOPT_POSTFIELDS, $formfields);

	$curldata = curl_exec($ch);
	if ($curldata===false) {
		print "Unable to contact jscompress.com\n";
		return NULL;
	}
	preg_match_all('/<\s*textarea\s*([^>]*)>(.*?)<\s*\/\s*textarea/is', $curldata, $textareas, PREG_PATTERN_ORDER);
	$mintext = NULL;
	for ($i=0; $i<count($textareas[1]); $i++) {
		if (preg_match('/name\s*=\s*[\'"]?\s*js_output/i', $textareas[1][$i])) {
			if (strlen($textareas[2][$i]))
				$mintext = $textareas[2][$i];
			break;
		}
	}

	if (is_null($mintext))
		print "Unable to extract minified js from jscompress.com: website format may have been updated\n";
	return $mintext;
}

function minify_css($text) {
	global $egWebRequestDelay;

	if (strlen($text)==0)
		return $text;
	sleep($egWebRequestDelay);

//	$ch = curl_init("http://shygypsy.com/cssCompress/");
// I'm directly calling the site's CGI script here, bypassing the actual web page completely
	$ch = curl_init("http://shygypsy.com/cssCompress/cssCompress.cgi");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);

	$formfields = array();
	$formfields['css'] = $text;
	curl_setopt($ch, CURLOPT_POSTFIELDS, $formfields);

	$curldata = curl_exec($ch);
	// no need to process the return text, because the CGI script directly returns the text I want
	if ($curldata===false) {
		print "Unable to contact shygypsy.com/cssCompress\n";
		return NULL;
	}
	elseif (strlen($curldata)==0) {
		print "Unable to extract minified css from shygypsy.com/cssCompress: website format may have been updated\n";
		return NULL;
	}
	else
		return $curldata;
}
?>
