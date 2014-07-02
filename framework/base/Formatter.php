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
 * Formatter is configured as an application component in [[\yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->formatter`.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Formatter extends Component
{
    /**
     * @var string the timezone to use for formatting time and date values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     */
    public $timeZone;
    /**
     * @var string the default format string to be used to format a date using PHP date() function.
     */
    public $dateFormat = 'Y-m-d';
    /**
     * @var string the default format string to be used to format a time using PHP date() function.
     */
    public $timeFormat = 'H:i:s';
    /**
     * @var string the default format string to be used to format a date and time using PHP date() function.
     */
    public $datetimeFormat = 'Y-m-d H:i:s';
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
     * @var array the format used to format size (bytes). Three elements may be specified: "base", "decimals" and "decimalSeparator".
     * They correspond to the base at which a kilobyte is calculated (1000 or 1024 bytes per kilobyte, defaults to 1024),
     * the number of digits after the decimal point (defaults to 2) and the character displayed as the decimal point.
     */
    public $sizeFormat = [
        'base' => 1024,
        'decimals' => 2,
        'decimalSeparator' => null,
    ];

    /**
     * Initializes the component.
     */
    public function init()
    {
        if ($this->timeZone === null) {
            $this->timeZone = Yii::$app->timeZone;
        }

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
        if ($this->hasMethod($method)) {
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

        return str_replace('<p></p>', '', '<p>' . preg_replace('/[\r\n]{2,}/', "</p>\n<p>", Html::encode($value)) . '</p>');
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

        return $this->formatTimestamp($value, $format === null ? $this->dateFormat : $format);
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

        return $this->formatTimestamp($value, $format === null ? $this->timeFormat : $format);
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

        return $this->formatTimestamp($value, $format === null ? $this->datetimeFormat : $format);
    }

    /**
     * Normalizes the given datetime value as one that can be taken by various date/time formatting methods.
     *
     * @param mixed $value the datetime value to be normalized.
     * @return integer the normalized datetime value
     */
    protected function normalizeDatetimeValue($value)
    {
        if (is_string($value)) {
            if (is_numeric($value) || $value === '') {
                $value = (double)$value;
            } else {
                try {
                    $date = new DateTime($value);
                } catch (\Exception $e) {
                    return false;
                }
                $value = (double)$date->format('U');
            }
            return $value;
        } elseif ($value instanceof DateTime || $value instanceof \DateTimeInterface) {
            return (double)$value->format('U');
        } else {
            return (double)$value;
        }
    }

    /**
     * @param integer $value normalized datetime value
     * @param string $format the format used to convert the value into a date string.
     * @return string the formatted result
     */
    protected function formatTimestamp($value, $format)
    {
        $date = new DateTime(null, new \DateTimeZone($this->timeZone));
        $date->setTimestamp($value);

        return $date->format($format);
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
            $value = (int) $value;

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
        $ds = isset($this->decimalSeparator) ? $this->decimalSeparator : '.';
        $ts = isset($this->thousandSeparator) ? $this->thousandSeparator : ',';

        return number_format((float) $value, $decimals, $ds, $ts);
    }

    /**
     * Formats the value in bytes as a size in human readable form.
     * @param integer $value value in bytes to be formatted
     * @param boolean $verbose if full names should be used (e.g. bytes, kilobytes, ...).
     * Defaults to false meaning that short names will be used (e.g. B, KB, ...).
     * @return string the formatted result
     * @see sizeFormat
     */
    public function asSize($value, $verbose = false)
    {
        $position = 0;

        do {
            if ($value < $this->sizeFormat['base']) {
                break;
            }

            $value = $value / $this->sizeFormat['base'];
            $position++;
        } while ($position < 6);

        $value = round($value, $this->sizeFormat['decimals']);
        $formattedValue = isset($this->sizeFormat['decimalSeparator']) ? str_replace('.', $this->sizeFormat['decimalSeparator'], $value) : $value;
        $params = ['n' => $formattedValue];

        switch ($position) {
            case 0:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# byte} other{# bytes}}', $params) : Yii::t('yii', '{n} B', $params);
            case 1:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# kilobyte} other{# kilobytes}}', $params) : Yii::t('yii', '{n} KB', $params);
            case 2:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# megabyte} other{# megabytes}}', $params) : Yii::t('yii', '{n} MB', $params);
            case 3:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# gigabyte} other{# gigabytes}}', $params) : Yii::t('yii', '{n} GB', $params);
            case 4:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# terabyte} other{# terabytes}}', $params) : Yii::t('yii', '{n} TB', $params);
            default:
                return $verbose ? Yii::t('yii', '{n, plural, =1{# petabyte} other{# petabytes}}', $params) : Yii::t('yii', '{n} PB', $params);
        }
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
     * @param integer|string|DateTime|\DateInterval $referenceTime if specified the value is used instead of now
     * @return string the formatted result
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
                return Yii::t('yii', 'in {delta, plural, =1{a year} other{# years}}', ['delta' => $interval->y]);
            }
            if ($interval->m >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a month} other{# months}}', ['delta' => $interval->m]);
            }
            if ($interval->d >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a day} other{# days}}', ['delta' => $interval->d]);
            }
            if ($interval->h >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{an hour} other{# hours}}', ['delta' => $interval->h]);
            }
            if ($interval->i >= 1) {
                return Yii::t('yii', 'in {delta, plural, =1{a minute} other{# minutes}}', ['delta' => $interval->i]);
            }

            return Yii::t('yii', 'in {delta, plural, =1{a second} other{# seconds}}', ['delta' => $interval->s]);
        } else {
            if ($interval->y >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a year} other{# years}} ago', ['delta' => $interval->y]);
            }
            if ($interval->m >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a month} other{# months}} ago', ['delta' => $interval->m]);
            }
            if ($interval->d >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a day} other{# days}} ago', ['delta' => $interval->d]);
            }
            if ($interval->h >= 1) {
                return Yii::t('yii', '{delta, plural, =1{an hour} other{# hours}} ago', ['delta' => $interval->h]);
            }
            if ($interval->i >= 1) {
                return Yii::t('yii', '{delta, plural, =1{a minute} other{# minutes}} ago', ['delta' => $interval->i]);
            }

            return Yii::t('yii', '{delta, plural, =1{a second} other{# seconds}} ago', ['delta' => $interval->s]);
        }
    }
}
