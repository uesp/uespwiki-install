<?php

/**
 * Table paging class for SpecialMetaVarsOnPage.
 *
 * @todo This version is a little wonky due to the fact that there is no single-field unique key, and even if there
 * were, it would probably cause sorting confusion. The only way around it would seem to be to override the low-level
 * functions to brute-force this to either use a multi-key id or, probably easier, to support a traditional offset.
 *
 */
class MetaVarsPager extends TablePager
{
	private $pageId;

	/**
	 * Creates a new instance of the MetaVarsPager class.
	 *
	 * @param IContextSource $context The MediaWiki context.
	 * @param mixed $conds Conditions to be applied to the results.
	 * @param mixed $limit The number of results to list.
	 *
	 */
	public function __construct(IContextSource $context, int $pageId, int $limit)
	{
		$this->pageId = $pageId;
		$this->mLimit = $limit;
		$this->mDefaultDirection = false;

		// TablePager doesn't handle two-key offsets and doesn't seem to support simple numerical offsets either. This
		// seemed like an acceptable trade-off, since it offers the added benefit of always showing an entire set. The
		// drawback is that if limit is set to less than the number of keys in the set, you'll never get anywhere.
		$this->mIncludeOffset = true;
		parent::__construct($context);
	}

	public function getFieldNames(): array
	{
		static $fieldNames;
		$fieldNames = $fieldNames ?? [
			MetaTemplateSql::FIELD_SET_NAME => $this->msg('metatemplate-pageswithmetavar-set')->text(),
			MetaTemplateSql::FIELD_VAR_NAME => $this->msg('metatemplate-pageswithmetavar-varname')->text(),
			MetaTemplateSql::FIELD_VAR_VALUE => $this->msg('metatemplate-pageswithmetavar-varvalue')->text()
		];

		return $fieldNames;
	}

	function formatValue($name, $value): string
	{
		switch ($name) {
			case MetaTemplateSql::FIELD_SET_NAME:
				return Html::rawElement(
					'span',
					[
						'class' => 'pageswithmetavar-set',
						'style' => 'white-space:nowrap;'
					],
					$value
				);
			case MetaTemplateSql::FIELD_VAR_VALUE:
				return htmlspecialchars(str_replace("\n", "<br>", $value));
			default:
				return htmlspecialchars($value);
		}
	}

	function getQueryInfo(): array
	{
		return MetaTemplateSql::getInstance()->pagerQuery($this->pageId);
	}

	public function getTableClass(): string
	{
		return 'TablePager pageswithmetavar-tablepager';
	}

	public function getDefaultSort(): string
	{
		return MetaTemplateSql::FIELD_SET_NAME;
	}

	public function getExtraSortFields(): array
	{
		return [MetaTemplateSql::FIELD_VAR_NAME];
	}

	public function isFieldSortable($name): bool
	{
		return true;
	}
}
