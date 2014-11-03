Routing and URL Creation
========================

> Note: This section is under development.

When a Yii application starts processing a requested URL, the first step it does is to parse the URL
into a [route](structure-controllers.md#routes). The route is then used to instantiate the corresponding 
[controller action](structure-controllers.md) to handle the request. This whole process is called *routing*.
 
The reverse process of routing is called *URL creation*, which creates a URL from a given route
and the associated parameters. When the created URL is later requested, the routing process can resolve it 
back into the original route and parameters.
  
The central piece responsible for routing and URL creation is the [[yii\web\UrlManager|URL manager]],
which is registered as the `urlManager` application component. The [[yii\web\UrlManager|URL manager]]
provides the [[yii\web\UrlManager::parseRequest()|parseRequest()]] method to parse an incoming request into
a route and the associated parameters and the [[yii\web\UrlManager::createUrl()|createUrl()]] method to
create a URL from a given route and its associated parameters.
 
By configuring the `urlManager` component in the application configuration, you can let your application 
to recognize arbitrary URL formats without modifying your existing application code. For example, you can 
use the following code to create a URL for the `post/view` action:

```php
use yii\helpers\Url;

// Url::to() calls UrlManager::createUrl() to create a URL
$url = Url::to(['post/view', 'id' => 100]);
```

Depending on the `urlManager` configuration, the created URL may look like one of the followings (or other format). 
And if the created URL is requested later, it will still be parsed back into the original route and parameter value.

```
/index.php?r=post/view&id=100
/index.php/post/100
/posts/100
```


## URL Formats <a name="url-formats"></a>

The [[yii\web\UrlManager|URL manager]] supports two URL formats: the default URL format and the pretty URL format.

The default URL format uses a query parameter named `r` to represent the route and normal query parameters 
to represent the parameters associated with the route. For example, the URL `/index.php?r=post/view&id=100` represents 
the route `post/view` and the `id` parameter 100. The default URL format does not require any configuration about 
the [[yii\web\UrlManager|URL manager]] and works in any Web server setup.

The pretty URL format uses the extra path following the entry script name to represent the route and the associated 
parameters. For example, the extra path in the URL `/index.php/post/100` is `/post/100` which may represent
the route `post/view` and the `id` parameter 100 with a proper [[yii\web\UrlManager::rules|URL rule]]. To use
the pretty URL format, you will need to design a set of [[yii\web\UrlManager::rules|URL rules]] according to the actual
requirement about how the URLs should look like.
 
You may switch between the two URL formats by toggling the [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] 
property of the [[yii\web\UrlManager|URL manager]] without changing any other application code.


## Routing <a name="routing"></a>

Routing involves two steps. In the first step, the incoming request is parsed into a route and the associated 
parameters. In the second step, a [controller action](structure-controllers.md) corresponding to the parsed route
is created to handle the request.

When using the default URL format, parsing a request into a route is as simple as getting the value of a `GET`
parameter named `r`. When using the pretty URL format, however, it requires examining the registered
[[yii\web\UrlManager::rules|URL rules]] to find one that can resolve the request into a route. If such a rule cannot 
be found, a [[yii\web\NotFoundHttpException]] exception will be thrown. 

Once the request is parsed into a route, it is time to create the controller action identified by the route.
The route is broken down into multiple parts by the slashes in it. For example, `site/index` will be
broken into `site` and `index`. Each part is an ID which may refer to a module, a controller or an action.
Starting from the first part in the route, the application conducts the following steps to create modules (if any),
the controller and the action:

1. Set the application as the current module.
2. Check if the [[yii\base\Module::controllerMap|controller map]] of the current module contains the current ID.
   If so, a controller object will be created according to the controller configuration found in the map,
   and Step 5 will be taken to handle the rest part of the route.
3. Check if the ID refers to a module listed in the [[yii\base\Module::modules|modules]] property of
   the current module. If so, a module is created according to the configuration found in the module list,
   and Step 2 will be taken to handle the next part of the route under the context of the newly created module.
4. Treat the ID as a controller ID and create a controller object. Do the next step with the rest part of
   the route.
5. The controller looks for the current ID in its [[yii\base\Controller::actions()|action map]]. If found,
   it creates an action according to the configuration found in the map. Otherwise, the controller will
   attempt to create an inline action which is defined by an action method corresponding to the current ID.

Among the above steps, if any error occurs, a [[yii\web\NotFoundHttpException]] will be thrown, indicating
failure of the routing process.


### Default Route <a name="default-route"></a>

When a request is parsed into an empty route, the so-called *default route* will be used, instead. By default,
the default route is `site/index`,  which refers to the `index` action of the `site` controller. You may customize
customize it by configuring the [[yii\web\Application::defaultRoute|defaultRoute]] property of the application
in the application configuration like the following:

```php
[
    // ...
    'defaultRoute' => 'main/index',
];
```


### `catchAll` Route <a name="catchall-route"></a>

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


## Creating URLs <a name="creating-urls"></a>

Yii provides a helper method [[yii\helpers\Url::to()]] to create various kinds of URLs from given routes and 
their associated parameters. For example,

```php
use yii\helpers\Url;

// creates a URL to a route: /index.php?r=post/index
echo Url::to(['post/index']);

// creates a URL to a route with parameters: /index.php?r=post/view&id=100
echo Url::to(['post/view', 'id' => 100]);

// creates an anchored URL: /index.php?r=post/view&id=100#content
echo Url::to(['post/view', 'id' => 100, '#' => 'content']);

// creates an absolute URL: http://www.example.com/index.php?r=post/index
echo Url::to(['post/index'], true);

// creates an absolute URL using https scheme: https://www.example.com/index.php?r=post/index
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

For example, assume the current module is `admin` and the current controller is `post`,

```php
use yii\helpers\Url;

// currently requested route: /index.php?r=admin/post/index
echo Url::to(['']);

// a relative route with action ID only: /index.php?r=admin/post/index
echo Url::to(['index']);

// a relative route: /index.php?r=admin/post/index
echo Url::to(['post/index']);

// an absolute route: /index.php?r=post/index
echo Url::to(['/post/index']);
```

The [[yii\helpers\Url::to()]] method is implemented by calling the [[yii\web\UrlManager::createUrl()|createUrl()]] 
and [[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]] methods of the [[yii\web\UrlManager|URL manager]].
In the next few subsections, we will explain how to configure the [[yii\web\UrlManager|URL manager]] to customize
the format of the created URLs.

The [[yii\helpers\Url::to()]] method also supports creating URLs that are NOT related with particular routes.
Instead of passing an array as its first parameter, you should pass a string in this case. For example,
 
```php
use yii\helpers\Url;

// currently requested URL: /index.php?r=admin/post/index
echo Url::to();

// an aliased URL: http://example.com
Yii::setAlias('@example', 'http://example.com/');
echo Url::to('@example');

// an absolute URL: http://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);
```

Besides the `to()` method, the [[yii\helpers\Url]]` helper class also provides several other convenient URL creation 
methods. For example,

```php
use yii\helpers\Url;

// home page URL: /index.php?r=site/index
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


## Using Pretty URLs <a name="using-pretty-urls"></a>

To use pretty URLs, configure the `urlManager` component in the application configuration like the following:

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

where

* [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]]: this property toggles the pretty URL format.
* [[yii\web\UrlManager::showScriptName|showScriptName]]: this property determines whether the entry script
  should be included in the created URLs. For example, in stead of creating a URL `/index.php/post/100`,
  by setting this property to be true, a URL `/post/100` may be generated. 
* [[yii\web\UrlManager::enableStrictParsing|enableStrictParsing]]: this property determines whether to enable
  strict request parsing. If strict parsing is enabled, the incoming requested URL must match at least one of 
  the [[yii\web\UrlManager::rules|rules]] in order to be treated as a valid request, or a [[yii\web\NotFoundHttpException]] 
  will be thrown. If strict parsing is disabled, when none of the [[yii\web\UrlManager::rules|rules]] matches
  the requested URL, the path info part of the URL will be treated as the requested route. 
* [[yii\web\UrlManager::rules|rules]]: this property contains a list of rules specifying how to parse and create
  URLs. It is the main property that you should work with in order to create URLs whose format satisfies your
  particular application requirement.

> Note: In order to hide the entry script name in the created URLs, besides setting
  [[yii\web\UrlManager::showScriptName|showScriptName]] to be true, you may also need to configure your Web server
  so that it can correctly identify which PHP script should be executed when a requested URL does not explicitly 
  specify one. If you are using Apache Web server, you may refer to the recommended configuration as described in the
  [Installation](start-installation.md#recommended-apache-configuration) section.


### URL Rules <a name="url-rules"></a>

URL rules are objects responsible for parsing and creating URLs when the pretty URL format is used. You declare 
the URL rules by configuring them in the [[yii\web\UrlManager::rules]] property. The property takes an array with 
each element specifying a single URL rule. When the [[yii\web\UrlManager|URL manager]] is parsing an incoming request,
it examines the rules in the order they are declared and looks for the *first* rule that matches the requested URL. 
The matching rule is then used to parse the URL into a route and its associated parameters. Similarly, when 
the [[yii\web\UrlManager|URL manager]] is used to create a URL, it looks for the first rule that matches the given
route and parameters and uses it to create a URL.

The following [[yii\web\UrlManager::rules|rules]] configuration declares two URL rules. The first rule matches
a URL `posts` and maps it into the route `post/index`. The second rule matches a URL matching the regular expression
`post/(\d+)` and maps it into the route `post/view` and a parameter named `id`.

```php
[
    'posts' => 'post/index', 
    'post/<id:\d+>' => 'post/view',
]
```

> Info: Only the path info part of a URL is used to match against a rule. The path info of a URL is the part
  after the entry script and before the query string. For example, the path info of `/index.php/post/100?source=ad`
  is `/post/100`. When performing the matching test, both of the leading and ending slashes in the path info are ignored.


### Named Parameters <a name="named-parameters"></a>

A URL rule can be associated with a few named parameters. When the rule is used to parse a URL, it will fill these
parameters with values matching various parts of the URL. And when the rule is used to create a URL, it will take the
values of the provided parameters and insert them into various parts of the URL being created.

To specify a named parameter, embed in the URL pattern with a token `<ParamName>` or `<ParamName:Pattern>`,
where `ParamName` specifies the parameter name and `Pattern` is a regular expression that the parameter value should
match. If `Pattern` is not specified, it means the parameter should match any characters except `/`. 

Let's use some examples to explain how URL rules work. Assume we have declared the following URL rules:

```php
[
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
    'posts/<year:\d{4}>/<category>' => 'post/index',
]
```

* Parsing URLs
   - `/index.php/posts` is parsed into the route `post/index` using the first rule;
   - `/index.php/posts/2014/php` is parsed into the route `post/index`, the `year` parameter whose value is 2014
     and the `category` parameter whose value is `php` using the third rule;
   - `/index.php/post/100` is parsed into the route `post/view` and the `id` parameter whose value is 100 using
     the second rule;
   - `/index.php/posts/php` will cause a [[yii\web\NotFoundHttpException]] when [[yii\web\UrlManager::enableStrictParsing]]
     is true, because it matches none of the patterns. If [[yii\web\UrlManager::enableStrictParsing]] is false (the
     default value), the path info part `posts/php` will be returned as the route.
* Creating URLs
   - `Url::to(['post/index'])` creates `/index.php/posts` using the first rule;
   - `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` creates `/index.php/posts/2014/php` using the
     third rule;
   - `Url::to(['post/view', 'id' => 100])` creates `/index.php/post/100` using the second rule;
   - `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` creates `/index.php/post/100?source=ad` using the second rule.
     Because the `source` parameter is not specified in the rule, it is appended as a query parameter in the created URL.
   - `Url::to(['post/index', 'category' => 'php'])` creates `/index.php/post/index?category=php` using none of rules.
     Note that since none of the rules applies, the URL is created by simply appending the route as the path info
     and all parameters as the query string part.
   

### Parameterizing Routes

Rules may also make use of named parameters as part of a route. Named parameters allow a rule to be applied to multiple routes based
on matching criteria. Named parameters may also help reduce the number of rules needed for an application, and thus improve the overall
performance.

The following example rules illustrate how to parameterize routes with named parameters:

```php
[
    '<controller:(post|comment)>/<id:\d+>/<action:(create|update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/read',
    '<controller:(post|comment)>s' => '<controller>/list',
]
```

In the above example, two named parameters are found in the route part of the rules: `controller` and `action`. The former matches a controller ID that's either "post" or "comment", while the latter matches an action ID that could be "create", "update", or "delete". You may name the parameters differently as long as they do not conflict with any GET parameters that may appear in your URLs.

Using the above rules, the URL `/index.php/post/123/create` will be parsed as the route `post/create` with `GET` parameter
`id=123`. Given the route `comment/list` and `GET` parameter `page=2`, Yii can create a URL `/index.php/comments?page=2`.


### Parameterizing Hostnames

It is also possible to include hostnames in the rules for parsing and creating URLs. One may extract part of the hostname
to be a `GET` parameter. This is especially useful for handling subdomains. For example, the URL
`http://admin.example.com/en/profile` may be parsed into GET parameters `user=admin` and `lang=en`. On the other hand,
rules with hostnames may also be used to create URLs with parameterized hostnames.

In order to use parameterized hostnames, simply declare the URL rules while including the host info:

```php
[
    'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
]
```

In the above example, the first segment of the hostname is treated as the "user" parameter while the first segment
of the pat is treated as the "lang" parameter. The rule corresponds to the `user/profile` route.

Note that [[yii\web\UrlManager::showScriptName]] will not take effect when a URL is being created using a rule with a parameterized hostname.

Also note that any rule with a parameterized hostname should NOT contain the subfolder if the application is under
a subfolder of the web root. For example, if the application is under `http://www.example.com/sandbox/blog`, then you
should still use the same URL rule as described above without the subfolder `sandbox/blog`.

### Faking URL Suffix

```php
<?php
return [
    // ...
    'components' => [
        'urlManager' => [
            'suffix' => '.html',
        ],
    ],
];
```

### Handling REST requests

TBD:
- RESTful routing: [[yii\filters\VerbFilter]], [[yii\web\UrlManager::$rules]]
- Json API:
  - response: [[yii\web\Response::format]]
  - request: [[yii\web\Request::$parsers]], [[yii\web\JsonParser]]


### Customizing URL Rules

[[yii\web\UrlRule]] class is used for both parsing URL into parameters and creating URL based on parameters. Despite
the fact that default implementation is flexible enough for the majority of projects, there are situations when using
your own rule class is the best choice. For example, in a car dealer website, we may want to support the URL format like
`/Manufacturer/Model`, where `Manufacturer` and `Model` must both match some data in a database table. The default rule
class will not work because it mostly relies on statically declared regular expressions which have no database knowledge.

We can write a new URL rule class by extending from [[yii\web\UrlRule]] and use it in one or multiple URL rules. Using
the above car dealer website as an example, we may declare the following URL rules in application config:

```php
// ...
'components' => [
    'urlManager' => [
        'rules' => [
            '<action:(login|logout|about)>' => 'site/<action>',

            // ...

            ['class' => 'app\components\CarUrlRule', 'connectionID' => 'db', /* ... */],
        ],
    ],
],
```

In the above, we use the custom URL rule class `CarUrlRule` to handle
the URL format `/Manufacturer/Model`. The class can be written like the following:

```php
namespace app\components;

use yii\web\UrlRule;

class CarUrlRule extends UrlRule
{
    public $connectionID = 'db';

    public function init()
    {
        if ($this->name === null) {
            $this->name = __CLASS__;
        }
    }

    public function createUrl($manager, $route, $params)
    {
        if ($route === 'car/index') {
            if (isset($params['manufacturer'], $params['model'])) {
                return $params['manufacturer'] . '/' . $params['model'];
            } elseif (isset($params['manufacturer'])) {
                return $params['manufacturer'];
            }
        }
        return false;  // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            // check $matches[1] and $matches[3] to see
            // if they match a manufacturer and a model in the database
            // If so, set $params['manufacturer'] and/or $params['model']
            // and return ['car/index', $params]
        }
        return false;  // this rule does not apply
    }
}
```

Besides the above usage, custom URL rule classes can also be implemented
for many other purposes. For example, we can write a rule class to log the URL parsing
and creation requests. This may be useful during development stage. We can also
write a rule class to display a special 404 error page in case all other URL rules fail
to resolve the current request. Note that in this case, the rule of this special class
must be declared as the last rule.

