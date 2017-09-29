Routing and URL Creation
========================

When a Yii application starts processing a requested URL, the first step it takes is to parse the URL
into a [route](structure-controllers.md#routes). The route is then used to instantiate the corresponding
[controller action](structure-controllers.md) to handle the request. This whole process is called *routing*.

The reverse process of routing is called *URL creation*, which creates a URL from a given route
and the associated query parameters. When the created URL is later requested, the routing process can resolve it
back into the original route and query parameters.

The central piece responsible for routing and URL creation is the [[yii\web\UrlManager|URL manager]],
which is registered as the `urlManager` [application component](structure-application-components.md). The [[yii\web\UrlManager|URL manager]]
provides the [[yii\web\UrlManager::parseRequest()|parseRequest()]] method to parse an incoming request into
a route and the associated query parameters and the [[yii\web\UrlManager::createUrl()|createUrl()]] method to
create a URL from a given route and its associated query parameters.

By configuring the `urlManager` component in the application configuration, you can let your application
recognize arbitrary URL formats without modifying your existing application code. For example, you can
use the following code to create a URL for the `post/view` action:

```php
use yii\helpers\Url;

// Url::to() calls UrlManager::createUrl() to create a URL
$url = Url::to(['post/view', 'id' => 100]);
```

Depending on the `urlManager` configuration, the created URL may look like one of the following (or other format).
And if the created URL is requested later, it will still be parsed back into the original route and query parameter value.

```
/index.php?r=post%2Fview&id=100
/index.php/post/100
/posts/100
```


## URL Formats <span id="url-formats"></span>

The [[yii\web\UrlManager|URL manager]] supports two URL formats:

- the default URL format;
- the pretty URL format.

The default URL format uses a [[yii\web\UrlManager::$routeParam|query parameter]] named `r` to represent the route and normal query parameters
to represent the query parameters associated with the route. For example, the URL `/index.php?r=post/view&id=100` represents
the route `post/view` and the `id` query parameter `100`. The default URL format does not require any configuration of
the [[yii\web\UrlManager|URL manager]] and works in any Web server setup.

The pretty URL format uses the extra path following the entry script name to represent the route and the associated
query parameters. For example, the extra path in the URL `/index.php/post/100` is `/post/100` which may represent
the route `post/view` and the `id` query parameter `100` with a proper [[yii\web\UrlManager::rules|URL rule]]. To use
the pretty URL format, you will need to design a set of [[yii\web\UrlManager::rules|URL rules]] according to the actual
requirement about how the URLs should look like.

You may switch between the two URL formats by toggling the [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]]
property of the [[yii\web\UrlManager|URL manager]] without changing any other application code.


## Routing <span id="routing"></span>

Routing involves two steps:

- the incoming request is parsed into a route and the associated query parameters;
- a [controller action](structure-controllers.md#actions) corresponding to the parsed route
is created to handle the request.

When using the default URL format, parsing a request into a route is as simple as getting the value of a `GET`
query parameter named `r`.

When using the pretty URL format, the [[yii\web\UrlManager|URL manager]] will examine the registered
[[yii\web\UrlManager::rules|URL rules]] to find matching one that can resolve the request into a route.
If such a rule cannot be found, a [[yii\web\NotFoundHttpException]] exception will be thrown.

Once the request is parsed into a route, it is time to create the controller action identified by the route.
The route is broken down into multiple parts by the slashes in it. For example, `site/index` will be
broken into `site` and `index`. Each part is an ID which may refer to a module, a controller or an action.
Starting from the first part in the route, the application takes the following steps to create modules (if any),
controller and action:

1. Set the application as the current module.
2. Check if the [[yii\base\Module::controllerMap|controller map]] of the current module contains the current ID.
   If so, a controller object will be created according to the controller configuration found in the map,
   and Step 5 will be taken to handle the rest part of the route.
3. Check if the ID refers to a module listed in the [[yii\base\Module::modules|modules]] property of
   the current module. If so, a module is created according to the configuration found in the module list,
   and Step 2 will be taken to handle the next part of the route under the context of the newly created module.
4. Treat the ID as a [controller ID](structure-controllers.md#controller-ids) and create a controller object. Do the next step with the rest part of
   the route.
5. The controller looks for the current ID in its [[yii\base\Controller::actions()|action map]]. If found,
   it creates an action according to the configuration found in the map. Otherwise, the controller will
   attempt to create an inline action which is defined by an action method corresponding to the current [action ID](structure-controllers.md#action-ids).

Among the above steps, if any error occurs, a [[yii\web\NotFoundHttpException]] will be thrown, indicating
the failure of the routing process.


### Default Route <span id="default-route"></span>

When a request is parsed into an empty route, the so-called *default route* will be used, instead. By default,
the default route is `site/index`,  which refers to the `index` action of the `site` controller. You may
customize it by configuring the [[yii\web\Application::defaultRoute|defaultRoute]] property of the application
in the application configuration like the following:

```php
[
    // ...
    'defaultRoute' => 'main/index',
];
```

Similar to the default route of the application, there is also a default route for modules, so for example if there
is a `user` module and the request is parsed into the route `user` the module's [[yii\base\Module::defaultRoute|defaultRoute]]
is used to determine the controller. By default the controller name is `default`. If no action is specified in [[yii\base\Module::defaultRoute|defaultRoute]],
the [[yii\base\Controller::defaultAction|defaultAction]] property of the controller is used to determine the action.
In this example, the full route would be `user/default/index`.


### `catchAll` Route <span id="catchall-route"></span>

Sometimes, you may want to put your Web application in maintenance mode temporarily and display the same
informational page for all requests. There are many ways to accomplish this goal. But one of the simplest
ways is to configure the [[yii\web\Application::catchAll]] property like the following in the application configuration:

```php
[
    // ...
    'catchAll' => ['site/offline'],
];
```

With the above configuration, the `site/offline` action will be used to handle all incoming requests.

The `catchAll` property should take an array whose first element specifies a route, and
the rest of the elements (name-value pairs) specify the parameters to be [bound to the action](structure-controllers.md#action-parameters).

> Info: The [debug toolbar](https://github.com/yiisoft/yii2-debug/blob/master/docs/guide/README.md) in development environment
> will not work when this property is enabled.


## Creating URLs <span id="creating-urls"></span>

Yii provides a helper method [[yii\helpers\Url::to()]] to create various kinds of URLs from given routes and
their associated query parameters. For example,

```php
use yii\helpers\Url;

// creates a URL to a route: /index.php?r=post%2Findex
echo Url::to(['post/index']);

// creates a URL to a route with parameters: /index.php?r=post%2Fview&id=100
echo Url::to(['post/view', 'id' => 100]);

// creates an anchored URL: /index.php?r=post%2Fview&id=100#content
echo Url::to(['post/view', 'id' => 100, '#' => 'content']);

// creates an absolute URL: http://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], true);

// creates an absolute URL using the https scheme: https://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], 'https');
```

Note that in the above example, we assume the default URL format is being used. If the pretty URL format is enabled,
the created URLs will be different, according to the [[yii\web\UrlManager::rules|URL rules]] in use.

The route passed to the [[yii\helpers\Url::to()]] method is context sensitive. It can be either a *relative* route
or an *absolute* route which will be normalized according to the following rules:

- If the route is an empty string, the currently requested [[yii\web\Controller::route|route]] will be used;
- If the route contains no slashes at all, it is considered to be an action ID of the current controller
  and will be prepended with the [[\yii\web\Controller::uniqueId|uniqueId]] value of the current controller;
- If the route has no leading slash, it is considered to be a route relative to the current module and
  will be prepended with the [[\yii\base\Module::uniqueId|uniqueId]] value of the current module.

Starting from version 2.0.2, you may specify a route in terms of an [alias](concept-aliases.md). If this is the case,
the alias will first be converted into the actual route which will then be turned into an absolute route according
to the above rules.

For example, assume the current module is `admin` and the current controller is `post`,

```php
use yii\helpers\Url;

// currently requested route: /index.php?r=admin%2Fpost%2Findex
echo Url::to(['']);

// a relative route with action ID only: /index.php?r=admin%2Fpost%2Findex
echo Url::to(['index']);

// a relative route: /index.php?r=admin%2Fpost%2Findex
echo Url::to(['post/index']);

// an absolute route: /index.php?r=post%2Findex
echo Url::to(['/post/index']);

// using an alias "@posts", which is defined as "/post/index": /index.php?r=post%2Findex
echo Url::to(['@posts']);
```

The [[yii\helpers\Url::to()]] method is implemented by calling the [[yii\web\UrlManager::createUrl()|createUrl()]]
and [[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]] methods of the [[yii\web\UrlManager|URL manager]].
In the next few subsections, we will explain how to configure the [[yii\web\UrlManager|URL manager]] to customize
the format of the created URLs.

The [[yii\helpers\Url::to()]] method also supports creating URLs that are **not** related with particular routes.
Instead of passing an array as its first parameter, you should pass a string in this case. For example,

```php
use yii\helpers\Url;

// currently requested URL: /index.php?r=admin%2Fpost%2Findex
echo Url::to();

// an aliased URL: http://example.com
Yii::setAlias('@example', 'http://example.com/');
echo Url::to('@example');

// an absolute URL: http://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);
```

Besides the `to()` method, the [[yii\helpers\Url]] helper class also provides several other convenient URL creation
methods. For example,

```php
use yii\helpers\Url;

// home page URL: /index.php?r=site%2Findex
echo Url::home();

// the base URL, useful if the application is deployed in a sub-folder of the Web root
echo Url::base();

// the canonical URL of the currently requested URL
// see https://en.wikipedia.org/wiki/Canonical_link_element
echo Url::canonical();

// remember the currently requested URL and retrieve it back in later requests
Url::remember();
echo Url::previous();
```


## Using Pretty URLs <span id="using-pretty-urls"></span>

To use pretty URLs, configure the `urlManager` component in the application configuration like the following:

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

The [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] property is mandatory as it toggles the pretty URL format.
The rest of the properties are optional. However, their configuration shown above is most commonly used.

* [[yii\web\UrlManager::showScriptName|showScriptName]]: this property determines whether the entry script
  should be included in the created URLs. For example, instead of creating a URL `/index.php/post/100`,
  by setting this property to be `false`, a URL `/post/100` will be generated.
* [[yii\web\UrlManager::enableStrictParsing|enableStrictParsing]]: this property determines whether to enable
  strict request parsing. If strict parsing is enabled, the incoming requested URL must match at least one of
  the [[yii\web\UrlManager::rules|rules]] in order to be treated as a valid request, otherwise a [[yii\web\NotFoundHttpException]]
  will be thrown. If strict parsing is disabled, when none of the [[yii\web\UrlManager::rules|rules]] matches
  the requested URL, the path info part of the URL will be treated as the requested route.
* [[yii\web\UrlManager::rules|rules]]: this property contains a list of rules specifying how to parse and create
  URLs. It is the main property that you should work with in order to create URLs whose format satisfies your
  particular application requirement.

> Note: In order to hide the entry script name in the created URLs, besides setting
  [[yii\web\UrlManager::showScriptName|showScriptName]] to be `false`, you may also need to configure your Web server
  so that it can correctly identify which PHP script should be executed when a requested URL does not explicitly
  specify one. If you are using Apache or nginx Web server, you may refer to the recommended configuration as described in the
  [Installation](start-installation.md#recommended-apache-configuration) section.


### URL Rules <span id="url-rules"></span>

A URL rule is a class implementing the [[yii\web\UrlRuleInterface]], usually [[yii\web\UrlRule]]. Each URL rule consists of a pattern used
for matching the path info part of URLs, a route, and a few query parameters. A URL rule can be used to parse a request
if its pattern matches the requested URL. A URL rule can be used to create a URL if its route and query parameter
names match those that are given.

When the pretty URL format is enabled, the [[yii\web\UrlManager|URL manager]] uses the URL rules declared in its
[[yii\web\UrlManager::rules|rules]] property to parse incoming requests and create URLs. In particular,
to parse an incoming request, the [[yii\web\UrlManager|URL manager]] examines the rules in the order they are
declared and looks for the *first* rule that matches the requested URL. The matching rule is then used to
parse the URL into a route and its associated parameters. Similarly, to create a URL, the [[yii\web\UrlManager|URL manager]]
looks for the first rule that matches the given route and parameters and uses that to create a URL.

You can configure [[yii\web\UrlManager::rules]] as an array with keys being the [[yii\web\UrlRule::$pattern|patterns]] and values the corresponding
[[yii\web\UrlRule::$route|routes]]. Each pattern-route pair constructs a URL rule. For example, the following [[yii\web\UrlManager::rules|rules]]
configuration declares two URL rules. The first rule matches a URL `posts` and maps it into the route `post/index`.
The second rule matches a URL matching the regular expression `post/(\d+)` and maps it into the route `post/view` and
defines a query parameter named `id`.

```php
'rules' => [
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
]
```

> Info: The pattern in a rule is used to match the path info part of a URL. For example, the path info of
  `/index.php/post/100?source=ad` is `post/100` (the leading and ending slashes are ignored) which matches
  the pattern `post/(\d+)`.

Besides declaring URL rules as pattern-route pairs, you may also declare them as configuration arrays. Each configuration
array is used to configure a single URL rule object. This is often needed when you want to configure other
properties of a URL rule. For example,

```php
'rules' => [
    // ...other url rules...
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

By default if you do not specify the `class` option for a rule configuration, it will take the default
class [[yii\web\UrlRule]], which is the default value defined in
[[yii\web\UrlManager::$ruleConfig]].


### Named Parameters <span id="named-parameters"></span>

A URL rule can be associated with named query parameters which are specified in the pattern in the format
of `<ParamName:RegExp>`, where `ParamName` specifies the parameter name and `RegExp` is an optional regular
expression used to match parameter values. If `RegExp` is not specified, it means the parameter value should be
a string without any slash.

> Note: You can only use regular expressions inside of parameters. The rest of a pattern is considered plain text.

When a rule is used to parse a URL, it will fill the associated parameters with values matching the corresponding
parts of the URL, and these parameters will be made available in `$_GET` later by the `request` application component.
When the rule is used to create a URL, it will take the values of the provided parameters and insert them at the
places where the parameters are declared.

Let's use some examples to illustrate how named parameters work. Assume we have declared the following three URL rules:

```php
'rules' => [
    'posts/<year:\d{4}>/<category>' => 'post/index',
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
]
```

When the rules are used to parse URLs:

- `/index.php/posts` is parsed into the route `post/index` using the second rule;
- `/index.php/posts/2014/php` is parsed into the route `post/index`, the `year` parameter whose value is 2014
  and the `category` parameter whose value is `php` using the first rule;
- `/index.php/post/100` is parsed into the route `post/view` and the `id` parameter whose value is 100 using
  the third rule;
- `/index.php/posts/php` will cause a [[yii\web\NotFoundHttpException]] when [[yii\web\UrlManager::enableStrictParsing]]
  is `true`, because it matches none of the patterns. If [[yii\web\UrlManager::enableStrictParsing]] is `false` (the
  default value), the path info part `posts/php` will be returned as the route. This will either execute the corresponding action if it exists or throw a [[yii\web\NotFoundHttpException]] otherwise.

And when the rules are used to create URLs:

- `Url::to(['post/index'])` creates `/index.php/posts` using the second rule;
- `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` creates `/index.php/posts/2014/php` using the first rule;
- `Url::to(['post/view', 'id' => 100])` creates `/index.php/post/100` using the third rule;
- `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` creates `/index.php/post/100?source=ad` using the third rule.
  Because the `source` parameter is not specified in the rule, it is appended as a query parameter in the created URL.
- `Url::to(['post/index', 'category' => 'php'])` creates `/index.php/post/index?category=php` using none of the rules.
  Note that since none of the rules applies, the URL is created by simply appending the route as the path info
  and all parameters as the query string part.


### Parameterizing Routes <span id="parameterizing-routes"></span>

You can embed parameter names in the route of a URL rule. This allows a URL rule to be used for matching multiple
routes. For example, the following rules embed `controller` and `action` parameters in the routes.

```php
'rules' => [
    '<controller:(post|comment)>/create' => '<controller>/create',
    '<controller:(post|comment)>/<id:\d+>/<action:(update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/view',
    '<controller:(post|comment)>s' => '<controller>/index',
]
```

To parse a URL `/index.php/comment/100/update`, the second rule will apply, which sets the `controller` parameter to
be `comment` and `action` parameter to be `update`. The route `<controller>/<action>` is thus resolved as `comment/update`.

Similarly, to create a URL for the route `comment/index`, the last rule will apply, which creates a URL `/index.php/comments`.

> Info: By parameterizing routes, it is possible to greatly reduce the number of URL rules, which can significantly
  improve the performance of [[yii\web\UrlManager|URL manager]].

### Default Parameter Values <span id="default-parameter-values"></span>

By default, all parameters declared in a rule are required. If a requested URL does not contain a particular parameter,
or if a URL is being created without a particular parameter, the rule will not apply. To make some of the parameters
optional, you can configure the [[yii\web\UrlRule::defaults|defaults]] property of a rule. Parameters listed in this
property are optional and will take the specified values when they are not provided.

In the following rule declaration, the `page` and `tag` parameters are both optional and will take the value of 1 and
empty string, respectively, when they are not provided.

```php
'rules' => [
    // ...other rules...
    [
        'pattern' => 'posts/<page:\d+>/<tag>',
        'route' => 'post/index',
        'defaults' => ['page' => 1, 'tag' => ''],
    ],
]
```

The above rule can be used to parse or create any of the following URLs:

* `/index.php/posts`: `page` is 1, `tag` is ''.
* `/index.php/posts/2`: `page` is 2, `tag` is ''.
* `/index.php/posts/2/news`: `page` is 2, `tag` is `'news'`.
* `/index.php/posts/news`: `page` is 1, `tag` is `'news'`.

Without using optional parameters, you would have to create 4 rules to achieve the same result.

> Note: If [[yii\web\UrlRule::$pattern|pattern]] contains only optional parameters and slashes, first parameter could be omitted 
  only if all other parameters are omitted.


### Rules with Server Names <span id="rules-with-server-names"></span>

It is possible to include Web server names in the patterns of URL rules. This is mainly useful when your application
should behave differently for different Web server names. For example, the following rules will parse the URL
`http://admin.example.com/login` into the route `admin/user/login` and `http://www.example.com/login` into `site/login`.

```php
'rules' => [
    'http://admin.example.com/login' => 'admin/user/login',
    'http://www.example.com/login' => 'site/login',
]
```

You can also embed parameters in the server names to extract dynamic information from them. For example, the following rule
will parse the URL `http://en.example.com/posts` into the route `post/index` and the parameter `language=en`.

```php
'rules' => [
    'http://<language:\w+>.example.com/posts' => 'post/index',
]
```

Since version 2.0.11, you may also use protocol relative patterns that work for both, `http` and `https`.
The syntax is the same as above but skipping the `http:` part, e.g.: `'//www.example.com/login' => 'site/login'`.

> Note: Rules with server names should **not** include the subfolder of the entry script in their patterns. For example, if the applications entry script is at `http://www.example.com/sandbox/blog/index.php`, then you should use the pattern
  `http://www.example.com/posts` instead of `http://www.example.com/sandbox/blog/posts`. This will allow your application
  to be deployed under any directory without the need to change your url rules. Yii will automatically detect the base url of the application.


### URL Suffixes <span id="url-suffixes"></span>

You may want to add suffixes to the URLs for various purposes. For example, you may add `.html` to the URLs so that they
look like URLs for static HTML pages; you may also add `.json` to the URLs to indicate the expected content type
of the response. You can achieve this goal by configuring the [[yii\web\UrlManager::suffix]] property like
the following in the application configuration:

```php
[
    // ...
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            // ...
            'suffix' => '.html',
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

The above configuration will allow the [[yii\web\UrlManager|URL manager]] to recognize requested URLs and also create
URLs with `.html` as their suffix.

> Tip: You may set `/` as the URL suffix so that the URLs all end with a slash.

> Note: When you configure a URL suffix, if a requested URL does not have the suffix, it will be considered as
  an unrecognized URL. This is a recommended practice for SEO (search engine optimization) to avoid duplicate content on different URLs.

Sometimes you may want to use different suffixes for different URLs. This can be achieved by configuring the
[[yii\web\UrlRule::suffix|suffix]] property of individual URL rules. When a URL rule has this property set, it will
override the suffix setting at the [[yii\web\UrlManager|URL manager]] level. For example, the following configuration
contains a customized URL rule which uses `.json` as its suffix instead of the global `.html` suffix.

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

### HTTP Methods <span id="http-methods"></span>

When implementing RESTful APIs, it is commonly needed that the same URL be parsed into different routes according to
the HTTP methods being used. This can be easily achieved by prefixing the supported HTTP methods to the patterns of
the rules. If a rule supports multiple HTTP methods, separate the method names with commas. For example, the following
rules have the same pattern `post/<id:\d+>` with different HTTP method support. A request for `PUT post/100` will
be parsed into `post/update`, while a request for `GET post/100` will be parsed into `post/view`.

```php
'rules' => [
    'PUT,POST post/<id:\d+>' => 'post/update',
    'DELETE post/<id:\d+>' => 'post/delete',
    'post/<id:\d+>' => 'post/view',
]
```

> Note: If a URL rule contains HTTP method(s) in its pattern, the rule will only be used for parsing purpose unless `GET` is among the specified verbs.
  It will be skipped when the [[yii\web\UrlManager|URL manager]] is called to create URLs.

> Tip: To simplify the routing of RESTful APIs, Yii provides a special URL rule class [[yii\rest\UrlRule]]
  which is very efficient and supports some fancy features such as automatic pluralization of controller IDs.
  For more details, please refer to the [Routing](rest-routing.md) section in the RESTful APIs chapter.


### Adding Rules Dynamically <span id="adding-rules"></span>

URL rules can be dynamically added to the [[yii\web\UrlManager|URL manager]]. This is often needed by redistributable
[modules](structure-modules.md) which want to manage their own URL rules. In order for the dynamically added rules
to take effect during the routing process, you should add them during the [bootstrapping](runtime-bootstrapping.md)
stage of the application. For modules, this means they should implement [[yii\base\BootstrapInterface]] and add the rules in the
[[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] method like the following:

```php
public function bootstrap($app)
{
    $app->getUrlManager()->addRules([
        // rule declarations here
    ], false);
}
```

Note that you should also list these modules in [[yii\web\Application::bootstrap]] so that they can participate the
[bootstrapping](runtime-bootstrapping.md) process.


### Creating Rule Classes <span id="creating-rules"></span>

Despite the fact that the default [[yii\web\UrlRule]] class is flexible enough for the majority of projects, there
are situations when you have to create your own rule classes. For example, in a car dealer Web site, you may want
to support the URL format like `/Manufacturer/Model`, where both `Manufacturer` and `Model` must match some data
stored in a database table. The default rule class will not work here because it relies on statically declared patterns.

We can create the following URL rule class to solve this problem.

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
            // check $matches[1] and $matches[3] to see
            // if they match a manufacturer and a model in the database.
            // If so, set $params['manufacturer'] and/or $params['model']
            // and return ['car/index', $params]
        }
        return false; // this rule does not apply
    }
}
```

And use the new rule class in the [[yii\web\UrlManager::rules]] configuration:

```php
'rules' => [
    // ...other rules...
    [
        'class' => 'app\components\CarUrlRule',
        // ...configure other properties...
    ],
]
```


## URL normalization <span id="url-normalization"></span>

Since version 2.0.10 [[yii\web\UrlManager|UrlManager]] can be configured to use [[yii\web\UrlNormalizer|UrlNormalizer]] for dealing
with variations of the same URL, for example with and without a trailing slash. Because technically `http://example.com/path`
and `http://example.com/path/` are different URLs, serving the same content for both of them can degrade SEO ranking.
By default normalizer collapses consecutive slashes, adds or removes trailing slashes depending on whether the
suffix has a trailing slash or not, and redirects to the normalized version of the URL using [permanent redirection](https://en.wikipedia.org/wiki/HTTP_301).
The normalizer can be configured globally for the URL manager or individually for each rule - by default each rule will use the normalizer
from URL manager. You can set [[yii\web\UrlRule::$normalizer|UrlRule::$normalizer]] to `false` to disable normalization
for particular URL rule.

The following shows an example configuration for the [[yii\web\UrlNormalizer|UrlNormalizer]]:

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'enableStrictParsing' => true,
    'suffix' => '.html',
    'normalizer' => [
        'class' => 'yii\web\UrlNormalizer',
        // use temporary redirection instead of permanent for debugging
        'action' => UrlNormalizer::ACTION_REDIRECT_TEMPORARY,
    ],
    'rules' => [
        // ...other rules...
        [
            'pattern' => 'posts',
            'route' => 'post/index',
            'suffix' => '/',
            'normalizer' => false, // disable normalizer for this rule
        ],
        [
            'pattern' => 'tags',
            'route' => 'tag/index',
            'normalizer' => [
                // do not collapse consecutive slashes for this rule
                'collapseSlashes' => false,
            ],
        ],
    ],
]
```

> Note: by default [[yii\web\UrlManager::$normalizer|UrlManager::$normalizer]] is disabled. You need to explicitly
  configure it in order to enable URL normalization.



## Performance Considerations <span id="performance-consideration"></span>

When developing a complex Web application, it is important to optimize URL rules so that it takes less time to parse
requests and create URLs.

By using parameterized routes, you may reduce the number of URL rules, which can significantly improve performance.

When parsing or creating URLs, [[yii\web\UrlManager|URL manager]] examines URL rules in the order they are declared.
Therefore, you may consider adjusting the order of the URL rules so that more specific and/or more commonly used rules are placed before less used ones.

If some URL rules share the same prefix in their patterns or routes, you may consider using [[yii\web\GroupUrlRule]]
so that they can be more efficiently examined by [[yii\web\UrlManager|URL manager]] as a group. This is often the case
when your application is composed by modules, each having its own set of URL rules with module ID as their common prefixes.
