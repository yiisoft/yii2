<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\InvalidParamException;

/**
 * StringHelperBase provides concrete implementation for [[StringHelper]].
 *
 * Do not use StringHelperBase. Use [[StringHelper]] instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class StringHelperBase
{
	/**
	 * Returns the number of bytes in the given string.
	 * This method ensures the string is treated as a byte array by using `mb_strlen()`.
	 * @param string $string the string being measured for length
	 * @return integer the number of bytes in the given string.
	 */
	public static function strlen($string)
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
	public static function substr($string, $start, $length)
	{
		return mb_substr($string, $start, $length, '8bit');
	}

	/**
	 * Returns the trailing name component of a path.
	 * This method does the same as the php function `basename()` except that it will
	 * always use \ and / as directory separators, independent of the operating system.
	 * This method was mainly created to work on php namespaces. When working with real
	 * file paths, php's `basename()` should work fine for you.
	 * Note: this method is not aware of the actual filesystem, or path components such as "..".
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
	 * Compares two strings or string arrays, and return their differences.
	 * This is a wrapper of the Horde_Text_Diff package.
	 * @param string|array $lines1 the first string or string array to be compared. If it is a string,
	 * it will be converted into a string array by breaking at newlines.
	 * @param string|array $lines2 the second string or string array to be compared. If it is a string,
	 * it will be converted into a string array by breaking at newlines.
	 * @param string $format the output format. It must be 'context', 'inline', or 'unified'.
	 * @param string $engine the diff engine to be used. It must be 'auto', 'native', 'shell', 'string', or 'xdiff'.
	 * @return array the comparison result. The first element is a string representing the detailed comparison result.
	 * The second and the third elements represent the number of added lines and deleted lines, respectively.
	 * @throws InvalidParamException if the format or the engine is invalid.
	 */
	public static function diff($lines1, $lines2, $format = 'inline', $engine = 'auto')
	{
		if (!is_array($lines1)) {
			$lines1 = explode("\n", $lines1);
		}
		if (!is_array($lines2)) {
			$lines2 = explode("\n", $lines2);
		}
		switch ($format) {
			case 'context':
				$renderer = new \Horde_Text_Diff_Renderer_Context();
				break;
			case 'inline':
				$renderer = new \Horde_Text_Diff_Renderer_Inline();
				break;
			case 'unified':
				$renderer = new \Horde_Text_Diff_Renderer_Unified();
				break;
			default:
				throw new InvalidParamException("Output format must be 'context', 'inline' or 'unified'.");
		}
		if (!in_array($engine, array('auto', 'native', 'shell', 'string', 'xdiff'))) {
			throw new InvalidParamException("Engine must be 'auto', 'native', 'shell', 'string' or 'xdiff'.");
		}
		$diff = new \Horde_Text_Diff($engine, array($lines1, $lines2));
		return array(
			$renderer->render($diff),
			$diff->countAddedLines(),
			$diff->countDeletedLines(),
		);
	}
}
