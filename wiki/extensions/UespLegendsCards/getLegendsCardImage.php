<?php

	require_once("/home/uesp/secrets/legends.secrets");
	
	
	function ReturnLegendsCardError($msg)
	{
		header("HTTP/1.0 404 Not Found", true, 404);
		header("UESP_ERROR: $msg");
		exit();
	}
	
	$cardName = $_REQUEST['card'];
	if ($cardName == null || $cardName == "") ReturnLegendsCardError();
	
	$db = new mysqli($uespLegendsReadDBHost, $uespLegendsReadUser, $uespLegendsReadPW, $uespLegendsDatabase);
	if ($db->connect_error) ReturnLegendsCardError("Failed to initialize database connection!");
	
	$name = $db->real_escape_string($cardName);
	$query = "SELECT image FROM cards WHERE name='$name';";
	
	$result = $db->query($query);
	if ($result === false) ReturnLegendsCardError("Query error!");
	if ($result->num_rows <= 0) ReturnLegendsCardError("Failed to find card matching '$name'!");
	
	$cardData = $result->fetch_assoc();
	$imageName = $cardData['image'];
	$imageFilename = "/home/uesp/www/w/images/" . $imageName; 
	
	if (!file_exists($imageFilename)) ReturnLegendsCardError("No file matching '$imageFilename' found!");

	$testImage = "/home/uesp/www/w/images/a/ad/LG-card-Wispmother's_Ring.png";
	
	header("Content-Type: image/png");
	header("Content-Length: " . filesize($imageFilename));
	
	readfile($imageFilename);