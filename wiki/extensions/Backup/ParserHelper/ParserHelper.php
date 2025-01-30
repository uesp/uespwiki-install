<?php
if (function_exists('wfLoadExtension')) {
	wfLoadExtension('ParserHelper');
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['ParserHelper'] = __DIR__ . '/i18n';
	wfWarn(
		'Deprecated PHP entry point used for ParserHelper extension. Please use wfLoadExtension instead, ' .
			'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);

	return true;
}

die('This version of the ParserHelper extension requires MediaWiki 1.25+');
