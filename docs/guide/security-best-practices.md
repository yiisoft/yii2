Security best practices
=======================

Below we'll review common security principles and describe how to avoid threats when developing applications using Yii.

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


### Escape output

Escape output means that depending on context where we're using data it should be escaped i.e. in context of HTML you
should escape `<`, `>` and alike special characters. In context of JavaScript or SQL it will be different set of characters.
Since it's error-prone to escape everything manually Yii provides various tools to perform escaping for different
contexts.

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

Avoiding CSRF
-------------

CSRF is an abbreviation for cross-site request forgery. The idea is that many applications assume that requests coming
from a user browser are made by the user himself. It could be false.

For example, `an.example.com` website has `/logout` URL that, when accessed using a simple GET, logs user out. As long
as it's requested by the user itself everything is OK but one day bad guys are somehow posting
`<img src="http://an.example.com/logout">` on a forum user visits frequently. Browser doesn't make any difference between
requesting an image or requesting a page so when user opens a page with such `img` tag, the browser will send the GET request to that URL, and the user will be logged out from `an.example.com`. 

That's the basic idea. One can say that logging user out is nothing serious, but bad guys can do much more, using this idea. Imagine that some website has an URL `http://an.example.com/purse/transfer?to=anotherUser&amout=2000`. Accessing it using GET request, causes transfer of $2000 from authorized user account to user `anotherUser`. We know, that browser will always send GET request to load an image, so we can modify code to accept only POST requests on that URL. Unfortunately, this will not save us, because an attacker can put some JavaScript code instead of `<img>` tag, which allows to send POST requests on that URL.

In order to avoid CSRF you should always:

1. Follow HTTP specification i.e. GET should not change application state.
2. Keep Yii CSRF protection enabled.


Avoiding file exposure
----------------------

By default server webroot is meant to be pointed to `web` directory where `index.php` is. In case of shared hosting
environments it could be impossible to achieve so we'll end up with all the code, configs and logs in server webroot.

If it's the case don't forget to deny access to everything except `web`. If it can't be done consider hosting your
application elsewhere.

Avoiding debug info and tools at production
-------------------------------------------

In debug mode Yii shows quite verbose errors which are certainly helpful for development. The thing is that these
verbose errors are handy for attacker as well since these could reveal database structure, configuration values and
parts of your code. Never run production applications with `YII_DEBUG` set to `true` in your `index.php`.

You should never enalble Gii at production. It could be used to get information about database structure, code and to
simply rewrite code with what's generated by Gii.

Debug toolbar should be avoided at production unless really necessary. It exposes all the application and config
details possible. If you absolutely need it check twice that access is properly restricted to your IP only.
