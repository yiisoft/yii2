データウィジェット
==================

> Note|注意: この節はまだ執筆中です。

ListView
--------



DetailView
----------

DetailView は単一のデータ [[yii\widgets\DetailView::$model|モデル]] の詳細を表示します。

モデルを標準的な書式で表示する場合 (例えば、全てのモデル属性をそれぞれテーブルの一行として表示する場合) に最も適しています。
モデルは [[\yii\base\Model]] のインスタンスか連想配列かのどちらかを取り得ます。
 
DetailView は [[yii\widgets\DetailView::$attributes]] プロパティを使って、モデルのどの属性をどのように表示すべきかを決定します。

次に DetailView の典型的な用例を示します。
 
```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',             // title 属性 (平文テキストで)
        'description:html',  // description 属性は HTML
        [                    // モデルの所有者の名前
            'label' => '所有者',
            'value' => $model->owner->name,
        ],
    ],
]);
```


GridView
--------

データグリッドすなわち GridView は Yii の最も強力なウィジェットの一つです。
これは、システムの管理セクションを素速く作らねばならない時に、この上なく便利なものです。
このウィジェットは [データプロバイダ](output-data-providers.md) からデータを受けて、テーブルの形式で、行ごとに一組のカラムを使ってデータを表示します。

テーブルの各行が一つのデータアイテムを表し、カラムは通常はアイテムの属性を表します (カラムの中には、複数の属性を組み合わせた複雑な式に対応するものや、静的なテキストを表すものもあります)。

グリッドビューはデータアイテムの並べ替えとページネーションの両方をサポートします。
並べ替えとページネーションは、AJAX モードで、あるいは、通常のページリクエストとして、実行することが出来ます。
GridView を使用することの利点は、ユーザが JavaScript を無効化しても、並べ替えとページネーションが自動的に通常のページリクエストにグレードダウンして、引き続き期待通りの動作をするという点にあります。

GridView を使うために必要な最小限のコードは次のようなものです。

```php
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

上記のコードは、最初にデータプロバイダを作成し、次に GridView を使って、データプロバイダから受け取る全ての行の全ての属性を表示するものです。
そして、表示されたテーブルには、並べ替えとページネーションの機能が装備されます。

### グリッドカラム

Yii のグリッドは数多くのカラムから構成されます。
それらのカラムは、タイプや設定に応じて、さまざまな形でデータを表示することが出来ます。

カラムは、GridView の構成情報の `columns` の部分において、以下のように定義されます。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        // $dataProvider に含まれるデータによって定義される単純なカラム
        // モデルのカラムのデータが使われる
        'id',
        'username',
        // 複雑なカラム定義
        [
            'class' => 'yii\grid\DataColumn', // 省略可。これが既定値。
            'value' => function ($data) {
                return $data->name; // 配列データの場合は $data['name']。例えば、SqlDataProvider を使う場合。
            },
        ],
    ],
]);
```

構成情報の `columns` の部分が指定されない場合は、Yii は、可能な限り、データプロバイダのモデルの全てのカラムを表示しようとすることに注意してください。

### カラムクラス

グリッドのカラムは、いろいろなカラムクラスを使うことでカスタマイズすることが出来ます。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- ここ
            // ここで追加のプロパティを構成することが出来ます
        ],
```

Yii によって提供されるカラムクラスを以下で見ていきますが、それらに加えて、あなた自身のカラムクラスを作成することも出来ます。

全てのカラムクラスは [[\yii\grid\Column]] を拡張するものですので、グリッドのカラムを構成するときに設定できる共通のオプションがいくつかあります。

- `header` によって、ヘッダ行のコンテントを設定することが出来ます。
- `footer` によって、振った行のコンテントを設定することが出来ます。
- `visible` はカラムの可視性を定義します。
- `content` によって、行のデータを返す有効な PHP コールバックを渡すことが出来ます。書式は以下の通りです。

  ```php
  function ($model, $key, $index, $column) {
      return '文字列';
  }
  ```

下記のオプションに配列を渡して、コンテナ要素のさまざまな HTML オプションを指定することが出来ます。

- `headerOptions`
- `footerOptions`
- `filterOptions`
- `contentOptions`

#### データカラム <a name="data-column"></a>

データカラムは、データの表示と並べ替えに使用されます。
これがデフォルトのカラムタイプですので、これを使用するときはクラスの指定を省略することが出来ます。

データカラムの主要な設定項目は、その書式です。
書式は `format` 属性によって定義することが出来ます。
その値は、デフォルトでは [[\yii\i18n\Formatter|Formatter]] である `formatter` [アプリケーションコンポーネント](structure-application-components.md) のメソッドと対応するものです。

```php
echo GridView::widget([
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'text'
        ],
        [
            'attribute' => 'birthday',
            'format' => ['date', 'php:Y-m-d']
        ],
    ],
]); 
```

上記において、`text` は [[\yii\i18n\Formatter::asText()]] に対応し、カラムの値が最初の引数として渡されます。
二番目のカラムの定義では、`date` が [[\yii\i18n\Formatter::asDate()]] に対応します。
カラムの値が、ここでも、最初の引数として渡され、'php:Y-m-d' が二番目の引数の値として渡されます。

利用できるフォーマッタの一覧については、[データのフォーマット](output-formatter.md) の節を参照してください。


#### アクションカラム

アクションカラムは、各行について、更新や削除などのアクションボタンを表示します。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\ActionColumn',
            // ここで追加のプロパティを構成することが出来ます
        ],
```

構成が可能なプロパティは、以下の通りです。

- `controller` は、アクションを処理すべきコントローラの ID です。設定されていない場合は、現在アクティブなコントローラが使われます。
- `template` は、アクションカラムの各セルを構成するのに使用されるテンプレートを定義します。
  波括弧に囲まれたトークンは、コントローラのアクション ID として扱われます (アクションカラムのコンテキストでは *ボタンの名前* とも呼ばれます)。
  これらは、[[yii\grid\ActionColumn::$buttons|buttons]] によって定義される、対応するボタン表示コールバックによって置き換えられます。
  例えば、`{view}` というトークンは、`buttons['view']` のコールバックの結果によって置き換えられます。
  コールバックが見つからない場合は、トークンは空文字列によって置き換えられます。
  デフォルトのテンプレートは `{view} {update} {delete}` です。
- `buttons` はボタン表示コールバックの配列です。
  配列のキーはボタンの名前 (波括弧を除く) であり、値は対応するボタン表示コールバックです。
  コールバックは下記のシグニチャを使わなければなりません。

```php
function ($url, $model, $key) {
    // ボタンの HTML コードを返す
}
```

上記のコードで、`$url` はカラムがボタンのために生成する URL、`$model` は現在の行に表示されるモデルオブジェクト、そして `$key` はデータプロバイダ配列のモデルのキーです。

- `urlCreator` は、指定されたモデルの情報を使って、ボタンの URL を生成するコールバックです。
  コールバックのシグニチャは [[yii\grid\ActionColumn::createUrl()]] のそれと同じでなければなりません。
  このプロパティが設定されていないときは、ボタンの URL は [[yii\grid\ActionColumn::createUrl()]] を使って生成されます。

#### チェックボックスカラム

CheckboxColumn はチェックボックスのカラムを表示します。

[[yii\grid\GridView]] に CheckboxColumn を追加するためには、以下のようにして、[[yii\grid\GridView::$columns|columns]] 構成情報にカラムを追加します。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        // ...
        [
            'class' => 'yii\grid\CheckboxColumn',
            // ここで追加のプロパティを構成することが出来ます
        ],
    ],
```

ユーザはチェックボックスをクリックして、グリッドの行を選択することが出来ます。
選択された行は、次の JavaScript コードを呼んで取得することが出来ます。

```javascript
var keys = $('#grid').yiiGridView('getSelectedRows');
// keys は選択された行と関連付けられたキーの配列
```

#### シリアルカラム

シリアルカラムは、`1` から始まる行番号を表示します。

使い方は、次のように、とても簡単です。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'], // <-- ここ
        // ...
```


### データを並べ替える

- https://github.com/yiisoft/yii2/issues/1576

### データをフィルタリングする

データをフィルタリングするためには、GridView は、フィルタリングのフォームから入力を受け取り、検索基準に合わせてデータプロバイダのクエリを調整するための [モデル](structure-models.md) を必要とします。
[アクティブレコード](db-active-record.md) を使用している場合は、必要な機能を提供する検索用のモデルクラスを作成するのが一般的なプラクティスです (あなたに代って Gii が生成してくれます)。
このクラスは、検索のためのバリデーション規則を定義し、データプロバイダを返す `search()` メソッドを提供するものです。

`Post` モデルに対して検索機能を追加するために、次の例のようにして、`PostSearch` モデルを作成することが出来ます。

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{
    public function rules()
    {
        // rules() にあるフィールドだけが検索可能
        return [
            [['id'], 'integer'],
            [['title', 'creation_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // 親クラスの scenarios() の実装をバイパスする
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Post::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // 検索フォームのデータをロードして検証する
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // フィルタを追加してクエリを調整する
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', $this->title])
              ->andFilterWhere(['like', 'creation_date', $this->creation_date]);

        return $dataProvider;
    }
}

```

この `search()` メソッドをコントローラで使用して、GridView のためのデータプロバイダを取得することが出来ます。

```php
$searchModel = new PostSearch();
$dataProvider = $searchModel->search(Yii::$app->request->get());

return $this->render('myview', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);
```

そしてビューでは、`$dataProvider` と `$searchModel` を GridView に与えます。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
]);
```


### モデルのリレーションを扱う

GridView でアクティブレコードを表示するときに、リレーションのカラムの値、例えば、単に投稿者の `id` というのではなく、投稿者の名前を表示するという場合に遭遇するかも知れません。
`Post` モデルが `author` という名前のリレーションを持っていて、その投稿者のモデルが `name` という属性を持っているなら、カラムの属性名を `author.name` と定義します。
そうすれば、GridView が投稿者の名前を表示するようになります。
ただし、並べ替えとフィルタリングは、既定では有効になりません。
これらの機能を追加するためには、前の項で導入した `PostSearch` モデルを調整しなければなりません。

リレーションのカラムによる並べ替えを有効にするためには、リレーションのテーブルを結合し、データプロバイダの Sort コンポーネントに並べ替えの規則を追加します。

```php
$query = Post::find();
$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);

// リレーション `author` を結合します。これはテーブル `users` に対するリレーションであり、
// テーブルエイリアスを `author` とします。
$query->joinWith(['author' => function($query) { $query->from(['author' => 'users']); }]);
// リレーションのカラムによる並べ替えを有効にします。
$dataProvider->sort->attributes['author.name'] = [
    'asc' => ['author.name' => SORT_ASC],
    'desc' => ['author.name' => SORT_DESC],
];

// ...
```

フィルタリングも上記と同じ joinWith の呼び出しを必要とします。また、次のように、`attributes` と `rules` の中で、検索可能なカラムを追加で定義する必要があります。

```php
public function attributes()
{
    // 検索可能な属性にリレーションのフィールドを追加する
    return array_merge(parent::attributes(), ['author.name']);
}

public function rules()
{
    return [
        [['id'], 'integer'],
        [['title', 'creation_date', 'author.name'], 'safe'],
    ];
}
```

`search()` メソッドでは、次のように、もう一つのフィルタ条件を追加するだけです。

```php
$query->andFilterWhere(['LIKE', 'author.name', $this->getAttribute('author.name')]);
```

> Info|情報: 上の例では、リレーション名とテーブルエイリアスに同じ文字列を使用しています。
> しかし、エイリアスとリレーション名が異なる場合は、どこでエイリアスを使い、どこでリレーション名を使うかに注意を払わなければなりません。
> これに関する簡単な規則は、データベースクエリを構築するために使われる全ての場所でエイリアスを使い、`attributes()` や `rules()` など、その他の全ての定義においてリレーション名を使う、というものです。
>
> 例えば、投稿者のリレーションテーブルに `au` というエイリアスを使う場合は、joinWith の文は以下のようになります。
>
> ```php
> $query->joinWith(['author' => function($query) { $query->from(['au' => 'users']); }]);
> ```
> リレーションの定義においてエイリアスが定義されている場合は、単に `$query->joinWith(['author']);` として呼び出すことも可能です。
>
> フィルタ条件においてはエイリアスが使われなければなりませんが、属性の名前はリレーション名のままで変りません。
>
> ```php
> $query->andFilterWhere(['LIKE', 'au.name', $this->getAttribute('author.name')]);
> ```
>
> 並べ替えの定義についても同じことです。
>
> ```php
> $dataProvider->sort->attributes['author.name'] = [
>      'asc' => ['au.name' => SORT_ASC],
>      'desc' => ['au.name' => SORT_DESC],
> ];
> ```
>
> さらに、並べ替えの [[yii\data\Sort::defaultOrder|defaultOrder]] を指定するときも、エイリアスではなくリレーション名を使う必要があります。
>
> ```php
> $dataProvider->sort->defaultOrder = ['author.name' => SORT_ASC];
> ```

> Info|情報: `joinWith` およびバックグラウンドで実行されるクエリの詳細については、[アクティブレコード - リレーションを使ってテーブルを結合する](db-active-record.md#lazy-and-eager-loading) を参照してください。

#### Using sql views for filtering, sorting and displaying data

There is also another approach that can be faster and more useful - sql views. For example, if we need to show the gridview 
with users and their profiles, we can do so in this way:

```php
CREATE OR REPLACE VIEW vw_user_info AS
    SELECT user.*, user_profile.lastname, user_profile.firstname
    FROM user, user_profile
    WHERE user.id = user_profile.user_id
```

Then you need to create the ActiveRecord that will be representing this view:

```php

namespace app\models\views\grid;

use yii\db\ActiveRecord;

class UserView extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vw_user_info';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // define here your rules
        ];
    }

    /**
     * @inheritdoc
     */
    public static function attributeLabels()
    {
        return [
            // define here your attribute labels
        ];
    }


}
```

After that you can use this UserView active record with search models, without additional specification of sorting and filtering attributes.
All attributes will be working out of the box. Note that this approach has several pros and cons:

- you don't need to specify different sorting and filtering conditions. Everything works out of the box;
- it can be much faster because of the data size, count of sql queries performed (for each relation you will need an additional query);
- since this is just a simple mapping UI on the sql view it lacks some domain logic that is in your entities, so if you have some methods like `isActive`,
`isDeleted` or others that will influence the UI, you will need to duplicate them in this class too.


### Multiple GridViews on one page

You can use more than one GridView on a single page but some additional configuration is needed so that
they do not interfere with each other.
When using multiple instances of GridView you have to configure different parameter names for
the generated sort and pagination links so that each GridView has its own individual sorting and pagination.
You do so by setting the [[yii\data\Sort::sortParam|sortParam]] and [[yii\data\Pagination::pageParam|pageParam]]
of the dataProvider's [[yii\data\BaseDataProvider::$sort|sort]] and [[yii\data\BaseDataProvider::$pagination|pagination]]
instances.

Assume we want to list the `Post` and `User` models for which we have already prepared two data providers
in `$userProvider` and `$postProvider`:

```php
use yii\grid\GridView;

$userProvider->pagination->pageParam = 'user-page';
$userProvider->sort->sortParam = 'user-sort';

$postProvider->pagination->pageParam = 'post-page';
$postProvider->sort->sortParam = 'post-sort';

echo '<h1>Users</h1>';
echo GridView::widget([
    'dataProvider' => $userProvider,
]);

echo '<h1>Posts</h1>';
echo GridView::widget([
    'dataProvider' => $postProvider,
]);
```

### Using GridView with Pjax

TBD
