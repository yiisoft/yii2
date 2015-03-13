分页
==========

当一次要在一个页面上显示很多数据时，通过需要将其分为
几部分，每个部分都包含一些数据列表并且一次只显示一部分。这些部分在网页上被称为
分页。
  
如果你使用 [数据提供者](output-data-providers.md) 和 [数据小部件](output-data-widgets.md) 中之一，
pagination 已经为你自动分页。否则，你需要创建 [[\yii\data\Pagination]]
对象，为其填充数据例如 [[\yii\data\Pagination::$totalCount|total item count]]，
[[\yii\data\Pagination::$pageSize|page size]] 和 [[\yii\data\Pagination::$page|current page]]，在
查询中使用它并且填充到 [[\yii\widgets\LinkPager|link pager]]。


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
