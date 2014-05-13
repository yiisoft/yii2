Quick Start
===========

Yii provides a whole set of tools to simplify the task of implementing RESTful Web Service APIs.
In particular, Yii provides support for the following aspects regarding RESTful APIs:

* Quick prototyping with support for common APIs for [Active Record](db-active-record.md);
* Response format (supporting JSON and XML by default) negotiation;
* Customizable object serialization with support for selectable output fields;
* Proper formatting of collection data and validation errors;
* Support for [HATEOAS](http://en.wikipedia.org/wiki/HATEOAS);
* Efficient routing with proper HTTP verb check;
* Built-in support for the `OPTIONS` and `HEAD` verbs;
* Authentication and authorization;
* Data caching and HTTP caching;
* Rate limiting;


Let's use a quick example to show how to build a set of RESTful APIs using Yii.
Assume you want to expose the user data via RESTful APIs. The user data are stored in the user DB table,
and you have already created the [[yii\db\ActiveRecord|ActiveRecord]] class `app\models\User` to access the user data.


Creating a Controller
---------------------

First, create a controller class `app\controllers\UserController` as follows,

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
    public $modelClass = 'app\models\User';
}
```


Configuring URL Rules
---------------------

Then, modify the configuration about the `urlManager` component in your application configuration:

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

Trying it Out
-------------

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
$ curl -i -H "Accept:application/json" "http://localhost/users"

HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self, 
      <http://localhost/users?page=2>; rel=next, 
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

[
    {
        "id": 1,
        ...
    },
    {
        "id": 2,
        ...
    },
    ...
]
```

Try changing the acceptable content type to be `application/xml`, and you will see the result
is returned in XML format:

```
$ curl -i -H "Accept:application/xml" "http://localhost/users"

HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
X-Powered-By: PHP/5.4.20
X-Pagination-Total-Count: 1000
X-Pagination-Page-Count: 50
X-Pagination-Current-Page: 1
X-Pagination-Per-Page: 20
Link: <http://localhost/users?page=1>; rel=self, 
      <http://localhost/users?page=2>; rel=next, 
      <http://localhost/users?page=50>; rel=last
Transfer-Encoding: chunked
Content-Type: application/xml

<?xml version="1.0" encoding="UTF-8"?>
<response>
    <item>
        <id>1</id>
        ...
    </item>
    <item>
        <id>2</id>
        ...
    </item>
    ...
</response>
```

> Tip: You may also access your APIs via Web browser by entering the URL `http://localhost/users`.
  However, you may need some browser plugins to send specific request headers.

As you can see, in the response headers, there are information about the total count, page count, etc.
There are also links that allow you to navigate to other pages of data. For example, `http://localhost/users?page=2`
would give you the next page of the user data.

Using the `fields` and `expand` parameters, you may also specify which fields should be included in the result.
For example, the URL `http://localhost/users?fields=id,email` will only return the `id` and `email` fields.


> Info: You may have noticed that the result of `http://localhost/users` includes some sensitive fields,
> such as `password_hash`, `auth_key`. You certainly do not want these to appear in your API result.
> You can and should filter out these fields as described in the [Data Formatting](rest-data-formatting.md) section.


Summary
-------

Using the Yii RESTful API framework, you implement an API endpoint in terms of a controller action, and you use
a controller to organize the actions that implement the endpoints for a single type of resource.

Resources are represented as data models which extend from the [[yii\base\Model]] class.
If you are working with databases (relational or NoSQL), it is recommended you use ActiveRecord to represent resources.

You may use [[yii\rest\UrlRule]] to simplify the routing to your API endpoints.

While not required, it is recommended that you develop your RESTful APIs as an application, separated from
your Web front end and back end.

