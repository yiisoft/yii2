ルーティング
============

リソースとコントローラのクラスが準備できたら、通常のウェブ・アプリケーションと同じように、
`http://localhost/index.php?r=user/create` というような URL を使ってリソースにアクセスすることが出来ます。

実際には、綺麗な URL を有効にして HTTP 動詞を利用したいというのが普通でしょう。
例えば、`POST /users` というリクエストが `user/create` アクションへのアクセスを意味するようにする訳です。
これは、アプリケーションの構成情報で `urlManager` [アプリケーション・コンポーネント](structure-application-components.md)
を次のように構成することによって容易に達成することが出来ます。

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

ウェブ・アプリケーションの URL 管理と比べたときに、上記で目に付く新しいことは、
RESTful API リクエストのルーティングに [[yii\rest\UrlRule]] を使用していることです。
この特殊な URL 規則クラスが、一揃いの子 URL 規則を作成して、指定されたコントローラのルーティングと URL 生成をサポートします。
例えば、上記のコードは、おおむね下記の規則と等価です。

```php
[
    'PUT,PATCH users/<id>' => 'user/update',
    'DELETE users/<id>' => 'user/delete',
    'GET,HEAD users/<id>' => 'user/view',
    'POST users' => 'user/create',
    'GET,HEAD users' => 'user/index',
    'users/<id>' => 'user/options',
    'users' => 'user/options',
]
```

そして、次の API エンド・ボイントがこの規則によってサポートされます。

* `GET /users`: 全てのユーザをページごとにリストする。
* `HEAD /users`: ユーザ一覧の概要を示す。
* `POST /users`: 新しいユーザを作成する。
* `GET /users/123`: ユーザ 123 の詳細を返す。
* `HEAD /users/123`: ユーザ 123 の概要情報を示す。
* `PATCH /users/123` と `PUT /users/123`: ユーザ 123 を更新する。
* `DELETE /users/123`: ユーザ 123 を削除する。
* `OPTIONS /users`: エンド・ボイント `/users` に関してサポートされる動詞を示す。
* `OPTIONS /users/123`: エンド・ボイント `/users/123` に関してサポートされる動詞を示す。

`only` および `except` オプションを構成すると、それぞれ、どのアクションをサポートするか、
また、どのアクションを無効にするかを明示的に指定することが出来ます。例えば、

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'except' => ['delete', 'create', 'update'],
],
```

また、`patterns` あるいは `extraPatterns` を構成して、既存のパターンを再定義したり、この規則によってサポートされる新しいパターンを追加したりすることも出来ます。
例えば、エンド・ボイント `GET /users/search` によって新しいアクション `search` をサポートするためには、`extraPatterns` オプションを次のように構成します。

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'extraPatterns' => [
        'GET search' => 'search',
    ],
]
```

エンド・ボイントの URL ではコントローラ ID `user` が `users` という複数形で出現していることに気が付いたかもしれません。
これは、[[yii\rest\UrlRule]] が子 URL 規則を作るときに、コントローラの ID を自動的に複数形にするためです。
この振舞いは [[yii\rest\UrlRule::pluralize]] を `false` に設定することで無効にすることが出来ます。

> Info: コントローラ ID の複数形化は [[yii\helpers\Inflector::pluralize()]] によって行われます。
  このメソッドは特殊な複数形の規則を考慮します。例えば、`box` という単語の複数形は `boxs` ではなく `boxes` になります。

自動的な複数形化があなたの要求を満たさない場合は、[[yii\rest\UrlRule::controller]] プロパティを構成して、
エンド・ボイント URL で使用される名前とコントローラ ID の対応を明示的に指定することも可能です。
例えば、次のコードはエンド・ボイント名 `u` をコントローラ ID `user` に割り当てます。
 
```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => ['u' => 'user'],
]
```

## 内蔵の規則に対する追加の構成

[[yii\rest\UrlRule]] の中に含まれるそれぞれの規則に対して適用される追加の構成を指定すると役に立つことがあります。
`expand` パラメータに対するデフォルトの指定が良い例です。

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => ['user'],
    'ruleConfig' => [
        'class' => 'yii\web\UrlRule',
        'defaults' => [
            'expand' => 'profile',
        ]
    ],
],
```
