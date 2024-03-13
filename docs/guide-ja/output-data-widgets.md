データ・ウィジェット
====================

Yii はデータを表示するために使うことが出来る一連の [ウィジェット](structure-widgets.md) を提供しています。
[DetailView](#detail-view) は、単一のレコードのデータを表示するのに使うことが出来ます。
それに対して、[ListView](#list-view) と [GridView](#grid-view) は、複数のデータ・レコードをリストまたはテーブルで表示することが出来るもので、
ページネーション、並べ替え、フィルタリングなどの機能を提供するものです。


DetailView <span id="detail-view"></span>
----------

DetailView は単一のデータ [[yii\widgets\DetailView::$model|モデル]] の詳細を表示します。

モデルを標準的な書式で表示する場合 (例えば、全てのモデル属性をそれぞれテーブルの一行として表示する場合) に最も適しています。
モデルは [[\yii\base\Model]] またはそのサブ・クラス、例えば [アクティブ・レコード](db-active-record.md) のインスタンスか、連想配列かのどちらかにすることが出来ます。
 
DetailView は [[yii\widgets\DetailView::$attributes]] プロパティを使って、モデルのどの属性が表示されるべきか、また、どういうフォーマットで表示されるべきかを決定します。
利用できるフォーマットのオプションについては、[フォーマッタのセクション](output-formatting.md) を参照してください。

次に DetailView の典型的な用例を示します。

```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',                                           // title 属性 (平文テキストで)
        'description:html',                                // description 属性は HTML としてフォーマットされる
        [                                                  // モデルの所有者の名前
            'label' => '所有者',
            'value' => $model->owner->name,
            'contentOptions' => ['class' => 'bg-red'],     // 値のタグをカスタマイズする HTML 属性
            'captionOptions' => ['tooltip' => 'Tooltip'],  // ラベルのタグをカスタマイズする HTML 属性
        ],
        'created_at:datetime',                             // 作成日時は datetime としてフォーマットされる
    ],
]);
```

[[yii\widgets\GridView|GridView]] が一組のモデルを処理するのとは異なって、
[[yii\widgets\DetailView|DetailView]] は一つのモデルしか処理しないということを覚えておいてください。
表示すべきモデルはビューの変数としてアクセスできる `$model` 一つだけですから、たいていの場合、クロージャを使用する必要はありません。

しかし、クロージャが役に立つ場合もあります。例えば、`visible` が指定されており、それが `false` と評価される場合には
`value` の計算を避けたい場合です。

```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        [
            'attribute' => 'owner',
            'value' => function ($model) {
                return $model->owner->name;
            },
            'visible' => \Yii::$app->user->can('posts.owner.view'),
        ],
    ],
]);
```

ListView <span id="list-view"></span>
--------

[[yii\widgets\ListView|ListView]] ウィジェットは、[データ・プロバイダ](output-data-providers.md) からのデータを表示するのに使用されます。
各データ・モデルは指定された [[yii\widgets\ListView::$itemView|ビュー・ファイル]] を使って表示されます。
ListView は、特に何もしなくても、ページネーション、並べ替え、フィルタリングなどの機能を提供してくれますので、
エンド・ユーザに情報を表示するためにも、データ管理 UI を作成するためにも、非常に便利なウィジェットです。

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

上記のビュー・ファイルでは、現在のデータ・モデルを `$model` としてアクセスすることが出来ます。追加で次のものを利用することも出来ます。

- `$key`: mixed - データ・アイテムと関連付けられたキーの値。
- `$index`: integer - データ・プロバイダによって返されるアイテムの配列の 0 から始まるインデックス。
- `$widget`: ListView - ウィジェットのインスタンス。

追加のデータを各ビューに渡す必要がある場合は、次のように、[[yii\widgets\ListView::$viewParams|$viewParams]]
を使って「キー・値」のペアを渡すことが出来ます。

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


GridView <span id="grid-view"></span>
--------

データ・グリッドすなわち [[yii\grid\GridView|GridView]] は Yii の最も強力なウィジェットの一つです。これは、システムの管理セクションを素速く作らねばならない時に、
この上なく便利なものです。このウィジェットは [データ・プロバイダ](output-data-providers.md) からデータを受けて、
テーブルの形式で、行ごとに一組の [[yii\grid\GridView::columns|カラム]] を使ってデータを表示します。

テーブルの各行が一つのデータ・アイテムを表します。そして、一つのカラムは通常はアイテムの一属性を表します
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

上記のコードは、最初にデータ・プロバイダを作成し、次に GridView を使って、データ・プロバイダから受け取る全ての行の全ての属性を表示するものです。
表示されるテーブルには、特に何も設定しなくても、並べ替えとページネーションの機能が装備されます。


### グリッドのカラム <span id="grid-columns"></span>

グリッドのテーブルのカラムは [[yii\grid\Column]] クラスとして表現され、GridView の構成情報の
[[yii\grid\GridView::columns|columns]] プロパティで構成されます。カラムは、タイプや設定の違いに応じて、
データをさまざまな形で表現することが出来ます。デフォルトのクラスは [[yii\grid\DataColumn]] です。
これは、モデルの一つの属性を表現し、その属性による並べ替えとフィルタリングを可能にするものです。

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

構成情報の [[yii\grid\GridView::columns|columns]] の部分が指定されない場合は、Yii は、
データ・プロバイダのモデルの表示可能な全てのカラムを表示しようとすることに注意してください。


### カラム・クラス <span id="column-classes"></span>

グリッドのカラムは、いろいろなカラム・クラスを使うことでカスタマイズすることが出来ます。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- ここ
            // ここで追加のプロパティを構成することが出来ます
        ],
```

Yii によって提供されるカラム・クラスを以下で見ていきますが、それらに加えて、あなた自身のカラム・クラスを作成することも出来ます。

全てのカラム・クラスは [[yii\grid\Column]] を拡張するものですので、グリッドのカラムを構成するときに設定できる
共通のオプションがいくつかあります。

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


#### データ・カラム <span id="data-column"></span>

[[yii\grid\DataColumn|データ・カラム]] は、データの表示と並べ替えに使用されます。
これがデフォルトのカラムタイプですので、これを使用するときはクラスの指定を省略することが出来ます。

データ・カラムの主要な設定項目は、その [[yii\grid\DataColumn::format|format]] プロパティです。
その値が、デフォルトでは [[\yii\i18n\Formatter|Formatter]] である `formatter` [アプリケーション・コンポーネント](structure-application-components.md) のメソッドに対応します。

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
        'created_at:datetime', // shortcut format
        [
            'label' => '教育',
            'attribute' => 'education',
            'filter' => ['0' => '初等教育', '1' => '中等教育', '2' => '高等教育'],
            'filterInputOptions' => ['prompt' => '全ての教育', 'class' => 'form-control', 'id' => null]
        ],
    ],
]);
```

上記において、`text` は [[\yii\i18n\Formatter::asText()]] に対応し、カラムの値が最初の引数として渡されます。
二番目のカラムの定義では、`date` が [[\yii\i18n\Formatter::asDate()]] に対応します。
カラムの値が、ここでも、最初の引数として渡され、'php:Y-m-d' が二番目の引数の値として渡されます。

利用できるフォーマッタの一覧については、[データのフォーマット](output-formatting.md) のセクションを参照してください。

データカラムを構成するためには、ショートカット形式を使うことも出来ます。
それについては、[[yii\grid\GridView::columns|columns]] の API ドキュメントで説明されています。

フィルタ・インプットの HTML を制御するためには、[[yii\grid\DataColumn::filter|filter]] と
[[yii\grid\DataColumn::filterInputOptions|filterInputOptions]] を使用して下さい。

デフォルトでは、カラム・ヘッダは [[yii\data\Sort::link]] によってレンダリングされますが、[[yii\grid\Column::header]] を使って調整することが出来ます。
ヘッダのテキストを変更するには、上の例のように、[[yii\grid\DataColumn::$label]] を設定しなければなりません。
デフォルトでは、ラベルはデータ・モデルによって設定されます。詳細は [[yii\grid\DataColumn::getHeaderCellLabel]] を参照して下さい。

#### アクション・カラム <span id="action-column"></span>

[[yii\grid\ActionColumn|アクション・カラム]] は、各行について、更新や削除などのアクション・ボタンを表示します。

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
- [[yii\grid\ActionColumn::template|template]] は、アクション・カラムの各セルを構成するのに使用されるテンプレートを定義します。
  波括弧に囲まれたトークンは、コントローラのアクション ID として扱われます (アクション・カラムのコンテキストでは *ボタンの名前* とも呼ばれます)。
  これらは、[[yii\grid\ActionColumn::$buttons|buttons]] によって定義される、対応するボタン表示コールバックによって置き換えられます。
  例えば、`{view}` というトークンは、`buttons['view']` のコールバックの結果によって置き換えられます。
  コールバックが見つからない場合は、トークンは空文字列によって置き換えられます。デフォルトのテンプレートは `{view} {update} {delete}` です。
- [[yii\grid\ActionColumn::buttons|buttons]] はボタン表示コールバックの配列です。
  配列のキーはボタンの名前 (波括弧を除く) であり、値は対応するボタン表示コールバックです。コールバックは下記のシグニチャを使わなければなりません。

  ```php
  function ($url, $model, $key) {
      // ボタンの HTML コードを返す
  }
  ```

  上記のコードで、`$url` はカラムがボタンのために生成する URL、`$model` は現在の行に表示されるモデル・オブジェクト、
  そして `$key` はデータ・プロバイダの配列の中にあるモデルのキーです。

- [[yii\grid\ActionColumn::urlCreator|urlCreator]] は、指定されたモデルの情報を使って、ボタンの URL を生成するコールバックです。
  コールバックのシグニチャは [[yii\grid\ActionColumn::createUrl()]] のそれと同じでなければなりません。
  このプロパティが設定されていないときは、ボタンの URL は [[yii\grid\ActionColumn::createUrl()]] を使って生成されます。
- [[yii\grid\ActionColumn::visibleButtons|visibleButtons]] は、各ボタンの可視性の条件を定義する配列です。
  配列のキーはボタンの名前 (波括弧を除く) であり、値は真偽値 `true`/`false` または無名関数です。
  ボタンの名前がこの配列の中で指定されていない場合は、デフォルトで、ボタンが表示されます。
  コールバックは次のシグニチャを使わなければなりません。

  ```php
  function ($model, $key, $index) {
      return $model->status === 'editable';
  }
  ```

  または、真偽値を渡すことも出来ます。

  ```php
  [
      'update' => \Yii::$app->user->can('update')
  ]
  ```

#### チェックボックス・カラム <span id="checkbox-column"></span>

[[yii\grid\CheckboxColumn|チェックボックス・カラム]] はチェックボックスのカラムを表示します。

GridView に CheckboxColumn を追加するためには、以下のようにして、[[yii\grid\GridView::$columns|columns]] 構成情報にカラムを追加します。

```php
echo GridView::widget([
    'id' => 'grid',
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

#### シリアル・カラム <span id="serial-column"></span>

[[yii\grid\SerialColumn|シリアルカラム]] は、`1` から始まる行番号を表示します。

使い方は、次のように、とても簡単です。

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'], // <-- ここ
        // ...
```


### データを並べ替える <span id="sorting-data"></span>

> Note: このセクションはまだ執筆中です。
>
> - https://github.com/yiisoft/yii2/issues/1576

### データをフィルタリングする <span id="filtering-data"></span>

データをフィルタリングするためには、GridView は検索基準を表す [モデル](structure-models.md) を必要とします。
検索基準は、通常は、グリッド・ビューのテーブルのフィルタのフィールドから取得されます。
[アクティブ・レコード](db-active-record.md) を使用している場合は、必要な機能を提供する検索用のモデル・クラスを
作成するのが一般的なプラクティスです (あなたに代って [Gii](start-gii.md) が生成してくれます)。
このクラスが、グリッド・ビューのテーブルに表示されるフィルタ・コントロールのための検証規則を定義し、
検索基準に従って修正されたクエリを持つデータ・プロバイダを返す `search()` メソッドを提供します。

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

> Tip: フィルタのクエリを構築する方法を学ぶためには、[クエリ・ビルダ](db-query-builder.md)、
> 中でも特に [フィルタ条件](db-query-builder.md#filter-conditions) を参照してください。

この `search()` メソッドをコントローラで使用して、GridView のためのデータ・プロバイダを取得することが出来ます。

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

### 独立したフィルタ・フォーム <span id="separate-filter-form"></span>

たいていの場合はグリッド・ビューのヘッダのフィルタで十分でしょう。しかし、独立したフィルタのフォームが必要な場合でも、
簡単に追加することができます。まず、以下の内容を持つパーシャル・ビュー `_search.php` を作成します。

```php
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PostSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="post-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'creation_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::submitButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
```

そして、これを以下のように `index.php` ビューにインクルードします。

```php
<?= $this->render('_search', ['model' => $searchModel]) ?>
```

> Note: Gii を使って CRUD コードを生成する場合、デフォルトで、独立したフィルタ・フォーム (`_search.php`) が生成されます。
  ただし、`index.php` ビューの中ではコメント・アウトされています。コメントを外せば、すぐに使うことが出来ます。

独立したフィルタ・フォームは、グリッド・ビューに表示されないフィールドによってフィルタをかけたり、
または日付の範囲のような特殊なフィルタ条件を使う必要があったりする場合に便利です。
日付の範囲によってフィルタする場合は、DB には存在しない `createdFrom` と `createdTo` という属性を検索用のモデルに追加すると良いでしょう。

```php
class PostSearch extends Post
{
    /**
     * @var string
     */
    public $createdFrom;

    /**
     * @var string
     */
    public $createdTo;
}
```

そして、`search()` メソッドでクエリの条件を次のように拡張します。

```php
$query->andFilterWhere(['>=', 'creation_date', $this->createdFrom])
      ->andFilterWhere(['<=', 'creation_date', $this->createdTo]);
```

そして、フィルタ・フォームに、日付の範囲を示すフィールドを追加します。

```php
<?= $form->field($model, 'creationFrom') ?>

<?= $form->field($model, 'creationTo') ?>
```

### モデルのリレーションを扱う <span id="working-with-model-relations"></span>

GridView でアクティブ・レコードを表示するときに、例えば、単に投稿者の `id` ではなく、
投稿者の名前のような関連するカラムの値を表示するという場合に遭遇するかも知れません。
`Post` モデルが `author` という名前のリレーションを持っていて、その投稿者のモデルが `name` という属性を持っているなら、
[[yii\grid\GridView::$columns]] の属性名を `author.name` と定義します。
そうすれば、GridView が投稿者の名前を表示するようになります。ただし、並べ替えとフィルタリングは、デフォルトでは有効になりません。
これらの機能を追加するためには、前の項で導入した `PostSearch` モデルを修正しなければなりません。

リレーションのカラムによる並べ替えを有効にするためには、リレーションのテーブルを結合し、
データ・プロバイダの Sort コンポーネントに並べ替えの規則を追加します。

```php
$query = Post::find();
$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);

// リレーション `author` を結合します。これはテーブル `users` に対するリレーションであり、
// テーブル・エイリアスを `author` とします。
$query->joinWith(['author' => function($query) { $query->from(['author' => 'users']); }]);
// バージョン 2.0.7 以降では、上の行は $query->joinWith('author AS author'); として単純化することが出来ます。
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

> Info: 上の例では、リレーション名とテーブル・エイリアスに同じ文字列を使用しています。
> しかし、エイリアスとリレーション名が異なる場合は、どこでエイリアスを使い、どこでリレーション名を使うかに注意を払わなければなりません。
> これに関する簡単な規則は、データベース・クエリを構築するために使われる全ての場所でエイリアスを使い、
> `attributes()` や `rules()` など、その他の全ての定義においてリレーション名を使う、というものです。
>
> 例えば、投稿者のリレーションテーブルに `au` というエイリアスを使う場合は、joinWith の文は以下のようになります。
>
> ```php
> $query->joinWith(['author au']);
> ```
>
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
> さらに、並べ替えの [[yii\data\Sort::defaultOrder|defaultOrder]] を指定するときも、
> エイリアスではなくリレーション名を使う必要があります。
>
> ```php
> $dataProvider->sort->defaultOrder = ['author.name' => SORT_ASC];
> ```

> Info: `joinWith` およびバックグラウンドで実行されるクエリの詳細については、
> [アクティブ・レコード - リレーションを使ってテーブルを結合する](db-active-record.md#joining-with-relations) を参照してください。

#### SQL ビューを使って、データのフィルタリング・並べ替え・表示をする <span id="using-sql-views"></span>

もう一つ別に、もっと高速で便利な手法があります。SQL ビューです。
例えば、ユーザとユーザのプロファイルを一緒にグリッド・ビューに表示する必要がある場合、次のような SQL ビューを作成することが出来ます。

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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // ここで規則を定義
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            // ここで属性のラベルを定義
        ];
    }


}
```

このようにした後は、この UserView アクティブ・レコードを検索用のモデルとともに使うことが出来ます。並べ替えやフィルタリングの属性を追加で定義する必要はありません。
全ての属性がそのままで動作します。この手法にはいくつかの長所と短所があることに注意してください。

- 並べ替えとフィルタリングの条件をいろいろと定義する必要はありません。全てそのままで動きます。
- データサイズが小さく、実行される SQL クエリの数が少ない (通常なら全てのリレーションについて一つずつ必要になる追加のクエリが要らない) ため、非常に高速になり得ます。
- これは SQL ビューにかぶせた単純な UI に過ぎないもので、エンティティに含まれるドメイン・ロジックを欠いています。
従って、`isActive` や `isDeleted` などのような UI に影響するメソッドがある場合は、それらをこのクラスの中に複製する必要があります。


### 一つのページに複数のグリッド・ビュー <span id="multiple-gridviews"></span>

一つのページで二つ以上のグリッド・ビューを使うことが出来ますが、
お互いが干渉しないように、追加の構成がいくつか必要になります。
グリッド・ビューの複数のインスタンスを使う場合は、並べ替えとページネーションのリンクが違うパラメータ名を持って生成されるように構成して、
それぞれのグリッド・ビューが独立した並べ替えとページネーションを持つことが出来るようにしなければなりません。
そのためには、データ・プロバイダの [[yii\data\BaseDataProvider::$sort|sort]] と [[yii\data\BaseDataProvider::$pagination|pagination]]
インスタンスの [[yii\data\Sort::sortParam|sortParam]] と [[yii\data\Pagination::pageParam|pageParam]]
を設定します。

`Post` と `User` のリストを表示するために、二つのプロバイダ、`$userProvider` と `$postProvider`
を準備済みであると仮定します。

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

### GridView を Pjax とともに使う <span id="using-gridview-with-pjax"></span>

[[yii\widgets\Pjax|Pjax]] ウィジェットを使うと、ページ全体をリロードせずに、
ページの一部分だけを更新することが出来ます。
これを使うと、フィルタを使うときに、[[yii\grid\GridView|GridView]] の中身だけを更新することが出来ます。

```php
use yii\widgets\Pjax;
use yii\grid\GridView;

Pjax::begin([
    // PJax のオプション
]);
    Gridview::widget([
        // GridView のオプション
    ]);
Pjax::end();
```

Pjax は、[[yii\widgets\Pjax|Pjax]] ウィジェットの内側にあるリンク、および、[[yii\widgets\Pjax::$linkSelector|Pjax::$linkSelector]]
で指定されているリンクに対しても動作します。
しかし、これは [[yii\grid\ActionColumn|ActionColumn]] のリンクに対しては問題となり得ます。
この問題を防止するためには、[[yii\grid\ActionColumn::$buttons|ActionColumn::$buttons]]
プロパティを編集して `data-pjax="0"` という HTML 属性を追加します。

#### Gii における Pjax を伴う GridView/ListView

バージョン 2.0.5 以降、[Gii](start-gii.md) の CRUD ジェネレータでは `$enablePjax`
というオプションがウェブ・インタフェイスまたはコマンドラインで使用可能になっています。

```php
yii gii/crud --controllerClass="backend\\controllers\PostController" \
  --modelClass="common\\models\\Post" \
  --enablePjax=1
```

これによって、[[yii\grid\GridView|GridView]] または [[yii\widgets\ListView|ListView]]
を囲む [[yii\widgets\Pjax|Pjax]] ウィジェットが生成されます。

さらに読むべき文書
------------------

- Arno Slatius による [Rendering Data in Yii 2 with GridView and ListView](https://www.sitepoint.com/rendering-data-in-yii-2-with-gridview-and-listview/)
