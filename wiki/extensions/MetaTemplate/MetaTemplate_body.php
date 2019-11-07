<?php
/*
 * Functions for Magic words and parser functions
 */

// This is the best place to disable individual magic words;
// To disable all magic words, disable the hook that calls this function
function efMetaTemplateDeclareMagicWord(&$aCustomVariableIds) {
	$aCustomVariableIds[] = MAG_METATEMPLATE_NESTLEVEL;
	$aCustomVariableIds[] = MAG_METATEMPLATE_NAMESPACE0;
	$aCustomVariableIds[] = MAG_METATEMPLATE_PAGENAME0;
	$aCustomVariableIds[] = MAG_METATEMPLATE_FULLPAGENAME0;
	return true;
}

// Commenting out lines here will also disable the related magic word (but generally
// requires commenting out more than one line)
function efMetaTemplateAssignMagicWord(&$parser, &$cache, &$magicWordId, &$ret, &$frame = NULL) {
	// need to fill frame somehow!!!!
	if ($magicWordId == MAG_METATEMPLATE_NESTLEVEL) {
	       $ret = efMetaTemplateImplementNestlevel($parser, $frame);
	}
	elseif ($magicWordId == MAG_METATEMPLATE_NAMESPACE0 ||
	        $magicWordId == MAG_METATEMPLATE_PAGENAME0 ||
	        $magicWordId == MAG_METATEMPLATE_FULLPAGENAME0) {
		$ret = efMetaTemplateImplementTemplateName($parser, $frame, $magicWordId);
	}
	return true;
}

// Blank function, used as hook when a given parser function is not activated
function efMetaTemplateBlank(&$parser) {
	 return '';
}

function efMetaTemplateProcessArgs($args, &$frame, &$matchcase, &$skip, &$subset=NULL) {
	global $wgVersion;
	if( version_compare( $wgVersion, '1.12.0', '>=')) {
		$frame = $args[0];
		$origargs = $args[1];
		$args = array();
		if (is_array($origargs)) {
			foreach ($origargs as $arg) {
				$args[] = $frame->expand($arg);
			}
		}
	}
	else
		$frame = NULL;
	$matchcase = true;
	$skip = false;
	$subset = '';
	$output = array();
	foreach ($args as $arg) {
		$arg = trim($arg);
		if ($arg===false)
			continue;
		if (preg_match('/^([^\s=]+?)\s*=\s*(.*)/', $arg, $matches)) {
			if ($matches[1]=='if')
				$skip = !($matches[2]==true);
			elseif ($matches[1]=='ifnot')
				$skip = ($matches[2]==true);
			elseif ($matches[1]=='case')
				$matchcase = !($matches[2]=='any');
			elseif (!is_null($subset) && $matches[1]=='subset')
				$subset = $matches[2];
			else
				$output[] = $arg;
		}
		else
			$output[] = $arg;
	}
	return $output;
}

// Implementation of {{#define}} parser function
// don't want to skip ns_base or ns_id definition...
function efMetaTemplateImplementDefine(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$data = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	if( $skip )
		return '';
	if( efMetaTemplateDisplayMode( $parser, $frame ) && (!array_key_exists(0, $data) || ($data[0]!='ns_base' && $data[0]!='ns_id') ) )
		return '';
	
	efMetaTemplateSharedDefine($parser, $frame, $matchcase, $data);
	return '';
}

// Implementation of ((#preview}} parser function
// (only difference from #define is when it gets activated)
function efMetaTemplateImplementPreview(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$data = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	if( $skip )
		return '';
	// originally arguments were true, true -> but that meant preview didn't work on templates
	// outside of template namespace
	if ( !efMetaTemplateDisplayMode( $parser, $frame, NULL, true ) )
		return '';
	
	efMetaTemplateSharedDefine($parser, $frame, $matchcase, $data);
	return '';
}

// Implementation of ((#local}} parser function
// (only difference from #define is in the parameters to SharedDefine)
function efMetaTemplateImplementLocal(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$data = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	if( $skip )
		return '';
	// matchcase is always true for local
	if( efMetaTemplateDisplayMode( $parser, $frame ) )
		return '';
	efMetaTemplateSharedDefine($parser, $frame, true, $data, false);
	return '';
}

function efMetaTemplateSharedDefine(&$parser, $frame, $matchcase, $data, $allowoverride=true) {
	$pstack = new MetaTemplateParserStack($parser, $frame);
	if (count($data)<1 || $data[0]===false || ($allowoverride && $pstack->exists($data[0])))
		return '';

	if (!$matchcase || $allowoverride) {
		if (!is_null($value=$pstack->get($data[0], $matchcase))) {
			// set value because the case may be different
			$pstack->set($value, $data[0]);
			return '';
		}
	}
	if (!array_key_exists(1, $data) || $data[1]===false)
		return '';
	$pstack->set($data[1], $data[0]);
	return '';
}

// Implementation of ((#unset}} parser function
function efMetaTemplateImplementUnset(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$varnames = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	if ($skip)
		return '';
	
	$pstack = new MetaTemplateParserStack($parser, $frame);
	foreach ($varnames as $varname)
		$pstack->unset_value($varname);
	return '';
}

// Implementation of {{#return}} parser function
function efMetaTemplateImplementReturn(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$varnames = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	if( $skip )
		return '';
	$pstack = new MetaTemplateParserStack($parser, $frame);
	if (!$pstack->get_stackcount())
		return;
	
	foreach ($varnames as $varname) {
		if (!is_null($value=$pstack->get($varname))) {
			$pstack->set($value,$varname,-1);
		}
	}
	return '';
}

// Implementation of {{#inherit}} parser function
function efMetaTemplateImplementInherit(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$varnames = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	if( $skip )
		return '';
	
	$pstack = new MetaTemplateParserStack($parser, $frame);
	foreach ($varnames as $varname) {
		if ($pstack->exists($varname))
			continue;
		if (!is_null($value=$pstack->get($varname, $matchcase, NULL))) {
			$pstack->set($value, $varname);
		}
	}
	return '';
}

// Implementation of {{#include}} parser function
function efMetaTemplateImplementInclude(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$pagenames = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	if( $skip )
		return '';
		
	foreach( $pagenames as $pagename ) {
		$nameonly = preg_replace('/\|.*/', '', $pagename);
		$t = Title::newFromText ( $nameonly, NS_TEMPLATE );
		if ($t && $t->exists()) {
			$text = '{{' . $pagename . '}}';
			return array($text, 'noparse' => false);
		}
	}
	return '';
}

// Implementation of {{#splitargs}} parser function
function efMetaTemplateImplementSplitargs(&$parser) {
	global $wgVersion;
	if( version_compare( $wgVersion, '1.12.0', '>=')) {
		$frame = func_get_arg(1);
		$targs = func_get_arg(2);
		$origargs = array();
		foreach ($targs as $arg)
			$origargs[] = $frame->expand($arg);
	}
	else {
		$frame = NULL;
		$origargs = func_get_args();
		array_shift($origargs);
	}
	
	$pagename = array_shift($origargs);
	$nargs = intval(array_shift($origargs));
	if (!is_int($nargs) || $nargs<=0)
		return '';
	
	$nonnum = '';
	if (count($origargs)) {
		// preprocess list to deal with any '=' signs in arguments --
		// arguments passed directly to parser function are not split/processed the same way as template args
		// this needs to be done before standard loop because argument numbers need to be reassigned
		// processing largely borrowed from Parser.php:createAssocArgs
		$args = array();
		$index = 1;
		foreach ($origargs as $arg) {
			$eqpos = strpos( $arg, '=' );
			if ( $eqpos === false )
				$args[$index++] = $arg;
			else {
				$key = trim( substr( $arg, 0, $eqpos ) );
				$value = trim( substr( $arg, $eqpos+1 ) );
				if ( $value === false )
					$value = '';
				if ( $key !== false )
					$args[$key] = $value;
			}
		}
	}
	else {
		$pstack = new MetaTemplateParserStack($parser, $frame);
		$args = $pstack->get_args();
	}
	
	$nums = array();
	$nonnum = '';
	foreach ($args as $key => $value) {
		if (is_int($key) && $key>0) {
			$nums[$key] = $value;
		}
		else {
			$nonnum .= '|' . $key . '=' . $value;
		}
	}

	$output = '';
	if (count($nums)) {
		ksort($nums);
		$currbase = -1;
		$currargs = '';
		foreach ($nums as $key => $value) {
			$nbase = floor(($key-1)/$nargs);
			$nnew = $key % $nargs;
			if( !$nnew )
				$nnew = $nargs;
			if( $nbase != $currbase ) {
				if( $currargs != '' ) {
					$output .= '{{' . $pagename . $currargs . $nonnum . '}}';
				}
				$currargs = '';
				$currbase = $nbase;
			}
			// RH70 (2019-02-03): The str_replace was added as a hack for doubly-parsed {{!}} pipes in the original parameter.
			// It would probably be better to actually parse the named and numbered values using PPTemplate_Hash methods, but
			// this seems to work well enough.
			$currargs .= '|' . $nnew . '=' . str_replace('|', '{{!}}', $value);
		}
		if( $currargs != '' ) {
			$output .= '{{' . $pagename . $currargs . $nonnum . '}}';
		}
	}
	else {
		$currargs = '';
		for ($i=3; $i<func_num_args(); $i++) {
			$nnew = ($i-3) % $nargs;
			if( !$nnew)
				$nnew = $nargs;
			$currargs .= '|' . $nnew . '=' . func_get_arg($i);
			if ($nnew==$nargs || $i==func_num_args()-1) {
				$output .= '{{' . $pagename . $currargs . '}}';
				$currargs = '';
			}
		}
	}
	return array($output, 'noparse' => false);
}

// Implementation of {{#Explodeargs}} parser function
function efMetaTemplateImplementExplodeargs(&$parser) {
	global $wgVersion;

	if( version_compare( $wgVersion, '1.12.0', '>=')) {
		$frame = func_get_arg(1);
		$targs = func_get_arg(2);
		$origargs = array();
		foreach ($targs as $arg)
			$origargs[] = $frame->expand($arg);
	}
	else {
		$frame = NULL;
		$origargs = func_get_args();
		array_shift($origargs);
	}
	
	$varvalue = array_shift($origargs);
	if($varvalue == false) {
		return '';
	}
	
	$delimeter = array_shift($origargs);
	$pagename = array_shift($origargs);
	$nargs = intval(array_shift($origargs));
	if (!is_int($nargs) || $nargs<0)
		return '';

	$nonnum = '';
	// preprocess list to deal with any '=' signs in arguments --
	// arguments passed directly to parser function are not split/processed the same way as template args
	// this needs to be done before standard loop because argument numbers need to be reassigned
	// processing largely borrowed from Parser.php:createAssocArgs
	$explodeargs = explode($delimeter, $varvalue);
	if ( $nargs == 0 ) {
		$nargs = count($explodeargs);
	}

	$args = array();				
	$index = 1;
	foreach ($explodeargs as $arg) {
			$args[$index++] = $arg;
	}

	foreach ($origargs as $arg) {
		$eqpos = strpos( $arg, '=' );
		if ( $eqpos === false )
			return '';
		else {
			$key = trim( substr( $arg, 0, $eqpos ) );
			$value = trim( substr( $arg, $eqpos+1 ) );
			if ( $value === false )
				$value = '';
			if ( $key !== false )
				$args[$key] = $value;
		}
	}
	
	$nums = array();
	$nonnum = '';
	foreach ($args as $key => $value) {
		if (is_int($key) && $key>0) {
			$nums[$key] = $value;
		}
		else {
			$nonnum .= '|' . $key . '=' . $value;
		}
	}

	$output = '';
	if (count($nums)) {
		ksort($nums);
		$currbase = -1;
		$currargs = '';
		foreach ($nums as $key => $value) {
			$nbase = floor(($key-1)/$nargs);
			$nnew = $key % $nargs;
			if( !$nnew )
				$nnew = $nargs;
			if( $nbase != $currbase ) {
				if( $currargs != '' ) {
					$output .= '{{' . $pagename . $currargs . $nonnum . '}}';
				}
				$currargs = '';
				$currbase = $nbase;
			}
			$currargs .= '|' . $nnew . '=' . $value;
		}
		if( $currargs != '' ) {
			$output .= '{{' . $pagename . $currargs . $nonnum . '}}';
		}
	}
	return array($output, 'noparse' => false);
}

// Implementation of {{#pickfrom}} parser function
// Uses old-style parser arguments (no frame)
function efMetaTemplateImplementPickfrom(&$parser, $npick) {
	$picklist = array();
	$seed = time();
	$separator = "\n";
	for ($i=2; $i<func_num_args(); $i++) {
		// argument has already been trimmed unfortunately
		$arg = func_get_arg($i);
		if (preg_match('/^\s*seed\s*=\s*(\d+)/', $arg, $matches))
			$seed = (int) $matches[1];
		elseif (preg_match('/^\s*separator\s*=(.*)/', $arg, $matches)) {
			$separator = stripcslashes($matches[1]);
			if (strlen($separator)>1 && $separator{0}==substr($separator,-1,1) && ($separator{0} == '\'' || $separator{0} == '"'))
				$separator = substr($separator,1,-1);
		}
		else
			$picklist[] = $arg;
	}
	
	// if npick>=nparams, show all items in order
	if ($npick<count($picklist)) {
		srand($seed);
		// randomize list
		shuffle($picklist);
		// cut off unwanted items
		array_splice($picklist, $npick);
	}
	
	$string = implode($picklist, $separator);
	return $string;
}
	
// Implementation of {{#trimlinks}} parser function
// Uses old-style parser arguments (no frame)
function efMetaTemplateImplementTrimlinks(&$parser, $text) {
	$text = preg_replace('/\[\[[^\[\]]+\|([^\[\]]+)\]\]/', '$1', $text);
	$text = preg_replace('/\[\[[^\[\]]*:([^\[\]]+?)(\([^\[\(\)\]]*\))?\s*\|\s*\]\]/', '$1', $text);
	$text = preg_replace('/\[\[([^\[\]]+)\]\]/', '$1', $text);
	return $text;
}

// Implementation of {{#save}} parser function
function efMetaTemplateImplementSave(&$parser) {

        if ( wfReadOnly() ) return '';

	// process before deciding whether to truly proceed, so that nowiki tags are previewed properly
	$args = func_get_args();
	array_shift($args);
	$data = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip, $subset);
	if( $skip )
		return '';
	$pstack = new MetaTemplateParserStack($parser, $frame);
	$tosave = array();
	foreach ($data as $arg) {
		// $value=$pstack->get($arg) - RH70: commented out because it appears to be redundant to next line
		if (!is_null($value=$pstack->get($arg))) {
			if (strpos($value, '-nowiki-')) {
				// demangle any nowiki tags in the text
				// not doing the rest of the demangling (pre, math, comments, etc.)
				$value = $parser->mStripState->unstripNoWiki( $value );
				$valb = efMetaTemplateTagParse($value, $parser, $frame);
				$pstack->set($valb, $arg);
				$tosave[$arg]['parsed'] = false;
			}
			$tosave[$arg]['value'] = $value;
		}
	}
	// this never gets activated on a template page, regardless of preview mode, and regardless of nest level
	// also never gets activated in preview mode, regardless of page
	if( !efMetaTemplateDisplayMode( $parser, $frame, false, false, false ) )
		return '';
	// never save on Special pages -> in particular, don't want Special:ExpandTemplates saving data
	if( $parser->getTitle()->getNamespace() < 0 )
		return '';
	
	if (!$subset)
		$subset = $pstack->get( 'subset' );
	MetaTemplateSaveData::adddata( $parser->getTitle(), $parser, $tosave, $subset );

	return '';
}

// Implementation of {{#load}} parser function
function efMetaTemplateImplementLoad(&$parser) {
	$args = func_get_args();
	array_shift($args);
	$data = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip, $subset);
	if( $skip || !count($data) )
		return '';
	if( !($loadfile = array_shift($data) ) )
		return '';
	$toget = array();
	$pstack = new MetaTemplateParserStack($parser, $frame);
	foreach ($data as $arg) {
		if (!$pstack->exists($arg))
			$toget[$arg] = true;
	}
	// if all of the load variables have already been set in this template
	if( !count($toget) )
		return '';
	
	// newFromText returns NULL if string is not a valid title
	if( !($loadtitle = Title::newFromText( $loadfile ) ) )
		return '';
	// add loadtitle to list of this article's transclusions
	// (whether or not file exists, and whether or not data found for loadtitle -- 
   //  I still want to force this page to refresh based on the transclusion)
	$parser->mOutput->addTemplate( $loadtitle, $loadtitle->getArticleID(), $loadtitle->getLatestRevID() );
	
	// subset_unknown is specifically for use by categoryPage, which needs to be able to call
	// #load without any information on the subset name... and needs to inherit the data
	// because template could have subtemplates
	$subset_unknown = false;
	if (!$subset)
		$subset = $pstack->get( 'subset', true, NULL );
	if (empty($subset)) {
		$subset_unknown=$pstack->get('load_subset_unknown', true, NULL);
		$subset = '';
	}
	$subsets_found = array();
	$subset_used = NULL;
	
	// processing previously in load class	
	$datafound = false;
	$redirtitle = false;
	$chktitle = $loadtitle;
	
		// only check for save data if the title corresponds to an actual article
		// (although still check for /Author and /Description pages below even for a non-article)
	if( $chktitle->exists() ) {
		$db = wfGetDB(DB_SLAVE);
		
		// loop twice to allow for a check of redirect if necessary
		for( $i = 0; $i < 2; $i++ ) {
			$chkid = $chktitle->getArticleID();
			// revid lookup has to be done before mt_save query -> don't want to throw out data
			// because article is simultaneously being modified
			$revid = $chktitle->getLatestRevID();
			$conds = array('mt_save_id=mt_set_id',
			               'mt_set_page_id' => $chkid);
			if (empty($subset_unknown)) {
				$conds['mt_set_subset'] = $subset;
			}
			$result = $db->select( array( 'mt_save_data', 'mt_save_set' ),
			                       '*',
			                       $conds,
			                       'efMetaTemplateImplementLoad-'.$chkid,
			                       array( 'ORDER BY' => 'mt_set_rev_id DESC, mt_set_id DESC' ) );
			//      print "query= ".$db->lastQuery()."<br>\n";
			
			if( !$result ) 
				break;
			elseif( $db->numRows( $result ) ) {
				$set_id = NULL;
				$do_clear = false;
				while( $row=$db->fetchRow( $result ) ) {
					$subsets_found[$row['mt_set_subset']] = true;
					if ( is_null( $set_id ) ) {
						// allow mt_set_rev_id to be more recent than revid (just in case there are simultaneous
						// saves going on)
						// should I also do something with time?
						if( $row['mt_set_rev_id']>=$revid ) {// || $row['mt_set_time']>now()-900 )
							$set_id = $row['mt_set_id'];
							$subset_used = $row['mt_set_subset'];
						}
						else
							$set_id = -1;
					}
					if ( $set_id == $row['mt_set_id'] ) {
						$datafound = true;
						if( array_key_exists( $row['mt_save_varname'], $toget ) ) {
		// I've already filtered out variables that have been set
							$value = $row['mt_save_value'];
							if (!$row['mt_save_parsed'])
								$value = efMetaTemplateTagParse( $value, $parser, $frame );
							$pstack->set( $value, $row['mt_save_varname'] );
						}
					}
					elseif (empty($subset_unknown))
						$do_clear = true;
				}
				
				// NB: cleardata will not be called if there's no match for the requested subset
				// (i.e., if there are other subsets for the page, just not the requested one)
				if( $do_clear )
					MetaTemplateSaveData::cleardata( $chktitle, $revid );
				if( $datafound )
					break;
			}
			
				// check whether requested article is a redirect 
				// get here either if no rows found for original title OR if original title data was cleared
			if ($i==0) {
				$rev = Revision::NewFromId($chktitle->getLatestRevID());
				$text = $rev->getText();
				if( $text !== false )
					$redirtitle = Title::newFromRedirect( $text );
				if( is_object( $redirtitle ) ) {
					$chktitle = $redirtitle;
					if (!$chktitle->exists())
						break;
					$parser->mOutput->addTemplate( $redirtitle, $redirtitle->getArticleID(), $redirtitle->getLatestRevID() );
				}
				else
					break;
			}
		}
	}
	
	if (!empty($subset_unknown) && count($subsets_found)>1) {
		// use '|' as separator since it's effectively impossible for it to appear in a wiki parameter
		$subsetlist = implode('|', array_keys($subsets_found));
		$pstack->unset_value('load_subset_unknown', NULL);
		$pstack->set($subsetlist, 'load_subset_unknown');
		if (isset($subset_used))
			$pstack->set($subset_used, 'subset');
	}
	
// temporary section of code to load Description or Author subpages
	if( !$datafound ) {
		$srcname = $loadtitle->getPrefixedText();
		foreach ($toget as $varname => $x) {
			// only do if description or author requested
			if ($varname!='description' && $varname!='author')
				continue;
			$title = Title::newFromText( $srcname . '/' . ucfirst( $varname ) );
			if ( $title->exists() ) {
				$value = efMetaTemplateTagParse( '{{' . $title->getPrefixedText() . '}}', $parser, $frame );
				$pstack->set($value, $varname);
			}
		}
	}
	return '';
}

// Implementation of {{#listsaved}} parser function
// To make this work efficiently, need to add index to mt_save_data
function efMetaTemplateImplementListsaved(&$parser) {
	// don't use standard ProcessArgs here because this routine needs special processing
	global $wgVersion;
	if( version_compare( $wgVersion, '1.12.0', '>=')) {
		$frame = func_get_arg(1);
		$targs = func_get_arg(2);
		$origargs = array();
		foreach ($targs as $arg)
			$origargs[] = $frame->expand($arg);
	}
	else {
		$frame = NULL;
		$origargs = func_get_args();
		array_shift($origargs);
	}
	$template = array_shift($origargs);
	$skip = false;
	$data = array();
	$where = array();
	$order = NULL;
	foreach ($origargs as $arg) {
		$arg = trim($arg);
		if ($arg===false)
			continue;
		if (preg_match('/^([^\s=]+?)\s*=\s*(.*)/', $arg, $matches)) {
			if ($matches[1]=='if')
				$skip = !($matches[2]==true);
			elseif ($matches[1]=='ifnot')
				$skip = ($matches[2]==true);
			elseif ($matches[1]=='order')
				$order = $matches[2];
			elseif ($matches[2]!='')
				$where[$matches[1]] = $matches[2];
			else
				$data[] = $matches[1];
		}
		else
			$data[] = $arg;
	}
	if ($skip)
		return '';
	if (is_null($template))
		return '<strong class="error">Listsaved error: No template provided</strong>';
	if (!count($where) || (count($where)==1 && array_key_exists('namespace', $where)))
		return '<strong class="error">Listsaved error: No condition provided</strong>';
	$tpl = Title::newFromText($template, NS_TEMPLATE);
	if (!$tpl || !$tpl->exists())
		return '<strong class="error">Listsaved error: Provided template, '.$template.', does not exist</strong>';
	$rev = Revision::NewFromId($tpl->getLatestRevID());
	$text = $rev->getText();
	$maxlen = trim(wfMsgForContent('mt_listsaved_template_maxlen'));
	if ($maxlen && strlen($text)>$maxlen)
		return '<strong class="error">Listsaved error: Provided template, '.$template.', is longer than '.$maxlen.' bytes</strong>';
	$disallowed = explode("\n", wfMsgForContent('mt_listsaved_template_disallowed'));
	foreach ($disallowed as $chkword) {
		if ($chkword && strpos($text, $chkword)!==false)
			return '<strong class="error">Listsaved error: Provided template, '.$template.', contains a disallowed word: '.$chkword.'</strong>';
	}
	
	$pstack = new MetaTemplateParserStack($parser, $frame);
	$defaults = array();
	foreach ($data as $arg) {
		if (!is_null($value=$pstack->get($arg)))
			$defaults[$arg] = $value;
	}
	
	$db = wfGetDB(DB_SLAVE);
	
	$cond = 'get.mt_save_varname IN (';
	$first = true;
	foreach (array_merge($data, array_keys($where)) as $arg) {
		if (!$first)
			$cond .= ', ';
		else
			$first = false;
		$cond .= '\''.addslashes($arg).'\'';
	}
	$cond .= ')';
	
	if (array_key_exists('namespace', $where)) {
		if (!ctype_digit($where['namespace'])) {
			if (is_null($where['namespace'] = MWNamespace::GetCanonicalIndex(strtolower($where['namespace']))))
				return '';
		}
		$cond .= ' AND page_namespace='.$where['namespace'];
		unset($where['namespace']);
	}
	
	$table = 'mt_save_set INNER JOIN page ON (mt_set_page_id=page_id AND mt_set_rev_id>=page_latest) INNER JOIN mt_save_data AS get ON (get.mt_save_id=mt_set_id)';
	$chknum = 1;
	
	foreach ($where as $varname => $value) {
		$table .= " INNER JOIN mt_save_data AS chk{$chknum} ON (chk{$chknum}.mt_save_id=mt_set_id AND chk{$chknum}.mt_save_varname='".addslashes($varname)."'";
		$table .= " AND chk{$chknum}.mt_save_value='".addslashes($value)."'";
		$table .= ')';
		$chknum++;
	}
	// Should this query be LIMIT'ed too?
	// Given that I'm requiring at least one where statement, the number of results shouldn't be
	// extreme.  Plus, a limit here will be a limit on nset*nparam, not just on nset.  And there's
	// no good way to allow a second call with an offset (since sorting is done post-SQL).
	$query = 'SELECT mt_save_set.*, get.*, page_namespace, page_title, page_latest FROM '.$table.' WHERE '.$cond.' ORDER BY mt_set_rev_id DESC, mt_set_id DESC';
	$result = $db->query($query);
	
	//	print "<br><br><br>query=$query<br>\nnumrows=".$db->numRows($result)."<br>\n";
	$orderarray = array('pagename', 'subset');
	if (!is_null($order)) {
		foreach (explode(',', $order) as $arg)
			array_unshift($orderarray, trim($arg));
	}
	
	global $wgContLang;
	$calls = array();
	$set_id = NULL;
	$row = false;
	$setdata = array();
	$donesets = array();
	while( true ) {
		$row=$db->fetchRow( $result );
		if (!is_null($set_id) && ($row===false || $row['mt_set_id']!=$set_id)) {
			$setorder = '';
			foreach ($orderarray as $arg) {
				if (array_key_exists($arg, $setdata))
					$setorder .= $setdata[$arg];
				$setorder .= '_';
			}
			$calls[$setorder] = '';
			foreach ($setdata as $varname => $value) {
				$calls[$setorder] .= "|$varname=$value";
			}
			$set_id = NULL;
		}
		if ($row===false)
			break;
		if (is_null($set_id)) {
			// NB revision id check for page was already done as part of original query
			$page_id = $row['mt_set_page_id'];
			if (isset($donesets[$page_id][$row['mt_set_subset']]))
				continue;
			$set_id = $row['mt_set_id'];
			$setdata = $defaults;
			$donesets[$page_id][$row['mt_set_subset']] = true;
			$setdata['subset'] = $row['mt_set_subset'];
			$setdata['pagename'] = str_replace('_', ' ', $row['page_title']);
			$setdata['namespace'] = $wgContLang->getNsText( $row['page_namespace'] );
			$tobj = Title::newFromID($page_id);
			$parser->mOutput->addTemplate( $tobj, $tobj->getArticleID(), $tobj->getLatestRevID() );
		}
		
		$value = $row['mt_save_value'];
		if (!$row['mt_save_parsed'])
			$value = efMetaTemplateTagParse( $value, $parser, $frame );
		$setdata[$row['mt_save_varname']] = $value;
	}
	
	ksort($calls);
	$text = '';
	foreach ($calls as $calldata) {
		$text .= '{{' . $template . $calldata . '}}';
	}
	return array($text, 'noparse' => false);
}

function efMetaTemplateImplementNestlevel(&$parser, $frame) {
	$pstack = new MetaTemplateParserStack($parser, $frame);
	
	if ($pstack->get_stackcount()<0 || is_null($value=$pstack->get('nestlevel')))
		$value = $pstack->get_stackcount();

	return sprintf("%d", $value);
}

function efMetaTemplateImplementNamespacex(&$parser) {
	$args = func_get_args();
	array_shift($args);
	return efMetaTemplatePreprocessTemplateName($parser, $args, 'NAMESPACE0');
}
function efMetaTemplateImplementPagenamex(&$parser) {
	$args = func_get_args();
	array_shift($args);
	return efMetaTemplatePreprocessTemplateName($parser, $args, 'PAGENAME0');
}
function efMetaTemplateImplementFullpagenamex(&$parser) {
	$args = func_get_args();
	array_shift($args);
	return efMetaTemplatePreprocessTemplateName($parser, $args, 'FULLPAGENAME0');
}

function efMetaTemplatePreprocessTemplateName(&$parser, $args, $nametype) {
	global $wgVersion;
	$level = 0;
	if( version_compare( $wgVersion, '1.12.0', '>=')) {
		$frame = $args[0];
		if (is_array($args[1]) && array_key_exists(1, $args) && array_key_exists(0, $args[1]))
			$level = $frame->expand($args[1][0]);
	}
	else {
		$frame = NULL;
		if (array_key_exists(0, $args))
			$level = $args[0];
	}
	return efMetaTemplateImplementTemplateName($parser, $frame, $nametype, $level);
}

function efMetaTemplateImplementTemplateName(&$parser, $frame, $nametype, $level=0) {
	global $wgContLang;
	$pstack = new MetaTemplateParserStack($parser, $frame);
	$frame->setVolatile();
	$title = $pstack->get_template_title($level);
	if (!is_object($title))
		return $title;
	switch( strtolower($nametype) ) {
		case 'namespace0':
			return str_replace('_',' ',$wgContLang->getNsText( $title->getNamespace() ) );
		case 'pagename0':
			return $title->getText();
		case 'fullpagename0':
		default:
			return $title->getPrefixedText();
	}
}

function efMetaTemplateTagParse($text, $parser, $frame) {
	global $wgVersion, $wgUser;
	static $localParser=NULL;
	// I'm worried about messing up the standard parser with this call
	// But I also need the standard parser to know about any transforms (comment mangling,etc) that need to
	// be undone
	// Somehow this was really messing up the parser status... somehow mTitle was ending up with NS=-1?
	/*	$prevoutputtype = $parser->mOutputType;
	$text = $parser->preSaveTransform($text, $parser->mTitle, $wgUser, $parser->mOptions, false );
	$parser->setOutputType($prevoutputtype);*/
	// even just pstPass2 is completely messing things up... returning empty text??
	//$text = $parser->pstPass2($text, $wgUser);
	if( version_compare( $wgVersion, '1.12.0', '>=')) {
		/*		if (MetaTemplateParserStack::is_docroot($parser, $frame))
			$flag = 0;
		else
			$flag = Parser::PTD_FOR_INCLUSION;
		$dom = $parser->preprocessToDom( $text, $flag );
		$newframe = $frame->newChild( false, false );
		$output = $newframe->expand( $dom );*/
		$output = $parser->recursiveTagParse( $text, $frame );
	}
	else {
		$output = $parser->recursiveTagParse( $text );
	}
	return $output;
}

function efMetaTemplateDisplaycode( $input, $args, $parser, $frame=NULL ) {
	if( efMetaTemplateDisplayMode( $parser, $frame ) )
		return '<code>' . htmlspecialchars( $input ) . '</code>';
	else
		return efMetaTemplateTagParse( $input, $parser, $frame );
}

function efMetaTemplateCleanspace( $input, $args, $parser, $frame=NULL ) {
	$output = preg_replace( '/([\]\}\>])\s+([\<\{\[])/s', '$1$2', trim( $input ) );
	// categories and trails are stripped on ''any'' template page, not just when directly calling the template
	// (but only in non-preview mode)
	if( efMetaTemplateDisplayMode( $parser, $frame, true, false, false ) ) {
// save categories before processing
		$precats = $parser->mOutput->getCategories();
		$output = efMetaTemplateTagParse( $output, $parser, $frame );
		$output = preg_replace('/\s*<\s*div\s+[^>]*class\s*=\s*"?breadcrumb[^>]*>.*?<\s*\/\s*div[^>]*>\s*/is', '', $output);
// reset categories to the pre-processing list to remove any new categories
		$parser->mOutput->setCategoryLinks( $precats );
	}
	else {
		$output = efMetaTemplateTagParse( $output, $parser, $frame );
	}
	return $output;
}

// need to handle nested tables better
// unset links that are removed (remove from wantedlinks)?
// fix issues with <small> tags ... if cell is empty except for paired tags, consider it empty
function efMetaTemplateCleantable( $input, $args, $parser, $frame=NULL ) {
	if (isset($parser->mLinkHolders->internals))
		$initialLinks = count($parser->mLinkHolders->internals);
	else
		$initialLinks = 0;
	
	$input = efMetaTemplateTagParse( $input, $parser, $frame );
	if( efMetaTemplateDisplayMode( $parser, $frame ) )
		return $input;
	
	// don't bother to waste CPU doing preg_matches on 'a href' if there aren't any links to start
	// with... or if parser structure has changed and mLinks is no longer where data is stored...
	// ... or if no links were added by tag contents
	$dolinks = false;
	if (isset($parser->mLinkHolders->internals)) {
		if (count($parser->mLinkHolders->internals)>$initialLinks)
			$dolinks = true;
	}
	
	// can use marker names system similar to parser, given that parser has finished processing this HTML chunk
	// to be safe, underscore instead of dash
	$marker = "\x7fUNIQ_table_QINU\x7f";
	$marker_len = strlen($marker);
	$splittables = preg_split('/(<\/?table(?:\s.*?>|>\s*))/is', $input, -1/*nolimit*/, PREG_SPLIT_DELIM_CAPTURE);
	
	$tablestack = array( 0 => '');
	$subtables = array( 0 => array());
	$ntable = 0;
	foreach ($splittables as $currtext) {
		// delimiter starting new table
		if (strtolower(substr($currtext,0,6))=='<table') {
			$ntable++;
			$tablestack[$ntable] = $currtext;
			$subtables[$ntable] = array();
		}
		// delimiter ending current table
		elseif (strtolower(substr($currtext,0,7))=='</table') {
			$tablestack[$ntable] .= $currtext;
			$cleaned = efMetaTemplateDoCleantable($tablestack[$ntable], $dolinks, $parser);
			// I could check here for empty tables (<table[^>]*>\s+</table>) and delete them... but is that what should be done?
			$demangled = '';
			$mlast = 0;
			for ($isub=0; $isub<count($subtables[$ntable]); $isub++) {
				$mloc = strpos($cleaned, $marker, $mlast);
				$demangled .= substr($cleaned,$mlast,$mloc-$mlast);
				$demangled .= $subtables[$ntable][$isub];
				$mlast = $mloc+$marker_len;
			}
			$demangled .= substr($cleaned,$mlast);
			
			unset($tablestack[$ntable]);
			unset($subtables[$ntable]);
			$ntable--;
			if ($ntable) {
				$subtables[$ntable][] = $demangled;
				$tablestack[$ntable] .= $marker;
			}
			else {
				$tablestack[$ntable] .= $demangled;
			}
		}
		else {
			$tablestack[$ntable] .= $currtext;
		}
	}
	
	$output = $tablestack[0];
	return $output;
}

// the function that actually does the work of cleantable, called each time a table is closed
function efMetaTemplateDoCleantable( $input, $dolinks, $parser ) {
	$output = '';
	$deletedlinks = array();
	$rawrows = preg_split('/(\s*<tr(?:\s.*?>|>\s*))/is', $input, -1/*nolimit*/, PREG_SPLIT_DELIM_CAPTURE);
	$output .= $rawrows[0];
	$posttext = '';
	$rows = array();
		
	$colspan = NULL;		
	for ($k=0, $j=1; $j<count($rawrows); $j++) {
		if (preg_match('/colspan\s*=\s*\"?(\d+)/', $rawrows[$j], $matches)) {
			if (is_null($colspan) || $matches[1]>$colspan)
				$colspan = $matches[1];
		}
		if ($j%2) {
			$rows[$k] = $rawrows[$j];
		}
		else {
			$rows[$k] .= $rawrows[$j];
			$k++;
		}
	}

	$k = count($rows)-1;
	if (preg_match('/^(.*?<\s*\/\s*tr(?:\s*[^>]*>|>))(.*)$/is', $rows[$k], $matches) ) {
		$rows[$k] = $matches[1];
		$posttext = $matches[2];
	}

	$empty = array();
	$header = array();
	for ($k=0; $k<count($rows); $k++) {
		$header[$k] = false;
		if ( !is_null($colspan) && preg_match('/colspan\s*=\s*\"?'.$colspan.'[\"\s>]/is', $rows[$k]) && preg_match('/<th/', $rows[$k])) {
			$header[$k] = true;
		}
		preg_match_all('/<\s*td(?:\s*[^>]*>|>)(.*?)<\s*\/\s*td(?:\s*[^>]*>|>)/is', $rows[$k], $cells, PREG_PATTERN_ORDER);
		if (count($cells[1]))
			$empty[$k] = true;
		else
			$empty[$k] = false;
		for ($l=0; $l<count($cells[1]); $l++) {
			// html tags (<..>) and unset template params ({{{..}}}) can be present in an "empty" cell
			// anything else is considered non-empty
			// BUT have to keep <!--LINK 0:0-->
			//     gets converted into proper link later
			if (!preg_match('/^\s*(?:(?:<[^!][^>]+>\s*)|(?:{{{[^}]*}}}\s*))*\s*$/is', $cells[1][$l]))
				$empty[$k] = false;
		}
	}
	
	$emptyset = false;
	$clearhead = NULL;
	for ($k=0; $k<count($rows); $k++) {
		if (!$emptyset) {
			if ($empty[$k]) {
				$emptyset = true;
				if ($k>2 && $header[$k-1])
					$clearhead = $k-1;
				else
					$clearhead = NULL;
			}
		}
		else {
			if (!$empty[$k]) {
				if ($header[$k] && !is_null($clearhead)) {
					$empty[$clearhead] = true;
				}
				$emptyset = false;
			}
		}
	}
	if ($emptyset && !is_null($clearhead))
		$empty[$clearhead] = true;
	
	for ($k=0; $k<count($rows); $k++) {
		if (!$empty[$k])
			$output .= $rows[$k];
		else if ($dolinks) {
			// links are all represented by placeholders at this point
			// and each placeholder is unique .... even if they all point to the same final link
			if (preg_match_all('/<!--\s*LINK\s+(\d+):(\d+)\s*-->/', $rows[$k], $matches, PREG_SET_ORDER)) {
				foreach ($matches as $mset) {
					unset($parser->mLinkHolders->internals[$mset[1]][$mset[2]]);
					if (!count($parser->mLinkHolders->internals[$mset[1]]))
						unset($parser->mLinkHolders->internals[$mset[1]]);
				}
			}
		}
	}
	$output .= $posttext;
	return $output;
}

function efMetaTemplateDisplayMode(&$parser, &$frame, $istemplate=true, $ispreview=false, $chknestlevel = true ) {
	global $wgRequest, $wgTitle;
	$mode = true;
	if( !is_null( $istemplate ) ) {
		if( $chknestlevel ) 
			$mode &= ( $istemplate == ( MetaTemplateParserStack::is_docroot($parser, $frame) && $parser->getTitle()->getNamespace()==NS_TEMPLATE ) );
		else
			$mode &= ( $istemplate == ( $parser->getTitle()->getNamespace()==NS_TEMPLATE ) );
	}
	if ( !is_null( $ispreview ) ) {
		// preview mode only applies if parser title matches global title -- otherwise we're on job queue
		// processing
		$previewmode = ( $wgRequest->getCheck( 'wpPreview' ) || $wgRequest->getCheck( 'wpLivePreview' ) ) && ( $parser->getTitle()->getArticleID()==$wgTitle->getArticleID() );
		$mode &= ( $ispreview == $previewmode );
	}
	return $mode;
}

// code copied from ParserFunctions
// copy of ifexist that does NOT add link to wanted pages
// code that created links is commented out
function efMetaTemplateIncrementIfexistCount( $parser, $frame ) {
		// Don't let this be called more than a certain number of times. It tends to make the database explode.
	global $wgExpensiveParserFunctionLimit;
	$parser->mExpensiveFunctionCount++;
	if ( $frame ) {
		$pdbk = $frame->getPDBK( 1 );
		if ( !isset( $parser->pf_ifexist_breakdown[$pdbk] ) ) {
			$parser->pf_ifexist_breakdown[$pdbk] = 0;
		}
		$parser->pf_ifexist_breakdown[$pdbk] ++;
	}
	return $parser->mExpensiveFunctionCount <= $wgExpensiveParserFunctionLimit;
}

// Implementation of {{#ifexistx}} parser function
function efMetaTemplateIfExist( &$parser ) {
	global $wgContLang;
	$args = func_get_args();
	array_shift($args);
	$data = efMetaTemplateProcessArgs($args, $frame, $matchcase, $skip);
	list($titletext, $then, $else) = array_pad($data, 3, '');
	
	$title = Title::newFromText( $titletext );
	$wgContLang->findVariantLink( $titletext, $title, true );
	if ( $title ) {
		if( $title->getNamespace() == NS_MEDIA ) {
				/* If namespace is specified as NS_MEDIA, then we want to
				 * check the physical file, not the "description" page.
				 */
			if ( !efMetaTemplateIncrementIfexistCount( $parser, $frame ) ) {
				return $else;
			}
			$file = wfFindFile($title);
			if ( !$file ) {
				return $else;
			}
			//			$parser->mOutput->addImage($file->getName());
			return $file->exists() ? $then : $else;
		} elseif( $title->getNamespace() == NS_SPECIAL ) {
				/* Don't bother with the count for special pages,
				 * since their existence can be checked without
				 * accessing the database.
				 */
			return SpecialPage::exists( $title->getDBkey() ) ? $then : $else;
		} elseif( $title->isExternal() ) {
				/* Can't check the existence of pages on other sites,
				 * so just return $else.  Makes a sort of sense, since
				 * they don't exist _locally_.
				 */
			return $else;
		} else {
			$pdbk = $title->getPrefixedDBkey();
			$lc = LinkCache::singleton();
			if ( !efMetaTemplateIncrementIfexistCount( $parser, $frame ) ) {
				return $else;
			}
			if ( 0 != ( $id = $lc->getGoodLinkID( $pdbk ) ) ) {
				//				$parser->mOutput->addLink( $title, $id );
				return $then;
			} elseif ( $lc->isBadLink( $pdbk ) ) {
				//				$parser->mOutput->addLink( $title, 0 );
				return $else;
			}
			$id = $title->getArticleID();
			//			$parser->mOutput->addLink( $title, $id );
			if ( $id ) {
				return $then;
			}
		}
	}
	return $else;
}

// Dynamic function
function efMetaTemplateArg( &$parser, $name = '', $default = '' ) {
	global $wgRequest;
	$parser->disableCache();
	return $wgRequest->getVal($name, $default);
}

 
function efMetaTemplateRand( &$parser, $a = 0, $b = 1 ) {
	$parser->disableCache();
	return mt_rand( intval($a), intval($b) );
}

 
function efMetaTemplateSkin( &$parser ) {
	global $wgUser, $wgRequest;
	$parser->disableCache();
	return $wgRequest->getVal('useskin', $wgUser->getOption('skin'));
}