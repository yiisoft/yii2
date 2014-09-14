<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\i18n;

use DateTime;
use DateTimeInterface;
use IntlDateFormatter;
use NumberFormatter;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\FormatConverter;
use yii\helpers\HtmlPurifier;
use yii\helpers\Html;

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
 * the [PHP intl extension](http://php.net/manual/en/book.intl.php) has to be installed.
 * Most of the methods however work also if the PHP intl extension is not installed by providing
 * a fallback implementation. Without intl month and day names are in English only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Enrica Ruedin <e.ruedin@guggach.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class Formatter extends Component
{
    /**
     * @var string the text to be displayed when formatting a `null` value.
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
     * @var string the locale ID that is used to localize the date and number formatting.
     * For number and date formatting this is only effective when the
     * [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
     * If not set, [[\yii\base\Application::language]] will be used.
     */
    public $locale;
    /**
     * @var string the timezone to use for formatting time and date values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     */
    public $timeZone;
    /**
     * @var string the default format string to be used to format a [[asDate()|date]].
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     *
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/de/function.date.php)-function.
     */
    public $dateFormat = 'medium';
    /**
     * @var string the default format string to be used to format a [[asTime()|time]].
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     *
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/de/function.date.php)-function.
     */
    public $timeFormat = 'medium';
    /**
     * @var string the default format string to be used to format a [[asDateTime()|date and time]].
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     *
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/de/function.date.php)-function.
     */
    public $datetimeFormat = 'medium';
    /**
     * @var string the character displayed as the decimal point when formatting a number.
     * If not set, the decimal separator corresponding to [[locale]] will be used.
     * If [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available, the default value is '.'.
     */
    public $decimalSeparator;
    /**
     * @var string the character displayed as the thousands separator (also called grouping separator) character when formatting a number.
     * If not set, the thousand separator corresponding to [[locale]] will be used.
     * If [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available, the default value is ','.
     */
    public $thousandSeparator;
    /**
     * @var array a list of name value pairs that are passed to the
     * intl [Numberformatter::setAttribute()](http://php.net/manual/en/numberformatter.setattribute.php) method of all
     * the number formatter objects created by [[createNumberFormatter()]].
     * This property takes only effect if the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
     *
     * Please refer to the [PHP manual](http://php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatattribute)
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
     * intl [Numberformatter::setTextAttribute()](http://php.net/manual/en/numberformatter.settextattribute.php) method of all
     * the number formatter objects created by [[createNumberFormatter()]].
     * This property takes only effect if the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
     *
     * Please refer to the [PHP manual](http://php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformattextattribute)
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
     * @var string the 3-letter ISO 4217 currency code indicating the default currency to use for [[asCurrency]].
     * If not set, the currency code corresponding to [[locale]] will be used.
     * Note that in this case the [[locale]] has to be specified with a country code, e.g. `en-US` otherwise it
     * is not possible to determine the default currency.
     */
    public $currencyCode;
    /**
     * @var array the base at which a kilobyte is calculated (1000 or 1024 bytes per kilobyte), used by [[asSize]] and [[asShortSize]].
     * Defaults to 1024.
     */
    public $sizeFormatBase = 1024;

    /**
     * @var boolean whether the [PHP intl extension](http://php.net/manual/en/book.intl.php) is loaded.
     */
    private $_intlLoaded = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->timeZone === null) {
            $this->timeZone = Yii::$app->timeZone;
        }
        if ($this->locale === null) {
            $this->locale = Yii::$app->language;
        }
        if ($this->booleanFormat === null) {
            $this->booleanFormat = [Yii::t('yii', 'No', [], $this->locale), Yii::t('yii', 'Yes', [], $this->locale)];
        }
        if ($this->nullDisplay === null) {
            $this->nullDisplay = '<span class="not-set">' . Yii::t('yii', '(not set)', [], $this->locale) . '</span>';
        }
        $this->_intlLoaded = extension_loaded('intl');
        if (!$this->_intlLoaded) {
            $this->decimalSeparator = '.';
            $this->thousandSeparator = ',';
        }
    }

    /**
     * Formats the value based on the given format type.
     * This method will call one of the "as" methods available in this class to do the formatting.
     * For type "xyz", the method "asXyz" will be used. For example, if the format is "html",
     * then [[asHtml()]] will be used. Format names are case insensitive.
     * @param mixed $value the value to be formatted.
     * @param string|array $format the format of the value, e.g., "html", "text". To specify additional
     * parameters of the formatting method, you may use an array. The first element of the array
     * specifies the format name, while the rest of the elements will be used as the parameters to the formatting
     * method. For example, a format of `['date', 'Y-m-d']` will cause the invocation of `asDate($value, 'Y-m-d')`.
     * @return string the formatting result.
     * @throws InvalidParamException if the format type is not supported by this class.
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
        if ($this->hasMethod($method)) {
            return call_user_func_array([$this, $method], $params);
        } else {
            throw new InvalidParamException("Unknown format type: $format");
        }
    }


    // simple formats


    /**
     * Formats the value as is without any formatting.
     * This method simply returns back the parameter without any format.
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
     * @param mixed $value the value to be formatted.
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
     * @param mixed $value the value to be formatted.
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
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asParagraphs($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return str_replace('<p></p>', '', '<p>' . preg_replace('/\R{2,}/', "</p>\n<p>", Html::encode($value)) . '</p>');
    }

    /**
     * Formats the value as HTML text.
     * The value will be purified using [[HtmlPurifier]] to avoid XSS attacks.
     * Use [[asRaw()]] if you do not want any purification of the value.
     * @param mixed $value the value to be formatted.
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
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
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
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
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
     * @param mixed $value the value to be formatted.
     * @return string the formatted result.
     */
    public function asUrl($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $url = $value;
        if (strpos($url, '://') === false) {
            $url = 'http://' . $url;
        }

        return Html::a(Html::encode($value), $url);
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
     * @param integer|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()`
     * - a PHP DateTime object
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[dateFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/de/function.date.php)-function.
     *
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @return string the formatted result.
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
     * @param integer|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()`
     * - a PHP DateTime object
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[timeFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/de/function.date.php)-function.
     *
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @return string the formatted result.
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
     * @param integer|string|DateTime $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()`
     * - a PHP DateTime object
     *
     * @param string $format the format used to convert the value into a date string.
     * If null, [[dateFormat]] will be used.
     *
     * This can be "short", "medium", "long", or "full", which represents a preset format of different lengths.
     * It can also be a custom format as specified in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the
     * PHP [date()](http://php.net/manual/de/function.date.php)-function.
     *
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     * @throws InvalidConfigException if the date format is invalid.
     * @return string the formatted result.
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
        'short'  => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long'   => 1, // IntlDateFormatter::LONG,
        'full'   => 0, // IntlDateFormatter::FULL,
    ];

    /**
     * @param integer $value normalized datetime value
     * @param string $format the format used to convert the value into a date string.
     * @param string $type 'date', 'time', or 'datetime'.
     * @throws InvalidConfigException if the date format is invalid.
     * @return string the formatted result.
     */
    private function formatDateTimeValue($value, $format, $type)
    {
        $value = $this->normalizeDatetimeValue($value);
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($this->_intlLoaded) {
            if (strpos($format, 'php:') === 0) {
                $format = FormatConverter::convertDatePhpToIcu(substr($format, 4));
            }
            if (isset($this->_dateFormats[$format])) {
                if ($type === 'date') {
                    $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE, $this->timeZone);
                } elseif ($type === 'time') {
                    $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormats[$format], $this->timeZone);
                } else {
                    $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], $this->_dateFormats[$format], $this->timeZone);
                }
            } else {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $this->timeZone, null, $format);
            }
            if ($formatter === null) {
                throw new InvalidConfigException(intl_get_error_message());
            }
            return $formatter->format($value);
        } else {
            if (strpos($format, 'php:') === 0) {
                $format = substr($format, 4);
            } else {
                $format = FormatConverter::convertDateIcuToPhp($format, $type, $this->locale);
            }
            $date = new DateTime(null, new \DateTimeZone($this->timeZone));
            $date->setTimestamp($value);
            return $date->format($format);
        }
    }

    /**
     * Normalizes the given datetime value as a UNIX timestamp that can be taken by various date/time formatting methods.
     *
     * @param mixed $value the datetime value to be normalized.
     * @return float the normalized datetime value (int64)
     */
    protected function normalizeDatetimeValue($value)
    {
        if ($value === null) {
            return null;
        } elseif (is_string($value)) {
            if (is_numeric($value) || $value === '') {
                $value = (double)$value;
            } else {
                $date = new DateTime($value);
                $value = (double)$date->format('U');
            }
            return $value;

        } elseif ($value instanceof DateTime || $value instanceof DateTimeInterface) {
            return (double)$value->format('U');
        } else {
            return (double)$value;
        }
    }

    /**
     * Formats a date, time or datetime in a float number as UNIX timestamp (seconds since 01-01-1970).
     * @param integer|string|DateTime|\DateInterval $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()` or that can be passed to a DateInterval constructor.
     * - a PHP DateTime object
     * - a PHP DateInterval object (a positive time interval will refer to the past, a negative one to the future)
     *
     * @return string the formatted result.
     */
    public function asTimestamp($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return number_format($this->normalizeDatetimeValue($value), 0, '.', '');
    }

    /**
     * Formats the value as the time interval between a date and now in human readable form.
     *
     * @param integer|string|DateTime|\DateInterval $value the value to be formatted. The following
     * types of value are supported:
     *
     * - an integer representing a UNIX timestamp
     * - a string that can be parsed into a UNIX timestamp via `strtotime()` or that can be passed to a DateInterval constructor.
     * - a PHP DateTime object
     * - a PHP DateInterval object (a positive time interval will refer to the past, a negative one to the future)
     *
     * @param integer|string|DateTime|\DateInterval $referenceTime if specified the value is used instead of `now`.
     * @return string the formatted result.
     * @throws InvalidParamException if the input value can not be evaluated as a date value.
     */
    public function asRelativeTime($value, $referenceTime = null)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        if ($value instanceof \DateInterval) {
            $interval = $value;
        } else {
            $timestamp = $this->normalizeDatetimeValue($value);

            if ($timestamp === false) {
                // $value is not a valid date/time value, so we try
                // to create a DateInterval with it
                try {
                    $interval = new \DateInterval($value);
                } catch (\Exception $e) {
                    // invalid date/time and invalid interval
                    return $this->nullDisplay;
                }
            } else {
                $timezone = new \DateTimeZone($this->timeZone);

                if ($referenceTime === null) {
                    $dateNow = new DateTime('now', $timezone);
                } else {
                    $referenceTime = $this->normalizeDatetimeValue($referenceTime);
                    $dateNow = new DateTime(null, $timezone);
                    $dateNow->setTimestamp($referenceTime);
                }

                $dateThen = new DateTime(null, $timezone);
                $dateThen->setTimestamp($timestamp);

                $interval = $dateThen->diff($dateNow);
            }
        }

        if ($interval->invert) {
            if ($interval->y >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a year} other{# years}}', ['delta' => $interval->y], $this->locale);
            }
            if ($interval->m >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a month} other{# months}}', ['delta' => $interval->m], $this->locale);
            }
            if ($interval->d >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a day} other{# days}}', ['delta' => $interval->d], $this->locale);
            }
            if ($interval->h >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{an hour} other{# hours}}', ['delta' => $interval->h], $this->locale);
            }
            if ($interval->i >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a minute} other{# minutes}}', ['delta' => $interval->i], $this->locale);
            }

            return Yii::t('yii', 'in {delta, plural, =1{a second} other{# seconds}}', ['delta' => $interval->s], $this->locale);
        } else {
            if ($interval->y >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a year} other{# years}} ago', ['delta' => $interval->y], $this->locale);
            }
            if ($interval->m >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a month} other{# months}} ago', ['delta' => $interval->m], $this->locale);
            }
            if ($interval->d >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a day} other{# days}} ago', ['delta' => $interval->d], $this->locale);
            }
            if ($interval->h >= 1) {
                return Yii::t('yii', '{delta, plural, =1{an hour} other{# hours}} ago', ['delta' => $interval->h], $this->locale);
            }
            if ($interval->i >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a minute} other{# minutes}} ago', ['delta' => $interval->i], $this->locale);
            }

            return Yii::t('yii', '{delta, plural, =1{a second} other{# seconds}} ago', ['delta' => $interval->s], $this->locale);
        }
    }


    // number formats


    /**
     * Formats the value as an integer number by removing any decimal digits without rounding.
     *
     * @param mixed $value the value to be formatted.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     */
    public function asInteger($value, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, null, $options, $textOptions);
            return $f->format($value, NumberFormatter::TYPE_INT64);
        } else {
            return number_format((int) $value, 0, $this->decimalSeparator, $this->thousandSeparator);
        }
    }

    /**
     * Formats the value as a decimal number.
     *
     * Property [[decimalSeparator]] will be used to represent the decimal point. The
     * value is rounded automatically to the defined decimal digits.
     *
     * @param mixed $value the value to be formatted.
     * @param integer $decimals the number of digits after the decimal point. If not given the number of digits is determined from the
     * [[locale]] and if the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available defaults to `2`.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     * @see decimalSeparator
     * @see thousandSeparator
     */
    public function asDecimal($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::DECIMAL, $decimals, $options, $textOptions);
            return $f->format($value);
        } else {
            if ($decimals === null){
                $decimals = 2;
            }
            return number_format($value, $decimals, $this->decimalSeparator, $this->thousandSeparator);
        }
    }


    /**
     * Formats the value as a percent number with "%" sign.
     *
     * @param mixed $value the value to be formatted. It must be a factor e.g. `0.75` will result in `75%`.
     * @param integer $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     */
    public function asPercent($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $f = $this->createNumberFormatter(NumberFormatter::PERCENT, $decimals, $options, $textOptions);
            return $f->format($value);
        } else {
            if ($decimals === null){
                $decimals = 0;
            }
            $value = $value * 100;
            return number_format($value, $decimals, $this->decimalSeparator, $this->thousandSeparator) . '%';
        }
    }

    /**
     * Formats the value as a scientific number.
     *
     * @param mixed $value the value to be formatted.
     * @param integer $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     */
    public function asScientific($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded){
            $f = $this->createNumberFormatter(NumberFormatter::SCIENTIFIC, $decimals, $options, $textOptions);
            return $f->format($value);
        } else {
            if ($decimals !== null) {
                return sprintf("%.{$decimals}E", $value);
            } else {
                return sprintf("%.E", $value);
            }
        }
    }

    /**
     * Formats the value as a currency number.
     *
     * This function does not requires the [PHP intl extension](http://php.net/manual/en/book.intl.php) to be installed
     * to work but it is highly recommended to install it to get good formatting results.
     *
     * @param mixed $value the value to be formatted.
     * @param string $currency the 3-letter ISO 4217 currency code indicating the currency to use.
     * If null, [[currencyCode]] will be used.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     * @throws InvalidConfigException if no currency is given and [[currencyCode]] is not defined.
     */
    public function asCurrency($value, $currency = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);

        if ($this->_intlLoaded) {
            $formatter = $this->createNumberFormatter(NumberFormatter::CURRENCY, null, $options, $textOptions);
            if ($currency === null) {
                if ($this->currencyCode === null) {
                    $currency = $formatter->getSymbol(NumberFormatter::INTL_CURRENCY_SYMBOL);
                } else {
                    $currency = $this->currencyCode;
                }
            }
            return $formatter->formatCurrency($value, $currency);
        } else {
            if ($currency === null) {
                if ($this->currencyCode === null) {
                    throw new InvalidConfigException('The default currency code for the formatter is not defined.');
                }
                $currency = $this->currencyCode;
            }
            return $currency . ' ' . $this->asDecimal($value, 2, $options, $textOptions);
        }
    }

    /**
     * Formats the value as a number spellout.
     *
     * This function requires the [PHP intl extension](http://php.net/manual/en/book.intl.php) to be installed.
     *
     * @param mixed $value the value to be formatted
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     * @throws InvalidConfigException when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available.
     */
    public function asSpellout($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded){
            $f = $this->createNumberFormatter(NumberFormatter::SPELLOUT);
            return $f->format($value);
        } else {
            throw new InvalidConfigException('Format as Spellout is only supported when PHP intl extension is installed.');
        }
    }

    /**
     * Formats the value as a ordinal value of a number.
     *
     * This function requires the [PHP intl extension](http://php.net/manual/en/book.intl.php) to be installed.
     *
     * @param mixed $value the value to be formatted
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     * @throws InvalidConfigException when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is not available.
     */
    public function asOrdinal($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $value = $this->normalizeNumericValue($value);
        if ($this->_intlLoaded){
            $f = $this->createNumberFormatter(NumberFormatter::ORDINAL);
            return $f->format($value);
        } else {
            throw new InvalidConfigException('Format as Ordinal is only supported when PHP intl extension is installed.');
        }
    }

    /**
     * Formats the value in bytes as a size in human readable form for example `12 KB`.
     *
     * This is the short form of [[asSize]].
     *
     * If [[sizeFormatBase]] is 1024, [binary prefixes](http://en.wikipedia.org/wiki/Binary_prefix) (e.g. kibibyte/KiB, mebibyte/MiB, ...)
     * are used in the formatting result.
     *
     * @param integer $value value in bytes to be formatted.
     * @param integer $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     * @see sizeFormat
     * @see asSize
     */
    public function asShortSize($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        list($params, $position) = $this->formatSizeNumber($value, $decimals, $options, $textOptions);

        if ($this->sizeFormatBase == 1024) {
            switch ($position) {
                case 0:  return Yii::t('yii', '{nFormatted} B', $params, $this->locale);
                case 1:  return Yii::t('yii', '{nFormatted} KiB', $params, $this->locale);
                case 2:  return Yii::t('yii', '{nFormatted} MiB', $params, $this->locale);
                case 3:  return Yii::t('yii', '{nFormatted} GiB', $params, $this->locale);
                case 4:  return Yii::t('yii', '{nFormatted} TiB', $params, $this->locale);
                default: return Yii::t('yii', '{nFormatted} PiB', $params, $this->locale);
            }
        } else {
            switch ($position) {
                case 0:  return Yii::t('yii', '{nFormatted} B', $params, $this->locale);
                case 1:  return Yii::t('yii', '{nFormatted} KB', $params, $this->locale);
                case 2:  return Yii::t('yii', '{nFormatted} MB', $params, $this->locale);
                case 3:  return Yii::t('yii', '{nFormatted} GB', $params, $this->locale);
                case 4:  return Yii::t('yii', '{nFormatted} TB', $params, $this->locale);
                default: return Yii::t('yii', '{nFormatted} PB', $params, $this->locale);
            }
        }
    }

    /**
     * Formats the value in bytes as a size in human readable form, for example `12 kilobytes`.
     *
     * If [[sizeFormatBase]] is 1024, [binary prefixes](http://en.wikipedia.org/wiki/Binary_prefix) (e.g. kibibyte/KiB, mebibyte/MiB, ...)
     * are used in the formatting result.
     *
     * @param integer $value value in bytes to be formatted.
     * @param integer $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return string the formatted result.
     * @throws InvalidParamException if the input value is not numeric.
     * @see sizeFormat
     * @see asShortSize
     */
    public function asSize($value, $decimals = null, $options = [], $textOptions = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        list($params, $position) = $this->formatSizeNumber($value, $decimals, $options, $textOptions);

        if ($this->sizeFormatBase == 1024) {
            switch ($position) {
                case 0:  return Yii::t('yii', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->locale);
                case 1:  return Yii::t('yii', '{nFormatted} {n, plural, =1{kibibyte} other{kibibytes}}', $params, $this->locale);
                case 2:  return Yii::t('yii', '{nFormatted} {n, plural, =1{mebibyte} other{mebibytes}}', $params, $this->locale);
                case 3:  return Yii::t('yii', '{nFormatted} {n, plural, =1{gibibyte} other{gibibytes}}', $params, $this->locale);
                case 4:  return Yii::t('yii', '{nFormatted} {n, plural, =1{tebibyte} other{tebibytes}}', $params, $this->locale);
                default: return Yii::t('yii', '{nFormatted} {n, plural, =1{pebibyte} other{pebibytes}}', $params, $this->locale);
            }
        } else {
            switch ($position) {
                case 0:  return Yii::t('yii', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->locale);
                case 1:  return Yii::t('yii', '{nFormatted} {n, plural, =1{kilobyte} other{kilobytes}}', $params, $this->locale);
                case 2:  return Yii::t('yii', '{nFormatted} {n, plural, =1{megabyte} other{megabytes}}', $params, $this->locale);
                case 3:  return Yii::t('yii', '{nFormatted} {n, plural, =1{gigabyte} other{gigabytes}}', $params, $this->locale);
                case 4:  return Yii::t('yii', '{nFormatted} {n, plural, =1{terabyte} other{terabytes}}', $params, $this->locale);
                default: return Yii::t('yii', '{nFormatted} {n, plural, =1{petabyte} other{petabytes}}', $params, $this->locale);
            }
        }
    }

    private function formatSizeNumber($value, $decimals, $options, $textOptions)
    {
        if (is_string($value) && is_numeric($value)) {
            $value = (int) $value;
        }
        if (!is_numeric($value)) {
            throw new InvalidParamException("'$value' is not a numeric value.");
        }

        $position = 0;
        do {
            if ($value < $this->sizeFormatBase) {
                break;
            }
            $value = $value / $this->sizeFormatBase;
            $position++;
        } while ($position < 5);

        // no decimals for bytes
        if ($position === 0) {
            $decimals = 0;
        } elseif ($decimals !== null) {
            $value = round($value, $decimals);
        }
        // disable grouping for edge cases like 1023 to get 1023 B instead of 1,023 B
        $oldThousandSeparator = $this->thousandSeparator;
        $this->thousandSeparator = '';
        $options[NumberFormatter::GROUPING_USED] = false;
        // format the size value
        $params = [
            // this is the unformatted number used for the plural rule
            'n' => $value,
            // this is the formatted number used for display
            'nFormatted' => $this->asDecimal($value, $decimals, $options, $textOptions),
        ];
        $this->thousandSeparator = $oldThousandSeparator;

        return [$params, $position];
    }

    /**
     * @param $value
     * @return float
     * @throws InvalidParamException
     */
    protected function normalizeNumericValue($value)
    {
        if (is_string($value) && is_numeric($value)) {
            $value = (float) $value;
        }
        if (!is_numeric($value)) {
            throw new InvalidParamException("'$value' is not a numeric value.");
        }
        return $value;
    }

    /**
     * Creates a number formatter based on the given type and format.
     *
     * You may overide this method to create a number formatter based on patterns.
     *
     * @param integer $style the type of the number formatter.
     * Values: NumberFormatter::DECIMAL, ::CURRENCY, ::PERCENT, ::SCIENTIFIC, ::SPELLOUT, ::ORDINAL
     *          ::DURATION, ::PATTERN_RULEBASED, ::DEFAULT_STYLE, ::IGNORE
     * @param integer $decimals the number of digits after the decimal point.
     * @param array $options optional configuration for the number formatter. This parameter will be merged with [[numberFormatterOptions]].
     * @param array $textOptions optional configuration for the number formatter. This parameter will be merged with [[numberFormatterTextOptions]].
     * @return NumberFormatter the created formatter instance
     */
    protected function createNumberFormatter($style, $decimals = null, $options = [], $textOptions = [])
    {
        $formatter = new NumberFormatter($this->locale, $style);

        if ($this->decimalSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $this->decimalSeparator);
        }
        if ($this->thousandSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
        }

        if ($decimals !== null) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        }

        foreach ($this->numberFormatterOptions as $name => $value) {
            $formatter->setAttribute($name, $value);
        }
        foreach ($options as $name => $value) {
            $formatter->setAttribute($name, $value);
        }
        foreach ($this->numberFormatterTextOptions as $name => $attribute) {
            $formatter->setTextAttribute($name, $attribute);
        }
        foreach ($textOptions as $name => $attribute) {
            $formatter->setTextAttribute($name, $attribute);
        }
        return $formatter;
    }
}
