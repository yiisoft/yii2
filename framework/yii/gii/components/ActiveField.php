<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\components;

use yii\gii\Generator;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveField extends \yii\widgets\ActiveField
{
	/**
	 * @var Generator
	 */
	public $model;

	public function init()
	{
		$stickyAttributes = $this->model->stickyAttributes();
		if (in_array($this->attribute, $stickyAttributes)) {
			$this->sticky();
		}
		$hints = $this->model->hints();
		if (isset($hints[$this->attribute])) {
			$this->hint($hints[$this->attribute]);
		}
	}

	public function sticky()
	{
		$this->options['class'] .= ' sticky';
		return $this;
	}
}
