最佳安全实践
=======================

下面，我们将会回顾常见的安全原则，并介绍在使用 Yii 开发应用程序时，如何避免潜在安全威胁。

基本准则
----------------

无论是开发何种应用程序，我们都有两条基本的安全准则：

1. 过滤输入
2. 转义输出


### 过滤输入

过滤输入的意思是，用户输入不应该认为是安全的，你需要总是验证你获得的输入值是在允许范围内。
比如，我们假设 sorting 只能指定为 `title`， `created_at` 和 `status` 三个值，然后，这个值是由用户输入提供的，
那么，最好在我们接收参数的时候，检查一下这个值是否是指定的范围。
对于基本的 PHP 而言，上述做法类似如下：

```php
$sortBy = $_GET['sort'];
if (!in_array($sortBy, ['title', 'created_at', 'status'])) {
	throw new Exception('Invalid sort value.');
}
```

在 Yii 中，很大可能性，你会使用 [表单校验器](input-validation.md) 来执行类似的检查。


### 转义输出

转义输出的意思是，根据我们使用数据的上下文环境，数据需要被转义。比如：在 HTML 上下文，
你需要转义 `<`，`>` 之类的特殊字符。在 JavaScript 或者 SQL 中，也有其他的特殊含义的字符串需要被转义。
由于手动的给所用的输出转义容易出错，
Yii 提供了大量的工具来在不同的上下文执行转义。

避免 SQL 注入
-----------------------

SQL 注入发生在查询语句是由连接未转义的字符串生成的场景，比如：

```php
$username = $_GET['username'];
$sql = "SELECT * FROM user WHERE username = '$username'";
```

除了提供正确的用户名外，攻击者可以给你的应用程序输入类似 '; DROP TABLE user; --` 的语句。
这将会导致生成如下的 SQL ：

```sql
SELECT * FROM user WHERE username = ''; DROP TABLE user; --'
```

这是一个合法的查询语句，并将会执行以空的用户名搜索用户操作，然后，删除 `user` 表。
这极有可能导致网站出差，数据丢失。（你是否进行了规律的数据备份？）

在 Yii 中，大部分的数据查询是通过 [Active Record](db-active-record.md) 进行的，
而其是完全使用 PDO 预处理语句执行 SQL 查询的。在预处理语句中，上述示例中，构造 SQL 查询的场景是不可能发生的。

有时，你仍需要使用 [raw queries](db-dao.md) 或者 [query builder](db-query-builder.md)。
在这种情况下，你应该使用安全的方式传递参数。如果数据是提供给表列的值，最好使用预处理语句：

```php
// query builder
$userIDs = (new Query())
    ->select('id')
    ->from('user')
    ->where('status=:status', [':status' => $status])
    ->all();

// DAO
$userIDs = $connection
    ->createCommand('SELECT id FROM user where status=:status')
    ->bindValues([':status' => $status])
    ->queryColumn();
```

如果数据是用于指定列的名字，或者表的名字，最好的方式是只允许预定义的枚举值。

```php
function actionList($orderBy = null)
{
    if (!in_array($orderBy, ['name', 'status'])) {
        throw new BadRequestHttpException('Only name and status are allowed to order by.')
    }

    // ...
}
```

如果上述方法不行，表名或者列名应该被转义。 Yii 针对这种转义提供了一个特殊的语法，
这样可以在所有支持的数据库都使用一套方案。

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

你可以在 [Quoting Table and Column Names](db-dao.md#quoting-table-and-column-names) 中获取更多的语法细节。


防止 XSS 攻击
------------

XSS 或者跨站脚本发生在输出 HTML 到浏览器时，输出内容没有正确的转义。
例如，如果用户可以输入其名称，那么他输入 `<script>alert('Hello!');</script>` 而非其名字 `Alexander`，
所有输出没有转义直接输出用户名的页面都会执行 JavaScript 代码 `alert('Hello!');`，
这会导致浏览器页面上出现一个警告弹出框。就具体的站点而言，除了这种无意义的警告输出外，
这样的脚本可以以你的名义发送一些消息到后台，甚至执行一些银行交易行为。

避免 XSS 攻击在 Yii 中非常简单，有如下两种一般情况：

1. 你希望数据以纯文本输出。
2. 你希望数据以 HTML 形式输出。

如果你需要的是纯文本，你可以如下简单的转义：


```php
<?= \yii\helpers\Html::encode($username) ?>
```

如果是 HTML ，我们可以用 HtmlPurifier 帮助类来执行：

```php
<?= \yii\helpers\HtmlPurifier::process($description) ?>
```

注意： HtmlPurifier 帮助类的处理过程较为费时，建议增加缓存：

防止 CSRF 攻击
-------------

CSRF 是跨站请求伪造的缩写。这个攻击思想源自许多应用程序假设来自用户的浏览器请求是由用户自己产生的，
而事实并非如此。

比如说：`an.example.com` 站点有一个 `/logout` URL，当以 GET 请求访问时，
登出用户。如果它是由用户自己操作的，那么一切都没有问题。但是，
有一天坏人在一个用户经常访问的论坛发了一个 `<img src="http://an.example.com/logout">` 内容的帖子。
浏览器无法辨别请求一个图片还是一个页面，所以，当用户打开含有上述标签的页面时，他将会从 `an.example.com` 登出。

上面就是最原始的思想。有人可能会说，登出用户也不是什么严重问题，然而，我们发送一些 POST 数据其实也不是很麻烦的事情。

为了避免 CSRF 攻击，你总是需要：

1. 遵循 HTTP 准则，比如 GET 不应该改变应用的状态。
2. 保证 Yii CSRF 保护开启。

Sometimes you need to disable CSRF validation per controller and/or action. It could be achieved by setting its property:

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


防止文件暴露
----------------------

默认的服务器 webroot 目录指向包含有 `index.php` 的 `web` 目录。在共享托管环境下，这样是不可能的，
这样导致了所有的代码，配置，日志都在webroot目录。

如果是这样，别忘了拒绝除了 `web` 目录以外的目录的访问权限。
如果没法这样做，考虑将你的应用程序托管在其他地方。

在生产环境关闭调试信息和工具
-------------------------------------------

在调试模式下， Yii 展示了大量的错误信息，这样是对开发有用的。
同样，这些调试信息对于攻击者而言也是方便其用于破解数据结构，配置值，以及你的部分代码。
永远不要在生产模式下将你的 `index.php` 中的 `YII_DEBUG` 设置为 `true`。

你同样也不应该在生产模式下开启 Gii。它可以被用于获取数据结构信息，
代码，以及简单的用 Gii 生成的代码覆盖你的代码。

调试工具栏同样也应该避免在生产环境出现，除非非常有必要。它将会暴露所有的应用和配置的详情信息。
如果你确定需要，反复确认其访问权限限定在你自己的 IP。

Using secure connection over TLS
--------------------------------

Yii provides features that rely on cookies and/or PHP sessions. These can be vulnerable in case your connection is
compromised. The risk is reduced if the app uses secure connection via TLS.

Please refer to your webserver documentation for instructions on how to configure it. You may also check example configs
provided by H5BP project:

- [Nginx](https://github.com/h5bp/server-configs-nginx)
- [Apache](https://github.com/h5bp/server-configs-apache).
- [IIS](https://github.com/h5bp/server-configs-iis).
- [Lighttpd](https://github.com/h5bp/server-configs-lighttpd).