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
# Generate a random string 8 characters long
$myChallengeString = substr(md5(uniqid(mt_rand(), true)), 0, 10);
# Pick a random location in those 8 strings
$myChallengeIndex = rand(0, 8);
# Let's use words to describe the position, just to make it a bit more complicated
$myChallengePositions = array ('first', 'second', 'third', 'fourth', 'fifth', 'fifth last', 'fourth last', 'third last', 'second last');
$myChallengePositionName = $myChallengePositions[$myChallengeIndex];
# Build the question/anwer
$wgCaptchaQuestions[] = array (
    'question' => "Please provide the two characters, starting from the $myChallengePositionName character, in the following sequence: <code>$myChallengeString</code>",
    'answer' => substr( $myChallengeString, $myChallengeIndex, 2 )
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
require_once "$IP/extensions/LogPageRenderTimes/LogPageRenderTimes.php";
require_once "$IP/extensions/MediaFunctions/MediaFunctions.php";
require_once "$IP/extensions/MetaTemplate/MetaTemplate.php";

require_once( "$IP/extensions/MobileFrontend/MobileFrontend.php" );
$wgMobileFrontendLogo = $wgScriptPath . '/extensions/MobileFrontend/stylesheets/images/uesp-mobile-logo.png';
$wgMFAutodetectMobileView = true;

require_once "$IP/extensions/ParserFunctions/ParserFunctions.php";
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

require_once "$IP/extensions/StringFunctions/StringFunctions.php";
$wgStringFunctionsLimitReplace=50;

require_once "$IP/extensions/TitleBlacklist/TitleBlacklist.php";

require_once "$IP/extensions/TorBlock/TorBlock.php";
$wgGroupPermissions['user']['torunblocked'] = false;

require_once "$IP/extensions/UespCustomCode/SiteCustomCode.php";
require_once "$IP/extensions/UespMap/UespMap.php";
require_once "$IP/extensions/UsersEditCount/UsersEditCount.php";
require_once "$IP/extensions/WikiTextLoggedInOut/WikiTextLoggedInOut.php";
require_once "$IP/skins/UespMonoBook/UespMonoBook.php";
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

require( "$IP/extensions/MwEmbedSupport/MwEmbedSupport.php" );
require( "$IP/extensions/TimedMediaHandler/TimedMediaHandler.php" );

require( "$IP/extensions/UespLegendsCards/UespLegendsCards.php" );
