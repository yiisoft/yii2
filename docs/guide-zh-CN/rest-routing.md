路由
=======

随着资源和控制器类准备，您可以使用 URL 如
`http://localhost/index.php?r=user/create` 访问资源，类似于你可以用正常的 Web 应用程序做法。

在实践中，你通常要用美观的 URL 并采取有优势的 HTTP 动词。
例如，请求 `POST /users` 意味着访问 `user/create` 动作。
这可以很容易地通过配置 `urlManager` 应用程序组件来完成
如下所示：

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'enableStrictParsing' => true,
    'showScriptName' => false,
    'rules' => [
        ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
    ],
]
```

相比于 URL 管理的 Web 应用程序，上述主要的新东西是通过 RESTful API
请求 [[yii\rest\UrlRule]] 。这个特殊的 URL 规则类将会
建立一整套子 URL 规则来支持路由和 URL 创建的指定的控制器。
例如，上面的代码中是大致按照下面的规则：

```php
[
    'PUT,PATCH users/<id>' => 'user/update',
    'DELETE users/<id>' => 'user/delete',
    'GET,HEAD users/<id>' => 'user/view',
    'POST users' => 'user/create',
    'GET,HEAD users' => 'user/index',
    'users/<id>' => 'user/options',
    'users' => 'user/options',
]
```

该规则支持下面的 API 末端:

* `GET /users`：逐页列出所有用户；
* `HEAD /users`：显示用户列表的概要信息；
* `POST /users`：创建一个新用户；
* `GET /users/123`：返回用户为 123 的详细信息；
* `HEAD /users/123`：显示用户 123 的概述信息；
* `PATCH /users/123` 和 `PUT /users/123`：更新用户 123；
* `DELETE /users/123`：删除用户 123；
* `OPTIONS /users`：显示关于末端 `/users` 支持的动词；
* `OPTIONS /users/123`：显示有关末端 `/users/123` 支持的动词。

您可以通过配置 `only` 和 `except` 选项来明确列出哪些行为支持，
哪些行为禁用。例如，

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'except' => ['delete', 'create', 'update'],
],
```

您也可以通过配置 `patterns` 或 `extraPatterns` 重新定义现有的模式或添加此规则支持的新模式。
例如，通过末端 `GET /users/search` 可以支持新行为 `search`， 按照如下配置 `extraPatterns` 选项，

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => 'user',
    'extraPatterns' => [
        'GET search' => 'search',
    ],
]
```

您可能已经注意到控制器 ID `user` 以复数形式出现在 `users` 末端。
这是因为 [[yii\rest\UrlRule]] 能够为他们使用的末端全自动复数化控制器 ID。
您可以通过设置 [[yii\rest\UrlRule::pluralize]] 为 false 来禁用此行为。

> Info: 控制器的 ID 复数化由 [[yii\helpers\Inflector::pluralize()]] 完成。该方法遵循
  特定的规则。举个例子，单词 `box` 会被复数化为 `boxes` 而不是 `boxs`。

如果自动复数化不能满足你的需求，你也可以配置
[[yii\rest\UrlRule::controller]] 属性来明确指定如何将端点URL中使用的名称映射到
控制器ID。例如，以下代码将名称 `u` 映射到控制器ID `user`。 
 
```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => ['u' => 'user'],
]
```

## 包含规则的额外配置

在 [[yii\rest\UrlRule]] 中所包含的每个规则中，指定额外的配置可能很有用。
一个很好的例子就是指定 `expand` 参数的默认值：

```php
[
    'class' => 'yii\rest\UrlRule',
    'controller' => ['user'],
    'ruleConfig' => [
        'class' => 'yii\web\UrlRule',
        'defaults' => [
            'expand' => 'profile',
        ]
    ],
],
```
