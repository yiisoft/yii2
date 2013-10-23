<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

if (!class_exists('MessageFormatter', false)) {
	require_once(__DIR__ . '/FallbackMessageFormatter.php');
}
defined('YII_INTL_MESSAGE_FALLBACK') || define('YII_INTL_MESSAGE_FALLBACK', false);

/**
 * MessageFormatter is an enhanced version of PHP intl class that no matter which PHP and ICU versions are used:
 *
 * - Accepts named arguments and mixed numeric and named arguments.
 * - Issues no error when an insufficient number of arguments have been provided. Instead, the placeholders will not be
 *   substituted.
 * - Fixes PHP 5.5 weird placeholder replacement in case no arguments are provided at all (https://bugs.php.net/bug.php?id=65920).
 * - Offers limited support for message formatting in case PHP intl extension is not installed.
 *   However it is highly recommended that you install [PHP intl extension](http://php.net/manual/en/book.intl.php) if you want
 *   to use MessageFormatter features.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class MessageFormatter extends \MessageFormatter
{
	/**
	 * Format the message.
	 *
	 * @link http://php.net/manual/en/messageformatter.format.php
	 * @param array $args Arguments to insert into the format string.
	 * @return string|boolean The formatted string, or false if an error occurred.
	 */
	public function format($args)
	{
		if ($args === []) {
			return $this->getPattern();
		}

		if (version_compare(PHP_VERSION, '5.5.0', '<') && !YII_INTL_MESSAGE_FALLBACK) {
			$pattern = self::replaceNamedArguments($this->getPattern(), $args);
			$this->setPattern($pattern);
			$args = array_values($args);
		}
		return parent::format($args);
	}

	/**
	 * Quick format message.
	 *
	 * @link http://php.net/manual/en/messageformatter.formatmessage.php
	 * @param string $locale The locale to use for formatting locale-dependent parts.
	 * @param string $pattern The pattern string to insert things into.
	 * @param array $args The array of values to insert into the format string.
	 * @return string|boolean The formatted pattern string or false if an error occurred.
	 */
	public static function formatMessage($locale, $pattern, $args)
	{
		if ($args === []) {
			return $pattern;
		}

		if (version_compare(PHP_VERSION, '5.5.0', '<') && !YII_INTL_MESSAGE_FALLBACK) {
			$pattern = self::replaceNamedArguments($pattern, $args);
			$args = array_values($args);
		}
		return parent::formatMessage($locale, $pattern, $args);
	}

	/**
	 * Replace named placeholders with numeric placeholders and quote unused.
	 *
	 * @param string $pattern The pattern string to replace things into.
	 * @param array $args The array of values to insert into the format string.
	 * @return string The pattern string with placeholders replaced.
	 */
	private static function replaceNamedArguments($pattern, $args)
	{
		$map = array_flip(array_keys($args));

		// parsing pattern based on ICU grammar:
		// http://icu-project.org/apiref/icu4c/classMessageFormat.html#details
		$parts = explode('{', $pattern);
		$c = count($parts);
		$pattern = $parts[0];
		$d = 0;
		$stack = [];
		for($i = 1; $i < $c; $i++) {
			if (preg_match('~^(\s*)([\d\w]+)(\s*)([},])(\s*)(.*)$~us', $parts[$i], $matches)) {
				// if we are not inside a plural or select this is a message
				if (!isset($stack[$d]) || $stack[$d] != 'plural' && $stack[$d] != 'select') {
					$d++;
					// replace normal arg if it is available
					if (isset($map[$matches[2]])) {
						$q = '';
						$pattern .= '{' . $matches[1] . $map[$matches[2]] . $matches[3];
					} else {
						// quote unused args
						$q = ($matches[4] == '}') ? "'" : "";
						$pattern .= "$q{" . $matches[1] . $matches[2] . $matches[3];
					}
					$pattern .= ($term = $matches[4] . $q . $matches[5] . $matches[6]);
					// store type of current level
					$stack[$d] = ($matches[4] == ',') ? substr($matches[6], 0, 6) : 'none';
					// if it's plural or select, the next bracket is NOT begin of a message then!
					if ($stack[$d] == 'plural' || $stack[$d] == 'select') {
						$i++;
						$d -= substr_count($term, '}');
					} else {
						$d -= substr_count($term, '}');
						continue;
					}
				}
			}
			$pattern .= '{' . $parts[$i];
			$d += 1 - substr_count($parts[$i], '}');
		}
		return $pattern;
	}
}
