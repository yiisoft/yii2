版本
==========

你的API应该是版本化的。不像你完全控制在客户端和服务器端Web应用程序代码, 对于API，您通常没有对API的客户端代码的控制权。
因此，应该尽可能的保持向后兼容性(BC)，如果一些不能向后兼容的变化必须引入
APIs，你应该增加版本号。你可以参考[Semantic Versioning](http://semver.org/)
有关设计的API的版本号的详细信息。

关于如何实现API版本，一个常见的做法是在API的URL中嵌入版本号。
例如，`http://example.com/v1/users`代表`/users`版本1的API. 另一种API版本化的方法最近用的非常多的是把版本号放入HTTP请求头，通常是通过`Accept`头，
如下：

```
// 通过参数
Accept: application/json; version=v1
// 通过vendor的内容类型
Accept: application/vnd.company.myapp-v1+json
```

这两种方法都有优点和缺点， 而且关于他们也有很多争论。
下面我们描述在一种API版本混合了这两种方法的一个实用的策略:

* 把每个主要版本的API实现在一个单独的模块ID的主版本号 (例如 `v1`, `v2`)。
  自然，API的url将包含主要的版本号。
* 在每一个主要版本 (在相应的模块)，使用 `Accept` HTTP 请求头
  确定小版本号编写条件代码来响应相应的次要版本.

为每个模块提供一个主要版本， 它应该包括资源类和控制器类
为特定服务版本。 更好的分离代码， 你可以保存一组通用的
基础资源和控制器类， 并用在每个子类版本模块。 在子类中，
实现具体的代码例如 `Model::fields()`。

你的代码可以类似于如下的方法组织起来：

```
api/
    common/
        controllers/
            UserController.php
            PostController.php
        models/
            User.php
            Post.php
    modules/
        v1/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
        v2/
            controllers/
                UserController.php
                PostController.php
            models/
                User.php
                Post.php
```

你的应用程序配置应该这样：

```php
return [
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
        ],
        'v2' => [
            'basePath' => '@app/modules/v2',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/user', 'v1/post']],
                ['class' => 'yii\rest\UrlRule', 'controller' => ['v2/user', 'v2/post']],
            ],
        ],
    ],
];
```

因此，`http://example.com/v1/users`将返回版本1的用户列表，而
`http://example.com/v2/users`将返回版本2的用户。

使用模块， 将不同版本的代码隔离。 通过共用基类和其他类
跨模块重用代码也是有可能的。

为了处理次要版本号， 可以利用内容协商
功能通过 [[yii\filters\ContentNegotiator|contentNegotiator]] 提供的行为。`contentNegotiator`
行为可设置 [[yii\web\Response::acceptParams]] 属性当它确定
支持哪些内容类型时。

例如， 如果一个请求通过 `Accept: application/json; version=v1`被发送，
内容交涉后，[[yii\web\Response::acceptParams]]将包含值`['version' => 'v1']`.

基于 `acceptParams` 的版本信息，你可以写条件代码
如 actions，resource classes，serializers等等。

由于次要版本需要保持向后兼容性，希望你的代码不会有
太多的版本检查。否则，有机会你可能需要创建一个新的主要版本。
