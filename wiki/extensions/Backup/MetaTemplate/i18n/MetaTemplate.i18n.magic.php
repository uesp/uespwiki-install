<?php

// While it's good form to do this anyway, this line MUST be here or the entire wiki will come crashing to a halt
// whenever you try to add new magic words.
$magicWords = [];
$magicWords['en'] = [
	MetaTemplate::AV_ANY => [0, 'any'],
	MetaTemplate::NA_CASE => [0, 'case'],
];

if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEPAGENAMES)) {
	$magicWords['en'] += [
		MetaTemplate::PF_FULLPAGENAMEx => [1, 'FULLPAGENAMEx'],
		MetaTemplate::PF_NAMESPACEx => [1, 'NAMESPACEx'],
		MetaTemplate::PF_NESTLEVEL => [1, 'NESTLEVEL'],
		MetaTemplate::PF_PAGENAMEx => [1, 'PAGENAMEx'],
	];
}

if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLECPT)) {
	$magicWords['en'] += [
		MetaTemplateCategoryViewer::NA_IMAGE => [0, 'image'],
		MetaTemplateCategoryViewer::NA_PAGE => [0, 'page'],
		MetaTemplateCategoryViewer::NA_PAGELENGTH => [0, 'pagelength'],
		MetaTemplateCategoryViewer::NA_SORTKEY => [0, 'sortkey'],
		MetaTemplateCategoryViewer::NA_SUBCAT => [0, 'subcat'],

		MetaTemplateCategoryViewer::TG_CATPAGETEMPLATE => [0, 'catpagetemplate'],

		MetaTemplateCategoryVars::VAR_CATGROUP => [0, 'catgroup'],
		MetaTemplateCategoryVars::VAR_CATLABEL => [0, 'catlabel'],
		MetaTemplateCategoryVars::VAR_CATTEXTPOST => [0, 'cattextpost'],
		MetaTemplateCategoryVars::VAR_CATTEXTPRE => [0, 'cattextpre'],

		MetaTemplateCategoryVars::VAR_SETANCHOR => [0, 'setanchor'],
		MetaTemplateCategoryVars::VAR_SETLABEL => [0, 'setlabel'],
		MetaTemplateCategoryVars::VAR_SETPAGE => [0, 'setpage'],
		MetaTemplateCategoryVars::VAR_SETREDIRECT => [0, 'setredirect'],
		MetaTemplateCategoryVars::VAR_SETSEPARATOR => [0, 'setseparator'],
		MetaTemplateCategoryVars::VAR_SETSKIP => [0, 'setskip'],
		MetaTemplateCategoryVars::VAR_SETSORTKEY => [0, 'setsortkey'],
		MetaTemplateCategoryVars::VAR_SETTEXTPOST => [0, 'settextpost'],
		MetaTemplateCategoryVars::VAR_SETTEXTPRE => [0, 'settextpre'],
	];
}

if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)) {
	$magicWords['en'] += [
		MetaTemplateData::NA_ORDER => [0, 'order'],
		MetaTemplateData::NA_SAVEMARKUP => [0, 'savemarkup'],
		MetaTemplateData::NA_SET => [0, 'set', 'subset'],

		MetaTemplateData::PF_LISTSAVED => [0, 'listsaved'],
		MetaTemplateData::PF_LOAD => [0, 'load'],
		MetaTemplateData::PF_PRELOAD => [0, 'preload'],
		MetaTemplateData::PF_SAVE => [0, 'save'],

		MetaTemplateData::TG_SAVEMARKUP => [0, 'savemarkup'],
	];
}

if (
	MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLECPT) ||
	MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)
) {
	$magicWords['en'] += [
		MetaTemplate::NA_FULLPAGENAME => [0, 'fullpagename'],
		MetaTemplate::NA_NAMESPACE => [0, 'namespace'],
		MetaTemplate::NA_PAGEID => [0, 'pageid'],
		MetaTemplate::NA_PAGENAME => [0, 'pagename'],
	];
}

if (MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDEFINE)) {
	$magicWords['en'] += [
		MetaTemplate::NA_SHIFT => [0, 'shift', 'shiftdown'],

		MetaTemplate::PF_DEFINE => [0, 'define'],
		MetaTemplate::PF_INHERIT => [0, 'inherit'],
		MetaTemplate::PF_LOCAL => [0, 'local'],
		MetaTemplate::PF_PREVIEW => [0, 'preview'],
		MetaTemplate::PF_RETURN => [0, 'return'],
		MetaTemplate::PF_UNSET => [0, 'unset'],
	];
}
