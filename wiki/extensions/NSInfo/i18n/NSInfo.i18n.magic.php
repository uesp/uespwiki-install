<?php

// While it's good form to do this anyway, this line MUST be here or the entire wiki will come crashing to a halt
// whenever you try to add new magic words.
$magicWords = [];
$magicWords['en'] = [
	NSInfo::NA_NS_BASE => [1, 'ns_base'],
	NSInfo::NA_NS_ID => [1, 'ns_id'],

	NSInfo::PF_ISGAMESPACE => [0, 'GAMESPACE', 'ISGAMESPACE'],
	NSInfo::PF_ISMODSPACE => [0, 'MODSPACE', 'ISMODSPACE'],
	NSInfo::PF_MOD_NAME => [0, 'MOD_NAME'],
	NSInfo::PF_MOD_PARENT => [0, 'MOD_PARENT'],
	NSInfo::PF_NS_BASE => [0, 'NS_BASE'],
	NSInfo::PF_NS_CATEGORY => [0, 'NS_CATEGORY'],
	NSInfo::PF_NS_CATLINK => [0, 'NS_CATLINK'],
	NSInfo::PF_NS_FULL => [0, 'NS_FULL'],
	NSInfo::PF_NS_ID => [0, 'NS_ID'],
	NSInfo::PF_NS_MAINPAGE => [0, 'NS_MAINPAGE'],
	NSInfo::PF_NS_NAME => [0, 'NS_NAME'],
	NSInfo::PF_NS_PARENT => [0, 'NS_PARENT'],
	NSInfo::PF_NS_TRAIL => [0, 'NS_TRAIL'],
];
