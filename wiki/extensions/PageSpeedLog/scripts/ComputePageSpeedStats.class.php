<?php


class ComputePageSpeedStats 
{
	
	public $LOGFILE = "/var/log/httpd/pagespeed.log";
	
	public $durationToParse = 60;		// Seconds
	public $currentTime = 0;
	public $lineBufferSize = 512;
	public $data = array();
	public $startScriptTime = 0;
	public $endScriptTime = 0;
	public $echo = false;
	public $numLinesFound = 0;
	public $scriptTimeTaken = 0;
	
	public $minSpeed = -1;
	public $maxSpeed = -1;
	public $avgSpeed = -1;
	public $stdSpeed = -1;
	public $stdSpeed90 = -1;
	public $medSpeed = -1;
	public $speedDataCount = -1;
	
	public $f = false;
	
	public $outputJson = false;
	public $outputData = array();
	public $json = "";
	
	
	function __construct()
	{
		$this->currentTime = time();
		$this->startScriptTime = microtime(true);
		
		$this->parseInputParams();
	}
	
	
	function parseInputParams() {
		$options = getopt("f:je");
		
		if ($options['j'] !== null) $this->outputJson = true;
		if ($options['e'] !== null) $this->echo = true;
		
		if ($options['f'] !== null) 
		{
			$logFile = $options['f'];
			
			if (file_exists($logFile)) 
			{
				$this->LOGFILE = $logFile;
				if ($this->echo) print("Using '$logFile' as source log file!\n");
			}
		}
	}
	
	
	function ReportError($msg)
	{
		if ($this->outputJson) {
			$this->outputData['isError'] = true;
			if ($this->outputData['errorMsg'] == null) $this->outputData['errorMsg'] = array();
			$this->outputData['errorMsg'][] = $msg;
		}
		else if ($this->echo) {
			print($msg . "\n");
		}
		
		return false;
	}
	
	
	function ParseLinesFromEndOfFile()
	{
		fseek($this->f, -1, SEEK_END);
		
		$firstTime = $this->currentTime - $this->durationToParse;
		$chunk = "";
		$leftOverChunk = "";
		$isFinished = false;
		$numLinesFound = 0;
		
		while (ftell($this->f) > 0 && !$isFinished) 
		{
			$seekOffset = min(ftell($this->f), $this->lineBufferSize);
			fseek($this->f, -$seekOffset, SEEK_CUR);
			
			$chunk = fread($this->f, $seekOffset) . $leftOverChunk;
			
			fseek($this->f, -$seekOffset, SEEK_CUR);
			
			$lines = explode("\n", $chunk);
			
			$leftOverChunk = $lines[0];
			
			for ($i = count($lines) - 1; $i >= 1 ; --$i)
			{
				$cols = explode(",", $lines[$i], 3);
				$startTime = floatval($cols[0]);
				
				if ($startTime <= 0) continue;
				
				if ($startTime < $firstTime)
				{
					$isFinished = true;
					break;
				}
				
				$this->data[] = $cols;
				++$numLinesFound;
			}
		}
		
		$this->numLinesFound = $numLinesFound;
		
	}
	
	
	function OutputText() {
		if (!$this->echo) return;
		
		print("\tFound {$this->numLinesFound} lines from last {$this->durationToParse} sec!\n");
		
		print("\tRange = {$this->minSpeed} to {$this->maxSpeed} ms\n");
		print("\tAverage = {$this->avgSpeed} ms\n");
		print("\tMedian = {$this->medSpeed} ms\n");
		print("\tStandard Deviation = {$this->stdSpeed} ms\n");
		print("\t90% = {$this->stdSpeed90} ms\n");
		
		print("\tTime Taken = {$this->scriptTimeTaken} ms\n"); 
	}
	
	
	function OutputJson() {
		$this->json = json_encode($this->outputData);
		print($this->json);
	}
	
	
	function Output() {
		
		if ($this->outputJson)
			$this->OutputJson();
		else
			$this->OutputText();
	}
	
	
	function ComputeMedian($data)
	{
		$values = [];
		
		foreach ($data as $element)
		{
			$values[] = floatval($element[1]);
		}
		
		sort($values);
		$count = count($values);
		$middleIndex = floor(($count-1)/2);
		
		if ($count % 2)
		{
			$median = $values[$middleIndex];
		} 
		else
		{
			$low = $values[$middleIndex];
			$high = $values[$middleIndex + 1];
			$median = (($low+$high)/2);
		}
		
		return $median;
	}
	
	
	function ComputeStats()
	{
		$minSpeed = 100000;
		$maxSpeed = 0;
		$sumSpeed = 0;
		$count = 0;
		
		foreach ($this->data as $data)
		{
			if (trim($data[2]) == "") continue;
			++$count;
			
			$speed = floatval($data[1]);
			
			if ($speed < $minSpeed) $minSpeed = $speed;
			if ($speed > $maxSpeed) $maxSpeed = $speed;
			
			$sumSpeed += $speed;
		}
		
		if ($count <= 0) return $this->ReportError("No data to compute stats for!");
		
		$avgSpeed = $sumSpeed / $count;
		$sumSpeed2 = 0;
		
		foreach ($this->data as $data)
		{
			$speed = floatval($data[1]);
			$sumSpeed2 += pow($speed - $avgSpeed, 2);
		}
		
		$deviation = sqrt($sumSpeed2 / $count);
		$deviation90 = $deviation * 1.645 + $avgSpeed;
		
		$this->speedDataCount = $count;
		$this->medSpeed = $this->ComputeMedian($this->data);
		$this->minSpeed = $minSpeed;
		$this->maxSpeed = $maxSpeed;
		$this->avgSpeed = $avgSpeed;
		$this->stdSpeed = $deviation;
		$this->stdSpeed90 = $deviation90;
		
		$this->outputData['parseDuration'] = $this->durationToParse;
		$this->outputData['dataCount'] = $count;
		$this->outputData['minSpeed'] = $minSpeed;
		$this->outputData['maxSpeed'] = $maxSpeed;
		$this->outputData['medSpeed'] = $medSpeed;
		$this->outputData['avgSpeed'] = $avgSpeed;
		$this->outputData['stdSpeed'] = $deviation;
		$this->outputData['stdSpeed90'] = $deviation90;
		
		return true;
	}
	
	
	function Parse()
	{
		$this->f = @fopen($this->LOGFILE, "rb");
		if ($this->f === false) return $this->ReportError("Failed to open log file '{$this->LOGFILE}'!");
		
		$this->ParseLinesFromEndOfFile();
		$this->ComputeStats();
		
		fclose($this->f);
		
		$this->endScriptTime = microtime(true);
		$this->scriptTimeTaken = ($this->endScriptTime - $this->startScriptTime) * 1000;
		
		$this->Output();
		
		return true;
	}
	
};

