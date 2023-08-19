レスポンス形式の設定
====================

RESTful API のリクエストを処理するとき、アプリケーションは、通常、レスポンス形式の設定に関して次のステップを踏みます。

1. レスポンス形式に影響するさまざまな要因、例えば、メディア・タイプ、言語、バージョンなどを決定します。
   このプロセスは [コンテント・ネゴシエーション](https://en.wikipedia.org/wiki/Content_negotiation) としても知られるものです。
2. リソース・オブジェクトを配列に変換します。
   [リソース](rest-resources.md) のセクションで説明したように、この作業は [[yii\rest\Serializer]] によって実行されます。
3. 配列をコンテント・ネゴシエーションのステップで決定された形式の文字列に変換します。
   この作業は、`response` [アプリケーション・コンポーネント](structure-application-components.md) の [[yii\web\Response::formatters|formatters]] プロパティに登録された [[yii\web\ResponseFormatterInterface|レスポンス・フォーマッタ]] によって実行されます。

## コンテント・ネゴシエーション <span id="content-negotiation"></span>

Yii は [[yii\filters\ContentNegotiator]] フィルタによってコンテント・ネゴシエーションをサポートします。
RESTful API の基底コントローラ・クラス [[yii\rest\Controller]] は `contentNegotiator` という名前でこのフィルタを持っています。
このフィルタは、レスポンス形式のネゴシエーションと同時に言語のネゴシエーションも提供します。
例えば、RESTful API リクエストが下記のヘッダを含んでいるとします。

```
Accept: application/json; q=1.0, */*; q=0.1
```

この場合、リクエストは JSON 形式のレスポンスを受け取ることになります。例えば、次のような具合です。

```
$ curl -i -H "Accept: application/json; q=1.0, */*; q=0.1" "http://localhost/users"

HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

[
    {
        "id": 1,
        ...
    },
    {
        "id": 2,
        ...
    },
    ...
]
```

舞台裏では、RESTful API コントローラ・アクションが実行される前に、[[yii\filters\ContentNegotiator]] フィルタがリクエストの `Accept` HTTP ヘッダをチェックして、[[yii\web\Response::format|レスポンス形式]] を `'json'` に設定します。
アクションが実行されて、その結果のリソースのオブジェクトまたはコレクションが返されると、[[yii\rest\Serializer]] が結果を配列に変換します。
そして最後に、[[yii\web\JsonResponseFormatter]] が配列を JSON 文字列に変換して、それをレスポンス・ボディに入れます。

デフォルトでは、RESTful API は JSON と XML の両方の形式をサポートします。
新しい形式をサポートするためには、下記のように、API コントローラ・クラスの中で `contentNegotiator` フィルタの [[yii\filters\ContentNegotiator::formats|formats]] プロパティを構成しなければなりません。

```php
use yii\web\Response;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
    return $behaviors;
}
```

`formats` プロパティのキーはサポートされる MIME タイプであり、値は対応するレスポンス形式名です。
このレスポンス形式名は、[[yii\web\Response::formatters]] の中でサポートされているものでなければなりません。


## データのシリアライズ <span id="data-serializing"></span>

上記で説明したように、[[yii\rest\Serializer]] が、リソースのオブジェクトやコレクションを配列に変換する際に、中心的な役割を果たします。
`Serializer` は、[[yii\base\Arrayable]] および [[yii\data\DataProviderInterface]] のインタフェイスを実装したオブジェクトを認識します。
前者は主としてリソース・オブジェクトによって実装され、後者はリソース・コレクションによって実装されています。

[[yii\rest\Controller::serializer]] プロパティに構成情報配列をセットしてシリアライザを構成することが出来ます。
例えば、場合によっては、クライアントの開発作業を単純化するために、ページネーション情報をレスポンス・ボディに直接に含ませたいことがあるでしょう。
そうするためには、[[yii\rest\Serializer::collectionEnvelope]] プロパティを次のように構成します。

```php
use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];
}
```

このようにすると、`http://localhost/users` というリクエストに対して、次のレスポンスを得ることが出来ます。

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self,
      <http://localhost/users?page=2>; rel=next,
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "items": [
        {
            "id": 1,
            ...
        },
        {
            "id": 2,
            ...
        },
        ...
    ],
    "_links": {
        "self": {
            "href": "http://localhost/users?page=1"
        },
        "next": {
            "href": "http://localhost/users?page=2"
        },
        "last": {
            "href": "http://localhost/users?page=50"
        }
    },
    "_meta": {
        "totalCount": 1000,
        "pageCount": 50,
        "currentPage": 1,
        "perPage": 20
    }
}
```

### JSON 出力を制御する

JSON 形式のレスポンスを生成する [[yii\web\JsonResponseFormatter|JsonResponseFormatter]] クラスは [[yii\helpers\Json|JSON ヘルパ]] を内部的に使用します。
このフォーマッタはさまざまなオプションによって構成することが可能です。
例えば、[[yii\web\JsonResponseFormatter::$prettyPrint|$prettyPrint]] オプションは、より読みやすいレスポンスのためのもので、開発時に有用なオプションです。
また、[[yii\web\JsonResponseFormatter::$encodeOptions|$encodeOptions]] によって JSON エンコーディングの出力を制御することが出来ます。

フォーマッタは、以下のように、アプリケーションの [構成情報](concept-configurations.md) の中で、`response` アプリケーション・コンポーネントの [[yii\web\Response::formatters|formatters]] プロパティの中で構成することが出来ます。

```php
'response' => [
    // ...
    'formatters' => [
        \yii\web\Response::FORMAT_JSON => [
            'class' => 'yii\web\JsonResponseFormatter',
            'prettyPrint' => YII_DEBUG, // デバッグ・モードでは "きれい" な出力を使用
            'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            // ...
        ],
    ],
],
```

[DAO](db-dao.md) データベース・レイヤを使ってデータベースからデータを返す場合は、全てのデータが文字列として表されます。
しかし、特に数値は JSON では数として表現されなければなりませんので、これは必ずしも期待通りの結果であるとは言えません。
一方、ActiveRecord レイヤを使ってデータベースからデータを取得する場合は、数値カラムの値は、[[yii\db\ActiveRecord::populateRecord()]] においてデータベースからデータが取得される際に、整数に変換されます。
