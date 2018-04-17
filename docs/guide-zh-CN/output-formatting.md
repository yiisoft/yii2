数据格式器（Data Formatting）
==========================

你可以使用 `formatter` [application component](structure-application-components.md) 来格式化数据。
默认 `fomatter` 由 [[yii\i18n\Formatter]] 来实现，这个组件提供了一系列关于日期/时间，数字，货币等的格式化方法。
使用方法如下：

```php
$formatter = \Yii::$app->formatter;

// output: January 1, 2014
echo $formatter->asDate('2014-01-01', 'long');
 
// output: 12.50%
echo $formatter->asPercent(0.125, 2);
 
// output: <a href="mailto:cebe@example.com">cebe@example.com</a>
echo $formatter->asEmail('cebe@example.com'); 

// output: Yes
echo $formatter->asBoolean(true); 
// it also handles display of null values:

// output: (Not set)
echo $formatter->asDate(null); 
```

我们可以看到，所有的方法都形似 `asXyz()`，这个 `Xzy` 就是所支持的格式化类型。
当然你也可以使用类方法 [[yii\i18n\Formatter::format()|format()]] 来进行格式化，通过这个类方法，你可以更自由地控制格式化的数据，这时候，类方法通常配合 [[yii\grid\GridView]] 或者 [[yii\widgets\DetailView]] 来使用。

举个例子：

```php
// output: January 1, 2014
echo Yii::$app->formatter->format('2014-01-01', 'date'); 

// 你可以在第二个参数指定一个数组，这个数组提供了一些配置的参数
// 例如这个 2 就是 asPercent() 方法的 $decimals 参数
// output: 12.50%
echo Yii::$app->formatter->format(0.125, ['percent', 2]); 
```

> Note: `formatter` 组件用来格式化最终展示给用户的数据。 
> 如果你想要将用户的输入进行格式化或者只是将一些别的日期数据进行格式化（这里的格式化说的是机器可读的格式化），
> 不要使用这个组件，
> 而应该使用 [[yii\validators\DateValidator]] 和 [[yii\validators\NumberValidator]] 进行用户输入格式化
> 对于机器可读的日期和时间格式之间的简单转换，
> PHP 方法 [date()](http://php.net/manual/en/function.date.php) 就足够了。

## 配置 Formatter（Configuring Formatter） <span id="configuring-formatter"></span>

可以对 `formatter` 组件在 [application configuration](concept-configurations.md#application-configurations) 中进行配置。
例如，

```php
return [
    'components' => [
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'EUR',
       ],
    ],
];
```

可以参考 [[yii\i18n\Formatter]] 的配置


## 格式化时间/日期数据（Formatting Date and Time Values） <span id="date-and-time"></span>

默认支持一下几种格式化格式

- [[yii\i18n\Formatter::asDate()|date]]：这个变量将被格式化为日期 `January 01, 2014`。
- [[yii\i18n\Formatter::asTime()|time]]：这个变量将被格式化为时间 `14:23`。
- [[yii\i18n\Formatter::asDatetime()|datetime]]：这个变量将被格式化为日期+时间 `January 01, 2014 14:23`。
- [[yii\i18n\Formatter::asTimestamp()|timestamp]]：这个变量将被格式化为 UNIX 时间戳 [unix timestamp](http://en.wikipedia.org/wiki/Unix_time)，例如 `1412609982`。
- [[yii\i18n\Formatter::asRelativeTime()|relativeTime]]：这个变量将被格式化为人类可读的
  当前相对时间 `1 hour ago`。
- [[yii\i18n\Formatter::asDuration()|duration]]：这个变量将被格式化为人类可读的时长 `1 day, 2 minutes`。

时间/日期数据默认使用 [[yii\i18n\Formatter::asDate()|date]], [[yii\i18n\Formatter::asTime()|time]]， 
[[yii\i18n\Formatter::asDatetime()|datetime]] 方法进行格式化,
你可以对他们进行一些自己的配置，只需在配置文件里配置 [[yii\i18n\Formatter::dateFormat|dateFormat]], 
[[yii\i18n\Formatter::timeFormat|timeFormat]], 和 [[yii\i18n\Formatter::datetimeFormat|datetimeFormat]] 即可。

同时，你还可以配置它使用 [ICU syntax](http://userguide.icu-project.org/formatparse/datetime)，
同时你也可以配置它使用 [PHP date() 语法](http://php.net/manual/en/function.date.php)，只需要加上 `php:` 前缀即可。
例如，

```php
// ICU format
echo Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); // 2014-10-06

// PHP date()-format
echo Yii::$app->formatter->asDate('now', 'php:Y-m-d'); // 2014-10-06
```

> Info: Some letters of the PHP format syntax are not supported by ICU and thus the PHP intl extension and can not be used
> in Yii formatter. Most of these (`w`, `t`, `L`, `B`, `u`, `I`, `Z`) are not really useful for formatting dates but rather
> used when doing date math. `S` and `U` however may be useful. Their behavior can be achived by doing the following:
>
> - for `S`, which is the English ordinal suffix for the day of the month (e.g. st, nd, rd or th.), the following replacement can be used:
>
>   ```php
>   $f = Yii::$app->formatter;
>   $d = $f->asOrdinal($f->asDate('2017-05-15', 'php:j'));
>   echo "On the $d day of the month.";  // prints "On the 15th day of the month."
>   ```
>
> - for `U`, the Unix Epoch, you can use the [[yii\i18n\Formatter::asTimestamp()|timestamp]] format.

When working with applications that need to support multiple languages, you often need to specify different date
and time formats for different locales. To simplify this task, you may use format shortcuts (e.g. `long`, `short`), instead.
The formatter will turn a format shortcut into an appropriate format according to the currently active [[yii\i18n\Formatter::locale|locale]].
The following format shortcuts are supported (the examples assume `en_GB` is the active locale):

- `short`: will output `06/10/2014` for date and `15:58` for time;
- `medium`: will output `6 Oct 2014` and `15:58:42`;
- `long`: will output `6 October 2014` and `15:58:42 GMT`;
- `full`: will output `Monday, 6 October 2014` and `15:58:42 GMT`.

版本 2.0.7 起，支持格式化日期为不同的系统时钟，
Please refer to the API documentation of the formatters [[yii\i18n\Formatter::$calendar|$calendar]]-property on how to set a different calendar.


### 时区（Time Zones） <span id="time-zones"></span>

格式化时间/日期数据时，你会将他们转换成 [[yii\i18n\Formatter::timeZone|time zone]]
这个时候，默认的时区为 UTC，除非你另外指定
[[yii\i18n\Formatter::defaultTimeZone]]。

下面使用 `Europe/Berlin` 作为默认 [[yii\i18n\Formatter::timeZone|time zone]] 

```php
// formatting a UNIX timestamp as a time
echo Yii::$app->formatter->asTime(1412599260); // 14:41:00

// formatting a datetime string (in UTC) as a time 
echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 14:41:00

// formatting a datetime string (in CEST) as a time
echo Yii::$app->formatter->asTime('2014-10-06 14:41:00 CEST'); // 14:41:00
```

If the [[yii\i18n\Formatter::timeZone|time zone]] is not set explicitly on the formatter component, the 
[[yii\base\Application::timeZone|time zone configured in the application]] is used, which is the same time zone
as set in the PHP configuration.

> 不同的政府和地区政策决定不同的时区, 
> 你在你的时区数据库中可能拿不到最新的数据。
> 这时你可以戳 [ICU manual](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) 来查看如何更新时区。
> 同时，这篇也可以作为参考 [Setting up your PHP environment for internationalization](tutorial-i18n.md#setup-environment)。


## 格式化数字（Formatting Numbers） <span id="numbers"></span>

`formatter` 支持如下的方法

- [[yii\i18n\Formatter::asInteger()|integer]]: 这个变量将被格式化为整形 e.g. `42`.
- [[yii\i18n\Formatter::asDecimal()|decimal]]: 这个变量将被格式化为带着逗号的指定精度的浮点型，
  例如：`2,542.123` 或 `2.542,123`。
- [[yii\i18n\Formatter::asPercent()|percent]]: 这个变量将被格式化为百分比 e.g. `42%`.
- [[yii\i18n\Formatter::asScientific()|scientific]]: 这个变量将被格式化为科学计数法 e.g. `4.2E4`.
- [[yii\i18n\Formatter::asCurrency()|currency]]: 这个变量将被格式化为货币，
  例如：`£420.00`。
  使用这个方法前请确认是否已经正确配置 [[yii\i18n\Formatter::locale|locale]]
- [[yii\i18n\Formatter::asSize()|size]]: 这个变量将被格式化为人类可读的字节数 e.g. `410 kibibytes`.
- [[yii\i18n\Formatter::asShortSize()|shortSize]]: 这个变量将被格式化为人类可读的字节数（缩写） [[yii\i18n\Formatter::asSize()|size]]，例如：`410 KiB`。

你可以使用 [[yii\i18n\Formatter::decimalSeparator|decimalSeparator]] 和 
[[yii\i18n\Formatter::thousandSeparator|thousandSeparator]] 来进行调整。
他们都会根据当前的 [[yii\i18n\Formatter::locale|locale]] 来进行格式化.

如果你想要进行更高级的配置, 可以使用 [[yii\i18n\Formatter::numberFormatterOptions]] 和 
[[yii\i18n\Formatter::numberFormatterTextOptions]]，
[NumberFormatter class](http://php.net/manual/en/class.numberformatter.php) 来进行格式化。
例如，为了调整小数部分的最大值和最小值，你可以配置 [[yii\i18n\Formatter::numberFormatterOptions]] 如下：

```php
'numberFormatterOptions' => [
    NumberFormatter::MIN_FRACTION_DIGITS => 0,
    NumberFormatter::MAX_FRACTION_DIGITS => 2,
]
```


## 其他的格式化（Other Formats） <span id="other"></span>

除了时间/日期和数字的格式化，Yii 还支持如下的常用格式化

- [[yii\i18n\Formatter::asRaw()|raw]]: the value is outputted as is, this is a pseudo-formatter that has no effect except that
  `null` values will be formatted using [[nullDisplay]].
- [[yii\i18n\Formatter::asText()|text]]: the value is HTML-encoded.
  This is the default format used by the [GridView DataColumn](output-data-widgets.md#data-column).
- [[yii\i18n\Formatter::asNtext()|ntext]]: the value is formatted as an HTML-encoded plain text with newlines converted
  into line breaks.
- [[yii\i18n\Formatter::asParagraphs()|paragraphs]]: the value is formatted as HTML-encoded text paragraphs wrapped
  into `<p>` tags.
- [[yii\i18n\Formatter::asHtml()|html]]: the value is purified using [[HtmlPurifier]] to avoid XSS attacks. You can
  pass additional options such as `['html', ['Attr.AllowedFrameTargets' => ['_blank']]]`.
- [[yii\i18n\Formatter::asEmail()|email]]: the value is formatted as a `mailto`-link.
- [[yii\i18n\Formatter::asImage()|image]]: the value is formatted as an image tag.
- [[yii\i18n\Formatter::asUrl()|url]]: the value is formatted as a hyperlink.
- [[yii\i18n\Formatter::asBoolean()|boolean]]: the value is formatted as a boolean. By default `true` is rendered
  as `Yes` and `false` as `No`, translated to the current application language. You can adjust this by configuring
  the [[yii\i18n\Formatter::booleanFormat]] property.


## 空值（Null Values） <span id="null-values"></span>

空值（`null`）会被特殊格式化. `fommater` 默认会将空值格式化为 `(not set)` 对应的当前的语言.
你可以配置 [[yii\i18n\Formatter::nullDisplay|nullDisplay]]
属性来进行个性化。


## 本地日期格式化（Localizing Data Format） <span id="localizing-data-format"></span>

As aforementioned, the formatter may use the currently active [[yii\i18n\Formatter::locale|locale]] to determine how
to format a value that is suitable in the target country/region. For example, the same date value may be formatted
differently for different locales:

```php
Yii::$app->formatter->locale = 'en-US';
echo Yii::$app->formatter->asDate('2014-01-01'); // output: January 1, 2014

Yii::$app->formatter->locale = 'de-DE';
echo Yii::$app->formatter->asDate('2014-01-01'); // output: 1. Januar 2014

Yii::$app->formatter->locale = 'ru-RU';
echo Yii::$app->formatter->asDate('2014-01-01'); // output: 1 января 2014 г.
```

默认配置下，当前 [[yii\i18n\Formatter::locale|locale]] 决定于 [[yii\base\Application::language]].
你可以覆盖 [[yii\i18n\Formatter::locale]] 属性来满足不同的需要。

> Note: Yii formatter 依赖 [PHP intl extension](http://php.net/manual/en/book.intl.php) 
> 来进行本地数据格式化
> 因为不同的 ICU 库可能会导致不同的输出，所以请在你的所有机器上保持 ICU 库的一致性.。
> 请参阅 [Setting up your PHP environment for internationalization](tutorial-i18n.md#setup-environment)。
>
> 如果 `intl` 扩展没有被安装，数据格式化不会考虑本地化. 
> 
> 在 32 位系统中，1901 年前或者 2038 年后的日期数据将不会被本地化，
> 因为 ICU 使用的是 32 位的 UNIX 时间戳。
