データプロバイダ
================

> Note|注意: この節はまだ執筆中です。

データプロバイダは、 [[yii\data\DataProviderInterface]] によってデータセットを抽象化し、ページネーションと並べ替えを処理します。
[グリッドやリストなどのデータウィジェット](output-data-widgets.md) で使用することが出来ます。

Yii は三つのデータプロバイダを内蔵しています。すなわち、[[yii\data\ActiveDataProvider]]、[[yii\data\ArrayDataProvider]] そして [[yii\data\SqlDataProvider]] です。

アクティブデータプロバイダ
--------------------------

`ActiveDataProvider` は [[yii\db\Query]] および [[yii\db\ActiveQuery]] を使って DB クエリを実行して、データを提供します。

次のコードは、これを使って、ActiveRecord のインスタンスを提供する例です。

```php
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);

// 現在のページの投稿を取得する
$posts = $provider->getModels();
```

そして次の例は、ActiveRecord なしで ActiveDataProvider を使う方法を示すものです。

```php
$query = new Query();
$provider = new ActiveDataProvider([
    'query' => $query->from('post'),
    'sort' => [
        // デフォルトのソートを name ASC, created_at DESC とする
        'defaultOrder' => [
            'name' => SORT_ASC, 
            'created_at' => SORT_DESC
        ]
    ],
    'pagination' => [
        'pageSize' => 20,
    ],
]);

// 現在のページの投稿を取得する
$posts = $provider->getModels();
```

配列データプロバイダ
--------------------

ArrayDataProvider はデータの配列に基づいたデータプロバイダを実装するものです。

[[yii\data\ArrayDataProvider::$allModels]] プロパティが、並べ替えやページネーションの対象となるデータの全てのモデルを含みます。
ArrayDataProvider は、並べ替えとページネーションを実行した後に、データを提供します。
[[yii\data\ArrayDataProvider::$sort]] および [[yii\data\ArrayDataProvider::$pagination]] のプロパティを構成して、並べ替えとページネーションの動作をカスタマイズすることが出来ます。

[[yii\data\ArrayDataProvider::$allModels]] 配列の要素は、オブジェクト (例えば、モデルのオブジェクト) であるか、連想配列 (例えば、DAO のクエリ結果) であるかの、どちらかです。
[[yii\data\ArrayDataProvider::$key]] プロパティには、必ず、データレコードを一意的に特定出来るフィールドの名前をセットするか、そのようなフィールドがない場合は `false` をセットするかしなければなりません。

`ActiveDataProvider` と比較すると、`ArrayDataProvider` は、[[yii\data\ArrayDataProvider::$allModels]] を準備して持たなければならないため、効率が良くありません。

`ArrayDataProvider` は次のようにして使用することが出来ます。

```php
$query = new Query();
$provider = new ArrayDataProvider([
    'allModels' => $query->from('post')->all(),
    'sort' => [
        'attributes' => ['id', 'username', 'email'],
    ],
    'pagination' => [
        'pageSize' => 10,
    ],
]);
// 現在のページの投稿を取得する
$posts = $provider->getModels();
```

> Note|注意: 並べ替えの機能を使いたいときは、どのカラムがソート出来るかをプロバイダが知ることが出来るように、[[sort]] プロパティを構成しなければなりません。

SQL データプロバイダ
--------------------

SqlDataProvider は、素の SQL 文に基づいたデータプロバイダを実装するものです。
これは、各要素がクエリ結果の行を表す配列の形式でデータを提供します。

他のプロバイダ同様に、SqlDataProvider も、並べ替えとページネーションをサポートしています。
並べ替えとページネーションは、与えられた [[yii\data\SqlDataProvider::$sql]] 文を "ORDER BY" 句および "LIMIT" 句で修正することによって実行されます。
[[yii\data\SqlDataProvider::$sort]] および [[yii\data\SqlDataProvider::$pagination]] のプロパティを構成して、並べ替えとページネーションの動作をカスタマイズすることが出来ます。

`SqlDataProvider` は次のようにして使用することが出来ます。

```php
$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM user WHERE status=:status
', [':status' => 1])->queryScalar();

$dataProvider = new SqlDataProvider([
    'sql' => 'SELECT * FROM user WHERE status=:status',
    'params' => [':status' => 1],
    'totalCount' => $count,
    'sort' => [
        'attributes' => [
            'age',
            'name' => [
                'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                'default' => SORT_DESC,
                'label' => 'Name',
            ],
        ],
    ],
    'pagination' => [
        'pageSize' => 20,
    ],
]);

// 現在のページの user のレコードを取得する
$models = $dataProvider->getModels();
```

> Note|注意: ページネーションの機能を使いたい場合は、[[yii\data\SqlDataProvider::$totalCount]] プロパティに (ページネーション無しの) 総行数を設定しなければなりません。
そして、並べ替えの機能を使いたい場合は、どのカラムがソート出来るかをプロバイダが知ることが出来るように、[[yii\data\SqlDataProvider::$sort]] プロパティを構成しなければなりません。


あなた自身のカスタムデータプロバイダを実装する
----------------------------------------------

(内容未定)
