<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Request extends \yii\base\Request
{
	const ANONYMOUS_PARAMS = '-args';

	public function getRawParams()
	{
		return isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
	}

	/**
	 * Resolves the current request into a route and the associated parameters.
	 * @return array the first element is the route, and the second is the associated parameters.
	 */
	public function resolve()
	{
		$rawParams = $this->getRawParams();
		array_shift($rawParams);  // the 1st argument is the yiic script name

		if (isset($rawParams[0])) {
			$route = $rawParams[0];
			array_shift($rawParams);
		} else {
			$route = '';
		}

		$params = array(self::ANONYMOUS_PARAMS => array());
		foreach ($rawParams as $param) {
			if (preg_match('/^--(\w+)(=(.*))?$/', $param, $matches)) {
				$name = $matches[1];
				$params[$name] = isset($matches[3]) ? $matches[3] : true;
			} else {
				$params[self::ANONYMOUS_PARAMS][] = $param;
			}
		}

		return array($route, $params);
	}
}
