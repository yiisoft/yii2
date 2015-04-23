Authentication
==============

> Note: This section is under development.

Authentication is the process of verifying the identity of a user. It usually uses an identifier 
(e.g. a username or an email address) and a secret token (e.g. a password or an access token) to judge 
if the user is the one whom he claims as. Authentication is the basis of the login feature.

Yii provides an authentication framework which wires up various components to support login. To use this framework, 
you mainly need to do the following work:
 
* Configure the [[yii\web\User|user]] application component;
* Create a class that implements the [[yii\web\IdentityInterface]] interface.


## Configuring [[yii\web\User]] <span id="configuring-user"></span>

The [[yii\web\User|user]] application component manages the user authentication status. With the help of
an [[yii\web\User::identityClass|identity class]], it implements the full login workflow. In the following
application configuration, the [[yii\web\User::identityClass|identity class]] for [[yii\web\User|user]] 
is configured to be `app\models\User` whose implementation is explained in the next subsection:
  
```php
return [
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
        ],
    ],
];
```


## Implementing [[yii\web\IdentityInterface]] <span id="implementing-identity"></span>

The [[yii\web\User::identityClass|identity class]] must implement the [[yii\web\IdentityInterface]] which
requires the implementation of the following methods:

* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]: it looks for an instance of the identity
  class using the specified user ID. This method is used when you need to maintain logic status via session.
* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]: it looks for
  an instance of the identity class using the specified access token. This method is used when you need
  to authenticate a user by a single secret token (e.g. in a stateless RESTful application).
* [[yii\web\IdentityInterface::getId()|getId()]]: it returns the ID of the user represented by this identity instance.
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]: it returns a key used to verify cookie-based login.
  The key is stored in the login cookie and will be later compared with the server-side version to make
  sure the login cookie is valid.
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]]: it implements the logic for verifying
  the cookie-based login key.

As you can see, these methods are required by different features. If you do not need a particular feature,
you may implement the corresponding methods with an empty body. For example, if your application is a pure
stateless RESTful application, you would only need to implement [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]
and [[yii\web\IdentityInterface::getId()|getId()]].


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
