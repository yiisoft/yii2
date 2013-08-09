Security
========

Hashing and verifyig passwords
------------------------------

It is important not to store passwords in plain text but, contrary to popular belief, just using `md5` or `sha1` to
compute and verify hashes isn't a good way either. Modern hardware allows to brute force these very fast.

In order to truly secure user passwords even in case your database is leaked you need to use a function that is resistant
to brute-force such as bcrypt. In PHP it can be achieved by using [crypt function](http://php.net/manual/en/function.crypt.php)
but since usage isn't trivial and one can easily misuse it, Yii provides two helper functions for generating hash from
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