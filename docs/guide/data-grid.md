Data grid
=========

Data grid or GridView is one of the most powerful Yii widgets. It is extremely useful if you need to quickly build admin
section of the system. It takes data from [data provider](data-provider.md) and renders each row using a set of columns
presenting data in a form of a table.

Each row of the table represents the data of a single data item, and a column usually represents an attribute of
the item (some columns may correspond to complex expression of attributes or static text).

Grid view supports both sorting and pagination of the data items. The sorting and pagination can be done in AJAX mode
or normal page request. A benefit of using GridView is that when the user disables JavaScript, the sorting and pagination
automatically degrade to normal page requests and are still functioning as expected.

The minimal code needed to use CGridView is as follows:

```php
use yii\data\GridView;
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
data provider. The displayed table is equiped with sorting and pagination functionality.

Grid columns
------------

Yii grid consists of a number of columns. Depending on column type and settings these are able to present data differently.

These are defined in the columns part of GridView config like the following:

```php
echo GridView::widget([
	'dataProvider' => $dataProvider,
	'columns' => [
		['class' => 'yii\grid\SerialColumn'],
		// A simple column defined by the data contained in $dataProvider.
		// Data from model's column1 will be used.
		'id',
		'username',
		// More complex one.
		[
			'class' => 'DataColumn', // can be omitted, default
			'name' => 'column1',
			'value' => function ($data) {
				return $data->name;
			},
			'type'=>'raw',
		],
	],
]);
```

Note that if columns part of config isn't specified, Yii tries to show all possible data provider model columns.

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

Additionally to column classes provided by Yii that we'll review below you can create your own column classes.

Each column class extends from [[\yii\grid\Column]] so there some common options you can set while configuring
grid columns.

- `header` allows to set content for header row.
- `footer` allows to set content for footer row.
- `visible` is the column should be visible.
- `content` allows you to pass a valid PHP callback that will return data for a row. The format is the following:

```php
function ($model, $key, $index, $grid) {
	return 'a string';
}
```

You may specify various container HTML options passing arrays to:

- `headerOptions`
- `contentOptions`
- `footerOptions`
- `filterOptions`

#### Data column

Data column is for displaying and sorting data. It is default column type so specifying class could be omitted when
using it.

TBD

#### Action column

Action column displays action buttons such as update or delete for each row.

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

- `controller` is the ID of the controller that should handle the actions. If not set, it will use the currently active
  controller.
- `template` the template used for composing each cell in the action column. Tokens enclosed within curly brackets are
  treated as controller action IDs (also called *button names* in the context of action column). They will be replaced
  by the corresponding button rendering callbacks specified in [[buttons]]. For example, the token `{view}` will be
  replaced by the result of the callback `buttons['view']`. If a callback cannot be found, the token will be replaced
  with an empty string. Default is `{view} {update} {delete}`.
- `buttons` is an array of button rendering callbacks. The array keys are the button names (without curly brackets),
  and the values are the corresponding button rendering callbacks. The callbacks should use the following signature:

```php
function ($url, $model) {
	// return the button HTML code
}
```

In the code above `$url` is the URL that the column creates for the button, and `$model` is the model object being
rendered for the current row.

- `urlCreator` is a callback that creates a button URL using the specified model information. The signature of
  the callback should be the same as that of [[createUrl()]]. If this property is not set, button URLs will be created
  using [[createUrl()]].

#### Checkbox column

CheckboxColumn displays a column of checkboxes.
 
To add a CheckboxColumn to the [[GridView]], add it to the [[GridView::columns|columns]] configuration as follows:
 
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

Serial column renders row numbers starting with `1` and going forward.

Usage is as simple as the following:

```php
echo GridView::widget([
	'dataProvider' => $dataProvider,
	'columns' => [
		['class' => 'yii\grid\SerialColumn'], // <-- here
```

TODO: rewrite these:

- https://github.com/samdark/a-guide-to-yii-grids-lists-and-data-providers/blob/master/grid-columns.md
- https://github.com/samdark/a-guide-to-yii-grids-lists-and-data-providers/pull/1

Sorting data
------------

- https://github.com/yiisoft/yii2/issues/1576

Filtering data
--------------

- https://github.com/yiisoft/yii2/issues/1581