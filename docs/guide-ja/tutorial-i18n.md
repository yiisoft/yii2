国際化
======

国際化 (I18N) とは、工学的な変更を伴わずにさまざまな言語と地域に順応できるように、ソフトウェアアプリケーションを設計するプロセスを指します。
潜在的なユーザが世界中にいるウェブアプリケーションにとっては、このことは特に重要な意味を持ちます。
Yii は、全ての領域にわたる国際化機能を提供し、メッセージの翻訳、ビューの翻訳、日付と数字の書式設定をサポートします。


## ロケールと言語 <span id="locale-language"></span>

ロケールとは、ユーザの言語、国、そして、ユーザが彼らのユーザインタフェイスにおいて目にすることを期待するすべての変異形式を定義する一連のパラメータです。
ロケールは、通常、言語 ID と地域 ID から成るロケール ID によって定義されます。
例えば、`en-US` は、英語とアメリカ合衆国のロケールを意味します。
Yii アプリケーションで使用される全てのロケール ID は、一貫性のために、`ll-CC` の形式に正規化されなければなりません。
ここで `ll` は [ISO-639](http://www.loc.gov/standards/iso639-2/) に従った小文字二つまたは三つの言語コードであり、`CC` は [ISO-3166](http://www.iso.org/iso/en/prods-services/iso3166ma/02iso-3166-code-lists/list-en1.html) に従った二文字の国コードです。
ロケールに関する更なる詳細は [ICU プロジェクトのドキュメント project](http://userguide.icu-project.org/locale#TOC-The-Locale-Concept) に述べられています。

Yii では、"言語" という用語でロケールに言及することがしばしばあります。

Yii のアプリケーションでは二つの言語を使用します。
すなわち、[[yii\base\Application::$sourceLanguage|ソース言語]] と [[yii\base\Application::$language|ターゲット言語]] です。
前者はソースコード中のテキストメッセージが書かれている言語を意味し、後者はコンテントをエンドユーザに表示するのに使用されるべき言語を指します。
いわゆるメッセージ翻訳サービスは、主として、テキストメッセージをソース言語からターゲット言語に翻訳するものです。

アプリケーションの言語は、アプリケーションの構成情報で次のように構成することが出来ます。

```php
return [
    // ターゲット言語を日本語に設定
    'language' => 'ja-JP',
    
    // ソース言語を英語に設定
    'sourceLanguage' => 'en-US',
    
    ......
];
```

[[yii\base\Application::$sourceLanguage|ソース言語]] のデフォルト値は `en-US` であり、合衆国の英語を意味します。
このデフォルト値は変えないことが推奨されます。
なぜなら、通常は、英語から他の言語への翻訳者を見つける方が、非英語から非英語への翻訳者を見つけるより、はるかに簡単だからです。

[[yii\base\Application::$language|ターゲット言語]] は、エンドユーザの言語選択など、さまざまな要因に基づいて、動的に設定しなければならないことがよくあります。
アプリケーションの構成情報で構成するかわりに、次の文を使ってターゲット言語を変更することが出来ます。

```php
// ターゲット言語を中国語に変更
\Yii::$app->language = 'zh-CN';
```


## メッセージ翻訳 <span id="message-translation"></span>

メッセージ翻訳サービスは、テキストメッセージをある言語 (通常は [[yii\base\Application::$sourceLanguage|ソース言語]]) から別の言語 (通常は [[yii\base\Application::$language|ターゲット言語]]) に翻訳するものです。
翻訳は、元のメッセージと翻訳されたメッセージを格納するメッセージソースの中から、翻訳対象となったメッセージを探すことにより行われます。
メッセージが見つかれば、対応する翻訳されたメッセージが返されます。
メッセージが見つからなければ、元のメッセージが翻訳されずに返されます。

メッセージ翻訳サービスを使用するためには、主として次の作業をする必要があります。

* 翻訳する必要のある全てのテキストメッセージを [[Yii::t()]] メソッドの呼び出しの中に包む。
* メッセージ翻訳サービスが翻訳されたメッセージを探すことが出来る一つまたは複数のメッセージソースを構成する。
* 翻訳者にメッセージを翻訳させて、それをメッセージソースに格納する。

[[Yii::t()]] メソッドは次のように使います。

```php
echo \Yii::t('app', 'This is a string to translate!');
```

ここで、二番目のパラメータが翻訳されるべきテキストメッセージを示し、最初のパラメータはメッセージを分類するのに使用されるカテゴリ名を示します。

[[Yii::t()]] メソッドは `i18n` [アプリケーションコンポーネント](structure-application-components.md) を呼んで実際の翻訳作業を実行します。
このコンポーネントはアプリケーションの構成情報の中で次のようにして構成することが出来ます。

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

上記のコードにおいては、[[yii\i18n\PhpMessageSource]] によってサポートされるメッセージソースが構成されています。
`app*` は、`app` で始まる全てのメッセージカテゴリがこのメッセージソースを使って翻訳されるべきであることを示しています。
[[yii\i18n\PhpMessageSource]] クラスは、メッセージ翻訳を格納するのに PHP ファイルを使用します。
デフォルトでは、ファイル名はカテゴリ名と同じでなければなりません。
ただし、[[yii\i18n\PhpMessageSource::fileMap|fileMap]] を構成して、別の命名方法によってカテゴリを PHP ファイルに割り付けることも可能です。
上記の例では、(`ja-JP` がターゲット言語であると仮定すると) `app/error` のカテゴリは `@app/messages/ja-JP/error.php` という PHP ファイルに割り付けられます。
`fileMap` を構成しなければ、このカテゴリは `@app/messages/ja-JP/app/error.php` に割り付けられることになります。

翻訳メッセージを格納するのには、PHP ファイル以外に、次のメッセージソースを使うことも可能です。

- [[yii\i18n\GettextMessageSource]] - 翻訳メッセージを保持するのに GNU Gettext の MO ファイルまたは PO ファイルを使用する
- [[yii\i18n\DbMessageSource]] - 翻訳メッセージを保存するのにデータベーステーブルを使用する


## メッセージのフォーマット <span id="message-formatting"></span>

メッセージを翻訳するときには、プレースホルダを埋め込んで、動的なパラメータ値で実行時に置き換えさせることが出来ます。
更には、パラメータ値をターゲット言語に応じてフォーマットさせるための特別なプレースホルダの構文を使うことも出来ます。
この項では、メッセージをフォーマットする様々な方法を説明します。

> Note|訳注: 以下においては、メッセージフォーマットの理解を助けるために、原文にはない日本語への翻訳例 (とその出力結果) をコードサンプルに追加しています。

### メッセージパラメータ <span id="message-parameters"></span>

翻訳対象となるメッセージには、一つまたは複数のプレースホルダを埋め込んで、与えられたパラメータ値で置き換えられるようにすることが出来ます。
様々なパラメータ値のセットを与えることによって、翻訳されるメッセージを動的に変化させることが出来ます。
次の例では、`'Hello, {username}!'` というメッセージの中のプレースホルダ `{username}` が `'Alexander'` と `'Qiang'` にそれぞれ置き換えられます。

```php
$username = 'Alexander';
// username が "Alexander" になった翻訳メッセージを表示
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);

$username = 'Qiang';
// username が "Qiang" になった翻訳メッセージを表示
echo \Yii::t('app', 'Hello, {username}!', [
    'username' => $username,
]);
```

プレースホルダを持つメッセージを翻訳する時には、プレースホルダはそのままにしておかなければなりません。
これは、プレースホルダは `Yii::t()` を呼んでメッセージを翻訳する時に、実際のパラメータ値に置き換えられるものだからです。

```php
// 日本語翻訳例: '{username} さん、こんにちは!'
```

プレースホルダには、*名前付きプレースホルダ* と *序数プレースホルダ* のどちらかを使用する事が出来ます。
ただし、一つのメッセージに両方を使うことは出来ません。

上記の例は名前付きプレースホルダの使い方を示すものです。
すなわち、各プレースホルダは `{パラメータ名}` という形式で書かれており、パラメータは連想配列で渡されます。
このとき、配列のキーが(波括弧なしの)パラメータ名であり、配列の値が対応するパラメータ値です。

序数プレースホルダは、0 ベースの整数の序数をプレースホルダとして使います。
このプレースホルダは、`Yii::t()` の呼び出しに出現する順序に従って、パラメータ値によって置き換えられます。
次の例では、序数プレースホルダ `{0}`、`{1}` および `{2}` は、それぞれ、`$price`、`$count` および `$subtotal` の値によって置き換えられます。

```php
$price = 100;
$count = 2;
$subtotal = 200;
echo \Yii::t('app', 'Price: {0}, Count: {1}, Subtotal: {2}', $price, $count, $subtotal);
```

```php
// 日本語翻訳例: '価格: {0}, 数量: {1}, 小計: {2}'
```

> Tip|ヒント: たいていの場合は名前付きプレースホルダを使うべきです。
> と言うのは、翻訳者にとっては、パラメータ名がある方が、翻訳すべきメッセージ全体をより良く理解できるからです。


### パラメータのフォーマット <span id="parameter-formatting"></span>

メッセージのプレースホルダにフォーマットの規則を追加して指定し、パラメータ値がプレースホルダを置き換える前に適切にフォーマットされるようにすることが出来ます。
次の例では、`price` のパラメータ値の型は数値として扱われ、通貨の形式でフォーマットされます。

```php
$price = 100;
echo \Yii::t('app', 'Price: {0, number, currency}', $price);
```

> Note|注意: パラメータのフォーマットには、[intl PHP 拡張](http://www.php.net/manual/ja/intro.intl.php) のインストールが必要です。

プレースホルダにフォーマット規則を指定するためには、短い構文または完全な構文のどちらかを使うことが出来ます。

```
短い形式: {プレースホルダ名, パラメータの型}
完全な形式: {プレースホルダ名, パラメータの型, パラメータの形式}
```

このようなプレースホルダを指定する方法についての完全な説明は、[ICU ドキュメント](http://icu-project.org/apiref/icu4c/classMessageFormat.html) を参照してください。

以下では、よくある使用方法をいくつか示します。

#### 数値 <span id="number"></span>

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number}', $sum);

// 翻訳: '差引残高: {0, number}'
// 出力: '差引残高: 12,345'
```

オプションで、パラメータの形式として、`integer`、`currency`、`percent` を指定することが出来ます。

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, currency}', $sum);

// 翻訳: '差引残高: {0, number, currency}'
// 出力: '差引残高: &yen; 12,345'
```

または、数値をフォーマットするカスタムパターンを指定することも出来ます。

```php
$sum = 42;
echo \Yii::t('app', 'Balance: {0, number, ,000,000000}', $sum);

// 翻訳: '差引残高: {0, number, ,000,000000}'
// 出力: '差引残高: 000,012345'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1DecimalFormat.html).

#### 日付 <span id="date"></span>

パラメータ値は日付としてフォーマットされます。例えば、

```php
echo \Yii::t('app', 'Today is {0, date}', time());

// 翻訳: '今日は {0, date} です。'
// 出力: '今日は 2015/01/07 です。'
```

オプションで、パラメータの形式として、`short`、`medium`、`long`、そして `full` を指定することが出来ます。

```php
echo \Yii::t('app', 'Today is {0, date, short}', time());

// 翻訳: '今日は {0, date, short} です。'
// 出力: '今日は 2015/01/07 です。'
```

日付の値をフォーマットするカスタムパターンを指定することも出来ます。

```php
echo \Yii::t('app', 'Today is {0, date, yyyy-MM-dd}', time());

// 翻訳: '今日は {0, date, yyyy-MM-dd} です。'
// 出力: '今日は 2015-01-07 です。'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).

#### 時刻 <span id="time"></span>

パラメータ値は時刻としてフォーマットされます。例えば、

```php
echo \Yii::t('app', 'It is {0, time}', time());

// 翻訳: '現在 {0, time} です。'
// 出力: '現在 22:37:47 です。'
```

オプションで、パラメータの形式として、`short`、`medium`、`long`、そして `full` を指定することが出来ます。

```php
echo \Yii::t('app', 'It is {0, time, short}', time());

// 翻訳: '現在 {0, time, short} です。'
// 出力: '現在 22:37 です。'
```

時刻の値をフォーマットするカスタムパターンを指定することも出来ます。

```php
echo \Yii::t('app', 'It is {0, date, HH:mm}', time());

// 翻訳: '現在 {0, time, HH:mm} です。'
// 出力: '現在 22:37 です。'
```

[書式のリファレンス](http://icu-project.org/apiref/icu4c/classicu_1_1SimpleDateFormat.html).


#### 綴り <span id="spellout"></span>

パラメータ値は数値として取り扱われ、綴りとしてフォーマットされます。例えば、

```php
// 出力例 : "42 is spelled as forty two"
echo \Yii::t('app', '{n, number} is spelled as {n, spellout}', ['n' => 42]);

// 翻訳: '{n, number} は、文字で綴ると {n, spellout} です。'
// 出力: '12,345 は、文字で綴ると 四十二 です。'
```

#### 序数 <span id="ordinal"></span>

パラメータ値は数値として取り扱われ、順序を表す文字列としてフォーマットされます。例えば、

```php
// 出力例 : "You are the 42nd visitor here!"
echo \Yii::t('app', 'You are the {n, ordinal} visitor here!', ['n' => 42]);
```

> Note|訳注: 上記のソースメッセージを、プレースホルダの書式指定を守って日本語に翻訳すると、'あなたはこのサイトの {n, ordinal} の訪問者です' となります。
> しかし、その出力結果は、'あなたはこのサイトの 第42 の訪問者です' となり、意味は通じますが、日本語としては若干不自然なものになります。
>
> プレースホルダの書式指定自体も、翻訳の対象として、より適切なものに変更することが可能であることに注意してください。
>
> この場合も、'あなたはこのサイトの{n, plural, =1{最初} other{#番目}}の訪問者です' のように翻訳するほうが適切でしょう。

#### 継続時間 <span id="duration"></span>

パラメータ値は秒数として取り扱われ、継続時間を表す文字列としてフォーマットされます。例えば、

```php
// 出力例 : "You are here for 47 sec. already!"
echo \Yii::t('app', 'You are here for {n, duration} already!', ['n' => 47]);
```

> Note|訳注: このソースメッセージを 'あなたはこのサイトに既に {n, duration} の間滞在しています' と翻訳した場合の出力結果は、'あなたはこのサイトに既に 47 の間滞在しています' となります。
> ICU ライブラリのバグでしょうか。これも、プレースホルダの書式設定も含めて全体を翻訳し直す方が良いようです。


#### 複数形 <span id="plural"></span>

言語によって、複数形の語形変化はさまざまに異なります。
Yii は、さまざまな形式の複数形語形変化に対応したメッセージ翻訳のための便利な方法を提供しています。
それは、非常に複雑な規則に対しても、十分に機能するものです。
語形変化の規則を直接に処理する代りに、特定の状況における語形変化した言葉の翻訳を提供するだけで十分です。

```php
// $n = 0 の場合の出力例 : "There are no cats!"
// $n = 1 の場合の出力例 : "There is one cat!"
// $n = 42 の場合の出力例 : "There are 42 cats!"
echo \Yii::t('app', 'There {n, plural, =0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

上記の複数形規則の引数において、`=0` はぴったりゼロ、`=1` はぴったり 1、そして `other` はそれ以外の数を表します。
`#` は `n` の値によって置き換えられます。

複数形の規則が非常に複雑な言語もあります。
例えば、次はロシア語の例では、`=1` が `n = 1` にぴったりと一致するのに対して、`one` が `21` や `101` などに一致します。

```
Здесь {n, plural, =0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

注意して欲しいのは、上記のメッセージは主として翻訳メッセージとして使用される、という点です。
アプリケーションの [[yii\base\Application::$sourceLanguage|ソース言語]] を `ru-RU` に設定しない限り、オリジナルのメッセージには使用されません。

オリジナルのメッセージに対する翻訳が見つからない場合は、[[yii\base\Application::$sourceLanguage|ソース言語]] の複数形規則がオリジナルのメッセージに対して適用されます。

特定の言語について、どのような語形変化を指定すべきかを学習するためには、[unicode.org にある規則のリファレンス](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html) を参照してください。

> Note|訳注: 上記のソースメッセージの日本語翻訳は以下のようなものになります。
>
> '猫は{n, plural, =0{いません} other{#匹います}}。'
>
> 日本語では単数形と複数形を区別しませんので、たいていの場合、`=0` と `other` を指定するだけで事足ります。
> 横着をして、`{n, plural, ...}` を `{n, number}` に置き換えても、多分、大きな問題は生じないでしょう。

#### 選択肢 <span id="selection"></span>

パラメータの型として `select` を使うと、パラメータ値に基づいて表現を選択することが出来ます。例えば、

```php
// 出力例 : "Snoopy is a dog and it loves Yii!"
echo \Yii::t('app', '{name} is a {gender} and {gender, select, female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

上記の式の中で、`female` と `male` が `gender` が取り得る値であり、`other` がそれらに一致しない値を処理します。
それぞれの取り得る値の後には、波括弧で囲んで対応する表現を指定します。

> Note|訳注: 日本語翻訳例: '{name} は {gender} であり、{gender, select, female{彼女} male{彼} other{それ}}は Yii を愛しています。'
>
> 日本語出力例: 'Snoopy は dog であり、それは Yii を愛しています。'

### デフォルトの翻訳を指定する <span id="default-translation"></span>

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

### モジュールのメッセージを翻訳する <span id="module-translation"></span>

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

### ウィジェットのメッセージを翻訳する <span id="widget-translation"></span>

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


### フレームワークメッセージを翻訳する <span id="framework-translation"></span>

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

### 欠落している翻訳の処理 <span id="missing-translations"></span>

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


## ビューの翻訳 <span id="view-translation"></span>
------

前の項で説明したようなメッセージの翻訳の代りに、ビューで `i18n` を使ってさまざまな言語に対するサポートを提供することも出来ます。
例えば、`views/site/index.php` というビューがあり、それのロシア語のための特別版を作りたい場合は、現在のコントローラ/ウィジェットのビューパスの下に `ru-RU` フォルダを作って、ロシア語のためのファイルを `views/site/ru-RU/index.php` として置きます。
そうすると、Yii は、現在の言語のためのファイルが存在する場合はそれをロードし、何も見つからなかった場合はオリジナルのビューファイルにフォールバックします。

> **Note**|注意: 言語が `en-US` と指定されている場合、対応するビューが無いと、Yii は `en` の下でビューを探して、そこにも無ければ、オリジナルのビューを使います。


## 数値と日付の値を書式設定する <span id="date-number"></span>

詳細は [データフォーマッタ](output-formatting.md) の節を参照してください。


## PHP 環境をセットアップする <span id="setup-environment"></span>

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
