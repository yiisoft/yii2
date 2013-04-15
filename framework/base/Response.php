<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Response extends Component
{
	/**
	 * Starts output buffering
	 */
	public function beginOutput()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Returns contents of the output buffer and discards it
	 * @return string output buffer contents
	 */
	public function endOutput()
	{
		return ob_get_clean();
	}

	/**
	 * Returns contents of the output buffer
	 * @return string output buffer contents
	 */
	public function getOutput()
	{
		return ob_get_contents();
	}

	/**
	 * Discards the output buffer
	 * @param boolean $all if true recursively discards all output buffers used
	 */
	public function cleanOutput($all = true)
	{
		if ($all) {
			for ($level = ob_get_level(); $level > 0; --$level) {
				if (!@ob_end_clean()) {
					ob_clean();
				}
			}
		} else {
			ob_end_clean();
		}
	}
}
