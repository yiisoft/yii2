<?php
/**
 * InlineAction class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\util\ReflectionHelper;

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
	 * Runs this action with the specified parameters.
	 * This method is mainly invoked by the controller.
	 * @param array $params action parameters
	 * @return integer the exit status (0 means normal, non-zero means abnormal).
	 */
	public function runWithParams($params)
	{
		try {
			$method = 'action' . $this->id;
			$ps = ReflectionHelper::extractMethodParams($this->controller, $method, $params);
		} catch (Exception $e) {
			$this->controller->invalidActionParams($this, $e);
			return 1;
		}
		if ($params !== $ps) {
			$this->controller->extraActionParams($this, $ps, $params);
		}
		return (int)call_user_func_array(array($this->controller, $method), $ps);
	}
}
