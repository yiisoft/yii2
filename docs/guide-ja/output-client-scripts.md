クライアントスクリプトを扱う
============================

> Note|注意: この節はまだ執筆中です。

### スクリプトを登録する

[[yii\web\View]] オブジェクトに対してスクリプトを登録することが出来ます。
このための専用のメソッドが二つあります。
すなわち、インラインスクリプトのための [[yii\web\View::registerJs()|registerJs()]] と、外部スクリプトのための [[yii\web\View::registerJsFile()|registerJsFile()]] です。
インラインスクリプトは、設定のためや、動的に生成されるコードのために有用なものです。
次のようにして、これらを追加するメソッドを使うことが出来ます。

```php
$this->registerJs("var options = ".json_encode($options).";", View::POS_END, 'my-options');
```

最初の引数は、ページに挿入したい実際の JS コードです。
二番目の引数は、スクリプトがページのどの場所に挿入されるべきかを決定します。
取りうる値は以下のとおりです。

- [[yii\web\View::POS_HEAD|View::POS_HEAD]] - head セクション。
- [[yii\web\View::POS_BEGIN|View::POS_BEGIN]] - 開始の `<body>` の直後。
- [[yii\web\View::POS_END|View::POS_END]] - 終了の `</body>` の直前。
- [[yii\web\View::POS_READY|View::POS_READY]] - ドキュメントの `ready` イベントで実行するコード。これを指定すると、[[yii\web\JqueryAsset|jQuery]] が自動的に登録されます。
- [[yii\web\View::POS_LOAD|View::POS_LOAD]] - ドキュメントの `load` イベントで実行するコード。これを指定すると、[[yii\web\JqueryAsset|jQuery]] が自動的に登録されます。

最後の引数は、スクリプトのユニークな ID です。これによってコードブロックを一意に特定し、同じ ID のスクリプトが既にある場合は、新しいものを追加するのでなく、それを置き換えます。
ID を指定しない場合は、JS コードそれ自身が ID として扱われます。

外部スクリプトは次のようにして追加することが出来ます。

```php
$this->registerJsFile('http://example.com/js/main.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
```

[[yii\web\View::registerJsFile()|registerJsFile()]] の引数は [[yii\web\View::registerCssFile()|registerCssFile()]] のそれと同じです。
上記の例では、`main.js` ファイルを `JqueryAsset` に依存するものとして登録しています。
これは、`main.js` ファイルが `jquery.js` の後に追加されるようになることを意味します。
この依存関係を指定しない場合は、`main.js` と `jquery.js` の相対的な順序は未定義となります。

[[yii\web\View::registerCssFile()|registerCssFile()]] と同じように、外部 JS ファイルを登録するのに [[yii\web\View::registerJsFile()|registerJsFile()]] を使わずに、[アセットバンドル](structure-assets.md) を使うことが強く推奨されます。


### アセットバンドルを登録する

既に述べたように、CSS と JavaScript を直接に使う代りにアセットバンドルを使うことが望まれます。
アセットバンドルを定義する方法の詳細は、ガイドの [アセットマネージャ](structure-assets.md) の節で知ることが出来ます。
既に定義されているアセットバンドルを使うことについては、次のように非常に簡明です。

```php
\frontend\assets\AppAsset::register($this);
```



### CSS を登録する

[[yii\web\View::registerCss()|registerCss()]] または [[yii\web\View::registerCssFile()|registerCssFile()]] を使って CSS を登録することが出来ます。
前者は CSS のコードブロックを登録し、後者は外部 CSS ファイルを登録します。
例えば、

```php
$this->registerCss("body { background: #f00; }");
```

上記のコードは、以下の内容をページの head セクションに追加する結果となります。

```html
<style>
body { background: #f00; }
</style>
```

`style` タグに追加のプロパティを指定したい場合は、三番目の引数として「名前-値」のペアの配列を渡します。
`style` タグが一つだけになることを保証する必要がある場合は、メタタグの説明で言及したように、4番目の引数を使います。

```php
$this->registerCssFile("http://example.com/css/themes/black-and-white.css", [
    'depends' => [BootstrapAsset::className()],
    'media' => 'print',
], 'css-print-theme');
```

上記のコードは、ページの head セクションに CSS ファイルへのリンクを追加します。

* 最初の引数が登録されるべき CSS ファイルを指定します。
* 二番目の引数は、結果として生成される `<link>` タグの HTML 属性を指定するものです。
  ただし、`depends` オプションは特別な処理を受けます。
  このオプションは、この CSS ファイルが依存するアセットバンドルを指定するものです。
  この例では、依存するアセットバンドルは [[yii\bootstrap\BootstrapAsset|BootstrapAsset]] です。
  これは、この CSS ファイルが、[[yii\bootstrap\BootstrapAsset|BootstrapAsset]] に含まれる CSS ファイルの *後* に追加されることを意味します。
* 最後の引数は、この CSS ファイルを特定するための ID を指定するものです。
  指定されていない場合は、CSS ファイルの URL が代りに ID として使用されます。

外部 CSS ファイルを登録するためには、[[yii\web\View::registerCssFile()|registerCssFile()]] を使うのではなく、[アセットバンドル](structure-assets.md) を使うことが強く推奨されます。
アセットバンドルを使うと、複数の CSS ファイルを結合して圧縮することが可能になります。
トラフィックの多いウェブサイトではそうすることが望まれます。
