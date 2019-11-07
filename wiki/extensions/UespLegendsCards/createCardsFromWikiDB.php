<?php

$ONLY_UPDATE_CARD_TEXT = true;

if (php_sapi_name() != "cli") die("Can only be run from command line!");

print("\tImporting new Legends card data from wiki pages...\n");

	/* Database users, passwords and other secrets */
require_once("/home/uesp/secrets/legends.secrets");
require_once("/home/uesp/secrets/wiki.secrets");

require_once("legendsCommon.php");

$wikiDb = new mysqli($UESP_SERVER_DB2, $uespWikiUser, $uespWikiPW, $uespWikiDB);
if ($wikiDb->connect_error) exit("Could not connect to wiki database!");

$db = new mysqli($uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase);
if ($db->connect_error) exit("Could not connect to legends database!");

$query = "SELECT * FROM categorylinks WHERE cl_to='Legends-Cards' AND cl_type='page';";
$cateResult = $wikiDb->query($query);
if ($cateResult === false) exit("Failed to load Legends category data from wiki database!");

$pages = array();

while (($category = $cateResult->fetch_assoc()))
{
	$pageId = $category['cl_from'];
	$pages[$pageId] = array();
	$pages[$pageId]['page_id'] = $pageId;
	$pages[$pageId]['page_name'] = $category['cl_sortkey'];
	$pages[$pageId]['category'] = $category;
}

$pageCount = count($pages);
print("\tLoaded $pageCount wiki pages from the Legends namespace!\n");

$setCount = 0;

foreach ($pages as $pageId => &$page)
{
	$query = "SELECT * FROM mt_save_set WHERE mt_set_page_id='$pageId' LIMIT 1;";
	
	$pageResult = $wikiDb->query($query);
	if ($pageResult === false) continue;
	
	if ($pageResult->num_rows <= 0)
	{
		if ($page['page_name'] == "CREATED CARDS") continue;
		if ($page['page_name'] == "UNOBTAINABLE CARDS") continue;
		
		print("\t\tWarning: {$page['page_name']}($pageId) has no template set data!\n");
		continue;
	}
	
	$set = $pageResult->fetch_assoc();
	$setCount += 1;
	
	$page['set'] = $set;
}

print("\tLoaded $setCount template set data from the Legends pages!\n");
$setVarCount = 0;

foreach ($pages as $pageIndex => &$page)
{
	$page['setVars'] = array();
	if ($page['set'] == null) continue;
	
	$setId = $page['set']['mt_set_id'];
	$query = "SELECT * FROM mt_save_data WHERE mt_save_id='$setId';";
	
	$result = $wikiDb->query($query);
	if ($result === false) continue;
	if ($result->num_rows <= 0) continue;
	
	$setVars = array();
	
	while (($setVar = $result->fetch_assoc()))
	{
		$varName = $setVar['mt_save_varname'];
		$varValue = $setVar['mt_save_value'];
		$setVars[$varName] = $varValue;
	}
	
	$setVarCount += count($setVars);
	
	$page['setVars'] = $setVars;
}

print("\tLoaded $setVarCount template set variables from the Legends pages!\n");
$cardCount = 0;
$cards = array();

foreach ($pages as $pageIndex => &$page)
{
	$name = $page['setVars']['name'];
	
	if ($name === null) 
	{
		if ($name == "CREATED CARDS") continue;
		if ($name == "UNOBTAINABLE CARDS") continue;
		
		print("\t\tWarning: {$page['page_name']}($pageId) has no name template variable!\n");
		continue;
	}
	
	$cardCount++;
	
	$card = array();
	
	$card['name'] = $name;
	$card['type'] = $page['setVars']['type'];
	$card['subtype'] = $page['setVars']['subtype'];
	$card['image'] = $page['setVars']['image'];
	$card['cost'] = $page['setVars']['cost'];
	$card['attribute'] = $page['setVars']['attribute'];
	$card['class'] = $page['setVars']['class'];
	$card['availability'] = $page['setVars']['availability'];	// Set
	$card['power'] = $page['setVars']['power'];			// Magicka
	$card['health'] = $page['setVars']['health'];
	$card['uses'] = $page['setVars']['uses'];
	$card['rarity'] = $page['setVars']['rarity'];
	$card['ability'] = $page['setVars']['ability'];		// Text
	$card['assemble'] = $page['setVars']['assemble'];
	$card['breakthrough'] = $page['setVars']['breakthrough'];
	$card['beastform'] = $page['setVars']['beastform'];
	$card['charge'] = $page['setVars']['charge'];
	$card['drain'] = $page['setVars']['drain'];
	$card['guard'] = $page['setVars']['guard'];
	$card['lastgasp'] = $page['setVars']['lastgasp'];
	$card['lethal'] = $page['setVars']['lethal'];
	$card['pilfer'] = $page['setVars']['pilfer'];
	$card['prophecy'] = $page['setVars']['prophecy'];
	$card['regenerate'] = $page['setVars']['regenerate'];
	$card['slay'] = $page['setVars']['slay'];
	$card['summon'] = $page['setVars']['summon'];
	$card['treasurehunt'] = $page['setVars']['treasurehunt'];
	$card['ward'] = $page['setVars']['ward'];
	$card['obtainable'] = $page['setVars']['obtainable'];
	$card['shout'] = $page['setVars']['shout'];
	$card['unique'] = $page['setVars']['isUnique'];
	
	$cards[$name] = $card; 
}

print("\tFound $cardCount possible cards from the Legends pages!\n");

$result = CreateLegendsTables($db);
if ($result !== true) exit($result . "\n");

$insertCount = 0;
$updateCount = 0;



foreach ($cards as $name => $card)
{
	$text = $card['ability'];			if ($text == null) $text = "";
	$type = $card['type'];				if ($type == null) $type = "";
	$subtype = $card['subtype'];		if ($subtype == null) $subtype = "";
	$image = $card['image'];			if ($image == null) $image = "";
	$rarity = $card['rarity'];			if ($rarity == null) $rarity = "";
	$attribute = $card['attribute'];	if ($attribute == null) $attribute = "";
	$class = $card['class'];			if ($class == null) $class = "";
	$set = $card['availability'];		if ($set == null) $set = "";
	$uses = $card['uses'];				if ($uses == null) $uses = 0;
	$obtainable = $card['obtainable'];	if ($obtainable == null) $obtainable = "No";
	$unique = $card['unique'];		    if ($unique == null) $unique = 0;
		
	$magicka = intval($card['cost']);
	$power = intval($card['power']);
	$health = intval($card['health']);
	
	//print ("$name: $text\n");
	$text = preg_replace("#'''(.*?)'''#", "$1", $text);
	$text = preg_replace("#\[\[(?:Legends|LG):.*?\|(.*?)\]\]#", "$1", $text);
	$text = preg_replace("#<br>|<br/>|</br>#", "\n", $text);
	$text = preg_replace("#<span .*?</span>#", "", $text);
	$text = str_replace('<div style="clear:left"></div>', "\n", $text);
	//print ("$name: $text\n");
	
	$image = preg_replace("# #", "_", $image);
	$image = preg_replace("#&\#39;#", "'", $image);
	$hash = GetLegendsImagePathHash($image);
	//$image = preg_replace("#'#", "%27", $image);
	
	if ($image != "") $image = "/" . $hash . $image;
	//print($image."\n");
	
	if ($obtainable == "Yes")
		$obtainable = 1;
	else
		$obtainable = 0;
		
	$filename = "/home/uesp/www/w/images" . $image;
	if (!file_exists($filename)) print("\t\tWarning: $name image file '$image' doesn't exist!\n");
	
	$text = $db->real_escape_string($text);
	$name = $db->real_escape_string($name);
	$type = $db->real_escape_string($type);
	$subtype = $db->real_escape_string($subtype);
	$image = $db->real_escape_string($image);
	$rarity = $db->real_escape_string($rarity);
	$attribute = $db->real_escape_string($attribute);
	$set = $db->real_escape_string($set);
	$class = $db->real_escape_string($class);
	$uses = $db->real_escape_string($uses);
	
	$query = "SELECT * FROM cards WHERE name='$name';";
	$result = $db->query($query);
	
	if ($result === false) 
	{
		print("\tError looking for existing card $name!");
		continue; 
	}
	
	$doInsert = $result->num_rows == 0;
	
	$writeQuery = "";
	$setQuery  = "text='$text', type='$type', subtype='$subtype', image='$image', rarity='$rarity', attribute='$attribute', ";
	$setQuery .= "attribute2='$attribute2', `class`='$class', `set`='$set', magicka='$magicka', power='$power', health='$health', ";
	$setQuery .= "obtainable='$obtainable', uses='$uses', `unique`='$unqiue'";
		
	if ($doInsert)
	{
		$writeQuery  = "INSERT INTO cards SET ";
		$writeQuery .= " name='$name', $setQuery";
		$writeQuery .= ";";
	}
	else
	{
		if ($ONLY_UPDATE_CARD_TEXT)
		{
			$writeQuery  = "UPDATE cards SET ";
			$writeQuery .= " text='$text' ";
			$writeQuery .= " WHERE name='$name';";
		}
		else
		{
			$writeQuery  = "UPDATE cards SET ";
			$writeQuery .= $setQuery;
			$writeQuery .= " WHERE name='$name';";
		}
	}	
	
	$result = $db->query($writeQuery);
	
	if ($result === false)
	{
		print("\tError inserting/updating card $name!");
		continue;
	}	
	
	if ($doInsert)
		$insertCount++;
	else
		$updateCount++;
	
}

print("\tInserted $insertCount new cards and updated $updateCount cards!\n");