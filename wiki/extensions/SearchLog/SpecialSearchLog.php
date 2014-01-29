<?php
class SpecialSearchLog extends SpecialPage {

	public $pageurl;
	public $sortkey;
	public $maxrows;
	public $summary;
	public $empty;

        function __construct() {
                parent::__construct( 'SearchLog' );
                wfLoadExtensionMessages('SearchLog');

			# TODO: Proper URL of this page
		$this->pageurl = "/wiki/Special:SearchLog";
		$this->sortkey = "none";
		$this->maxrows = 1000;
		$this->summary = 0;
		$this->empty   = 0;
        }
 
        function execute( $par ) {
                global $wgRequest, $wgOut, $wgUser;
 
                $this->setHeaders();

		if ( !$wgUser->isAllowed( 'patrol' ) )
		{
			$wgOut->permissionRequired( 'patrol' );
		        return;
		}

		$this->showHeader();
 
         	$this->summary = $wgRequest->getText('summary');
		$this->sortkey = $wgRequest->getText('sort');
		$this->empty   = $wgRequest->getText('empty');

		if ($this->summary)
			$this->showSummary();
		else
			$this->showLog();
	}

	function makePageLink ($var, $value)
	{
		$pagelink = $this->pageurl . '?';

		if ( $var != 'summary' )
			$pagelink .= 'summary='. $this->summary .'&';
		else
			$pagelink .= $var .'='. $value .'&';
		
		if ( $var != 'sort' )
			$pagelink .= 'sort='. $this->sortkey .'&';
		else
			$pagelink .= $var .'='. $value .'&';

		if ( $var != 'empty' )
			$pagelink .= 'empty='. $this->empty .'&';
		else
			$pagelink .= $var .'='. $value .'&';

		return $pagelink;
	}

	function showHeader()
	{
		global $wgOut, $ArticlePath;

		$wgOut->addHTML('<small>');
		$wgOut->addHTML('<a href="' . $this->makePageLink('summary', '0') . '">Display Log</a> &nbsp; &nbsp; ');
		$wgOut->addHTML('<a href="' . $this->makePageLink('empty',   '1') . '">Display Empty Log</a> &nbsp; &nbsp; ');
		$wgOut->addHTML('<a href="' . $this->makePageLink('summary', '1') . '">Display Summary</a>');
		$wgOut->addHTML("</small><br />\n");
	}

	function showSummary()
	{
		global $wgOut;

		$wgOut->addHTML("Displaying accumulated summary of search queries ordered by $this->sortkey.<p />\n");

		$dbw = wfGetDB(DB_SLAVE);
		$sortSQL = 'term ASC';

		switch ($this->sortkey)
		{
		case 'term':
			$sortSQL = 'term';
			break;
		case 'count':
			$sortSQL = 'count DESC';
			break;
		}

		$log = $dbw->select('searchlog_summary', array('term', 'count'), '', __METHOD__, array('ORDER BY' => $sortSQL));

		if ($log)
			$wgOut->addHTML("Found ". $log->numRows() ." unique search queries...");
		else
			$wgOut->addHTML("Found 0 unique search queries...");

		$wgOut->addHTML("<table class='wikitable'>\n");
		$wgOut->addHTML("<tr>");
		$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'term') . "'>Search Term(s)</a></th>");
		$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'count') ."'>Count</a></th>");
		$wgOut->addHTML("</tr>\n");

		$count = 0;

		while ($result = $log->next())
		{
			++$count;
			if ($count > $this->maxrows) break;

			$wgOut->addHTML("<tr>");
			$wgOut->addHTML("<td>$result->term</td>");
			$wgOut->addHTML("<td>$result->count</td>");
			$wgOut->addHTML("</tr>\n");
		}
 
                $wgOut->addHTML("</table>\n");
	}

	function showLog () 
	{
		global $wgOut;

		$sortname = $this->sortkey;
		if ($sortname == "") $sortname = "date";

		if ($this->empty)
			$wgOut->addHTML("Displaying log of empty search queries ordered by $sortname.<p />\n");
		else
			$wgOut->addHTML("Displaying log of search queries ordered by $sortname.<p />\n");


		$sortSQL = 'searchdate DESC';

		switch ($this->sortkey)
		{
		case 'term':
			$sortSQL = 'term';
			break;
		case 'titlecount':
			$sortSQL = 'titlecount ASC';
			break;
		case 'textcount':
			$sortSQL = 'textcount ASC';
			break;
		case 'date':
			$sortSQL = 'searchdate DESC';
			break;
		case 'timetaken':
			$sortSQL = 'searchtime DESC';
			break;
		}

		$dbw = wfGetDB(DB_SLAVE);

		if ($this->empty)
			$where = "titlecount=0 AND textcount=0";
		else
			$where = "";

		$log = $dbw->select('searchlog', array('term', 'titlecount', 'textcount', 'searchdate', 'searchtime'), $where, __METHOD__, array('ORDER BY' => $sortSQL));

		if ($log)
		{
			$wgOut->addHTML("Found ". $log->numRows() ." search queries...");
			if ($log->numRows() > $this->maxrows) $wgOut->addHTML("showing latest " . $this->maxrows . " rows.");
		}
		else
		{
			$wgOut->addHTML("Found 0 search queries...");
		}

		$wgOut->addHTML("<table class='wikitable'>\n");
		$wgOut->addHTML("<tr>");
		$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'term') . "'>Search Term(s)</a></th>");
		$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'titlecount') . "'>Title Count</a></th>");
		$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'textcount') . "'>Text Count</a></th>");
		$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'date') . "'>Search Date</a></th>");
		$wgOut->addHTML("<th><a href='". $this->makePageLink('sort', 'timetaken') . "'>Time Taken</a></th>");
		$wgOut->addHTML("</tr>\n");

		$count = 0;
		$sumtime = 0;
		$sumcount = 0;

		while ($result = $log->next())
		{
			++$count;
			if ($count > $this->maxrows) break;

			$wgOut->addHTML("<tr>");
			$wgOut->addHTML("<td>$result->term</td>");
			$wgOut->addHTML("<td>$result->titlecount</td>");
			$wgOut->addHTML("<td>$result->textcount</td>");
			$wgOut->addHTML("<td>$result->searchdate</td>");
			$wgOut->addHTML("<td>$result->searchtime</td>");
			$wgOut->addHTML("</tr>\n");

			if ($result->searchtime > 0) 
			{
				$sumtime += $result->searchtime;
				$sumcount++;
			}
		}
 
		if ($sumcount > 0)
		{
			$avgtime = round( $sumtime / $sumcount * 1000 );
			$wgOut->addHTML("<tr><td colspan='4' align='right'><b>Average search time:</b></td><td><b>$avgtime ms</b></td></tr>");
		}

		$wgOut->addHTML("</table>\n");
        }

}

?>
