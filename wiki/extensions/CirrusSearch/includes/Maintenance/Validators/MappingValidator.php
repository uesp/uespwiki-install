<?php

namespace CirrusSearch\Maintenance\Validators;

use CirrusSearch\ElasticsearchIntermediary;
use CirrusSearch\Maintenance\Maintenance;
use Elastica\Exception\ExceptionInterface;
use Elastica\Index;
use Elastica\Request;
use Elastica\Type;
use Elastica\Type\Mapping;
use RawMessage;
use Status;

class MappingValidator extends Validator {
	/**
	 * @var Index
	 */
	private $index;

	/**
	 * @var string
	 */
	private $masterTimeout;

	/**
	 * @var bool
	 */
	private $optimizeIndexForExperimentalHighlighter;

	/**
	 * @var array
	 */
	private $availablePlugins;

	/**
	 * @var array
	 */
	private $mappingConfig;

	/**
	 * @var Type[]
	 */
	private $types;

	/**
	 * @todo: this constructor takes way too much arguments - refactor
	 *
	 * @param Index $index
	 * @param string $masterTimeout
	 * @param bool $optimizeIndexForExperimentalHighlighter
	 * @param array $availablePlugins
	 * @param array $mappingConfig
	 * @param Type[] $types Array with type names as key & type object as value
	 * @param Maintenance $out
	 */
	public function __construct( Index $index, $masterTimeout, $optimizeIndexForExperimentalHighlighter, array $availablePlugins, array $mappingConfig, array $types, Maintenance $out = null ) {
		parent::__construct( $out );

		$this->index = $index;
		$this->masterTimeout = $masterTimeout;
		$this->optimizeIndexForExperimentalHighlighter = $optimizeIndexForExperimentalHighlighter;
		$this->availablePlugins = $availablePlugins;
		$this->mappingConfig = $mappingConfig;
		$this->types = $types;
	}

	/**
	 * @return Status
	 */
	public function validate() {
		$this->outputIndented( "Validating mappings..." );
		if ( $this->optimizeIndexForExperimentalHighlighter &&
			!in_array( 'experimental highlighter', $this->availablePlugins ) ) {
			$this->output( "impossible!\n" );
			return Status::newFatal( new RawMessage(
				"wgCirrusSearchOptimizeIndexForExperimentalHighlighter is set to true but the " .
				"'experimental highlighter' plugin is not installed on all hosts." ) );
		}

		$requiredMappings = $this->mappingConfig;
		if ( !$this->checkMapping( $requiredMappings ) ) {
			/** @var Mapping[] $actions */
			$actions = array();

			// TODO Conflict resolution here might leave old portions of mappings
			foreach ( $this->types as $typeName => $type ) {
				$action = new Mapping( $type );
				foreach ( $requiredMappings[$typeName] as $key => $value ) {
					$action->setParam( $key, $value );
				}

				$actions[] = $action;
			}

			try {
				// @todo Use $action->send(array('master_timeout' => ...))
				// after updating to version of Elastica library that supports it.
				// See https://github.com/ruflin/Elastica/pull/1004
				foreach ( $actions as $action ) {
					$action->getType()->request(
						'_mapping',
						Request::PUT,
						$action->toArray(),
						array(
							'master_timeout' => $this->masterTimeout,
						)
					);
				}
				$this->output( "corrected\n" );
			} catch ( ExceptionInterface $e ) {
				$this->output( "failed!\n" );
				$message = ElasticsearchIntermediary::extractMessage( $e );
				return Status::newFatal( new RawMessage(
					"Couldn't update mappings.  Here is elasticsearch's error message: $message\n" ) );
			}
		}

		return Status::newGood();
	}

	/**
	 * Check that the mapping returned from Elasticsearch is as we want it.
	 *
	 * @param array $requiredMappings the mappings we want
	 * @return bool is the mapping good enough for us?
	 */
	private function checkMapping( array $requiredMappings ) {
		$actualMappings = $this->index->getMapping();
		$this->output( "\n" );
		$this->outputIndented( "\tValidating mapping..." );
		if ( $this->checkConfig( $actualMappings, $requiredMappings ) ) {
			$this->output( "ok\n" );
			return true;
		} else {
			$this->output( "different..." );
			return false;
		}
	}
}
