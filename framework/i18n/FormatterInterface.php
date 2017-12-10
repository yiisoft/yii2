<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
namespace yii\i18n;

use Closure;
use DateInterval;
use DateTime;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;

/**
 * FormatterInterface defines a set of commonly used data formatting methods.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Enrica Ruedin <e.ruedin@guggach.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0.15
 */
interface FormatterInterface
{
    /**
     * Formats the value based on the given format type.
     * This method will call one of the "as" methods available in this class to do the formatting.
     * For type "xyz", the method "asXyz" will be used. For example, if the format is "html",
     * then [[asHtml()]] will be used. Format names are case insensitive.
     * @param mixed $value the value to be formatted.
     * @param string|array|Closure $format the format of the value, e.g., "html", "text" or an anonymous function
     * returning the formatted value.
     *
     * To specify additional parameters of the formatting method, you may use an array.
     * The first element of the array specifies the format name, while the rest of the elements will be used as the
     * parameters to the formatting method. For example, a format of `['date', 'Y-m-d']` will cause the invocation
     * of `asDate($value, 'Y-m-d')`.
     *
     * The anonymous function signature should be: `function($value, $formatter)`,
     * where `$value` is the value that should be formatted and `$formatter` is an instance of the Formatter class,
     * which can be used to call other formatting functions.
     * The possibility to use an anonymous function is available since version 2.0.13.
     * @return string the formatting result.
     * @throws InvalidParamException if the format type is not supported by this class.
     */
    public function format($value, $format);

    /**
     * Formats the value as is without any formatting.
     * This method simply returns back the parameter without any format.
     * The only exception is a `null` value which will be formatted using [[nullDisplay]].
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asRaw($value);

    /**
     * Formats the value as an HTML-encoded plain text.
     * @param string $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asText($value);

    /**
     * Formats the value as an HTML-encoded plain text with newlines converted into breaks.
     * @param string $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asNtext($value);

    /**
     * Formats the value as HTML-encoded text paragraphs.
     * Each text paragraph is enclosed within a `<p>` tag.
     * One or multiple consecutive empty lines divide two paragraphs.
     * @param string $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asParagraphs($value);

    /**
     * Formats the value as HTML text.
     * The value will be purified using [[HtmlPurifier]] to avoid XSS attacks.
     * Use [[asRaw()]] if you do not want any purification of the value.
     * @param string $value the value to be formatted.
     * @param array|null $config the configuration for the HTMLPurifier class.
     * @return string the formatted result.
     */
    public function asHtml($value, $config = null);

    /**
     * Formats the value as a mailto link.
     * @param string $value the value to be formatted.
     * @param array $options the tag options in terms of name-value pairs. See [[Html::mailto()]].
     * @return string the formatted result.
     */
    public function asEmail($value, $options = []);

    /**
     * Formats the value as an image tag.
     * @param mixed $value the value to be formatted.
     * @param array $options the tag options in terms of name-value pairs. See [[Html::img()]].
     * @return string the formatted result.
     */
    public function asImage($value, $options = []);

    /**
     * Formats the value as a hyperlink.
     * @param mixed $value the value to be formatted.
     * @param array $options the tag options in terms of name-value pairs. See [[Html::a()]].
     * @return string the formatted result.
     */
    public function asUrl($value, $options = []);

    /**
     * Formats the value as a boolean.
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
     * @see booleanFormat
     */
    public function asBoolean($value);

    /**
     * Formats the value as a date.
     * @param int|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp. A UNIX timestamp is always in UTC by its definition.
     * - a string that can be [parsed to create a DateTime object](http://php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](http://php.net/manual/en/class.datetime.php) object. You may set the time zone
     *   for the DateTime object to specify the source time zone.
     *
     * The formatter will convert date values according to [[timeZone]] before formatting it.
     * If no timezone conversion should be performed, you need to set [[defaultTimeZone]] and [[timeZone]] to the same value.
     * Also no conversion will be performed on values that have no time information, e.g. `"2017-06-05"`.
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[dateFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/en/function.date.php)-function.
     *
     * @return string the formatted result.
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @see dateFormat
     */
    public function asDate($value, $format = null);

    /**
     * Formats the value as a time.
     * @param int|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp. A UNIX timestamp is always in UTC by its definition.
     * - a string that can be [parsed to create a DateTime object](http://php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](http://php.net/manual/en/class.datetime.php) object. You may set the time zone
     *   for the DateTime object to specify the source time zone.
     *
     * The formatter will convert date values according to [[timeZone]] before formatting it.
     * If no timezone conversion should be performed, you need to set [[defaultTimeZone]] and [[timeZone]] to the same value.
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[timeFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/en/function.date.php)-function.
     *
     * @return string the formatted result.
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @see timeFormat
     */
    public function asTime($value, $format = null);

    /**
     * Formats the value as a datetime.
     * @param int|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp. A UNIX timestamp is always in UTC by its definition.
     * - a string that can be [parsed to create a DateTime object](http://php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](http://php.net/manual/en/class.datetime.php) object. You may set the time zone
     *   for the DateTime object to specify the source time zone.
     *
     * The formatter will convert date values according to [[timeZone]] before formatting it.
     * If no timezone conversion should be performed, you need to set [[defaultTimeZone]] and [[timeZone]] to the same value.
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[datetimeFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/en/function.date.php)-function.
     *
     * @return string the formatted result.
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @see datetimeFormat
     */
    public function asDatetime($value, $format = null);

    /**
     * Formats a date, time or datetime in a float number as UNIX timestamp (seconds since 01-01-1970).
     * @param int|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be [parsed to create a DateTime object](http://php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](http://php.net/manual/en/class.datetime.php) object
     *
     * @return string the formatted result.
     */
    public function asTimestamp($value);

    /**
     * Formats the value as the time interval between a date and now in human readable form.
     *
     * This method can be used in three different ways:
     *
     * 1. Using a timestamp that is relative to `now`.
     * 2. Using a timestamp that is relative to the `$referenceTime`.
     * 3. Using a `DateInterval` object.
     *
     * @param int|string|DateTime|DateInterval $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be [parsed to create a DateTime object](http://php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](http://php.net/manual/en/class.datetime.php) object
     * - a PHP DateInterval object (a positive time interval will refer to the past, a negative one to the future)
     *
     * @param int|string|DateTime $referenceTime if specified the value is used as a reference time instead of `now`
     * when `$value` is not a `DateInterval` object.
     * @return string the formatted result.
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     */
    public function asRelativeTime($value, $referenceTime = null);

    /**
     * Represents the value as duration in human readable format.
     *
     * @param DateInterval|string|int $value the value to be formatted. Acceptable formats:
     *  - [DateInterval object](http://php.net/manual/ru/class.dateinterval.php)
     *  - integer - number of seconds. For example: value `131` represents `2 minutes, 11 seconds`
     *  - ISO8601 duration format. For example, all of these values represent `1 day, 2 hours, 30 minutes` duration:
     *    `2015-01-01T13:00:00Z/2015-01-02T13:30:00Z` - between two datetime values
     *    `2015-01-01T13:00:00Z/P1D2H30M` - time interval after datetime value
     *    `P1D2H30M/2015-01-02T13:30:00Z` - time interval before datetime value
     *    `P1D2H30M` - simply a date interval
     *    `P-1D2H30M` - a negative date interval (`-1 day, 2 hours, 30 minutes`)
     *
     * @param string $implodeString will be used to concatenate duration parts. Defaults to `, `.
     * @param string $negativeSign will be prefixed to the formatted duration, when it is negative. Defaults to `-`.
     * @return string the formatted duration.
     * @since 2.0.7
     */
    public function asDuration($value, $implodeString = ', ', $negativeSign = '-');

    /**
     * Formats the value as an integer number by removing any decimal digits without rounding.
     *
     * @param mixed $value the value to be formatted.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     */
    public function asInteger($value, $options = [], $textOptions = []);

    /**
     * Formats the value as a decimal number.
     *
     * Property [[decimalSeparator]] will be used to represent the decimal point. The
     * value is rounded automatically to the defined decimal digits.
     *
     * @param mixed $value the value to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * If not given, the number of digits depends in the input value and is determined based on
     * `NumberFormatter::MIN_FRACTION_DIGITS` and `NumberFormatter::MAX_FRACTION_DIGITS`, which can be configured
     * using [[$numberFormatterOptions]].
     * If the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available, the default value is `2`.
     * If you want consistent behavior between environments where intl is available and not, you should explicitly
     * specify a value here.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @see decimalSeparator
     * @see thousandSeparator
     */
    public function asDecimal($value, $decimals = null, $options = [], $textOptions = []);

    /**
     * Formats the value as a percent number with "%" sign.
     *
     * @param mixed $value the value to be formatted. It must be a factor e.g. `0.75` will result in `75%`.
     * @param int $decimals the number of digits after the decimal point.
     * If not given, the number of digits depends in the input value and is determined based on
     * `NumberFormatter::MIN_FRACTION_DIGITS` and `NumberFormatter::MAX_FRACTION_DIGITS`, which can be configured
     * using [[$numberFormatterOptions]].
     * If the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available, the default value is `0`.
     * If you want consistent behavior between environments where intl is available and not, you should explicitly
     * specify a value here.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     */
    public function asPercent($value, $decimals = null, $options = [], $textOptions = []);

    /**
     * Formats the value as a scientific number.
     *
     * @param mixed $value the value to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * If not given, the number of digits depends in the input value and is determined based on
     * `NumberFormatter::MIN_FRACTION_DIGITS` and `NumberFormatter::MAX_FRACTION_DIGITS`, which can be configured
     * using [[$numberFormatterOptions]].
     * If the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available, the default value depends on your PHP configuration.
     * If you want consistent behavior between environments where intl is available and not, you should explicitly
     * specify a value here.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     */
    public function asScientific($value, $decimals = null, $options = [], $textOptions = []);

    /**
     * Formats the value as a currency number.
     *
     * This function does not require the [PHP intl extension](http://php.net/manual/en/book.intl.php) to be installed
     * to work, but it is highly recommended to install it to get good formatting results.
     *
     * @param mixed $value the value to be formatted.
     * @param string $currency the 3-letter ISO 4217 currency code indicating the currency to use.
     * If null, [[currencyCode]] will be used.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException if no currency is given and [[currencyCode]] is not defined.
     */
    public function asCurrency($value, $currency = null, $options = [], $textOptions = []);

    /**
     * Formats the value as a number spellout.
     *
     * This function requires the [PHP intl extension](http://php.net/manual/en/book.intl.php) to be installed.
     *
     * @param mixed $value the value to be formatted
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available.
     */
    public function asSpellout($value);

    /**
     * Formats the value as a ordinal value of a number.
     *
     * This function requires the [PHP intl extension](http://php.net/manual/en/book.intl.php) to be installed.
     *
     * @param mixed $value the value to be formatted
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available.
     */
    public function asOrdinal($value);

    /**
     * Formats the value in bytes as a size in human readable form for example `12 KB`.
     *
     * This is the short form of [[asSize]].
     *
     * If [[sizeFormatBase]] is 1024, [binary prefixes](http://en.wikipedia.org/wiki/Binary_prefix) (e.g. kibibyte/KiB, mebibyte/MiB, ...)
     * are used in the formatting result.
     *
     * @param string|int|float $value value in bytes to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @see sizeFormatBase
     * @see asSize
     */
    public function asShortSize($value, $decimals = null, $options = [], $textOptions = []);

    /**
     * Formats the value in bytes as a size in human readable form, for example `12 kilobytes`.
     *
     * If [[sizeFormatBase]] is 1024, [binary prefixes](http://en.wikipedia.org/wiki/Binary_prefix) (e.g. kibibyte/KiB, mebibyte/MiB, ...)
     * are used in the formatting result.
     *
     * @param string|int|float $value value in bytes to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @see sizeFormatBase
     * @see asShortSize
     */
    public function asSize($value, $decimals = null, $options = [], $textOptions = []);

    /**
     * Formats the value as a length in human readable form for example `12 meters`.
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * @param array $numberOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @see asLength
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asLength($value, $decimals = null, $numberOptions = [], $textOptions = []);

    /**
     * Formats the value as a length in human readable form for example `12 m`.
     * This is the short form of [[asLength]].
     *
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @see asLength
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asShortLength($value, $decimals = null, $options = [], $textOptions = []);

    /**
     * Formats the value as a weight in human readable form for example `12 kilograms`.
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asWeight($value, $decimals = null, $options = [], $textOptions = []);

    /**
     * Formats the value as a weight in human readable form for example `12 kg`.
     * This is the short form of [[asWeight]].
     *
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asShortWeight($value, $decimals = null, $options = [], $textOptions = []);

}