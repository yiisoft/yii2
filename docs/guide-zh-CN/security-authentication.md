认证
==============

认证是鉴定用户身份的过程。它通常使用一个标识符
（如用户名或电子邮件地址）和一个加密令牌（比如密码或者存取令牌）来
鉴别用户身份。认证是登录功能的基础。

Yii提供了一个认证框架，它连接了不同的组件以支持登录。欲使用这个框架，
你主要需要做以下工作：
 
* 设置用户组件 [[yii\web\User|user]] ;
* 创建一个类实现 [[yii\web\IdentityInterface]] 接口。


## 配置 [[yii\web\User]] <span id="configuring-user"></span>

用户组件 [[yii\web\User|user]] 用来管理用户的认证状态。这需要你
指定一个含有实际认证逻辑的认证类 [[yii\web\User::identityClass|identity class]]。
在以下web应用的配置项中，将用户用户组件 [[yii\web\User|user]] 的
认证类 [[yii\web\User::identityClass|identity class]] 配置成
模型类 `app\models\User`， 它的实现将在下一节中讲述。 
  
```php
return [
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
        ],
    ],
];
```


## 认证接口 [[yii\web\IdentityInterface]] 的实现 <span id="implementing-identity"></span>

认证类 [[yii\web\User::identityClass|identity class]] 必须实现包含以下方法的
认证接口 [[yii\web\IdentityInterface]]：

* [[yii\web\IdentityInterface::findIdentity()|findIdentity()]]：根据指定的用户ID查找
  认证模型类的实例，当你需要使用session来维持登录状态的时候会用到这个方法。
* [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]：根据指定的存取令牌查找
  认证模型类的实例，该方法用于
  通过单个加密令牌认证用户的时候（比如无状态的RESTful应用）。
* [[yii\web\IdentityInterface::getId()|getId()]]：获取该认证实例表示的用户的ID。
* [[yii\web\IdentityInterface::getAuthKey()|getAuthKey()]]：获取基于 cookie 登录时使用的认证密钥。
  认证密钥储存在 cookie 里并且将来会与服务端的版本进行比较以确保
  cookie的有效性。
* [[yii\web\IdentityInterface::validateAuthKey()|validateAuthKey()]] ：是基于 cookie 登录密钥的
验证的逻辑的实现。

用不到的方法可以空着，例如，你的项目只是一个
无状态的 RESTful 应用，只需实现 [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]]
和 [[yii\web\IdentityInterface::getId()|getId()]] ，其他的方法的函数体留空即可。

下面的例子是一个通过结合了 `user` 数据表的 
AR 模型 [Active Record](db-active-record.md) 实现的一个认证类 [[yii\web\User::identityClass|identity class]]。

```php
<?php

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return 'user';
    }

    /**
     * 根据给到的ID查询身份。
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * 根据 token 查询身份。
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
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

如上所述，如果你的应用利用 cookie 登录，
你只需要实现 `getAuthKey()` 和 `validateAuthKey()` 方法。这样的话，你可以使用下面的代码在 
`user` 表中生成和存储每个用户的认证密钥。

```php
class User extends ActiveRecord implements IdentityInterface
{
    ......
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }
}
```

> Note: 不要混淆 `user` 认证类和用户组件 [[yii\web\User]]。前者是实现
  认证逻辑的类，通常用关联了
  持久性存储的用户信息的AR模型 [Active Record](db-active-record.md) 实现。后者是负责管理用户认证状态的
  应用组件。


## 使用用户组件 [[yii\web\User]] <span id="using-user"></span>

在 `user` 应用组件方面，你主要用到 [[yii\web\User]] 。

你可以使用表达式 `Yii::$app->user->identity` 检测当前用户身份。它返回
一个表示当前登录用户的认证类 [[yii\web\User::identityClass|identity class]] 的实例，
未认证用户（游客）则返回 null。下面的代码展示了如何从 [[yii\web\User]] 
获取其他认证相关信息：

```php
// 当前用户的身份实例。未认证用户则为 Null 。
$identity = Yii::$app->user->identity;

// 当前用户的ID。 未认证用户则为 Null 。
$id = Yii::$app->user->id;

// 判断当前用户是否是游客（未认证的）
$isGuest = Yii::$app->user->isGuest;
```

你可以使用下面的代码登录用户：

```php
// 使用指定用户名获取用户身份实例。
// 请注意，如果需要的话您可能要检验密码
$identity = User::findOne(['username' => $username]);

// 登录用户
Yii::$app->user->login($identity);
```

[[yii\web\User::login()]] 方法将当前用户的身份登记到 [[yii\web\User]]。如果 session 设置为 
[[yii\web\User::enableSession|enabled]]，则使用 session 记录用户身份，用户的
认证状态将在整个会话中得以维持。如果开启自动登录 [[yii\web\User::enableAutoLogin|enabled]] 
则基于 cookie 登录（如：记住登录状态），它将使用 cookie 保存用户身份，这样
只要 cookie 有效就可以恢复登录状态。

为了使用 cookie 登录，你需要在应用配置文件中将 [[yii\web\User::enableAutoLogin]] 
设为 true。你还需要在 [[yii\web\User::login()]] 方法中
传递有效期（记住登录状态的时长）参数。

注销用户：

```php
Yii::$app->user->logout();
```

请注意，启用 session 时注销用户才有意义。该方法将从内存和 session 中
同时清理用户认证状态。默认情况下，它还会注销所有的
用户会话数据。如果你希望保留这些会话数据，可以换成 `Yii::$app->user->logout(false)` 。


## 认证事件 <span id="auth-events"></span>

[[yii\web\User]] 类在登录和注销流程引发一些事件。

* [[yii\web\User::EVENT_BEFORE_LOGIN|EVENT_BEFORE_LOGIN]]：在登录 [[yii\web\User::login()]] 时引发。
  如果事件句柄将事件对象的 [[yii\web\UserEvent::isValid|isValid]] 属性设为 false，
  登录流程将会被取消。
* [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]]：登录成功后引发。
* [[yii\web\User::EVENT_BEFORE_LOGOUT|EVENT_BEFORE_LOGOUT]]：注销 [[yii\web\User::logout()]] 前引发。
  如果事件句柄将事件对象的 [[yii\web\UserEvent::isValid|isValid]] 属性设为 false，
  注销流程将会被取消。
* [[yii\web\User::EVENT_AFTER_LOGOUT|EVENT_AFTER_LOGOUT]]：成功注销后引发。

你可以通过响应这些事件来实现一些类似登录统计、在线人数统计的功能。例如,
在登录后 [[yii\web\User::EVENT_AFTER_LOGIN|EVENT_AFTER_LOGIN]] 的处理程序，你可以将用户的登录时间和IP记录到
`user` 表中。
