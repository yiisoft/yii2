Sorting
=======

<<<<<<< HEAD
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
=======
When displaying multiple rows of data, it is often needed that the data be sorted according to some columns
specified by end users. Yii uses a [[yii\data\Sort]] object to represent the information about a sorting schema.
In particular, 

* [[yii\data\Sort::$attributes|attributes]] specifies the *attributes* by which the data can be sorted.
  An attribute can be as simple as a [model attribute](structure-models.md#attributes). It can also be a composite
  one by combining multiple model attributes or DB columns. More details will be given in the following.
* [[yii\data\Sort::$attributeOrders|attributeOrders]] gives the currently requested ordering directions for 
  each attribute.
* [[yii\data\Sort::$orders|orders]] gives the ordering directions in terms of the low-level columns.

To use [[yii\data\Sort]], first declare which attributes can be sorted. Then retrieve the currently requested
ordering information from [[yii\data\Sort::$attributeOrders|attributeOrders]] or [[yii\data\Sort::$orders|orders]]
and use them to customize the data query. For example,

```php
use yii\data\Sort;

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

$articles = Article::find()
    ->where(['status' => 1])
    ->orderBy($sort->orders)
    ->all();
```

In the above example, two attributes are declared for the [[yii\data\Sort|Sort]] object: `age` and `name`. 

The `age` attribute is a *simple* attribute corresponding to the `age` attribute of the `Article` Active Record class.
It is equivalent to the following declaration:

```php
'age' => [
    'asc' => ['age' => SORT_ASC],
    'desc' => ['age' => SORT_DESC],
    'default' => SORT_ASC,
    'label' => Inflector::camel2words('age'),
]
```

The `name` attribute is a *composite* attribute defined by `first_name` and `last_name` of `Article`. It is declared
using the following array structure:

- The `asc` and `desc` elements specify how to sort by the attribute in ascending and descending directions, respectively.
  Their values represent the actual columns and the directions by which the data should be sorted by. You can specify
  one or multiple columns to indicate simple ordering or composite ordering.
- The `default` element specifies the direction by which the attribute should be sorted when initially requested. 
  It defaults to ascending order, meaning if it is not sorted before and you request to sort by this attribute, 
  the data will be sorted by this attribute in ascending order.
- The `label` element specifies what label should be used when calling [[yii\data\Sort::link()]] to create a sort link.
  If not set, [[yii\helpers\Inflector::camel2words()]] will be called to generate a label from the attribute name.
  Note that it will not be HTML-encoded.

> Info: You can directly feed the value of [[yii\data\Sort::$orders|orders]] to the database query to build
  its `ORDER BY` clause. Do not use [[yii\data\Sort::$attributeOrders|attributeOrders]] because some of the
  attributes may be composite and cannot be recognized by the database query.

You can call [[yii\data\Sort::link()]] to generate a hyperlink upon which end users can click to request sorting
the data by the specified attribute. You may also call [[yii\data\Sort::createUrl()]] to create a sortable URL.
For example,

```php
// specifies the route that the URL to be created should use
// If you do not specify this, the currently requested route will be used
$sort->route = 'article/index';

// display links leading to sort by name and age, respectively
echo $sort->link('name') . ' | ' . $sort->link('age');

// displays: /index.php?r=article/index&sort=age
echo $sort->createUrl('age');
```

[[yii\data\Sort]] checks the `sort` query parameter to determine which attributes are being requested for sorting.
You may specify a default ordering via [[yii\data\Sort::defaultOrder]] when the query parameter is not present.
You may also customize the name of the query parameter by configuring the [[yii\data\Sort::sortParam|sortParam]] property.
>>>>>>> yiichina/master
