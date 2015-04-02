Working with Passwords
========

> Note: This section is under development.

Good security is vital to the health and success of any application. Unfortunately, many developers cut corners when it comes to security, either due to a lack of understanding or because implementation is too much of a hurdle. To make your Yii powered application as secure as possible, Yii has included several excellent and easy to use security features.


Hashing and Verifying Passwords
-------------------------------

Most developers know that passwords cannot be stored in plain text, but many developers believe it's still safe to hash passwords using `md5` or `sha1`. There was a time when using the aforementioned hashing algorithms was sufficient, but modern hardware makes it possible to reverse such hashes very quickly using brute force attacks.

In order to provide increased security for user passwords, even in the worst case scenario (your application is breached), you need to use a hashing algorithm that is resilient against brute force attacks. The best current choice is `bcrypt`. In PHP, you can create a `bcrypt` hash  using the [crypt function](http://php.net/manual/en/function.crypt.php). Yii provides two helper functions which make using `crypt` to securely generate and verify hashes easier.

When a user provides a password for the first time (e.g., upon registration), the password needs to be hashed:


```php
$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
```

The hash can then be associated with the corresponding model attribute, so it can be stored in the database for later use.

When a user attempts to log in, the submitted password must be verified against the previously hashed and stored password:


```php
if (Yii::$app->getSecurity()->validatePassword($password, $hash)) {
    // all good, logging user in
} else {
    // wrong password
}
```

Generating Pseudorandom Data
-----------

Pseudorandom data is useful in many situations. For example when resetting a password via email you need to generate a token, save it to the database, and send it via email to end user which in turn will allow them to prove ownership of that account. It is very important that this token be unique and hard to guess, else there is a possibility that attacker can predict the token's value and reset the user's password.

Yii security helper makes generating pseudorandom data simple:


```php
$key = Yii::$app->getSecurity()->generateRandomString();
```

Note that you need to have the `openssl` extension installed in order to generate cryptographically secure random data.

Encryption and Decryption
-------------------------

Yii provides convenient helper functions that allow you to encrypt/decrypt data using a secret key. The data is passed through the encryption function so that only the person which has the secret key will be able to decrypt it.
For example, we need to store some information in our database but we need to make sure only the user which has the secret key can view it (even if the application database is compromised):


```php
// $data and $secretKey are obtained from the form
$encryptedData = Yii::$app->getSecurity()->encryptByPassword($data, $secretKey);
// store $encryptedData to database
```

Subsequently when user wants to read the data:

```php
// $secretKey is obtained from user input, $encryptedData is from the database
$data = Yii::$app->getSecurity()->decryptByPassword($encryptedData, $secretKey);
```

Confirming Data Integrity
--------------------------------

There are situations in which you need to verify that your data hasn't been tampered with by a third party or even corrupted in some way. Yii provides an easy way to confirm data integrity in the form of two helper functions.

Prefix the data with a hash generated from the secret key and data


```php
// $secretKey our application or user secret, $genuineData obtained from a reliable source
$data = Yii::$app->getSecurity()->hashData($genuineData, $secretKey);
```

Checks if the data integrity has been compromised

```php
// $secretKey our application or user secret, $data obtained from an unreliable source
$data = Yii::$app->getSecurity()->validateData($data, $secretKey);
```


todo: XSS prevention, CSRF prevention, cookie protection, refer to 1.1 guide

You also can disable CSRF validation per controller and/or action, by setting its property:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        // CSRF validation will not be applied to this and other actions
    }

}
```

To disable CSRF validation per custom actions you can do:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function beforeAction($action)
    {
        // ...set `$this->enableCsrfValidation` here based on some conditions...
        // call parent method that will check CSRF if such property is true.
        return parent::beforeAction($action);
    }
}
```

Securing Cookies
----------------

- validation
- httpOnly is default

See also
--------

- [Views security](structure-views.md#security)

