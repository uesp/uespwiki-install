<?php
# Confirm it's being called from a valid entry point; skip unless MEDIAWIKI is defined
if (!defined('MEDIAWIKI')) {
        echo <<<EOT
To install the MetaTemplate extension, put the following line in LocalSettings.php:
require_once( \$IP . '/extensions/MetaTemplate/MetaTemplate.php' );
EOT;
        exit( 1 );
}

// explore making this code work with up-to-date wiki version.... then posting it??
// check what was done in previous SVN commits
// need to check LoadExtensionSchemaUpdates hook and use it for generating the DB tables

// eventually, look into having mt_save_std as well as mt_save_data
// provide a mechanism for specifying a list of fields to appear in mt_save_std, along with 
// maximum sizes for those fields (ideally via a mediawiki page ... although changes wouldn't
// really take effect until server-side script runs)
// limit #listsaved to only allowing searches based on mt_save_std fields
// (or have a $eg variable to add that restriction)
// any parameters used in templates such as Place Link (that appear multiple times on a page) should be in _std
// plus particularly small fields
// need an update script that allows any set of fields to be implemented (including future
// changes to the set of fields -- so allow data to be transferred from one table to another as the
// definitions change)
$wgExtensionCredits['other'][] = array(
	'name' => 'MetaTemplate',
	'author' => 'Nephele',
	'url' => 'http://www.uesp.net/wiki/UESPWiki:MetaTemplate',
	'description' => 'Features to make templates more powerful',
	'version' => '1.0.5',
);

/*
 * Extension definitions
 */
define('MAG_METATEMPLATE_DEFINE', 'define');
define('MAG_METATEMPLATE_PREVIEW', 'preview');
define('MAG_METATEMPLATE_UNSET', 'unset');
define('MAG_METATEMPLATE_INHERIT', 'inherit');
define('MAG_METATEMPLATE_INCLUDE', 'include');
define('MAG_METATEMPLATE_TRIMLINKS', 'trimlinks');
define('MAG_METATEMPLATE_SAVE', 'save');
define('MAG_METATEMPLATE_LOAD', 'load');
define('MAG_METATEMPLATE_LISTSAVED', 'listsaved');
define('MAG_METATEMPLATE_NESTLEVEL', 'NESTLEVEL');
define('MAG_METATEMPLATE_RETURN', 'return');
define('MAG_METATEMPLATE_LOCAL', 'local');
define('MAG_METATEMPLATE_SPLITARGS', 'splitargs');
define('MAG_METATEMPLATE_PICKFROM', 'pickfrom');
define('MAG_METATEMPLATE_DISPLAYCODE', 'displaycode');
define('MAG_METATEMPLATE_CLEANSPACE', 'cleanspace');
define('MAG_METATEMPLATE_CLEANTABLE', 'cleantable');
define('MAG_METATEMPLATE_NAMESPACE0', 'NAMESPACE0');
define('MAG_METATEMPLATE_PAGENAME0', 'PAGENAME0');
define('MAG_METATEMPLATE_FULLPAGENAME0', 'FULLPAGENAME0');
define('MAG_METATEMPLATE_NAMESPACEx', 'NAMESPACEx');
define('MAG_METATEMPLATE_PAGENAMEx', 'PAGENAMEx');
define('MAG_METATEMPLATE_FULLPAGENAMEx', 'FULLPAGENAMEx');
define('MAG_METATEMPLATE_CATPAGETEMPLATE', 'catpagetemplate');
define('MAG_METATEMPLATE_IFEXISTX', 'ifexistx');
define('MAG_METATEMPLATE_EXPLODEARGS', 'explodeargs');

# Global variable that enables all save/load-related functions
# This is reset to false later if mt_save_data and mt_save_set tables do not exist 
$egMetaTemplateEnableSaveLoad = true;
$egMetaTemplateEnableCatPageTemplate = true;

$dir = dirname(__FILE__) . '/';
$dirspec = $wgScriptPath . '/extensions' . preg_replace('/.*\/extensions/', '', $dir);

# Include the rest of the always-loaded extension code
require_once( $dir . "MetaTemplate_body.php");

# Tell Mediawiki where to find files containing extension-specific classes
if ( version_compare ( $wgVersion, '1.12.0', '>=' ) ) {
	$wgAutoloadClasses['MetaTemplateParserStack'] = $dir . 'MetaTemplateParserStack_v14.php';
	$wgParserConf['preprocessorClass'] = 'Preprocessor_Uesp';
	$wgAutoloadClasses['Preprocessor_Uesp'] = $dir . 'MetaTemplatePPFrame.php';
}
else
	$wgAutoloadClasses['MetaTemplateParserStack'] = $dir . 'MetaTemplateParserStack_v10.php';
$wgAutoloadClasses['MetaTemplateSaveData'] = $dir . 'MetaTemplateSaveData.php';
$wgAutoloadClasses['MetaTemplateCategoryPage'] = $dir . 'MetaTemplateCategoryPage.php';
$wgAutoloadClasses['MetaTemplateCategoryViewer'] = $dir . 'MetaTemplateCategoryPage.php';
if (!isset($wgAutoloadClasses['CategoryTreeCategoryPage']))
	$wgAutoloadClasses['CategoryTreeCategoryPage'] = $dir . 'FakeCategoryTreeStubs.php';

# Intialization function where most of customization is done
$wgExtensionFunctions[] = 'efMetaTemplateInit';

/* 
 * Add Hooks
 */

# Add magic words and parser functions
# Comment out the first line to disable all magic words
$wgHooks['MagicWordwgVariableIDs'][] = 'efMetaTemplateDeclareMagicWord';
$wgHooks['LanguageGetMagic'][] = 'efMetaTemplateMagicWords';
$wgHooks['ParserGetVariableValueSwitch'][] = 'efMetaTemplateAssignMagicWord';
if ($egMetaTemplateEnableSaveLoad)
	$wgHooks['LoadExtensionSchemaUpdates'][] = 'efMetaTemplateSchemaUpdates';
if ($egMetaTemplateEnableCatPageTemplate)
	$wgHooks['ArticleFromTitle'][] = 'efMetaTemplateArticleFromTitle';

# Load messages
$wgExtensionMessagesFiles['metatemplate'] = $dir . 'MetaTemplate.i18n.php';
$wgExtensionAliasesFiles['metatemplate'] = $dir . 'MetaTemplate.alias.php';
$wgHooks['LoadAllMesages'][] = 'efMetaTemplateLoadMessages';

/*
 * Initialization functions
 */

# This function is called as soon as setup is done
# Loads extension messages and does some other initialization that can be safely moved out of global
function efMetaTemplateInit() {
	global $wgVersion, $wgMessageCache, $wgSearchType, $wgParser, $wgRequest, $wgHooks;
	global $egUespNamespace;
	$dir = dirname(__FILE__) . '/';

	global $egMetaTemplateEnableSaveLoad;
	if ($egMetaTemplateEnableSaveLoad) {
		$db = wfGetDB(DB_SLAVE);
		if (!$db->tableExists('mt_save_data') || !$db->tableExists('mt_save_set'))
			$egMetaTemplateEnableSaveLoad = false;
	}
	// Parser function hooks
	// To disable a specific function, comment out the corresponding line

	// checks to enable hooks only when necessary
	// except, in most cases these checks CANNOT be used because job queue can attach processing
	// to any request; if job queue needs to regenerate an article using one of these commands,
	// then hook needs to be in place or else regenerated version will be useless
	$ispreview = $wgRequest->getCheck( 'wpPreview' ) || $wgRequest->getCheck( 'wpLivePreview' );
// $wgTitle hasn't been initialized yet... so rely on wgRequest instead
//	$istemplate = $wgTitle->getNamespace()==NS_TEMPLATE;
	$istemplate = substr( $wgRequest->getVal( 'title' ), 0, 9) == 'Template:' ;
	
	if( version_compare( $wgVersion, '1.12.0', '>='))
		$hookoption = SFH_OBJECT_ARGS;
	else
		$hookoption = 0;
		
	
	$wgParser->setFunctionHook( MAG_METATEMPLATE_DEFINE, 'efMetaTemplateImplementDefine', $hookoption);
	// previews are only on Template pages when in preview mode; if not activated, call blank instead
	// this one check is reliable here, because previews are never done by job queue
	if ( $istemplate && $ispreview )
		$wgParser->setFunctionHook( MAG_METATEMPLATE_PREVIEW, 'efMetaTemplateImplementPreview', $hookoption);
	else
		$wgParser->setFunctionHook( MAG_METATEMPLATE_PREVIEW, 'efMetaTemplateBlank');
	$wgParser->setFunctionHook( MAG_METATEMPLATE_UNSET, 'efMetaTemplateImplementUnset', $hookoption);
	$wgParser->setFunctionHook( MAG_METATEMPLATE_INHERIT, 'efMetaTemplateImplementInherit', $hookoption);
	$wgParser->setFunctionHook( MAG_METATEMPLATE_RETURN, 'efMetaTemplateImplementReturn', $hookoption);
	$wgParser->setFunctionHook( MAG_METATEMPLATE_LOCAL, 'efMetaTemplateImplementLocal', $hookoption);
	$wgParser->setFunctionHook( MAG_METATEMPLATE_IFEXISTX, 'efMetaTemplateIfExist', $hookoption);
	
	if ($egMetaTemplateEnableSaveLoad) {
		$wgParser->setFunctionHook( MAG_METATEMPLATE_SAVE, 'efMetaTemplateImplementSave', $hookoption);
		$wgParser->setFunctionHook( MAG_METATEMPLATE_LOAD, 'efMetaTemplateImplementLoad', $hookoption);
		$wgParser->setFunctionHook( MAG_METATEMPLATE_LISTSAVED, 'efMetaTemplateImplementListsaved', $hookoption);

		$wgHooks['ArticleDeleteComplete'][] = 'MetaTemplateSaveData::OnDelete';
		$wgHooks['TitleMoveComplete'][] = 'MetaTemplateSaveData::OnMove';
	}
	
	$wgParser->setFunctionHook( MAG_METATEMPLATE_NAMESPACEx, 'efMetaTemplateImplementNamespacex', SFH_NO_HASH | $hookoption );
	$wgParser->setFunctionHook( MAG_METATEMPLATE_PAGENAMEx, 'efMetaTemplateImplementPagenamex', SFH_NO_HASH | $hookoption );
	$wgParser->setFunctionHook( MAG_METATEMPLATE_FULLPAGENAMEx, 'efMetaTemplateImplementFullpagenamex', SFH_NO_HASH | $hookoption );
	
	$wgParser->setFunctionHook( MAG_METATEMPLATE_SPLITARGS, 'efMetaTemplateImplementSplitargs', $hookoption);
// explodeargs and include do not access parser stack, but still need frame (to expand templates)
	$wgParser->setFunctionHook( MAG_METATEMPLATE_EXPLODEARGS, 'efMetaTemplateImplementExplodeargs', $hookoption );
	$wgParser->setFunctionHook( MAG_METATEMPLATE_INCLUDE, 'efMetaTemplateImplementInclude', $hookoption );
// these functions do not access parser stack and therefore can use old-style hook arguments
	$wgParser->setFunctionHook( MAG_METATEMPLATE_PICKFROM, 'efMetaTemplateImplementPickfrom' );
	$wgParser->setFunctionHook( MAG_METATEMPLATE_TRIMLINKS, 'efMetaTemplateImplementTrimlinks');
	
	// Tag function hooks
	$wgParser->setHook( MAG_METATEMPLATE_DISPLAYCODE, 'efMetaTemplateDisplaycode' );
	$wgParser->setHook( MAG_METATEMPLATE_CLEANSPACE, 'efMetaTemplateCleanspace' );
	$wgParser->setHook( MAG_METATEMPLATE_CLEANTABLE, 'efMetaTemplateCleantable' );
	global $egMetaTemplateEnableCatPageTemplate;
	if ($egMetaTemplateEnableCatPageTemplate)
		$wgParser->setHook( MAG_METATEMPLATE_CATPAGETEMPLATE, 'efMetaTemplateCatPageTemplate' );
	
	efMetaTemplateLoadMessages();
	return true;	
}

function efMetaTemplateLoadMessages() {
	global $wgVersion;
	static $loaded = false;
	if ($loaded)
		return true;
	
	$dir = dirname(__FILE__) . '/';
# Old way to load messages 
	if( version_compare( $wgVersion, '1.11.0', '<')) {
		global $wgMessageCache;
                require( $dir . 'MetaTemplate.i18n.php' );
                foreach ( $messages as $lang => $langMessages ) {
                        $wgMessageCache->addMessages( $langMessages, $lang );
                }
	}

	return true;
}

function efMetaTemplateMagicWords(&$aWikiWords, $langID) {
	# variables
	# (0 means case-insensitive)

	# functions
	$aWikiWords[MAG_METATEMPLATE_DEFINE] = array(0, 'define');
	$aWikiWords[MAG_METATEMPLATE_PREVIEW] = array(0, 'preview');
	$aWikiWords[MAG_METATEMPLATE_UNSET] = array(0, 'unset');
	$aWikiWords[MAG_METATEMPLATE_INHERIT] = array(0, 'inherit');
	$aWikiWords[MAG_METATEMPLATE_INCLUDE] = array(0, 'include');
	$aWikiWords[MAG_METATEMPLATE_TRIMLINKS] = array(0, 'trimlinks');
	$aWikiWords[MAG_METATEMPLATE_SAVE] = array(0, 'save');
	$aWikiWords[MAG_METATEMPLATE_LOAD] = array(0, 'load');
	$aWikiWords[MAG_METATEMPLATE_LISTSAVED] = array(0, 'listsaved');
	$aWikiWords[MAG_METATEMPLATE_NESTLEVEL] = array(0, 'NESTLEVEL');
	$aWikiWords[MAG_METATEMPLATE_NAMESPACE0] = array(0, 'NAMESPACE0');
	$aWikiWords[MAG_METATEMPLATE_PAGENAME0] = array(0, 'PAGENAME0');
	$aWikiWords[MAG_METATEMPLATE_FULLPAGENAME0] = array(0, 'FULLPAGENAME0');
	$aWikiWords[MAG_METATEMPLATE_NAMESPACEx] = array(0, 'NAMESPACEx');
	$aWikiWords[MAG_METATEMPLATE_PAGENAMEx] = array(0, 'PAGENAMEx');
	$aWikiWords[MAG_METATEMPLATE_FULLPAGENAMEx] = array(0, 'FULLPAGENAMEx');
	$aWikiWords[MAG_METATEMPLATE_RETURN] = array(0, 'return');
	$aWikiWords[MAG_METATEMPLATE_LOCAL] = array(0, 'local');
	$aWikiWords[MAG_METATEMPLATE_SPLITARGS] = array(0, 'splitargs');
	$aWikiWords[MAG_METATEMPLATE_PICKFROM] = array(0, 'pickfrom');
	$aWikiWords[MAG_METATEMPLATE_IFEXISTX] = array(0, 'ifexistx');
	$aWikiWords[MAG_METATEMPLATE_EXPLODEARGS] = array(0, 'explodeargs');
	
	#must do this or you will silence every LanguageGetMagic
	#hook after this!
	return true;
}

function efMetaTemplateArticleFromTitle( &$title, &$article ) {
	if ( $title->getNamespace() == NS_CATEGORY ) {
		$article = new MetaTemplateCategoryPage( $title );
	}
	return true;
}

// Completely untested!!!
function efMetaTemplateSchemaUpdates() {
	global $wgExtNewTables;
	// based on FlaggedRevs example, it appears OK to create multiple tables within one sql file
	$wgExtNewTables[] = array( 'mt_save_set', dirname( __FILE__ ) . '/install.sql' );
	return true;
}

// old PHP doesn't seem to like trying to directly access MetaTemplateCategoryViewer::catPageTemplate
// from call_user_func_array
function efMetaTemplateCatPageTemplate( $input, $args, $parser, $frame=NULL ) {
	return MetaTemplateCategoryViewer::catPageTemplate($input, $args, $parser, $frame);
}
