<?php


require_once("/home/uesp/secrets/esolog.secrets");
require_once("/home/uesp/esolog.static/esoCommon.php");


class CUespEsoData 
{
	
	
	public static function onParserInit ( &$parser )
	{
		global $wgOut;
		$wgOut->addModules( 'ext.UespEsoData.resources' );
		
		$parser->setHook( 'uespesoserverstatus', [ self::class, 'renderEsoServerStatus' ] );
	}
	
	
	public static function renderEsoServerStatus( $input, array $args, Parser $parser, PPFrame $frame ) 
	{
		//global $wgOut;
		//$wgOut->addModules( 'ext.UespEsoData.resources' );
		
		$input = trim($input);
		if ($input != "") $input = "<h1>$input</h1>";
		
		$output = "<div class='uespEsoServerStatus'>$input</div>";
		return $output;
	}
	
};