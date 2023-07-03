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
class ApiQueryMetaVars extends ApiQueryGeneratorBase
{
	#region Private Constants
	private const KEY_CONTINUE = 'continue';
	private const KEY_LIMIT = 'limit';
	private const KEY_SET = 'set';
	private const KEY_VAR = 'var';
	private const KEY_VARS = self::KEY_VAR . 's';
	#endregion

	#region Constructor
	public function __construct($query, $moduleName)
	{
		parent::__construct($query, $moduleName, 'mv');
	}
	#endregion

	#region Public Override Functions
	public function execute()
	{
		$this->run();
	}

	public function executeGenerator($resultPageSet)
	{
		$this->run($resultPageSet);
	}

	public function getAllowedParams()
	{
		return [
			self::KEY_CONTINUE => null,
			self::KEY_LIMIT => [
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_TYPE => self::KEY_LIMIT,
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			],
			self::KEY_SET => [
				ApiBase::PARAM_ISMULTI => true,
			],
			self::KEY_VAR => [
				ApiBase::PARAM_ISMULTI => true,
			],
		];
	}

	public function getCacheMode($params)
	{
		return 'public';
	}

	public function getDescription()
	{
		return 'Get various values saved on the page by MetaTemplate';
	}

	public function getExamples()
	{
		return [
			'api.php?action=query&prop=metavars&titles=Skyrim:Riften|Skyrim:Armor|Main%20Page',
			"api.php?action=query&prop=metavars&titles=Skyrim:Riften|Skyrim:Armor|Main%20Page&{$this->getModulePrefix()}" . self::KEY_SET . '=|',
			"api.php?action=query&prop=metavars&titles=Skyrim:Armor&{$this->getModulePrefix()}" . self::KEY_VAR . '=weight|value',
			"api.php?action=query&prop=metavars&titles=Skyrim:Armor&{$this->getModulePrefix()}" . self::KEY_SET . "=Fur%20Armor|Iron%20Armor&{$this->getModulePrefix()}" . self::KEY_VAR . '=weight|value',
			"api.php?action=query&generator=metavars&g{$this->getModulePrefix()}&prop=metavars" . self::KEY_VAR . '=weight|value&mv' . self::KEY_VAR . '=weight|value',
		];
	}

	public function getHelpUrls()
	{
		return 'https://www.uesp.net/wiki/Project:MetaTemplate#API';
	}
	#endregion

	#region Private Functions
	private function run($resultPageSet = null)
	{
		$params = $this->extractRequestParams();
		$isGenerator = $resultPageSet !== null;
		if ($isGenerator) {
			/* Current max variables on a page is 52, so an arbitrary limit of 60 should be a safe assumption. An
			 * accurate limit could be achieved by joining to a query with an exact LIMIT, but this seems needlessly
			 * complex for something that's likely to see limited use.
			 */
			$this->addOption('LIMIT', 60 * $params[self::KEY_LIMIT]);
		} else {
			$pages = $this->getPageSet()->getGoodTitles();
			if (!count($pages)) {
				# Nothing to do
				return;
			}

			$this->addWhereFld(MetaTemplateSql::SET_PAGE_ID, array_keys($pages));
		}

		$this->addTables(MetaTemplateSql::TABLE_DATA);
		$this->addTables(MetaTemplateSql::TABLE_SET);
		$this->addJoinConds([MetaTemplateSql::TABLE_DATA => ['JOIN', MetaTemplateSql::SET_SET_ID . '=' . MetaTemplateSql::DATA_SET_ID]]);
		$this->addFields([
			MetaTemplateSql::SET_SET_ID,
			MetaTemplateSql::SET_PAGE_ID,
			MetaTemplateSql::SET_SET_NAME,
			MetaTemplateSql::DATA_VAR_NAME,
			MetaTemplateSql::DATA_VAR_VALUE
		]);

		if ($params[self::KEY_CONTINUE])
			$this->addWhere(MetaTemplateSql::SET_PAGE_ID . '>=' . (int)$params[self::KEY_CONTINUE]);

		if ($params[self::KEY_VAR])
			$this->addWhereFld(MetaTemplateSql::DATA_VAR_NAME, $params[self::KEY_VAR]);

		if ($params[self::KEY_SET] !== null)
			$this->addWhereFld(MetaTemplateSql::SET_SET_NAME, $params[self::KEY_SET]);

		$this->addOption('ORDER BY', [MetaTemplateSql::SET_PAGE_ID, MetaTemplateSql::SET_REV_ID]);

		$rows = $this->select(__METHOD__);
		$currentPage = 0; # Id of the page currently processed
		$values = [];

		if ($isGenerator) {
			$titles = [];
			$count = 0;
			for ($row = $rows->fetchRow(); $row; $row = $rows->fetchRow()) {
				if ($currentPage != $row[MetaTemplateSql::FIELD_PAGE_ID]) {
					if ($currentPage) {
						$title = Title::newFromID($currentPage);
						if ($title) {
							if (++$count > $params[self::KEY_LIMIT]) {
								$this->setContinueEnumParameter(self::KEY_CONTINUE, $currentPage);
								break;
							}

							$titles[] = $title;
						}
					}

					$currentPage = $row[MetaTemplateSql::FIELD_PAGE_ID];
				}
			}

			$resultPageSet->populateFromTitles($titles);
		} else {
			for ($row = $rows->fetchRow(); $row; $row = $rows->fetchRow()) {
				if ($currentPage != $row[MetaTemplateSql::FIELD_PAGE_ID]) {
					# Different page than previous row, so add the values to
					# the result and save the new page id.

					if ($currentPage) {
						if (!$this->addMetaValues($currentPage, $values)) {
							# addMetaValues() indicated that the result did not fit
							# so stop adding data. Reset values so that it doesn't
							# get added again after loop exit

							$this->setContinueEnumParameter(self::KEY_CONTINUE, $currentPage);
							$values = [];
							break;
						}

						$values = [];
					}

					$currentPage = $row[MetaTemplateSql::FIELD_PAGE_ID];
				}

				$values[$row[MetaTemplateSql::FIELD_SET_NAME]][$row[MetaTemplateSql::FIELD_VAR_NAME]] = $row[MetaTemplateSql::FIELD_VAR_VALUE];
			}

			if (count($values)) {
				# Add any remaining values to the results.
				$this->addMetaValues($currentPage, $values);
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
	private function addMetaValues($page, $values)
	{
		$items = [];
		foreach ($values as $key => $set) {
			$item = [];
			if ($key !== '')
				$item[self::KEY_SET] = $key;
			$item[self::KEY_VARS] = $set;
			$items[] = $item;
		}

		return $this->addPageSubItems($page, $items);
	}
	#endregion
}
