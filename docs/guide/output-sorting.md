Sorting
=======

Sometimes data to be displayed should be sorted according to one or several attributes. If you are using
[data provider](output-data-providers.md) with one of the [data widgets](output-data-widgets.md) it is
handled for you automatically. If not, you should create [[\yii\data\Sort]] instance in controller, configure it
apply it to the query and then pass it to the view where it can be used to create links to sort by attributes. 

A typical usage example is as follows,

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

In the view:

```php
// display links leading to sort actions
echo $sort->link('name') . ' | ' . $sort->link('age');

foreach ($models as $model) {
    // display $model here
}
```

In the above, we declare two attributes that support sorting: `name` and `age`.
We pass the sort information to the Article query so that the query results are
sorted by the orders specified by the Sort object. In the view, we show two hyperlinks
that can lead to pages with the data sorted by the corresponding attributes.