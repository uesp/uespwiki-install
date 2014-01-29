<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "UespSiteStats extension requires MediaWiki\n";
	exit ( 1 );
}

class UespSiteStats extends SpecialPage {
	
	protected $m_HostAliases   = array();
	protected $m_UespSiteStats = array();
	
	function __construct() {
		parent::__construct( 'UespSiteStats', 'editinterface' );

		include "UespSiteStats_conf.php";
	}

	function execute( $parameters ) {
		global $wgOut, $wgUser, $wgScriptPath, $IP;

		$this->setHeaders();

		if ( !$wgUser->isAllowed( 'uespstats' ) )
		{
			$wgOut->addHTML("Permission Denied!");
			return;
		}	

		$wgOut->addHTML("<script type=\"text/javascript\">\n");
		$wgOut->addHTML("   var UESP_BASE_URL = \"$wgScriptPath\";\n");
		$jslines = file_get_contents("$IP/extensions/UespSiteStats/uespsitestats.js");
		$wgOut->addHTML($jslines);
		$wgOut->addHTML("</script>\n");

		$this->doSiteStats();

	}

	function doSiteStats () {
		global $wgOut;

		foreach ($this->m_UespSiteStats as $stattype => $values) {
		  $this->doStats($stattype, $values);
 		}
	}

	function doStats ($stattype, $sites) {
		global $wgOut;

		foreach ($sites as $key => $value) {

		  switch ($stattype) {
		    case "memory":
			$sitename = $this->transformSiteName($value);
			$this->getSiteMemory($sitename);
			break;
		    case "uptime":
			$sitename = $this->transformSiteName($value);
			$this->getSiteUptime($sitename);
			break;
		    case "diskusage":
			$sitename = $this->transformSiteName($value);
			$this->getDiskUsage($sitename);
			break;
		    case "ifconfig":
			$sitename = $this->transformSiteName($value);
			$this->getIfConfig($sitename);
			break;
		    case "masterdbstatus":
			$sitename = $this->transformSiteName($value['host']);
			$user     = $this->transformUser($value['user']);
			$this->getMasterStatus($sitename, $user);
			break;
		    case "slavedbstatus":
			$sitename = $this->transformSiteName($value['host']);
			$user     = $this->transformUser($value['user']);
			$this->getSlaveStatus($sitename, $user);
		 	break;
		    default:
			$wgOut->addHTML("<pre>Unknown statistic '$stattype' for host '$value'</pre>");
			break;
		  }
		}
	}

	function transformSiteName ($sitename) {

		if (preg_match("/[a-zA-Z0-9]+/", $sitename)) {
			return "$sitename.uesp.net";
		}
		else if (preg_match("/[a-zA-Z0-9.]+/", $sitename)) {
			return $sitename;
		}
		else {
			return "badhost";
		}
	}

	function transformUser ($user) {

		if ($user == "") {
			return "slaveinfo";
		}
		else if (preg_match("/[a-zA-Z0-9]+/", $user)) {
			return $user;
		}
		else {
			return "baduser";
		}
	}

	function outputAjaxJS ( $function ) {
		global $wgOut;

		$wgOut->addHTML("<script type=\"text/javascript\">");
		$wgOut->addHTML(" addEvent(window, \"load\", $function); ");
		$wgOut->addHTML("</script>");
  	}

	function getSiteMemory ( $host ) {
		global $wgOut;

		$elementid="memory-$host";

		$this->outputAjaxJS("getMemory(\"$host\", \"$elementid\")");

		$wgOut->addHTML("<h2>$host Memory Usage</h2>\n");
		$wgOut->addHTML("<pre id='$elementid'>");
		$wgOut->addHTML("</pre>\n");
	}

	function getSiteUptime ( $host ) {
		global $wgOut;

		$elementid="uptime-$host";

		$this->outputAjaxJS("getUptime(\"$host\", \"$elementid\")");

		$wgOut->addHTML("<h2>$host Uptime</h2>\n");
		$wgOut->addHTML("<pre id=\"$elementid\">\n");
		$wgOut->addHTML("</pre>\n");
	}

	function getIfConfig ( $host ) {
		global $wgOut;
		
		$elementid="ifconfig-$host";
		
		$this->outputAjaxJS("getIfConfig(\"$host\", \"$elementid\")");

		$wgOut->addHTML("<h2>$host IfConfig</h2>\n");
		$wgOut->addHTML("<pre id=\"$elementid\">\n");
		$wgOut->addHTML("</pre>\n");
	}

	function getDiskUsage ( $host ) {
		global $wgOut;

		$elementid="diskusage-$host";
		$this->outputAjaxJS("getDiskUsage(\"$host\", \"$elementid\")");

		$wgOut->addHTML("<h2>Disk Usage -- $host</h2>\n");
		$wgOut->addHTML("<pre id=\"$elementid\">\n");
		$wgOut->addHTML("</pre>\n");
	}

	function getMasterStatus ( $host, $user ) {
		global $wgOut;

		$elementid="masterstatus-$host";
		$this->outputAjaxJS("getMasterDBStatus(\"$host\", \"$user\", \"$elementid\")");
		
		$wgOut->addHTML("<h2>$host Database Master Status </h2>\n");
		$wgOut->addHTML("<pre id=\"$elementid\">\n");
		$wgOut->addHTML("</pre>\n");
	}

	function getSlaveStatus ( $host, $user ) {
		global $wgOut;

		$elementid="slavestatus-$host";
		$this->outputAjaxJS("getSlaveDBStatus(\"$host\", \"$user\", \"$elementid\")");

		$wgOut->addHTML("<h2>$host Database Slave Status </h2>\n");
		$wgOut->addHTML("<pre id=\"$elementid\">\n");
		$wgOut->addHTML("</pre>\n");
	}
}
?>
