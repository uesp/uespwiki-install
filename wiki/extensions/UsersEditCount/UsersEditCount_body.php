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

	function __construct( $name = 'UsersEditCount' ) {
		global $wgUser;
		
		parent::__construct( $name );
		
		$inputdate = $this->getRequest()->getVal('date');
		 
		switch (strtolower($inputdate)) {
			case 'day': 	$this->RequestDate = 'day'; $this->RequestDateTitle = 'day'; break;
			case 'week':	$this->RequestDate = 'week'; $this->RequestDateTitle = 'week'; break;
			case 'month':	$this->RequestDate = 'month'; $this->RequestDateTitle = 'month'; break;
			case '6month':	$this->RequestDate = '6month'; $this->RequestDateTitle = '6 months'; break;
			case 'year':	$this->RequestDate = 'year'; $this->RequestDateTitle = 'year'; break;
		}
		
		if ($this->getRequest()->getVal('csv') == 1) $this->OutputCSV = true;
		
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
		$queryinfo = array(
			'tables' => array( 'user' ),
			'fields' => array( 'user_id',
					'user_name as type',
					'user_editcount as value' ),
			'conds' => array('user_editcount >= 0' )
		);
		
		$tsnow = time();
		
		switch ($this->RequestDate) {
			case 'day':
				$tsvalue = wfTimestamp(TS_MW, $tsnow - 86400);
				$datecond = 'rev_timestamp >= "'.$tsvalue .'"';
				break;
			case 'week':
				$tsvalue =  wfTimestamp(TS_MW, $tsnow - 86400*7);
				$datecond = 'rev_timestamp >= "'.$tsvalue .'"';
				break;
			case 'month':
				$tsvalue =  wfTimestamp(TS_MW, $tsnow - 86400*31);
				$datecond = 'rev_timestamp >= "'.$tsvalue .'"';
				break;
			case '6month':
				$tsvalue =  wfTimestamp(TS_MW, $tsnow - 86400*31*6);
				$datecond = 'rev_timestamp >= "'.$tsvalue .'"';
				break;
			case 'year':
				$tsvalue =  wfTimestamp(TS_MW, $tsnow - 86400*365);
				$datecond = 'rev_timestamp >= "'.$tsvalue .'"';
				break;
			default:
				$datecond = null;
				break;
		}
		
		if ($datecond == null) return $queryinfo;
		
		$queryinfo = array(
				'tables' => array( 'revision' ),
				'fields' => array( 'rev_user',
						'rev_user_text as type',
						'count(*) as value' ),
				'conds' => array($datecond),
				'options' => array('GROUP BY' => 'rev_user')
		);
		
		
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
		$user = User::newFromName($result->type);

		if (is_null($user)) {
			return "{$result->type} has {$result->value} edits.";
		}
		else if (isset($result->rev_user) && $result->rev_user == 0) {
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
		$user = User::newFromName($result->type);
	
		if (is_null($user)) {
			if ($this->OutputEmails) return "{$result->type}, n/a, n/a, {$result->value}";
			return "{$result->type}, {$result->value}";
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
