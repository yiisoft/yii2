Implementing RESTful Web Service APIs
=====================================

Yii provides a whole set of tools to greatly simplify the task of implementing RESTful Web Service APIs.
In particular, Yii provides support for the following aspects regarding RESTful APIs:

* Quick prototyping with support for common APIs for ActiveRecord;
* Response format (supporting JSON and XML by default) and API version negotiation;
* Customizable object serialization with support for selectable output fields;
* Proper formatting of collection data and validation errors;
* Efficient routing with proper HTTP verb check;
* Support `OPTIONS` and `HEAD` verbs;
* Authentication;
* Authorization;
* Support for HATEOAS;
* Caching via `yii\web\HttpCache`;
* Rate limiting;
* Searching and filtering: TBD
* Testing: TBD
* Automatic generation of API documentation: TBD


A Quick Example
---------------

Let's use a quick example to show how to build a set of RESTful APIs using Yii.
Assume you want to expose the user data via RESTful APIs. The user data are stored in the user DB table,
and you have already created the ActiveRecord class `app\models\User` to access the user data.

First, check your `User` class for its implementation of the `findIdentityByAccessToken()` method.
It may look like the following:

```php
class User extends ActiveRecord
{
	...
	public static function findIdentityByAccessToken($token)
	{
		return static::find(['access_token' => $token]);
	}
}
```

This means your user table has a column named `access_token` which stores API access tokens for the users.
Pick up a token from the table as you will need it to access your APIs next.

Second, create a controller class `app\controllers\UserController` as follows,

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
	public $modelClass = 'app\models\City';
}
```

Third, modify the configuration about the `urlManager` component in your application configuration:

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

With the above minimal amount of effort, you have already finished your task of creating the RESTful APIs
for accessing the user data. The APIs you have created include:

* `GET /users`: list all users page by page;
* `HEAD /users`: show the overview information of user listing;
* `POST /users`: create a new user;
* `GET /users/123`: return the details of the user 123;
* `HEAD /users/123`: show the overview information of user 123;
* `PATCH /users/123` and `PUT /users/123`: update the user 123;
* `DELETE /users/123`: delete the user 123;
* `OPTIONS /users`: show the supported verbs regarding endpoint `/users`;
* `OPTIONS /users/123`: show the supported verbs regarding endpoint `/users/123`.

You may access your APIs with the `curl` command like the following,

```
curl -i -u "Your-API-Access-Token:" -H "Accept:application/json" "http://localhost/users"
```

which may give the following output:

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self, <http://localhost/users?page=2>; rel=next, <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

[{"id":1,..},{"id":2,...}...]
```

> Tip: You may also access your API via Web browser. You will be asked
> to enter a username and password. Fill in the username field with the API access token you obtained
> previously and leave the password field blank.

Try changing the acceptable content type to be `application/xml`, and you will see the result
is returned in XML format.

Using the `fields` and `expand` parameters, you can request to return a subset of the fields in the result.
For example, the following URL will only return the `id` and `email` columns in the result:

```
http://localhost/users?fields=id,email
```

You may have noticed that the result of `http://localhost/users` includes some sensitive columns,
such as `password_hash`, `auth_key`. You certainly do not want these to appear in your API result.
To filter these data out, modify the `User` class as follows,

```php
class User extends ActiveRecord
{
	public function fields()
	{
		$fields = parent::fields();
		unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);
		return $fields;
	}
}
```

In the following subsections, we will explain in more details about implementing RESTful APIs.



Data Formatting
---------------


Implementing New API Endpoints
------------------------------


Routing
-------


Authentication
--------------


Authorization
-------------


Versioning
----------


Caching
-------


Rate Limiting
-------------


HTTP Status Code Summary
------------------------

* `200`: OK. Everything worked as expected.
* `201`: A resource was successfully created in response to a `POST` request. The `Location` header
   contains the URL pointing to the newly created resource.
* `204`: The request is handled successfully and the response contains no body content (like a `DELETE` request).
* `304`: Resource was not modified. You can use the cached version.
* `400`: Bad request. This could be caused by various reasons from the user side, such as invalid JSON
   data in the request body, invalid action parameters, etc.
* `401`: No valid API access token is provided.
* `403`: The authenticated user is not allowed to access the specified API endpoint.
* `404`: The requested resource does not exist.
* `405`: Method not allowed. Please check the `Allow` header for allowed HTTP methods.
* `415`: Unsupported media type. The requested content type or version number is invalid.
* `422`: Data validation failed (in response to a `POST` request, for example). Please check the response body for detailed error messages.
* `429`: Too many requests. The request is rejected due to rate limiting.
* `500`: Internal server error. This could be caused by internal program errors.


Documentation
-------------

Testing
-------

