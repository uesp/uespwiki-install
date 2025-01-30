<?php

$IP = getenv('MW_INSTALL_PATH');
if ($IP === false) {
	$IP = __DIR__ . '/../../..';
	if (!file_exists("$IP/index.php")) {
		// This version compensates for design environment where main folder is one below normal
		$IP = __DIR__ . '/../../../..';
	}
}

require_once "$IP/maintenance/Maintenance.php";

class CreateList extends Maintenance
{
	/* Duplicated from NSInfo class so script has no dependencies and can be run prior to activating NSInfo. The
	 * obvious drawback is that it doesn't respond to any changes in NSInfo. */
	private const NSLIST = 'MediaWiki:Nsinfo-namespacelist';
	private const OLDLIST = 'MediaWiki:Sitenamespacelist';
	private const UESPLIST = 'MediaWiki:Uespnamespacelist';

	public function __construct()
	{
		parent::__construct();
		$this->addDescription('Create new namespacelist, converting from old version if present');
	}

	public function execute()
	{
		$nsList = Title::newFromText(self::NSLIST);
		if ($nsList->exists()) {
			$this->output('WARNING - current list is being overwritten. You may revert ' . $nsList->getFullText() . " as normal if this was not what was intended.\n\n");
		}

		$this->output(
			"For simplicity, this script creates only a basic table using the default terms. Additional text is " .
				"possible and probably desirable. The only limitations are that the data must be in a single table " .
				"with id=nsinfo-table and that the cells in any normal row are strictly data with no formatting or " .
				"comments of any kind. Title rows will be ignored, as will anything before or after the table.\n"
		);

		$rows = [];
		$helper = VersionHelper::getInstance();
		$oldTitle = Title::newFromText(self::OLDLIST);
		if (!$oldTitle->exists()) {
			$oldTitle = Title::newFromText(self::UESPLIST);
		}

		if ($oldTitle->exists()) {
			$text = $helper->getPageText($oldTitle) ?? '';
			$lines = explode("\n", $text);
			$user = User::newSystemUser('MediaWiki default', ['steal' => true]);
			foreach ($lines as $line) {
				$line = preg_replace('/\s*<\s*\/?\s*pre(\s+[^>]*>|>)\s*/', '', trim($line));
				if (substr($line, 0, 1) !== '#' && strlen($line) > 0) {
					$fields = explode(';', $line);
					$fields = array_map('trim', $fields);
					$fields = array_pad($fields, 8, '');
					$row = '| ' . implode(' || ', $fields);
					$rows[] = $row;
				}
			}
		}

		$pageText =
			"{| class=\"wikitable sortable\" id=nsinfo-table\n" .
			"! NS_BASE !! NS_ID !! NS_PARENT !! NS_NAME !! NS_MAINPAGE !! NS_CATEGORY !! NS_TRAIL !! GAMESPACE\n" .
			"|-\n" .
			implode("\n|-\n", $rows) .
			"\n|}";
		$content = new WikitextContent($pageText);
		$helper->saveContent($nsList, $content, 'Namespaces updated', $user, EDIT_SUPPRESS_RC | EDIT_INTERNAL);
	}
}

$maintClass = CreateList::class;
require_once RUN_MAINTENANCE_IF_MAIN;
