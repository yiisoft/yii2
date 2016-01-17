分页
==========

当一次要在一个页面上显示很多数据时，通过需要将其分为
几部分，每个部分都包含一些数据列表并且一次只显示一部分。这些部分在网页上被称为
分页。
  
如果你使用 [数据提供者](output-data-providers.md) 和 [数据小部件](output-data-widgets.md) 中之一，
分页已经自动为你整理。否则，你需要创建 [[\yii\data\Pagination]]
对象为其填充数据，例如 [[\yii\data\Pagination::$totalCount|总记录数]]，
[[\yii\data\Pagination::$pageSize|每页数量]] 和 [[\yii\data\Pagination::$page|当前页码]]，在
查询中使用它并且填充到 [[\yii\widgets\LinkPager|链接分页]]。


首先在控制器的动作中，我们创建分页对象并且为其填充数据：

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

其次在视图中我们输出的模板为当前页并通过分页对象链接到该页：

```php
foreach ($models as $model) {
    // 在这里显示 $model
}

// 显示分页
echo LinkPager::widget([
    'pagination' => $pages,
]);
```
