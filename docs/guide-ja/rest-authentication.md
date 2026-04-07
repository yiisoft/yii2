認証
====

ウェブ・アプリケーションとは異なり、RESTful API は通常はステート・レスです。
これは、セッションやクッキーは使用すべきでないことを意味します。
従って、ユーザの認証ステータスをセッションやクッキーで保持することが出来ないため、全てのリクエストに何らかの認証情報を付加する必要があります。
通常使われるのは、ユーザを認証するための秘密のアクセス・トークンを全てのリクエストとともに送信する方法です。
アクセス・トークンはユーザを一意に特定して認証することが出来るものですので、
**API リクエストは、中間者攻撃 (man-in-the-middle attack) を防止するために、常に HTTPS 経由で送信されなければなりません**。

アクセス・トークンを送信するには、いくつかの異なる方法があります。

* [HTTP Basic 認証](https://ja.wikipedia.org/wiki/Basic%E8%AA%8D%E8%A8%BC): アクセス・トークンはユーザ名として送信されます。
  この方法は、アクセス・トークンを API コンシューマ側で安全に保存することが出来る場合、
  例えば API コンシューマがサーバ上で走るプログラムである場合などのみに使用されるべきです。
* クエリ・パラメータ: アクセス・トークンは API の URL、例えば、`https://example.com/users?access-token=xxxxxxxx`
  でクエリ・パラメータとして送信されます。
  ほとんどのウェブ・サーバはクエリ・パラメータをサーバのログに記録するため、この手法は、
  アクセス・トークンを HTTP ヘッダを使って送信することができない `JSONP` リクエストに応答するために主として使用されるべきです。
* [OAuth 2](https://oauth.net/2/): OAuth2 プロトコルに従って、
  アクセス・トークンはコンシューマによって権限付与サーバから取得され、[HTTP Bearer Tokens](https://datatracker.ietf.org/doc/html/rfc6750) 経由で
  API サーバに送信されます。

Yii は上記の全ての認証方法をサポートしています。新しい認証方法を作成することも簡単に出来ます。

あなたの API に対して認証を有効にするためには、次のステップを実行します。

1. `user` [アプリケーション・コンポーネント](structure-application-components.md) を構成します。
   - [[yii\web\User::enableSession|enableSession]] プロパティを `false` に設定します。
   - [[yii\web\User::loginUrl|loginUrl]] プロパティを `null` に設定し、ログインページにリダイレクトする代りに HTTP 403 エラーを表示します。
2. REST コントローラ・クラスにおいて、`authenticator` ビヘイビアを構成することによって、
  どの認証方法を使用するかを指定します。
3. [[yii\web\User::identityClass|ユーザ・アイデンティティ・クラス]] において [[yii\web\IdentityInterface::findIdentityByAccessToken()]] を実装します。

ステップ 1 は必須ではありませんが、ステート・レスであるべき RESTful API のために推奨されます。
[[yii\web\User::enableSession|enableSession]] が `false` である場合、ユーザの認証ステータスがセッションを使ってリクエストをまたいで存続することはありません。
その代りに、すべてのリクエストに対して認証が実行されます。このことは、ステップ 2 と 3 によって達成されます。

> Tip: RESTful API をアプリケーションの形式で開発する場合は、アプリケーションの構成情報で `user` アプリケーション・コンポーネント(structure-application-components.md) 
>  [[yii\web\User::enableSession|enableSession]] プロパティを構成することが出来ます。
> RESTful API をモジュールとして開発する場合は、次のように、モジュールの `init()` メソッドに一行を追加することが出来ます。
>
> ```php
> public function init()
> {
>     parent::init();
>     \Yii::$app->user->enableSession = false;
> }
> ```

例えば、HTTP Basic 認証を使う場合は、`authenticator` ビヘイビアを次のように構成することが出来ます。

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HttpBasicAuth::class,
    ];
    return $behaviors;
}
```

上で説明した三つの認証方法を全てサポートしたい場合は、次のように `CompositeAuth` を使うことが出来ます。

```php
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => CompositeAuth::class,
        'authMethods' => [
            HttpBasicAuth::class,
            HttpBearerAuth::class,
            QueryParamAuth::class,
        ],
    ];
    return $behaviors;
}
```

`authMethods` の各要素は、認証方法クラスの名前であるか、構成情報配列でなければなりません。


`findIdentityByAccessToken()` の実装はアプリケーション固有のものです。
例えば、各ユーザが一つだけアクセス・トークンを持ち得るような単純なシナリオでは、アクセス・トークンをユーザのテーブルの `access_token` カラムに保存することが出来ます。
そうすれば、次のように、`findIdentityByAccessToken()` メソッドを `User` クラスにおいて簡単に実装することが出来ます。

```php
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
}
```

上記のように認証が有効化された後は、全ての API リクエストに対して、リクエストされたコントローラ
 `beforeAction()` の段階でユーザを認証することを試みます。

認証が成功すると、コントローラはその他のチェック (レート制限、権限付与など) をしてから、アクションを実行します。
認証されたユーザのアイデンティティは `Yii::$app->user->identity` によって取得することが出来ます。

認証が失敗したときは、HTTP ステータス 401 およびその他の適切なヘッダ (HTTP Basic 認証に対する `WWW-Authenticate` ヘッダなど)
を持つレスポンスが送り返されます。


## 権限付与 <span id="authorization"></span>

ユーザが認証された後、おそらくは、リクエストされたリソースに対してリクエストされたアクションを実行する許可を
彼または彼女が持っているかどうかをチェックしたいでしょう。
*権限付与* と呼ばれるこのプロセスについては、[権限付与](security-authorization.md) のセクションで詳細に説明されています。

あなたのコントローラが [[yii\rest\ActiveController]] から拡張したものである場合は、
[[yii\rest\ActiveController::checkAccess()|checkAccess()]] メソッドをオーバーライドして権限付与のチェックを実行することが出来ます。
このメソッドが [[yii\rest\ActiveController]] によって提供されている内蔵のアクションから呼び出されます。
