<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Page\PageReference;

/* In theory, this process could be optimized further by subdividing <catpagetemplate> into a section for pages and a
 * section for sets so that only the set portion is parsed inside the loop at the end of processTemplate(). Given the
 * syntax changes already being introduced in this version and the extra level of user knowledge that a pages/sets
 * style would require, I don't think it's especially useful.
 */

/**
 * This class wraps around the base CategoryViewer class to provide MetaTemplate's custom capabilities like altering
 * the title and showing set names on a page.
 */
class MetaTemplateCategoryViewer37 extends MetaTemplateCategoryViewer
{
	#region Public Override Functions
	public function addImage(PageReference $page, string $sortkey, int $pageLength, bool $isRedirect = false): void
	{
		#RHshow(__METHOD__, $page->getPrefixedText());
		if ($this->showGallery && isset(self::$templates[self::CV_FILE])) {
			$type = self::CV_FILE;
		} elseif (!$this->showGallery && isset(self::$templates[self::CV_PAGE])) {
			$type = self::CV_PAGE;
		} else {
			$type = null;
		}

		if (is_null(self::$templates[$type])) {
			parent::addImage($page, $sortkey, $pageLength, $isRedirect);
			return;
		}

		[$group, $link] = $this->processTemplate($type, $page, $sortkey, $pageLength, $isRedirect);
		$this->imgsNoGallery[] = $link;
		$this->imgsNoGallery_start_char[] = $group;
	}

	public function addPage(PageReference $page, string $sortkey, int $pageLength, bool $isRedirect = false): void
	{
		#RHshow(__METHOD__, $page->getPrefixedText());
		$template = self::$templates[self::CV_PAGE] ?? null;
		if (is_null($template)) {
			parent::addPage($page, $sortkey, $pageLength, $isRedirect);
			return;
		}

		[$group, $link] = $this->processTemplate(self::CV_PAGE, $page, $sortkey, $pageLength, $isRedirect);
		$this->articles[] = $link;
		$this->articles_start_char[] = $group;
	}

	public function addSubcategoryObject(Category $cat, $sortkey, $pageLength)
	{
		#RHshow(__METHOD__, $cat->getTitle()->getPrefixedText());
		$template = self::$templates[self::CV_SUBCAT] ?? null;
		if (is_null($template)) {
			parent::addSubcategoryObject($cat, $sortkey, $pageLength);
			return;
		}

		$page = $cat->getPage();
		if (!$page) {
			return;
		}

		// Subcategory; strip the 'Category' namespace from the link text.
		$pageRecord = MediaWikiServices::getInstance()->getPageStore()
			->getPageByReference($page);
		if (!$pageRecord) {
			return;
		}

		[$group, $link] = $this->processTemplate(self::CV_SUBCAT, $pageRecord, $sortkey, $pageLength);
		$this->children[] = $link;
		$this->children_start_char[] = $group;
	}
	#endregion

	#region Private Functions
	// This is now private, so the underlying code is copied here straight from 1.37 CategoryViewer code.
	/**
	 * @param string $type
	 * @param PageReference $page
	 * @param bool $isRedirect
	 * @param string|null $html
	 * @return string
	 * Annotations needed to tell taint about HtmlArmor,
	 * due to the use of the hook it is not possible to avoid raw html handling here
	 * @param-taint $html tainted
	 * @return-taint escaped
	 */
	private function generateLinkInternal(string $type, PageReference $page, bool $isRedirect, ?string $html = null): string
	{
		$link = null;
		$legacyTitle = MediaWikiServices::getInstance()->getTitleFactory()->castFromPageReference($page);
		$this->getHookRunner()->onCategoryViewer__generateLink($type, $legacyTitle, $html, $link);
		if ($link === null) {
			$linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
			if ($html !== null) {
				$html = new HtmlArmor($html);
			}

			$link = $linkRenderer->makeLink($page, $html);
		}

		if ($isRedirect) {
			$link = Html::rawElement(
				'span',
				['class' => 'redirect-in-category'],
				$link
			);
		}

		return $link;
	}

	/**
	 * Generates the text of the entry.
	 *
	 * @param string $template The catpagetemplate to use for this category entry.
	 * @param string $type What type of category entry this is.
	 * @param PageReference $title The title of the entry.
	 * @param string $sortkey The sortkey for the entry.
	 * @param int $pageLength The page length of the entry.
	 * @param bool $isRedirect Whether or not the entry is a redirect.
	 *
	 * @return array
	 */
	private function processTemplate(string $type, PageReference $page, string $sortkey, int $pageLength, bool $isRedirect = false): array
	{
		$pageRecord = MediaWikiServices::getInstance()->getPageStore()
			->getPageByReference($page);
		$articleId = $pageRecord->getId();
		if (isset(MetaTemplateData::$preloadCache[$articleId]) && MetaTemplate::getSetting(MetaTemplate::STTNG_ENABLEDATA)) {
			$setsFound = MetaTemplateData::$preloadCache[$articleId]->sets;
			if (isset($setsFound[''])) {
				$defaultSet = $setsFound[''];
				unset($setsFound['']);
			} else {
				$defaultSet =  new MetaTemplateSet('');
			}

			$setsFound = array_values($setsFound);
		} else {
			$defaultSet = new MetaTemplateSet('');
			$setsFound = [];
		}
		#RHshow('Sets found', count($setsFound), "\n", $setsFound);

		$mws = MediaWikiServices::getInstance();
		$title = $mws->getTitleFactory()->castFromPageReference($page);
		$catVars = $this->parseCatPageTemplate($type, $title, $sortkey, $pageLength, $defaultSet);
		#RHDebug::show('$catVars', $catVars);

		/* $catGroup does not need sanitizing as MW runs it through htmlspecialchars later in the process.
		 * Unfortunately, that means you can't make links without deriving formatList(), which can then call either
		 * static::columnList() instead of self::columnList() and the same for shortList() so that those two methods
		 * can be statically derived. Are we having fun yet?
		 */
		$catGroup = $catVars->catGroup ?? ($type === self::CV_SUBCAT
			? $this->getSubcategorySortChar($page, $sortkey)
			: self::$contLang->convert($this->collation->getFirstLetter($sortkey)));
		$catText = $catVars->catTextPre . $this->generateLinkInternal($type, $pageRecord, $isRedirect, $catVars->catLabel) . $catVars->catTextPost;
		$texts = [];
		foreach ($setsFound as $index => $setValues) {
			#RHDebug::show($setValues->name, $setValues);
			$setVars = $this->parseCatPageTemplate($type, $title, null, -1, $setValues);
			if (!$setVars->setSkip && (!is_null($setVars->setLabel) || !is_null($setVars->setPage))) {
				$texts[$setVars->setSortKey . '.' . $index] = is_null($setVars->setPage)
					? $setVars->setLabel
					: $this->generateLinkInternal(
						$type,
						$setVars->setPage ?? $page,
						$setVars->setRedirect ?? $isRedirect,
						$setVars->setLabel ?? $title->getFullText()
					);
			}
		}

		ksort($texts, SORT_NATURAL);
		$text = implode($catVars->setSeparator, $texts);
		if (strlen($text)) {
			$text = $catVars->setTextPre . $text . $catVars->setTextPost;
		}

		return [$catGroup, $catText . $text];
	}
	#endregion
}
