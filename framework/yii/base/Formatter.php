<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use DateTime;
use yii\helpers\HtmlPurifier;
use yii\helpers\Html;


/**
 * Formatter provides a set of commonly used data formatting methods.
 *
 * The formatting methods provided by Formatter are all named in the form of `asXyz()`.
 * The behavior of some of them may be configured via the properties of Formatter. For example,
 * by configuring [[dateFormat]], one may control how [[asDate()]] formats the value into a date string.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Formatter extends Component
{
	/**
	 * @var string the default format string to be used to format a date using PHP date() function.
	 */
	public $dateFormat = 'Y/m/d';
	/**
	 * @var string the default format string to be used to format a time using PHP date() function.
	 */
	public $timeFormat = 'h:i:s A';
	/**
	 * @var string the default format string to be used to format a date and time using PHP date() function.
	 */
	public $datetimeFormat = 'Y/m/d h:i:s A';
	/**
	 * @var array the text to be displayed when formatting a boolean value. The first element corresponds
	 * to the text display for false, the second element for true. Defaults to <code>array('No', 'Yes')</code>.
	 */
	public $booleanFormat;


	/**
	 * Initializes the component.
	 */
	public function init()
	{
		if (empty($this->booleanFormat)) {
			$this->booleanFormat = array(Yii::t('yii', 'No'), Yii::t('yii', 'Yes'));
		}
	}

	/**
	 * Formats the value as is without any formatting.
	 * This method simply returns back the parameter without any format.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asRaw($value)
	{
		return $value;
	}

	/**
	 * Formats the value as an HTML-encoded plain text.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asText($value)
	{
		return Html::encode($value);
	}

	/**
	 * Formats the value as an HTML-encoded plain text with newlines converted into breaks.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asNtext($value)
	{
		return nl2br(Html::encode($value));
	}

	/**
	 * Formats the value as HTML-encoded text paragraphs.
	 * Each text paragraph is enclosed within a `<p>` tag.
	 * One or multiple consecutive empty lines divide two paragraphs.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asParagraphs($value)
	{
		return str_replace('<p></p>', '',
			'<p>' . preg_replace('/[\r\n]{2,}/', "</p>\n<p>", Html::encode($value)) . '</p>'
		);
	}

	/**
	 * Formats the value as HTML text.
	 * The value will be purified using [[HtmlPurifier]] to avoid XSS attacks.
	 * Use [[asRaw()]] if you do not want any purification of the value.
	 * @param mixed $value the value to be formatted
	 * @param array|null $config the configuration for the HTMLPurifier class.
	 * @return string the formatted result
	 */
	public function asHtml($value, $config = null)
	{
		return HtmlPurifier::process($value, $config);
	}

	/**
	 * Formats the value as a mailto link.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asEmail($value)
	{
		return Html::mailto($value);
	}

	/**
	 * Formats the value as an image tag.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asImage($value)
	{
		return Html::img($value);
	}

	/**
	 * Formats the value as a hyperlink.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asUrl($value)
	{
		$url = $value;
		if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
			$url = 'http://' . $url;
		}
		return Html::a(Html::encode($value), $url);
	}

	/**
	 * Formats the value as a boolean.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 * @see booleanFormat
	 */
	public function asBoolean($value)
	{
		return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
	}

	/**
	 * Formats the value as a date.
	 * @param integer|string|DateTime $value the value to be formatted. The following
	 * types of value are supported:
	 *
	 * - an integer representing UNIX timestamp
	 * - a string that can be parsed into a UNIX timestamp via `strtotime()`
	 * - a PHP DateTime object
	 *
	 * @param string $format the format used to convert the value into a date string.
	 * If null, [[dateFormat]] will be used. The format string should be the one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see dateFormat
	 */
	public function asDate($value, $format = null)
	{
		$value = $this->normalizeDatetimeValue($value);
		return date($format === null ? $this->dateFormat : $format, $value);
	}

	/**
	 * Formats the value as a time.
	 * @param integer|string|DateTime $value the value to be formatted. The following
	 * types of value are supported:
	 *
	 * - an integer representing UNIX timestamp
	 * - a string that can be parsed into a UNIX timestamp via `strtotime()`
	 * - a PHP DateTime object
	 *
	 * @param string $format the format used to convert the value into a date string.
	 * If null, [[timeFormat]] will be used. The format string should be the one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function asTime($value, $format = null)
	{
		$value = $this->normalizeDatetimeValue($value);
		return date($format === null ? $this->timeFormat : $format, $value);
	}

	/**
	 * Formats the value as a datetime.
	 * @param integer|string|DateTime $value the value to be formatted. The following
	 * types of value are supported:
	 *
	 * - an integer representing UNIX timestamp
	 * - a string that can be parsed into a UNIX timestamp via `strtotime()`
	 * - a PHP DateTime object
	 *
	 * @param string $format the format used to convert the value into a date string.
	 * If null, [[datetimeFormat]] will be used. The format string should be the one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function asDatetime($value, $format = null)
	{
		$value = $this->normalizeDatetimeValue($value);
		return date($format === null ? $this->datetimeFormat : $format, $value);
	}

	/**
	 * Normalizes the given datetime value as one that can be taken by various date/time formatting methods.
	 * @param mixed $value the datetime value to be normalized.
	 * @return mixed the normalized datetime value
	 */
	protected function normalizeDatetimeValue($value)
	{
		if (is_string($value)) {
			if (ctype_digit($value) || $value[0] === '-' && ctype_digit(substr($value, 1))) {
				return (int)$value;
			} else {
				return strtotime($value);
			}
		} elseif ($value instanceof DateTime) {
			return $value->getTimestamp();
		} else {
			return (int)$value;
		}
	}

	/**
	 * Formats the value as an integer.
	 * @param mixed $value the value to be formatted
	 * @return string the formatting result.
	 */
	public function asInteger($value)
	{
		if (is_string($value) && preg_match('/^(-?\d+)/', $value, $matches)) {
			return $matches[1];
		} else {
			$value = (int)$value;
			return "$value";
		}
	}

	/**
	 * Formats the value as a double number.
	 * @param mixed $value the value to be formatted
	 * @param integer $decimals the number of digits after the decimal point
	 * @return string the formatting result.
	 */
	public function asDouble($value, $decimals = 2)
	{
		return sprintf("%.{$decimals}f", $value);
	}

	/**
	 * Formats the value as a number with decimal and thousand separators.
	 * This method calls the PHP number_format() function to do the formatting.
	 * @param mixed $value the value to be formatted
	 * @param integer $decimals the number of digits after the decimal point
	 * @param string $decimalSeparator the character displayed as the decimal point
	 * @param string $thousandSeparator the character displayed as the thousands separator character.
	 * @return string the formatted result
	 */
	public function asNumber($value, $decimals = 0 , $decimalSeparator = '.' , $thousandSeparator = ',' )
	{
		return number_format($value, $decimals, $decimalSeparator, $thousandSeparator);
	}
}
