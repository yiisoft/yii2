<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\model;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public function getName()
	{
		return 'Model Generator';
	}

	public function getDescription()
	{
		return 'This generator generates a model class for the specified database table.';
	}

	/**
	 * @inheritdoc
	 */
	public function generate()
	{
		return array();
	}
}
