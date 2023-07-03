<?php

class MetaTemplateCategoryViewer28 extends MetaTemplateCategoryViewer
{
	#region Public Override Functions
	public function addImage($title, $sortkey, $pageLength, $isRedirect = false)
	{
		#RHshow(__METHOD__, $title->getPrefixedText());
		if ($this->showGallery && isset(self::$templates[self::CV_FILE])) {
			$type = self::CV_FILE;
		} elseif (!$this->showGallery && isset(self::$templates[self::CV_PAGE])) {
			$type = self::CV_PAGE;
		} else {
			$type = null;
		}

		if (is_null(self::$templates[$type])) {
			parent::addImage($title, $sortkey, $pageLength, $isRedirect);
			return;
		}

		[$group, $link] = $this->processTemplate($type, $title, $sortkey, $pageLength, $isRedirect);
		$this->imgsNoGallery[] = $link;
		$this->imgsNoGallery_start_char[] = $group;
	}

	public function addPage($title, $sortkey, $pageLength, $isRedirect = false)
	{
		#RHshow(__METHOD__, $title->getPrefixedText());
		$template = self::$templates[self::CV_PAGE] ?? null;
		if (is_null($template)) {
			parent::addPage($title, $sortkey, $pageLength, $isRedirect);
			return;
		}

		[$group, $link] = $this->processTemplate(self::CV_PAGE, $title, $sortkey, $pageLength, $isRedirect);
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

		[$group, $link] = $this->processTemplate(self::CV_SUBCAT, $cat->getTitle(), $sortkey, $pageLength);
		$this->children[] = $link;
		$this->children_start_char[] = $group;
	}
	#endregion

	#region Private Functions
	/**
	 * Generates the text of the entry.
	 *
	 * @param string $template The catpagetemplate to use for this category entry.
	 * @param string $type What type of category entry this is.
	 * @param Title $title The title of the entry.
	 * @param string $sortkey The sortkey for the entry.
	 * @param int $pageLength The page length of the entry.
	 * @param bool $isRedirect Whether or not the entry is a redirect.
	 *
	 * @return array
	 */
	private function processTemplate(string $type, Title $title, string $sortkey, int $pageLength, bool $isRedirect = false): array
	{
		$articleId = $title->getArticleID();
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

		$catVars = $this->parseCatPageTemplate($type, $title, $sortkey, $pageLength, $defaultSet);
		#RHDebug::show('$catVars', $catVars);

		/* $catGroup does not need sanitizing as MW runs it through htmlspecialchars later in the process.
		 * Unfortunately, that means you can't make links without deriving formatList(), which can then call either
		 * static::columnList() instead of self::columnList() and the same for shortList() so that those two methods
		 * can be statically derived. Are we having fun yet?
		 */
		$catGroup = $catVars->catGroup ?? ($type === self::CV_SUBCAT
			? $this->getSubcategorySortChar($title, $sortkey)
			: self::$contLang->convert($this->collation->getFirstLetter($sortkey)));
		$catText = $catVars->catTextPre . $this->generateLink($type, $title, $isRedirect, $catVars->catLabel) . $catVars->catTextPost;
		$texts = [];
		#RHDebug::show('setsFound', $setsFound);
		foreach ($setsFound as $index => $setValues) {
			#RHDebug::show($setValues->name, $setValues);
			$setVars = $this->parseCatPageTemplate($type, $title, null, -1, $setValues);
			if (!$setVars->setSkip && (!is_null($setVars->setLabel) || !is_null($setVars->setPage))) {
				$texts[$setVars->setSortKey . '.' . $index] = is_null($setVars->setPage)
					? $setVars->setLabel
					: $this->generateLink(
						$type,
						$setVars->setPage ?? $title,
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
