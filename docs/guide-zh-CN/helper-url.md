Url 助手类（Url Helper）
=====================

Url 帮助类提供一系列的静态方法来帮助管理 URL。


## 获得通用 URL（Getting Common URLs） <span id="getting-common-urls"></span>

有两种获取通用 URLS 的方法 ：当前请求的 home URL 和 base URL 。
为了获取 home URL ，使用如下代码：

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

如果没有传任何参数，这个方法将会生成相对 URL 。你可以传 `true` 来获得一个针对当前协议的绝对 URL；
或者，你可以明确的指定具体的协议类型（ `https` , `http` ）。

如下代码可以获得当前请求的 base URL：

 ```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

这个方法的调用方式和 `Url::home()` 的完全一样。


## 创建 URLs（Creating URLs） <span id="creating-urls"></span>

为了创建一个给定路由的 URL 地址，请使用 `Url::toRoute()`方法。 这个方法使用 [[\yii\web\UrlManager]] 
来创建一个 URL ：

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```

你可以指定一个字符串来作为路由，如： `site/index` 。如果想要指定将要被创建的 URL 的附加查询参数，
你同样可以使用一个数组来作为路由。数组的格式须为：

```php
// generates: /index.php?r=site/index&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

如果你想要创建一个带有 anchor 的 URL ，你可以使用一个带有 `#` 参数的数组。比如：

```php
// generates: /index.php?r=site/index&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```
  
一个路由既可能是绝对的又可能是相对的。一个绝对的路由以前导斜杠开头（如： `/site/index`），
而一个相对的路由则没有（比如： `site/index` 或者 `index`）。一个相对的路由将会按照如下规则转换为绝对路由：

- 如果这个路由是一个空的字符串，将会使用当前 [[\yii\web\Controller::route|route]] 作为路由；
- 如果这个路由不带任何斜杠（比如 `index` ），它会被认为是当前控制器的一个 action ID，
  然后将会把 [[\yii\web\Controller::uniqueId]] 插入到路由前面。
- 如果这个路由不带前导斜杠（比如： `site/index` ），它会被认为是相对当前模块（module）的路由，
  然后将会把 [[\yii\base\Module::uniqueId|uniqueId]] 插入到路由前面。

从2.0.2版本开始，你可以用 [alias](concept-aliases.md) 来指定一个路由。
在这种情况下， alias 将会首先转换为实际的路由，
然后会按照上述规则转换为绝对路由。

以下是该方法的一些例子：

```php
// /index.php?r=site/index
echo Url::toRoute('site/index');

// /index.php?r=site/index&src=ref1#name
echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     假设别名 "@postEdit" 被定义为 "post/edit"
echo Url::toRoute(['@postEdit', 'id' => 100]);

// http://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', true);

// https://www.example.com/index.php?r=site/index
echo Url::toRoute('site/index', 'https');
```

还有另外一个方法 `Url::to()` 和 [[toRoute()]] 非常类似。这两个方法的唯一区别在于，前者要求一个路由必须用数组来指定。
如果传的参数为字符串，它将会被直接当做 URL 。

`Url::to()` 的第一个参数可以是：

- 数组：将会调用 [[toRoute()]] 来生成URL。比如：
  `['site/index']`, `['post/index', 'page' => 2]` 。
  详细用法请参考 [[toRoute()]] 。
- 带前导 `@` 的字符串：它将会被当做别名，
  对应的别名字符串将会返回。
- 空的字符串：当前请求的 URL 将会被返回；
- 普通的字符串：返回本身。

当 `$scheme` 指定了（无论是字符串还是 true ），一个带主机信息（通过 [[\yii\web\UrlManager::hostInfo]] 获得）
的绝对 URL 将会被返回。如果 `$url` 已经是绝对 URL 了，
它的协议信息将会被替换为指定的（ https 或者 http ）。

以下是一些使用示例：

```php
// /index.php?r=site/index
echo Url::to(['site/index']);

// /index.php?r=site/index&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post/edit&id=100     假设别名 "@postEdit" 被定义为 "post/edit"
echo Url::to(['@postEdit', 'id' => 100]);

// 当前请求的 URL
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

从2.0.3版本开始，你可以使用 [[yii\helpers\Url::current()]] 来创建一个基于当前请求路由和 GET 参数的 URL。
你可以通过传递一个 `$params` 给这个方法来添加或者删除 GET 参数。
例如：

```php
// 假设 $_GET = ['id' => 123, 'src' => 'google']，当前路由为 "post/view"

// /index.php?r=post/view&id=123&src=google
echo Url::current();

// /index.php?r=post/view&id=123
echo Url::current(['src' => null]);
// /index.php?r=post/view&id=100&src=google
echo Url::current(['id' => 100]);
```


## 记住 URLs（Remember URLs） <span id="remember-urls"></span>

有时，你需要记住一个 URL 并在后续的请求处理中使用它。
你可以用以下方式达到这个目的：
 
```php
// 记住当前 URL 
Url::remember();

// 记住指定的 URL。参数格式请参阅 Url::to()。
Url::remember(['product/view', 'id' => 42]);

// 记住用给定名称指定的 URL
Url::remember(['product/view', 'id' => 42], 'product');
```

在后续的请求处理中，可以用如下方式获得记住的 URL：

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## 检查相对 URLs（Checking Relative URLs） <span id="checking-relative-urls"></span>

你可以用如下代码检测一个 URL 是否是相对的（比如，包含主机信息部分）。
                             
```php
$isRelative = Url::isRelative('test/it');
```
