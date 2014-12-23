テーマ
======

> Note|注意: この節はまだ執筆中です。

テーマとは、あるディレクトリの下に集められたビューとレイアウトのファイルです。
テーマの各ファイルが、アプリケーションの対応するファイルをレンダリングの際にオーバーライドします。
一つのアプリケーションは複数のテーマを使用することが可能で、それぞれのテーマはまったく異なるユーザ体験を提供することが出来ます。
いつでも一つのテーマだけがアクティブになり得ます。

> Note|注意: ビューはアプリケーションの固有性が強いものですので、通常は、テーマを再配布可能なものとして作ることはしません。
  カスタマイズしたルックアンドフィールを再配布したい場合は、テーマの代りに、[アセットバンドル](structure-assets.md) の形で CSS と JavaScript のファイルを再配布することを検討してください。

テーマを構成する
----------------

テーマの構成情報は、アプリケーションの `view` コンポーネントを通じて指定します。
`basic application` のビューに対して働くテーマをセットアップするためには、アプリケーションの構成情報ファイルに以下のように記述しなければなりません。

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => ['@app/views' => '@app/themes/basic'],
            'baseUrl' => '@web/themes/basic',
        ],
    ],
],
```

上記においては、`pathMap` が元のパスからテーマのパスへの割り付けを定義し、`baseUrl` がテーマのファイルによって参照されるリソースのベース URL を定義しています。

私たちの例では、`pathMap` は `['@app/views' => '@app/themes/basic']` です。
これは、`@app/views` の全てのビューは、最初に `@app/themes/basic` の下で探され、そのテーマのディレクトリにビューが存在していれば、それが元のビューの代りに使われる、ということを意味します。

例えば、上記の構成においては、ビューファイル `@app/views/site/index.php` のテーマ版は `@app/themes/basic/site/index.php` になります。
基本的には、`@app/views/site/index.php` の `@app/views` を `@app/themes/basic` に置き換えるわけです。

ランタイムにおいてテーマを構成するためには、ビューをレンダリングする前に次のコードを使用することが出来ます。
典型的には、コントローラの中に次のコードを置きます。

```php
$this->getView()->theme = Yii::createObject([
    'class' => '\yii\base\Theme',
    'pathMap' => ['@app/views' => '@app/themes/basic'],
    'baseUrl' => '@web/themes/basic',
]);
```

### モジュールにテーマを適用する

モジュールにテーマを適用するためには、`pathMap` を次のようなものにすることが出来ます。

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => [
                '@app/views' => '@app/themes/basic',
                '@app/modules' => '@app/themes/basic/modules', // <-- !!!
            ],
        ],
    ],
],
```

これによって、`@app/modules/blog/views/comment/index.php` に `@app/themes/basic/modules/blog/views/comment/index.php` というテーマを適用することが出来ます。

### ウィジェットにテーマを適用する

`@app/widgets/currency/views/index.php` に配置されているウィジェットのビューにテーマを適用するためには、ビューコンポーネントのテーマに、次のような構成情報を設定する必要があります。

```php
'components' => [
    'view' => [
        'theme' => [
            'pathMap' => ['@app/widgets' => '@app/themes/basic/widgets'],
        ],
    ],
],
```

上記の構成によって、`@app/widgets/currency/index.php` ビューのテーマ版を `@app/themes/basic/widgets/currency/index.php` として作成することが出来るようになります。

複数のパスを使う
----------------

一つのパスを複数のテーマパスに割り付けることが出来ます。例えば、

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

この場合、最初に `@app/themes/christmas/site/index.php` というビューファイルが探され、それが見つからない場合は、次に `@app/themes/basic/site/index.php` が探されます。
そして、そこにもビューがない場合は、アプリケーションのビューが使用されます。

この機能は、いくつかのビューを一時的または条件的にオーバーライドしたい場合に、特に役立ちます。
