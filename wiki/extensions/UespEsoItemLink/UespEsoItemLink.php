<?php

/*
 * UespEsoItemLink -- by DAve Humphrey, dave@uesp.net, December 2014
 * 
 * Adds the <esoitemlink> tag extension to MediaWiki for displaying ESO item link popup.
 *
 * TODO:
 * 		- Unify JS/CSS files with the EsoLog project (prevent duplicate code)
 * 		- Add parameters:
 * 			- nolink (don't link to the item page)
 * 			- color?
 * 			- itemlink
 * 			- enchant data
 * 		- Feedback/display when loading tooltip
 */

$wgHooks['ParserFirstCallInit'][] = 'uespEsoItemLinkParserInit';
$wgHooks['BeforePageDisplay'][] = 'uesoEsoItemLink_beforePageDisplay';

function uesoEsoItemLink_beforePageDisplay(&$out) {
	global $wgScriptPath;
	
	$out->addHeadItem("uesp-esoitemlink-css", "<link rel='stylesheet' href='http://esoitem.uesp.net/resources/esoitemlink_embed.css?18Dec2014' />");
	$out->addHeadItem("uesp-esoitemlink-js", "<script src='$wgScriptPath/extensions/UespEsoItemLink/uespitemlink.js?18Dec2014'></script>");
	// $out->addHeadItem("uesp-esoitemlink-jquery-js", "<script src='$wgScriptPath/extensions/UespEsoItemLink/jquery-1.10.2.js?18Dec2014'></script>");
	
	return true;
}


function uespEsoItemLinkParserInit(Parser $parser)
{
	$parser->setHook('esoitemlink', 'uespRenderEsoItemLink');
	return true;
}


function uespRenderEsoItemLink($input, array $args, Parser $parser, PPFrame $frame)
{
	$output = "";
	$itemId = "";
	$itemLevel = "";
	$itemQuality = "";
	$showSummary = false;
	
	foreach ($args as $name => $value)
	{
		$name = strtolower($name);
		
		if ($name == "itemid")
			$itemId = $value;
		elseif ($name == "level")
			$itemLevel = $value;
		elseif ($name == "quality")
			$itemQuality = $value;
		elseif ($name == "summary")
			$showSummary = $value;
		
	}
	
	$itemLink = "http://esoitem.uesp.net/itemLink.php?itemid=$itemId";
	if ($itemLevel != "") $itemLink .= "&level=$itemLevel";
	if ($itemQuality != "") $itemLink .= "&quality=$itemQuality";
	if ($showSummary != "") $itemLink .= "&summary";
	
	if ($itemQuality == "")
		$qualityClass = "eso_item_link_q0";
	else
		$qualityClass = "eso_item_link_q" . $itemQuality;
	
	$attributes = "itemid='$itemId' ";
	if ($itemLevel != "") $attributes .= "level='$itemLevel' ";
	if ($itemQuality != "") $attributes .= "quality='$itemQuality' ";
	if ($showSummary != "") $attributes .= "summary='1' ";
	
	$output = "<a href='$itemLink' class='eso_item_link $qualityClass' $attributes>$input</a>";
	
	return $output;
}


