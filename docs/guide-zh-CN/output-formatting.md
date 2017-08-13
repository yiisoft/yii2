数据格式器
==============

Yii提供一个格式化类来格式化输出，以使输出数据对终端用户更友好易读，
[[yii\i18n\Formatter]] 是一个助手类，作为 [应用组件](structure-application-components.md) 使用，默认名为`formatter`。

它提供一些方法用来格式化数据，如日期/时间、数字或其他常用的本地化格式，
两种方式使用格式器：

1. 直接使用格式化方法(所有的格式器方法以 `as`做前缀):

   ```php
   echo Yii::$app->formatter->asDate('2014-01-01', 'long'); // 输出: January 1, 2014
   echo Yii::$app->formatter->asPercent(0.125, 2); // 输出: 12.50%
   echo Yii::$app->formatter->asEmail('cebe@example.com'); // 输出: <a href="mailto:cebe@example.com">cebe@example.com</a>
   echo Yii::$app->formatter->asBoolean(true); // 输出: Yes
   // 也可处理null值的输出显示:
   echo Yii::$app->formatter->asDate(null); // 输出: (Not set)
   ```

2. 使用 [[yii\i18n\Formatter::format()|format()]] 方法和格式化名，
   该方法也被一些小部件如[[yii\grid\GridView]] 和 [[yii\widgets\DetailView]]使用，在小部件配置中可以指定列的数据格式。

   ```php
   echo Yii::$app->formatter->format('2014-01-01', 'date'); // 输出: January 1, 2014
   // 可使用数组来指定格式化方法的参数：
   // `2` 是asPercent()方法的参数$decimals的值
   echo Yii::$app->formatter->format(0.125, ['percent', 2]); // 输出: 12.50%
   ```

当[PHP intl extension](http://php.net/manual/en/book.intl.php)安装时，格式器的输出会本地化，
为此可配置格式器的 [[yii\i18n\Formatter::locale|locale]] 属性，如果没有配置，
应用配置 [[yii\base\Application::language|language]] 作为当前区域，更多详情参考 [国际化](tutorial-i18n.md)一节。
然后格式器根据当前区域为日期和数字选择正确的格式，包括月份和星期也会转换到当前语言，
日期格式也会被 [[yii\i18n\Formatter::timeZone|timeZone]] 参数影响，
该参数如果没有明确配置会使用应用的 [[yii\base\Application::timeZone|from the application]] 参数。

日期格式根据不同区域输出不同的结果，如下例所示：
For example the date format call will output different results for different locales:

```php
Yii::$app->formatter->locale = 'en-US';
echo Yii::$app->formatter->asDate('2014-01-01'); // 输出: January 1, 2014
Yii::$app->formatter->locale = 'de-DE';
echo Yii::$app->formatter->asDate('2014-01-01'); // 输出: 1. Januar 2014
Yii::$app->formatter->locale = 'ru-RU';
echo Yii::$app->formatter->asDate('2014-01-01'); // 输出: 1 января 2014 г.
```

> 注意不管[PHP intl extension](http://php.net/manual/en/book.intl.php)有没有安装，PHP编译的ICU库不同，格式化结果可能不同，
> 所以为确保不同环境下得到相同的输出，推荐在每个环境下安装PHP intl扩展以及相同的ICU库，
> 可参考： [为国际化设置PHP环境](tutorial-i18n.md#setup-environment).


配置格式器 <span id="configuring-format"></span>
-------------------------

可配置[[yii\i18n\Formatter|formatter class]]的属性来调整格式器方法的默认格式，
可以在[应用主体配置](concept-configurations.md#application-configurations) 中配置 `formatter` 组件应用到整个项目，
配置样例如下所示，
更多关于可用属性的详情请参考 [[yii\i18n\Formatter|API documentation of the Formatter class]] 和接下来一小节。

```php
'components' => [
    'formatter' => [
        'dateFormat' => 'dd.MM.yyyy',
        'decimalSeparator' => ',',
        'thousandSeparator' => ' ',
        'currencyCode' => 'EUR',
   ],
],
```

格式化日期和时间 <span id="date-and-time"></span>
-------------------------------

格式器类为格式化日期和时间提供了多个方法：
The formatter class provides different methods for formatting date and time values. These are:

- [[yii\i18n\Formatter::asDate()|date]] - 值被格式化成日期，如 `January, 01 2014`.
- [[yii\i18n\Formatter::asTime()|time]] - 值被格式化成时间，如 `14:23`.
- [[yii\i18n\Formatter::asDatetime()|datetime]] - 值被格式化成日期和时间，如 `January, 01 2014 14:23`.
- [[yii\i18n\Formatter::asTimestamp()|timestamp]] - 值被格式化成 [unix 时间戳](http://en.wikipedia.org/wiki/Unix_time) 如 `1412609982`.
- [[yii\i18n\Formatter::asRelativeTime()|relativeTime]] - 值被格式化成和当前时间比较的时间间隔并用人们易读的格式，如`1 hour ago`.

可配置格式器的属性[[yii\i18n\Formatter::$dateFormat|$dateFormat]], [[yii\i18n\Formatter::$timeFormat|$timeFormat]]
和[[yii\i18n\Formatter::$datetimeFormat|$datetimeFormat]]来全局指定[[yii\i18n\Formatter::asDate()|date]],
[[yii\i18n\Formatter::asTime()|time]] 和 [[yii\i18n\Formatter::asDatetime()|datetime]] 方法的日期和时间格式。

格式器默认会使用一个快捷格式，它根据当前启用的区域来解析，
这样日期和时间会格式化成用户国家和语言通用的格式，
有四种不同的快捷格式：

- `en_GB`区域的 `short` 会打印日期为 `06/10/2014`，时间为 `15:58`
- `medium` 会分别打印 `6 Oct 2014` 和 `15:58:42`,
- `long` 会分别打印 `6 October 2014` 和 `15:58:42 GMT`,
- `full` 会分别打印 `Monday, 6 October 2014` 和 `15:58:42 GMT`.

另外你可使用[ICU 项目](http://site.icu-project.org/) 定义的语法来自定义格式，
ICU项目在该URL：<http://userguide.icu-project.org/formatparse/datetime>下的手册有介绍，
或者可使用PHP [date()](http://php.net/manual/de/function.date.php) 方法的语法字符串并加上前缀`php:`.

```php
// ICU 格式化
echo Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); // 2014-10-06
// PHP date()-格式化
echo Yii::$app->formatter->asDate('now', 'php:Y-m-d'); // 2014-10-06
```

### 时区 <span id="time-zones"></span>

当格式化日期和时间时，Yii会将它们转换为对应的 [[yii\i18n\Formatter::timeZone|configured time zone]] 时区，
输入的值在没有指定时区时候会被当作UTC时间，因此，推荐存储所有的日期和时间为UTC而不是UNIX时间戳，UNIX通常也是UTC。
如果输入值所在的时区不同于UTC，时区应明确指定，如下所示：

```php
// 假定 Yii::$app->timeZone = 'Europe/Berlin';
echo Yii::$app->formatter->asTime(1412599260); // 14:41:00
echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 14:41:00
echo Yii::$app->formatter->asTime('2014-10-06 14:41:00 CEST'); // 14:41:00
```

> Note: 时区从属于全世界各国政府定的规则，可能会频繁的变更，因此你的系统的时区数据库可能不是最新的信息，
> 可参考 [ICU manual](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data)
> 关于更新时区数据库的详情，
> 也可参考：[为国际化设置PHP环境](tutorial-i18n.md#setup-environment).


格式化数字 <span id="numbers"></span>
------------------

格式器类提供如下方法格式化数值：
For formatting numeric values the formatter class provides the following methods:

- [[yii\i18n\Formatter::asInteger()|integer]] - 值被格式化成整型，如 `42`.
- [[yii\i18n\Formatter::asDecimal()|decimal]] - 值被格式化成十进制数字并带有小数位和千分位，如 `42.123`.
- [[yii\i18n\Formatter::asPercent()|percent]] - 值被格式化成百分率，如 `42%`.
- [[yii\i18n\Formatter::asScientific()|scientific]] - 值被格式化成科学计数型，如`4.2E4`.
- [[yii\i18n\Formatter::asCurrency()|currency]] - 值被格式化成货币格式，如 `£420.00`.
- [[yii\i18n\Formatter::asSize()|size]] - 字节值被格式化成易读的值，如 `410 kibibytes`.

可配置[[yii\i18n\Formatter::decimalSeparator|decimalSeparator]] 和 [[yii\i18n\Formatter::thousandSeparator|thousandSeparator]]
属性来调整数字格式化的格式，默认和当前区域相同。

更多高级配置， [[yii\i18n\Formatter::numberFormatterOptions]] 和 [[yii\i18n\Formatter::numberFormatterTextOptions]]
可用于配置内部使用 [NumberFormatter class](http://php.net/manual/en/class.numberformatter.php)

为调整数字的小数部分的最大值和最小值，可配置如下属性：

```php
[
    NumberFormatter::MIN_FRACTION_DIGITS => 0,
    NumberFormatter::MAX_FRACTION_DIGITS => 2,
]
```

其他格式器  <span id="other"></span>
----------------

除了日期、时间和数字格式化外，Yii提供其他用途提供一些实用的格式器：
Additional to date, time and number formatting, Yii provides a set of other useful formatters for different purposes:

- [[yii\i18n\Formatter::asRaw()|raw]] - 输出值和原始值一样，除了`null`值会用[[nullDisplay]]格式化，这是一个伪格式器；
- [[yii\i18n\Formatter::asText()|text]] - 值会经过HTML编码； 
  这是[GridView DataColumn](output-data-widgets.md#data-column)默认使用的格式；
- [[yii\i18n\Formatter::asNtext()|ntext]] - 值会格式化成HTML编码的纯文本，新行会转换成换行符；
- [[yii\i18n\Formatter::asParagraphs()|paragraphs]] - 值会转换成HTML编码的文本段落，用`<p>`标签包裹；
- [[yii\i18n\Formatter::asHtml()|html]] - 值会被[[HtmlPurifier]]过滤来避免XSS跨域攻击，可传递附加选项如`['html', ['Attr.AllowedFrameTargets' => ['_blank']]]；
- [[yii\i18n\Formatter::asEmail()|email]] - 值会格式化成 `mailto`-链接；
- [[yii\i18n\Formatter::asImage()|image]] - 值会格式化成图片标签；
- [[yii\i18n\Formatter::asUrl()|url]] - 值会格式化成超链接；
- [[yii\i18n\Formatter::asBoolean()|boolean]] - 值会格式化成布尔型值，默认情况下 `true` 对应 `Yes`，`false` 对应 `No`，
  可根据应用语言配置进行翻译，可以配置[[yii\i18n\Formatter::booleanFormat]]-属性来调整；

`null`-值 <span id="null-values"></span>
-------------

对于PHP的`null`值，格式器类会打印一个占位符而不是空字符串，空字符串默认会显示对应当前语言`(not set)`,
可配置[[yii\i18n\Formatter::nullDisplay|nullDisplay]]-属性配置一个自定义占位符，
如果对处理`null`值没有特殊要求，可设置[[yii\i18n\Formatter::nullDisplay|nullDisplay]] 为 `null`.
