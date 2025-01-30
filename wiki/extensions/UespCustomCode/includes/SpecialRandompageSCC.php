<?php

global $IP;
require_once "$IP/includes/specials/SpecialRandompage.php";


class SpecialRandomPageSCC extends RandomPage
{
	public function __construct($name = 'Randompage')
	{
		parent::__construct($name);
	}


	public function execute($par)
	{
		global $wgOut;

		$wgOut->enableClientCache(false);
		parent::execute($par);
	}
};
