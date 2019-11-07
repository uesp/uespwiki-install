<?php


class ComputePageSpeedStats 
{
	
	public $logFile = "/var/log/httpd/pagespeed.log";
	public $durationToParse = 60;		// Seconds
	public $currentTime = 0;
	public $lineBufferSize = 512;
	public $data = array();
	public $startScriptTime = 0;
	public $endScriptTime = 0;
	public $echo = false;
	
	public $minSpeed = -1;
	public $maxSpeed = -1;
	public $avgSpeed = -1;
	public $stdSpeed = -1;
	public $stdSpeed90 = -1;
	public $speedDataCount = -1;
	
	public $f = false;

	
	function __construct()
	{
		$this->currentTime = time();
		$this->startScriptTime = microtime(true);
	}
	
	
	function Output($msg)
	{
		if ($this->echo) print($msg . "\n");
	}
	
	
	function ReportError($msg)
	{
		if ($this->echo) print($msg . "\n");
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
		
		$this->Output("\tFound $numLinesFound lines from last {$this->durationToParse} sec!");
	}
	
	
	function ComputeStats()
	{
		$minSpeed = 100000;
		$maxSpeed = 0;
		$sumSpeed = 0;
		$count = 0;
				
		foreach ($this->data as $data)
		{
			if ($data[2] == "") continue;
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
			$sumSpeed2 = pow($speed - $avgSpeed, 2);
		}
		
		$deviation = $sumSpeed2 / $count;
		$deviation90 = $deviation * 1.645 + $average; 
		
		$this->Output("\tRange = $minSpeed to $maxSpeed ms");
		$this->Output("\tAverage = $avgSpeed ms");
		$this->Output("\tStandard Deviation = $deviation ms");
		$this->Output("\t90% = $deviation90 ms");
		
		$this->speedDataCount = $count;
		$this->minSpeed = $minSpeed;
		$this->maxSpeed = $maxSpeed;
		$this->avgSpeed = $avgSpeed;
		$this->stdSpeed = $deviation;
		$this->stdSpeed90 = $deviation90;
		
		return true;
	}
	
	
	function Parse()
	{
		$this->f = @fopen($this->logFile, "rb");
		if ($this->f === false) return $this->ReportError("Failed to open log file '{$this->logFile}'!");
		
		$this->ParseLinesFromEndOfFile();
		$this->ComputeStats();
		
		fclose($this->f);
		
		$this->endScriptTime = microtime(true);
		$diffTime = ($this->endScriptTime - $this->startScriptTime) * 1000;
		$this->Output("\tTime Taken = $diffTime ms"); 
		
		return true;
	}
	
};

