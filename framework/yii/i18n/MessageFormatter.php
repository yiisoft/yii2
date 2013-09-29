<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

/**
 * MessageFormatter is an enhanced version of PHP intl class that no matter which PHP and ICU versions are used:
 *
 * - Accepts named arguments and mixed numeric and named arguments.
 * - Issues no error when an insufficient number of arguments have been provided. Instead, the placeholders will not be
 *   substituted.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
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
		if (self::needFix()) {
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
		if (self::needFix()) {
			$pattern = self::replaceNamedArguments($pattern, $args);
			$args = array_values($args);
		}
		return parent::formatMessage($locale, $pattern, $args);
	}

	/**
	 * Replace named placeholders with numeric placeholders.
	 *
	 * @param string $pattern The pattern string to replace things into.
	 * @param array $args The array of values to insert into the format string.
	 * @return string The pattern string with placeholders replaced.
	 */
	private static function replaceNamedArguments($pattern, $args)
	{
		$map = array_flip(array_keys($args));
		return preg_replace_callback('~({\s*)([\d\w]+)(\s*[,}])~u', function ($input) use ($map) {
			$name = $input[2];
			if (isset($map[$name])) {
				return $input[1] . $map[$name] . $input[3];
			}
			else {
				//return $input[1] . $name . $input[3];
				return "'" . $input[1] . $name . $input[3] . "'";
			}
		}, $pattern);
	}

	/**
	 * Checks if fix should be applied
	 *
	 * @see http://php.net/manual/en/migration55.changed-functions.php
	 * @return boolean if fix should be applied
	 */
	private static function needFix()
	{
		return (
			!defined('INTL_ICU_VERSION') ||
			version_compare(INTL_ICU_VERSION, '48.0.0', '<') ||
			version_compare(PHP_VERSION, '5.5.0', '<')
		);
	}
}
 