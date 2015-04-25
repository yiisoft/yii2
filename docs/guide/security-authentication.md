Authentication
==============

Authentication is the process of verifying the identity of a user. It usually uses an identifier 
(e.g. a username or an email address) and a secret token (e.g. a password or an access token) to judge 
if the user is the one whom he claims as. Authentication is the basis of the login feature.

Yii provides an authentication framework which wires up various components to support login. To use this framework, 
you mainly need to do the following work:
 
* Configure the [[yii\web\User|user]] application component;
* Create a class that implements the [[yii\web\IdentityInterface]] interface.


## Configuring [[yii\web\User]] <span id="configuring-user"></span>

The [[yii\web\User|user]] application component manages the user authentication status. It requires you to 
specify an [[yii\web\User::identityClass|identity class]] which contains the actual authentication logic.
In the following application configuration, the [[yii\web\User::identityClass|identity class]] for
[[yii\web\User|user]] is configured to be `app\models\User` whose implementation is explained in 
the next subsection:
  
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

The [[yii\web\User::identityClass|identity class]] must implement the [[yii\web\IdentityInterface]] which contains
the following methods:

* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]: it looks for an instance of the identity
  class using the specified user ID. This method is used when you need to maintain the login status via session.
* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]: it looks for
  an instance of the identity class using the specified access token. This method is used when you need
  to authenticate a user by a single secret token (e.g. in a stateless RESTful application).
* [[yii\web\IdentityInterface::getId()|getId()]]: it returns the ID of the user represented by this identity instance.
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]: it returns a key used to verify cookie-based login.
  The key is stored in the login cookie and will be later compared with the server-side version to make
  sure the login cookie is valid.
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]]: it implements the logic for verifying
  the cookie-based login key.

If a particular method is not needed, you may implement it with an empty body. For example, if your application 
is a pure stateless RESTful application, you would only need to implement [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]
and [[yii\web\IdentityInterface::getId()|getId()]] while leaving all other methods with an empty body.

In the following example, an [[yii\web\User::identityClass|identity class]] is implemented as 
an [Active Record](db-active-record.md) class associated with the `user` database table.

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

As explained previously, you only need to implement `getAuthKey()` and `validateAuthKey()` if your application
uses cookie-based login feature. In this case, you may use the following code to generate an auth key for each
user and store it in the `user` table:

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

> Note: Do not confuse the `User` identity class with [[yii\web\User]]. The former is the class implementing
  the authentication logic. It is often implemented as an [Active Record](db-active-record.md) class associated
  with some persistent storage for storing the user credential information. The latter is an application component
  class responsible for managing the user authentication state.


## Using [[yii\web\User]] <span id="using-user"></span>

You mainly use [[yii\web\User]] in terms of the `user` application component. 

You can detect the identity of the current user using the expression `Yii::$app->user->identity`. It returns
an instance of the [[yii\web\User::identityClass|identity class]] representing the currently logged-in user,
or null if the current user is not authenticated (meaning a guest). The following code shows how to retrieve
other authentication-related information from [[yii\web\User]]:

```php
// the current user identity. Null if the user is not authenticated.
$identity = Yii::$app->user->identity;

// the ID of the current user. Null if the user not authenticated.
$id = Yii::$app->user->id;

// whether the current user is a guest (not authenticated)
$isGuest = Yii::$app->user->isGuest;
```

To login a user, you may use the following code:

```php
// find a user identity with the specified username.
// note that you may want to check the password if needed
$identity = User::findOne(['username' => $username]);

// logs in the user 
Yii::$app->user->login($identity);
```

The [[yii\web\User::login()]] method sets the identity of the current user to the [[yii\web\User]]. If session is 
[[yii\web\User::enableSession|enabled]], it will keep the identity in the session so that the user
authentication status is maintained throughout the whole session. If cookie-based login (i.e. "remember me" login)
is [[yii\web\User::enableAutoLogin|enabled]], it will also save the identity in a cookie so that
the user authentication status can be recovered from the cookie as long as the cookie remains valid.

In order to enable cookie-based login, you need to configure [[yii\web\User::enableAutoLogin]] to be
true in the application configuration. You also need to provide a duration time parameter when calling 
the [[yii\web\User::login()]] method. 

To logout a user, simply call

```php
Yii::$app->user->logout();
```

Note that logging out a user is only meaningful when session is enabled. The method will clean up
the user authentication status from both memory and session. And by default, it will also destroy *all*
user session data. If you want to keep the session data, you should call `Yii::$app->user->logout(false)`, instead.


## Authentication Events <span id="auth-events"></span>

The [[yii\web\User]] class raises a few events during the login and logout processes. 

* [[yii\web\User::EVENT_BEFORE_LOGIN|EVENT_BEFORE_LOGIN]]: raised at the beginning of [[yii\web\User::login()]].
  If the event handler sets the [[yii\web\UserEvent::isValid|isValid]] property of the event object to be false,
  the login process will be cancelled. 
* [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]]: raised after a successful login.
* [[yii\web\User::EVENT_BEFORE_LOGOUT|EVENT_BEFORE_LOGOUT]]: raised at the beginning of [[yii\web\User::logout()]].
  If the event handler sets the [[yii\web\UserEvent::isValid|isValid]] property of the event object to be false,
  the logout process will be cancelled. 
* [[yii\web\User::EVENT_AFTER_LOGOUT|EVENT_AFTER_LOGOUT]]: raised after a successful logout.

You may respond to these events to implement features such as login audit, online user statistics. For example,
in the handler for [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]], you may record the login time and IP
address in the `user` table.
