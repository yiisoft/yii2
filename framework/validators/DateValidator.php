<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use DateTime;
use IntlDateFormatter;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\FormatConverter;

/**
 * DateValidator verifies if the attribute represents a date, time or datetime in a proper [[format]].
 * DateValidator 校验这个属性是否是一个 [[format]] 要求的格式的日期，时间戳，或者日期时间。
 *
 * It can also parse internationalized dates in a specific [[locale]] like e.g. `12 мая 2014` when [[format]]
 * is configured to use a time pattern in ICU format.
 * 在指定 [[locale]] 属性值后，它同样支持转换国际化时间。类似 `12 мая 2014` 这种，当 [[format]] 配置为 ICU 格式的时间字符串模式。
 *
 * It is further possible to limit the date within a certain range using [[min]] and [[max]].
 * 你还可以使用 [[min]] 和 [[max]] 属性将日期范围限定在一个特定的区间。
 *
 * Additional to validating the date it can also export the parsed timestamp as a machine readable format
 * which can be configured using [[timestampAttribute]]. For values that include time information (not date-only values)
 * also the time zone will be adjusted. The time zone of the input value is assumed to be the one specified by the [[timeZone]]
 * property and the target timeZone will be UTC when [[timestampAttributeFormat]] is `null` (exporting as UNIX timestamp)
 * or [[timestampAttributeTimeZone]] otherwise. If you want to avoid the time zone conversion, make sure that [[timeZone]] and
 * [[timestampAttributeTimeZone]] are the same.
 * 在校验时间之外，这个校验器同样可以把日期输出为一个机器可读的时间戳格式，你可以通过 [[timestampAttribute]] 属性来配置输出的属性名。
 * 对于哪些包含时间信息（不只是日期）的值，时区信息也会被自动调整。输入的时区信息默认通过 [[timeZone]] 属性指定，
 * 输出的时区如果 [[timestampAttributeFormat]] 为 null ，则默认是 UTC ，否则是 [[timestampAttributeTimeZone]]。
 * 如果你想避免时区转换，确保 [[timeZone]] 和 [[timestampAttributeTimeZone]] 的值是一样的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0
 */
class DateValidator extends Validator
{
    /**
     * Constant for specifying the validation [[type]] as a date value, used for validation with intl short format.
     * 常量，用于设置属性校验 [[type]] 为日期值，以intl短格式校验
     * @since 2.0.8
     * @see type
     */
    const TYPE_DATE = 'date';
    /**
     * Constant for specifying the validation [[type]] as a datetime value, used for validation with intl short format.
     * 常量，用于设置属性校验 [[type]] 为时间日期值，以intl短格式校验
     * @since 2.0.8
     * @see type
     */
    const TYPE_DATETIME = 'datetime';
    /**
     * Constant for specifying the validation [[type]] as a time value, used for validation with intl short format.
     * 常量，用于设置属性校验 [[type]] 为时间戳值，以intl短格式校验
     * @since 2.0.8
     * @see type
     */
    const TYPE_TIME = 'time';

    /**
     * @var string the type of the validator. Indicates, whether a date, time or datetime value should be validated.
     * This property influences the default value of [[format]] and also sets the correct behavior when [[format]] is one of the intl
     * short formats, `short`, `medium`, `long`, or `full`.
     * @var string 校验器的类型，意味着被校验的是一个日期，时间戳，或者时间日期值。
     * 这个属性影响着 [[format]] 的默认值，当 [[format]] 是 `short`, `medium`, `long`, 或者 `full` 之一的intl短格式时，它会设置校验器的正确行为。
     * 这个属性只有扩展 [PHP intl extension](http://php.net/manual/en/book.intl.php) 安装时才生效。
     * This is only effective when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
     *
     * This property can be set to the following values:
     * 这个属性可以设置为如下值：
     *
     * - [[TYPE_DATE]] - (default) for validating date values only, that means only values that do not include a time range are valid.
     * - [[TYPE_DATE]] - （默认）只接受日期值，这意味着不包括时间部分的日期值才有效。
     * - [[TYPE_DATETIME]] - for validating datetime values, that contain a date part as well as a time part.
     * - [[TYPE_DATETIME]] - 接受时间日期值，即同时包含时间部分和日期部分。
     * - [[TYPE_TIME]] - for validating time values, that contain no date information.
     * - [[TYPE_TIME]] - 接受时间戳值，即不包含日期信息。
     *
     * @since 2.0.8
     */
    public $type = self::TYPE_DATE;
    /**
     * @var string the date format that the value being validated should follow.
     * This can be a date time pattern as described in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
     * @var string 要被校验的值应该符合的时间日期格式。
     * 它可以是一个 [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax) 文档中描述的时间日期格式。
     *
     * Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the PHP Datetime class.
     * Please refer to <http://php.net/manual/en/datetime.createfromformat.php> on supported formats.
     * 另外，这个属性可以是一个前缀为`php:` 的字符串，代表一个可以被PHP Datetime 类识别的时间日期格式。
     *
     * If this property is not set, the default value will be obtained from `Yii::$app->formatter->dateFormat`, see [[\yii\i18n\Formatter::dateFormat]] for details.
     * Since version 2.0.8 the default value will be determined from different formats of the formatter class,
     * dependent on the value of [[type]]:
     * 如果这个属性没有设置，默认值为 `Yii::$app->formatter->dateFormat`，更多详情，参考 [[\yii\i18n\Formatter::dateFormat]]。
     * 自 2.0.8 起，默认值将由不同的格式化类所决定，依赖 [[type]] 的具体值：
     *
     * - if type is [[TYPE_DATE]], the default value will be taken from [[\yii\i18n\Formatter::dateFormat]],
     * - if type is [[TYPE_DATETIME]], it will be taken from [[\yii\i18n\Formatter::datetimeFormat]],
     * - and if type is [[TYPE_TIME]], it will be [[\yii\i18n\Formatter::timeFormat]].
     * - 如果是 [[TYPE_DATE]], 默认值为 [[\yii\i18n\Formatter::dateFormat]],
     * - 如果是 [[TYPE_DATETIME]], 默认值为 [[\yii\i18n\Formatter::datetimeFormat]],
     * - 如果是 [[TYPE_TIME]], 默认为 [[\yii\i18n\Formatter::timeFormat]].
     *
     * Here are some example values:
     * 以下是这个属性的示例值：
     *
     * ```php
     * 'MM/dd/yyyy' // date in ICU format
     * 'php:m/d/Y' // the same date in PHP format
     * 'MM/dd/yyyy HH:mm' // not only dates but also times can be validated
     * ```
     *
     * **Note:** the underlying date parsers being used vary dependent on the format. If you use the ICU format and
     * the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed, the [IntlDateFormatter](http://php.net/manual/en/intldateformatter.parse.php)
     * is used to parse the input value. In all other cases the PHP [DateTime](http://php.net/manual/en/datetime.createfromformat.php) class
     * is used. The IntlDateFormatter has the advantage that it can parse international dates like `12. Mai 2015` or `12 мая 2014`, while the
     * PHP parser is limited to English only. The PHP parser however is more strict about the input format as it will not accept
     * `12.05.05` for the format `php:d.m.Y`, but the IntlDateFormatter will accept it for the format `dd.MM.yyyy`.
     * If you need to use the IntlDateFormatter you can avoid this problem by specifying a [[min|minimum date]].
     * **注意：**底层所使用的日期转换函数依赖具体的格式，如果你用 ICU 格式，然后 [PHP intl extension](http://php.net/manual/en/book.intl.php) 扩展正确安装，
     * 将使用 [IntlDateFormatter](http://php.net/manual/en/intldateformatter.parse.php) 转换输入值。其他的情况，使用 PHP [DateTime](http://php.net/manual/en/datetime.createfromformat.php)来转换。
     * IntlDateFormatter 的优势在于它可以转换国际化的世界格式，类似 `12. Mai 2015` 或者 `12 мая 2014`。而 PHP 内置转换函数只能转换英语格式。PHP内置转换函数对输入更严格一些，
     * 如果是格式 `php:d.m.Y` ，那么值 `12.05.05`将不会被PHP内置函数接受 ,但是 IntlDateFormatter 却可以以格式 `dd.MM.yyyy` 接受这个值。
     * 如果你需要使用 IntlDateFormatter 你可以通过指定 [[min|minimum date]] 来避免这个问题。
     */
    public $format;
    /**
     * @var string the locale ID that is used to localize the date parsing.
     * This is only effective when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
     * If not set, the locale of the [[\yii\base\Application::formatter|formatter]] will be used.
     * See also [[\yii\i18n\Formatter::locale]].
     * @var string 本地化时间日期转化的 locale ID
     * 这个只有在 [PHP intl extension](http://php.net/manual/en/book.intl.php) 安装的情况下才生效。
     * 如果未设置，将使用 [[\yii\base\Application::formatter|formatter]]，参见 [[\yii\i18n\Formatter::locale]]
     */
    public $locale;
    /**
     * @var string the timezone to use for parsing date and time values.
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * If this property is not set, [[\yii\base\Application::timeZone]] will be used.
     * @var string 转化日期和时间值的时区。
     * 这个可以是任意可以传递给函数 [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php) 调用的参数值。
     * 例如： `UTC`, `Europe/Berlin` 或者 `America/Chicago`。
     * 可用的时区值参考 [php manual](http://www.php.net/manual/en/timezones.php)。
     * 如果这个属性没有设置，将使用 [[\yii\base\Application::timeZone]]。
     */
    public $timeZone;
    /**
     * @var string the name of the attribute to receive the parsing result.
     * When this property is not null and the validation is successful, the named attribute will
     * receive the parsing result.
     * @var string 用于接受转换结果的属性名称。
     * 当这个属性不为空，且校验成功的情况下，指定的属性将会被赋值为转换结果。
     *
     * This can be the same attribute as the one being validated. If this is the case,
     * the original value will be overwritten with the timestamp value after successful validation.
     * 这个可以同时为被校验的属性名。在这种情况下，原始值在成功校验后，会被重写为时间戳值。
     *
     * Note, that when using this property, the input value will be converted to a unix timestamp,
     * which by definition is in UTC, so a conversion from the [[$timeZone|input time zone]] to UTC
     * will be performed. When defining [[$timestampAttributeFormat]] you can control the conversion by
     * setting [[$timestampAttributeTimeZone]] to a different value than `'UTC'`.
     * 注意，当使用这个属性时，输入值将会被转换为一个 unix 时间戳，默认是 UTC 时区，所以会有一个 [[$timeZone|input time zone]] 到 UTC 的时区转换。
     * 当定义了 [[$timestampAttributeFormat]] ，你可以通过设置 [[$timestampAttributeTimeZone]] 为其他不同于 `'UTC'` 的值来控制这个转换。
     *
     * @see timestampAttributeFormat
     * @see timestampAttributeTimeZone
     */
    public $timestampAttribute;
    /**
     * @var string the format to use when populating the [[timestampAttribute]].
     * The format can be specified in the same way as for [[format]].
     * @var string 填充 [[timestampAttribute]] 的格式。
     * 这个格式可以同 [[format]] 一样。
     *
     * If not set, [[timestampAttribute]] will receive a UNIX timestamp.
     * If [[timestampAttribute]] is not set, this property will be ignored.
     * 如果未设置，[[timestampAttribute]] 将会接受一个 UNIX 时间戳。
     * 如果 [[timestampAttribute]] 未设置，这个属性将会被忽略。
     * @see format
     * @see timestampAttribute
     * @since 2.0.4
     */
    public $timestampAttributeFormat;
    /**
     * @var string the timezone to use when populating the [[timestampAttribute]]. Defaults to `UTC`.
     * @var string 当填充 [[timestampAttribute]] 属性时，使用的时区，默认为 `UTC`。
     *
     * This can be any value that may be passed to [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php)
     * e.g. `UTC`, `Europe/Berlin` or `America/Chicago`.
     * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
     * 这个可以是任意可以传递给函数 [date_default_timezone_set()](http://www.php.net/manual/en/function.date-default-timezone-set.php) 调用的参数值。
     * 例如： `UTC`, `Europe/Berlin` 或者 `America/Chicago`。
     * 可用的时区值参考 [php manual](http://www.php.net/manual/en/timezones.php)。
     *
     * If [[timestampAttributeFormat]] is not set, this property will be ignored.
     * 如果属性 [[timestampAttributeFormat]] 未设置，这个属性也会被忽略。
     * @see timestampAttributeFormat
     * @since 2.0.4
     */
    public $timestampAttributeTimeZone = 'UTC';
    /**
     * @var int|string upper limit of the date. Defaults to null, meaning no upper limit.
     * This can be a unix timestamp or a string representing a date time value.
     * If this property is a string, [[format]] will be used to parse it.
     * @var int|string 时间日期的上限，默认为 null ，代表无上限。
     * 这个可以是一个 unix 时间戳，也可以是一个代表日期时间格式的字符串值。
     * 如果这个属性是字符串， 将使用 [[format]] 来格式化它。
     * @see tooBig 当日期太大时，参考 tooBig 自定义错误消息。
     * @since 2.0.4
     */
    public $max;
    /**
     * @var int|string lower limit of the date. Defaults to null, meaning no lower limit.
     * This can be a unix timestamp or a string representing a date time value.
     * If this property is a string, [[format]] will be used to parse it.
     * @var int|string 时间日期的下限，默认是 null，代表无下限。
     * 这个可以是一个 unix 时间戳，也可以是一个代表日期时间格式的字符串值。
     * 如果这个属性是字符串， 将使用 [[format]] 来格式化它。
     * @see tooSmall 当日期太小时，参考 tooSmall 自定义错误消息。
     * @since 2.0.4
     */
    public $min;
    /**
     * @var string 用户自定义错误消息当值大于 [[max]].
     * @since 2.0.4
     */
    public $tooBig;
    /**
     * @var string 用户自定义错误消息当值小于 [[min]].
     * @since 2.0.4
     */
    public $tooSmall;
    /**
     * @var string user friendly value of upper limit to display in the error message.
     * If this property is null, the value of [[max]] will be used (before parsing).
     * @var string 用户友好的最大值，用于展示在错误消息中。
     * 如果这个属性是 null， 将会使用未转换的 [[max]]。
     * @since 2.0.4
     */
    public $maxString;
    /**
     * @var string user friendly value of lower limit to display in the error message.
     * If this property is null, the value of [[min]] will be used (before parsing).
     * @var string 用户友好的最小值，用于展示在错误消息中。
     * 如果这个属性是 null， 将会使用未转换的 [[min]]。
     * @since 2.0.4
     */
    public $minString;

    /**
     * @var array map of short format names to IntlDateFormatter constant values.
     * @var array IntlDateFormatter 格式化名称的短名对照字典。
     */
    private $_dateFormats = [
        'short' => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long' => 1, // IntlDateFormatter::LONG,
        'full' => 0, // IntlDateFormatter::FULL,
    ];


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'The format of {attribute} is invalid.');
        }
        if ($this->format === null) {
            if ($this->type === self::TYPE_DATE) {
                $this->format = Yii::$app->formatter->dateFormat;
            } elseif ($this->type === self::TYPE_DATETIME) {
                $this->format = Yii::$app->formatter->datetimeFormat;
            } elseif ($this->type === self::TYPE_TIME) {
                $this->format = Yii::$app->formatter->timeFormat;
            } else {
                throw new InvalidConfigException('Unknown validation type set for DateValidator::$type: ' . $this->type);
            }
        }
        if ($this->locale === null) {
            $this->locale = Yii::$app->language;
        }
        if ($this->timeZone === null) {
            $this->timeZone = Yii::$app->timeZone;
        }
        if ($this->min !== null && $this->tooSmall === null) {
            $this->tooSmall = Yii::t('yii', '{attribute} must be no less than {min}.');
        }
        if ($this->max !== null && $this->tooBig === null) {
            $this->tooBig = Yii::t('yii', '{attribute} must be no greater than {max}.');
        }
        if ($this->maxString === null) {
            $this->maxString = (string) $this->max;
        }
        if ($this->minString === null) {
            $this->minString = (string) $this->min;
        }
        if ($this->max !== null && is_string($this->max)) {
            $timestamp = $this->parseDateValue($this->max);
            if ($timestamp === false) {
                throw new InvalidConfigException("Invalid max date value: {$this->max}");
            }
            $this->max = $timestamp;
        }
        if ($this->min !== null && is_string($this->min)) {
            $timestamp = $this->parseDateValue($this->min);
            if ($timestamp === false) {
                throw new InvalidConfigException("Invalid min date value: {$this->min}");
            }
            $this->min = $timestamp;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if ($this->isEmpty($value)) {
            if ($this->timestampAttribute !== null) {
                $model->{$this->timestampAttribute} = null;
            }
            return;
        }

        $timestamp = $this->parseDateValue($value);
        if ($timestamp === false) {
            if ($this->timestampAttribute === $attribute) {
                if ($this->timestampAttributeFormat === null) {
                    if (is_int($value)) {
                        return;
                    }
                } else {
                    if ($this->parseDateValueFormat($value, $this->timestampAttributeFormat) !== false) {
                        return;
                    }
                }
            }
            $this->addError($model, $attribute, $this->message, []);
        } elseif ($this->min !== null && $timestamp < $this->min) {
            $this->addError($model, $attribute, $this->tooSmall, ['min' => $this->minString]);
        } elseif ($this->max !== null && $timestamp > $this->max) {
            $this->addError($model, $attribute, $this->tooBig, ['max' => $this->maxString]);
        } elseif ($this->timestampAttribute !== null) {
            if ($this->timestampAttributeFormat === null) {
                $model->{$this->timestampAttribute} = $timestamp;
            } else {
                $model->{$this->timestampAttribute} = $this->formatTimestamp($timestamp, $this->timestampAttributeFormat);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        $timestamp = $this->parseDateValue($value);
        if ($timestamp === false) {
            return [$this->message, []];
        } elseif ($this->min !== null && $timestamp < $this->min) {
            return [$this->tooSmall, ['min' => $this->minString]];
        } elseif ($this->max !== null && $timestamp > $this->max) {
            return [$this->tooBig, ['max' => $this->maxString]];
        }

        return null;
    }

    /**
     * Parses date string into UNIX timestamp.
     * 将日期字符串转换为 UNIX 时间戳。
     *
     * @param string $value 日期字符串。
     * @return int|false 一个合法的 UNIX 时间戳，如果转换失败，返回 `false`。
     */
    protected function parseDateValue($value)
    {
        // TODO consider merging these methods into single one at 2.1
        return $this->parseDateValueFormat($value, $this->format);
    }

    /**
     * Parses date string into UNIX timestamp.
     * 将字符串转换为 UNIX 时间戳。
     *
     * @param string $value 日期字符串。
     * @param string $format 预期的日期格式字符串
     * @return int|false 一个合法的 UNIX 时间戳，如果转换失败，返回 `false`。
     */
    private function parseDateValueFormat($value, $format)
    {
        if (is_array($value)) {
            return false;
        }
        if (strncmp($format, 'php:', 4) === 0) {
            $format = substr($format, 4);
        } else {
            if (extension_loaded('intl')) {
                return $this->parseDateValueIntl($value, $format);
            }

            // fallback to PHP if intl is not installed
            $format = FormatConverter::convertDateIcuToPhp($format, 'date');
        }

        return $this->parseDateValuePHP($value, $format);
    }

    /**
     * Parses a date value using the IntlDateFormatter::parse().
     * 用 IntlDateFormatter::parse() 函数转换日期值
     * @param string $value 日期字符串
     * @param string $format 预期的日期格式字符串
     * @return int|bool 一个合法的 UNIX 时间戳，如果转换失败，返回 `false`。
     * @throws InvalidConfigException
     */
    private function parseDateValueIntl($value, $format)
    {
        if (isset($this->_dateFormats[$format])) {
            if ($this->type === self::TYPE_DATE) {
                $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE, 'UTC');
            } elseif ($this->type === self::TYPE_DATETIME) {
                $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], $this->_dateFormats[$format], $this->timeZone);
            } elseif ($this->type === self::TYPE_TIME) {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormats[$format], $this->timeZone);
            } else {
                throw new InvalidConfigException('Unknown validation type set for DateValidator::$type: ' . $this->type);
            }
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
     * Parses a date value using the DateTime::createFromFormat().
     * 用 DateTime::createFromFormat() 转换日期值。
     * @param string $value 日期字符串
     * @param string $format 预期的日期格式字符串
     * @return int|bool 一个合法的 UNIX 时间戳，如果转换失败，返回 `false`。
     */
    private function parseDateValuePHP($value, $format)
    {
        // if no time was provided in the format string set time to 0 to get a simple date timestamp
        $hasTimeInfo = (strpbrk($format, 'HhGgisU') !== false);

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
     * Formats a timestamp using the specified format.
     * 用指定的格式格式化时间戳
     * @param int $timestamp
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
