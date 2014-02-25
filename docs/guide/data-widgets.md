Data widgets
============

ListView
--------



DetailView
----------

DetailView displays the detail of a single data [[model]].
 
It is best used for displaying a model in a regular format (e.g. each model attribute is displayed as a row in a table).
The model can be either an instance of [[Model]] or an associative array.
 
DetailView uses the [[attributes]] property to determines which model attributes should be displayed and how they
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
