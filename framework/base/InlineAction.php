<?php
/**
 * InlineAction class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * InlineAction represents an action that is defined as a controller method.
 *
 * The method name is like 'actionXYZ' where 'XYZ' stands for the action name.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
	/**
	 * Runs the action.
	 * This method is invoked by the controller to run the action.
	 * @param array $params the input parameters
	 */
	public function run($params)
	{
		call_user_func_array(array($this->controller, 'action' . $this->id), $params);
	}

	/**
	 * Extracts the input parameters according to the signature of the controller action method.
	 * This method is invoked by controller when it attempts to run the action
	 * with the user supplied parameters.
	 * @param array $params the parameters in name-value pairs
	 * @return array|boolean the extracted parameters in the order as declared in the controller action method.
	 * False is returned if the input parameters do not follow the method declaration.
	 */
	public function normalizeParams($params)
	{
		$method = new \ReflectionMethod($this->controller, 'action' . $this->id);
		$params = $this->normalizeParams($method, $params);
		if ($params !== false) {
			return array($params);
		} else {
			return false;
		}
	}
}
