<?php
# WARNING: This file is publically viewable on the web. Do not put private data here.
#
# This file contains namespace related settings.
# It is included by LocalSettings.php.
#


$wgExtraNamespaces =
array(	100 => "Tamold",      101 => "Tamold_talk",
		102 => "Arena",       103 => "Arena_talk",
		104 => "Daggerfall",  105 => "Daggerfall_talk",
		106 => "Battlespire", 107 => "Battlespire_talk",
		108 => "Redguard",    109 => "Redguard_talk",
		110 => "Morrowind",   111 => "Morrowind_talk",
		112 => "Tribunal",    113 => "Tribunal_talk",
		114 => "Bloodmoon",   115 => "Bloodmoon_talk",
		116 => "Oblivion",    117 => "Oblivion_talk",
		118 => "General",     119 => "General_talk",
		120 => "Review",      121 => "Review_talk",
		122 => "Tes3Mod",     123 => "Tes3Mod_talk",
		124 => "Tes4Mod",     125 => "Tes4Mod_talk",
		126 => "Shivering",   127 => "Shivering_talk",
		128 => "Shadowkey",   129 => "Shadowkey_talk",
		130 => "Lore",        131 => "Lore_talk",
		132 => "Dawnstar",    133 => "Dawnstar_talk",
		134 => "Skyrim",      135 => "Skyrim_talk",
		136 => "OBMobile",    137 => "OBMobile_talk",
		138 => "Stormhold",   139 => "Stormhold_talk",
		140 => "Books",       141 => "Books_talk",
		142 => "Tes5Mod",     143 => "Tes5Mod_talk",
		144 => "Online",      145 => "Online_talk",
		146 => "Dragonborn",  147 => "Dragonborn_talk",
		148 => "ESOMod",      149 => "ESOMod_talk",
		150 => "Legends",	  151 => "Legends_talk",
		152 => "Blades",	  153 => "Blades_talk",
		200 => "Dapel",       201 => "Dapel_talk");

$wgNamespaceAliases =
array(	'UESP' => NS_PROJECT, 'UESP_talk' => NS_PROJECT+1,
		'AR' => 102,          'AR_talk' => 103,
		'DF' => 104,          'DF_talk' => 105,
		'BS' => 106,          'BS_talk' => 107,
		'RG' => 108,          'RG_talk' => 109,
		'MW' => 110,          'MW_talk' => 111,
		'TR' => 112,          'TR_talk' => 113,
		'BM' => 114,          'BM_talk' => 115,
		'OB' => 116,          'OB_talk' => 117,
		'GEN' => 118,         'GEN_talk' => 119,
		'T3' => 122,          'T3_talk' => 123,
		'T4' => 124,          'T4_talk' => 125,
		'SI' => 126,          'SI_talk' => 127,
		'SK' => 128,          'SK_talk' => 129,
		'LO' => 130,          'LO_talk' => 131,
		'Tamriel' => 130,     'Tamriel_talk' => 131,
		'DS' => 132,          'DS_talk' => 133,
		'SR' => 134,          'SR_talk' => 135,
		'OM' => 136,          'OM_talk' => 137,
		'SH' => 138,          'SH_talk' => 139,
		'BK' => 140,          'BK_talk' => 141,
		'T5' => 142,          'T5_talk' => 143,
		'ON' => 144,          'ON_talk' => 145,
		'ESO' => 144,         'ESO_talk' => 145,
		'TESO' => 144,        'TESO_talk' => 145,
		'DB' => 146,          'DB_talk' => 147,
		'LG' => 150,          'LG_talk' => 151,
		'BL' => 152,          'BL_talk' => 153,
);

$wgNamespacesWithSubpages = array(
	 	-1 => 0,
		0 => 0,   1 => 1,   2 => 1,   3 => 1,   4 => 1,   5 => 1,   6 => 0,   7 => 1,   8 => 0,   9 => 1,
	 	10 => 1,  11 => 1,
		100 => 1, 101 => 1, 102 => 1, 103 => 1, 104 => 1, 105 => 1, 106 => 1, 107 => 1, 108 => 1, 109 => 1,
		110 => 1, 111 => 1, 112 => 1, 113 => 1, 114 => 1, 115 => 1, 116 => 1, 117 => 1, 118 => 1, 119 => 1,
		120 => 1, 121 => 1, 122 => 1, 123 => 1, 124 => 1, 125 => 1, 126 => 1, 127 => 1, 128 => 1, 129 => 1,
		130 => 1, 131 => 1, 132 => 1, 133 => 1, 134 => 1, 135 => 1, 136 => 1, 137 => 1, 138 => 1, 139 => 1,
		140 => 1, 141 => 1, 142 => 1, 143 => 1, 144 => 1, 145 => 1, 146 => 1, 147 => 1, 148 => 1, 149 => 1,
		150 => 1, 151 => 1, 152 => 1, 153 => 1, 
		200 => 1, 201 => 1);

$wgNamespacesToBeSearchedDefault = array(
	 	-1 => 0,
		0 => 1,   1 => 0,   2 => 0,   3 => 0,   4 => 0,   5 => 0,   6 => 0,   7 => 0,   8 => 0,   9 => 0,
		10 => 0,  11 => 0,
		100 => 0, 101 => 0, 102 => 1, 103 => 0, 104 => 1, 105 => 0, 106 => 1, 107 => 0, 108 => 1, 109 => 0,
		110 => 1, 111 => 0, 112 => 1, 113 => 0, 114 => 1, 115 => 0, 116 => 1, 117 => 0, 118 => 1, 119 => 0,
		120 => 1, 121 => 0, 122 => 1, 123 => 0, 124 => 1, 125 => 0, 126 => 1, 127 => 0, 128 => 1, 129 => 0,
		130 => 1, 131 => 0, 132 => 1, 133 => 0, 134 => 1, 135 => 0, 136 => 1, 137 => 0, 138 => 1, 139 => 0,
		140 => 1, 141 => 0, 142 => 1, 143 => 0, 144 => 1, 145 => 0, 146 => 1, 147 => 0, 148 => 1, 149 => 0,
		150 => 1, 151 => 0, 152 => 1, 153 => 0, 
		200 => 0, 201 => 0);

$wgContentNamespaces = array( NS_MAIN, 102, 104, 106, 108, 110, 112, 114, 116, 118, 120, 122, 124, 126, 128, 
		                      130, 132, 134, 136, 138, 140, 142, 144, 146, 148, 150, 152 );
