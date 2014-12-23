Resources
资源
=========

RESTful APIs are all about accessing and manipulating *resources*. You may view resources as
[models](structure-models.md) in the MVC paradigm.
RESTful 的 API 都是关于访问和操作 *资源*，可将资源看成MVC模式中的
[模型](structure-models.md)

While there is no restriction in how to represent a resource, in Yii you usually would represent resources
in terms of objects of [[yii\base\Model]] or its child classes (e.g. [[yii\db\ActiveRecord]]), for the
following reasons:
在如何代表一个资源没有固定的限定，在Yii中通常使用 [[yii\base\Model]] 或它的子类（如 [[yii\db\ActiveRecord]]）
代表资源，是为以下原因：

* [[yii\base\Model]] implements the [[yii\base\Arrayable]] interface, which allows you to
  customize how you want to expose resource data through RESTful APIs.
* [[yii\base\Model]] 实现了 [[yii\base\Arrayable]] 接口，它允许你通过RESTful API自定义你想要公开的资源数据。
* [[yii\base\Model]] supports [input validation](input-validation.md), which is useful if your RESTful APIs
  need to support data input.
* [[yii\base\Model]] 支持 [输入验证](input-validation.md), 在你的RESTful API需要支持数据输入时非常有用。
* [[yii\db\ActiveRecord]] provides powerful DB data access and manipulation support, which makes it
  a perfect fit if your resource data is stored in databases.
* [[yii\db\ActiveRecord]] 提供了强大的数据库访问和操作方面的支持，如资源数据需要存到数据库它提供了完美的支持。

In this section, we will mainly describe how a resource class extending from [[yii\base\Model]] (or its child classes)
can specify what data may be returned via RESTful APIs. If the resource class does not extend from [[yii\base\Model]],
then all its public member variables will be returned.
本节主要描述资源类如何从 [[yii\base\Model]] (或它的子类) 继承并指定哪些数据可通过RESTful API返回，如果资源类没有
继承 [[yii\base\Model]] 会将它所有的公开成员变量返回。


## Fields <a name="fields"></a>
## 字段 <a name="fields"></a>

When including a resource in a RESTful API response, the resource needs to be serialized into a string.
Yii breaks this process into two steps. First, the resource is converted into an array by [[yii\rest\Serializer]].
Second, the array is serialized into a string in a requested format (e.g. JSON, XML) by
[[yii\web\ResponseFormatterInterface|response formatters]]. The first step is what you should mainly focus when
developing a resource class.
当RESTful API响应中包含一个资源时，该资源需要序列化成一个字符串。
Yii将这个过程分成两步，首先，资源会被[[yii\rest\Serializer]]转换成数组，
然后，该数组会通过[[yii\web\ResponseFormatterInterface|response formatters]]根据请求格式(如JSON, XML)被序列化成字符串。
当开发一个资源类时应重点关注第一步。

By overriding [[yii\base\Model::fields()|fields()]] and/or [[yii\base\Model::extraFields()|extraFields()]],
you may specify what data, called *fields*, in the resource can be put into its array representation.
The difference between these two methods is that the former specifies the default set of fields which should
be included in the array representation, while the latter specifies additional fields which may be included
in the array if an end user requests for them via the `expand` query parameter. For example,
通过覆盖 [[yii\base\Model::fields()|fields()]] 和/或 [[yii\base\Model::extraFields()|extraFields()]] 方法,
可指定资源中称为 *字段* 的数据放入展现数组中，两个方法的差别为前者指定默认包含到展现数组的字段集合，
后者指定由于终端用户的请求包含 `expand` 参数哪些额外的字段应被包含到展现数组，例如，


```
// returns all fields as declared in fields()
http://localhost/users

// only returns field id and email, provided they are declared in fields()
http://localhost/users?fields=id,email

// returns all fields in fields() and field profile if it is in extraFields()
http://localhost/users?expand=profile

// only returns field id, email and profile, provided they are in fields() and extraFields()
http://localhost/users?fields=id,email&expand=profile
```
```
// 返回fields()方法中申明的所有字段
http://localhost/users

// 只返回fields()方法中申明的id和email字段
http://localhost/users?fields=id,email

// 返回fields()方法申明的所有字段，以及extraFields()方法中的profile字段
http://localhost/users?expand=profile

// 返回回fields()和extraFields()方法中提供的id, email 和 profile字段
http://localhost/users?fields=id,email&expand=profile
```


### Overriding `fields()` <a name="overriding-fields"></a>
### 覆盖 `fields()` 方法 <a name="overriding-fields"></a>

By default, [[yii\base\Model::fields()]] returns all model attributes as fields, while
[[yii\db\ActiveRecord::fields()]] only returns the attributes which have been populated from DB.
[[yii\base\Model::fields()]] 默认返回模型的所有属性作为字段，
[[yii\db\ActiveRecord::fields()]] 只返回和数据表关联的属性作为字段。

You can override `fields()` to add, remove, rename or redefine fields. The return value of `fields()`
should be an array. The array keys are the field names, and the array values are the corresponding
field definitions which can be either property/attribute names or anonymous functions returning the
corresponding field values. In the special case when a field name is the same as its defining attribute
name, you can omit the array key. For example,
可覆盖 `fields()` 方法来增加、删除、重命名、重定义字段，`fields()` 的返回值应为数组，数组的键为字段名
数组的值为对应的字段定义，可为属性名或返回对应的字段值的匿名函数，特殊情况下，如果字段名和属性名相同，
可省略数组的键，例如

```php
// 明确列出每个字段，适用于你希望数据表或模型属性修改时不导致你的字段修改（保持后端API兼容性）
public function fields()
{
    return [
        // 字段名和属性名相同
        'id',
        // 字段名为"email", 对应的属性名为"email_address"
        'email' => 'email_address',
        // 字段名为"name", 值由一个PHP回调函数定义
        'name' => function ($model) {
            return $model->first_name . ' ' . $model->last_name;
        },
    ];
}

// 过滤掉一些字段，适用于你希望继承父类实现同时你想屏蔽掉一些敏感字段
public function fields()
{
    $fields = parent::fields();

    // 删除一些包含敏感信息的字段
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: Because by default all attributes of a model will be included in the API result, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.
> 警告: 模型的所有属性默认会被包含到API结果中，应检查数据确保没包含敏感数据，如果有敏感数据，
> 应覆盖`fields()`过滤掉，在上述例子中，我们选择过滤掉 `auth_key`, `password_hash` 和 `password_reset_token`.


### Overriding `extraFields()` <a name="overriding-extra-fields"></a>
### 覆盖 `extraFields()` 方法 <a name="overriding-extra-fields"></a>

By default, [[yii\base\Model::extraFields()]] returns nothing, while [[yii\db\ActiveRecord::extraFields()]]
returns the names of the relations that have been populated from DB.
[[yii\base\Model::extraFields()]] 默认返回空值，[[yii\db\ActiveRecord::extraFields()]] 返回和数据表关联的属性。

The return data format of `extraFields()` is the same as that of `fields()`. Usually, `extraFields()`
is mainly used to specify fields whose values are objects. For example, given the following field
declaration,
`extraFields()` 返回的数据格式和 `fields()` 相同，一般`extraFields()` 主要用于指定哪些值为对象的字段，
例如，给定以下字段申明

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

`http://localhost/users?fields=id,email&expand=profile` 的请求可能返回如下JSON 数据:

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


## Links <a name="links"></a>
## 链接 <a name="links"></a>

[HATEOAS](http://en.wikipedia.org/wiki/HATEOAS), 是Hypermedia as the Engine of Application State的缩写,
提升RESTful API 应返回允许终端用户访问的资源操作的信息，HATEOAS 的目的是在API中返回包含相关链接信息的资源数据。 

资源类通过实现[[yii\web\Linkable]] 接口来支持HATEOAS，该接口包含方法 [[yii\web\Linkable::getLinks()|getLinks()]] 来返回
[[yii\web\Link|links]] 列表，典型情况下应返回包含代表本资源对象URL的 `self` 链接，例如

```php
use yii\db\ActiveRecord;
use yii\web\Link;
use yii\web\Linkable;
use yii\helpers\Url;

class User extends ActiveRecord implements Linkable
{
    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['user/view', 'id' => $this->id], true),
        ];
    }
}
```

当响应中返回一个`User` 对象，它会包含一个 `_links` 单元表示和用户相关的链接，例如

```
{
    "id": 100,
    "email": "user@example.com",
    // ...
    "_links" => [
        "self": "https://example.com/users/100"
    ]
}
```


## Collections <a name="collections"></a>
## 集合 <a name="collections"></a>

资源对象可以组成 *集合*，每个集合包含一组相同类型的资源对象。

While collections can be represented as arrays, it is usually more desirable to represent them
as [data providers](output-data-providers.md). This is because data providers support sorting and pagination
of resources, which is a commonly needed feature for RESTful APIs returning collections. For example,
the following action returns a data provider about the post resources:
集合可被展现成数组，更多情况下展现成 [data providers](output-data-providers.md). 
因为data providers支持资源的排序和分页，这个特性在 RESTful API 返回集合时也用到，例如This is because data providers support sorting and pagination
如下操作返回post资源的data provider:

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

当在RESTful API响应中发送data provider 时， [[yii\rest\Serializer]] 会取出资源的当前页并组装成资源对象数组，
[[yii\rest\Serializer]] 也通过如下HTTP头包含页码信息：

* `X-Pagination-Total-Count`: The total number of resources;
* `X-Pagination-Page-Count`: The number of pages;
* `X-Pagination-Current-Page`: The current page (1-based);
* `X-Pagination-Per-Page`: The number of resources in each page;
* `Link`: A set of navigational links allowing client to traverse the resources page by page.
* `X-Pagination-Total-Count`: 资源所有数量;
* `X-Pagination-Page-Count`: 页数;
* `X-Pagination-Current-Page`: 当前页(从1开始);
* `X-Pagination-Per-Page`: 每页资源数量;
* `Link`: 允许客户端一页一页遍历资源的导航链接集合.

An example may be found in the [Quick Start](rest-quick-start.md#trying-it-out) section.
可在[快速入门](rest-quick-start.md#trying-it-out) 一节中找到样例.
