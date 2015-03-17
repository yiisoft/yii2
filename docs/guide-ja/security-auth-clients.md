認証クライアント
================

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
----------------------

エクステンションがインストールされた後に、認証クライアントコレクションのアプリケーションコンポーネントをセットアップする必要があります。

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
--------------------

外部サービスによって認証されたユーザを認識するために、最初の認証のときに提供された ID を保存し、以後の認証のときにはそれをチェックする必要があります。
ログインのオプションを外部サービスに限定するのは良いアイデアではありません。
外部サービスによる認証が失敗して、ユーザがログインする方法がなくなるかもしれないからです。
そんなことはせずに、外部認証と昔ながらのパスワードによるログインの両方を提供する方が適切です。

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
`auth` テーブルにおいて `source` は使用される認証プロバイダの名前であり、`source_id` はログイン成功後に外部サービスから提供される一意のユーザ識別子です。

上記で作成されたテーブルを使って `Auth` モデルを生成することが出来ます。これ以上の修正は必要ありません。


コントローラにアクションを追加する
----------------------------------

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
        } else { // ユーザは既にログインしている
            if (!$auth) { // 認証プロバイダを追加
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

- ユーザがゲストであり、auth にレコードが見つかった場合は、そのユーザをログインさせる。
- ユーザがゲストであり、auth にレコードが見つからなかった場合は、新しいユーザを作成して、auth テーブルにレコードを作成する。そして、ログインさせる。
- ユーザがログインしており、auth にレコードが見つからなかった場合は、追加のアカウントにも接続するようにする (そのデータを auth テーブルに保存する)。

全ての Auth クライアントには違いがありますが、同じインタフェイス  [[yii\authclient\ClientInterface]] を共有し、共通の API によって管理されます。

各クライアントは、異なる目的に使用できるいくつかの説明的なデータを持っています。

- `id` - クライアントを他のクライアントから区別する一意の ID。
  URL やログに使うことが出来ます。
- `name` - このクライアントが属する外部認証プロバイダの名前。
  認証クライアントが異なっても、同じ外部認証プロバイダを参照している場合は、同じ名前になることがあります。
  例えば、Google OpenID のクライアントと Google OAuth のクライアントは同じ名前 "google" を持ちます。
  この属性は内部的にデータベースや CSS スタイルなどにおいて使用することが出来ます。
- `title` - 外部認証プロバイダのユーザフレンドリな名前。ビューのレイヤにおいて認証クライアントを表示するのに使用されます。

それぞれの認証クライアントは異なる認証フローを持ちますが、すべてのものが `getUserAttributes()` メソッドをサポートしており、認証が成功した後にこのメソッドを呼び出すことが出来ます。

このメソッドによって、外部のユーザアカウントの情報、例えば、ID、メールアドレス、フルネーム、優先される言語などを取得することが出来ます。
ただし、プロバイダごとに利用できるフィールドの有無や名前が異なることに注意してください。

外部認証プロバイダが返すべき属性を定義するリストは、クライアントのタイプに依存します。

- [[yii\authclient\OpenId]]: `requiredAttributes` と `optionalAttributes` の組み合わせ。
- [[yii\authclient\OAuth1]] と [[yii\authclient\OAuth2]]: `scope` フィールド。
  プロバイダによってスコープの形式が異なることに注意。

### API 呼び出しによって追加のデータを取得する

[[yii\authclient\OAuth1]] と [[yii\authclient\OAuth2]] は、ともに、`api()` メソッドをサポートしており、これによって外部認証プロバイダの REST API にアクセスすることが出来ます。
ただし、このメソッドは非常に基本的なもので、外部 API の完全な機能にアクセスするためには、十分なものではありません。
このメソッドは、主として、外部のユーザアカウントの情報を取得するために使用されます。

API の呼び出しを使用するためには、API の仕様に従って [[yii\authclient\BaseOAuth::apiBaseUrl]] をセットアップする必要があります。
そうすれば [[yii\authclient\BaseOAuth::api()]] メソッドを呼ぶことが出来ます。

```php
use yii\authclient\OAuth2;

$client = new OAuth2;

// ...

$client->apiBaseUrl = 'https://www.googleapis.com/oauth2/v1';
$userInfo = $client->api('userinfo', 'GET');
```

ログインビューにウィジェットを追加する
--------------------------------------

そのまま使える [[yii\authclient\widgets\AuthChoice]] ウィジェットをビューで使用することが出来ます。

```php
<?= yii\authclient\widgets\AuthChoice::widget([
     'baseAuthUrl' => ['site/auth'],
     'popupMode' => false,
]) ?>
```

あなた自身の認証クライアントを作成する
--------------------------------------

どの外部認証プロバイダでも、あなた自身の認証クライアントを作成して、OpenID または OAuth プロトコルをサポートすることが出来ます。
そうするためには、最初に、外部認証プロバイダによってどのプロトコルがサポートされているかを見出す必要があります。
それによって、あなたのエクステンションの基底クラスの名前が決ります。

 - OAuth 2 のためには [[yii\authclient\OAuth2]] を使います。
 - OAuth 1/1.0a のためには [[yii\authclient\OAuth1]] を使います。
 - OpenID のためには [[yii\authclient\OpenId]] を使います。

この段階で、対応するメソッドを宣言することによって、認証クライアントのデフォルトの名前、タイトル、および、ビューオプションを決定することが出来ます。

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

使用する基底クラスによって、宣言し直さなければならないフィールドやメソッドが異なります。

### [[yii\authclient\OpenId]]

必要なことは、`authUrl` フィールドを宣言し直して URL を指定することだけです。
デフォルトの 必須属性 および/または オプション属性を設定することも可能です。
例えば、

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

以下のものを指定する必要があります。

- 認証 URL - `authUrl` フィールド。
- トークンリクエスト URL - `tokenUrl` フィールド。
- API のベース URL - `apiBaseUrl` フィールド。
- ユーザ属性取得ストラテジー - `initUserAttributes()` メソッド。

例えば、

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

デフォルトの auth スコープを指定することも出来ます。

> Note|注意: OAuth プロバイダの中には、OAuth の標準を厳格に遵守せず、標準と異なる仕様を導入しているものもあります。
  そのようなものに対してクライアントを実装するためには、追加の労力が必要になることがあります。

### [[yii\authclient\OAuth1]]

以下のものを指定する必要があります。

- 認証 URL - `authUrl` フィールド。
- リクエストトークン URL - `requestTokenUrl` フィールド。
- アクセストークン URL - `accessTokenUrl` フィールド。
- API のベース URL - `apiBaseUrl` フィールド。
- ユーザ属性取得ストラテジー - `initUserAttributes()` メソッド。

例えば、

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

デフォルトの auth スコープを指定することも出来ます。

> Note|注意: OAuth プロバイダの中には、OAuth の標準を厳格に遵守せず、標準と異なる仕様を導入しているものもあります。
  そのようなものに対してクライアントを実装するためには、追加の労力が必要になることがあります。
