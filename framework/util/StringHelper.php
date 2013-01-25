<?php
/**
 * StringHelper class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

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
	 * Converts a word to its plural form.
	 * Note that this is for English only!
	 * For example, 'apple' will become 'apples', and 'child' will become 'children'.
	 * @param string $name the word to be pluralized
	 * @return string the pluralized word
	 */
	public static function pluralize($name)
	{
		$rules = array(
			'/move$/i' => 'moves',
			'/foot$/i' => 'feet',
			'/child$/i' => 'children',
			'/human$/i' => 'humans',
			'/man$/i' => 'men',
			'/tooth$/i' => 'teeth',
			'/person$/i' => 'people',
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
