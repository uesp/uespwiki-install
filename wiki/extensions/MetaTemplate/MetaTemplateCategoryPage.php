<?php
global $IP;
require_once("$IP/includes/page/CategoryPage.php");

// implement more options?
// multisubsets = true/false? (turn off subset_list processing)
// stdPage?  stdImage?

// think more about catsortkey... should it be implemented?
// to handle it, need to
// a) override from/until/limit, so that dbCategoryQuery is reading correct subset of pages
// b) use finaliseCategoryState to resort articles and categories
// c) reimpose from/until limits
// (unless I implement some type of resort parameter that allows (a) and (c) to be bypassed)

// make multiple <catpagetemplate>s possible on a page -- one for subcats, one for pages, etc.
// fix issue with using {{{subset}}} in displayed text (doesn't work on first entry)
// add better way to skip pages that aren't wanted ... hard to make catlabel empty... and pages can still
//    add to total article number
// allow better interaction with categorytree -- make it so catlabels can be modified

class MetaTemplateCategoryPage extends CategoryTreeCategoryPage {
	function closeShowCategory() {
		global $wgOut, $wgRequest, $wgExtensionFunctions;
		$from = $wgRequest->getVal( 'from' );
		$until = $wgRequest->getVal( 'until' );

// START  copied code from include/Categorypage.php
                $request = $this->getContext()->getRequest();
                $oldFrom = $request->getVal( 'from' );
                $oldUntil = $request->getVal( 'until' );

                $reqArray = $request->getValues();

                $from = $until = array();
                foreach ( array( 'page', 'subcat', 'file' ) as $type ) {
                        $from[$type] = $request->getVal( "{$type}from", $oldFrom );
                        $until[$type] = $request->getVal( "{$type}until", $oldUntil );

                        // Do not want old-style from/until propagating in nav links.
                        if ( !isset( $reqArray["{$type}from"] ) && isset( $reqArray["from"] ) ) {
                                $reqArray["{$type}from"] = $reqArray["from"];
                        }
                        if ( !isset( $reqArray["{$type}to"] ) && isset( $reqArray["to"] ) ) {
                                $reqArray["{$type}to"] = $reqArray["to"];
                        }
                }

                unset( $reqArray["from"] );
                unset( $reqArray["to"] );

// END  copied code from include/Categorypage.php

		if (MetaTemplateCategoryViewer::hasTemplate())
			$viewer = new MetaTemplateCategoryViewer( $this->mTitle, $this->getContext(), $from, $until );
		// calling MetaTemplateCategoryViewer in this case wouldn't cause a problem, but this saves the
		// overhead of multiple checks to see whether to do template processing
		elseif (in_array('efCategoryTree', $wgExtensionFunctions))
			$viewer = new CategoryTreeCategoryViewer( $this->mTitle,$this->getContext(), $from, $until, $reqArray );
		else
			$viewer = new CategoryViewer( $this->mTitle, $this->getContext(), $from, $until, $reqArray);
		$wgOut->addHTML( $viewer->getHTML() );
	}
}

class MetaTemplateCategoryViewer extends CategoryTreeCategoryViewer {
	protected static $_templatedata = array();
	protected $_pstack = NULL;

	public static function catPageTemplate( $input, $args, $parser, $frame=NULL ) {
		// page needs to be re-parsed everytime, otherwise categories get printed without the template being read
		$parser->disableCache();
		$templatedata = array('template' => $input,
		                      'parser' => $parser,
		                      'frame' => $frame);
		$dosubcat = true;
		$dopage = true;
		// manually transfer args so that case can be changed
		// also be careful not to let an arg override the basic templatedata
		foreach ($args as $argname => $argval) {
			$argname = strtolower($argname);
			if ($argname=='stdsubcat')
				$dosubcat = false;
			elseif ($argname=='stdpage')
				$dopage = false;
			elseif ($argname=='subcatonly')
				$dopage = false;
			elseif ($argname=='pageonly')
				$dosubcat = false;
			if (isset($templatedata[$argname]))
				continue;
			$templatedata[$argname] = $argval;
		}
		if ($dosubcat)
			self::$_templatedata['subcat'] = $templatedata;
		if ($dopage)
			self::$_templatedata['page'] = $templatedata;
		return '';
	}

	public static function hasTemplate() {
		if (count(self::$_templatedata))
			return true;
		else
			return false;
	}

	protected function processTemplate( $title, $sortkey, $pageLength, $isRedirect = false, $isCategory=false, $curr_subset=NULL ) {
		global $wgContLang, $wgUser;
		$subsets_found = array();
		$origTitle = $title;
		$origSortkey = $sortkey;
		$origPageLength = $pageLength;
		$origIsRedirect = $isRedirect;
		$origIsCategory = $isCategory;
		if ($isCategory)
			$ttype = 'subcat';
		else
			$ttype = 'page';

		$catparams = array('catpage', 'catlabel', 'catgroup', 'cattextpre', 'cattextpost', 'catanchor', 'catredirect', 'catsortkey', 'catskip');
		$vals = array();

		if (isset(self::$_templatedata[$ttype]['template']) && isset(self::$_templatedata[$ttype]['parser'])) {
			if (empty(self::$_templatedata[$ttype]['frame'])) {
				// old-style template parsing... is likely to not work correctly with #load
				// to do properly, I should add another layer onto mArgStack.... but I'd have to directly
				// change the parser's stack
				if (!isset($this->_pstack))
					$this->_pstack = new MetaTemplateParserStack(self::$_templatedata[$ttype]['parser'], self::$_templatedata[$ttype]['frame']);
				$pstack = $this->_pstack;
				foreach ($catparams as $param)
					$pstack->unset_value($param);
				$newFrame = NULL;
			}
			else {
				$newFrame = self::$_templatedata[$ttype]['frame']->newChild(array(), $title);
				$pstack = new MetaTemplateParserStack(self::$_templatedata[$ttype]['parser'], $newFrame);
			}

			$pstack->set($title->getPrefixedDBKey(), 'pagename');
			$splitkey = explode("\n", $sortkey);
			$pstack->set($splitkey[0], 'sortkey');
			if (is_null($curr_subset))
				$pstack->set(1, 'load_subset_unknown');
			else
				$pstack->set($curr_subset, 'subset');
			$templateOutput = trim(efMetaTemplateTagParse(self::$_templatedata[$ttype]['template'], self::$_templatedata[$ttype]['parser'], $newFrame));
			foreach ($catparams as $param)
				$vals[$param] = trim($pstack->get($param));
			if (is_null($curr_subset)) {
				$subset_unknown = $pstack->get('load_subset_unknown', true, NULL);
				if (!empty($subset_unknown) && strpos($subset_unknown, '|')!==false) {
					$subsets_found = explode('|', $subset_unknown);
				}
			}
		}

		if (!empty($vals['catpage'])) {
			// not bothering to see whether isCategory needs to be overridden....
			// assume that if this really is a different page, user is responsible for specifying that?
			// also not yet handling possibility that the new title might be a redlink, or that pageLength might not be relevant any more
			$title = Title::newFromText($vals['catpage']);
		}

		if (empty($vals['catskip'])) {
			if (!empty($vals['catanchor'])) {
				if ($vals['catanchor']{0}!='#')
					$vals['catanchor'] = '#'.$vals['catanchor'];
				// setFragment is deprecated, but makes more sense than generating a whole new title object
				$title->setFragment($vals['catanchor']);
			}

			if (empty($vals['catlabel']))
				$vals['catlabel'] = $templateOutput === '' ? $title->getFullText() : $templateOutput;

			if (strlen($vals['catredirect'])>0)
				$isRedirect = $vals['catredirect'];

			$link = Linker::link( $title, htmlspecialchars( $vals['catlabel'] ) );
			if ($isRedirect)
				$link = '<span class="redirect-in-category">' . $link . '</span>';

			if (!empty($vals['cattextpre']))
				$link = $vals['cattextpre'].' '.$link;
			if (!empty($vals['cattextpost']))
				$link .= ' '.$vals['cattextpost'];

			if (isset($vals['catgroup']))
				$start_char = $vals['catgroup'];
			elseif ($isCategory)
				$start_char = $this->getSubcategorySortChar( $title, $sortkey );
			else
				$start_char = $wgContLang->convert( $wgContLang->firstChar( $sortkey ) );

			$retvals = array('link' => $link, 'start_char' => $start_char, 'catlabel' => $vals['catlabel']);
		}
		else
			$retvals = array();

		$subset_list = array();

		// this is where function gets called recursively to fill in multiple subsets, if need be
		if (is_null($curr_subset) && count($subsets_found)) {
			$curr_subset = array_shift($subsets_found);
			if (!empty($retvals))
				$subset_list[$curr_subset] = $retvals;
			foreach ($subsets_found as $set) {
				$subset_list = array_merge($subset_list, $this->processTemplate($origTitle, $origSortkey, $origPageLength, $origIsRedirect, $origIsCategory, $set));
			}
			// only keep multiple subsets if catlabel is set
			// in theory, all I care about is that the links are different (otherwise just have two identical
			// entries in listing).
			// but even if I just keep distinct links, one of them could be the 'default' link that
			//  appears for any non-matching subset
			// (e.g., if page contains both custom_classes and custom_spells... don't want
			//  an entry to appear in spells category for the custom_class subsets)
			foreach ($subset_list as $set => $sdata) {
				if ($set==$curr_subset)
					continue;
				if (empty($sdata['catlabel']))
					unset($subset_list[$set]);
			}
			// If I've found at least one subset with a catlabel, I then need to check the first entry, too
			// (otherwise it is kept as sole representative of page)
			if (count($subset_list)>1 && empty($vals['catlabel']))
				unset($subset_list[$curr_subset]);
			ksort($subset_list);

			// NB I am NOT doing anything to warn category page that multiple objects are being added
			// when it only expects one... there is code in the catpage that is supposed to be there
			// to handle such surprises
		}
		elseif (isset($curr_subset) && empty($vals['catskip']))
			$subset_list[$curr_subset] = $retvals;
		elseif (empty($vals['catskip']))
			$subset_list[0] = $retvals;
		return $subset_list;
	}

	function addSubcategoryObject( Category $cat, $sortkey, $pageLength ) {
		if (!isset(self::$_templatedata['subcat'])) {
			parent::addSubcategoryObject( $cat, $sortkey, $pageLength );
			return;
		}

		$title = $cat->getTitle();
		$retsets = $this->processTemplate($title, $sortkey, $pageLength, false, true);
		foreach ($retsets as $retvals) {
			$this->children[] = $retvals['link'];
			$this->children_start_char[] = $retvals['start_char'];
		}
	}

	/**
		* Add a page in the image namespace
		* This is here mainly so I remember that the function exists
		* But at the moment, there isn't anything to tweak in processing
		* * If it's an image gallery, there's no text or start_char
		* * Otherwise, parent already simply calls addPage, which is where my customizations will kick in
		* 
		* 2014-09-22: At least as of 1.19, the above assertion is untrue for __NOGALLERY__
		* so added appropriate processing. -RH70
		*/
	function addImage( Title $title, $sortkey, $pageLength, $isRedirect = false ) {
		if ( $this->showGallery ) {
			parent::addImage( $title, $sortkey, $pageLength, $isRedirect );
			return;
		}

		$retsets = $this->processTemplate($title, $sortkey, $pageLength, $isRedirect);
		foreach ($retsets as $retvals) {
			$this->imgsNoGallery[] = $retvals['link'];
			$this->imgsNoGallery_start_char[] = $retvals['start_char'];
		}
	}

	/**
		* Add a miscellaneous page
		*/
	function addPage( $title, $sortkey, $pageLength, $isRedirect = false ) {
		if (!isset(self::$_templatedata['page'])) {
			parent::addPage( $title, $sortkey, $pageLength, $isRedirect );
			return;
		}

		$retsets = $this->processTemplate($title, $sortkey, $pageLength, $isRedirect);
		foreach ($retsets as $retvals) {
			$this->articles[] = $retvals['link'];
			$this->articles_start_char[] = $retvals['start_char'];
		}
	}

	function finaliseCategoryState() {
		// if I wanted to change sort order, this is where I could do it
		// arrays to be reordered:
		// $this->children
		// $this->children_start_char
		// $this->articles
		// $this->articles_start_char


		// let parent handle flipping
		parent::finaliseCategoryState();
	}

}
