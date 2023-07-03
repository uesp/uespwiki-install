<?php

class CUespGameMapTags {
	
	
	public static function onParserFirstCallInit( Parser $parser )
	{
		$parser->setHook( 'uespgamemap', [ self::class, 'renderGameMap' ] );
		$parser->setHook( 'uespdevgamemap', [ self::class, 'renderDevGameMap' ] );
	}
	
	
	public static function renderGameMap( $input, array $args, Parser $parser, PPFrame $frame )
	{
		$map = $args['map'];
		if ($map == null || $map == '') $map = 'eso';
		
		$class = $args['class'];
		if ($class == null) $class = '';
		
		$title = $args['title'];
		if ($title == null) $title = 'UESP Game Map';
		
		$width = intval($args['width']);
		if ($width < 300) $width = 300;
		
		$height = intval($args['height']);
		if ($height < 200) $width = 200;
		
		$src = "https://gamemap.uesp.net/$map/";
		
		$x = $args['x'];
		$y = $args['y'];
		$zoom = $args['zoom'];
		$centeron = $args['centeron'];
		$world = $args['world'];
		
		$queryParams = [];
		$queryParams['embed'] = 1;
		if ($x !== null) $queryParams[] = "x=" . intval($x);
		if ($y !== null) $queryParams[] = "y=" . intval($y);
		if ($zoom !== null) $queryParams[] = "zoom=" . intval($zoom);
		if ($centeron !== null) $queryParams[] = "centeron=" . htmlspecialchars($centeron);
		if ($world !== null) $queryParams[] = "world=" . htmlspecialchars($world);
		
		$queryParams = http_build_query($queryParams, '', '&');
		if ($queryParams) $queryParams = '?' . $queryParams;
		
		$html = "<iframe class='$class uesp_gamemap_iframe' title='$title' src='$src$queryParams' width='$width' height='$height'></iframe>";
		
		return $html;
	}
	
	
	public static function renderDevGameMap( $input, array $args, Parser $parser, PPFrame $frame )
	{
		$map = $args['map'];
		if ($map == null || $map == '') $map = 'eso';
		
		$class = $args['class'];
		if ($class == null) $class = '';
		
		$title = $args['title'];
		if ($title == null) $title = 'UESP Game Map';
		
		$width = intval($args['width']);
		if ($width < 300) $width = 300;
		
		$height = intval($args['height']);
		if ($height < 200) $width = 200;
		
		$src = "https://devgamemap.uesp.net/$map/";
		
		$x = $args['x'];
		$y = $args['y'];
		$zoom = $args['zoom'];
		$centeron = $args['centeron'];
		$world = $args['world'];
		
		$queryParams = [];
		$queryParams['embed'] = 1;
		if ($x !== null) $queryParams[] = "x=" . intval($x);
		if ($y !== null) $queryParams[] = "y=" . intval($y);
		if ($zoom !== null) $queryParams[] = "zoom=" . intval($zoom);
		if ($centeron !== null) $queryParams[] = "centeron=" . htmlspecialchars($centeron);
		if ($world !== null) $queryParams[] = "world=" . htmlspecialchars($world);
		
		$queryParams = http_build_query($queryParams, '', '&');
		if ($queryParams) $queryParams = '?' . $queryParams;
		
		$html = "<iframe class='$class uesp_gamemap_iframe' title='$title' src='$src$queryParams' width='$width' height='$height'></iframe>";
		
		return $html;
	}
	
};