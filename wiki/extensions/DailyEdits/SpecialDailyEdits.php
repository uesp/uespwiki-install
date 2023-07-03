<?php
class SpecialDailyEdits extends SpecialPage {

	public $pageurl;
	public $sortkey;
	public $maxrows;
	public $exportcsv;

        function __construct()
	{
		global $wgArticlePath;

		parent::__construct( 'DailyEdits' );

		$this->pageurl   = str_replace( '$1', 'Special:DailyEdits', $wgArticlePath);
		$this->sortkey   = "none";
		$this->maxrows   = 3660;
		$this->exportcsv = false;
        }
 
        function execute( $par ) 
	{
		$this->getParameters();
		$this->setHeaders();
		$this->showHeader();
		$this->showDailyEdits();
	}

	function getParameters ()
	{
		global $wgRequest; 

		$this->sortkey   = $wgRequest->getText('sort');
		$this->exportcsv = $wgRequest->getText('csv');
	}

	function makePageLink ($var, $value)
	{
		$pagelink = $this->pageurl . '?';

		if ( $var != 'sort' )
			$pagelink .= 'sort='. $this->sortkey .'&';
		else
			$pagelink .= $var .'='. $value .'&';

		if ( $var != 'csv' )
			$pagelink .= 'csv='. $this->exportcsv .'&';
		else
			$pagelink .= $var .'='. $value .'&';

		return $pagelink;
	}

	function showHeader()
	{
		global $wgOut;

		$wgOut->addHTML('<small>');
	
		if ( $this->exportcsv )
		{
			$wgOut->addHTML('<a href="' . $this->makePageLink('csv', '0') . '">Display HTML</a> &nbsp; &nbsp; ');
		}
		else {
			$wgOut->addHTML('<a href="' . $this->makePageLink('csv', '1') . '">Display as CSV</a> &nbsp; &nbsp; ');	
		}

		$wgOut->addHTML("</small><br /><br />\n");
	}

	function showDailyEdits() 
	{
		global $wgOut, $wgDailyEditsGraphFile;

		$sortname = $this->sortkey;
		if ($sortname == "") $sortname = "date";

		$wgOut->addHTML("Displaying count of daily edits sorted by $sortname.<p />\n");

		$sortSQL = 'revdate DESC';

		switch ($this->sortkey)
		{
		case 'editcount':
			$sortSQL = 'editcount DESC';
			break;
		case 'date':
			$sortSQL = 'revdate DESC';
			break;
		}

		$dbw = wfGetDB(DB_SLAVE);

		$where = "";

		$results = $dbw->select('revision', array('rev_timestamp', 'COUNT(*) as editcount', 'LEFT(rev_timestamp,8) as revdate'), $where, __METHOD__, array('GROUP BY' => 'revdate', 'ORDER BY' => $sortSQL) );

		if ($results)
		{
			$wgOut->addHTML("Found ". $results->numRows() ." days of edits...");
			if ($results->numRows() > $this->maxrows) $wgOut->addHTML("showing " . $this->maxrows . " days.");
		}
		else
		{
			$wgOut->addHTML("Found 0 days of edits...");
		}

		if ($this->exportcsv)
		{
			$wgOut->addHTML("<pre>\n");
		} else {
			$wgOut->addHTML("<table class='wikitable'>\n");
			$wgOut->addHTML("<tr>");
			$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'date') . "'>Date</a></th>");
			$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'editcount') . "'>Edit Count</a></th>");

			if ( strlen($wgDailyEditsGraphFile) > 0 )
			{
				$wgOut->addHTML("<td rowspan='1000000' valign='top'>");
				$wgOut->addHTML("<center>The following graph of daily edits is automatically generated once a day:</center><br/>");
				$wgOut->addHTML("<img src='$wgDailyEditsGraphFile' /></td>");
			}

			$wgOut->addHTML("</tr>\n");	
		}

		$count = 0;

		while ($result = $results->next())
		{
			++$count;
			if ($count > $this->maxrows) break;

			$year  = substr($result->revdate, 0, 4);
			$month = substr($result->revdate, 4, 2);
			$day   = substr($result->revdate, 6, 2);

			if ( $this->exportcsv )
			{
				$wgOut->addHTML("$year-$month-$day, $result->editcount\n");
			} else 
			{
				$wgOut->addHTML("<tr>");
				$wgOut->addHTML("<td>$year-$month-$day</td>");
				$wgOut->addHTML("<td align='center'>$result->editcount</td>");
				$wgOut->addHTML("</tr>\n");
			}
		}
 
		if ( $this->exportcsv )
		{
			$wgOut->addHTML("</pre>\n");
		} else 
		{
			$wgOut->addHTML("</table>\n");
		}
        }

}

?>
