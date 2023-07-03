<?php

/**
 * Overrides the newFrame method in order to allow variable assignment on pages rather than only in templates.
 */
class MetaTemplatePreprocessor extends Preprocessor_Hash
{
	/**
	 * Creates a template-like frame as the root frame.
	 *
	 * @return MetaTemplateFrameRoot
	 *
	 */
	function newFrame(): MetaTemplateFrameRoot
	{
		return new MetaTemplateFrameRoot($this);
	}
}
