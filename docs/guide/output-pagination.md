Pagination
==========

When there's too much data to be displayed on a single page at once it's often divided into
parts each containing some data items and displayed one part at a time. Such parts are called
pages thus the name pagination.
  
If you're using [data provider](output-data-providers.md) with one of the [data widgets](output-data-widgets.md)
pagination is already sorted out for you automatically. If not, you need to create [[\yii\data\Pagination]]
object, fill it with data such as [[\yii\data\Pagination::$totalCount|total item count]],
[[\yii\data\Pagination::$pageSize|page size]] and [[\yii\data\Pagination::$page|current page]], apply
it to the query and then feed it to [[\yii\widgets\LinkPager|link pager]].


First of all in controller action we're creating pagination object and filling it with data:

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

Then in a view we're outputting models for the current page and passing pagination object to the link pager:

```php
foreach ($models as $model) {
    // display $model here
}

// display pagination
echo LinkPager::widget([
    'pagination' => $pages,
]);
```
