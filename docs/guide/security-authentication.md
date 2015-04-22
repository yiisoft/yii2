Authentication
==============

> Note: This section is under development.

Authentication is the process of determining the identity of a user. It typically uses an identifier 
(e.g. a username or an email address) and a secret token (e.g. a password or an access token) to judge 
if the user is the one whom he claims as. Authentication is the basis of more complex security-related
features, such as login.

In Yii, this entire process is performed semi-automatically, leaving the developer to merely implement
 [[yii\web\IdentityInterface]], the most important class in the authentication system. 
 Typically, implementation of `IdentityInterface` is accomplished using the `User` model.

You can find a fully featured example of authentication in the
[advanced project template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md). Below, only the interface methods are listed:

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
