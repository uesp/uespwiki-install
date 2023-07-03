<?php

class MetaTemplateCategoryPage extends CategoryPage
{
	function closeShowCategory()
	{
		$catViewer = MetaTemplate::getCatViewer();
		$this->mCategoryViewerClass = $catViewer::hasTemplate()
			? $catViewer
			: (class_exists('CategoryTreeCategoryViewer')
				? 'CategoryTreeCategoryViewer'
				: 'CategoryViewer');
		parent::closeShowCategory();
	}
}
