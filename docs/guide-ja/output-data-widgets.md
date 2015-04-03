データウィジェット
==================

Yii はデータを表示するために使うことが出来る一連の [ウィジェット](structure-widgets.md) を提供しています。
[DetailView](#detail-view) は、単一のレコードのデータを表示するのに使うことが出来ます。
それに対して、[ListView](#list-view) と [GridView](#grid-view) は、複数のデータレコードをリストまたはテーブルで表示することが出来るもので、ページネーション、並べ替え、フィルタリングなどの機能を提供するものです。


DetailView
----------

DetailView は単一のデータ [[yii\widgets\DetailView::$model|モデル]] の詳細を表示します。

モデルを標準的な書式で表示する場合 (例えば、全てのモデル属性をそれぞれテーブルの一行として表示する場合) に最も適しています。
モデルは [[\yii\base\Model]] またはそのサブクラス、例えば [アクティブレコード](db-active-record.md) のインスタンスか、連想配列かのどちらかにすることが出来ます。
 
DetailView は [[yii\widgets\DetailView::$attributes]] プロパティを使って、モデルのどの属性が表示されるべきか、また、どういうフォーマットで表示されるべきかを決定します。
利用できるフォーマットのオプションについては、[フォーマッタの節](output-formatting.md) を参照してください。

次に DetailView の典型的な用例を示します。
 
```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',               // title 属性 (平文テキストで)
        'description:html',    // description 属性は HTML としてフォーマットされる
        [                      // モデルの所有者の名前
            'label' => '所有者',
            'value' => $model->owner->name,
        ],
        'created_at:datetime', // 作成日は datetime としてフォーマットされる
    ],
]);
```


ListView
--------

[[yii\widgets\ListView|ListView]] ウィジェットは、[データプロバイダ](output-data-providers.md) からのデータを表示するのに使用されます。
各データモデルは指定された [[yii\widgets\ListView::$itemView|ビューファイル]] を使って表示されます。
ListView は、特に何もしなくても、ページネーション、並べ替え、フィルタリングなどの機能を提供してくれますので、エンドユーザに情報を表示するためにも、データ管理 UI を作成するためにも、非常に便利なウィジェットです。

典型的な使用方法は以下の通りです。

```php
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
]);
```

`_post` ビューは次のような内容を含むことが出来ます。


```php
<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>
<div class="post">
    <h2><?= Html::encode($model->title) ?></h2>
    
    <?= HtmlPurifier::process($model->text) ?>    
</div>
```

上記のビューファイルでは、現在のデータモデルを `$model` としてアクセスすることが出来ます。
追加で次のものを利用することも出来ます。

- `$key`: mixed - データアイテムと関連付けられたキーの値。
- `$index`: integer - データプロバイダによって返されるアイテムの配列の 0 から始まるインデックス。
- `$widget`: ListView - ウィジェットのインスタンス。

追加のデータを各ビューに渡す必要がある場合は、次のように、[[yii\widgets\ListView::$viewParams|$viewParams]] を使って「キー - 値」のペアを渡すことが出来ます。

```php
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
    'viewParams' => [
        'fullView' => true,
        'context' => 'main-page',
        // ...
    ],
]);
```

このようにすると、これらをビューで変数として利用できるようになります。


GridView <a name="grid-view"></a>
--------

データグリッドすなわち GridView は Yii の最も強力なウィジェットの一つです。
これは、システムの管理セクションを素速く作らねばならない時に、この上なく便利なものです。
このウィジェットは [データプロバイダ](output-data-providers.md) からデータを受けて、テーブルの形式で、行ごとに一組の [[yii\grid\GridView::columns|カラム]] を使ってデータを表示します。

テーブルの各行が一つのデータアイテムを表します。そして、一つのカラムは通常はアイテムの一属性を表します
(カラムの中に、複数の属性を組み合わせた複雑な式に対応するものや、静的なテキストを表すものを含めることも出来ます)。

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
表示されるテーブルには、特に何も設定しなくても、並べ替えとページネーションの機能が装備されます。

### グリッドのカラム

グリッドのテーブルのカラムは [[yii\grid\Column]] クラスとして表現され、GridView の構成情報の [[yii\grid\GridView::columns|columns]] プロパティで構成されます。
カラムは、タイプや設定の違いに応じて、データをさまざまな形で表現することが出来ます。
デフォルトのクラスは [[yii\grid\DataColumn]] です。これは、モデルの一つの属性を表現し、その属性による並べ替えとフィルタリングを可能にするものです。

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
            'class' => 'yii\grid\DataColumn', // 省略可。これがデフォルト値。
            'value' => function ($data) {
                return $data->name; // 配列データの場合は $data['name']。例えば、SqlDataProvider を使う場合。
            },
        ],
    ],
]);
```

構成情報の [[yii\grid\GridView::columns|columns]] の部分が指定されない場合は、Yii は、データプロバイダのモデルの表示可能な全てのカラムを表示しようとすることに注意してください。

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

全てのカラムクラスは [[yii\grid\Column]] を拡張するものですので、グリッドのカラムを構成するときに設定できる共通のオプションがいくつかあります。


- [[yii\grid\Column::header|header]] によって、ヘッダ行のコンテントを設定することが出来ます。
- [[yii\grid\Column::footer|footer]] によって、フッタ行のコンテントを設定することが出来ます。
- [[yii\grid\Column::visible|visible]] はカラムの可視性を定義します。
- [[yii\grid\Column::content|content]] によって、行のデータを返す有効な PHP コールバックを渡すことが出来ます。書式は以下の通りです。

  ```php
  function ($model, $key, $index, $column) {
      return '文字列';
  }
  ```

下記のオプションに配列を渡して、コンテナ要素のさまざまな HTML オプションを指定することが出来ます。

- [[yii\grid\Column::headerOptions|headerOptions]]
- [[yii\grid\Column::footerOptions|footerOptions]]
- [[yii\grid\Column::filterOptions|filterOptions]]
- [[yii\grid\Column::contentOptions|contentOptions]]


#### データカラム <span id="data-column"></span>

[[yii\grid\DataColumn|データカラム]] は、データの表示と並べ替えに使用されます。
これがデフォルトのカラムタイプですので、これを使用するときはクラスの指定を省略することが出来ます。

データカラムの主要な設定項目は、その [[yii\grid\DataColumn::format|format]] プロパティです。
その値が、デフォルトでは [[\yii\i18n\Formatter|Formatter]] である `formatter` [アプリケーションコンポーネント](structure-application-components.md) のメソッドに対応します。

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

利用できるフォーマッタの一覧については、[データのフォーマット](output-formatting.md) の節を参照してください。

データカラムを構成するためには、ショートカット形式を使うことも出来ます。
それについては、[[yii\grid\GridView::columns|columns]] の API ドキュメントで説明されています。

#### アクションカラム

[[yii\grid\ActionColumn|アクションカラム]] は、各行について、更新や削除などのアクションボタンを表示します。

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

- [[yii\grid\ActionColumn::controller|controller]] は、アクションを処理すべきコントローラの ID です。
  設定されていない場合は、現在アクティブなコントローラが使われます。
- [[yii\grid\ActionColumn::template|template]] は、アクションカラムの各セルを構成するのに使用されるテンプレートを定義します。
  波括弧に囲まれたトークンは、コントローラのアクション ID として扱われます (アクションカラムのコンテキストでは *ボタンの名前* とも呼ばれます)。
  これらは、[[yii\grid\ActionColumn::$buttons|buttons]] によって定義される、対応するボタン表示コールバックによって置き換えられます。
  例えば、`{view}` というトークンは、`buttons['view']` のコールバックの結果によって置き換えられます。
  コールバックが見つからない場合は、トークンは空文字列によって置き換えられます。
  デフォルトのテンプレートは `{view} {update} {delete}` です。
- [[yii\grid\ActionColumn::buttons|buttons]] はボタン表示コールバックの配列です。
  配列のキーはボタンの名前 (波括弧を除く) であり、値は対応するボタン表示コールバックです。
  コールバックは下記のシグニチャを使わなければなりません。

  ```php
  function ($url, $model, $key) {
      // ボタンの HTML コードを返す
  }
  ```

  上記のコードで、`$url` はカラムがボタンのために生成する URL、`$model` は現在の行に表示されるモデルオブジェクト、そして `$key` はデータプロバイダの配列の中にあるモデルのキーです。

- [yii\grid\ActionColumn::urlCreator|urlCreator]] は、指定されたモデルの情報を使って、ボタンの URL を生成するコールバックです。
  コールバックのシグニチャは [[yii\grid\ActionColumn::createUrl()]] のそれと同じでなければなりません。
  このプロパティが設定されていないときは、ボタンの URL は [[yii\grid\ActionColumn::createUrl()]] を使って生成されます。

#### チェックボックスカラム

[[yii\grid\CheckboxColumn|チェックボックスカラム]] はチェックボックスのカラムを表示します。

GridView に CheckboxColumn を追加するためには、以下のようにして、[[yii\grid\GridView::$columns|columns]] 構成情報にカラムを追加します。

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

> Note|注意: このセクションはまだ執筆中です。
>
> - https://github.com/yiisoft/yii2/issues/1576

### データをフィルタリングする

データをフィルタリングするためには、GridView は、フィルタリングのフォームから入力を受け取り、検索基準に合わせてデータプロバイダのクエリを修正するための [モデル](structure-models.md) を必要とします。
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

        // フィルタを追加してクエリを修正する
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
    'columns' => [
        // ...
    ],
]);
```


### モデルのリレーションを扱う

GridView でアクティブレコードを表示するときに、リレーションのカラムの値、例えば、単に投稿者の `id` というのではなく、投稿者の名前を表示するという場合に遭遇するかも知れません。
`Post` モデルが `author` という名前のリレーションを持っていて、その投稿者のモデルが `name` という属性を持っているなら、[[yii\grid\GridView::$columns]] の属性名を `author.name` と定義します。
そうすれば、GridView が投稿者の名前を表示するようになります。
ただし、並べ替えとフィルタリングは、デフォルトでは有効になりません。
これらの機能を追加するためには、前の項で導入した `PostSearch` モデルを修正しなければなりません。

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

> Info|情報: `joinWith` およびバックグラウンドで実行されるクエリの詳細については、[アクティブレコード - リレーションを使ってテーブルを結合する](db-active-record.md#joining-with-relations) を参照してください。

#### SQL ビューを使って、データのフィルタリング・並べ替え・表示をする

もう一つ別に、もっと高速で便利な手法があります。SQL ビューです。
例えば、ユーザとユーザのプロファイルを一緒にグリッドビューに表示する必要がある場合、次のような SQL ビューを作成することが出来ます。

```sql
CREATE OR REPLACE VIEW vw_user_info AS
    SELECT user.*, user_profile.lastname, user_profile.firstname
    FROM user, user_profile
    WHERE user.id = user_profile.user_id
```

そして、このビューを表す ActiveRecord を作成します。

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
            // ここで規則を定義
        ];
    }

    /**
     * @inheritdoc
     */
    public static function attributeLabels()
    {
        return [
            // ここで属性のラベルを定義
        ];
    }


}
```

このようにした後は、この UserView アクティブレコードを検索用のモデルとともに使うことが出来ます。
並べ替えやフィルタリングの属性を追加で定義する必要はありません。
全ての属性がそのままで動作します。
この手法にはいくつかの長所と短所があることに注意してください。

- 並べ替えとフィルタリングの条件をいろいろと定義する必要はありません。全てそのままで動きます。
- データサイズが小さく、実行される SQL クエリの数が少ない (通常なら全てのリレーションについて一つずつ必要になる追加のクエリが要らない) ため、非常に高速になり得ます。
- これは SQL ビューにかぶせた単純な UI に過ぎないもので、エンティティに含まれるドメインロジックを欠いています。
従って、`isActive` や `isDeleted` などのような UI に影響するメソッドがある場合は、それらをこのクラスの中に複製する必要があります。


### 一つのページに複数のグリッドビュー

一つのページで二つ以上のグリッドビューを使うことが出来ますが、お互いが干渉しないように、追加の構成がいくつか必要になります。
グリッドビューの複数のインスタンスを使う場合は、並べ替えとページネーションのリンクが違うパラメータ名を持って生成されるように構成して、それぞれのグリッドビューが独立した並べ替えとページネーションを持つことが出来るようにしなければなりません。
そのためには、データプロバイダの [[yii\data\BaseDataProvider::$sort|sort]] と [[yii\data\BaseDataProvider::$pagination|pagination]] インスタンスの [[yii\data\Sort::sortParam|sortParam]] と [[yii\data\Pagination::pageParam|pageParam]] を設定します。

`Post` と `User` のリストを表示するために、二つのプロバイダ、`$userProvider` と `$postProvider` を準備済みであると仮定します。

```php
use yii\grid\GridView;

$userProvider->pagination->pageParam = 'user-page';
$userProvider->sort->sortParam = 'user-sort';

$postProvider->pagination->pageParam = 'post-page';
$postProvider->sort->sortParam = 'post-sort';

echo '<h1>ユーザ</h1>';
echo GridView::widget([
    'dataProvider' => $userProvider,
]);

echo '<h1>投稿</h1>';
echo GridView::widget([
    'dataProvider' => $postProvider,
]);
```

### GridView を Pjax とともに使う

> Note|注意: このセクションはまだ執筆中です。
>

(内容未定)
