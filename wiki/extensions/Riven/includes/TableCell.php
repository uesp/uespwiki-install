<?php
class TableCell
{
    private $attribs = '';
    private $colspan = 1;
    private $content = '';
    private $isHeader = false;
    private $rowspan = 1;
    private $rowspanModified = false;

    /**
     * $parent
     *
     * @var self
     */
    private $parent;

    public function __construct($cell)
    {
        if ($cell) {
            if ($cell instanceof TableCell) {
                // We don't do rowspan here as alterations to rowspan should be synchronized with the parent.
                $this->attribs = $cell->attribs;
                $this->colspan = $cell->colspan;
                $this->isHeader = $cell->isHeader;
                $this->parent = $cell;
            } else {
                $this->attribs = trim($cell['attribs']);
                $this->content = trim($cell['content']);
                $this->isHeader = $cell['name'] === 'th';
                preg_match('#\bcolspan\s*=\s*([\'"]?)(?<span>\d+)\1#', $this->attribs, $colspan);
                if ($colspan) {
                    $this->colspan = $colspan['span'];
                }

                preg_match('#\browspan\s*=\s*([\'"]?)(?<span>\d+)\1#', $this->attribs, $rowspan);
                if ($rowspan) {
                    $this->rowspan = $rowspan['span'];
                }
            }
        }
    }

    public function decrementRowspan()
    {
        // Because of the possibility of repeated updates, the rowspan value is altered on its own and the attributes
        // updated only when actually called for.
        if ($this->parent) {
            $this->parent->decrementRowspan();
        } else {
            $this->rowspan--;
            $this->rowspanModified = true;
        }
    }

    public function getAttributes()
    {
        if ($this->parent) {
            return $this->parent->getAttributes();
        }

        $this->updateRowspan();
        return $this->attribs;
    }

    public function getColspan()
    {
        return $this->colspan;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRowspan()
    {
        $obj = $this->parent ? $this->parent : $this;
        return $obj->rowspan;
    }

    public function isHeader()
    {
        return $this->isHeader;
    }

    public function toHtml()
    {
        if ($this->parent) {
            return '';
        }

        $this->updateRowSpan();
        $name = $this->isHeader ? 'th' : 'td';
        $attribs = trim($this->attribs);
        if (strlen($attribs) > 0) {
            $attribs = ' ' . $attribs;
        }

        return "<$name$attribs>$this->content</$name>";
    }

    private function updateRowSpan()
    {
        if ($this->rowspanModified) {
            $this->attribs = preg_replace('#\s*rowspan\s*=\s*([\'"]?)(\d+)\1#', $this->rowspan === 1 ? '' : " rowspan=$this->rowspan", $this->attribs);
            $this->rowspanModified = false;
        }
    }
}
