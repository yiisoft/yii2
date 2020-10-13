加密（Cryptography）
==================

在本节中，我们将回顾以下安全问题：

- 生成随机数据
- 加密和解密
- 确认数据完整性

生成伪随机数据（Generating Pseudorandom Data）
----------------------------

伪随机数据在很多情况下都很有用。 例如，当通过电子邮件重置密码时，
您需要生成一个令牌，将其保存到数据库中，并通过电子邮件发送给最终用户，
这反过来又会允许他们证明该帐户的所有权。
这个令牌是独一无二且难以猜测的，否则攻击者可能会预测令牌的值并重置用户的密码。

Yii 安全助手类简单生成伪随机数据：


```php
$key = Yii::$app->getSecurity()->generateRandomString();
```

加密和解密（Encryption and Decryption）
-------------------------

Yii 提供了便利的帮助功能，使您可以使用密钥 加密/解密 数据。 数据通过加密功能传递，以便只有拥有密钥的人才能解密。
例如，我们需要在数据库中存储一些信息，但我们需要确保只有拥有密钥的用户才能查看它（即使应用程序数据库已被泄露）：


```php
// $data 和 $secretKey 从表单中获得
$encryptedData = Yii::$app->getSecurity()->encryptByPassword($data, $secretKey);
// 将 $encryptedData 存储到数据库
```

随后当用户想要读取数据时：

```php
// $secretKey 从用户输入获得，$encryptedData 来自数据库
$data = Yii::$app->getSecurity()->decryptByPassword($encryptedData, $secretKey);
```

也可以通过 [[\yii\base\Security::encryptByKey()]] 和
[[\yii\base\Security::decryptByKey()]] 使用密钥而不是密码。

确认数据完整性（Confirming Data Integrity）
-------------------------

在某些情况下，您需要验证您的数据未被第三方篡改甚至以某种方式损坏。 Yii 提供了一种简单的方法用两个帮助功能的类确认数据完整性的。

用密钥和数据生成的哈希前缀数据


```php
// $secretKey 是我们的应用程序或用户密钥，$genuineData 是从可靠来源获得的
$data = Yii::$app->getSecurity()->hashData($genuineData, $secretKey);
```

检查数据完整性是否受到损害

```php
// $secretKey 我们的应用程序或用户密钥，$data 从不可靠的来源获得
$data = Yii::$app->getSecurity()->validateData($data, $secretKey);
```
