クイックスタート
================

Yii は、RESTful ウェブサービス API を実装する仕事を簡単にするために、一揃いのツールを提供しています。
具体的に言えば、RESTful API に関する次の機能をサポートしています。

* [アクティブレコード](db-active-record.md) のための共通 API をサポートした迅速なプロトタイプ作成
* レスポンス形式のネゴシエーション (デフォルトで JSON と XML をサポート)
* 出力フィールドの選択をサポートした、カスタマイズ可能なオブジェクトのシリアライゼーション
* コレクションデータとバリデーションエラーの適切な書式設定
* [HATEOAS](http://en.wikipedia.org/wiki/HATEOAS) のサポート
* HTTP 動詞を適切にチェックする効率的なルーティング
* `OPTIONS` および `HEAD` 動詞のサポートを内蔵
* 認証と権限付与
* データキャッシュと HTTP キャッシュ
* レート制限


以下においては、例を使って、どのようにして最小限のコーディング労力で一組の RESTful API を構築することが出来るかを説明します。

ユーザのデータを RESTful API によって公開したいと仮定しましょう。
ユーザのデータは `user` という DB テーブルに保存されており、それにアクセスするための [アクティブレコード](db-active-record.md) クラス `app\models\User` が既に作成済みであるとします。


## コントローラを作成する <span id="creating-controller"></span>

最初に、[コントローラ](structure-controllers.md) クラス `app\controllers\UserController` を次のようにして作成します。

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
}
```

このコントローラクラスは、よく使用される一揃いの RESTful アクションを実装した [[yii\rest\ActiveController]] を拡張するものです。
[[yii\rest\ActiveController::modelClass|modelClass]] を `app\models\User` と指定することによって、データの取得と操作にどのモデルが使用できるかをコントローラに教えてやります。
The controller class extends from [[yii\rest\ActiveController]], which implements a common set of RESTful actions.
By specifying [[yii\rest\ActiveController::modelClass|modelClass]]
as `app\models\User`, the controller knows which model can be used for fetching and manipulating data.


## URL 規則を構成する <span id="configuring-url-rules"></span>

次に、アプリケーションの構成情報において、`urlManager` コンポーネントに関する構成情報を修正します。

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
    ],
]
```

上記の構成情報は、主として、`user` コントローラの URL 規則を追加して、ユーザのデータが綺麗な URL と意味のある HTTP 動詞によってアクセスおよび操作できるようにするものです。


## JSON の入力を可能にする <span id="enabling-json-input"></span>

API が JSON 形式で入力データを受け取ることが出来るように、`request` [アプリケーションコンポーネント](structure-application-components.md) の [[yii\web\Request::$parsers|parsers]] プロパティを構成して、JSON 入力のために [[yii\web\JsonParser]] を使うようにします。

```php
'request' => [
    'parsers' => [
        'application/json' => 'yii\web\JsonParser',
    ]
]
```

> Info|情報: 上記の構成はオプションです。
  上記のように構成しない場合は、API は `application/x-www-form-urlencoded` と `multipart/form-data` だけを入力形式として認識します。


## 試してみる <span id="trying-it-out"></span>

上記で示した最小限の労力によって、ユーザのデータにアクセスする RESTful API を作成する仕事は既に完成しています。
作成した API は次のものを含みます。

* `GET /users`: 全てのユーザをページごとに一覧する
* `HEAD /users`: ユーザ一覧の概要を示す
* `POST /users`: 新しいユーザを作成する
* `GET /users/123`: ユーザ 123 の詳細を返す
* `HEAD /users/123`: ユーザ 123 の概要を示す
* `PATCH /users/123` と `PUT /users/123`: ユーザ 123 を更新する
* `DELETE /users/123`: ユーザ 123 を削除する
* `OPTIONS /users`: エンドポイント `/users` に関してサポートされている動詞を示す
* `OPTIONS /users/123`: エンドポイント `/users/123` に関してサポートされている動詞を示す

> Info|情報: Yii はコントローラの名前を自動的に複数形にしてエンドポイントとして使用します。
> この振る舞いは [[yii\rest\UrlRule::$pluralize]] プロパティを使って構成することが可能です。

作成した API は、次のように、`curl` コマンドでアクセスすることが出来ます。

```
$ curl -i -H "Accept:application/json" "http://localhost/users"

HTTP/1.1 200 OK
...
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

受入れ可能なコンテントタイプを `application/xml` に変更してみてください。
すると、結果が XML 形式で返されます。

```
$ curl -i -H "Accept:application/xml" "http://localhost/users"

HTTP/1.1 200 OK
...
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self, 
      <http://localhost/users?page=2>; rel=next, 
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/xml

<?xml version="1.0" encoding="UTF-8"?>
<response>
    <item>
        <id>1</id>
        ...
    </item>
    <item>
        <id>2</id>
        ...
    </item>
    ...
</response>
```

次のコマンドは、JSON 形式でユーザのデータを持つ POST リクエストを送信して、新しいユーザを作成します。

```
$ curl -i -H "Accept:application/json" -H "Content-Type:application/json" -XPOST "http://localhost/users" -d '{"username": "example", "email": "user@example.com"}'

HTTP/1.1 201 Created
...
Location: http://localhost/users/1
Content-Length: 99
Content-Type: application/json; charset=UTF-8

{"id":1,"username":"example","email":"user@example.com","created_at":1414674789,"updated_at":1414674789}
```

> Tip|ヒント: URL `http://localhost/users` を入力すれば、ウェブブラウザ経由で API にアクセスすることも出来ます。
  ただし、特殊なリクエストヘッダを送信するためには、何らかのブラウザプラグインが必要になるでしょう。

ご覧のように、レスポンスヘッダの中には、総ユーザ数やページ数などの情報が書かれています。
また、データの他のページへナビゲートすることを可能にするリンクもあります。
例えば、`http://localhost/users?page=2` にアクセスすれば、ユーザのデータの次のページを取得することが出来ます。

`fields` と `expand` パラメータを使えば、どのフィールドが結果に含まれるべきかを指定することも出来ます。
例えば、URL `http://localhost/users?fields=id,email` は、`id` と `email` のフィールドだけを返します。


> Info|情報: 気がついたかも知れませんが、`http://localhost/users` の結果は、いくつかの公開すべきでないフィールド、例えば `password_hash` や `auth_key` を含んでいます。
> 当然ながら、これらが API の結果に出現することは避けたいでしょう。
> [レスポンス形式の設定](rest-response-formatting.md) の節で説明されているように、これらのフィールドを除外することは出来ますし、また、除外しなければなりません。


## まとめ <span id="summary"></span>

Yii の RESTful API フレームワークを使う場合は、API エンドポイントをコントローラアクションの形式で実装します。
そして、コントローラを使って、単一タイプのリソースに対するエンドポイントを実装するアクションを編成します。

リソースは [[yii\base\Model]] クラスを拡張するデータモデルとして表現されます。
データベース (リレーショナルまたは NoSQL) を扱っている場合は、[[yii\db\ActiveRecord|ActiveRecord]] を使ってリソースを表現することが推奨されます。

[[yii\rest\UrlRule]] を使って API エンドポイントへのルーティングを簡単にすることが出来ます。

これは要求されてはいませんが、RESTful API は、保守を容易にするために、ウェブのフロントエンドやバックエンドとは別の独立したアプリケーションとして開発することが推奨されます。
