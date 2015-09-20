处理密码
========

> 注意：本节内容正在开发中。

好的安全策略对任何应用的健康和成功极其重要。不幸的是，许多开发者在遇到安全问题时，因为认识不够或者实现起来比较麻烦，都不是很注意细节。为了让你的 Yii 应用程序尽可能的安全， Yii 囊括了一些卓越并简单易用的安全特性。

密码的哈希与验证
-------------------------------

大部分开发者知道密码不能以明文形式存储，但是许多开发者仍认为使用 `md5` 或者 `sha1` 来哈希化密码是安全的。一度，使用上述的哈希算法是足够安全的，但是，现代硬件的发展使得短时间内暴力破解上述算法生成的哈希串成为可能。

为了即使在最糟糕的情况下（你的应用程序被破解了）也能给用户密码提供增强的安全性，你需要使用一个能够对抗暴力破解攻击的哈希算法。目前最好的选择是 `bcrypt`。在 PHP 中，你可以通过 [crypt 函数](http://php.net/manual/en/function.crypt.php) 生成 `bcrypt` 哈希。Yii 提供了两个帮助函数以让使用 `crypt` 来进行安全的哈希密码生成和验证更加容易。

当一个用户为第一次使用，提供了一个密码时（比如：注册时），密码就需要被哈希化。

```php
$hash = Yii::$app->getSecurity()->generatePasswordHash($password);
```

哈希串可以被关联到对应的模型属性，这样，它可以被存储到数据库中以备将来使用。

当一个用户尝试登录时，表单提交的密码需要使用之前的存储的哈希串来验证：

```php
if (Yii::$app->getSecurity()->validatePassword($password, $hash)) {
    // all good, logging user in
} else {
    // wrong password
}
```

生成伪随机数
-----------

伪随机数据在许多场景下都非常有用。比如当通过邮件重置密码时，你需要生成一个令牌，将其保存到数据库中，并通过邮件发送到终端用户那里以让其证明其对某个账号的所有权。这个令牌的唯一性和难猜解性非常重要，否则，就存在攻击者猜解令牌，并重置用户的密码的可能性。

Yii security helper makes generating pseudorandom data simple:
Yii 安全助手使得生成伪随机数据非常简单：

```php
$key = Yii::$app->getSecurity()->generateRandomString();
```

注意，你需要安装有 `openssl` 扩展，以生成密码的安全随机数据。

加密与解密
-------------------------

Yii 提供了方便的帮助函数来让你用一个安全秘钥来加密解密数据。数据通过加密函数进行传输，这样只有拥有安全秘钥的人才能解密。比如，我们需要存储一些信息到我们的数据库中，但是，我们需要保证只有拥有安全秘钥的人才能看到它（即使应用的数据库泄露）

```php
// $data and $secretKey are obtained from the form
$encryptedData = Yii::$app->getSecurity()->encryptByPassword($data, $secretKey);
// store $encryptedData to database
```

随后，当用户需要读取数据时：

```php
// $secretKey is obtained from user input, $encryptedData is from the database
$data = Yii::$app->getSecurity()->decryptByPassword($encryptedData, $secretKey);
```

校验数据完整性
--------------------------------

有时，你需要验证你的数据没有第三方篡改或者使用某种方式破坏了。Yii 通过两个帮助函数，提供了一个简单的方式来进行数据的完整性校验。

首先，将由安全秘钥和数据生成的哈希串前缀到数据上。

```php
// $secretKey our application or user secret, $genuineData obtained from a reliable source
$data = Yii::$app->getSecurity()->hashData($genuineData, $secretKey);
```

验证数据完整性是否被破坏了。

```php
// $secretKey our application or user secret, $data obtained from an unreliable source
$data = Yii::$app->getSecurity()->validateData($data, $secretKey);
```

todo： XSS 防范， CSRF 防范， Cookie 保护相关的内容，参考 1.1 文档

你同样可以给控制器或者 action 设置它的 `enableCsrfValidation` 属性来单独禁用 CSRF 验证。

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

为了给某个定制的 action 关闭 CSRF 验证，你可以：

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

安全 Cookie
----------------

- validation
- httpOnly is default

参考
--------

- [Views security](structure-views.md#security)
