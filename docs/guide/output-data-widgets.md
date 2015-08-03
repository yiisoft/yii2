Data widgets
============

Yii provides a set of [widgets](structure-widgets.md) that can be used to display data.
While the [DetailView](#detail-view) widget can be used to display data for a single record,
[ListView](#list-view) and [GridView](#grid-view) can be used to display a list or table of data records
providing features like pagination, sorting and filtering.


DetailView <a name="detail-view"></a>
----------

The [[yii\widgets\DetailView|DetailView]] widget displays the details of a single data [[yii\widgets\DetailView::$model|model]].

It is best used for displaying a model in a regular format (e.g. each model attribute is displayed as a row in a table).
The model can be either an instance or subclass of [[\yii\base\Model]] such as an [active record](db-active-record.md) or an associative array.

DetailView uses the [[yii\widgets\DetailView::$attributes|$attributes]] property to determine which model attributes should be displayed and how they
should be formatted. See the [formatter section](output-formatting.md) for available formatting options.

A typical usage of DetailView is as follows:
 
```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',               // title attribute (in plain text)
        'description:html',    // description attribute formatted as HTML
        [                      // the owner name of the model
            'label' => 'Owner',
            'value' => $model->owner->name,
        ],
        'created_at:datetime', // creation date formatted as datetime
    ],
]);
```

ListView <a name="list-view"></a>
--------

The [[yii\widgets\ListView|ListView]] widget is used to display data from a [data provider](output-data-providers.md).
Each data model is rendered using the specified [[yii\widgets\ListView::$itemView|view file]].
Since it provides features such as pagination, sorting and filtering out of the box, it is handy both to display
information to end user and to create data managing UI.

A typical usage is as follows:

```php
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
]);
```

The `_post` view file could contain the following:


```php
<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>
<div class="post">
    <h2><?= Html::encode($model->title) ?></h2>
    
    <?= HtmlPurifier::process($model->text) ?>    
</div>
```

In the view file above, the current data model is available as `$model`. Additionally the following variables are available:

- `$key`: mixed, the key value associated with the data item.
- `$index`: integer, the zero-based index of the data item in the items array returned by the data provider.
- `$widget`: ListView, this widget instance.

If you need to pass additional data to each view, you can use the [[yii\widgets\ListView::$viewParams|$viewParams]] property
to pass key value pairs like the following:

```php
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
    'viewParams' => [
        'fullView' => true,
        'context' => 'main-page',
        // ...
    ],
]);
```

These are then also available as variables in the view.


GridView <a name="grid-view"></a>
--------

Data grid or GridView is one of the most powerful Yii widgets. It is extremely useful if you need to quickly build the admin
section of the system. It takes data from a [data provider](output-data-providers.md) and renders each row using a set of [[yii\grid\GridView::columns|columns]]
presenting data in the form of a table. 

Each row of the table represents the data of a single data item, and a column usually represents an attribute of
the item (some columns may correspond to complex expressions of attributes or static text).

The minimal code needed to use GridView is as follows:

```php
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

The above code first creates a data provider and then uses GridView to display every attribute in every row taken from
the data provider. The displayed table is equipped with sorting and pagination functionality out of the box.


### Grid columns

The columns of the grid table are configured in terms of [[yii\grid\Column]] classes, which are
configured in the [[yii\grid\GridView::columns|columns]] property of GridView configuration.
Depending on column type and settings these are able to present data differently.
The default class is [[yii\grid\DataColumn]], which represents a model attribute and can be sorted and filtered by.

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        // Simple columns defined by the data contained in $dataProvider.
        // Data from the model's column will be used.
        'id',
        'username',
        // More complex one.
        [
            'class' => 'yii\grid\DataColumn', // can be omitted, as it is the default
            'value' => function ($data) {
                return $data->name; // $data['name'] for array data, e.g. using SqlDataProvider.
            },
        ],
    ],
]);
```

Note that if the [[yii\grid\GridView::columns|columns]] part of the configuration isn't specified,
Yii tries to show all possible columns of the data provider's model.


### Column classes

Grid columns could be customized by using different column classes:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- here
            // you may configure additional properties here
        ],
```

In addition to column classes provided by Yii that we'll review below, you can create your own column classes.

Each column class extends from [[yii\grid\Column]] so that there are some common options you can set while configuring
grid columns.

- [[yii\grid\Column::header|header]] allows to set content for header row.
- [[yii\grid\Column::footer|footer]] allows to set content for footer row.
- [[yii\grid\Column::visible|visible]] defines if the column should be visible.
- [[yii\grid\Column::content|content]] allows you to pass a valid PHP callback that will return data for a row. The format is the following:

  ```php
  function ($model, $key, $index, $column) {
      return 'a string';
  }
  ```

You may specify various container HTML options by passing arrays to:

- [[yii\grid\Column::headerOptions|headerOptions]]
- [[yii\grid\Column::footerOptions|footerOptions]]
- [[yii\grid\Column::filterOptions|filterOptions]]
- [[yii\grid\Column::contentOptions|contentOptions]]


#### Data column <span id="data-column"></span>

[[yii\grid\DataColumn|Data column]] is used for displaying and sorting data. It is the default column type so the specifying class could be omitted when
using it.

The main setting of the data column is its [[yii\grid\DataColumn::format|format]] property. Its values
correspond to methods in the `formatter` [application component](structure-application-components.md) that is [[\yii\i18n\Formatter|Formatter]] by default:

```php
echo GridView::widget([
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'text'
        ],
        [
            'attribute' => 'birthday',
            'format' => ['date', 'php:Y-m-d']
        ],
    ],
]); 
```

In the above, `text` corresponds to [[\yii\i18n\Formatter::asText()]]. The value of the column is passed as the first
argument. In the second column definition, `date` corresponds to [[\yii\i18n\Formatter::asDate()]]. The value of the
column is, again, passed as the first argument while 'php:Y-m-d' is used as the second argument value.

For a list of available formatters see the [section about Data Formatting](output-formatting.md).

For configuring data columns there is also a shortcut format which is described in the 
API documentation for [[yii\grid\GridView::columns|columns]].


#### Action column

[[yii\grid\ActionColumn|Action column]] displays action buttons such as update or delete for each row.

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\ActionColumn',
            // you may configure additional properties here
        ],
```

Available properties you can configure are:

- [[yii\grid\ActionColumn::controller|controller]] is the ID of the controller that should handle the actions. If not set, it will use the currently active
  controller.
- [[yii\grid\ActionColumn::template|template]] defines the template used for composing each cell in the action column. Tokens enclosed within curly brackets are
  treated as controller action IDs (also called *button names* in the context of action column). They will be replaced
  by the corresponding button rendering callbacks specified in [[yii\grid\ActionColumn::$buttons|buttons]]. For example, the token `{view}` will be
  replaced by the result of the callback `buttons['view']`. If a callback cannot be found, the token will be replaced
  with an empty string. The default tokens are `{view} {update} {delete}`.
- [[yii\grid\ActionColumn::buttons|buttons]] is an array of button rendering callbacks. The array keys are the button names (without curly brackets),
  and the values are the corresponding button rendering callbacks. The callbacks should use the following signature:

  ```php
  function ($url, $model, $key) {
      // return the button HTML code
  }
  ```

  In the code above, `$url` is the URL that the column creates for the button, `$model` is the model object being
  rendered for the current row, and `$key` is the key of the model in the data provider array.

- [[yii\grid\ActionColumn::urlCreator|urlCreator]] is a callback that creates a button URL using the specified model information. The signature of
  the callback should be the same as that of [[yii\grid\ActionColumn::createUrl()]]. If this property is not set,
  button URLs will be created using [[yii\grid\ActionColumn::createUrl()]].


#### Checkbox column

[[yii\grid\CheckboxColumn|Checkbox column]] displays a column of checkboxes.

To add a CheckboxColumn to the GridView, add it to the [[yii\grid\GridView::$columns|columns]] configuration as follows:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        // ...
        [
            'class' => 'yii\grid\CheckboxColumn',
            // you may configure additional properties here
        ],
    ],
```

Users may click on the checkboxes to select rows of the grid. The selected rows may be obtained by calling the following
JavaScript code:

```javascript
var keys = $('#grid').yiiGridView('getSelectedRows');
// keys is an array consisting of the keys associated with the selected rows
```

#### Serial column

[[yii\grid\SerialColumn|Serial column]] renders row numbers starting with `1` and going forward.

Usage is as simple as the following:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'], // <-- here
        // ...
```


### Sorting data

> Note: This section is under development.
>
> - https://github.com/yiisoft/yii2/issues/1576

### Filtering data

For filtering data the GridView needs a [model](structure-models.md) that takes the input from, the filtering
form and adjusts the query of the dataProvider to respect the search criteria.
A common practice when using [active records](db-active-record.md) is to create a search Model class
that provides needed functionality (it can be generated for you by [Gii](start-gii.md)). This class defines the validation 
rules for the search and provides a `search()` method that will return the data provider.

To add the search capability for the `Post` model, we can create `PostSearch` like the following example:

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{
    public function rules()
    {
        // only fields in rules() are searchable
        return [
            [['id'], 'integer'],
            [['title', 'creation_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Post::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // load the search form data and validate
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // adjust the query by adding the filters
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', $this->title])
              ->andFilterWhere(['like', 'creation_date', $this->creation_date]);

        return $dataProvider;
    }
}

```

You can use this function in the controller to get the dataProvider for the GridView:

```php
$searchModel = new PostSearch();
$dataProvider = $searchModel->search(Yii::$app->request->get());

return $this->render('myview', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);
```

And in the view you then assign the `$dataProvider` and `$searchModel` to the GridView:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        // ...
    ],
]);
```


### Working with model relations

When displaying active records in a GridView you might encounter the case where you display values of related
columns such as the post author's name instead of just his `id`.
You do this by defining the attribute name in [[yii\grid\GridView::$columns]] as `author.name` when the `Post` model
has a relation named `author` and the author model has an attribute `name`.
The GridView will then display the name of the author but sorting and filtering are not enabled by default.
You have to adjust the `PostSearch` model that has been introduced in the last section to add this functionality.

To enable sorting on a related column you have to join the related table and add the sorting rule
to the Sort component of the data provider:

```php
$query = Post::find();
$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);

// join with relation `author` that is a relation to the table `users`
// and set the table alias to be `author`
$query->joinWith(['author' => function($query) { $query->from(['author' => 'users']); }]);
// enable sorting for the related column
$dataProvider->sort->attributes['author.name'] = [
    'asc' => ['author.name' => SORT_ASC],
    'desc' => ['author.name' => SORT_DESC],
];

// ...
```

Filtering also needs the joinWith call as above. You also need to define the searchable column in attributes and rules like this:

```php
public function attributes()
{
    // add related fields to searchable attributes
    return array_merge(parent::attributes(), ['author.name']);
}

public function rules()
{
    return [
        [['id'], 'integer'],
        [['title', 'creation_date', 'author.name'], 'safe'],
    ];
}
```

In `search()` you then just add another filter condition with:

```php
$query->andFilterWhere(['LIKE', 'author.name', $this->getAttribute('author.name')]);
```

> Info: In the above we use the same string for the relation name and the table alias; however, when your alias and relation name
> differ, you have to pay attention to where you use the alias and where you use the relation name.
> A simple rule for this is to use the alias in every place that is used to build the database query and the
> relation name in all other definitions such as `attributes()` and `rules()` etc.
>
> For example, if you use the alias `au` for the author relation table, the joinWith statement looks like the following:
>
> ```php
> $query->joinWith(['author' => function($query) { $query->from(['au' => 'users']); }]);
> ```
> It is also possible to just call `$query->joinWith(['author']);` when the alias is defined in the relation definition.
>
> The alias has to be used in the filter condition but the attribute name stays the same:
>
> ```php
> $query->andFilterWhere(['LIKE', 'au.name', $this->getAttribute('author.name')]);
> ```
>
> The same is true for the sorting definition:
>
> ```php
> $dataProvider->sort->attributes['author.name'] = [
>      'asc' => ['au.name' => SORT_ASC],
>      'desc' => ['au.name' => SORT_DESC],
> ];
> ```
>
> Also, when specifying the [[yii\data\Sort::defaultOrder|defaultOrder]] for sorting, you need to use the relation name
> instead of the alias:
>
> ```php
> $dataProvider->sort->defaultOrder = ['author.name' => SORT_ASC];
> ```

> Info: For more information on `joinWith` and the queries performed in the background, check the
> [active record docs on joining with relations](db-active-record.md#joining-with-relations).

#### Using SQL views for filtering, sorting and displaying data

There is also another approach that can be faster and more useful - SQL views. For example, if we need to show the gridview 
with users and their profiles, we can do so in this way:

```sql
CREATE OR REPLACE VIEW vw_user_info AS
    SELECT user.*, user_profile.lastname, user_profile.firstname
    FROM user, user_profile
    WHERE user.id = user_profile.user_id
```

Then you need to create the ActiveRecord that will be representing this view:

```php

namespace app\models\views\grid;

use yii\db\ActiveRecord;

class UserView extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vw_user_info';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // define here your rules
        ];
    }

    /**
     * @inheritdoc
     */
    public static function attributeLabels()
    {
        return [
            // define here your attribute labels
        ];
    }


}
```

After that you can use this UserView active record with search models, without additional specification of sorting and filtering attributes.
All attributes will be working out of the box. Note that this approach has several pros and cons:

- you don't need to specify different sorting and filtering conditions. Everything works out of the box;
- it can be much faster because of the data size, count of sql queries performed (for each relation you will not need any additional query);
- since this is just a simple mapping UI on the sql view it lacks some domain logic that is in your entities, so if you have some methods like `isActive`,
`isDeleted` or others that will influence the UI, you will need to duplicate them in this class too.


### Multiple GridViews on one page

You can use more than one GridView on a single page but some additional configuration is needed so that
they do not interfere with each other.
When using multiple instances of GridView you have to configure different parameter names for
the generated sort and pagination links so that each GridView has its own individual sorting and pagination.
You do so by setting the [[yii\data\Sort::sortParam|sortParam]] and [[yii\data\Pagination::pageParam|pageParam]]
of the dataProvider's [[yii\data\BaseDataProvider::$sort|sort]] and [[yii\data\BaseDataProvider::$pagination|pagination]]
instances.

Assume we want to list the `Post` and `User` models for which we have already prepared two data providers
in `$userProvider` and `$postProvider`:

```php
use yii\grid\GridView;

$userProvider->pagination->pageParam = 'user-page';
$userProvider->sort->sortParam = 'user-sort';

$postProvider->pagination->pageParam = 'post-page';
$postProvider->sort->sortParam = 'post-sort';

echo '<h1>Users</h1>';
echo GridView::widget([
    'dataProvider' => $userProvider,
]);

echo '<h1>Posts</h1>';
echo GridView::widget([
    'dataProvider' => $postProvider,
]);
```

### Using GridView with Pjax

> Note: This section is under development.

TBD
