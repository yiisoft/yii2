Security
========

Hashing and verifying passwords
------------------------------

Most developers know that you cannot store passwords in plain text, but many believe it's safe to hash passwords using `md5` or `sha1`. There was a time when those hashing algorithms were sufficient, but modern hardware makes it possible to break those hashes very quickly using a brute force attack.

In order to truly secure user passwords, even in the worst case scenario (your database is broken into), you need to use a hashing algorithm that is resistant to brute force attacks. The best current choice is bcrypt. In PHP, you can create a bcrypt hash by using [crypt function](http://php.net/manual/en/function.crypt.php). However, this function is not easy to use properly, so Yii provides two helper functions for generating hash from
password and verifying existing hash.

When user sets his password we're taking password string from POST and then getting a hash:

```php
$hash = \yii\helpers\Security::generatePasswordHash($password);
```

The hash we've got is persisted to database to be used later.

Then when user is trying to log in we're verifying the password he entered against a hash that we've previously persisted:

```php
if(Security::validatePassword($password, $hash)) {
	// all good, logging user in
}
else {
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

