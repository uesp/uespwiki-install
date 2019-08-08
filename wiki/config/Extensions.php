<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains extension includes and settings.
# It is included by LocalSettings.php.
#

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
require_once( "$IP/extensions/MetaTemplate/MetaTemplate.php" );

require_once( "$IP/extensions/MobileFrontend/MobileFrontend.php" );
$wgMobileFrontendLogo = $wgScriptPath . '/extensions/MobileFrontend/stylesheets/images/uesp-mobile-logo.png';
$wgMFAutodetectMobileView = true;

wfLoadExtension( "ParserFunctions" );
$wgPFEnableStringFunctions = true;
$wgPFStringLengthLimit = 30000;

require_once( "$IP/extensions/Patroller/Patroller.php" );
require_once( "$IP/extensions/ProtectSection/ProtectSection.php" );
$egProtectSectionNoAddAbove = true;
require_once( "$IP/extensions/RegexFunctions/RegexFunctions.php" );
wfLoadExtension( "Renameuser" );

wfLoadExtension( "SpamBlacklist" );
$wgSpamBlacklistFiles = array( "DB: uesp_net_wiki5 UESPWiki:Spam_Blacklist" );

wfLoadExtension( "TitleBlacklist" );

require_once( "$IP/extensions/TorBlock/TorBlock.php" );
$wgGroupPermissions['user']['torunblocked'] = false;

require_once( "$IP/extensions/UespCustomCode/SiteCustomCode.php" );
require_once( "$IP/extensions/UespMap/UespMap.php" );
require_once( "$IP/extensions/UsersEditCount/UsersEditCount.php" );
require_once( "$IP/extensions/WikiTextLoggedInOut/WikiTextLoggedInOut.php" );
wfLoadSkin( "UespMonoBook" );
// require_once( "$IP/skins/UespMonoBook/UespMonoBook.php" );
wfLoadSkin( "UespVector" );
require_once( "$IP/extensions/EsoCharData/EsoCharData.php" );

require_once( "$IP/extensions/UespEsoItemLink/UespEsoItemLink.php" );
require_once( "$IP/extensions/UespEsoSkills/UespEsoSkills.php" );

require_once( "$IP/extensions/MwEmbedSupport/MwEmbedSupport.php" );
require_once( "$IP/extensions/TimedMediaHandler/TimedMediaHandler.php" );
$wgEnableTranscode = false; // Disabled for now, since transcoding isn't working anyway - needs FFMPEG.

require_once( "$IP/extensions/UespLegendsCards/UespLegendsCards.php" );

require_once( "$IP/extensions/JsonConfig/JsonConfig.php" );
require_once( "$IP/extensions/Graph/Graph.php" );
$wgEnableGraphParserTag = true;

require_once( "$IP/extensions/RecentPopularPages/RecentPopularPages.php");

# wfLoadExtension( 'DisableAccount' );
# $wgGroupPermissions['bureaucrat']['disableaccount'] = true;
# $wgGroupPermissions['sysop']['disableaccount'] = true;

# Temporarily disabled extensions (enable with caution)
# require_once( "$IP/extensions/SearchLog/SearchLog.php" );
# require_once( "$IP/extensions/LogPageRenderTimes/LogPageRenderTimes.php" );

# RM: Things to consider adding
# wfLoadExtension( "Interwiki" );
# wfLoadExtension( "Nuke" );
# wfLoadExtension( "SyntaxHighlight_GeSHi" );
# wfLoadExtension( "WikiEditor" );
