<?php

class SiteSpecialMobilePreferences extends SpecialMobilePreferences {
	
	protected $validTabs = [
		'personal',
		'rendering',
		'editing',
		'rc',
		'watchlist',
		'gadgets',
		'uploads',
		'uesppatreon',	//TODO: What if the UespPatreon extension is not installed?
	];
	
	
	public function execute( $par = '' ) 
	{
		$form = Preferences::getFormObject( $this->getUser(), $this->getContext() );
		$validForRendering = $this->validTabs + $form->getPreferenceSections();
		
		if ( $par && in_array( $par, $validForRendering ) ) {
			$out = $this->getOutput();
			$out->addHtml( "<p><a href=\"/wiki/Special:Preferences\">&lt; All Preferences</a><hr>" );
		}
		
		parent::execute($par);
	}

};