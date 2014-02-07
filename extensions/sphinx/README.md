Sphinx Extension for Yii 2
==========================

This extension adds [Sphinx](http://sphinxsearch.com/docs) full text search engine extension for the Yii 2 framework.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-sphinx "*"
```

or add

```json
"yiisoft/yii2-sphinx": "*"
```

to the require section of your composer.json.


Usage & Documentation
---------------------

This extension interacts with Sphinx search daemon using MySQL protocol and [SphinxQL](http://sphinxsearch.com/docs/current.html#sphinxql) query language.
In order to setup Sphinx "searchd" to support MySQL protocol following configuration should be added:

```
searchd
{
	listen = localhost:9306:mysql41
	...
}
```

This extension supports all Sphinx features including [Runtime Indexes](http://sphinxsearch.com/docs/current.html#rt-indexes).
Since this extension uses MySQL protocol to access Sphinx, it shares base approach and much code from the
regular "yii\db" package.

To use this extension, simply add the following code in your application configuration:

```php
return [
	//....
	'components' => [
		'sphinx' => [
			'class' => 'yii\sphinx\Connection',
			'dsn' => 'mysql:host=127.0.0.1;port=9306;',
			'username' => '',
			'password' => '',
		],
	],
];
```

This extension provides ActiveRecord solution similar ot the [[\yii\db\ActiveRecord]].
To declare an ActiveRecord class you need to extend [[\yii\sphinx\ActiveRecord]] and
implement the `indexName` method:

```php
use yii\sphinx\ActiveRecord;

class Article extends ActiveRecord
{
	/**
	 * @return string the name of the index associated with this ActiveRecord class.
	 */
	public static function indexName()
	{
		return 'idx_article';
	}
}
```

You can use [[\yii\data\ActiveDataProvider]] with the [[\yii\sphinx\Query]] and [[\yii\sphinx\ActiveQuery]]:

```php
use yii\data\ActiveDataProvider;
use yii\sphinx\Query;

$query = new Query;
$query->from('yii2_test_article_index')->match('development');
$provider = new ActiveDataProvider([
	'query' => $query,
	'pagination' => [
		'pageSize' => 10,
	]
]);
$models = $provider->getModels();
```

```php
use yii\data\ActiveDataProvider;
use app\models\Article;

$provider = new ActiveDataProvider([
	'query' => Article::find(),
	'pagination' => [
		'pageSize' => 10,
	]
]);
$models = $provider->getModels();
```
