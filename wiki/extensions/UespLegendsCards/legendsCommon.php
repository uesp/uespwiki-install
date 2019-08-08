<?php


$UESP_LEGENDS_WIKIIMAGEPATH = "/home/uesp/www/w/images/";
$UESP_LEGENDS_OUTPUT_PATH = "./cardimages/";
$UESP_LEGENDS_CARD_WIDTH = 200;
$UESP_LEGENDS_CARD_HEIGHT = 324;

	/* Loaded from database */
$UESP_LEGENDS_DISAMBIGUATION = array();
$uespLegendsDisambiguationLoaded = false;


function CreateLegendsTables($db)
{
	$query = "CREATE TABLE IF NOT EXISTS cards (
						name TINYTEXT NOT NULL,
						type TINYTEXT NOT NULL,
						subtype TINYTEXT NOT NULL,
						text TEXT NOT NULL,
						image TINYTEXT NOT NULL,
						magicka INTEGER NOT NULL DEFAULT 0,
						power INTEGER NOT NULL DEFAULT 0,
						health INTEGER NOT NULL DEFAULT 0,
						rarity TINYTEXT NOT NULL,
						attribute TINYTEXT NOT NULL,
						attribute2 TINYTEXT NOT NULL,
						attribute3 TINYTEXT NOT NULL,
						`set` TINYTEXT NOT NULL,
						`class` TINYTEXT NOT NULL,
						obtainable TINYINT(1) NOT NULL DEFAULT 0,
						`unique` TINYINT(1) NOT NULL DEFAULT 0,
						training1 TINYTEXT NOT NULL,
						trainingLevel1 TINYINT NOT NULL,
						training2 TINYTEXT NOT NULL,
						trainingLevel2 TINYINT NOT NULL,
						uses TINYTEXT NOT NULL,
						PRIMARY KEY (name(32)),
						INDEX index_type (type(3), subtype(3)),
						INDEX index_subtype (subtype(3)),
						INDEX index_attribute (attribute(3)),
						INDEX index_attribute2 (attribute2(3)),
						INDEX index_set (`set`(16)),
						INDEX index_class (`class`(3)),
						INDEX index_rarity (rarity(3)),
						FULLTEXT (name, text)
					);";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to create the cards table!";
	
	$query = "CREATE TABLE IF NOT EXISTS deletedCards (
						name TINYTEXT NOT NULL,
						type TINYTEXT NOT NULL,
						subtype TINYTEXT NOT NULL,
						text TEXT NOT NULL,
						image TINYTEXT NOT NULL,
						magicka INTEGER NOT NULL DEFAULT 0,
						power INTEGER NOT NULL DEFAULT 0,
						health INTEGER NOT NULL DEFAULT 0,
						rarity TINYTEXT NOT NULL,
						attribute TINYTEXT NOT NULL,
						attribute2 TINYTEXT NOT NULL,
						attribute3 TINYTEXT NOT NULL,
						`set` TINYTEXT NOT NULL,
						`class` TINYTEXT NOT NULL,
						obtainable TINYINT(1) NOT NULL DEFAULT 0,
						`unique` TINYINT(1) NOT NULL DEFAULT 0,
						training1 TINYTEXT NOT NULL,
						trainingLevel1 TINYINT NOT NULL,
						training2 TINYTEXT NOT NULL,
						trainingLevel2 TINYINT NOT NULL,
						uses TINYTEXT NOT NULL,
						deleteTimestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
					);";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to create the deletedCards table!";
	
	$query = "CREATE TABLE IF NOT EXISTS sets (
						name TINYTEXT NOT NULL
					);";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to create the sets table!";
	
	
	$query = "CREATE TABLE IF NOT EXISTS disambiguation (
						name TINYTEXT NOT NULL,
						linkSuffix TINYTEXT NOT NULL
					);";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to create the disambiguation table!";
	
	$query = "CREATE TABLE IF NOT EXISTS logInfo (
						id TINYTEXT NOT NULL,
						value TINYTEXT NOT NULL,
						PRIMARY KEY (id(16))
					);";
	
	$result = $db->query($query);
	if ($result === false) return "Failed to create the logInfo table!";
	
	return true;
}


function UpdateLegendsPageViews($id, $db = null)
{
	global $uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase;

	$deleteDb = false;

	if ($db == null)
	{
		$deleteDb = true;
		$db = new mysqli($uespLegendsWriteDBHost, $uespLegendsWriteUser, $uespLegendsWritePW, $uespLegendsDatabase);
		if ($db->connect_error) return false;
	}

	$query = "UPDATE logInfo SET value=value+1 WHERE id='$id';";
	$result = $db->query($query);

	if ($deleteDb) $db->close();

	return $result !== false;
}


/* This should be the same hash used in MediaWiki for image directories */
function GetLegendsImagePathHash($name)
{
	$name = preg_replace("# #", "_", $name);
	$name = preg_replace("#&\#39;#", "'", $name);
	
	$levels = 2;	/* 2 for hashed upload directory, 0 for non-hashed */

	$hash = md5( $name );
	$path = '';
	for ( $i = 1; $i <= $levels; $i++ ) {
		$path .= substr( $hash, 0, $i ) . '/';
	}

	return $path;
}


function CreateLegendsPopupImage($cardName, $imageBaseName, $outputPath = null, $print = false)
{
	global $UESP_LEGENDS_WIKIIMAGEPATH, $UESP_LEGENDS_OUTPUT_PATH, $UESP_LEGENDS_CARD_WIDTH, $UESP_LEGENDS_CARD_HEIGHT;
	
	if ($outputPath === null) $outputPath = $UESP_LEGENDS_OUTPUT_PATH;
	
	$width = $UESP_LEGENDS_CARD_WIDTH;
	$height = $UESP_LEGENDS_CARD_HEIGHT;
	
	$imageFilename = str_replace('//', '/', $UESP_LEGENDS_WIKIIMAGEPATH . $imageBaseName);
	$outputFilename = $outputPath . $cardName . ".png";
		
	if ($imageBaseName == "")
	{
		if ($print) print("\t$cardName: Has no image file set!\n");
		return false;
	}
		
	if (!file_exists($imageFilename))
	{
		if ($print) print("\t$cardName: Image file '$imageFilename' not found!\n");
		return false;;
	}
	
	$image = imagecreatefrompng($imageFilename);
	
	$srcWidth = imagesx($image);
    $srcHeight = imagesy($image);
    $srcRatio = $srcWidth / $srcHeight;
	
	$calcWidth = intval($srcRatio * $height);
	$width = $calcWidth;
	print("\t\tUsing resize of $width x $height ($srcWidth x $srcHeight, $srcRatio)\n");
	
	$resizeImage = imagecreatetruecolor($width, $height);
	
	if ($image == null || $resizeImage == null)
	{
		if ($print) print("\t$cardName: Failed to create PNG image from file '$imageFilename'!\n");
		return false;
	}
	
	imagealphablending($resizeImage, false);
	imagesavealpha($resizeImage, true);
	$transparent = imagecolorallocatealpha($resizeImage, 255, 255, 255, 127);
	imagefilledrectangle($resizeImage, 0, 0, $width, $height, $transparent);
	
	imagecopyresampled($resizeImage, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
	
	$fileResult = imagepng($resizeImage, $outputFilename);
	
	if (!$fileResult)
	{
		if ($print) print("\t$cardName: Failed to save resized PNG image to file '$outputFilename'!\n");
		return false;;
	}
	
	if ($print) print("\t$cardName: Saved image to '$outputFilename'!\n");
	return true;
}


function LoadLegendsDisambiguationPages($db = null)
{
	global $UESP_LEGENDS_DISAMBIGUATION;
	global $uespLegendsDisambiguationLoaded;
	global $uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase;
	
	if ($uespLegendsDisambiguationLoaded) return $UESP_LEGENDS_DISAMBIGUATION;
	
	$deleteDb = false;
	$uespLegendsDisambiguationLoaded = true;
	
	if ($db == null)
	{
		$deleteDb = true;
		$db = new mysqli($uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase);
		if ($db->connect_error) return false;
	}
	
	$query = "SELECT * FROM disambiguation;";
	$result = $db->query($query);
	
	if ($result === false)
	{
		if ($deleteDb) $db->close();
		return $UESP_LEGENDS_DISAMBIGUATION;
	}
	
	while (($row = $result->fetch_assoc()))
	{
		$UESP_LEGENDS_DISAMBIGUATION[$row['name']] = $row['linkSuffix'];
	}
	
	if ($deleteDb) $db->close();
	
	ksort($UESP_LEGENDS_DISAMBIGUATION);
	return $UESP_LEGENDS_DISAMBIGUATION;
}