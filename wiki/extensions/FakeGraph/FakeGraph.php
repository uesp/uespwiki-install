<?php
if (function_exists('wfLoadExtension')) {
	wfLoadExtension('FakeGraph');
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['FakeGraph'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['FakeGraphMagic'] = __DIR__ . '/i18n/Riven.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for Riven extension. Please use wfLoadExtension instead, ' .
			'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);

	return true;
}

die('This version of the FakeGraph extension requires MediaWiki 1.25+');
