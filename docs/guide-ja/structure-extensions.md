エクステンション
================

エクステンションは、Yii のアプリケーションで使われることに限定して設計され、そのまますぐに使える機能を提供する再配布可能なソフトウェア・パッケージです。
例えば、[yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug) エクステンションは、あなたのアプリケーションにおいて、全てのページの末尾に便利なデバッグ・ツールバーを追加して、
ページが生成される過程をより容易に把握できるように手助けしてくれます。
エクステンションを使うと、あなたの開発プロセスを加速することが出来ます。
また、あなたのコードをエクステンションとしてパッケージ化すると、あなたの優れた仕事を他の人たちと共有することが出来ます。

> Info: 「エクステンション」という用語は Yii に限定されたソフトウェア・パッケージを指すものとして使用します。
  Yii がなくても使用できる汎用のソフトウェア・パッケージを指すためには、「パッケージ」または「ライブラリ」という用語を使うことにします。


## エクステンションを使う <span id="using-extensions"></span>

エクステンションを使うためには、先ずはそれをインストールする必要があります。
ほとんどのエクステンションは [Composer](https://getcomposer.org/) のパッケージとして配布されていて、次の二つの簡単なステップをふめばインストールすることが出来ます。

1. アプリケーションの `composer.json` ファイルを修正して、どのエクステンション (Composer パッケージ) をインストールしたいかを指定する。
2. `composer install` コマンドを走らせて指定したエクステンションをインストールする。

[Composer](https://getcomposer.org/) を持っていない場合は、それをインストールする必要があることに注意してください。

デフォルトでは、Composer はオープン・ソース Composer パッケージの最大のレポジトリである [Packagist](https://packagist.org/) に登録されたパッケージをインストールします。
エクステンションは Packagist で探すことが出来ます。
また、[自分自身のレポジトリを作成](https://getcomposer.org/doc/05-repositories.md#repository) して、それを使うように Composer を構成することも出来ます。
これは、あなたがプライベートなエクステンションを開発していて、それを自分のプロジェクト間でのみ共有したい場合に役に立つ方法です。

Composer によってインストールされるエクステンションは `BasePath/vendor` ディレクトリに保存されます。
ここで `BasePath` は、アプリケーションの [ベース・パス](structure-applications.md#basePath) を指します。
Composer は依存関係を管理するものですから、あるパッケージをインストールするときには、それが依存する全てのパッケージも同時にインストールします。

例えば、`yiisoft/yii2-imagine` エクステンションをインストールするためには、あなたの `composer.json` を次のように修正します。

```json
{
    // ...

    "require": {
        // ... 他の依存パッケージ

        "yiisoft/yii2-imagine": "*"
    }
}
```

インストール完了後には、`BasePath/vendor` の下に `yiisoft/yii2-imagine` ディレクトリが作られている筈です。
それと同時に、`imagine/imagine` という別のディレクトリも作られて、依存するパッケージがそこにインストールされている筈です。

> Info: `yiisoft/yii2-imagine` は Yii 開発チームによって開発され保守されるコア・エクステンションの一つです。
  全てのコア・エクステンションは [Packagist](https://packagist.org/) でホストされ、`yiisoft/yii2-xyz` のように名付けられます。
  ここで `xyz` はエクステンションによってさまざまに変ります。

これであなたはインストールされたエクステンションをあなたのアプリケーションの一部であるかのように使うことが出来ます。
次の例は、`yiisoft/yii2-imagine` エクステンションによって提供される `yii\imagine\Image` クラスをどのようにして使うことが出来るかを示すものです。

```php
use Yii;
use yii\imagine\Image;

// サムネール画像を生成する
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> Info: エクステンションのクラスは [Yii クラス・オートローダ](concept-autoloading.md) によってオートロードされます。


### エクステンションを手作業でインストールする <span id="installing-extensions-manually"></span>

あまり無いことですが、いくつかまたは全てのエクステンションを Composer に頼らずに手作業でインストールしたい場合があるかもしれません。
そうするためには、次のようにしなければなりません。

1. エクステンションのアーカイブ・ファイルをダウンロードして、`vendor` ディレクトリに解凍する。
2. もし有れば、エクステンションによって提供されているクラス・オートローダをインストールする。
3. 指示に従って、依存するエクステンションを全てダウンロードしインストールする。

エクステンションがクラス・オートローダを持っていなくても、[PSR-4 標準](https://www.php-fig.org/psr/psr-4/) に従っている場合は、Yii によって提供されているクラス・オートローダを使ってエクステンションのクラスをオートロードすることが出来ます。
必要なことは、エクステンションのルート・ディレクトリのための [ルート・エイリアス](concept-aliases.md#defining-aliases) を宣言することだけです。
例えば、エクステンションを `vendor/mycompany/myext` というディレクトリにインストールしたと仮定します。
そして、エクステンションのクラスは `myext` 名前空間の下にあるとします。
その場合、アプリケーションの構成情報に下記のコードを含めます。

```php
[
    'aliases' => [
        '@myext' => '@vendor/mycompany/myext',
    ],
]
```


## エクステンションを作成する <span id="creating-extensions"></span>

あなたの優れたコードを他の人々と共有する必要があると感じたときは、エクステンションを作成することを考慮するのが良いでしょう。
エクステンションは、ヘルパ・クラス、ウィジェット、モジュールなど、どのようなコードでも含むことが出来ます。

エクステンションは、[Composer パッケージ](https://getcomposer.org/) の形式で作成することが推奨されます。
そうすれば、直前の項で説明したように、いっそう容易に他のユーザによってインストールされ、使用されることが出来ます。

以下は、エクステンションを Composer のパッケージとして作成するために踏む基本的なステップです。

1. エクステンションのためのプロジェクトを作成して、[github.com](https://github.com) などの VCS レポジトリ上でホストします。
   エクステンションに関する開発と保守の作業はこのレポジトリ上でしなければなりません。
2. プロジェクトのルート・ディレクトリに、Composer によって要求される `composer.json` という名前のファイルを作成します。
   詳細については、次の項を参照してください。
3. エクステンションを [Packagist](https://packagist.org/) などの Composer レポジトリに登録します。
   そうすると、他のユーザがエクステンションを見つけて Composer を使ってインストールすることが出来るようになります。


### `composer.json` <span id="composer-json"></span>

全ての Composer パッケージは、ルート・ディレクトリに `composer.json` というファイルを持たなければなりません。このファイルはパッケージに関するメタデータを含むものです。
このファイルに関する完全な仕様は [Composer Manual](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup) に記載されています。
次の例は、`yiisoft/yii2-imagine` エクステンションのための `composer.json` ファイルを示すものです。

```json
{
    // パッケージ名
    "name": "yiisoft/yii2-imagine",

    // パッケージタイプ
    "type": "yii2-extension",

    "description": "The Imagine integration for the Yii framework",
    "keywords": ["yii2", "imagine", "image", "helper"],
    "license": "BSD-3-Clause",
    "support": {
        "issues": "https://github.com/yiisoft/yii2/issues?labels=ext%3Aimagine",
        "forum": "https://forum.yiiframework.com/",
        "wiki": "https://www.yiiframework.com/wiki/",
        "irc": "ircs://irc.libera.chat:6697/yii",
        "source": "https://github.com/yiisoft/yii2"
    },
    "authors": [
        {
            "name": "Antonio Ramirez",
            "email": "amigo.cobos@gmail.com"
        }
    ],

    // 依存パッケージ
    "require": {
        "yiisoft/yii2": "~2.0.0",
        "imagine/imagine": "v0.5.0"
    },

    // クラスのオートロードの仕様
    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```


#### パッケージ名 <span id="package-name"></span>

全ての Composer パッケージは、他の全てパッケージと異なる一意に特定できる名前を持たなければなりません。
パッケージ名の形式は `vendorName/projectName` です。
例えば、`yiisoft/yii2-imagine` というパッケージ名の中では、ベンダー名とプロジェクト名は、それぞれ、`yiisoft` と `yii2-imagine` です。

ベンダー名として `yiisoft` を使ってはいけません。これは Yii のコア・コードに使うために予約されています。

プロジェクト名には、Yii 2 エクステンションを表す `yii2-` を前置することを推奨します。例えば、`myname/yii2-mywidget` です。
このようにすると、ユーザはパッケージが Yii 2 エクステンションであることをより容易に知ることが出来ます。


#### パッケージ・タイプ <span id="package-type"></span>

パッケージがインストールされたときに Yii のエクステンションとして認識されるように、エクステンションのパッケージ・タイプを `yii2-extension` と指定することは重要なことです。

ユーザが `composer install` を走らせてエクステンションをインストールすると、
`vendor/yiisoft/extensions.php` というファイルが自動的に更新されて、新しいエクステンションに関する情報を含むようになります。
Yii のアプリケーションは、このファイルによって、どんなエクステンションがインストールされているかを知ることが出来ます
(その情報には、[[yii\base\Application::extensions]] を通じてアクセスすることが出来ます)。


#### 依存パッケージ <span id="dependencies"></span>

あなたのエクステンションは Yii に依存します (当然ですね)。ですから、`composer.json` の `require` エントリのリストにそれ (`yiisoft/yii2`) を挙げなければなりません。
あなたのエクステンションがその他のエクステンションやサード・パーティのライブラリに依存する場合は、それらもリストに挙げなければなりません。
それぞれの依存パッケージについて、適切なバージョン制約 (例えば `1.*` や `@stable`) を指定することも忘れてはなりません。
あなたのエクステンションを安定バージョンとしてリリースする場合は、安定した依存パッケージを使ってください。

たいていの JavaScript/CSS パッケージは、Composer ではなく、[Bower](https://bower.io/) および/または [NPM](https://www.npmjs.com/) を使って管理されています。
Yii は [Composer アセット・プラグイン](https://github.com/fxpio/composer-asset-plugin) を使って、この種のパッケージを Composer によって管理することを可能にしています。
あなたのエクステンションが Bower パッケージに依存している場合でも、次のように、
`composer.json` に依存パッケージをリストアップすることが簡単に出来ます。

```json
{
    // 依存パッケージ
    "require": {
        "bower-asset/jquery": ">=1.11.*"
    }
}
```

上記のコードは、エクステンションが `jquery` Bower パッケージに依存することを述べています。
一般に、`composer.json` の中で Bower パッケージを指すためには `bower-asset/PackageName` を使うことが出来ます。
そして、NPM パッケージを指すためには `npm-asset/PackageName` を使うことが出来ます。
Composer が Bower または NPM のパッケージをインストールする場合は、デフォルトでは、それぞれ、`@vendor/bower/PackageName` および `@vendor/npm/Packages` というディレクトリの下にパッケージの内容がインストールされます。
この二つのディレクトリは、`@bower/PackageName` および `@npm/PackageName` という短いエイリアスを使って参照することも可能です。

アセット管理に関する詳細については、[アセット](structure-assets.md#bower-npm-assets) のセクションを参照してください。


#### クラスのオートロード <span id="class-autoloading"></span>

エクステンションのクラスが Yii のクラス・オートローダまたは Composer のクラス・オートローダによってオートロードされるように、
下記に示すように、`composer.json` ファイルの `autoload` エントリを指定しなければなりません。

```json
{
    // ....

    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```

一つまたは複数のルート名前空間と、それに対応するファイル・パスをリストに挙げることが出来ます。

エクステンションがアプリケーションにインストールされると、Yii は列挙されたルート名前空間の一つ一つに対して、
名前空間に対応するディレクトリを指す [エイリアス](concept-aliases.md#extension-aliases) を作成します。
例えば、上記の `autoload` の宣言は、`@yii/imagine` という名前のエイリアスに対応することになります。


### 推奨されるプラクティス <span id="recommended-practices"></span>

エクステンションは他の人々によって使われることを意図したものですから、多くの場合、追加の開発努力が必要になります。
以下に、高品質のエクステンションを作成するときによく用いられ、また推奨されるプラクティスのいくつかを紹介します。


#### 名前空間 <span id="namespaces"></span>

名前の衝突を避けて、エクステンションの中のクラスをオートロード可能にするために、名前空間を使うべきであり、
エクステンションの中のクラスには [PSR-4 標準](https://www.php-fig.org/psr/psr-4/) または [PSR-0 標準](https://www.php-fig.org/psr/psr-0/)
に従った名前を付けるべきです。

あなたのクラスの名前空間は `vendorName\extensionName` で始まるべきです。
ここで `extensionName` は、`yii2-` という接頭辞を含むべきでないことを除けば、パッケージ名におけるプロジェクト名と同じものです。
例えば、`yiisoft/yii2-imagine` エクステンションでは、`yii\imagine` をエクステンションのクラスの名前空間として使っています。

`yii`、`yii2` または `yiisoft` をベンダー名として使ってはいけません。これらの名前は、Yii のコア・コードに使うために予約されています。


#### ブートストラップ・クラス <span id="bootstrapping-classes"></span>

場合によっては、アプリケーションが [ブートストラップ](runtime-bootstrapping.md) の段階にある間に、エクステンションに何らかのコードを実行させたい場合があるでしょう。
例えば、エクステンションをアプリケーションの `beginRequest` イベントに反応させて、何らかの環境設定を調整したいことがあります。
エクステンションのユーザに対して、エクステンションの中にあるイベント・ハンドラを `beginRequest`
イベントに明示的にアタッチするように指示することも出来ますが、より良い方法は、それを自動的に行うことです。

この目的を達するためには、[[yii\base\BootstrapInterface]] を実装する、いわゆる *ブートストラップ・クラス* を作成します。
例えば、

```php
namespace myname\mywidget;

use yii\base\BootstrapInterface;
use yii\base\Application;

class MyBootstrapClass implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
             // ここで何かをする
        });
    }
}
```

そして、次のように、このクラスを `composer.json` ファイルのリストに挙げます。

```json
{
    // ...

    "extra": {
        "bootstrap": "myname\\mywidget\\MyBootstrapClass"
    }
}
```

このエクステンションがアプリケーションにインストールされると、すべてのリクエストのブートストラップの過程において、
毎回、Yii が自動的にブートストラップ・クラスのインスタンスを作成し、
その [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] メソッドを呼びます。


#### データベースを扱う <span id="working-with-databases"></span>

あなたのエクステンションはデータベースにアクセスする必要があるかも知れません。エクステンションを使うアプリケーションが常に `Yii::$db` を DB 接続として使用すると仮定してはいけません。
その代りに、DB アクセスを必要とするクラスのために `db` プロパティを宣言すべきです。
このプロパティによって、エクステンションのユーザは、エクステンションにどの DB 接続を使わせるかをカスタマイズすることが出来るようになります。
その一例として、[[yii\caching\DbCache]] クラスを参照して、それがどのように `db` プロパティを宣言して使っているかを見ることが出来ます。

あなたのエクステンションが特定の DB テーブルを作成したり、DB スキーマを変更したりする必要がある場合は、次のようにするべきです。

- DB スキーマを操作するために、平文の SQL ファイルを使うのではなく、[マイグレーション](db-migrations.md) を提供する。
- マイグレーションがさまざまな DBMS に適用可能なものになるように試みる。
- マイグレーションの中では [アクティブ・レコード](db-active-record.md) の使用を避ける。


#### アセットを使う <span id="using-assets"></span>

あなたのエクステンションがウィジェットかモジュールである場合は、動作するために何らかの [アセット](structure-assets.md) が必要である可能性が高いでしょう。
例えば、モジュールは、画像、JavaScript、そして CSS を含むページを表示することがあるでしょう。
アプリケーションにインストールされるときに、エクステンションの全てのファイルは同じディレクトリの下に配置されますが、そのディレクトリはウェブからはアクセス出来ないものです。
そのため、次のどちらかの方法を使って、アセット・ファイルをウェブから直接アクセス出来るようにしなければなりません。

- アセット・ファイルをウェブからアクセス出来る特定のフォルダに手作業でコピーするように、エクステンションのユーザに要求する。
- [アセット・バンドル](structure-assets.md) を宣言し、アセット発行メカニズムに頼って、
アセット・バンドルにリストされているファイルをウェブからアクセス出来るフォルダに自動的にコピーする。

あなたのエクステンションが他の人々にとって一層使いやすいものになるように、第二の方法をとることを推奨します。
アセットの取り扱い一般に関する詳細は [アセット](structure-assets.md) のセクションを参照してください。


#### 国際化と地域化 <span id="i18n-l10n"></span>

あなたのエクステンションは、さまざまな言語をサポートするアプリケーションによって使われるかもしれません。
従って、あなたのエクステンションがエンド・ユーザにコンテントを表示するものである場合は、それを [国際化](tutorial-i18n.md) するように努めるべきです。具体的には、

- エクステンションがエンド・ユーザに向けたメッセージを表示する場合は、翻訳することが出来るようにメッセージを `Yii::t()` で囲むべきです。
  開発者に向けられたメッセージ (内部的な例外のメッセージなど)
  は翻訳される必要はありません。
- エクステンションが数値や日付などを表示する場合は、
  [[yii\i18n\Formatter]] を適切な書式化の規則とともに使って書式設定すべきです。

詳細については、[国際化](tutorial-i18n.md) のセクションを参照してください。


#### テスト <span id="testing"></span>

あなたは、あなたのエクステンションが他の人々に問題をもたらすことなく完璧に動作することを望むでしょう。
この目的を達するためには、あなたのエクステンションを公開する前にテストすべきです。

手作業のテストに頼るのではなく、あなたのエクステンションのコードをカバーするさまざまなテスト・ケースを作成することが推奨されます。
あなたのエクステンションの新しいバージョンを公開する前には、毎回、それらのテスト・ケースを走らせるだけで、全てが良い状態にあることを確認することが出来ます。
Yii はテストのサポートを提供しており、それよって、単体テスト、機能テスト、受入テストを書くことが一層簡単に出来るようになっています。
詳細については、[テスト](test-overview.md) のセクションを参照してください。


#### バージョン管理 <span id="versioning"></span>

エクステンションのリリースごとにバージョン番号 (例えば `1.0.1`) を付けるべきです。
どのようなバージョン番号を付けるべきかを決定するときは、[セマンティック・バージョニング](https://semver.org) のプラクティスに従うことを推奨します。


#### リリース(公開) <span id="releasing"></span>

他の人々にあなたのエクステンションを知ってもらうためには、それをリリース(公開)する必要があります。

エクステンションをリリースするのが初めての場合は、[Packagist](https://packagist.org/) などの Composer レポジトリにエクステンションを登録するべきです。
その後は、あなたがしなければならない仕事は、エクステンションの VCS レポジトリでリリース・タグ (例えば `v1.0.1`) を作成することと、
Composer レポジトリに新しいリリースについて通知するだけのことになります。
そうすれば、人々が新しいリリースを見出すことが出来るようになり、Composer レポジトリを通じてエクステンションをインストールしたりアップデートしたりするようになります。

エクステンションのリリースには、コード・ファイル以外に、人々があなたのエクステンションについて知ったり、
エクステンションを使ったりするのを助けるために、下記のものを含めることを考慮すべきです。

* パッケージのルート・ディレクトリに readme ファイル: あなたのエクステンションが何をするものか、
  そして、どのようにインストールして使うものかを説明するものです。
  [Markdown](https://daringfireball.net/projects/markdown/) 形式で書いて、`readme.md` という名前にすることを推奨します。
* パッケージのルート・ディレクトリに changelog ファイル: それぞれのリリースで何が変ったかを一覧表示するものです。
  このファイルは Markdown 形式で書いて `changelog.md` と名付けることが出来ます。
* パッケージのルート・ディレクトリに upgrade ファイル: エクステンションの古いリリースからのアップグレード方法について説明するものです。
  このファイルは Markdown 形式で書いて `upgrade.md` と名付けることが出来ます。
* チュートリアル、デモ、スクリーン・ショットなど: あなたのエクステンションが readme ファイルでは十分にカバーできないほど多くの機能を提供するものである場合は、
  これらが必要になります。
* API ドキュメント: あなたのコードは、他の人々が読んで理解することがより一層容易に出来るように、十分な解説を含むべきです。
  [BaseObject のクラス・ファイル](https://github.com/yiisoft/yii2/blob/master/framework/base/BaseObject.php) を参照すると、
  コードに解説を加える方法を学ぶことが出来ます。

> Info: コードのコメントを Markdown 形式で書くことが出来ます。
  `yiisoft/yii2-apidoc` エクステンションが、コードのコメントに基づいて綺麗な API ドキュメントを生成するツールを提供しています。

> Info: これは要求ではありませんが、あなたのエクステンションも一定のコーディング・スタイルを守るのが良いと思います。
  [コア・フレームワーク・コード・スタイル](https://github.com/yiisoft/yii2/blob/master/docs/internals/core-code-style.md) を参照してください。


## コア・エクステンション <span id="core-extensions"></span>

Yii は下記のコア・エクステンション (または ["公式エクステンション"](https://www.yiiframework.com/extensions/official)) を提供しています。
これらは Yii 開発チームによって開発され保守されているものです。
全て [Packagist](https://packagist.org/) に登録され、[エクステンションを使う](#using-extensions) の項で説明したように、簡単にインストールすることが出来ます。

- [yiisoft/yii2-apidoc](https://github.com/yiisoft/yii2-apidoc):
  拡張可能で高性能な API ドキュメント生成機能を提供します。
  コア・フレームワークの API ドキュメントを生成するためにも使われています。
- [yiisoft/yii2-authclient](https://github.com/yiisoft/yii2-authclient):
  Facebook OAuth2 クライアント、GitHub OAuth2 クライアントなど、よく使われる一連の auth クライアントを提供します。
- [yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2-bootstrap):
  [Bootstrap](https://getbootstrap.com/) のコンポーネントとプラグインをカプセル化した一連のウィジェットを提供します。
- [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug):
  Yii アプリケーションのデバッグのサポートを提供します。
  このエクステンションが使われると、全てのページの末尾にデバッガ・ツールバーが表示されます。
  このエクステンションは、より詳細なデバッグ情報を表示する一連のスタンドアロン・ページも提供します。
- [yiisoft/yii2-elasticsearch](https://github.com/yiisoft/yii2-elasticsearch):
  [Elasticsearch](https://www.elastic.co/) の使用に対するサポートを提供します。
  基本的なクエリ/サーチのサポートを含むだけでなく、Elasticsearch にアクティブ・レコードを保存することを可能にする
  [アクティブ・レコード](db-active-record.md) パターンをも実装しています。
- [yiisoft/yii2-faker](https://github.com/yiisoft/yii2-faker):
  ダミー・データを作る [Faker](https://github.com/fzaninotto/Faker) を使うためのサポートを提供します。
- [yiisoft/yii2-gii](https://github.com/yiisoft/yii2-gii):
  拡張性が非常に高いウェブ・ベースのコード・ジェネレータを提供します。
  これを使って、モデル、フォーム、モジュール、CRUD などを迅速に生成することが出来ます。
- [yiisoft/yii2-httpclient](https://github.com/yiisoft/yii2-httpclient):
  HTTP クライアントを提供します。
- [yiisoft/yii2-imagine](https://github.com/yiisoft/yii2-imagine):
  [Imagine](https://imagine.readthedocs.org/) に基づいて、使われることの多い画像操作機能を提供します。
- [yiisoft/yii2-jui](https://github.com/yiisoft/yii2-jui):
  [JQuery UI](https://jqueryui.com/) のインタラクションとウィジェットをカプセル化した一連のウィジェットを提供します。
- [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb):
  [MongoDB](https://www.mongodb.com/) の使用に対するサポートを提供します。
  基本的なクエリ、アクティブ・レコード、マイグレーション、キャッシュ、コード生成などの機能を含みます。
- [yiisoft/yii2-queue](https://www.yiiframework.com/extension/yiisoft/yii2-queue):
  キューによるタスクの非同期実行のサポートを提供します。
  データベース、Redis、RabbitMQ、AMQP、Beanstalk および Gearman によるキューをサポートしています。
- [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis):
  [redis](https://redis.io/) の使用に対するサポートを提供します。
  基本的なクエリ、アクティブ・レコード、キャッシュなどの機能を含みます。
- [yiisoft/yii2-shell](https://www.yiiframework.com/extension/yiisoft/yii2-shell):
  [psysh](https://psysh.org/) に基づくイタラクティブなシェルを提供します。
- [yiisoft/yii2-smarty](https://github.com/yiisoft/yii2-smarty):
  [Smarty](https://www.smarty.net/) に基づいたテンプレート・エンジンを提供します。
- [yiisoft/yii2-sphinx](https://github.com/yiisoft/yii2-sphinx):
  [Sphinx](https://sphinxsearch.com/) の使用に対するサポートを提供します。
  基本的なクエリ、アクティブ・レコード、コード生成などの機能を含みます。
- [yiisoft/yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer):
  [swiftmailer](https://swiftmailer.symfony.com/) に基づいたメール送信機能を提供します。
- [yiisoft/yii2-twig](https://github.com/yiisoft/yii2-twig):
  [Twig](https://twig.symfony.com/) に基づいたテンプレート・エンジンを提供します。

下記の公式エクステンションは Yii 2.1 以上のためのものです。
これらは、Yii 2.0 ではコア・フレームワークに含まれていますので、インストールする必要はありません。.

- [yiisoft/yii2-captcha](https://www.yiiframework.com/extension/yiisoft/yii2-captcha):
  CAPTCHA を提供します。
- [yiisoft/yii2-jquery](https://www.yiiframework.com/extension/yiisoft/yii2-jquery):
  [jQuery](https://jquery.com/) のサポートを提供します。
- [yiisoft/yii2-maskedinput](https://www.yiiframework.com/extension/yiisoft/yii2-maskedinput):
  [jQuery Input Mask plugin](https://robinherbots.github.io/Inputmask/) に基づいて、マスクト・インプットを提供します。
- [yiisoft/yii2-mssql](https://www.yiiframework.com/extension/yiisoft/yii2-mssql):
  [MSSQL](https://www.microsoft.com/sql-server/) を使うためのサポートを提供します。
- [yiisoft/yii2-oracle](https://www.yiiframework.com/extension/yiisoft/yii2-oracle):
  [Oracle](https://www.oracle.com/) を使うためのサポートを提供します。
- [yiisoft/yii2-rest](https://www.yiiframework.com/extension/yiisoft/yii2-rest):
  REST API に対するサポートを提供します。
