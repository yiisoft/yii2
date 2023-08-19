国際化
======

国際化 (I18N) とは、工学的な変更を伴わずにさまざまな言語と地域に順応できるように、
ソフトウェア・アプリケーションを設計するプロセスを指します。
潜在的なユーザが世界中にいるウェブ・アプリケーションにとっては、このことは特に重要な意味を持ちます。
Yii は、全ての領域にわたる国際化機能を提供し、メッセージの翻訳、ビューの翻訳、日付と数字の書式設定をサポートします。


## ロケールと言語 <span id="locale-language"></span>

### ロケール

ロケールとは、ユーザの言語、国、そして、ユーザが彼らのユーザ・インタフェイスにおいて目にすることを期待する
すべての変異形式を定義する一連のパラメータです。
ロケールは、通常、言語 ID と地域 ID から成るロケール ID によって定義されます。

例えば、`en-US` という ID は、「英語とアメリカ合衆国」というロケールを意味します。

Yii アプリケーションで使用される全てのロケール ID は、一貫性のために、
`ll-CC` の形式に正規化されなければなりません。
ここで `ll` は [ISO-639](https://www.loc.gov/standards/iso639-2/) に従った小文字二つまたは三つの言語コードであり、
`CC` は [ISO-3166](https://en.wikipedia.org/wiki/ISO_3166-1#Current_codes) に従った二文字の国コードです。
ロケールに関する更なる詳細は [ICU プロジェクトのドキュメント](https://unicode-org.github.io/icu/userguide/locale/#the-locale-concept)
に述べられています。

### 言語

Yii では、"言語" という用語でロケールに言及することがしばしばあります。

Yii のアプリケーションでは二つの言語を使用します。すなわち、
* [[yii\base\Application::$sourceLanguage|ソース言語]] : ソース・コード中のテキスト・メッセージが書かれている言語。
* [[yii\base\Application::$language|ターゲット言語]] : コンテントをエンド・ユーザに表示するのに使用されるべき言語。

いわゆるメッセージ翻訳サービスは、主として、テキスト・メッセージをソース言語からターゲット言語に翻訳するものです。

### 構成
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
このデフォルト値は変えないことが **推奨** されます。
なぜなら、通常は、英語から他の言語への翻訳者を見つける方が、非英語から非英語への翻訳者を見つけるより、はるかに簡単だからです。

[[yii\base\Application::$language|ターゲット言語]] は、エンド・ユーザの言語選択など、
さまざまな要因に基づいて、動的に設定しなければならないことがよくあります。
アプリケーションの構成情報で構成するかわりに、次の文を使ってターゲット言語を変更することが出来ます。

```php
// ターゲット言語を中国語に変更
\Yii::$app->language = 'zh-CN';
```

> Tip: ソース言語がコードの部分によって異なる場合は、メッセージ・ソースごとにソース言語をオーバーライドすることが出来ます。
> これについては、次の説で説明します。

## メッセージ翻訳 <span id="message-translation"></span>

### ソース言語からターゲット言語へ
メッセージ翻訳サービスは、テキスト・メッセージをある言語 (通常は [[yii\base\Application::$sourceLanguage|ソース言語]])
から別の言語 (通常は [[yii\base\Application::$language|ターゲット言語]]) に翻訳するものです。

翻訳は、元のメッセージと翻訳されたメッセージを格納するメッセージ・ソースの中から、翻訳対象となったメッセージを探すことにより行われます。
メッセージが見つかれば、対応する翻訳されたメッセージが返されます。メッセージが見つからなければ、元のメッセージが翻訳されずに返されます。

### 実装の仕方
メッセージ翻訳サービスを使用するためには、主として次の作業をする必要があります。

1. 翻訳する必要のある全てのテキスト・メッセージを [[Yii::t()]] メソッドの呼び出しの中に包む。
2. メッセージ翻訳サービスが翻訳されたメッセージを探すことが出来る一つまたは複数のメッセージ・ソースを構成する。
3. 翻訳者にメッセージを翻訳させて、それをメッセージ・ソースに格納する。


#### 1. テキスト・メッセージを包む
[[Yii::t()]] メソッドは次のように使います。

```php
echo \Yii::t('app', 'This is a string to translate!');
```

ここで、二番目のパラメータが翻訳されるべきテキスト・メッセージを示し、
最初のパラメータはメッセージを分類するのに使用されるカテゴリ名を示します。

#### 2. 一つまたは複数のメッセージ・ソースを構成する
[[Yii::t()]] メソッドは `i18n` [アプリケーション・コンポーネント](structure-application-components.md) の `translate` メソッドを呼んで実際の翻訳作業を実行します。
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

上記のコードにおいては、[[yii\i18n\PhpMessageSource]] によってサポートされるメッセージ・ソースが構成されています。

##### シンボル `*` によるカテゴリのワイルドカード

`app*` は、`app` で始まる全てのメッセージ・カテゴリが
このメッセージ・ソースを使って翻訳されるべきであることを示しています。

#### 3. 翻訳者にメッセージを翻訳させて、それをメッセージ・ソースに格納する

[[yii\i18n\PhpMessageSource]] クラスは、単純な PHP 配列を持つ複数の PHP ファイルを使用してメッセージ翻訳を格納します。
それらのファイルが、「ソース言語」のメッセージと「ターゲット言語」の翻訳とのマップを含みます。

> Info: それらのファイルを [`message` コマンド](#message-command) を使用して自動的に生成することが出来ます。
> このセクションで後で紹介します。

PHP ファイルは、それぞれ、一つのカテゴリのメッセージに対応します。
デフォルトでは、ファイル名はカテゴリ名と同じでなければなりません。`app/messages/nl-NL/main.ph` の例を示します。

```php
<?php

/**
* Translation map for nl-NL
*/
return [
    'welcome' => 'welkom'
];

```


##### ファイルのマッピング

ただし、[[yii\i18n\PhpMessageSource::fileMap|fileMap]] を構成して、別の命名方法によってカテゴリを PHP ファイルにマップすることも可能です。

上記の例では、(`ja-JP` がターゲット言語であると仮定すると) `app/error` のカテゴリは 
`@app/messages/ja-JP/error.php` という PHP ファイルにマップされます。
`fileMap` を構成しなければ、このカテゴリは `@app/messages/ja-JP/app/error.php` にマップされることになります。

#####  他のストレージ・タイプ

翻訳メッセージを格納するのには、PHP ファイル以外に、
次のメッセージ・ソースを使うことも可能です。

- [[yii\i18n\GettextMessageSource]] - 翻訳メッセージを保持するのに GNU Gettext の MO ファイルまたは PO ファイルを使用する
- [[yii\i18n\DbMessageSource]] - 翻訳メッセージを保存するのにデータベース・テーブルを使用する


## メッセージのフォーマット <span id="message-formatting"></span>

メッセージを翻訳するときには、プレースホルダを埋め込んで、動的なパラメータ値で実行時に置き換えさせることが出来ます。
更には、パラメータ値をターゲット言語に応じてフォーマットさせるための特別なプレースホルダの構文を使うことも出来ます。
この項では、メッセージをフォーマットする様々な方法を説明します。

> Note: 以下においては、メッセージ・フォーマットの理解を助けるために、原文にはない日本語への翻訳例 (とその出力結果) をコード・サンプルに追加しています。

### メッセージ・パラメータ <span id="message-parameters"></span>

翻訳対象となるメッセージには、一つまたは複数のパラメータ (プレースホルダとも呼びます) を埋め込んで、
与えられたパラメータ値で置き換えられるようにすることが出来ます。
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
これは、プレースホルダは `Yii::t()` を呼んでメッセージを翻訳する時に、実際の値に置き換えられるものだからです。

```php
// 日本語翻訳: '{username} さん、こんにちは!'
```

プレースホルダには、*名前付きプレースホルダ* と *序数プレースホルダ* のどちらかを使用する事が出来ます。ただし、一つのメッセージに両方を使うことは出来ません。

上記の例は名前付きプレースホルダの使い方を示すものです。
すなわち、各プレースホルダは `{name}` という形式で書かれていますが、それに対して、キーが(波括弧なしの)プレースホルダ名であり、
値がそのプレースホルダを置き換える値である連想配列を渡す訳です。

序数プレースホルダは、0 ベースの整数の序数をプレースホルダ名として使います。
このプレースホルダは、`Yii::t()` の呼び出しに出現する順序に従って、パラメータ値によって置き換えられます。
次の例では、序数プレースホルダ `{0}`、`{1}` および `{2}` は、それぞれ、`$price`、`$count` および `$subtotal` の値によって置き換えられます。

```php
$price = 100;
$count = 2;
$subtotal = 200;
echo \Yii::t('app', 'Price: {0}, Count: {1}, Subtotal: {2}', [$price, $count, $subtotal]);
```

```php
// 日本語翻訳: '価格: {0}, 数量: {1}, 小計: {2}'
```

序数プレースホルダが一つだけの場合は、値を配列に入れずにそのまま指定することができます。

```php
echo \Yii::t('app', 'Price: {0}', $price);
```

> Tip: たいていの場合は名前付きプレースホルダを使うべきです。
> と言うのは、翻訳者にとっては、パラメータ名がある方が、翻訳すべきメッセージ全体をより良く理解できるからです。


### パラメータのフォーマット <span id="parameter-formatting"></span>

メッセージのプレースホルダにフォーマットの規則を追加して指定し、
パラメータ値がプレースホルダを置き換える前に適切にフォーマットされるようにすることが出来ます。
次の例では、`price` のパラメータ値の型は数値として扱われ、通貨の形式でフォーマットされます。

```php
$price = 100;
echo \Yii::t('app', 'Price: {0,number,currency}', $price);
```

> Note: パラメータのフォーマットには、[intl PHP 拡張](https://www.php.net/manual/ja/intro.intl.php) のインストールが必要です。

プレースホルダにフォーマット規則を指定するためには、短い構文または完全な構文のどちらかを使うことが出来ます。

```
短い形式: {name,type}
完全な形式: {name,type,style}
```

> Note: `{`、`}`、`'`、`#` などの特殊な文字を使用する必要がある場合は、その部分の文字列を `'` で囲んでください。
> 
```php
echo Yii::t('app', "Example of string with ''-escaped characters'': '{' '}' '{test}' {count,plural,other{''count'' value is # '#{}'}}", ['count' => 3]);
+```

このようなプレースホルダを指定する方法についての完全な説明は、[ICU ドキュメント](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classMessageFormat.html)を参照してください。以下では、よくある使用方法をいくつか示します。


#### 数値 <span id="number"></span>

```php
$sum = 12345;
echo \Yii::t('app', 'Balance: {0,number}', $sum);

// 日本語翻訳: '差引残高: {0,number}'
// 日本語出力: '差引残高: 12,345'
```

オプションのパラメータとして、`integer`、`currency`、`percent` のスタイルを指定することが出来ます。

```php
$sum = 12345;
echo \Yii::t('app', 'Balance: {0,number,currency}', $sum);

// 日本語翻訳: '差引残高: {0,number,currency}'
// 日本語出力: '差引残高: ￥12,345'
```

または、数値をフォーマットするカスタム・パターンを指定することも出来ます。

```php
$sum = 12345;
echo \Yii::t('app', 'Balance: {0,number,,000,000000}', $sum);

// 日本語翻訳: '差引残高: {0,number,,000,000000}'
// 日本語出力: '差引残高: 000,012345'
```

カスタムフォーマットで使用される文字については、
[ICU API リファレンス](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classDecimalFormat.html) の "Special Pattern Characters"
のセクションに記述されています。
 
数値は常に翻訳先のロケールに従ってフォーマットされます。
つまり、ロケールを変更せずに、小数点や桁区切りを変更することは出来ません。
それらをカスタマイズしたい場合は [[yii\i18n\Formatter::asDecimal()]] や [[yii\i18n\Formatter::asCurrency()]] を使うことが出来ます。

#### 日付 <span id="date"></span>

パラメータ値は日付としてフォーマットされます。例えば、

```php
echo \Yii::t('app', 'Today is {0,date}', time());

// 日本語翻訳: '今日は {0,date} です。'
// 日本語出力: '今日は 2015/01/07 です。'
```

オプションのパラメータとして、`short`、`medium`、`long`、そして `full` のスタイルを指定することが出来ます。

```php
echo \Yii::t('app', 'Today is {0,date,short}', time());

// 日本語翻訳: '今日は {0,date,short} です。'
// 日本語出力: '今日は 2015/01/07 です。'
```

日付の値をフォーマットするカスタム・パターンを指定することも出来ます。

```php
echo \Yii::t('app', 'Today is {0,date,yyyy-MM-dd}', time());

// 日本語翻訳: '今日は {0,date,yyyy-MM-dd} です。'
// 日本語出力: '今日は 2015-01-07 です。'
```

[書式のリファレンス](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classicu_1_1SimpleDateFormat.html#details).

#### 時刻 <span id="time"></span>

パラメータ値は時刻としてフォーマットされます。例えば、

```php
echo \Yii::t('app', 'It is {0,time}', time());

// 日本語翻訳: '現在 {0,time} です。'
// 日本語出力: '現在 22:37:47 です。'
```

オプションのパラメータとして、`short`、`medium`、`long`、そして `full` のスタイルを指定することが出来ます。

```php
echo \Yii::t('app', 'It is {0,time,short}', time());

// 日本語翻訳: '現在 {0,time,short} です。'
// 日本語出力: '現在 22:37 です。'
```

時刻の値をフォーマットするカスタム・パターンを指定することも出来ます。

```php
echo \Yii::t('app', 'It is {0,date,HH:mm}', time());

// 日本語翻訳: '現在 {0,time,HH:mm} です。'
// 日本語出力: '現在 22:37 です。'
```

[書式のリファレンス](https://unicode-org.github.io/icu-docs/apidoc/released/icu4c/classicu_1_1SimpleDateFormat.html#details).


#### 綴り <span id="spellout"></span>

パラメータ値は数値として取り扱われ、綴りとしてフォーマットされます。例えば、

```php
// 出力例 : "42 is spelled as forty-two"
echo \Yii::t('app', '{n,number} is spelled as {n,spellout}', ['n' => 42]);

// 日本語翻訳: '{n,number} は、文字で綴ると {n,spellout} です。'
// 日本語出力: '42 は、文字で綴ると 四十二 です。'
```

デフォルトでは、数値は基数として綴られます。それを変更することは可能です。

```php
// 出力例 : "I am forty-seventh agent"
echo \Yii::t('app', 'I am {n,spellout,%spellout-ordinal} agent', ['n' => 47]);

// 日本語翻訳: '私は{n,spellout,%spellout-ordinal}の工作員です。'
// 日本語出力: '私は第四十七の工作員です。'
```

'spellout,' と '%' の間に空白を入れてはならないことに注意してください。

あなたが使用しているロケールで利用可能なオプションのリストについては、
[https://intl.rmcreative.ru/](https://intl.rmcreative.ru/) の "Numbering schemas, Spellout" を参照してください。


#### 序数 <span id="ordinal"></span>

パラメータ値は数値として取り扱われ、順序を表す文字列としてフォーマットされます。例えば、

```php
// 出力: "You are the 42nd visitor here!"
echo \Yii::t('app', 'You are the {n,ordinal} visitor here!', ['n' => 42]);
```

序数については、スペイン語などの言語では、さらに多くのフォーマットがサポートされています。

```php
// 出力: "471ª"
echo \Yii::t('app', '{n,ordinal,%digits-ordinal-feminine}', ['n' => 471]);
```

'ordinal,' と '%' の間に空白を入れてはならないことに注意してください。

あなたが使用しているロケールで利用可能なオプションのリストについては、
[https://intl.rmcreative.ru/](https://intl.rmcreative.ru/) の "Numbering schemas, Ordinal" を参照してください。

> Note: 上記のソース・メッセージを、プレースホルダのスタイルを守って日本語に翻訳すると、'あなたはこのサイトの{n,ordinal}の訪問者です' となります。
> しかし、その出力結果は、'あなたはこのサイトの第42の訪問者です' となり、意味は通じますが、日本語としては若干不自然なものになります。
>
> プレースホルダのスタイル自体も、翻訳の対象として、より適切なものに変更することが可能であることに注意してください。
>
> この場合も、'あなたはこのサイトの{n,plural,=1{最初} other{#番目}}の訪問者です' のように翻訳するほうが適切でしょう。

#### 継続時間 <span id="duration"></span>

パラメータ値は秒数として取り扱われ、継続時間を表す文字列としてフォーマットされます。例えば、

```php
// 出力: "You are here for 47 sec. already!"
echo \Yii::t('app', 'You are here for {n,duration} already!', ['n' => 47]);
```

継続時間については、さらに多くのフォーマットがサポートされています。

```php
// 出力: '130:53:47'
echo \Yii::t('app', '{n,duration,%in-numerals}', ['n' => 471227]);
```

'duration,' と '%' の間に空白を入れてはならないことに注意してください。

あなたが使用しているロケールで利用可能なオプションのリストについては、
[https://intl.rmcreative.ru/](https://intl.rmcreative.ru/) の "Numbering schemas, Duration" を参照してください。

> Note: このソース・メッセージを 'あなたはこのサイトに既に{n,duration}の間滞在しています' と翻訳した場合の出力結果は、'あなたはこのサイトに既に47の間滞在しています' となります。
> これも、プレースホルダのスタイルも含めて全体を翻訳し直す方が良いでしょう。
> どうも、ICU ライブラリは、ja_JP の数値関連の書式指定においては、割と貧弱な実装にとどまっている印象です。


#### 複数形 <span id="plural"></span>

言語によって、複数形の語形変化はさまざまに異なります。Yii は、さまざまな形式の複数形語形変化に対応したメッセージ翻訳のための便利な方法を提供しています。
それは、非常に複雑な規則に対しても、十分に機能するものです。
語形変化の規則を直接に処理する代りに、特定の状況における語形変化した言葉の翻訳を提供するだけで十分です。

```php
// $n = 0 の場合の出力: "There are no cats!"
// $n = 1 の場合の出力: "There is one cat!"
// $n = 42 の場合の出: "There are 42 cats!"
echo \Yii::t('app', 'There {n,plural,=0{are no cats} =1{is one cat} other{are # cats}}!', ['n' => $n]);
```

上記の複数形規則の引数において、`=` はぴったりその値であることを意味します。従って、`=0` はぴったりゼロ、`=1` はぴったり 1 を表します。
`other` はそれ以外の数を表します。`#` は ターゲット言語に従ってフォーマットされた `n` の値によって置き換えられます。

複数形の規則が非常に複雑な言語もあります。
例えば、次のロシア語の例では、`=1` が `n = 1` にぴったりと一致するのに対して、`one` が `21` や `101` などに一致します。

```
Здесь {n,plural,=0{котов нет} =1{есть один кот} one{# кот} few{# кота} many{# котов} other{# кота}}!
```

これら `other`、`few`、`many` などの特別な引数の名前は言語によって異なります。
特定のロケールに対してどんな引数を指定すべきかを学ぶためには、[https://intl.rmcreative.ru/](https://intl.rmcreative.ru/) の "Plural Rules, Cardinal" を参照してください。
あるいは、その代りに、[unicode.org の規則のリファレンス](https://cldr.unicode.org/index/cldr-spec/plural-rules) を参照することも出来ます。

> Note: 上記のロシア語のメッセージのサンプルは、主として翻訳メッセージとして使用されるものです。
> アプリケーションの [[yii\base\Application::$sourceLanguage|ソース言語]] を `ru-RU` にしてロシア語から他の言語に翻訳するという設定にしない限り、オリジナルのメッセージとしては使用されることはありません。
>
> `Yii::t()` の呼び出しにおいて、オリジナルのメッセージに対する翻訳が見つからない場合は、
> [[yii\base\Application::$sourceLanguage|ソース言語]] の複数形規則がオリジナルのメッセージに対して適用されます。

文字列が以下のようなものである場合のために `offset` というパラメータがあります。
 
```php
$likeCount = 2;
echo Yii::t('app', 'You {likeCount,plural,
    offset: 1
    =0{did not like this}
    =1{liked this}
    one{and one other person liked this}
    other{and # others liked this}
}', [
    'likeCount' => $likeCount
]);

// 出力: 'You and one other person liked this'
```

> Note: 上記のソース・メッセージの日本語翻訳は以下のようなものになります。
>
> '猫は{n, plural, =0{いません} other{#匹います}}。'
>
> 日本語では単数形と複数形を区別しませんので、たいていの場合、`=0` と `other` を指定するだけで事足ります。
> 横着をして、`{n, plural, ...}` を `{n, number}` に置き換えても、多分、大きな問題は生じないでしょう。


#### 序数選択肢 <span id="ordinal-selection"></span>

パラメータのタイプとして `selectordinal` を使うと、翻訳先ロケールの言語規則に基づいて序数のための文字列を選択することが出来ます。

```php
$n = 3;
echo Yii::t('app', 'You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor', ['n' => $n]);
// 英語の出力
// You are the 3rd visitor

// ロシア語の翻訳
'You are the {n,selectordinal,one{#st} two{#nd} few{#rd} other{#th}} visitor' => 'Вы {n, selectordinal, other{#-й}} посетитель',

// ロシア語の出力
// Вы 3-й посетитель
```

フォーマットは複数形で使われるものと非常に近いものです。
特定のロケールに対してどんな引数を指定すべきかを学ぶためには、[https://intl.rmcreative.ru/](https://intl.rmcreative.ru/) の "Plural Rules, Ordinal" を参照してください。
あるいは、その代りに、[unicode.org の規則のリファレンス](https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html) を参照することも出来ます。


#### 選択肢 <span id="selection"></span>

パラメータのタイプとして `select` を使うと、パラメータの値に基づいて表現を選択することが出来ます。例えば、

```php
// 出力: "Snoopy is a dog and it loves Yii!"
echo \Yii::t('app', '{name} is a {gender} and {gender,select,female{she} male{he} other{it}} loves Yii!', [
    'name' => 'Snoopy',
    'gender' => 'dog',
]);
```

上記の式の中で、`female` と `male` が `gender` が取り得る値であり、`other` がそれらに一致しない値を処理します。
それぞれの取り得る値の後には、波括弧で囲んで対応する表現を指定します。

> Note: 日本語翻訳: '{name} は {gender} であり、{gender,select,female{彼女} male{彼} other{それ}}は Yii を愛しています。'
>
> 日本語出力: 'Snoopy は dog であり、それは Yii を愛しています。'

### デフォルトのメッセージ・ソースを指定する <span id="default-message-source"></span>

構成されたカテゴリのどれにもマッチしないカテゴリのためのフォールバックとして使用される、デフォルトのメッセージ・ソースを指定することが出来ます。
これは、ワイルドカードのカテゴリ `*` を構成することによって可能になります。
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

> Note: ウィジェットのためには i18n ビューも使うことが出来ます。コントローラのための同じ規則がウィジェットにも適用されます。


### フレームワーク・メッセージを翻訳する <span id="framework-translation"></span>

Yii には、検証エラーとその他いくつかの文字列に対するデフォルトの翻訳メッセージが付属しています。これらのメッセージは、全て 'yii' というカテゴリの中にあります。
場合によっては、あなたのアプリケーションのために、デフォルトのフレームワーク・メッセージの翻訳を修正したいことがあるでしょう。
そうするためには、`i18n` [アプリケーション・コンポーネント](structure-application-components.md) を以下のように構成してください。

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
この振舞いは、原文のメッセージが正当かつ詳細なテキストである場合には、非常に好都合です。しかし、場合によっては、それだけでは十分ではありません。
リクエストされた翻訳がソースに欠落しているときに、何らかの特別な処理を実行する必要がある場合もあります。
そういう処理は、[[yii\i18n\MessageSource]] の [[yii\i18n\MessageSource::EVENT_MISSING_TRANSLATION|missingTranslation]] イベントを使うことによって達成できます。

例えば、全ての欠落している翻訳に何か目立つマークを付けて、ページに表示されたときに簡単に見つけられるようにしましょう。
最初にイベント・ハンドラをセットアップする必要があります。これはアプリケーションの構成によって行うことが出来ます。

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

次に、私たち独自のイベント・ハンドラを実装する必要があります。

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

このイベント・ハンドラによって [[yii\i18n\MissingTranslationEvent::translatedMessage]] がセットされた場合は、それが翻訳結果として表示されます。

> Note: 全てのメッセージ・ソースは、欠落した翻訳をそれぞれ独自に処理します。
> いくつかのメッセージ・ソースを使っていて、それらが同じ方法で欠落した翻訳を取り扱うようにしたい場合は、対応するイベント・ハンドラを全てのメッセージ・ソースそれぞれに割り当てなければなりません。

### `message` コマンドを使う <span id="message-command"></span>

翻訳は [[yii\i18n\PhpMessageSource|php ファイル]]、[[yii\i18n\GettextMessageSource|.po ファイル]]、または [[yii\i18n\DbMessageSource|database]] に保存することが出来ます。追加のオプションについてはそれぞれのクラスを参照してください。

まず最初に、構成情報ファイルを作成する必要があります。
どこに保存したいかを決めて、次のコマンドを発行してください。

```bash
./yii message/config-template path/to/config.php
```

作成されたファイルを開いて、あなたの要求に合わせてパラメータを修正します。特に、下記の項目に注意を払ってください。

* `languages`: あなたのアプリケーションが翻訳されるべき言語を表す配列。
* `messagePath`: メッセージファイルを保存するパス。これは、アプリケーションの構成情報で記述されている `i18n` の `basePath` と合致しなければなりません。

'./yii message/config' コマンドを使って、CLI 経由で、指定したオプションを持つ設定ファイルを動的に生成することも可能です。
例えば、`languages` と `messagePath` のパラメータは、次のようにして設定することが出来ます。

```bash
./yii message/config --languages=de,ja --messagePath=messages path/to/config.php
```

利用可能なオプションのリストを取得するためには、次のコマンドを実行します。

```bash
./yii help message/config
```

構成情報ファイルの編集が完了すれば、ついに、下記のコマンドを使ってメッセージを抽出することが出来ます。

```bash
./yii message path/to/config.php
```

また、オプションを指定して、抽出のパラメータを動的に変更することも出来ます。

これで、(あなたがファイル・ベースの翻訳を選択していた場合は) `messagePath` ディレクトリにファイルが出現します。


## ビューの翻訳 <span id="view-translation"></span>

個々のテキスト・メッセージを翻訳する代りに、ビュー・スクリプト全体を翻訳したい場合があるでしょう。
この目的を達するためには、ビューを翻訳して、ターゲット言語と同じ名前のサブ・ディレクトリに保存するだけで大丈夫です。
例えば、`views/site/index.php` というビューをターゲット言語 `ru-RU` に翻訳したい場合は、翻訳したビューを `views/site/ru-RU/index.php` というファイルとして保存します。
このようにすると、[[yii\base\View::renderFile()]] メソッド、または、このメソッドを呼び出す他のメソッド
(例えば [[yii\base\Controller::render()]]) を呼んで `views/site/index.php` をレンダリングするたびに、
翻訳された `views/site/ru-RU/index.php` が代りにレンダリングされるようになります。

> Note: [[yii\base\Application::$language|ターゲット言語]] が [[yii\base\Application::$sourceLanguage|ソース言語]] と同じ場合は、
> 翻訳されたビューの有無にかかわらず、オリジナルのビューがレンダリングされます。


## 数値と日付の値を書式設定する <span id="date-number"></span>

詳細は [データのフォーマット](output-formatting.md) のセクションを参照して下さい。


## PHP 環境をセットアップする <span id="setup-environment"></span>

Yii は、[[yii\i18n\Formatter]] クラスの数値や日付の書式設定や、[[yii\i18n\MessageFormatter]] を使うメッセージのフォーマッティングなど、ほとんどの国際化機能を提供するために [PHP intl 拡張](https://www.php.net/manual/ja/book.intl.php) を使います。
この二つのクラスは、`intl` がインストールされていない場合に備えて基本的な機能を提供するフォールバックを実装しています。
だだし、このフォールバックの実装は、英語がターゲット言語である場合にのみ十分に機能するものす。
従って、国際化機能が必要とされる場合は、`intl` をインストールすることが強く推奨されます。

[PHP intl 拡張](https://www.php.net/manual/ja/book.intl.php) は、さまざまに異なる全てのロケールについて知識と書式の規則を提供する
[ICU ライブラリ](https://icu.unicode.org/) に基礎を置いています。
ICU のバージョンが異なると、日付や数値のフォーマットの結果も異なる場合があります。
あなたのウェブ・サイトが全ての環境で同じ出力をすることを保証するためには、
全ての環境において同じバージョンの PHP intl 拡張 (従って同じバージョンの ICU) をインストールすることが推奨されます。

どのバージョンの ICU が PHP によって使われているかを知るために、次のスクリプトを走らせることが出来ます。このスクリプトは、使用されている PHP と ICU のバージョンを出力します。

```php
<?php
echo "PHP: " . PHP_VERSION . "\n";
echo "ICU: " . INTL_ICU_VERSION . "\n";
echo "ICU Data: " . INTL_ICU_DATA_VERSION . "\n";
```

さらに、バージョン 49 以上の ICU を使用する事も推奨されます。そうすることによって、このドキュメントで説明されている全ての機能を使うことが出来るようになります。
例えば、49 未満の ICU は、複数形規則における `#` プレースホルダをサポートしていません。
入手できる ICU バージョン については、<https://icu.unicode.org/download> を参照してください。
バージョン番号の採番方式が 4.8 リリースの後に変更されたことに注意してください (すなわち、ICU 4.8、ICU 49、ICU 50、等々となっています)。

これに加えて、ICU ライブラリとともに出荷されるタイム・ゾーン・データベースの情報も古くなっている可能性があります。
タイム・ゾーン・データベースの更新に関する詳細は [ICU マニュアル](https://unicode-org.github.io/icu/userguide/datetime/timezone/#updating-the-time-zone-data) を参照してください。
出力の書式設定には ICU タイム・ゾーン・データベースが使用されますが、PHP によって使われるタイム・ゾーン・データベースも影響する可能性があります。
PHP のタイム・ゾーン・データベースは、[`timezonedb` pecl パッケージ](https://pecl.php.net/package/timezonedb) の最新版をインストールすることによって更新することが出来ます。
