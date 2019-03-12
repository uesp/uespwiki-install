<?php


class SpecialEsoCps extends SpecialPage
{
	public $cpsViewer = null;


	function __construct()
	{
		global $wgOut;

		parent::__construct( 'EsoCps' );

		$this->cpsViewer = new CEsoViewCP(true);
		$this->cpsViewer->baseUrl = "/wiki/Special:EsoCps";
		$this->cpsViewer->basePath = "/home/uesp/esolog.static/";
		$this->cpsViewer->baseResourceUrl = "//esolog.uesp.net/";
	}


	function execute( $par )
	{
		$this->cpsViewer->wikiContext = $this->getContext();

		$request = $this->getRequest();
		$output = $this->getOutput();

		$this->setHeaders();
		$output->addHTML($this->cpsViewer->GetOutputHtml());
	}


	function getGroupName()
	{
		return 'wiki';
	}

};

