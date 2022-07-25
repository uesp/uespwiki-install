<?php


if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is a MediaWiki extension and must be run from within MediaWiki.' );
}


class SpecialUespGameMap extends SpecialPage {
	
	public $map = "";
	
	private $MAP_DATA = array(
			"eso" => array(
					"name" => "Elder Scrolls Online",
					"url" => "https://esomap.uesp.net/esomap.html", 
			),
			"dragonborn" => array(
					"name" => "Dragonborn",
					"url" => "https://dbmap.uesp.net/dbmap.html", 
			),
			"skyrim" => array(
					"name" => "Skyrim",
					"url" => "https://srmap.uesp.net/srmap.html", 
			),
			"oblivion" => array(
					"name" => "Oblivion",
					"url" => "https://obmap.uesp.net/obmap.html", 
			),
			"shiveringisles" => array(
					"name" => "Shivering Isles",
					"url" => "https://simap.uesp.net/simap.html", 
			),
			"morrowind" => array(
					"name" => "Morrowind",
					"url" => "https://mwmap.uesp.net/mwmap.html", 
			),
	);
	
	
	public function __construct() {
		global $wgOut;
		
		parent::__construct('UespGameMap');
		
		$wgOut->addModules( 'ext.UespGameMap.modules' );
	}
	
	
	public function escapeHtml($html) {
		return htmlspecialchars ($html);
	}
	
	
	public function parseRequest() {
		$req = $this->getRequest();
		
		$map = $req->getVal('map');
		if ($map != null) $this->map = strtolower($map);
	}
	
	
	public function isValidMap()
	{
		$mapData = $this->MAP_DATA[$this->map];
		if ($mapData == null) return false;
		
		return true;
	}
	
	public function listMaps()
	{
		$output = $this->getOutput();
		
		$output->addWikiText("The following are valid maps:");
		
		foreach ($this->MAP_DATA as $mapId => $map)
		{
			$name = $map['name'];
			$output->addWikiText(":* [[Special:UespGameMap/$mapId|$name]]\n");
		}
	}
	
	
	public function execute( $parameter ){
		
		$output = $this->getOutput();
		
		$this->map = strtolower($parameter);
		
		$this->setHeaders();
		$this->parseRequest();
		
		if (!$this->isValidMap())
		{
			$output->addWikiText("No map name specified!");
			$this->listMaps();
			return;
		}
		
		$mapData = $this->MAP_DATA[$this->map];
		$src = $mapData['url'];
		$id = "uesp_gamemap_iframe";
		$class = "uesp_gamemap_{$this->map}_iframe";
		$title = "{$mapData['name']} Game Map";
		
		$output->addHTML("<iframe id='$id' class='$class uesp_gamemap_iframe' title='$title' src='$src'></iframe>");
	}
	
};
