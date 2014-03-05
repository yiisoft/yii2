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

First, create a controller class `app\controllers\UserController` as follows,

```php
namespace app\controllers;

use yii\rest\ActiveController;

class UserController extends ActiveController
{
	public $modelClass = 'app\models\City';
}
```

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
curl -i -H "Accept:application/json" "http://localhost/users"
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

Try changing the acceptable content type to be `application/xml`, and you will see the result
is returned in XML format:

```
curl -i -H "Accept:application/xml" "http://localhost/users"
```

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
Content-Type: application/xml

<?xml version="1.0" encoding="UTF-8"?>
<response><item><id>1</id>...</item><item><id>2</id>...</item>...</response>
```

> Tip: You may also access your APIs via Web browser by entering the URL `http://localhost/users`.

As you can see, in the response headers, there are information about the total count, page count, etc.
There are also links that allow you to navigate to other pages of data. For example, `http://localhost/users?page=2`
would give you the next page of the user data.

Using the `fields` and `expand` parameters, you may also request to return a subset of the fields in the result.
For example, the URL `http://localhost/users?fields=id,email` will only return the `id` and `email` fields in the result:


> Info: You may have noticed that the result of `http://localhost/users` includes some sensitive fields,
> such as `password_hash`, `auth_key`. You certainly do not want these to appear in your API result.
> You can/should filter out these fields as described in the following sections.


In the following sections, we will explain in more details about implementing RESTful APIs.


General Architecture
--------------------

Using the Yii RESTful API framework, you implement an API endpoint in terms of a controller action, and you use
a controller to organize the actions that implement the endpoints for a single type of resource.

Resources are represented as data models which extend from the [[yii\base\Model]] class.
If you are working with databases (relational or NoSQL), it is recommended you use ActiveRecord to represent resources.

You may use [[yii\rest\UrlRule]] to simplify the routing to your API endpoints.

While not required, it is recommended that you develop your RESTful APIs as an application, separated from
your Web front end and back end.


Adding or Removing Endpoints
----------------------------

As explained above, controllers and actions are used to implement API endpoints.

To add an API endpoint servicing a new kind of model (resource), create a new controller class by extending
[[yii\rest\ActiveController]] or [[yii\rest\Controller]]. The difference between these two base controller
classes is that the former is a subclass of the latter and implements a commonly needed actions to deal
with ActiveRecord. The controller class should be named after the model class with the `Controller` suffix.
For example, for the `Post` model, you would create a `PostController` class.

If your new controller class extends from [[yii\rest\ActiveController]], you already have a whole set of
endpoints available out of box, as shown in the quick example. You may want to disable some of the actions
or customize them. This can be easily done by overriding the `actions()` method, like the following,

```php
public function actions()
{
	$actions = parent::actions();

	// disable the "delete" and "create" actions
	unset($actions['delete'], $actions['create']);

	// customize the data provider preparation with the "prepareDataProvider()" method
	$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

	return $actions;
}

public function prepareDataProvider()
{
	// prepare and return a data provider for the "index" action
}
```

You can certainly create new actions like you do with regular controllers. The only difference is that
instead of calling [[yii\base\Controller::render()]] to render views, you directly return the data
in your action. For example,

```php
public function actionSearch($keyword)
{
	$result = SolrService::search($keyword);
	return $result;
}
```

The data will be automatically formatted and sent to the client, as we will explain in the next section.


Formatting Response Data
------------------------

By default, Yii supports two response formats for RESTful APIs: JSON and XML. If you want to support
other formats, you should configure [[yii\rest\Controller::supportedFormats]] and also [[yii\web\Response::formatters]].

The data formatting is in general a two-step process:

1. The objects (including embedded objects) in the response data are converted into arrays by [[yii\rest\Serializer]];
2. The array data are converted into different formats (e.g. JSON, XML) by [[yii\web\ResponseFormatterInterface|response formatters]].

Step 2 is usually a very mechanical data conversion process and can be well handled by the built-in response formatters.
Step 1 involves some major development effort as explained below.

When the [[yii\rest\Serializer|serializer]] converts an object into an array, it will call the `toArray()` method
of the object if it implements [[yii\base\ArrayableInterface]]. If an object does not implement this interface,
an array consisting of all its public properties will be returned.

For classes extending from [[yii\base\Model]] or [[yii\db\ActiveRecord]], besides directly overriding `toArray()`,
you may also override the `fields()` method and/or the `extraFields()` method to customize the data to be returned.

The method [[yii\base\Model::fields()]] declares a set of fields of an object that should be included in the result.
The default implementation returns all attributes of a model as the output fields. You can customize it to add,
remove, rename or reformat the fields. For example,

```php
class User extends \yii\db\ActiveRecord
{
	public function fields()
	{
		$fields = parent::fields();

		// remove fields that contain sensitive information
		unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

		// add a new field "full_name" defined as the concatenation of "first_name" and "last_name"
		$fields['full_name'] = function () {
			return $this->first_name . ' ' . $this->last_name;
		};

		return $fields;
	}
}
```

The return value of `fields()` should be an array. The array keys are the field names, and the array values
are the corresponding field definitions which can be either property/attribute names or anonymous functions
returning the corresponding field values.

> Warning: Because by default all attributes of a model will be included in the API result, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` or `toArray()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.

You may use the `fields` query parameter to specify which fields in `fields()` should be included in the result.
If this parameter is not specified, all fields returned by `fields()` will be returned.

The method [[yii\base\Model::extraFields()]] is very similar to [[yii\base\Model::fields()]].
The difference between these methods is that the latter declares the fields that should be returned by default,
while the former declares the fields that should only be returned when the user specifies them in the `expand` query parameter.

For example, `http://localhost/users?fields=id,email&expand=profile` may return the following JSON data:

```php
[
	{
		"id": 100,
		"email": "100@example.com",
		"profile": {
			"id": 100,
			"age": 30,
		}
	},
	...
]
```


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


Error Handling
--------------

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

