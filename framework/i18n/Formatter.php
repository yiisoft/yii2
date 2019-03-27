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
use DateTimeInterface;
use DateTimeZone;
use IntlDateFormatter;
use NumberFormatter;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\FormatConverter;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * Formatter 提供一组常用的数据格式化方法。
 *
 * Formatter 提供的格式化方法都以 `asXyz()` 的形式命名。
 * 它们中的一些行为可以通过 Formatter 的属性进行配置。
 * 例如，通过配置 [[dateFormat]]，可以控制 [[asDate()]] 将值格式化为自定义的日期字符串。
 *
 * Formatter 默认配置为 [[\yii\base\ application]] 中的应用程序组件。
 * 您可以通过 `Yii::$app->formatter` 访问该实例。
 *
 * Formatter 类用于根据 [[locale]] 格式化值。
 * 要使此功能起作用，必须安装 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 扩展。
 * 但是，如果没有通过提供回滚实现安装 PHP intl 扩展，大多数方法也可以工作。
 * 没有 intl 扩展时，只有英文的月、日名称。
 * 注意，即使安装了 intl 扩展，在 32 位系统上格式化年份 >=2038 或 <=1901 的日期和时间值也将回到 PHP 实现。
 * 因为 intl 在内部使用 32 位 UNIX 时间戳。
 * 在 64 位系统上，如果安装了 intl 格式化程序，则在所有情况下都使用它。
 *
 * > 注意：Formatter 类用于格式化以不同语言和时区显示给用户的值。
 * > 如果需要将日期或时间格式化为机器可读的格式，
 * > 请使用 PHP [date()](https://secure.php.net/manual/en/function.date.php) 函数。
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
     * @var string 格式化值为 `null` 时显示的文本。
     * 默认为 `'<span class="not-set">(not set)</span>'`，其中 `(not set)`
     * 将会根据 [[locale]] 来翻译。
     */
    public $nullDisplay;
    /**
     * @var array 格式化布尔值时显示的文本。
     * 第一个元素对应 `false` 显示的文本，第二个元素对应 `true` 显示的文本。
     * 默认为 `['No', 'Yes']`, 其中 `Yes` 和 `No`
     * 将会根据 [[locale]] 来翻译。
     */
    public $booleanFormat;
    /**
     * @var string 用于本地化日期和数字格式的区域设置 ID。
     * 对于数字和日期格式，
     * 只有在安装了 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 时才有效。
     * 如果没有设置，将使用 [[\yii\base\Application::language]]。
     */
    public $locale;
    /**
     * @var string 用于格式化时间和日期值的时区。
     *
     * 这可以是可传递给 [date_default_timezone_set()](https://secure.php.net/manual/en/function.date-default-timezone-set.php) 的任何值，
     * 例如 `UTC`，`Europe/Berlin` 或 `America/Chicago`。
     * 有关可用时区，请参阅 [php manual](https://secure.php.net/manual/en/timezones.php)。
     * 如果未设置此属性，将使用 [[\yii\base\Application::timeZone]]。
     *
     * 请注意，如果输入日期值中不包含任何时区，则默认情况下输入数据的默认时区为 UTC。
     * 如果将数据存储在数据库的不同时区，则必须相应地调整 [[defaultTimeZone]]。
     */
    public $timeZone;
    /**
     * @var string 如果输入值不显式包含时区，则为输入值假定的时区。
     *
     * 该值必须是有效的时区标识符，例如 `UTC`，`Europe/Berlin` 或者 `America/Chicago`。
     * 有关可用时区，请参阅 [php manual](https://secure.php.net/manual/en/timezones.php)。
     *
     * 它默认为 `UTC`，因此如果将日期时间值存储在数据库的另一个时区中，则只需调整此值。
     *
     * 请注意，UNIX 时间戳的定义始终为 UTC。
     * 这意味着指定与 UTC 不同的默认时区对作为 UNIX 时间戳给出的日期值没有影响。
     *
     * @since 2.0.1
     */
    public $defaultTimeZone = 'UTC';
    /**
     * @var string 用于格式化 [[asDate()|date]] 的默认格式字符串。
     * 这可以是 "short"，"medium"，"long" 或 "full"，其表示不同长度的预设格式。
     *
     * 它也可以是 [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax) 中指定的自定义格式。
     * 这也可以是一个前缀为 `php:` 的字符串，
     * 表示可以由 PHP [date()](https://secure.php.net/manual/en/function.date.php) 函数识别的格式。
     *
     * 例如
     *
     * ```php
     * 'MM/dd/yyyy' // date in ICU format
     * 'php:m/d/Y' // the same date in PHP format
     * ```
     */
    public $dateFormat = 'medium';
    /**
     * @var string 用于格式化 [[asTime()|time]] 的默认格式字符串。
     * 这可以是 "short"，"medium"，"long" 或 "full"，其表示不同长度的预设格式。
     *
     * 它也可以是 [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax) 中指定的自定义格式。
     * 这也可以是一个前缀为 `php:` 的字符串，
     * 表示可以由 PHP [date()](https://secure.php.net/manual/en/function.date.php) 函数识别的格式。
     *
     * 例如：
     *
     * ```php
     * 'HH:mm:ss' // time in ICU format
     * 'php:H:i:s' // the same time in PHP format
     * ```
     */
    public $timeFormat = 'medium';
    /**
     * @var string 用于格式化 [[asDatetime()|date and time]] 的默认格式字符串。
     * 这可以是 "short"，"medium"，"long" 或 "full"，其表示不同长度的预设格式。
     *
     * 它也可以是 [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax) 中指定的自定义格式。
     *
     * 这也可以是一个前缀为 `php:` 的字符串，
     * 表示可以由 PHP [date()](https://secure.php.net/manual/en/function.date.php) 函数识别的格式。
     *
     * 例如：
     *
     * ```php
     * 'MM/dd/yyyy HH:mm:ss' // date and time in ICU format
     * 'php:m/d/Y H:i:s' // the same date and time in PHP format
     * ```
     */
    public $datetimeFormat = 'medium';
    /**
     * @var \IntlCalendar|int|null 用于日期格式的日历。
     * 该属性的值将直接传递给 [constructor of the `IntlDateFormatter` class](https://secure.php.net/manual/en/intldateformatter.create.php).
     *
     * 默认值为 `null`，这意味着将使用公历。
     * 您也可以为公历日历显式传递常量 `\IntlDateFormatter::GREGORIAN`。
     *
     * 要使用替代日历，例如 [Jalali calendar](https://en.wikipedia.org/wiki/Jalali_calendar)，
     * 请将此属性设置为 `\IntlDateFormatter::TRADITIONAL`。
     * 然后必须在 [[locale]] 中指定日历，例如对于 persian 日历，格式化程序的配置将是：
     *
     * ```php
     * 'formatter' => [
     *     'locale' => 'fa_IR@calendar=persian',
     *     'calendar' => \IntlDateFormatter::TRADITIONAL,
     * ],
     * ```
     *
     * 可在 [ICU manual](http://userguide.icu-project.org/datetime/calendar) 中找到可用的日历名称。
     *
     * 从 PHP 5.5 开始，您也可以使用 [[\IntlCalendar]] 类的实例。
     * 查看 [PHP manual](https://secure.php.net/manual/en/intldateformatter.create.php) 了解更多详情。
     *
     * 如果 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 不可用，则设置此属性将不起作用。
     *
     * @see https://secure.php.net/manual/en/intldateformatter.create.php
     * @see https://secure.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes
     * @see https://secure.php.net/manual/en/class.intlcalendar.php
     * @since 2.0.7
     */
    public $calendar;
    /**
     * @var string 格式化数字时显示为小数点的字符。
     * 如果未设置，将使用与 [[locale]] 对应的小数分隔符。
     * 如果 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 不可用，默认值为 '.'。
     */
    public $decimalSeparator;
    /**
     * @var string 格式化数字时显示的千位分隔符（也称为分组分隔符）的字符。
     * 如果未设置，将使用与 [[locale]] 对应的千位分隔符。
     * 如果 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 不可用，默认值为 ','。
     */
    public $thousandSeparator;
    /**
     * @var array 传递给
     * intl [NumberFormatter::setAttribute()](https://secure.php.net/manual/en/numberformatter.setattribute.php) 方法的键值对，
     * 所有数字格式化程序由 [[createNumberFormatter()]] 所创建。
     * 仅安装了 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 时，此属性才有效。
     *
     * 请参阅 [PHP manual](https://secure.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatattribute)
     * 获取可以调整的选项。
     *
     * 例如，要调整小数位的最大值和最小值，您可以配置此属性，如下所示：
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
     * @var array 传递给
     * intl [NumberFormatter::setTextAttribute()](https://secure.php.net/manual/en/numberformatter.settextattribute.php) 方法的键值对，
     * 所有数字格式化程序由 [[createNumberFormatter()]] 所创建。
     * 仅安装了 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 时，此属性才有效。
     *
     * 请参阅 [PHP manual](https://secure.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformattextattribute)
     * 获取可以调整的选项。
     *
     * 例如，要更改负数的减号，您可以配置此属性，如下所示：
     *
     * ```php
     * [
     *     NumberFormatter::NEGATIVE_PREFIX => 'MINUS',
     * ]
     * ```
     */
    public $numberFormatterTextOptions = [];
    /**
     * @var array 传递给
     * intl [NumberFormatter::setSymbol()](https://secure.php.net/manual/en/numberformatter.setsymbol.php) 方法的键值对，
     * 所有数字格式化程序由 [[createNumberFormatter()]] 所创建。
     * 仅安装了 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 时，此属性才有效。
     *
     * 请参阅 [PHP manual](https://secure.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatsymbol)
     * 获取可以调整的选项。
     *
     * 例如，选择自定义货币符号，例如俄罗斯卢布使用 [U+20BD](http://unicode-table.com/en/20BD/) 而不是 `руб.`：
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
     * @var string 货币代码 ISO 4217 定义的 3 个字母货币代码，表示 [[asCurrency]] 使用的默认货币。
     * 如果未设置，将使用与 [[locale]] 对应的货币代码。
     * 请注意，在这种情况下，必须为 [[locale]] 指定国家/地区代码，
     * 否则，使用 `en-US` 无法确定默认货币。
     */
    public $currencyCode;
    /**
     * @var int 计算千字节的基数（每千字节为 1000 或 1024 个字节），由 [[asSize]] 和 [[asShortSize]] 使用。
     * 默认为 1024。
     */
    public $sizeFormatBase = 1024;
    /**
     * @var string 默认的系统度量单位。默认为 [[UNIT_SYSTEM_METRIC]]。
     * 可能的值：
     *  - [[UNIT_SYSTEM_METRIC]]
     *  - [[UNIT_SYSTEM_IMPERIAL]]
     *
     * @see asLength
     * @see asWeight
     * @since 2.0.13
     */
    public $systemOfUnits = self::UNIT_SYSTEM_METRIC;
    /**
     * @var array 重量和长度测量单位的配置。
     * 此数组包含最常用的测量单位，
     * 但如果您有其它要求，可以更改它。
     *
     * 例如，您可以添加较小的度量单位：
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
     * @var array 用于 [[measureUnits]] 中最小可能单位的乘法的基本单位。
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
     * @var bool [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 是否已经加载。
     */
    private $_intlLoaded = false;
    /**
     * @var \ResourceBundle 缓存的 ResourceBundle 对象，用于读取单元翻译
     */
    private $_resourceBundle;
    /**
     * @var array 缓存的单元翻译模式
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
        if ($this->booleanFormat === null) {
            $this->booleanFormat = [Yii::t('yii', 'No', [], $this->locale), Yii::t('yii', 'Yes', [], $this->locale)];
        }
        if ($this->nullDisplay === null) {
            $this->nullDisplay = '<span class="not-set">' . Yii::t('yii', '(not set)', [], $this->locale) . '</span>';
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
     * 根据给定的格式类型格式化值。
     * 此方法将调用此类中可用的 "as" 方法来进行格式化。例如，如果格式为 "html"，
     * 将使用 [[asHtml()]]。格式名称不区分大小写。
     * 对于类型 "xyz"，将使用方法 "asXyz"。
     * @param mixed $value 要格式化的值。
     * @param string|array|Closure $format 值的格式，
     * 例如："html"，"text" 或者一个匿名函数返回的格式值。
     *
     * 要指定格式化方法的其他参数，可以使用数组。
     * 数组的第一个元素指定格式名称，
     * 而其余元素将用作格式化方法的参数。 
     * 例如，`['date', 'Y-m-d']` 的格式将导致调用 `asDate($value, 'Y-m-d')`。
     *
     * 匿名函数应为：`function($value, $formatter)`，
     * 其中`$value` 是应该格式化的值，`$formatter` 是 Formatter 类的一个实例，
     * 可用于调用其他格式化函数。
     * 从版本 2.0.13 开始，可以使用匿名函数。
     * @return string 格式化结果。
     * @throws InvalidArgumentException 如果此类不支持格式类型。
     */
    public function format($value, $format)
    {
        if ($format instanceof Closure) {
            return call_user_func($format, $value, $this);
        } elseif (is_array($format)) {
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
            return call_user_func_array([$this, $method], $params);
        }

        throw new InvalidArgumentException("Unknown format type: $format");
    }


    // simple formats


    /**
     * 按原样格式化值，不进行任何格式化。
     * 此方法只返回没有任何格式的参数。
     * 唯一的例外是当值为 `null` 时，将使用 [[nullDisplay]] 格式化。
     * @param mixed $value 要格式化的值。
     * @return string 格式化的结果。
     */
    public function asRaw($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return $value;
    }

    /**
     * 将值格式化为 HTML 编码的纯文本。
     * @param string $value 要格式化的值。
     * @return string 格式化的结果。
     */
    public function asText($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::encode($value);
    }

    /**
     * 将值格式化为 HTML 编码的纯文本，并拆分换行符为新行。
     * @param string $value 要格式化的值。
     * @return string 格式化的结果。
     */
    public function asNtext($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return nl2br(Html::encode($value));
    }

    /**
     * 将值格式化为HTML编码的文本段落。
     * 每个文本段落都包含在一个 `<p>` 标签中。
     * 一个或多个连续的空行分为两段。
     * @param string $value 要格式化的值。
     * @return string 格式化的结果。
     */
    public function asParagraphs($value)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return str_replace('<p></p>', '', '<p>' . preg_replace('/\R{2,}/u', "</p>\n<p>", Html::encode($value)) . '</p>');
    }

    /**
     * 将值格式化为 HTML 文本。
     * 该值将使用 [[HtmlPurifier]] 进行过滤，以避免 XSS 攻击。
     * 如果你不想对值进行过滤，请使用 [[asRaw()]]。
     * @param string $value 要格式化的值。
     * @param array|null $config HTMLPurifier 类的配置。
     * @return string 格式化的结果。
     */
    public function asHtml($value, $config = null)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return HtmlPurifier::process($value, $config);
    }

    /**
     * 将值格式化为 mailto 链接。
     * @param string $value 要格式化的值。
     * @param array $options  键值对形式的标签选项。见 [[Html::mailto()]]。
     * @return string 格式化的结果。
     */
    public function asEmail($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::mailto(Html::encode($value), $value, $options);
    }

    /**
     * 将值格式化为 img 标签。
     * @param mixed $value 要格式化的值。
     * @param array $options  键值对形式的标签选项。见 [[Html::img()]]。
     * @return string 格式化的结果。
     */
    public function asImage($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }

        return Html::img($value, $options);
    }

    /**
     * 将值格式化为超链接。
     * @param mixed $value 要格式化的值。
     * @param array $options 键值对形式的标签选项。见 [[Html::a()]]。
     * @return string 格式化的结果。
     */
    public function asUrl($value, $options = [])
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        $url = $value;
        if (strpos($url, '://') === false) {
            $url = 'http://' . $url;
        }

        return Html::a(Html::encode($value), $url, $options);
    }

    /**
     * 将值格式化为布尔值。
     * @param mixed $value 要格式化的值。
     * @return string 格式化的结果。
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
     * 将值格式化为日期。
     * @param int|string|DateTime $value 要格式化的值。
     * 支持以下类型的值：
     *
     * - 表示 UNIX 时间戳的整数。UNIX 时间戳的定义始终为 UTC。
     * - 可以供 [DateTime object](https://secure.php.net/manual/en/datetime.formats.php) 解析的字符串。
     *   时间戳假定在 [[defaultTimeZone]]，除非给出一个明确的时区。
     * - 一个 [DateTime](https://secure.php.net/manual/en/class.datetime.php) PHP 对象。
     *   您可以为 DateTime 对象设置时区，以指定源时区。
     *
     * 在格式化之前，格式化程序将根据 [[timeZone]] 转换日期值。
     * 如果不应执行时区转换，则需要将 [[defaultTimeZone]] 和 [[timeZone]] 设置为相同的值。
     * 此外，不会对没有时间信息的值执行转换，例如，`"2017-06-05"`.
     *
     * @param string $format 用于将值转换为日期字符串的格式。
     * 如果为 null，将使用 [[dateFormat]]。
     *
     * 这可以是 "short"，"medium"，"long" 或 "full"，其表示不同长度的预设格式。
     * 它也可以是 [ICU manual](http://userguide.icu-project.org/formatparse/datetime) 中指定的自定义格式。
     *
     * 这也可以是一个前缀为 `php:` 的字符串，
     * 表示可以由 PHP [date()](https://secure.php.net/manual/en/function.date.php) 函数识别的格式。
     *
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值无法计算为日期值。
     * @throws InvalidConfigException 如果日期格式无效。
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
     * 将值格式化为时间。
     * @param int|string|DateTime $value 要格式化的值。
     * 支持以下类型的值：
     *
     * - 表示 UNIX 时间戳的整数。UNIX 时间戳的定义始终为 UTC。
     * - 可以供 [DateTime object](https://secure.php.net/manual/en/datetime.formats.php) 解析的字符串。
     *   时间戳假定在 [[defaultTimeZone]]，除非给出一个明确的时区。
     * - 一个 [DateTime](https://secure.php.net/manual/en/class.datetime.php) 对象。
     *   您可以为 DateTime 对象设置时区，以指定源时区。
     *
     * 在格式化之前，格式化程序将根据 [[timeZone]] 转换日期值。
     * 如果不应执行时区转换，则需要将 [[defaultTimeZone]] 和 [[timeZone]] 设置为相同的值。
     *
     * @param string $format 用于将值转换为日期字符串的格式。
     * 如果为 null，将使用 [[timeFormat]]。
     *
     * 这可以是 "short"，"medium"，"long" 或 "full"，其表示不同长度的预设格式。
     * 它也可以是 [ICU manual](http://userguide.icu-project.org/formatparse/datetime) 中指定的自定义格式。
     *
     * 这也可以是一个前缀为 `php:` 的字符串，
     * 表示可以由 PHP [date()](https://secure.php.net/manual/en/function.date.php) 函数识别的格式。
     *
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值无法计算为日期值。
     * @throws InvalidConfigException 如果日期格式无效。
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
     * 将值格式化为日期时间。
     * @param int|string|DateTime $value 要格式化的值。
     * 支持以下类型的值：
     *
     * - 表示 UNIX 时间戳的整数。UNIX 时间戳的定义始终为 UTC。
     * - 可以供 [DateTime object](https://secure.php.net/manual/en/datetime.formats.php) 解析的字符串。
     *   时间戳假定在 [[defaultTimeZone]]，除非给出一个明确的时区。
     * - 一个 [DateTime](https://secure.php.net/manual/en/class.datetime.php) 对象。
     *   您可以为 DateTime 对象设置时区，以指定源时区。
     *
     * 在格式化之前，格式化程序将根据 [[timeZone]] 转换日期值。
     * 如果不应执行时区转换，则需要将 [[defaultTimeZone]] 和 [[timeZone]] 设置为相同的值。
     *
     * @param string $format 用于将值转换为日期字符串的格式。
     * 如果为 null，则将使用 [[datetimeFormat]]。
     *
     * 这可以是 "short"，"medium"，"long" 或 "full"，其表示不同长度的预设格式。
     * 它也可以是 [ICU manual](http://userguide.icu-project.org/formatparse/datetime) 中指定的自定义格式。
     *
     * 这也可以是一个前缀为 `php:` 的字符串，
     * 表示可以由 PHP [date()](https://secure.php.net/manual/en/function.date.php) 函数识别的格式。
     *
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值无法计算为日期值。
     * @throws InvalidConfigException 如果日期格式无效。
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
     * @var array 短格式的名称以映射到 IntlDateFormatter 常量值。
     */
    private $_dateFormats = [
        'short' => 3, // IntlDateFormatter::SHORT,
        'medium' => 2, // IntlDateFormatter::MEDIUM,
        'long' => 1, // IntlDateFormatter::LONG,
        'full' => 0, // IntlDateFormatter::FULL,
    ];

    /**
     * @param int|string|DateTime $value 要格式化的值。
     * 支持以下类型的值：
     *
     * - 表示 UNIX 时间戳的整数
     * - 可以供 [DateTime object](https://secure.php.net/manual/en/datetime.formats.php) 解析的字符串。
     *   时间戳假定在 [[defaultTimeZone]]，除非给出一个明确的时区。
     * - 一个 [DateTime](https://secure.php.net/manual/en/class.datetime.php) PHP 对象
     *
     * @param string $format 用于将值转换为日期字符串的格式。
     * @param string $type 'date', 'time', or 'datetime'.
     * @throws InvalidConfigException 如果日期格式无效。
     * @return string 格式化的结果。
     */
    private function formatDateTimeValue($value, $format, $type)
    {
        $timeZone = $this->timeZone;
        // avoid time zone conversion for date-only and time-only values
        if ($type === 'date' || $type === 'time') {
            list($timestamp, $hasTimeInfo, $hasDateInfo) = $this->normalizeDatetimeValue($value, true);
            if ($type === 'date' && !$hasTimeInfo || $type === 'time' && !$hasDateInfo) {
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
                    $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], IntlDateFormatter::NONE, $timeZone, $this->calendar);
                } elseif ($type === 'time') {
                    $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, $this->_dateFormats[$format], $timeZone, $this->calendar);
                } else {
                    $formatter = new IntlDateFormatter($this->locale, $this->_dateFormats[$format], $this->_dateFormats[$format], $timeZone, $this->calendar);
                }
            } else {
                $formatter = new IntlDateFormatter($this->locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, $timeZone, $this->calendar, $format);
            }
            if ($formatter === null) {
                throw new InvalidConfigException(intl_get_error_message());
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
     * 将给定的日期时间值规范化为可由各种日期/时间格式化方法采用的 DateTime 对象。
     *
     * @param int|string|DateTime $value 要标准化的日期时间值。
     * 支持以下类型的值：
     *
     * - 表示 UNIX 时间戳的整数
     * - 可以供 [DateTime object](https://secure.php.net/manual/en/datetime.formats.php) 解析的字符串。
     *   时间戳假定在 [[defaultTimeZone]]，除非给出一个明确的时区。
     * - 一个 [DateTime](https://secure.php.net/manual/en/class.datetime.php) PHP 对象
     *
     * @param bool $checkDateTimeInfo 是否还检查日期/时间值是否附加了一些时间和日期信息。
     * 默认为 `false`。如果为 `true`，则该方法将返回一个数组，
     * 其中第一个元素是标准化时间戳，第二个是表示时间戳是否具有时间信息的布尔值，
     * 第三个是表示时间戳是否具有日期信息的布尔值。
     * 此参数自版本 2.0.1 起可用。
     * @return DateTime|array 规范化的日期时间值。
     * 从版本 2.0.1 开始，如果 `$checkDateTimeInfo` 为真，这会返回一个数组。
     * 数组的第一个元素是标准化的时间戳，第二个元素是一个布尔值，
     * 表示时间戳是时间信息还是日期值。
     * 从版本 2.0.12 开始，该数组具有第三个为布尔值的元素，
     * 指示时间戳是否具有日期信息，或者它只是一个时间值。
     * @throws InvalidArgumentException 如果输入值无法计算为日期值。
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
            } elseif (($timestamp = DateTime::createFromFormat('Y-m-d|', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d format (support invalid dates like 2012-13-01)
                return $checkDateTimeInfo ? [$timestamp, false, true] : $timestamp;
            } elseif (($timestamp = DateTime::createFromFormat('Y-m-d H:i:s', $value, new DateTimeZone($this->defaultTimeZone))) !== false) { // try Y-m-d H:i:s format (support invalid dates like 2012-13-01 12:63:12)
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
     * 将浮点数中的日期，时间或日期时间格式化为 UNIX 时间戳（自1970-01-01以来的秒数）。
     * @param int|string|DateTime $value 要格式化的值。
     * 支持以下类型的值：
     *
     * - 表示 UNIX 时间戳的整数
     * - 可以供 [DateTime object](https://secure.php.net/manual/en/datetime.formats.php) 解析的字符串。
     *   时间戳假定在 [[defaultTimeZone]]，除非给出一个明确的时区。
     * - 一个 [DateTime](https://secure.php.net/manual/en/class.datetime.php) PHP 对象
     *
     * @return string 格式化的结果。
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
     * 将日期值与当前时间的时间间隔格式化为易读的形式。
     *
     * 此方法可以以三种不同的方式使用：
     *
     * 1. 使用相对于 `now` 的时间戳。
     * 2. 使用相对于 `$referenceTime` 的时间戳。
     * 3. `DateInterval` 对象。
     *
     * @param int|string|DateTime|DateInterval $value 要格式化的值。
     * 支持以下类型的值：
     *
     * - 表示 UNIX 时间戳的整数
     * - 可以供 [DateTime object](https://secure.php.net/manual/en/datetime.formats.php) 解析的字符串。
     *   时间戳假定在 [[defaultTimeZone]]，除非给出一个明确的时区。
     * - 一个 [DateTime](https://secure.php.net/manual/en/class.datetime.php) PHP 对象
     * - 一个 PHP DateInterval 对象（正值时间间隔表示过去，负值时间间隔表示未来）
     *
     * @param int|string|DateTime $referenceTime 如果指定了该值，当 `$value` 不是 `DateInterval` 对象时，
     * 该值将用作参考时间，而不是 `now`。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值无法计算为日期值。
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

            if ($timestamp === false) {
                // $value is not a valid date/time value, so we try
                // to create a DateInterval with it
                try {
                    $interval = new DateInterval($value);
                } catch (\Exception $e) {
                    // invalid date/time and invalid interval
                    return $this->nullDisplay;
                }
            } else {
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
            if ($interval->s == 0) {
                return Yii::t('yii', 'just now', [], $this->locale);
            }

            return Yii::t('yii', 'in {delta, plural, =1{a second} other{# seconds}}', ['delta' => $interval->s], $this->locale);
        }

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
        if ($interval->s == 0) {
            return Yii::t('yii', 'just now', [], $this->locale);
        }

        return Yii::t('yii', '{delta, plural, =1{a second} other{# seconds}} ago', ['delta' => $interval->s], $this->locale);
    }

    /**
     * 以将时间值值格式为易读形式的持续时间。
     *
     * @param DateInterval|string|int $value 要格式化的值。 可接受的格式：
     *  - [DateInterval object](https://secure.php.net/manual/ru/class.dateinterval.php)
     *  - 整数 - 秒数。 例如：值 `131` 表示 `2 minutes, 11 seconds`
     *  - ISO8601 持续时间格式。例如，以下这些值表示 `1 day, 2 hours, 30 minutes` 持续时间：
     *    `2015-01-01T13:00:00Z/2015-01-02T13:30:00Z` - 在两个日期时间值之间
     *    `2015-01-01T13:00:00Z/P1D2H30M` - 日期时间值之后的时间间隔
     *    `P1D2H30M/2015-01-02T13:30:00Z` - 日期时间值之前的时间间隔
     *    `P1D2H30M` - 只是一个日期间隔
     *    `P-1D2H30M` - 一个负日期间隔（`-1 day, 2 hours, 30 minutes`）
     *
     * @param string $implodeString 将用于连接持续时间部分。 默认为`, `。
     * @param string $negativeSign 当它为负数时，将为格式化持续时间的前缀。 默认为`-`。
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
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 year} other{# years}}', ['delta' => $interval->y], $this->locale);
        }
        if ($interval->m > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 month} other{# months}}', ['delta' => $interval->m], $this->locale);
        }
        if ($interval->d > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 day} other{# days}}', ['delta' => $interval->d], $this->locale);
        }
        if ($interval->h > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 hour} other{# hours}}', ['delta' => $interval->h], $this->locale);
        }
        if ($interval->i > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 minute} other{# minutes}}', ['delta' => $interval->i], $this->locale);
        }
        if ($interval->s > 0) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 second} other{# seconds}}', ['delta' => $interval->s], $this->locale);
        }
        if ($interval->s === 0 && empty($parts)) {
            $parts[] = Yii::t('yii', '{delta, plural, =1{1 second} other{# seconds}}', ['delta' => $interval->s], $this->locale);
            $isNegative = false;
        }

        return empty($parts) ? $this->nullDisplay : (($isNegative ? $negativeSign : '') . implode($implodeString, $parts));
    }


    // number formats


    /**
     * 通过移除小数部分将值格式化为整数。
     *
     * 从版本 2.0.16 起，规范化后出现错误的数字使用误的数字使用回调函数格式化为字符串，
     * 不支持 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
     * 对于非常大的数字，建议将它们以字符串传递，而不是使用科学记数法，否则输出可能是错误的。
     *
     * @param mixed $value 要格式化的值。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
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
     * 将值格式化为十进制数。
     *
     * 属性 [[decimalSeparator]] 将用于表示小数点。
     * 该值自动舍入为定义的十进制数字。
     *
     * 从版本 2.0.16 起，规范化后出现错误的数字使用回调函数格式化为字符串，
     * 不支持 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
     * 对于非常大的数字，建议将它们以字符串传递，而不是使用科学记数法，否则输出可能是错误的。
     *
     * @param mixed $value 要格式化的值。
     * @param int $decimals 小数点后的位数。
     * 如果没有给出，则位数取决于输入值，
     * 并且基于 `NumberFormatter::MIN_FRACTION_DIGITS` 和 `NumberFormatter::MAX_FRACTION_DIGITS` 确定，
     * 可以使用 [[$numberFormatterOptions]] 进行配置。
     * 如果 PHP intl 扩展不可用，则默认值为 `2`。
     * 如果要在 intl 可用和不可用的环境之间保持一致的行为，
     * 则应在此处明确指定值。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
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
     * 将值格式化为带有 "%" 符号的百分比数字。
     *
     * 从版本 2.0.16 起，规范化后出现错误的数字使用回调函数格式化为字符串，
     * 不支持 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
     * 对于非常大的数字，建议将它们以字符串传递，而不是使用科学记数法，否则输出可能是错误的。
     *
     * @param mixed $value 要格式化的值。这必须是一个小数，例如 `0.75` 将格式化为 `75%`。
     * @param int $decimals 小数点后的位数。
     * 如果没有给出，则位数取决于输入值，
     * 并且基于 `NumberFormatter::MIN_FRACTION_DIGITS` 和 `NumberFormatter::MAX_FRACTION_DIGITS` 确定，
     * 可以使用 [[$numberFormatterOptions]] 进行配置。
     * 如果 PHP intl 扩展不可用，则默认值为 `0`。
     * 如果要在 intl 可用和不可用的环境之间保持一致的行为，
     * 则应在此处明确指定值。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
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
     * 将值格式化为科学数字。
     *
     * @param mixed $value 要格式化的值。
     * @param int $decimals 小数点后的位数。
     * 如果没有给出，则位数取决于输入值，
     * 并且基于 `NumberFormatter::MIN_FRACTION_DIGITS` 和 `NumberFormatter::MAX_FRACTION_DIGITS` 确定，
     * 可以使用 [[$numberFormatterOptions]] 进行配置。
     * 如果 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 不可用，默认值取决于您的PHP配置。
     * 如果要在 intl 可用和不可用的环境之间保持一致的行为，
     * 则应在此处明确指定值。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
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
     * 将值格式化为货币编号。
     *
     * 此函数不需要安装 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 也能工作，
     * 但是强烈建议安装它以获得良好的格式化结果。
     *
     * 从版本 2.0.16 起，规范化后出现错误的数字使用回调函数格式化为字符串，
     * 不支持 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
     * 对于非常大的数字，建议将它们以字符串传递，而不是使用科学记数法，否则输出可能是错误的。
     *
     * @param mixed $value 要格式化的值。
     * @param string $currency 3 个字母的 ISO 4217 货币代码，表示要使用的货币。
     * 如果为 null，将使用 [[currencyCode]]。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     * @throws InvalidConfigException 如果没有给出货币且未定义 [[currencyCode]]。
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
            // https://secure.php.net/manual/en/numberformatter.formatcurrency.php#114376
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
     * 将值格式化为数字拼写。
     *
     * 此函数需要安装 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
     *
     * 这个格式化程序不适用于非常大的数字。
     *
     * @param mixed $value 要格式化的值
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     * @throws InvalidConfigException 当 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) 不可用时。
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
     * 将值格式化为数字的序数值。
     *
     * 此函数需要安装 [PHP intl extension](https://secure.php.net/manual/en/book.intl.php)。
     *
     * 这个格式化程序不适用于非常大的数字。
     *
     * @param mixed $value 要格式化的值
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     * @throws InvalidConfigException when the [PHP intl extension](https://secure.php.net/manual/en/book.intl.php) is not available.
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
     * 将以字节为单位的值格式化为易读形式的大小，例如 `12 kB`。
     *
     * 这是 [[asSize]] 的缩写形式。
     *
     * 如果 [[sizeFormatBase]] 是 1024，
     * 将在格式化结果中使用 [binary prefixes](http://en.wikipedia.org/wiki/Binary_prefix) （例如，kibibyte/KiB， mebibyte/MiB， ...）
     *
     * @param string|int|float $value 要格式化的字节值。
     * @param int $decimals 小数点后的位数。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
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
                    return Yii::t('yii', '{nFormatted} B', $params, $this->locale);
                case 1:
                    return Yii::t('yii', '{nFormatted} KiB', $params, $this->locale);
                case 2:
                    return Yii::t('yii', '{nFormatted} MiB', $params, $this->locale);
                case 3:
                    return Yii::t('yii', '{nFormatted} GiB', $params, $this->locale);
                case 4:
                    return Yii::t('yii', '{nFormatted} TiB', $params, $this->locale);
                default:
                    return Yii::t('yii', '{nFormatted} PiB', $params, $this->locale);
            }
        } else {
            switch ($position) {
                case 0:
                    return Yii::t('yii', '{nFormatted} B', $params, $this->locale);
                case 1:
                    return Yii::t('yii', '{nFormatted} kB', $params, $this->locale);
                case 2:
                    return Yii::t('yii', '{nFormatted} MB', $params, $this->locale);
                case 3:
                    return Yii::t('yii', '{nFormatted} GB', $params, $this->locale);
                case 4:
                    return Yii::t('yii', '{nFormatted} TB', $params, $this->locale);
                default:
                    return Yii::t('yii', '{nFormatted} PB', $params, $this->locale);
            }
        }
    }

    /**
     * 将值格式化为易读形式的字节大小，例如 `12 kilobytes`。
     *
     * 如果 [[sizeFormatBase]] 是 1024，
     * 将在格式化结果中使用 [binary prefixes](http://en.wikipedia.org/wiki/Binary_prefix) （例如，kibibyte/KiB， mebibyte/MiB， ...）
     *
     * @param string|int|float $value 要格式化的字节值。
     * @param int $decimals 小数点后的位数。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
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
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->locale);
                case 1:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{kibibyte} other{kibibytes}}', $params, $this->locale);
                case 2:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{mebibyte} other{mebibytes}}', $params, $this->locale);
                case 3:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{gibibyte} other{gibibytes}}', $params, $this->locale);
                case 4:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{tebibyte} other{tebibytes}}', $params, $this->locale);
                default:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{pebibyte} other{pebibytes}}', $params, $this->locale);
            }
        } else {
            switch ($position) {
                case 0:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{byte} other{bytes}}', $params, $this->locale);
                case 1:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{kilobyte} other{kilobytes}}', $params, $this->locale);
                case 2:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{megabyte} other{megabytes}}', $params, $this->locale);
                case 3:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{gigabyte} other{gigabytes}}', $params, $this->locale);
                case 4:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{terabyte} other{terabytes}}', $params, $this->locale);
                default:
                    return Yii::t('yii', '{nFormatted} {n, plural, =1{petabyte} other{petabytes}}', $params, $this->locale);
            }
        }
    }

    /**
     * 将值格式化为易读形式的长度，例如 `12 meters`。
     * 如果需要将值单位更改为最小单位的乘数，
     * 并使 [[systemOfUnits]] 在 [[UNIT_SYSTEM_METRIC]] 或 [[UNIT_SYSTEM_IMPERIAL]] 之间切换，请检查属性 [[baseUnits]]。
     *
     * @param float|int $value 要格式化的值。
     * @param int $decimals 小数点后的位数。
     * @param array $numberOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     * @throws InvalidConfigException 未安装 INTL 或不包含所需信息时。
     * @see asLength
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asLength($value, $decimals = null, $numberOptions = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_LENGTH, self::FORMAT_WIDTH_LONG, $value, null, null, $decimals, $numberOptions, $textOptions);
    }

    /**
     * 将值格式化为易读形式的长度，例如 `12 m`。
     * 这是 [[asLength]] 的缩写形式。
     *
     * 如果需要将值单位更改为最小单位的乘数，
     * 并使 [[systemOfUnits]] 在 [[UNIT_SYSTEM_METRIC]] 或 [[UNIT_SYSTEM_IMPERIAL]] 之间切换，请检查属性 [[baseUnits]]。
     *
     * @param float|int $value 要格式化的值。
     * @param int $decimals 小数点后的位数。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     * @throws InvalidConfigException 未安装 INTL 或不包含所需信息时。
     * @see asLength
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asShortLength($value, $decimals = null, $options = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_LENGTH, self::FORMAT_WIDTH_SHORT, $value, null, null, $decimals, $options, $textOptions);
    }

    /**
     * 将值格式化为易读形式的重量，例如 `12 kilograms`。
     * 如果需要将值单位更改为最小单位的乘数，
     * 并使 [[systemOfUnits]] 在 [[UNIT_SYSTEM_METRIC]] 或 [[UNIT_SYSTEM_IMPERIAL]] 之间切换，请检查属性 [[baseUnits]]。
     *
     * @param float|int $value 要格式化的值。
     * @param int $decimals 小数点后的位数。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     * @throws InvalidConfigException 未安装 INTL 或不包含所需信息时。
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asWeight($value, $decimals = null, $options = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_WEIGHT, self::FORMAT_WIDTH_LONG, $value, null, null, $decimals, $options, $textOptions);
    }

    /**
     * 将值格式化为易读形式的重量，例如 `12 kg`。
     * 这是 [[asWeight]] 的缩写形式。
     *
     * 如果需要将值单位更改为最小单位的乘数，
     * 并使 [[systemOfUnits]] 在 [[UNIT_SYSTEM_METRIC]] 或 [[UNIT_SYSTEM_IMPERIAL]] 之间切换，请检查属性 [[baseUnits]]。
     *
     * @param float|int $value 要格式化的值。
     * @param int $decimals 小数点后的位数。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string 格式化的结果。
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     * @throws InvalidConfigException 未安装 INTL 或不包含所需信息时。
     * @since 2.0.13
     * @author John Was <janek.jan@gmail.com>
     */
    public function asShortWeight($value, $decimals = null, $options = [], $textOptions = [])
    {
        return $this->formatUnit(self::UNIT_WEIGHT, self::FORMAT_WIDTH_SHORT, $value, null, null, $decimals, $options, $textOptions);
    }

    /**
     * @param string $unitType 为 [[UNIT_WEIGHT]] 或者 [[UNIT_LENGTH]]
     * @param string $unitFormat 为 [[FORMAT_WIDTH_SHORT]] 或者 [[FORMAT_WIDTH_LONG]]
     * @param float|int $value 要格式化的值
     * @param float $baseUnit 值的单位作为最小单位的乘数。当为 `null` 时，
     * [[baseUnits]] 将用于确定使用 $unitType 和 $unitSystem 的基本单位。
     * @param string $unitSystem 可以是 [[UNIT_SYSTEM_METRIC]] 或者 [[UNIT_SYSTEM_IMPERIAL]]. When `null`, property [[systemOfUnits]] will be used.
     * @param int $decimals 小数点后的位数。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return string
     * @throws InvalidConfigException 未安装 INTL 或不包含所需信息时
     */
    private function formatUnit($unitType, $unitFormat, $value, $baseUnit, $unitSystem, $decimals, $options, $textOptions)
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        if ($unitSystem === null) {
            $unitSystem = $this->systemOfUnits;
        }
        if ($baseUnit === null) {
            $baseUnit = $this->baseUnits[$unitType][$unitSystem];
        }

        $multipliers = array_values($this->measureUnits[$unitType][$unitSystem]);

        list($params, $position) = $this->formatNumber(
            $this->normalizeNumericValue($value) * $baseUnit,
            $decimals,
            null,
            $multipliers,
            $options,
            $textOptions
        );

        $message = $this->getUnitMessage($unitType, $unitFormat, $unitSystem, $position);

        return (new \MessageFormatter($this->locale, $message))->format([
            '0' => $params['nFormatted'],
            'n' => $params['n'],
        ]);
    }

    /**
     * @param string $unitType 为 [[UNIT_WEIGHT]] 或者 [[UNIT_LENGTH]]
     * @param string $unitFormat 为 [[FORMAT_WIDTH_SHORT]] 或者 [[FORMAT_WIDTH_LONG]]
     * @param string $system 为 [[UNIT_SYSTEM_METRIC]] 或者 [[UNIT_SYSTEM_IMPERIAL]]。 当值为 `null` 时，将使用属性 [[systemOfUnits]]。
     * @param int $position 大小单位的内部位置
     * @return string
     * @throws InvalidConfigException 未安装 INTL 或不包含所需信息时
     */
    private function getUnitMessage($unitType, $unitFormat, $system, $position)
    {
        if (isset($this->_unitMessages[$unitType][$system][$position])) {
            return $this->_unitMessages[$unitType][$system][$position];
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
            throw new InvalidConfigException('Current ICU data version does not contain information about unit type "' . $unitType . '" and unit measure "' . $unitNames[$position] . '". Check system requirements.');
        }

        $message = [];
        foreach ($unitBundle as $key => $value) {
            if ($key === 'dnam') {
                continue;
            }
            $message[] = "$key{{$value}}";
        }

        return $this->_unitMessages[$unitType][$system][$position] = '{n, plural, ' . implode(' ', $message) . '}';
    }

    /**
     * 给出以字节为单位易读形式的格式化值的数值部分。
     *
     * @param string|int|float $value 要格式化的字节值。
     * @param int $decimals 小数点后的位数
     * @param int $maxPosition 如果 $formatBase 是一个数组，则字节单元的最大内部位置
     * @param array|int $formatBase 计算每下一个单位的基数，1000 或 1024，或数组
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return array [parameters for Yii::t containing formatted number, internal position of size unit]
     * @throws InvalidArgumentException 如果输入值不是数字或格式化失败。
     */
    private function formatNumber($value, $decimals, $maxPosition, $formatBase, $options, $textOptions)
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
            $options[NumberFormatter::GROUPING_USED] = false;
        }
        // format the size value
        $params = [
            // this is the unformatted number used for the plural rule
            // abs() to make sure the plural rules work correctly on negative numbers, intl does not cover this
            // http://english.stackexchange.com/questions/9735/is-1-singular-or-plural
            'n' => abs($value),
            // this is the formatted number used for display
            'nFormatted' => $this->asDecimal($value, $decimals, $options, $textOptions),
        ];
        $this->thousandSeparator = $oldThousandSeparator;

        return [$params, $position];
    }

    /**
     * 规范化数字输入值。
     *
     * - 所有的 [empty](https://secure.php.net/manual/en/function.empty.php) 将是 `0`
     * - 一个 [numeric](https://secure.php.net/manual/en/function.is-numeric.php) 字符串将转化为浮点数
     * - 如果它是 [numeric](https://secure.php.net/manual/en/function.is-numeric.php)，则返回其它所有内容，
     *   否则抛出异常。
     *
     * @param mixed $value 输入的值
     * @return float|int 规范化的数值
     * @throws InvalidArgumentException 如果输入值不是数字。
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
     * 创建基于给定类型和格式的数字格式。
     *
     * 您可以重写此方法创建基于模式的数字格式化程序。
     *
     * @param int $style 数字格式器的类型。
     * 类型有： NumberFormatter::DECIMAL, ::CURRENCY, ::PERCENT, ::SCIENTIFIC, ::SPELLOUT, ::ORDINAL
     * ::DURATION, ::PATTERN_RULEBASED, ::DEFAULT_STYLE, ::IGNORE
     * @param int $decimals 小数点后的位数。
     * @param array $options 数字格式化程序的可选配置。此参数将与 [[numberFormatterOptions]] 合并。
     * @param array $textOptions 数字格式化程序的可选配置。此参数将与 [[numberFormatterTextOptions]] 合并。
     * @return NumberFormatter 创建的格式化程序实例
     */
    protected function createNumberFormatter($style, $decimals = null, $options = [], $textOptions = [])
    {
        $formatter = new NumberFormatter($this->locale, $style);

        // set text attributes
        foreach ($this->numberFormatterTextOptions as $name => $attribute) {
            $formatter->setTextAttribute($name, $attribute);
        }
        foreach ($textOptions as $name => $attribute) {
            $formatter->setTextAttribute($name, $attribute);
        }

        // set attributes
        foreach ($this->numberFormatterOptions as $name => $value) {
            $formatter->setAttribute($name, $value);
        }
        foreach ($options as $name => $value) {
            $formatter->setAttribute($name, $value);
        }
        if ($decimals !== null) {
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        }

        // set symbols
        if ($this->decimalSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, $this->decimalSeparator);
        }
        if ($this->thousandSeparator !== null) {
            $formatter->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
            $formatter->setSymbol(NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, $this->thousandSeparator);
        }
        foreach ($this->numberFormatterSymbols as $name => $symbol) {
            $formatter->setSymbol($name, $symbol);
        }

        return $formatter;
    }

    /**
     * 检查给定值及其规范化版本的值是否不同。
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
     * 规范化数字字符串值。
     * @param string $value
     * @return string 规范化数字字符串值
     * @since 2.0.16
     */
    protected function normalizeNumericStringValue($value)
    {
        $separatorPosition = strrpos($value, '.');

        if ($separatorPosition !== false) {
            $integerPart = substr($value, 0, $separatorPosition);
            $fractionalPart = substr($value, $separatorPosition + 1);
        } else {
            $integerPart = $value;
            $fractionalPart = null;
        }

        // truncate insignificant zeros, keep minus
        $integerPart = preg_replace('/^\+?(-?)0*(\d+)$/', '$1$2', $integerPart);
        // for zeros only leave one zero, keep minus
        $integerPart = preg_replace('/^\+?(-?)0*$/', '${1}0', $integerPart);

        if ($fractionalPart !== null) {
            // truncate insignificant zeros
            $fractionalPart = rtrim($fractionalPart, '0');
        }

        $normalizedValue = $integerPart;
        if (!empty($fractionalPart)) {
            $normalizedValue .= '.' . $fractionalPart;
        } elseif ($normalizedValue === '-0') {
            $normalizedValue = '0';
        }

        return $normalizedValue;
    }

    /**
     * 将值格式化为十进制数的回退函数。
     *
     * 属性 [[decimalSeparator]] 将用于表示小数点。
     * 该值自动舍入为定义的十进制数字。
     *
     * @param string|int|float $value 要格式化的值。
     * @param int $decimals 小数点后的位数。默认值是 `2`。
     * @return string 格式化的结果。
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
     * 将值通过移除小数部分格式化为整数的回退函数。
     *
     * @param string|int|float $value 要格式化的值。
     * @return string 格式化的结果。
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
     * 将值格式化为带 "%" 符号百分数的回退函数。
     *
     * 属性 [[decimalSeparator]] 将用于表示小数点。
     * 该值自动舍入为定义的十进制数字。
     *
     * @param string|int|float $value 要格式化的值。
     * @param int $decimals 小数点后的位数。默认值为 `0`。
     * @return string 格式化的结果。
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
     * 将值格式化为货币号的回退函数。
     *
     * @param string|int|float $value 要格式化的值。
     * @param string $currency 3 个字母的 ISO 4217 货币代码，表示要使用的货币。
     * 如果为 null，将使用 [[currencyCode]]。
     * @return string 格式化的结果。
     * @throws InvalidConfigException 如果没有给出货币且未定义 [[currencyCode]]。
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
