<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\i18n;

use Closure;
use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use IntlDateFormatter;
use NumberFormatter;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/**
 * Formatter provides a set of commonly used data formatting methods.
 *
 * The formatting methods provided by Formatter are all named in the form of `asXyz()`.
 * The behavior of some of them may be configured via the properties of Formatter. For example,
 * by configuring [[dateFormat]], one may control how [[asDate()]] formats the value into a date string.
 *
 * Formatter is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->formatter`.
 *
 * The Formatter class is designed to format values according to a [[locale]]. For this feature to work
 * the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) has to be installed.
 * Most of the methods however work also if the PHP intl extension is not installed by providing
 * a fallback implementation. Without intl month and day names are in English only.
 * Note that even if the intl extension is installed, formatting date and time values for years >=2038 or <=1901
 * on 32bit systems will fall back to the PHP implementation because intl uses a 32bit UNIX timestamp internally.
 * On a 64bit system the intl formatter is used in all cases if installed.
 *
 * > Note: The Formatter class is meant to be used for formatting values for display to users in different
 * > languages and time zones. If you need to format a date or time in machine readable format, use the
 * > PHP [date()](https://www.php.net/manual/en/function.date.php) function instead.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Enrica Ruedin <e.ruedin@guggach.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Formatter extends Component
{
    /**
     * @since 2.0.13
     */
    const UNIT_SYSTEM_METRIC = 'metric';
    /**
     * @since 2.0.13
     */
    const UNIT_SYSTEM_IMPERIAL = 'imperial';
    /**
     * @since 2.0.13
     */
    const FORMAT_WIDTH_LONG = 'long';
    /**
     * @since 2.0.13
     */
    const FORMAT_WIDTH_SHORT = 'short';
    /**
     * @since 2.0.13
     */
    const UNIT_LENGTH = 'length';
    /**
     * @since 2.0.13
     */
    const UNIT_WEIGHT = 'mass';

    /**
     * @var string|null the text to be displayed when formatting a `null` value.
     * Defaults to `'<span class="not-set">(not set)</span>'`, where `(not set)`
     * will be translated according to [[locale]].
     */
    public $nullDisplay;
    /**
     * @var array the text to be displayed when formatting a boolean value. The first element corresponds
     * to the text displayed for `false`, the second element for `true`.
     * Defaults to `['No', 'Yes']`, where `Yes` and `No`
     * will be translated according to [[locale]].
     */
    public $booleanFormat;
    /**
     * @var string|null the locale ID that is used to localize the date and number formatting.
     * For number and date formatting this is only effective when the
     * [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is installed.
     * If not set, [[\yii\base\Application::language]] will be used.
     */
    public $locale;
    /**
     * @var string|null the language code (e.g. `en-US`, `en`) that is used to translate internal messages.
     * If not set, [[locale]] will be used (without the `@calendar` param, if included).
     *
     * @since 2.0.28
     */
    public $language;
    /**
     * @var string|null the time zone to use for formatting time and date values.
     *
     * This can be any value that may be passed to [date_default_timezone_set()](https://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](https://www.php.net/manual/en/timezones.php) for available time zones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     *
     * Note that the default time zone for input data is assumed to be UTC by default if no time zone is included in the input date value.
     * If you store your data in a different time zone in the database, you have to adjust [[defaultTimeZone]] accordingly.
     */
    public $timeZone;
    /**
     * @var string the time zone that is assumed for input values if they do not include a time zone explicitly.
     *
     * The value must be a valid time zone identifier, e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Please refer to the [php manual](https://www.php.net/manual/en/timezones.php) for available time zones.
     *
     * It defaults to `UTC` so you only have to adjust this value if you store datetime values in another time zone in your database.
     *
     * Note that a UNIX timestamp is always in UTC by its definition. That means that specifying a default time zone different from
     * UTC has no effect on date values given as UNIX timestamp.
     *
     * @since 2.0.1
     */
    public $defaultTimeZone = 'UTC';
    /**
     * @var string the default format string to be used to format a [[asDate()|date]].
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     *
     * It can also be a custom format as specified in the [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/).
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](https://www.php.net/manual/en/function.date.php)-function.
     *
     * For example:
     *
     * ```php
     * 'MM/dd/yyyy' // date in ICU format
     * 'php:m/d/Y' // the same date in PHP format
     * ```
     */
    public $dateFormat = 'medium';
    /**
     * @var string the default format string to be used to format a [[asTime()|time]].
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     *
     * It can also be a custom format as specified in the [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/).
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](https://www.php.net/manual/en/function.date.php)-function.
     *
     * For example:
     *
     * ```php
     * 'HH:mm:ss' // time in ICU format
     * 'php:H:i:s' // the same time in PHP format
     * ```
     */
    public $timeFormat = 'medium';
    /**
     * @var string the default format string to be used to format a [[asDatetime()|date and time]].
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     *
     * It can also be a custom format as specified in the [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](https://www.php.net/manual/en/function.date.php) function.
     *
     * For example:
     *
     * ```php
     * 'MM/dd/yyyy HH:mm:ss' // date and time in ICU format
     * 'php:m/d/Y H:i:s' // the same date and time in PHP format
     * ```
     */
    public $datetimeFormat = 'medium';
    /**
     * @var \IntlCalendar|int|null the calendar to be used for date formatting. The value of this property will be directly
     * passed to the [constructor of the `IntlDateFormatter` class](https://www.php.net/manual/en/intldateformatter.create.php).
     *
     * Defaults to `null`, which means the Gregorian calendar will be used. You may also explicitly pass the constant
     * `\IntlDateFormatter::GREGORIAN` for Gregorian calendar.
     *
     * To use an alternative calendar like for example the [Jalali calendar](https://en.wikipedia.org/wiki/Jalali_calendar),
     * set this property to `\IntlDateFormatter::TRADITIONAL`.
     * The calendar must then be specified in the [[locale]], for example for the persian calendar the configuration for the formatter would be:
     *
     * ```php
     * 'formatter' => [
     *     'locale' => 'fa_IR@calendar=persian',
     *     'calendar' => \IntlDateFormatter::TRADITIONAL,
     * ],
     * ```
     *
     * Available calendar names can be found in the [ICU manual](https://unicode-org.github.io/icu/userguide/datetime/calendar/).
     *
     * Since PHP 5.5 you may also use an instance of the [[\IntlCalendar]] class.
     * Check the [PHP manual](https://www.php.net/manual/en/intldateformatter.create.php) for more details.
     *
     * If the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is not available, setting this property will have no effect.
     *
     * @see https://www.php.net/manual/en/intldateformatter.create.php
     * @see https://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes
     * @see https://www.php.net/manual/en/class.intlcalendar.php
     * @since 2.0.7
     */
    public $calendar;
    /**
     * @var string|null the character displayed as the decimal point when formatting a number.
     * If not set, the decimal separator corresponding to [[locale]] will be used.
     * If [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is not available, the default value is '.'.
     */
    public $decimalSeparator;
    /**
     * @var string|null the character displayed as the decimal point when formatting a currency.
     * If not set, the currency decimal separator corresponding to [[locale]] will be used.
     * If [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is not available, setting this property will have no effect.
     * @since 2.0.35
     */
    public $currencyDecimalSeparator;
    /**
     * @var string|null the character displayed as the thousands separator (also called grouping separator) character when formatting a number.
     * If not set, the thousand separator corresponding to [[locale]] will be used.
     * If [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is not available, the default value is ','.
     */
    public $thousandSeparator;
    /**
     * @var array a list of name value pairs that are passed to the
     * intl [NumberFormatter::setAttribute()](https://www.php.net/manual/en/numberformatter.setattribute.php) method of all
     * the number formatter objects created by [[createNumberFormatter()]].
     * This property takes only effect if the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is installed.
     *
     * Please refer to the [PHP manual](https://www.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatattribute)
     * for the possible options.
     *
     * For example to adjust the maximum and minimum value of fraction digits you can configure this property like the following:
     *
     * ```php
     * [
     *     NumberFormatter::MIN_FRACTION_DIGITS => 0,
     *     NumberFormatter::MAX_FRACTION_DIGITS => 2,
     * ]
     * ```
     */
    public $numberFormatterOptions = [];
    /**
     * @var array a list of name value pairs that are passed to the
     * intl [NumberFormatter::setTextAttribute()](https://www.php.net/manual/en/numberformatter.settextattribute.php) method of all
     * the number formatter objects created by [[createNumberFormatter()]].
     * This property takes only effect if the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is installed.
     *
     * Please refer to the [PHP manual](https://www.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformattextattribute)
     * for the possible options.
     *
     * For example to change the minus sign for negative numbers you can configure this property like the following:
     *
     * ```php
     * [
     *     NumberFormatter::NEGATIVE_PREFIX => 'MINUS',
     * ]
     * ```
     */
    public $numberFormatterTextOptions = [];
    /**
     * @var array a list of name value pairs that are passed to the
     * intl [NumberFormatter::setSymbol()](https://www.php.net/manual/en/numberformatter.setsymbol.php) method of all
     * the number formatter objects created by [[createNumberFormatter()]].
     * This property takes only effect if the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is installed.
     *
     * Please refer to the [PHP manual](https://www.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatsymbol)
     * for the possible options.
     *
     * For example to choose a custom currency symbol, e.g. [U+20BD](https://unicode-table.com/en/20BD/) instead of `руб.` for Russian Ruble:
     *
     * ```php
     * [
     *     NumberFormatter::CURRENCY_SYMBOL => '₽',
     * ]
     * ```
     *
     * @since 2.0.4
     */
    public $numberFormatterSymbols = [];
    /**
     * @var string|null the 3-letter ISO 4217 currency code indicating the default currency to use for [[asCurrency]].
     * If not set, the currency code corresponding to [[locale]] will be used.
     * Note that in this case the [[locale]] has to be specified with a country code, e.g. `en-US` otherwise it
     * is not possible to determine the default currency.
     */
    public $currencyCode;
    /**
     * @var int the base at which a kilobyte is calculated (1000 or 1024 bytes per kilobyte), used by [[asSize]] and [[asShortSize]].
     * Defaults to 1024.
     */
    public $sizeFormatBase = 1024;
    /**
     * @var string default system of measure units. Defaults to [[UNIT_SYSTEM_METRIC]].
     * Possible values:
     *  - [[UNIT_SYSTEM_METRIC]]
     *  - [[UNIT_SYSTEM_IMPERIAL]]
     *
     * @see asLength
     * @see asWeight
     * @since 2.0.13
     */
    public $systemOfUnits = self::UNIT_SYSTEM_METRIC;
    /**
     * @var array configuration of weight and length measurement units.
     * This array contains the most usable measurement units, but you can change it
     * in case you have some special requirements.
     *
     * For example, you can add smaller measure unit:
     *
     * ```php
     * $this->measureUnits[self::UNIT_LENGTH][self::UNIT_SYSTEM_METRIC] = [
     *     'nanometer' => 0.000001
     * ]
     * ```
     * @see asLength
     * @see asWeight
     * @since 2.0.13
     */
    public $measureUnits = [
        self::UNIT_LENGTH => [
            self::UNIT_SYSTEM_IMPERIAL => [
                'inch' => 1,
                'foot' => 12,
                'yard' => 36,
                'chain' => 792,
                'furlong' => 7920,
                'mile' => 63360,
            ],
            self::UNIT_SYSTEM_METRIC => [
                'millimeter' => 1,
                'centimeter' => 10,
                'meter' => 1000,
                'kilometer' => 1000000,
            ],
        ],
        self::UNIT_WEIGHT => [
            self::UNIT_SYSTEM_IMPERIAL => [
                'grain' => 1,
                'drachm' => 27.34375,
                'ounce' => 437.5,
                'pound' => 7000,
                'stone' => 98000,
                'quarter' => 196000,
                'hundredweight' => 784000,
                'ton' => 15680000,
            ],
            self::UNIT_SYSTEM_METRIC => [
                'gram' => 1,
                'kilogram' => 1000,
                'ton' => 1000000,
            ],
        ],
    ];
    /**
     * @var array The base units that are used as multipliers for smallest possible unit from [[measureUnits]].
     * @since 2.0.13
     */
    public $baseUnits = [
        self::UNIT_LENGTH => [
            self::UNIT_SYSTEM_IMPERIAL => 12, // 1 feet = 12 inches
            self::UNIT_SYSTEM_METRIC => 1000, // 1 meter = 1000 millimeters
        ],
        self::UNIT_WEIGHT => [
            self::UNIT_SYSTEM_IMPERIAL => 7000, // 1 pound = 7000 grains
            self::UNIT_SYSTEM_METRIC => 1000, // 1 kilogram = 1000 grams
        ],
    ];

    /**
     * @var bool whether the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is loaded.
     */
    private $_intlLoaded = false;
    /**
     * @var \ResourceBundle cached ResourceBundle object used to read unit translations
     */
    private $_resourceBundle;
    /**
     * @var array cached unit translation patterns
     */
    private $_unitMessages = [];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if ($this->timeZone === null) {
            $this->timeZone = Yii::$app->timeZone;
        }
        if ($this->locale === null) {
            $this->locale = Yii::$app->language;
        }
        if ($this->language === null) {
            $this->language = strtok($this->locale, '@');
        }
        if ($this->booleanFormat === null) {
            $this->booleanFormat = [Yii::t('yii', 'No', [], $this->language), Yii::t('yii', 'Yes', [], $this->language)];
        }
        if ($this->nullDisplay === null) {
            $this->nullDisplay = '<span class="not-set">' . Yii::t('yii', '(not set)', [], $this->language) . '</span>';
        }
        $this->_intlLoaded = extension_loaded('intl');
        if (!$this->_intlLoaded) {
            if ($this->decimalSeparator === null) {
                $this->decimalSeparator = '.';
            }
            if ($this->thousandSeparator === null) {
                $this->thousandSeparator = ',';
            }
        }
    }

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
     * @throws InvalidArgumentException if the format type is not supported by this class.
     */
    public function format($value, $format)
    {
        if ($format instanceof Closure) {
            return $format($value, $this);
        }
        if (is_array($format)) {
            if (!isset($format[0])) {
                throw new InvalidArgumentException('The $format array must contain at least one element.');
            }
            $f = $format[0];
            $format[0] = $value;
            $params = $format;
            $format = $f;
        } else {
            $params = [$value];
        }
        $method = 'as' . $format;
        if ($this->hasMethod($method)) {
            return call_user_func_array([$this, $method], array_values($params));
        }

        throw new InvalidArgumentException("Unknown format type: $format");
    }

    // simple formats

    /**
     * Formats the value as is without any formatting.
     * This method simply returns back the parameter without any format.
     * The only exception is a `null` value which will be formatted using [[nullDisplay]].
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
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
     * @param string|null $value the value to be formatted.
     * @return string the formatted result.
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
     * @param string|null $value the value to be formatted.
     * @return string the formatted result.
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
     * @param string|null $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asParagraphs($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return str_replace('<p></p>', '', '<p>' . preg_replace('/\R{2,}/u', "</p>\n<p>", Html::encode($value)) . '</p>');
    }

    /**
     * Formats the value as HTML text.
     * The value will be purified using [[HtmlPurifier]] to avoid XSS attacks.
     * Use [[asRaw()]] if you do not want any purification of the value.
     * @param string|null $value the value to be formatted.
     * @param array|null $config the configuration for the HTMLPurifier class.
     * @return string the formatted result.
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
     * @param string|null $value the value to be formatted.
     * @param array $options the tag options in terms of name-value pairs. See [[Html::mailto()]].
     * @return string the formatted result.
     */
    public function asEmail($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::mailto(Html::encode($value), $value, $options);
    }

    /**
     * Formats the value as an image tag.
     * @param mixed $value the value to be formatted.
     * @param array $options the tag options in terms of name-value pairs. See [[Html::img()]].
     * @return string the formatted result.
     */
    public function asImage($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::img($value, $options);
    }

    /**
     * Formats the value as a hyperlink.
     * @param mixed $value the value to be formatted.
     * @param array $options the tag options in terms of name-value pairs. See [[Html::a()]]. Since 2.0.43 there is
     * a special option available `scheme` - if set it won't be passed to [[Html::a()]] but it will control the URL
     * protocol part of the link by normalizing URL and ensuring that it uses specified scheme. See [[Url::ensureScheme()]].
     * If `scheme` is not set the original behavior is preserved which is to add "http://" prefix when "://" string is
     * not found in the $value.
     * @return string the formatted result.
     */
    public function asUrl($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $url = $value;
        $scheme = ArrayHelper::remove($options, 'scheme');
        if ($scheme === null) {
            if (strpos($url, '://') === false) {
                $url = 'http://' . $url;
            }
        } else {
            $url = Url::ensureScheme($url, $scheme);
        }

        return Html::a(Html::encode($value), $url, $options);
    }

    /**
     * Formats the value as a boolean.
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
     * @see booleanFormat
     */
    public function asBoolean($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return $value ? $this->booleanFormat[1] : $this->booleanFormat[0];
    }

    // date and time formats

    /**
     * Formats the value as a date.
     * @param int|string|DateTime|DateTimeInterface|null $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp. A UNIX timestamp is always in UTC by its definition.
     * - a string that can be [parsed to create a DateTime object](https://www.php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) object. You may set the time zone
     *   for the DateTime object to specify the source time zone.
     *
     * The formatter will convert date values according to [[timeZone]] before formatting it.
     * If no timezone conversion should be performed, you need to set [[defaultTimeZone]] and [[timeZone]] to the same value.
     * Also no conversion will be performed on values that have no time information, e.g. `"2017-06-05"`.
     *
     * @param string|null $format the format used to convert the value into a date string.
     * If null, [[dateFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](https://www.php.net/manual/en/function.date.php)-function.
     *
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @see dateFormat
     */
    public function asDate($value, $format = null)
    {
        if ($format === null) {
            $format = $this->dateFormat;
        }

        return $this->formatDateTimeValue($value, $format, 'date');
    }

    /**
     * Formats the value as a time.
     * @param int|string|DateTime|DateTimeInterface|null $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp. A UNIX timestamp is always in UTC by its definition.
     * - a string that can be [parsed to create a DateTime object](https://www.php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) object. You may set the time zone
     *   for the DateTime object to specify the source time zone.
     *
     * The formatter will convert date values according to [[timeZone]] before formatting it.
     * If no timezone conversion should be performed, you need to set [[defaultTimeZone]] and [[timeZone]] to the same value.
     *
     * @param string|null $format the format used to convert the value into a date string.
     * If null, [[timeFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](https://www.php.net/manual/en/function.date.php)-function.
     *
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @see timeFormat
     */
    public function asTime($value, $format = null)
    {
        if ($format === null) {
            $format = $this->timeFormat;
        }

        return $this->formatDateTimeValue($value, $format, 'time');
    }

    /**
     * Formats the value as a datetime.
     * @param int|string|DateTime|DateTimeInterface|null $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp. A UNIX timestamp is always in UTC by its definition.
     * - a string that can be [parsed to create a DateTime object](https://www.php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) object. You may set the time zone
     *   for the DateTime object to specify the source time zone.
     *
     * The formatter will convert date values according to [[timeZone]] before formatting it.
     * If no timezone conversion should be performed, you need to set [[defaultTimeZone]] and [[timeZone]] to the same value.
     *
     * @param string|null $format the format used to convert the value into a date string.
     * If null, [[datetimeFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](https://unicode-org.github.io/icu/userguide/format_parse/datetime/).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](https://www.php.net/manual/en/function.date.php)-function.
     *
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @see datetimeFormat
     */
    public function asDatetime($value, $format = null)
    {
        if ($format === null) {
            $format = $this->datetimeFormat;
        }

        return $this->formatDateTimeValue($value, $format, 'datetime');
    }

    /**
     * @var array map of short format names to IntlDateFormatter constant values.
     */
    private $_dateFormats = [
        'short' => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long' => 1, // IntlDateFormatter::LONG,
        'full' => 0, // IntlDateFormatter::FULL,
    ];

    /**
     * @param int|string|DateTime|DateTimeInterface|null $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be [parsed to create a DateTime object](https://www.php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) object
     *
     * @param string $format the format used to convert the value into a date string.
     * @param string $type 'date', 'time', or 'datetime'.
     * @throws InvalidConfigException if the date format is invalid.
     * @return string the formatted result.
     */
    private function formatDateTimeValue($value, $format, $type)
    {
        $timeZone = $this->timeZone;
        // avoid time zone conversion for date-only and time-only values
        if ($type === 'date' || $type === 'time') {
            list($timestamp, $hasTimeInfo, $hasDateInfo) = $this->normalizeDatetimeValue($value, true);
            if (($type === 'date' && !$hasTimeInfo) || ($type === 'time' && !$hasDateInfo)) {
                $timeZone = $this->defaultTimeZone;
            }
        } else {
            $timestamp = $this->normalizeDatetimeValue($value);
        }
        if ($timestamp === null) {
            return $this->nullDisplay;
        }

        // intl does not work with dates >=2038 or <=1901 on 32bit machines, fall back to PHP
        $year = $timestamp->format('Y');
        if ($this->_intlLoaded && !(PHP_INT_SIZE === 4 && ($year <= 1901 || $year >= 2038))) {
            if (strncmp($format, 'php:', 4) === 0) {
                $format = FormatConverter::convertDatePhpToIcu(substr($format, 4));
            }
            if (isset($this->_dateFormats[$format])) {
                if ($type === 'date') {
                    $formatter = new IntlDateFormatter(
                        $this->locale,
                        $this->_dateFormats[$format],
                        IntlDateFormatter::NONE,
                        $timeZone,
                        $this->calendar
                    );
                } elseif ($type === 'time') {
                    $formatter = new IntlDateFormatter(
                        $this->locale,
                        IntlDateFormatter::NONE,
                        $this->_dateFormats[$format],
                        $timeZone,
                        $this->calendar
                    );
                } else {
                    $formatter = new IntlDateFormatter(
                        $this->locale,
                        $this->_dateFormats[$format],
                        $this->_dateFormats[$format],
                        $timeZone,
                        $this->calendar
                    );
                }
            } else {
                $formatter = new IntlDateFormatter(
                    $this->locale,
                    IntlDateFormatter::NONE,
                    IntlDateFormatter::NONE,
                    $timeZone,
                    $this->calendar,
                    $format
                );
            }

            // make IntlDateFormatter work with DateTimeImmutable
            if ($timestamp instanceof \DateTimeImmutable) {
                $timestamp = new DateTime($timestamp->format(DateTime::ISO8601), $timestamp->getTimezone());
            }

            return $formatter->format($timestamp);
        }

        if (strncmp($format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        } else {
            $format = FormatConverter::convertDateIcuToPhp($format, $type, $this->locale);
        }
        if ($timeZone != null) {
            if ($timestamp instanceof \DateTimeImmutable) {
                $timestamp = $timestamp->setTimezone(new DateTimeZone($timeZone));
            } else {
                $timestamp->setTimezone(new DateTimeZone($timeZone));
            }
        }

        return $timestamp->format($format);
    }

    /**
     * Normalizes the given datetime value as a DateTime object that can be taken by various date/time formatting methods.
     *
     * @param int|string|DateTime|DateTimeInterface|null $value the datetime value to be normalized. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be [parsed to create a DateTime object](https://www.php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) object
     *
     * @param bool $checkDateTimeInfo whether to also check if the date/time value has some time and date information attached.
     * Defaults to `false`. If `true`, the method will then return an array with the first element being the normalized
     * timestamp, the second a boolean indicating whether the timestamp has time information and third a boolean indicating
     * whether the timestamp has date information.
     * This parameter is available since version 2.0.1.
     * @return DateTime|array the normalized datetime value
     * Since version 2.0.1 this may also return an array if `$checkDateTimeInfo` is true.
     * The first element of the array is the normalized timestamp and the second is a boolean indicating whether
     * the timestamp has time information or it is just a date value.
     * Since version 2.0.12 the array has third boolean element indicating whether the timestamp has date information
     * or it is just a time value.
     * @throws InvalidArgumentException if the input value can not be evaluated as a date value.
     */
    protected function normalizeDatetimeValue($value, $checkDateTimeInfo = false)
    {
        // checking for DateTime and DateTimeInterface is not redundant, DateTimeInterface is only in PHP>5.5
        if ($value === null || $value instanceof DateTime || $value instanceof DateTimeInterface) {
            // skip any processing
            return $checkDateTimeInfo ? [$value, true, true] : $value;
        }
        if (empty($value)) {
            $value = 0;
        }
        try {
            if (is_numeric($value)) { // process as unix timestamp, which is always in UTC
                $timestamp = new DateTime('@' . (int) $value, new DateTimeZone('UTC'));
                return $checkDateTimeInfo ? [$timestamp, true, true] : $timestamp;
            }
            if (
                ($timestamp = DateTime::createFromFormat(
                    'Y-m-d|',
                    $value,
                    new DateTimeZone($this->defaultTimeZone))
                ) !== false
            ) { // try Y-m-d format (support invalid dates like 2012-13-01)
                return $checkDateTimeInfo ? [$timestamp, false, true] : $timestamp;
            }
            if (
                ($timestamp = DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $value,
                    new DateTimeZone($this->defaultTimeZone))
                ) !== false
            ) { // try Y-m-d H:i:s format (support invalid dates like 2012-13-01 12:63:12)
                return $checkDateTimeInfo ? [$timestamp, true, true] : $timestamp;
            }
            // finally try to create a DateTime object with the value
            if ($checkDateTimeInfo) {
                $timestamp = new DateTime($value, new DateTimeZone($this->defaultTimeZone));
                $info = date_parse($value);
                return [
                    $timestamp,
                    !($info['hour'] === false && $info['minute'] === false && $info['second'] === false),
                    !($info['year'] === false && $info['month'] === false && $info['day'] === false && empty($info['zone'])),
                ];
            }

            return new DateTime($value, new DateTimeZone($this->defaultTimeZone));
        } catch (\Exception $e) {
            throw new InvalidArgumentException("'$value' is not a valid date time value: " . $e->getMessage()
                . "\n" . print_r(DateTime::getLastErrors(), true), $e->getCode(), $e);
        }
    }

    /**
     * Formats a date, time or datetime in a float number as UNIX timestamp (seconds since 01-01-1970).
     * @param int|string|DateTime|DateTimeInterface|null $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be [parsed to create a DateTime object](https://www.php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) object
     *
     * @return string the formatted result.
     */
    public function asTimestamp($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $timestamp = $this->normalizeDatetimeValue($value);
        return number_format($timestamp->format('U'), 0, '.', '');
    }

    /**
     * Formats the value as the time interval between a date and now in human readable form.
     *
     * This method can be used in three different ways:
     *
     * 1. Using a timestamp that is relative to `now`.
     * 2. Using a timestamp that is relative to the `$referenceTime`.
     * 3. Using a `DateInterval` object.
     *
     * @param int|string|DateTime|DateTimeInterface|DateInterval|null $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be [parsed to create a DateTime object](https://www.php.net/manual/en/datetime.formats.php).
     *   The timestamp is assumed to be in [[defaultTimeZone]] unless a time zone is explicitly given.
     * - a PHP [DateTime](https://www.php.net/manual/en/class.datetime.php) object
     * - a PHP DateInterval object (a positive time interval will refer to the past, a negative one to the future)
     *
     * @param int|string|DateTime|DateTimeInterface|null $referenceTime if specified the value is used as a reference time instead of `now`
     * when `$value` is not a `DateInterval` object.
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value can not be evaluated as a date value.
     */
    public function asRelativeTime($value, $referenceTime = null)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($value instanceof DateInterval) {
            $interval = $value;
        } else {
            $timestamp = $this->normalizeDatetimeValue($value);
            $timeZone = new DateTimeZone($this->timeZone);

            if ($referenceTime === null) {
                $dateNow = new DateTime('now', $timeZone);
            } else {
                $dateNow = $this->normalizeDatetimeValue($referenceTime);
                $dateNow->setTimezone($timeZone);
            }

            $dateThen = $timestamp->setTimezone($timeZone);
            $interval = $dateThen->diff($dateNow);
        }

        if ($interval->invert) {
            if ($interval->y >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a year} other{# years}}', ['delta' => $interval->y], $this->language);
            }
            if ($interval->m >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a month} other{# months}}', ['delta' => $interval->m], $this->language);
            }
            if ($interval->d >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a day} other{# days}}', ['delta' => $interval->d], $this->language);
            }
            if ($interval->h >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{an hour} other{# hours}}', ['delta' => $interval->h], $this->language);
            }
            if ($interval->i >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a minute} other{# minutes}}', ['delta' => $interval->i], $this->language);
            }
            if ($interval->s == 0) {
                return Yii::t('yii', 'just now', [], $this->language);
            }

            return Yii::t('yii', 'in {delta, plural, =1{a second} other{# seconds}}', ['delta' => $interval->s], $this->language);
        }

        if ($interval->y >= 1) {
            return Yii::t('yii', '{delta, plural, =1{a year} other{# years}} ago', ['delta' => $interval->y], $this->language);
        }
        if ($interval->m >= 1) {
            return Yii::t('yii', '{delta, plural, =1{a month} other{# months}} ago', ['delta' => $interval->m], $this->language);
        }
        if ($interval->d >= 1) {
            return Yii::t('yii', '{delta, plural, =1{a day} other{# days}} ago', ['delta' => $interval->d], $this->language);
        }
        if ($interval->h >= 1) {
            return Yii::t('yii', '{delta, plural, =1{an hour} other{# hours}} ago', ['delta' => $interval->h], $this->language);
        }
        if ($interval->i >= 1) {
            return Yii::t('yii', '{delta, plural, =1{a minute} other{# minutes}} ago', ['delta' => $interval->i], $this->language);
        }
        if ($interval->s == 0) {
            return Yii::t('yii', 'just now', [], $this->language);
        }

        return Yii::t('yii', '{delta, plural, =1{a second} other{# seconds}} ago', ['delta' => $interval->s], $this->language);
    }

    /**
     * Represents the value as duration in human readable format.
     *
     * @param DateInterval|string|int|null $value the value to be formatted. Acceptable formats:
     *  - [DateInterval object](https://www.php.net/manual/ru/class.dateinterval.php)
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
    public function asDuration($value, $implodeString = ', ', $negativeSign = '-')
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($value instanceof DateInterval) {
            $isNegative = $value->invert;
            $interval = $value;
        } elseif (is_numeric($value)) {
            $isNegative = $value < 0;
            $zeroDateTime = (new DateTime())->setTimestamp(0);
            $valueDateTime = (new DateTime())->setTimestamp(abs($value));
            $interval = $valueDateTime->diff($zeroDateTime);
        } elseif (strncmp($value, 'P-', 2) === 0) {
            $interval = new DateInterval('P' . substr($value, 2));
            $isNegative = true;
        } else {
            $interval = new DateInterval($value);
            $isNegative = $interval->invert;
        }

        $parts = [];
        if ($interval->y > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 year} other{# years}}', ['delta' => $interval->y], $this->language);
        }
        if ($interval->m > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 month} other{# months}}', ['delta' => $interval->m], $this->language);
        }
        if ($interval->d > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 day} other{# days}}', ['delta' => $interval->d], $this->language);
        }
        if ($interval->h > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 hour} other{# hours}}', ['delta' => $interval->h], $this->language);
        }
        if ($interval->i > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 minute} other{# minutes}}', ['delta' => $interval->i], $this->language);
        }
        if ($interval->s > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 second} other{# seconds}}', ['delta' => $interval->s], $this->language);
        }
        if ($interval->s === 0 && empty($parts)) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 second} other{# seconds}}', ['delta' => $interval->s], $this->language);
            $isNegative = false;
        }

        return empty($parts) ? $this->nullDisplay : (($isNegative ? $negativeSign : '') . implode($implodeString, $parts));
    }


    // number formats


    /**
     * Formats the value as an integer number by removing any decimal digits without rounding.
     *
     * Since 2.0.16 numbers that are mispresented after normalization are formatted as strings using fallback function
     * without [PHP intl extension](https://www.php.net/manual/en/book.intl.php) support. For very big numbers it's
     * recommended to pass them as strings and not use scientific notation otherwise the output might be wrong.
     *
     * @param mixed $value the value to be formatted.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     */
    public function asInteger($value, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        $normalizedValue = $this->normalizeNumericValue($value);

        if ($this->isNormalizedValueMispresented($value, $normalizedValue)) {
            return $this->asIntegerStringFallback((string) $value);
        }

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, null, $options, $textOptions);
            $f->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
            if (($result = $f->format($normalizedValue, NumberFormatter::TYPE_INT64)) === false) {
                throw new InvalidArgumentException('Formatting integer value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }

            return $result;
        }

        return number_format((int) $normalizedValue, 0, $this->decimalSeparator, $this->thousandSeparator);
    }

    /**
     * Formats the value as a decimal number.
     *
     * Property [[decimalSeparator]] will be used to represent the decimal point. The
     * value is rounded automatically to the defined decimal digits.
     *
     * Since 2.0.16 numbers that are mispresented after normalization are formatted as strings using fallback function
     * without [PHP intl extension](https://www.php.net/manual/en/book.intl.php) support. For very big numbers it's
     * recommended to pass them as strings and not use scientific notation otherwise the output might be wrong.
     *
     * @param mixed $value the value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * If not given, the number of digits depends in the input value and is determined based on
     * `NumberFormatter::MIN_FRACTION_DIGITS` and `NumberFormatter::MAX_FRACTION_DIGITS`, which can be configured
     * using [[$numberFormatterOptions]].
     * If the PHP intl extension is not available, the default value is `2`.
     * If you want consistent behavior between environments where intl is available and not, you should explicitly
     * specify a value here.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @see decimalSeparator
     * @see thousandSeparator
     */
    public function asDecimal($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        $normalizedValue = $this->normalizeNumericValue($value);

        if ($this->isNormalizedValueMispresented($value, $normalizedValue)) {
            return $this->asDecimalStringFallback((string) $value, $decimals);
        }

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, $decimals, $options, $textOptions);
            if (($result = $f->format($normalizedValue)) === false) {
                throw new InvalidArgumentException('Formatting decimal value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }

            return $result;
        }

        if ($decimals === null) {
            $decimals = 2;
        }

        return number_format($normalizedValue, $decimals, $this->decimalSeparator, $this->thousandSeparator);
    }

    /**
     * Formats the value as a percent number with "%" sign.
     *
     * Since 2.0.16 numbers that are mispresented after normalization are formatted as strings using fallback function
     * without [PHP intl extension](https://www.php.net/manual/en/book.intl.php) support. For very big numbers it's
     * recommended to pass them as strings and not use scientific notation otherwise the output might be wrong.
     *
     * @param mixed $value the value to be formatted. It must be a factor e.g. `0.75` will result in `75%`.
     * @param int|null $decimals the number of digits after the decimal point.
     * If not given, the number of digits depends in the input value and is determined based on
     * `NumberFormatter::MIN_FRACTION_DIGITS` and `NumberFormatter::MAX_FRACTION_DIGITS`, which can be configured
     * using [[$numberFormatterOptions]].
     * If the PHP intl extension is not available, the default value is `0`.
     * If you want consistent behavior between environments where intl is available and not, you should explicitly
     * specify a value here.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     */
    public function asPercent($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        $normalizedValue = $this->normalizeNumericValue($value);

        if ($this->isNormalizedValueMispresented($value, $normalizedValue)) {
            return $this->asPercentStringFallback((string) $value, $decimals);
        }

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::PERCENT, $decimals, $options, $textOptions);
            if (($result = $f->format($normalizedValue)) === false) {
                throw new InvalidArgumentException('Formatting percent value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }

            return $result;
        }

        if ($decimals === null) {
            $decimals = 0;
        }

        $normalizedValue *= 100;
        return number_format($normalizedValue, $decimals, $this->decimalSeparator, $this->thousandSeparator) . '%';
    }

    /**
     * Formats the value as a scientific number.
     *
     * @param mixed $value the value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * If not given, the number of digits depends in the input value and is determined based on
     * `NumberFormatter::MIN_FRACTION_DIGITS` and `NumberFormatter::MAX_FRACTION_DIGITS`, which can be configured
     * using [[$numberFormatterOptions]].
     * If the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is not available, the default value
     * depends on your PHP configuration.
     * If you want consistent behavior between environments where intl is available and not, you should explicitly
     * specify a value here.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     */
    public function asScientific($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::SCIENTIFIC, $decimals, $options, $textOptions);
            if (($result = $f->format($value)) === false) {
                throw new InvalidArgumentException('Formatting scientific number value failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }

            return $result;
        }

        if ($decimals !== null) {
            return sprintf("%.{$decimals}E", $value);
        }

        return sprintf('%.E', $value);
    }

    /**
     * Formats the value as a currency number.
     *
     * This function does not require the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) to be installed
     * to work, but it is highly recommended to install it to get good formatting results.
     *
     * Since 2.0.16 numbers that are mispresented after normalization are formatted as strings using fallback function
     * without PHP intl extension support. For very big numbers it's recommended to pass them as strings and not use
     * scientific notation otherwise the output might be wrong.
     *
     * @param mixed $value the value to be formatted.
     * @param string|null $currency the 3-letter ISO 4217 currency code indicating the currency to use.
     * If null, [[currencyCode]] will be used.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException if no currency is given and [[currencyCode]] is not defined.
     */
    public function asCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        $normalizedValue = $this->normalizeNumericValue($value);

        if ($this->isNormalizedValueMispresented($value, $normalizedValue)) {
            return $this->asCurrencyStringFallback((string) $value, $currency);
        }

        if ($this->_intlLoaded) {
            $currency = $currency ?: $this->currencyCode;
            // currency code must be set before fraction digits
            // https://www.php.net/manual/en/numberformatter.formatcurrency.php#114376
            if ($currency && !isset($textOptions[NumberFormatter::CURRENCY_CODE])) {
                $textOptions[NumberFormatter::CURRENCY_CODE] = $currency;
            }
            $formatter = $this->createNumberFormatter(NumberFormatter::CURRENCY, null, $options, $textOptions);
            if ($currency === null) {
                $result = $formatter->format($normalizedValue);
            } else {
                $result = $formatter->formatCurrency($normalizedValue, $currency);
            }
            if ($result === false) {
                throw new InvalidArgumentException('Formatting currency value failed: ' . $formatter->getErrorCode() . ' ' . $formatter->getErrorMessage());
            }

            return $result;
        }

        if ($currency === null) {
            if ($this->currencyCode === null) {
                throw new InvalidConfigException('The default currency code for the formatter is not defined and the php intl extension is not installed which could take the default currency from the locale.');
            }
            $currency = $this->currencyCode;
        }

        return $currency . ' ' . $this->asDecimal($normalizedValue, 2, $options, $textOptions);
    }

    /**
     * Formats the value as a number spellout.
     *
     * This function requires the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) to be installed.
     *
     * This formatter does not work well with very big numbers.
     *
     * @param mixed $value the value to be formatted
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is not available.
     */
    public function asSpellout($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::SPELLOUT);
            if (($result = $f->format($value)) === false) {
                throw new InvalidArgumentException('Formatting number as spellout failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }

            return $result;
        }

        throw new InvalidConfigException('Format as Spellout is only supported when PHP intl extension is installed.');
    }

    /**
     * Formats the value as a ordinal value of a number.
     *
     * This function requires the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) to be installed.
     *
     * This formatter does not work well with very big numbers.
     *
     * @param mixed $value the value to be formatted
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when the [PHP intl extension](https://www.php.net/manual/en/book.intl.php) is not available.
     */
    public function asOrdinal($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::ORDINAL);
            if (($result = $f->format($value)) === false) {
                throw new InvalidArgumentException('Formatting number as ordinal failed: ' . $f->getErrorCode() . ' ' . $f->getErrorMessage());
            }

            return $result;
        }

        throw new InvalidConfigException('Format as Ordinal is only supported when PHP intl extension is installed.');
    }

    /**
     * Formats the value in bytes as a size in human readable form for example `12 kB`.
     *
     * This is the short form of [[asSize]].
     *
     * If [[sizeFormatBase]] is 1024, [binary prefixes](https://en.wikipedia.org/wiki/Binary_prefix) (e.g. kibibyte/KiB, mebibyte/MiB, ...)
     * are used in the formatting result.
     *
     * @param string|int|float|null $value value in bytes to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @see sizeFormatBase
     * @see asSize
     */
    public function asShortSize($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        list($params, $position) = $this->formatNumber($value, $decimals, 4, $this->sizeFormatBase, $options, $textOptions);

        if ($this->sizeFormatBase == 1024) {
            switch ($position) {
                case 0:
                    return Yii::t('yii', '{nFormatted} B', $params, $this->language);
                case 1:
                    return Yii::t('yii', '{nFormatted} KiB', $params, $this->language);
                case 2:
                    return Yii::t('yii', '{nFormatted} MiB', $params, $this->language);
                case 3:
                    return Yii::t('yii', '{nFormatted} GiB', $params, $this->language);
                case 4:
                    return Yii::t('yii', '{nFormatted} TiB', $params, $this->language);
                default:
                    return Yii::t('yii', '{nFormatted} PiB', $params, $this->language);
            }
        } else {
            switch ($position) {
                case 0:
                    return Yii::t('yii', '{nFormatted} B', $params, $this->language);
                case 1:
                    return Yii::t('yii', '{nFormatted} kB', $params, $this->language);
                case 2:
                    return Yii::t('yii', '{nFormatted} MB', $params, $this->language);
                case 3:
                    return Yii::t('yii', '{nFormatted} GB', $params, $this->language);
                case 4:
                    return Yii::t('yii', '{nFormatted} TB', $params, $this->language);
                default:
                    return Yii::t('yii', '{nFormatted} PB', $params, $this->language);
            }
        }
    }

    /**
     * Formats the value in bytes as a size in human readable form, for example `12 kilobytes`.
     *
     * If [[sizeFormatBase]] is 1024, [binary prefixes](https://en.wikipedia.org/wiki/Binary_prefix) (e.g. kibibyte/KiB, mebibyte/MiB, ...)
     * are used in the formatting result.
     *
     * @param string|int|float|null $value value in bytes to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @see sizeFormatBase
     * @see asShortSize
     */
    public function asSize($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        list($params, $position) = $this->formatNumber($value, $decimals, 4, $this->sizeFormatBase, $options, $textOptions);

        if ($this->sizeFormatBase == 1024) {
            switch ($position) {
                case 0:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->language);
                case 1:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{kibibyte} other{kibibytes}}', $params, $this->language);
                case 2:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{mebibyte} other{mebibytes}}', $params, $this->language);
                case 3:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{gibibyte} other{gibibytes}}', $params, $this->language);
                case 4:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{tebibyte} other{tebibytes}}', $params, $this->language);
                default:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{pebibyte} other{pebibytes}}', $params, $this->language);
            }
        } else {
            switch ($position) {
                case 0:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->language);
                case 1:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{kilobyte} other{kilobytes}}', $params, $this->language);
                case 2:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{megabyte} other{megabytes}}', $params, $this->language);
                case 3:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{gigabyte} other{gigabytes}}', $params, $this->language);
                case 4:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{terabyte} other{terabytes}}', $params, $this->language);
                default:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{petabyte} other{petabytes}}', $params, $this->language);
            }
        }
    }

    /**
     * Formats the value as a length in human readable form for example `12 meters`.
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $numberOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @see asLength
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asLength($value, $decimals = null, $numberOptions = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_LENGTH, self::FORMAT_WIDTH_LONG, $value, $decimals, $numberOptions, $textOptions);
    }

    /**
     * Formats the value as a length in human readable form for example `12 m`.
     * This is the short form of [[asLength]].
     *
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @see asLength
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asShortLength($value, $decimals = null, $options = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_LENGTH, self::FORMAT_WIDTH_SHORT, $value, $decimals, $options, $textOptions);
    }

    /**
     * Formats the value as a weight in human readable form for example `12 kilograms`.
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asWeight($value, $decimals = null, $options = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_WEIGHT, self::FORMAT_WIDTH_LONG, $value, $decimals, $options, $textOptions);
    }

    /**
     * Formats the value as a weight in human readable form for example `12 kg`.
     * This is the short form of [[asWeight]].
     *
     * Check properties [[baseUnits]] if you need to change unit of value as the multiplier
     * of the smallest unit and [[systemOfUnits]] to switch between [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]].
     *
     * @param float|int $value value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @throws InvalidConfigException when INTL is not installed or does not contain required information.
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asShortWeight($value, $decimals = null, $options = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_WEIGHT, self::FORMAT_WIDTH_SHORT, $value, $decimals, $options, $textOptions);
    }

    /**
     * @param string $unitType one of [[UNIT_WEIGHT]], [[UNIT_LENGTH]]
     * @param string $unitFormat one of [[FORMAT_WIDTH_SHORT]], [[FORMAT_WIDTH_LONG]]
     * @param float|int|null $value to be formatted
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string
     * @throws InvalidConfigException when INTL is not installed or does not contain required information
     */
    private function formatUnit($unitType, $unitFormat, $value, $decimals, $options, $textOptions)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        $multipliers = array_values($this->measureUnits[$unitType][$this->systemOfUnits]);

        list($params, $position) = $this->formatNumber(
            $this->normalizeNumericValue($value) * $this->baseUnits[$unitType][$this->systemOfUnits],
            $decimals,
            null,
            $multipliers,
            $options,
            $textOptions
        );

        $message = $this->getUnitMessage($unitType, $unitFormat, $this->systemOfUnits, $position);

        return (new \MessageFormatter($this->locale, $message))->format([
            '0' => $params['nFormatted'],
            'n' => $params['n'],
        ]);
    }

    /**
     * @param string $unitType one of [[UNIT_WEIGHT]], [[UNIT_LENGTH]]
     * @param string $unitFormat one of [[FORMAT_WIDTH_SHORT]], [[FORMAT_WIDTH_LONG]]
     * @param string|null $system either [[UNIT_SYSTEM_METRIC]] or [[UNIT_SYSTEM_IMPERIAL]]. When `null`, property [[systemOfUnits]] will be used.
     * @param int $position internal position of size unit
     * @return string
     * @throws InvalidConfigException when INTL is not installed or does not contain required information
     */
    private function getUnitMessage($unitType, $unitFormat, $system, $position)
    {
        if (isset($this->_unitMessages[$unitType][$unitFormat][$system][$position])) {
            return $this->_unitMessages[$unitType][$unitFormat][$system][$position];
        }
        if (!$this->_intlLoaded) {
            throw new InvalidConfigException('Format of ' . $unitType . ' is only supported when PHP intl extension is installed.');
        }

        if ($this->_resourceBundle === null) {
            try {
                $this->_resourceBundle = new \ResourceBundle($this->locale, 'ICUDATA-unit');
            } catch (\IntlException $e) {
                throw new InvalidConfigException('Current ICU data does not contain information about measure units. Check system requirements.');
            }
        }
        $unitNames = array_keys($this->measureUnits[$unitType][$system]);
        $bundleKey = 'units' . ($unitFormat === self::FORMAT_WIDTH_SHORT ? 'Short' : '');

        $unitBundle = $this->_resourceBundle[$bundleKey][$unitType][$unitNames[$position]];
        if ($unitBundle === null) {
            throw new InvalidConfigException(
                'Current ICU data version does not contain information about unit type "' . $unitType
                . '" and unit measure "' . $unitNames[$position] . '". Check system requirements.'
            );
        }

        $message = [];
        foreach ($unitBundle as $key => $value) {
            if ($key === 'dnam') {
                continue;
            }
            $message[] = "$key{{$value}}";
        }

        return $this->_unitMessages[$unitType][$unitFormat][$system][$position] = '{n, plural, ' . implode(' ', $message) . '}';
    }

    /**
     * Given the value in bytes formats number part of the human readable form.
     *
     * @param string|int|float $value value in bytes to be formatted.
     * @param int|null $decimals the number of digits after the decimal point
     * @param int $maxPosition maximum internal position of size unit, ignored if $formatBase is an array
     * @param array|int $formatBase the base at which each next unit is calculated, either 1000 or 1024, or an array
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return array [parameters for Yii::t containing formatted number, internal position of size unit]
     * @throws InvalidArgumentException if the input value is not numeric or the formatting failed.
     * @since 2.0.32
     */
    protected function formatNumber($value, $decimals, $maxPosition, $formatBase, $options, $textOptions)
    {
        $value = $this->normalizeNumericValue($value);

        $position = 0;
        if (is_array($formatBase)) {
            $maxPosition = count($formatBase) - 1;
        }
        do {
            if (is_array($formatBase)) {
                if (!isset($formatBase[$position + 1])) {
                    break;
                }

                if (abs($value) < $formatBase[$position + 1]) {
                    break;
                }
            } else {
                if (abs($value) < $formatBase) {
                    break;
                }
                $value /= $formatBase;
            }
            $position++;
        } while ($position < $maxPosition + 1);

        if (is_array($formatBase) && $position !== 0) {
            $value /= $formatBase[$position];
        }

        // no decimals for smallest unit
        if ($position === 0) {
            $decimals = 0;
        } elseif ($decimals !== null) {
            $value = round($value, $decimals);
        }
        // disable grouping for edge cases like 1023 to get 1023 B instead of 1,023 B
        $oldThousandSeparator = $this->thousandSeparator;
        $this->thousandSeparator = '';
        if ($this->_intlLoaded && !isset($options[NumberFormatter::GROUPING_USED])) {
            $options[NumberFormatter::GROUPING_USED] = 0;
        }
        // format the size value
        $params = [
            // this is the unformatted number used for the plural rule
            // abs() to make sure the plural rules work correctly on negative numbers, intl does not cover this
            // https://english.stackexchange.com/questions/9735/is-1-followed-by-a-singular-or-plural-noun
            'n' => abs($value),
            // this is the formatted number used for display
            'nFormatted' => $this->asDecimal($value, $decimals, $options, $textOptions),
        ];
        $this->thousandSeparator = $oldThousandSeparator;

        return [$params, $position];
    }

    /**
     * Normalizes a numeric input value.
     *
     * - everything [empty](https://www.php.net/manual/en/function.empty.php) will result in `0`
     * - a [numeric](https://www.php.net/manual/en/function.is-numeric.php) string will be casted to float
     * - everything else will be returned if it is [numeric](https://www.php.net/manual/en/function.is-numeric.php),
     *   otherwise an exception is thrown.
     *
     * @param mixed $value the input value
     * @return float|int the normalized number value
     * @throws InvalidArgumentException if the input value is not numeric.
     */
    protected function normalizeNumericValue($value)
    {
        if (empty($value)) {
            return 0;
        }
        if (is_string($value) && is_numeric($value)) {
            $value = (float) $value;
        }
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("'$value' is not a numeric value.");
        }

        return $value;
    }

    /**
     * Creates a number formatter based on the given type and format.
     *
     * You may override this method to create a number formatter based on patterns.
     *
     * @param int $style the type of the number formatter.
     * Values: NumberFormatter::DECIMAL, ::CURRENCY, ::PERCENT, ::SCIENTIFIC, ::SPELLOUT, ::ORDINAL
     * ::DURATION, ::PATTERN_RULEBASED, ::DEFAULT_STYLE, ::IGNORE
     * @param int|null $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return NumberFormatter the created formatter instance
     */
    protected function createNumberFormatter($style, $decimals = null, $options = [], $textOptions = [])
    {
        $formatter = new NumberFormatter($this->locale, $style);

        // set text attributes
        foreach ($this->numberFormatterTextOptions as $attribute => $value) {
            $this->setFormatterTextAttribute($formatter, $attribute, $value, 'numberFormatterTextOptions', 'numberFormatterOptions');
        }
        foreach ($textOptions as $attribute => $value) {
            $this->setFormatterTextAttribute($formatter, $attribute, $value, '$textOptions', '$options');
        }

        // set attributes
        foreach ($this->numberFormatterOptions as $attribute => $value) {
            $this->setFormatterIntAttribute($formatter, $attribute, $value, 'numberFormatterOptions', 'numberFormatterTextOptions');
        }
        foreach ($options as $attribute => $value) {
            $this->setFormatterIntAttribute($formatter, $attribute, $value, '$options', '$textOptions');
        }
        if ($decimals !== null) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        }

        // set symbols
        if ($this->decimalSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $this->decimalSeparator);
        }
        if ($this->currencyDecimalSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::MONETARY_SEPARATOR_SYMBOL, $this->currencyDecimalSeparator);
        }
        if ($this->thousandSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
            $formatter->setSymbol(NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
        }
        foreach ($this->numberFormatterSymbols as $symbol => $value) {
            $this->setFormatterSymbol($formatter, $symbol, $value, 'numberFormatterSymbols');
        }

        return $formatter;
    }

    /**
     * @param NumberFormatter $formatter
     * @param mixed $attribute
     * @param mixed $value
     * @param string $source
     * @param string $alternative
     */
    private function setFormatterTextAttribute($formatter, $attribute, $value, $source, $alternative)
    {
        if (!is_int($attribute)) {
            throw new InvalidArgumentException(
                "The $source array keys must be integers recognizable by NumberFormatter::setTextAttribute(). \""
                . gettype($attribute) . '" provided instead.'
            );
        }
        if (!is_string($value)) {
            if (is_int($value)) {
                throw new InvalidArgumentException(
                    "The $source array values must be strings. Did you mean to use $alternative?"
                );
            }
            throw new InvalidArgumentException(
                "The $source array values must be strings. \"" . gettype($value) . '" provided instead.'
            );
        }
        $formatter->setTextAttribute($attribute, $value);
    }

    /**
     * @param NumberFormatter $formatter
     * @param mixed $symbol
     * @param mixed $value
     * @param string $source
     */
    private function setFormatterSymbol($formatter, $symbol, $value, $source)
    {
        if (!is_int($symbol)) {
            throw new InvalidArgumentException(
                "The $source array keys must be integers recognizable by NumberFormatter::setSymbol(). \""
                . gettype($symbol) . '" provided instead.'
            );
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                "The $source array values must be strings. \"" . gettype($value) . '" provided instead.'
            );
        }
        $formatter->setSymbol($symbol, $value);
    }

    /**
     * @param NumberFormatter $formatter
     * @param mixed $attribute
     * @param mixed $value
     * @param string $source
     * @param string $alternative
     */
    private function setFormatterIntAttribute($formatter, $attribute, $value, $source, $alternative)
    {
        if (!is_int($attribute)) {
            throw new InvalidArgumentException(
                "The $source array keys must be integers recognizable by NumberFormatter::setAttribute(). \""
                . gettype($attribute) . '" provided instead.'
            );
        }
        if (!is_int($value)) {
            if (is_string($value)) {
                throw new InvalidArgumentException(
                    "The $source array values must be integers. Did you mean to use $alternative?"
                );
            }
            throw new InvalidArgumentException(
                "The $source array values must be integers. \"" . gettype($value) . '" provided instead.'
            );
        }
        $formatter->setAttribute($attribute, $value);
    }

    /**
     * Checks if string representations of given value and its normalized version are different.
     * @param string|float|int $value
     * @param float|int $normalizedValue
     * @return bool
     * @since 2.0.16
     */
    protected function isNormalizedValueMispresented($value, $normalizedValue)
    {
        if (empty($value)) {
            $value = 0;
        }

        return (string) $normalizedValue !== $this->normalizeNumericStringValue((string) $value);
    }

    /**
     * Normalizes a numeric string value.
     * @param string $value
     * @return string the normalized number value as a string
     * @since 2.0.16
     */
    protected function normalizeNumericStringValue($value)
    {
        $powerPosition = strrpos($value, 'E');
        if ($powerPosition !== false) {
            $valuePart = substr($value, 0, $powerPosition);
            $powerPart = substr($value, $powerPosition + 1);
        } else {
            $powerPart = null;
            $valuePart = $value;
        }

        $separatorPosition = strrpos($valuePart, '.');

        if ($separatorPosition !== false) {
            $integerPart = substr($valuePart, 0, $separatorPosition);
            $fractionalPart = substr($valuePart, $separatorPosition + 1);
        } else {
            $integerPart = $valuePart;
            $fractionalPart = null;
        }

        // truncate insignificant zeros, keep minus
        $integerPart = preg_replace('/^\+?(-?)0*(\d+)$/', '$1$2', $integerPart);
        // for zeros only leave one zero, keep minus
        $integerPart = preg_replace('/^\+?(-?)0*$/', '${1}0', $integerPart);

        if ($fractionalPart !== null) {
            // truncate insignificant zeros
            $fractionalPart = rtrim($fractionalPart, '0');

            if (empty($fractionalPart)) {
                $fractionalPart = $powerPart !== null ? '0' : null;
            }
        }

        $normalizedValue = $integerPart;
        if ($fractionalPart !== null) {
            $normalizedValue .= '.' . $fractionalPart;
        } elseif ($normalizedValue === '-0') {
            $normalizedValue = '0';
        }

        if ($powerPart !== null) {
            $normalizedValue .= 'E' . $powerPart;
        }

        return $normalizedValue;
    }

    /**
     * Fallback for formatting value as a decimal number.
     *
     * Property [[decimalSeparator]] will be used to represent the decimal point. The value is rounded automatically
     * to the defined decimal digits.
     *
     * @param string|int|float $value the value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point. The default value is `2`.
     * @return string the formatted result.
     * @see decimalSeparator
     * @see thousandSeparator
     * @since 2.0.16
     */
    protected function asDecimalStringFallback($value, $decimals = 2)
    {
        if (empty($value)) {
            $value = 0;
        }

        $value = $this->normalizeNumericStringValue((string) $value);

        $separatorPosition = strrpos($value, '.');

        if ($separatorPosition !== false) {
            $integerPart = substr($value, 0, $separatorPosition);
            $fractionalPart = substr($value, $separatorPosition + 1);
        } else {
            $integerPart = $value;
            $fractionalPart = null;
        }

        $decimalOutput = '';

        if ($decimals === null) {
            $decimals = 2;
        }

        $carry = 0;

        if ($decimals > 0) {
            $decimalSeparator = $this->decimalSeparator;
            if ($this->decimalSeparator === null) {
                $decimalSeparator = '.';
            }

            if ($fractionalPart === null) {
                $fractionalPart = str_repeat('0', $decimals);
            } elseif (strlen($fractionalPart) > $decimals) {
                $cursor = $decimals;

                // checking if fractional part must be rounded
                if ((int) substr($fractionalPart, $cursor, 1) >= 5) {
                    while (--$cursor >= 0) {
                        $carry = 0;

                        $oneUp = (int) substr($fractionalPart, $cursor, 1) + 1;
                        if ($oneUp === 10) {
                            $oneUp = 0;
                            $carry = 1;
                        }

                        $fractionalPart = substr($fractionalPart, 0, $cursor) . $oneUp . substr($fractionalPart, $cursor + 1);

                        if ($carry === 0) {
                            break;
                        }
                    }
                }

                $fractionalPart = substr($fractionalPart, 0, $decimals);
            } elseif (strlen($fractionalPart) < $decimals) {
                $fractionalPart = str_pad($fractionalPart, $decimals, '0');
            }

            $decimalOutput .= $decimalSeparator . $fractionalPart;
        }

        // checking if integer part must be rounded
        if ($carry || ($decimals === 0 && $fractionalPart !== null && (int) substr($fractionalPart, 0, 1) >= 5)) {
            $integerPartLength = strlen($integerPart);
            $cursor = 0;

            while (++$cursor <= $integerPartLength) {
                $carry = 0;

                $oneUp = (int) substr($integerPart, -$cursor, 1) + 1;
                if ($oneUp === 10) {
                    $oneUp = 0;
                    $carry = 1;
                }

                $integerPart = substr($integerPart, 0, -$cursor) . $oneUp . substr($integerPart, $integerPartLength - $cursor + 1);

                if ($carry === 0) {
                    break;
                }
            }
            if ($carry === 1) {
                $integerPart = '1' . $integerPart;
            }
        }

        if (strlen($integerPart) > 3) {
            $thousandSeparator = $this->thousandSeparator;
            if ($thousandSeparator === null) {
                $thousandSeparator = ',';
            }

            $integerPart = strrev(implode(',', str_split(strrev($integerPart), 3)));
            if ($thousandSeparator !== ',') {
                $integerPart = str_replace(',', $thousandSeparator, $integerPart);
            }
        }

        return $integerPart . $decimalOutput;
    }

    /**
     * Fallback for formatting value as an integer number by removing any decimal digits without rounding.
     *
     * @param string|int|float $value the value to be formatted.
     * @return string the formatted result.
     * @since 2.0.16
     */
    protected function asIntegerStringFallback($value)
    {
        if (empty($value)) {
            $value = 0;
        }

        $value = $this->normalizeNumericStringValue((string) $value);
        $separatorPosition = strrpos($value, '.');

        if ($separatorPosition !== false) {
            $integerPart = substr($value, 0, $separatorPosition);
        } else {
            $integerPart = $value;
        }

        return $this->asDecimalStringFallback($integerPart, 0);
    }

    /**
     * Fallback for formatting value as a percent number with "%" sign.
     *
     * Property [[decimalSeparator]] will be used to represent the decimal point. The value is rounded automatically
     * to the defined decimal digits.
     *
     * @param string|int|float $value the value to be formatted.
     * @param int|null $decimals the number of digits after the decimal point. The default value is `0`.
     * @return string the formatted result.
     * @since 2.0.16
     */
    protected function asPercentStringFallback($value, $decimals = null)
    {
        if (empty($value)) {
            $value = 0;
        }

        if ($decimals === null) {
            $decimals = 0;
        }

        $value = $this->normalizeNumericStringValue((string) $value);
        $separatorPosition = strrpos($value, '.');

        if ($separatorPosition !== false) {
            $integerPart = substr($value, 0, $separatorPosition);
            $fractionalPart = str_pad(substr($value, $separatorPosition + 1), 2, '0');

            $integerPart .= substr($fractionalPart, 0, 2);
            $fractionalPart = substr($fractionalPart, 2);

            if ($fractionalPart === '') {
                $multipliedValue = $integerPart;
            } else {
                $multipliedValue = $integerPart . '.' . $fractionalPart;
            }
        } else {
            $multipliedValue = $value . '00';
        }

        return $this->asDecimalStringFallback($multipliedValue, $decimals) . '%';
    }

    /**
     * Fallback for formatting value as a currency number.
     *
     * @param string|int|float $value the value to be formatted.
     * @param string|null $currency the 3-letter ISO 4217 currency code indicating the currency to use.
     * If null, [[currencyCode]] will be used.
     * @return string the formatted result.
     * @throws InvalidConfigException if no currency is given and [[currencyCode]] is not defined.
     * @since 2.0.16
     */
    protected function asCurrencyStringFallback($value, $currency = null)
    {
        if ($currency === null) {
            if ($this->currencyCode === null) {
                throw new InvalidConfigException('The default currency code for the formatter is not defined.');
            }
            $currency = $this->currencyCode;
        }

        return $currency . ' ' . $this->asDecimalStringFallback($value, 2);
    }
}
