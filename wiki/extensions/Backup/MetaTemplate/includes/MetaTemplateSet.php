<?php

class MetaTemplateSet
{
	/** @var ?string The name of the set. */
	public $name;

	/** @var string[] $variables */
	public $variables = [];

	/**
	 * Creates an instance of the MetaTemplateSet class.
	 *
	 * @param ?string $name The name of the set to create.
	 * @param string[] $variables Any variables to pre-initialize the set with.
	 */
	public function __construct(?string $name = null, array $variables = [])
	{
		$this->name = $name;
		$this->variables = $variables ?? [];
	}
}
