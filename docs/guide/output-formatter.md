Data Formatter
==============

For formatting of outputs Yii provides a formatter class to make data more readable for users.
[[yii\i18n\Formatter]] is a helper class that is registered as an [application component](concept-components.md) named `formatter` by default.

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
application [[yii\base\Application::language|language]] is used as the locale. See the [Section on internationaization](tutorial-i18n.md) for more details.
The Formatter will then choose the correct format for dates and numbers according to the locale including names of month and
week days translated to the current language. Date formats are also affected by the [[yii\i18n\Formatter::timeZone|timeZone]]
which will also be taken [[yii\base\Application::timeZone|from the application]] if not configured explicitly.

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


Configuring the format
----------------------

The default format of the Formatter class can be adjusted using the properties of the formatter class.
You can adjust these values application wide by configuring the `formatter` component in your [application config](concept-configurations.md#application-configurations).
An example configuration is shown in the following.
For more details about the available properties check out the [[yii\i18n\Formatter|API documentation of the Formatter class]].

```php
'components' => [
    'formatter' => [
        'dateFormat' => 'dd.MM.yyyy',
        'decimalSeparator' => ',',
        'thousandSeparator' => ' ',
        'currencyCode' => 'EUR',
   ];
```

Formatting Dates
----------------

> Note: This section is under development.

TDB

See http://site.icu-project.org/ for the format.

- [[\yii\i18n\Formatter::asDate()|date]] - the value is formatted as date.
- [[\yii\i18n\Formatter::asTime()|time]] - the value is formatted as time.
- [[\yii\i18n\Formatter::asDatetime()|datetime]] - the value is formatted as datetime.
- [[\yii\i18n\Formatter::asTimestamp()|timestamp]] - the value is formatted as a unix timestamp.
- [[\yii\i18n\Formatter::asRelativeTime()|relativeTime]] - the value is formatted as the time interval between a date
  and now in human readable form.


The input value for date and time formatting is assumed to be in UTC unless a timezone is explicitly given.

Formatting Numbers
------------------

> Note: This section is under development.

TDB

See http://site.icu-project.org/ for the format.

- [[\yii\i18n\Formatter::asInteger()|integer]] - the value is formatted as an integer.
- [[\yii\i18n\Formatter::asDecimal()|decimal]] - the value is formatted as a number with decimal and thousand separators.
- [[\yii\i18n\Formatter::asPercent()|percent]] - the value is formatted as a percent number.
- [[\yii\i18n\Formatter::asScientific()|scientific]] - the value is formatted as a number in scientific format.
- [[\yii\i18n\Formatter::asCurrency()|currency]] - the value is formatted as a currency value.
- [[\yii\i18n\Formatter::asSize()|size]] - the value that is a number of bytes is formatted as a human readable size.
- [[\yii\i18n\Formatter::asShortSize()|shortSize]] - the value that is a number of bytes is formatted as a human readable size.


Other formatters
----------------

> Note: This section is under development.

TDB


Here's the bundled formatters list:

- [[\yii\i18n\Formatter::asRaw()|raw]] - the value is outputted as is.
- [[\yii\i18n\Formatter::asText()|text]] - the value is HTML-encoded. This format is used by default.
- [[\yii\i18n\Formatter::asNtext()|ntext]] - the value is formatted as an HTML-encoded plain text with newlines converted
  into line breaks.
- [[\yii\i18n\Formatter::asParagraphs()|paragraphs]] - the value is formatted as HTML-encoded text paragraphs wrapped
  into `<p>` tags.
- [[\yii\i18n\Formatter::asHtml()|html]] - the value is purified using [[HtmlPurifier]] to avoid XSS attacks. You can
  pass additional options such as `['html', ['Attr.AllowedFrameTargets' => ['_blank']]]`.
- [[\yii\i18n\Formatter::asEmail()|email]] - the value is formatted as a mailto link.
- [[\yii\i18n\Formatter::asImage()|image]] - the value is formatted as an image tag.
- [[\yii\i18n\Formatter::asUrl()|url]] - the value is formatted as a hyperlink.
- [[\yii\i18n\Formatter::asBoolean()|boolean]] - the value is formatted as a boolean. You can set what's rendered for
  true and false values by calling `Yii::$app->formatter->booleanFormat = ['No', 'Yes'];` before outputting GridView.
