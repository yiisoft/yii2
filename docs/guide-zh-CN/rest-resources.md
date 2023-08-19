资源
=========

RESTful 的 API 都是关于访问和操作 *资源*，可将资源看成 MVC 模式中的
[模型](structure-models.md)

在如何代表一个资源没有固定的限定，在 Yii 中通常使用 
[[yii\base\Model]] 或它的子类（如 [[yii\db\ActiveRecord]]）
代表资源，是为以下原因：

* [[yii\base\Model]] 实现了 [[yii\base\Arrayable]] 接口，
  它允许你通过 RESTful API 自定义你想要公开的资源数据。
* [[yii\base\Model]] 支持 [输入验证](input-validation.md),
  在你的 RESTful API 需要支持数据输入时非常有用。
* [[yii\db\ActiveRecord]] 提供了强大的数据库访问和操作方面的支持，
  如资源数据需要存到数据库它提供了完美的支持。

本节主要描述资源类如何从 [[yii\base\Model]] (或它的子类) 继承
并指定哪些数据可通过 RESTful API 返回，如果资源类没有
继承 [[yii\base\Model]] 会将它所有的公开成员变量返回。


## 字段 <span id="fields"></span>

当 RESTful API 响应中包含一个资源时，该资源需要序列化成一个字符串。
Yii 将这个过程分成两步，首先，资源会被 [[yii\rest\Serializer]] 转换成数组，
然后，该数组会通过 [[yii\web\ResponseFormatterInterface|response formatters]]
根据请求格式（如 JSON，XML）被序列化成字符串。
当开发一个资源类时应重点关注第一步。

通过覆盖 [[yii\base\Model::fields()|fields()]] 和/或
[[yii\base\Model::extraFields()|extraFields()]] 方法,
可指定资源中称为 *字段* 的数据放入展现数组中，
两个方法的差别为前者指定默认包含到展现数组的字段集合，
后者指定由于终端用户的请求包含 `expand` 参数哪些额外的字段应被包含到展现数组，例如，

```
// 返回 fields() 方法中声明的所有字段
http://localhost/users

// 只返回 fields() 方法中声明的“id”和“email”字段
http://localhost/users?fields=id,email

// 返回 fields() 方法中声明的所有字段，以及 extraFields() 方法中的“profile”字段
http://localhost/users?expand=profile

// 返回 fields() 方法中声明的所有字段，以及 post 中的“author”
// 如果它位于 post 模型的 extraFields() 中
http://localhost/comments?expand=post.author

// 返回 fields() 方法中的“id”，“email”，以及 extraFields() 方法中的“profile”字段
http://localhost/users?fields=id,email&expand=profile
```


### 覆盖 `fields()` 方法 <span id="overriding-fields"></span>

[[yii\base\Model::fields()]] 默认返回模型的所有属性作为字段，
[[yii\db\ActiveRecord::fields()]] 只返回和数据表关联的属性作为字段。

可覆盖 `fields()` 方法来增加、删除、重命名、重定义字段，
`fields()` 的返回值应为数组，数组的键为字段名
数组的值为对应的字段定义，可为属性名或返回对应的字段值的匿名函数，
特殊情况下，如果字段名和属性名相同，
可省略数组的键，例如

```php
// 明确列出每个字段，适用于你希望数据表或
// 模型属性修改时不导致你的字段修改（保持后端API兼容性）
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

// 过滤掉一些字段，适用于你希望继承
// 父类实现同时你想屏蔽掉一些敏感字段
public function fields()
{
    $fields = parent::fields();

    // 删除一些包含敏感信息的字段
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: 模型的所有属性默认会被包含到 API 结果中，
> 应检查数据确保没包含敏感数据，如果有敏感数据，
> 应覆盖 `fields()` 过滤掉，在上述例子中，我们选择过滤掉 `auth_key`，
> `password_hash` 和 `password_reset_token`。


### 覆盖 `extraFields()` 方法 <span id="overriding-extra-fields"></span>

[[yii\base\Model::extraFields()]] 默认返回空值，
[[yii\db\ActiveRecord::extraFields()]] 返回和数据表关联的属性。

`extraFields()` 返回的数据格式和 `fields()` 相同，
一般 `extraFields()` 主要用于指定哪些值为对象的字段，
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

`http://localhost/users?fields=id,email&expand=profile` 的请求可能返回如下 JSON 数据：

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


## 链接 <span id="links"></span>

[HATEOAS](https://zh.wikipedia.org/wiki/HATEOAS), 
是 Hypermedia as the Engine of Application State的缩写,
提升 RESTful API 应返回允许终端用户访问的资源操作的信息，
HATEOAS 的目的是在API中返回包含相关链接信息的资源数据。 

资源类通过实现 [[yii\web\Linkable]] 接口来支持 HATEOAS，
该接口包含方法 [[yii\web\Linkable::getLinks()|getLinks()]] 来返回
[[yii\web\Link|links]] 列表，典型情况下应返回包含代表本资源对象 URL 的 `self` 链接，例如

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

当响应中返回一个 `User` 对象，
它会包含一个 `_links` 单元表示和用户相关的链接，例如

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


## 集合 <span id="collections"></span>

资源对象可以组成 *集合*，
每个集合包含一组相同类型的资源对象。

集合可被展现成数组，更多情况下展现成 [data providers](output-data-providers.md)。
因为 data providers 支持资源的排序和分页，
这个特性在 RESTful API 返回集合时也用到，
例如，如下操作返回 post 资源的 data provider：

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

当在 RESTful API 响应中发送 data provider 时， 
[[yii\rest\Serializer]] 会取出资源的当前页并组装成资源对象数组，
[[yii\rest\Serializer]] 也通过如下 HTTP 头包含页码信息：

* `X-Pagination-Total-Count`：资源所有数量；
* `X-Pagination-Page-Count`：页数；
* `X-Pagination-Current-Page`：当前页（从 1 开始）；
* `X-Pagination-Per-Page`：每页资源数量；
* `Link`：允许客户端一页一页遍历资源的导航链接集合。

由于 REST API 中的集合是 data provider，因此它共享所有 data provider 功能，即分页和排序。

可在[快速入门](rest-quick-start.md#trying-it-out) 一节中找到样例。

### 过滤集合 <span id="filtering-collections"></span>

从 2.0.13 版本开始，Yii 提供了过滤集合的工具。一个例子可以在
[快速入门](rest-quick-start.md#trying-it-out) 指南中找到。如果您自己实现末端，
可以按照 Data Providers 指南的
[Filtering Data Providers using Data Filters](output-data-providers.md#filtering-data-providers-using-data-filters)
部分中的描述进行过滤。
