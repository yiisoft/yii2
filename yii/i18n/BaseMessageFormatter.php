<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\NotSupportedException;

/**
 * BaseMessageFormatter is a fallback implementation for the PHP intl MessageFormatter that is used in case intl extension is not installed.
 *
 * This implementation only supports message plural formatting for english and simple parameters.
 * All other formats are ignored.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class BaseMessageFormatter
{
	private $_locale;
	private $_pattern;

	/**
	 * Constructs a new Message Formatter
	 * @link http://php.net/manual/en/messageformatter.create.php
	 * @param string $locale The locale to use when formatting arguments
	 * @param string $pattern The pattern string to stick arguments into.
	 * The pattern uses an 'apostrophe-friendly' syntax; it is run through
	 * umsg_autoQuoteApostrophe before being interpreted.
	 */
	public function __construct($locale, $pattern)
	{
		$this->_locale = $locale;
		$this->_pattern = $pattern;
	}

	/**
	 * Constructs a new Message Formatter
	 * @link http://php.net/manual/en/messageformatter.create.php
	 * @param string $locale The locale to use when formatting arguments
	 * @param string $pattern The pattern string to stick arguments into.
	 * The pattern uses an 'apostrophe-friendly' syntax; it is run through
	 * umsg_autoQuoteApostrophe before being interpreted.
	 * @return MessageFormatter The formatter object
	 */
	public static function create($locale, $pattern)
	{
		return new static($locale, $pattern);
	}

	/**
	 * Format the message
	 * @link http://php.net/manual/en/messageformatter.format.php
	 * @param array $args Arguments to insert into the format string
	 * @return string The formatted string, or <b>FALSE</b> if an error occurred
	 */
	public function format(array $args)
	{
		return static::formatMessage($this->_locale, $this->_pattern, $args);
	}

	/**
	 * Quick format message
	 * @link http://php.net/manual/en/messageformatter.formatmessage.php
	 * @param string $locale The locale to use for formatting locale-dependent parts
	 * @param string $pattern The pattern string to insert things into.
	 * The pattern uses an 'apostrophe-friendly' syntax; it is run through
	 * umsg_autoQuoteApostrophe before being interpreted.
	 * @param array $args The array of values to insert into the format string
	 * @return string The formatted pattern string or <b>FALSE</b> if an error occurred
	 */
	public static function formatMessage($locale, $pattern, array $args)
	{
		// TODO implement plural format

		$a = [];
		foreach($args as $name => $value) {
			$a['{' . $name . '}'] = $value;
		}
		return strtr($pattern, $a);
	}

	/**
	 * Parse input string according to pattern
	 * @link http://php.net/manual/en/messageformatter.parse.php
	 * @param string $value The string to parse
	 * @return array An array containing the items extracted, or <b>FALSE</b> on error
	 */
	public function parse ($value)
	{
		throw new NotSupportedException('You have to install PHP intl extension to use this feature.');
	}

	/**
	 * Quick parse input string
	 * @link http://php.net/manual/en/messageformatter.parsemessage.php
	 * @param string $locale The locale to use for parsing locale-dependent parts
	 * @param string $pattern The pattern with which to parse the <i>value</i>.
	 * @param string $source The string to parse, conforming to the <i>pattern</i>.
	 * @return array An array containing items extracted, or <b>FALSE</b> on error
	 */
	public static function parseMessage ($locale, $pattern, $source)
	{
		throw new NotSupportedException('You have to install PHP intl extension to use this feature.');
	}

	/**
	 * Set the pattern used by the formatter
	 * @link http://php.net/manual/en/messageformatter.setpattern.php
	 * @param string $pattern The pattern string to use in this message formatter.
	 * The pattern uses an 'apostrophe-friendly' syntax; it is run through
	 * umsg_autoQuoteApostrophe before being interpreted.
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function setPattern ($pattern)
	{
		$this->_pattern = $pattern;
		return true;
	}

	/**
	 * Get the pattern used by the formatter
	 * @link http://php.net/manual/en/messageformatter.getpattern.php
	 * @return string The pattern string for this message formatter
	 */
	public function getPattern()
	{
		return $this->_pattern;
	}

	/**
	 * Get the locale for which the formatter was created.
	 * @link http://php.net/manual/en/messageformatter.getlocale.php
	 * @return string The locale name
	 */
	public function getLocale()
	{
		return $this->_locale;
	}

	/**
	 * Get the error code from last operation
	 * @link http://php.net/manual/en/messageformatter.geterrorcode.php
	 * @return int The error code, one of UErrorCode values. Initial value is U_ZERO_ERROR.
	 */
	public function getErrorCode()
	{
		return 0;
	}

	/**
	 * Get the error text from the last operation
	 * @link http://php.net/manual/en/messageformatter.geterrormessage.php
	 * @return string Description of the last error.
	 */
	public function getErrorMessage()
	{
		return '';
	}
}

if (!class_exists('MessageFormatter', false)) {
	class_alias('yii\\i18n\\BaseMessageFormatter', 'MessageFormatter');
}

