<?php


class SpecialEsoSkills extends SpecialPage
{
	public $skillsViewer = null;


	function __construct()
	{
		global $wgOut;

		parent::__construct( 'EsoSkills' );

		$this->skillsViewer = new CEsoViewSkills(true);
		$this->skillsViewer->baseUrl = "/wiki/Special:EsoSkills";
		$this->skillsViewer->basePath = "/home/uesp/esolog.static/";
		$this->skillsViewer->baseResourceUrl = "//esolog.uesp.net/";
	}


	function execute( $par )
	{
		$this->skillsViewer->wikiContext = $this->getContext();

		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		$charId = $request->getText( 'id' );
		$raw = $request->getText( 'raw' );

		$output->addHTML($this->skillsViewer->GetOutputHtml());
	}


	function getGroupName()
	{
		return 'wiki';
	}

};

