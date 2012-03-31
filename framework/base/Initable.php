<?php
/**
 * Initable interface file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Initable is an interface indicating a class needs initialization to work properly.
 *
 * Initable requires a class to implement the [[init()]] method.
 * When [[\Yii::createObject()]] is being used to create a new component which implements
 * Initable, it will call the [[init()]] method after setting the initial values of the
 * component properties.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Initable
{
	/**
	 * Initializes this component.
	 * This method is invoked by [[\Yii::createObject]] after its creates the new
	 * component instance and initializes the component properties. In other words,
	 * at this stage, the component has been fully configured.
	 */
	public function init();
}
