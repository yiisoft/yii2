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

	/**
	 * Converts a word to its plural form.
	 * Note that this is for English only!
	 * For example, 'apple' will become 'apples', and 'child' will become 'children'.
	 * @param string $name the word to be pluralized
	 * @return string the pluralized word
	 */
	public static function pluralize($name)
	{
		static $rules = array(
			'/(m)ove$/i' => '\1oves',
			'/(f)oot$/i' => '\1eet',
			'/(c)hild$/i' => '\1hildren',
			'/(h)uman$/i' => '\1umans',
			'/(m)an$/i' => '\1en',
			'/(s)taff$/i' => '\1taff',
			'/(t)ooth$/i' => '\1eeth',
			'/(p)erson$/i' => '\1eople',
			'/([m|l])ouse$/i' => '\1ice',
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(shea|lea|loa|thie)f$/i' => '\1ves',
			'/([ti])um$/i' => '\1a',
			'/(tomat|potat|ech|her|vet)o$/i' => '\1oes',
			'/(bu)s$/i' => '\1ses',
			'/(ax|test)is$/i' => '\1es',
			'/s$/' => 's',
		);
		foreach ($rules as $rule => $replacement) {
			if (preg_match($rule, $name)) {
				return preg_replace($rule, $replacement, $name);
			}
		}
		return $name . 's';
	}

	/**
	 * Converts a CamelCase name into space-separated words.
	 * For example, 'PostTag' will be converted to 'Post Tag'.
	 * @param string $name the string to be converted
	 * @param boolean $ucwords whether to capitalize the first letter in each word
	 * @return string the resulting words
	 */
	public static function camel2words($name, $ucwords = true)
	{
		$label = trim(strtolower(str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));
		return $ucwords ? ucwords($label) : $label;
	}

	/**
	 * Converts a CamelCase name into an ID in lowercase.
	 * Words in the ID may be concatenated using the specified character (defaults to '-').
	 * For example, 'PostTag' will be converted to 'post-tag'.
	 * @param string $name the string to be converted
	 * @param string $separator the character used to concatenate the words in the ID
	 * @return string the resulting ID
	 */
	public static function camel2id($name, $separator = '-')
	{
		if ($separator === '_') {
			return trim(strtolower(preg_replace('/(?<![A-Z])[A-Z]/', '_\0', $name)), '_');
		} else {
			return trim(strtolower(str_replace('_', $separator, preg_replace('/(?<![A-Z])[A-Z]/', $separator . '\0', $name))), $separator);
		}
	}

	/**
	 * Converts an ID into a CamelCase name.
	 * Words in the ID separated by `$separator` (defaults to '-') will be concatenated into a CamelCase name.
	 * For example, 'post-tag' is converted to 'PostTag'.
	 * @param string $id the ID to be converted
	 * @param string $separator the character used to separate the words in the ID
	 * @return string the resulting CamelCase name
	 */
	public static function id2camel($id, $separator = '-')
	{
		return str_replace(' ', '', ucwords(implode(' ', explode($separator, $id))));
	}
}
