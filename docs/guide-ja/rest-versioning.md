バージョン管理
==============

良い API は*バージョン管理* されています。すなわち、一つのバージョンを絶え間なく変更するのではなく、変更と新機能は API の新しいバージョンにおいて実装されます。
クライアント・サイドとサーバ・サイドの両方のコードを完全に制御できるウェブ・アプリケーションとは違って、API はあなたの制御が及ばないクライアントによって使用されることを想定したものです。このため、API の後方互換性 (BC) は、可能な限り保たれなければなりません。
BC を損なうかも知れない変更が必要な場合は、それを API の新しいバージョンにおいて導入し、バージョン番号を上げるべきです。そうすれば、既存のクライアントは、API の古いけれども動作するバージョンを使い続けることが出来ますし、新しいまたはアップグレードされたクライアントは、新しい API バージョンで新しい機能を使うことが出来ます。

> Tip: API のバージョン番号の設計についての詳細な情報は
  [Semantic Versioning](https://semver.org/) を参照してください。

API のバージョン管理を実装する方法としてよく使われるのは、バージョン番号を API の URL に埋め込む方法です。
例えば、`https://example.com/v1/users` が API バージョン 1 の `/users` エンド・ボイントを指す、というものです。

API のバージョン管理のもう一つの方法は、最近流行しているものですが、バージョン番号を HTTP リクエスト・ヘッダに付ける方法です。
これは、典型的には、`Accept` ヘッダによって行われます。

```
// パラメータによって
Accept: application/json; version=v1
// ベンダーのコンテント・タイプによって
Accept: application/vnd.company.myapp-v1+json
```

どちらの方法にも長所と短所があり、それぞれのアプローチに対して多くの議論があります。
下記では、この二つの方法をミックスした API バージョン管理の実際的な戦略を紹介します。

* API 実装の各メジャー・バージョンを独立したモジュールに置き、モジュールの ID はメジャー・バージョン番号 (例えば `v1` や `v2`) とします。
  当然ながら、API の URL はメジャー・バージョン番号を含むことになります。
* 各メジャー・バージョンの中では (従って対応するモジュールの中では) `Accept` HTTP リクエスト・ヘッダを使ってマイナー・バージョン番号を決定し、
  マイナー・バージョンに応じたレスポンスのための条件分岐コードを書きます。

メジャー・バージョンを提供する各モジュールは、それぞれ、指定されたバージョンのためのリソースとコントローラのクラスを含んでいなければなりません。
コードの責任範囲をより良く分離するために、共通の基底のリソースとコントローラのクラスを保持して、
それをバージョンごとの個別のモジュールでサブ・クラス化することが出来ます。
サブ・クラスの中で、`Model::fields()` のような具体的なコードを実装します。

あなたのコードを次のように編成することが出来ます。

```
api/
    common/
        controllers/
            UserController.php
            PostController.php
        models/
            User.php
            Post.php
    modules/
        v1/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
            Module.php
        v2/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
            Module.php
```

アプリケーションの構成情報は次のようなものになります。

```php
return [
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'app\modules\v2\Module',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/user', 'v1/post']],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v2/user', 'v2/post']],
            ],
        ],
    ],
];
```

上記のコードの結果として、`https://example.com/v1/users` はバージョン 1 のユーザ一覧を返し、
`https://example.com/v2/users` はバージョン 2 のユーザ一覧を返すことになります。

モジュール化のおかげで、異なるメジャー・バージョンのためのコードを綺麗に分離することが出来ます。
しかし、モジュール化しても、共通の基底クラスやその他の共有リソースを通じて、モジュール間でコードを再利用することは引き続いて可能です。

マイナー・バージョン番号を扱うためには、[[yii\filters\ContentNegotiator|contentNegotiator]]
ビヘイビアによって提供されるコンテント・ネゴシエーションの機能を利用することが出来ます。
`contentNegotiator` ビヘイビアは、どのコンテント・タイプをサポートするかを決定するときに、
[[yii\web\Response::acceptParams]] プロパティをセットします。

例えば、リクエストが HTTP ヘッダ `Accept: application/json; version=v1` を伴って送信された場合、
コンテント・ネゴシエーションの後では、[[yii\web\Response::acceptParams]] に `['version' => 'v1']` という値が含まれています。

`acceptParams` のバージョン情報に基づいて、アクション、リソース・クラス、シリアライザなどの個所で条件付きのコードを書いて、
適切な機能を提供することが出来ます。

マイナー・バージョンは、定義上、後方互換性を保つことを要求するものですので、コードの中でバージョンチェックをする個所はそれほど多くないものと期待されます。
そうでない場合は、たいていは、新しいメジャー・バージョンを作成する必要がある、ということです。
