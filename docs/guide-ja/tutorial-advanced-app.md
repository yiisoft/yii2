アドバンストアプリケーションテンプレート
========================================

> Note|注意: この節はまだ執筆中です。

このテンプレートは、バックエンドがフロントエンドから分離されたり、アプリケーションが複数のサーバに配備されたりするような、チーム開発による大規模なプロジェクトのためのものです。
また、このアプリケーションテンプレートは機能に関して少し踏み込んで、不可欠なデータベースやユーザ登録、パスワード回復などをそのまま使える形で提供しています。

次の表はアドバンストとベーシックのアプリケーションテンプレートの違いを比較するものです。


| 機能  |  ベーシック  |  アドバンスト |
|---|:---:|:---:|
| プロジェクト構造 |  ✓  |  ✓  |
| Site コントローラ |  ✓  |  ✓  |
| ユーザのログイン/ログアウト |   ✓  |  ✓  |
| フォーム  |   ✓  |  ✓  |
| DB 接続  |   ✓  |  ✓  |
| コンソールコマンド  |   ✓  |  ✓  |
| アセットバンドル  |   ✓  |  ✓  |
| Codeception によるテスト  |   ✓  |  ✓  |
| Twitter Bootstrap  |  ✓   |  ✓  |
| フロントエンドとバックエンド  |    |  ✓  |
| すぐに使える User モデル |    |  ✓  |
| ユーザの登録とパスワード回復  |     |  ✓  |


インストール
------------

### Composer によってインストールする

[Composer](http://getcomposer.org/) を持っていない場合は、[Yii をインストールする](start-installation.md#installing-via-composer) の節の指示に従ってインストールしてください。

Composer がインストールされていれば、次のコマンドを使ってアプリケーションをインストールすることが出来ます。

    composer global require "fxp/composer-asset-plugin:1.0.0"
    composer create-project --prefer-dist yiisoft/yii2-app-advanced yii-application

最初のコマンドは [composer asset plugin](https://github.com/francoispluchino/composer-asset-plugin/) をインストールします。
これにより、Composer を通じて bower と npm の依存パッケージを管理することが出来るようになります。
このコマンドは全体で一度だけ走らせれば十分です。
第二のコマンドは `yii-application` という名前のディレクトリにアドバンストアプリケーションをインストールします。
望むなら別のディレクトリ名を選ぶことも出来ます。


始めよう
--------

アプリケーションをインストールした後に、インストールされたアプリケーションの初期設定をするために、次の各ステップを実行しなければなりません。
これらは全体で一度だけやれば十分です。

1. `init` コマンドを実行して、環境として `dev` を選択します。

    ```
    php /path/to/yii-application/init
    ```

    あるいは、本番サーバで、非対話モードで `init` を実行します。

    ```
    php /path/to/yii-application/init --env=Production --overwrite=All
    ```

2. 新しいデータベースを作成し、それに従って `common/config/main-local.php` の `components['db']` の構成情報を修正します。
3. コンソールコマンド `yii migrate` でマイグレーションを適用します。
4. ウェブサーバのドキュメントルートを設定します。

- フロントエンドのパスは `/path/to/yii-application/frontend/web/`、URL は `http://frontend/` を使用
- バックエンドのパスは `/path/to/yii-application/backend/web/`、URL は `http://backend/` を使用

アプリケーションにログインするためには、最初にユーザ登録をする必要があります。
あなたの任意のメールアドレス、ユーザ名、パスワードを指定してください。
そうすれば、同じメールアドレスとパスワードを使って何時でもアプリケーションにログインすることが出来ます。

ディレクトリ構造
----------------

ルートディレクトリは次のサブディレクトリを含みます。

- `backend` - バックエンドのウェブアプリケーション
- `common` - 全てのアプリケーションに共通なファイル
- `console` - コンソールアプリケーション
- `environments` - 環境設定
- `frontend` - フロントエンドのアプリケーション

ルートディレクトリは次の一群のファイルを含みます。

- `.gitignore` - git バージョン管理システムによって無視されるディレクトリの一覧を含みます。
  ソースコードのレポジトリに決して入れたくないものがあれば、それをこれに追加してください。
- `composer.json` - Composer の構成。下の「Composer を構成する」で説明します。
- `init` - 初期化スクリプト。下の「構成情報と環境」で説明します。
- `init.bat` - 同上 (Windows 用)。
- `LICENSE.md` - ライセンス情報。プロジェクトのライセンスを置きます。特にオープンソースにする場合。
- `README.md` - テンプレートのインストールに関する基本的な情報。
  あなたのプロジェクトとそのインストールに関する情報に置き換えることを検討してください。
- `requirements.php` - Yii 必要条件チェッカ。
- `yii` - コンソールアプリケーションのブートストラップスクリプト。
- `yii.bat` - 同上 (Windows 用)。

事前定義されたパスエイリアス
----------------------------

- `@yii` - フレームワークのディレクトリ。
- `@app` - 現在走っているアプリケーションのベースパス。
- `@common` - 共通ディレクトリ。
- `@frontend` - フロントエンドウェブアプリケーションのディレクトリ。
- `@backend` - バックエンドウェブアプリケーションのディレクトリ。
- `@console` - コンソールアプリケーションのディレクトリ。
- `@runtime` - 現在走っているウェブアプリケーションのランタイムディレクトリ。
- `@vendor` - Composer の ベンダーディレクトリ。
- `@bower` - [bower パッケージ](http://bower.io/) を含むベンダーディレクトリ。
- `@npm` - [npm パッケージ](https://www.npmjs.org/) を含むベンダーディレクトリ。
- `@web` - 現在走っているウェブアプリケーションのベース URL。
- `@webroot` - 現在走っているウェブアプリケーションのウェブルートディレクトリ。

アドバンストアプリケーションのディレクトリ構造特有のエイリアス (`@common`、`@frontend`、`@backend`、`@console`) は `common/config/bootstrap.php` で定義されています。


アプリケーション
----------------

アドバンストテンプレートには三つのアプリケーションがあります。
すなわち、フロントエンド、バックエンド、そして、コンソールです。
フロントエンドは典型的にはエンドユーザに提示されるもので、プロジェクトの本体です。
バックエンドは管理パネルや、分析などの機能です。
コンソールは典型的にはクロンジョブや低レベルのサーバ管理に使用されます。
コンソールは、また、アプリケーションの配備の際にも使われ、マイグレーションやアセットを処理します。

さらに、二つ以上のアプリケーションから使われるファイルを含む `common` ディレクトリがあります。
例えば、`User` モデルがそうです。

フロントエンドとバックエンドは両方ともウェブアプリケーションであり、ともに `web` ディレクトリを含んでいます。
これがウェブサーバのウェブルートとすべきディレクトリです。

各アプリケーションはそれ自身の名前空間と、その名前に対応するエイリアスをもっています。
同じことは `common` ディレクトリにも当てはまります。

構成情報と環境
--------------

構成情報に対する典型的なアプローチには、複数の問題があります。

- チームの各メンバーは、自分自身の構成オプションを持っています。
  そのような構成をコミットすると、他のメンバーに影響を与えます。
- 本番のデータベースのパスワードと API キーは、レポジトリに入れるべきではありません。
- 複数のサーバ環境があります。すなわち、開発、テスト、本番などです。各サーバはそれ自身の構成情報を持たなければなりません。
- 全ての構成オプションを各ケースに対して定義することは反復の多い作業であり、保守するのに時間を取りすぎます。

これらの問題を解決するために、Yii は単純な環境の概念を導入しています。
それぞれの環境は `environments` ディレクトリ配下の一群のファイルとして表現されます。
`init` コマンドがこれらの環境を切り替えるのに使用されます。
`init` コマンドが実際にやっていることは、環境ディレクトリから、全てのアプリケーションがあるルートディレクトリへと、すべてをごっそりとコピーすることです。

デフォルトでは二つの環境があります。すなわち、`dev` と `prod` です。
最初のものは開発用の環境で、全ての開発ツールとデバッグが有効になっています。
第二のものは本番サーバ配備用の環境で、デバッグと開発ツールは無効になっています。

典型的には、環境ディレクトリは `index.php` のようなアプリケーションブートストラップファイルや、`-local.php` という接尾辞を持つ構成情報ファイルを含んでいます。
これらは `.gitignore` に追加されて、ソースコードレポジトリには決して追加されないようになっています。

重複を避けるために、構成情報はお互いを上書きします。
例えば、フロントエンドは次の順序で構成情報を読み取ります。

- `common/config/main.php`
- `common/config/main-local.php`
- `frontend/config/main.php`
- `frontend/config/main-local.php`

パラメータは次の順序で読まれます。

- `common/config/params.php`
- `common/config/params-local.php`
- `frontend/config/params.php`
- `frontend/config/params-local.php`

後の構成情報ファイルが先のものを上書きするわけです。

全体の枠組みはこのようになります。

![アドバンストアプリケーションの構成情報](images/advanced-app-configs.png)

Composer を構成する
-------------------

アプリケーションテンプレートがインストールされた後に、ルートディレクトリにあるデフォルトの `composer.json` を修正するのは良い考えです。

```json
{
    "name": "yiisoft/yii2-app-advanced",
    "description": "Yii 2 Advanced Application Template",
    "keywords": ["yii2", "framework", "advanced", "application template"],
    "homepage": "http://www.yiiframework.com/",
    "type": "project",
    "license": "BSD-3-Clause",
    "support": {
        "issues": "https://github.com/yiisoft/yii2/issues?state=open",
        "forum": "http://www.yiiframework.com/forum/",
        "wiki": "http://www.yiiframework.com/wiki/",
        "irc": "irc://irc.freenode.net/yii",
        "source": "https://github.com/yiisoft/yii2"
    },
    "minimum-stability": "dev",
    "require": {
        "php": ">=5.4.0",
        "yiisoft/yii2": "*",
        "yiisoft/yii2-bootstrap": "*",
        "yiisoft/yii2-swiftmailer": "*"
    },
    "require-dev": {
        "yiisoft/yii2-codeception": "*",
        "yiisoft/yii2-debug": "*",
        "yiisoft/yii2-gii": "*",
        "yiisoft/yii2-faker": "*"
    },
    "config": {
        "process-timeout": 1800
    },
    "extra": {
        "asset-installer-paths": {
            "npm-asset-library": "vendor/npm",
            "bower-asset-library": "vendor/bower"
        }
    }
}
```

最初に、基本的な情報を更新しましょう。
`name`、`description`、`keywords`、`homepage` および `support` をあなたのプロジェクトに合うように変更します。

次に興味深い部分です。
あなたは、あなたのアプリケーションが必要とするパッケージを `require` セクションに追加することが出来ます。
追加のパッケージは全て [packagist.org](https://packagist.org/) から取ってくることが出来ます。ウェブサイトを閲覧して、役に立つコードを探してください。

`composer.json` を修正した後、`composer update --prefer-dist` を実行し、パッケージがダウンロードされインストールされるのを待ちます。
後はただ使用するだけです。クラスのオートロードは自動的に処理されます。

バックエンドからフロントエンドにリンクを張る
--------------------------------------------

バックエンドアプリケーションからフロントエンドアプリケーションにリンクを張らなければならないことがよくあります。
フロントエンドアプリケーションはそれ自身の URL マネージャ規則を持っている場合がありますので、それをバックエンドアプリケーションのために別の名前で複製する必要があります。

```php
return [
    'components' => [
        'urlManager' => [
            // ここに通常のバックエンドの URL マネージャの構成
        ],
        'urlManagerFrontend' => [
            // ここにフロントエンドの URL マネージャの構成
        ],

    ],
];
```

このようにすると、フロントエンドを指す URL を次のようにして取得することが出来ます。

```php
echo Yii::$app->urlManagerFrontend->createUrl(...);
```
