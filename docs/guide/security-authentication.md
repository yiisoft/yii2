Authentication
==============

> Note: This section is under development.

Authentication is the act of verifying who a user is, and is the basis of the login process. Typically, authentication uses the combination of an identifier--a username or email address--and a password. The user submits these values  through a form, and the application then compares the submitted information against that previously stored (e.g., upon registration).

In Yii, this entire process is performed semi-automatically, leaving the developer to merely implement [[yii\web\IdentityInterface]], the most important class in the authentication system. Typically, implementation of `IdentityInterface` is accomplished using the `User` model.

You can find a fully featured example of authentication in the
[advanced project template](tutorial-advanced-app.md). Below, only the interface methods are listed:

```php
class User extends ActiveRecord implements IdentityInterface
{
    // ...

    /**
     * Finds an identity by the given ID.
     *
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
```

Two of the outlined methods are simple: `findIdentity` is provided with an  ID value and returns a model instance
associated with that ID. The `getId` method returns the ID itself. Two of the other methods – `getAuthKey` and
`validateAuthKey` – are used to provide extra security to the "remember me" cookie. The `getAuthKey` method should
return a string that is unique for each user. You can reliably create a unique string using
`Yii::$app->getSecurity()->generateRandomString()`. It's a good idea to also save this as part of the user's record:

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

The `validateAuthKey` method just needs to compare the `$authKey` variable, passed as a parameter (itself retrieved from a cookie), with the value fetched from the database.
