アプリケーションコンポーネント
==============================

アプリケーションは [サービスロケータ](concept-service-locator.md) です。
アプリケーションは、リクエストを処理するためのいろいろなサービスを提供する一連のオブジェクト、いわゆる *アプリケーションコンポーネント* をホストします。
例えば、`urlManager` がウェブリクエストを適切なコントローラにルーティングする役割を負い、`db` コンポーネントが DB 関連のサービスを提供する、等々です。

全てのアプリケーションコンポーネントは、それぞれ、同一のアプリケーション内で他のアプリケーションコンポーネントから区別できるように、ユニークな ID を持ちます。
アプリケーションコンポーネントには、次の式によってアクセス出来ます。

```php
\Yii::$app->componentID
```

例えば、`\Yii::$app->db` を使って、アプリケーションに登録された [[yii\db\Connection|DB 接続]] を取得することが出来ます。
また、`\Yii::$app->cache` を使って、[[yii\caching\Cache|プライマリキャッシュ]] を取得できます。

アプリケーションコンポーネントは、上記の式を使ってアクセスされた最初の時に作成されます。
二度目以降のアクセスでは、同じコンポーネントインスタンスが返されます。

どのようなオブジェクトでも、アプリケーションコンポーネントとすることが可能です。
[アプリケーションの構成情報](structure-applications.md#application-configurations) の中で [[yii\base\Application::components]] プロパティを構成することによって、アプリケーションコンポーネントを登録することが出来ます。
例えば、

```php
[
    'components' => [
        // クラス名を使って "cache" コンポーネントを登録
        'cache' => 'yii\caching\ApcCache',

        // 構成情報の配列を使って "db" コンポーネントを登録
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // 無名関数を使って "search" コンポーネントを登録
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Info|情報: 必要なだけ多くのアプリケーションコンポーネントを登録することが出来ますが、慎重にしなければなりません。
  アプリケーションコンポーネントはグローバル変数のようなものです。
  あまり多くのアプリケーションコンポーネントを使うと、コードのテストと保守が困難になるおそれがあります。
  多くの場合、必要なときにローカルなコンポーネントを作成して使用するだけで十分です。


## コンポーネントをブートストラップに含める <span id="bootstrapping-components"></span>

上述のように、アプリケーションコンポーネントは最初にアクセスされた時に初めてインスタンスが作成されます。
リクエストの間に全くアクセスされなかった時は、インスタンスは作成されません。
けれども、場合によっては、明示的にアクセスされないときでも、リクエストごとにアプリケーションコンポーネントのインスタンスを作成したいことがあります。
そうするためには、アプリケーションの [[yii\base\Application::bootstrap|bootstrap]] プロパティのリストにそのコンポーネントの ID を挙げることが出来ます。

例えば、次のアプリケーション構成情報は、`log` コンポーネントが常にロードされることを保証するものです。

```php
[
    'bootstrap' => [
        'log',
    ],
    'components' => [
        'log' => [
            // "log" コンポーネントの構成情報
        ],
    ],
]
```


## コアアプリケーションコンポーネント <span id="core-application-components"></span>

Yii は固定の ID とデフォルトの構成情報を持つ一連の *コア* アプリケーションコンポーネントを定義しています。
例えば、[[yii\web\Application::request|request]] コンポーネントは、ユーザリクエストに関する情報を収集して、それを [ルート](runtime-routing.md) として解決するために使用されます。
また、[[yii\base\Application::db|db]] コンポーネントは、それを通じてデータベースクエリを実行できるデータベース接続を表現します。
Yii のアプリケーションがユーザリクエストを処理出来るのは、まさにこれらのコアアプリケーションコンポーネントの助けがあってこそです。

下記が事前に定義されたコアアプリケーションコンポーネントです。
通常のアプリケーションコンポーネントに対するのと同様に、これらを構成し、カスタマイズすることが出来ます。
コアアプリケーションコンポーネントを構成するときは、クラスを指定しない場合は、デフォルトのクラスが使用されます。

* [[yii\web\AssetManager|assetManager]]: アセットバンドルとアセットの発行を管理します。
  詳細は [アセット](structure-assets.md) の節を参照してください。
* [[yii\db\Connection|db]]: データベース接続を表します。これを通じて、DB クエリを実行することが出来ます。
  このコンポーネントを構成するときは、コンポーネントのクラスはもちろん、[[yii\db\Connection::dsn]] のような必須のコンポーネントプロパティを指定しなければならないことに注意してください。
  詳細は [データアクセスオブジェクト](db-dao.md) の節を参照してください。
* [[yii\base\Application::errorHandler|errorHandler]]: PHP のエラーと例外を処理します。
  詳細は [エラー処理](runtime-handling-errors.md) の節を参照してください。
* [[yii\i18n\Formatter|formatter]]: エンドユーザに表示されるデータに書式を設定します。
  例えば、数字が3桁ごとの区切りを使って表示されたり、日付が長い書式で表示されたりします。
  詳細は [データの書式設定](output-formatting.md) の節を参照してください。
* [[yii\i18n\I18N|i18n]]: メッセージの翻訳と書式設定をサポートします。
  詳細は [国際化](tutorial-i18n.md) の節を参照してください。
* [[yii\log\Dispatcher|log]]: ログターゲットを管理します。
  詳細は [ロギング](runtime-logging.md) の節を参照してください。
* [[yii\swiftmailer\Mailer|mail]]: メールの作成と送信をサポートします。
  詳細は [メール](tutorial-mailing.md) の節を参照してください。
* [[yii\base\Application::response|response]]: エンドユーザに送信されるレスポンスを表現します。
  詳細は [レスポンス](runtime-responses.md) の節を参照してください。
* [[yii\base\Application::request|request]]: エンドユーザから受信したリクエストを表現します。
  詳細は [リクエスト](runtime-requests.md) の節を参照してください。
* [[yii\web\Session|session]]: セッション情報を表現します。
  このコンポーネントは、[[yii\web\Application|ウェブアプリケーション]] においてのみ利用できます。
  詳細は [セッションとクッキー](runtime-sessions-cookies.md) の節を参照してください。
* [[yii\web\UrlManager|urlManager]]: URL の解析と生成をサポートします。
  詳細は [ルーティング と URL 生成](runtime-routing.md) の節を参照してください。
* [[yii\web\User|user]]: ユーザの認証情報を表現します。
  このコンポーネントは、[[yii\web\Application|ウェブアプリケーション]] においてのみ利用できます。
  詳細は [認証](security-authentication.md) の節を参照してください。
* [[yii\web\View|view]]: ビューのレンダリングをサポートします。
  詳細は [ビュー](structure-views.md) の節を参照してください。
