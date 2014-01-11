Security
========

Good security is vital to the health and success of any website. Unfortunately, many developers  cut corners when it comes to security, either due to a lack of understanding or because implementation is too large of a hurdle. To make your Yii-based site as secure as possible, Yii has baked in several excellent and easy to use security features.

Hashing and verifying passwords
-------------------------------

Most developers know that passwords cannot be stored in plain text, but many developers believe it's still safe to hash passwords using `md5` or `sha1`. There was a time when those hashing algorithms were sufficient, but modern hardware makes it possible to break those hashes very quickly using a brute force attack.

In order to truly secure user passwords, even in the worst case scenario (your database is broken into), you need to use a hashing algorithm that is resistant to brute force attacks. The best current choice is `bcrypt`. In PHP, you can create a `bcrypt` hash  using the [crypt function](http://php.net/manual/en/function.crypt.php). Because this function is not easy to use properly, Yii provides two helper functions to make securely generating and verifying hashes easier.

When a user provides a password for the first time (e.g., upon registration), the password needs to be hashed:

```php
$hash = \yii\helpers\Security::generatePasswordHash($password);
```

The hash would then be associated with the corresponding model attribute, so that the hashed password will be stored in the database for later use.

When user attempts to log in, the submitted log in password must be verified against the previously hashed and stored password:

```php
use \yii\helpers;
if (Security::validatePassword($password, $hash)) {
	// all good, logging user in
} else {
	// wrong password
}
```


Creating random data
-----------

Random data is useful in many cases. For example, when resetting a password via email you need to generate a token,
save it to database, and send it via email to end user, allowing him to prove that email is his. It is very
important that this token be truly unique, else there will be a possibility to predict the token's value and reset another user's
password.

Yii security helper makes generating random data this simple:

```php
$key = \yii\helpers\Security::generateRandomKey();
```

Encryption and decryption
-------------------------

In order to encrypt data so only the person knowing a secret passphrase or having a secret key will be able to decrypt it.
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

Confirming data integrity
--------------------------------

Making sure data wasn't modified

hashData()
validateData()


Securing Cookies
----------------

- validation
- httpOnly

See also
--------

- [Views security](view.md#security)

