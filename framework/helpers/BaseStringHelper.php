<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\InvalidParamException;

/**
 * BaseStringHelper provides concrete implementation for [[StringHelper]].
 *
 * Do not use BaseStringHelper. Use [[StringHelper]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseStringHelper
{
	/**
	 * Returns the number of bytes in the given string.
	 * This method ensures the string is treated as a byte array by using `mb_strlen()`.
	 * @param string $string the string being measured for length
	 * @return integer the number of bytes in the given string.
	 */
	public static function byteLength($string)
	{
		return mb_strlen($string, '8bit');
	}

	/**
	 * Returns the portion of string specified by the start and length parameters.
	 * This method ensures the string is treated as a byte array by using `mb_substr()`.
	 * @param string $string the input string. Must be one character or longer.
	 * @param integer $start the starting position
	 * @param integer $length the desired portion length
	 * @return string the extracted part of string, or FALSE on failure or an empty string.
	 * @see http://www.php.net/manual/en/function.substr.php
	 */
	public static function byteSubstr($string, $start, $length)
	{
		return mb_substr($string, $start, $length, '8bit');
	}

	/**
	 * Returns the trailing name component of a path.
	 * This method is similar to the php function `basename()` except that it will
	 * treat both \ and / as directory separators, independent of the operating system.
	 * This method was mainly created to work on php namespaces. When working with real
	 * file paths, php's `basename()` should work fine for you.
	 * Note: this method is not aware of the actual filesystem, or path components such as "..".
	 *
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

	/**
	 * Returns parent directory's path.
	 * This method is similar to `dirname()` except that it will treat
	 * both \ and / as directory separators, independent of the operating system.
	 *
	 * @param string $path A path string.
	 * @return string the parent directory's path.
	 * @see http://www.php.net/manual/en/function.basename.php
	 */
	public static function dirname($path)
	{
		$pos = mb_strrpos(str_replace('\\', '/', $path), '/');
		if ($pos !== false) {
			return mb_substr($path, 0, $pos);
		} else {
			return '';
		}
	}
}
