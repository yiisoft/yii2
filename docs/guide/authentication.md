Authentication
==============

Authentication is the act of verifying who a user is, and is the basis of the login process. Typically, authentication uses an identifier--a username or email address--and password, submitted through a form. The application then compares this information against that previously stored.

In Yii all this is done semi-automatically, leaving the developer to merely  implement [[\yii\web\IdentityInterface]]. Typically, implementation is accomplished using the `User` model. You can find a full featured example in the 
[advanced application template](installation.md). Below only the interface methods are listed:

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

Two of the outlined methods are simple: `findIdentity` is provided with an  ID and returns a model instance represented by that ID. The `getId` method returns the ID itself.
Two of the other methods--`getAuthKey` and `validateAuthKey`--are used to provide extra security to the "remember me" cookie. `getAuthKey` should return a string that is unique for each user. A good idea is to save this value when the user is created by using Yii's `Security::generateRandomKey()`:

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

The `validateAuthKey` method just compares the `$authKey` variable, passed as parameter (itself retrieved from a cookie), with the value fetched from database.
