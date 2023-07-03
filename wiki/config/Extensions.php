<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains extension includes and settings.
# It is included by LocalSettings.php.
#

#
# Keep this array updated as extensions are added or removed. It is currently only used when
# upgrading MediaWiki in order to automatically upgrade extensions as needed.
#
#   EXTENSION_DIRECTORY => VALUE,
#
$UESP_EXT_DEFAULT = 0;		// Included with the MW extension
$UESP_EXT_UPGRADE = 1;		// Upgrade with the uesp-getmwext script
$UESP_EXT_OTHER = 2;		// Needs a manual upgrade
$UESP_EXT_NONE = 3;			// Doesn't need an upgrade
$UESP_EXT_IGNORE = 4;		// Don't do anything

$UESP_EXTENSION_INFO = [
	"AbuseFilter" => $UESP_EXT_UPGRADE,
	"AntiSpoof" => $UESP_EXT_UPGRADE,
	"CharInsert" => $UESP_EXT_UPGRADE,
	"CheckUser" => $UESP_EXT_UPGRADE,
	"CirrusSearch" => $UESP_EXT_UPGRADE,
	"Cite" => $UESP_EXT_DEFAULT,
	"CiteThisPage" => $UESP_EXT_DEFAULT,
	"ConfirmEdit" => $UESP_EXT_DEFAULT,
	"DaedricFont" => $UESP_EXT_NONE,
	"DailyEdits" => $UESP_EXT_NONE,
	"DeleteBatch" => $UESP_EXT_UPGRADE,
	"DisableAccount" => $UESP_EXT_UPGRADE,
	"Disambiguator" => $UESP_EXT_UPGRADE,
	"DismissableSiteNotice" => $UESP_EXT_UPGRADE,
	"DragonFont" => $UESP_EXT_NONE,
	"DwemerFont" => $UESP_EXT_NONE,
	"Editcount" => $UESP_EXT_NONE,
	"Elastica" => $UESP_EXT_UPGRADE,
	"EmbedVideo" => $UESP_EXT_OTHER,		//1.29
	"EsoCharData" => $UESP_EXT_NONE,
	"FalmerFont" => $UESP_EXT_NONE,
	"Gadgets" => $UESP_EXT_DEFAULT,
	"Graph" => $UESP_EXT_UPGRADE,
	"ImageMap" => $UESP_EXT_DEFAULT,
	"InputBox" => $UESP_EXT_DEFAULT,
	"Interwiki" => $UESP_EXT_DEFAULT,
	"JobQueue" => $UESP_EXT_NONE,
	"JsonConfig" => $UESP_EXT_UPGRADE,
	"LabeledSectionTransclusion" => $UESP_EXT_UPGRADE,
	"LocalisationUpdate" => $UESP_EXT_DEFAULT,
	"LogPageRenderTimes" => $UESP_EXT_NONE,
	"MediaFunctions" => $UESP_EXT_UPGRADE,
	"MetaTemplate" => $UESP_EXT_NONE,
	"MobileFrontend" => $UESP_EXT_UPGRADE,
	"MwEmbedSupport" => $UESP_EXT_OTHER,
	"NativeSvgHandler" => $UESP_EXT_NONE,		// Doesn't have versions available before 1.35
	"Nuke" => $UESP_EXT_DEFAULT,
	"PageImages" => $UESP_EXT_UPGRADE,
	"PageSpeedLog" => $UESP_EXT_NONE,
	"ParserFunctions" => $UESP_EXT_DEFAULT,
	"ParserHelper" => $UESP_EXT_NONE,
	"Patroller" => $UESP_EXT_UPGRADE,
	"PdfHandler" => $UESP_EXT_DEFAULT,
	"Poem" => $UESP_EXT_DEFAULT,
	"Popups" => $UESP_EXT_UPGRADE,
	//"ProtectSection" => $UESP_EXT_NONE,
	"RecentPopularPages" => $UESP_EXT_NONE,
	"RegexFunctions" => $UESP_EXT_UPGRADE,
	"Renameuser" => $UESP_EXT_DEFAULT,
	"Riven" => $UESP_EXT_NONE,
	"Scribunto" => $UESP_EXT_UPGRADE,
	"SearchLog" => $UESP_EXT_NONE,
	"SpamBlacklist" => $UESP_EXT_DEFAULT,
	"SyntaxHighlight_GeSHi" => $UESP_EXT_DEFAULT,
	"Tabs" => $UESP_EXT_UPGRADE,
	"TextExtracts" => $UESP_EXT_UPGRADE,
	"TemplateStyles" => $UESP_EXT_UPGRADE,
	"TimedMediaHandler" => $UESP_EXT_UPGRADE,
	"TitleBlacklist" => $UESP_EXT_DEFAULT,
	"TorBlock" => $UESP_EXT_UPGRADE,
	"UespCustomCode" => $UESP_EXT_NONE,
	"UespCustomNew" => $UESP_EXT_NONE,
	"UespEsoData" => $UESP_EXT_NONE,
	"UespEsoItemLink" => $UESP_EXT_NONE,
	"UespEsoSkills" => $UESP_EXT_NONE,
	"UespGameMap" => $UESP_EXT_NONE,
	"UespLegendsCards" => $UESP_EXT_NONE,
	"UespMap" => $UESP_EXT_NONE,
	"UespPatreon" => $UESP_EXT_NONE,
	"UespShortLinks" => $UESP_EXT_NONE,
	"UploadWizard" => $UESP_EXT_UPGRADE,
	"UsersEditCount" => $UESP_EXT_NONE,
	"WikiEditor" => $UESP_EXT_DEFAULT,
	"WikiTextLoggedInOut" => $UESP_EXT_UPGRADE,
		
	"FakeGraph" => $UESP_EXT_IGNORE,
	"ParserHelperBackup" => $UESP_EXT_IGNORE,
	"FakeGraphBackup" => $UESP_EXT_IGNORE,
	"MetaTemplateBackup" => $UESP_EXT_IGNORE,
	"RivenBackup" => $UESP_EXT_IGNORE,
];

if ($UESP_UPGRADING_MW == 1) return;

wfLoadExtension( 'ParserHelper' ); // Needs to be before both MetaTemplate and Riven.

require_once( "$IP/extensions/AbuseFilter/AbuseFilter.php" );
$wgAbuseFilterEmergencyDisableThreshold['default'] = 0.5;

require_once( "$IP/extensions/AntiSpoof/AntiSpoof.php" );
wfLoadExtension( "CharInsert" );
wfLoadExtension( "CheckUser" );
wfLoadExtension( "Cite" );
wfLoadExtension( "CiteThisPage" );

// Based on http://thingelstad.com/stopping-mediawiki-spam-with-dynamic-questy-captchas/
wfLoadExtension( "ConfirmEdit" );
require_once("$IP/extensions/ConfirmEdit/QuestyCaptcha.php");
$wgCaptchaClass = 'QuestyCaptcha';

# Now a more complicated one
# Generate a random string 10 characters long
$myChallengeLength = rand(2, 5);
$myChallengeIndex = rand(0, 12 - $myChallengeLength);
$myChallengeString = md5(uniqid(mt_rand(), true));
$prefix = substr($myChallengeString, 0, $myChallengeIndex);
$answer = substr($myChallengeString, $myChallengeIndex, $myChallengeLength);
$suffix = substr($myChallengeString, $myChallengeIndex + $myChallengeLength, 12 - $myChallengeIndex + $myChallengeLength);
$myChallengeString = "<span style='background-color:white'>$prefix</span><span style='background-color:lightgreen'>$answer</span><span style='background-color:lightgrey'>$suffix</span>";
# Pick a random location in the string

# Build the question/anwer
$wgCaptchaQuestions[] = array (
    'question' => "Please enter the characters highlighted in green in the following sequence: <code>$myChallengeString</code>",
    'answer' => $answer
);
// ------------------
$wgCaptchaTriggers['addurl'] = false;

require_once( "$IP/extensions/DailyEdits/DailyEdits.php" );
$wgDailyEditsGraphFile = "//content3.uesp.net/w/extensions/UespCustomCode/files/dailyedits.png";

require_once( "$IP/extensions/DeleteBatch/DeleteBatch.php" );
wfLoadExtension( "Disambiguator" );
require_once( "$IP/extensions/DismissableSiteNotice/DismissableSiteNotice.php" );
require_once( "$IP/extensions/Editcount/Editcount.php" );
wfLoadExtension( "Gadgets" );
wfLoadExtension( "ImageMap" );
wfLoadExtension( "InputBox" );
require_once( "$IP/extensions/JobQueue/JobQueue.php" );
require_once( "$IP/extensions/LabeledSectionTransclusion/LabeledSectionTransclusion.php" );
require_once( "$IP/extensions/MediaFunctions/MediaFunctions.php" );
wfLoadExtension( "MetaTemplate" );

require_once( "$IP/extensions/MobileFrontend/MobileFrontend.php" );
$wgMobileFrontendLogo = $wgScriptPath . '/extensions/MobileFrontend/stylesheets/images/uesp-mobile-logo.png';
$wgMFAutodetectMobileView = true;
$wgMFCollapseSectionsByDefault = false;
$wgMFContentNamespace = "NS_MAIN|102|104|106|108|110|112|114|116|118|120|122|124|126|128|130|132|134|136|138|140|142|144|146|148|150|152|154|156|158|160|162|164|166|168|170|172|174|176|178";

wfLoadExtension( "ParserFunctions" );
$wgPFEnableStringFunctions = true;
$wgPFStringLengthLimit = 30000;

require_once( "$IP/extensions/Patroller/Patroller.php" );

// Very old extension never updated...do we even use it?
//require_once( "$IP/extensions/ProtectSection/ProtectSection.php" );
//$egProtectSectionNoAddAbove = true;

require_once( "$IP/extensions/RegexFunctions/RegexFunctions.php" );
wfLoadExtension( "Renameuser" );
wfLoadExtension( 'Riven' );
wfLoadExtension( "SpamBlacklist" );
wfLoadExtension( 'TemplateStyles' );
wfLoadExtension( "TitleBlacklist" );

require_once( "$IP/extensions/TorBlock/TorBlock.php" );
$wgGroupPermissions['user']['torunblocked'] = false;

require_once( "$IP/extensions/UespCustomCode/SiteCustomCode.php" );
require_once( "$IP/extensions/UespMap/UespMap.php" );
require_once( "$IP/extensions/UsersEditCount/UsersEditCount.php" );
wfLoadExtension ( "WikiTextLoggedInOut" );
wfLoadSkin( "UespMonoBook" );
wfLoadSkin( "UespVector" );
require_once( "$IP/extensions/EsoCharData/EsoCharData.php" );

require_once( "$IP/extensions/UespEsoItemLink/UespEsoItemLink.php" );
require_once( "$IP/extensions/UespEsoSkills/UespEsoSkills.php" );

require_once( "$IP/extensions/MwEmbedSupport/MwEmbedSupport.php" );
require_once( "$IP/extensions/TimedMediaHandler/TimedMediaHandler.php" );
$wgEnableTranscode = true;
$wgTranscodeBackgroundTimeLimit = 60 * 5;
	# Use custom compiled version instead of default one at /usr/bin/
$wgFFmpegLocation = '/home/uesp/ffmpeg/ffmpeg';

require_once( "$IP/extensions/UespLegendsCards/UespLegendsCards.php" );

require_once( "$IP/extensions/JsonConfig/JsonConfig.php" );
wfLoadExtension("Graph");
$wgEnableGraphParserTag = true;

require_once( "$IP/extensions/RecentPopularPages/RecentPopularPages.php");
require_once( "$IP/extensions/Tabs/Tabs.php" );

require_once( "$IP/extensions/UploadWizard/UploadWizard.php" );

$wgMessagesDirs['UploadWizard'] = array(
		"$IP/extensions/UploadWizard/i18n",
		"$IP/extensions/UespCustomCode/uploadWizard-i18n"
); 
$wgUploadWizardConfig['uwLanguages'] = array( 'en' => 'English' );
$wgUploadWizardConfig['enableCategoryCheck'] = false;
$wgUploadWizardConfig['minAuthorLength'] = 0;
$wgUploadWizardConfig['minSourceLength'] = 0;
$wgUploadWizardConfig['minDescriptionLength'] = 0;
$wgUploadWizardConfig['defaults']['description'] = 'Uploaded by UploadWizard';
//unset($wgUploadWizardConfig['licensing']['ownWork']['template']);
$wgUploadWizardConfig['licensing']['ownWork']['defaults'] = 'uesp-cc-by-sa-2.5';
$wgUploadWizardConfig['licensing']['thirdParty']['defaults'] = 'uesp-cc-by-sa-2.5';

$wgUploadWizardConfig['licensing']['ownWork']['licenses'] = array(
		'uesp-cc-by-sa-2.5', 
		'uesp-cc-by-2.5',
		'uesp-cc-by-sa-nc-2.5',
		//'uesp-gfdl',
		//'uesp-none',
		//'uesp-dontknow',
		'uesp-pd',
		//'uesp-esimage-direwolf',
		//'uesp-esimage-modipihius',
		//'uesp-esimage',
		//'uesp-zenimage',
		'uesp-usedwithpermission',
		//'uesp-uespimage',
		//'uesp-uespimage-zos',
);

$wgUploadWizardConfig['licensing']['thirdParty']['licenseGroups'] = array(
		array(
				'head' => 'mwe-upwiz-license-none-head',
				'licenses' => array(
						'uesp-none',
						'uesp-dontknow',
				)
		),
		array(
				'head' => 'mwe-upwiz-license-publicdomain-head',
				'licenses' => array(
						'uesp-pd',
				)
		),
		array(
				'head' => 'mwe-upwiz-license-cc-head',
				'licenses' => array(
						'uesp-cc-by-sa-2.5', 
						'uesp-cc-by-2.5',
						'uesp-cc-by-sa-nc-2.5',
						'uesp-gfdl',
				)
		),
		array(
				'head' => 'mwe-upwiz-license-nonfree-head',
				'licenses' => array(
						'uesp-esimage-direwolf',
						'uesp-esimage-modipihius',
						'uesp-esimage',
						'uesp-zenimage',
						'uesp-usedwithpermission',
				)
		),
		array(
				'head' => 'mwe-upwiz-license-screenshots-head',
				'licenses' => array(
						'uesp-uespimage',
						'uesp-uespimage-zos',
				)
		),
);

$wgUploadWizardConfig['licenses']['uesp-none'] = array(
		'msg' => 'mwe-upwiz-license-uesp-none',
		'templates' => array( 'nolicense' ),
);
$wgUploadWizardConfig['licenses']['uesp-dontknow'] = array(
		'msg' => 'mwe-upwiz-license-uesp-dontknow',
		'templates' => array( 'nolicense' ),
);
$wgUploadWizardConfig['licenses']['uesp-pd'] = array(
		'msg' => 'mwe-upwiz-license-uesp-pd',
		'templates' => array( 'publicdomain' ),
		'icons' => array( 'cc-zero' )
);
$wgUploadWizardConfig['licenses']['uesp-cc-by-sa-2.5'] = array(
		'msg' => 'mwe-upwiz-license-uesp-cc-by-sa-2.5',
		'templates' => array( 'cc-by-sa-2.5' ),
		'icons' => array( 'cc-by', 'cc-sa' )
);
$wgUploadWizardConfig['licenses']['uesp-cc-by-2.5'] = array(
		'msg' => 'mwe-upwiz-license-uesp-cc-by-2.5',
		'templates' => array( 'cc-by-2.5' ),
		'icons' => array( 'cc-by' )
);
$wgUploadWizardConfig['licenses']['uesp-cc-by-sa-nc-2.5'] = array(
		'msg' => 'mwe-upwiz-license-uesp-cc-by-sa-nc-2.5',
		'templates' => array( 'cc-by-sa-nc-2.5' ),
		'icons' => array( 'cc-by', 'cc-sa' )
);
$wgUploadWizardConfig['licenses']['uesp-gfdl'] = array(
		'msg' => 'mwe-upwiz-license-uesp-gfdl',
		'templates' => array( 'GFDL' ),
);
$wgUploadWizardConfig['licenses']['uesp-esimage-direwolf'] = array(
		'msg' => 'mwe-upwiz-license-uesp-esimage-direwolf',
		'templates' => array( 'esimage|Dire Wolf Digital' ),
);
$wgUploadWizardConfig['licenses']['uesp-esimage-modipihius'] = array(
		'msg' => 'mwe-upwiz-license-uesp-esimage-modipihius',
		'templates' => array( 'esimage|Modipihius Entertainment' ),
);
$wgUploadWizardConfig['licenses']['uesp-esimage'] = array(
		'msg' => 'mwe-upwiz-license-uesp-esimage',
		'templates' => array( 'esimage' ),
);
$wgUploadWizardConfig['licenses']['uesp-zenimage'] = array(
		'msg' => 'mwe-upwiz-license-uesp-zenimage',
		'templates' => array( 'zenimage' ),
);
$wgUploadWizardConfig['licenses']['uesp-usedwithpermission'] = array(
		'msg' => 'mwe-upwiz-license-uesp-usedwithpermission',
		'templates' => array( 'usedwithpermission' ),
);
$wgUploadWizardConfig['licenses']['uesp-uespimage'] = array(
		'msg' => 'mwe-upwiz-license-uesp-uespimage',
		'templates' => array( 'uespimage' ),
);
$wgUploadWizardConfig['licenses']['uesp-uespimage-zos'] = array(
		'msg' => 'mwe-upwiz-license-uesp-uespimage-zos',
		'templates' => array( 'uespimage|Zenimax Online Studios' ),
);

$wgResourceModules['ext.uploadWizardUesp']['messages'] = array(
		"mwe-upwiz-source-ownwork-assert-uesp-cc-by-sa-2.5",
		"mwe-upwiz-source-ownwork-uesp-cc-by-sa-2.5-explain",
		"mwe-upwiz-license-uesp-cc-by-sa-2.5",
		"mwe-upwiz-license-uesp-cc-by-2.5",
		"mwe-upwiz-license-uesp-cc-by-sa-nc-2.5",
		"mwe-upwiz-license-uesp-pd",
		"mwe-upwiz-license-uesp-usedwithpermission",
		"mwe-upwiz-license-uesp-gfdl",
		"mwe-upwiz-license-uesp-none",
		"mwe-upwiz-license-uesp-dontknow",
		"mwe-upwiz-license-uesp-esimage-direwolf",
		"mwe-upwiz-license-uesp-esimage-modipihius",
		"mwe-upwiz-license-uesp-esimage",
		"mwe-upwiz-license-uesp-zenimage",
		"mwe-upwiz-license-uesp-uespimage",
		"mwe-upwiz-license-uesp-uespimage-zos",
		"mwe-upwiz-license-publicdomain-head",
		"mwe-upwiz-license-nonfree-head",
		"mwe-upwiz-license-screenshots-head",
);

	/* TODO: Only need to load for the Special:UploadWizard page */
$wgHooks['BeforePageDisplay'][] = 'UESPUploadWizard_beforePageDisplay';

function UESPUploadWizard_beforePageDisplay($out, $skin)
{
	$out->addModules( 'ext.uploadWizardUesp' );
}


# wfLoadExtension( 'DisableAccount' );
# $wgGroupPermissions['bureaucrat']['disableaccount'] = true;
# $wgGroupPermissions['sysop']['disableaccount'] = true;

# Temporarily disabled extensions (enable with caution)
# require_once( "$IP/extensions/SearchLog/SearchLog.php" );
# require_once( "$IP/extensions/LogPageRenderTimes/LogPageRenderTimes.php" );

# RM: Things to consider adding
# wfLoadExtension( "Interwiki" );
# wfLoadExtension( "Nuke" );

wfLoadExtension( "WikiEditor" );
$wgDefaultUserOptions['usebetatoolbar'] = 0;
$wgDefaultUserOptions['usebetatoolbar-cgd'] = 0;
$wgDefaultUserOptions['wikieditor-preview'] = 0;
$wgDefaultUserOptions['wikieditor-publish'] = 0;

//wfLoadExtension( 'Scribunto' );
require_once("$IP/extensions/Scribunto/Scribunto.php");
$wgScribuntoDefaultEngine = 'luastandalone';

wfLoadExtension( "PageSpeedLog" );
$wgPageSpeedLogFile = "/var/log/httpd/pagespeed.log";

wfLoadExtension( "UespPatreon" );
$wgSharedTables[] = 'patreon_user';	//Should be in extension but is not working
wfLoadExtension( "SyntaxHighlight_GeSHi" );
wfLoadExtension( "UespShortLinks" );

require_once( "$IP/extensions/NativeSvgHandler/NativeSvgHandler.php" );
$wgNativeSvgHandlerEnableLinks = true;

wfLoadExtension( "UespGameMap" );
wfLoadExtension( "UespEsoData" );

wfLoadExtension( "EmbedVideo" );

require_once "$IP/extensions/PageImages/PageImages.php";
$wgPageImagesNamespaces = [NS_MAIN, 102, 104, 106, 108,
		110, 112, 114, 116, 118,
		120, 122, 124, 126, 128, 
		130, 132, 134, 136, 138,
		140, 142, 144, 146, 148,
		150, 152, 154, 156, 158,
		160, 162, 164, 166, 168,
		170, 172, 174, 176, 178];

wfLoadExtension( "TextExtracts" );
wfLoadExtension( "Popups" );
$wgPopupsReferencePreviewsBetaFeature = false;