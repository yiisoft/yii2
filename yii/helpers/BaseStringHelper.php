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
			return $path;
		}
	}

	/**
	 * Compares two strings or string arrays, and return their differences.
	 * This is a wrapper of the [phpspec/php-diff](https://packagist.org/packages/phpspec/php-diff) package.
	 * @param string|array $lines1 the first string or string array to be compared. If it is a string,
	 * it will be converted into a string array by breaking at newlines.
	 * @param string|array $lines2 the second string or string array to be compared. If it is a string,
	 * it will be converted into a string array by breaking at newlines.
	 * @param string $format the output format. It must be 'inline', 'unified', 'context', 'side-by-side', or 'array'.
	 * @return string|array the comparison result. An array is returned if `$format` is 'array'. For all other
	 * formats, a string is returned.
	 * @throws InvalidParamException if the format is invalid.
	 */
	public static function diff($lines1, $lines2, $format = 'inline')
	{
		if (!is_array($lines1)) {
			$lines1 = explode("\n", $lines1);
		}
		if (!is_array($lines2)) {
			$lines2 = explode("\n", $lines2);
		}
		foreach ($lines1 as $i => $line) {
			$lines1[$i] = rtrim($line, "\r\n");
		}
		foreach ($lines2 as $i => $line) {
			$lines2[$i] = rtrim($line, "\r\n");
		}
		switch ($format) {
			case 'inline':
				$renderer = new \Diff_Renderer_Html_Inline();
				break;
			case 'array':
				$renderer = new \Diff_Renderer_Html_Array();
				break;
			case 'side-by-side':
				$renderer = new \Diff_Renderer_Html_SideBySide();
				break;
			case 'context':
				$renderer = new \Diff_Renderer_Text_Context();
				break;
			case 'unified':
				$renderer = new \Diff_Renderer_Text_Unified();
				break;
			default:
				throw new InvalidParamException("Output format must be 'inline', 'side-by-side', 'array', 'context' or 'unified'.");
		}
		$diff = new \Diff($lines1, $lines2);
		return $diff->render($renderer);
	}
}
