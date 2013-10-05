Security
========

Hashing and verifying passwords
------------------------------

Most developers know that you cannot store passwords in plain text, but many believe it's safe to hash passwords using `md5` or `sha1`. There was a time when those hashing algorithms were sufficient, but modern hardware makes it possible to break those hashes very quickly using a brute force attack.

In order to truly secure user passwords, even in the worst case scenario (your database is broken into), you need to use a hashing algorithm that is resistant to brute force attacks. The best current choice is `bcrypt`. In PHP, you can create a `bcrypt` hash by using the [crypt function](http://php.net/manual/en/function.crypt.php). However, this function is not easy to use properly, so Yii provides two helper functions to make securely generating and verifying hashes easier.

When a user provides a password for the first time (e.g., upon registration), the password needs to be hashed:

```php
$hash = \yii\helpers\Security::generatePasswordHash($password);
```

The hash would then be associated with the model, so that it will be stored in the database for later use.

When user attempts to log in, the submitted log in password must be verified against the previously hashed and stored password:

```php
use \yii\helpers;
if (Security::validatePassword($password, $hash)) {
	// all good, logging user in
} else {
	// wrong password
}
```


Random data
-----------

Random data is useful in many cases. For example, when resetting a password via email you need to generate a token,
save it to database and send it via email to end user so he's able to prove that email belongs to him. It is very
important for this token to be truly unique else there will be a possibility to predict a value and reset another user's
password.

Yii security helper makes it as simple as:

```php
$key = \yii\helpers\Security::generateRandomKey();
```

Encryption and decryption
-------------------------

In order to encrypt data so only person knowing a secret passphrase or having a secret key will be able to decrypt it.
For example, we need to store some information in our database but we need to make sure only user knowing a secret code
can view it (even if database is leaked):


```php
// $data and $secretWord are from the form
$encryptedData = \yii\helpers\Security::encrypt($data, $secretWord);
// store $encryptedData to database
```

Then when user want to read it:

```php
// $secretWord is from the form, $encryptedData is from database
$data = \yii\helpers\Security::decrypt($encryptedData, $secretWord);
```

Making sure data wasn't modified
--------------------------------

hashData()
validateData()


Securing Cookies
----------------

- validation
- httpOnly

See also
--------

- [Views security](view.md#security)

