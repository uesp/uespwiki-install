<?php
global $egSiteOtherMagicWords, $egSiteParserFunctions;

$magicWords = array();

$magicWords['en'] = array();

foreach (array_merge($egSiteOtherMagicWords, $egSiteParserFunctions) as $magicword => $case) {
	$magicWords['en'][$magicword] = array($case, $magicword);
}
