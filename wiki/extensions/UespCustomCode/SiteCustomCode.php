<?php
if (function_exists('wfLoadExtension')) {
	wfLoadExtension('UespCustomCode');
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['UespCustomCode'] = __DIR__ . '/i18n';
	/*wfWarn(
		'Deprecated PHP entry point used for UespCustomCode extension. Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);*/
	return;
} else {
	die('This version of the UespCustomCode extension requires MediaWiki 1.29+');
}
