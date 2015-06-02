<?php
if ( ! defined( 'MEDIAWIKI' ) )
        die();
 
require_once( "$IP/includes/Article.php" );
require_once( "$IP/includes/CategoryPage.php" );
 
/**
 * Category view with custom display.
 */
class CustomCategoryViewer extends CategoryViewer {
        /**
         * No-arg constructor.
         */
        function __construct() {
        }
 
        /**
         * Initialize
         * Copied from CategoryViewer::__construct
         */
        function init($title, $from = '', $until = '') {
                global $wgCategoryPagingLimit;
                $this->title = $title;
                $this->from = $from;
                $this->until = $until;
                $this->limit = $wgCategoryPagingLimit;
        }
 
        /**
         * Copied from original CategoryView::doCategoryQuery
         */
        function doCategoryQuery() {
                $dbr = wfGetDB( DB_SLAVE );
                if( $this->from != '' ) {
                        $pageCondition = 'cl_sortkey >= ' . $dbr->addQuotes( $this->from );
                        $this->flip = false;
                } elseif( $this->until != '' ) {
                        $pageCondition = 'cl_sortkey < ' . $dbr->addQuotes( $this->until );
                        $this->flip = true;
                } else {
                        $pageCondition = '1 = 1';
                        $this->flip = false;
                }
                $res = $dbr->select(
                        array( 'page', 'categorylinks' ),
                        array( 'page_title', 'page_namespace', 'page_len', 'cl_sortkey' ),
                        array( $pageCondition,
                               'cl_from          =  page_id',
                               'cl_to'           => $this->title->getDBKey()),
                               #'page_is_redirect' => 0),
                        #+ $pageCondition,
                        __METHOD__,
                        array( 'ORDER BY' => $this->flip ? 'cl_sortkey DESC' : 'cl_sortkey',
                               'USE INDEX' => 'cl_sortkey', 
                               'LIMIT'    => $this->limit + 1 ) );
 
                $count = 0;
                global $wgContLang;
 
                $categories = $this->title->getParentCategories();
                $plainView = ($categories[$wgContLang->getNSText ( NS_CATEGORY ) . ":DisplaySortkey"] == NULL);
 
                // sortkey == '*' | sortkey == page_title
                $this->nextPage = null;
                while( $x = $dbr->fetchObject ( $res ) ) {
                        if( ++$count > $this->limit ) {
                                // We've reached the one extra which shows that there are
                                // additional pages to be had. Stop here...
                                $this->nextPage = $x->cl_sortkey;
                                break;
                        }
                        // HERE
                        $nsDefaultSortkey = $wgContLang->getNSText($x->page_namespace) . ':' . $x->page_title;
 
                        if (!$plainView && 
                            $x->cl_sortkey != $x->page_title &&
                            $x->cl_sortkey != $nsDefaultSortkey &&
                            $x->cl_sortkey != '*') {
 
                          $x->cl_sortkey = '_CustomCategory_:' . $x->cl_sortkey;
                        }
                        // _END
                        $title = Title::makeTitle( $x->page_namespace, $x->page_title );
 
                        if( $title->getNamespace() == NS_CATEGORY ) {
                                $this->addSubcategory( $title, $x->cl_sortkey, $x->page_len );
                        } elseif( $title->getNamespace() == NS_IMAGE ) {
                                $this->addImage( $title, $x->cl_sortkey, $x->page_len );
                        } else {
                                $this->addPage( $title, $x->cl_sortkey, $x->page_len );
                        }
                }
                $dbr->freeResult( $res );
        } 
 
        function addPage( $title, $sortkey, $pageLength ) {
                $label = $title->getPrefixedText();
 
                if (strpos($sortkey, 'CustomCategory_:') == 1) {
                  $sortkey = substr($sortkey, 17);
                  $label = $sortkey;
                }
 
                global $wgContLang;
                $this->articles[] = $this->getSkin()->makeSizeLinkObj(
                  $pageLength, 
                  $title, 
                  $label
                );
 
                $this->articles_start_char[] = 
                  $wgContLang->convert( $wgContLang->firstChar( $sortkey ) );
        }
}
