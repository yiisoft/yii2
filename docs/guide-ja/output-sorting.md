並べ替え
========

<<<<<<< HEAD
表示するデータを一つまたはいくつかの属性に従って並べ替えなければならないことがあります。
あなたが [データウィジェット](output-data-widgets.md) の一つとともに [データプロバイダ](output-data-providers.md) を使っている場合は、並べ替えはあなたに代って自動的に処理されます。
そうでない場合は、[[\yii\data\Sort]] のインスタンスを作成して構成し、クエリに適用しなければなりません。
また、[[\yii\data\Sort]] のインスタンスをビューに渡して、属性による並べ替えのためのリンクを作成することが出来ます。

典型的な使用方法の例を次に示します。

```php
function actionIndex()
{
    $sort = new Sort([
        'attributes' => [
            'age',
            'name' => [
                'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                'default' => SORT_DESC,
                'label' => 'Name',
            ],
        ],
    ]);

    $models = Article::find()
        ->where(['status' => 1])
        ->orderBy($sort->orders)
        ->all();

    return $this->render('index', [
         'models' => $models,
         'sort' => $sort,
    ]);
}
```

ビューにおいては、

```php
// 並べ替えのアクションに導くリンクを表示
echo $sort->link('name') . ' | ' . $sort->link('age');

foreach ($models as $model) {
    // ここで $model を表示
}
```

上記においては、並べ替えをサポートする二つの属性、すなわち、`name` と `age` を宣言しています。
並べ替えの情報を Article クエリに渡して、クエリ結果が Sort オブジェクトで指定された順序に従って並べ替えられるようにしています。
ビューにおいては、二つのハイパーリンクを表示して、対応する属性によって並べ替えられたデータを表示するページへ移動できるようにしています。

[[yii\data\Sort|Sort]] クラスは、リクエストで渡されたパラメータを自動的に取得して、それに応じて並べ替えのオプションを調整します。
パラメータは [[yii\data\Sort::$params|$params]] プロパティを構成して調整することが出来ます。
=======
複数のデータ行を表示する際に、エンドユーザによって指定されるカラムに従ってデータを並べ替えなければならないことがよくあります。
Yii は [[yii\data\Sort]] オブジェクトを使って並べ替えのスキーマに関する情報を表します。
具体的に言えば、

* [[yii\data\Sort::$attributes|attributes]] データの並べ替えに使用できる *属性* を指定します。
  単純で良ければ、[モデルの属性](structure-models.md#attributes) をこの属性とすることが出来ます。
  また、複数のモデル属性や DB のカラムを結合した合成的な属性を指定することも出来ます。
  詳細については後述します。
* [[yii\data\Sort::$attributeOrders|attributeOrders]] 各属性について、現在リクエストされている並べ替えの方向を指定します。
* [[yii\data\Sort::$orders|orders]] 並べ替えの方向をカラムを使う低レベルな形式で示します。

[[yii\data\Sort]] を使用するためには、最初にどの属性が並べ替え可能であるかを宣言します。
次に、現在リクエストされている並べ替え情報を [[yii\data\Sort::$attributeOrders|attributeOrders]] または [[yii\data\Sort::$orders|orders]] から取得して、データのクエリをカスタマイズします。
例えば、

```php
use yii\data\Sort;

$sort = new Sort([
    'attributes' => [
        'age',
        'name' => [
            'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
            'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
            'default' => SORT_DESC,
            'label' => '氏名',
        ],
    ],
]);

$articles = Article::find()
    ->where(['status' => 1])
    ->orderBy($sort->orders)
    ->all();
```

上記の例では、[[yii\data\Sort|Sort]] オブジェクトに対して二つの属性が宣言されています。
すなわち、`age` と `name` です。

`age` 属性は `Article` アクティブレコードクラスの `age` 属性に対応する *単純な* 属性です。
これは、次の宣言と等価です。

```php
'age' => [
    'asc' => ['age' => SORT_ASC],
    'desc' => ['age' => SORT_DESC],
    'default' => SORT_ASC,
    'label' => Inflector::camel2words('age'),
]
```

`name` 属性は `Article` の `first_name` と `last_name` によって定義される *合成的な* 属性です。
これは次のような配列構造を使って宣言されています。

- `asc` および `desc` の要素は、それぞれ、この属性を昇順および降順に並べ替える方法を指定します。
  この値が、データの並べ替えに使用されるべき実際のカラムと方向を表します。
  一つまたは複数のカラムを指定して、単純な並べ替えや合成的な並べ替えを示すことが出来ます。
- `default` 要素は、最初にリクエストされたときの属性の並べ替えに使用されるべき方向を指定します。
  デフォルト値は昇順です。
  つまり、以前に並べ替えられたことがない状態でこの属性による並べ替えをリクエストすると、この属性の昇順に従ってデータが並べ替えられることになります。
- `label` 要素は、並べ替えのリンクを作成するために [[yii\data\Sort::link()]] を呼んだときに、どういうラベルを使用すべきかを指定するものです。
  設定されていない場合は、[[yii\helpers\Inflector::camel2words()]] が呼ばれて、属性名からラベルが生成されます。
  ラベルは HTML エンコードされないことに注意してください。

> Info|情報: [[yii\data\Sort::$orders|orders]] の値をデータベースのクエリに直接に供給して、`ORDER BY` 句を構築することが出来ます。
  データベースのクエリが認識できない合成的な属性が入っている場合があるため、[[yii\data\Sort::$attributeOrders|attributeOrders]] を使ってはいけません。

[[yii\data\Sort::link()]] を呼んでハイパーリンクを生成すれば、それをクリックして、指定した属性によるデータの並べ替えをリクエストすることが出来るようになります。
[[yii\data\Sort::createUrl()]] を呼んで並べ替えを実行する URL を生成することも出来ます。
例えば、

```php
// 生成される URL が使用すべきルートを指定する
// これを指定しない場合は、現在リクエストされているルートが使用される
$sort->route = 'article/index';

// 氏名による並べ替えと年齢による並べ替えを実行するリンクを表示
echo $sort->link('name') . ' | ' . $sort->link('age');

// /index.php?r=article/index&sort=age を表示
echo $sort->createUrl('age');
```

[[yii\data\Sort]] は、リクエストの `sort` クエリパラメータをチェックして、どの属性による並べ替えがリクエストされたかを判断します。
このクエリパラメータが存在しない場合のデフォルトの並べ替え方法は [[yii\data\Sort::defaultOrder]] によって指定することが出来ます。
また、[[yii\data\Sort::sortParam|sortParam]] プロパティを構成して、このクエリパラメータの名前をカスタマイズすることも出来ます。
>>>>>>> yiichina/master
