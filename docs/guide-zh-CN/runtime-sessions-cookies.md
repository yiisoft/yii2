Sessions 和 Cookies
====================

Sessions 和 cookies 允许数据在多次请求中保持，
在纯 PHP 中，可以分别使用全局变量 `$_SESSION` 和 `$_COOKIE` 来访问，Yii 将 session 和 cookie 封装成对象并增加一些功能，
可通过面向对象方式访问它们。


## Sessions <span id="sessions"></span>

和 [请求](runtime-requests.md) 和 [响应](runtime-responses.md)类似，
默认可通过为 [[yii\web\Session]] 实例的 `session` 
[应用组件](structure-application-components.md) 来访问 sessions。


### 开启和关闭 Sessions <span id="opening-closing-sessions"></span>

可使用以下代码来开启和关闭 session。

```php
$session = Yii::$app->session;

// 检查session是否开启 
if ($session->isActive) ...

// 开启session
$session->open();

// 关闭session
$session->close();

// 销毁session中所有已注册的数据
$session->destroy();
```

多次调用[[yii\web\Session::open()|open()]] 和[[yii\web\Session::close()|close()]] 方法并不会产生错误，
因为方法内部会先检查session是否已经开启。


### 访问 Session 数据 <span id="access-session-data"></span>

可使用如下方式访问session中的数据：

```php
$session = Yii::$app->session;

// 获取session中的变量值，以下用法是相同的：
$language = $session->get('language');
$language = $session['language'];
$language = isset($_SESSION['language']) ? $_SESSION['language'] : null;

// 设置一个session变量，以下用法是相同的：
$session->set('language', 'en-US');
$session['language'] = 'en-US';
$_SESSION['language'] = 'en-US';

// 删除一个session变量，以下用法是相同的：
$session->remove('language');
unset($session['language']);
unset($_SESSION['language']);

// 检查session变量是否已存在，以下用法是相同的：
if ($session->has('language')) ...
if (isset($session['language'])) ...
if (isset($_SESSION['language'])) ...

// 遍历所有session变量，以下用法是相同的：
foreach ($session as $name => $value) ...
foreach ($_SESSION as $name => $value) ...
```

> Info: 当使用 `session` 组件访问 session 数据时候，
  如果 session 没有开启会自动开启，
  这和通过 `$_SESSION` 不同，`$_SESSION` 要求先执行 `session_start()`。

当 session 数据为数组时，`session` 组件会限制你直接修改数据中的单元项，
例如：

```php
$session = Yii::$app->session;

// 如下代码不会生效
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// 如下代码会生效：
$session['captcha'] = [
    'number' => 5,
    'lifetime' => 3600,
];

// 如下代码也会生效：
echo $session['captcha']['lifetime'];
```

可使用以下任意一个变通方法来解决这个问题：

```php
$session = Yii::$app->session;

// 直接使用$_SESSION (确保Yii::$app->session->open() 已经调用)
$_SESSION['captcha']['number'] = 5;
$_SESSION['captcha']['lifetime'] = 3600;

// 先获取session数据到一个数组，修改数组的值，然后保存数组到session中
$captcha = $session['captcha'];
$captcha['number'] = 5;
$captcha['lifetime'] = 3600;
$session['captcha'] = $captcha;

// 使用ArrayObject 数组对象代替数组
$session['captcha'] = new \ArrayObject;
...
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// 使用带通用前缀的键来存储数组
$session['captcha.number'] = 5;
$session['captcha.lifetime'] = 3600;
```

为更好的性能和可读性，推荐最后一种方案，
也就是不用存储 session 变量为数组，
而是将每个数组项变成有相同键前缀的 session 变量。


### 自定义 Session 存储 <span id="custom-session-storage"></span>

[[yii\web\Session]] 类默认存储 session 数据为文件到服务器上，
Yii 提供以下 session 类实现不同的 session 存储方式：

* [[yii\web\DbSession]]：存储 session 数据在数据表中
* [[yii\web\CacheSession]]：存储 session 数据到缓存中，缓存和配置中的[缓存组件](caching-data.md#cache-components)相关
* [[yii\redis\Session]]：存储 session 数据到以 [redis](https://redis.io/) 作为存储媒介中
* [[yii\mongodb\Session]]：存储 session 数据到 [MongoDB](https://www.mongodb.com/)。

所有这些 session 类支持相同的 API 方法集，因此，
切换到不同的 session 存储介质不需要修改项目使用 session 的代码。

> Note: 如果通过`$_SESSION`访问使用自定义存储介质的session，
  需要确保session已经用[[yii\web\Session::open()]] 开启，
  这是因为在该方法中注册自定义session存储处理器。

> Note: 如果使用自定义会话存储，则可能需要显式配置会话垃圾收集器。
  一些安装的 PHP（例如 Debian）使用垃圾收集器概率为 0，并在计划任务中离线清理会话文件。
  此过程不适用于您的自定义存储，
  因此您需要配置 [[yii\web\Session::$GCProbability]] 以使用非零值。

学习如何配置和使用这些组件类请参考它们的 API 文档，如下为一个示例
显示如何在应用配置中配置 [[yii\web\DbSession]]
将数据表作为 session 存储介质。

```php
return [
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',
            // 'db' => 'mydb',  // 数据库连接的应用组件ID，默认为'db'.
            // 'sessionTable' => 'my_session', // session 数据表名，默认为'session'.
        ],
    ],
];
```

也需要创建如下数据库表来存储 session 数据：

```sql
CREATE TABLE session
(
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```

其中 'BLOB' 对应你选择的数据库管理系统的 BLOB-type 类型，以下一些常用数据库管理系统的 BLOB 类型：

- MySQL: LONGBLOB
- PostgreSQL: BYTEA
- MSSQL: BLOB

> Note: 根据 php.ini 设置的 `session.hash_function`，你需要调整 `id` 列的长度，
  例如，如果 `session.hash_function=sha256`，
  应使用长度为 64 而不是 40 的 char 类型。

或者，可以通过以下迁移完成：

```php
<?php

use yii\db\Migration;

class m170529_050554_create_table_session extends Migration
{
    public function up()
    {
        $this->createTable('{{%session}}', [
            'id' => $this->char(64)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->binary()
        ]);
        $this->addPrimaryKey('pk-id', '{{%session}}', 'id');
    }

    public function down()
    {
        $this->dropTable('{{%session}}');
    }
}
```

### Flash 数据 <span id="flash-data"></span>

Flash 数据是一种特别的 session 数据，它一旦在某个请求中设置后，
只会在下次请求中有效，然后该数据就会自动被删除。
常用于实现只需显示给终端用户一次的信息，
如用户提交一个表单后显示确认信息。

可通过 `session` 应用组件设置或访问 `session`，例如：

```php
$session = Yii::$app->session;

// 请求 #1
// 设置一个名为"postDeleted" flash 信息
$session->setFlash('postDeleted', 'You have successfully deleted your post.');

// 请求 #2
// 显示名为"postDeleted" flash 信息
echo $session->getFlash('postDeleted');

// 请求 #3
// $result 为 false，因为flash信息已被自动删除
$result = $session->hasFlash('postDeleted');
```

和普通 session 数据类似，可将任意数据存储为 flash 数据。

当调用 [[yii\web\Session::setFlash()]] 时, 会自动覆盖相同名的已存在的任何数据，
为将数据追加到已存在的相同名 flash 中，可改为调用 [[yii\web\Session::addFlash()]]。
例如:

```php
$session = Yii::$app->session;

// 请求 #1
// 在名称为"alerts"的flash信息增加数据
$session->addFlash('alerts', 'You have successfully deleted your post.');
$session->addFlash('alerts', 'You have successfully added a new friend.');
$session->addFlash('alerts', 'You are promoted.');

// 请求 #2
// $alerts 为名为'alerts'的flash信息，为数组格式
$alerts = $session->getFlash('alerts');
```

> Note: 不要在相同名称的 flash 数据中使用 [[yii\web\Session::setFlash()]] 的同时也使用 [[yii\web\Session::addFlash()]]，
  因为后一个防范会自动将 flash 信息转换为数组以使新的 flash 数据可追加进来，因此，
  当你调用 [[yii\web\Session::getFlash()]] 时，
  会发现有时获取到一个数组，有时获取到一个字符串，
  取决于你调用这两个方法的顺序。

> Tip: 要显示 Flash 消息，您可以通过以下方式使用 [[yii\bootstrap\Alert|bootstrap Alert]] 小部件：
>
> ```php
> echo Alert::widget([
>    'options' => ['class' => 'alert-info'],
>    'body' => Yii::$app->session->getFlash('postDeleted'),
> ]);
> ```


## Cookies <span id="cookies"></span>

Yii 使用 [[yii\web\Cookie]] 对象来代表每个 cookie，
[[yii\web\Request]] 和 [[yii\web\Response]]
通过名为 'cookies' 的属性维护一个 cookie 集合，前者的 cookie 集合代表请求提交的 cookies，
后者的 cookie 集合表示发送给用户的 cookies。

控制器是直接处理请求和响应的部分。 因此, 应当在控制器中读取和发送 cookie 。
(译者注：意思在控制器中处理 cookie 是安全的。)

### 读取 Cookies <span id="reading-cookies"></span>

当前请求的 cookie 信息可通过如下代码获取：

```php
// 从 "request" 组件中获取 cookie 集合(yii\web\CookieCollection)
$cookies = Yii::$app->request->cookies;

// 获取名为 "language" cookie 的值，如果不存在，返回默认值 "en"
$language = $cookies->getValue('language', 'en');

// 另一种方式获取名为 "language" cookie 的值
if (($cookie = $cookies->get('language')) !== null) {
    $language = $cookie->value;
}

// 可将 $cookies 当作数组使用
if (isset($cookies['language'])) {
    $language = $cookies['language']->value;
}

// 判断是否存在名为 "language" 的 cookie
if ($cookies->has('language')) ...
if (isset($cookies['language'])) ...
```


### 发送 Cookies <span id="sending-cookies"></span>

可使用如下代码发送 cookie 到终端用户：

```php
// 从 "response" 组件中获取 cookie 集合(yii\web\CookieCollection)
$cookies = Yii::$app->response->cookies;

// 在要发送的响应中添加一个新的 cookie
$cookies->add(new \yii\web\Cookie([
    'name' => 'language',
    'value' => 'zh-CN',
]));

// 删除一个 cookie
$cookies->remove('language');
// 等同于以下删除代码
unset($cookies['language']);
```

除了上述例子定义的 [[yii\web\Cookie::name|name]] 和 [[yii\web\Cookie::value|value]] 属性
[[yii\web\Cookie]] 类也定义了其他属性来实现 cookie 的各种信息，如
[[yii\web\Cookie::domain|domain]]，[[yii\web\Cookie::expire|expire]]
可配置这些属性到 cookie 中并添加到响应的 cookie 集合中。

> Note: 为安全起见 [[yii\web\Cookie::httpOnly]] 被设置为 true，
  这可减少客户端脚本访问受保护 cookie（如果浏览器支持）的风险，
  更多详情可阅读 [httpOnly wiki article](https://owasp.org/www-community/HttpOnly)。


### Cookie 验证 <span id="cookie-validation"></span>

在上两节中，当通过 `request` 和 `response` 组件读取和发送 cookie 时，
你会喜欢扩展的 cookie 验证的保障安全功能，它能
使 cookie 不被客户端修改。该功能通过给每个 cookie 签发一个哈希字符串来告知服务端 cookie 是否在客户端被修改，
如果被修改，通过 `request` 组件的
[[yii\web\Request::cookies|cookie collection]] cookie 集合访问不到该 cookie。

> Note: Cookie 验证只保护 cookie 值被修改，如果一个 cookie 验证失败，
  仍然可以通过 `$_COOKIE` 来访问该 cookie，
  因为这是第三方库对未通过 cookie 验证自定义的操作方式。

Cookie 验证默认启用，可以设置 [[yii\web\Request::enableCookieValidation]] 属性为 false 来禁用它，
尽管如此，我们强烈建议启用它。

> Note: 直接通过 `$_COOKIE` 和 `setcookie()` 读取和发送的 Cookie 不会被验证。

当使用 cookie 验证时，必须指定 [[yii\web\Request::cookieValidationKey]]，它是用来生成上述的哈希值，
可通过在应用配置中配置 `request` 组件。

```php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'fill in a secret key here',
        ],
    ],
];
```

> Info: [[yii\web\Request::cookieValidationKey|cookieValidationKey]] 对你的应用安全很重要，
  应只被你信任的人知晓，请不要将它放入版本控制中。
