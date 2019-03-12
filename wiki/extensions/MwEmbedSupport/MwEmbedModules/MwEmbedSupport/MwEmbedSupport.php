<?php
return array(
	"mw.MwEmbedSupport" => array(
		'scripts' => array(
			"mw.MwEmbedSupport.js",
		),
		'debugRaw' => false,
		'dependencies' => array(
			// jQuery dependencies:
			'jquery.triggerQueueCallback',
			'Spinner',
			'jquery.loadingSpinner',
			'jquery.mwEmbedUtil',
			'mw.MwEmbedSupport.style',
		),
		'messageDir' => 'i18n',
	),
	"Spinner" => array(
		'scripts' => 'jquery.loadingSpinner/Spinner.js',
	),
	'iScroll' => array(
		'scripts' => 'iscroll/src/iscroll.js',
	),
	"jquery.loadingSpinner" => array(
		'scripts' => 'jquery.loadingSpinner/jquery.loadingSpinner.js',
	),
	'mw.MwEmbedSupport.style' => array(
		// NOTE we add the loadingSpinner.css as a work around to the resource loader register
		// of modules as "ready" even though only the "script" part of the module was included.
		'styles'=> array( 'skins/common/MwEmbedCommonStyle.css' ),
		'skinStyles' => array(
			/* shared jQuery ui skin styles */
			'kaltura-dark' => 'skins/jquery.ui.themes/kaltura-dark/jquery-ui-1.7.2.css',
		),
	),
	'mediawiki.UtilitiesTime' => array( 'scripts' => 'mediawiki/mediawiki.UtilitiesTime.js' ),
	'mediawiki.client' => array( 'scripts' => 'mediawiki/mediawiki.client.js' ),
	'mediawiki.absoluteUrl' => array( 'scripts' => 'mediawiki/mediawiki.absoluteUrl.js',
		'dependancies' => array( 'mediawiki.Uri' ),
	),

	'mw.ajaxProxy' => array(
		'scripts' => 'mediawiki/mediawiki.ajaxProxy.js'
	),

	'fullScreenApi'=> array(
		'scripts' => 'fullScreenApi/fullScreenApi.js'
	),
	'jquery.embedMenu' => array(
		'scripts' => 'jquery.embedMenu/jquery.embedMenu.js',
		'styles' => 'jquery.embedMenu/jquery.embedMenu.css'
	),
	'jquery.ui.touchPunch' => array(
		'scripts' => 'jquery/jquery.ui.touchPunch.js',
		'dependencies' => array(
			'jquery.ui.core',
			'jquery.ui.mouse'
		)
	),
	// Startup modules must set debugRaw to false
	"jquery.triggerQueueCallback"	=> array(
		'scripts'=> "jquery/jquery.triggerQueueCallback.js",
		'debugRaw' => false
	),
	"jquery.mwEmbedUtil" => array(
		'scripts' => "jquery/jquery.mwEmbedUtil.js",
		'debugRaw' => false,
	),
	'jquery.debouncedresize' => array(
		'scripts' => 'jquery/jquery.debouncedresize.js'
	),
);
