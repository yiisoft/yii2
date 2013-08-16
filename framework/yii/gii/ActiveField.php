<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveField extends \yii\widgets\ActiveField
{
	public function sticky()
	{
		$this->options['class'] .= ' sticky';
		return $this;
	}
}
