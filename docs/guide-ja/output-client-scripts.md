クライアント・スクリプトを扱う
==============================

今日のウェブ・アプリケーションでは、静的な HTML ページがレンダリングされてブラウザに送信されるだけでなく、
JavaScript によって、既存の要素を操作したり、新しいコンテントを AJAX でロードしたりして、
ブラウザに表示されるページを修正します。
このセクションでは、JavaScript と CSS をウェブ・サイトに追加したり、
それらを動的に調整するために Yii によって提供されているメソッドを説明します。

## スクリプトを登録する <span id="register-scripts"></span>

[[yii\web\View]] オブジェクトを扱う際には、フロントエンド・スクリプトを動的に登録することが出来ます。
このための専用のメソッドが二つあります。

- インライン・スクリプトのための [[yii\web\View::registerJs()|registerJs()]]
- 外部スクリプトのための [[yii\web\View::registerJsFile()|registerJsFile()]]

### インライン・スクリプトを登録する <span id="inline-scripts"></span>

インライン・スクリプトは、設定のためのコード、動的に生成されるコード、および、[ウィジェット](structure-widgets.md) に含まれる再利用可能なフロントエンド・コードが生成する
コード断片などです。インライン・スクリプトを追加するためのメソッド [[yii\web\View::registerJs()|registerJs()]] は、次のようにして使うことが出来ます。

```php
$this->registerJs(
    "$('#myButton').on('click', function() { alert('ボタンがクリックされました'); });",
    View::POS_READY,
    'my-button-handler'
);
```

最初の引数は、ページに挿入したい実際の JS コードです。これが `<script>` タグに包まれて挿入されます。
二番目の引数は、スクリプトがページのどの位置に挿入されるべきかを決定します。
取りうる値は以下のとおりです。

- [[yii\web\View::POS_HEAD|View::POS_HEAD]] - head セクション。
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] - 開始の `<body>` の直後。
- [[yii\web\View::POS_END|View::POS_END]] - 終了の `</body>` の直前。
- [[yii\web\View::POS_READY|View::POS_READY]] - [ドキュメントの `ready` イベント](https://learn.jquery.com/using-jquery-core/document-ready/) でコードを実行するための指定。
  これを指定すると、[[yii\web\JqueryAsset|jQuery]] が自動的に登録され、コードは適切な jQuery コードの中に包まれます。これがデフォルトの位置指定です。
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] - [ドキュメントの `load` イベント](https://learn.jquery.com/using-jquery-core/document-ready/) でコードを実行するための指定。
  上記と同じく、これを指定すると、[[yii\web\JqueryAsset|jQuery]] が自動的に登録されます。

最後の引数は、スクリプトのコード・ブロックを一意に特定するために使われるスクリプトのユニークな ID です。同じ ID のスクリプトが既にある場合は、新しいものを追加するのでなく、それを置き換えます。
ID を指定しない場合は、JS コードそれ自身が ID として扱われます。この ID によって、同じコードが複数回登録されるのを防止します。

### スクリプト・ファイルを登録する <span id="script-files"></span>

[[yii\web\View::registerJsFile()|registerJsFile()]] の引数は、
[[yii\web\View::registerCssFile()|registerCssFile()]] の引数と同様なものです。
以下に示す例では、`main.js` ファイルを、[[yii\web\JqueryAsset]] への依存関係とともに、登録します。
これは、`main.js` ファイルは `jquery.js` の後に追加される、ということを意味します。
このような依存関係の仕様が無ければ、`main.js` と `jquery.js` の間の相対的な順序は未定義となり、コードは動作しなくなるでしょう。

外部スクリプトは次のようにして追加することが出来ます。

```php
$this->registerJsFile(
    '@web/js/main.js',
    ['depends' => [\yii\web\JqueryAsset::class]]
);
```

これによって、アプリケーションの [base URL](concept-aliases.md#predefined-aliases) の下に配置されている `/js/main.js` スクリプトを読み込むタグが追加されます。

ただし、外部 JS ファイルを登録するのには、 [[yii\web\View::registerJsFile()|registerJsFile()]] を使わずに、[アセット・バンドル](structure-assets.md) を使うことが強く推奨されます。
なぜなら、そうする方が、柔軟性も高く、依存関係の構成も粒度を細かく出来るからです。また、アセット・バンドルを使えば、複数の JS ファイルを結合して圧縮すること (アクセスの多いウェブ・サイトではそうすることが望まれます) が可能になります。

## CSS を登録する <span id="register-css"></span>

Javascript と同様に、[[yii\web\View::registerCss()|registerCss()]]
または [[yii\web\View::registerCssFile()|registerCssFile()]]
を使って CSS を登録することが出来ます。
前者は CSS のコードブロックを登録し、後者は外部 CSS ファイルを登録するものです。

### インライン CSS を登録する <span id="inline-css"></span>

```php
$this->registerCss("body { background: #f00; }");
```

上記のコードによって、結果として、下記の出力がページの `<head>` セクションに追加されます。

```html
<style>
body { background: #f00; }
</style>
```

`style` タグに追加の属性を指定したい場合は、名前-値 の配列を二番目の引数として渡します。最後の引数は、スタイルのブロックを一意に特定するために使われるユニークな ID です。
同じスタイルがコードの別の箇所で重複して登録されたとしても、このスタイルのブロックが一度だけ追加されることを保証するものです。

### CSS ファイルを登録する <span id="css-files"></span>

CSS ファイルは次のようにして登録することが出来ます。

```php
$this->registerCssFile("@web/css/themes/black-and-white.css", [
    'depends' => [\yii\bootstrap\BootstrapAsset::class],
    'media' => 'print',
], 'css-print-theme');
```

上記のコードは `/css/themes/black-and-white.css` という CSS ファイルに対するリンクをページの `<head>` セクションに追加します。

* 最初の引数が、登録される CSS ファイルを指定します。
  この例における `@web` in this example is an [アプリケーションのベース URL に対するエイリアス](concept-aliases.md#predefined-aliases) です。
* 二番目の引数は、結果として出力される `<link>` タグの HTML 属性を指定するものです。
  ただし、`depends` というオプションは特別な処理を受けます。これは、この CSS ファイルが依存するアセット・バンドルを指定するものです。
  この例の場合は、[[yii\bootstrap\BootstrapAsset|BootstrapAsset]] が依存するアセット・バンドルです。
  これは、この CSS ファイルが [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] に属する CSS ファイルの*後に*追加されることを意味します。
* 最後の引数はこの CSS ファイルを特定する ID を指定するものです。
  省略された場合は、CSS ファイルの URL が代りに ID として使用されます。

外部 CSS ファイルを登録するのには、 [[yii\web\View::registerCssFile()|registerCssFile()]] を使わずに、
[アセット・バンドル](structure-assets.md) を使うことが強く推奨されます。アセット・バンドルを使えば、複数の CSS ファイルを結合して圧縮すること
(アクセスの多いウェブ・サイトではそうすることが望まれます) が可能になります。
また、アプリケーションの全てのアセットの依存関係を一ヶ所で構成することが出来るため、より大きな柔軟性を得ることが出来ます。


## アセット・バンドルを登録する <span id="asset-bundles"></span>

既に述べたように、CSS ファイルと JavaScript ファイルを直接に登録する代りにアセット・バンドルを使うことが推奨されます。
アセット・バンドルを定義する方法の詳細は、ガイドの [アセット](structure-assets.md) のセクションで知ることが出来ます。
既に定義されているアセット・バンドルの使い方は、
次のように非常に単純明快です。

```php
\frontend\assets\AppAsset::register($this);
```

上記のコードでは、ビュー・ファイルのコンテキストにおいて、`AppAsset` バンドルが (`$this` で表される) 現在のビューに対して登録されています。
ウィジェットの中からアセット・バンドルを登録するときは、ウィジェットの [[yii\base\Widget::$view|$view]]
を代りに渡します (`$this->view`)。


## 動的な Javascript を生成する <span id="dynamic-js"></span>

ビュー・ファイルでは、HTML コードが直接に書き出されのではなく、ビューの変数に依存して、PHP のコードによって生成されることがよくあります。
生成された HTML を Javascript によって操作するためには、JS コードも同様に動的な部分を含まなければなりません。
例えば、jQuery セレクタの ID などがそうです。

PHP の変数を JS コードに挿入するためには、変数の値を適切にエスケープする必要があります。
JS コードを専用の JS ファイルの中に置くのではなく、HTML に挿入する場合は特にそうです。
Yii は、この目的のために、[[yii\helpers\Json|Json]] ヘルパの [[yii\helpers\Json::htmlEncode()|htmlEncode()]] メソッドを提供しています。
その使用方法は、以下の例の中で示されています。

### グローバルな JavaScript の構成情報を登録する <span id="js-configuration"></span>

この例では、配列を使って、グローバルな構成情報のパラメータをアプリケーションの
PHP のパートから JS のフロントエンド・コードに渡します。

```php
$options = [
    'appName' => Yii::$app->name,
    'baseUrl' => Yii::$app->request->baseUrl,
    'language' => Yii::$app->language,
    // ...
];
$this->registerJs(
    "var yiiOptions = ".\yii\helpers\Json::htmlEncode($options).";",
    View::POS_HEAD,
    'yiiOptions'
);
```

上記のコードは、次のような JavaScript の変数定義を含む `<script>` タグを登録します。
例えば、

```javascript
var yiiOptions = {"appName":"My Yii Application","baseUrl":"/basic/web","language":"en"};
```

このようにすれば、あなたの Javascript コードで、これらの構成情報に `yiiOptions.baseUrl` や `yiiOptions.language` のようにしてアクセスすることが出来るようになります。.

### 翻訳されたメッセージを渡す <span id="translated-messages"></span>

あなたの JavaScript が何らかのイベントに反応してメッセージを表示する必要がある、という状況に遭遇するかも知れません。
複数の言語で動作するアプリケーションでは、この文字列は、現在のアプリケーシの言語に翻訳されなければなりません。
これを達成する一つの方法は、Yii によって提供されている [メッセージ翻訳機能] (tutorial-i18n.md#message-translation) を使って、その結果を JavaScript コードに渡すことです。

```php
$message = \yii\helpers\Json::htmlEncode(
    \Yii::t('app', 'Button clicked!')
);
$this->registerJs(<<<JS
    $('#myButton').on('click', function() { alert( $message ); });
JS
);
```

上記のサンプル・コードは、可読性を高めるために、PHP の [ヒアドキュメント構文](https://www.php.net/manual/ja/language.types.string.php#language.types.string.syntax.heredoc) を使っています。
また、ヒアドキュメントは、たいていの IDE で、より良い構文ハイライトが可能になるので、
インライン JavaScript、特に一行に収まらないものを書くときに推奨される方法です。
変数 `$message` は PHP で生成され、[[yii\helpers\Json::htmlEncode|Json::htmlEncode]] のおかげで、適切な JS 構文の文字列を含むものになります。
それを JavaScript コードに挿入して、`alert()` の関数呼び出しに動的な文字列を渡すことが出来ます。

> Note: ヒアドキュメントを使う場合は、JS コード中の変数名に注意してください。
> `$` で始まる変数は、PHP の変数として解釈され、
> その値によって置き換えられる可能性があります。
> ただし、`$(` または `$.` という形式の jQuery 関数は
> PHP 変数として解釈される心配は無く、安全に使うことが出来ます。

## `yii.js` スクリプト <span id="yii.js"></span>

> Note: このセクションはまだ書かれていません。このセクションは、`yii.js` によって提供される以下の機能についての説明を含むはずのものです。
> 
> - Yii JavaScript モジュール
> - CSRF パラメータの処理
> - `data-confirm` ハンドラ
> - `data-method` ハンドラ
> - スクリプトのフィルタリング
> - リダイレクトの処理

