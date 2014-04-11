Data widgets
============

GridView
--------

The [[yii\grid\GridView]] widget is a powerful tool to create a data grid that provides pagination, sorting
and filtering of the data out of the box.
The GridView gets its data from a [data provider](data-providers.md) which is responsible for sorting and
pagination.
The following code shows a basic example of a gridview showing Users from the database(`User` is an [active record](active-record.md)):

In the controller action:

```php
$dataProvider = new ActiveDataProvider(['query' => User::find()]);

return $this->render('myview', ['dataProvider' => $dataProvider]);
```

In the view `myview.php`:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'name',
        'email',
        'last_login_date:datetime',
        [
            'class' => 'yii\grid\ActionColumn',
        ]
    ],
]); ?>
```

### Filtering

TDB

### Filtering by related columns

TDB

ListView
--------



DetailView
----------

DetailView displays the detail of a single data [[yii\widgets\DetailView::$model|model]].
 
It is best used for displaying a model in a regular format (e.g. each model attribute is displayed as a row in a table).
The model can be either an instance of [[\yii\base\Model]] or an associative array.
 
DetailView uses the [[yii\widgets\DetailView::$attributes]] property to determines which model attributes should be displayed and how they
should be formatted.
 
A typical usage of DetailView is as follows:
 
```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',             // title attribute (in plain text)
        'description:html',  // description attribute in HTML
        [                    // the owner name of the model
            'label' => 'Owner',
            'value' => $model->owner->name,
        ],
    ],
]);
```
