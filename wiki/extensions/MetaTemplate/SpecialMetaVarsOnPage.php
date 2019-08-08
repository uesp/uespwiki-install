<?php
/**
 * Implements Special:MetaVarsOnPage
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup SpecialPage
 */

/**
 * A special page that lists existing blocks
 *
 * @ingroup SpecialPage
 */
class SpecialMetaVarsOnPage extends SpecialPage {

	private $page;
	
	private $limit;

	function __construct() {
		parent::__construct( 'MetaVarsOnPage' );
	}

	/**
	 * Main execution point
	 *
	 * @param string $par title fragment
	 */
	public function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();
		$out = $this->getOutput();
		$lang = $this->getLanguage();
		$out->addModuleStyles( 'mediawiki.special' );

		$request = $this->getRequest();
		$this->page = $request->getVal( 'page', $par );
		$this->limit = intval ( $request->getVal( 'limit' , 50 ) );

		$action = $request->getText( 'action' );

		$fields = array(
			'Page' => array(
				'type' => 'text',
				'name' => 'page',
				'label-message' => 'mt_varsonpage-page',
				'default' => $this->page,
			),
			'Limit' => array(
				'type' => 'select',
				'name' => 'limit',
				'label-message' => 'table_pager_limit_label',
				'options' => array(
					$lang->formatNum( 20 ) => 20,
					$lang->formatNum( 50 ) => 50,
					$lang->formatNum( 100 ) => 100,
					$lang->formatNum( 250 ) => 250,
					$lang->formatNum( 500 ) => 500,
				),
				'default' => 50,
			),
		);
		$form = new HTMLForm( $fields, $this->getContext() );
		$form->setMethod( 'get' );
		$form->setWrapperLegendMsg( 'mt_varsonpage-legend' );
		$form->setSubmitTextMsg( 'mt_varsonpage-submit' );
		$form->prepareForm();

		$form->displayForm( '' );
		$this->showList();
	}

	function showList() {
		if ( $this->page !== null ) {
			$title = Title::newFromText ( $this->page );
		}
		
		$conds = array();

		$out = $this->getOutput();

		
		if ( $title && $title->getNamespace() >= 0 ) {
			$conds['mt_set_page_id'] = $title->getArticleID();
			$pager = new MetaVarsPager( $this->getContext() , $conds, $this->limit );
			if ( $pager->getNumRows() ) {
				$out->addHTML(
					$pager->getNavigationBar() .
						$pager->getBody() .
						$pager->getNavigationBar()
				);
			} else {
				$out->addWikiMsg( 'mt_varsonpage-no-results' );
			}
		} else {
			$out->addWikiMsg( 'mt_varsonpage-no-page' );
		}
	}

	protected function getGroupName() {
		return 'wiki';
	}
}

class MetaVarsPager extends TablePager {
	private $conds;

	/**
	 * @param $page SpecialPage
	 * @param $conds Array
	 */
	function __construct( $context, $conds, $limit ) {
		$this->conds=$conds;
		$this->mLimit=$limit;
		$this->mDefaultDirection = false;
		
		// TablePager doesn't handle two-key offsets and doesn't seem to support simple numerical offsets either.
		// This seemed like an acceptable trade-off, since it offers the added benefit of always showing
		// an entire set. The drawback is that if limit is set to less than the number of keys in the subset,
		// you'll never get anywhere.
		$this->mIncludeOffset = true;
		parent::__construct( $context );
	}

	function getFieldNames() {
		static $headers = null;

		if ( $headers === null ) {
			$headers = array(
				'mt_set_subset' => 'mt_subset',
				'mt_save_varname' => 'mt_varname',
				'mt_save_value' => 'mt_varvalue',
			);
			
			foreach ( $headers as $key => $val ) {
				$headers[$key] = $this->msg( $val )->text();
			}
		}

		return $headers;
	}

	function formatValue( $name, $value ) {
		switch ( $name ) {
			case 'mt_set_subset':
				$formatted = Html::rawElement(
						'span',
						array( 'class' => 'mt_varsonpage-subset', 'style' => 'white-space:nowrap;' ),
						$value
					);
				break;
			default:
				$formatted = htmlspecialchars( $value );
				break;
		}

		return $formatted;
	}

	function getQueryInfo() {
		return array (
			'tables' => array ( 'mt_save_set', 'mt_save_data' ),
			'fields' => array (
				'mt_set_page_id',
				'mt_set_subset',
				'mt_save_varname',
				'mt_save_value',
			),
			'conds' => $this->conds,
			'options' => array (),
			'join_conds' => array (
				'mt_save_data' => array ( 'INNER JOIN', 'mt_save_set.mt_set_id = mt_save_data.mt_save_id' ),
			),
		);
	}

	public function getTableClass() {
		return 'TablePager mt-varsonpage';
	}

	function getDefaultSort() {
		return 'mt_set_subset';
	}
	
	function getExtraSortFields() {
		return array ( 'mt_save_varname' );
	}

	function isFieldSortable( $name ) {
		return $name !== 'mt_save_value';
	}
}
