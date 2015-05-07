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

言語/ロケールの書式は `ll-CC` です。ここで `ll` は [ISO-639](http://www.loc.gov/standards/iso639-2/) に従った二文字または三文字の小文字の言語コードであり、`CC` は [ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html) に従った国コードです。

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
`fileMap` を構成する代りに、カテゴリ名をファイル名として使用する規約に依拠することも出来ます
(例えば、`app/error` というカテゴリは、[[yii\i18n\PhpMessageSource::basePath|basePath]] の下の `app/error.php` というファイル名に帰結します) 。

`\Yii::t('app', 'This is a string to translate!')` というメッセージを翻訳するとき、アプリケーション言語が `ja-JP` である場合は、Yii は利用できるメッセージのリストを取得するために、まず `@app/messages/ja-JP/app.php` というファイルを探します。
`ja-JP` ディレクトリにファイルが無い場合は、失敗であるとする前に、`ja` ディレクトリも探します。

メッセージを PHP ファイルに保存する [[yii\i18n\PhpMessageSource|PhpMessageSource]] に加えて、Yii は二つのクラスを提供しています。

- [[yii\i18n\GettextMessageSource]] - GNU Gettext の MO ファイルまたは PO ファイルを使用
- [[yii\i18n\DbMessageSource]] - データベースを使用


> Note|訳注: 以下においては、メッセージ書式の理解を助けるために、原文にはない日本語への翻訳例 (とその出力結果) をコードサンプルに追加しています。

### 名前付きプレースホルダ

翻訳メッセージには、翻訳後に対応する値に置き換えられるパラメータを追加することが出来ます。
このパラメータの形式は、次の例で示すように、パラメータ名を波括弧で囲むものです。

```php
$username = 'Alexander';
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);

// 翻訳: '{username} さん、こんにちは!'
```

パラメータに値を代入するときには波括弧を使わないことに注意してください。

### 序数プレースホルダ

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0}', $sum);

// 翻訳: '差引残高: {0}'
```

> **Tip**|ヒント: メッセージ文字列の意味が分らなくならないように、あまりに多くの序数プレースホルダを使うことは避けてください。
> 翻訳者にはソース文字列しか無く、従って、各プレースホルダに何が入るのかが一見して明らかでなければならない、ということを忘れないでください。

### 高度なプレースホルダの書式設定

この高度な機能を使うためには、[intl PHP 拡張](http://www.php.net/manual/ja/intro.intl.php) をインストールして有効にする必要があります。
それをインストールして有効にして初めて、プレースホルダのための拡張構文を使うことが出来るようになります。
すなわち、デフォルトの書式を使用する短い構文 `{プレースホルダ名, タイプ}`、あるいは、書式のスタイルを指定できる完全な構文 `{プレースホルダ名, タイプ, スタイル}` を使うことが出来るようになります。

完全なリファレンスは [ICU ウェブサイト](http://icu-project.org/apiref/icu4c/classMessageFormat.html) で入手可能ですが、以下においてはいくつかの例を示します。

#### 数値

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number}', $sum);

// 翻訳: '差引残高: {0, number}'
// 出力: '差引残高: 12,345'
```

内蔵のスタイル (`integer`、`currency`、`percent`) の一つを指定することが出来ます。

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, currency}', $sum);

// 翻訳: '差引残高: {0, number, currency}'
// 出力: '差引残高: &yen; 12,345'
```

または、カスタムパターンを指定することも出来ます。

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, ,000,000000}', $sum);

// 翻訳: '差引残高: {0, number, ,000,000000}'
// 出力: '差引残高: 000,012345'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html).

#### 日付

```php
echo \Yii::t('app', 'Today is {0, date}', time());

// 翻訳: '今日は {0, date} です。'
// 出力: '今日は 2015/01/07 です。'
```

内蔵の書式は、`short`、`medium`、`long`、そして `full` です。

```php
echo \Yii::t('app', 'Today is {0, date, short}', time());

// 翻訳: '今日は {0, date, short} です。'
// 出力: '今日は 2015/01/07 です。'
```

カスタムパターンを指定することも出来ます。

```php
echo \Yii::t('app', 'Today is {0, date, yyyy-MM-dd}', time());

// 翻訳: '今日は {0, date, yyyy-MM-dd} です。'
// 出力: '今日は 2015-01-07 です。'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).

#### 時刻

```php
echo \Yii::t('app', 'It is {0, time}', time());

// 翻訳: '現在 {0, time} です。'
// 出力: '現在 22:37:47 です。'
```

内蔵の書式は、`short`、`medium`、`long`、そして `full` です。

```php
echo \Yii::t('app', 'It is {0, time, short}', time());

// 翻訳: '現在 {0, time, short} です。'
// 出力: '現在 22:37 です。'
```

カスタムパターンを指定することも出来ます。

```php
echo \Yii::t('app', 'It is {0, date, HH:mm}', time());

// 翻訳: '現在 {0, time, HH:mm} です。'
// 出力: '現在 22:37 です。'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).


#### 綴り

```php
echo \Yii::t('app', '{n, number} is spelled as {n, spellout}', ['n' => 12345]);

// 翻訳: '{n, number} は、文字で綴ると {n, spellout} です。'
// 出力: '12,345 は、文字で綴ると 一万二千三百四十五 です。'
```

#### 序数

```php
echo \Yii::t('app', 'You are the {n, ordinal} visitor here!', ['n' => 42]);
```

これは、"You are the 42nd visitor here!" というメッセージになります。

> Note|訳注: 上記のソースメッセージを、プレースホルダの書式指定を守って日本語に翻訳すると、'あなたはこのサイトの {n, ordinal} の訪問者です' となります。
> しかし、その出力結果は、'あなたはこのサイトの 第42 の訪問者です' となり、意味は通じますが、日本語としては若干不自然なものになります。
>
> プレースホルダの書式指定自体も、翻訳の対象として、より適切なものに変更することが可能です。
>
> この場合も、'あなたはこのサイトの{n, plural, =1{最初} other{#番目}}の訪問者です' のように翻訳することが出来ます。

#### 継続時間

```php
echo \Yii::t('app', 'You are here for {n, duration} already!', ['n' => 47]);
```

これは、"You are here for 47 sec. already!" というメッセージになります。

> Note|訳注: このソースメッセージを 'あなたはこのサイトに既に {n, duration} の間滞在しています' と翻訳した場合の出力結果は、'あなたはこのサイトに既に 47 の間滞在しています' となります。
> ICU ライブラリのバグでしょうか。これも、プレースホルダの書式設定も含めて全体を翻訳し直す方が良いようです。


#### 複数形

言語によって、複数形の語形変化はさまざまに異なります。
Yii は、さまざまな形式の複数形語形変化に対応したメッセージ翻訳のための便利な方法を提供しています。
それは、非常に複雑な規則に対しても、十分に機能するものです。
語形変化の規則を直接に処理する代りに、特定の状況における語形変化した言葉の翻訳を提供するだけで十分です。

```php
echo \Yii::t('app', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

結果は以下のようになります。

- `$n = 0` の場合は "There are no cats!"
- `$n = 1` の場合は "There is one cat!"
- そして `$n = 42` の場合は "There are 42 cats!"

上記の複数形規則の引数において、`=0` はぴったりゼロ、`=1` はぴったり 1、そして `other` はそれ以外の数を表します。
`#` は `n` の値によって置き換えられます。
英語以外の言語では、これほど単純ではありません。
例えば、次はロシア語の例です。

```
Здесь {n, plural, =0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

上の例について言及する価値があると思われるのは、`=1` が `n = 1` にぴったりと一致するのに対して、`one` が `21` や `101` などに一致する、ということです。

注意して欲しいのは、あなたの [[yii\base\Application::$sourceLanguage|ソース言語]] を `ru_RU` に設定しなければ、このロシア語のメッセージを `Yii::t()` の中に直接に書くことは出来ない、ということです。
ただし、ソース言語を `ru_RU` に設定することは推奨されません。
むしろ、このような文字列はメッセージファイルまたは (DB ソースが使われている場合は) メッセージデータベースに入れるべきです。
Yii は翻訳された言語の文字列にある複数形規則を使います。翻訳が入手できない場合にはソース言語の複数形規則にフォールバックします。

あなたの言語について、どのような語形変化を指定すべきかを学習するためには、[unicode.org にある規則のリファレンス](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html) を参照してください。

> Note|訳注: 上記のソースメッセージの日本語翻訳は以下のようなものになります。
>
> '猫は{n, plural, =0{いません} other{#匹います}}。'
>
> 日本語では単数形と複数形を区別しませんので、たいていの場合、`=0` と `other` を指定するだけで事足ります。
> 横着をして、`{n, plural, ...}` を `{n, number}` に置き換えても、多分、大きな問題は生じないでしょう。

#### 選択肢

キーワードに基づいて表現を選択することが出来ます。
この場合のパターンは、キーワードに対する表現の割り当てを指定し、デフォルトの表現を提供するものです。

```php
echo \Yii::t('app', '{name} is a {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

これは "Snoopy is a dog and it loves Yii!" となります。

式の中で、`female` と `male` が `gender` が取り得る値であり、`other` がそれらに一致しない値を処理します。
波括弧の中の文字列は下位の式であり、単なる文字列でも、さらにプレースホルダを持つ文字列でも構いません。

> Note|訳注: 翻訳: '{name} は {gender} であり、{gender, select, female{彼女} male{彼} other{それ}}は Yii を愛しています。'
>
> 出力: 'Snoopy は dog であり、それは Yii を愛しています。'

### デフォルトの翻訳を指定する

他の翻訳にマッチしないカテゴリのフォールバックとして使用されるデフォルトの翻訳を指定することが出来ます。
この翻訳は `*` によってマークされなければなりません。
そうするためには、アプリケーションの構成情報に次のように追加します。

```php
// i18n コンポーネントを構成する

'i18n' => [
    'translations' => [
        '*' => [
            'class' => 'yii\i18n\PhpMessageSource'
        ],
    ],
],
```

こうすることで、個別に構成することなくカテゴリを使うことが可能になり、Yii 1.1 の振る舞いと同じになります。
カテゴリのメッセージは、デフォルトの翻訳の `basePath` すなわち `@app/messages` の下にあるファイルから読み込まれます。

```php
echo Yii::t('not_specified_category', 'message from unspecified category');
```

この場合、メッセージは `@app/messages/<LanguageCode>/not_specified_category.php` から読み込まれます。

### モジュールのメッセージを翻訳する

モジュール用のメッセージを翻訳したいけれども、全てのメッセージに対して一つの翻訳ファイルを使うことは避けたい、という場合には、次のようにすることが出来ます。

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

上記の例では、マッチングのためにワイルドカードを使い、次に必要なファイルごとに各カテゴリをフィルタリングしています。
`fileMap` を使わずに、カテゴリを同じ名前のファイルにマップする規約を使って済ませることも出来ます。
以上のようにすれば、直接に `Module::t('validation', 'your custom validation message')` や `Module::t('form', 'some form label')` などを使用することが出来ます。

### ウィジェットのメッセージを翻訳する

モジュールに適用できる同じ規則をウィジェットにも適用することが出来ます。例えば、

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

`fileMap` を使わずに、カテゴリを同じ名前のファイルにマップする規約を使って済ませることも出来ます。
これで、直接に `Menu::t('messages', 'new messages {messages}', ['{messages}' => 10])` を使用することが出来ます。

> **Note**|注意: ウィジェットのためには i18n ビューも使うことが出来ます。コントローラのための同じ規則がウィジェットにも適用されます。


### フレームワークメッセージを翻訳する

Yii には、検証エラーとその他いくつかの文字列に対するデフォルトの翻訳メッセージが付属しています。
これらのメッセージは、全て 'yii' というカテゴリの中にあります。
場合によっては、あなたのアプリケーションのために、デフォルトのフレームワークメッセージの翻訳を修正したいことがあるでしょう。
そうするためには、`i18n` [アプリケーションコンポーネント](structure-application-components.md) を以下のように構成してください。

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

これで、あなたの修正した翻訳を `@app/messages/<language>/yii.php` に置くことが出来ます。

### 欠落している翻訳の処理

ソースに翻訳が欠落している場合でも、Yii はリクエストされたメッセージの内容を表示します。
この振舞いは、原文のメッセージが正当かつ詳細なテキストである場合には、非常に好都合です。
しかし、場合によっては、それだけでは十分ではありません。
リクエストされた翻訳がソースに欠落しているときに、何らかの特別な処理を実行する必要がある場合もあります。
そういう処理は、[[yii\i18n\MessageSource]] の [[yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION|missingTranslation]] イベントを使うことによって達成できます。

例えば、全ての欠落している翻訳に何か目立つマークを付けて、ページに表示されたときに簡単に見つけられるようにしましょう。
最初にイベントハンドラをセットアップする必要があります。これはアプリケーションの構成によって行うことが出来ます。

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

次に、私たち独自のイベントハンドラを実装する必要があります。

```php
<?php

namespace app\components;

use yii\i18n\MissingTranslationEvent;

class TranslationEventHandler
{
    public static function handleMissingTranslation(MissingTranslationEvent $event)
    {
        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }
}
```

このイベントハンドラによって [[yii\i18n\MissingTranslationEvent::translatedMessage]] がセットされた場合は、それが翻訳結果として表示されます。

> Note|注意: 全てのメッセージソースは、欠落した翻訳をそれぞれ独自に処理します。
> いくつかのメッセージソースを使っていて、それらが同じ方法で欠落した翻訳を取り扱うようにしたい場合は、対応するイベントハンドラを全てのメッセージソースそれぞれに割り当てなければなりません。

### `message` コマンドを使う <a name="message-command"></a>

翻訳は [[yii\i18n\PhpMessageSource|php ファイル]]、[[yii\i18n\GettextMessageSource|.po ファイル]、または [[yii\i18n\DbMessageSource|database]] に保存することが出来ます。
追加のオプションについてはそれぞれのクラスを参照してください。

まず最初に、構成情報ファイルを作成する必要があります。
どこに保存したいかを決めて、次のコマンドを発行してください。

```bash
./yii message/config path/to/config.php
```

作成されたファイルを開いて、あなたの要求に合わせてパラメータを修正します。
特に、下記の項目に注意を払ってください。

* `languages`: あなたのアプリケーションが翻訳されるべき言語を表す配列。
* `messagePath`: メッセージファイルを保存するパス。
  これは、アプリケーションの構成情報で記述されている `i18n` の `basePath` と合致しなければなりません。

> エイリアスがここではサポートされていないことに注意してください。
  構成情報ファイルの位置からの相対パスで記述しなければなりません。

構成情報ファイルの編集が完了すれば、ついに、下記のコマンドを使ってメッセージを抽出することが出来ます。

```bash
./yii message path/to/config.php
```

これで、(あなたがファイルベースの翻訳を選択していた場合は) `messagePath` ディレクトリにファイルが出現します。


ビュー
------

前の項で説明したようなメッセージの翻訳の代りに、ビューで `i18n` を使ってさまざまな言語に対するサポートを提供することも出来ます。
例えば、`views/site/index.php` というビューがあり、それのロシア語のための特別版を作りたい場合は、現在のコントローラ/ウィジェットのビューパスの下に `ru-RU` フォルダを作って、ロシア語のためのファイルを `views/site/ru-RU/index.php` として置きます。
そうすると、Yii は、現在の言語のためのファイルが存在する場合はそれをロードし、何も見つからなかった場合はオリジナルのビューファイルにフォールバックします。

> **Note**|注意: 言語が `en-US` と指定されている場合、対応するビューが無いと、Yii は `en` の下でビューを探して、そこにも無ければ、オリジナルのビューを使います。


数値と日付の値を書式設定する
----------------------------

詳細は [データフォーマッタ](output-formatting.md) の節を参照してください。


PHP 環境をセットアップする <span id="setup-environment"></span>
--------------------------

Yii は、[[yii\i18n\Formatter]] クラスの数値や日付の書式設定や、[[yii\i18n\MessageFormatter]] を使うメッセージのフォーマッティングなど、ほとんどの国際化機能を提供するために [PHP intl 拡張](http://php.net/manual/ja/book.intl.php) を使います。
この二つのクラスは、`intl` がインストールされていない場合に備えて基本的な機能を提供するフォールバックを実装しています。
だだし、このフォールバックの実装は、英語のサイトでのみ十分に機能するものであり、たとえ英語のサイトであっても、PHP intl 拡張によって利用可能になる一連の豊かな機能を提供できるものではありません。
従って、PHP intl 拡張のインストールが強く推奨されます。

[PHP intl 拡張](http://php.net/manual/ja/book.intl.php) は、さまざまに異なる全てのロケールについて知識と書式の規則を提供する [ICU ライブラリ](http://site.icu-project.org/) に基礎を置いています。
この事実により、日付や数値の書式設定、また、メッセージのフォーマッティングで利用できる構文は、PHP バイナリとともにコンパイルされる ICU ライブラリのバージョンの違いによって異なってきます。

あなたのウェブサイトが全ての環境で同じ出力をすることを保証するためには、全ての環境において PHP intl 拡張をインストールし、PHP とともにコンパイルされた ICU ライブラリのバージョンが同一であることを確認することが推奨されます。

どのバージョンの ICU が PHP によって使われているかを知るために、次のスクリプトを走らせることが出来ます。
このスクリプトは、使用されている PHP と ICU のバージョンを出力します。

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
```

このドキュメントで説明されている全ての機能を使うことが出来るように、ICU のバージョンが 49 以上であることを推奨します。
49 未満のバージョンに欠落している主要な機能の一つが、複数形規則における `#` プレースホルダです。
入手できる ICU バージョン については、<http://site.icu-project.org/download> を参照してください。
バージョン番号の採番方式が 4.8 リリースの後に変更されて、最初の二つの数字が結合されたことに注意してください。
すなわち、ICU 4.8、ICU 49、ICU 50 という順序です。

これに加えて、ICU ライブラリとともに出荷されるタイムゾーンデータベースの情報も古くなっている可能性があります。
タイムゾーンデータベースの更新に関する詳細は [ICU マニュアル](http://userguide.icu-project.org/datetime/timezone#TOC-Updating-the-Time-Zone-Data) を参照してください。
出力の書式設定には ICU タイムゾーンデータベースが使用されますが、PHP によって使われるタイムゾーンデータベースも影響する可能性があります。
PHP のタイムゾーンデータベースは、[`timezonedb` pecl パッケージ](http://pecl.php.net/package/timezonedb) の最新版をインストールすることによって更新することが出来ます。
