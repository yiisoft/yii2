路由
=======

随着资源和控制器类准备，您可以使用URL如
`http://localhost/index.php?r=user/create`访问资源，类似于你可以用正常的Web应用程序做法。

在实践中，你通常要用美观的URL并采取有优势的HTTP动词。
例如，请求`POST /users`意味着访问`user/create`动作。
这可以很容易地通过配置`urlManager`应用程序组件来完成
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

相比于URL管理的Web应用程序，上述主要的新东西是通过RESTful API
请求[[yii\rest\UrlRule]]。这个特殊的URL规则类将会
建立一整套子URL规则来支持路由和URL创建的指定的控制器。
例如， 上面的代码中是大致按照下面的规则:

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

该规则支持下面的API末端:

* `GET /users`: 逐页列出所有用户；
* `HEAD /users`: 显示用户列表的概要信息；
* `POST /users`: 创建一个新用户；
* `GET /users/123`: 返回用户为123的详细信息;
* `HEAD /users/123`: 显示用户 123 的概述信息;
* `PATCH /users/123` and `PUT /users/123`: 更新用户123;
* `DELETE /users/123`: 删除用户123;
* `OPTIONS /users`: 显示关于末端 `/users` 支持的动词;
* `OPTIONS /users/123`: 显示有关末端 `/users/123` 支持的动词。

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
```

您可能已经注意到控制器ID`user`以复数形式出现在`users`末端。
这是因为 [[yii\rest\UrlRule]] 能够为他们使用的末端全自动复数化控制器ID。
您可以通过设置 [[yii\rest\UrlRule::pluralize]] 为false 来禁用此行为，如果您想
使用一些特殊的名字您可以通过配置 [[yii\rest\UrlRule::controller]] 属性。
