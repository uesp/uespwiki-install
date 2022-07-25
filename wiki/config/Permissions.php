<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains permission related settings.
# It is included by LocalSettings.php.
#

# Remove redundant checkuser group (all admins have equivalent rights)
$wgExtensionFunctions[] = function() use ( &$wgGroupPermissions ) {
    unset( $wgGroupPermissions['checkuser'] );
};

# Rights to add or remove user groups
$wgAddGroups   ['sysop'] = array ( 'abuseeditor', 'autopatrolled', 'blockuser', 'cartographer', 'confirmed', 'patroller', 'translator', 'userpatroller', 'esocartographer', 'trcartographer', 'othercartographer' );
$wgRemoveGroups['sysop'] = array ( 'abuseeditor', 'autopatrolled', 'blockuser', 'cartographer', 'confirmed', 'patroller', 'translator', 'userpatroller', 'esocartographer', 'trcartographer', 'othercartographer' );

# Removed group rights
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['*']['mapedit'] = false;
$wgGroupPermissions['*']['esomapedit'] = false;
$wgGroupPermissions['*']['trmapedit'] = false;
$wgGroupPermissions['*']['patroller'] = false;

$wgGroupPermissions['user']['move'] = false;

# Added group rights
$wgGroupPermissions['*']['abusefilter-log'] = true;
$wgGroupPermissions['*']['abusefilter-log-detail'] = true;
$wgGroupPermissions['*']['abusefilter-view'] = true;
$wgGroupPermissions['*']['map'] = true;

$wgGroupPermissions['abuseeditor']['abusefilter-modify'] = true;
$wgGroupPermissions['abuseeditor']['abusefilter-modify-restricted'] = true;
$wgGroupPermissions['abuseeditor']['abusefilter-private'] = true;
$wgGroupPermissions['abuseeditor']['abusefilter-revert'] = true;

$wgGroupPermissions['autoconfirmed']['move'] = true;

$wgGroupPermissions['autopatrolled']['allspacepatrol'] = true;
$wgGroupPermissions['autopatrolled']['autopatrol'] = true;
$wgGroupPermissions['autopatrolled']['skipcaptcha'] = true;
$wgGroupPermissions['autopatrolled']['tboverride'] = true;

$wgGroupPermissions['blockuser']['blocktalk'] = true;
$wgGroupPermissions['blockuser']['skipcaptcha'] = true;

$wgGroupPermissions['bot']['deletedhistory'] = true;
$wgGroupPermissions['bot']['editprotected'] = true;
$wgGroupPermissions['bot']['protect'] = true;
$wgGroupPermissions['bot']['tboverride'] = true;

$wgGroupPermissions['cartographer']['map'] = true;
$wgGroupPermissions['cartographer']['mapedit'] = true;
$wgGroupPermissions['cartographer']['esomapedit'] = true;
$wgGroupPermissions['cartographer']['trmapedit'] = true;

$wgGroupPermissions['esocartographer']['map'] = true;
$wgGroupPermissions['esocartographer']['mapedit'] = false;
$wgGroupPermissions['esocartographer']['esomapedit'] = true;
$wgGroupPermissions['esocartographer']['trmapedit'] = false;

$wgGroupPermissions['trcartographer']['map'] = true;
$wgGroupPermissions['trcartographer']['mapedit'] = false;
$wgGroupPermissions['trcartographer']['esomapedit'] = false;
$wgGroupPermissions['trcartographer']['trmapedit'] = true;

$wgGroupPermissions['othercartographer']['map'] = true;
$wgGroupPermissions['othercartographer']['mapedit'] = true;
$wgGroupPermissions['othercartographer']['esomapedit'] = false;
$wgGroupPermissions['othercartographer']['trmapedit'] = false;

$wgGroupPermissions['confirmed']['autoconfirmed'] = true;

//$wgGroupPermissions['map']['map'] = true;

$wgGroupPermissions['patroller']['autopatrol'] = true;
$wgGroupPermissions['patroller']['editinterface'] = true;
$wgGroupPermissions['patroller']['movefile'] = true;
$wgGroupPermissions['patroller']['patrol'] = true;
$wgGroupPermissions['patroller']['patroller'] = true;
$wgGroupPermissions['patroller']['protectsection'] = true;
$wgGroupPermissions['patroller']['skipcaptcha'] = true;
$wgGroupPermissions['patroller']['tboverride'] = true;

$wgGroupPermissions['sysop']['abusefilter-modify'] = true;
$wgGroupPermissions['sysop']['abusefilter-modify-restricted'] = true;
$wgGroupPermissions['sysop']['abusefilter-private'] = true;
$wgGroupPermissions['sysop']['abusefilter-revert'] = true;
$wgGroupPermissions['sysop']['checkuser'] = true;
$wgGroupPermissions['sysop']['checkuser-log'] = true;
$wgGroupPermissions['sysop']['deleterevision'] = true;
$wgGroupPermissions['sysop']['editcontentmodel'] = true; // Defaults to true in 1.27 and above and can be removed.
$wgGroupPermissions['sysop']['patroller'] = true;
$wgGroupPermissions['sysop']['renameuser'] = true;
$wgGroupPermissions['sysop']['skipcaptcha'] = true;
$wgGroupPermissions['sysop']['tboverride'] = true;

$wgGroupPermissions['translator']['abusefilter-modify'] = true;
$wgGroupPermissions['translator']['abusefilter-modify-restricted'] = true;
$wgGroupPermissions['translator']['abusefilter-private'] = true;
$wgGroupPermissions['translator']['abusefilter-revert'] = true;
$wgGroupPermissions['translator']['allspacepatrol'] = true;
$wgGroupPermissions['translator']['autopatrol'] = true;
$wgGroupPermissions['translator']['delete'] = true;
$wgGroupPermissions['translator']['deleterevision'] = true;
$wgGroupPermissions['translator']['editinterface'] = true;
$wgGroupPermissions['translator']['editprotected'] = true;
$wgGroupPermissions['translator']['editsemiprotected'] = true;
$wgGroupPermissions['translator']['import'] = true;
$wgGroupPermissions['translator']['movefile'] = true;
$wgGroupPermissions['translator']['patroller'] = true;
$wgGroupPermissions['translator']['protectsection'] = true;
$wgGroupPermissions['translator']['rollback'] = true;
$wgGroupPermissions['translator']['skipcaptcha'] = true;
$wgGroupPermissions['translator']['tboverride'] = true;
$wgGroupPermissions['translator']['undelete'] = true;

$wgGroupPermissions['userpatroller']['tboverride'] = true;

# Temporary elevation of reupload rights
$wgGroupPermissions['user']['reupload'] = false;
$wgGroupPermissions['autoconfirmed']['reupload'] = true;
$wgGroupPermissions['confirmed']['reupload'] = true;

# Right to create an account via the API (completely disabled for all users)
$wgAPIModules['createaccount'] = 'ApiDisabled';

# Restrict viewing of renamed users to those with permission to rename
$wgLogRestrictions['renameuser'] = 'renameuser';

/* old code to prevent non-bots from accessing API edits
$wgHooks['APIEditBeforeSave'][] = 'onAPIEditBeforeSave';
function onAPIEditBeforeSave( $editPage, $text, &$resultArr ) {
	global $wgUser;

	if ( !in_array( 'bot', $wgUser->getGroups() ) ) {
		$resultArr = array(
				'code' => 'BotEditsOnly',
				'info' => 'API edits are disabled on this wiki except for registered bots.');
		return false;
	}

	return true;
} */

# Special permissions for translation wikis
if ($uespLanguageSuffix != "")
{
	$wgGroupPermissions['*']['edit'] = false;
	$wgGroupPermissions['user']['edit'] = true;
}
