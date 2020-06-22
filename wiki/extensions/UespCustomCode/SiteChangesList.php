<?php
global $IP;
require_once "$IP/includes/changes/ChangesList.php";

class SiteOldChangesList extends OldChangesList {
	public function recentChangesLine( &$rc, $watched = false, $linenumber = NULL ) {
		global $wgUser;
		$orig_patrol = $rc->mAttribs['rc_patrolled'];
		// turn off patrolling features on line by telling parent that it's been patrolled
		if ($wgUser->useRCPatrol() && !$rc->mAttribs['rc_patrolled']) {
			if ($rc->mAttribs['rc_namespace']!=NS_USER && $rc->mAttribs['rc_namespace']!=(NS_USER+1) && !$wgUser->isAllowed('allspacepatrol')) {
				$rc->mAttribs['rc_patrolled'] = true;
			}
		}
		$retval = parent::recentChangesLine($rc, $watched);
		// just in case object is still going to be used upstream, undo my kludge
		$rc->mAttribs['rc_patrolled'] = $orig_patrol;
		return $retval;
	}
}

class SiteEnhancedChangesList extends EnhancedChangesList {
	public function recentChangesLine( &$rc, $watched = false, $lineNumber = null ) {
		global $wgUser;
		$orig_patrol = $rc->mAttribs['rc_patrolled'];
		// turn off patrolling features on line by telling parent that it's been patrolled
		if ($wgUser->useRCPatrol() && !$rc->mAttribs['rc_patrolled']) {
			if ($rc->mAttribs['rc_namespace']!=NS_USER && $rc->mAttribs['rc_namespace']!=(NS_USER+1) && !$wgUser->isAllowed('allspacepatrol')) {
				$rc->mAttribs['rc_patrolled'] = true;
			}
		}
		$retval = parent::recentChangesLine($rc, $watched);
		// just in case object is still going to be used upstream, undo my kludge
		$rc->mAttribs['rc_patrolled'] = $orig_patrol;
		return $retval;
	}
}
