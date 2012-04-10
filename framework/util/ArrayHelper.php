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
	 * Merges two arrays into one recursively.
	 * If each array has an element with the same string key value, the latter
	 * will overwrite the former (different from array_merge_recursive).
	 * Recursive merging will be conducted if both arrays have an element of array
	 * type and are having the same key.
	 * For integer-keyed elements, the elements from the latter array will
	 * be appended to the former array.
	 * @param array $a array to be merged to
	 * @param array $b array to be merged from
	 * @return array the merged array (the original arrays are not changed.)
	 * @see mergeWith
	 */
	public static function merge($a, $b)
	{
		foreach ($b as $k => $v) {
			if (is_integer($k)) {
				isset($a[$k]) ? $a[] = $v : $a[$k] = $v;
			} elseif (is_array($v) && isset($a[$k]) && is_array($a[$k])) {
				$a[$k] = static::merge($a[$k], $v);
			} else {
				$a[$k] = $v;
			}
		}
		return $a;
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
}