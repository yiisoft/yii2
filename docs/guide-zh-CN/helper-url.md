Url 帮助类
==========

Url helper provides a set of static methods for managing URLs.

Url 帮助类提供一系列的静态方法来管理 URL。

## 获得通用 URL <span id="getting-common-urls"></span>

There are two methods you can use to get common URLs: home URL and base URL of the current request. In order to get
home URL, use the following:

我们提供了两种方法来获取通用 URLS ：当前请求的 home URL 和 base URL 。为了获取 home URL ，使用如下代码：

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

If no parameter is passed, the generated URL is relative. You can either pass `true` to get an absolute URL for the current
schema or specify a schema explicitly (`https`, `http`).

如果没有传递任何参数，将会生成相对 URL 。你可以传 `true` 来获得一个针对当前协议的绝对 URL ；同时，你可以明确的指定具体的协议类型（ `https` , `http` ）

To get the base URL of the current request use the following:

如下代码可以获得当前请求的 base URL：

 
```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

The only parameter of the method works exactly the same as for `Url::home()`.

这个方法的参数和 `Url::home()` 的完全一样。

## 创建 URLs <span id="creating-urls"></span>

In order to create a URL to a given route use the `Url::toRoute()` method. The method uses [[\yii\web\UrlManager]] to create
a URL:

为了创建一个给定路由的 URL 地址，请使用 `Url::toRoute()`方法。 这个方法使用 [[\yii\web\UrlManager]] 来创建一个 URL 地址：

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```
 
You may specify the route as a string, e.g., `site/index`. You may also use an array if you want to specify additional
query parameters for the URL being created. The array format must be:

你可以用一个字符串来作为路由，比如： `site/index` 。如果想要指定将要被创建的 URL 附加的查询参数，你同样可以使用一个数组来作为路由。数组的格式必须为：

```php
// generates: /index.php?r=site/index&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

If you want to create a URL with an anchor, you can use the array format with a `#` parameter. For example,

如果你想要创建一个带有 anchor 的 URL ，你可以使用一个带有 `#` 参数的数组。比如：

```php
// generates: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```

A route may be either absolute or relative. An absolute route has a leading slash (e.g. `/site/index`) while a relative
route has none (e.g. `site/index` or `index`). A relative route will be converted into an absolute one by the following rules:

- If the route is an empty string, the current [[\yii\web\Controller::route|route]] will be used;
- If the route contains no slashes at all (e.g. `index`), it is considered to be an action ID of the current controller
  and will be prepended with [[\yii\web\Controller::uniqueId]];
- If the route has no leading slash (e.g. `site/index`), it is considered to be a route relative to the current module
  and will be prepended with the module's [[\yii\base\Module::uniqueId|uniqueId]].
  
一个路由可以是绝对或者相对的。一个绝对的路由以前导斜杠开头（比如： `/site/index`），而一个相对的路由则没有（比如： `site/index` 或者 `index`）。一个相对的路由将会按照如下规则转换为绝对路由：

- 如果这个路由是一个空的字符串，将会使用当前 [[\yii\web\Controller::route|route]] 作为路由
- 如果这个路由不带任何斜杠（比如 `index` ），它会被认为是当前控制器的一个 action ID，然后将会把 [[\yii\web\Controller::uniqueId]] 插入到路由前面。
- 如果这个路由不带前导斜杠（比如： `site/index` ），它会被认为是相对当前模块的路由，然后将会把 [[\yii\base\Module::uniqueId|uniqueId]] 插入到路由前面。

Starting from version 2.0.2, you may specify a route in terms of an [alias](concept-aliases.md). If this is the case,
the alias will first be converted into the actual route which will then be turned into an absolute route according
to the above rules.

从2.0.2版本开始，你可以用 [alias](concept-aliases.md) 来指定一个路由。在这种情况下， alias 将会首先转换为实际的路由，然后会按照上述规则转换为绝对路由。

Below are some examples of using this method:

以下是使用这种方法的一些例子：

```php
// /index.php?r=site/index
echo Url::toRoute('site/index');

// /index.php?r=site/index&src=ref1#name
echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     assume the alias "@postEdit" is defined as "post/edit"
echo Url::toRoute(['@postEdit', 'id' => 100]);

// http://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', true);

// https://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', 'https');
```

There's another method `Url::to()` that is very similar to [[toRoute()]]. The only difference is that this method
requires a route to be specified as an array only. If a string is given, it will be treated as a URL.

还有另外一个方法 `Url::to()` 和 [[toRoute()]] 非常类似。这两个方法的唯一区别在于，前者要求一个路由必须用数组来指定。如果传的参数为字符串，它将会被直接当做 URL 。

The first argument could be:
         
- an array: [[toRoute()]] will be called to generate the URL. For example:
  `['site/index']`, `['post/index', 'page' => 2]`. Please refer to [[toRoute()]] for more details
  on how to specify a route.
- a string with a leading `@`: it is treated as an alias, and the corresponding aliased string
  will be returned.
- an empty string: the currently requested URL will be returned;
- a normal string: it will be returned as is.

 `Url::to()` 的第一个参数可以是：

- 数组：将会调用 [[toRoute()]] 来生成URL。比如 `['site/index']`, `['post/index', 'page' => 2]` 。更多的用法请参考 [[toRoute()]] 。
- 带前导 `@` 的字符串：它将会被当做别名，对应的别名字符串将会返回。
- 空的字符串：当前请求的 URL 将会被返回；
- 普通的字符串：返回本身。

When `$scheme` is specified (either a string or true), an absolute URL with host info (obtained from
[[\yii\web\UrlManager::hostInfo]]) will be returned. If `$url` is already an absolute URL, its scheme
will be replaced with the specified one.

Below are some usage examples:

当 `$scheme` 指定了（无论是字符串还是 true ），一个带主机信息（通过 [[\yii\web\UrlManager::hostInfo]] 获得）的绝对 URL 将会被返回。如果 `$url` 已经是绝对 URL 了，它的协议信息将会被替换为指定的。

以下是一些使用示例：

```php
// /index.php?r=site/index
echo Url::to(['site/index']);

// /index.php?r=site/index&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     assume the alias "@postEdit" is defined as "post/edit"
echo Url::to(['@postEdit', 'id' => 100]);

// the currently requested URL
echo Url::to();

// /images/logo.gif
echo Url::to('@web/images/logo.gif');

// images/logo.gif
echo Url::to('images/logo.gif');

// http://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', true);

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', 'https');
```

Starting from version 2.0.3, you may use [[yii\helpers\Url::current()]] to create a URL based on the currently
requested route and GET parameters. You may modify or remove some of the GET parameters or add new ones by
passing a `$params` parameter to the method. For example,

从2.0.3版本开始，你可以使用 [[yii\helpers\Url::current()]] 来创建一个基于当前请求路由和 GET 参数的 URL。你可以通过传递一个 `$params` 给这个方法来添加或者删除 GET 参数。例如：

```php
// assume $_GET = ['id' => 123, 'src' => 'google'], current route is "post/view"

// /index.php?r=post/view&id=123&src=google
echo Url::current();

// /index.php?r=post/view&id=123
echo Url::current(['src' => null]);
// /index.php?r=post/view&id=100&src=google
echo Url::current(['id' => 100]);
```


## Remember URLs <span id="remember-urls"></span>

There are cases when you need to remember URL and afterwards use it during processing of the one of sequential requests.
It can be achieved in the following way:
 
```php
// Remember current URL 
Url::remember();

// Remember URL specified. See Url::to() for argument format.
Url::remember(['product/view', 'id' => 42]);

// Remember URL specified with a name given
Url::remember(['product/view', 'id' => 42], 'product');
```

In the next request we can get URL remembered in the following way:

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## Checking Relative URLs <span id="checking-relative-urls"></span>

To find out if URL is relative i.e. it doesn't have host info part, you can use the following code:
                             
```php
$isRelative = Url::isRelative('test/it');
```
