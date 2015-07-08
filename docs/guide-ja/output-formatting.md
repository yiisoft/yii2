データのフォーマット
====================

ユーザにとってより読みやすい形式でデータを表示するために、`formatter` [アプリケーションコンポーネント](structure-application-components.md) を使ってデータをフォーマットすることが出来ます。
デフォルトでは、フォーマッタは [[yii\i18n\Formatter]] によって実装されており、これが、日付/時刻、数字、通貨、その他のよく使われる形式にデータをフォーマットする一連のメソッドを提供します。
このフォーマッタは次のようにして使うことが出来ます。

```php
$formatter = \Yii::$app->formatter;

// 出力: January 1, 2014
echo $formatter->asDate('2014-01-01', 'long');
 
// 出力: 12.50%
echo $formatter->asPercent(0.125, 2);
 
// 出力: <a href="mailto:cebe@example.com">cebe@example.com</a>
echo $formatter->asEmail('cebe@example.com'); 

// 出力: Yes
echo $formatter->asBoolean(true); 
// it also handles display of null values:

// 出力: (Not set)
echo $formatter->asDate(null); 
```

ご覧のように、これらのメソッドは全て `asXyz()` という名前を付けられており、`Xyz` がサポートされている形式を表しています。
別の方法として、汎用メソッド [[yii\i18n\Formatter::format()|format()]] を使ってデータをフォーマットすることも出来ます。
この方法を使うと望む形式をプログラム的に制御することが可能になりますので、[[yii\grid\GridView]] や [[yii\widgets\DetailView]] などのウィジェットでは、こちらがよく使われています。
例えば、

```php
// 出力: January 1, 2014
echo Yii::$app->formatter->format('2014-01-01', 'date'); 

// 配列を使ってフォーマットメソッドのパラメータを指定することも出来ます。
// `2` は asPercent() メソッドの $decimals パラメータの値です。
// 出力: 12.50%
echo Yii::$app->formatter->format(0.125, ['percent', 2]); 
```


## フォーマッタを構成する <span id="configuring-formater"></span>

[アプリケーションの構成情報](concept-configurations.md#application-configurations) の中で `formatter` コンポーネントを構成して、フォーマットの規則をカスタマイズすることが出来ます。
例えば、


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

構成可能なプロパティについては、[[yii\i18n\Formatter]] を参照してください。


## 日付と時刻の値をフォーマットする <span id="date-and-time"></span>

フォーマッタは日付と時刻に関連した下記の出力形式をサポートしています。

- [[yii\i18n\Formatter::asDate()|date]] - 値は日付としてフォーマットされます。例えば `January 01, 2014`。
- [[yii\i18n\Formatter::asTime()|time]] - 値は時刻としてフォーマットされます。例えば `14:23`。
- [[yii\i18n\Formatter::asDatetime()|datetime]] - 値は日付および時刻としてフォーマットされます。例えば `January 01, 2014 14:23`。
- [[yii\i18n\Formatter::asTimestamp()|timestamp]] - 値は [unix タイムスタンプ](http://en.wikipedia.org/wiki/Unix_time) としてフォーマットされます。例えば `1412609982`。
- [[yii\i18n\Formatter::asRelativeTime()|relativeTime]] - 値は、その日時と現在との間隔として、人間に分かりやすい言葉でフォーマットされます。例えば `1 hour ago`。

[[yii\i18n\Formatter::asDate()|date]]、[[yii\i18n\Formatter::asTime()|time]]、[[yii\i18n\Formatter::asDatetime()|datetime]] メソッドに使われるデフォルトの日時書式は、フォーマッタの [[yii\i18n\Formatter::$dateFormat|$dateFormat]]、[[yii\i18n\Formatter::$timeFormat|$timeFormat]]、[[yii\i18n\Formatter::$datetimeFormat|$datetimeFormat]] を構成することで、グローバルにカスタマイズすることが出来ます。

日付と時刻のフォーマットは、[ICU 構文](http://userguide.icu-project.org/formatparse/datetime) によって指定することが出来ます。
また、ICU 構文と区別するために `php:` という接頭辞を付けて、[PHP の date() 構文](http://php.net/manual/ja/function.date.php) を使うことも出来ます。
例えば、

```php
// ICU 形式
echo Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); // 2014-10-06
// PHP date() 形式
echo Yii::$app->formatter->asDate('now', 'php:Y-m-d'); // 2014-10-06
```

複数の言語をサポートする必要があるアプリケーションを扱う場合には、ロケールごとに異なる日付と時刻のフォーマットを指定しなければならないことがよくあります。
この仕事を単純化するためには、(`long`、`short` などの) フォーマットのショートカットを代りに使うことが出来ます。
フォーマッタは、現在アクティブな [[yii\i18n\Formatter::locale|locale]] に従って、フォーマットのショートカットを適切なフォーマットに変換します。
フォーマットのショートカットとして、次のものがサポートされています
(例は `en_GB` がアクティブなロケールであると仮定したものです)。

- `short`: 日付は `06/10/2014`、時刻は `15:58` を出力
- `medium`: `6 Oct 2014` と `15:58:42` を出力
- `long`: `6 October 2014` と `15:58:42 GMT` を出力
- `full`: `Monday, 6 October 2014` と `15:58:42 GMT` を出力

> Info|訳注: ja_JP ロケールでは、次のようになります。
> 
> short: 2014/10/06 と 15:58
> medium: 2014/10/06 と 15:58:42
> long: 2014年10月6日 と 15:58:42 JST
> full: 2014年10月6日月曜日 と 15時58分42秒 日本標準時

### タイムゾーン <span id="time-zones"></span>

日時の値をフォーマットするときに、Yii はその値をターゲット [[yii\i18n\Formatter::timeZone|タイムゾーン]] に変換します。
フォーマットされる値は、タイムゾーンが明示的に指定されるか、[[yii\i18n\Formatter::defaultTimeZone]] が構成されるかしていない限り、UTC であると見なされます。

次の例では、ターゲット [[yii\i18n\Formatter::timeZone|タイムゾーン]] が `Europe/Berlin` に設定されているものとします。

```php
// UNIX タイムスタンプを時刻としてフォーマット
echo Yii::$app->formatter->asTime(1412599260); // 14:41:00

// UTC の日付時刻文字列を時刻としてフォーマット
echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 14:41:00

// CEST の日付時刻文字列を時刻としてフォーマット
echo Yii::$app->formatter->asTime('2014-10-06 14:41:00 CEST'); // 14:41:00
```

> Info|訳注:
> ターゲット [[yii\i18n\Formatter::timeZone|タイムゾーン]] が `Asia/Tokyo` である場合は、次のようになります。
> 
> ```php
> echo Yii::$app->formatter->asTime(1412599260); // 21:41:00
> echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 21:41:00
> echo Yii::$app->formatter->asTime('2014-10-06 21:41:00 JST'); // 21:41:00
> ```

> Note|注意: タイムゾーンは世界中のさまざまな政府によって作られる規則に従うものであり、頻繁に変更されるものであるため、あなたのシステムにインストールされたタイムゾーンのデータベースが最新の情報を持っていない可能性が大いにあります。
> タイムゾーンデータベースの更新についての詳細は、[ICU マニュアル](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) で参照することが出来ます。
> [PHP 環境を国際化のために設定する](tutorial-i18n.md#setup-environment) も参照してください。


## 数値をフォーマットする <span id="numbers"></span>

フォーマッタは、数値に関連した下記の出力フォーマットをサポートしています。

- [[yii\i18n\Formatter::asInteger()|integer]] - 値は整数としてフォーマットされます。例えば `42`。
- [[yii\i18n\Formatter::asDecimal()|decimal]] - 値は小数点と三桁ごとの区切りを考慮して十進数としてフォーマットされます。例えば `2,542.123` または `2.542,123`。
- [[yii\i18n\Formatter::asPercent()|percent]] - 値は百分率としてフォーマットされます。例えば `42%`。
- [[yii\i18n\Formatter::asScientific()|scientific]] - 値は科学記法による数値としてフォーマットされます。例えば `4.2E4`。
- [[yii\i18n\Formatter::asCurrency()|currency]] - 値は通貨の値としてフォーマットされます。例えば `£420.00`。
  この関数が正しく働くためには、`en_GB` や `en_US` のように、ロケールが国コードを含んでいる必要があります。
  なぜなら、この場合は言語だけでは曖昧になるからです。
- [[yii\i18n\Formatter::asSize()|size]] - バイト数である値が人間にとって読みやすいサイズとしてフォーマットされます。例えば `410 キビバイト`。
- [[yii\i18n\Formatter::asShortSize()|shortSize]] - [[yii\i18n\Formatter::asSize()|size]] の短いバージョンです。例えば `410 KiB`。

数値のフォーマットに使われる書式は、デフォルトではロケールに従って設定される [[yii\i18n\Formatter::decimalSeparator|decimalSeparator]] と [[yii\i18n\Formatter::thousandSeparator|thousandSeparator]] を使って調整することが出来ます。

更に高度な設定のためには、[[yii\i18n\Formatter::numberFormatterOptions]] と [[yii\i18n\Formatter::numberFormatterTextOptions]] を使って、内部的に使用される [NumberFormatter クラス](http://php.net/manual/ja/class.numberformatter.php) を構成することが出来ます。

例えば、小数部の最大桁数と最小桁数を調整するためには、次のように [[yii\i18n\Formatter::numberFormatterOptions]] プロパティを構成します。

```php
'numberFormatterOptions' => [
    NumberFormatter::MIN_FRACTION_DIGITS => 0,
    NumberFormatter::MAX_FRACTION_DIGITS => 2,
]
```

## その他のフォーマット <span id="other"></span>

日付/時刻と数値のフォーマット以外にも、Yii はよく使われるフォーマットをサポートしています。
その中には、次のものが含まれます。

- [[yii\i18n\Formatter::asRaw()|raw]] - 値はそのまま出力されます。`null` 値が [[nullDisplay]] を使ってフォーマットされる以外は、何の効果もない擬似フォーマッタです。
- [[yii\i18n\Formatter::asText()|text]] - 値は HTML エンコードされます。
  これは [GridView DataColumn](output-data-widgets.md#data-column) で使われるデフォルトのフォーマットです。
- [[yii\i18n\Formatter::asNtext()|ntext]] - 値は HTML エンコードされ、改行文字が強制改行に変換された平文テキストとしてフォーマットされます。
- [[yii\i18n\Formatter::asParagraphs()|paragraphs]] - 値は HTML エンコードされ、`<p>` タグに囲まれた段落としてフォーマットされます。
- [[yii\i18n\Formatter::asHtml()|html]] - 値は XSS 攻撃を避けるために [[HtmlPurifier]] を使って浄化されます。
  `['html', ['Attr.AllowedFrameTargets' => ['_blank']]]` のような追加のオプションを渡すことが出来ます。
- [[yii\i18n\Formatter::asEmail()|email]] - 値は `mailto` リンクとしてフォーマットされます。
- [[yii\i18n\Formatter::asImage()|image]] - 値は `image` タグとしてフォーマットされます。
- [[yii\i18n\Formatter::asUrl()|url]] - 値はハイパーリンクとしてフォーマットされます。
- [[yii\i18n\Formatter::asBoolean()|boolean]] - 値は真偽値としてフォーマットされます。
  デフォルトでは、`true` は `Yes`、`false` は `No` とレンダリングされ、現在のアプリケーションの言語に翻訳されます。
  この動作は [[yii\i18n\Formatter::booleanFormat]] プロパティを構成して調整できます。


## `null` 値 <span id="null-values"></span>

Null 値は特殊な方法でフォーマットされます。
空文字列を表示する代りに、フォーマッタは null 値を事前定義された文字列 (そのデフォルト値は `(not set)` です) に変換し、それを現在のアプリケーションの言語に翻訳します。
この文字列は [[yii\i18n\Formatter::nullDisplay|nullDisplay]] プロパティを構成してカスタマイズすることが出来ます。

## データのフォーマットをローカライズする <span id="localizing-data-format"></span>

既に述べたように、フォーマッタは現在のアクティブな [[yii\i18n\Formatter::locale|locale]] を使って、ターゲットの国/地域にふさわしい値のフォーマットを決定することが出来ます。
例えば、同じ日時の値でも、ロケールによって異なる書式にフォーマットされます。

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

デフォルトでは、現在のアクティブな [[yii\i18n\Formatter::locale|locale]] は [[yii\base\Application::language]] の値によって決定されます。
これは [[yii\i18n\Formatter::locale]] プロパティを明示的に指定することによってオーバーライドすることが出来ます。

> Note|注意: Yii のフォーマッタは、[PHP intl extension](http://php.net/manual/ja/book.intl.php) に依存してデータのフォーマットのローカライズをサポートしています。
> PHP にコンパイルされた ICU ライブラリのバージョンによってフォーマットの結果が異なる場合がありますので、あなたの全ての環境で、同じ ICU バージョンを使うことが推奨されます。
> 詳細については、[PHP 環境を国際化のために設定する](tutorial-i18n.md#setup-environment) を参照してください。
>
> intl 拡張がインストールされていない場合は、データはローカライズされません。
>
> 1901年より前、または、2038年より後の日時の値は、たとえ intl 拡張がインストールされていても、32-bit システムではローカライズされないことに注意してください。
> これは、この場合、ICU ライブラリが日時の値に対して 32-bit の UNIX タイムスタンプを使用しているのが原因です。
