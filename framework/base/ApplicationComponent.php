<?php
/**
 * ApplicationComponent class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ApplicationComponent is the base class for application component classes.
 *
 * Child classes mainly needs to implement the [[Initable::init|init]] method as required by
 * the [[Initable]] interface.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class ApplicationComponent extends Component implements Initable
{
	/**
	 * @var array the behaviors that should be attached to this component.
	 * The behaviors will be attached to the component when [[init]] is called.
	 * Please refer to [[Model::behaviors]] on how to specify the value of this property.
	 */
	public $behaviors = array();

	/**
	 * Initializes the application component.
	 * This method is invoked after the component is created and its property values are
	 * initialized. The default implementation will call [[Component::attachBehaviors()]]
	 * to attach behaviors declared in [[behaviors]].
	 * If you override this method, make sure to call the parent implementation.
	 */
	public function init()
	{
		$this->attachBehaviors($this->behaviors);
	}
}
