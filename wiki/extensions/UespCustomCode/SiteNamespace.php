<?php

class SiteNamespace
{
	static protected $saved_by_ns = array();
	static protected $saved_by_title = array();
	protected $entries;
	protected $data;

	// determine appropriate namespace for request
	// * if ns is a NS_BASE, NS_ID, or any recognized alias, then the corresponding namespace will be returned
	// * if ns is an integer, it's assumed to be an internal namespace index;
	//   page_title will be checked for mod_name
	// * if ns is NULL, both namespace index and page_title will be taken from parser
	function __construct($ns_input = NULL, $parser = NULL, $page_title = NULL)
	{
		global $wgContLang, $egCustomSiteID, $wgTitle, $wgRequest;
		$ns_num = $ns_name = $titleid = $mod_name = NULL;
		if (is_null($ns_input)) {
			$title = is_null($page_title) ? $parser->getTitle() : $page_title;
			// parser's title is invalid; try wgTitle instead
			// (can happen in strange circumstances... API?  job queue?  If it is the API, where can I get title???)
			if ($title->getArticleID() <= 0)
				$title = $wgTitle;
			if ($title->getArticleID() <= 0) {
				$tname = $wgRequest->getVal('title');
				$title = Title::newFromText($tname);
			}
			// if everything failed, go back to parser :(
			if (!is_object($title) || $title->getArticleID() < 0)
				$title = $parser->getTitle();
			$ns_num = $title->getNamespace();
			$titleid = $title->getArticleID();
			$mod_name = $title->getText();
			if ($i = stripos($mod_name, '/'))
				$mod_name = substr($mod_name, 0, $i);
		} elseif (is_int($ns_input))
			$ns_num = $ns_input;
		elseif (ctype_digit($ns_input))
			$ns_num = (int) $ns_input;
		else {
			// RH70: Revamped to allow all aliases to work instead of just NS_ID.
			$ns_num = $wgContLang->getNsIndex(strtr($ns_input, ' ', '_'));
			if ($ns_num) {
				$ns_num = MWNamespace::getSubject($ns_num);
				$ns_name = $wgContLang->getNsText($ns_num);
			} else {
				$ns_name = $ns_input;
			}
		}

		if (is_null($ns_name) && !is_null($ns_num)) {
			if ($ns_num === 0 || $ns_num === 1) {
				$ns_num = 0;
				$ns_name = '';
			} else {
				$ns_num = MWNamespace::getSubject($ns_num);
				$ns_name = $wgContLang->getNsText($ns_num);
			}
		}

		$ns_name = strtr($ns_name, '_', ' ');
		$lines = explode("\n", wfMessage(strtolower($egCustomSiteID) . 'namespacelist')->inContentLanguage()->text());
		$entries_ns = NULL;
		$entries_mod = NULL;
		foreach ($lines as $line) {
			$line = trim($line);
			$line = preg_replace('/\s*<\s*\/?\s*pre(\s+[^>]*>|>)\s*/', '', $line);
			if (substr($line, 0, 1) == '#' || strlen($line) == 0)
				continue;
			$entries = explode(";", $line);
			$entries[0] = strtr(trim($entries[0]), '_', ' ');
			$entries[1] = strtoupper((count($entries) < 1 || ctype_space($entries[1]) || $entries[1] == '')
				? $entries[0]
				: trim($entries[1]));
			if ($entries[0] == $ns_name || (strlen($entries[1]) > 0 && $entries[1] == strtoupper($ns_name)))
				$entries_ns = $entries;
			elseif (!is_null($mod_name) && ($entries[0] == $ns_name . ':' . $mod_name || $entries[0] == $ns_name . ':' . $mod_name . '/')) {
				$entries_mod = $entries;
			}
		}
		$this->data = array();
		if (!is_null($entries_mod)) {
			$this->entries = $entries_mod;
			$this->data['ns_base'] = $entries_mod[0];
			$this->data['mod_name'] = $mod_name;
		} elseif (!is_null($entries_ns)) {
			$this->entries = $entries_ns;
			$this->data['ns_base'] = $entries_ns[0];
		} else {
			// this is only if no matching entries were found -- either because request was incorrect
			// or because namespace uses default treatment
			$this->entries = array();
			$this->data['ns_base'] = $ns_name;
		}
		if (!array_key_exists('mod_name', $this->data)) {
			if (($i = stripos($this->data['ns_base'], ':')))
				$this->data['mod_name'] = substr($this->data['ns_base'], $i + 1);
			else
				$this->data['mod_name'] = '';
		}
		// saves are intended to match future values passed to find_nsobj
		// these make sure that if find_nsobj is called with identical arguments, find_nsobj can find the object
		if (!is_null($titleid))
			self::$saved_by_title[$titleid] = $this;
		// only save according to ns_input if ns_input is not a number
		// (can't save according to namespace index, because that's not sufficient to uniquely
		//  identify namespace)
		elseif ($ns_name == $ns_input)
			self::$saved_by_ns[$ns_input] = $this;

		// these are in case of future calls using ns_base or ns_id as arguments
		self::$saved_by_ns[$this->get('ns_base')] = $this;
		self::$saved_by_ns[$this->get('ns_id')] = $this;
	}

	public function get($value)
	{
		$value = strtolower($value);
		if (array_key_exists($value, $this->data))
			return $this->data[$value];
		elseif (method_exists($this, $fname = 'get_' . $value)) {
			return $this->$fname();
		} else
			return NULL;
	}

	protected function get_entry($num)
	{
		if (array_key_exists($num, $this->entries) && $this->entries[$num] != '') {
			$this->entries[$num] = trim($this->entries[$num]);
			if ($this->entries[$num] == '')
				return NULL;
			return $this->entries[$num];
		}
		return NULL;
	}

	public function get_ns_full()
	{
		$this->data['ns_full'] = $this->get('ns_base');
		if (strlen($this->data['ns_full']) > 0) {
			if (!stripos($this->data['ns_full'], ':'))
				$this->data['ns_full'] .= ':';
			else
				$this->data['ns_full'] .= '/';
		}
		return $this->data['ns_full'];
	}

	public function get_ns_id()
	{
		if (is_null($this->data['ns_id'] = $this->get_entry(1)))
			$this->data['ns_id'] = $this->data['ns_base'];
		return $this->data['ns_id'];
	}

	public function get_ns_parent()
	{
		if (is_null($this->data['ns_parent'] = $this->get_entry(2)))
			$this->data['ns_parent'] = $this->data['ns_base'];
		return $this->data['ns_parent'];
	}

	public function get_ns_name()
	{
		if (is_null($this->data['ns_name'] = $this->get_entry(3)))
			$this->data['ns_name'] = $this->data['ns_base'];
		return $this->data['ns_name'];
	}

	public function get_ns_mainpage()
	{
		if (is_null($this->data['ns_mainpage'] = $this->get_entry(4)))
			$this->data['ns_mainpage'] = $this->get('ns_full') . $this->get('ns_name');
		return $this->data['ns_mainpage'];
	}

	public function get_ns_category()
	{
		if (is_null($this->data['ns_category'] = $this->get_entry(5)))
			$this->data['ns_category'] = $this->data['ns_base'];
		return $this->data['ns_category'];
	}

	public function get_ns_trail()
	{
		if (is_null($this->data['ns_trail'] = $this->get_entry(6)))
			$this->data['ns_trail'] = '[[' . $this->get('ns_mainpage') . '|' . $this->get('ns_name') . ']]';
		// fix colons within trail (can't provide them as &#058; in input, because semicolon gets misinterpreted)
		else
			$this->data['ns_trail'] = preg_replace('/(\]\]\s*):/', '$1&#058;', $this->data['ns_trail']);
		return $this->data['ns_trail'];
	}

	public static function parser_get_ns_base()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_base');
	}

	public static function parser_get_ns_full()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_full');
	}

	public static function parser_get_mod_name()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'mod_name');
	}

	public static function parser_get_ns_id()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_id');
	}

	public static function parser_get_ns_parent()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_parent');
	}

	public static function parser_get_ns_name()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_name');
	}

	public static function parser_get_ns_mainpage()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_mainpage');
	}

	public static function parser_get_ns_category()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_category');
	}

	public static function parser_get_ns_trail()
	{
		$args = func_get_args();
		return self::parser_get_value($args, 'ns_trail');
	}

	# call as either ($args, $valname) or ($parser, $valname, $frame, $ns)
	public static function parser_get_value($args, $valname, $frame = NULL, $ns = NULL)
	{
		if (is_array($args)) {
			$parser = $args[0];
			$frame = $args[1];
			if (is_array($args[2]) && count($args[2]))
				$ns = $frame->expand(array_shift($args[2]));
			else
				$ns = NULL;
		} else {
			$parser = $args;
		}

		$obj = self::find_nsobj($parser, $frame, $ns);
		return $obj->get($valname);
	}

	public static function find_nsobj(&$parser, $frame = NULL, $ns = NULL)
	{
		global $wgTitle, $wgRequest;
		$nschk = NULL;
		if (!is_null($ns) && $ns)
			$nschk = $ns;
		// not forcing entire extension to be installed... at least not yet
		elseif (function_exists('efMetaTemplateInit')) {
			$pstack = new MetaTemplateParserStack($parser, $frame);
			if (is_null($nschk = $pstack->get('ns_base')) && is_null($nschk = $pstack->get('ns_id')))
				$nschk = NULL;
		} else
			$nschk = NULL;
		if ($nschk === -1 || $nschk === '-1')
			$nschk = NULL;

		// if ns not explicitly requested, need to go by article title
		// but to determine correct ns for article title takes some processing
		// (to recognize whether article is in a sub-namespace, e.g., Tes3Mod:Tamriel Rebuilt/Filename)
		// don't want to repeat that processing every time, but can't just assume there's only one
		// article title (if multiple articles are processed in a single session because of job queue)
		if (is_null($nschk)) {
			$title = $parser->getTitle();
			// parser's title is invalid; try wgTitle instead
			if ($title->getArticleID() <= 0 && isset($wgTitle))
				$title = $wgTitle;
			if ($title->getArticleID() <= 0) {
				$tname = $wgRequest->getVal('title');
				$title = Title::newFromText($tname);
			}
			// if everything failed, go back to parser :(
			if (!is_object($title) || $title->getArticleID() < 0) {
				$title = $parser->getTitle();
			}
			$titleid = $title->getArticleID();

			if (array_key_exists($titleid, self::$saved_by_title))
				return self::$saved_by_title[$titleid];
		} elseif (array_key_exists($nschk, self::$saved_by_ns))
			return self::$saved_by_ns[$nschk];
		$obj = new SiteNamespace($nschk, $parser);
		return $obj;
	}

	public static function getRelatedNamespaces($input_nsnum, &$extratext, &$nsprimary)
	{
		global $wgContLang, $egCustomSiteID;
		$subjectspace = $wgContLang->getNsText(MWNamespace::getSubject($input_nsnum));
		$nssubj = $wgContLang->getNsIndex($subjectspace);
		$extratext = '';

		$lines = explode("\n", wfMessage(strtolower($egCustomSiteID) . 'namespacelist')->inContentLanguage()->text());
		$all_ns = array();
		$related_ns = array();
		$parent_ns = NULL;
		foreach ($lines as $line) {
			$line = trim($line);
			$line = preg_replace('/\s*<\s*\/?\s*pre(\s+[^>]*>|>)\s*/', '', $line);
			if (substr($line, 0, 1) == '#' || strlen($line) == 0)
				continue;
			$entries = explode(";", $line);
			$entries[0] = trim($entries[0]);
			if ($entries[0] == '')
				continue;
			// only continue if this is a gamespace
			// if standard wiki namespaces such as Project or Main are added to namespacelist for the sake of
			//  providing NS_TRAIL or NS_CATEGORY values, then don't want that entry to mess up
			//  searches
			if (!is_null($index = $wgContLang->getNsIndex($entries[0])) && $index < 100)
				continue;
			if (!array_key_exists(2, $entries))
				$entries[2] = $entries[0];
			else
				$entries[2] = trim($entries[2]);
			$all_ns[] = $entries[0];
			if ($entries[0] == $subjectspace)
				$parent_ns = $entries[2];
			if (!array_key_exists($entries[2], $related_ns))
				$related_ns[$entries[2]] = array($entries[0]);
			else
				$related_ns[$entries[2]][] = $entries[0];
		}

		$namespacelist = array();
		if (is_null($parent_ns)) {
			# If we are in a custom namespace
			# use current subjectspace as default namespace
			if ($input_nsnum >= 100) {
				$namespacelist[] = $nssubj;
				$namespacelist[] = $wgContLang->getNsIndex('Lore');
				$nslist = array();
			} else {
				# We're in a default namespace
				# in namespaces where search isn't broken, check this namespace too
				if ($nssubj != NS_MAIN && $nssubj != NS_SPECIAL && $nssubj != NS_USER && $nssubj != NS_MEDIAWIKI)
					$namespacelist[] = $nssubj;
				$nslist = $all_ns;
			}
		} else {
			# add other related namespaces
			$nslist = $related_ns[$parent_ns];
			$namespacelist[] = $nssubj;
		}

		foreach ($nslist as $ns) {
			if (!is_null($parent_ns) && $ns == $subjectspace)
				continue;
			if ($slash = stripos($ns, ':')) {
				if ($extratext == '')
					$extratext = '((';
				else
					$extratext .= '|';
				$extratext .= substr($ns, $slash + 1);
				if (substr($extratext, strlen($extratext) - 1, 1) == '/')
					$extratext = substr($extratext, 0, strlen($extratext) - 1);
			} else {
				$nsnum = $wgContLang->getNsIndex($ns);
				if (!is_null($nsnum))
					$namespacelist[] = $nsnum;
			}
		}
		if (!is_null($parent_ns)) {
			$namespacelist[] = $wgContLang->getNsIndex('Lore');
			$nsprimary = $input_nsnum;
		} else {
			$nsprimary = NULL;
		}

		if ($extratext != '')
			$extratext .= ")\/)?";

		return $namespacelist;
	}
}
