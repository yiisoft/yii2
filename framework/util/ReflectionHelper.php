<?php
/**
 * ReflectionHelper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

/**
 * ReflectionHelper
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ReflectionHelper
{
	/**
	 * Prepares parameters so that they can be bound to the specified method.
	 * This method mainly helps method parameter binding. It converts `$params`
	 * into an array which can be passed to `call_user_func_array()` when calling
	 * the specified method. The conversion is based on the matching of method parameter names
	 * and the input array keys. For example,
	 *
	 * ~~~
	 * class Foo {
	 *     function bar($a, $b) { ... }
	 * }
	 *
	 * $method = new \ReflectionMethod('Foo', 'bar');
	 * $params = array('b' => 2, 'c' => 3, 'a' => 1);
	 * var_export(ReflectionHelper::bindMethodParams($method, $params));
	 * // would output: array('a' => 1, 'b' => 2)
	 * ~~~
	 *
	 * @param \ReflectionMethod $method the method reflection
	 * @param array $params the parameters in terms of name-value pairs
	 * @return array|boolean the parameters that can be passed to the method via `call_user_func_array()`.
	 * False is returned if the input parameters do not follow the method declaration.
	 */
	public static function bindParams($method, $params)
	{
		$ps = array();
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			if (array_key_exists($name, $params)) {
				if ($param->isArray()) {
					$ps[$name] = is_array($params[$name]) ? $params[$name] : array($params[$name]);
				} elseif (!is_array($params[$name])) {
					$ps[$name] = $params[$name];
				} else {
					return false;
				}
			} elseif ($param->isDefaultValueAvailable()) {
				$ps[$name] = $param->getDefaultValue();
			} else {
				return false;
			}
		}
		return $ps;
	}
}
