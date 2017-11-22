<?php
/**
 * MwEmbed Support Extension, Supports MwEmbed based modules,
 * and registers shared javascript resources.
 *
 * @file
 * @ingroup Extensions
 *
 * @author Michael Dale ( michael.dale@kaltura.com )
 * @license GPL v2 or later
 * @version 0.3.0
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo "This is the TimedMediaHandler extension. Please see the README file for installation instructions.\n";
	exit( 1 );
}

/* Configuration */

// When used as a MediaWiki extension use ResourceLoaderFileModule,
// to use standalone set to MwEmbedResourceLoaderFileModule
$wgMwEmbedResourceLoaderFileModule = "ResourceLoaderFileModule";


/* Setup */
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'MwEmbedSupport',
	'author' => array( 'Michael Dale' ),
	'version' => '0.3.0',
	'url' => 'https://www.mediawiki.org/wiki/Extension:MwEmbed',
	'descriptionmsg' => 'mwembed-desc',
);

$wgAutoloadClasses['MwEmbedResourceManager'] = __DIR__ . '/MwEmbedResourceManager.php';
$wgAutoloadClasses['MwEmbedSupportHooks'] = __DIR__ . '/MwEmbedSupport.hooks.php';

// Include module messages:
$wgMessagesDirs['MwEmbedSupport'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['MwEmbedSupport'] = __DIR__  ."/MwEmbedSupport.i18n.php";
$wgMessagesDirs['MwEmbed.MwEmbedSupport'] = __DIR__ . '/MwEmbedModules/MwEmbedSupport/i18n';
$wgExtensionMessagesFiles['MwEmbed.MwEmbedSupport'] = __DIR__  ."/MwEmbedModules/MwEmbedSupport/MwEmbedSupport.i18n.php";

// Add mwEmbed Support modules that are not part of startup
$wgHooks['BeforePageDisplay'][] = 'MwEmbedSupportHooks::UpdatePageModules';

// Add the startup modules hook
$wgHooks['ResourceLoaderGetStartupModules'][] = 'MwEmbedSupportHooks::addStartupModules';

// Add Global MwEmbed Registration hook
$wgHooks['ResourceLoaderRegisterModules'][] = 'MwEmbedResourceManager::registerModules';

// Add MwEmbed module configuration
$wgHooks['ResourceLoaderGetConfigVars'][] =  'MwEmbedResourceManager::registerConfigVars';

// Register the core MwEmbed Support Module:
MwEmbedResourceManager::register( 'extensions/MwEmbedSupport/MwEmbedModules/MwEmbedSupport' );

// Register the MwEmbed 'mediaWiki' Module:
MwEmbedResourceManager::register( 'extensions/MwEmbedSupport/MwEmbedModules/MediaWikiSupport' );
