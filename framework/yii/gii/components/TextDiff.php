<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\components;

use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class TextDiff
{
	public static function compare($lines1, $lines2)
	{
		if (is_string($lines1)) {
			$lines1 = explode("\n", $lines1);
		}
		if (is_string($lines2)) {
			$lines2 = explode("\n", $lines2);
		}
		$diff = new \Horde_Text_Diff('auto', array($lines1, $lines2));
		$renderer = new \Horde_Text_Diff_Renderer_Inline();
		return $renderer->render($diff);
	}
}
