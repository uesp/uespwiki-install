<?php

/*
 * UespEsoSkills -- by Dave Humphrey, dave@uesp.net, April 2016
 * 
 * Adds the <esoskill> tag extension to MediaWiki for displaying an ESO skill popup tooltip
 * as well as adding the skills and champion point browser special pages.
 *
 * TODO:
 *
 */


if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "\$IP/extensions/UespEsoSkills.php/UespEsoSkills.php" );
EOT;
	exit( 1 );
}


require_once("/home/uesp/secrets/esolog.secrets");
require_once('/home/uesp/esolog.static/viewSkills.class.php');
require_once('/home/uesp/esolog.static/viewCps.class.php');


$wgExtensionCredits['specialpage'][] = array(
		'path' => __FILE__,
		'name' => 'EsoSkills',
		'author' => 'Dave Humphrey (dave@uesp.net)',
		'url' => '//www.uesp.net/wiki/UESPWiki:EsoSkills',
		'descriptionmsg' => 'esoskills-desc',
		'version' => '0.2.0',
);


$wgAutoloadClasses['SpecialEsoSkills'] = __DIR__ . '/SpecialEsoSkills.php';
$wgAutoloadClasses['SpecialEsoCps'] = __DIR__ . '/SpecialEsoCps.php';
$wgMessagesDirs['EsoSkills'] = __DIR__ . "/i18n";
$wgExtensionMessagesFiles['EsoSkills'] = __DIR__ . '/EsoSkills.alias.php';
$wgSpecialPages['EsoSkills'] = 'SpecialEsoSkills';
$wgSpecialPages['EsoCps'] = 'SpecialEsoCps';

$wgHooks['ParserFirstCallInit'][] = 'UespEsoSkillsParserInit';
//$wgHooks['BeforePageDisplay'][] = 'uespEsoSkills_beforePageDisplay';

$wgResourceModules['ext.EsoSkills.styles'] = array(
	'position' => 'top',
	'styles' => array( 'esoskills_embed.css', 'esocp_simple_embed.css' ),
	'localBasePath' => '/home/uesp/esolog.static/resources/',
	'remoteBasePath' => '//esolog-static.uesp.net/resources/',
	'targets' => array( 'desktop', 'mobile' ),
);

$wgResourceModules['ext.EsoSkills.scripts'] = array(
	'position' => 'top',
	'scripts' => array( 'jquery-ui.min.js', 'jquery.ui.touch-punch.min.js', 'esoskills.js', 'esocp_simple.js' ),
	'localBasePath' => '/home/uesp/esolog.static/resources/',
	'remoteBasePath' => '//esolog-static.uesp.net/resources/',
	'targets' => array( 'desktop', 'mobile' ),
);

$wgResourceModules['ext.EsoSkills.styles2'] = array(
	'position' => 'top',
	'styles' => array( 'uespesoskills.css' ),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespEsoSkills/",
	'targets' => array( 'desktop', 'mobile' ),
);

$wgResourceModules['ext.EsoSkills.scripts2'] = array(
	'position' => 'top',
	'scripts' => array( 'uespesoskills.js' ),
	'localBasePath' => __DIR__,
	'remoteBasePath' => "$wgScriptPath/extensions/UespEsoSkills/",
	'targets' => array( 'desktop', 'mobile' ),
);


function uespEsoSkills_beforePageDisplay(&$out) {
	global $wgScriptPath;
	
	$out->addHeadItem("uesp-jqueryui1-js", "<script src='//esolog-static.uesp.net/resources/jquery-ui.min.js?version=2May2018'></script>");
	$out->addHeadItem("uesp-jqueryui2-js", "<script src='//esolog-static.uesp.net/resources/jquery.ui.touch-punch.min.js?version=2May2018'></script>");
	
	$out->addHeadItem("uesp-esoskills-css", "<link rel='stylesheet' href='$wgScriptPath/extensions/UespEsoSkills/uespesoskills.css?2May2018' />");
	$out->addHeadItem("uesp-esoskills-js", "<script src='$wgScriptPath/extensions/UespEsoSkills/uespesoskills.js?12Aug2018'></script>");
	
	$out->addHeadItem("uesp-esoskillsbrowser-css", "<link rel='stylesheet' href='//esolog-static.uesp.net/resources/esoskills_embed.css?2May2018' />");
	$out->addHeadItem("uesp-esoskillsbrowser-js", "<script src='//esolog-static.uesp.net/resources/esoskills.js?12Aug2018'></script>");
	
	$out->addHeadItem("uesp-esocpsbrowser-css", "<link rel='stylesheet' href='//esolog-static.uesp.net/resources/esocp_simple_embed.css?2May2018' />");
	$out->addHeadItem("uesp-esocpsbrowser-js", "<script src='//esolog-static.uesp.net/resources/esocp_simple.js?2May2018'></script>");
	
	return true;
}


function UespEsoSkillsParserInit(Parser $parser)
{
	global $wgOut;
	
	$wgOut->addModules( array( 'ext.EsoSkills.scripts', 'ext.EsoSkills.scripts2' ) );
	$wgOut->addModuleStyles( array( 'ext.EsoSkills.styles', 'ext.EsoSkills.styles2' ) );
			
	$parser->setHook('esoskill', 'uespRenderEsoSkillTooltip');
	return true;
}


function ParseEsoLevel($level)
{
	if (is_numeric($level))
	{
		$value = intval($level);
		if ($value <  1) $value = 1;
		if ($value > 66) $value = 66;
		return $value;
	}

	if (preg_match("#^[vV]([0-9]+)#", trim($level), $matches))
	{
		$value = intval($matches[1]) + 50;
		if ($value <  1) $value = 1;
		if ($value > 66) $value = 66;
		return $value;
	}

	return 66;
}


function uespRenderEsoSkillTooltip($input, array $args, Parser $parser, PPFrame $frame)
{
	global $wgScriptPath;
	
	$output = "";
	
	$skillId = "";
	$skillName = "";
	$skillLine = "";
	$skillLevel = "";
	$skillHealth = "";
	$skillMagicka = "";
	$skillStamina = "";
	$skillSpellDamage = "";
	$skillWeaponDamage = "";
	$skillShowAll = "1";
	
	foreach ($args as $name => $value)
	{
		$name = strtolower($name);
		
		switch ($name)
		{
			case "skillid":
				$skillId = $value;
				break;
			case "skillname":
				$skillName = $value;
				break;
			case "skillline":
				$skillLine = $value;
				break;
			case "l":
			case "level":
				$skillLevel = $value;
				break;
			case "h":
			case "hea":
			case "health":
				$skillHealth = $value;
				break;
			case "m":
			case "mag":
			case "magicka":
				$skillMagicka = $value;
				break;
			case "s";
			case "sta":
			case "stamina":
				$skillStamina = $value;
				break;
			case "sd":
			case "spelldmg":
			case "spelldamage":
				$skillSpellDamage = $value;
				break;
			case "wd":
			case "weapdmg":
			case "weapondamage":
				$skillWeaponDamage = $value;
				break;
			case "showall":
				$skillShowAll = trim($value);
				if ($skillShowAll == "") $skillShowAll = "1";
				break;
		}
	
	}
	
	if ($skillLevel != "")
	{
		$realLevel = ParseEsoLevel($skillLevel);
		
		if ($skillHealth  == "") $skillHealth  = round($realLevel * 287.8788 + 712.1212);
		if ($skillMagicka == "") $skillMagicka = round($realLevel * 287.8788 + 712.1212);
		if ($skillStamina == "") $skillStamina = round($realLevel * 287.8788 + 712.1212);
		
		if ($skillSpellDamage  == "") $skillSpellDamage  = round($realLevel * 28.78788 + 71.21212);
		if ($skillWeaponDamage == "") $skillWeaponDamage = round($realLevel * 28.78788 + 71.21212);
	}
	
	$attributes = "";
	$params = "";
	
	if ($skillId != "") 
	{
		$attributes .= "skillid='$skillId' ";
		$params .= "&id=$skillId";
	}
	
	if ($skillName != "") 
	{
		$attributes .= "skillname='$skillName' ";
		$params .= "&skillname=$skillName";
	}
	
	if ($skillLine != "") 
	{
		$attributes .= "skillline='$skillLine' ";
		$params .= "&skillline=$skillLine";
	}
	
	if ($skillLevel != "") 
	{
		$attributes .= "level='$skillLevel' ";
		$params .= "&level=$skillLevel";
	}
	
	if ($skillHealth != "") 
	{
		$attributes .= "health='$skillHealth' ";
		$params .= "&health=$skillHealth";
	}
	
	if ($skillMagicka != "") 
	{ 
		$attributes .= "magicka='$skillMagicka' ";
		$params .= "&magicka=$skillMagicka";
	}
	
	if ($skillStamina != "") 
	{
		$attributes .= "stamina='$skillStamina' ";
		$params .= "&stamina=$skillStamina";
	}
	
	if ($skillSpellDamage != "") 
	{
		$attributes .= "spelldamage='$skillSpellDamage' ";
		$params .= "&spelldamage=$skillSpellDamage";
	}
	
	if ($skillWeaponDamage != "") 
	{
		$attributes .= "weapondamage='$skillWeaponDamage' ";
		$params .= "&weapondamage=$skillWeaponDamage";
	}
	
	if ($skillShowAll != "") 
	{
		$attributes .= "showall='$skillShowAll' ";
		$params .= "&showall=$skillShowAll";
	}
	
	$url = "/wiki/Special:EsoSkills?$params";
	
	$output = "<a class='esoSkillTooltipLink' href='$url' $attributes>$input</a>";
	
	return $output;
}


