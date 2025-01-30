<?php

/* These functions were unused at the time of conversion. They've been left in this file for reference should we need them in the future. */
function SetUespMapSessionData()
{
	//Note: This is no longer needed/used

	global $_SESSION, $wgUser;

	// TODO: Unsure when we have to migrate to the new session object (should have been 1.27/28)
	//$session = \MediaWiki\Session\SessionManager::getGlobalSession();

	$_SESSION['UESP_AllMap_canEdit'] = 0;
	$_SESSION['UESP_EsoMap_canEdit'] = 0;
	$_SESSION['UESP_TRMap_canEdit'] = 0;
	$_SESSION['UESP_OtherMap_canEdit'] = 0;

	//$session->set('UESP_AllMap_canEdit', 0);
	//$session->set('UESP_EsoMap_canEdit', 0);
	//$session->set('UESP_TRMap_canEdit', 0);
	//$session->set('UESP_OtherMap_canEdit', 0);

	if ($wgUser == null) return;

	if ($wgUser->isAllowed('esomapedit')) {
		$_SESSION['UESP_EsoMap_canEdit'] = 1;
		//$session->set('UESP_EsoMap_canEdit', 1);
	}

	if ($wgUser->isAllowed('mapedit') || $wgUser->isAllowed('othermapedit')) {
		$_SESSION['UESP_OtherMap_canEdit'] = 1;
		//$session->set('UESP_OtherMap_canEdit', 1);
	}

	if ($wgUser->isAllowed('trmapedit')) {
		$_SESSION['UESP_TRMap_canEdit'] = 1;
		//$session->set('UESP_TRMap_canEdit', 1);
	}
}

function onSearchGetNearMatchBefore($allSearchTerms, &$title)
{
	$title = Title::newFromText($allSearchTerms[0]); // Look for exact match before trying alternates
	if ($title->exists()) {
		return false; // false = match
	}

	$title = Title::newFromText(preg_replace('/\beso\b/i', 'Online', $allSearchTerms[0]));
	if ($title->exists()) {
		return false; // false = match
	}

	$title = Title::newFromText(preg_replace('/\beso\b/i', 'online', $allSearchTerms[0]));

	return !$title->exists();
}

function onSpecialSearchCreateLink($t, &$params)
{
	$params[1] = preg_replace('/\((ESO) OR online\)/i', '$1', $params[1]);

	return true;
}

// group pages appear under at Special:SpecialPages
// $wgSpecialPageGroups['Preferences'] = 'users';
// $wgSpecialPageGroups['Search'] = 'redirects';
// $wgSpecialPageGroups['Wantedpages'] = 'maintenance';

return true;
