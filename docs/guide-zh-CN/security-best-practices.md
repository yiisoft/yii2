最佳安全实践
=======================

下面，我们将会回顾常见的安全原则，并介绍在使用 Yii 开发应用程序时，如何避免潜在安全威胁。
大多数这些原则并非您独有，而是适用于网站或软件开发，
因此，您还可以找到有关这些背后的一般概念的进一步阅读的链接。


基本准则
----------------

无论是开发何种应用程序，我们都有两条基本的安全准则：

1. 过滤输入
2. 转义输出


### 过滤输入

过滤输入的意思是，用户输入不应该认为是安全的，你需要总是验证你获得的输入值是在允许范围内。
比如，我们假设可以通过三个字段完成排序 `title`，`created_at` 和 `status`，然后，这个值是由用户输入提供的，
那么，最好在我们接收参数的时候，检查一下这个值是否是指定的范围。
对于基本的 PHP 而言，上述做法类似如下：

```php
$sortBy = $_GET['sort'];
if (!in_array($sortBy, ['title', 'created_at', 'status'])) {
	throw new Exception('Invalid sort value.');
}
```

在 Yii 中，很大可能性，你会使用 [表单校验器](input-validation.md) 来执行类似的检查。

进一步阅读该主题：

- <https://owasp.org/www-community/vulnerabilities/Improper_Data_Validation>
- <https://www.owasp.org/index.php/Input_Validation_Cheat_Sheet>


### 转义输出

转义输出的意思是，根据我们使用数据的上下文环境，数据需要被转义。比如：在 HTML 上下文，
你需要转义 `<`，`>` 之类的特殊字符。在 JavaScript 或者 SQL 中，也有其他的特殊含义的字符串需要被转义。
由于手动的给所用的输出转义容易出错，
Yii 提供了大量的工具来在不同的上下文执行转义。

进一步阅读该话题：

- <https://owasp.org/www-community/attacks/Command_Injection>
- <https://owasp.org/www-community/attacks/Code_Injection>
- <https://owasp.org/www-community/attacks/xss/>


避免 SQL 注入
-----------------------

SQL 注入发生在查询语句是由连接未转义的字符串生成的场景，比如：

```php
$username = $_GET['username'];
$sql = "SELECT * FROM user WHERE username = '$username'";
```

除了提供正确的用户名外，攻击者可以给你的应用程序输入类似 `'; DROP TABLE user; --` 的语句。
这将会导致生成如下的 SQL：

```sql
SELECT * FROM user WHERE username = ''; DROP TABLE user; --'
```

这是一个合法的查询语句，并将会执行以空的用户名搜索用户操作，然后，删除 `user` 表。
这极有可能导致网站出错，数据丢失。（你是否进行了规律的数据备份？）

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

如果上述方法不行，表名或者列名应该被转义。Yii 针对这种转义提供了一个特殊的语法，
这样可以在所有支持的数据库都使用一套方案。

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

你可以在 [Quoting Table and Column Names](db-dao.md#quoting-table-and-column-names) 中获取更多的语法细节。

进一步阅读该话题：

- <https://owasp.org/www-community/attacks/SQL_Injection>


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

如果是 HTML，我们可以用 HtmlPurifier 帮助类来执行：

```php
<?= \yii\helpers\HtmlPurifier::process($description) ?>
```

注意 HtmlPurifier 帮助类的处理过程较为费时，建议增加缓存。

进一步阅读该话题：

- <https://owasp.org/www-community/attacks/xss/>


防止 CSRF 攻击
-------------

CSRF 是跨站请求伪造的缩写。这个攻击思想源自许多应用程序假设来自用户的浏览器请求是由用户自己产生的，
而事实并非如此。

例如，网站 `an.example.com` 有一个 `/logout` 网址，当使用简单的 GET 请求访问时, 记录用户退出。
只要用户的请求一切正常，但是有一天坏人们故意在用户经常访问的论坛上放上 `<img src="https://an.example.com/logout">`。
浏览器在请求图像或请求页面之间没有任何区别，
所以当用户打开一个带有这样一个被操作过的 `<img>` 标签的页面时，
浏览器将 GET 请求发送到该 URL，用户将从 `an.example.com` 注销。

这是 CSRF 攻击如何运作的基本思路。可以说用户退出并不是一件严重的事情，
然而这仅仅是一个例子，使用这种方法可以做更多的事情，例如触发付款或者是改变数据。
想象一下如果某个网站有一个这样的 `https://an.example.com/purse/transfer?to=anotherUser&amount=2000` 网址。 
使用 GET 请求访问它会导致从授权用户账户转账 $2000 给 `anotherUser`。
我们知道，浏览器将始终发送 GET 请求来加载图像，
所以我们可以修改代码以仅接受该 URL 上的 POST 请求。
不幸的是，这并不会拯救我们，因为攻击者可以放置一些 JavaScript 代码而不是 `<img>` 标签，这样就可以向该 URL 发送 POST 请求。

出于这个原因，Yii 应用其他机制来防止 CSRF 攻击。

为了避免 CSRF 攻击，你总是需要：

1. 遵循 HTTP 准则，比如 GET 不应该改变应用的状态。
   有关详细信息，请参阅 [RFC2616](https://www.rfc-editor.org/rfc/rfc9110.html#name-method-definitions)。
2. 保证 Yii CSRF 保护开启。

有的时候你需要对每个控制器和/或方法使用禁用 CSRF。可以通过设置其属性来实现：

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

要对每个自定义方法禁用 CSRF 验证，您可以使用：

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

在 [standalone actions](structure-controllers.md#standalone-actions) 禁用 CSRF 必须在 `init()` 方法中设置。
不要把这段代码放在 `beforeRun()` 方法中，因为它不会起任何作用。

```php
<?php

namespace app\components;

use yii\base\Action;

class ContactAction extends Action
{
    public function init()
    {
        parent::init();
        $this->controller->enableCsrfValidation = false;
    }

    public function run()
    {
          $model = new ContactForm();
          $request = Yii::$app->request;
          if ($request->referrer === 'yiipowered.com'
              && $model->load($request->post())
              && $model->validate()
          ) {
              $model->sendEmail();
          }
    }
}
```

> Warning: 禁用 CSRF 将允许任何站点向您的站点发送 POST 请求。在这种情况下，实施额外验证非常重要，例如检查 IP 地址或秘密令牌。

进一步阅读该话题：

- <https://owasp.org/www-community/attacks/csrf>


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

进一步阅读该话题：

- <https://owasp.org/www-project-.net/articles/Exception_Handling.md>
- <https://owasp.org/www-pdf-archive/OWASP_Top_10_2007.pdf> (A6 - Information Leakage and Improper Error Handling)


使用 TLS 上的安全连接
--------------------------------

Yii 提供依赖 cookie 和/或 PHP 会话的功能。如果您的连接受到威胁，这些可能会很容易受到攻击。
如果应用程序通过 TLS 使用安全连接，则风险会降低。

有关如何配置它的说明，请参阅您的 Web 服务器文档。
您还可以参考 H5BP 项目提供的示例配置：

- [Nginx](https://github.com/h5bp/server-configs-nginx)。
- [Apache](https://github.com/h5bp/server-configs-apache)。
- [IIS](https://github.com/h5bp/server-configs-iis)。
- [Lighttpd](https://github.com/h5bp/server-configs-lighttpd)。


安全服务器配置
---------------------------

本节的目的是强调在为基于 Yii 的网站提供服务配置时需要考虑的风险。 
除了这里涉及的要点之外，
可能还有其他与安全相关的配置选项，
所以不要认为这部分是完整的。

### 避免 `Host`-header 攻击

像 [[yii\web\UrlManager]] 和 [[yii\helpers\Url]] 这样的类会使用 
[[yii\web\Request::getHostInfo()|currently requested host name]] 来生成链接。
如果 Web 服务器配置为独立于 `Host` 标头的值提供相同的站点，这个信息并不可靠，
并且 [可能由发送HTTP请求的用户伪造](https://www.acunetix.com/vulnerabilities/web/host-header-attack)。
在这种情况下，您应该修复您的 Web 服务器配置以便仅为指定的主机名提供站点服务
或者通过设置 `request` 应用程序组件的 [[yii\web\Request::setHostInfo()|hostInfo]] 属性来显式设置或过滤该值。

有关于服务器配置的更多信息，请参阅您的 web 服务器的文档：

- Apache 2：<https://httpd.apache.org/docs/trunk/vhosts/examples.html#defaultallports>
- Nginx：<https://www.nginx.com/resources/wiki/start/topics/examples/server_blocks/>

如果您无权访问服务器配置，您可以在应用程序级别设置 [[yii\filters\HostControl]] 过滤器，
以防此类的攻击。

```php
// Web Application configuration file
return [
    'as hostControl' => [
        'class' => 'yii\filters\HostControl',
        'allowedHosts' => [
            'example.com',
            '*.example.com',
        ],
        'fallbackHostInfo' => 'https://example.com',
    ],
    // ...
];
```

> Note: 您应该始更倾向于使用 web 服务器配置 'host header attack' 保护而不是使用过滤器。
  仅当服务器配置设置不可用时 [[yii\filters\HostControl]] 才应该被使用。
