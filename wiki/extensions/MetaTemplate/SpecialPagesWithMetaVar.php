<?php
/**
 * Implements Special:PagesWithMetaVar
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */
class SpecialPagesWithMetaVar extends QueryPage {

	private $subsetName = null;
	private $varName = null;
	private $sortByVal = null;
	private $nsNum = null;

	public function __construct() {
		parent::__construct( 'PagesWithMetaVar' );
	}
	/*
	  public function preprocessResults ( $db, $res ) {
	  var_dump ( $res );
	  }
	 */

	public function execute( $par ) {
		global $wgContLang;
		
		$this->setHeaders();
		$this->outputHeader();
		$this->getOutput()->addModuleStyles( 'mediawiki.special.mtpageswithvar' );

		$subsetname = null;
		$varname = null;
		$sort = false;

		if ( $par !== null ) {
			$newPar = str_replace( '_', ' ', $par );
			$split = split( '/', $newPar, 2 );

			if ( count( $split ) == 1 ) {
				$subsetname = '*';
				$varname = $split[0];
			} else {
				$subsetname = $split[0];
				$varname = $split[1];
			}
		} else {
			$request = $this->getRequest();
			$varname = $request->getVal( 'varname' );
			$subsetname = $request->getVal( 'subsetname', '*' );
			$sort = $request->getVal( 'sortbyval' );
			$nsNum = $request->getVal( 'ns', 'all' );
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'mt_save_data',
			array ( 'mt_save_varname' ),
			'',
			__METHOD__,
			array (
				'GROUP BY' => 'mt_save_varname',
				'ORDER BY' => 'COUNT(*) DESC',
				'LIMIT' => 25
			)
		);
		
		$varnames = array ();
		foreach ( $res as $row ) {
			$varnames[$row->mt_save_varname] = $row->mt_save_varname;
		}

		natcasesort( $varnames );
		
		$nsAll = wfMessage( 'namespacesall' )->text();
		$namespaces = array( $nsAll => 'all' );
		
		$nsQuery = $dbr->select(
			array ( 'mt_save_set', 'page' ),
			array ( 'DISTINCT page_namespace' ),
			null,
			__METHOD__,
			array ( 'ORDER BY' => 'page_namespace' ),
			array ( 'mt_save_set' => array ( 'INNER JOIN', 'mt_save_set.mt_set_page_id = page.page_id' ), )
		);
		
		foreach ( $nsQuery as $row ) {
			$nsId = $row->page_namespace;
			if ( $nsId >= NS_MAIN ) {
				if ( $nsId === NS_MAIN ) {
					$nsName = wfMessage( 'blanknamespace' )->text();
				} else {
					$nsName = $wgContLang->getFormattedNsText( $nsId );
				}
				
				$namespaces[$nsName] = $nsId;
			}
		}

		$form = new HTMLForm( array (
			'subsetname' => array (
				'type' => 'selectorother',
				'name' => 'subsetname',
				'options' => array (
					$this->msg( 'mt_pageswithvar-subsetany' )->text() => '*',
					$this->msg( 'mt_pageswithvar-subsetmain' )->text() => ':', // 1.19 considers '' to break "required" rule, even though required = false
					$this->msg( 'mt_pageswithvar-subsetspecific' )->text() => 'other',
				),
				'default' => $subsetname,
				'label-message' => 'mt_subset',
				'required' => false,
			),
			'varname' => array (
				'type' => 'selectorother',
				'name' => 'varname',
				'options' => $varnames,
				'default' => $varname,
				'label-message' => 'mt_varname',
				'required' => false,
			),
			'sortbyval' => array (
				'type' => 'check',
				'name' => 'sortbyval',
				'default' => $sort,
				'label-message' => 'mt_pageswithvar-sort',
			),
			'ns' => array (
				'type' => 'select',
				'name' => 'ns',
				'options' => $namespaces,
				'default' => $nsNum,
				'label-message' => 'namespace',
			),
		), $this->getContext() );
		$form->setMethod( 'get' );
		$form->setSubmitCallback( array ( $this, 'onSubmit' ) );
		$form->setWrapperLegendMsg( 'mt_pageswithvar-legend' );
		$form->addHeaderText( $this->msg( 'mt_pageswithvar-text' )->parseAsBlock() );
		$form->setSubmitTextMsg( 'mt_pageswithvar-submit' );

		$form->prepareForm();
		$result = $form->trySubmit();
		$form->displayForm( $result );

		if ( $result === true || ( $result instanceof Status && $result->isGood() ) ) {
			parent::execute( $par );
		}
	}

	public function formatResult( $skin, $result ) {
		$title = Title::newFromRow( $result );
		$ret = Linker::link( $title, null, array (), array (), array ( 'known' ) );

		$ret .= wfMessage( 'colon-separator' )->escaped();
		
		if ( $result->mt_set_subset !== null ) {
			if ( $result->mt_set_subset === '' ) {
				$ret .= Html::element( 'span', array ( 'class' => 'mt-subsetvalue mt-subsetvaluemain' ), $this->msg ( 'mt_pageswithvar-subsetmain' )->text() );
			} else {
				$ret .= Html::element( 'span', array ( 'class' => 'mt-subsetvalue' ), $result->mt_set_subset );
			}
			
			if ( $result->mt_save_varname !== null ) {
				$ret .= ' → ';
			}
		}

		if ( $result->mt_save_varname !== null ) {
			$ret .= Html::element( 'span', array ( 'class' => 'mt-varvalue' ), $result->mt_save_varname . ' = ' . $result->mt_save_value );
		}

		return $ret;
	}

	protected function getGroupName() {
		return 'pages';
	}

	public function getOrderFields() {
		$return = array ();
		if ( $this->sortByVal && $this->varName !== null && $this->varName !== ':' ) {
			$return[] = 'mt_save_value';
		}

		return array_merge( $return, array ( 'page_namespace', 'page_title', 'mt_set_subset' ) );
	}

	public function getQueryInfo() {
		$result = array ();
		
		if ( ( $this->subsetName !== null && $this->subsetName !== '*' ) && ($this->varName === null || $this->varName === '') ) {
			$result = array (
				'tables' => array ( 'page', 'mt_save_set' ),
				'fields' => array (
					'page_id',
					'page_namespace',
					'page_title',
					'page_len',
					'page_is_redirect',
					'page_latest',
					'mt_set_subset',
				),
				'conds' => array (),
				'options' => array (),
				'join_conds' => array ( 'mt_save_set' => array ( 'INNER JOIN', 'page_id = mt_set_page_id' ) ),
			);
		} else {
			$result = array (
				'tables' => array ( 'page', 'mt_save_set', 'mt_save_data' ),
				'fields' => array (
					'page_id',
					'page_namespace',
					'page_title',
					'page_len',
					'page_is_redirect',
					'page_latest',
					'mt_set_subset',
					'mt_save_varname',
					'mt_save_value',
				),
				'conds' => array ( 'mt_save_varname' => $this->varName ),
				'options' => array (),
				'join_conds' => array (
					'mt_save_set' => array ( 'INNER JOIN', 'page.page_id = mt_save_set.mt_set_page_id' ),
					'mt_save_data' => array ( 'INNER JOIN', 'mt_save_set.mt_set_id = mt_save_data.mt_save_id' ),
				),
			);
		}

		if ( $this->subsetName === ':' ) {
			$result[conds] += array ( 'mt_set_subset' => '' );
		} elseif ( $this->subsetName !== '*' ) {
			$result[conds] += array ( 'mt_set_subset' => $this->subsetName );
		}
		
		if ( $this->nsNum !== null && $this->nsNum !== 'all' ) {
			$result[conds] += array ( 'page_namespace' => $this->nsNum );
		}

		return $result;
	}

	public function isCacheable() {
		return false;
	}

	public function isExpensive() {
		return true;
	}

	public function isSyndicated() {
		return false;
	}

	public function linkParameters() {
		return array ( 'subsetname' => $this->subsetName, 'varname' => $this->varName, 'sortbyval' => $this->sortByVal, 'ns' => $this->nsNum );
	}

	public function onSubmit( $data, $form ) {
		$this->subsetName = $data['subsetname'];
		$this->varName = $data['varname'];
		$this->sortByVal = $data['sortbyval'];
		$this->nsNum = $data['ns'];

		if ( $this->subsetName === '*' && $this->varName === '' ) {
			return Status::newFatal( 'mt_pageswithvar-nodata' );
		}

		return Status::newGood();
	}

	public function sortDescending() {
		return false;
	}
}
