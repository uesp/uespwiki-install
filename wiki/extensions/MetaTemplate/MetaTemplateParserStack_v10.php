<?php
// group of functions to manipulate parser argument stack
// functions separated out to isolate the code that will need to revamped for MW1.14
class MetaTemplateParserStack {
	protected $_parser;
	protected $_stack;
	protected $_stackcount;
	protected $_templates;
	
	// $frame isn't used by this version of the code, but it needs to be passed to
	// parser for compatibility with MW1.14 version of class
	function __construct(&$parser, $frame) {
		$this->_parser = &$parser;
		$this->_stack = &$parser->mArgStack;
		$this->_stackcount = count($this->_stack)-1;
		$this->_templates = &$parser->mTemplatePath;
	}
	
	function get_stackcount() {
		return $this->_stackcount;
	}
	
	function exists($varname, $stacklvl=0) {
		if (is_null($stacklvl))
			$stackuse = NULL;
		else
			$stackuse = $this->_stackcount+$stacklvl;
		if (is_null($stackuse)) {
			for ($i=$this->_stackcount; $i>=0; $i--) {
				if (array_key_exists($varname, $this->_stack[$i]))
					return true;
			}
			return false;
		}
		else {
			return (array_key_exists($varname, $this->_stack[$stackuse]));
		}
	}
	
	function set($value, $varname, $stacklvl=0) {
		if (is_null($stacklvl))
			$stacklvl = 0;
		$stackuse = $this->_stackcount+$stacklvl;
		$this->_stack[$stackuse][$varname] = $value;
	}
	
	function unset_value($varname, $stacklvl=0) {
		if (is_null($stacklvl))
			$stacklvl = 0;
		$stackuse = $this->_stackcount+$stacklvl;
		unset($this->_stack[$stackuse][$varname]);
	}
		
	function get($varname, $matchcase=false, $stacklvl=0) {
		if (is_null($stacklvl))
			$stackuse = NULL;
		else
			$stackuse = $this->_stackcount+$stacklvl;
		
		if ($matchcase) {
			if (is_null($stackuse)) {
				for ($i=$this->_stackcount; $i>=0; $i--) {
					if (array_key_exists($varname, $this->_stack[$i]))
						return $this->_stack[$i][$varname];
				}
				return NULL;
			}
			else {
				if (array_key_exists($varname, $this->_stack[$stackuse]))
					return $this->_stack[$stackuse][$varname];
				else
					return NULL;
			}
		}
		else {
			$lcname = strtolower($varname);
			if (is_null($stackuse)) {
				for ($i=$this->_stackcount; $i>=0; $i--) {
					foreach ($this->_stack[$i] as $key => $value) {
						if (strtolower($key)==$lcname)
							return $value;
					}
				}
			}
			else {
				foreach ($this->_stack[$stackuse] as $key => $value) {
					if (strtolower($key)==$lcname)
						return $value;
				}
			}
			return NULL;
		}
	}
	
	function get_args($stacklvl=0) {
		if (is_null($stacklvl))
			$stacklvl = 0;
		$stackuse = $this->_stackcount+$stacklvl;
		return $this->_stack[$stackuse];
	}
	
	static function is_docroot(&$parser, &$frame) {
		return (count($parser->mArgStack)<=1);
	}
	
	// can't check whether level is numeric, because it's likely to be a string containing an int
	function get_template_title($level=0) {
		// instead of simply assigning keys to an array, scan stack to make sure no parser functions are
		// in the stack
		// only items in double braces ({{..}}) should be at risk of being mistaken as templates
		// also, I'm assuming that all parser functions start with # (or at least that any non-hash parser
		// functions would have no reason to show up in the template stack)
		$tkeys = array();
		foreach ($this->_templates as $key => $x) {
			if ($key{0}!='#')
				$tkeys[] = $key;
		}
		
		$nkeys = count($tkeys)-1;
		if ($level>0)
			$nget = $level-2;
		else
			$nget = $nkeys+$level;
		// the actual article is not in the template list, so a request for entry -1 is equivalent to the article that is being parsed
		if ($nget==-1)
			return $this->_parser->getTitle();
		if ($nget<0 || $nget>$nkeys)
			return '';
		$tname = $tkeys[$nget];
		
		// template list contains the original "part1" text, not the real title after processing
		// so all of the title processing needs to be repeated here
		// (maybeDoSubpageLink function is technically private, but I need its functionality)
		$ns = NS_TEMPLATE;
		$subpage = '';
		$page = $this->_parser->maybeDoSubpageLink( $tname, $subpage );
		if ($subpage !== '')
			$ns = $this->_parser->getTitle()->getNamespace();
		$title = Title::newFromText( $page, $ns );
		if (is_object($title))
			return $title;
		else
			// if there's an error looking up the name, then return the original template entry
			// for example, if a parser function did manage to leak through
			// although the result is probably not what was wanted, providing the string will be more useful for
			// debugging than an empty string.
			return $tname;
	}
}
