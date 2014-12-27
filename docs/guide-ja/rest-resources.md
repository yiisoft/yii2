リソース
========

RESTful API は、つまるところ、*リソース* にアクセスし、それを操作するものです。
MVC の枠組の中では、リソースは [models](structure-models.md) として見ることが出来ます。

リソースをどのように表現すべきかについて制約がある訳ではありませんが、Yii においては、通常は、次のような理由によって、リソースを [[yii\base\Model]] またはその子クラス (例えば [[yii\db\ActiveRecord]]) のオブジェクトとして表現することになります。

* [[yii\base\Model]] は [[yii\base\Arrayable]] インタフェイスを実装しています。
  これによって、リソースのデータを RESTful API を通じて公開する仕方をカスタマイズすることが出来ます。
* [[yii\base\Model]] は [入力値のバリデーション](input-validation.md) をサポートしています。
  これは、RESTful API がデータ入力をサポートする必要がある場合に役に立ちます。
* [[yii\db\ActiveRecord]] は DB データのアクセスと操作に対する強力なサポートを提供しています。
  リソースデータがデータベースに保存されているときは、アクティブレコードが最適の選択です。

この節では、主として、[[yii\base\Model]] クラス (またはその子クラス) から拡張したリソースクラスにおいて、どのデータを RESTful API を通じて返すことが出来るかを指定する方法を説明します。
リソースクラスが [[yii\base\Model]] から拡張しない場合は、全てのパブリックなメンバ変数が返されます。


## フィールド <a name="fields"></a>

RESTful API のレスポンスにリソースを含めるとき、リソースは文字列にシリアライズされる必要があります。
Yii はこのプロセスを二つのステップに分けます。
最初に、リソースは [[yii\rest\Serializer]] によって配列に変換されます。
次に、その配列が [[yii\web\ResponseFormatterInterface|レスポンスフォーマッタ]] によって、リクエストされた形式 (例えば JSON や XML) の文字列にシリアライズされます。
リソースクラスを開発するときに主として力を注ぐべきなのは、最初のステップです。

[[yii\base\Model::fields()|fields()]] および/または [[yii\base\Model::extraFields()|extraFields()]] をオーバーライドすることによって、リソースのどういうデータ (*フィールド* と呼ばれます) を配列表現に入れることが出来るかを指定することが出来ます。
この二つのメソッドの違いは、前者が配列表現に含まれるべきフィールドのデフォルトのセットを指定するのに対して、後者はエンドユーザが `expand` クエリパラメータで要求したときに配列に含めることが出来る追加のフィールドを指定する、という点にあります。
例えば、

```
// fields() に宣言されている全てのフィールドを返す。
http://localhost/users

// id と email のフィールドだけを返す (ただし、fields() で宣言されているなら) 。
http://localhost/users?fields=id,email

// fields() の全てのフィールドと profile のフィールドを返す (ただし、profile が extraFields() で宣言されているなら)。
http://localhost/users?expand=profile

// id、email、profile のフィールドだけを返す (ただし、それらが fields() と extraFields() で宣言されているなら)。
http://localhost/users?fields=id,email&expand=profile
```


### fields()` をオーバーライドする <a name="overriding-fields"></a>

デフォルトでは、[[yii\base\Model::fields()]] は、モデルの全ての属性をフィールドとして返し、[[yii\db\ActiveRecord::fields()]] は、DB から投入された属性だけを返します。

`fields()` をオーバーライドして、フィールドを追加、削除、名前変更、または再定義することが出来ます。
`fields()` の返り値は配列でなければなりません。
配列のキーはフィールド名であり、配列の値は対応するフィールドの定義です。
フィールドの定義は、プロパティ/属性の名前か、あるいは、対応するフィールドの値を返す無名関数とすることが出来ます。
フィールド名がそれを定義する属性名と同一であるという特殊な場合においては、配列のキーを省略することが出来ます。
例えば、

```php
// 明示的に全てのフィールドをリストする方法。(API の後方互換性を保つために) DB テーブルやモデル属性の
// 変更がフィールドの変更を引き起こさないことを保証したい場合に適している。
public function fields()
{
    return [
        // フィールド名が属性名と同じ
        'id',
        // フィールド名は "email"、対応する属性名は "email_address"
        'email' => 'email_address',
        // フィールド名は "name"、その値は PHP コールバックで定義
        'name' => function ($model) {
            return $model->first_name . ' ' . $model->last_name;
        },
    ];
}

// いくつかのフィールドを除去する方法。親の実装を継承しつつ、公開すべきでないフィールドを
// 除外したいときに適している。
public function fields()
{
    $fields = parent::fields();

    // 公開すべきでない情報を含むフィールドを削除する
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning|警告: 既定ではモデルの全ての属性がエクスポートされる配列に含まれるため、データを精査して、
> 公開すべきでない情報が含まれていないことを確認すべきです。そういう情報がある場合は、
> `fields()` をオーバーライドして、除去すべきです。上記の例では、`auth_key`、`password_hash`
> および `password_reset_token` を選んで除去しています。


### `extraFields()` をオーバーライドする<a name="overriding-extra-fields"></a>

By default, [[yii\base\Model::extraFields()]] returns nothing, while [[yii\db\ActiveRecord::extraFields()]]
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


## Links <a name="links"></a>

[HATEOAS](http://en.wikipedia.org/wiki/HATEOAS), an abbreviation for Hypermedia as the Engine of Application State,
promotes that RESTful APIs should return information that allow clients to discover actions supported for the returned
resources. The key of HATEOAS is to return a set of hyperlinks with relation information when resource data are served
by the APIs.

Your resource classes may support HATEOAS by implementing the [[yii\web\Linkable]] interface. The interface
contains a single method [[yii\web\Linkable::getLinks()|getLinks()]] which should return a list of [[yii\web\Link|links]].
Typically, you should return at least the `self` link representing the URL to the resource object itself. For example,

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

When a `User` object is returned in a response, it will contain a `_links` element representing the links related
to the user, for example,

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

An example may be found in the [Quick Start](rest-quick-start.md#trying-it-out) section.
