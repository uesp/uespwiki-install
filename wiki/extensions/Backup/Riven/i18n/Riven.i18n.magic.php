<?php

// While it's good form to do this anyway, this line MUST be here or the entire wiki will come crashing to a halt
// whenever you try to add new magic words.
$magicWords = [];

$magicWords['en'] = [
	// Parameter values
	Riven::AV_ORIGINAL => [0, 'original'],
	Riven::AV_RECURSIVE => [0, 'recursive'],
	Riven::AV_SMART => [0, 'smart'],
	Riven::AV_TOP => [0, 'top'],

	// Parameter names
	Riven::NA_ALLOWEMPTY => [0, 'allowempty'],
	Riven::NA_CACHETIME => [0, 'cachetime'],
	Riven::NA_CLEANIMG => [0, 'cleanimages'],
	Riven::NA_DELIMITER => [0, 'delimiter', ':delimiter'],
	Riven::NA_EXPLODE => [0, 'explode', ':explode'],
	Riven::NA_MODE => [0, 'mode'],
	Riven::NA_SEED => [0, 'seed'],

	// Function names
	RivenHooks::PF_ARG => [0, 'arg'],
	RivenHooks::PF_EXPLODEARGS => [0, 'explodeargs'],
	RivenHooks::PF_FINDFIRST => [0, 'findfirst'],
	RivenHooks::PF_IFEXISTX => [0, 'ifexistx'],
	RivenHooks::PF_INCLUDE => [0, 'include'],
	RivenHooks::PF_LABEL => [0, 'label'],
	RivenHooks::PF_LABELNAME => [0, 'LABELNAME'],
	RivenHooks::PF_PICKFROM => [0, 'pickfrom'],
	RivenHooks::PF_RAND => [0, 'rand'],
	RivenHooks::PF_SORTABLE => [0, 'sortable'],
	RivenHooks::PF_SPLITARGS => [0, 'splitargs'],
	RivenHooks::PF_TRIMLINKS => [0, 'trimlinks'],

	// Tag names
	RivenHooks::TG_CLEANSPACE => [0, 'cleanspace'],
	RivenHooks::TG_CLEANTABLE => [0, 'cleantable'],

	// Variable names
	RivenHooks::VR_SKINNAME => [1, 'SKINNAME']
];
