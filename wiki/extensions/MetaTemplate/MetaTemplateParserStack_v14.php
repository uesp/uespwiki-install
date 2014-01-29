<?php
// group of functions to manipulate parser argument stack
class MetaTemplateParserStack {
	protected $_parser;
	protected $_frame;
	protected $_stackcount;
	
	function __construct(&$parser, &$frame) {
		$this->_parser = &$parser;
		// empty frames possible if being called via transparentTagHook... not that I really ever want to be
		// doing that, following testing...
		if (empty($frame))
			$frame = $parser->getPreprocessor()->newFrame();
		$this->_frame = &$frame;
		$this->_stackcount = $frame->depth;
	}
	
	function get_stackcount() {
		return $this->_stackcount;
	}
	
	function get_frame($stacklvl=0) {
		$newframe = $this->_frame;
		for ($newframe = $this->_frame; $stacklvl<0; $stacklvl++)
			$newframe = $this->prev_frame($newframe);
		return $newframe;
	}
	
	function prev_frame($frame) {
		if (!isset($frame->parent))
			return false;
		$newframe = $frame->parent;
		return $newframe;
	}
	
	function exists($varname, $stacklvl=0) {
		if (is_null($stacklvl)) {
			for ($currframe=$this->_frame; $currframe; $currframe = $this->prev_frame($currframe)) {
				if ($currframe->getArgument($varname)!==false)
					return true;
			}
		}
		else {
			$currframe = $this->get_frame($stacklvl);
			if ($currframe->getArgument($varname)!==false)
				return true;
		}
		return false;
	}
	
	function set($value, $varname, $stacklvl=0) {
		$currframe = $this->get_frame($stacklvl);
		if (is_string($varname) && ctype_digit($varname))
			$varname = (integer) $varname;
		if ($currframe->getArgument($varname)!==false) {
			if (is_int($varname)) {
				$currframe->numberedArgs[$varname]->getFirstChild()->value = $value;
				$currframe->numberedExpansionCache[$varname] = $value;
			}
			else {
				$currframe->namedArgs[$varname]->getFirstChild()->value = $value;
				$currframe->namedExpansionCache[$varname] = $value;
			}
		}
		else {
			$element = PPNode_Hash_Tree::newWithText( 'value', $value );
			if (is_int($varname)) {
				$currframe->numberedArgs[$varname] = $element;
				$currframe->numberedExpansionCache[$varname] = $value;
			}
			else {
				$currframe->namedArgs[$varname] = $element;
				$currframe->namedExpansionCache[$varname] = $value;
			}
		}
	}
	
	function unset_value($varname, $stacklvl=0) {
		$currframe = $this->get_frame($stacklvl);
		if (is_string($varname) && ctype_digit($varname))
			$varname = (integer) $varname;
		if (is_int($varname)) {
			unset ($currframe->numberedArgs[$varname]);
			unset ($currframe->numberedExpansionCache[$varname]);
			// to catch cases where template called using, i.e. {{template|1=first}}
			unset ($currframe->namedArgs[$varname]);
			unset ($currframe->namedExpansionCache[$varname]);
		}
		else {
			unset ($currframe->namedArgs[$varname]);
			unset ($currframe->namedExpansionCache[$varname]);
		}
	}
		
	function get($varname, $matchcase=true, $stacklvl=0) {
		if ($matchcase) {
			if (is_null($stacklvl)) {
				for ($currframe=$this->_frame; $currframe; $currframe = $this->prev_frame($currframe)) {
					if (($value=$currframe->getArgument($varname))!==false)
						return $value;
				}
			}
			else {
				$currframe = $this->get_frame($stacklvl);
				if (($value=$currframe->getArgument($varname))!==false)
					return $value;
			}
		}
		else {
			$lcname = strtolower($varname);
			if (is_null($stacklvl)) {
				for ($currframe=$this->_frame; $currframe; $currframe = $this->prev_frame($currframe)) {
					$args = $currframe->getArguments();
					foreach ($args as $key => $value) {
						if (strtolower($key)==$lcname)
							return $value;
					}
				}
			}
			else {
				$currframe = $this->get_frame($stacklvl);
				$args = $currframe->getArguments();
				foreach ($args as $key => $value) {
					if (strtolower($key)==$lcname)
						return $value;
				}
			}
			return NULL;
		}
	}
	
	function get_args($stacklvl=0) {
		$currframe = $this->get_frame($stacklvl);
		return $currframe->getArguments();
	}
	
	static function is_docroot(&$parser, &$frame) {
		if (is_object($frame) && ($frame->isTemplate() || isset($frame->parent)))
			return false;
		return true;
	}
	
	function get_template_title($level=0) {
		if ($level>0)
			$stacklvl = ($level-1)-$this->_stackcount;
		else
			$stacklvl = $level;
		$nback = $stacklvl*-1;
		
		// the actual article does not have a title in the stack
		if ($nback==$this->_stackcount)
			return $this->_parser->getTitle();
		if ($nback<0 || $nback>$this->_stackcount)
			return '';
		
		$currframe = $this->get_frame($stacklvl);
		return $currframe->title;
	}
}
