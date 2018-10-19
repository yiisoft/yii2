处理密码
========

大部分开发者知道密码不能以明文形式存储，但是许多开发者仍认为使用 `md5` 或者 `sha1` 来哈希化密码是安全的。
一度，使用上述的哈希算法是足够安全的，但是，
现代硬件的发展使得短时间内暴力破解上述算法生成的哈希串成为可能。

为了即使在最糟糕的情况下（你的应用程序被破解了）也能给用户密码提供增强的安全性，
你需要使用一个能够对抗暴力破解攻击的哈希算法。目前最好的选择是 `bcrypt`。在 PHP 中，
你可以通过 [crypt 函数](http://php.net/manual/en/function.crypt.php) 生成 `bcrypt` 哈希。
Yii 提供了两个帮助函数以让使用 `crypt` 来进行安全的哈希密码生成和验证更加容易。

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
