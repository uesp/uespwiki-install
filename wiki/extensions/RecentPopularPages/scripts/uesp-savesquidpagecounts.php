<?php
/*
 * Very basic script that takes the output from uesp-squidlogpagecounts and saves them in the UESP wiki database for display.
 */

if (php_sapi_name() != "cli") die("Can only be run from command line!");

require_once("/home/uesp/secrets/wiki.secrets");


class CSaveUespSquidPageCounts
{
	public $INPUTFILE = "/home/uesp/pagecounts/squidpagecounts.txt";
	public $TIMEFILE = "/home/uesp/pagecounts/squidpagetime.txt";
	public $SQUIDLOG = "/var/log/squid/access.log";
	
		/* Estimate the number of lines to go back in the squid log to get at least the last 1 hour of data */ 
	public $SQUIDLOG_NUMLINES = 1000000;
	
	public $pageData = array();
	public $savedPageCount = 0;
	public $totalPageCounts = 0;
	public $pageCountTime = 0;
	
	public $db = null;
	public $dbWriteInitialized = false;
	
	
	public function __construct()
	{
	}
	
	
	public function ReportError($msg)
	{
		//error_log("SaveUespSquidPageCounts: $msg");
		print("$msg\n");
		return false;
	}
	
	
	public function InitDatabase()
	{
		global $uespWikiDB,	$uespWikiUser, $uespWikiPW, $UESP_SERVER_DB1;
		
		if ($this->dbWriteInitialized) return true;
		
		$this->db = new mysqli($UESP_SERVER_DB1, $uespWikiUser, $uespWikiPW, $uespWikiDB);
		if ($this->db->connect_error) return $this->ReportError("Could not connect to wiki database!");
		
		$this->dbWriteInitialized = true;
		return true;
	}
	
	
	public function ParseSquidLog()
	{
		$startTime = microtime(true);
		//$output = shell_exec("tail -n {$this->SQUIDLOG_NUMLINES} $this->SQUIDLOG");
		//$lines = explode("\n", $output);
		$lines = array();
		exec("tail -n {$this->SQUIDLOG_NUMLINES} $this->SQUIDLOG", $lines);
		$count = count($lines);
		
		$diffTime = intval((microtime(true) - $startTime)/1000);
		
		print("Read $count lines from tail command in $diffTime ms!\n");
		print($lines[0]);
	}
	
	
	public function ParsePageCountDataFile()
	{
		$lines = file($this->INPUTFILE);
		if ($lines === false) return $this->ReportError("Failed to open data file '{$this->INPUTFILE}' for reading!");
		
		$this->pageCountTime = file_get_contents($this->TIMEFILE);
		
		if ($this->pageCountTime === false) 
		{
			$this->pageCountTime = 0;
		}
		else
		{
			$this->pageCountTime = round($this->pageCountTime);
		}
		
		$this->pageData = array();
		$this->totalPageCounts = 0;
		
		foreach ($lines as $line)
		{
			$cols = preg_split('/ /', $line, 2, PREG_SPLIT_NO_EMPTY);
			
			$count = $cols[0];
			$name = $cols[1];
			
			if ($count == null || $name == null) continue;
			
			$this->pageData[$name] = $count;
			$this->totalPageCounts += $count;
		}
		
		$count = count($this->pageData);
		
		print("Loaded $count pages from the data file with {$this->totalPageCounts} counts over a period of {$this->pageCountTime} secs!\n");
		
		return true;
	}
	
	
	public function CreateTable()
	{
		$query = "CREATE TABLE IF NOT EXISTS popularPageCounts (
						pageName VARCHAR(128) BINARY NOT NULL,
						pageCount INTEGER NOT NULL,
						PRIMARY KEY (pageName(128)),
						INDEX countIndex(pageCount)
				)  ENGINE=MYISAM;";
		$result = $this->db->query($query);
		if ($result === false) return $this->ReportError("Failed to create the popularPageCounts table! {$this->db->error}");
		
		return true;
	}
	
	
	public function SaveData()
	{
		if (!$this->CreateTable()) return false;
		
		$result = $this->db->query("DROP TABLE IF EXISTS popularPageCountsTmp;");
		if ($result === false) return $this->ReportError("Failed to drop the popularPageCountsTmp table! {$this->db->error}");
		
		$result = $this->db->query("CREATE TABLE popularPageCountsTmp LIKE popularPageCounts;");
		if ($result === false) return $this->ReportError("Failed to create the popularPageCountsTmp table! {$this->db->error}");
		
		$query = "INSERT INTO popularPageCountsTmp(pageName, pageCount) VALUES('', '{$this->pageCountTime}');";
		$result = $this->db->query($query);
		
		foreach ($this->pageData as $name => $count)
		{
			$safeName = $this->db->real_escape_string($name);
			if ($safeName == "") continue;
			
			$query = "INSERT INTO popularPageCountsTmp(pageName, pageCount) VALUES('$safeName','$count') ON DUPLICATE KEY UPDATE pageCount=pageCount + $count;";
			$result = $this->db->query($query);
			if ($result === false) return $this->ReportError("Failed to insert row into the popularPageCountsTmp table! {$this->db->error}");
			
			$this->savedPageCount++;
		}
		
		$result = $this->db->query("DROP TABLE IF EXISTS popularPageCountsOld;");
		if ($result === false) return $this->ReportError("Failed to drop the popularPageCountsOld table! {$this->db->error}");
		
		$result = $this->db->query("RENAME TABLE popularPageCounts to popularPageCountsOld;");
		if ($result === false) $this->ReportError("Failed to rename the popularPageCountsOld table! {$this->db->error}");
		
		$result = $this->db->query("RENAME TABLE popularPageCountsTmp to popularPageCounts;");
		if ($result === false) return $this->ReportError("Failed to rename the popularPageCountsTmp table! {$this->db->error}");
		
		return true;
	}
	
	
	public function DoSave()
	{
		if (!$this->InitDatabase()) return false;
		if (!$this->ParsePageCountDataFile()) return false;
		if (!$this->SaveData()) return false;
		
		print("Saved $this->savedPageCount page count data to the UESP wiki database!\n");
		
		return true;
	}
	
};


$g_saveUespSquidPageCounts = new CSaveUespSquidPageCounts();
$g_saveUespSquidPageCounts->DoSave();
//$g_saveUespSquidPageCounts->ParseSquidLog();