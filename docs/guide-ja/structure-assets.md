アセット
========

Yii では、アセットは、ウェブページで参照できるファイルを意味します。CSS ファイルであったり、JavaScript ファイルであったり、
画像やビデオのファイルであったりします。アセットはウェブでアクセス可能なディレクトリに配置され、
ウェブサーバによって直接に提供されます。

たいていの場合、アセットはプログラム的に管理する方が望ましいものです。例えば、ページの中で [[yii\jui\DatePicker]]
ウィジェットを使うとき、ウィジェットが必要な CSS と JavaScript のファイルを自動的にインクルードします。あなたに対して、
手作業で必要なファイルを探してインクルードするように要求したりはしません。そして、ウィジェットを新しいバージョンに
アップグレードしたときは、ウィジェットが自動的に新しいバージョンのアセットファイルを使用するようになります。
このチュートリアルでは、Yii によって提供される強力なアセット管理機能について説明します。


## アセットバンドル <a name="asset-bundles"></a>

Yii はアセットを *アセットバンドル* を単位として管理します。アセットバンドルは、単にあるディレクトリの下に集められた
一群のアセットに過ぎません。[ビュー](structure-views.md) の中でアセットバンドルを登録すると、バンドルの中の CSS や
JavaScript のファイルがレンダリングされるウェブページに挿入されます。


## アセットバンドルを定義する <a name="defining-asset-bundles"></a>

アセットバンドルは [[yii\web\AssetBundle]] から拡張された PHP クラスとして定義されます。バンドルの名前は、対応する PHP
クラスの完全修飾名 (先頭のバックスラッシュを除く) です。アセットバンドルクラスは [オートロード可能](concept-autoloading.md)
でなければなりません。アセットバンドルクラスは、通常、アセットがどこに置かれているか、バンドルがどういう CSS や JavaScript
のファイルを含んでいるか、そして、バンドルが他のバンドルにどのように依存しているかを定義します。

以下のコードは [ベーシックアプリケーションテンプレート](start-installation.md) によって使用されているメインのアセットバンドルを定義するものです:

```php
<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

上の `AppAsset` クラスは、アセットファイルが `@webroot` ディレクトリの下に配置されており、それが URL `@web` に対応することを
定義しています。バンドルは一つだけ CSS ファイル `css/site.css` を含み、JavaScript ファイルは含みません。バンドルは、
他の二つのバンドル、すなわち [[yii\web\YiiAsset]] と [[yii\bootstrap\BootstrapAsset]] に依存しています。
以下、[[yii\web\AssetBundle]] のプロパティに関して、更に詳細に説明します。

* [[yii\web\AssetBundle::sourcePath|sourcePath]]: このバンドルのアセットファイルを含むルートディレクトリを指定します。
  ルートディレクトリがウェブからアクセス可能でない場合はこのプロパティをセットしなければなりません。ウェブからアクセス可能な場合は、
  かわりに [[yii\web\AssetBundle::basePath|basePath]] と [[yii\web\AssetBundle::baseUrl|baseUrl]] のプロパティをセットすべきです。
  [パスエイリアス](concept-aliases.md) をここで使うことが出来ます。
* [[yii\web\AssetBundle::basePath|basePath]]: このバンドルのアセットファイルを含むウェブからアクセス可能なディレクトリを指定します。
  [[yii\web\AssetBundle::sourcePath|sourcePath]] プロパティをセットした場合は、[アセットマネージャ](#asset-manager) がバンドルに
  含まれるファイルをウェブからアクセス可能なディレクトリに発行して、その結果に応じてこのプロパティを上書きします。
  アセットファイルが既にウェブからアクセス可能なディレクトリにあり、アセットの発行が必要でない場合に、このプロパティをセットすべきです。
  [パスエイリアス](concept-aliases.md) をここで使うことが出来ます。
* [[yii\web\AssetBundle::baseUrl|baseUrl]]: [[yii\web\AssetBundle::basePath|basePath]] ディレクトリに対応する URL を指定します。
  [[yii\web\AssetBundle::basePath|basePath]] と同じように、[[yii\web\AssetBundle::sourcePath|sourcePath]] プロパティをセットした場合は、
  [アセットマネージャ](#asset-manager) がアセットを発行して、その結果に応じてこのプロパティを上書きします。
  [パスエイリアス](concept-aliases.md) をここで使うことが出来ます。
* [[yii\web\AssetBundle::js|js]]: このバンドルに含まれる JavaScript ファイルをリストする配列です。ディレクトリの区切りとして
  フォワードスラッシュ "/" だけを使うべきことに注意してください。それぞれの JavaScript ファイルは、以下の二つの形式のどちらかによって
  指定することが出来ます。
  - ローカルの JavaScript ファイルを表す相対パス (例えば `js/main.js`)。実際のファイルのパスは、この相対パスの前に
    [[yii\web\AssetManager::basePath]] を付けることによって決定されます。また、実際の URL は、この相対パスの前に
    [[yii\web\AssetManager::baseUrl]] を付けることによって決定されます。
  - 外部の JavaScript ファイルを表す絶対 URL。例えば、
    `http://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` や
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` など。
* [[yii\web\AssetBundle::css|css]]: このバンドルに含まれる CSS ファイルをリストする配列です。この配列の形式は、
  [[yii\web\AssetBundle::js|js]] の形式と同じです。
* [[yii\web\AssetBundle::depends|depends]]: このバンドルが依存しているアセットバンドルの名前をリストする配列です
   (バンドルの依存関係については、すぐ後で説明します)。
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: [[yii\web\View::registerJsFile()]] メソッドに渡されるオプションを指定します。
  このバンドルにある *全て* の JavaScript ファイルについて、それを登録するときに、このメソッドが指定されたオプションとともに呼ばれます。
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: [[yii\web\View::registerCssFile()]] メソッドに渡されるオプションを指定します。
  このバンドルにある *全て* の CSS ファイルについて、それを登録するときに、このメソッドが指定されたオプションとともに呼ばれます。
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: [[yii\web\AssetManager::publish()]] メソッドに渡されるオプションを指定します。
  ソースのアセットファイルをウェブディレクトリに発行するときに、このメソッドが指定されたオプションとともに呼ばれます。
  これは [[yii\web\AssetBundle::sourcePath|sourcePath]] プロパティを指定した場合にだけ使用されます。


### アセットの配置場所 <a name="asset-locations"></a>

アセットは、配置場所を基準にして、次のように分類することが出来ます:

* ソースアセット: アセットファイルは、ウェブ経由で直接にアクセスすることが出来ない PHP ソースコードと一緒に配置されています。
  ページの中でソースアセットを使用するためには、ウェブディレクトリにコピーして、いわゆる発行されたアセットに変換しなければなりません。
  このプロセスは、すぐ後で詳しく説明しますが、*アセット発行* と呼ばれます。
* 発行されたアセット: アセットファイルはウェブディレクトリに配置されており、したがってウェブ経由で直接にアクセスすることが出来ます。
* 外部アセット: アセットファイルは、あなたのウェブアプリケーションをホストしているのとは別のウェブサーバ上に配置されています。

アセットバンドルクラスを定義するときに、[[yii\web\AssetBundle::sourcePath|sourcePath]] プロパティを指定した場合は、
相対パスを使ってリストに挙げられたアセットは全てソースアセットであると見なされます。このプロパティを指定しなかった場合は、
アセットは発行されたアセットであることになります (したがって、[[yii\web\AssetBundle::basePath|basePath]] と
[[yii\web\AssetBundle::baseUrl|baseUrl]] を指定して、アセットがどこに配置されているかを Yii に知らせなければなりません)。

アプリケーションに属するアセットは、不要なアセット発行プロセスを避けるために、ウェブディレクトリに置くことが推奨されます。
前述の例において `AppAsset` が [[yii\web\AssetBundle::sourcePath|sourcePath]] ではなく [[yii\web\AssetBundle::basePath|basePath]]
を指定しているのは、これが理由です。

[エクステンション](structure-extensions.md) の場合は、アセットがソースコードと一緒にウェブからアクセス出来ないディレクトリに
配置されているため、アセットバンドルクラスを定義するときには [[yii\web\AssetBundle::sourcePath|sourcePath]] プロパティを
指定しなければなりません。

> Note|注意: `@webroot/assets` を [[yii\web\AssetBundle::sourcePath|ソースパス]] として使ってはいけません。
  このディレクトリは、既定では、[[yii\web\AssetManager|アセットマネージャ]] がソースの配置場所から発行されたアセットファイルを
  保存する場所として使われます。このディレクトリの中のファイルはすべて一時的なものと見なされており、削除されることがあります。


### アセットの依存関係 <a name="asset-dependencies"></a>

ウェブページに複数の CSS や JavaScript ファイルをインクルードするときは、オーバーライドの問題を避けるために、
一定の順序に従わなければなりません。例えば、ウェブページで jQuery UI ウィジェットを使おうとするときは、jQuery JavaScript
ファイルが jQuery UI JavaScript ファイルより前にインクルードされることを保証しなければなりません。
このような順序付けをアセット間の依存関係と呼びます。

アセットの依存関係は、主として、[[yii\web\AssetBundle::depends]] プロパティによって指定されます。`AppAsset` の例では、
このアセットバンドルは他の二つのアセットバンドル、すなわち、[[yii\web\YiiAsset]] と [[yii\bootstrap\BootstrapAsset]] に依存しています。
このことは、`AppAsset` の CSS と JavaScript ファイルが、依存している二つのアセットバンドルにあるファイルの *後に*
インクルードされることを意味します。

アセットの依存関係は中継されます。つまり、バンドル A が B に依存し、B が C に依存していると、A は C にも依存していることになります。


### アセットのオプション <a name="asset-options"></a>

[[yii\web\AssetBundle::cssOptions|cssOptions]] および [[yii\web\AssetBundle::jsOptions|jsOptions]] のプロパティを指定して、
CSS と JavaScript ファイルがページにインクルードされる方法をカスタマイズすることが出来ます。これらのプロパティの値は、
[ビュー](structure-views.md) が CSS と JavaScript ファイルをインクルードするために、[[yii\web\View::registerCssFile()]] および
[[yii\web\View::registerJsFile()]] メソッドを呼ぶときに、それぞれ、オプションとして引き渡されます。

> Note|注意: バンドルクラスでセットしたオプションは、バンドルの中の *全て* の CSS/JavaScript ファイルに適用されます。
  いろいろなファイルに別々のオプションを使用したい場合は、別々のアセットバンドルを作成して、個々のバンドルの中では、
  一組のオプションを使うようにしなければなりません。

例えば、IE9 以上のブラウザに対して CSS ファイルを条件的にインクルードするために、次のオプションを使うことが出来ます:

```php
public $cssOptions = ['condition' => 'lte IE9'];
```

こうすると、バンドルの中の CSS ファイルは下記の HTML タグを使ってインクルードされるようになります:

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

リンクタグを `<noscript>` で包むためには、次のコードが使用できます:

```php
public $cssOptions = ['noscript' => true];
```

JavaScript ファイルをページの head セクションにインクルードするためには、次のオプションを使います
(既定では、JavaScript ファイルは body セクションの最後にインクルードされます)。

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```


### Bower と NPM のアセット <a name="bower-npm-assets"></a>

ほとんどの JavaScript/CSS パッケージは、[Bower](http://bower.io/) および/または [NPM](https://www.npmjs.org/) によって管理されています。
あなたのアプリケーションやエクステンションがそのようなパッケージを使っている場合は、以下のステップに従って
ライブラリの中のアセットを管理することが推奨されます。

1. アプリケーションまたはエクステンションの `composer.json` ファイルを修正して、パッケージを `require` のエントリに入れます。
   ライブラリを参照するのに、`bower-asset/PackageName` (Bower パッケージ) または `npm-asset/PackageName` (NPM パッケージ)
   を使わなければなりません。
2. アセットバンドルクラスを作成して、アプリケーションまたはエクステンションで使う予定の JavaScript/CSS ファイルをリストに挙げます。
   [[yii\web\AssetBundle::sourcePath|sourcePath]] プロパティは、`@bower/PackageName` または `@npm/PackageName` としなければなりません。
   これは、Composer が Bower または NPM パッケージを、このエイリアスに対応するディレクトリにインストールするためです。

> Note|注意: パッケージの中には、全ての配布ファイルをサブディレクトリに置くものがあります。その場合には、そのサブディレクトリを
  [[yii\web\AssetBundle::sourcePath|sourcePath]] の値として指定しなければなりません。例えば、[[yii\web\JqueryAsset]] は
  `@bower/jquery` ではなく `@bower/jquery/dist` を使います。


## アセットバンドルを使う <a name="using-asset-bundles"></a>

アセットバンドルを使うためには、[[yii\web\AssetBundle::register()]] メソッドを呼んでアセットバンドルを [ビュー](structure-views.md)
に登録します。例えば、次のようにしてビューテンプレートの中でアセットバンドルを登録することが出来ます:

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this はビューオブジェクトを表す
```

> Info|情報: [[yii\web\AssetBundle::register()]] メソッドは、[[yii\web\AssetBundle::basePath|basePath]] や
  [[yii\web\AssetBundle::baseUrl|baseUrl]] など、発行されたアセットに関する情報を含むアセットバンドルオブジェクトを返します。

他の場所でアセットバンドルを登録しようとするときは、必要とされるビューオブジェクトを提供しなければなりません。例えば、
[ウィジェット](structure-widgets.md) クラスの中でアセットバンドルを登録するためには、`$this->view` によってビューオブジェクトを
取得することが出来ます。

アセットバンドルがビューに登録されるとき、舞台裏では、Yii が依存している全てのアセットバンドルを登録します。
そして、アセットバンドルがウェブからはアクセス出来ないディレクトリに配置されている場合は、アセットバンドルはウェブディレクトリに発行されます。
その後、ビューがページをレンダリングするときに、登録されたバンドルのリストに挙げられている CSS と JavaScript ファイルのための
`<link>` タグと `<script>` タグが生成されます。これらのタグの順序は、登録されたバンドル間の依存関係、および、
[[yii\web\AssetBundle::css]] と [[yii\web\AssetBundle::js] のプロパティのリストに挙げられたアセットの順序によって決定されます。


### アセットバンドルをカスタマイズする <a name="customizing-asset-bundles"></a>

Yii は、[[yii\web\AssetManager]] によって実装されている `assetManager` という名前のアプリケーションコンポーネントを通じて
アセットバンドルを管理します。[[yii\web\AssetManager::bundles]] プロパティを構成することによって、アセットバンドルの振る舞いを
カスタマイズすることが出来ます。例えば、デフォルトの [[yii\web\JqueryAsset]] アセットバンドルはインストールされた jQuery の
Bower パッケージにある `jquery.js` ファイルを使用します。あなたは、可用性とパフォーマンスを向上させるために、
Google によってホストされたバージョンを使いたいと思うかも知れません。次のように、アプリケーションのコンフィギュレーションで
`assetManager` を構成することによって、それが達成できます。

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // バンドルを発行しない
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
    ],
];
```

複数のアセットバンドルも同様に [[yii\web\AssetManager::bundles]] によって構成することが出来ます。配列のキーは、
アセットバンドルのクラス名 (最初のバックスラッシュを除く) とし、配列の値は、
対応する [コンフィギュレーション配列](concept-configurations.md) とします。

> Tip|ヒント: アセットバンドルの中で使うアセットを条件的に選択することが出来ます。次の例は、開発環境では
> `jquery.js` を使い、そうでなければ `jquery.min.js` を使う方法を示すものです:
>
> ```php
> 'yii\web\JqueryAsset' => [
>     'js' => [
>         YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
>     ]
> ],
> ```

無効にしたいアセットバンドルの名前に `false` を結びつけることによって、一つまたは複数のアセットバンドルを無効にすることが出来ます。
無効にされたアセットバンドルをビューに登録した場合は、依存するバンドルは一つも登録されません。また、ビューがページを
レンダリングするときも、バンドルの中のアセットは一つもインクルードされません。例えば、[[yii\web\JqueryAsset]] を無効化するためには、
次のコンフィギュレーションを使用することが出来ます:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => false,
            ],
        ],
    ],
];
```
[[yii\web\AssetManager::bundles]] を `false` にセットすることによって、*全て* のバンドルを無効にすることも出来ます。


### アセットマッピング <a name="asset-mapping"></a>

時として、複数のアセットバンドルで使われている 正しくない/互換でない アセットファイルパスを「修正」したい場合があります。
例えば、バンドル A がバージョン 1.11.1 の `jquery.min.js` を使い、バンドル B がバージョン 2.1.1 の `jquery.js` を使っている
ような場合です。それぞれのバンドルをカスタマイズすることで問題を修正することも出来ますが、それよりも簡単な方法は、
*アセットマップ* 機能を使って、正しくないアセットを望ましいアセットに割り付けることです。そうするためには、以下のように
[[yii\web\AssetManager::assetMap]] プロパティを構成します:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'assetMap' => [
                'jquery.js' => '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ],
        ],
    ],
];
```

[[yii\web\AssetManager::assetMap|assetMap]] のキーは修正したいアセットの名前であり、値は望ましいアセットのパスです。
アセットバンドルをビューに登録するとき、[[yii\web\AssetBundle::css|css]] と [[yii\web\AssetBundle::js|js]] の配列に含まれる
すべてのアセットファイルの相対パスがこのマップと突き合わせて調べられます。キーのどれかがアセットファイルのパス (利用できる場合は、
[[yii\web\AssetBundle::sourcePath]] が前置されます) の最後の部分と一致した場合は、対応する値によってアセットが置き換えられ、
ビューに登録されます。例えば、`my/path/to/jquery.js` というアセットファイルは `jquery.js` というキーにマッチします。

> Note|注意: 相対パスを使って指定されたアセットだけがアセットマッピングの対象になります。そして、置き換える側のアセットのパスは
  絶対 URL であるか、[[yii\web\AssetManager::basePath]] からの相対パスであるかの、どちらかでなければなりません。


### アセット発行 <a name="asset-publishing"></a>

既に述べたように、アセットバンドルがウェブからアクセス出来ないディレクトリに配置されている場合は、バンドルがビューに登録されるときに、
アセットがウェブディレクトリにコピーされます。このプロセスは *アセット発行* と呼ばれ、[[yii\web\AssetManager|アセットマネージャ]]
によって自動的に実行されます。

既定では、アセットが発行されるディレクトリは `@webroot/assets` であり、`@web/assets` という URL に対応するものです。
この場所は、[[yii\web\AssetManager::basePath|basePath]] と [[yii\web\AssetManager::baseUrl|baseUrl]] のプロパティを構成して
カスタマイズすることが出来ます。

ファイルをコピーすることでアセットを発行する代りに、OS とウェブサーバが許容するなら、シンボリックリンクを使うことを考慮しても良いでしょう。
この機能は [[yii\web\AssetManager::linkAssets|linkAssets]] を true にセットすることで有効にすることが出来ます:

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
        ],
    ],
];
```

上記のコンフィギュレーションによって、アセットマネージャはアセットバンドルを発行するときにソースパスへのシンボリックリンクを
作成するようになります。この方がファイルのコピーより速く、また、発行されたアセットが常に最新であることを保証することも出来ます。


## よく使われるアセットバンドル <a name="common-asset-bundles"></a>

コアの Yii コードは多くのアセットバンドルを定義しています。その中で、下記のバンドルはよく使われるものであり、あなたの
アプリケーションやエクステンションのコードでも参照することが出来るものです。

- [[yii\web\YiiAsset]]: 主として `yii.js` ファイルをインクルードするためのバンドルです。このファイルはモジュール化された
  JavaScript のコードを組織化するメカニズムを実装しています。また、`data-method` と `data-confirm` の属性に対する特別な
  サポートや、その他の有用な機能を提供します。
- [[yii\web\JqueryAsset]]: jQuery の bower パッケージから `jquery.js` ファイルをインクルードします。
- [[yii\bootstrap\BootstrapAsset]]: Twitter Bootstrap フレームワークから CSS ファイルをインクルードします。
- [[yii\bootstrap\BootstrapPluginAsset]]: Bootstrap JavaScript プラグインをサポートするために、Twitter Bootstrap
  フレームワークから JavaScript ファイルをインクルードします。
- [[yii\jui\JuiAsset]]: jQuery UI ライブラリから CSS と JavaScript のファイルをインクルードします。

あなたのコードが、jQuery や jQuery UI または Bootstrap に依存する場合は、自分自身のバージョンを作るのではなく、これらの
定義済みのアセットバンドルを使用すべきです。これらのバンドルのデフォルトの設定があなたの必要を満たさない時は、
[アセットバンドルをカスタマイズする](#customizing-asset-bundles) の項で説明したように、それをカスタマイズすることが出来ます。


## アセット変換 <a name="asset-conversion"></a>

直接に CSS および/または JavaScript のコードを書く代りに、何らかの拡張構文を使って書いたものを特別なツールを使って
CSS/JavaScript に変換する、ということを開発者はしばしば行います。例えば、CSS コードのためには、[LESS](http://lesscss.org/) や
[SCSS](http://sass-lang.com/) を使うことが出来ます。また、JavaScript のためには、[TypeScript](http://www.typescriptlang.org/)
を使うことが出来ます。

拡張構文を使ったアセットファイルをアセットバンドルの中の [[yii\web\AssetBundle::css|css]] と [[yii\web\AssetBundle::js|js]]
のリストに挙げることが出来ます。例えば、

```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.less',
    ];
    public $js = [
        'js/site.ts',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

このようなアセットバンドルをビューに登録すると、[[yii\web\AssetManager|アセットマネージャ]] が自動的にプリプロセッサツールを
走らせて、認識できた拡張構文のアセットを CSS/JavaScript に変換します。最終的にビューがページをレンダリングするときには、
ビューは元の拡張構文のアセットではなく、変換後の CSS/JavaScript ファイルをページにインクルードします。

Yii はファイル名の拡張子を使って、アセットが使っている拡張構文を識別します。デフォルトでは、下記の構文とファイル名拡張子を認識します。

- [LESS](http://lesscss.org/): `.less`
- [SCSS](http://sass-lang.com/): `.scss`
- [Stylus](http://learnboost.github.io/stylus/): `.styl`
- [CoffeeScript](http://coffeescript.org/): `.coffee`
- [TypeScript](http://www.typescriptlang.org/): `.ts`

Yii はインストールされたプリプロセッサツールに頼ってアセットを変換します。例えば、[LESS](http://lesscss.org/) を使うためには、
`lessc` プリプロセッサコマンドをインストールしなければなりません。

下記のように [[yii\web\AssetManager::converter]] を構成することで、プリプロセッサコマンドとサポートされる拡張構文を
カスタマイズすることが出来ます:

```php
return [
    'components' => [
        'assetManager' => [
            'converter' => [
                'class' => 'yii\web\AssetConverter',
                'commands' => [
                    'less' => ['css', 'lessc {from} {to} --no-color'],
                    'ts' => ['js', 'tsc --out {to} {from}'],
                ],
            ],
        ],
    ],
];
```

上記においては、サポートされる拡張構文が [[yii\web\AssetConverter::commands]] プロパティによって定義されています。
配列のキーはファイルの拡張子 (先頭のドットは省く) であり、配列の値は結果として作られるアセットファイルの拡張子と
アセット変換を実行するためのコマンドです。コマンドの中の `{from}` と `{to}` のトークンは、ソースのアセットファイルのパスと
ターゲットのアセットファイルのパスに置き換えられます。

> Info|情報: 上記で説明した方法の他にも、拡張構文のアセットを扱う方法はあります。例えば、[grunt](http://gruntjs.com/)
  のようなビルドツールを使って、拡張構文のアセットをモニターし、自動的に変換することが出来ます。この場合は、元のファイルではなく、
  結果として作られる CSS/JavaScript ファイルをアセットバンドルのリストに挙げなければなりません。


## アセットを結合して圧縮する <a name="combining-compressing-assets"></a>

ウェブページは数多くの CSS および/または JavaScript ファイルをインクルードすることがあり得ます。HTTP リクエストの数と
これらのファイルの全体としてのダウンロードサイズを削減するためによく用いられる方法は、複数の CSS/JavaScript ファイルを
結合して圧縮し、一つまたはごく少数のファイルにまとめることです。そして、ウェブページでは元のファイルをインクルードする
代りに、圧縮されたファイルをインクルードする訳です。
 
> Info|情報: アセットの結合と圧縮は、通常はアプリケーションが実運用モードにある場合に必要になります。開発モードにおいては、
  たいていは元の CSS/JavaScript ファイルを使う方がデバッグのために好都合です。

In the following, we introduce an approach to combine and compress asset files without the need of modifying
your existing application code.

1. Find out all asset bundles in your application that you plan to combine and compress.
2. Divide these bundles into one or a few groups. Note that each bundle can only belong to a single group.
3. Combine/compress the CSS files in each group into a single file. Do this similarly for the JavaScript files.
4. Define a new asset bundle for each group:
   * Set the [[yii\web\AssetBundle::css|css]] and [[yii\web\AssetBundle::js|js]] properties to be
     the combined CSS and JavaScript files, respectively.
   * Customize the asset bundles in each group by setting their [[yii\web\AssetBundle::css|css]] and 
     [[yii\web\AssetBundle::js|js]] properties to be empty, and setting their [[yii\web\AssetBundle::depends|depends]]
     property to be the new asset bundle created for the group.

Using this approach, when you register an asset bundle in a view, it causes the automatic registration of
the new asset bundle for the group that the original bundle belongs to. And as a result, the combined/compressed 
asset files are included in the page, instead of the original ones.


### An Example <a name="example"></a>

Let's use an example to further explain the above approach. 

Assume your application has two pages X and Y. Page X uses asset bundle A, B and C, while Page Y uses asset bundle B, C and D. 

You have two ways to divide these asset bundles. One is to use a single group to include all asset bundles, the
other is to put (A, B, C) in Group X, and (B, C, D) in Group Y. Which one is better? It depends. The first way
has the advantage that both pages share the same combined CSS and JavaScript files, which makes HTTP caching
more effective. On the other hand, because the single group contains all bundles, the size of the combined CSS and 
JavaScript files will be bigger and thus increase the initial file transmission time. In this example, we will use 
the first way, i.e., use a single group to contain all bundles.

> Info: Dividing asset bundles into groups is not trivial task. It usually requires analysis about the real world
  traffic data of various assets on different pages. At the beginning, you may start with a single group for simplicity. 

Use existing tools (e.g. [Closure Compiler](https://developers.google.com/closure/compiler/), 
[YUI Compressor](https://github.com/yui/yuicompressor/)) to combine and compress CSS and JavaScript files in 
all the bundles. Note that the files should be combined in the order that satisfies the dependencies among the bundles. 
For example, if Bundle A depends on B which depends on both C and D, then you should list the asset files starting 
from C and D, followed by B and finally A. 

After combining and compressing, we get one CSS file and one JavaScript file. Assume they are named as 
`all-xyz.css` and `all-xyz.js`, where `xyz` stands for a timestamp or a hash that is used to make the file name unique
to avoid HTTP caching problem.
 
We are at the last step now. Configure the [[yii\web\AssetManager|asset manager]] as follows in the application
configuration:

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => [
                'all' => [
                    'class' => 'yii\web\AssetBundle',
                    'basePath' => '@webroot/assets',
                    'baseUrl' => '@web/assets',
                    'css' => ['all-xyz.css'],
                    'js' => ['all-xyz.js'],
                ],
                'A' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'B' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'C' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'D' => ['css' => [], 'js' => [], 'depends' => ['all']],
            ],
        ],
    ],
];
```

As explained in the [Customizing Asset Bundles](#customizing-asset-bundles) subsection, the above configuration
changes the default behavior of each bundle. In particular, Bundle A, B, C and D no longer have any asset files.
They now all depend on the `all` bundle which contains the combined `all-xyz.css` and `all-xyz.js` files.
Consequently, for Page X, instead of including the original source files from Bundle A, B and C, only these
two combined files will be included; the same thing happens to Page Y.

There is one final trick to make the above approach work more smoothly. Instead of directly modifying the
application configuration file, you may put the bundle customization array in a separate file and conditionally
include this file in the application configuration. For example,

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require(__DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php')),  
        ],
    ],
];
```

That is, the asset bundle configuration array is saved in `assets-prod.php` for production mode, and
`assets-dev.php` for non-production mode.


### Using the `asset` Command <a name="using-asset-command"></a>

Yii provides a console command named `asset` to automate the approach that we just described.

To use this command, you should first create a configuration file to describe what asset bundles should
be combined and how they should be grouped. You can use the `asset/template` sub-command to generate
a template first and then modify it to fit for your needs.

```
yii asset/template assets.php
```

The command generates a file named `assets.php` in the current directory. The content of this file looks like the following:

```php
<?php
/**
 * Configuration file for the "yii asset" console command.
 * Note that in the console environment, some path aliases like '@webroot' and '@web' may not exist.
 * Please define these missing path aliases.
 */
return [
    // Adjust command/callback for JavaScript files compressing:
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // Adjust command/callback for CSS files compressing:
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // The list of asset bundles to compress:
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Asset bundle for compression output:
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Asset manager configuration:
    'assetManager' => [
    ],
];
```

You should modify this file and specify which bundles you plan to combine in the `bundles` option. In the `targets` 
option you should specify how the bundles should be divided into groups. You can specify one or multiple groups, 
as aforementioned.

> Note: Because the alias `@webroot` and `@web` are not available in the console application, you should
  explicitly define them in the configuration.

JavaScript files are combined, compressed and written to `js/all-{hash}.js` where {hash} is replaced with the hash of
the resulting file.

The `jsCompressor` and `cssCompressor` options specify the console commands or PHP callbacks for performing
JavaScript and CSS combining/compressing. By default Yii uses [Closure Compiler](https://developers.google.com/closure/compiler/) 
for combining JavaScript files and [YUI Compressor](https://github.com/yui/yuicompressor/) for combining CSS files. 
You should install tools manually or adjust these options to use your favorite tools.


With the configuration file, you can run the `asset` command to combine and compress the asset files
and then generate a new asset bundle configuration file `assets-prod.php`:
 
```
yii asset assets.php config/assets-prod.php
```

The generated configuration file can be included in the application configuration, like described in
the last subsection.


> Info: Using the `asset` command is not the only option to automate the asset combining and compressing process.
  You can use the excellent task runner tool [grunt](http://gruntjs.com/) to achieve the same goal.
