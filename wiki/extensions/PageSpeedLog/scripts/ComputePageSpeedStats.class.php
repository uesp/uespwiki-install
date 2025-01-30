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
	public $ignoreTimesMoreThan = -1;	//ms
	public $linesIgnored = 0;
	public $showSummary = false;
	public $summaryTime = 60;					//sec
	public $DEFAULT_SUMMARY_DURATION = 600;		//sec
	public $showTimesMoreThan = -1;	//ms
	
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
	public $summaryData = array();
	public $json = "";
	
	
	function __construct()
	{
		$this->currentTime = time();
		$this->startScriptTime = microtime(true);
		
		$this->parseInputParams();
	}
	
	
	function ShowHelp()
	{
		print("     -e        Output verbose text\n");
		print("     -f file   Specify log file to parse (default: {$this->LOGFILE})\n");
		print("     -h        Show help\n");
		print("     -i        Ignore times more than this (ms)\n");
		print("     -j        Output as JSON\n");
		print("     -l        Show all times times more than this (ms)\n");
		print("     -m        Specify duration for summary (default: {$this->summaryTime} secs)\n");
		print("     -s        Show summary\n");
		print("     -t        Specify parse duration (default: {$this->durationToParse} secs)\n");
	}
	
	
	function parseInputParams() {
		$options = getopt("f:jet:sm:i:l:h");
		
		if ($options['h'] !== null)
		{
			$this->ShowHelp();
			exit(1);
		}
		
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
		
		if ($options['s'] !== null) $this->showSummary = true;
		
		if ($options['m'] !== null)
		{
			$duration = intval($options['m']);
			if ($duration > 0) $this->summaryTime = $duration;
			print("Using value of $duration sec for summary duration...\n");
		}
		
		if ($options['t'] !== null) 
		{
			$duration = intval($options['t']);
			if ($duration > 0) $this->durationToParse = $duration;
			print("Using duration of $duration sec for parsing log file...\n");
		}
		else if ($this->showSummary)
		{
			$duration = $this->DEFAULT_SUMMARY_DURATION;
			$this->durationToParse = $duration;
			print("Using duration of $duration sec for summary parsing log file...\n");
		}
		
		if ($options['i'] !== null)
		{
			$duration = intval($options['i']);
			if ($duration > 0) $this->ignoreTimesMoreThan = $duration;
			print("Ignoring load times more than $duration ms ...\n");
		}
		
		if ($options['l'] !== null)
		{
			$duration = intval($options['l']);
			if ($duration > 0) $this->showTimesMoreThan = $duration;
			print("Showing load times more than $duration ms ...\n");
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
				
				$speed = floatval($cols[1]);
				if ($this->ignoreTimesMoreThan > 0 && $speed > $this->ignoreTimesMoreThan)
				{
					$this->linesIgnored++;
					continue;
				}
				
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
	
	
	function OutputText()
	{
		if (!$this->echo) return;
		
		if ($this->linesIgnored > 0)
			print("\tFound {$this->numLinesFound} lines (ignored $this->linesIgnored lines slower than {$this->ignoreTimesMoreThan} ms) from last {$this->durationToParse} sec )!\n");
		else
			print("\tFound {$this->numLinesFound} lines from last {$this->durationToParse} sec!\n");
		
		print("\tRange = {$this->minSpeed} to {$this->maxSpeed} ms\n");
		print("\tAverage = {$this->avgSpeed} ms\n");
		print("\tMedian = {$this->medSpeed} ms\n");
		print("\tStandard Deviation = {$this->stdSpeed} ms\n");
		print("\t90% = {$this->stdSpeed90} ms\n");
		
		print("\tTime Taken = {$this->scriptTimeTaken} ms\n"); 
	}
	
	
	function OutputSummaryText($startTime, $deltaTime)
	{
		if (!$this->echo) return;
		
		$endTime = $startTime + $deltaTime;
		print("\t$startTime + $deltaTime seconds:\n");
		
		if ($this->linesIgnored > 0)
			print("\t\tFound {$this->speedDataCount} lines (ignored $this->linesIgnored lines slower than {$this->ignoreTimesMoreThan} ms) from last {$this->summaryTime} sec )!\n");
		else
			print("\t\tFound {$this->speedDataCount} lines from last {$this->summaryTime} sec!\n");
		
		print("\t\tRange = {$this->minSpeed} to {$this->maxSpeed} ms\n");
		print("\t\tAverage = {$this->avgSpeed} ms\n");
		print("\t\tMedian = {$this->medSpeed} ms\n");
		print("\t\tStandard Deviation = {$this->stdSpeed} ms\n");
		print("\t\t90% = {$this->stdSpeed90} ms\n");
	}
	
	
	function OutputJson()
	{
		$this->json = json_encode($this->outputData);
		print($this->json);
	}
	
	
	function Output()
	{
		if ($this->outputJson)
			$this->OutputJson();
		else
			$this->OutputText();
	}
	
	
	function OutputSummary($startTime, $deltaTime)
	{
		if ($this->outputJson)
		{
			$this->outputData[] = $this->summaryData;
		}
		else
		{
			$this->OutputSummaryText($startTime, $deltaTime);
		}
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
	
	
	function ComputeSummaryStats($startTime, $deltaTime)
	{
		$minSpeed = 100000;
		$maxSpeed = 0;
		$sumSpeed = 0;
		$count = 0;
		
		for ($i = count($this->data); $i >= 0; $i--)
		{
			$data = $this->data[$i];
			
			if (trim($data[2]) == "") continue;
			
			$time = floatval($data[0]);
			if ($time < $startTime) continue;
			if ($time > $startTime + $deltaTime) break;
			
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
		
		$this->summaryData = [];
		$this->summaryData['count'] = $count;
		$this->summaryData['startTime'] = $startTime;
		$this->summaryData['deltaTime'] = $deltaTime;
		$this->summaryData['lineIgnored'] = $this->linesIgnored;
		$this->summaryData['parseDuration'] = $this->durationToParse;
		$this->summaryData['dataCount'] = $count;
		$this->summaryData['minSpeed'] = $minSpeed;
		$this->summaryData['maxSpeed'] = $maxSpeed;
		$this->summaryData['medSpeed'] = $medSpeed;
		$this->summaryData['avgSpeed'] = $avgSpeed;
		$this->summaryData['stdSpeed'] = $deviation;
		$this->summaryData['stdSpeed90'] = $deviation90;
		
		return true;
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
		
		$this->outputData['lineIgnored'] = $this->linesIgnored;
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
	
	
	function computeBins($numBins)
	{
		if ($numBins <= 1) return [];
		
		$minSpeed = $this->minSpeed;
		$minSpeed = 0;
		$range = $this->maxSpeed - $minSpeed;
		if ($range <= 0) return [];
		
		$delta = $range / $numBins;
		$binData = array_fill(0, $numBins, 0.0);
		$xData = array_fill(0, $numBins, 0.0);
		$countData = array_fill(0, $numBins, 0);
		$dataCount = count($this->data);
		
		for ($x = $minSpeed + $delta, $i = 0; $i < $numBins; $x += $delta, $i++)
		{
			$xData[$i] = number_format($x);
		}
		
		foreach ($this->data as $data)
		{
			$binIndex = (floatval($data[1]) - $minSpeed) /$delta;
			if ($binIndex >= $numBins) $binIndex = $numBins - 1;
			$binData[$binIndex] += 100.0 / $dataCount;
			$countData[$binIndex] += 1;
		}
		
		return [$binData, $xData, $countData];
	}
	
	
	function ParseSummary()
	{
		$this->f = @fopen($this->LOGFILE, "rb");
		if ($this->f === false) return $this->ReportError("Failed to open log file '{$this->LOGFILE}'!");
		
		$this->ParseLinesFromEndOfFile();
		fclose($this->f);
		
		$t = $this->currentTime - $this->durationToParse;
		
		do
		{
			$this->ComputeSummaryStats($t, $this->summaryTime);
			$this->OutputSummary($t, $this->summaryTime);
			
			$t += $this->summaryTime;
		} while ($t < $this->currentTime);
		
		
		$this->endScriptTime = microtime(true);
		$this->scriptTimeTaken = ($this->endScriptTime - $this->startScriptTime) * 1000;
		$this->outputData['scriptTimeTaken'] = $this->scriptTimeTaken;
		
		$this->OutputJson();
		return true;
	}
	
	
	function ShowLongTimes()
	{
		$this->f = @fopen($this->LOGFILE, "rb");
		if ($this->f === false) return $this->ReportError("Failed to open log file '{$this->LOGFILE}'!");
		
		$this->ParseLinesFromEndOfFile();
		$this->ComputeStats();
		
		fclose($this->f);
		
		$this->endScriptTime = microtime(true);
		$this->scriptTimeTaken = ($this->endScriptTime - $this->startScriptTime) * 1000;
		
		for ($i = count($this->data); $i >= 0; $i--)
		{
			$data = $this->data[$i];
			if (trim($data[2]) == "") continue;
			
			$time = floatval($data[0]);
			if ($time < $startTime) continue;
			
			++$count;
			$speed = floatval($data[1]);
			$page = $data[2];
			
			$niceDate = date('m/d/Y H:i:s', $time);
			
			if ($speed < $this->showTimesMoreThan) continue;
			
			print("\t\t$niceDate, $speed, $page\n");
		}
		
		return true;
	}
	
	
	function Parse()
	{
		if ($this->showSummary) return $this->ParseSummary();
		if ($this->showTimesMoreThan > 0) return $this->ShowLongTimes();
		
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

