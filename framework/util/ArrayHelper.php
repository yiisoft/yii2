<?php
/**
 * ArrayHelper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

/**
 * ArrayHelper provides additional array functionality you can use in your
 * application.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ArrayHelper extends \yii\base\Component
{
	/**
	 * Merges two or more arrays into one recursively.
	 * If each array has an element with the same string key value, the latter
	 * will overwrite the former (different from array_merge_recursive).
	 * Recursive merging will be conducted if both arrays have an element of array
	 * type and are having the same key.
	 * For integer-keyed elements, the elements from the latter array will
	 * be appended to the former array.
	 * @param array $a array to be merged to
	 * @param array $b array to be merged from. You can specify additional
	 * arrays via third argument, fourth argument etc.
	 * @return array the merged array (the original arrays are not changed.)
	 */
	public static function merge($a, $b)
	{
		$args = func_get_args();
		$res = array_shift($args);
		while ($args !== array()) {
			$next = array_shift($args);
			foreach ($next as $k => $v) {
				if (is_integer($k)) {
					isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
				} elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
					$res[$k] = self::merge($res[$k], $v);
				} else {
					$res[$k] = $v;
				}
			}
		}
		return $res;
	}

	/**
	 * Retrieves the value of an array element with the specified key.
	 *
	 * If the key does not exist in the array, the default value will be returned instead.
	 * For example,
	 *
	 * ~~~
	 * $username = \yii\util\ArrayHelper::get($_POST, 'username');
	 * ~~~
	 *
	 * @param array $array array to extract value from
	 * @param string $key key name of the array element
	 * @param mixed $default the default value to be returned if the specified key does not exist
	 * @return mixed
	 */
	public static function get($array, $key, $default = null)
	{
		return isset($array[$key]) || array_key_exists($key, $array) ? $array[$key] : $default;
	}

	/**
	 * Converts an array such that it is indexed by the specified column values or anonymous function results.
	 * This method should be used only for two or higher dimensional arrays.
	 * For example,
	 *
	 * ~~~
	 * $array = array(
	 *     array('id' => '123', 'data' => 'abc'),
	 *     array('id' => '345', 'data' => 'def'),
	 * );
	 * $result = ArrayHelper::index($array, 'id');
	 * // the result is:
	 * // array(
	 * //     '123' => array('id' => '123', 'data' => 'abc'),
	 * //     '345' => array('id' => '123', 'data' => 'abc'),
	 * // )
	 *
	 * // using anonymous function
	 * $result = ArrayHelper::index($array, function(element) {
	 *     return $element['id'];
	 * });
	 *
	 * Note that if an index value is null, it will NOT be added to the resulting array.
	 *
	 * @param array $array the multidimensional array that needs to be indexed
	 * @param mixed $key the column name or anonymous function whose result will be used to index the array
	 * @return array the indexed array (the input array will be kept intact.)
	 */
	public static function index($array, $key)
	{
		$result = array();
		if ($key instanceof \Closure) {
			foreach ($array as $element) {
				$key = call_user_func($key, $element);
				if ($key !== null) {
					$result[$key] = $element;
				}
			}
		} else {
			foreach ($array as $element) {
				if (isset($element[$key]) && $element[$key] !== null) {
					$result[$element[$key]] = $element;
				}
			}
		}
		return $result;
	}
}