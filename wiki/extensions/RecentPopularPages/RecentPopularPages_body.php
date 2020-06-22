<?php
global $IP;

require_once "$IP/includes/specialpage/QueryPage.php";

/** RecentPopularPagesPage extends QueryPage.
 * This does the real work of generating the page contents
 * @package MediaWiki
 * @subpackage SpecialPage
 */
class RecentPopularPagesPage extends SpecialPage 
{
	
	public $pageCountData = array();
	public $pageCountTime = 0;


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
	
	
	function loadPageCountData()
	{
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select("popularPageCounts", array('pageName', 'pageCount'), '', __METHOD__, array('ORDER BY' => 'pageCount DESC', 'LIMIT' => '100'));
		
		$this->pageCountData = array();
		
		foreach( $res as $row ) 
		{
			$name = trim($row->pageName);
			$count = $row->pageCount;
			
			if ($name == '')
				$this->pageCountTime = $count;
			else
				$this->pageCountData[$name] = $count; 	

		}
		
	}
	

	function execute( $par ) 
	{
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		
		$this->loadPageCountData();
		
		$wikiText = "";
				
		if ($this->pageCountTime > 0)
		{
			$hours = number_format($this->pageCountTime / 3600, 1);
			
			$wikiText .= "Showing list of recently popular pages in the past $hours hours...";
		}
		else
		{
			$wikiText .= "Showing list of recently popular pages...";
		}
		
		$wikiText .= "<ol>";
		
		foreach ($this->pageCountData as $name => $count)
		{
			$nameParts = explode('?', $name, 2);
			$name = $nameParts[0];
			$query = $nameParts[1];
			$title = Title::newFromText($name, 0);
			
			if ($query) {
				$wikiText .= "<li><span class=\"plainlinks\">[//en.uesp.net/wiki/$name?$query $name?$query]</span> has $count views</li>";
			}
			else if ($title) {
				$wikiText .= "<li>[[:$name]] has $count views</li>";
			}
			else {
				$wikiText .= "<li>$name has $count views</li>";
			}
			
		}

		$wikiText .= "</ol>";
		$output->addWikiText( $wikiText );
	}
	
}
