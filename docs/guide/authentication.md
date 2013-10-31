Authentication
==============

Authentication is basically what happens when one is trying to sign in. Typically login and passwords are read from
the form and then application checks if there's such user with such password.

In Yii all this is done semi-automatically and what's left to developer is to implement [[\yii\web\IdentityInterface]].
Typically it is being implemented in `User` model. You can find a full featured example in
[advanced application template](installation.md). Below only interface methods are listed:

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
		return static::find($id);
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

First two methods are simple. `findIdentity` given ID returns model instance while `getId` returns ID itself.
`getAuthKey` and `validateAuthKey` are used to provide extra security to the "remember me" cookie.
`getAuthKey` should return a string that is unique for each user. A good idea is to save this value when user is
created using `Security::generateRandomKey()`:

```php
public function beforeSave($insert)
{
	if (parent::beforeSave($insert)) {
		if ($this->isNewRecord) {
			$this->auth_key = Security::generateRandomKey();
		}
		return true;
	}
	return false;
}
```

`validateAuthKey` just compares `$authKey` passed as parameter (got from cookie) with the value got from database.
