Sorting
=======

Sometimes the data that is to be displayed should be sorted according to one or several attributes. If you are using
a [data provider](output-data-providers.md) with one of the [data widgets](output-data-widgets.md), sorting is
handled for you automatically. If not, you should create a [[yii\data\Sort]] instance, configure it and
apply it to the query. It can also be passed to the view, where it can be used to create links to sort by certain attributes.

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

The [[yii\data\Sort|Sort]] class will obtain the parameters passed with the request automatically
and adjust the sort options accordingly.
You can adjust the parameters by configuring the [[yii\data\Sort::$params|$params]] property.
