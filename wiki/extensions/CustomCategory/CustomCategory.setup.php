<?php
if ( ! defined( 'MEDIAWIKI' ) )
        die();

/**#@+
 *
 * @addtogroup Extensions
 */
$wgExtensionFunctions[] = 'setupCustomCategoryView';
$wgExtensionCredits['parserhook'][] = array(
  'name' => 'CustomCategory',
  'author' => 'Cedric Chantepie',
  'description' => 'Allow to display sortkey instead of article name in category view'
);
$wgHooks['CategoryPageView'][] = 'customCategoryView';

require_once ( dirname( __FILE__ ) . '/CustomCategoryViewer.php' );

/**
 */
function setupCustomCategoryView() {
  global $customCategoryViewer;
  $customCategoryViewer = new CustomCategoryViewer();
}

/**
 * Category hook function
 */
function customCategoryView(&$categoryArticle) {
  $meth = new ReflectionMethod('Article', 'view');

  $meth->invoke($categoryArticle);

  if ( NS_CATEGORY != $categoryArticle->mTitle->getNamespace() ) {
    return true; // skip
  }
  // ---

  // Copied from CategoryPage::closeShowCategory
  global $wgOut, $wgRequest, $customCategoryViewer;
  $from = $wgRequest->getVal( 'from' );
  $until = $wgRequest->getVal( 'until' );

  $customCategoryViewer->init( $categoryArticle->mTitle, $from, $until );
  $wgOut->addHTML( $customCategoryViewer->getHTML() );

  return false;
}
?>
