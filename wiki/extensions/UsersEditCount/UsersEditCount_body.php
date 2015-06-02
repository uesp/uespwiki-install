<?php
global $IP;

require_once "$IP/includes/QueryPage.php";

/** UsersEditCountPage extends QueryPage.
 * This does the real work of generating the page contents
 * @package MediaWiki
 * @subpackage SpecialPage
 */
class UsersEditCountPage extends QueryPage {
	var $RequestDate = NULL;
	var $RequestDateTitle = '';
	var $OutputCSV = false;
	var $OutputEmails = false;
	var $Group = NULL;
	var $ExcludeGroup = false;

	function __construct( $name = 'UsersEditCount' ) {
		global $wgUser;
		
		parent::__construct( $name );
		
		$req = $this->getRequest();
		$inputdate = $req->getVal('date');
		 
		switch (strtolower($inputdate)) {
			case 'day': 	$this->RequestDate = 'day'; $this->RequestDateTitle = 'day'; break;
			case 'week':	$this->RequestDate = 'week'; $this->RequestDateTitle = 'week'; break;
			case 'month':	$this->RequestDate = 'month'; $this->RequestDateTitle = 'month'; break;
			case '6month':	$this->RequestDate = '6month'; $this->RequestDateTitle = '6 months'; break;
			case 'year':	$this->RequestDate = 'year'; $this->RequestDateTitle = 'year'; break;
		}
		
		$this->Group = $req->getVal('group');
		$this->ExcludeGroup = $req->getCheck('excludegroup');
		
		if ($req->getVal('csv') == 1) $this->OutputCSV = true;
		
		if (in_array('sysop', $wgUser->getEffectiveGroups())) $this->OutputEmails = true;
		
		$this->listoutput = false;
	}

	function getName() {
		return 'UsersEditCount';
	}

	function isCacheable() {
		return false;
	}

	function isExpensive() {
		return false;
	}
	
	function isSyndicated() {
		return false;
	}

	function getPageHeader() {
		$header  = '<p>';
		$skin = $this->getSkin();
		
		$linkday = $skin->makeLinkObj( $this->getTitle(), 'Day', 'date=day');
		$linkweek = $skin->makeLinkObj( $this->getTitle(), 'Week', 'date=week');
		$linkmonth = $skin->makeLinkObj( $this->getTitle(), 'Month', 'date=month');
		$link6month = $skin->makeLinkObj( $this->getTitle(), '6 Months', 'date=6month');
		$linkyear = $skin->makeLinkObj( $this->getTitle(), 'Year', 'date=year');
		$linkall = $skin->makeLinkObj( $this->getTitle(), 'All Time');
		
		$header .= "<small style='position:absolute; top:12px;'>View Edit Counts for the Last: {$linkday} | {$linkweek} | {$linkmonth} | {$link6month} | {$linkyear} | {$linkall} </small>";
		$header .= '<br />';
		$header .= wfMsg('userseditcounttext') . ' ';
		
		if ($this->RequestDate)
			$header .= 'Showing counts for edits in the last '. $this->RequestDateTitle .'. ';
		else
			$header .= 'Showing counts for all time.';
		
		$header .= '</p>';
		return $header;
	}

	function getQueryInfo() {
		if ( $this->Group ) {
			$dbr = wfGetDB( DB_SLAVE );
			$sql = $dbr->selectSQLText ( 'user_groups', 'ug_user', array ('ug_group' => $this->Group) );
			$exclude = $this->ExcludeGroup ? 'NOT' : '';
		}
		
		$queryinfo = array(
			'tables' => array( 'user' ),
			'fields' => array( 
					'2 as namespace',
					'user_id as title',
					'user_editcount as value' ),
			'conds' => array( 'user_editcount >= 0')
		);
		
		if ( $sql ) {
			$queryinfo['conds'][] = "user_id $exclude IN ($sql)";
		}
		
		switch ($this->RequestDate) {
			case 'day':
				$tsvalue = 1;
				break;
			case 'week':
				$tsvalue = 7;
				break;
			case 'month':
				$tsvalue = 31;
				break;
			case '6month':
				$tsvalue = 182.5;
				break;
			case 'year':
				$tsvalue = 365;
				break;
			default:
				$tsvalue = null;
				break;
		}
		
		if ($tsvalue == null) return $queryinfo;
		
		$tsvalue = time() - ( $tsvalue * 86400 );
		$queryinfo = array(
				'tables' => array( 'revision' ),
				'fields' => array( 
						'2 as namespace',
						'rev_user as title',
						'count(*) as value' ),
				'conds' => array( 'rev_timestamp >= "'. wfTimestamp(TS_MW, $tsvalue) .'"' ),
				'options' => array( 'GROUP BY' => 'rev_user' )
		);
				
		if ( $sql ) {
			$queryinfo['conds'][] = "rev_user $exclude IN ($sql)";
		}
		
		return $queryinfo;
	}

	function linkParameters() {
		$params = array('date' => $this->RequestDate);
		
		return $params;
	}

	function sortDescending() {
		return true;
	}

	function formatResult( $skin, $result ) {
		global $wgLang, $wgContLang;
		
		if ($this->OutputCSV) return $this->formatResultCSV($skin, $result);

		$user = null;
		$user = User::newFromId($result->title);

		if (is_null($user)) {
			return "User ID {$result->title} has {$result->value} edits.";
		}
		else if ($user->isAnon()) {
			return "Anonymous users have {$result->value} edits.";
		}
		else {
			$title = $user->getUserPage();
			$link  = $skin->makeLinkObj( $title, $wgContLang->convert( $user->getName() ) );
			
			$titletalk = $user->getTalkPage();
			$linktalk  = $skin->makeLinkObj( $titletalk, 'talk' );

			$titlecontrib = Title::newFromText("Special:Contributions/{$user->getName()}");
			$linkcontrib  = $skin->makeLinkObj( $titlecontrib, 'contribs');
		
			return "{$link} ( {$linktalk} | {$linkcontrib} ) has {$result->value} edits.";
		}
	}
	
	function formatResultCSV( $skin, $result ) {
		global $wgLang, $wgContLang;
	
		$user = null;
		$user = User::newFromName($result->title);
	
		if (is_null($user)) {
			if ($this->OutputEmails) return "{$result->title}, n/a, n/a, {$result->value}";
			return "{$result->title}, {$result->value}";
		}
		else if (isset($result->rev_user) && $result->rev_user == 0) {
			if ($this->OutputEmails) return "Anonymous, Anonymous, n/a, {$result->value}";
			return "Anonymous, {$result->value}";
		}
		else {
			if ($this->OutputEmails) return "{$user->getName()}, {$user->getRealName()}, {$user->getEmail()}, {$result->value}";
			return "{$user->getName()}, {$result->value}";
		}
	}
}
