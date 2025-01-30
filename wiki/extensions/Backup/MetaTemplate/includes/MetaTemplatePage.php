<?php

class MetaTemplatePage
{
	/** @var string $namespace */
	public $namespace;

	/** @var string $pagename */
	public $pagename;

	/**
	 * All sets on the page.
	 *
	 * @var MetaTemplateSet[]
	 */
	public $sets = [];

	/**
	 * Stores information for use with the bulk-loading features of <catpagetemplate> and {{#listsaved}}.
	 *
	 * @param mixed $namespace The namespace of the page.
	 * @param mixed $pagename The page name.
	 *
	 */
	public function __construct(string $namespace, string $pagename)
	{
		$this->namespace = $namespace;
		$this->pagename = $pagename;
	}

	/**
	 * Gets a set by name if it exists or creates one if it doesn't.
	 *
	 * @param int $setId The set ID. If set to zero, the set will be ignored for deletes and updates, though it will be
	 *                   added, if appropriate.
	 * @param string $setName
	 *
	 * @return MetaTemplateSet
	 *
	 */
	public function addToSet(string $setName, string $varName, string $varValue)
	{
		if (!isset($this->sets[$setName])) {
			$set = new MetaTemplateSet($setName, [$varName => $varValue]);
			$this->sets[$setName] = $set;
			return;
		}

		$set = $this->sets[$setName];
		$set->variables = array_merge($set->variables, [$varName => $varValue]);
	}
}
