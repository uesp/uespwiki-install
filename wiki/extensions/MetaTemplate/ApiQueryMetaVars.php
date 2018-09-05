<?php

/**
 *
 *
 * Created on Aug 2, 2014
 *
 * Copyright © 2014 RobinHood70
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
 */

/**
 * A query module to show basic page information.
 *
 * @ingroup API
 */
class ApiQueryMetaVars extends ApiQueryGeneratorBase {

    public function __construct( $query, $moduleName ) {
        parent::__construct( $query, $moduleName, 'mv' );
    }

    public function execute() {
        $this->run();
    }

    public function executeGenerator( $resultPageSet ) {
        $this->run( $resultPageSet );
    }

    private function run( $resultPageSet = null ) {
        $params = $this->extractRequestParams();
        $isGenerator = $resultPageSet !== null;

        if ( $isGenerator ) {
            /* Current max variables on a page is 52, so an arbitrary limit of 60 should be a
              safe assumption. An accurate limit could be achieved by joining to a query with
              an exact LIMIT, but this seems needlessly complex for something that's likely to
              see limited use. */
            $this->addOption( 'LIMIT', 60 * $params['limit'] );
        } else {
            $pages = $this->getPageSet()->getGoodTitles();
            if ( !count( $pages ) ) {
                # Nothing to do
                return;
            }

            $this->addWhereFld( 'mt_save_set.mt_set_page_id', array_keys( $pages ) );
        }

        $this->addTables( 'mt_save_data' );
        $this->addTables( 'mt_save_set' );
        $this->addJoinConds( array ( 'mt_save_data' => array ( 'INNER JOIN', 'mt_save_set.mt_set_id=mt_save_data.mt_save_id' ) ) );
        $this->addFields( array (
            'mt_save_set.mt_set_id',
            'mt_save_set.mt_set_page_id',
            'mt_save_set.mt_set_subset',
            'mt_save_data.mt_save_varname',
            'mt_save_data.mt_save_value' ) );

        if ( $params['continue'] )
            $this->addWhere( 'mt_save_set.mt_set_page_id >= ' . intval( $params['continue'] ) );

        if ( $params['var'] )
            $this->addWhereFld( 'mt_save_data.mt_save_varname', $params['var'] );

		if ( $params['subset'] !== null )
            $this->addWhereFld( 'mt_save_set.mt_set_subset', $params['subset'] );

        $this->addOption( 'ORDER BY', 'mt_save_set.mt_set_page_id' ); // , mt_save_set.mt_set_subset, mt_save_data.mt_save_varname

        $res = $this->select( __METHOD__ );
        $currentPage = 0; # Id of the page currently processed
        $values = array ( );

        if ( $isGenerator ) {
            $titles = array ( );
            $count = 0;
            foreach ( $res as $row ) {
                if ( $currentPage != $row->mt_set_page_id ) {
                    if ( $currentPage ) {
                        $title = Title::newFromID( $currentPage );
                        if ( $title ) {
                            if ( ++$count > $params['limit'] ) {
                                $this->setContinueEnumParameter( 'continue', $currentPage );
                                break;
                            }

                            $titles[] = $title;
                        }
                    }

                    $currentPage = $row->mt_set_page_id;
                }
            }

            $resultPageSet->populateFromTitles( $titles );
        } else {
            foreach ( $res as $row ) {
                if ( $currentPage != $row->mt_set_page_id ) {
                    # Different page than previous row, so add the values to
                    # the result and save the new page id.

                    if ( $currentPage ) {
                        if ( !$this->addMetaValues( $currentPage, $values ) ) {
                            # addMetaValues() indicated that the result did not fit
                            # so stop adding data. Reset values so that it doesn't
                            # get added again after loop exit

                            $this->setContinueEnumParameter( 'continue', $currentPage );
                            $values = array ( );
                            break;
                        }

                        $values = array ( );
                    }

                    $currentPage = $row->mt_set_page_id;
                }

                $values[$row->mt_set_subset][$row->mt_save_varname] = $row->mt_save_value;
            }

            if ( count( $values ) ) {
                # Add any remaining values to the results.
                $this->addMetaValues( $currentPage, $values );
            }
        }
    }

    /**
     * Add MetaTemplate saved values to the page output.
     *
     * @param $page int
     * @param $values array
     * @return bool True if it fits on the page.
     */
    private function addMetaValues( $page, $values ) {
        $items = array ( );
        foreach ( $values as $key => $set ) {
			$item = array ( );
            if ( $key !== '' )
                $item['subset'] = $key;
            $item['vars'] = $set;
			$items[] = $item;
        }

        return $this->addPageSubItems( $page, $items );
    }

    public function getCacheMode( $params ) {
        return 'public';
    }

    public function getAllowedParams() {
        return array (
            'continue' => null,
            'limit' => array (
                ApiBase::PARAM_DFLT => 10,
                ApiBase::PARAM_TYPE => 'limit',
                ApiBase::PARAM_MIN => 1,
                ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
                ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
            ),
            'subset' => array (
                ApiBase::PARAM_ISMULTI => true,
            ),
            'var' => array (
                ApiBase::PARAM_ISMULTI => true,
            ),
        );
    }

    public function getParamDescription() {
        return array (
            'continue' => 'When more results are available, use this to continue.',
            'limit' => 'The maximum number of pages to return (generator only).',
            'subset' => "Only list values from these subsets. To get only the main set, use \"{$this->getModulePrefix()}subset=|\".",
            'var' => 'Only list values with these names. Useful for checking whether a certain page uses a certain variable.',
        );
    }

    public function getDescription() {
        return 'Get various values saved on the page by MetaTemplate';
    }

    public function getExamples() {
        return array (
            'api.php?action=query&prop=metavars&titles=Skyrim:Riften|Skyrim:Armor|Main%20Page',
            "api.php?action=query&prop=metavars&titles=Skyrim:Riften|Skyrim:Armor|Main%20Page&{$this->getModulePrefix()}subset=|",
            "api.php?action=query&prop=metavars&titles=Skyrim:Armor&{$this->getModulePrefix()}subset=Fur%20Armor|Iron%20Armor&{$this->getModulePrefix()}var=weight|value",
            "api.php?action=query&prop=metavars&titles=Skyrim:Armor&{$this->getModulePrefix()}var=weight|value",
            "api.php?action=query&generator=metavars&g{$this->getModulePrefix()}var=weight|value&prop=metavars&mvvar=weight|value",
        );
    }

    public function getHelpUrls() {
        return 'http://www.uesp.net/wiki/Project:MetaTemplate#API';
    }

	// Can be removed post 1.21
	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}