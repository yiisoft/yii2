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
 * The name of the controller method should be in the format of `actionXyz`
 * where `Xyz` stands for the action ID (e.g. `actionIndex`).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
	/**
	 * Runs the action with the supplied parameters.
	 * This method is invoked by the controller.
	 * @param array $params the input parameters in terms of name-value pairs.
	 * @return boolean whether the input parameters are valid
	 */
	public function runWithParams($params)
	{
		$method = new \ReflectionMethod($this->controller, 'action' . $this->id);
		$params = $this->normalizeParamsByMethod($method, $params);
		if ($params !== false) {
			call_user_func_array(array($this->controller, 'action' . $this->id), $params);
			return true;
		} else {
			return false;
		}
	}
}
