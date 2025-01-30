<?php
if (function_exists('wfLoadExtension')) {
	wfLoadExtension('MetaTemplate');
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['MetaTemplate'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['MetaTemplateMagic'] = __DIR__ . '/i18n/MetaTemplate.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for MetaTemplate extension. Please use wfLoadExtension instead, ' .
			'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die('This version of the MetaTemplate extension requires MediaWiki 1.25+');
}
