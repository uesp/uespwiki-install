<?php

/**
 *
 *
 * Created on Jul 16, 2024
 *
 * Copyright © 2024 RobinHood70
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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
class ApiQueryNSInfo extends ApiQueryBase
{
	#region Constructor
	public function __construct($query, $moduleName)
	{
		parent::__construct($query, $moduleName, 'nsi');
	}
	#endregion

	#region Public Override Functions
	public function execute()
	{
		$allNamespaces = MWNamespace::getCanonicalNamespaces();
		$nsNamespaces = NSInfo::getNsMessage();
		$result = $this->getResult();

		foreach ($allNamespaces as $nsId => $nsCanonical) {
			if (MWNamespace::isSubject($nsId)) {
				$ns = NSInfo::nsFromArg($nsId);
				$data = $this->getApiResult($ns);
				if ($data !== '') {
					#RHDebug::echo($ns->getBase());
					$result->addValue(['query', $this->getModuleName()], $ns->getBase(), $data);
				}
			}
		}

		foreach ($nsNamespaces as $key => $ns) {
			if ($ns->getIsPseudoSpace()) {
				$data = $this->getApiResult($ns);
				if ($data !== '') {
					$result->addValue(['query', $this->getModuleName()], $ns->getBase(), $data);
				}
			}
		}
	}

	public function getAllowedParams()
	{
		return [];
	}

	public function getCacheMode($params)
	{
		return 'public';
	}

	public function getDescription()
	{
		return 'Get namespace-specific values used by NSInfo';
	}

	public function getExamples()
	{
		return [
			'api.php?action=query&list=nsinfo',
		];
	}

	public function getHelpUrls()
	{
		return 'https://www.uesp.net/wiki/Project:NSInfo#API';
	}
	#endregion

	#region PRivate Methods
	private function getApiResult(NSInfoNamespace $ns)
	{
		$icon = $ns->getIcon();
		$iconTitle = Title::newFromText('File:' . $icon);
		if (!$iconTitle) {
			return '';
		}

		$iconPage = WikiPage::factory($iconTitle);
		if ($iconPage instanceof WikiFilePage) {
			$iconurl = $iconPage->exists()
				? $iconPage->getFile()->getUrl()
				: '';

			return [
				'category' => $ns->getCategory(),
				'full' => $ns->getFull(),
				'icon' => $icon,
				'iconurl' => $iconurl,
				'id' => $ns->getId(),
				'isgamespace' => $ns->getIsGameSpace(),
				'ismodspace' => $ns->getIsModSpace(),
				'ispseudospace' => $ns->getIsPseudoSpace(),
				'mainpage' => $ns->getMainPage(),
				'modname' => $ns->getModName(),
				'modparent' => $ns->getModParent(),
				'name' => $ns->getName(),
				'nsid' => $ns->getNsId(),
				'pagename' => $ns->getPageName(),
				'parent' => $ns->getParent(),
				'trail' => $ns->getTrail(),
			];
		}

		return '';
	}
}
