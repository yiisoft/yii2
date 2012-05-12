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
	 * Runs this action with the specified parameters.
	 * This method is mainly invoked by the controller.
	 * @param array $params action parameters
	 * @return integer the exit status (0 means normal, non-zero means abnormal).
	 */
	public function runWithParams($params)
	{
		$method = new \ReflectionMethod($this->controller, 'action' . $this->id);
		$params = \yii\util\ReflectionHelper::bindParams($method, $params);
		if ($params === false) {
			$this->controller->invalidActionParams($this);
			return 1;
		} else {
			return (int)$method->invokeArgs($this, $params);
		}
	}
}
