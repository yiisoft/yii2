テーマ
======

<<<<<<< HEAD
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
=======
テーマは、元のビューレンダリングのコードに触れる必要なしに、[ビュー](structure-views.md) のセットを別のセットに置き換えるための方法です。
テーマを使うとアプリケーションのルックアンドフィールを体系的に変更することが出来ます。

テーマを使うためには、`view` アプリケーションコンポーネントの [[yii\base\View::theme|theme]] プロパティを構成しなければなりません。
このプロパティが、ビューファイルが置換される方法を管理する [[yii\base\Theme]] オブジェクトを構成します。
指定しなければならない [[yii\base\Theme]] のプロパティは主として以下のものです。

- [[yii\base\Theme::basePath]]: テーマのリソース (CSS、JS、画像など) を含むベースディレクトリを指定します。
- [[yii\base\Theme::baseUrl]]: テーマのリソースのベース URL を指定します。
- [[yii\base\Theme::pathMap]]: ビューファイルの置換の規則を指定します。
  詳細は後述する項で説明します。

例えば、`SiteController` で `$this->render('about')` を呼び出すと、ビューファイル `@app/views/site/about.php` をレンダリングすることになります。
しかし、下記のようにアプリケーション構成情報でテーマを有効にすると、代りに、ビューファイル `@app/themes/basic/site/about.php` がレンダリングされます。

```php
return [
    'components' => [
        'view' => [
            'theme' => [
                'basePath' => '@app/themes/basic'
                'baseUrl' => '@web/themes/basic',
                'pathMap' => [
                    '@app/views' => '@app/themes/basic',
                ],
            ],
        ],
    ],
];
```

> Info|情報: テーマではパスエイリアスがサポートされています。
  ビューの置換を行う際に、パスエイリアスは実際のファイルパスまたは URL に変換されます。

[[yii\base\View::theme]] プロパティを通じて [[yii\base\Theme]] オブジェクトにアクセスすることが出来ます。
例えば、ビューファイルの中では `$this` がビューオブジェクトを指すので、次のようなコードを書くことが出来ます。

```php
$theme = $this->theme;

// $theme->baseUrl . '/img/logo.gif' を返す
$url = $theme->getUrl('img/logo.gif');

// $theme->basePath . '/img/logo.gif' を返す
$file = $theme->getPath('img/logo.gif');
```

[[yii\base\Theme::pathMap]] プロパティが、ビューファイルがどのように置換されるべきかを制御します。
このプロパティは「キー・値」ペアの配列を取ります。
キーは置き換えられる元のビューのパスであり、値は対応するテーマのビューのパスです。
置換は部分一致に基づいて行われます。
あるビューのパスが [[yii\base\Theme::pathMap|pathMap]] 配列のキーのどれかで始っていると、その一致している部分が対応する配列の値によって置き換えられます。
上記の構成例を使う場合、`@app/views/site/about.php` は `@app/views` というキーに部分一致するため、`@app/themes/basic/site/about.php` に置き換えられることになります。


### モジュールにテーマを適用する <span id="theming-modules"></span>

モジュールにテーマを適用するためには、[[yii\base\Theme::pathMap]] を次のように構成します。

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/modules' => '@app/themes/basic/modules', // <-- !!!
>>>>>>> yiichina/master
],
```

これによって、`@app/modules/blog/views/comment/index.php` に `@app/themes/basic/modules/blog/views/comment/index.php` というテーマを適用することが出来ます。

<<<<<<< HEAD
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
=======

### ウィジェットにテーマを適用する <span id="theming-widgets"></span>

ウィジェットにテーマを適用するためには、[[yii\base\Theme::pathMap]] を次のように構成します。

```php
'pathMap' => [
    '@app/views' => '@app/themes/basic',
    '@app/widgets' => '@app/themes/basic/widgets', // <-- !!!
],
```

これによって、`@app/widgets/currency/views/index.php` に `@app/themes/basic/widgets/currency/index.php` というテーマを適用することが出来ます。


## テーマの継承 <span id="theme-inheritance"></span>

場合によっては、基本的なルックアンドフィールを含むアプリケーションの基本テーマを定義しておいて、現在の祝日に基づいて少しだけルックアンドフィールを変更したい、ということがあるかもしれません。
テーマの継承を使ってこの目的を達することが出来ます。
テーマの継承は、一つのビューパスを複数のターゲットに割り付けることによって設定することが出来ます。
例えば、
>>>>>>> yiichina/master

```php
'pathMap' => [
    '@app/views' => [
        '@app/themes/christmas',
        '@app/themes/basic',
    ],
]
```

<<<<<<< HEAD
この場合、最初に `@app/themes/christmas/site/index.php` というビューファイルが探され、それが見つからない場合は、次に `@app/themes/basic/site/index.php` が探されます。
そして、そこにもビューがない場合は、アプリケーションのビューが使用されます。

この機能は、いくつかのビューを一時的または条件的にオーバーライドしたい場合に、特に役立ちます。
=======
この場合、ビュー `@app/views/site/index.php` には、どちらのテーマファイルが存在するかに従って、`@app/themes/christmas/site/index.php` または `@app/themes/basic/site/index.php` というテーマが適用されます。
テーマファイルが両方とも存在する場合は、最初のものが優先されます。
実際の場面では、ほとんどのテーマビューファイルを `@app/themes/basic` に保管し、その中のいくつかを `@app/themes/christmas` でカスタマイズすることになるでしょう。
>>>>>>> yiichina/master
