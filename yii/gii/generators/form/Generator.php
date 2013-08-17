<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\generators\form;

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
	public function getName()
	{
		return 'Form Generator';
	}

	public function getDescription()
	{
		return 'This generator generates a view script file that displays a form to collect input for the specified model class.';
	}
}
