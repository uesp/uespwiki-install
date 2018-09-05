<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains extension includes and settings.
# It is included by LocalSettings.php.
#

require_once "$IP/extensions/AbuseFilter/AbuseFilter.php";
$wgAbuseFilterEmergencyDisableThreshold['default'] = 0.5;

require_once "$IP/extensions/AntiSpoof/AntiSpoof.php";
require_once "$IP/extensions/CharInsert/CharInsert.php";
require_once "$IP/extensions/CheckUser/CheckUser.php";
require_once "$IP/extensions/Cite/Cite.php";

// Based on http://thingelstad.com/stopping-mediawiki-spam-with-dynamic-questy-captchas/
require_once( "$IP/extensions/ConfirmEdit/ConfirmEdit.php" );
require_once("$IP/extensions/ConfirmEdit/QuestyCaptcha.php");
$wgCaptchaClass = 'QuestyCaptcha';

# Now a more complicated one
# Generate a random string 10 characters long
$myChallengeLength = rand(2, 5);
$myChallengeIndex = rand(0, 12 - $myChallengeLength);
$myChallengeString = md5(uniqid(mt_rand(), true));
$prefix = substr($myChallengeString, 0, $myChallengeIndex);
$answer = substr($myChallengeString, $myChallengeIndex, $myChallengeLength);
$suffix = substr($myChallengeString, $myChallengeIndex + $myChallengeLength, 12 - $myChallengeIndex + myChallengeLength);
$myChallengeString = "<span style='background-color:white'>$prefix</span><span style='background-color:lightgreen'>$answer</span><span style='background-color:lightgrey'>$suffix</span>";
# Pick a random location in the string

# Build the question/anwer
$wgCaptchaQuestions[] = array (
    'question' => "Please enter the characters highlighted in green in the following sequence: <code>$myChallengeString</code>",
    'answer' => $answer
);
// ------------------
$wgCaptchaTriggers['addurl'] = false;

require_once "$IP/extensions/DailyEdits/DailyEdits.php";
$wgDailyEditsGraphFile = "//content3.uesp.net/w/extensions/UespCustomCode/files/dailyedits.png";

require_once "$IP/extensions/DeleteBatch/DeleteBatch.php";
require_once "$IP/extensions/Disambiguator/Disambiguator.php";
require_once "$IP/extensions/DismissableSiteNotice/DismissableSiteNotice.php";
require_once "$IP/extensions/DynamicFunctions/DynamicFunctions.php";
require_once "$IP/extensions/Editcount/Editcount.php";
# require_once "$IP/extensions/ExpandTemplates/ExpandTemplates.php";
require_once "$IP/extensions/Gadgets/Gadgets.php";
require_once "$IP/extensions/ImageMap/ImageMap.php";
require_once "$IP/extensions/InputBox/InputBox.php";
require_once "$IP/extensions/JobQueue/JobQueue.php";
require_once "$IP/extensions/LabeledSectionTransclusion/lst.php";
require_once "$IP/extensions/LabeledSectionTransclusion/lsth.php";
# require_once "$IP/extensions/LogPageRenderTimes/LogPageRenderTimes.php";
require_once "$IP/extensions/MediaFunctions/MediaFunctions.php";
require_once "$IP/extensions/MetaTemplate/MetaTemplate.php";

require_once( "$IP/extensions/MobileFrontend/MobileFrontend.php" );
$wgMobileFrontendLogo = $wgScriptPath . '/extensions/MobileFrontend/stylesheets/images/uesp-mobile-logo.png';
$wgMFAutodetectMobileView = true;

require_once "$IP/extensions/ParserFunctions/ParserFunctions.php";
$wgPFEnableStringFunctions = true;
$wgPFStringLengthLimit = 30000;

require_once "$IP/extensions/Patroller/Patroller.php";
require_once "$IP/extensions/ProtectSection/ProtectSection.php";
$egProtectSectionNoAddAbove = true;
require_once "$IP/extensions/qwebirChat/qwebirChat.php";
require_once "$IP/extensions/RegexFunctions/RegexFunctions.php";
require_once "$IP/extensions/Renameuser/SpecialRenameuser.php";
# require_once "$IP/extensions/SearchLog/SearchLog.php";
# require_once "$IP/extensions/SimpleAntiSpam/SimpleAntiSpam.php";

require_once "$IP/extensions/SpamBlacklist/SpamBlacklist.php";
$wgSpamBlacklistFiles = array( "DB: uesp_net_wiki5 UESPWiki:Spam_Blacklist" );

# require_once "$IP/extensions/StringFunctions/StringFunctions.php";
# $wgStringFunctionsLimitReplace=50;

require_once "$IP/extensions/TitleBlacklist/TitleBlacklist.php";

require_once "$IP/extensions/TorBlock/TorBlock.php";
$wgGroupPermissions['user']['torunblocked'] = false;

require_once "$IP/extensions/UespCustomCode/SiteCustomCode.php";
require_once "$IP/extensions/UespMap/UespMap.php";
require_once "$IP/extensions/UsersEditCount/UsersEditCount.php";
require_once "$IP/extensions/WikiTextLoggedInOut/WikiTextLoggedInOut.php";
require_once "$IP/skins/UespMonoBook/UespMonoBook.php";
require_once "$IP/skins/uespvector/UespVector.php";
require_once "$IP/extensions/EsoCharData/EsoCharData.php";

# Disabled Extensions
# require_once "$IP/extensions/CategoryTree/CategoryTree.php";

# disable temporarily: doesn't play nicely with MetaTemplate
# require_once "$IP/extensions/CustomCategory/CustomCategory.setup.php";


# RM: Things to consider adding
# require_once "$IP/extensions/Interwiki/Interwiki.php";
# require_once "$IP/extensions/Nuke/Nuke.php";
# require_once "$IP/extensions/SyntaxHighlight_GeSHi/SyntaxHighlight_GeSHi.php";
# require_once "$IP/extensions/WikiEditor/WikiEditor.php";

require_once( "$IP/extensions/UespEsoItemLink/UespEsoItemLink.php" );
require_once( "$IP/extensions/UespEsoSkills/UespEsoSkills.php" );

require_once( "$IP/extensions/MwEmbedSupport/MwEmbedSupport.php" );
require_once( "$IP/extensions/TimedMediaHandler/TimedMediaHandler.php" );
$wgEnableTranscode = false; // Disabled for now, since transcoding isn't working anyway - needs FFMPEG.

require_once( "$IP/extensions/UespLegendsCards/UespLegendsCards.php" );

require_once( "$IP/extensions/JsonConfig/JsonConfig.php" );
require_once( "$IP/extensions/Graph/Graph.php" );
$wgEnableGraphParserTag = true;
