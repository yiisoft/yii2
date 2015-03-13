分页
==========

当一次要在一个页面上显示很多数据时，通过需要将其分为
几部分，每个部分都包含一些数据列表并且一次只显示一部分。这些部分被称为
分页，例如 pagination。
  
如果你使用 [data provider](output-data-providers.md) 和 [data widgets](output-data-widgets.md) 中之一
pagination 已经为你自动排序。否则，你需要创建 [[\yii\data\Pagination]]
对象，为其填充数据例如 [[\yii\data\Pagination::$totalCount|total item count]]，
[[\yii\data\Pagination::$pageSize|page size]] 和 [[\yii\data\Pagination::$page|current page]]，apply
it to the query and then feed it to [[\yii\widgets\LinkPager|link pager]].


首先在 controller action，我们创建 pagination 对象并且为其填充数据：

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

其次在视图中我们输出的模板为当前页并通过 pagination 对象链接到该页：

```php
foreach ($models as $model) {
    // display $model here
}

// display pagination
echo LinkPager::widget([
    'pagination' => $pages,
]);
```
