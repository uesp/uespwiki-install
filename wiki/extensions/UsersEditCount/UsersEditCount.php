<?php
if (function_exists('wfLoadExtension')) {
	wfLoadExtension('UsersEditCount');
	// Keep i18n globals so mergeMessageFileList.php doesn't break
	$wgMessagesDirs['UsersEditCount'] = __DIR__ . '/i18n';
	$wgExtensionMessagesFiles['UsersEditCountAlias'] = __DIR__ . '/i18n/UsersEditCount.i18n.alias.php';
	wfWarn(
		'Deprecated PHP entry point used for Riven extension. Please use wfLoadExtension instead, ' .
			'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return true;
} else {
	die('This version of the UsersEditCount extension requires MediaWiki 1.25+');
}
