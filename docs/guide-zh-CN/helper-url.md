Url 帮助类
==========

Url 帮助类提供一系列的静态方法来帮助管理 URL。

## 获得通用 URL <a name="getting-common-urls"></a>

有两种获取通用 URLS 的方法 ：当前请求的 home URL 和 base URL 。为了获取 home URL ，使用如下代码：

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

If no parameter is passed, the generated URL is relative. You can either pass `true` to get an absolute URL for the current
schema or specify a schema explicitly (`https`, `http`).

如果没有传任何参数，这个方法将会生成相对 URL 。你可以传 `true` 来获得一个针对当前协议的绝对 URL ；或者，你可以明确的指定具体的协议类型（ `https` , `http` ）

如下代码可以获得当前请求的 base URL：

 
```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

这个方法的调用方式和 `Url::home()` 的完全一样。

## 创建 URLs <span id="creating-urls"></span>

为了创建一个给定路由的 URL 地址，请使用 `Url::toRoute()`方法。 这个方法使用 [[\yii\web\UrlManager]] 来创建一个 URL ：

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```

你可以指定一个字符串来作为路由，如： `site/index` 。如果想要指定将要被创建的 URL 的附加查询参数，你同样可以使用一个数组来作为路由。数组的格式须为：

```php
// generates: /index.php?r=site/index&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

如果你想要创建一个带有 anchor 的 URL ，你可以使用一个带有 `#` 参数的数组。比如：

```php
// generates: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```
  
一个路由既可能是绝对的又可能是相对的。一个绝对的路由以前导斜杠开头（如： `/site/index`），而一个相对的路由则没有（比如： `site/index` 或者 `index`）。一个相对的路由将会按照如下规则转换为绝对路由：

- 如果这个路由是一个空的字符串，将会使用当前 [[\yii\web\Controller::route|route]] 作为路由；
- 如果这个路由不带任何斜杠（比如 `index` ），它会被认为是当前控制器的一个 action ID，然后将会把 [[\yii\web\Controller::uniqueId]] 插入到路由前面。
- 如果这个路由不带前导斜杠（比如： `site/index` ），它会被认为是相对当前模块（module）的路由，然后将会把 [[\yii\base\Module::uniqueId|uniqueId]] 插入到路由前面。

从2.0.2版本开始，你可以用 [alias](concept-aliases.md) 来指定一个路由。在这种情况下， alias 将会首先转换为实际的路由，然后会按照上述规则转换为绝对路由。

以下是该方法的一些例子：

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

还有另外一个方法 `Url::to()` 和 [[toRoute()]] 非常类似。这两个方法的唯一区别在于，前者要求一个路由必须用数组来指定。如果传的参数为字符串，它将会被直接当做 URL 。

[aaa](#getting-common-urls)

The first argument could be:
         
- an array: [[toRoute()]] will be called to generate the URL. For example:
  `['site/index']`, `['post/index', 'page' => 2]`. Please refer to [[toRoute()]] for more details
  on how to specify a route.
- a string with a leading `@`: it is treated as an alias, and the corresponding aliased string
  will be returned.
- an empty string: the currently requested URL will be returned;
- a normal string: it will be returned as is.

`Url::to()` 的第一个参数可以是：

- 数组：将会调用 [[toRoute()]] 来生成URL。比如： `['site/index']`, `['post/index', 'page' => 2]` 。详细用法请参考 [[toRoute()]] 。
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


## 记住 URLs <span id="remember-urls"></span>

There are cases when you need to remember URL and afterwards use it during processing of the one of sequential requests.
It can be achieved in the following way:

有时，你需要记住一个 URL 并在后续的请求处理中使用它。你可以用以下方式达成这个目的：

 
```php
// Remember current URL 
Url::remember();

// Remember URL specified. See Url::to() for argument format.
Url::remember(['product/view', 'id' => 42]);

// Remember URL specified with a name given
Url::remember(['product/view', 'id' => 42], 'product');
```

In the next request we can get URL remembered in the following way:

在后续的请求，我们可以按照如下方式获得记住的 URL：

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## 检查相对 URLs <span id="checking-relative-urls"></span>

To find out if URL is relative i.e. it doesn't have host info part, you can use the following code:

你可以用如下代码检测一个 URL 是否是相对的（比如，包含主机信息部分）。
                             
```php
$isRelative = Url::isRelative('test/it');
```
