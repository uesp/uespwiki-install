<?php
class MetaTemplateUpserts
{
	/** @var int[] */
	public $deletes = [];

	/** @var MetaTemplateSet[] */
	public $inserts = [];

	/** @var array */ // [int setId, [MetaTemplateSet oldSet, MetaTemplateSet newSet]]
	public $updates = [];

	public $newRevId;
	public $oldRevId;
	public $pageId;

	/**
	 * Creates a new instance of the MetaTemplateUpserts class.
	 *
	 * @param ?MetaTemplateSetCollection $oldData
	 * @param ?MetaTemplateSetCollection $newData
	 */
	public function __construct(?MetaTemplateSetCollection $oldData, ?MetaTemplateSetCollection $newData)
	{
		$oldSets = $oldData->sets ?? null;
		$newSets = $newData->sets ?? null;

		if ($oldData) {
			#RHshow('Old Data', $oldData);
			$this->pageId = $oldData->articleId;
			$this->oldRevId = $oldData->revId;
			foreach ($oldSets as $setName => $oldSet) {
				if (!isset($newSets[$setName])) {
					$oldId = $oldData->setIds[$setName] ?? 0;
					if ($oldId !== 0) {
						$this->deletes[] = $oldId;
					}
				}
			}
		}

		if ($newData) {
			#RHshow('New Data', $newData);
			$this->pageId = $newData->articleId; // Possibly redundant, but if both collections are present, both page IDs will be the same.
			$this->newRevId = $newData->revId;
			if ($newSets) {
				foreach ($newSets as $setName => $newSet) {
					$oldSet = $oldSets[$setName] ?? null;
					if (is_null($oldSet)) {
						$this->inserts[] = $newSet;
					} else {
						// All sets are checked for updates as long as an old set existed, since transcluded info may have changed values.
						$oldId = $oldData->setIds[$setName] ?? 0;
						if ($oldId !== 0 || $this->newRevId === 0) {
							ksort($oldSet->variables);
							ksort($newSet->variables);
							if ($oldSet != $newSet) {
								$this->updates[$oldId] = [$oldSet, $newSet];
							}
						}
					}
				}
			}
		}

		/*
		if (count($this->deletes)) {
			RHshow('Upsert Deletes', $this->deletes);
		}

		if (count($this->inserts)) {
			#RHshow('Upsert Inserts', $this->inserts);
		}

		if (count($this->updates)) {
			#RHshow('Upsert Updates', $this->updates);
		}
		*/
	}

	/**
	 * Gets the total number of operations for this upsert.
	 *
	 * @return int The total number of operations for this upsert.
	 */
	public function getTotal()
	{
		return count($this->deletes) + count($this->inserts) + count($this->updates);
	}
}
