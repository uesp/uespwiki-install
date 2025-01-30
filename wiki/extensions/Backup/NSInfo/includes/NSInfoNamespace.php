<?php

class NSInfoNamespace
{
	#region Public Constants
	public const FIELD_COUNT = 11;
	#endregion

	#region Private Static Fields
	/** @var NSInfoNamespace $empty */
	private static $empty;
	#endregion

	#region Private Fields
	/** @var string $base The full text name of the (pseudo-)namespace without a trailing colon or slash. */
	private $base;

	/** @var string $category The text to be used in category names. */
	private $category = '';

	/** @var string $icon The icon for the namespace. (Currently only applies to mobile app.) */
	private $icon = '';

	/** @var string $id The shortened text ID of the (pseudo-)namespace. */
	private $id = '';

	/** @var bool $isGameSpace Whether the (pseudo-)namespace counts as being in game space. */
	private $isGameSpace = true;

	/** @var bool $isModSpace Whether the (pseudo-)namespace counts as being in mod space. */
	private $isModSpace = false;

	/** @var string $mainPage The main page for the (pseudo-)namespace. */
	private $mainPage = '';

	/** @var string $modBase The base mod namespace if this is a child mod. */
	private $modParent = '';

	/** @var string $name The friendly name of the (pseudo-)namespace. */
	private $name = '';

	/** @var int|bool $nsId The numerical MediaWiki ID of the namespace or false if the requested namespace was invalid. */
	private $nsId;

	/** @var string $pageName The pagename of the pseudo-namespace; otherwise an empty string. */
	private $pageName;

	/** @var string $parent The parent namespace if this is a child (pseudo-)namespace. */
	private $parent = '';

	/** @var NSInfoNamespace[] $pseudoSpaces Any pseudo-namespaces belonging in a given root namespace. */
	private $pseudoSpaces = [];

	/** @var string $trail The trail to use for pages in this (pseudo-)namespace. */
	private $trail = '';
	#endregion

	#region Constructor
	public function __construct($nsOrBase, $pageName = '')
	{
		$contLang = VersionHelper::getInstance()->getContentLanguage();

		if (is_int($nsOrBase)) {
			$this->pageName = $pageName ?? '';
			$nsName = $contLang->getNsText($nsOrBase);
			if ($nsName !== false) {
				$nsName = strtr($nsName, '_', ' ');
				$this->nsId = $nsOrBase;
				if ($this->pageName === '') {
					$this->base = $nsName;
				} else {
					$this->base = "{$nsName}:{$this->pageName}";
					$this->modParent = $nsName;
				}
			} else {
				$this->nsId = false;
			}
		} else if ($nsOrBase === false) {
			$this->nsId = false;
			$this->pageName = '';
			$this->base = '';
		} else {
			$exploded = explode(':', $nsOrBase, 2);
			$nsId = VersionHelper::getInstance()->getContentLanguage()->getNsIndex(strtr($exploded[0], ' ', '_'));
			$this->nsId = $nsId;
			$this->base = $nsOrBase;
			if (count($exploded) === 2) {
				$this->modParent = $exploded[0];
				$this->pageName = $exploded[1];
			} else {
				$this->pageName = '';
			}
		}
	}
	#endregion

	#region Public Static Functions
	public static function empty()
	{
		if (!isset(self::$empty)) {
			self::$empty = new NSInfoNamespace(false, '');
		}

		return self::$empty;
	}

	/**
	 * Returns default namespace information from a namespace ID.
	 *
	 * @param int $nsId
	 * @param string|null $pageName
	 *
	 * @return NSInfoNamespace
	 *
	 */
	public static function fromNamespace(int $nsId): NSInfoNamespace
	{
		$nsId = VersionHelper::getNsSubject($nsId);
		$nsInfo = new NSInfoNamespace($nsId, '');
		if ($nsInfo->getNsId() === false) {
			return NSInfoNamespace::empty();
		}

		$nsInfo->category = strtr($nsInfo->base, ':', '-');
		$nsInfo->isGameSpace = $nsInfo->getDefaultGamespace();
		$nsInfo->id = $nsInfo->base;
		$nsInfo->name = $nsInfo->base;
		$nsInfo->parent = $nsInfo->base;
		$nsInfo->mainPage = $nsInfo->buildMainPage($nsInfo->name);
		$nsInfo->trail = $nsInfo->buildTrail($nsInfo->mainPage, $nsInfo->name);
		$nsInfo->icon = $nsInfo->getDefaultIcon();

		return $nsInfo;
	}

	public static function fromRow(string $row): NSInfoNamespace
	{
		if (($row[0] ?? '') !== '|') {
			return NSInfoNamespace::empty();
		}

		$row = substr($row, 1);
		$row = str_replace('||', '\n|', $row);
		$fields = explode('\n|', $row);
		$fields = array_map('trim', $fields);
		$fields = array_pad($fields, self::FIELD_COUNT, '');

		$nsInfo = new NSInfoNamespace($fields[0]);
		if ($nsInfo->getNsId() === false) {
			return $nsInfo;
		}

		$nsInfo->id = strtoupper(strlen($fields[1])
			? $fields[1]
			: $nsInfo->base);
		$nsInfo->parent = strlen($fields[2])
			? $fields[2]
			: $nsInfo->base;
		$nsInfo->name = strlen($fields[3])
			? $fields[3]
			: $nsInfo->base;
		$nsInfo->mainPage = strlen($fields[4])
			? $fields[4]
			: $nsInfo->buildMainPage($nsInfo->name);
		$nsInfo->category = strlen($fields[5])
			? $fields[5]
			: strtr($nsInfo->base, ':', '-');
		$nsInfo->trail = strlen($fields[6])
			? $fields[6]
			: $nsInfo->buildTrail($nsInfo->mainPage, $nsInfo->name);
		$nsInfo->isGameSpace = strlen($fields[7])
			? filter_var($fields[7], FILTER_VALIDATE_BOOLEAN)
			: $nsInfo->getDefaultGamespace();
		if ($nsInfo->pageName !== '' && strlen($fields[8])) {
			$nsInfo->modParent = $fields[8];
		}
		$nsInfo->icon = strlen($fields[9])
			? $fields[9]
			: $nsInfo->getDefaultIcon();
		$nsInfo->isModSpace = strlen($fields[10])
			? filter_var($fields[10], FILTER_VALIDATE_BOOLEAN)
			: false;

		return $nsInfo;
	}
	#endregion

	#region Public Functions
	public function addPseudoSpaces(array $pseudoSpaces)
	{
		usort($pseudoSpaces, function ($a, $b) {
			return mb_strlen($b->base) <=> mb_strlen($a->base);
		});

		$this->pseudoSpaces = $pseudoSpaces;
	}

	public function buildMainPage(string $name): string
	{
		return $this->getFull() . $name;
	}

	public function buildTrail(string $mainPage, string $name): string
	{
		return "[[$mainPage|$name]]";
	}

	public function getBase(): string
	{
		return $this->base;
	}

	public function getCategory(): string
	{
		return $this->category;
	}

	public function getDefaultGamespace()
	{
		// The IDs listed are the preferred custom namespace ranges for all wikis and are not UESP-specific. The idea here is to make the "No" namespaces explicit in the table.
		$id = $this->nsId;
		return ($id >= 100 && $id < 200) ||
			($id >= 3000 && $id < 5000);
	}

	public function getDefaultIcon(): string
	{
		return "{$this->id}-icon-HeaderIcon.svg";
	}

	public function getFull(): string
	{
		if ($this->base === '') {
			return '';
		}

		return $this->base . (strlen($this->pageName) ? '/' : ':');
	}

	public function getIcon(): string
	{
		return $this->icon;
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function getIsGameSpace(): bool
	{
		return $this->isGameSpace;
	}

	public function getIsModSpace(): bool
	{
		return $this->isModSpace;
	}

	public function getIsPseudoSpace(): bool
	{
		return  $this->pageName !== '';
	}

	public function getMainPage(): string
	{
		return $this->mainPage;
	}

	public function getModName(): string
	{
		if ($this->pageName === '') {
			return '';
		}

		$exploded = explode('/', $this->pageName);
		return end($exploded);
	}

	public function getModParent(): string
	{
		return $this->modParent;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return int|bool
	 */
	public function getNsId()
	{
		return $this->nsId;
	}

	public function getPageName(): string
	{
		return $this->pageName;
	}

	public function getParent(): string
	{
		return $this->parent;
	}

	/**
	 * Gets any pseudo-namespaces in this namespace.
	 *
	 * @return NSInfoNamespace[]
	 */
	public function getPseudoSpaces(): array
	{
		return $this->pseudoSpaces;
	}

	public function getTrail(): string
	{
		return $this->trail;
	}
	#endregion
}
