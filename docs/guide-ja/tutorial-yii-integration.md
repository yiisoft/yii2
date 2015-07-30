サードパーティのコードを扱う
============================

時々、Yii アプリケーションの中でサードパーティのコードを使用する必要があることがあります。
あるいは、サードパーティのシステムの中で Yii をライブラリとして使用したいこともあるでしょう。
この節では、こういう目的をどうやって達成するかを説明します。


Yii の中でサードパーティのライブラリを使う <span id="using-libs-in-yii"></span>
------------------------------------------

Yii アプリケーションの中でサードパーティのライブラリを使うために主として必要なことは、そのライブラリのクラスが適切にインクルードされること、または、オートロード可能であることを保証することです。

### Composer パッケージを使う <span id="using-composer-packages"></span>

多くのサードパーティライブラリは [Composer](https://getcomposer.org/) パッケージの形式でリリースされています。
そのようなライブラリは、次の二つの簡単なステップを踏むことによって、インストールすることが出来ます。

1. アプリケーションの `composer.json` ファイルを修正して、どの Composer パッケージをインストールしたいかを指定する。
2. `composer install` を実行して、指定したパッケージをインストールする。

インストールされた Composer パッケージ内のクラスは、Composer のオートローダを使ってオートロードすることが出来ます。
アプリケーションの [エントリスクリプト](structure-entry-scripts.md) に、Composer のオートローダをインストールするための下記の行があることを確認してください。

```php
// Composer のオートローダをインストール
require(__DIR__ . '/../vendor/autoload.php');

// Yii クラスファイルをインクルード
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
```

### ダウンロードしたライブラリを使う <span id="using-downloaded-libs"></span>

ライブラリが Composer パッケージとしてリリースされていない場合は、そのライブラリのインストールの指示に従ってインストールしなければなりません。
たいていの場合は、リリースファイルを手動でダウンロードし、`BasePath/vendor` ディレクトリの下に解凍する必要があります。
ここで `BasePath` は、アプリケーションの [base path](structure-applications.md#basePath) を表すものです。

ライブラリがそれ自身のオートローダを持っている場合は、それをアプリケーションの [エントリスクリプト](structure-entry-scripts.md) でインストールすることが出来ます。
複数のオートローダクラスの中で Yii のクラスオートローダが優先されるように、ライブラリのオートローダは `Yii.php` ファイルをインクルードする前にインストールすることを推奨します。

ライブラリがクラスオートローダを提供していない場合でも、クラスの命名規約が [PSR-4](http://www.php-fig.org/psr/psr-4/) に従っている場合は、ライブラリのクラスをオートロードするのに Yii のクラスオートローダを使うことが出来ます。
必要なことは、ライブラリのクラスによって使われている全てのルート名前空間に対して [ルートエイリアス](concept-aliases.md#defining-aliases) を宣言することだけです。
例えば、ライブラリを `vendor/foo/bar` ディレクトリの下にインストールしたとしましょう。
そしてライブラリのクラスは `xyz` ルート名前空間の下にあるとします。
この場合、アプリケーションの構成情報において、次のコードを含めれば良いのです。

```php
[
    'aliases' => [
        '@xyz' => '@vendor/foo/bar',
    ],
]
```

上記のどちらにも当てはまらない場合、おそらくそのライブラリは、クラスファイルを探して適切にインクルードするために、PHP の include path 設定に依存しているのでしょう。
この場合は、PHP include path の設定に関するライブラリの指示に従うしかありません。

最悪の場合として、ライブラリが全てのクラスファイルを明示的にインクルードすることを要求している場合は、次の方法を使ってクラスを必要に応じてインクルードすることが出来るようになります。

* ライブラリに含まれるクラスを特定する。
* アプリケーションの [エントリスクリプト](structure-entry-scripts.md) において、クラスと対応するファイルパスを `Yii::$classMap` としてリストアップする。
例えば、
```php
Yii::$classMap['Class1'] = 'path/to/Class1.php';
Yii::$classMap['Class2'] = 'path/to/Class2.php';
```


サードパーティのシステムで Yii を使う <span id="using-yii-in-others"></span>
-------------------------------------

Yii は数多くの優れた機能を提供していますので、サードパーティのシステム (例えば、WordPress、Joomla、または、他の PHP フレームワークを使って開発されるアプリケーション) を開発したり機能拡張したりするのをサポートするために Yii の機能のいくつかを使用したいことがあるでしょう。
例えば、[[yii\helpers\ArrayHelper]] クラスや [アクティブレコード](db-active-record.md) をサードパーティのシステムで使いたいことがあるでしょう。
この目的を達するためには、主として、二つのステップを踏む必要があります。
すなわち、Yii のインストールと、Yii のブートストラップです。

サードパーティのシステムが Composer を使って依存を管理している場合は、単に下記のコマンドを実行すれば Yii をインストールすることが出来ます。

    composer global require "fxp/composer-asset-plugin:~1.0.0"
    composer require yiisoft/yii2
    composer install

最初のコマンドは [composer アセットプラグイン](https://github.com/francoispluchino/composer-asset-plugin/) をインストールします。
これは、Composer によって bower と npm の依存パッケージを管理できるようにするものです。
このことは、データベースなど、アセットに関係しない Yii の機能を使いたいだけの場合でも、Yii の Composer パッケージをインストールするために必要とされます。
Composer に関する更なる情報や、インストールの過程で出現しうる問題に対する解決方法については、一般的な [Composer によるインストール](start-installation.md#installing-via-composer) の節を参照してください。

そうでない場合は、Yii のリリースを [ダウンロード](http://www.yiiframework.com/download/) して、`BasePath/vendor` ディレクトリに解凍してください。

次に、サードパーティのシステムのエントリスクリプトを修正します。
次のコードをエントリスクリプトの先頭に追加してください。

```php
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$yiiConfig = require(__DIR__ . '/../config/yii/web.php');
new yii\web\Application($yiiConfig); // ここで run() を呼ばない
```

ごらんのように、上記のコードは典型的な Yii アプリケーションの [エントリスクリプト](structure-entry-scripts.md) と非常に良く似ています。
唯一の違いは、アプリケーションのインスタンスが作成された後に `run()` メソッドが呼ばれない、という点です。
`run()` を呼ぶと Yii がリクエスト処理のワークフローを制御するようになりますが、この場合はリクエストを処理する別のアプリケーションが既に存在していますので、これは必要ではないからです。

Yii アプリケーションでの場合と同じように、サードパーティシステムが走っている環境に基づいて Yii のアプリケーションインスタンスを構成する必要があります。
例えば、[アクティブレコード](db-active-record.md) の機能を使うためには、サードパーティシステムによって使用されている DB 接続の設定を使って `db` [アプリケーションコンポーネント](structure-application-components.md) を構成しなければなりません。

これで、Yii によって提供されているほとんどの機能を使うことが出来ます。
例えば、アクティブレコードクラスを作成して、それを使ってデータベースを扱うことが出来ます。


Yii 2 を Yii 1 とともに使う <span id="using-both-yii2-yii1"></span>
---------------------------

あなたが Yii 1 を前から使っている場合は、たぶん、稼働中の Yii 1 アプリケーションを持っているでしょう。
アプリケーション全体を Yii 2 で書き直す代りに、Yii 2 でのみ利用できる機能を使ってアプリケーションを機能拡張したいこともあるでしょう。
このことは、以下に述べるようにして、実現できます。

> Note|注意: Yii 2 は PHP 5.4 以上を必要とします。
> あなたのサーバと既存のアプリケーションが PHP 5.4 以上をサポートしていることを確認しなければなりません。

最初に、[直前の項](#using-yii-in-others) で述べられている指示に従って、Yii 2 を既存のアプリケーションにインストールします。

次に、アプリケーションのエントリスクリプトを以下のように修正します。

```php
// カスタマイズされた Yii クラスをインクルード (下記で説明)
require(__DIR__ . '/../components/Yii.php');

// Yii 2 アプリケーションの構成
$yii2Config = require(__DIR__ . '/../config/yii2/web.php');
new yii\web\Application($yii2Config); // ここで run() を呼ばない

// Yii 1 アプリケーションの構成
$yii1Config = require(__DIR__ . '/../config/yii1/main.php');
Yii::createWebApplication($yii1Config)->run();
```

Yii 1 と Yii 2 の両者が `Yii` クラスを持っているため、二つを結合するカスタムバージョンを作成する必要があります。
上記のコードでカスタマイズされた `Yii` クラスファイルをインクルードしていますが、これは下記のようにして作成することが出来ます。

```php
$yii2path = '/path/to/yii2';
require($yii2path . '/BaseYii.php'); // Yii 2.x

$yii1path = '/path/to/yii1';
require($yii1path . '/YiiBase.php'); // Yii 1.x

class Yii extends \yii\BaseYii
{
    // YiiBase (1.x) のコードをここにコピー・ペースト
}

Yii::$classMap = include($yii2path . '/classes.php');
// Yii2 オートローダを Yii1 によって登録
Yii::registerAutoloader(['Yii', 'autoload']);
// 依存注入コンテナを作成
Yii::$container = new yii\di\Container;
```

以上です。
これで、あなたのコードのどの部分においても、`Yii::$app` を使って Yii 2 アプリケーションインスタンスにアクセスすることが出来、`Yii::app()` によって Yii 1 アプリケーションインスタンスを取得することが出来ます。

```php
echo get_class(Yii::app()); // 'CWebApplication' を出力
echo get_class(Yii::$app);  // 'yii\web\Application' を出力
```
