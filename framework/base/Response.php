<?php
/**
 * Response class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Response extends ApplicationComponent
{
	public function beginOutput()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	public function endOutput()
	{
		return ob_get_clean();
	}

	public function getOutput()
	{
		return ob_get_contents();
	}

	public function cleanOutput()
	{
		ob_clean();
	}

	public function removeOutput($all = true)
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
