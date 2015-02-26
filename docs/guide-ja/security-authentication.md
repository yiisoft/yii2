認証
====

> Note|注意: この節はまだ執筆中です。

認証はユーザが誰であるかを確認する行為であり、ログインプロセスの基礎となるものです。
典型的には、認証は、識別子 (ユーザ名またはメールアドレス) とパスワードの組み合わせを使用します。
ユーザはこれらの値をフォームを通じて送信し、アプリケーションは送信された情報を以前に (例えば、ユーザ登録時に) 保存された情報と比較します。

Yii では、このプロセス全体が半自動的に実行されます。
開発者に残されているのは、認証システムにおいて最も重要なクラスである [[yii\web\IdentityInterface]] を実装することだけです。
典型的には、`IdentityInterface` の実装は `User` モデルを使って達成されます。

十分な機能を有する認証の実例を [アドバンストアプリケーションテンプレート](tutorial-advanced-app.md) の中に見出すことが出来ます。
下記にインターフェイスのメソッドだけをリストします。

```php
class User extends ActiveRecord implements IdentityInterface
{
    // ...

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

概要を述べたメソッドのうち、二つは単純なものです。
`findIdentity` は ID の値を受け取って、その ID と関連付けられたモデルのインスタンスを返します。
`getId` メソッドは ID そのものを返します。
その他のメソッドのうち、二つのもの - `getAuthKey` と `validateAuthKey` - は、「次回から自動ログイン ("remember me")」のクッキーに対して追加のセキュリティを提供するために使われます。
`getAuthKey` メソッドは全てのユーザに対してユニークな文字列を返さなければなりません。
`Yii::$app->getSecurity()->generateRandomString()` を使うと、信頼性の高い方法でユニークな文字列を生成することが出来ます。
これをユーザのレコードの一部として保存しておくのは良いアイデアです。

```php
public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {
        if ($this->isNewRecord) {
            $this->auth_key = Yii::$app->getSecurity()->generateRandomString();
        }
        return true;
    }
    return false;
}
```

`validateAuthKey` メソッドでは、パラメータとして渡された `$authKey` 変数 (これ自体はクッキーから読み出されます) をデータベースから読み出された値と比較する必要があるだけです。
