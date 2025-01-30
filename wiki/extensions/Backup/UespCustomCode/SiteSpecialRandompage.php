<?php

global $IP;
require_once "$IP/includes/specials/SpecialRandompage.php";


class SiteSpecialRandomPage extends RandomPage
{

	public function __construct($name = 'Randompage')
	{
		parent::__construct($name);
	}


	public function execute($par)
	{
		global $wgOut, $wgParser;

		//$wgParser->disableCache();
		$wgOut->enableClientCache(false);

		parent::execute($par);
	}
};
