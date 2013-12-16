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
 * Formatter is configured as an application component in [[yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->formatter`.
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
	 * @var string the text to be displayed when formatting a null. Defaults to '<span class="not-set">(not set)</span>'.
	 */
	public $nullDisplay;
	/**
	 * @var array the text to be displayed when formatting a boolean value. The first element corresponds
	 * to the text display for false, the second element for true. Defaults to `['No', 'Yes']`.
	 */
	public $booleanFormat;
	/**
	 * @var string the character displayed as the decimal point when formatting a number.
	 * If not set, "." will be used.
	 */
	public $decimalSeparator;
	/**
	 * @var string the character displayed as the thousands separator character when formatting a number.
	 * If not set, "," will be used.
	 */
	public $thousandSeparator;


	/**
	 * Initializes the component.
	 */
	public function init()
	{
		if (empty($this->booleanFormat)) {
			$this->booleanFormat = [Yii::t('yii', 'No'), Yii::t('yii', 'Yes')];
		}
		if ($this->nullDisplay === null) {
			$this->nullDisplay = '<span class="not-set">' . Yii::t('yii', '(not set)') . '</span>';
		}
	}

	/**
	 * Formats the value based on the given format type.
	 * This method will call one of the "as" methods available in this class to do the formatting.
	 * For type "xyz", the method "asXyz" will be used. For example, if the format is "html",
	 * then [[asHtml()]] will be used. Format names are case insensitive.
	 * @param mixed $value the value to be formatted
	 * @param string|array $format the format of the value, e.g., "html", "text". To specify additional
	 * parameters of the formatting method, you may use an array. The first element of the array
	 * specifies the format name, while the rest of the elements will be used as the parameters to the formatting
	 * method. For example, a format of `['date', 'Y-m-d']` will cause the invocation of `asDate($value, 'Y-m-d')`.
	 * @return string the formatting result
	 * @throws InvalidParamException if the type is not supported by this class.
	 */
	public function format($value, $format)
	{
		if (is_array($format)) {
			if (!isset($format[0])) {
				throw new InvalidParamException('The $format array must contain at least one element.');
			}
			$f = $format[0];
			$format[0] = $value;
			$params = $format;
			$format = $f;
		} else {
			$params = [$value];
		}
		$method = 'as' . $format;
		if (method_exists($this, $method)) {
			return call_user_func_array([$this, $method], $params);
		} else {
			throw new InvalidParamException("Unknown type: $format");
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
		if ($value === null) {
			return $this->nullDisplay;
		}
		return $value;
	}

	/**
	 * Formats the value as an HTML-encoded plain text.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asText($value)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		return Html::encode($value);
	}

	/**
	 * Formats the value as an HTML-encoded plain text with newlines converted into breaks.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asNtext($value)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
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
		if ($value === null) {
			return $this->nullDisplay;
		}
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
		if ($value === null) {
			return $this->nullDisplay;
		}
		return HtmlPurifier::process($value, $config);
	}

	/**
	 * Formats the value as a mailto link.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asEmail($value)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		return Html::mailto(Html::encode($value), $value);
	}

	/**
	 * Formats the value as an image tag.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asImage($value)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		return Html::img($value);
	}

	/**
	 * Formats the value as a hyperlink.
	 * @param mixed $value the value to be formatted
	 * @return string the formatted result
	 */
	public function asUrl($value)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
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
		if ($value === null) {
			return $this->nullDisplay;
		}
		return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
	}

	/**
	 * Formats the value as a date.
	 * @param integer|string|DateTime $value the value to be formatted. The following
	 * types of value are supported:
	 *
	 * - an integer representing a UNIX timestamp
	 * - a string that can be parsed into a UNIX timestamp via `strtotime()`
	 * - a PHP DateTime object
	 *
	 * @param string $format the format used to convert the value into a date string.
	 * If null, [[dateFormat]] will be used. The format string should be one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see dateFormat
	 */
	public function asDate($value, $format = null)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		$value = $this->normalizeDatetimeValue($value);
		return date($format === null ? $this->dateFormat : $format, $value);
	}

	/**
	 * Formats the value as a time.
	 * @param integer|string|DateTime $value the value to be formatted. The following
	 * types of value are supported:
	 *
	 * - an integer representing a UNIX timestamp
	 * - a string that can be parsed into a UNIX timestamp via `strtotime()`
	 * - a PHP DateTime object
	 *
	 * @param string $format the format used to convert the value into a date string.
	 * If null, [[timeFormat]] will be used. The format string should be one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function asTime($value, $format = null)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		$value = $this->normalizeDatetimeValue($value);
		return date($format === null ? $this->timeFormat : $format, $value);
	}

	/**
	 * Formats the value as a datetime.
	 * @param integer|string|DateTime $value the value to be formatted. The following
	 * types of value are supported:
	 *
	 * - an integer representing a UNIX timestamp
	 * - a string that can be parsed into a UNIX timestamp via `strtotime()`
	 * - a PHP DateTime object
	 *
	 * @param string $format the format used to convert the value into a date string.
	 * If null, [[datetimeFormat]] will be used. The format string should be one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function asDatetime($value, $format = null)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		$value = $this->normalizeDatetimeValue($value);
		return date($format === null ? $this->datetimeFormat : $format, $value);
	}

	/**
	 * Normalizes the given datetime value as one that can be taken by various date/time formatting methods.
	 * @param mixed $value the datetime value to be normalized.
	 * @return integer the normalized datetime value
	 */
	protected function normalizeDatetimeValue($value)
	{
		if (is_string($value)) {
			return is_numeric($value) || $value === '' ? (int)$value : strtotime($value);
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
		if ($value === null) {
			return $this->nullDisplay;
		}
		if (is_string($value) && preg_match('/^(-?\d+)/', $value, $matches)) {
			return $matches[1];
		} else {
			$value = (int)$value;
			return "$value";
		}
	}

	/**
	 * Formats the value as a double number.
	 * Property [[decimalSeparator]] will be used to represent the decimal point.
	 * @param mixed $value the value to be formatted
	 * @param integer $decimals the number of digits after the decimal point
	 * @return string the formatting result.
	 * @see decimalSeparator
	 */
	public function asDouble($value, $decimals = 2)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		if ($this->decimalSeparator === null) {
			return sprintf("%.{$decimals}f", $value);
		} else {
			return str_replace('.', $this->decimalSeparator, sprintf("%.{$decimals}f", $value));
		}
	}

	/**
	 * Formats the value as a number with decimal and thousand separators.
	 * This method calls the PHP number_format() function to do the formatting.
	 * @param mixed $value the value to be formatted
	 * @param integer $decimals the number of digits after the decimal point
	 * @return string the formatted result
	 * @see decimalSeparator
	 * @see thousandSeparator
	 */
	public function asNumber($value, $decimals = 0)
	{
		if ($value === null) {
			return $this->nullDisplay;
		}
		$ds = isset($this->decimalSeparator) ? $this->decimalSeparator: '.';
		$ts = isset($this->thousandSeparator) ? $this->thousandSeparator: ',';
		return number_format($value, $decimals, $ds, $ts);
	}
}
