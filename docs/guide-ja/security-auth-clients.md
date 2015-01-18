Auth クライアント
=================

Yii は、[OpenID](http://openid.net/)、[OAuth](http://oauth.net/) または [OAuth2](http://oauth.net/2/) のコンシューマとして、外部サービスを使用して認証 および/または 権限付与を行うことを可能にする公式エクステンションを提供しています。

エクステンションをインストールする
---------------------------------

エクステンションをインストールするためには、Composer を使います。次のコマンドを実行します。

```
composer require --prefer-dist yiisoft/yii2-authclient "*"
```

または、あなたの composer.json の `require` セクションに次の行を追加します。

```json
"yiisoft/yii2-authclient": "*"
```

クライアントを構成する
---------------------

エクステンションがインストールされた後に、Auth クライアントコレクションのアプリケーションコンポーネントをセットアップする必要があります。

```php
'components' => [
    'authClientCollection' => [
        'class' => 'yii\authclient\Collection',
        'clients' => [
            'google' => [
                'class' => 'yii\authclient\clients\GoogleOpenId'
            ],
            'facebook' => [
                'class' => 'yii\authclient\clients\Facebook',
                'clientId' => 'facebook_client_id',
                'clientSecret' => 'facebook_client_secret',
            ],
            // etc.
        ],
    ]
    ...
]
```

特別な設定なしに使用できる次のクライアントが提供されています。

- [[\yii\authclient\clients\Facebook|Facebook]]
- [[yii\authclient\clients\GitHub|GitHub]]
- Google ([[yii\authclient\clients\GoogleOpenId|OpenID]] または [[yii\authclient\clients\GoogleOAuth|OAuth]] で)
- [[yii\authclient\clients\LinkedIn|LinkedIn]]
- [[yii\authclient\clients\Live|Microsoft Live]]
- [[yii\authclient\clients\Twitter|Twitter]]
- [[yii\authclient\clients\VKontakte|VKontakte]]
- Yandex ([[yii\authclient\clients\YandexOpenId|OpenID]] または [[yii\authclient\clients\YandexOAuth|OAuth]] で)

それぞれのクライアントの構成は少しずつ異なります。
OAuth では、使おうとしているサービスからクライアント ID と秘密キーを取得することが必要です。
OpenID では、たいていの場合、何も設定しなくても動作します。


認証データを保存する
-------------------

外部サービスによって認証されたユーザを認識するために、最初の認証のときに提供された ID を保存し、以後の認証のときにはそれをチェックする必要があります。
ログインのオプションを外部サービスに限定するのは良いアイデアではありません。
外部サービスによる認証が失敗して、ユーザがログインする方法がなくなるかもしれないからです。
そんなことはせずに、外部認証と昔ながらのパスワードによるログインの両方を提供する方が良いです。

ユーザの情報をデータベースに保存しようとする場合、スキーマは次のようなものになります。

```sql
CREATE TABLE user (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username varchar(255) NOT NULL,
    auth_key varchar(32) NOT NULL,
    password_hash varchar(255) NOT NULL,
    password_reset_token varchar(255),
    email varchar(255) NOT NULL,
    status smallint(6) NOT NULL DEFAULT 10,
    created_at int(11) NOT NULL,
    updated_at int(11) NOT NULL
);

CREATE TABLE auth (
    id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id int(11) NOT NULL,
    source string(255) NOT NULL,
    source_id string(255) NOT NULL
);

ALTER TABLE auth ADD CONSTRAINT fk-auth-user_id-user-id
FOREIGN KEY user_id REFERENCES auth(id);
```

上記の SQL における `user` は、アドバンストアプリケーションテンプレートでユーザ情報を保存するために使われている標準的なテーブルです。
全てのユーザはそれぞれ複数の外部サービスを使って認証できますので、全ての `user` レコードはそれぞれ複数の `auth` レコードと関連を持ち得ます。
`auth` テーブルにおいて `client_name` は使用された認証プロバイダの名前であり、`client_id` はログイン成功後に外部サービスから提供される一意のユーザ識別子です。

上記で作成されたテーブルを使って `Auth` モデルを生成することが出来ます。これ以上の修正は必要ありません。


コントローラにアクションを追加する
--------------------------------

次のステップでは、ウェブのコントローラ、典型的には `SiteController` に [[yii\authclient\AuthAction]] を追加します。

```php
class SiteController extends Controller
{
    public function actions()
    {
        return [
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    public function onAuthSuccess($client)
    {
       $attributes = $client->getUserAttributes();

        /** @var Auth $auth */
        $auth = Auth::find()->where([
            'source' => $client->getId(),
            'source_id' => $attributes['id'],
        ])->one();

        if (Yii::$app->user->isGuest) {
            if ($auth) { // ログイン
                $user = $auth->user;
                Yii::$app->user->login($user);
            } else { // ユーザ登録
                if (User::find()->where(['email' => $attributes['email']])->exists()) {
                    Yii::$app->getSession()->setFlash('error', [
                        Yii::t('app', "{client} のアカウントと同じメールアドレスを持つユーザが既に存在しますが、まだそのアカウントとリンクされていません。リンクするために、まずメールアドレスを使ってログインしてください。", ['client' => $client->getTitle()]),
                    ]);
                } else {
                    $password = Yii::$app->security->generateRandomString(6);
                    $user = new User([
                        'username' => $attributes['login'],
                        'email' => $attributes['email'],
                        'password' => $password,
                    ]);
                    $user->generateAuthKey();
                    $user->generatePasswordResetToken();
                    $transaction = $user->getDb()->beginTransaction();
                    if ($user->save()) {
                        $auth = new Auth([
                            'user_id' => $user->id,
                            'source' => $client->getId(),
                            'source_id' => (string)$attributes['id'],
                        ]);
                        if ($auth->save()) {
                            $transaction->commit();
                            Yii::$app->user->login($user);
                        } else {
                            print_r($auth->getErrors());
                        }
                    } else {
                        print_r($user->getErrors());
                    }
                }
            }
        } else { // user already logged in
            if (!$auth) { // add auth provider
                $auth = new Auth([
                    'user_id' => Yii::$app->user->id,
                    'source' => $client->getId(),
                    'source_id' => $attributes['id'],
                ]);
                $auth->save();
            }
        }
    }
}
```

外部サービスによるユーザの認証が成功すると `successCallback` メソッドが呼ばれます。
`$client` インスタンスを通じて、外部サービスから受け取った情報を取得することが出来ます。
私たちの例では、次のことをしようとしています。

- ユーザがゲストであり、Auth にレコードが見つかった場合は、そのユーザをログインさせる。
- ユーザがログインしており、Auth にレコードが見つかった場合は、If user is logged in and record found in auth then try connecting additional account (save its data into auth table).
- If user is guest and record not found in auth then create new user and make a record in auth table. Then log in.

Although, all clients are different they shares same basic interface [[yii\authclient\ClientInterface]],
which governs common API.

Each client has some descriptive data, which can be used for different purposes:

- `id` - unique client id, which separates it from other clients, it could be used in URLs, logs etc.
- `name` - external auth provider name, which this client is match too. Different auth clients
  can share the same name, if they refer to the same external auth provider.
  For example: clients for Google OpenID and Google OAuth have same name "google".
  This attribute can be used inside the database, CSS styles and so on.
- `title` - user friendly name for the external auth provider, it is used to present auth client
  at the view layer.

Each auth client has different auth flow, but all of them supports `getUserAttributes()` method,
which can be invoked if authentication was successful.

This method allows you to get information about external user account, such as ID, email address,
full name, preferred language etc.

Defining list of attributes, which external auth provider should return, depends on client type:

- [[yii\authclient\OpenId]]: combination of `requiredAttributes` and `optionalAttributes`.
- [[yii\authclient\OAuth1]] and [[yii\authclient\OAuth2]]: field `scope`, note that different
  providers use different formats for the scope.

### Getting additional data via extra API calls

Both [[yii\authclient\OAuth1]] and [[yii\authclient\OAuth2]] provide method `api()`, which
can be used to access external auth provider REST API. However this method is very basic and
it may be not enough to access full external API functionality. This method is mainly used to
fetch the external user account data.

To use API calls, you need to setup [[yii\authclient\BaseOAuth::apiBaseUrl]] according to the
API specification. Then you can call [[yii\authclient\BaseOAuth::api()]] method:

```php
use yii\authclient\OAuth2;

$client = new OAuth2;

// ...

$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');
```

Adding widget to login view
---------------------------

There's ready to use [[yii\authclient\widgets\AuthChoice]] widget to use in views:

```php
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth'],
     'popupMode' => false,
]) ?>
```

Creating your own auth clients
------------------------------

You may create your own auth client for any external auth provider, which supports
OpenId or OAuth protocol. To do so, first of all, you need to find out which protocol is
supported by the external auth provider, this will give you the name of the base class
for your extension:

 - For OAuth 2 use [[yii\authclient\OAuth2]].
 - For OAuth 1/1.0a use [[yii\authclient\OAuth1]].
 - For OpenID use [[yii\authclient\OpenId]].

At this stage you can determine auth client default name, title and view options, declaring
corresponding methods:

```php
use yii\authclient\OAuth2;

class MyAuthClient extends OAuth2
{
    protected function defaultName()
    {
        return 'my_auth_client';
    }

    protected function defaultTitle()
    {
        return 'My Auth Client';
    }

    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 800,
            'popupHeight' => 500,
        ];
    }
}
```

Depending on actual base class, you will need to redeclare different fields and methods.

### [[yii\authclient\OpenId]]

All you need is to specify auth URL, by redeclaring `authUrl` field.
You may also setup default required and/or optional attributes.
For example:

```php
use yii\authclient\OpenId;

class MyAuthClient extends OpenId
{
    public $authUrl = 'https://www.my.com/openid/';

    public $requiredAttributes = [
        'contact/email',
    ];

    public $optionalAttributes = [
        'namePerson/first',
        'namePerson/last',
    ];
}
```

### [[yii\authclient\OAuth2]]

You will need to specify:

- Auth URL by redeclaring `authUrl` field.
- Token request URL by redeclaring `tokenUrl` field.
- API base URL by redeclaring `apiBaseUrl` field.
- User attribute fetching strategy by redeclaring `initUserAttributes()` method.

For example:

```php
use yii\authclient\OAuth2;

class MyAuthClient extends OAuth2
{
    public $authUrl = 'https://www.my.com/oauth2/auth';

    public $tokenUrl = 'https://www.my.com/oauth2/token';

    public $apiBaseUrl = 'https://www.my.com/apis/oauth2/v1';

    protected function initUserAttributes()
    {
        return $this->api('userinfo', 'GET');
    }
}
```

You may also specify default auth scopes.

> Note: Some OAuth providers may not follow OAuth standards clearly, introducing
  differences, and may require additional efforts to implement clients for.

### [[yii\authclient\OAuth1]]

You will need to specify:

- Auth URL by redeclaring `authUrl` field.
- Request token URL by redeclaring `requestTokenUrl` field.
- Access token URL by redeclaring `accessTokenUrl` field.
- API base URL by redeclaring `apiBaseUrl` field.
- User attribute fetching strategy by redeclaring `initUserAttributes()` method.

For example:

```php
use yii\authclient\OAuth1;

class MyAuthClient extends OAuth1
{
    public $authUrl = 'https://www.my.com/oauth/auth';

    public $requestTokenUrl = 'https://www.my.com/oauth/request_token';

    public $accessTokenUrl = 'https://www.my.com/oauth/access_token';

    public $apiBaseUrl = 'https://www.my.com/apis/oauth/v1';

    protected function initUserAttributes()
    {
        return $this->api('userinfo', 'GET');
    }
}
```

You may also specify default auth scopes.

> Note: Some OAuth providers may not follow OAuth standards clearly, introducing
  differences, and may require additional efforts to implement clients for.
