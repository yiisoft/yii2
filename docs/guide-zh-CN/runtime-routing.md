路由
=======

当[入口脚本](structure-entry-scripts.md)在调用 [[yii\web\Application::run()|run()]]
方法时，它进行的第一个操作就是解析输入的请求，然后实例化对应的[控制器动作](structure-controllers.md)处理这个请求。
该过程就被称为*引导路由（routing）*。
路由相反的操作会将给定的路由和参数生成一个可访问的URL地址，
这个操作叫做*创建URL*。
创建出来的URL被请求的时候，路由处理器可以解析成原始的路由信息和参数。


负责路由解析和创建URL的组件是 [[yii\web\UrlManager|URL管理器]],
URL管理器在[程序组件](structure-application-components.md)中被注册成 `urlManager`。
[[yii\web\UrlManager|URL管理器]] 提供方法 [[yii\web\UrlManager::parseRequest()|parseRequest()]] 来
解析请求的URL并返回路由信息和参数，
方法 [[yii\web\UrlManager::createUrl()|createUrl()]] 用来根据提供的路由和参数创建一个可访问的URL。
 
在程序配置中配置 `urlManager` 组件，可以让你的应用不改变现有代码的情况下
识别任意的URL格式。
例如使用下面的代码创建一个到 `post/view` 控制器的 URL：

```php
use yii\helpers\Url;

// Url::to() 将调用 UrlManager::createUrl() 来创建URL
$url = Url::to(['post/view', 'id' => 100]);
```

根据 `urlManager` 中的配置，创建出来的URL可能看起来像是以下的一种格式（或者其它的格式）。
如果此URL被访问，将被解析成原来的路由和参数。

```
/index.php?r=post%2Fview&id=100
/index.php/post/100
/posts/100
```


## URL 格式化 <span id="url-formats"></span>

[[yii\web\UrlManager|URL管理器]]提供两种URL格式：

- 默认URL格式；
- 美化URL格式。

默认URL格式使用一个参数`r`表示路由，
并且使用一般的参数格式表示请求参数。例如，`/index.php?r=post/view&id=100`表示路由为`post/view`，参数`id`为100。
默认URL格式不需要为[[yii\web\UrlManager|URL管理器]]做任何配置，
并且在任何Web服务器都可以正常使用。

美化URL格式在脚本名称后面使用更多的路径信息表示路由和参数信息。
例如，用适当的[[yii\web\UrlManager::rules|URL规则]]，`/index.php/post/100`中附加的路径信息`/post/100`表示
路由为`post/view`，参数`id`为100。
要使用美化的URL格式，你需要根据实际的需求
设计一组[[yii\web\UrlManager::rules|URL规则]]来规定URL的样式。
 
你可以仅设置[[yii\web\UrlManager|URL管理器]]中的[[yii\web\UrlManager::enablePrettyUrl|开启美化URL]]来切换两种URL格式，
而不必改动任何程序代码。


## 路由 <span id="routing"></span>

路由处理包含两个步骤：

- 请求被解析成一个路由和关联的参数；
- 路由相关的一个[控制器动作](structure-controllers.md#actions)被创建出来处理这个请求。


如果使用默认URL格式，解析请求到路由只是简单的从`GET`请求中得到命名为`r`的参数。


当使用用美化URL格式时，[[yii\web\UrlManager|URL管理器]]将检查注册的[[yii\web\UrlManager::rules|URL规则]]，
找到一条可以匹配的将请求转到路由的规则。
如果找不到任何匹配的规则，系统将抛出[[yii\web\NotFoundHttpException]]异常。

一旦请求解析成路由，系统将马上根据路由信息创建一个控制器动作。
路由信息根据`/`分解成多个部分。例如，`site/index`将被分解成`site`和`index`两部分。
每个部分都可能被认为是一个模块、控制器或动作的ID。
从路由的第一个部分开始，系统将执行以下步骤创建所需模块（如果有模块的话）、控制器和动作：


1. 设置应用系统作为当前的模块。
2. 检查当前模块中的[[yii\base\Module::controllerMap|控制器映射]]是否存在当前ID。
   如果存在，根据控制器映射中的定义创建一个控制器实例，
   跳到步骤5处理路由剩下的部分。
3. 检查ID是否为当前模块下[[yii\base\Module::modules|modules]]定义的子模块
   如果是，创建对应子模块，
   跳到步骤2使用刚创建的子模块处理路由下一部分。
4. 将ID作为一个[控制器ID](structure-controllers.md#controller-ids)并创建一个控制器实例，
   并用来处理路由剩下的部分。
5. 控制器在自己的[[yii\base\Controller::actions()|动作映射]]中查找当前ID。
   如果找到，根据映射中的定义创建一个动作。
   如果没找到，控制器将尝试根据[动作ID](structure-controllers.md#action-ids)定义的动作方法创建一个行内动作。

在上面的步骤中，如果出现任何错误，系统将抛出一个[[yii\web\NotFoundHttpException]]异常，
表示路由处理程序出现的错误信息。


### 缺省路由 <span id="default-route"></span>

如果传入请求并没有提供一个具体的路由，（一般这种情况多为于对首页的请求）此时就会启用由
[[yii\web\Application::defaultRoute]] 属性所指定的缺省路由。
该属性的默认值为 `site/index`，它指向 `site` 控制器的 `index`
动作。你可以像这样在应用配置中调整该属性的值：

```php
return [
    // ...
    'defaultRoute' => 'main/index',
];
```

和应用系统中的缺省路由类似，模块中也存在一个缺省路由，所以如果
有一个`user`模块，当请求到`user`模块时，模块的[[yii\base\Module::defaultRoute|缺省路由]]
被用来决定缺省的控制器。默认的缺省控制器名称是`default`。如果在[[yii\base\Module::defaultRoute|缺省路由]]中没有指定任何动作，
[[yii\base\Controller::defaultAction|缺省动作]]将被用来决定缺省的动作。
在这个例子中，完整的路由应该是`user/default/index`。


### `catchAll` 路由（全拦截路由） <span id="catchall-route"></span>

有时候，你会想要将你的 Web应用临时调整到维护模式，所有的请求下都会显示相同的信息页。
当然，要实现这一点有很多种方法。这里面最简单快捷的方法就是在应用配置中设置
[[yii\web\Application::catchAll]] 属性：

```php
return [
    // ...
    'catchAll' => ['site/offline'],
];
```

根据上面的配置，`site/offline`控制器将被用来处理所有的请求。

`catchAll`属性应该配置成一个数组，第一个元素为路由，
剩下的元素为键值对格式的[动作的参数](structure-controllers.md#action-parameters)。

> Info: 如果此属性被设置，开发环境中的[调试工具条](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md)
> 将被停用。


## 创建 URLs <span id="creating-urls"></span>

Yii提供了一个助手方法[[yii\helpers\Url::to()]]，用来根据提供的路由和参数创建各种各样的URL。 
例如：

```php
use yii\helpers\Url;

// 创建一个普通的路由URL：/index.php?r=post%2Findex
echo Url::to(['post/index']);

// 创建一个带路由参数的URL：/index.php?r=post%2Fview&id=100
echo Url::to(['post/view', 'id' => 100]);

// 创建一个带锚定的URL：/index.php?r=post%2Fview&id=100#content
echo Url::to(['post/view', 'id' => 100, '#' => 'content']);

// 创建一个绝对路径URL：https://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], true);

// 创建一个带https协议的绝对路径URL：https://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], 'https');
```

注意上面的例子中，我们假定使用默认的URL格式。如果启用美化的URL格式，
创建出来的URL根据使用的[[yii\web\UrlManager::rules|URL规则]]可能有所不同。

方法[[yii\helpers\Url::to()]]传入的路由是上下文相关的。
根据以下规则确认传入的路由是一个*相对的*路由还是*绝对的*路由：

- 如果路由是一个空字符串，则使用当前请求的[[yii\web\Controller::route|路由]]；
- 如果路由中不存在`/`，则被认为是一个当前控制器下的动作ID，
  且路由被附加到当前控制器的[[\yii\web\Controller::uniqueId|唯一ID]]后面；
- 如果路由不以`/`开头，被认为是当前模块下的路由，
  路由将被附加到当前模块的[[\yii\base\Module::uniqueId|唯一ID]]后面。

从版本 2.0.2 开始，你可以使用根据[别名](concept-aliases.md)中定义的别名路由。如果是这种情况，
别名将首先被转化为实际的路由，然后根据上面的规则转化成一个绝对路由。


例如，假设当前的模块为`admin`，当前控制器为`post`，

```php
use yii\helpers\Url;

// 当前请求路由：/index.php?r=admin%2Fpost%2Findex
echo Url::to(['']);

// 只有动作ID的相对路由：/index.php?r=admin%2Fpost%2Findex
echo Url::to(['index']);

// 相对路由：/index.php?r=admin%2Fpost%2Findex
echo Url::to(['post/index']);

// 绝对路由：/index.php?r=post%2Findex
echo Url::to(['/post/index']);

// 假设有一个"/post/index"的别名"@posts"：/index.php?r=post%2Findex
echo Url::to(['@posts']);
```

方法 [[yii\helpers\Url::to()]] 实际上调用了 [[yii\web\UrlManager|URL管理器]] 中的 [[yii\web\UrlManager::createUrl()|createUrl()]] 方法
和 [[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]] 方法。
下面的介绍中，我们将介绍如何配置 [[yii\web\UrlManager|URL管理器]] 来创建自定义的 URL 格式。


方法 [[yii\helpers\Url::to()]] 同时支持创建和任何路由不相关的 URL。
这种情况下，第一个参数不再传入一个数组，而是传入一个字符串。例如：
 
```php
use yii\helpers\Url;

// 当前请求URL：/index.php?r=admin%2Fpost%2Findex
echo Url::to();

// 设定了别名的URL：https://example.com
Yii::setAlias('@example', 'https://example.com/');
echo Url::to('@example');

// 绝对URL：https://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);
```

除了 `to()` 方法，[[yii\helpers\Url]] 助手类同时提供了多个其它创建 URL 的方法。
例如：

```php
use yii\helpers\Url;

// 主页URL：/index.php?r=site%2Findex
echo Url::home();

// 根URL，如果程序部署到一个Web目录下的子目录时非常有用
echo Url::base();

// 当前请求的权威规范URL
// 参考 https://en.wikipedia.org/wiki/Canonical_link_element
echo Url::canonical();

// 记住当前请求的URL并在以后获取
Url::remember();
echo Url::previous();
```


## 使用美化的 URL <span id="using-pretty-urls"></span>

要使用美化的URL，像下面这样在应用配置中配置`urlManager`组件：

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

[[yii\web\UrlManager::enablePrettyUrl|开启美化URL]] 属性被用来切换是否启用美化URL格式。
虽然除了[[yii\web\UrlManager::enablePrettyUrl|开启美化URL]] 属性以外其它属性都是可选的，但是上面的配置是最常用到的。

* [[yii\web\UrlManager::showScriptName|是否显示脚本名称]]：
  此属性决定创建的URL中是否包含入口脚本名称。
  例如，默认的 `/index.php/post/100`，开启此属性后将创建成 `/post/100`。
* [[yii\web\UrlManager::enableStrictParsing|是否开启严格解析]]：此属性决定是否开启严格的请求解析。
  如果设置为启用，请求的URL必须至少匹配 [[yii\web\UrlManager::rules|规则]] 中设定的一条规则作为正确请求，
  否则系统将抛出 [[yii\web\NotFoundHttpException]] 异常。
  如果严格解析被关闭，当 [[yii\web\UrlManager::rules|规则]] 中没有任何一条匹配时，
  请求URL中的路径信息将被作为请求路由使用。
* [[yii\web\UrlManager::rules|规则]]：此属性包含一个规则列表，用来规定如何解析和创建URL。
  这是一个主要属性，你应该根据特定的应用环境配置此属性用来生成特定格式的URL。


> Note: 如果你想在URL中隐藏入口脚本名称，除了要设置 [[yii\web\UrlManager::showScriptName|showScriptName]] 为 false，
  同时应该配置 Web 服务，处理当请求 URL 没有特殊指定入口脚本时确定要执行哪个PHP文件，
  如果你使用 Apache Web server，你可以参考[安装](start-installation.md#recommended-apache-configuration)中推荐的配置。




### URL规则 <span id="url-rules"></span>

一个URL规则是类 [[yii\web\UrlRule]] 或子类的一个实例。每个URL规则包含一个匹配URL中的路径、路由和少量参数的规则。
如果请求地址匹配一个URL规则，则此规则可以用来解析此请求。
如果生成URL时路由和参数符合一个URL规则，则此规则可以用来生成此URL。


如果开启了美化URL格式，[[yii\web\UrlManager|URL管理器]]使用定义的[[yii\web\UrlManager::rules|规则]]解析请求和创建URL。
尤其注意，[[yii\web\UrlManager|URL管理器]]按照规则中定义的顺序依次检测请求地址，
当找到*第一条*匹配的规则时停止。
匹配到的规则将被用来解析请求URL到指定的路由和参数。
同样的，创建URL的时候，[[yii\web\UrlManager|URL管理器]] 查找
第一条匹配的的规则并用来生成URL。

你可以配置 [[yii\web\UrlManager::rules]] 为一个数组，键为匹配规则，值为路由。
每条键值对为一条URL规则。例如，下面的 [[yii\web\UrlManager::rules|规则]]
配置了两条URL规则。第一条规则匹配URL `posts` 映射到路由 `post/index`。
第二条规则匹配符合正则表达式 `post/(\d+)` 的URL并映射到路由 `post/view`，同时包含
一个参数 `id`。

```php
[
    'posts' => 'post/index', 
    'post/<id:\d+>' => 'post/view',
]
```

> Info: 规则中的匹配模式用来匹配URL中的路径信息。例如，
  `/index.php/post/100?source=ad` 中的路径信息为
  `post/100`（开始和结尾处的 `/` 将被忽略）和模式 `post/(\d+)` 匹配。

除了定义 URL 规则外，你还可以将规则定义为配置数组。
每个配置数组用来配置一个单独的 URL 规则对象。如果你需要配置 URL 规则的其它参数时可以这样用。
例如：

```php
[
    // ...其它 URL 规则...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

如果你在URL规则中不配置 `class` 选项，默认将使用类 [[yii\web\UrlRule]]。



### 命名参数 <span id="named-parameters"></span>

一条URL规则可以对匹配模式中的参数设置格式为 `<ParamName:RegExp>` 的命名，
其中 `ParamName` 指定参数的名称，`RegExp` 是可选的用来匹配参数值得正则表达式。
如果没有设置 `RegExp`，表示参数值为不包含 `/` 的字符串。


> Note: 你可以仅针对参数设置正则表达式，其余部分设置普通文本。

当一条规则用来匹配URL时，符合匹配规则的相关的参数值被填充到规则中，
并且这些参数可以在 `request` 组件中使用 `$_GET` 获取到。
当规则用来创建 URL 时，
提供的参数值将被插入到规则定义的指定位置。

让我们使用一些例子来说明命名参数是如何工作的。假设我们定义了以下三条 URL 规则：

```php
[
    'posts/<year:\d{4}>/<category>' => 'post/index',
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
]
```

当规则用来解析 URL 时：

- 根据第二条规则，`/index.php/posts` 被解析成路由 `post/index`；
- 根据第一条规则，`/index.php/posts/2014/php` 被解析成路由 `post/index`，
  参数 `year` 的值是 2014，参数 `category` 的值是 `php`；
- 根据第三条规则，`/index.php/post/100` 被解析成路由 `post/view`，
  参数 `id` 的值是 100；
- 当[[yii\web\UrlManager::enableStrictParsing]] 设置为 `true` 时，`/index.php/posts/php` 将导致一个[[yii\web\NotFoundHttpException]] 异常，
  因为无法匹配任何规则。如果 [[yii\web\UrlManager::enableStrictParsing]] 设为 `false`（默认值），
  路径部分 `posts/php` 将被作为路由。

当规则用来生成 URL 时：

- 根据第二条规则 `Url::to(['post/index'])` 生成 `/index.php/posts`；
- 根据第一条规则 `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` 生成 `/index.php/posts/2014/php`；
- 根据第三条规则 `Url::to(['post/view', 'id' => 100])` 生成 `/index.php/post/100`；
- 根据第三条规则 `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` 生成 `/index.php/post/100?source=ad`。
  因为参数 `source` 在规则中没有指定，将被作为普通请求参数附加到生成的 URL 后面。
- `Url::to(['post/index', 'category' => 'php'])` 生成 `/index.php/post/index?category=php`。
  注意因为没有任何规则适用，将把路由信息当做路径信息来生成URL，
  并且所有参数作为请求查询参数附加到 URL 后面。
   

### 参数化路由 <span id="parameterizing-routes"></span>

你可以在 URL 规则中嵌入参数名称，这样可以允许一个 URL 规则用来匹配多个路由。
例如，下面的规则在路由中嵌入了 `controller` 和 `action` 两个参数。

```php
'rules' => [
    '<controller:(post|comment)>/create' => '<controller>/create',
    '<controller:(post|comment)>/<id:\d+>/<action:(update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/view',
    '<controller:(post|comment)>s' => '<controller>/index',
]
```

解析 URL `/index.php/comment/100/update` 时，第二条规则适用，设置参数 `controller` 为 `comment`，
设置参数 `action` 为 `update`。自然的，路由 `<controller>/<action>` 变成了 `comment/update`。

同样的，根据路由 `comment/index` 创建 URL 时，最后一条规则适用，将生成 URL `/index.php/comments`。

> Info: 使用参数化的路由，可以显著的减少 URL 规则的数量，
  可以显著提高[[yii\web\UrlManager|URL管理器]]的效率。

### 默认参数值 <span id="default-parameter-values"></span>

默认的，所有规则中定义的参数都是必须的。如果一个请求 URL 不存在其中一个参数，
或者创建URL时没有指定其中一个参数，则无法应用此规则。
如果需要设置某些参数为可选的，必须设置规则的[[yii\web\UrlRule::defaults|默认值]]属性。
此属性中列出的参数将变成可选的且在没有指定时会使用此处设置的默认值。

下面设置的规则中，参数 `page` 和 `tag` 都是可选的，当没有指定时将分别使用值 1 和空字符串。


```php
[
    // ...其它规则...
    [
        'pattern' => 'posts/<page:\d+>/<tag>',
        'route' => 'post/index',
        'defaults' => ['page' => 1, 'tag' => ''],
    ],
]
```

上面的规则可以用来解析或创建下面的 URL：

* `/index.php/posts`：`page` 为 1，`tag` 为 ''。
* `/index.php/posts/2`：`page` 为 2，`tag` 为 ''。
* `/index.php/posts/2/news`：`page` 为 2，`tag`为 `'news'`。
* `/index.php/posts/news`：`page` 为 1，`tag` 为 `'news'`。

如果不使用可选参数，你必须创建 4 条规则才可以实现相同的效果。

> Note: 如果 [[yii\web\UrlRule::$pattern|pattern]] 中仅包含可选参数和斜杠，
  只有所有参数被忽略时第一个参数才被忽略。


### 带服务名称的规则 <span id="rules-with-server-names"></span>

可以在URL规则中设置Web服务的名称，如果你需要使你的应用程序在不同的Web服务名称下表现不同的话。
例如，下面的规则将URL`https://admin.example.com/login`解析成路由`admin/user/login`，
URL`https://www.example.com/login`解析成路由`site/login`。

```php
[
    'https://admin.example.com/login' => 'admin/user/login',
    'https://www.example.com/login' => 'site/login',
]
```

你还可以在服务名称中嵌入参数用来动态的提取服务名称。例如，下面的规则
将URL`https://en.example.com/posts`解析成路由`post/index`且参数`language=en`。

```php
[
    'http://<language:\w+>.example.com/posts' => 'post/index',
]
```

从版本 2.0.11 开始，你还可以使用不带协议类型的模式来同时匹配 `http` 和 `https`。
规则语法和上面相比只是忽略掉 `http:` 部分，例如：`'//www.example.com/login' => 'site/login'`。

> Note: 带服务名称的规则**不应该**包含任何子目录。例如，如果程序入口脚本在 `https://www.example.com/sandbox/blog/index.php`，
  应该使用 `https://www.example.com/posts` 代替 `https://www.example.com/sandbox/blog/posts`。
  这样才可以将你的应用部署到任何目录而不需要更改 URL 规则。Yii 将会自动的检测应用程序所在的根目录。


### URL 后缀 <span id="url-suffixes"></span>

你可能因为各种目的需要在 URL 后面添加后缀。例如，你可以在URL后面添加 `.html` 让其看起来像是一个 HTML 页面；
也可以添加 `.json` 用来表明需要的返回值内容类型。
可以参考下面的系统配置，
通过设置 [[yii\web\UrlManager::suffix]] 属性来达到此目的:

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

上面的配置允许[[yii\web\UrlManager|URL管理器]]识别或生成带 `.html` 后缀的 URL。


> Tip: 你可以设置URL后缀为 `/` 让所有的 URL 以斜线结束。

> Note: 当你配置 URL 后缀时，如果请求的 URL 没有此后缀，系统将认为此 URL 无法识别。
  这是 SEO（搜索引擎优化）的最佳实践。
  
有时你可能需要在不同的URL使用不同的后缀。可以通过在不同的URL规则下不同的设置[[yii\web\UrlRule::suffix|后缀]]属性。
URL规则中此属性将覆盖在[[yii\web\UrlManager|URL管理器]]中设置的值。
例如，下面的配置中全局使用 `.html` 后缀，但是定义了一个自定义的使用 `.json` 为后缀的规则。


```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            // ...
            'suffix' => '.html',
            'rules' => [
                // ...
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.json',
                ],
            ],
        ],
    ],
]
```

### HTTP 方法 <span id="http-methods"></span>

当使用 RESTful 接口时，经常需要根据 HTTP 请求方法将同样的URL解析到不同的路由。
可以容易的通过将支持的 HTTP 方法设置为 URL 规则的前缀来实现这个目的。
如果一个规则需要支持多种 HTTP 方法，可以将方法名称用逗号隔开。
例如，下面的规则有相同的模式 `post/<id:\d+>` 但是支持不同的 HTTP 方法。
一个 `PUT post/100` 请求将被解析到 `post/update`，`GET post/100` 请求将被解析到 `post/view`。

```php
'rules' => [
    'PUT,POST post/<id:\d+>' => 'post/update',
    'DELETE post/<id:\d+>' => 'post/delete',
    'post/<id:\d+>' => 'post/view',
]
```

> Note: 如果一个 URL 规则包含 HTTP 方法，这个规则将只能用来解析请求，除非 `GET` 请求明确被指定在 HTTP 方法中，
  否则创建 URL 时此规则将被[[yii\web\UrlManager|URL管理器]]忽略。

> Tip: 为了简化 RESTful 接口的路由定义，Yii 提供了一个特殊的URL规则类 [[yii\rest\UrlRule]]
  支持高效的且支持一些设想中的功能，像自动多元化控制器 ID。
  更多信息，请参考 RESTful 接口说明中的[路由](rest-routing.md)章节。


### 动态添加规则 <span id="adding-rules"></span>

URL规则可以动态添加到[[yii\web\UrlManager|URL管理器]]。如果[模块](structure-modules.md)需要管理自己的URL规则时很有必要。
如果需要使路由处理过程中动态添加的规则可用，
你应该在应用程序[启动引导](runtime-bootstrapping.md)时添加。
对模块来说，需要实现 [[yii\base\BootstrapInterface]] 接口的 [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] 方法，
类似下面这样动态添加规则：

```php
public function bootstrap($app)
{
    $app->getUrlManager()->addRules([
        // 规则在这里定义
    ], false);
}
```

注意你需要同时在 [[yii\web\Application::bootstrap]] 中指定这些模块，这样模块才可以参与到
[启动引导](runtime-bootstrapping.md)过程中。


### 创建规则类 <span id="creating-rules"></span>

尽管默认的 [[yii\web\UrlRule]] 类已经足够灵活可以处理大部分项目了，
有时还是会需要创建一个自定义的规则类。例如，在一个汽车经销网站，你可能会需要使用
这样的URL格式 `/Manufacturer/Model`，`Manufacturer` 和 `Model` 必须同时匹配保存在数据库中的一些数据。
默认的规则类只能使用静态定义而无法适应此种情况。

我们可以创建一个自定义的 URL 规则类来解决这个问题。

```php
<?php

namespace app\components;

use yii\web\UrlRuleInterface;
use yii\base\BaseObject;

class CarUrlRule extends BaseObject implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {
        if ($route === 'car/index') {
            if (isset($params['manufacturer'], $params['model'])) {
                return $params['manufacturer'] . '/' . $params['model'];
            } elseif (isset($params['manufacturer'])) {
                return $params['manufacturer'];
            }
        }
        return false; // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            // 检查 $matches[1] 和 $matches[3]
            // 确认是否匹配到一个数据库中保存的厂家和型号。
            // 如果匹配，设置参数 $params['manufacturer'] 和 / 或 $params['model']
            // 返回 ['car/index', $params]
        }
        return false; // 本规则不会起作用
    }
}
```

在 [[yii\web\UrlManager::rules]] 配置中设置新定义的规则类：

```php
'rules' => [
    // ...其它规则...
    [
        'class' => 'app\components\CarUrlRule',
        // ...配置其它参数...
    ],
]
```


## URL规范化 <span id="url-normalization"></span>

从 2.0.10 版开始[[yii\web\UrlManager|Url管理器]]可以配置用[[yii\web\UrlNormalizer|URL规范器]]来处理
相同URL的不同格式，例如是否带结束斜线。因为技术上来说 `https://example.com/path`
和 `https://example.com/path/` 是完全不同的 URL，两个地址返回相同的内容会导致SEO排名降低。
默认情况下 URL 规范器会合并连续的斜线，根据配置决定是否添加或删除结尾斜线，
并且会使用[永久重定向](https://zh.wikipedia.org/wiki/HTTP_301)将地址重新跳转到规范化后的URL。
URL规范器可以针对URL管理器全局配置，也可以针对规则单独配置 - 默认每个规则都使用URL管理器中的规范器。
你可以针对特定的URL规则设置 [[yii\web\UrlRule::$normalizer|UrlRule::$normalizer]] 为 `false` 来关闭规范化。


下面的例子显示了一个[[yii\web\UrlNormalizer|URL规范器]]的配置：

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'suffix' => '.html',
    'normalizer' => [
        'class' => 'yii\web\UrlNormalizer',
        // 调试时使用临时跳转代替永久跳转
        'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
    ],
    'rules' => [
        // ...其它规则...
        [
            'pattern' => 'posts',
            'route' => 'post/index',
            'suffix' => '/',
            'normalizer' => false, // 针对此规则关闭规范器
        ],
        [
            'pattern' => 'tags',
            'route' => 'tag/index',
            'normalizer' => [
                // 针对此规则不合并连续的斜线
                'collapseSlashes' => false,
            ],
        ],
    ],
]
```

> Note: 默认 [[yii\web\UrlManager::$normalizer|UrlManager::$normalizer]] 规范器是关闭的。你需要明确配置其开启
  来启用 URL 规范化。



## 性能考虑 <span id="performance-consideration"></span>

在开发复杂的 Web 应用程序时，优化 URL 规则非常重要，以便解析请求和创建 URL 所需
的时间更少。

通过使用参数化路由，您可以减少 URL 规则的数量，这可以显著提高性能。

当解析或创建URL时，[[yii\web\UrlManager|URL manager]] 按照它们声明的顺序检查 URL 规则。
因此，您可以考虑调整 URL 规则的顺序，以便在较少使用的规则之前放置更具体和/或更常用的规则。

如果多个 URL 规则使用相同的前缀，你可以考虑使用 [[yii\web\GroupUrlRule]]，
这样作为一个组合，[[yii\web\UrlManager|URL管理器]]会更高效。
特别是当应用程序由模块组合而成时，每个模块都有各自的 URL 规则且都有各自的模块 ID 作为前缀。
