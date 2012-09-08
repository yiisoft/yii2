<?php
/**
 * Request class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Request extends \yii\base\Request
{
	/**
	 * @var string the controller route specified by this request. If this is an empty string,
	 * it means the [[Application::defaultRoute|default route]] will be used.
	 * Note that the value of this property may not be a correct route. The console application
	 * will determine it is valid or not when it attempts to execute with this route.
	 */
	public $route;
	/**
	 * @var array
	 */
	public $params;

	public function init()
	{
		parent::init();
		$this->resolveRequest();
	}

	public function getRawParams()
	{
		return isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
	}

	protected function resolveRequest()
	{
		$rawParams = $this->getRawParams();
		array_shift($rawParams);  // the 1st argument is the yiic script name

		if (isset($rawParams[0])) {
			$this->route = $rawParams[0];
			array_shift($rawParams);
		} else {
			$this->route = '';
		}

		$this->params = array();
		foreach ($rawParams as $param) {
			if (preg_match('/^--(\w+)(=(.*))?$/', $param, $matches)) {
				$name = $matches[1];
				$this->params[$name] = isset($matches[3]) ? $matches[3] : true;
			} else {
				$this->params['args'][] = $param;
			}
		}
	}
}
