リソース
========

RESTful API は、つまるところ、*リソース* にアクセスし、それを操作するものです。
MVC の枠組の中では、リソースは [モデル](structure-models.md) として見ることが出来ます。

リソースをどのように表現すべきかについて制約がある訳ではありませんが、
Yii においては、通常は、次のような理由によって、リソースを [[yii\base\Model]] またはその子クラス (例えば [[yii\db\ActiveRecord]])
のオブジェクトとして表現することになります。

* [[yii\base\Model]] は [[yii\base\Arrayable]] インタフェイスを実装しています。
  これによって、リソースのデータを RESTful API を通じて公開する仕方をカスタマイズすることが出来ます。
* [[yii\base\Model]] は [入力値の検証](input-validation.md) をサポートしています。
  これは、RESTful API がデータ入力をサポートする必要がある場合に役に立ちます。
* [[yii\db\ActiveRecord]] は DB データのアクセスと操作に対する強力なサポートを提供しています。
  リソース・データがデータベースに保存されているときは、アクティブ・レコードが最適の選択です。

このセクションでは、主として、[[yii\base\Model]] クラス (またはその子クラス) から拡張したリソース・クラスにおいて、
RESTful API を通じて返すことが出来るデータを指定する方法を説明します。
リソース・クラスが [[yii\base\Model]] から拡張したものでない場合は、全てのパブリックなメンバ変数が返されます。


## フィールド <span id="fields"></span>

RESTful API のレスポンスにリソースを含めるとき、リソースは文字列にシリアライズされる必要があります。
Yii はこのプロセスを二つのステップに分けます。
最初に、リソースは [[yii\rest\Serializer]] によって配列に変換されます。
次に、その配列が [[yii\web\ResponseFormatterInterface|レスポンス・フォーマッタ]] によって、リクエストされた形式 (例えば JSON や XML) の文字列にシリアライズされます。
リソース・クラスを開発するときに主として力を注ぐべきなのは、最初のステップです。

[[yii\base\Model::fields()|fields()]] および/または [[yii\base\Model::extraFields()|extraFields()]] をオーバーライドすることによって、
リソースのどういうデータ (*フィールド* と呼ばれます) を配列表現に入れることが出来るかを指定することが出来ます。
この二つのメソッドの違いは、前者が配列表現に含まれるべきフィールドのデフォルトのセットを指定するのに対して、
後者はエンド・ユーザが `expand` クエリ・パラメータで要求したときに配列に含めることが出来る追加のフィールドを指定する、
という点にあります。例えば、

```
// fields() で宣言されている全てのフィールドを返す。
http://localhost/users

// "id" と "email" のフィールドだけを返す (ただし、fields() で宣言されていれば) 。
http://localhost/users?fields=id,email

// fields() の全てのフィールドと "profile" のフィールドを返す (ただし、"profile" が extraFields() で宣言されていれば)。
http://localhost/users?expand=profile

// fields() の全てのフィールドと post の "author" フィールドを返す
// (ただし、"author" が post モデルの extraFields() にあれば）。
http://localhost/comments?expand=post.author

// "id" と"email" (ただし、fileds() で宣言されていれば) と
// "profile" (ただし、extraFields() で宣言されていれば) を返す。
http://localhost/users?fields=id,email&expand=profile
```

### fields()` をオーバーライドする <span id="overriding-fields"></span>

デフォルトでは、[[yii\base\Model::fields()]] は、モデルの全ての属性をフィールドとして返し、
[[yii\db\ActiveRecord::fields()]] は、DB から投入された属性だけを返します。

`fields()` をオーバーライドして、フィールドを追加、削除、名前変更、または再定義することが出来ます。
`fields()` の返り値は配列でなければなりません。
配列のキーはフィールド名であり、配列の値は対応するフィールドの定義です。
フィールドの定義は、プロパティ/属性の名前か、あるいは、対応するフィールドの値を返す無名関数とすることが出来ます。
フィールド名がそれを定義する属性名と同一であるという特殊な場合においては、配列のキーを省略することが出来ます。例えば、

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

> Warning: デフォルトではモデルの全ての属性がエクスポートされる配列に含まれるため、データを精査して、
> 公開すべきでない情報が含まれていないことを確認すべきです。そういう情報がある場合は、
> `fields()` をオーバーライドして、除去すべきです。上記の例では、`auth_key`、`password_hash`
> および `password_reset_token` を選んで除去しています。


### `extraFields()` をオーバーライドする<span id="overriding-extra-fields"></span>

デフォルトでは、[[yii\base\Model::extraFields()]] は空の配列を返し、[[yii\db\ActiveRecord::extraFields()]]
は DB から取得されたリレーションの名前を返します。

`extraFields()` によって返されるデータの形式は `fields()` のそれと同じです。
通常、`extraFields()` は、主として、値がオブジェクトであるフィールドを指定するのに使用されます。
例えば、次のようなフィールドの宣言があるとしましょう。

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

`http://localhost/users?fields=id,email&expand=profile` というリクエストは、次のような JSON データを返すことが出来ます。

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


## リンク <span id="links"></span>

[HATEOAS](http://en.wikipedia.org/wiki/HATEOAS) は、Hypermedia as the Engine of Application State (アプリケーション状態のエンジンとしてのハイパーメディア) の略称です。
HATEOAS は、RESTful API は自分が返すリソースについて、どのようなアクションがサポートされているかをクライアントが発見できるような情報を返すべきである、という概念です。
HATEOAS のキーポイントは、リソース・データが API によって提供されるときには、
関連する情報を一群のハイパーリンクによって返すべきである、ということです。

あなたのリソース・クラスは、[[yii\web\Linkable]] インタフェイスを実装することによって、HATEOAS をサポートすることが出来ます。
このインタフェイスは、[[yii\web\Link|リンク]] のリストを返すべき [[yii\web\Linkable::getLinks()|getLinks()]] メソッド一つだけを含みます。
典型的には、少なくとも、リソース・オブジェクトそのものへの URL を表現する `self` リンクを返さなければなりません。例えば、

```php
use yii\base\Model;
use yii\web\Link; // JSON ハイパーメディア API 言語に定義されているリンク・オブジェクトを表す
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

`UserResource` オブジェクトがレスポンスで返されるとき、レスポンスはそのユーザに関連するリンクを表現する `_links` 要素を含むことになります。
例えば、

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


## コレクション <span id="collections"></span>

リソース・オブジェクトは *コレクション* としてグループ化することが出来ます。
各コレクションは、同じ型のリソースのリストを含みます。

コレクションは配列として表現することも可能ですが、通常は、[データ・プロバイダ](output-data-providers.md) として表現する方がより望ましい方法です。
これは、データ・プロバイダがリソースの並べ替えとページネーションをサポートしているからです。
並べ替えとページネーションは、コレクションを返す RESTful API にとっては、普通に必要とされる機能です。
例えば、次のアクションは投稿のリソースについてデータ・プロバイダを返すものです。

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

データ・プロバイダが RESTful API のレスポンスで送信される場合は、[[yii\rest\Serializer]]
が現在のページのリソースを取り出して、リソース・オブジェクトの配列としてシリアライズします。
それだけでなく、[[yii\rest\Serializer]] は次の HTTP ヘッダを使ってページネーション情報もレスポンスに含めます。

* `X-Pagination-Total-Count`: リソースの総数
* `X-Pagination-Page-Count`: ページ数
* `X-Pagination-Current-Page`: 現在のページ (1 から始まる)
* `X-Pagination-Per-Page`: 各ページのリソース数
* `Link`: クライアントがリソースをページごとにたどることが出来るようにするための一群のナビゲーションリンク

REST API におけるコレクションはデータ・プロバイダであるため、データ・プロバイダの全ての機能、すなわち、ページネーションやソーティングを共有しています。

その一例を [クイック・スタート](rest-quick-start.md#trying-it-out) のセクションで見ることが出来ます。

### コレクションをフィルタリングする <span id="filtering-collections"></span>

バージョン 2.0.13 以降、Yii はコレクションをフィルタリングする便利な機能を提供しています。
その一例を [クイック・スタート](rest-quick-start.md#trying-it-out) のガイドに見ることが出来ます。
エンド・ボイントをあなた自身が実装しようとしている場合、フィルタリングは
データ・プロバイダのガイドの [データ・フィルタを使ってデータ・プロバイダをフィルタリングする](output-data-providers.md#filtering-data-providers-using-data-filters
 のセクションで述べられている方法で行うことが出来ます。
