認証
====

認証は、ユーザが誰であるかを確認するプロセスです。
通常は、識別子 (ユーザ名やメールアドレスなど) と秘密のトークン (パスワードやアクセストークンなど) を使って、ユーザがそうであると主張する通りのユーザであるか否かを判断します。
認証がログイン機能の基礎となります。

Yii はさまざまなコンポーネントを結び付けてログインをサポートする認証フレームワークを提供しています。
このフレームワークを使用するために、あなたは主として次の仕事をする必要があります。

* [[yii\web\User|user]] アプリケーションコンポーネントを構成する。
* [[yii\web\IdentityInterface]] インタフェイスを実装するクラスを作成する。


## [[yii\web\User]] を構成する <span id="configuring-user"></span>

[[yii\web\User|user]] アプリケーションコンポーネントがユーザの認証状態を管理します。
実際の認証ロジックを含む [[yii\web\User::identityClass|ユーザ識別情報クラス]] は、あなたが指定しなければなりません。
下記のアプリケーション構成情報においては、[[yii\web\User|user]] の [[yii\web\User::identityClass|ユーザ識別情報クラス]] は `app\models\User` であると構成されています。
`app\models\User` の実装については、次の項で説明します。
  
```php
return [
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
        ],
    ],
];
```

## [[yii\web\IdentityInterface]] を実装する <span id="implementing-identity"></span>

[[yii\web\User::identityClass|ユーザ識別情報クラス]] が実装しなければならない [[yii\web\IdentityInterface]] は次のメソッドを含んでいます。

* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]: 指定されたユーザ ID を使ってユーザ識別情報クラスのインスタンスを探します。
  セッションを通じてログイン状態を保持する必要がある場合に、このメソッドが使用されます。
* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]: 指定されたアクセストークンを使ってユーザ識別情報クラスのインスタンスを探します。
  単一の秘密のトークンでユーザを認証する必要がある場合 (ステートレスな RESTful アプリケーションなどの場合) に、このメソッドが使用されます。
* [[yii\web\IdentityInterface::getId()|getId()]]: ユーザ識別情報クラスのインスタンスによって表されるユーザの ID を返します。
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]: クッキーベースのログインを検証するのに使用されるキーを返します。
  このキーがログインクッキーに保存され、後でサーバ側のキーと比較されて、ログインクッキーが有効であることが確認されます。
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]]: クッキーベースのログインキーを検証するロジックを実装します。

特定のメソッドが必要でない場合は、中身を空にして実装しても構いません。
例えば、あなたのアプリケーションが純粋なステートレス RESTful アプリケーションであるなら、実装する必要があるのは [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]] と [[yii\web\IdentityInterface::getId()|getId()]] だけであり、他のメソッドは全て中身を空にしておくことが出来ます。

次の例では、[[yii\web\User::identityClass|ユーザ識別情報クラス]] は、`user` データベーステーブルと関連付けられた [アクティブレコード](db-active-record.md) クラスとして実装されています。

```php
<?php

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }

    /**
     * 与えられた ID によってユーザ識別情報を探す
     *
     * @param string|integer $id 探すための ID
     * @return IdentityInterface|null 与えられた ID に合致する Identity オブジェクト
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * 与えられたトークンによってユーザ識別情報を探す
     *
     * @param string $token 探すためのトークン
     * @return IdentityInterface|null 与えられたトークンに合致する Identity オブジェクト
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 現在のユーザの ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string 現在のユーザの認証キー
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return boolean 認証キーが現在のユーザに対して有効か否か
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
```

前述のように、`getAuthKey()` と `validateAuthKey()` は、あなたのアプリケーションがクッキーベースのログイン機能を使用する場合にのみ実装する必要があります。
この場合、次のコードを使って、各ユーザに対して認証キーを生成して、`user` テーブルに保存しておくことが出来ます。

```php
class User extends ActiveRecord implements IdentityInterface
{
    ......
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}
```

> Note|注意: ユーザ識別情報クラスである `User` と [[yii\web\User]] を混同してはいけません。
  前者は認証のロジックを実装するクラスであり、普通は、ユーザの認証情報を保存する何らかの持続的ストレージと関連付けられた [アクティブレコード](db-active-record.md) クラスとして実装されます。
  後者はユーザの認証状態の管理に責任を持つアプリケーションコンポーネントです。


## [[yii\web\User]] を使う <span id="using-user"></span>

[[yii\web\User]] は、主として、`user` アプリケーションコンポーネントの形で使います。

現在のユーザの識別情報は、`Yii::$app->user->identity` という式を使って取得することが出来ます。
これは、現在ログインしているユーザの [[yii\web\User::identityClass|ユーザ識別情報クラス]] のインスタンスを返すか、現在のユーザが認証されていない (つまりゲストである) 場合は null を返します。
次のコードは、[[yii\web\User]] からその他の認証関連の情報を取得する方法を示すものです。

```php
// 現在のユーザの識別情報。ユーザが認証されていない場合は null
$identity = Yii::$app->user->identity;

// 現在のユーザの ID。ユーザが認証されていない場合は null
$id = Yii::$app->user->id;

// 現在のユーザがゲストである (認証されていない) かどうか
$isGuest = Yii::$app->user->isGuest;
```

ユーザをログインさせるためには、次のコードを使うことが出来ます。

```php
// 指定された username を持つユーザ識別情報を探す
// 必要ならパスワードをチェックしてもよいことに注意
$identity = User::findOne(['username' => $username]);

// ユーザをログインさせる
Yii::$app->user->login($identity);
```

[[yii\web\User::login()]] メソッドは現在のユーザの識別情報を [[yii\web\User]] にセットします。
セッションが [[yii\web\User::enableSession|有効]] にされている場合は、ユーザの認証状態がセッション全体を通じて保持されるように、ユーザ識別情報がセッションに保管されます。
クッキーベースのログイン (つまり "remember me"、「次回は自動ログイン」) が [[yii\web\User::enableAutoLogin|有効]] にされている場合は、ユーザ識別情報をクッキーにも保存して、クッキーが有効である限りは、ユーザの認証状態をクッキーから復元することが可能になります。

クッキーベースのログインを有効にするためには、アプリケーションの構成情報で [[yii\web\User::enableAutoLogin]] を true に構成する必要があります。
また、[[yii\web\User::login()]] メソッドを呼ぶときには、有効期間のパラメータを与える必要があります。

ユーザをログアウトさせるためには、単に次のように `logout()` を呼びます。

```php
Yii::$app->user->logout();
```

ユーザのログアウトはセッションが有効にされている場合にだけ意味があることに注意してください。
`logout()` メソッドは、ユーザ認証状態をメモリとセッションの両方から消去します。
そして、デフォルトでは、ユーザのセッションデータの *全て* を破壊します。
セッションデータを保持したい場合は、代りに、`Yii::$app->user->logout(false)` を呼ばなければなりません。


## 認証のイベント <span id="auth-events"></span>

[[yii\web\User]] クラスは、ログインとログアウトのプロセスで、いくつかのイベントを発生させます。

* [[yii\web\User::EVENT_BEFORE_LOGIN|EVENT_BEFORE_LOGIN]]: [[yii\web\User::login()]] の開始時に発生します。
  イベントハンドラがイベントの [[yii\web\UserEvent::isValid|isValid]] プロパティを false にセットした場合は、ログインのプロセスがキャンセルされます。
* [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]]: ログインが成功した時に発生します。
* [[yii\web\User::EVENT_BEFORE_LOGOUT|EVENT_BEFORE_LOGOUT]]: [[yii\web\User::logout()]] の開始時に発生します。
  イベントハンドラがイベントの [[yii\web\UserEvent::isValid|isValid]] プロパティを false にセットした場合は、ログアウトのプロセスがキャンセルされます。
* [[yii\web\User::EVENT_AFTER_LOGOUT|EVENT_AFTER_LOGOUT]]: ログアウトが成功した時に発生します。

これらのイベントに反応して、ログイン監査、オンラインユーザ統計などの機能を実装することが出来ます。
例えば、[[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]] のハンドラの中で、`user` テーブルにログインの日時と IP アドレスを記録することが出来ます。
