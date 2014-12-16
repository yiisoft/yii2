データフォーマッタ
==================

出力の書式設定のために、Yii はデータをユーザにとってより読みやすいものにするためのフォーマッタクラスを提供しています。
デフォルトでは、[[yii\i18n\Formatter]] というヘルパクラスが、`formatter` という名前の [アプリケーションコンポーネント](structure-application-components.md) として登録されます。

このヘルパが、日付/時刻、数字、その他のよく使われる形式について、データをローカライズして書式設定するための一連のメソッドを提供します。
フォーマッタは、二つの異なる方法で使うことが出来ます。

1. 書式設定のメソッド (全て `as` という接頭辞を持ちます) を直接に使用する。

   ```php
   echo Yii::$app->formatter->asDate('2014-01-01', 'long'); // 出力: January 1, 2014
   echo Yii::$app->formatter->asPercent(0.125, 2); // 出力: 12.50%
   echo Yii::$app->formatter->asEmail('cebe@example.com'); // 出力: <a href="mailto:cebe@example.com">cebe@example.com</a>
   echo Yii::$app->formatter->asBoolean(true); // 出力: Yes
   // null 値の表示も処理します。
   echo Yii::$app->formatter->asDate(null); // 出力: (Not set)
   ```

2. [[yii\i18n\Formatter::format()|format()]] メソッドとフォーマット名を使う。
   [[yii\grid\GridView]] や [[yii\widgets\DetailView]] のようなウィジェットでは、構成情報でカラムのデータの書式を指定することが出来ますが、これらウィジェットでもこのメソッドが使われています。

   ```php
   echo Yii::$app->formatter->format('2014-01-01', 'date'); // 出力: January 1, 2014
   // 配列を使って、書式設定メソッドのパラメータを指定することも出来ます。
   // `2` は asPercent() メソッドの $decimals パラメータの値です。
   echo Yii::$app->formatter->format(0.125, ['percent', 2]); // 出力: 12.50%
   ```


[PHP intl 拡張](http://php.net/manual/ja/book.intl.php) がインストールされているときは、フォーマッタの全ての出力がローカライズされます。
これのために [[yii\i18n\Formatter::locale|locale]] プロパティを構成することが出来ます。
これが構成されていないときは、アプリケーションの [[yii\base\Application::language|language]] がロケールとして用いられます。
詳細は [国際化](tutorial-i18n.md) の節を参照してください。
フォーマッタはロケールに従って、正しい日付や数字の形式を選択し、月や曜日の名称もカレントの言語に翻訳されます。
日付の形式は [[yii\i18n\Formatter::timeZone|timeZone]] によっても左右されます。
[[yii\i18n\Formatter::timeZone|timeZone]] も、明示的に構成されていない場合は、アプリケーションの [[yii\base\Application::timeZone|timeZone]] から取られます。

例えば、日付の書式設定を呼ぶと、ロケールによってさまざまな結果を出力します。

```php
Yii::$app->formatter->locale = 'en-US';
echo Yii::$app->formatter->asDate('2014-01-01'); // 出力: January 1, 2014
Yii::$app->formatter->locale = 'de-DE';
echo Yii::$app->formatter->asDate('2014-01-01'); // 出力: 1. Januar 2014
Yii::$app->formatter->locale = 'ru-RU';
echo Yii::$app->formatter->asDate('2014-01-01'); // 出力: 1 января 2014 г.
Yii::$app->formatter->locale = 'ja-JP';
echo Yii::$app->formatter->asDate('2014-01-01'); // 出力: 2014/01/01
```

> Note|注意: 書式設定は、PHP とともにコンパイルされた ICU ライブラリのバージョンの違いによって異なる可能性がありますし、[PHP intl 拡張](http://php.net/manual/ja/book.intl.php) がインストールされているか否かという事実によっても異なってきます。
> 従って、あなたのウェブサイトが全ての環境で同じ出力を表示することを保証するために、全ての環境に PHP intl 拡張をインストールして、ICU ライブラリのバージョンが同じであることを確認する事を推奨します。
> [PHP 環境を国際化のために設定する](tutorial-i18n.md#setup-environment) も参照してください。


フォーマッタを構成する <a name="configuring-format"></a>
----------------------

書式設定メソッドによって使われるデフォルトの書式は、[[yii\i18n\Formatter|フォーマッタクラス]] のプロパティを使って調整することが出来ます。
プロパティの値をアプリケーション全体にわたって調整するために、[アプリケーションの構成情報](concept-configurations.md#application-configurations) において、`formatter` コンポーネントを構成することが出来ます。
構成の例を下記に示します。
利用できるプロパティの詳細については、[[yii\i18n\Formatter|Formatter クラスの API ドキュメント]] と、後続の項を参照してください。

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

日付と時刻の値を書式設定する <a name="date-and-time"></a>
----------------------------

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

```php
// ICU format
echo Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); // 2014-10-06
// PHP date()-format
echo Yii::$app->formatter->asDate('now', 'php:Y-m-d'); // 2014-10-06
```

### Time zones <a name="time-zones"></a>

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


Formatting Numbers <a name="numbers"></a>
------------------

For formatting numeric values the formatter class provides the following methods:

- [[yii\i18n\Formatter::asInteger()|integer]] - the value is formatted as an integer e.g. `42`.
- [[yii\i18n\Formatter::asDecimal()|decimal]] - the value is formatted as a decimal number considering decimal and thousand
  separators e.g. `2,542.123` or `2.542,123`.
- [[yii\i18n\Formatter::asPercent()|percent]] - the value is formatted as a percent number e.g. `42%`.
- [[yii\i18n\Formatter::asScientific()|scientific]] - the value is formatted as a number in scientific format e.g. `4.2E4`.
- [[yii\i18n\Formatter::asCurrency()|currency]] - the value is formatted as a currency value e.g. `£420.00`.
- [[yii\i18n\Formatter::asSize()|size]] - the value that is a number of bytes is formatted as a human readable size e.g. `410 kibibytes`.
- [[yii\i18n\Formatter::asShortSize()|shortSize]] - is the short version of [[yii\i18n\Formatter::asSize()|size]], e.g. `410 KiB`.

The format for number formatting can be adjusted using the [[yii\i18n\Formatter::decimalSeparator|decimalSeparator]] and
[[yii\i18n\Formatter::thousandSeparator|thousandSeparator]] which are set by default according to the locale.

For more advanced configuration, [[yii\i18n\Formatter::numberFormatterOptions]] and [[yii\i18n\Formatter::numberFormatterTextOptions]]
can be used to configure the internally used [NumberFormatter class](http://php.net/manual/en/class.numberformatter.php)

For example to adjust the maximum and minimum value of fraction digits you can configure this property like the following:

```php
[
    NumberFormatter::MIN_FRACTION_DIGITS => 0,
    NumberFormatter::MAX_FRACTION_DIGITS => 2,
]
```

Other formatters  <a name="other"></a>
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

`null`-values <a name="null-values"></a>
-------------

For values that are `null` in PHP, the formatter class will print a placeholder instead of an empty string which
defaults to `(not set)` translated to the current application language. You can configure the
[[yii\i18n\Formatter::nullDisplay|nullDisplay]] property to set a custom placeholder.
If you do not you want special handling for `null` values, you can set [[yii\i18n\Formatter::nullDisplay|nullDisplay]] to `null`.
