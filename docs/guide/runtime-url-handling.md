URL Parsing and Generation
==========================

> Note: This section is under development.

The concept of *URL management* in Yii is fairly simple: the application simply uses internal routes and parameters everywhere. The framework itself will then translate routes into URLs, and vice versa, according to the URL manager's configuration. This approach allows you to make site-wide changes to URLs merely by
editing a single configuration file, without ever touching any application code.

Internal Routes
---------------

When implementing an application using Yii, you'll deal with *internal* routes, often referred to as "routes and parameters".
Each controller and controller action has a corresponding internal route, such as `site/index` or `user/create`.

In the first example, `site` is the *controller ID*, while `index` is the *action ID*. In the
second example, `user` is the controller ID and `create` is the action ID. 

If the controller belongs to a *module*, the
internal route is prefixed with the module ID: for example, `blog/post/index` represents a blog module, the module's `post` 
controller, and the `index` action.

Creating URLs
-------------

The most important rule for creating URLs in your site is to always do so through the URL manager. The URL manager is a built-in application component fittingly named `urlManager`. This component is accessible from both web and console applications via
`\Yii::$app->urlManager`. The component makes available two methods for creating URLs:

- `createUrl($params)`
- `createAbsoluteUrl($params, $schema = null)`

The `createUrl` method creates an URL relative to the application root, such as `/index.php/site/index/`.
The `createAbsoluteUrl` method creates an URL that beings with with the proper protocol and hostname:
`http://www.example.com/index.php/site/index`. Relative URLs, and the `createUrl` method are suitable for internal application URLs, while absolute URLs, and the `createAbsoluteUrl` method, are appropriate when you need to create URLs for external resources, such as connecting to third party services, sending email,
generating RSS feeds, etc.

Both methods can be passed parameters used to further customize the URL, such as appending values to pass along as part of the request.

Some examples of these two methods:

```php
echo \Yii::$app->urlManager->createUrl(['site/page', 'id' => 'about']);
// /index.php/site/page/id/about/
echo \Yii::$app->urlManager->createUrl(['date-time/fast-forward', 'id' => 105])
// /index.php?r=date-time/fast-forward&id=105
echo \Yii::$app->urlManager->createAbsoluteUrl('blog/post/index');
// http://www.example.com/index.php/blog/post/index/
```

The exact format of the resulting URL depends on how the URL manager is configured. The above
examples could also output:

* `/site/page/id/about/`
* `/index.php?r=site/page&id=about`
* `/index.php?r=date-time/fast-forward&id=105`
* `/index.php/date-time/fast-forward?id=105`
* `http://www.example.com/blog/post/index/`
* `http://www.example.com/index.php?r=blog/post/index`

In order to simplify URL creation, Yii has the [[yii\helpers\Url]] helper. Assuming the current URL is `/index.php?r=management/default/users&id=10`, the following
shows how the `Url` helper works:

```php
use yii\helpers\Url;

// currently active route
// /index.php?r=management/default/users
echo Url::to('');

// same controller, different action
// /index.php?r=management/default/page&id=contact
echo Url::toRoute(['page', 'id' => 'contact']);

// same module, different controller and action
// /index.php?r=management/post/index
echo Url::toRoute('post/index');

// absolute route no matter what controller is making this call
// /index.php?r=site/index
echo Url::toRoute('/site/index');

// url for the case sensitive action `actionHiTech` of the current controller
// /index.php?r=management/default/hi-tech
echo Url::toRoute('hi-tech');

// url for action the case sensitive controller, `DateTimeController::actionFastForward`
// /index.php?r=date-time/fast-forward&id=105
echo Url::toRoute(['/date-time/fast-forward', 'id' => 105]);

// get URL from alias
// http://google.com/
Yii::setAlias('@google', 'http://google.com/');
echo Url::to('@google');

// get home URL
// /index.php?r=site/index
echo Url::home();

Url::remember(); // save URL to be used later
Url::previous(); // get previously saved URL
```

> **Tip**: In order to generate a URL containing a hashtag, for example `/index.php?r=site/page&id=100#title`, you need to
  specify the parameter named `#` using `Url::to(['post/read', 'id' => 100, '#' => 'title'])`.

There's also the `Url::canonical()` method that allows you to generate
[canonical URLs](https://en.wikipedia.org/wiki/Canonical_link_element) for the current action.
This method ignores all action parameters except for ones specifically passed via action arguments:

```php
namespace app\controllers;

use yii\web\Controller;
use yii\helpers\Url;

class CanonicalController extends Controller
{
    public function actionTest($page)
    {
        echo Url::canonical();
    }
}
```

When accessed as `/index.php?r=canonical/test&page=hello&number=42`, the canonical URL will be `/index.php?r=canonical/test&page=hello`.

Customizing URLs
----------------

By default, Yii uses a query string format for URLs, such as `/index.php?r=news/view&id=100`. In order to make URLs
human-friendly (i.e., more legible), you need to configure the `urlManager` component in the application's configuration
file. Enabling "pretty" URLs will convert the query string format to a directory-based format: `/index.php/news/view?id=100`.


Disabling the `showScriptName` parameter further customizes the URL such that `index.php` will be omitted. Here's the relevant part of
the application's configuration file:

```php
<?php
return [
    // ...
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
    ],
];
```

Note that this configuration will only work if the web server has also been properly configured for Yii, see
[installation](start-installation.md#recommended-apache-configuration).

### Named Parameters

A rule can be associated with a few `GET` parameters. These `GET` parameters appear in the rule's pattern as special tokens in the following format:

```
<ParameterName:ParameterPattern>
```

`ParameterName` is a name of a `GET` parameter, and the optional `ParameterPattern` is the regular expression that should
be used to match the value of the `GET` parameter. When `ParameterPattern` is omitted, it means the parameter
should match any characters except `/`. When creating a URL, these parameter tokens will be replaced with the
corresponding parameter values; when parsing a URL, the corresponding GET parameters will be populated with the parsed results.

Let's use some examples to explain how URL rules work. Asusuming that the rule set consists of three rules:

```php
[
    'posts'=>'post/list',
    'post/<id:\d+>'=>'post/read',
    'post/<year:\d{4}>/<title>'=>'post/read',
]
```

- Calling `Url::toRoute('post/list')` generates `/index.php/posts`. The first rule is applied.
- Calling `Url::toRoute(['post/read', 'id' => 100])` generates `/index.php/post/100`. The second rule is applied.
- Calling `Url::toRoute(['post/read', 'year' => 2008, 'title' => 'a sample post'])` generates
  `/index.php/post/2008/a%20sample%20post`. The third rule is applied.
- Calling `Url::toRoute('post/read')` generates `/index.php/post/read`. None of the rules is applied; convention is used instead.

In summary, when using `createUrl` to generate a URL, the route and the `GET` parameters passed to the method are used to
decide which URL rule will be applied. If every parameter associated with a rule can be found in the `GET` parameters passed
to `createUrl`, and if the route of the rule also matches the route parameter, the rule will be used to generate the URL.

If the `GET` parameters passed to `Url::toRoute` are more than those required by a rule, the additional parameters will
appear in the query string. For example, the call `Url::toRoute(['post/read', 'id' => 100, 'year' => 2008])`, will
generate `/index.php/post/100?year=2008`.

As mentioned earlier, the other purpose of URL rules is to parse the requesting URLs. Naturally, this is the inverse of URL creation. For example, when a user requests for `/index.php/post/100`, the second rule in the above example
will apply, which resolves to the route `post/read` with the `GET` parameter `['id' => 100]` (accessible via
`Yii::$app->request->get('id')`).

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


URL parsing
-----------

Complimentary to creating URLs Yii also handles transforming custom URLs back into internal routes and parameters.

### Strict URL parsing

By default if there's no custom rule for a URL and the URL matches the default format such as `/site/page`, Yii tries to run the corresponding controller's action. This behavior can be disabled so if there's no custom rule match, a 404 not found error will be produced immediately.

```php
<?php
return [
    // ...
    'components' => [
        'urlManager' => [
            'enableStrictParsing' => true,
        ],
    ],
];
```

Creating your own rule classes
------------------------------

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
