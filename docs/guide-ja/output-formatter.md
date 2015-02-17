データフォーマッタ
==================

出力をフォーマットするために、Yii はフォーマッタクラスを提供して、データをユーザにとってより読みやすいものにします。
デフォルトでは、[[yii\i18n\Formatter]] というヘルパクラスが、`formatter` という名前の [アプリケーションコンポーネント](structure-application-components.md) として登録されます。

このヘルパが、日付/時刻、数字、その他のよく使われる形式について、データをローカライズしてフォーマットするための一連のメソッドを提供します。
フォーマッタは、二つの異なる方法で使うことが出来ます。

1. フォーマットするメソッド (全て `as` という接頭辞を持つフォーマッタのメソッドです) を直接に使用する。

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
   // 配列を使って、フォーマットメソッドのパラメータを指定することも出来ます。
   // `2` は asPercent() メソッドの $decimals パラメータの値です。
   echo Yii::$app->formatter->format(0.125, ['percent', 2]); // 出力: 12.50%
   ```


[PHP intl 拡張](http://php.net/manual/ja/book.intl.php) がインストールされているときは、フォーマッタの全ての出力がローカライズされます。
これのために [[yii\i18n\Formatter::locale|locale]] プロパティを構成することが出来ます。
[[yii\i18n\Formatter::locale|locale]] が構成されていないときは、アプリケーションの [[yii\base\Application::language|language]] がロケールとして用いられます。
詳細は [国際化](tutorial-i18n.md) の節を参照してください。
フォーマッタはロケールに従って、正しい日付や数字の形式を選択し、月や曜日の名称もカレントの言語に翻訳します。
日付の形式は [[yii\i18n\Formatter::timeZone|timeZone]] によっても左右されます。
[[yii\i18n\Formatter::timeZone|timeZone]] も、明示的に構成されていない場合は、アプリケーションの [[yii\base\Application::timeZone|timeZone]] から取られます。

例えば、日付のフォーマットを呼ぶと、ロケールによってさまざまな結果を出力します。

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

> Note|注意: フォーマットの仕方は、PHP とともにコンパイルされた ICU ライブラリのバージョンの違いによって異なる可能性がありますし、[PHP intl 拡張](http://php.net/manual/ja/book.intl.php) がインストールされているか否かという事実によっても異なってきます。
> 従って、あなたのウェブサイトが全ての環境で同じ出力を表示することを保証するために、全ての環境に PHP intl 拡張をインストールして、ICU ライブラリのバージョンが同じであることを確認する事を推奨します。
> [PHP 環境を国際化のために設定する](tutorial-i18n.md#setup-environment) も参照してください。
>
> もう一つ注意してほしいのは、たとえ intl 拡張がインストールされていても、32-bit システムでは、2038 年以降および 1901 年以前の日付・時刻の書式は、ローカライズされた月と日の名前を提供しない PHP の実装にフォールバックする、ということです。
> これは 32-bit システムでは intl が 32-bit の UNIX タイムスタンプを内部的に使用するからです。
> 64-bit のシステムでは、インストールされていれば、全ての場合に intl フォーマッタが使用されます。


フォーマッタを構成する <span id="configuring-format"></span>
----------------------

フォーマットメソッドによって使われるデフォルトの書式は、[[yii\i18n\Formatter|フォーマッタクラス]] のプロパティを使って調整することが出来ます。
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

日時の値をフォーマットする <span id="date-and-time"></span>
--------------------------

フォーマッタクラスは日時の値をフォーマットするさまざまなメソッドを提供しています。すなわち、

- [[yii\i18n\Formatter::asDate()|date]] - 値は日付としてフォーマットされます。例えば `2014/01/01`。
- [[yii\i18n\Formatter::asTime()|time]] - 値は時刻としてフォーマットされます。例えば `14:23`。
- [[yii\i18n\Formatter::asDatetime()|datetime]] - 値は日付および時刻としてフォーマットされます。例えば `2014/01/01 14:23`。
- [[yii\i18n\Formatter::asTimestamp()|timestamp]] - 値は [unix タイムスタンプ](http://en.wikipedia.org/wiki/Unix_time) としてフォーマットされます。例えば `1412609982`。
- [[yii\i18n\Formatter::asRelativeTime()|relativeTime]] - 値は、その日時と現在との間隔として、人間に分かりやすい言葉でフォーマットされます。例えば `1 時間前`。

[[yii\i18n\Formatter::asDate()|date]]、[[yii\i18n\Formatter::asTime()|time]]、[[yii\i18n\Formatter::asDatetime()|datetime]] メソッドの日時の書式は、フォーマッタのプロパティ [[yii\i18n\Formatter::$dateFormat|$dateFormat]]、[[yii\i18n\Formatter::$timeFormat|$timeFormat]]、[[yii\i18n\Formatter::$datetimeFormat|$datetimeFormat]] を構成することで、グローバルに指定することが出来ます。

デフォルトでは、フォーマッタが使う書式は、ショートカット形式で指定します。
これは、日付と時刻をユーザの国と言語にとって一般的な形式でフォーマット出来るように、現在アクティブなロケールに従ってさまざまに解釈されるものです。
四つの異なるショートカット形式が利用できます。

- `short` は、`en_GB` ロケールでは、例えば、日付を `06/10/2014`、時刻を `15:58` と表示します。
- `medium` は、 `6 Oct 2014` および `15:58:42`、
- `long` は、`6 October 2014` および `15:58:42 GMT`、
- そして `full` は `Monday, 6 October 2014` および `15:58:42 GMT` を表示します。

> Info:情報| `ja_JP` ロケールでは、次のようになります。
>
> - `short` ... `2014/10/06` および `15:58`
> - `medium` ... `2014/10/06` および `15:58:42`
> - `long` ... `2014年10月6日` および `15:58:42 JST`
> - `full` ... `2014年10月6日月曜日` および `15時58分42秒 日本標準時`

これに加えて、[ICU プロジェクト](http://site.icu-project.org/) によって定義された構文を使うカスタム書式を指定することが出来ます。
この構文を説明する ICU マニュアルが下記の URL にあります: <http://userguide.icu-project.org/formatparse/datetime>。
別の選択肢として、`php:` という接頭辞を付けた文字列を使って、PHP の [date()](http://php.net/manual/ja/function.date.php) 関数が認識する構文を使うことも出来ます。

```php
// ICU 形式
echo Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); // 2014-10-06
// PHP date() 形式
echo Yii::$app->formatter->asDate('now', 'php:Y-m-d'); // 2014-10-06
```

### タイムゾーン <span id="time-zones"></span>

日時の値をフォーマットするときに、Yii はその値を [[yii\i18n\Formatter::timeZone|設定されたタイムゾーン]] に変換します。
従って、入力値は、タイムゾーンが明示的に指定されていなければ、UTC であると見なされます。
この理由により、全ての日時の値を UTC、それも、なるべくなら、定義によって UTC であることが保証されている UNIX タイムスタンプで保存することが推奨されます。
入力値が UTC とは異なるタイムゾーンに属する場合は、次の例のように、タイムゾーンを明示的に記述しなければなりません。

```php
// Yii::$app->timeZone は 'Asia/Tokyo' であるとします。
echo Yii::$app->formatter->asTime(1412599260); // 21:41:00
echo Yii::$app->formatter->asTime('2014-10-06 12:41:00'); // 21:41:00
echo Yii::$app->formatter->asTime('2014-10-06 21:41:00 JST'); // 21:41:00
```

バージョン 2.0.1 からは、上記のコードの二番目の例のようにタイムゾーン識別子を含まないタイムスタンプに対して適用されるタイムゾーンを設定することも可能になりました。
[[yii\i18n\Formatter::defaultTimeZone]] を設定して、データストレージに使用しているタイムゾーンに合わせることが出来ます。

> Note|注意: タイムゾーンは世界中のさまざまな政府によって作られる規則に従うものであり、頻繁に変更されるものであるため、あなたのシステムにインストールされたタイムゾーンのデータベースが最新の情報を持っていない可能性が大いにあります。
> タイムゾーンデータベースの更新についての詳細は、[ICU マニュアル](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) で参照することが出来ます。
> [PHP 環境を国際化のために設定する](tutorial-i18n.md#setup-environment) も参照してください。


数値をフォーマットする <span id="numbers"></span>
----------------------

数値をフォーマットするために、フォーマッタクラスは次のメソッドを提供しています。

- [[yii\i18n\Formatter::asInteger()|integer]] - 値は整数としてフォーマットされます。例えば `42`。
- [[yii\i18n\Formatter::asDecimal()|decimal]] - 値は小数点と三桁ごとの区切りを使って十進数としてフォーマットされます。例えば `2,542.123` または `2.542,123`。
- [[yii\i18n\Formatter::asPercent()|percent]] - 値は百分率としてフォーマットされます。例えば `42%`。
- [[yii\i18n\Formatter::asScientific()|scientific]] - 値は科学記法による数値としてフォーマットされます。例えば `4.2E4`。
- [[yii\i18n\Formatter::asCurrency()|currency]] - 値は通貨の値としてフォーマットされます。例えば `£420.00`。
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

その他のフォーマッタ  <span id="other"></span>
--------------------

日付、時刻、そして、数値の他にも、Yii はさまざまな状況で使える一連のフォーマッタを提供しています。

- [[yii\i18n\Formatter::asRaw()|raw]] - 値はそのまま出力されます。`null` 値が [[nullDisplay]] を使ってフォーマットされる以外は、何の効果もない擬似フォーマッタです。
- [[yii\i18n\Formatter::asText()|text]] - 値は HTML エンコードされます。
  これは [GridView DataColumn](output-data-widgets.md#data-column) で使われるデフォルトの形式です。
- [[yii\i18n\Formatter::asNtext()|ntext]] - 値は HTML エンコードされ、改行文字が強制改行に変換された平文テキストとしてフォーマットされます。
- [[yii\i18n\Formatter::asParagraphs()|paragraphs]] - 値は HTML エンコードされ、`<p>` タグに囲まれた段落としてフォーマットされます。
- [[yii\i18n\Formatter::asHtml()|html]] - 値は XSS 攻撃を避けるために [[HtmlPurifier]] を使って浄化されます。
  `['html', ['Attr.AllowedFrameTargets' => ['_blank']]]` のような追加のオプションを渡すことが出来ます。
- [[yii\i18n\Formatter::asEmail()|email]] - 値は `mailto` リンクとしてフォーマットされます。
- [[yii\i18n\Formatter::asImage()|image]] - 値は `image` タグとしてフォーマットされます。
- [[yii\i18n\Formatter::asUrl()|url]] - 値はハイパーリンクとしてフォーマットされます。
- [[yii\i18n\Formatter::asBoolean()|boolean]] - 値は真偽値としてフォーマットされます。
  デフォルトでは、`true` は `Yes`、`false` は `No` とレンダリングされ、現在のアプリケーションの言語に翻訳されます。
  この振る舞いは [[yii\i18n\Formatter::booleanFormat]] プロパティを構成して調整できます。

`null` 値 <span id="null-values"></span>
---------

PHP において `null` である値に対して、フォーマッタクラスは空文字ではなくプレースホルダを表示します。
`null` のプレースホルダは、デフォルトでは `(not set)` であり、それが現在のアプリケーションの言語に翻訳されます。
[[yii\i18n\Formatter::nullDisplay|nullDisplay]] プロパティを構成して、カスタムのプレースホルダを設定することが出来ます。
`null` 値の特別な扱いをしたくない場合は、[[yii\i18n\Formatter::nullDisplay|nullDisplay]] を `null` に設定することが出来ます。
