Security best practices
=======================

Below we'll review common security principles and describe how to avoid threats when developing applications using Yii.
Most of these priciples are not unique to Yii alone but apply to website or software development in general,
so you will also find links for further reading on the general ideas behind these.


Basic principles
----------------

There are two main principles when it comes to security no matter which application is being developed:

1. Filter input.
2. Escape output.


### Filter input

Filter input means that input should never be considered safe and you should always check if the value you've got is
actually among allowed ones. For example, if we know that sorting could be done by three fields `title`, `created_at` and `status`
and the field could be supplied via user input, it's better to check the value we've got right where we're receiving it.
In terms of basic PHP that would look like the following:

```php
$sortBy = $_GET['sort'];
if (!in_array($sortBy, ['title', 'created_at', 'status'])) {
	throw new Exception('Invalid sort value.');
}
```

In Yii, most probably you'll use [form validation](input-validation.md) to do alike checks.

Further reading on the topic:

- <https://www.owasp.org/index.php/Data_Validation>
- <https://www.owasp.org/index.php/Input_Validation_Cheat_Sheet>


### Escape output

Escape output means that depending on context where we're using data it should be escaped i.e. in context of HTML you
should escape `<`, `>` and alike special characters. In context of JavaScript or SQL it will be different set of characters.
Since it's error-prone to escape everything manually Yii provides various tools to perform escaping for different
contexts.

Further reading on the topic:

- <https://www.owasp.org/index.php/Command_Injection>
- <https://www.owasp.org/index.php/Code_Injection>
- <https://www.owasp.org/index.php/Cross-site_Scripting_%28XSS%29>


Avoiding SQL injections
-----------------------

SQL injection happens when query text is formed by concatenating unescaped strings such as the following:

```php
$username = $_GET['username'];
$sql = "SELECT * FROM user WHERE username = '$username'";
```

Instead of supplying correct username attacker could give your applications something like `'; DROP TABLE user; --`.
Resulting SQL will be the following:

```sql
SELECT * FROM user WHERE username = ''; DROP TABLE user; --'
```

This is valid query that will search for users with empty username and then will drop `user` table most probably
resulting in broken website and data loss (you've set up regular backups, right?).

In Yii most of database querying happens via [Active Record](db-active-record.md) which properly uses PDO prepared
statements internally. In case of prepared statements it's not possible to manipulate query as was demonstrated above.

Still, sometimes you need [raw queries](db-dao.md) or [query builder](db-query-builder.md). In this case you should use
safe ways of passing data. If data is used for column values it's preferred to use prepared statements:

```php
// query builder
$userIDs = (new Query())
    ->select('id')
    ->from('user')
    ->where('status=:status', [':status' => $status])
    ->all();

// DAO
$userIDs = $connection
    ->createCommand('SELECT id FROM user where status=:status')
    ->bindValues([':status' => $status])
    ->queryColumn();
```

If data is used to specify column names or table names the best thing to do is to allow only predefined set of values:
 
```php
function actionList($orderBy = null)
{
    if (!in_array($orderBy, ['name', 'status'])) {
        throw new BadRequestHttpException('Only name and status are allowed to order by.')
    }
    
    // ...
}
```

In case it's not possible, table and column names should be escaped. Yii has special syntax for such escaping
which allows doing it the same way for all databases it supports:

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

You can get details about the syntax in [Quoting Table and Column Names](db-dao.md#quoting-table-and-column-names).

Further reading on the topic:

- <https://www.owasp.org/index.php/SQL_Injection>


Avoiding XSS
------------

XSS or cross-site scripting happens when output isn't escaped properly when outputting HTML to the browser. For example,
if user can enter his name and instead of `Alexander` he enters `<script>alert('Hello!');</script>`, every page that
outputs user name without escaping it will execute JavaScript `alert('Hello!');` resulting in alert box popping up
in a browser. Depending on website instead of innocent alert such script could send messages using your name or even
perform bank transactions.

Avoiding XSS is quite easy in Yii. There are generally two cases:

1. You want data to be outputted as plain text.
2. You want data to be outputted as HTML.

If all you need is plain text then escaping is as easy as the following:


```php
<?= \yii\helpers\Html::encode($username) ?>
```

If it should be HTML we could get some help from HtmlPurifier:

```php
<?= \yii\helpers\HtmlPurifier::process($description) ?>
```

Note that HtmlPurifier processing is quite heavy so consider adding caching.

Further reading on the topic:

- <https://www.owasp.org/index.php/Cross-site_Scripting_%28XSS%29>


Avoiding CSRF
-------------

CSRF is an abbreviation for cross-site request forgery. The idea is that many applications assume that requests coming
from a user browser are made by the user himself. It could be `false`.

For example, `an.example.com` website has `/logout` URL that, when accessed using a simple GET, logs user out. As long
as it's requested by the user itself everything is OK but one day bad guys are somehow posting
`<img src="http://an.example.com/logout">` on a forum user visits frequently. Browser doesn't make any difference between
requesting an image or requesting a page so when user opens a page with such `img` tag, the browser will send the GET request to that URL, and the user will be logged out from `an.example.com`. 

That's the basic idea. One can say that logging user out is nothing serious, but bad guys can do much more, using this idea. Imagine that some website has an URL `http://an.example.com/purse/transfer?to=anotherUser&amount=2000`. Accessing it using GET request, causes transfer of $2000 from authorized user account to user `anotherUser`. We know, that browser will always send GET request to load an image, so we can modify code to accept only POST requests on that URL. Unfortunately, this will not save us, because an attacker can put some JavaScript code instead of `<img>` tag, which allows to send POST requests on that URL.

In order to avoid CSRF you should always:

1. Follow HTTP specification i.e. GET should not change application state.
2. Keep Yii CSRF protection enabled.

Sometimes you need to disable CSRF validation per controller and/or action. It could be achieved by setting its property:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        // CSRF validation will not be applied to this and other actions
    }

}
```

To disable CSRF validation per custom actions you can do:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function beforeAction($action)
    {
        // ...set `$this->enableCsrfValidation` here based on some conditions...
        // call parent method that will check CSRF if such property is `true`.
        return parent::beforeAction($action);
    }
}
```

Disabling CSRF validation in [standalone actions](structure-controllers.md#standalone-actions) must be done in `init()`
method. Do not place this code into `beforeRun()` method because it won't have effect.

```php
<?php

namespace app\components;

use yii\base\Action;

class ContactAction extends Action
{
    public function init()
    {
        parent::init();
        $this->controller->enableCsrfValidation = false;
    }

    public function run()
    {
          $model = new ContactForm();
          $request = Yii::$app->request;
          if ($request->referrer === 'yiipowered.com'
              && $model->load($request->post())
              && $model->validate()
          ) {
              $model->sendEmail();
          }
    }
}
```

> Warning: Disabling CSRF will allow any site to send POST requests to your site. It is important to implement extra validation such as checking an IP address or a secret token in this case.

Further reading on the topic:

- <https://www.owasp.org/index.php/CSRF>

Avoiding file exposure
----------------------

By default server webroot is meant to be pointed to `web` directory where `index.php` is. In case of shared hosting
environments it could be impossible to achieve so we'll end up with all the code, configs and logs in server webroot.

If it's the case don't forget to deny access to everything except `web`. If it can't be done consider hosting your
application elsewhere.


Avoiding debug info and tools in production
-------------------------------------------

In debug mode Yii shows quite verbose errors which are certainly helpful for development. The thing is that these
verbose errors are handy for attacker as well since these could reveal database structure, configuration values and
parts of your code. Never run production applications with `YII_DEBUG` set to `true` in your `index.php`.

You should never enable Gii or the Debug toolbar in production. It could be used to get information about database structure, code and to
simply rewrite code with what's generated by Gii.

Debug toolbar should be avoided at production unless really necessary. It exposes all the application and config
details possible. If you absolutely need it check twice that access is properly restricted to your IP only.

Further reading on the topic:

- <https://www.owasp.org/index.php/Exception_Handling>
- <https://www.owasp.org/index.php/Top_10_2007-Information_Leakage>


Using secure connection over TLS
--------------------------------

Yii provides features that rely on cookies and/or PHP sessions. These can be vulnerable in case your connection is
compromised. The risk is reduced if the app uses secure connection via TLS (often referred to as [SSL](https://en.wikipedia.org/wiki/Transport_Layer_Security)).

Please refer to your webserver documentation for instructions on how to configure it. You may also check example configs
provided by the H5BP project:

- [Nginx](https://github.com/h5bp/server-configs-nginx)
- [Apache](https://github.com/h5bp/server-configs-apache).
- [IIS](https://github.com/h5bp/server-configs-iis).
- [Lighttpd](https://github.com/h5bp/server-configs-lighttpd).


Secure Server configuration
---------------------------

The purpose of this section is to highlight risks that need to be considered when creating a
server configuration for serving a Yii based website. Besides the points covered here there may
be other security related configuration options to be considered, so do not consider this section to
be complete.

### Avoiding `Host`-header attacks

Classes like [[yii\web\UrlManager]] and [[yii\helpers\Url]] may use the [[yii\web\Request::getHostInfo()|currently requested host name]]
for generating links.
If the webserver is configured to serve the same site independent of the value of the `Host` header, this information may not be reliable
and [may be faked by the user sending the HTTP request](https://www.acunetix.com/vulnerabilities/web/host-header-attack).
In such situations you should either fix your webserver configuration to serve the site only for specified host names
or explicitly set or filter the value by setting the [[yii\web\Request::setHostInfo()|hostInfo]] property of the `request` application component.

For more information about the server configuration, please refer to the documentation of your webserver:

- Apache 2: <http://httpd.apache.org/docs/trunk/vhosts/examples.html#defaultallports>
- Nginx: <https://www.nginx.com/resources/wiki/start/topics/examples/server_blocks/>

If you don't have access to the server configuration, you can setup [[yii\filters\HostControl]] filter at
application level in order to protect against such kind of attack:

```php
// Web Application configuration file
return [
    'as hostControl' => [
        '__class' => yii\filters\HostControl::class,
        'allowedHosts' => [
            'example.com',
            '*.example.com',
        ],
        'fallbackHostInfo' => 'https://example.com',
    ],
    // ...
];
```

> Note: you should always prefer web server configuration for 'host header attack' protection instead of the filter usage.
  [[yii\filters\HostControl]] should be used only if server configuration setup is unavailable.
