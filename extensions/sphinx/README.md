Sphinx Extension for Yii 2
==========================

This extension adds [Sphinx](http://sphinxsearch.com/docs) full text search engine extension for the Yii 2 framework.
It supports all Sphinx features including [Runtime Indexes](http://sphinxsearch.com/docs/current.html#rt-indexes).


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


Configuration
-------------

This extension interacts with Sphinx search daemon using MySQL protocol and [SphinxQL](http://sphinxsearch.com/docs/current.html#sphinxql) query language.
In order to setup Sphinx "searchd" to support MySQL protocol following configuration should be added:

```
searchd
{
    listen = localhost:9306:mysql41
    ...
}
```

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


Basic Usage
-----------

Since this extension uses MySQL protocol to access Sphinx, it shares base approach and much code from the
regular "yii\db" package. Running SphinxQL queries a very similar to regular SQL ones:

```php
$sql = 'SELECT * FROM idx_item WHERE group_id = :group_id';
$params = [
    'group_id' => 17
];
$rows = Yii::$app->sphinx->createCommand($sql, $params)->queryAll();
```

You can also use a Query Builder:

```php
use yii\sphinx\Query;

$query = new Query;
$rows = $query->select('id, price')
    ->from('idx_item')
    ->andWhere(['group_id' => 1])
    ->all();
```

> Note: Sphinx limits the number of records returned by any query to 10 records by default.
  If you need to get more records you should specify limit explicitly.


Composing 'MATCH' statement
---------------------------

Sphinx usage does not make sense unless you are using its fulltext search ability.
In SphinxSQL it is provided via 'MATCH' statement. You can always compose it manually as a part of the 'where'
condition, but if you are using `yii\sphinx\Query` you can do it via `yii\sphinx\Query::match()`:

```php
use yii\sphinx\Query;

$query = new Query;
$rows = $query->from('idx_item')
    ->match($_POST['search'])
    ->all();
```

Please note that Sphinx 'MATCH' statement argument uses complex internal syntax for better tuning.
By default `yii\sphinx\Query::match()` will escape all special characters related to this syntax from
its argument. So if you wish to use complex 'MATCH' statement, you should use `yii\db\Expression` for it:

```php
use yii\sphinx\Query;
use yii\db\Expression;

$query = new Query;
$rows = $query->from('idx_item')
    ->match(new Expression(':match', ['match' => '@(content) ' . Yii::$app->sphinx->escapeMatchValue($_POST['search'])]))
    ->all();
```

> Note: if you compose 'MATCH' argument, make sure to use `yii\sphinx\Connection::escapeMatchValue()` to properly
  escape any special characters, which may break the query.


Using the ActiveRecord
----------------------

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


Working with data providers
---------------------------

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


Building Snippets (Excerpts)
----------------------------

Snippet (excerpt) - is a fragment of the index source text, which contains highlighted words from fulltext search
condition. Sphinx has a powerful build-in mechanism to compose snippets. However, since Sphinx does not store the
original indexed text, the snippets for the rows in query result should be build separately via another query.
Such query may be performed via `yii\sphinx\Command::callSnippets()`:

```php
$sql = "SELECT * FROM idx_item WHERE MATCH('about')";
$rows = Yii::$app->sphinx->createCommand($sql)->queryAll();

$rowSnippetSources = [];
foreach ($rows as $row) {
    $rowSnippetSources[] = file_get_contents('/path/to/index/files/' . $row['id'] . '.txt');
}

$snippets = Yii::$app->sphinx->createCommand($sql)->callSnippets('idx_item', $rowSnippetSources, 'about');
```

You can simplify this workflow using [[yii\sphinx\Query::snippetCallback]].
It is a PHP callback, which receives array of query result rows as an argument and must return the
array of snippet source strings in the order, which match one of incoming rows.
Example:

```php
use yii\sphinx\Query;

$query = new Query;
$rows = $query->from('idx_item')
    ->match($_POST['search'])
    ->snippetCallback(function ($rows) {
        $result = [];
        foreach ($rows as $row) {
            $result[] = file_get_contents('/path/to/index/files/' . $row['id'] . '.txt');
        }
        return $result;
    })
    ->all();

foreach ($rows as $row) {
    echo $row['snippet'];
}
```

If you are using Active Record, you can [[yii\sphinx\ActiveQuery::snippetByModel()]] to compose a snippets.
This method retrieves snippet source per each row calling `getSnippetSource()` method of the result model.
All you need to do is implement it in your Active Record class, so it return the correct value:

```php
use yii\sphinx\ActiveRecord;

class Article extends ActiveRecord
{
    public function getSnippetSource()
    {
        return file_get_contents('/path/to/source/files/' . $this->id . '.txt');;
    }
}

$articles = Article::find()->snippetByModel()->all();

foreach ($articles as $article) {
    echo $article->snippet;
}
```


Using Gii generator
-------------------

This extension provides a code generator, which can be integrated with yii 'gii' module. It allows generation of the
Active Record code. In order to enable it, you should adjust your application configuration in following way:

```php
return [
    //....
    'modules' => [
        // ...
        'gii' => [
            'class' => 'yii\gii\Module',
            'generators' => [
                'sphinxModel' => [
                    'class' => 'yii\sphinx\gii\model\Generator'
                ]
            ],
        ],
    ]
];
```