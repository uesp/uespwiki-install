<?php
class TableCell
{
	#region Private Constants
	private const PREFIX_LEN = 9; // strlen(Parser::MARKER_SUFFIX) = 6 for MW < 1.27;
	#endregion

	#region Static Fields
	/** @var string $colSpanRegex Regex to find a colspan. */
	private static $colSpanRegex = '#\bcolspan\s*=\s*([\'"]?)(?<span>\d+)\1#';

	/** @var string $rowSpanRegex Regex to find a rowspan. */
	private static $rowSpanRegex = '#\browspan\s*=\s*([\'"]?)(?<span>\d+)\1#';
	#endregion

	#region Fields
	private $attribs;
	private $rowspanModified;
	#endregion

	#region Public Properties
	/** @var int $colspan Reflection of the colspan attribute */
	public $colspan;

	/** @var string $content Content between <tr> or <th> tags. */
	public $content;

	/** @var bool $isHeader Is this a <th>? */
	public $isHeader;

	/** @var bool $isImage Does the content reduce to being just an image (optionally with surrounding tag pairs)? */
	public $isImage;

	/** @var ?self $parent Is this is a span element, the parent cell; otherwise, null. */
	public $parent;

	/** @var int $colspan Reflection of the rowspan attribute */
	public $rowspan;
	#endregion

	#region Constructor
	/**
	 * Creates a new instance of a TableCell.
	 *
	 * @param string $attribs Cell attributes.
	 * @param bool $isHeader Whether the cell a header.
	 * @param ?TableCell $parent The parent cell for cells that span multiple rows or columns.
	 * @param int $colspan How many columns the cell spans.
	 * @param int $rowspan How many rows the cell spans.
	 */
	private function __construct(?string $content, string $attribs, bool $isHeader, ?TableCell $parent, int $colspan, int $rowspan)
	{
		$this->content = html_entity_decode($content);
		$this->attribs = $attribs;
		$this->isHeader = $isHeader;
		$this->parent = $parent;
		$this->colspan = $colspan;
		$this->rowspan = $rowspan;
	}
	#endregion

	#region Magic Methods
	public function __debugInfo()
	{
		$parent = $this->parent;
		if (is_null($parent)) {
			return [
				'attribs:TableCell:private' => $this->attribs,
				'rowspanModified:TableCell:private' => $this->rowspanModified,
				'isHeader' => $this->isHeader,
				'colspan' => $this->colspan,
				'rowspan' => $this->rowspan,
				'content' => $this->content,
				'isImage' => $this->isImage
			];
		}

		return ['<span child>' => ''];
	}
	#endregion

	/**
	 * Creates a new instance of a TableCell from a Regex match with named groups.
	 *
	 * @param array $match
	 *
	 * @return ?TableCell
	 *
	 */
	public static function FromMatch(array $match): ?TableCell
	{
		if (!isset($match)) {
			return null;
		}

		$attribs = trim($match['attribs']);
		preg_match(self::$colSpanRegex, $attribs, $colspan);
		$colspan = $colspan ? intval($colspan['span']) : 1;
		preg_match(self::$rowSpanRegex, $attribs, $rowspan);
		$rowspan = $rowspan ? intval($rowspan['span']) : 1;

		return new TableCell($match['content'], $attribs, $match['name'] === 'th', null, $colspan, $rowspan);
	}

	/**
	 * Creates a TableCell that points to its parent cell for column/row spans.
	 *
	 * @param TableCell $parent
	 *
	 * @return ?TableCell
	 *
	 */
	public static function SpanChild(TableCell $parent): ?TableCell
	{
		return isset($parent)
			? new TableCell('', '', $parent->isHeader, $parent, 0, 0, false)
			: null;
	}

	/**
	 * Reduces the parent rowspan by one or, if there's no parent, the current rowspan.
	 *
	 * @return void
	 *
	 */
	public function decrementRowspan(): void
	{
		$this->rowspan--;
		$this->rowspanModified = true;
	}

	/**
	 * Serializes the TableCell to HTML.
	 *
	 * @return string
	 */
	public function toHtml(): string
	{
		if ($this->parent) {
			return '';
		}

		$this->updateRowSpan();
		$name = $this->isHeader ? 'th' : 'td';
		$attribs = strlen($this->attribs) > 0 ? ' ' . $this->attribs : '';
		return "<$name$attribs>$this->content</$name>";
	}

	public function getTrimmedContent(bool $cleanImages): string
	{
		$content = $this->content;
		if ($this->isHeader) {
			$startPos = strpos($content, Parser::MARKER_PREFIX);
			$endPos = $startPos ? strpos($content, Parser::MARKER_SUFFIX, $startPos + self::PREFIX_LEN) : false;
			if ($startPos === false || $endPos === false) {
				return $content;
			}
		}

		if ($cleanImages && !$this->isHeader) {
			// Remove <img> tags
			$content = preg_replace('#<img[^>]+?/>#', '', $content, -1, $imgCount);
			$initialCount = $imgCount;
			while ($imgCount > 0) {
				// Removes any content-free open/close tags that used to surround the removed image.
				$content = preg_replace('#<(\w+)[^>]*>\s*</(\1)>#', '', $content, -1, $imgCount);
			}

			$content = trim($content);
			$this->isImage = $initialCount && !strlen($content) && !$this->isHeader;
		} else {
			$this->isImage = false; // This may actually be true, but we don't care.
		}

		// Remove unassigned {{{parameter values}}}
		$content = preg_replace('#\{{3}[^\}]+\}{3}#', '', $content, -1, $count);
		while ($count > 0) {
			// Removes any content-free open/close tags that used to surround the removed value.
			$content = preg_replace('#<(\w+)[^>]*>\s*</(\1)>#', '', $content, -1, $count);
		}

		return trim($content);
	}

	/**
	 * Updates the rowspan portion of the attribs based on the current rowspan property.
	 *
	 * @return void
	 */
	private function updateRowSpan(): void
	{
		if ($this->rowspanModified) {
			$this->attribs = preg_replace(self::$rowSpanRegex, $this->rowspan === 1 ? '' : "rowspan=$this->rowspan", $this->attribs);
			$this->rowspanModified = false;
		}
	}
}
