<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers\base;

/**
 * StringHelper
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class StringHelper
{
	/**
	 * Returns the number of bytes in the given string.
	 * This method ensures the string is treated as a byte array.
	 * It will use `mb_strlen()` if it is available.
	 * @param string $string the string being measured for length
	 * @return integer the number of bytes in the given string.
	 */
	public static function strlen($string)
	{
		return function_exists('mb_strlen') ? mb_strlen($string, '8bit') : strlen($string);
	}

	/**
	 * Returns the portion of string specified by the start and length parameters.
	 * This method ensures the string is treated as a byte array.
	 * It will use `mb_substr()` if it is available.
	 * @param string $string the input string. Must be one character or longer.
	 * @param integer $start the starting position
	 * @param integer $length the desired portion length
	 * @return string the extracted part of string, or FALSE on failure or an empty string.
	 * @see http://www.php.net/manual/en/function.substr.php
	 */
	public static function substr($string, $start, $length)
	{
		return function_exists('mb_substr') ? mb_substr($string, $start, $length, '8bit') : substr($string, $start, $length);
	}

	/**
	 * Returns the trailing name component of a path.
	 * This method does the same as the php function basename() except that it will
	 * always use \ and / as directory separators, independent of the operating system.
	 * Note: basename() operates naively on the input string, and is not aware of the
	 * actual filesystem, or path components such as "..".
	 * @param string $path A path string.
	 * @param string $suffix If the name component ends in suffix this will also be cut off.
	 * @return string the trailing name component of the given path.
	 * @see http://www.php.net/manual/en/function.basename.php
	 */
	public static function basename($path, $suffix = '')
	{
		if (($len = mb_strlen($suffix)) > 0 && mb_substr($path, -$len) == $suffix) {
			$path = mb_substr($path, 0, -$len);
		}
		$path = rtrim(str_replace('\\', '/', $path), '/\\');
		if (($pos = mb_strrpos($path, '/')) !== false) {
			return mb_substr($path, $pos + 1);
		}
		return $path;
	}
}
