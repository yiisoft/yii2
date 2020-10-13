# Yii をマイクロ・フレームワークとして使う

Yii はベーシック・テンプレートやアドバンスト・テンプレートに含まれる機能なしで使うことが簡単にできます。言葉を換えれば、Yii は既にマイクロ・フレームワークです。Yii を使うためにテンプレートによって提供されているディレクトリ構造を持つことは要求されていません。

このことは、アセットやビューなどの事前定義されたテンプレート・コードを必要としない場合には、特に好都合です。そのような場合の一つが JSON API です。以下に続くセクションで、どのようにしてそれを実現するかを示します。

## Yii をインストールする

プロジェクト・ファイルのためのディレクトリを作成し、ワーキング・ディレクトリをそのパスに変更します。例で使用されているコマンドは UNIX ベースのものですが、同様のコマンドが Windows にもあります。

```bash
mkdir micro-app
cd micro-app
```

> Note: 続けるためには Composer についての知識が多少必要です。Composer の使い方をまだ知らない場合は、時間を取って、[Composer Guide](https://getcomposer.org/doc/00-intro.md) を読んでください。

`micro-app` ディレクトリの下に  `composer.json` ファイルを作成し、あなたの好みのエディタを使って、下記を追加します。

```json
{
    "require": {
        "yiisoft/yii2": "~2.0.0"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "https://asset-packagist.org"
        }
    ]
}
```

ファイルを保存して `composer install` コマンドを実行します。これによって、フレームワークがその全ての依存とともにインストールされます。

## プロジェクトの構造を作成する

フレームワークをインストールしたら、次は、アプリケーションの [エントリ・ポイント](structure-entry-scripts.md) を作成します。エントリ・ポイントは、アプリケーションを開こうとしたときに、一番最初に実行されるファイルです。セキュリティ上の理由により、エントリ・ポイントを置くディレクトリは別にして、それをウェブ・ルートとします。

`web` ディレクトリを作成して、下記の内容を持つ `index.php` をそこに置きます。

```php 
<?php

// 実運用サーバに配備するときは次の2行をコメント・アウトする
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require __DIR__ . '/../config.php';
(new yii\web\Application($config))->run();
```

また `config.php` という名前のファイルを作成し、アプリケーションの全ての構成情報をそこに含ませます。

```php
<?php
return [
    'id' => 'micro-app',
    // アプリケーションの basePath は `micro-app` ディレクトリになります
    'basePath' => __DIR__,
    // この名前空間からアプリケーションは全てのコントローラを探します
    'controllerNamespace' => 'micro\controllers',
    // 'micro' 名前空間からのクラスのオートロードを可能にするためにエイリアスを設定します
    'aliases' => [
        '@micro' => __DIR__,
    ],
];
```

> Info: 構成情報を `index.php` ファイルに持つことも出来ますが、別のファイルに持つことを推奨します。
> そうすれば、後で示しているように、同じ構成情報をコンソール・アプリケーションから使うことが出来ます。

これであなたのプロジェクトはコーディングの準備が出来ました。プロジェクトのディレクトリ構造を決定するのは、名前空間に注意する限り、あなた次第です。

## 最初のコントローラを作成する

`controllers` ディレクトリを作成し、`SiteController.php` というファイルを追加します。
これが、パス情報を持たないリクエストを処理する、デフォルトのコントローラです。

```php
<?php

namespace micro\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return 'こんにちは!';
    }
}
```

このコントローラに違う名前を使いたい場合は、名前を変更して [[yii\base\Application::$defaultRoute]] をそれに応じて変更します。
例えば、`DefaultController` であれば、`'defaultRoute' => 'default/index'` と変更します。

この時点で、プロジェクトの構造は次のようになっています。

```
micro-app/
├── composer.json
├── config.php
├── web/
    └── index.php
└── controllers/
    └── SiteController.php
```

まだウェブ・サーバをセットアップしていない場合は、[ウェブ・サーバの構成ファイル例](start-installation.md#configuring-web-servers) を参照すると良いでしょう。
もう一つのオプションは、PHP の内蔵ウェブ・サーバを利用する `yii serve` コマンドを使うことです。
`micro-app/` ディレクトリから、次のコマンドを実行します。

    vendor/bin/yii serve --docroot=./web

アプリケーションの URL をブラウザで開くと、`SiteController::actionIndex()` で返された "こんにちは!" という文字列が表示される筈です。

> Info: 私たちの例では、アプリケーションのデフォルトの名前空間 `app` を `micro` に変更しています。
> これは、あなたがその名前に縛られていないことを示すためです(万一あなたが縛られていると思っている場合を考えて)。
> そして、[[yii\base\Application::$controllerNamespace|コントローラの名前空間]] を修正し、正しいエイリアスを設定しています。


## REST API を作成する

私たちの "マイクロ・フレームワーク" の使い方を示すために、記事のための簡単な REST API を作成しましょう。
`
この API が何らかのデータを提供するためには、まず、データベースが必要です。
データベース接続の構成をアプリケーション構成に追加します。

```php
'components' => [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'sqlite:@micro/database.sqlite',
    ],
],
```

> Info: ここでは話を簡単にするために sqlite データベースを使用します。他のオプションについては [データベースのガイド](db-dao.md) を参照してください。

次に、[データベース・マイグレーション](db-migrations.md) を作成して、記事のテーブルを作成します。
既に述べたように、独立した構成情報ファイルがあることを確認してください。
下記のコンソール・コマンドを実行するためには、それが必要です。
次のコマンドを実行すると、データベース・マイグレーション・ファイルが作成され、そして、マイグレーションがデータベースに適用されます。

    vendor/bin/yii migrate/create --appconfig=config.php create_post_table --fields="title:string,body:text"
    vendor/bin/yii migrate/up --appconfig=config.php

`models` ディレクトリを作成し、`Post.php` ファイルをそのディレクトリに置きます。以下がそのモデルのためのコードです。

```php
<?php

namespace micro\models;

use yii\db\ActiveRecord;

class Post extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{post}}';
    }
}
```

> Info: ここで作成されたモデルは ActiveRecord クラスのもので、`post` テーブルのデータを表します。
> 詳細な情報は [アクティブ・レコードのガイド](db-active-record.md) を参照してください。

私たちの API で記事データへのアクセスを提供するために、`controllers` に `PostController` を追加します。

```php
<?php

namespace micro\controllers;

use yii\rest\ActiveController;

class PostController extends ActiveController
{
    public $modelClass = 'micro\models\Post';

    public function behaviors()
    {
        // 動作のために認証済みユーザであることを要求する rateLimiter を削除
        $behaviors = parent::behaviors();
        unset($behaviors['rateLimiter']);
        return $behaviors;
    }
}
```

この時点で私たちの API は以下の URL を提供します。

- `/index.php?r=post` - 全ての記事をリストする
- `/index.php?r=post/view&id=1` - ID 1 の記事を表示する
- `/index.php?r=post/create` - 記事を作成する
- `/index.php?r=post/update&id=1` - ID 1 の記事を更新する
- `/index.php?r=post/delete&id=1` - ID 1 の記事を削除する

ここから開始して、あなたのアプリケーションの開発を更に進めるために、次のガイドを読むと良いでしょう。

- API は今のところ入力として URL エンコードされたフォームデータだけを理解します。
  本物の JSON API にするためには、[[yii\web\JsonParser]] を構成する必要があります。
- URL をもっと馴染みやすいものにするためには、ルーティングを構成しなければなりません。
  方法を知るためには [REST のルーティングのガイド](rest-routing.md) を参照してください。
- 更に参照すべき文書を知るために [先を見通す](start-looking-ahead.md) のセクションを読んでください。
