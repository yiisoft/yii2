<?php
/**
 * ReflectionHelper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

use yii\base\Exception;

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
	 * This method converts the input parameters into an array that can later be
	 * passed to `call_user_func_array()` when calling the specified method.
	 * The conversion is based on the matching of method parameter names
	 * and the input array keys. For example,
	 *
	 * ~~~
	 * class Foo {
	 *     function bar($a, $b) { ... }
	 * }
	 * $object = new Foo;
	 * $params = array('b' => 2, 'c' => 3, 'a' => 1);
	 * var_export(ReflectionHelper::extractMethodParams($object, 'bar', $params));
	 * // output: array('a' => 1, 'b' => 2);
	 * ~~~
	 *
	 * @param object|string $object the object or class name that owns the specified method
	 * @param string $method the method name
	 * @param array $params the parameters in terms of name-value pairs
	 * @return array parameters that are needed by the method only and
	 * can be passed to the method via `call_user_func_array()`.
	 * @throws Exception if any required method parameter is not found in the given parameters
	 */
	public static function extractMethodParams($object, $method, $params)
	{
		$m = new \ReflectionMethod($object, $method);
		$ps = array();
		foreach ($m->getParameters() as $param) {
			$name = $param->getName();
			if (array_key_exists($name, $params)) {
				$ps[$name] = $params[$name];
			} elseif ($param->isDefaultValueAvailable()) {
				$ps[$name] = $param->getDefaultValue();
			} else {
				throw new Exception(\Yii::t('yii', 'Missing required parameter "{name}".', array('{name}' => $name)));
			}
		}
		return $ps;
	}

	/**
	 * Initializes an object with the given parameters.
	 * Only the public non-static properties of the object will be initialized, and their names must
	 * match the given parameter names. For example,
	 *
	 * ~~~
	 * class Foo {
	 *     public $a;
	 *     protected $b;
	 * }
	 * $object = new Foo;
	 * $params = array('b' => 2, 'c' => 3, 'a' => 1);
	 * $remaining = ReflectionHelper::bindObjectParams($object, $params);
	 * var_export($object);    // output: $object->a = 1; $object->b = null;
	 * var_export($remaining); // output: array('b' => 2, 'c' => 3);
	 * ~~~
	 *
	 * @param object $object the object whose properties are to be initialized
	 * @param array $params the input parameters to be used to initialize the object
	 * @return array the remaining unused input parameters
	 */
	public static function initObjectWithParams($object, $params)
	{
		if (empty($params)) {
			return array();
		}

		$class = new \ReflectionClass(get_class($object));
		foreach ($params as $name => $value) {
			if ($class->hasProperty($name)) {
				$property = $class->getProperty($name);
				if ($property->isPublic() && !$property->isStatic()) {
					$object->$name = $value;
					unset($params[$name]);
				}
			}
		}

		return $params;
	}
}
