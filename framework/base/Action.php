<?php
/**
 * Action class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Action is the base class for all controller action classes.
 *
 * Action provides a way to divide a complex controller into
 * smaller actions in separate class files.
 *
 * Derived classes must implement {@link run()} which is invoked by
 * controller when the action is requested.
 *
 * An action instance can access its controller via {@link getController controller} property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Action extends Component
{
	/**
	 * @var string ID of the action
	 */
	public $id;
	/**
	 * @var Controller the controller that owns this action
	 */
	public $controller;

	/**
	 * Extracts the input parameters according to the signature of the "run()" method.
	 * This method is invoked by controller when it attempts to run the action
	 * with the user supplied parameters.
	 * @param array $params the parameters in name-value pairs
	 * @return array|boolean the extracted parameters in the order as declared in the "run()" method.
	 * False is returned if the input parameters do not follow the method declaration.
	 */
	public function normalizeParams($params)
	{
		$method = new \ReflectionMethod($this, 'run');
		return $this->normalizeParamsByMethod($method, $params);
	}

	/**
	 * Extracts the input parameters according to the specified method signature.
	 * @param \ReflectionMethod $method the method reflection
	 * @param array $params the parameters in name-value pairs
	 * @return array|boolean the extracted parameters in the order as declared in the "run()" method.
	 * False is returned if the input parameters do not follow the method declaration.
	 */
	protected function normalizeParamsByMethod($method, $params)
	{
		$ps = array();
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			if (isset($params[$name])) {
				if ($param->isArray()) {
					$ps[] = is_array($params[$name]) ? $params[$name] : array($params[$name]);
				} elseif (!is_array($params[$name])) {
					$ps[] = $params[$name];
				} else {
					return false;
				}
			} elseif ($param->isDefaultValueAvailable()) {
				$ps[] = $param->getDefaultValue();
			} else {
				return false;
			}
		}
		return false;
	}
}
