<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\apidoc\helpers;

use Parsedown;
use yii\base\Component;

/**
 * A Markdown helper with support for class reference links.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Markdown extends Component
{
	private $_parseDown;

	protected function getParseDown()
	{
		if ($this->_parseDown === null) {
			$this->_parseDown = new ParseDown();
		}
		return $this->_parseDown;
	}

	public function parse($markdown)
	{
		return $this->getParseDown()->parse($markdown);
	}

	public function parseLine($markdown)
	{
		return $this->getParseDown()->parseLine($markdown);
	}

	public function registerBlockHander($blockName, $callback)
	{
		$this->getParseDown()->register_block_handler($blockName, $callback);
	}

	public function unregisterBlockHander($blockName)
	{
		$this->getParseDown()->remove_block_handler($blockName);
	}

	public function registerInlineMarkerHandler($marker, $callback)
	{
		$this->getParseDown()->add_span_marker($marker, $callback);
	}

	public function unregisterInlineMarkerHandler($marker)
	{
		$this->getParseDown()->remove_span_marker($marker);
	}
}
