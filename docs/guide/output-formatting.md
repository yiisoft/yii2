<<<<<<< HEAD
Data Formatter
==============

For formatting of outputs Yii provides a formatter class to make data more readable for users.
[[yii\i18n\Formatter]] is a helper class that is registered as an [application component](structure-application-components.md) named `formatter` by default.

It provides a set of methods for data formatting purpose such as date/time values, numbers and other commonly used formats in a localized way.
The formatter can be used in two different ways.

1. Using the formatting methods (all formatter methods prefixed with `as`) directly:

   ```php
   echo Yii::$app->formatter->asDate('2014-01-01', 'long'); // output: January 1, 2014
   echo Yii::$app->formatter->asPercent(0.125, 2); // output: 12.50%
   echo Yii::$app->formatter->asEmail('cebe@example.com'); // output: <a href="mailto:cebe@example.com">cebe@example.com</a>
   echo Yii::$app->formatter->asBoolean(true); // output: Yes
   // it also handles display of null values:
   echo Yii::$app->formatter->asDate(null); // output: (Not set)
   ```

2. Using the [[yii\i18n\Formatter::format()|format()]] method and the format name.
   This method is also used by widgets like [[yii\grid\GridView]] and [[yii\widgets\DetailView]] where you can specify
   the data format of a column in the widget configuration.

   ```php
   echo Yii::$app->formatter->format('2014-01-01', 'date'); // output: January 1, 2014
   // you can also use an array to specify parameters for the format method:
   // `2` is the value for the $decimals parameter of the asPercent()-method.
   echo Yii::$app->formatter->format(0.125, ['percent', 2]); // output: 12.50%
   ```

All output of the formatter is localized when the [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed.
You can configure the [[yii\i18n\Formatter::locale|locale]] property of the formatter for this. If not configured, the
application [[yii\base\Application::language|language]] is used as the locale. See the [section on internationalization](tutorial-i18n.md) for more details.
The Formatter will then choose the correct format for dates and numbers according to the locale including names of month and
weekdays translated to the current language. Date formats are also affected by the [[yii\i18n\Formatter::timeZone|timeZone]]
which will also be taken from the application [[yii\base\Application::timeZone|timeZone]] if not configured explicitly.

For example the date format call will output different results for different locales:

```php
Yii::$app->formatter->locale = 'en-US';
echo Yii::$app->formatter->asDate('2014-01-01'); // output: January 1, 2014
Yii::$app->formatter->locale = 'de-DE';
echo Yii::$app->formatter->asDate('2014-01-01'); // output: 1. Januar 2014
Yii::$app->formatter->locale = 'ru-RU';
echo Yii::$app->formatter->asDate('2014-01-01'); // output: 1 января 2014 г.
```

> Note that formatting may differ between different versions of the ICU library compiled with PHP and also based on the fact whether the
> [PHP intl extension](http://php.net/manual/en/book.intl.php) is installed or not. So to ensure your website works with the same output
> in all environments it is recommended to install the PHP intl extension in all environments and verify that the version of the ICU library
> is the same. See also: [Setting up your PHP environment for internationalization](tutorial-i18n.md#setup-environment).
>
> Note also that even if the intl extension is installed, formatting date and time values for years >=2038 or <=1901
> on 32bit systems will fall back to the PHP implementation, which does not provide localized month and day names,
> because intl uses a 32bit UNIX timestamp internally. On a 64bit system the intl formatter is used in all cases if installed.


Configuring the formatter <span id="configuring-format"></span>
-------------------------

The default formats used by the formatter methods can be adjusted using the properties of the [[yii\i18n\Formatter|formatter class]].
You can adjust these values application wide by configuring the `formatter` component in your [application config](concept-configurations.md#application-configurations).
An example configuration is shown in the following.
For more details about the available properties check out the [[yii\i18n\Formatter|API documentation of the Formatter class]] and the following subsections.

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

Formatting Date and Time values <span id="date-and-time"></span>
-------------------------------

The formatter class provides different methods for formatting date and time values. These are:

- [[yii\i18n\Formatter::asDate()|date]] - the value is formatted as a date e.g. `January 01, 2014`.
- [[yii\i18n\Formatter::asTime()|time]] - the value is formatted as a time e.g. `14:23`.
- [[yii\i18n\Formatter::asDatetime()|datetime]] - the value is formatted as date and time e.g. `January 01, 2014 14:23`.
- [[yii\i18n\Formatter::asTimestamp()|timestamp]] - the value is formatted as a [unix timestamp](http://en.wikipedia.org/wiki/Unix_time) e.g. `1412609982`.
- [[yii\i18n\Formatter::asRelativeTime()|relativeTime]] - the value is formatted as the time interval between a date
  and now in human readable form e.g. `1 hour ago`.

The date and time format for the [[yii\i18n\Formatter::asDate()|date]], [[yii\i18n\Formatter::asTime()|time]], and
[[yii\i18n\Formatter::asDatetime()|datetime]] methods can be specified globally by configuring the formatters
properties [[yii\i18n\Formatter::$dateFormat|$dateFormat]], [[yii\i18n\Formatter::$timeFormat|$timeFormat]], and
[[yii\i18n\Formatter::$datetimeFormat|$datetimeFormat]].

By default the formatter uses a shortcut format that is interpreted differently according to the currently active locale
so that dates and times are formatted in a way that is common for the users country and language.
There are four different shortcut formats available:

- `short` in `en_GB` locale will print for example `06/10/2014` for date and `15:58` for time, while
- `medium` will print `6 Oct 2014` and `15:58:42`,
- `long` will print `6 October 2014` and `15:58:42 GMT`,
- and `full` will print `Monday, 6 October 2014` and `15:58:42 GMT`.

Additionally you can specify custom formats using the syntax defined by the
[ICU Project](http://site.icu-project.org/) which is described in the ICU manual under the following URL:
<http://userguide.icu-project.org/formatparse/datetime>. Alternatively you can use the syntax that can be recognized by the
PHP [date()](http://php.net/manual/en/function.date.php) function using a string that is prefixed with `php:`.
=======
Data Formatting
===============

To display data in a more readable format for users, you may format them using the `formatter` [application component](structure-application-components.md).
By default the formatter is implemented by [[yii\i18n\Formatter]] which provides a set of methods to format data as 
date/time, numbers, currencies, and other commonly used formats. You can use the formatter like the following,

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

As you can see, all these methods are named as `asXyz()`, where `Xyz` stands for a supported format. Alternatively,
you may format data using the generic method [[yii\i18n\Formatter::format()|format()]], which allows you to control
the desired format programmatically and is commonly used by widgets like [[yii\grid\GridView]] and [[yii\widgets\DetailView]].
For example,

```php
// output: January 1, 2014
echo Yii::$app->formatter->format('2014-01-01', 'date'); 

// you can also use an array to specify parameters for the format method:
// `2` is the value for the $decimals parameter of the asPercent()-method.
// output: 12.50%
echo Yii::$app->formatter->format(0.125, ['percent', 2]); 
```

> Note: The formatter component is designed to format values to be displayed for the end user. If you want
> to convert user input into machine readable format, or just format a date in a machine readable format,
> the formatter is not the right tool for that.
> To convert user input for date and number values you may use [[yii\validators\DateValidator]] and [[yii\validators\NumberValidator]]
> respectively. For simple conversion between machine readable date and time formats,
> the PHP [date()](http://php.net/manual/en/function.date.php)-function is enough.

## Configuring Formatter <span id="configuring-formatter"></span>

You may customize the formatting rules by configuring the `formatter` component in the [application configuration](concept-configurations.md#application-configurations).
For example,

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

Please refer to [[yii\i18n\Formatter]] for the properties that may be configured.


## Formatting Date and Time Values <span id="date-and-time"></span>

The formatter supports the following output formats that are related with date and time:

- [[yii\i18n\Formatter::asDate()|date]]: the value is formatted as a date, e.g. `January 01, 2014`.
- [[yii\i18n\Formatter::asTime()|time]]: the value is formatted as a time, e.g. `14:23`.
- [[yii\i18n\Formatter::asDatetime()|datetime]]: the value is formatted as date and time, e.g. `January 01, 2014 14:23`.
- [[yii\i18n\Formatter::asTimestamp()|timestamp]]: the value is formatted as a [unix timestamp](http://en.wikipedia.org/wiki/Unix_time), e.g. `1412609982`.
- [[yii\i18n\Formatter::asRelativeTime()|relativeTime]]: the value is formatted as the time interval between a date
  and now in human readable form e.g. `1 hour ago`.
- [[yii\i18n\Formatter::asDuration()|duration]]: the value is formatted as a duration in human readable format. e.g. `1 day, 2 minutes`.

The default date and time formats used for the [[yii\i18n\Formatter::asDate()|date]], [[yii\i18n\Formatter::asTime()|time]],
and [[yii\i18n\Formatter::asDatetime()|datetime]] methods can be customized globally by configuring  
[[yii\i18n\Formatter::dateFormat|dateFormat]], [[yii\i18n\Formatter::timeFormat|timeFormat]], and
[[yii\i18n\Formatter::datetimeFormat|datetimeFormat]].

You can specify date and time formats using the [ICU syntax](http://userguide.icu-project.org/formatparse/datetime).
You can also use the [PHP date() syntax](http://php.net/manual/en/function.date.php) with a prefix `php:` to differentiate
it from ICU syntax. For example,
>>>>>>> master

```php
// ICU format
echo Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); // 2014-10-06
<<<<<<< HEAD
=======

>>>>>>> master
// PHP date()-format
echo Yii::$app->formatter->asDate('now', 'php:Y-m-d'); // 2014-10-06
```

<<<<<<< HEAD
### Time zones <span id="time-zones"></span>

When formatting date and time values, Yii will convert them to the [[yii\i18n\Formatter::timeZone|configured time zone]].
Therefore the input value is assumed to be in UTC unless a time zone is explicitly given. For this reason
it is recommended to store all date and time values in UTC, preferably as a UNIX timestamp, which is always UTC by definition.
If the input value is in a time zone different from UTC, the time zone has to be stated explicitly like in the following example:

```php
// assuming Yii::$app->timeZone = 'Europe/Berlin';
echo Yii::$app->formatter->asTime(1412599260); // 14:41:00
echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 14:41:00
echo Yii::$app->formatter->asTime('2014-10-06 14:41:00 CEST'); // 14:41:00
```

Since version 2.0.1 it is also possible to configure the time zone that is assumed for timestamps that do not include a time zone
identifier like the second example in the code above. You can set [[yii\i18n\Formatter::defaultTimeZone]] to the time zone you use for data storage.

> Note: As time zones are subject to rules made by the governments around the world and may change frequently, it is
> likely that you do not have the latest information in the time zone database installed on your system.
> You may refer to the [ICU manual](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data)
> for details on updating the time zone database.
> See also: [Setting up your PHP environment for internationalization](tutorial-i18n.md#setup-environment).


Formatting Numbers <span id="numbers"></span>
------------------

For formatting numeric values the formatter class provides the following methods:

- [[yii\i18n\Formatter::asInteger()|integer]] - the value is formatted as an integer e.g. `42`.
- [[yii\i18n\Formatter::asDecimal()|decimal]] - the value is formatted as a decimal number considering decimal and thousand
  separators e.g. `2,542.123` or `2.542,123`.
- [[yii\i18n\Formatter::asPercent()|percent]] - the value is formatted as a percent number e.g. `42%`.
- [[yii\i18n\Formatter::asScientific()|scientific]] - the value is formatted as a number in scientific format e.g. `4.2E4`.
- [[yii\i18n\Formatter::asCurrency()|currency]] - the value is formatted as a currency value e.g. `£420.00`.
  Note that for this function to work properly, the locale needs to include a country part e.g. `en_GB` or `en_US` because language only
  would be ambiguous in this case.
- [[yii\i18n\Formatter::asSize()|size]] - the value that is a number of bytes is formatted as a human readable size e.g. `410 kibibytes`.
- [[yii\i18n\Formatter::asShortSize()|shortSize]] - is the short version of [[yii\i18n\Formatter::asSize()|size]], e.g. `410 KiB`.

The format for number formatting can be adjusted using the [[yii\i18n\Formatter::decimalSeparator|decimalSeparator]] and
[[yii\i18n\Formatter::thousandSeparator|thousandSeparator]] which are set by default according to the locale.

For more advanced configuration, [[yii\i18n\Formatter::numberFormatterOptions]] and [[yii\i18n\Formatter::numberFormatterTextOptions]]
can be used to configure the internally used [NumberFormatter class](http://php.net/manual/en/class.numberformatter.php)

For example, to adjust the maximum and minimum value of fraction digits, you can configure [[yii\i18n\Formatter::numberFormatterOptions]] property like the following:
=======
When working with applications that need to support multiple languages, you often need to specify different date
and time formats for different locales. To simplify this task, you may use format shortcuts (e.g. `long`, `short`), instead.
The formatter will turn a format shortcut into an appropriate format according to the currently active [[yii\i18n\Formatter::locale|locale]].
The following format shortcuts are supported (the examples assume `en_GB` is the active locale):

- `short`: will output `06/10/2014` for date and `15:58` for time;
- `medium`: will output `6 Oct 2014` and `15:58:42`;
- `long`: will output `6 October 2014` and `15:58:42 GMT`;
- `full`: will output `Monday, 6 October 2014` and `15:58:42 GMT`.

Since version 2.0.7 it is also possible to format dates in different calendar systems.
Please refer to the API documentation of the formatters [[yii\i18n\Formatter::$calendar|$calendar]]-property on how to set a different calendar.


### Time Zones <span id="time-zones"></span>

When formatting date and time values, Yii will convert them to the target [[yii\i18n\Formatter::timeZone|time zone]].
The value being formatted is assumed to be in UTC, unless a time zone is explicitly given or you have configured
[[yii\i18n\Formatter::defaultTimeZone]].

In the following examples, we assume the target [[yii\i18n\Formatter::timeZone|time zone]] is set as `Europe/Berlin`. 

```php
// formatting a UNIX timestamp as a time
echo Yii::$app->formatter->asTime(1412599260); // 14:41:00

// formatting a datetime string (in UTC) as a time 
echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 14:41:00

// formatting a datetime string (in CEST) as a time
echo Yii::$app->formatter->asTime('2014-10-06 14:41:00 CEST'); // 14:41:00
```

> Note: As time zones are subject to rules made by the governments around the world and may change frequently, it is
> likely that you do not have the latest information in the time zone database installed on your system.
> You may refer to the [ICU manual](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data)
> for details on updating the time zone database. Please also read
> [Setting up your PHP environment for internationalization](tutorial-i18n.md#setup-environment).


## Formatting Numbers <span id="numbers"></span>

The formatter supports the following output formats that are related with numbers:

- [[yii\i18n\Formatter::asInteger()|integer]]: the value is formatted as an integer e.g. `42`.
- [[yii\i18n\Formatter::asDecimal()|decimal]]: the value is formatted as a decimal number considering decimal and thousand
  separators e.g. `2,542.123` or `2.542,123`.
- [[yii\i18n\Formatter::asPercent()|percent]]: the value is formatted as a percent number e.g. `42%`.
- [[yii\i18n\Formatter::asScientific()|scientific]]: the value is formatted as a number in scientific format e.g. `4.2E4`.
- [[yii\i18n\Formatter::asCurrency()|currency]]: the value is formatted as a currency value e.g. `£420.00`.
  Note that for this function to work properly, the locale needs to include a country part e.g. `en_GB` or `en_US` because language only
  would be ambiguous in this case.
- [[yii\i18n\Formatter::asSize()|size]]: the value that is a number of bytes is formatted as a human readable size e.g. `410 kibibytes`.
- [[yii\i18n\Formatter::asShortSize()|shortSize]]: is the short version of [[yii\i18n\Formatter::asSize()|size]], e.g. `410 KiB`.

The format for number formatting can be adjusted using the [[yii\i18n\Formatter::decimalSeparator|decimalSeparator]] and
[[yii\i18n\Formatter::thousandSeparator|thousandSeparator]], both of which take default values according to the 
active [[yii\i18n\Formatter::locale|locale]].

For more advanced configuration, [[yii\i18n\Formatter::numberFormatterOptions]] and [[yii\i18n\Formatter::numberFormatterTextOptions]]
can be used to configure the [NumberFormatter class](http://php.net/manual/en/class.numberformatter.php) used internally
to implement the formatter. For example, to adjust the maximum and minimum value of fraction digits, you can configure 
the [[yii\i18n\Formatter::numberFormatterOptions]] property like the following:
>>>>>>> master

```php
'numberFormatterOptions' => [
    NumberFormatter::MIN_FRACTION_DIGITS => 0,
    NumberFormatter::MAX_FRACTION_DIGITS => 2,
]
```

<<<<<<< HEAD
Other formatters  <span id="other"></span>
----------------

In addition to date, time and number formatting, Yii provides a set of other useful formatters for different situations:

- [[yii\i18n\Formatter::asRaw()|raw]] - the value is outputted as is, this is a pseudo-formatter that has no effect except that
  `null` values will be formatted using [[nullDisplay]].
- [[yii\i18n\Formatter::asText()|text]] - the value is HTML-encoded.
  This is the default format used by the [GridView DataColumn](output-data-widgets.md#data-column).
- [[yii\i18n\Formatter::asNtext()|ntext]] - the value is formatted as an HTML-encoded plain text with newlines converted
  into line breaks.
- [[yii\i18n\Formatter::asParagraphs()|paragraphs]] - the value is formatted as HTML-encoded text paragraphs wrapped
  into `<p>` tags.
- [[yii\i18n\Formatter::asHtml()|html]] - the value is purified using [[HtmlPurifier]] to avoid XSS attacks. You can
  pass additional options such as `['html', ['Attr.AllowedFrameTargets' => ['_blank']]]`.
- [[yii\i18n\Formatter::asEmail()|email]] - the value is formatted as a `mailto`-link.
- [[yii\i18n\Formatter::asImage()|image]] - the value is formatted as an image tag.
- [[yii\i18n\Formatter::asUrl()|url]] - the value is formatted as a hyperlink.
- [[yii\i18n\Formatter::asBoolean()|boolean]] - the value is formatted as a boolean. By default `true` is rendered
  as `Yes` and `false` as `No`, translated to the current application language. You can adjust this by configuring
  the [[yii\i18n\Formatter::booleanFormat]] property.

`null`-values <span id="null-values"></span>
-------------

For values that are `null` in PHP, the formatter class will print a placeholder instead of an empty string which
defaults to `(not set)` translated to the current application language. You can configure the
[[yii\i18n\Formatter::nullDisplay|nullDisplay]] property to set a custom placeholder.
If you do not want special handling for `null` values, you can set [[yii\i18n\Formatter::nullDisplay|nullDisplay]] to `null`.
=======

## Other Formats <span id="other"></span>

Besides date/time and number formats, Yii also supports other commonly used formats, including

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


## Null Values <span id="null-values"></span>

Null values are specially formatted. Instead of displaying an empty string, the formatter will convert it into a
preset string which defaults to `(not set)` translated into the current application language. You can configure the
[[yii\i18n\Formatter::nullDisplay|nullDisplay]] property to customize this string.


## Localizing Data Format <span id="localizing-data-format"></span>

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

By default, the currently active [[yii\i18n\Formatter::locale|locale]] is determined by the value of 
[[yii\base\Application::language]]. You may override it by setting the [[yii\i18n\Formatter::locale]] property explicitly.

> Note: The Yii formatter relies on the [PHP intl extension](http://php.net/manual/en/book.intl.php) to support
> localized data formatting. Because different versions of the ICU library compiled with PHP may cause different
> formatting results, it is recommended that you use the same ICU version for all your environments. For more details,
> please refer to [Setting up your PHP environment for internationalization](tutorial-i18n.md#setup-environment).
>
> If the intl extension is not installed, the data will not be localized. 
>
> Note that for date values that are before year 1901 or after 2038, they will not be localized on 32-bit systems, even
> if the intl extension is installed. This is because in this case ICU is using 32-bit UNIX timestamps to date values.
>>>>>>> master
