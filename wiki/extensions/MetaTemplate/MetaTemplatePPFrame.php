<?php
global $IP;
require_once("$IP/includes/parser/Preprocessor.php");
require_once("$IP/includes/parser/Preprocessor_Hash.php");

/* Customize preprocessor classes in order to make template variables available even in top-level article */
// Tip thanks to Tim Starling
// Not changing TemplateFrame or CustomFrame ... just overriding PPFrame_Hash
// Also requires setting: $wgParserConf['preprocessorClass'] = 'Preprocessor_Uesp'

class Preprocessor_Uesp extends Preprocessor_Hash {
	function newFrame() {
		return new PPFrame_Uesp( $this );
	}
}

class PPFrame_Uesp extends PPFrame_Hash {
	var $parent;
	var $numberedArgs, $namedArgs;
	var $numberedExpansionCache, $namedExpansionCache;
	
	function __construct( $preprocessor ) {
		parent::__construct( $preprocessor );
		// can't initialize parent here, because there are tests that simply check whether parent is set
		$this->numberedArgs = array();
		$this->namedArgs = array();
		$this->numberedExpansionCache = array();
		$this->namedExpansionCache = array();
	}
	
	function isEmpty() {
		return !count( $this->numberedArgs ) && !count( $this->namedArgs );
	}
	
	function getArgument( $name ) {
		$text = $this->getNumberedArgument( $name );
		if ( $text === false ) {
			$text = $this->getNamedArgument( $name );
		}
		return $text;
	}
	
	function getArguments() {
		$arguments = array();
		foreach ( array_merge(
		                       array_keys($this->numberedArgs),
		                       array_keys($this->namedArgs)) as $key ) {
			                       $arguments[$key] = $this->getArgument($key);
		                       }
		return $arguments;
	}
	
	function getNumberedArguments() {
		$arguments = array();
		foreach ( array_keys($this->numberedArgs) as $key ) {
			$arguments[$key] = $this->getArgument($key);
		}
		return $arguments;
	}
	
	function getNamedArguments() {
		$arguments = array();
		foreach ( array_keys($this->namedArgs) as $key ) {
			$arguments[$key] = $this->getArgument($key);
		}
		return $arguments;
	}
	
	function getNumberedArgument( $index ) {
		if ( !isset( $this->numberedArgs[$index] ) ) {
			return false;
		}
		if ( !isset( $this->numberedExpansionCache[$index] ) ) {
			# infer the appropriate context for the variable expansion
			# template variables should be expanded in the context of the parent, but
			# if this is used in the main article, the parent does not exist
			# (given current class layout, parent is never set, but keeping this code snippet here means
			#  function can also be used if PPFrame_Uesp is subclassed into PPTemplateFrame_Uesp)
			if ( isset( $this->parent ) )
				$context = $this->parent;
			else
				$context = $this;
			# No trimming for unnamed arguments
			$this->numberedExpansionCache[$index] = $context->expand( $this->numberedArgs[$index], self::STRIP_COMMENTS );
		}
		return $this->numberedExpansionCache[$index];
	}
	
	function getNamedArgument( $name ) {
		if ( !isset( $this->namedArgs[$name] ) ) {
			return false;
		}
		if ( !isset( $this->namedExpansionCache[$name] ) ) {
			# infer the appropriate context for the variable expansion
			# template variables should be expanded in the context of the parent, but
			# if this is used in the main article, the parent does not exist
			if ( isset( $this->parent ) )
				$context = $this->parent;
			else
				$context = $this;
			# Trim named arguments post-expand, for backwards compatibility
			$this->namedExpansionCache[$name] = trim(
			                                          $context->expand( $this->namedArgs[$name], self::STRIP_COMMENTS ) );
		}
		return $this->namedExpansionCache[$name];
	}
}

