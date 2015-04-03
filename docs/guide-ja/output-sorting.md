並べ替え
========

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
