<?php


class CUespShortLinkAjax {

	protected $db = null;
	
	protected $inputAction = '';
	protected $inputLink = '';
	
	protected $outputData = array( 'isError' => false );
	protected $outputJson = "";
	
	protected $HASH_LENGTH = 4;
	protected $MAX_HASH_CHECKS = 32;
	
	
	public function __construct() {
		$this->parseInputParams();
	}
	
	
	protected function parseInputParams() {
		$params = $_REQUEST;
		
		$this->outputData['inputaction'] = $_REQUEST['action'];
		
		if (array_key_exists('action', $params)) $this->inputAction = $params['action'];
		if (array_key_exists('link', $params)) $this->inputLink = $params['link'];
	}
	
	
	protected function initDbRead() {
		if ($this->db != null) return;
		
		include('/home/uesp/secrets/shortlinks.secrets');
		
		$this->db = new mysqli($uespShortLinkDatabaseReadHost, $uespShortLinkDatabaseUser, $uespShortLinkDatabasePassword, $uespShortLinkDatabase);
	}
	
	
	public function reportError($msg, $statusCode = 0) {
		error_log($errorMsg);
		
		$this->outputData['action'] = $this->inputAction;
		
		if ($this->outputData['error'] == null) $this->outputData['error'] = array();
		$this->outputData['error'][] = $msg;
		$this->outputData['isError'][] = true;
		
		if ($statusCode > 0) header("X-PHP-Response-Code: " . $statusCode, true, $statusCode);
		
		return false;
	}
	
	
	protected function isValidReferer() {
		$referer = $_SERVER['HTTP_REFERER'];
		if ($referer == "") return false;
		
		$host = parse_url($referer, PHP_URL_HOST);
		if (!$host) return false;
				
		$rootHost = substr($host, -8);
		if ($rootHost != "uesp.net") return false;
		
		return true;
	}
	
	
	protected function outputHeader()
	{
		ob_start("ob_gzhandler");
	
		header("Expires: 0");
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, no-store, must-revalidate");
		header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN'] . "");
		header("content-type: application/json");
	}
	
	
	protected function outputJson() {
		$this->outputJson = json_encode($this->outputData);
		print($this->outputJson);
	}
	
	
	public function createHash($base = "") {
		if ($base == "") $base = mt_rand();
		return substr(base64_encode(md5($base)), 0, $this->HASH_LENGTH);
	}
	
	
	public function makeRandomShortLink($link) {
		$this->initDbRead();
		
		$numChecks = 0;
		$isValid = false;
				
		while (!$isValid && $numChecks < $this->MAX_HASH_CHECKS) {
			$newLink = $this->createHash();
			if ($this->checkShortLink($newLink)) $isValid = true;
			
			++$numChecks;
		}
		
		if (!$isValid) {
			$this->outputData['isValid'] = false;
			$this->reportError("Failed to generate random short link after $numChecks tries!");
			return false;
		}
		
		$this->outputData['link'] = $newLink;
		$this->outputData['isValid'] = true;
		
		return true;
	}
	
	
	public function checkShortLink($link) {
		$this->initDbRead();
		
		$safeLink = $this->db->real_escape_string($link);
		$result = $this->db->query("SELECT * FROM links WHERE shortLink='$safeLink';");
		if (!$result) return $this->reportError("Database query error when checking link!");
		
		if ($result->num_rows > 0) {
			$this->outputData['isValid'] = false;
		 	return false;
		}
		
		$this->outputData['isValid'] = true;
		return true;
	}
	
	
	public function run() {
		$this->outputHeader();
		
		if (!$this->isValidReferer()) {
			$this->reportError("Permission Denied!");
			$this->outputJson();			
			return false;
		}
		
		if ($this->inputAction == '') {
			$this->reportError("No action suppplied!");
		}
		else if ($this->inputAction == 'makerandom') {
			$this->makeRandomShortLink($this->inputLink);
		}
		else if ($this->inputAction == 'checklink') {
			$this->checkShortLink($this->inputLink);
		}
	
		$this->outputJson();
		
		return true;
	}
	
}


$uslAjax = new CUespShortLinkAjax();
$uslAjax->run();