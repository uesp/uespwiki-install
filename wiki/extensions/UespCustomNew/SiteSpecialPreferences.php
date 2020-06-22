<?php
/**
 * Tweak preferences page to alter search options
 */

global $IP;
require_once "$IP/includes/specials/SpecialPreferences.php";


class SitePreferencesForm extends SpecialPreferences {
	protected $mRcNs = array();
	protected $mRcNsTalk = NULL;
	protected $mPosted = false;
	
	function __construct( ) {
		global $wgContLang;
		global $wgRequest;
		
		$request = $wgRequest;
		
		parent::__construct();
		
		$this->mRcNs = array();
		$this->mPosted = $request->wasPosted();

		if ( $this->mPosted ) {
			$namespaces = $wgContLang->getNamespaces();
			$this->mRcNsTalk = $request->getCheck( "wpRcNsTalk" ) ? 1 : 0;
			foreach ( $namespaces as $i => $namespace ) {
				if ($i%2)
					continue;
				if ($i<0)
					continue;
				$rcNs = $request->getCheck( "wpRcNs$i" ) ? 1 : 0;
				$this->mRcNs[$i] = $rcNs;
			}
		}
	}
	
	public function submitReset( $formData ) {
		
		if ( !$this->getUser()->isAllowed( 'editmyoptions' ) ) {
			throw new PermissionsError( 'editmyoptions' );
		}
		
		$user = $this->getUser();
		$this->resetPrefs();
		$user->resetOptions( 'all', $this->getContext() );
		$user->saveSettings();
		
		$url = $this->getPageTitle()->getFullURL( 'success' );
		
		$this->getOutput()->redirect( $url );
		
		return true;
	}
	
	function resetPrefs() {
		global $wgUser, $wgDefaultUserOptions, $wgContLang, $egCustomSiteID;
				
		$siteprefix = strtolower($egCustomSiteID);
		foreach (array('searchtitles', 'searchtalk', 'searchredirects') as $tname) {
			$this->mToggles[$siteprefix.$tname] = $wgUser->getOption( $siteprefix.$tname, $wgDefaultUserOptions[$siteprefix.$tname]);
		}
		
		foreach (array('hideuserspace', 'usecustomns', 'userspaceunpatrolled', 'userspacewatchlist', 'userspaceownpage', 'userspacetalk', 'userspaceownedit', 'userspaceanonedit', 'userspacewarning', 'userspacelogs') as $tname) {
			$this->mToggles[$tname] = $wgUser->getOption( $tname, 0);
		}
		
		$this->mRcNsTalk = $wgUser->getOption( 'rcNsTalk' );
		$namespaces = $wgContLang->getNamespaces();
		$nfound = 0;
		foreach ( $namespaces as $i => $namespace ) {
			if ( $i < NS_MAIN || ($i%2))
				continue;
			$rc = $wgUser->getOption( 'rcNs'.$i );
			$this->mRcNs[$i] = $rc;
			if ($rc)
				$nfound++;
		}
		
		// if no namespaces were set, reset to defaults
		if (!$nfound) {
			foreach( $namespaces as $i => $namespace ) {
				if ($i < NS_MAIN || ($i%2))
					continue;
				if (empty($wgDefaultUserOptions['rcNs'.$i]))
					continue;
				$this->mRcNs[$i] = $wgDefaultUserOptions['rcNs'.$i];
				$wgUser->setOption( "rcNs{$i}", $this->mRcNs[$i]);
			}
			$this->mRcNsTalk = 1;
			$wgUser->setOption( 'rcNsTalk', 1);
		}
	}
	
	function namespacesCheckboxes($type = 'search') {
		global $wgUser, $wgLang, $wgDefaultUserOptions, $egCustomSiteID;
		$siteprefix = strtolower($egCustomSiteID);
		$powersearchtext = "<table class=\"vtop\"><tr><td>\n";
		
		$lines = explode( "\n", wfMsgforContent( $siteprefix.'powersearchtable' ) );
		$ncols = 1;
		foreach ($lines as $line) {
			$line = trim($line);
			if ($line=='') {
				$powersearchtext .= "</td><td style=\"padding-left:2em\">\n";
				$ncols++;
				continue;
			}
			if (substr($line,0,2)=='**')
				$powersearchtext .= '&nbsp; &nbsp; ';
			$line = trim($line, '* ');
			if (($div=strpos($line, '|'))!==false) {
				$nsname = trim(substr($line, 0, $div));
				$nsshowname = trim(substr($line, $div+1));
			}
			else {
				$nsname = $line;
				$nsshowname = NULL;
			}
			$powersearchtext .= $this->namespaceLine( $nsname, $nsshowname, $type );
		}
		$powersearchtext .= "</td></tr>\n";
		
		if ($type=='rc') {
			$ttext = 'Include talk pages for selected namespaces';
			$tname = 'RcNsTalk';
			$powersearchtext .= "<tr><td colspan='{$ncols}' style='text-align:center; border-top:thin solid black'>\n";
			$checked = $this->mRcNsTalk == 1 ? ' checked="checked"' : '';
			
			$powersearchtext .= "<input type='checkbox' value='1' id=\"wp$tname\" name=\"wp$tname\"$checked />" .
				" <span class='toggletext'><label for=\"$tname\">$ttext</label></span>&nbsp; &nbsp;\n";
			$powersearchtext .= "</td></tr>\n";
		}
		else {
			$powersearchtext .= "<tr><td colspan='{$ncols}' style='text-align:center; border-top:thin solid black'>\n";
		// not using default getToggle function, because it doesn't make use of $wgDefaultUserOptions;
			foreach (array($siteprefix.'searchtitles', $siteprefix.'searchtalk', $siteprefix.'searchredirects') as $tname) {
				$this->mUsedToggles[$tname] = true;
				$ttext = $wgLang->getUserToggle( $tname );
				
				$checked = $wgUser->getOption( $tname, $wgDefaultUserOptions[$tname] ) == 1 ? ' checked="checked"' : '';
				$powersearchtext .= "<input type='checkbox' value='1' id=\"$tname\" name=\"wpOp$tname\"$checked />" .
					" <span class='toggletext'><label for=\"$tname\">$ttext</label></span>&nbsp; &nbsp;\n";
			}
			$powersearchtext .= "</td></tr>\n";
		}
		$powersearchtext .= "</table>\n";
			
		return $powersearchtext;
	}
	
	protected function namespaceLine( $nsname, $nsshowname=NULL, $type='search' ) {
		global $wgContLang;
		if ($nsname=='Main' || $nsname=='(Main)')
			$nsnum = NS_MAIN;
		else
			$nsnum = $wgContLang->getNsIndex( $nsname );
		if( is_null( $nsshowname ))
			$nsshowname = $nsname;
		
		if ($type=='rc') {
			$prefix = 'wpRcNs';
			$checked = $this->mRcNs[$nsnum];
		}
		else {
			$prefix = 'wpNs';
			$checked = $this->mSearchNs[$nsnum];
		}
		$checked = ($checked ? "checked='checked'" : '');

		return "<input type='checkbox' value='1' name='{$prefix}{$nsnum}' id='{$prefix}{$nsnum}' {$checked}/><label for='{$prefix}{$nsnum}'>{$nsshowname}</label><br/>\n";
	}
	
	function mainPrefsForm( $status , $message = '' ) {
		global $wgOut, $wgUser;
		// generate some of HTML before calling parent function... saves trouble of setting mUsedToggles
		$myPrefs = '';
		
		// Box around the userspace preferences
		$myPrefs .= Xml::openElement( 'fieldset' ) .
			Xml::element( 'legend', null, 'User and User talk namespace options' );
			
		$myPrefs .= "<table width=100%>\n<tr><td colspan=2>\n";
		$myPrefs .= $this->getToggle('hideuserspace');
		$myPrefs .= "</td></tr>\n";
		foreach (array('userspacetalk', 'userspaceownpage', 'userspaceownedit', 'userspacewatchlist', 'userspaceunpatrolled', 'userspaceanonedit', 'userspacewarning', 'userspacelogs') as $tname) {
			if ($tname=='userspaceunpatrolled' && !$wgUser->useRCPatrol())
				continue;
			$myPrefs .= "<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>\n".$this->getToggle($tname)."</td></tr>\n";
		}
		$myPrefs .= "</table>\n";
		$myPrefs .= Xml::closeElement( 'fieldset' );
		$myPrefs .= Xml::openElement( 'fieldset' ) .
			Xml::element( 'legend', null, 'Custom namespace selections' );
		$myPrefs .= $this->getToggle('usecustomns');
		$myPrefs .= '<hr>';
		$myPrefs .= $this->namespacesCheckboxes('rc');
		$myPrefs .= Xml::closeElement( 'fieldset' );
			
		parent::mainPrefsForm( $status, $message );
		
		// This is really ugly... but until we upgrade to new MW (with revamped preferences), it seems like the best
		// way
		// I'm getting all of the HTML generated so far, finding the places where I want to make changes,
		// inserting my stuff into the HTML, then replacing the stored version of it all....

		$allHTML = $wgOut->getHTML();
		
		$rcLegend = '<legend>'.wfMsg( 'prefs-rc').'</legend>';
		$rcStart = strpos($allHTML, $rcLegend);
		if ($rcStart===false)
			return;
		$rcStart += strlen($rcLegend);
		$rcEnd = strpos($allHTML, '</fieldset>', $rcStart);
		
		$newHTML = substr($allHTML,0,$rcEnd) . $myPrefs . substr($allHTML,$rcEnd);
		$wgOut->clearHTML();
		$wgOut->addHTML($newHTML);
	}
	
	function savePreferences() {
		global $wgUser;
		# Set recent changes namespace options
		// Needs to be done before calling parent
		$wgUser->setOption( "rcNsTalk", $this->mRcNsTalk);
		foreach( $this->mRcNs as $i => $value ) {
			$wgUser->setOption( "rcNs{$i}", $value );
		}
		
		parent::savePreferences();
	} 
}
