<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\jui;

use Yii;

/**
 * This is the bootstrap class for the Yii JUI extension.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Extension extends \yii\base\Extension
{
	/**
	 * @inheritdoc
	 */
	public static function init()
	{
		Yii::setAlias('@yii/jui', __DIR__);
	}
}
