Resources
=========

RESTful APIs are all about accessing and manipulating *resources*. You may view resources as
[models](structure-models.md) in the MVC paradigm.

While there is no restriction in how to represent a resource, in Yii you usually would represent resources
in terms of objects of [[yii\base\Model]] or its child classes (e.g. [[yii\db\ActiveRecord]]), for the
following reasons:

* [[yii\base\Model]] implements the [[yii\base\Arrayable]] interface, which allows you to
  customize how you want to expose resource data through RESTful APIs.
* [[yii\base\Model]] supports [input validation](input-validation.md), which is useful if your RESTful APIs
  need to support data input.
* [[yii\db\ActiveRecord]] provides powerful DB data access and manipulation support, which makes it
  a perfect fit if your resource data is stored in databases.

In this section, we will mainly describe how a resource class extending from [[yii\base\Model]] (or its child classes)
can specify what data may be returned via RESTful APIs. If the resource class does not extend from [[yii\base\Model]],
then all its public member variables will be returned.


## Fields <span id="fields"></span>

When including a resource in a RESTful API response, the resource needs to be serialized into a string.
Yii breaks this process into two steps. First, the resource is converted into an array by [[yii\rest\Serializer]].
Second, the array is serialized into a string in a requested format (e.g. JSON, XML) by
[[yii\web\ResponseFormatterInterface|response formatters]]. The first step is what you should mainly focus when
developing a resource class.

By overriding [[yii\base\Model::fields()|fields()]] and/or [[yii\base\Model::extraFields()|extraFields()]],
you may specify what data, called *fields*, in the resource can be put into its array representation.
The difference between these two methods is that the former specifies the default set of fields which should
be included in the array representation, while the latter specifies additional fields which may be included
in the array if an end user requests for them via the `expand` query parameter. For example,

```
// returns all fields as declared in fields()
http://localhost/users

// only returns "id" and "email" fields, provided they are declared in fields()
http://localhost/users?fields=id,email

// returns all fields in fields() and field "profile" if it is in extraFields()
http://localhost/users?expand=profile

// returns all fields in fields() and "author" from post if
// it is in extraFields() of post model
http://localhost/comments?expand=post.author

// only returns "id" and "email" provided they are in fields() and "profile" if it is in extraFields()
http://localhost/users?fields=id,email&expand=profile
```


### Overriding `fields()` <span id="overriding-fields"></span>

By default, [[yii\base\Model::fields()]] returns all model attributes as fields, while
[[yii\db\ActiveRecord::fields()]] only returns the attributes which have been populated from DB.

You can override `fields()` to add, remove, rename or redefine fields. The return value of `fields()`
should be an array. The array keys are the field names, and the array values are the corresponding
field definitions which can be either property/attribute names or anonymous functions returning the
corresponding field values. In the special case when a field name is the same as its defining attribute
name, you can omit the array key. For example,

```php
// explicitly list every field, best used when you want to make sure the changes
// in your DB table or model attributes do not cause your field changes (to keep API backward compatibility).
public function fields()
{
    return [
        // field name is the same as the attribute name
        'id',
        // field name is "email", the corresponding attribute name is "email_address"
        'email' => 'email_address',
        // field name is "name", its value is defined by a PHP callback
        'name' => function ($model) {
            return $model->first_name . ' ' . $model->last_name;
        },
    ];
}

// filter out some fields, best used when you want to inherit the parent implementation
// and exclude some sensitive fields.
public function fields()
{
    $fields = parent::fields();

    // remove fields that contain sensitive information
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: Because by default all attributes of a model will be included in the API result, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.


### Overriding `extraFields()` <span id="overriding-extra-fields"></span>

By default, [[yii\base\Model::extraFields()]] returns an empty array, while [[yii\db\ActiveRecord::extraFields()]]
returns the names of the relations that have been populated from DB.

The return data format of `extraFields()` is the same as that of `fields()`. Usually, `extraFields()`
is mainly used to specify fields whose values are objects. For example, given the following field
declaration,

```php
public function fields()
{
    return ['id', 'email'];
}

public function extraFields()
{
    return ['profile'];
}
```

the request with `http://localhost/users?fields=id,email&expand=profile` may return the following JSON data:

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


## Links <span id="links"></span>

[HATEOAS](https://en.wikipedia.org/wiki/HATEOAS), an abbreviation for Hypermedia as the Engine of Application State,
promotes that RESTful APIs should return information that allows clients to discover actions supported for the returned
resources. The key of HATEOAS is to return a set of hyperlinks with relation information when resource data are served
by the APIs.

Your resource classes may support HATEOAS by implementing the [[yii\web\Linkable]] interface. The interface
contains a single method [[yii\web\Linkable::getLinks()|getLinks()]] which should return a list of [[yii\web\Link|links]].
Typically, you should return at least the `self` link representing the URL to the resource object itself. For example,

```php
use yii\base\Model;
use yii\web\Link; // represents a link object as defined in JSON Hypermedia API Language.
use yii\web\Linkable;
use yii\helpers\Url;

class UserResource extends Model implements Linkable
{
    public $id;
    public $email;

    //...

    public function fields()
    {
        return ['id', 'email'];
    }

    public function extraFields()
    {
        return ['profile'];
    }

    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['user/view', 'id' => $this->id], true),
            'edit' => Url::to(['user/view', 'id' => $this->id], true),
            'profile' => Url::to(['user/profile/view', 'id' => $this->id], true),
            'index' => Url::to(['users'], true),
        ];
    }
}
```

When a `UserResource` object is returned in a response, it will contain a `_links` element representing the links related
to the user, for example,

```
{
    "id": 100,
    "email": "user@example.com",
    // ...
    "_links" => {
        "self": {
            "href": "https://example.com/users/100"
        },
        "edit": {
            "href": "https://example.com/users/100"
        },
        "profile": {
            "href": "https://example.com/users/profile/100"
        },
        "index": {
            "href": "https://example.com/users"
        }
    }
}
```


## Collections <span id="collections"></span>

Resource objects can be grouped into *collections*. Each collection contains a list of resource objects
of the same type.

While collections can be represented as arrays, it is usually more desirable to represent them
as [data providers](output-data-providers.md). This is because data providers support sorting and pagination
of resources, which is a commonly needed feature for RESTful APIs returning collections. For example,
the following action returns a data provider about the post resources:

```php
namespace app\controllers;

use yii\rest\Controller;
use yii\data\ActiveDataProvider;
use app\models\Post;

class PostController extends Controller
{
    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => Post::find(),
        ]);
    }
}
```

When a data provider is being sent in a RESTful API response, [[yii\rest\Serializer]] will take out the current
page of resources and serialize them as an array of resource objects. Additionally, [[yii\rest\Serializer]]
will also include the pagination information by the following HTTP headers:

* `X-Pagination-Total-Count`: The total number of resources;
* `X-Pagination-Page-Count`: The number of pages;
* `X-Pagination-Current-Page`: The current page (1-based);
* `X-Pagination-Per-Page`: The number of resources in each page;
* `Link`: A set of navigational links allowing client to traverse the resources page by page.

Since collection in REST APIs is a data provider, it shares all data provider features i.e. pagination and sorting.

An example may be found in the [Quick Start](rest-quick-start.md#trying-it-out) section.
