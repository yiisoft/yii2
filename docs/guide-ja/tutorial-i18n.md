国際化
======

> Note|注意: この節はまだ執筆中です。

国際化 (I18N) とは、工学的な変更を伴わずにさまざまな言語と地域に順応できるように、ソフトウェアアプリケーションを設計するプロセスを指します。
潜在的なユーザが世界中にいるウェブアプリケーションにとっては、このことは特に重要な意味を持ちます。

Yii は、メッセージの翻訳、数字や日付の書式設定など、ウェブサイトの国際化を手助けするいくつかのツールを提供しています。

ロケールと言語
--------------

Yii のアプリケーションでは二つの言語が定義されます。
すなわち、[[yii\base\Application::$sourceLanguage|ソース言語]] と [[yii\base\Application::$language|ターゲット言語]] です。

ソース言語とは、次のように、コードに直接書かれているオリジナルのアプリケーションメッセージの言語です。

```php
echo \Yii::t('app', 'I am a message!');
```

ターゲット言語は、現在のページを表示するのに使用されるべき言語、すなわち、オリジナルのメッセージの翻訳先となるべき言語です。
これはアプリケーションの構成情報において、次のように定義されているものです。

```php
return [
    'id' => 'applicationID',
    'basePath' => dirname(__DIR__),
    // ...
    'language' => 'ru-RU', // <- ここ !
    // ...
]
```

> **Tip**|ヒント: [[yii\base\Application::$sourceLanguage|ソース言語]] のデフォルト値は英語であり、この値は変えないことが推奨されます。
> 理由は、英語から他の言語への翻訳者を見つける方が、非英語から非英語への翻訳者を見つけるより簡単だからです。

アプリケーションの言語を実行時に設定して、ユーザが選択した言語をターゲットにすることが可能です。
この操作は、全ての出力に対して正しく効果を発揮するように、まだ出力が何も生成されていない時点で行われなければなりません。
そのためには、アプリケーションのターゲット言語プロパティを望ましい値に変更するだけです。

```php
\Yii::$app->language = 'zh-CN';
```

言語/ロケールの書式は `ll-CC` です。`ll` は [ISO-639](http://www.loc.gov/standards/iso639-2/) に従った二文字または三文字の小文字の言語コードであり、`CC` は [ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html) に従った国コードです。

> **Note**|注意: ロケールの概念と構文に関する詳細な情報については、[ICU プロジェクトのドキュメント](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept) を参照してください。


メッセージ翻訳
--------------

メッセージ翻訳は、アプリケーションによって出力されたメッセージを別の言語に翻訳して、さまざまな国のユーザが自国語でアプリケーションを使えるようにするために使用されるものです。

Yii におけるメッセージ翻訳機能は、ソース言語からターゲット言語へとメッセージの翻訳を探すという単純な動作をします。
メッセージ翻訳機能を使うためには、オリジナルのメッセージ文字列を [[Yii::t()]] メソッドで包みます。
このメソッドの最初のパラメータは、メッセージの生成元であるアプリケーションのさまざまな部分を区別するためのカテゴリであり、二番目のパラメータはメッセージそのものです。

```php
echo \Yii::t('app', 'This is a string to translate!');
```

Yii は `i18n` [アプリケーションコンポーネント](structure-application-components.md) に定義されているメッセージソースの中から、現在の [[yii\base\Application::$language|アプリケーション言語]] に従って、適切な翻訳を読み込もうと試みます。
メッセージソースは、翻訳メッセージを提供する一群のファイルまたはデータベースです。
次の構成情報の例は、メッセージを PHP ファイルから取得するメッセージソースを定義するものです。

```php
'components' => [
    // ...
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                //'basePath' => '@app/messages',
                //'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
            ],
        ],
    ],
],
```

上記において `app*` は、このメッセージソースによって扱われるカテゴリを指定するパターンです。
この例では、`app` から始まる全てのカテゴリをこのメッセージソースで処理します。
メッセージファイルは `@app/messages`、すなわち、アプリケーションディレクトリの下の `messages` ディレクトリに配置されています。
[[yii\i18n\PhpMessageSource::fileMap|fileMap]] 配列が、どのカテゴリに対してどのファイルが使われるべきかを定義しています。
`fileMap` を構成する代りに、カテゴリ名をファイル名として使用する規約 (例えば、`app/error` というカテゴリは、[[yii\i18n\PhpMessageSource::basePath|basePath]] の下の `app/error.php` というファイル名に帰結します) に依存することも出来ます。

`\Yii::t('app', 'This is a string to translate!')` というメッセージを翻訳するとき、アプリケーション言語が `ja-JP` である場合は、Yii は利用できるメッセージのリストを取得するために、まず `@app/messages/ja-JP/app.php` というファイルを探します。
`ja-JP` ディレクトリにファイルが無い場合は、失敗であるとする前に、`ja` ディレクトリも探します。

メッセージを PHP ファイルに保存する [[yii\i18n\PhpMessageSource|PhpMessageSource]] に加えて、Yii は二つのクラスを提供しています。

- [[yii\i18n\GettextMessageSource]] - GNU Gettext の MO ファイルまたは PO ファイルを使用
- [[yii\i18n\DbMessageSource]] - データベースを使用


> Info|情報: 以下においては、メッセージ書式の理解を助けるために、原文にはない日本語へのメッセージの翻訳例 (と出力結果) を追加している個所があります。

### 名前付きプレースホルダ

翻訳メッセージには、翻訳後に対応する値に置き換えられるパラメータを追加することが出来ます。
このパラメータの形式は、次の例で示すように、パラメータ名を波括弧で囲むものです。

```php
$username = 'Alexander';
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);

// 翻訳例: '{username} さん、こんにちは!'
```

パラメータへの代入には波括弧を使わないことに注意してください。

### 序数プレースホルダ

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0}', $sum);

// 翻訳例: '差引残高: {0}'
```

> **Tip**|ヒント: メッセージ文字列の意味が分らなくならないように努めて、あまりに多くの序数プレースホルダを使うことは避けてください。
> 翻訳者にはソース文字列しか無く、従って、各プレースホルダに何が入るのかは自明でなければならない、ということを忘れないでください。

### 高度なプレースホルダの書式

高度な機能を使うためには、[intl PHP 拡張](http://www.php.net/manual/ja/intro.intl.php) をインストールして有効にする必要があります。
それをインストールして有効にして初めて、プレースホルダのための拡張構文を使うことが出来るようになります。
すなわち、デフォルトの書式を意味する短い形式 `{placeholderName, argumentType}`、あるいは、書式のスタイルを指定できる完全な形式 `{placeholderName, argumentType, argumentStyle}` を使うことが出来るようになります。

完全なリファレンスは [ICU ウェブサイト](http://icu-project.org/apiref/icu4c/classMessageFormat.html) で入手可能ですが、以下においてはいくつかの例を示します。

#### 数値

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number}', $sum);

// 翻訳例: '差引残高: {0, number}'
// 出力例: '差引残高: 12,345'
```

内蔵のスタイル (`integer`、`currency`、`percent`) の一つを指定することが出来ます。

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, currency}', $sum);

// 翻訳例: '差引残高: {0, number, currency}'
// 出力例: '差引残高: &yen; 12,345'
```

または、カスタムパターンを指定することも出来ます。

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, ,000,000000}', $sum);

// 翻訳例: '差引残高: {0, number, ,000,000000}'
// 出力例: '差引残高: 000,012345'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html).

#### 日付

```php
echo \Yii::t('app', 'Today is {0, date}', time());

// 翻訳例: '今日は {0, date} です。'
// 出力例: '今日は 2015/01/07 です。'
```

内蔵の書式は、`short`、`medium`、`long`、そして `full` です。

```php
echo \Yii::t('app', 'Today is {0, date, short}', time());

// 翻訳例: '今日は {0, date, short} です。'
// 出力例: '今日は 2015/01/07 です。'
```

カスタムパターンを指定することも出来ます。

```php
echo \Yii::t('app', 'Today is {0, date, yyyy-MM-dd}', time());

// 翻訳例: '今日は {0, date, yyyy-MM-dd} です。'
// 出力例: '今日は 2015-01-07 です。'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).

#### 時刻

```php
echo \Yii::t('app', 'It is {0, time}', time());

// 翻訳例: '現在 {0, time} です。'
// 出力例: '現在 22:37:47 です。'
```

内蔵の書式は、`short`、`medium`、`long`、そして `full` です。

```php
echo \Yii::t('app', 'It is {0, time, short}', time());

// 翻訳例: '現在 {0, time, short} です。'
// 出力例: '現在 22:37 です。'
```

カスタムパターンを指定することも出来ます。

```php
echo \Yii::t('app', 'It is {0, date, HH:mm}', time());

// 翻訳例: '現在 {0, time, HH:mm} です。'
// 出力例: '現在 22:37 です。'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).


#### 綴り

```php
echo \Yii::t('app', '{n, number} is spelled as {n, spellout}', ['n' => 12345]);

// 翻訳例: '{n, number} は、文字で綴ると {n, spellout} です。'
// 出力例: '12,345 は、文字で綴ると 一万二千三百四十五 です。'
```

#### 序数

```php
echo \Yii::t('app', 'You are the {n, ordinal} visitor here!', ['n' => 42]);
```

これは、"You are the 42nd visitor here!" というメッセージになります。

> Tip|ヒント: 上記のソースメッセージを、プレースホルダの書式指定を守って日本語に翻訳すると、'あなたはこのサイトの {n, ordinal} の訪問者です。' となります。
> しかし、その出力結果は、'あなたはこのサイトの 第42 の訪問者です。' となり、意味は通じますが、日本語としては若干不自然なものになります。
> この場合は、'あなたはこのサイトの {n} 番目の訪問者です。' のように、プレースホルダの書式も含めて全体を翻訳する方が良いでしょう。

#### 継続時間

```php
echo \Yii::t('app', 'You are here for {n, duration} already!', ['n' => 47]);
```

これは、"You are here for 47 sec. already!" というメッセージになります。

> Tip|ヒント: このソースメッセージを 'あなたはこのサイトに既に {n, duration} 滞在しています。' と翻訳した場合の出力結果は、'あなたはこのサイトに既に 47 滞在しています。' となります。
> これも、プレースホルダの書式も含めて全体を翻訳し直す方が良いようです。


#### Plurals

Different languages have different ways to inflect plurals. Yii provides a convenient way for translating messages in
different plural forms that works well even for very complex rules. Instead of dealing with the inflection rules directly
it is sufficient to provide the translation of inflected words in certain situations only.

```php
echo \Yii::t('app', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

Will give us

- "There are no cats!" for `$n = 0`,
- "There is one cat!" for `$n = 1`,
- and "There are 42 cats!" for `$n = 42`.

In the plural rule arguments above `=0` means exactly zero, `=1` stands for exactly one, and `other` is for any other number.
`#` is replaced with the value of `n`. It's not that simple for languages other than English. Here's an example
for Russian:

```
Здесь {n, plural, =0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

In the above it's worth mentioning that `=1` matches exactly `n = 1` while `one` matches `21` or `101`.

Note, that you can not use the Russian example in `Yii::t()` directly if your
[[yii\base\Application::$sourceLanguage|source language]] isn't set to `ru_RU`. This however is not recommended, instead such
strings should go into message files or message database (in case DB source is used). Yii uses plural rules of the
translated language strings and is falling back to plural rules of source language if translation isn't available.

To learn which inflection forms you should specify for your language you can referrer to the
[rules reference at unicode.org](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html).

#### Selections

You can select phrases based on keywords. The pattern in this case specifies how to map keywords to phrases and
provides a default phrase.

```php
echo \Yii::t('app', '{name} is {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

Will produce "Snoopy is dog and it loves Yii!".

In the expression `female` and `male` are possible values. `other` handles values that do not match. Strings inside
brackets are sub-expressions so could be just a string or a string with more placeholders.

### Specifying default translation

You can specify default translations that will be used as a fallback for categories that don't match any other translation.
This translation should be marked with `*`. In order to do it add the following to the application config:

```php
//configure i18n component

'i18n' => [
    'translations' => [
        '*' => [
            'class' => 'yii\i18n\PhpMessageSource'
        ],
    ],
],
```

Now you can use categories without configuring each one, which is similar to Yii 1.1 behavior.
Messages for the category will be loaded from a file under the default translation `basePath` that is `@app/messages`:

```php
echo Yii::t('not_specified_category', 'message from unspecified category');
```

Message will be loaded from `@app/messages/<LanguageCode>/not_specified_category.php`.

### Translating module messages

If you want to translate messages for a module and avoid using a single translation file for all messages, you can do it like the following:

```php
<?php

namespace app\modules\users;

use Yii;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\users\controllers';

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/users/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/modules/users/messages',
            'fileMap' => [
                'modules/users/validation' => 'validation.php',
                'modules/users/form' => 'form.php',
                ...
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('modules/users/' . $category, $message, $params, $language);
    }

}
```

In the example above we are using wildcard for matching and then filtering each category per needed file. Instead of using `fileMap` you can simply
use convention of category mapping to the same named file and use `Module::t('validation', 'your custom validation message')` or `Module::t('form', 'some form label')` directly.

### Translating widgets messages

The same rule as applied for Modules above can be applied for widgets too, for example:

```php
<?php

namespace app\widgets\menu;

use yii\base\Widget;
use Yii;

class Menu extends Widget
{

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        $i18n->translations['widgets/menu/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/widgets/menu/messages',
            'fileMap' => [
                'widgets/menu/messages' => 'messages.php',
            ],
        ];
    }

    public function run()
    {
        echo $this->render('index');
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return Yii::t('widgets/menu/' . $category, $message, $params, $language);
    }

}
```

Instead of using `fileMap` you can simply use convention of category mapping to the same named file and use `Menu::t('messages', 'new messages {messages}', ['{messages}' => 10])` directly.

> **Note**: For widgets you also can use i18n views, same rules as for controllers are applied to them too.


### Translating framework messages

Yii comes with default translation messages for validation errors and some other strings. These messages are all
in the category `yii`. Sometimes you want to correct default framework message translation for your application.
In order to do so configure the `i18n` [application component](structure-application-components.md) like the following:

```php
'i18n' => [
    'translations' => [
        'yii' => [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@app/messages'
        ],
    ],
],
```

Now you can place your adjusted translations to `@app/messages/<language>/yii.php`.

### Handling missing translations

If the translation is missing at the source, Yii displays the requested message content. Such behavior is very convenient
in case your raw message is a valid verbose text. However, sometimes it is not enough.
You may need to perform some custom processing of the situation, when requested translation is missing at the source.
This can be achieved using the [[yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION|missingTranslation]]-event of [[yii\i18n\MessageSource]].

For example to mark all missing translations with something notable, so they can be easily found at the page we
first we need to setup event handler. This can be done in the application configuration:

```php
'components' => [
    // ...
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
                'on missingTranslation' => ['app\components\TranslationEventHandler', 'handleMissingTranslation']
            ],
        ],
    ],
],
```

Now we need to implement our own event handler:

```php
<?php

namespace app\components;

use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event) {
        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}
```

If [[yii\i18n\MissingTranslationEvent::translatedMessage]] is set by the event handler it will be displayed as the translation result.

> Attention: each message source handles its missing translations separately. If you are using several message sources
> and wish them treat missing translation in the same way, you should assign corresponding event handler to each of them.


Views
-----

Instead of translating messages as described in the last section,
you can also use `i18n` in your views to provide support for different languages. For example, if you have a view `views/site/index.php` and
you want to create a special version for russian language of it, you create a `ru-RU` folder under the view path of the current controller/widget and
put the file for russian language as follows `views/site/ru-RU/index.php`. Yii will then load the file for the current language if it exists
and fall back to the original view file if none was found.

> **Note**: If language is specified as `en-US` and there are no corresponding views, Yii will try views under `en`
> before using original ones.


Formatting Number and Date values
---------------------------------

See the [data formatter section](output-formatter.md) for details.


Setting up your PHP environment <a name="setup-environment"></a>
-------------------------------

Yii uses the [PHP intl extension](http://php.net/manual/en/book.intl.php) to provide most of its internationalization features
such as the number and date formatting of the [[yii\i18n\Formatter]] class and the message formatting using [[yii\i18n\MessageFormatter]].
Both classes provides a fallback implementation that provides basic functionality in case intl is not installed.
This fallback implementation however only works well for sites in english language and even there can not provide the
rich set of features that is available with the PHP intl extension, so its installation is highly recommended.

The [PHP intl extension](http://php.net/manual/en/book.intl.php) is based on the [ICU library](http://site.icu-project.org/) which
provides the knowledge and formatting rules for all the different locales. According to this fact the formatting of dates and numbers
and also the supported syntax available for message formatting differs between different versions of the ICU library that is compiled with
you PHP binary.

To ensure your website works with the same output in all environments it is recommended to install the PHP intl extension
in all environments and verify that the version of the ICU library compiled with PHP is the same.

To find out which version of ICU is used by PHP you can run the following script, which will give you the PHP and ICU version used.

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
```

We recommend an ICU version greater or equal to version ICU 49 to be able to use all the features described in this document.
One major feature that is missing in Versions below 49 is the `#` placeholder in plural rules.
See <http://site.icu-project.org/download> for a list of available ICU versions. Note that the version numbering has changed after the
4.8 release so that the first digits are now merged: the sequence is ICU 4.8, ICU 49, ICU 50.

Additionally the information in the time zone database shipped with the ICU library may be outdated. Please refer
to the [ICU manual](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) for details
on updating the time zone database. While for output formatting the ICU timezone database is used, the time zone database
used by PHP may be relevant too. You can update it by installing the latest version of the [pecl package `timezonedb`](http://pecl.php.net/package/timezonedb).
