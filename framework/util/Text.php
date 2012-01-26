<?php
/**
 * Text helper class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\util;

/**
 * Text helper
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Alex Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class Text
{
	/**
	 * Converts a word to its plural form.
	 * @param string $name the word to be pluralized
	 * @return string the pluralized word
	 */
	public static function pluralize($name)
	{
		$rules = array(
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(m)an$/i' => '\1en',
			'/(child)$/i' => '\1ren',
			'/(r)y$/i' => '\1ies',
			'/s$/' => 's',
		);
		foreach ($rules as $rule => $replacement)
		{
			if (preg_match($rule, $name)) {
				return preg_replace($rule, $replacement, $name);
			}
		}
		return $name . 's';
	}

	/**
	 * Converts a class name into space-separated words.
	 * For example, 'PostTag' will be converted as 'Post Tag'.
	 * @param string $name the string to be converted
	 * @param boolean $ucwords whether to capitalize the first letter in each word
	 * @return string the resulting words
	 */
	public static function name2words($name, $ucwords = true)
	{
		$label = trim(strtolower(str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));
		return $ucwords ? ucwords($label) : $label;
	}

	/**
	 * Converts a class name into a HTML ID.
	 * For example, 'PostTag' will be converted as 'post-tag'.
	 * @param string $name the string to be converted
	 * @return string the resulting ID
	 */
	public static function name2id($name)
	{
		return trim(strtolower(str_replace('_','-',preg_replace('/(?<![A-Z])[A-Z]/', '-\0', $name))),'-');
	}
}
