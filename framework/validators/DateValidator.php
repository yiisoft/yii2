<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use DateTimeInterface;
use IntlDateFormatter;
use Yii;
use DateTime;
use yii\base\Exception;
use yii\helpers\FormatConverter;

/**
 * DateValidator verifies if the attribute represents a date, time or datetime in a proper format.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DateValidator extends Validator
{
    /**
     * @var string the date format that the value being validated should follow.
     * This can be a date time pattern as described in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the PHP Datetime class.
     * Please refer to <http://php.net/manual/en/datetime.createfromformat.php> on supported formats.
     *
     * If this property is not set, the default value will be obtained from `Yii::$app->formatter->dateFormat`, see [[\yii\i18n\Formatter::dateFormat]] for details.
     *
     * Here are some example values:
     *
     * ```php
     * 'MM/dd/yyyy' // date in ICU format
     * 'php:m/d/Y' // the same date in PHP format
     * 'MM/dd/yyyy HH:mm' // not only dates but also times can be validated
     * ```
     */
    public $format;
    /**
     * @var string the locale ID that is used to localize the date parsing.
     * This is only effective when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
     * If not set, the locale of the [[\yii\base\Application::formatter|formatter]] will be used.
     * See also [[\yii\i18n\Formatter::locale]].
     */
    public $locale;
    /**
     * @var string the timezone to use for parsing date and time values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     */
    public $timeZone;
    /**
     * @var string the name of the attribute to receive the parsing result.
     * When this property is not null and the validation is successful, the named attribute will
     * receive the parsing result.
     *
     * This can be the same attribute as the one being validated. If this is the case,
     * the original value will be overwritten with the timestamp value after successful validation.
     * @see timestampAttributeFormat
     * @see timestampAttributeTimeZone
     */
    public $timestampAttribute;
    /**
     * @var string the format to use when populating the [[timestampAttribute]].
     * The format can be specified in the same way as for [[format]].
     *
     * If not set, [[timestampAttribute]] will receive a UNIX timestamp.
     * If [[timestampAttribute]] is not set, this property will be ignored.
     * @see format
     * @see timestampAttribute
     * @since 2.0.4
     */
    public $timestampAttributeFormat;
    /**
     * @var string the timezone to use when populating the [[timestampAttribute]]. Defaults to `UTC`.
     *
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     *
     * If [[timestampAttributeFormat]] is not set, this property will be ignored.
     * @see timestampAttributeFormat
     * @since 2.0.4
     */
    public $timestampAttributeTimeZone = 'UTC';

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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'The format of {attribute} is invalid.');
        }
        if ($this->format === null) {
            $this->format = Yii::$app->formatter->dateFormat;
        }
        if ($this->locale === null) {
            $this->locale = Yii::$app->language;
        }
        if ($this->timeZone === null) {
            $this->timeZone = Yii::$app->timeZone;
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        $timestamp = $this->parseDateValue($value);
        if ($timestamp === false) {
            $this->addError($model, $attribute, $this->message, []);
        } elseif ($this->timestampAttribute !== null) {
            if ($this->timestampAttributeFormat === null) {
                $model->{$this->timestampAttribute} = $timestamp;
            } else {
                $model->{$this->timestampAttribute} = $this->formatTimestamp($timestamp, $this->timestampAttributeFormat);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        return $this->parseDateValue($value) === false ? [$this->message, []] : null;
    }

    /**
     * Parses date string into UNIX timestamp
     *
     * @param string $value string representing date
     * @return integer|boolean a UNIX timestamp or `false` on failure.
     */
    protected function parseDateValue($value)
    {
        if (is_array($value)) {
            return false;
        }
        $format = $this->format;
        if (strncmp($this->format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        } else {
            if (extension_loaded('intl')) {
                return $this->parseDateValueIntl($value, $format);
            } else {
                // fallback to PHP if intl is not installed
                $format = FormatConverter::convertDateIcuToPhp($format, 'date');
            }
        }
        return $this->parseDateValuePHP($value, $format);
    }

    /**
     * Parses a date value using the IntlDateFormatter::parse()
     * @param string $value string representing date
     * @param string $format the expected date format
     * @return integer|boolean a UNIX timestamp or `false` on failure.
     */
    private function parseDateValueIntl($value, $format)
    {
        if (isset($this->_dateFormats[$format])) {
            $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE, 'UTC');
            $hasTimeInfo = false;
        } else {
            // if no time was provided in the format string set time to 0 to get a simple date timestamp
            $hasTimeInfo = (strpbrk($format, 'ahHkKmsSA') !== false);
            $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $hasTimeInfo ? $this->timeZone : 'UTC', null, $format);
        }
        // enable strict parsing to avoid getting invalid date values
        $formatter->setLenient(false);

        // There should not be a warning thrown by parse() but this seems to be the case on windows so we suppress it here
        // See https://github.com/yiisoft/yii2/issues/5962 and https://bugs.php.net/bug.php?id=68528
        $parsePos = 0;
        $parsedDate = @$formatter->parse($value, $parsePos);
        if ($parsedDate === false || $parsePos !== mb_strlen($value, Yii::$app ? Yii::$app->charset : 'UTF-8')) {
            return false;
        }

        return $parsedDate;
    }

    /**
     * Parses a date value using the DateTime::createFromFormat()
     * @param string $value string representing date
     * @param string $format the expected date format
     * @return integer|boolean a UNIX timestamp or `false` on failure.
     */
    private function parseDateValuePHP($value, $format)
    {
        // if no time was provided in the format string set time to 0 to get a simple date timestamp
        $hasTimeInfo = (strpbrk($format, 'HhGgis') !== false);

        $date = DateTime::createFromFormat($format, $value, new \DateTimeZone($hasTimeInfo ? $this->timeZone : 'UTC'));
        $errors = DateTime::getLastErrors();
        if ($date === false || $errors['error_count'] || $errors['warning_count']) {
            return false;
        }

        if (!$hasTimeInfo) {
            $date->setTime(0, 0, 0);
        }
        return $date->getTimestamp();
    }

    /**
     * Formats a timestamp using the specified format
     * @param integer $timestamp
     * @param string $format
     * @return string
     */
    private function formatTimestamp($timestamp, $format)
    {
        if (strncmp($format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        } else {
            $format = FormatConverter::convertDateIcuToPhp($format, 'date');
        }

        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone(new \DateTimeZone($this->timestampAttributeTimeZone));
        return $date->format($format);
    }
}
