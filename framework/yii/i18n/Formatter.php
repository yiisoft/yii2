<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Yii;
use IntlDateFormatter;
use NumberFormatter;
use DateTime;
use yii\base\InvalidConfigException;

/**
 * Formatter is the localized version of [[\yii\base\Formatter]].
 *
 * Formatter requires the PHP "intl" extension to be installed. Formatter supports localized
 * formatting of date, time and numbers, based on the current [[locale]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Formatter extends \yii\base\Formatter
{
	/**
	 * @var string the locale ID that is used to localize the date and number formatting.
	 * If not set, [[\yii\base\Application::language]] will be used.
	 */
	public $locale;
	/**
	 * @var string the default format string to be used to format a date using PHP date() function.
	 */
	public $dateFormat = 'short';
	/**
	 * @var string the default format string to be used to format a time using PHP date() function.
	 */
	public $timeFormat = 'short';
	/**
	 * @var string the default format string to be used to format a date and time using PHP date() function.
	 */
	public $datetimeFormat = 'short';
	/**
	 * @var array the options to be set for the NumberFormatter objects. Please refer to
	 */
	public $numberFormatOptions = array();


	/**
	 * Initializes the component.
	 * This method will check if the "intl" PHP extension is installed and set the
	 * default value of [[locale]].
	 * @throws InvalidConfigException if the "intl" PHP extension is not installed.
	 */
	public function init()
	{
		if (!extension_loaded('intl')) {
			throw new InvalidConfigException('The "intl" PHP extension is not install. It is required to format data values in localized formats.');
		}
		if ($this->locale === null) {
			$this->locale = Yii::$app->language;
		}
		parent::init();
	}

	private $_dateFormats = array(
		'short' => IntlDateFormatter::SHORT,
		'medium' => IntlDateFormatter::MEDIUM,
		'long' => IntlDateFormatter::LONG,
		'full' => IntlDateFormatter::FULL,
	);

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
		if ($format === null) {
			$format = $this->dateFormat;
		}
		if (isset($this->_dateFormats[$format])) {
			$formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE);
		} else {
			$formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE);
			$formatter->setPattern($format);
		}
		return $formatter->format($value);
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
	 * If null, [[dateFormat]] will be used. The format string should be the one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see timeFormat
	 */
	public function asTime($value, $format = null)
	{
		$value = $this->normalizeDatetimeValue($value);
		if ($format === null) {
			$format = $this->timeFormat;
		}
		if (isset($this->_dateFormats[$format])) {
			$formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormats[$format]);
		} else {
			$formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE);
			$formatter->setPattern($format);
		}
		return $formatter->format($value);
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
	 * If null, [[dateFormat]] will be used. The format string should be the one
	 * that can be recognized by the PHP `date()` function.
	 * @return string the formatted result
	 * @see datetimeFormat
	 */
	public function asDatetime($value, $format = null)
	{
		$value = $this->normalizeDatetimeValue($value);
		if ($format === null) {
			$format = $this->datetimeFormat;
		}
		if (isset($this->_dateFormats[$format])) {
			$formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], $this->_dateFormats[$format]);
		} else {
			$formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE);
			$formatter->setPattern($format);
		}
		return $formatter->format($value);
	}

	/**
	 * Formats the value as a decimal number.
	 * @param mixed $value the value to be formatted
	 * @param string $format the format to be used. Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
	 * for details on how to specify a format.
	 * @return string the formatted result.
	 */
	public function asDecimal($value, $format = null)
	{
		$this->createNumberFormatter(NumberFormatter::DECIMAL, $format)->format($value);
	}

	/**
	 * Formats the value as a currency number.
	 * @param mixed $value the value to be formatted
	 * @param string $currency the 3-letter ISO 4217 currency code indicating the currency to use.
	 * @param string $format the format to be used. Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
	 * for details on how to specify a format.
	 * @return string the formatted result.
	 */
	public function asCurrency($value, $currency = 'USD', $format = null)
	{
		$this->createNumberFormatter(NumberFormatter::CURRENCY, $format)->formatCurrency($value, $currency);
	}

	/**
	 * Formats the value as a percent number.
	 * @param mixed $value the value to be formatted
	 * @param string $format the format to be used. Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
	 * for details on how to specify a format.
	 * @return string the formatted result.
	 */
	public function asPercent($value, $format = null)
	{
		$this->createNumberFormatter(NumberFormatter::PERCENT, $format)->format($value);
	}

	/**
	 * Formats the value as a scientific number.
	 * @param mixed $value the value to be formatted
	 * @param string $format the format to be used. Please refer to [ICU manual](http://www.icu-project.org/apiref/icu4c/classDecimalFormat.html#_details)
	 * for details on how to specify a format.
	 * @return string the formatted result.
	 */
	public function asScientific($value, $format = null)
	{
		$this->createNumberFormatter(NumberFormatter::SCIENTIFIC, $format)->format($value);
	}

	/**
	 * Creates a number formatter based on the given type and format.
	 * @param integer $type the type of the number formatter
	 * @param string $format the format to be used
	 * @return NumberFormatter the created formatter instance
	 */
	protected function createNumberFormatter($type, $format)
	{
		$formatter = new NumberFormatter($this->locale, $type);
		if ($format !== null) {
			$formatter->setPattern($format);
		}
		if (!empty($this->numberFormatOptions)) {
			foreach ($this->numberFormatOptions as $name => $attribute) {
				$formatter->setAttribute($name, $attribute);
			}
		}
		return $formatter;
	}
}
