<?php
$messages = array();

/* *** English *** */
$messages['en'] = array(
	'uespnamespacelist' =>
	'<pre>
# Format:
# NS_BASE ; NS_ID ; NS_PARENT ; NS_NAME ; NS_MAINPAGE ; NS_CATEGORY ; NS_TRAIL
# Any missing or blank entries are filled with default values (e.g., NS_CATEGORY=NS_BASE, NS_TRAIL = [[NS_MAINPAGE|NS_NAME]])
# This list is also used by the \"Go\" search feature: it defines the default namespaces, the order in which to search them, and related namespaces
Oblivion    ; OB ; Oblivion
Shivering   ; SI ; Oblivion   ; Shivering Isles
Morrowind   ; MW ; Morrowind
Tribunal    ; TR ; Morrowind
Bloodmoon   ; BM ; Morrowind
Redguard    ; RG ; Redguard
Battlespire ; BS ; Battlespire
Daggerfall  ; DF ; Daggerfall
Arena       ; AR ; Arena
Shadowkey   ; SK ; Shadowkey
Lore        ; LO ; Lore       ; Lore ; Lore:Main Page
Tes4Mod     ; T4 ; Oblivion
Tes3Mod     ; T3 ; Morrowind
Tes3Mod:Tamriel Rebuilt ; TR3 ; Morrowind ; Tamriel Rebuilt ; Tes3Mod:Tamriel Rebuilt ; Tes3Mod-Tamriel Rebuilt ; [[Tes3Mod:Tes3Mod|Tes3Mod]]: [[Tes3Mod:Tamriel Rebuilt|Tamriel Rebuilt]]
Tes4Mod:Tamriel Rebuilt ; TR4 ; Oblivion ; Tamriel Rebuilt ; Tes4Mod:Tamriel Rebuilt ; Tes4Mod-Tamriel Rebuilt ; [[Tes4Mod:Tes4Mod|Tes4Mod]]: [[Tes4Mod:Tamriel Rebuilt|Tamriel Rebuilt]]
            ; MAIN ;           ; Mainspace ; :Main Page
</pre>',
	'uespsearchmanyplus' => "'''There are several pages with titles similar to \"\$1\".'''  Or you can [[:\$2|create this page]].",
	'uespsearchmain' => "'''There is no page titled \"\$1\".'''  If you wish to create this page, you should first select one of the games listed in the sidebar, then redo your search for this page title.",
	'uespsearchmany' => "'''There are several possible pages titled \"\$1\".'''",
	'uespsearchtitlesonly' => "'''Note:''' This was a title-only search.  To do a full-text search for this term, deselect the \"Search Titles Only\" option below and redo the search.",
	'uesppowersearchtable' => '* Arena
* Daggerfall
* Battlespire
* Redguard

* Morrowind
** Tribunal
** Bloodmoon
** Tes3Mod

* Oblivion
** Shivering | Shivering Isles
** Tes4Mod
* Shadowkey

* Lore
* (Main)
* User
* Image

* UESPWiki
* Template
* Help
* Category

* General
* Review
* MediaWiki
* Dapel',

	'uesppowersearchfor' => 'Search for',
	'uesppowersearchtitles' => 'Search Titles Only',
	'uesppowersearchtalk' => 'Search Talk Pages',
	'uesppowersearchredirects' => 'List Redirects',
	'uesppowersearchselect' => 'Select All',
	'uesppowersearchdeselect' => 'Deselect All',
	'uesppowersearchboolean' => 'Use [[Help:Searching#Boolean Searches|boolean search syntax]] in search string',
	'tog-uespsearchtitles' => 'Search Titles Only',
	'tog-uespsearchredirects' => 'List Redirects',
	'tog-uespsearchtalk' => 'Search Talk Pages',
	'uesptrailseparator' => ': ',
	'uespsettrail' => '1',
	'uespextrasearchbutton' => '...',

	'group-patroller' => 'Patrollers',
	'group-patroller-member' => 'Patroller',
	'grouppage-patroller' => 'UESPWiki:Patrollers',
	'group-autopatrolled' => 'Autopatrolled Users',
	'group-autopatrolled-member' => 'Autopatrolled User',
	'grouppage-autopatrolled' => 'UESPWiki:Autopatrolled Users',

	'tog-hideuserspace' => 'Hide most userspace edits in recent changes by default',
	'tog-usecustomns' => 'Use custom namespace selection in recent changes by default',
	'tog-userspacetalk' => 'Only hide the user namespace, not talk pages',
	'tog-userspaceunpatrolled' => 'Except for unpatrolled edits',
	'tog-userspacewatchlist' => 'Except for edits to your watched pages',
	'tog-userspaceownpage' => 'Except for edits to your user pages',
	'tog-userspaceownedit' => 'Except for your own edits',
	'tog-userspaceanonedit' => 'Except for anonymous edits',
	'tog-userspacewarning' => 'Except for user warnings and blocks',
	'tog-userspacelogs' => 'Except for user account changes',

	'group-userpatroller'            => 'Userspace Patrollers',
	'group-userpatroller-member'     => 'Userspace Patroller',
	'right-allspacepatrol'           => "Mark edits as patrolled in all namespaces",
	'markedaspatrollederror-nonuserspace' => "You are not allowed to mark non-userspace edits as patrolled.",

	'right-uespstats' => 'View UESP Statistics',

	'group-blockuser'            => 'Blockers',
	'group-blockuser-member'     => 'Blocker',
	'right-blocktalk'             => 'Block a user from editing their talk page',
	'right-unrestrictedblock'     => 'Block for any length of time',
	'restrictblock-denied-utalk'  => '<div class="error">You are not allowed to block a user from editing their talk page.</div>',
	'restrictblock-denied'        => '<div class="error">You are not allowed to block for more than $1 seconds.</div>',
);
