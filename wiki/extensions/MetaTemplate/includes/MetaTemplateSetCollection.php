<?php

class MetaTemplateSetCollection
{
	/** @var int */
	public $articleId;

	/** @var int $revId */
	public $revId;

	/**
	 * All sets on the page.
	 *
	 * @var MetaTemplateSet[]
	 */
	public $sets = [];

	/**
	 * All set IDs.
	 *
	 * @var int[]
	 */
	public $setIds = []; // We mostly want to ignore the IDs in any operations, except when it comes to the final upserts, so we store them separately.

	/**
	 * Creates a set collection.
	 *
	 * @param Title $title The title the set belongs to.
	 * @param int $revId The revision ID of the set.

	 * @internal These parameters are strictly here so that they travel with the set data; they're not used internally.
	 *
	 */
	public function __construct(int $articleId, int $revId)
	{
		$this->articleId = $articleId;
		$this->revId = $revId;
	}

	/**
	 * Adds variables to a set, creating a new one if needed. Values will not be updated if they already existed in the
	 * base set.
	 *
	 * @param int $setId The set ID. If set to zero, the set will be ignored for deletes and updates, though it will be
	 *                   added, if appropriate.
	 * @param string $setName
	 *
	 * @return MetaTemplateSet
	 *
	 */
	public function addToSet(int $setId, string $setName, array $variables = []): MetaTemplateSet
	{
		if ($setId) {
			$this->setIds[$setName] = $setId;
		}

		if (isset($this->sets[$setName])) {
			$retval = $this->sets[$setName];
			$retval->variables = array_replace($retval->variables, $variables);
		} else {
			$retval = new MetaTemplateSet($setName, $variables);
			$this->sets[$setName] = $retval;
		}

		return $retval;
	}
}
