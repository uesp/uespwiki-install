<?php
global $egSiteNamespaceMagicWords, $egSiteOtherMagicWords, $egSiteParserFunctions;

$magicWords = array();

$magicWords['en'] = array();

foreach (array_merge($egSiteNamespaceMagicWords, $egSiteOtherMagicWords, $egSiteParserFunctions) as $magicword => $case) {
	$magicWords['en'][$magicword] = array($case, $magicword);
}
