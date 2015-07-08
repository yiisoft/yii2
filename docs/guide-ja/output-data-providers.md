データプロバイダ
================

[ページネーション](output-pagination.md) と [並べ替え](output-sorting.md) の節において、エンドユーザが特定のページのデータを選んで表示し、いずれかのカラムによってデータを並べ替えることが出来るようにする方法を説明しました。
データのページネーションと並べ替えは非常によくあるタスクですから、Yii はこれをカプセル化した一連の *データプロバイダ* を提供しています。

データプロバイダは [[yii\data\DataProviderInterface]] を実装するクラスであり、主として、ページ分割され並べ替えられたデータの取得をサポートするものです。
通常は、[データウィジェット](output-data-widgets.md) と共に使用して、エンドユーザが対話的にデータのページネーションと並べ替えをすることが出来るようにします。

Yii のリリースには次のデータプロバイダのクラスが含まれています。

* [[yii\data\ActiveDataProvider]]: [[yii\db\Query]] または [[yii\db\ActiveQuery]] を使ってデータベースからデータを取得して、配列または [アクティブレコード](db-active-record.md) インスタンスの形式でデータを返します。
* [[yii\data\SqlDataProvider]]: SQL 文を実行して、データベースのデータを配列として返します。
* [[yii\data\ArrayDataProvider]]: 大きな配列を受け取り、ページネーションと並べ替えの指定に基づいて、一部分を切り出して返します。

これら全てのデータプロバイダの使用方法は、次の共通のパターンを持っています。

```php
// ページネーションと並べ替えのプロパティを構成してデータプロバイダを作成する
$provider = new XyzDataProvider([
    'pagination' => [...],
    'sort' => [...],
]);

// ページ分割されて並べ替えられたデータを取得する
$models = $provider->getModels();

// 現在のページにあるデータアイテムの数を取得する
$count = $provider->getCount();

// 全ページ分のデータアイテムの総数を取得する
$totalCount = $provider->getTotalCount();
```

データプロバイダのページネーションと並べ替えの振る舞いを指定するためには、その [[yii\data\BaseDataProvider::pagination|pagination]] と [[yii\data\BaseDataProvider::sort|sort]] のプロパティを構成します。
二つのプロパティは、それぞれ、[[yii\data\Pagination]] と [[yii\data\Sort]] の構成情報に対応します。
これらを false に設定して、ページネーションや並べ替えの機能を無効にすることも出来ます。

[データウィジェット](output-data-widgets.md)、例えば [[yii\grid\GridView]] は、`dataProvider` という名前のプロパティを持っており、これにデータプロバイダのインスタンスを受け取らせて、それが提供するデータを表示させることが出来ます。
例えば、

```php
echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

これらのデータプロバイダの主たる相異点は、データソースがどのように指定されるかという点にあります。
次に続く項において、各データプロバイダの詳細な使用方法を説明します。


## アクティブデータプロバイダ <span id="active-data-provider"></span> 

[[yii\data\ActiveDataProvider]] を使用するためには、その [[yii\data\ActiveDataProvider::query|query]] プロパティを構成しなければなりません。
これは、[[yii\db\Query]] または [[yii\db\ActiveQuery]] のオブジェクトを取ることが出来ます。
前者であれば、返されるデータは配列になります。
後者であれば、返されるデータは配列または [アクティブレコード](db-active-record.md) インスタンスとすることが出来ます。
例えば、

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

> Note|注意: クエリが既に `orderBy` 句を指定しているものである場合、(`sort` の構成を通して) エンドユーザによって与えられる並べ替えの指定は、既存の `orderBy` 句に追加されます。
一方、`limit` と `offset` の句が存在している場合は、(`pagenation` の構成を通して) エンドユーザによって指定されるページネーションのリクエストによって上書きされます。

デフォルトでは、[[yii\data\ActiveDataProvider]] はデータベース接続として `db` アプリケーションコンポーネントを使用します。
[[yii\data\ActiveDataProvider::db]] プロパティを構成すれば、別のデータベース接続を使用することが出来ます。


## SQL データプロバイダ <span id="sql-data-provider"></span>

[[yii\data\SqlDataProvider]] は、生の SQL 文を使用して、必要なデータを取得します。
このデータプロバイダは、[[yii\data\SqlDataProvider::sort|sort]] と [[yii\data\SqlDataProvider::pagination|pagination]] の指定に基づいて、SQL 文の `ORDER BY` と `OFFSET/LIMIT` の句を修正し、指定された順序に並べ替えられたデータを要求されたページの分だけ取得します。

[[yii\data\SqlDataProvider]] を使用するためには、[[yii\data\SqlDataProvider::sql|sql]] プロパティだけでなく、[[yii\data\SqlDataProvider::totalCount|totalCount]] プロパティを指定しなければなりません。
例えば、

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

> Info|情報: [[yii\data\SqlDataProvider::totalCount|totalCount]] プロパティは、データにページネーションを適用しなければならない場合にだけ要求されます。
  これは、[[yii\data\SqlDataProvider::sql|sql]] によって指定される SQL 文は、現在要求されているページのデータだけを返すように、データプロバイダによって修正されてしまうからです。
  データプロバイダは、総ページ数を正しく計算するためには、データアイテムの総数を知る必要があります。


## 配列データプロバイダ <span id="array-data-provider"></span>

[[yii\data\ArrayDataProvider]] は、一つの大きな配列を扱う場合に最も適しています。
このデータプロバイダによって、一つまたは複数のカラムで並べ替えた配列データの 1 ページ分を返すことが出来ます。
[[yii\data\ArrayDataProvider]] を使用するためには、全体の大きな配列として [[yii\data\ArrayDataProvider::allModels|allModels]] プロパティを指定しなければなりません。
この大きな配列の要素は、連想配列 (例えば [DAO](db-dao.md) のクエリ結果) またはオブジェクト (例えば [アクティブレコード](db-active-record.md) インスタンス) とすることが出来ます。
例えば、

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

> Note|注意: [アクティブデータプロバイダ](#active-data-provider) および [SQL データプロバイダ](#sql-data-provider) と比較すると、配列データプロバイダは効率の面では劣ります。
  何故なら、*全ての* データをメモリにロードしなければならないからです。


## データのキーを扱う <span id="working-with-keys"></span>

データプロバイダによって返されたデータアイテムを使用する場合、各データアイテムを一意のキーで特定しなければならないことがよくあります。
例えば、データアイテムが顧客情報を表す場合、顧客 ID を各顧客データのキーとして使用したいでしょう。
データプロバイダは、[[yii\data\DataProviderInterface::getModels()]] によって返されたデータアイテムに対応するそのようなキーのリストを返すことが出来ます。
例えば、

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => Post::find(),
]);

// Post オブジェクトの配列を返す
$posts = $provider->getModels();

// $post に対応するプライマリキーの値を返す
$ids = $provider->getKeys();
```

上記の例では、[[yii\data\ActiveDataProvider]] に対して [[yii\db\ActiveQuery]] オブジェクトを供給していますから、キーとしてプライマリキーの値を返すのが理にかなっています。
キーの値の計算方法を明示的に指定するために、[[yii\data\ActiveDataProvider::key]] にカラム名を設定したり、キーの値を計算するコーラブルを設定したりすることも出来ます。
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


## カスタムデータプロバイダを作成する <span id="custom-data-provider"></span>

あなた自身のカスタムデータプロバイダクラスを作成するためには、[[yii\data\DataProviderInterface]] を実装しなければなりません。
[[yii\data\BaseDataProvider]] を拡張するのが比較的簡単な方法です。
そうすれば、データプロバイダのコアのロジックに集中することが出来ます。
具体的に言えば、実装する必要があるのは、主として次のメソッドです。
                                                   
- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]: 現在のページで利用できるデータモデルを準備して、それを配列として返します。
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]: 現在利用できるデータモデルの配列を受け取って、それと関連付けられるキーの配列を返します。
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]: データプロバイダにあるデータモデルの総数を示す値を返します。

下記は、CSV ファイルを効率的に読み出すデータプロバイダのサンプルです。

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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        // ファイルを開く
        $this->fileObject = new SplFileObject($this->filename);
    }
 
    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
