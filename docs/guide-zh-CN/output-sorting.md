排序
=======

有时显示数据会根据一个或多个属性进行排序。如果你正在使用
[数据提供者](output-data-providers.md) 和 [数据小部件](output-data-widgets.md) 中之一，排序
可以为你自动处理。否则，你应该创建一个 [[yii\data\Sort]] 实例，配置好后
将其应用到查询中。也可以传递给视图，可以在视图中通过某些属性创建链接来排序。

如下是一个典型的使用范例，

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

在视图中：

```php
// 显示指向排序动作的链接
echo $sort->link('name') . ' | ' . $sort->link('age');

foreach ($models as $model) {
    // 在这里显示 $model
}
```

以上，我们声明了支持了两个属性的排序：`name` 和 `age`。
我们通过排序信息来查询以便于查询结果通过 Sort 对象
排序后更加准确有序。在视图中，我们通过相应的属性
展示了链接到页的两个超链接和数据排序。

[[yii\data\Sort|Sort]] 类将获得自动传递的请求参数
并相应地调整排序选项。
你可以通过配置 [[yii\data\Sort::$params|$params]] 属性来调整参数。
