<?php
/**
* These customizations only apply to standard recentchanges page.  None of them will
* propagate to SpecialRecentchangeslinked, and I don't think they need to
*
* As far as I can tell, pageRenderingHash is never called when executing recentchanges
* (code doesn't bomb when pageRenderingHash hook invalid)
*/

global $IP;
require_once "$IP/includes/specials/SpecialRecentchanges.php";

class SiteSpecialRecentChanges extends SpecialRecentChanges {
	protected $customnslink;
	
	public function getDefaultOptions() {
		global $wgUser;
		$opts = parent::getDefaultOptions();
		
		if ($wgUser->getId()) {
			$opts->add( 'hideuserspace', (bool)$wgUser->getOption( 'hideuserspace' ) );
			$opts->add( 'usecustomns', (bool)$wgUser->getOption( 'usecustomns' ) );
		}
		else {
			$opts->add( 'hideuserspace', false);
			$opts->add( 'usecustomns', false);
		}
		return $opts;
	}
	
	public function setup( $parameters ) {
		$opts = parent::setup( $parameters );
			
		if ($opts['namespace']!=='')
			$opts->setValue('usecustomns', false);
		if (($opts['namespace']==NS_USER || $opts['namespace']==(NS_USER+1)) && !$opts['invert'])
			$opts->setValue('hideuserspace', false);
		
		return $opts;
	}
	
	public function parseParameters( $par, FormOptions $opts ) {
		parent::parseParameters( $par, $opts );
		$bits = preg_split( '/\s*,\s*/', trim( $par ) );
		foreach( $bits as $bit ) {
			if( 'hideuserspace' === $bit ) $opts->setValue('hideuserspace', true);
			if( 'usecustomns' === $bit ) $opts->setValue('usecustomns', true);
		}
	}
	
	public function buildMainQueryConds( FormOptions $opts ) {
		global $wgUser, $wgContLang;
		$dbr = wfGetDB( DB_SLAVE );
		// make sure that parent doesn't set any namespace conditions if I'm going to add custom ones
		if ($opts['usecustomns'])
			$opts->setValue('namespace', '');
		$conds = parent::buildMainQueryConds( $opts );
		
		if (!$wgUser->getId())
			return $conds;
				
		// standard query only makes this condition active if user is a patroller; override and let anyone use it
		if( $opts['hidepatrolled'] )
			$conds['rc_patrolled'] = 0;
		
		if ($opts['usecustomns']) {
			$customnslist = array();
			$rcNsTalk = $wgUser->getOption( 'rcNsTalk' );
			foreach( $wgContLang->getNamespaces() as $ns => $name ) {
				if ($ns < NS_MAIN)
					continue;
				if (($ns%2))
					continue;
				// not handling deselectallns / selectallns, or rcTalkPages options here
				if ($wgUser->getOption( 'rcNs' . $ns)) {
					$customnslist[] = $ns;
					if ($rcNsTalk)
						$customnslist[] = $ns+1;
				}
			}
			if (count($customnslist)) {
				$conds[] = $dbr->makeList(array('rc_namespace' => $customnslist, 'wl_user IS NOT NULL'), LIST_OR);
			}
		}
		if ($opts['hideuserspace']) {
			$subconds = array();
			if ($wgUser->getOption( 'userspacetalk'))
				$subconds[] = 'rc_namespace != '.NS_USER;
			else
				$subconds[] = 'rc_namespace != '.NS_USER.' AND rc_namespace != '.(NS_USER+1);
			// options are setup so that default values are always zero
			if ($wgUser->getOption( 'userspaceunpatrolled') && $wgUser->useRCPatrol())
				$subconds['rc_patrolled'] = 0;
			if ($wgUser->getOption( 'userspacewatchlist'))
				$subconds[] = 'wl_user IS NOT NULL';
			if ($wgUser->getOption( 'userspaceownpage')) {
				$subconds['rc_title'] = $wgUser->getTitleKey();
				$subconds[] = 'rc_title like \''.$wgUser->getTitleKey().'/%\'';
			}
			if ($wgUser->getOption( 'userspaceownedit')) {
				$subconds[] = 'rc_user = '.$wgUser->getId();
			}
			if ($wgUser->getOption( 'userspaceanonedit')) {
				$subconds[] = 'rc_user = 0';
			}
			if ($wgUser->getOption( 'userspacewarning')) {
				$subconds[] = 'rc_comment like \'%Warn%\'';
				$subconds[] = 'rc_comment like \'%Block%\'';
				$subconds[] = 'rc_comment like \'%{|%\'';
				$subconds[] = 'rc_log_type = \'block\'';
			}
			if ($wgUser->getOption( 'userspacelogs')) {
// NB user-related log items (new accounts, blocked accounts) are considered
// part of user namespace
// rc_log_type=='block'/'newusers'/'renameuser'/'rights'
// They're also automatically shown with that user's page
				$subconds[] = 'rc_log_type IS NOT NULL';
			}
			
			$conds[] = $dbr->makeList($subconds, LIST_OR);
		}
		
		return $conds;
	}
	
	// NB when running actual query, I want to make use of the new_name_timestamp index if either
	// of my conditions are in effect (filtering by namespace should be much faster with index)
	// easiest way to "force" that is by setting $opts['namespace'] to a bogus value
	// pre-1.29: public function doMainQuery( $conds, $opts ) {
	public function doMainQuery( $tables, $fields, $conds, $query_options, $join_conds, FormOptions $opts ) {
		global $wgUser;
		if (($opts['usecustomns'] || $opts['hideuserspace']) && empty($opts['namespace'])) {
			$opts->setValue('invert', false);
			$opts->setValue('namespace', -1);
			
		}
		// pre-1.29: $res = parent::doMainQuery( $conds, $opts );
		$res = parent::doMainQuery( $tables, $fields, $conds, $query_options, $join_conds, $opts );
		$opts->setValue('namespace', NULL);
		return $res;
	}
			
	// couldn't see any easy way around copying the whole function just to add a couple tweaked lines...
	function optionsPanel( $defaults, $nondefaults, $numRows ) {
		global $wgLang, $wgUser, $wgRCLinkLimits, $wgRCLinkDaysi, $wgOut;
		
		$options = $nondefaults + $defaults;
		
		$note = '';
		if( $options['from'] ) {
			$note .= wfMessage( 'rcnotefrom',
			                   $wgLang->formatNum( $options['limit'] ),
			                   $wgLang->timeanddate( $options['from'], true ) )->parse() . '<br />';
		}
		if( !wfMessage( 'rclegend' )->inContentLanguage()->isBlank() ) {
			$note .= wfMessage( 'rclegend' )->parse(). '<br />';
		}
		
		# Sort data for display and make sure it's unique after we've added user data.
		$wgRCLinkLimits[] = $options['limit'];
		$wgRCLinkDays[] = $options['days'];
		sort( $wgRCLinkLimits );
		sort( $wgRCLinkDays );
		$wgRCLinkLimits = array_unique( $wgRCLinkLimits );
		$wgRCLinkDays = array_unique( $wgRCLinkDays );
		
		// limit links
		foreach( $wgRCLinkLimits as $value ) {
			$cl[] = $this->makeOptionsLink( $wgLang->formatNum( $value ),
			                                array( 'limit' => $value ), $nondefaults, $value == $options['limit'] ) ;
		}
		$cl = implode( ' | ', $cl );
		
		// day links, reset 'from' to none
		foreach( $wgRCLinkDays as $value ) {
			$dl[] = $this->makeOptionsLink( $wgLang->formatNum( $value ),
			                                array( 'days' => $value, 'from' => '' ), $nondefaults, $value == $options['days'] ) ;
		}
		$dl = implode( ' | ', $dl );
		
		// link to mypreferences for setup
		$sk = $wgOut->getSkin();
		$preftitle = Title::newFromText('Special:Preferences#mw-prefsection-rc');
		$setuplink = Linker::link( $preftitle, 'setup');
		
		// show/hide links
		$showhide = array( wfMessage( 'show' )->text(), wfMessage( 'hide' )->text() );
		$minorLink = $this->makeOptionsLink( $showhide[1-$options['hideminor']],
		                                     array( 'hideminor' => 1-$options['hideminor'] ), $nondefaults);
		$botLink = $this->makeOptionsLink( $showhide[1-$options['hidebots']],
		                                   array( 'hidebots' => 1-$options['hidebots'] ), $nondefaults);
		$anonsLink = $this->makeOptionsLink( $showhide[ 1 - $options['hideanons'] ],
		                                     array( 'hideanons' => 1 - $options['hideanons'] ), $nondefaults );
		$liuLink   = $this->makeOptionsLink( $showhide[1-$options['hideliu']],
		                                     array( 'hideliu' => 1-$options['hideliu'] ), $nondefaults);
		$patrLink  = $this->makeOptionsLink( $showhide[1-$options['hidepatrolled']],
		                                     array( 'hidepatrolled' => 1-$options['hidepatrolled'] ), $nondefaults);
		$myselfLink = $this->makeOptionsLink( $showhide[1-$options['hidemyself']],
		                                      array( 'hidemyself' => 1-$options['hidemyself'] ), $nondefaults);
		$userspaceLink = $this->makeOptionsLink( $showhide[1-$options['hideuserspace']],
		                                      array( 'hideuserspace' => 1-$options['hideuserspace'] ), $nondefaults);
		if ($options['usecustomns'])
			$text = 'Turn off';
		else
			$text = 'Turn on';
		$this->customnslink = $this->makeOptionsLink( $text,
		                                              array( 'usecustomns' => 1-$options['usecustomns'], 'namespace' => '' ), $nondefaults);
		$this->customnslink .= ' custom list ('.$setuplink.')';
		
		$links[] = wfMessage( 'rcshowhideminor' )->rawParams( $minorLink )->escaped();
		$links[] = wfMessage( 'rcshowhidebots' )->rawParams( $botLink )->escaped();
		$links[] = wfMessage( 'rcshowhideanons' )->rawParams( $anonsLink )->escaped();
		$links[] = wfMessage( 'rcshowhideliu' )->rawParams( $liuLink )->escaped();
		// turn "Hide patrolled edits" on for registered users, unlike mediawiki's option
		//		if( $wgUser->useRCPatrol() )
		if ($wgUser->getId())
			$links[] = wfMessage( 'rcshowhidepatr' )->rawParams( $patrLink )->escaped();
		$links[] = wfMessage( 'rcshowhidemine' )->rawParams( $myselfLink )->escaped();
		if ($wgUser->getId()) {
			if ($options['hideuserspace'])
				$links[] = $userspaceLink.' all userspace edits ('.$setuplink.')';
			else
				$links[] = $userspaceLink.' most userspace edits ('.$setuplink.')';
		}
		$hl = implode( ' | ', $links );
		
		// show from this onward link
		$timestamp = wfTimeStampNow();
		$now = $wgLang->userTimeAndDate( $timestamp, $wgUser );
		$timenow = $wgLang->userTime( $timestamp, $wgUser );
		$datenow = $wgLang->userdate( $timestamp, $wgUser );
		
		$rclinks = wfMessage( 'rclinks' )->rawParams( $cl, $dl )->parse();
		$rclistfrom = wfMessage( 'rclistfrom' )->rawParams( $now, $timenow, $datenow )->parse();
		return "{$note}$rclinks<br>$hl<br>$rclistfrom";
	}
	
	protected function namespaceFilterForm( FormOptions $opts ) {
		global $wgUser;
		$nsSelect = Html::namespaceSelector( array( $opts['namespace'] ), array() );
		$nsLabel = Xml::label( wfMessage('namespace')->text(), 'namespace' );
		$invert = Xml::checkLabel( wfMessage('invert')->text(), 'invert', 'nsinvert', $opts['invert'] );
		
		if ($wgUser->getId()) {
			// turn custom list into link to preferences page
			// (setup) to userspace edits that's also a link
			$nsCustom = $this->customnslink;
			$nsCustom .= ' &nbsp;&nbsp; OR &nbsp;&nbsp; Select one namespace: ';
		}
		else
			$nsCustom = '';
		
		return array( $nsLabel, "$nsCustom$nsSelect $invert" );
	}
	
}
