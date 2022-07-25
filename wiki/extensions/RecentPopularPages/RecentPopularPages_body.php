<?php
global $IP;

require_once "$IP/includes/specialpage/QueryPage.php";

/** RecentPopularPagesPage extends SpecialPage.
 * This does the real work of generating the page contents
 * @package MediaWiki
 * @subpackage SpecialPage
 */
class RecentPopularPagesPage extends SpecialPage 
{
	
	public $pageCountData = array();
	public $pageNames = array();
	
	public $showSummary = false;
	public $pageFilter = "";
	public $pageCountDays = 1;				// Time range in days
	public $pageCountLimit = 2500;
	public $lastParseTimestamp = -1;
	
	
	function __construct( $name = 'RecentPopularPages' )
	{
		parent::__construct( $name );
	}
	
	
	function getName()
	{
		return 'RecentPopularPages';
	}
	
	
	function isCacheable()
	{
		return false;
	}
	
	
	function isExpensive()
	{
		return false;
	}
	
	
	function isSyndicated()
	{
		return false;
	}
	
	
	function getGroupName()
	{
		return 'pages';
	}
	
	
	function parseParameters()
	{
		$request = $this->getRequest();
		$days = $request->getVal('days');
		
		if ($days != null)
		{
			$this->pageCountDays = floatval($days);
			if ($this->pageCountDays <= 0) $this->pageCountDays = 1;
		}
		
		$filter = $request->getVal('filter');
		if ($filter) $this->pageFilter = trim($filter);
		
		if ($request->getVal('summary') > 0) $this->showSummary = true;
	}
	
	
	function loadPageInfoData()
	{
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select("popularPageInfo", '*');
		
		foreach( $res as $row )
		{
			$key = $row->k;
			$value = $row->v;
			
			if ($key == 'lastParseTimestamp')
			{
				$this->lastParseTimestamp = intval($value);
			}
		}
	}
	
	
	function loadPageCountData()
	{
		$dbr = wfGetDB( DB_SLAVE );
		
		$pageDays = $this->pageCountDays;
		
		$where = array("pageDate >= NOW() - INTERVAL $pageDays DAY");
		if ($this->pageFilter) $where[] = "pageName LIKE ".$dbr->addQuotes('%'.$this->pageFilter.'%')."";
		
		$res = $dbr->select("popularPageCounts", array('pageName', 'pageDate', 'pageCount'), $where, __METHOD__, array('LIMIT' => $this->pageCountLimit));
		
		$this->pageCountData = array();
		
		foreach( $res as $row )
		{
			$name = $row->pageName;
			$count = $row->pageCount;
			$lowerName = strtolower($name);
			
			$this->pageNames[$lowerName][$name] += 1;
			$this->pageCountData[$lowerName] += $count;
		}
		
		arsort($this->pageCountData);
		
		$this->fixPageNames();
	}
	
	
	function loadPageSummaryData()
	{
		$dbr = wfGetDB( DB_SLAVE );
		
		$pageDays = $this->pageCountDays;
		
		$where = array("pageDate >= NOW() - INTERVAL $pageDays DAY");
		if ($this->pageFilter) $where[] = "pageName LIKE ".$dbr->addQuotes('%'.$this->pageFilter.'%')."";
		
		$res = $dbr->select("popularPageSummaries", array('pageName', 'pageDate', 'pageCount'), $where, __METHOD__, array('LIMIT' => $this->pageCountLimit));
		
		$this->pageCountData = array();
		
		foreach( $res as $row )
		{
			$name = $row->pageName;
			$count = $row->pageCount;
			$lowerName = strtolower($name);
			
			$this->pageNames[$lowerName][$name] += 1;
			$this->pageCountData[$lowerName] += $count;
		}
		
		arsort($this->pageCountData);
		
		$this->fixPageNames();
	}
	
	
	function findMostUsedName($nameCounts)
	{
		if ($nameCounts == null) return "";
		
		$maxName = "";
		$maxCounts = -1;
		
		foreach ($nameCounts as $name => $count)
		{
			if ($count > $maxCounts)
			{
				$maxName = $name;
				$maxCounts = $count;
			}
		}
		
		return $maxName;
	}
	
	
	function fixPageNames()
	{
		$countData = $this->pageCountData;
		$this->pageCountData = array();
		
		foreach ($countData as $lowerName => $count)
		{
			$name = $this->findMostUsedName($this->pageNames[$lowerName]);
			$this->pageCountData[$name] += $count;
		}
	}
	
	
	function getNavLinks()
	{
		$links = "<div id='popularPageLinks'>";
		
		$days = $this->pageCountDays;
		
		$filter = "";
		if ($this->pageFilter) $filter = "&filter=".urlencode($this->pageFilter);
		
		if ($this->showSummary)
		{
			$links .= "<a href='?days=$days$filter'>View Pages</a> : ";
			$links .= "<a href='?days=1&summary=1$filter'>1 Day</a> : ";
			$links .= "<a href='?days=7&summary=1$filter'>7 Days</a> : ";
			$links .= "<a href='?days=30&summary=1$filter'>30 Days</a> : ";
			$links .= "<a href='?days=182&summary=1$filter'>6 Months</a> : ";
			$links .= "<a href='?days=365&summary=1$filter'>1 Year</a> : ";
		}
		else
		{
			$links .= "<a href='?days=$days&summary=1$filter'>View Summaries</a> : ";
			$links .= "<a href='?days=1$filter'>1 Day</a> : ";
			$links .= "<a href='?days=7$filter'>7 Days</a> : ";
			$links .= "<a href='?days=30$filter'>30 Days</a> : ";
			$links .= "<a href='?days=182$filter'>6 Months</a> : ";
			$links .= "<a href='?days=365$filter'>1 Year</a> : ";
		}
		
		$links .= "<form style='display:inline;' method='get' name='popularPageFilterForm'>";
		$links .= "<input hidden='text' value='$days' name='days'>";
		if ($this->showSummary) $links .= "<input hidden='text' value='{$this->showSummary}' name='summary'>";
		
		$filter = htmlspecialchars($this->pageFilter, ENT_QUOTES);
		$links .= "<input type='text' value='$filter' name='filter' size='12'>";
		
		$links .= "<input type='submit' value='Filter'>";
		$links .= "</form>";
		
		$links .= "</div>";
		return $links;
	}
	
	
	function execute( $par ) 
	{
		$this->parseParameters();
		
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		
		$this->loadPageInfoData();
		
		if ($this->showSummary)
			$this->loadPageSummaryData();
		else
			$this->loadPageCountData();
		
		$output->addHTML($this->getNavLinks());
		$wikiText = "";
		
		$category = "list of recent popular pages";
		if ($this->showSummary) $category = "summary of recent popular pages";
		
		if ($this->pageCountDays > 0)
		{
			$wikiText .= "<p/>Showing $category in the past {$this->pageCountDays} days.";
		}
		else
		{
			$wikiText .= "<p/>Showing $category.";
		}
		
		if ($this->lastParseTimestamp > 0)
		{
			$fmtDate = date("r", $this->lastParseTimestamp);
			$wikiText .= " Page count data last updated on $fmtDate.";
		}
		
		$wikiText .= "<p/><ol>";
		
		if (count($this->pageCountData) == 0)
		{
			$wikiText .= "<li>No page data found!</li>";
		}
		
		foreach ($this->pageCountData as $name => $count)
		{
			$count = number_format($count);
			$safeName = htmlspecialchars($name, ENT_QUOTES);
			
			if ($this->showSummary)
			{
				if (strpos($name, ":") !== false)
					$title = null;
				else
					$title = Title::newFromText($name . ":" . $name, 0);
			}
			else
			{
				$title = Title::newFromText($name, 0);
			}
			
			if ($title && $title->isKnown())
			{
				if ($this->showSummary)
					$wikiText .= "<li>'''[[$safeName:$safeName]]''' has $count views</li>";
				else
					$wikiText .= "<li>'''[[:$safeName]]''' has $count views</li>";
			}
			else 
			{
				$wikiText .= "<li>'''$safeName''' has $count views</li>";
			}
			
		}
		
		$wikiText .= "</ol>";
		$output->addWikiText( $wikiText );
	}
	
}
