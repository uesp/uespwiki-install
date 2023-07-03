<?php

/**
 * Adds a Google map to the page using the supplied parameters.
 *
 * @author Dave Humphrey <dave@uesp.net>
 * @copyright Public domain
 * @license Public domain
 * @package MediaWikiExtensions
 * @version 0.3
 */
/* modified from version 0.1 to 0.2 by Nephele
 * - adding compatibility with mwmap
 * - using js and css from maps directory instead of duplicating it here
 * - setting up some controls in js so they can be based on js settings 
 * 
 * v0.3 - Modified by Daveh
 * - Added support for the v2 maps using the v3 Google maps API.
 * 
 * */

/**
 * Register the Map extension with MediaWiki
 */ 
$wgExtensionFunctions[] = 'registerUespMapExtension';
$wgExtensionCredits['parserhook'][] = array(
	'name' => 'UespMap',
	'author' => 'Dave Humphrey',
	'url' => '//www.uesp.net/wiki/UESPWiki:Extension_UESPMap',
	'description' => 'Allows the UESP maps to be inserted into a wiki page with the optional ability to edit locations.',
	'version' => 0.3
);


/**
 * Global extension parameters
 */
$umUespGame = "sr";		/* Default game map */

/**
 * Sets the tag that this extension looks for and the function by which it
 * operates
 */
function registerUespMapExtension()
{
	global $wgParser;
	$wgParser->setHook('uespmap', 'renderUespMap');
}


/**
 * Renders a new map: generic function used by all versions of map
 */
function renderUespMap($input, $params, $parser)
{	
	global $wgUser;
	global $wgOut;
	global $wgRequest;
	global $wgScriptPath;
	global $umUespGame;

		// Default display settings
	$width				= "100%";
	$height				= "600px";
	$showedit			= false;
	$showresults		= false;
	$showsearch			= true;
	$showcells			= false;
	$disablecontrols	= false;
	$showdisabled		= false;
	$usedev				= false;
	$border				= "none";
	$showinfo			= true;
	$searchtext			= "";
	$centeron			= "";
	$locx				= "";
	$locy				= "";
	$zoom				= "";
	
	$ExtensionURL = '//'.$_SERVER['HTTP_HOST'].$wgScriptPath.'/extensions/UespMap/';
	
		// Ensure the page is not cached because of the dynamic map content
	$parser->disableCache();

		// Ignore if the user is not allowed to the view the map
	if( !$wgUser->isAllowed( 'map' ) ) {
		return "";
	}

		// Parse input parameters
	if (array_key_exists("game",   $params)) $umUespGame = strtolower(substr($params["game"], 0, 2));	
	if (array_key_exists("border", $params)) $border     = $params["border"];
	if (array_key_exists("locx",   $params)) $locx       = $params["locx"];
	if (array_key_exists("locy",   $params)) $locy       = $params["locy"];
	if (array_key_exists("zoom",   $params)) $zoom       = $params["zoom"];
	
	if (array_key_exists("searchtext", $params)) {
		$searchtext = $params["searchtext"];
	}
	
	if (array_key_exists("centeron", $params)) {
		$centeron = $params["centeron"];
	}
	
	if (array_key_exists("search", $params)) {
		if ($params["search"] == "false") { $showsearch = false; }
		else if ($params["search"] == "true") { $showsearch = true; }
	}
	if (array_key_exists("results", $params)) {
		if ($params["results"] == "false") { $showresults = false; }
		else if ($params["results"] == "true") { $showresults = true; }
	}
	
	if (array_key_exists("edit", $params)) {
		if ($params["edit"] == "false") { $showedit = false; }
		else if ($params["edit"] == "true") { $showedit = true; }
	}
	
	if (array_key_exists("control", $params)) {
		if ($params["control"] == "false") { $disablecontrols = true; }
		else if ($params["control"] == "true") { $disablecontrols = false; }
	}
	
	if (array_key_exists("showdisabled", $params)) {
		if ($params["showdisabled"] == "false") { $showdisabled = false; }
		else if ($params["showdisabled"] == "true") { $showdisabled = true; }
	}
	
	if (array_key_exists("dev", $params)) {
		if ($params["dev"] == "false") { $usedev = false; }
		else if ($params["dev"] == "true") { $usedev = true; }
	}
	
	if (array_key_exists("showinfo", $params)) {
		if ($params["showinfo"] == "false") { $showinfo = false; }
		else if ($params["showinfo"] == "true") { $showinfo = true; }
	}
	
	if (array_key_exists("cells", $params)) {
		if ($params["cells"] == "false") { $showcells = false; }
		else if ($params["cells"] == "true") { $showcells = true; }
	}
	
	if (array_key_exists("width",  $params)) { $width  = $params["width"]; }
	if (array_key_exists("height", $params)) { $height = $params["height"]; }
	
	if( !$wgUser->isAllowed( 'mapedit' )) $showedit = false;
	
	$_SESSION['mapview'] = 1;
	
	if( $wgUser->isAllowed( 'mapedit' ) && $showedit) {
		$_SESSION['mapedit'] = 1;
	}
	else {
		$_SESSION['mapedit'] = 0;
	}
	
	$FrameQuery = "?";
	if ($locx != "") $FrameQuery .= "locx=" . $locx . "&";
	if ($locy != "") $FrameQuery .= "locy=" . $locy . "&";
	if ($zoom != "") $FrameQuery .= "zoom=" . $zoom . "&";
	$FrameQuery .= "edit=" . ($showedit ? "true" : "false") . "&";
	$FrameQuery .= "showsearch=" . ($showsearch ? "true" : "false") . "&";
	$FrameQuery .= "showresults=" . ($showresults ? "true" : "false") . "&";
	$FrameQuery .= "showcells=" . ($showcells ? "true" : "false") . "&";
	$FrameQuery .= "disablecontrols=" . ($disablecontrols ? "true" : "false") . "&";
	$FrameQuery .= "showdisabled=" . ($showdisabled ? "true" : "false") . "&";
	$FrameQuery .= "showinfo=" . ($showinfo ? "true" : "false") . "&";
	
	if ($centeron) $FrameQuery .= "centeron=". $centeron;
	if ($searchtext) $FrameQuery .= "search=". $searchtext;
	
	$devurl = "";
	if ($usedev) $devurl="_dev";
	
	$Result  = "";
	$Result .= "<div style='width: " . $width . "; height: " . $height . "; border: none;'>";
	$Result .= "<iframe src='". $ExtensionURL . $umUespGame . "mapwiki".$devurl.".html" . $FrameQuery . "' ";
	$Result .= " id='umWikiMap' ";
	$Result .= " width='" . $width . "' height='" . $height . "' ";
	$Result .= " style='border: ". $border .";' ";
	$Result .= "></iframe></div>";
	
	return $Result;
	
	$showtoolbar  = true;
	$showlayout   = false;
	$searchwidth  = "300px";
	$searchheight = "";
	$topmapmargin = "24px";
	$showoverview = true;
	$showzoom     = true;
	$showpan      = true;
	$mapdragging  = true;
	
	if (array_key_exists("toolbar",		$params) && $params["toolbar"]    == "false") { $showtoolbar = false; }
	if (array_key_exists("overview",	$params) && $params["overview"]    == "false") { $showoverview = false; }
	if (array_key_exists("zoomcontrol",	$params) && $params["zoomcontrol"] == "false") { $showzoom     = false; }
	if (array_key_exists("pancontrol",	$params) && $params["pancontrol"]  == "false") { $showpan      = false; }
	if (array_key_exists("mapdragging",	$params) && $params["mapdragging"] == "false") { $mapdragging  = false; }
	if (array_key_exists("showlayout",	$params) && $params["showlayout"]	== "true")  { $showlayout  = true; }
	

		// Output things
	$Result = umOutputCSS($showlayout);
	$Result .= '<div id="umMapParent" style="width:'.$width.'; height:'.$height.';">';

	if (! $showlayout) {
		$Result .= umOutputGoggleScript();
	}

	if ($showsearch)  { $Result .= umOutputSearchForm($searchwidth); }
	if ($showtoolbar) { $Result .= umOutputToolBar(); }

	if (!$showsearch && !$showtoolbar) {
		$topmapmargin = "0px";
	}

	if ($showresults) { 
		$Result .= umOutputMapArea($searchwidth, $topmapmargin);
		$Result .= umOutputSearchResults($searchwidth, $searchheight, $topmapmargin);
	} else {
		$Result .= umOutputMapArea("0px", $topmapmargin);
	}	

	if (! $showlayout) {
		$Result .= umOutputScripts($showoverview, $showzoom, $showpan, $mapdragging);
	}

	$Result .= '</div>';

	if( $wgUser->isAllowed( 'mapedit' ) && $showedit) {
		$Result .= umOutputEdit();
		$_SESSION['mapedit'] = 1;
	}
	else {
		$_SESSION['mapedit'] = 0;
	}

	//$Result .= "Session mapedit = '".$_SESSION['mapedit']."', ".$_SESSION['test'].", id='".session_id()."' ";
	session_write_close();

	return ($Result);
}

