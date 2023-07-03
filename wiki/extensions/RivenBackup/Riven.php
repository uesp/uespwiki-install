<?php
if (function_exists('wfLoadExtension')) {
	wfLoadExtension('Riven');
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['Riven'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['RivenMagic'] = __DIR__ . '/i18n/Riven.i18n.magic.php';
	wfWarn(
		'Deprecated PHP entry point used for Riven extension. Please use wfLoadExtension instead, ' .
			'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);

	return true;
}

die('This version of the Riven extension requires MediaWiki 1.25+');
