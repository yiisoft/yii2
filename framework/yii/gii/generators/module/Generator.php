<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\module;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public function getName()
	{
		return 'Module Generator';
	}

	public function getDescription()
	{
		return 'This generator helps you to generate the skeleton code needed by a Yii module.';
	}
}
