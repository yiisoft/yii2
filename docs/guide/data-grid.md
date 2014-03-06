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
sdf
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

Note: If columns part of config isn't specified, Yii tries to show all possible data provider model columns.


### Column classes


#### Data column

#### Action column

#### Checkbox column

#### Serial column

TODO: rewrite these:

- https://github.com/samdark/a-guide-to-yii-grids-lists-and-data-providers/blob/master/grid-columns.md
- https://github.com/samdark/a-guide-to-yii-grids-lists-and-data-providers/pull/1

Sorting data
------------

- https://github.com/yiisoft/yii2/issues/1576

Filtering data
--------------

- https://github.com/yiisoft/yii2/issues/1581