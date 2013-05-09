<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

use Yii;
use yii\base\InvalidParamException;

/**
 * ArrayHelper provides additional array functionality you can use in your
 * application.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ArrayHelper
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
		while (!empty($args)) {
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
	 * Retrieves the value of an array element or object property with the given key or property name.
	 * If the key does not exist in the array, the default value will be returned instead.
	 *
	 * Below are some usage examples,
	 *
	 * ~~~
	 * // working with array
	 * $username = \yii\helpers\ArrayHelper::getValue($_POST, 'username');
	 * // working with object
	 * $username = \yii\helpers\ArrayHelper::getValue($user, 'username');
	 * // working with anonymous function
	 * $fullName = \yii\helpers\ArrayHelper::getValue($user, function($user, $defaultValue) {
	 *     return $user->firstName . ' ' . $user->lastName;
	 * });
	 * ~~~
	 *
	 * @param array|object $array array or object to extract value from
	 * @param string|\Closure $key key name of the array element, or property name of the object,
	 * or an anonymous function returning the value. The anonymous function signature should be:
	 * `function($array, $defaultValue)`.
	 * @param mixed $default the default value to be returned if the specified key does not exist
	 * @return mixed the value of the
	 */
	public static function getValue($array, $key, $default = null)
	{
		if ($key instanceof \Closure) {
			return $key($array, $default);
		} elseif (is_array($array)) {
			return isset($array[$key]) || array_key_exists($key, $array) ? $array[$key] : $default;
		} else {
			return $array->$key;
		}
	}

	/**
	 * Indexes an array according to a specified key.
	 * The input array should be multidimensional or an array of objects.
	 *
	 * The key can be a key name of the sub-array, a property name of object, or an anonymous
	 * function which returns the key value given an array element.
	 *
	 * If a key value is null, the corresponding array element will be discarded and not put in the result.
	 *
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
	 * //     '345' => array('id' => '345', 'data' => 'def'),
	 * // )
	 *
	 * // using anonymous function
	 * $result = ArrayHelper::index($array, function(element) {
	 *     return $element['id'];
	 * });
	 * ~~~
	 *
	 * @param array $array the array that needs to be indexed
	 * @param string|\Closure $key the column name or anonymous function whose result will be used to index the array
	 * @return array the indexed array
	 */
	public static function index($array, $key)
	{
		$result = array();
		foreach ($array as $element) {
			$value = static::getValue($element, $key);
			$result[$value] = $element;
		}
		return $result;
	}

	/**
	 * Returns the values of a specified column in an array.
	 * The input array should be multidimensional or an array of objects.
	 *
	 * For example,
	 *
	 * ~~~
	 * $array = array(
	 *     array('id' => '123', 'data' => 'abc'),
	 *     array('id' => '345', 'data' => 'def'),
	 * );
	 * $result = ArrayHelper::getColumn($array, 'id');
	 * // the result is: array( '123', '345')
	 *
	 * // using anonymous function
	 * $result = ArrayHelper::getColumn($array, function(element) {
	 *     return $element['id'];
	 * });
	 * ~~~
	 *
	 * @param array $array
	 * @param string|\Closure $name
	 * @param boolean $keepKeys whether to maintain the array keys. If false, the resulting array
	 * will be re-indexed with integers.
	 * @return array the list of column values
	 */
	public static function getColumn($array, $name, $keepKeys = true)
	{
		$result = array();
		if ($keepKeys) {
			foreach ($array as $k => $element) {
				$result[$k] = static::getValue($element, $name);
			}
		} else {
			foreach ($array as $element) {
				$result[] = static::getValue($element, $name);
			}
		}

		return $result;
	}

	/**
	 * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
	 * The `$from` and `$to` parameters specify the key names or property names to set up the map.
	 * Optionally, one can further group the map according to a grouping field `$group`.
	 *
	 * For example,
	 *
	 * ~~~
	 * $array = array(
	 *     array('id' => '123', 'name' => 'aaa', 'class' => 'x'),
	 *     array('id' => '124', 'name' => 'bbb', 'class' => 'x'),
	 *     array('id' => '345', 'name' => 'ccc', 'class' => 'y'),
	 * );
	 *
	 * $result = ArrayHelper::map($array, 'id', 'name');
	 * // the result is:
	 * // array(
	 * //     '123' => 'aaa',
	 * //     '124' => 'bbb',
	 * //     '345' => 'ccc',
	 * // )
	 *
	 * $result = ArrayHelper::map($array, 'id', 'name', 'class');
	 * // the result is:
	 * // array(
	 * //     'x' => array(
	 * //         '123' => 'aaa',
	 * //         '124' => 'bbb',
	 * //     ),
	 * //     'y' => array(
	 * //         '345' => 'ccc',
	 * //     ),
	 * // )
	 * ~~~
	 *
	 * @param array $array
	 * @param string|\Closure $from
	 * @param string|\Closure $to
	 * @param string|\Closure $group
	 * @return array
	 */
	public static function map($array, $from, $to, $group = null)
	{
		$result = array();
		foreach ($array as $element) {
			$key = static::getValue($element, $from);
			$value = static::getValue($element, $to);
			if ($group !== null) {
				$result[static::getValue($element, $group)][$key] = $value;
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * Sorts an array of objects or arrays (with the same structure) by one or several keys.
	 * @param array $array the array to be sorted. The array will be modified after calling this method.
	 * @param string|\Closure|array $key the key(s) to be sorted by. This refers to a key name of the sub-array
	 * elements, a property name of the objects, or an anonymous function returning the values for comparison
	 * purpose. The anonymous function signature should be: `function($item)`.
	 * To sort by multiple keys, provide an array of keys here.
	 * @param boolean|array $ascending whether to sort in ascending or descending order. When
	 * sorting by multiple keys with different ascending orders, use an array of ascending flags.
	 * @param integer|array $sortFlag the PHP sort flag. Valid values include
	 * `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING` and `SORT_LOCALE_STRING`.
	 * Please refer to [PHP manual](http://php.net/manual/en/function.sort.php)
	 * for more details. When sorting by multiple keys with different sort flags, use an array of sort flags.
	 * @param boolean|array $caseSensitive whether to sort string in case-sensitive manner. This parameter
	 * is used only when `$sortFlag` is `SORT_STRING`.
	 * When sorting by multiple keys with different case sensitivities, use an array of boolean values.
	 * @throws InvalidParamException if the $ascending or $sortFlag parameters do not have
	 * correct number of elements as that of $key.
	 */
	public static function multisort(&$array, $key, $ascending = true, $sortFlag = SORT_REGULAR, $caseSensitive = true)
	{
		$keys = is_array($key) ? $key : array($key);
		if (empty($keys) || empty($array)) {
			return;
		}
		$n = count($keys);
		if (is_scalar($ascending)) {
			$ascending = array_fill(0, $n, $ascending);
		} elseif (count($ascending) !== $n) {
			throw new InvalidParamException('The length of $ascending parameter must be the same as that of $keys.');
		}
		if (is_scalar($sortFlag)) {
			$sortFlag = array_fill(0, $n, $sortFlag);
		} elseif (count($sortFlag) !== $n) {
			throw new InvalidParamException('The length of $sortFlag parameter must be the same as that of $keys.');
		}
		if (is_scalar($caseSensitive)) {
			$caseSensitive = array_fill(0, $n, $caseSensitive);
		} elseif (count($caseSensitive) !== $n) {
			throw new InvalidParamException('The length of $caseSensitive parameter must be the same as that of $keys.');
		}
		$args = array();
		foreach ($keys as $i => $key) {
			$flag = $sortFlag[$i];
			$cs = $caseSensitive[$i];
			if (!$cs && ($flag === SORT_STRING)) {
				if (defined('SORT_FLAG_CASE')) {
					$flag = $flag | SORT_FLAG_CASE;
					$args[] = static::getColumn($array, $key);
				} else {
					$column = array();
					foreach (static::getColumn($array, $key) as $k => $value) {
						$column[$k] = mb_strtolower($value);
					}
					$args[] = $column;
				}
			} else {
				$args[] =  static::getColumn($array, $key);
			}
			$args[] = $ascending[$i] ? SORT_ASC : SORT_DESC;
			$args[] = $flag;
		}
		$args[] = &$array;
		call_user_func_array('array_multisort', $args);
	}

	/**
	 * Encodes special characters in an array of strings into HTML entities.
	 * Both the array keys and values will be encoded.
	 * If a value is an array, this method will also encode it recursively.
	 * @param array $data data to be encoded
	 * @param boolean $valuesOnly whether to encode array values only. If false,
	 * both the array keys and array values will be encoded.
	 * @param string $charset the charset that the data is using. If not set,
	 * [[\yii\base\Application::charset]] will be used.
	 * @return array the encoded data
	 * @see http://www.php.net/manual/en/function.htmlspecialchars.php
	 */
	public static function htmlEncode($data, $valuesOnly = true, $charset = null)
	{
		if ($charset === null) {
			$charset = Yii::$app->charset;
		}
		$d = array();
		foreach ($data as $key => $value) {
			if (!$valuesOnly && is_string($key)) {
				$key = htmlspecialchars($key, ENT_QUOTES, $charset);
			}
			if (is_string($value)) {
				$d[$key] = htmlspecialchars($value, ENT_QUOTES, $charset);
			} elseif (is_array($value)) {
				$d[$key] = static::htmlEncode($value, $charset);
			}
		}
		return $d;
	}

	/**
	 * Decodes HTML entities into the corresponding characters in an array of strings.
	 * Both the array keys and values will be decoded.
	 * If a value is an array, this method will also decode it recursively.
	 * @param array $data data to be decoded
	 * @param boolean $valuesOnly whether to decode array values only. If false,
	 * both the array keys and array values will be decoded.
	 * @return array the decoded data
	 * @see http://www.php.net/manual/en/function.htmlspecialchars-decode.php
	 */
	public static function htmlDecode($data, $valuesOnly = true)
	{
		$d = array();
		foreach ($data as $key => $value) {
			if (!$valuesOnly && is_string($key)) {
				$key = htmlspecialchars_decode($key, ENT_QUOTES);
			}
			if (is_string($value)) {
				$d[$key] = htmlspecialchars_decode($value, ENT_QUOTES);
			} elseif (is_array($value)) {
				$d[$key] = static::htmlDecode($value);
			}
		}
		return $d;
	}
}
