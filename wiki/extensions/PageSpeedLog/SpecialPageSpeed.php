<?php
/*
 * TODO:

 */


if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is a MediaWiki extension and must be run from within MediaWiki.' );
}


require_once(dirname(__FILE__) . "/scripts/ComputePageSpeedStats.class.php");


class SpecialPageSpeed extends SpecialPage 
{
	public $computeStats = null;
	
	public $NUM_GRAPH_BINS = 20;
	
	
	function __construct()
	{
		global $wgPageSpeedLogFile;
		
		parent::__construct( 'PageSpeed' );
		
		$this->computeStats = new ComputePageSpeedStats();
		$this->computeStats->LOGFILE = $wgPageSpeedLogFile;
	}
	
	
	function parseInputParams($par)
	{
		$request = $this->getRequest();
		$duration = $request->getText('time');
		$timeother = $request->getText('timeother');
		$ignoreTimes = $request->getText('ignoretimes');
		
		if ($duration == "other" && $timeother != null)
		{
			$duration = intval($timeother);
			if ($duration > 0) $this->computeStats->durationToParse = $duration;
		}
		elseif ($duration != null)
		{
			$duration = intval($duration);
			if ($duration > 0) $this->computeStats->durationToParse = $duration;
		}
		else if ($timeother != null)
		{
			$duration = intval($timeother);
			if ($duration > 0) $this->computeStats->durationToParse = $duration;
		}
		
		if ($ignoreTimes != null)
		{
			$ignoreTimes = floatval($ignoreTimes);
			if ($ignoreTimes > 0) $this->computeStats->ignoreTimesMoreThan = $ignoreTimes;
		}
	}
	
	
	function execute( $par )
	{
		$this->parseInputParams($par);
		
		$request = $this->getRequest();
		$output = $this->getOutput();
		
		$this->setHeaders();
		$output->addModules( 'ext.PageSpeed.modules' );
		
		//$this->computeStats->echo = true;
		$this->computeStats->Parse();
		
		$server = gethostname();
		$numLines = $this->computeStats->numLinesFound;
		$ignoredLines = $this->computeStats->linesIgnored;
		$parseDuration = intval($this->computeStats->durationToParse);
		
		if ($numLines <= 1 || $parseDuration <= 0)
		{
			if ($ignoredLines > 0)
				$output->addHTML("WARNING: Only $numLines log lines (ignored $ignoredLines lines slower than {$this->computeStats->ignoreTimesMoreThan} ms) found from the server $server over $parseDuration sec.");
			else
				$output->addHTML("WARNING: Only $numLines log lines found from the server $server over $parseDuration sec.");
			
			return;
		}
		
		$minSpeed = number_format($this->computeStats->minSpeed, 0);
		$maxSpeed = number_format($this->computeStats->maxSpeed, 0);
		$avgSpeed = number_format($this->computeStats->avgSpeed, 0);
		$stdSpeed = number_format($this->computeStats->stdSpeed, 0);
		$medSpeed = number_format($this->computeStats->medSpeed, 0);
		$stdSpeed90 = number_format($this->computeStats->stdSpeed90, 0);
		$scriptTimeTaken = number_format($this->computeStats->scriptTimeTaken, 0);
		
		$this->outputForm();
		$output->addHTML("Showing stats from the page render speed log.");
		
		$output->addHTML("<p><h2>Summary</h2>");
		$output->addHTML("Showing stats from the page render speed log from the server $server.");
		$output->addHTML("<ul>");
		
		if ($ignoredLines > 0)
			$output->addHTML("<li>Found $numLines lines (ignored $ignoredLines lines slower than {$this->computeStats->ignoreTimesMoreThan} ms) from last $parseDuration sec</li>");
		else
			$output->addHTML("<li>Found $numLines lines from last $parseDuration sec</li>");
		
		$output->addHTML("<li>Speed Range: $minSpeed to $maxSpeed ms</li>");
		$output->addHTML("<li>Average Speed: $avgSpeed ms</li>");
		$output->addHTML("<li>Median Speed: $medSpeed ms</li>");
		$output->addHTML("<li>Standard Deviation: $stdSpeed ms</li>");
		$output->addHTML("<li>90%: $stdSpeed90 ms</li>");
		$output->addHTML("<li>Log Parse Time: $scriptTimeTaken ms (using log file <em>{$this->computeStats->LOGFILE}</em>)</li>");
		$output->addHTML("</ul>");
		
		$this->outputGraph();
	}
	
	
	function outputForm()
	{
		$request = $this->getRequest();
		$output = $this->getOutput();
		
		$ignoreTimes = $this->computeStats->ignoreTimesMoreThan;
		if ($ignoreTimes <= 0) $ignoreTimes = "";
		
		$check1 = ($this->computeStats->durationToParse == 60) ? "checked" : "";
		$check2 = ($this->computeStats->durationToParse == 600) ? "checked" : "";
		$check3 = ($this->computeStats->durationToParse == 3600) ? "checked" : "";
		$check4 = ($this->computeStats->durationToParse == 36000) ? "checked" : "";
		$check5 = ($this->computeStats->durationToParse == 86400) ? "checked" : "";
		$check6 = ($check1 || $check2 || $check3 || $check4 || $check5) ? "" : "checked";
		
		$output->addHTML("<form method='get' id='psInputForm'>");
		$output->addHTML("<div class='psInputBox'>Show for the Last ");
		$output->addHTML("<label class='psInputTime'><input type='radio' name='time' value='60' $check1>60 sec</label> ");
		$output->addHTML("<label class='psInputTime'><input type='radio' name='time' value='600' $check2>10 min</label> ");
		$output->addHTML("<label class='psInputTime'><input type='radio' name='time' value='3600' $check3>1 hour</label> ");
		$output->addHTML("<label class='psInputTime'><input type='radio' name='time' value='36000' $check4>10 hours</label> ");
		$output->addHTML("<label class='psInputTime'><input type='radio' name='time' value='86400' $check5>1 day</label> ");
		$output->addHTML("<label class='psInputTime'><input type='radio' name='time' value='other' $check6>Other</label> ");
		$output->addHTML("<label class='psInputTime'><input type='text' name='timeother' value='{$this->computeStats->durationToParse}' placeholder='...' maxlength='10' size='5'> secs</label> ");
		$output->addHTML("</div>");
		$output->addHTML("<div class='psInputBox'>");
		$output->addHTML("<label class='psInputTime'>Ignore Times More Than <input type='text' name='ignoretimes' value='{$ignoreTimes}' placeholder='...' maxlength='10' size='5'> ms</label>");
		$output->addHTML("</div>");
		$output->addHTML(" &nbsp; <input type='submit' value='Update'>");
		$output->addHTML("</form>");
	}
	
	
	function outputGraph()
	{
		list($binData, $xData, $countData) = $this->computeStats->computeBins($this->NUM_GRAPH_BINS);
		$output = $this->getOutput();
		$parseDuration = intval($this->computeStats->durationToParse);
		
		$output->addHTML("<p><h2>Graph</h2>");
		$output->addHTML("Showing the distribution of page render times in the last $parseDuration secs.");
		$output->addHTML("<div id='pageSpeedGraphRoot'>");
		$output->addHTML("<div class='pageSpeedGraph'>");
		
		$output->addHTML("<div class='psGraphBar psGraphBarHidden' style=\"--bar-value:100%;\" data-name=\"\" data-bin=\"\" title=\"\"></div>");
		
		for ($i = 0; $i < count($xData); $i++)
		{
			$x = $xData[$i];
			$bin = $binData[$i];
			$count = $countData[$i];
			
			$niceBin = number_format(floatval($bin), 2);
			$niceX = $x . "ms";
			
			$output->addHTML("<div class='psGraphBar' style=\"--bar-value:$niceBin%;\" data-name=\"$niceX\" data-bin=\"$count\" title=\"$niceBin%\"></div>");
		}
		
		$output->addHTML("</div>");
		$output->addHTML("</div>");
		$lastX = 0;
		
		$output->addHTML("<p><h2>Raw Data</h2>");
		
		for ($i = 0; $i < count($xData); $i++)
		{
			$x = $xData[$i];
			$bin = $binData[$i];
			$niceBin = number_format(floatval($bin), 2);
			$count = $countData[$i];
			
			$output->addHTML("<p>$lastX - $x ms = $count ($niceBin%)</p>");
			$lastX = $x;
		}
		
		if ($this->computeStats->linesIgnored > 0)
		{
			$niceBin = number_format(100 * $this->computeStats->linesIgnored / $this->computeStats->numLinesFound, 2);
			$x = $this->computeStats->ignoreTimesMoreThan;
			$count = $this->computeStats->linesIgnored;
			$output->addHTML("<p>&gt; $x ms = $count ($niceBin%) Ignored</p>");
		}
	}
	
}	
