<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ModelEvent class.
 *
 * ModelEvent represents the parameter needed by model events.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModelEvent extends Event
{
	/**
	 * @var boolean whether the model is in valid status. Defaults to true.
	 * A model is in valid status if it passes validations or certain checks.
	 */
	public $isValid = true;
}
