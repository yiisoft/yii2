<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InlineAction represents an action that is defined as a controller method.
 *
 * The name of the controller method is available via [[actionMethod]] which
 * is set by the [[controller]] who creates this action.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
	/**
	 * @var string the controller method that  this inline action is associated with
	 */
	public $actionMethod;

	/**
	 * @param string $id the ID of this action
	 * @param Controller $controller the controller that owns this action
	 * @param string $actionMethod the controller method that  this inline action is associated with
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($id, $controller, $actionMethod, $config = array())
	{
		$this->actionMethod = $actionMethod;
		parent::__construct($id, $controller, $config);
	}

	/**
	 * Runs this action with the specified parameters.
	 * This method is mainly invoked by the controller.
	 * @param array $params action parameters
	 * @return integer the exit status (0 means normal, non-zero means abnormal).
	 */
	public function runWithParams($params)
	{
		$args = $this->controller->bindActionParams($this, $params);
		return (int)call_user_func_array(array($this->controller, $this->actionMethod), $args);
	}
}
