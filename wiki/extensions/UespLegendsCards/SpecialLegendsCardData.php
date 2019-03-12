<?php


require_once("legendsCardViewer.php");


class SpecialLegendsCardData extends SpecialPage 
{
	
	public $legendsCardViewer = null;
	
	
	function __construct() 
	{
		global $wgOut;
		
		$this->legendsCardViewer = new CUespLegendsCardDataViewer();
				
		parent::__construct( 'LegendsCardData' );
	}
	

	function execute( $par ) 
	{
		$this->legendsCardViewer->wikiContext = $this->getContext();
		
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		$charId = $request->getText( 'id' );
		$raw = $request->getText( 'raw' );

		$output->addHTML($this->legendsCardViewer->getOutput());
	}
	
	
	function getGroupName() 
	{
		return 'wiki';
	}
	
};
