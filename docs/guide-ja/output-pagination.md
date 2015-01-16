ページネーション
================

一つのページに一度に表示するにはデータ数が多すぎる場合に、それぞれ一定数のデータアイテムを含む部分にデータを分割して、一度に一つの部分だけを表示することがよく行われます。
このような部分はページと呼ばれますが、それがページネーションという名前の由来です。

あなたが [データウィジェット](output-data-widgets.md) の一つとともに [データプロバイダ](output-data-providers.md) を使っている場合は、ページネーションは既に自動的に設定されて、うまく動作するようになっています。
そうでない場合は、あなたが [[\yii\data\Pagination]] オブジェクトを作成し、[[\yii\data\Pagination::$totalCount|総アイテム数]]、[[\yii\data\Pagination::$pageSize|ページサイズ]]、[[\yii\data\Pagination::$page|現在のページ]] などのデータを代入して、クエリに適用し、そして [[\yii\widgets\LinkPager|リンクページャ]] に与えなければなりません。

まず最初に、コントローラアクションの中でページネーションオブジェクトを作成し、データを代入します。

```php
function actionIndex()
{
    $query = Article::find()->where(['status' => 1]);
    $countQuery = clone $query;
    $pages = new Pagination(['totalCount' => $countQuery->count()]);
    $models = $query->offset($pages->offset)
        ->limit($pages->limit)
        ->all();

    return $this->render('index', [
         'models' => $models,
         'pages' => $pages,
    ]);
}
```

次に、ビューにおいて、現在のページのモデルを出力し、リンクページャにページネーションオブジェクトを渡します。

```php
foreach ($models as $model) {
    // ここで $model を表示
}

// ページネーションを表示
echo LinkPager::widget([
    'pagination' => $pages,
]);
```
