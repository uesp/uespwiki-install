<?php


use MediaWiki\MediaWikiServices;
use Seld\JsonLint\JsonParser;


class WikiAppApi extends ApiBase
{
		//TODO: Set from wiki config?
	const REMOVE_JSON_COMMENTS = true;
	const INCLUDE_NEWS_CONTENT = true;
	const CHECK_JSON_ONPAGELOAD = true;
	const USE_JSON_LINT = true;
	
		// Page in PROJECT namespace that contains the JSON data
	const APPHOMEPAGE = "AppManifest";
	
	public static $parser = null;
	public static $parserOptions = null;
	public static $projectNS = null;
	
	
	public function __construct($mainModule, $moduleName, $modulePrefix = '') 
	{
		parent::__construct($mainModule, $moduleName, $modulePrefix);
	}
	
	
	public function outputError($msg)
	{
		$apiResult = $this->getResult();
		
		$apiResult->addValue( null, "error", $msg );
	}
	
	
	public function createParser()
	{
		global $wgUser;
		
		if (class_exists('ParserFactory'))
			$this->parser = MediaWikiServices::getInstance()->getParserFactory()->create();
		else
			$this->parser = new Parser;
		
		$this->parserOptions = new ParserOptions(new User( '127.0.0.1' ));
	}
	
	
	public static function getProjectNamespace()
	{
		if (self::$projectNS) return self::$projectNS;
		
		global $wgContLang;
		
		if ($wgContLang)
		{
			self::$projectNS = $wgContLang->getFormattedNsText( NS_PROJECT );
		}
		else
		{
			self::$projectNS = MediaWikiServices::getInstance()->getContentLanguage()->getFormattedNsText( NS_PROJECT );
		}
		return self::$projectNS;
	}
	
	
	public static function getPageText($textTitle)
	{
		$title = Title::newFromText($textTitle);
		$page = WikiPage::factory($title);
		
		if (class_exists('Revision'))
			$content = $page->getContent(Revision::RAW);
		else
			$content = $page->getContent(MediaWiki\Revision\RevisionRecord::RAW);
		
		$text = ContentHandler::getContentText($content);
		
		return $text;
	}
	
	
	public function getPageHtml($textTitle)
	{
		$title = Title::newFromText($textTitle);
		$page = WikiPage::factory($title);
		
		if (class_exists('Revision'))
			$content = $page->getContent(Revision::RAW);
		else
			$content = $page->getContent(MediaWiki\Revision\RevisionRecord::RAW);
		
		$text = ContentHandler::getContentText($content);
		
		if ($text == null) return "";
		
		if ($this->parser == null) $this->createParser();
		
		$parserOutput = $this->parser->parse( $text, $title, $this->parserOptions );
		$html = $parserOutput->getText();
		
		return $html;
	}
	
	
	public function getPageTextAndHtml($textTitle)
	{
		$title = Title::newFromText($textTitle);
		$page = WikiPage::factory($title);
		
		if (class_exists('Revision'))
			$content = $page->getContent(Revision::RAW);
		else
			$content = $page->getContent(MediaWiki\Revision\RevisionRecord::RAW);
		
		$text = ContentHandler::getContentText($content);
		
		if ($text == null) return array("", "");
		
		if ($this->parser == null) $this->createParser();
		
		$parserOutput = $this->parser->parse( $text, $title, $this->parserOptions );
		$html = $parserOutput->getText();
		
		return array($text, $html);
	}
	
	
	public function convertWikiTextToHtml($text)
	{	//Converts a fragment of wiki text into HTML
		
		if ($this->parser == null) $this->createParser();
		
		$parserOutput = $this->parser->parse( $text, Title::newFromText("Fragment"), $this->parserOptions );
		$html = $parserOutput->getText();
		
		return $html;
	}
	
	
	public static function removeJsonComments($text)
	{
		return preg_replace('~(" (?:\\\\. | [^"])*+ ") | // [^\v]*+ | /\* .*? \*/~xs', '$1', $text);
	}
	
	
	public function getImageFileUrl($imageFile)
	{	//$imageFile is an image article name with or without the leading "File:" namespace
		
		$imageFile = preg_replace('#^File:#', '', $imageFile);
		
		//MediaWikiServices::getInstance()->getRepoGroup()->findFile() in 1.38+
		
		$mwFile = wfFindFile($imageFile);
		if (!$mwFile) return "";
		return $mwFile->getFullUrl();
	}
	
	
	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ) 
	{
		if (!self::CHECK_JSON_ONPAGELOAD) return;
		
		$projectNS = self::getProjectNamespace();
		if ($out->getPageTitle() != "$projectNS:" . self::APPHOMEPAGE) return;
		
		$json = self::parseJsonFromPage($errorMsg, true);
		
		if ($json == null) 
			$out->addHTML("<hr><div style='background-color:#ffcccc; color:#000000; padding:5px;'>Error: $errorMsg</div>");
		else
			$out->addHTML("<hr><div style='background-color:#ccffcc; color:#000000; padding:5px;'>Page/JSON Format OK!</div>");
		
	}
	
	
	public function loadNewsPageContent($textTitle, &$content)
	{
		list($text, $html) = $this->getPageTextAndHtml($textTitle);
		if ($text == null || $html == null) return false;
		
		$isMatched = preg_match('#\[\[(File:[^\[|{]+)#', $text, $matches);
		
		if ($isMatched)
		{
			$imageUrl = $this->getImageFileUrl($matches[1]);
			if ($imageUrl) $content['thumbnail'] = $imageUrl;
		}
		
		$content['content'] = $html;
		
		$isMatched = preg_match("#date=(.*)#", $text, $matches);
		
			//TODO: Check for other date formats?
		if ($isMatched) 
		{
			$strDate = $matches[1];
			$strDate = str_replace(',', ' ', $strDate);
			$strDate = str_replace('  ', ' ', $strDate);
			$dt = DateTime::createFromFormat('F j Y', $strDate);
			if ($dt) $content['date'] = $dt->getTimestamp();
		}
		
		//TODO: Get snippet from content? Author? Category?
		
		return true;
	}
	
	
	public function getGenericContent($widget)
	{
		$content = [];
		
		$templateContent = $widget['template_content'];
		if ($templateContent == null) return $content;
		
		$content = $this->getPageHtml($templateContent);
		
		return $content;
	}
	
	
	public function getNewsContent($widget)
	{
		$currentPage = $widget['wikiPage'];
		if ($currentPage == null) $currentPage = $widget['wiki_page'];
		if ($currentPage == null) $currentPage = $this->getProjectNamespace() . ":News";
		
		$content = [];
		
		$newsText = $this->getPageText($currentPage);
		if ($newsText == null || $newsText == "") return $content;
		
		$isMatched = preg_match('#<onlyinclude>(.*)</onlyinclude>#s', $newsText, $matches);
		$newsSection = $newsText;
		if ($isMatched) $newsSection = $matches[1];
		
		$isMatched = preg_match_all('#{{' . preg_quote($currentPage) . '/(.*)}}#', $newsSection, $matches, PREG_PATTERN_ORDER);
		if (!$isMatched) return $content;
		
		foreach ($matches[1] as $match)
		{
			$link = $currentPage . "/" . $match;
			
			$newContent = [
					'title' => $match,
					'link' => $link,
					'date' => 0,
					'author' => "",
					'category' => "",
			];
			
			if (self::INCLUDE_NEWS_CONTENT) $this->loadNewsPageContent($link, $newContent);
			
			$content[] = $newContent;
		}
		
		return $content;
	}
	
	
	public function getTriviaContent($widget)
	{
		$currentPage = $widget['wikiPage'];
		if ($currentPage == null) $currentPage = $widget['wiki_page'];
		if ($currentPage == null) $currentPage = "Main_Page/Did_You_Know_Transclusion";
		
		$content = [];
		
		$triviaHtml = $this->getPageHtml($currentPage);
		if ($triviaHtml == null || $triviaHtml == "") return $content;
		
		$isMatched = preg_match_all('#<li>(.*?)</li>#', $triviaHtml, $matches, PREG_PATTERN_ORDER);
		if (!$isMatched) return $content;
		
		foreach ($matches[1] as $match)
		{
			$content[] = trim($match);
		}
		
		return $content;
	}
	
	
	public function getFeaturedArticleContent($widget)
	{
		$currentPage = $widget['wikiPage'];
		if ($currentPage == null) $currentPage = $widget['wiki_page'];
		if ($currentPage == null) $currentPage = "Main Page/Featured Article";
		
		$currentFA = $this->getPageText($currentPage);
		if (!$currentFA) return $content;
		
		$isMatched = preg_match('#\[\[([^\]]+)\]\]#', $currentFA, $matches);
		if (!$isMatched) return $content;
		
		$articleLink = "";
		$pageUrl = "";
		$imageUrl = "";
		$imageCaption = "";
		$snippet = "";
		
		$articleLink = $matches[1];
		$title = preg_replace('#^.*:#', '', $articleLink);
		$mwtitle = Title::newFromText($articleLink);
		$pageUrl = $mwtitle->getFullURL();
		
		$content = [
			'title' => $articleLink,	//TODO: Confirm full article name with namespace or just the article name
			'pageURL' => $pageUrl,
		];
		
		$isMatched = preg_match('#<caption>(.*)</caption>#s', $currentFA, $matches);
		if ($isMatched)
		{
			$snippet = trim($matches[1]);
			if ($snippet) $content['snippet'] = $this->convertWikiTextToHtml($snippet);
		}
		
			//[[File:SR-npc-Serana.jpg|thumb|center|{{Center|'''Serana'''}}]]
		$isMatched = preg_match('#\[\[(File:[^\]]+)\]\]#', $currentFA, $matches);
		if (!$isMatched) return $content;
		
		$line = $matches[1];
		$fileMatched = preg_match('#File:(.*?)\|(?:.*)\|?{{(?:.*\|)?(.*)}}#', $line, $matches);
		if (!$fileMatched) $fileMatched = preg_match('#File:(.*?)\|(?:.*\|)?(?:.*\|)?(.*)$#', $line, $matches);
		if (!$fileMatched) $fileMatched = preg_match('#File:(.*)#', $line, $matches);
		
		if ($fileMatched)
		{
			$imageFile = $matches[1];
			$imageCaption = $matches[2];
			if ($imageCaption == null) $imageCaption = "";
			
			$imageCaption = str_replace("'''", "", $imageCaption);
			$imageCaption = str_replace("''", "", $imageCaption);
			
			$imageUrl = $this->getImageFileUrl($imageFile);
			if ($imageUrl) $content['pageImageUrl'] = $imageUrl;
		}
		
		return $content;
	}
	
	
	public function getFeaturedImageContent($widget)
	{
		$currentPage = $widget['wikiPage'];
		if ($currentPage == null) $currentPage = $widget['wiki_page'];
		if ($currentPage == null) $currentPage = "Main Page/Featured Image";
		
		$content = [];
		
		$currentFI = $this->getPageText($currentPage);
		if (!$currentFI) return $content;
		
			//[[File:SR-npc-Serana.jpg|thumb|center|{{Center|'''Serana'''}}]]
		$isMatched = preg_match('#\[\[(File:[^\]]+)\]\]#', $currentFI, $matches);
		if (!$isMatched) return $content;
		
		$line = $matches[1];
		$fileMatched = preg_match('#File:(.*?)\|(?:.*)\|?{{(?:.*\|)?(.*)}}#', $line, $matches);
		if (!$fileMatched) $fileMatched = preg_match('#File:(.*?)\|(?:.*\|)?(?:.*\|)?(.*)$#', $line, $matches);
		if (!$fileMatched) $fileMatched = preg_match('#File:(.*)#', $line, $matches);
		
		$file = "";
		$imageUrl = "";
		$caption = "";
		
		if ($fileMatched)
		{
			$file = $matches[1];
			$caption = $matches[2];
			if ($caption == null) $caption = "";
			
			$caption = str_replace("'''", "", $caption);
			$caption = str_replace("''", "", $caption);
			
			$imageUrl = $this->getImageFileUrl($file);
		}
		
		$content = [
			'imageURL' => $imageUrl,
			'imagePageURL' => "File:" . $file,
		];
		
		if ($caption) $content['caption'] = $caption;
		
		return $content;
	}
	
	
	//TODO: Remove if not needed
	public function getFeaturedImageHistory($widget)
	{
		$content = [];
		
		$historyPage = $widget['history_page'];
		if ($historyPage == null) return $content;
		
		$text = $this->getPageText($historyPage);
		if ($text == null) return $content;			//TODO: Error or default value?
		
		$isMatched = preg_match('#<gallery>(.*)</gallery>#s', $text, $matches);	//TODO: Different FI page formats?
		if (!$isMatched) return $content;
		
		$gallery = $matches[1];
		$lines = explode("\n", $gallery);
		
		foreach ($lines as $line)
		{
			$line = trim($line);
			if ($line == "") continue;
			
			$line = preg_replace('/[|].*$/', '', $line);
			$file = str_replace("File:", "", $line);
			$imageUrl = '';
			
			$imageUrl = $this->getImageFileUrl($file);
			
			$content[] = [
				'imageURL' => $imageUrl,
				'imagePageURL' => $line,
			];
		}
		
		return $content;
	}
	
	
	public function replaceJsonContent(&$json)
	{
		$homepage = &$json['homepage'];
		
		foreach ($homepage as $i => &$widget)
		{
			$type = $widget['type'];
			
			switch ($type)
			{
				case "card_featured_image":
					$widget['content'] = $this->getFeaturedImageContent($widget);
					break;
				case "card_featured_article":
					$widget['content'] = $this->getFeaturedArticleContent($widget);
					break;
				case "trivia_box":
				case "card_trivia":
					$widget['content'] = $this->getTriviaContent($widget);
					break;
				case "card_news":
					$widget['content'] = $this->getNewsContent($widget);
					break;
				case "card_generic":
					$widget['content'] = $this->getGenericContent($widget);
					break;
			}
		}
		
		return $text;
	}
	
	
	public static function parseJsonFromPage(&$errorMsg, $formatErrorHtml = false)
	{
		$projectNS = self::getProjectNamespace();
		$text = self::getPageText("$projectNS:" . self::APPHOMEPAGE);
		
		if ($text == null) 
		{
			$errorMsg = "Page data not available.";
			return null;
		}
		
		$text = str_replace('\n', "\n", $text);
		$text = str_replace('\"', '"', $text);
		$isMatched = preg_match('#<syntaxhighlight .*?>(.*)</syntaxhighlight>#s', $text, $matches);
		
		if (!$isMatched) 
		{
			$errorMsg = "Invalid page format (missing <syntaxhighlight...> section).";
			return null;
		}
		
		$text = $matches[1];
		if (self::REMOVE_JSON_COMMENTS) $text = self::removeJsonComments($text);
		
		$json = json_decode($text, true);
		
		if ($json == null)
		{
			$errorMsg = "Invalid page JSON format (" . json_last_error_msg() . "). ";
			$errorMsg .= self::jsonLint($text, $formatErrorHtml)."</pre>";
			return null;
		}
		
		$errorMsg = "";
		return $json;
	}
	
	
	public static function jsonLint($json, $formatErrorHtml = false)
	{
		if (!self::USE_JSON_LINT) return "";
		
		require_once(__DIR__."/JsonLint/Undefined.php");
		require_once(__DIR__."/JsonLint/ParsingException.php");
		require_once(__DIR__."/JsonLint/DuplicateKeyException.php");
		require_once(__DIR__."/JsonLint/Lexer.php");
		require_once(__DIR__."/JsonLint/JsonParser.php");
		
		$parser = new JsonParser();
		$errorMsg = "";
		
		try
		{
			$parser->parse($json);
		} catch (Exception $e) 
		{
			$errorMsg = $e->getMessage();
		}
		
		if ($formatErrorHtml) return "<pre>$errorMsg</pre>";
		return $errorMsg;
	}
	
	
	public function execute()
	{
		$params = $this->extractRequestParams();
		$apiResult = $this->getResult();
		
		if ($params['version'] == null) return $this->outputError("Invalid request, manifest version required.");
		
		$version = intval($params['version']);
		if ($version <= 0) return $this->outputError("Invalid request, provided non-integer manifest version param.");
		
		//TODO: Check manifest version for valid value?
		$json = $this->parseJsonFromPage($errorMsg, false);
		if ($json == null) return $this->outputError($errorMsg);
		
		$this->replaceJsonContent($json);
		
		$apiResult->addValue( null, "apphomepage", $json );
	}
	
	
	protected function getAllowedParams() {
		return [
				'version' => [
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
				],
		];
	}
	
	
};