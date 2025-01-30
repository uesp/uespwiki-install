<?php

use MediaWiki\MediaWikiServices;

/**
 * Implements Special:PagesWithMetaVar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */
class SpecialPagesWithMetaVar extends QueryPage
{
	private $linkRenderer = null;
	private $nsNum = null;
	private $setName = null;
	private $sortByVal = null;
	private $varName = null;

	public function __construct()
	{
		parent::__construct('PagesWithMetaVar');
		$this->linkRenderer = MediaWikiServices::getInstance()->getLinkRenderer();
	}
	/*
	  public function preprocessResults ( $db, $res ) {
	  var_dump ( $res );
	  }
	 */

	public function execute($par)
	{
		$this->setHeaders();
		$this->outputHeader();

		$setName = null;
		$sort = false;

		$par = is_null($par) ? trim($par) : '';
		if (strlen($par)) {
			$newPar = str_replace('_', ' ', $par);
			$split = explode('/', $newPar, 2);
			if (count($split) === 1) {
				$setName = '*';
			} else {
				$setName = $split[0];
			}
		} else {
			$request = $this->getRequest();
			$setName = $request->getVal('setname', '*');
			$sort = $request->getVal('sortbyval');
			$nsNum = $request->getVal('ns', 'all');
		}

		$descriptor = [
			'setname' => [
				'type' => 'selectorother',
				'name' => 'setname',
				'options' => [
					$this->msg('metatemplate-pageswithmetavar-setany')->text() => '*',
					$this->msg('metatemplate-pageswithmetavar-setmain')->text() => '',
					$this->msg('metatemplate-pageswithmetavar-setspecific')->text() => 'other',
				],
				'default' => $setName,
				'label-message' => 'metatemplate-pageswithmetavar-set',
				'required' => false,
			],
			'varname' => [
				'type' => 'text',
				'name' => 'varname',
				'label-message' => 'metatemplate-pageswithmetavar-varname',
				'required' => false,
			],
			'sortbyval' => [
				'type' => 'check',
				'name' => 'sortbyval',
				'default' => $sort,
				'label-message' => 'metatemplate-pageswithmetavar-sort',
			],
			'ns' => [
				'type' => 'namespaceselect',
				'name' => 'ns',
				'default' => $nsNum,
				'label-message' => 'namespace',
				'all' => '',
			],
		];

		$form = HTMLForm::factory('ooui', $descriptor, $this->getContext());
		$form
			->setMethod('get')
			->setSubmitCallback([$this, 'onSubmit'])
			->setWrapperLegendMsg('metatemplate-pageswithmetavar-legend')
			->addHeaderText($this->msg('metatemplate-pageswithmetavar-text')->parseAsBlock())
			->setSubmitTextMsg('metatemplate-pageswithmetavar-submit')
			->prepareForm()
			->displayForm(false);
		$result = $form->trySubmit();

		if ($result === true || ($result instanceof Status && $result->isGood())) {
			parent::execute($par);
		}
	}

	public function formatResult($skin, $result)
	{
		$title = Title::newFromRow($result);
		$retval = $this->linkRenderer->makeKnownLink($title);
		$retval .= wfMessage('colon-separator')->escaped();
		$row = get_object_vars($result);
		$setName = $row[MetaTemplateSql::FIELD_SET_NAME] ?? null;
		if ($setName !== null) {
			$retval .= $setName === ''
				? Html::element(
					'span',
					['class' => 'mt-setvalue mt-setvaluemain'],
					$this->msg('metatemplate-pageswithmetavar-setmain')->text()
				)
				: Html::element(
					'span',
					['class' => 'mt-setvalue'],
					$row[MetaTemplateSql::FIELD_SET_NAME]
				);
			if (isset($row[MetaTemplateSql::FIELD_VAR_NAME])) {
				$retval .= ' → ';
			}
		}

		if (isset($row[MetaTemplateSql::FIELD_VAR_NAME])) {
			$retval .= Html::element(
				'span',
				['class' => 'mt-varvalue'],
				$row[MetaTemplateSql::FIELD_VAR_NAME] . ' = ' . $row[MetaTemplateSql::FIELD_VAR_VALUE]
			);
		}

		return $retval;
	}

	protected function getGroupName()
	{
		return 'pages';
	}

	public function getOrderFields()
	{
		$retval = [];
		if ($this->sortByVal && $this->varName !== null && $this->varName !== '') {
			$retval[] = MetaTemplateSql::FIELD_VAR_VALUE;
		}

		$retval = array_merge($retval, ['page_namespace', 'page_title', MetaTemplateSql::FIELD_SET_NAME]);

		return $retval;
	}

	public function getQueryInfo()
	{
		return MetaTemplateSql::getInstance()->getPageswWithMetaVarsQueryInfo($this->nsNum, $this->setName, $this->varName);
	}

	public function isCacheable()
	{
		return true;
	}

	public function isExpensive()
	{
		return false;
	}

	public function isSyndicated()
	{
		return false;
	}

	public function linkParameters()
	{
		return [
			'setname' => $this->setName,
			'varname' => $this->varName,
			'sortbyval' => $this->sortByVal,
			'ns' => $this->nsNum
		];
	}

	public function onSubmit($data, $form)
	{
		$this->setName = $data['setname'];
		$this->varName = $data['varname'];
		$this->sortByVal = $data['sortbyval'];
		$this->nsNum = $data['ns'];

		return ($this->setName === '*' && $this->varName === '')
			? Status::newFatal('metatemplate-pageswithmetavar-nodata')
			: Status::newGood();
	}

	public function sortDescending()
	{
		return false;
	}
}
