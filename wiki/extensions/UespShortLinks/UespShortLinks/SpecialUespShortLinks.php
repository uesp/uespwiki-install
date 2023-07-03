<?php


if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is a MediaWiki extension and must be run from within MediaWiki.' );
}


class SpecialUespShortLinks extends SpecialPage {
	
	public $db = null;
	
	public $shortLinkCount = 0;
	
	public $links = array();
	public $areLinksLoaded = false;

	
	public function __construct() {
		parent::__construct('UespShortLinks');
	}
	
	
	public function initDbRead() {
		include('/home/uesp/secrets/shortlinks.secrets');
		
		$params = array(
				'host' => $uespShortLinkDatabaseReadHost,
				'user' => $uespShortLinkDatabaseUser,
				'password' => $uespShortLinkDatabasePassword,
				'dbName' => $uespShortLinkDatabase,
				'variables' => array(),
		);
		
		$this->db = new DatabaseMysql($params);
		
		$this->db->selectDB($uespShortLinkDatabase);
	}
	
	
	public function initDbWrite() {
		include('/home/uesp/secrets/shortlinks.secrets');
		
		$params = array(
				'host' => $uespShortLinkDatabaseWriteHost,
				'user' => $uespShortLinkDatabaseUser,
				'password' => $uespShortLinkDatabasePassword,
				'dbName' => $uespShortLinkDatabase,
				'variables' => array(),
		);
		
		$this->db = new DatabaseMysql($params);
		
		$this->db->selectDB($uespShortLinkDatabase);
	}
	
	
	public function loadStats() {
		$res = $this->db->select('links', 'count(*) as numLinks');
		if ($res->numRows() == 0) return;
		
		$row = $res->fetchRow();
		if ($row == null) return;
		
		$this->shortLinkCount = $row['numLinks'];
	}
	
	
	public function loadLinks() {
		$res = $this->db->select('links', '*', '', __METHOD__, array('ORDER BY' => 'shortLink'));
		if ($res->numRows() == 0) return false;
		
		$this->links = array();
		
		while ($row = $res->fetchRow()) {
			$this->links[] = $row;
		}
		
		$this->areLinksLoaded = true;
		return true;
	}
	
	
	public function doesUserHaveReadAccess() {

		$user = $this->getContext()->getUser();
		if (user == null) return false;
		
		return $user->isAllowed('uespShortLinksRead');
	}

	
	public function doesUserHaveWriteAccess() {
		
		$user = $this->getContext()->getUser();
		if (user == null) return false;
				
		return $user->isAllowed('uespShortLinksWrite');
	}

	
	public function execute( $parameter ) {
				
		$output = $this->getOutput();
		
		$output->addModules('ext.UespShortLinks');
		
		if (!$this->doesUserHaveReadAccess()) {
			$output->addHTML("Permission Denied!");
			return;
		}
		
		$request = $this->getRequest();
		$action = $request->getVal('action');
		
		switch ($action) {
			case 'new':
				$this->displayNewLink();
				break;
			case 'savenew':
				$this->saveNewLink();
				break;
			default:
				$this->displayLinks();
				break;
		}
		
	}
	
	
	public function saveNewLink() {
		$output = $this->getOutput();
		$request = $this->getRequest();
		
		if (!$this->doesUserHaveWriteAccess()) {
			$output->addHTML("Permission Denied!");
			return false;
		}
		
		$shortLink = $request->getVal('shortlink');
		$link = $request->getVal('link');
		
		if ($shortLink == "") {
			$output->addHTML("Error: No short link specified!");
			return false;
		}
		
		if ($link == "") {
			$output->addHTML("Error: No redirect link specified!");
			return false;
		}
		
		$this->initDbWrite();
				
		$data = array(
				'shortLink' => $shortLink,
				'link' => $link,
		);
		
		$result = $this->db->insert('links', $data);
		
		$safeShortLink = htmlspecialchars($shortLink);
		$safeLink = htmlspecialchars($link);
		
		if (!$result) {
			$output->addHTML("Error adding link to database!<p/>");
			$output->addHTML("<a href=''>Back to All Links</a><p/>");
			return false;
		}		
		
		$shortLinkUrl = "<a href='//s.uesp.net/$safeShortLink'>s.uesp.net/$safeShortLink</a>";
		$linkUrl = "<a href='$safeLink'>$safeLink</a>";
		
		$output->addHTML("Successfully added <b>$shortLinkUrl</b> that redirects to <b>$linkUrl</b>!<p/>");
		$output->addHTML("<a href=''>Back to All Links</a><p/>");
		return true;
	}
	
	
	public function displayNewLink() {
		$output = $this->getOutput();
		
		if (!$this->doesUserHaveWriteAccess()) {
			$output->addHTML("Permission Denied!");
			return false;
		}
		
		$output->addHTML("Create a new short link:<p/>");
		
		$output->addHTML("<form action=\"\" method=\"post\">");
		$output->addHTML("<input type=\"hidden\" name=\"action\" value=\"savenew\">");
		$output->addHTML("<table class='uslCreateTable'>");
		
		$output->addHTML("<tr>");
		$output->addHTML("<th>Short Link:</th>");
		$output->addHTML("<td><input type=\"text\" name=\"shortlink\" value=\"\" maxlength=\"8\" size=\"8\" id=\"uslShortLinkText\" title=\"\"> &nbsp; <button type=\"button\" id=\"uslMakeRandomLinkButton\">Random</button></td>");
		$output->addHTML("<tr>");
		
		$output->addHTML("<tr>");
		$output->addHTML("<th>Redirect Link:</th>");
		$output->addHTML("<td><input type=\"text\" name=\"link\" value=\"\" maxlength=\"256\" size=\"32\" id=\"uslLinkText\" title=\"\"></td>");
		$output->addHTML("<tr>");
		
		$output->addHTML("</table>");
		$output->addHTML("<p/><button id=\"uslCreateButton\" disabled>Create Link</button>");
		$output->addHTML("</form>");
		
		return true;
	}
	
	
	public function displayLinks() {
		$this->initDbRead();
		$this->loadStats();
		
		$this->outputLinks();
	}
	
	
	public function outputLinks() {
		$this->loadLinks();

		$output = $this->getOutput();
		
		$output->addHTML("There are currently {$this->shortLinkCount} links being shortened.<p/>");
		$output->addHTML("<table class='uslLinkTable'>");
		
		$output->addHTML("<tr>");
		$output->addHTML("<th>Short Link</th>");
		$output->addHTML("<th>Links To</th>");
		$output->addHTML("</tr>");
		
		if ($this->doesUserHaveWriteAccess()) {
			$output->addHTML("<tr>");
			$createButton = "<form action=\"\" method=\"post\"><input type=\"hidden\" name=\"action\" value=\"new\"><input type=\"submit\" value=\"Create Short Link\" id=\"uslCreateButton\"></form>";
			$output->addHTML("<td colspan=\"2\" class=\"uslCreateButtonRow\">$createButton</td>");
			$output->addHTML("</tr>");
		}
		
		foreach ($this->links as $link) {
			$safeShortLink = htmlspecialchars($link['shortLink']);
			$safeLink = htmlspecialchars($link['link']);
			$output->addHTML("<tr>");
			$output->addHTML("<td><a href=\"https://s.uesp.net/$safeShortLink\">$safeShortLink</a></td>");
			$output->addHTML("<td><a href=\"$safeLink\">$safeLink</a></td>");
			$output->addHTML("</tr>");
		}
		
		$output->addHTML("</table>");
	}
};

