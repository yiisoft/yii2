データ・プロバイダ
================

[ページネーション](output-pagination.md) と [並べ替え](output-sorting.md) のセクションにおいて
、エンド・ユーザが特定のページのデータを選んで表示し、いずれかのカラムによってデータを並べ替えることが出来るようにする方法を説明しました。
データのページネーションと並べ替えは非常によくあるタスクですから、Yii はこれをカプセル化した一連の *データ・プロバイダ* を提供しています。

データ・プロバイダは [[yii\data\DataProviderInterface]] を実装するクラスであり、主として、ページ分割され並べ替えられたデータの取得をサポートするものです。
通常は、[データ・ウィジェット](output-data-widgets.md) と共に使用して、
エンド・ユーザが対話的にデータのページネーションと並べ替えをすることが出来るようにします。

Yii のリリースには次のデータ・プロバイダのクラスが含まれています。

* [[yii\data\ActiveDataProvider]]: [[yii\db\Query]] または [[yii\db\ActiveQuery]] を使ってデータベースからデータを取得して、
  配列または [アクティブ・レコード](db-active-record.md)・インスタンスの形式でデータを返します。
* [[yii\data\SqlDataProvider]]: SQL 文を実行して、データベースのデータを配列として返します。
* [[yii\data\ArrayDataProvider]]: 大きな配列を受け取り、ページネーションと並べ替えの指定に基づいて、
  一部分を切り出して返します。

これら全てのデータ・プロバイダの使用方法は、次の共通のパターンを持っています。

```php
// ページネーションと並べ替えのプロパティを構成してデータ・プロバイダを作成する
$provider = new XyzDataProvider([
    'pagination' => [...],
    'sort' => [...],
]);

// ページ分割されて並べ替えられたデータを取得する
$models = $provider->getModels();

// 現在のページにあるデータ・アイテムの数を取得する
$count = $provider->getCount();

// 全ページ分のデータ・アイテムの総数を取得する
$totalCount = $provider->getTotalCount();
```

データ・プロバイダのページネーションと並べ替えの振る舞いを指定するためには、その [[yii\data\BaseDataProvider::pagination|pagination]]
と [[yii\data\BaseDataProvider::sort|sort]] のプロパティを構成します。
二つのプロパティは、それぞれ、[[yii\data\Pagination]] と [[yii\data\Sort]] の構成情報に対応します。
これらを `false` に設定して、ページネーションや並べ替えの機能を無効にすることも出来ます。

[データ・ウィジェット](output-data-widgets.md)、例えば [[yii\grid\GridView]] は、`dataProvider` という名前のプロパティを持っており、
これにデータ・プロバイダのインスタンスを受け取らせて、それが提供するデータを表示させることが出来ます。例えば、

```php
echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

これらのデータ・プロバイダの主たる相異点は、データソースがどのように指定されるかという点にあります。
次に続く項において、各データ・プロバイダの詳細な使用方法を説明します。


## アクティブ・データ・プロバイダ <span id="active-data-provider"></span> 

[[yii\data\ActiveDataProvider]] を使用するためには、その [[yii\data\ActiveDataProvider::query|query]] プロパティを構成しなければなりません。
これは、[[yii\db\Query]] または [[yii\db\ActiveQuery]] のオブジェクトを取ることが出来ます。
前者であれば、返されるデータは配列になります。後者であれば、返されるデータは配列または [アクティブ・レコード](db-active-record.md)
インスタンスとすることが出来ます。例えば、

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'defaultOrder' => [
            'created_at' => SORT_DESC,
            'title' => SORT_ASC, 
        ]
    ],
]);

// Post オブジェクトの配列を返す
$posts = $provider->getModels();
```

上記の例における `$query` が次のコードによって作成される場合は、提供されるデータは生の配列になります。

```php
use yii\db\Query;

$query = (new Query())->from('post')->where(['status' => 1]); 
```

> Note: クエリが既に `orderBy` 句を指定しているものである場合、(`sort` の構成を通して) エンド・ユーザによって与えられる並べ替えの指定は、
  既存の `orderBy` 句に追加されます。一方、`limit` と `offset` の句が存在している場合は、
  (`pagenation` の構成を通して) エンド・ユーザによって指定されるページネーションのリクエストによって上書きされます。

デフォルトでは、[[yii\data\ActiveDataProvider]] はデータベース接続として `db` アプリケーション・コンポーネントを使用します。
[[yii\data\ActiveDataProvider::db]] プロパティを構成すれば、別のデータベース接続を使用することが出来ます。


## SQL データ・プロバイダ <span id="sql-data-provider"></span>

[[yii\data\SqlDataProvider]] は、生の SQL 文を使用して、必要なデータを取得します。
このデータ・プロバイダは、[[yii\data\SqlDataProvider::sort|sort]] と [[yii\data\SqlDataProvider::pagination|pagination]]
の指定に基づいて、SQL 文の `ORDER BY` と `OFFSET/LIMIT` の句を修正し、
指定された順序に並べ替えられたデータを要求されたページの分だけ取得します。

[[yii\data\SqlDataProvider]] を使用するためには、[[yii\data\SqlDataProvider::sql|sql]] プロパティだけでなく、
[[yii\data\SqlDataProvider::totalCount|totalCount]] プロパティを指定しなければなりません。例えば、

```php
use yii\data\SqlDataProvider;

$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM post WHERE status=:status
', [':status' => 1])->queryScalar();

$provider = new SqlDataProvider([
    'sql' => 'SELECT * FROM post WHERE status=:status',
    'params' => [':status' => 1],
    'totalCount' => $count,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => [
            'title',
            'view_count',
            'created_at',
        ],
    ],
]);

// データ行の配列を返す
$models = $provider->getModels();
```

> Info: [[yii\data\SqlDataProvider::totalCount|totalCount]] プロパティは、データにページネーションを適用しなければならない
  場合にだけ要求されます。これは、[[yii\data\SqlDataProvider::sql|sql]] によって指定される SQL 文は、
  現在要求されているページのデータだけを返すように、データ・プロバイダによって修正されてしまうからです。
  データ・プロバイダは、総ページ数を正しく計算するためには、データ・アイテムの総数を知る必要があります。


## 配列データ・プロバイダ <span id="array-data-provider"></span>

[[yii\data\ArrayDataProvider]] は、一つの大きな配列を扱う場合に最も適しています。
このデータ・プロバイダによって、一つまたは複数のカラムで並べ替えた配列データの 1 ページ分を返すことが出来ます。
[[yii\data\ArrayDataProvider]] を使用するためには、全体の大きな配列として
[[yii\data\ArrayDataProvider::allModels|allModels]] プロパティを指定しなければなりません。
この大きな配列の要素は、連想配列 (例えば [DAO](db-dao.md) のクエリ結果) またはオブジェクト
(例えば [アクティブ・レコード](db-active-record.md) インスタンス) とすることが出来ます。例えば、

```php
use yii\data\ArrayDataProvider;

$data = [
    ['id' => 1, 'name' => 'name 1', ...],
    ['id' => 2, 'name' => 'name 2', ...],
    ...
    ['id' => 100, 'name' => 'name 100', ...],
];

$provider = new ArrayDataProvider([
    'allModels' => $data,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => ['id', 'name'],
    ],
]);

// 現在リクエストされているページの行を返す
$rows = $provider->getModels();
``` 

> Note: [アクティブ・データ・プロバイダ](#active-data-provider) および [SQL データ・プロバイダ](#sql-data-provider) と比較すると、
  配列データ・プロバイダは効率の面では劣ります。何故なら、*全ての* データをメモリにロードしなければならないからです。


## データのキーを扱う <span id="working-with-keys"></span>

データ・プロバイダによって返されたデータ・アイテムを使用する場合、各データ・アイテムを一意のキーで
特定しなければならないことがよくあります。例えば、データ・アイテムが顧客情報を表す場合、顧客 ID
を各顧客データのキーとして使用したいでしょう。データ・プロバイダは、[[yii\data\DataProviderInterface::getModels()]]
によって返されたデータ・アイテムに対応するそのようなキーのリストを返すことが出来ます。例えば、

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
]);

// Post オブジェクトの配列を返す
$posts = $provider->getModels();

// $post に対応するプライマリ・キーの値を返す
$ids = $provider->getKeys();
```

上記の例では、[[yii\data\ActiveDataProvider]] に対して [[yii\db\ActiveQuery]] オブジェクトを供給していますから、
キーとしてプライマリ・キーの値を返すのが理にかなっています。キーの値の計算方法を明示的に指定するために、
[[yii\data\ActiveDataProvider::key]] にカラム名を設定したり、キーの値を計算するコーラブルを設定したりすることも出来ます。
例えば、

```php
// "slug" カラムをキーの値として使用する
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => 'slug',
]);

// md5(id) の結果をキーの値として使用する
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => function ($model) {
        return md5($model->id);
    }
]);
```


## カスタム・データ・プロバイダを作成する <span id="custom-data-provider"></span>

あなた自身のカスタム・データ・プロバイダ・クラスを作成するためには、[[yii\data\DataProviderInterface]] を実装しなければなりません。
[[yii\data\BaseDataProvider]] を拡張するのが比較的簡単な方法です。そうすれば、データ・プロバイダのコアのロジックに集中することが出来ます。
具体的に言えば、実装する必要があるのは、主として次のメソッドです。
                                                   
- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]: 現在のページで利用できるデータ・モデルを準備して、
  それを配列として返します。
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]: 現在利用できるデータ・モデルの配列を受け取って、
  それと関連付けられるキーの配列を返します。
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]: データ・プロバイダにある
  データ・モデルの総数を示す値を返します。

下記は、CSV ファイルを効率的に読み出すデータ・プロバイダのサンプルです。

```php
<?php
use yii\data\BaseDataProvider;

class CsvDataProvider extends BaseDataProvider
{
    /**
     * @var string 読み出す CSV ファイルの名前
     */
    public $filename;
    
    /**
     * @var string|callable キーカラムの名前またはそれを返すコーラブル
     */
    public $key;
    
    /**
     * @var SplFileObject
     */
    protected $fileObject; // ファイルの特定の行までシークするのに SplFileObject が非常に便利
    
 
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // ファイルを開く
        $this->fileObject = new SplFileObject($this->filename);
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $models = [];
        $pagination = $this->getPagination();
 
        if ($pagination === false) {
            // ページネーションが無い場合、全ての行を読む
            while (!$this->fileObject->eof()) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        } else {
            // ページネーションがある場合、一つのページだけを読む
            $pagination->totalCount = $this->getTotalCount();
            $this->fileObject->seek($pagination->getOffset());
            $limit = $pagination->getLimit();
 
            for ($count = 0; $count < $limit; ++$count) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        }
 
        return $models;
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
 
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
 
            return $keys;
        } else {
            return array_keys($models);
        }
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        $count = 0;
 
        while (!$this->fileObject->eof()) {
            $this->fileObject->next();
            ++$count;
        }
 
        return $count;
    }
}
```

## データ・フィルタを使ってデータ・プロバイダをフィルタリングする <span id="filtering-data-providers-using-data-filters"></span>

[データをフィルタリングする](output-data-widgets.md#filtering-data) や [独立したフィルタ・フォーム](output-data-widgets.md#separate-filter-form) で述べられているように、
アクティブ・データ・プロバイダの条件を手作業で構築することも可能ですが、
柔軟なフィルタ条件を必要とする場合には、Yii が持っているデータ・フィルタが非常に役に立ちます。
データ・フィルタは次のようにして使うことが出来ます。

```php
$filter = new ActiveDataFilter([
    'searchModel' => 'app\models\PostSearch'
]);

$filterCondition = null;

// どのようなソースからでもフィルタをロードすることが出来ます。例えば、
// リクエスト・ボディの JSON からロードしたい場合は、
// 下記のように Yii::$app->request->getBodyParams() を使います。
if ($filter->load(\Yii::$app->request->get())) { 
    $filterCondition = $filter->build();
    if ($filterCondition === false) {
        // シリアライザがフィルタの抽出でエラーを出すかもしれない
        return $filter;
    }
}

$query = Post::find();
if ($filterCondition !== null) {
    $query->andWhere($filterCondition);
}

return new ActiveDataProvider([
    'query' => $query,
]);
```

`PostSearch` モデルは、どういうプロパティと値がフィルタリングに使用できるかを定義するという目的のために使用されています。

```php
use yii\base\Model;

class PostSearch extends Model 
{
    public $id;
    public $title;
    
    public function rules()
    {
        return [
            ['id', 'integer'],
            ['title', 'string', 'min' => 2, 'max' => 200],            
        ];
    }
}
```

データ・フィルタは極めて柔軟で、どのようにして条件が構築されるか、また、どんな演算子が許容されるかをカスタマイズすることが可能です。
詳細は API リファレンスで [[\yii\data\DataFilter]] を参照して下さい。
