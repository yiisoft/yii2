<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use yii\base\NotSupportedException;

/**
 * FallbackMessageFormatter is a fallback implementation for the PHP intl MessageFormatter that is
 * used in case PHP intl extension is not installed.
 *
 * Do not use this class directly. Use [[MessageFormatter]] instead, which will automatically detect
 * installed version of PHP intl and use the fallback if it is not installed.
 *
 * It is highly recommended that you install [PHP intl extension](http://php.net/manual/en/book.intl.php) if you want to use
 * MessageFormatter features.
 *
 * This implementation only supports to following message formats:
 * - plural formatting for english
 * - select format
 * - simple parameters
 *
 * The pattern also does NOT support the ['apostrophe-friendly' syntax](http://www.php.net/manual/en/messageformatter.formatmessage.php).
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class FallbackMessageFormatter
{
	private $_locale;
	private $_pattern;
	private $_errorMessage = '';
	private $_errorCode = 0;

	/**
	 * Constructs a new Message Formatter
	 * @link http://php.net/manual/en/messageformatter.create.php
	 * @param string $locale The locale to use when formatting arguments
	 * @param string $pattern The pattern string to stick arguments into.
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
	 * @return string The formatted string, or `FALSE` if an error occurred
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
	 * @param array $args The array of values to insert into the format string
	 * @return string The formatted pattern string or `FALSE` if an error occurred
	 */
	public static function formatMessage($locale, $pattern, array $args)
	{
		if (($tokens = static::tokenizePattern($pattern)) === false) {
			return false;
		}
		foreach($tokens as $i => $token) {
			if (is_array($token)) {
				if (($tokens[$i] = static::parseToken($token, $args, $locale)) === false) {
					return false;
				}
			}
		}
		return implode('', $tokens);
	}

	/**
	 * Tokenizes a pattern by separating normal text from replaceable patterns
	 * @param string $pattern patter to tokenize
	 * @return array|bool array of tokens or false on failure
	 */
	private static function tokenizePattern($pattern)
	{
		$depth = 1;
		if (($start = $pos = mb_strpos($pattern, '{')) === false) {
			return [$pattern];
		}
		$tokens = [mb_substr($pattern, 0, $pos)];
		while(true) {
			$open = mb_strpos($pattern, '{', $pos + 1);
			$close = mb_strpos($pattern, '}', $pos + 1);
			if ($open === false && $close === false) {
				break;
			}
			if ($open === false) {
				$open = mb_strlen($pattern);
			}
			if ($close > $open) {
				$depth++;
				$pos = $open;
			} else {
				$depth--;
				$pos = $close;
			}
			if ($depth == 0) {
				$tokens[] = explode(',', mb_substr($pattern, $start + 1, $pos - $start - 1), 3);
				$start = $pos + 1;
				$tokens[] = mb_substr($pattern, $start, $open - $start);
				$start = $open;
			}
		}
		if ($depth != 0) {
			return false;
		}
		return $tokens;
	}

	/**
	 * Parses a token
	 * @param array $token the token to parse
	 * @param array $args arguments to replace
	 * @param string $locale the locale
	 * @return bool|string parsed token or false on failure
	 * @throws \yii\base\NotSupportedException when unsupported formatting is used.
	 */
	private static function parseToken($token, $args, $locale)
	{
		$param = trim($token[0]);
		if (isset($args[$param])) {
			$arg = $args[$param];
		} else {
			return '{' . implode(',', $token) . '}';
		}
		$type = isset($token[1]) ? trim($token[1]) : 'none';
		switch($type)
		{
			case 'number':
			case 'date':
			case 'time':
			case 'spellout':
			case 'ordinal':
			case 'duration':
			case 'choice':
			case 'selectordinal':
				throw new NotSupportedException("Message format '$type' is not supported. You have to install PHP intl extension to use this feature.");
			case 'none': return $arg;
			case 'select':
				/* http://icu-project.org/apiref/icu4c/classicu_1_1SelectFormat.html
				selectStyle = (selector '{' message '}')+
				*/
				$select = static::tokenizePattern($token[2]);
				$c = count($select);
				$message = false;
				for($i = 0; $i + 1 < $c; $i++) {
					if (is_array($select[$i]) || !is_array($select[$i + 1])) {
						return false;
					}
					$selector = trim($select[$i++]);
					if ($message === false && $selector == 'other' || $selector == $arg) {
						$message = implode(',', $select[$i]);
					}
				}
				if ($message !== false) {
					return static::formatMessage($locale, $message, $args);
				}
			break;
			case 'plural':
				/* http://icu-project.org/apiref/icu4c/classicu_1_1PluralFormat.html
				pluralStyle = [offsetValue] (selector '{' message '}')+
				offsetValue = "offset:" number
				selector = explicitValue | keyword
				explicitValue = '=' number  // adjacent, no white space in between
				keyword = [^[[:Pattern_Syntax:][:Pattern_White_Space:]]]+
				message: see MessageFormat
				*/
				$plural = static::tokenizePattern($token[2]);
				$c = count($plural);
				$message = false;
				$offset = 0;
				for($i = 0; $i + 1 < $c; $i++) {
					if (is_array($plural[$i]) || !is_array($plural[$i + 1])) {
						return false;
					}
					$selector = trim($plural[$i++]);
					if ($i == 1 && substr($selector, 0, 7) == 'offset:') {
						$offset = (int) trim(mb_substr($selector, 7, ($pos = mb_strpos(str_replace(["\n", "\r", "\t"], ' ', $selector), ' ', 7)) - 7));
						$selector = trim(mb_substr($selector, $pos + 1));
					}
					if ($message === false && $selector == 'other' ||
						$selector[0] == '=' && (int) mb_substr($selector, 1) == $arg ||
						$selector == 'zero' && $arg - $offset == 0 ||
						$selector == 'one' && $arg - $offset == 1 ||
						$selector == 'two' && $arg - $offset == 2
					) {
						$message = implode(',', str_replace('#', $arg - $offset, $plural[$i]));
					}
				}
				if ($message !== false) {
					return static::formatMessage($locale, $message, $args);
				}
				break;
		}
		return false;
	}

	/**
	 * Parse input string according to pattern
	 * @link http://php.net/manual/en/messageformatter.parse.php
	 * @param string $value The string to parse
	 * @return array An array containing the items extracted, or `FALSE` on error
	 */
	public function parse($value)
	{
		throw new NotSupportedException('You have to install PHP intl extension to use this feature.');
	}

	/**
	 * Quick parse input string
	 * @link http://php.net/manual/en/messageformatter.parsemessage.php
	 * @param string $locale The locale to use for parsing locale-dependent parts
	 * @param string $pattern The pattern with which to parse the `value`.
	 * @param string $source The string to parse, conforming to the `pattern`.
	 * @return array An array containing items extracted, or `FALSE` on error
	 */
	public static function parseMessage($locale, $pattern, $source)
	{
		throw new NotSupportedException('You have to install PHP intl extension to use this feature.');
	}

	/**
	 * Set the pattern used by the formatter
	 * @link http://php.net/manual/en/messageformatter.setpattern.php
	 * @param string $pattern The pattern string to use in this message formatter.
	 * @return bool `TRUE` on success or `FALSE` on failure.
	 */
	public function setPattern($pattern)
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
		return $this->_errorCode;
	}

	/**
	 * Get the error text from the last operation
	 * @link http://php.net/manual/en/messageformatter.geterrormessage.php
	 * @return string Description of the last error.
	 */
	public function getErrorMessage()
	{
		return $this->_errorMessage;
	}
}

if (!class_exists('MessageFormatter', false)) {
	class_alias('yii\\i18n\\FallbackMessageFormatter', 'MessageFormatter');
	define('YII_INTL_MESSAGE_FALLBACK', true);
}
