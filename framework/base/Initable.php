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
 * Initable requires a class to implement the [[init]] method.
 * When [[\Yii::createComponent]] is creating a new component instance, if the component
 * class implements Initable interface, the method will call its [[init]] method
 * after setting the initial values of the component properties.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Initable
{
	/**
	 * Initializes this component.
	 * This method is invoked by [[\Yii::createComponent]] after its creates the new
	 * component instance and initializes the component properties. In other words,
	 * at this stage, the component has been fully configured.
	 *
	 * The default implementation calls [[behaviors]] and registers any available behaviors.
	 * You may override this method with additional initialization logic (e.g. establish DB connection).
	 * Make sure you call the parent implementation.
	 */
	public function init();
}
