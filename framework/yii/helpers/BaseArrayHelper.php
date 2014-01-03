<?php
/**
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @link http://www.yiiframework.com/
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;
use yii\base\Arrayable;
use yii\base\InvalidParamException;

/**
 * BaseArrayHelper provides concrete implementation for [[ArrayHelper]].
 *
 * Do not use BaseArrayHelper. Use [[ArrayHelper]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseArrayHelper
{
	/**
	 * Converts an object or an array of objects into an array.
	 * @param object|array $object the object to be converted into an array
	 * @param array $properties a mapping from object class names to the properties that need to put into the resulting arrays.
	 * The properties specified for each class is an array of the following format:
	 *
	 * ~~~
	 * [
	 *     'app\models\Post' => [
	 *         'id',
	 *         'title',
	 *         // the key name in array result => property name
	 *         'createTime' => 'create_time',
	 *         // the key name in array result => anonymous function
	 *         'length' => function ($post) {
	 *             return strlen($post->content);
	 *         },
	 *     ],
	 * ]
	 * ~~~
	 *
	 * The result of `ArrayHelper::toArray($post, $properties)` could be like the following:
	 *
	 * ~~~
	 * [
	 *     'id' => 123,
	 *     'title' => 'test',
	 *     'createTime' => '2013-01-01 12:00AM',
	 *     'length' => 301,
	 * ]
	 * ~~~
	 *
	 * @param boolean $recursive whether to recursively converts properties which are objects into arrays.
	 * @return array the array representation of the object
	 */
	public static function toArray($object, $properties = [], $recursive = true)
	{
		if (!empty($properties) && is_object($object)) {
			$className = get_class($object);
			if (!empty($properties[$className])) {
				$result = [];
				foreach ($properties[$className] as $key => $name) {
					if (is_int($key)) {
						$result[$name] = $object->$name;
					} else {
						$result[$key] = static::getValue($object, $name);
					}
				}
				return $result;
			}
		}
		if ($object instanceof Arrayable) {
			$object = $object->toArray();
			if (!$recursive) {
				return $object;
			}
		}
		$result = [];
		foreach ($object as $key => $value) {
			if ($recursive && (is_array($value) || is_object($value))) {
				$result[$key] = static::toArray($value, true);
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

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
	 * If the key does not exist in the array or object, the default value will be returned instead.
	 *
	 * The key may be specified in a dot format to retrieve the value of a sub-array or the property
	 * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
	 * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
	 * or `$array->x` is neither an array nor an object, the default value will be returned.
	 * Note that if the array already has an element `x.y.z`, then its value will be returned
	 * instead of going through the sub-arrays.
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
	 * // using dot format to retrieve the property of embedded object
	 * $street = \yii\helpers\ArrayHelper::getValue($users, 'address.street');
	 * ~~~
	 *
	 * @param array|object $array array or object to extract value from
	 * @param string|\Closure $key key name of the array element, or property name of the object,
	 * or an anonymous function returning the value. The anonymous function signature should be:
	 * `function($array, $defaultValue)`.
	 * @param mixed $default the default value to be returned if the specified key does not exist
	 * @return mixed the value of the element if found, default value otherwise
	 * @throws InvalidParamException if $array is neither an array nor an object.
	 */
	public static function getValue($array, $key, $default = null)
	{
		if ($key instanceof \Closure) {
			return $key($array, $default);
		}

		if (is_array($array) && array_key_exists($key, $array)) {
			return $array[$key];
		}

		if (($pos = strrpos($key, '.')) !== false) {
			$array = static::getValue($array, substr($key, 0, $pos), $default);
			$key = substr($key, $pos + 1);
		}

		if (is_object($array)) {
			return $array->$key;
		} elseif (is_array($array)) {
			return array_key_exists($key, $array) ? $array[$key] : $default;
		} else {
			return $default;
		}
	}

	/**
	 * Removes an item from an array and returns the value. If the key does not exist in the array, the default value
	 * will be returned instead.
	 *
	 * Usage examples,
	 *
	 * ~~~
	 * // $array = ['type' => 'A', 'options' => [1, 2]];
	 * // working with array
	 * $type = \yii\helpers\ArrayHelper::remove($array, 'type');
	 * // $array content
	 * // $array = ['options' => [1, 2]];
	 * ~~~
	 *
	 * @param array $array the array to extract value from
	 * @param string $key key name of the array element
	 * @param mixed $default the default value to be returned if the specified key does not exist
	 * @return mixed|null the value of the element if found, default value otherwise
	 */
	public static function remove(&$array, $key, $default = null)
	{
		if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
			$value = $array[$key];
			unset($array[$key]);
			return $value;
		}
		return $default;
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
	 * $array = [
	 *     ['id' => '123', 'data' => 'abc'],
	 *     ['id' => '345', 'data' => 'def'],
	 * ];
	 * $result = ArrayHelper::index($array, 'id');
	 * // the result is:
	 * // [
	 * //     '123' => ['id' => '123', 'data' => 'abc'],
	 * //     '345' => ['id' => '345', 'data' => 'def'],
	 * // ]
	 *
	 * // using anonymous function
	 * $result = ArrayHelper::index($array, function ($element) {
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
		$result = [];
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
	 * $array = [
	 *     ['id' => '123', 'data' => 'abc'],
	 *     ['id' => '345', 'data' => 'def'],
	 * ];
	 * $result = ArrayHelper::getColumn($array, 'id');
	 * // the result is: ['123', '345']
	 *
	 * // using anonymous function
	 * $result = ArrayHelper::getColumn($array, function ($element) {
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
		$result = [];
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
	 * $array = [
	 *     ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
	 *     ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
	 *     ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
	 * );
	 *
	 * $result = ArrayHelper::map($array, 'id', 'name');
	 * // the result is:
	 * // [
	 * //     '123' => 'aaa',
	 * //     '124' => 'bbb',
	 * //     '345' => 'ccc',
	 * // ]
	 *
	 * $result = ArrayHelper::map($array, 'id', 'name', 'class');
	 * // the result is:
	 * // [
	 * //     'x' => [
	 * //         '123' => 'aaa',
	 * //         '124' => 'bbb',
	 * //     ],
	 * //     'y' => [
	 * //         '345' => 'ccc',
	 * //     ],
	 * // ]
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
		$result = [];
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
	 * Checks if the given array contains the specified key.
	 * This method enhances the `array_key_exists()` function by supporting case-insensitive
	 * key comparison.
	 * @param string $key the key to check
	 * @param array $array the array with keys to check
	 * @param boolean $caseSensitive whether the key comparison should be case-sensitive
	 * @return boolean whether the array contains the specified key
	 */
	public static function keyExists($key, $array, $caseSensitive = true)
	{
		if ($caseSensitive) {
			return array_key_exists($key, $array);
		} else {
			foreach (array_keys($array) as $k) {
				if (strcasecmp($key, $k) === 0) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Sorts an array of objects or arrays (with the same structure) by one or several keys.
	 * @param array $array the array to be sorted. The array will be modified after calling this method.
	 * @param string|\Closure|array $key the key(s) to be sorted by. This refers to a key name of the sub-array
	 * elements, a property name of the objects, or an anonymous function returning the values for comparison
	 * purpose. The anonymous function signature should be: `function($item)`.
	 * To sort by multiple keys, provide an array of keys here.
	 * @param integer|array $direction the sorting direction. It can be either `SORT_ASC` or `SORT_DESC`.
	 * When sorting by multiple keys with different sorting directions, use an array of sorting directions.
	 * @param integer|array $sortFlag the PHP sort flag. Valid values include
	 * `SORT_REGULAR`, `SORT_NUMERIC`, `SORT_STRING`, `SORT_LOCALE_STRING`, `SORT_NATURAL` and `SORT_FLAG_CASE`.
	 * Please refer to [PHP manual](http://php.net/manual/en/function.sort.php)
	 * for more details. When sorting by multiple keys with different sort flags, use an array of sort flags.
	 * @throws InvalidParamException if the $descending or $sortFlag parameters do not have
	 * correct number of elements as that of $key.
	 */
	public static function multisort(&$array, $key, $direction = SORT_ASC, $sortFlag = SORT_REGULAR)
	{
		$keys = is_array($key) ? $key : [$key];
		if (empty($keys) || empty($array)) {
			return;
		}
		$n = count($keys);
		if (is_scalar($direction)) {
			$direction = array_fill(0, $n, $direction);
		} elseif (count($direction) !== $n) {
			throw new InvalidParamException('The length of $descending parameter must be the same as that of $keys.');
		}
		if (is_scalar($sortFlag)) {
			$sortFlag = array_fill(0, $n, $sortFlag);
		} elseif (count($sortFlag) !== $n) {
			throw new InvalidParamException('The length of $sortFlag parameter must be the same as that of $keys.');
		}
		$args = [];
		foreach ($keys as $i => $key) {
			$flag = $sortFlag[$i];
			$args[] = static::getColumn($array, $key);
			$args[] = $direction[$i];
			$args[] = $flag;
		}
		$args[] = & $array;
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
		$d = [];
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
		$d = [];
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
