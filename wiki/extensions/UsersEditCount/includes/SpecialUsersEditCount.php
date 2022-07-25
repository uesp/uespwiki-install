<?php

/** UsersEditCountPage extends QueryPage.
 * This does the real work of generating the page contents
 * @package MediaWiki
 * @subpackage SpecialPage
 */
class SpecialUsersEditCount extends QueryPage
{
	private static $UEC = 'userseditcount-';
	private static $requestDates = [
		'day' => 1,
		'week' => 7,
		'month' => 31,
		'6month' => 182.5,
		'year' => 365,
	];

	// Can't use array_flip for this because of the non-integer value.
	private static $requestDatesFlipped = [
		'1' => 'day',
		'7' => 'week',
		'31' => 'month',
		'182.5' => '6month',
		'365' => 'year',
	];

	private $requestDate = NULL;
	private $requestDateTitle = '';
	private $group = NULL;
	private $excludeGroup = false;

	public function __construct($name = 'UsersEditCount')
	{
		parent::__construct($name);

		$req = $this->getRequest();
		$inputDate = $req->getVal('date');
		if ($inputDate) {
			$inputDate = strtolower($inputDate);
		}

		// Since we now allow any date to be entered, check if it's one of the well-known values and reverse it if so.
		if (isset(self::$requestDatesFlipped[$inputDate])) {
			$inputDate = self::$requestDatesFlipped[$inputDate];
		}

		if (isset(self::$requestDates[$inputDate])) {
			$this->requestDate = self::$requestDates[$inputDate];
			$this->requestDateTitle = $this->msg(self::$UEC . "showrange-$inputDate")->text();
		} else {
			if (is_numeric($inputDate)) {
				$this->requestDate = $inputDate;
				$this->requestDateTitle = $this->msg(self::$UEC . 'showrange-days', $inputDate)->text();
			} else {
				$this->requestDate = null;
				$this->requestDateTitle = $this->msg(self::$UEC . 'showrange-all')->text();
			}
		}

		$group = $req->getVal('group');
		if (is_null($group)) {
			$this->group = 'bot';
			$this->excludeGroup = true;
		} else {
			$this->group = $group;
			$this->excludeGroup = $group === '' ? false : $req->getBool('excludegroup');
		}

		$this->setListoutput(false);
	}

	public function formatResult($skin, $result)
	{
		if (isset($result->title)) {
			$msg = 'normal';
			$name = $result->title;
			$user = User::newFromName($result->title);
			$name = $user === false
				? $this->msg(self::$UEC . 'invaliduser')->text()
				: Linker::userLink($user->getId(), $name) . Linker::userToolLinks($user->getId(), $result->title, false, Linker::TOOL_LINKS_NOBLOCK, $result->value);
		} else {
			$msg = 'anon';
			$name = null;
			$user = User::newFromId(0);
		}

		return $this->msg(self::$UEC . 'result-' . $msg)
			->params($name)
			->numParams($result->value)
			->text();
	}

	public function getGroupName()
	{
		return 'users';
	}

	public function getPageHeader()
	{
		$header  = '<p>';
		$title = $this->getPageTitle();

		// Form tag
		$header = Xml::openElement(
			'form',
			['method' => 'get', 'action' => wfScript(), 'id' => 'mw-listusers-form']
		) .
			Xml::fieldset($this->msg('userseditcount')->text()) .
			Html::hidden('title', $title) .
			Html::hidden('date', $this->requestDate);

		// Date Options
		$msg = $this->msg(self::$UEC . 'headerlinks');
		foreach (array_keys(self::$requestDates) as $dateRange) {
			$msg->params($this->makeLink($title, $dateRange));
		}

		$msg->params($this->makeLink($title, 'all'));
		$header .= $msg->text() . '<br>';

		// Group drop-down list
		$groupBox = new XmlSelect('group', 'group', $this->group);
		$groupBox->addOption($this->msg('group-all')->text(), '');
		foreach ($this->getAllGroups() as $group => $groupText) {
			$groupBox->addOption($groupText, $group);
		}

		$header .=
			Xml::label($this->msg('group')->text(), 'group') . ' ' .
			$groupBox->getHTML() . '&nbsp;' .
			Xml::checkLabel(
				$this->msg(self::$UEC . 'excludegroup')->text(),
				'excludegroup',
				'excludegroup',
				$this->excludeGroup
			) .
			'&nbsp;';

		// Submit button and form bottom
		$header .=
			Xml::submitButton($this->msg(self::$UEC . 'submit')->text()) .
			Xml::closeElement('fieldset') .
			Xml::closeElement('form');

		$note = is_null($this->requestDate) ? $this->msg(self::$UEC . 'estimate')->text() : '';

		// Intro line
		return
			$header .
			$this->msg(self::$UEC . 'headingtext') .
			' ' .
			$this->requestDateTitle .
			$note .
			'<br>';
	}

	public function getQueryInfo()
	{
		if (is_null($this->requestDate)) {
			// Note that user_editcount is not guaranteed to be accurate, but this query is roughly 5x faster than the revisions query.
			$filterField = 'user_id';
			$queryInfo = [
				'tables' => ['user'],
				'fields' => [
					'2 as namespace',
					'user_name as title',
					'user_editcount as value'
				],
				'conds' => ['user_editcount >= 0']
			];
		} else {
			$filterField = 'rev_user';
			$queryInfo = [
				'tables' => ['revision', 'user'],
				'fields' => [
					'2 as namespace',
					'user_name as title',
					'COUNT(*) as value'
				],
				'conds' => ['rev_timestamp >= "' . wfTimestamp(TS_MW, time() - ($this->requestDate * 86400)) . '"'],
				'join_conds' => [
					'user' => [
						'LEFT JOIN',
						['rev_user=user_id']
					]
				],
				'options' => ['GROUP BY' => 'rev_user']
			];
		}

		if ($this->group) {
			$dbr = wfGetDB(DB_SLAVE);
			$groupFilter = $dbr->selectSQLText('user_groups', 'ug_user', ['ug_group' => $this->group]);
			$not = $this->excludeGroup ? ' NOT' : '';
			$queryInfo['conds'][] = "$filterField$not IN ($groupFilter)";
		}

		return $queryInfo;
	}

	public function isCacheable()
	{
		return false;
	}

	public function isExpensive()
	{
		return true;
	}

	public function isSyndicated()
	{
		return false;
	}

	public function linkParameters()
	{
		return [
			'date' => $this->requestDate,
			'group' => $this->group,
			'excludegroup' => $this->excludeGroup
		];
	}

	public function sortDescending()
	{
		return true;
	}

	/**
	 * Get a list of all explicit groups
	 * @return array
	 */
	private function getAllGroups()
	{
		$result = [];
		foreach (User::getAllGroups() as $group) {
			$result[$group] = User::getGroupName($group);
		}
		asort($result);

		return $result;
	}

	private function makeLink($title, $dateRange)
	{
		$params = $this->linkParameters();
		$params['date'] = $dateRange;
		return Linker::link(
			$title,
			$this->msg("userseditcount-daterange-$dateRange"),
			[],
			$params
		);
	}
}
