MongoDb Extension for Yii 2
===========================

This extension provides the [MongoDB](http://www.mongodb.org/) integration for the Yii2 framework.


Installation
------------

This extension requires [MongoDB PHP Extension](http://us1.php.net/manual/en/book.mongo.php) version 1.3.0 or higher.

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-mongodb "*"
```

or add

```
"yiisoft/yii2-mongodb": "*"
```

to the require section of your composer.json.


General Usage
-------------

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'mongodb' => [
            'class' => '\yii\mongodb\Connection',
            'dsn' => 'mongodb://developer:password@localhost:27017/mydatabase',
        ],
    ],
];
```

Using the connection instance you may access databases and collections.
Most of the MongoDB commands are accessible via [[\yii\mongodb\Collection]] instance:

```php
$collection = Yii::$app->mongodb->getCollection('customer');
$collection->insert(['name' => 'John Smith', 'status' => 1]);
```

To perform "find" queries, you should use [[\yii\mongodb\Query]]:

```php
use yii\mongodb\Query;

$query = new Query;
// compose the query
$query->select(['name', 'status'])
    ->from('customer')
    ->limit(10);
// execute the query
$rows = $query->all();
```

This extension supports logging and profiling, however log messages does not contain
actual text of the performed queries, they contains only a “close approximation” of it
composed on the values which can be extracted from PHP Mongo extension classes.
If you need to see actual query text, you should use specific tools for that.


Notes about MongoId
-------------------

Remember: MongoDB document id ("_id" field) is not scalar, but an instance of [[\MongoId]] class.
To get actual Mongo ID string your should typecast [[\MongoId]] instance to string:

```php
$query = new Query;
$row = $query->from('customer')->one();
var_dump($row['_id']); // outputs: "object(MongoId)"
var_dump((string)$row['_id']); // outputs "string 'acdfgdacdhcbdafa'"
```

Although this fact is very useful sometimes, it often produces some problems.
You may face them in URL composition or attempt of saving "_id" to other storage.
In these cases, ensure you have converted [[\MongoId]] into the string:

```php
/** @var yii\web\View $this */
echo $this->createUrl(['item/update', 'id' => (string)$row['_id']]);
```

While building condition, values for the key '_id' will be automatically cast to [[\MongoId]] instance,
even if they are plain strings. So it is not necessary for you to perform back cast of string '_id'
representation:

```php
use yii\web\Controller;
use yii\mongodb\Query;

class ItemController extends Controller
{
    /**
     * @param string $id MongoId string (not object)
     */
    public function actionUpdate($id)
    {
        $query = new Query;
        $row = $query->from('item')
            where(['_id' => $id]) // implicit typecast to [[\MongoId]]
            ->one();
        ...
    }
}
```

However, if you have other columns, containing [[\MongoId]], you
should take care of possible typecast on your own.


Using the MongoDB ActiveRecord
------------------------------

This extension provides ActiveRecord solution similar ot the [[\yii\db\ActiveRecord]].
To declare an ActiveRecord class you need to extend [[\yii\mongodb\ActiveRecord]] and
implement the `collectionName` and 'attributes' methods:

```php
use yii\mongodb\ActiveRecord;

class Customer extends ActiveRecord
{
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function collectionName()
    {
        return 'customer';
    }

    /**
     * @return array list of attribute names.
     */
    public function attributes()
    {
        return ['_id', 'name', 'email', 'address', 'status'];
    }
}
```

Note: collection primary key name ('_id') should be always explicitly setup as an attribute.

You can use [[\yii\data\ActiveDataProvider]] with [[\yii\mongodb\Query]] and [[\yii\mongodb\ActiveQuery]]:

```php
use yii\data\ActiveDataProvider;
use yii\mongodb\Query;

$query = new Query;
$query->from('customer')->where(['status' => 2]);
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
use app\models\Customer;

$provider = new ActiveDataProvider([
    'query' => Customer::find(),
    'pagination' => [
        'pageSize' => 10,
    ]
]);
$models = $provider->getModels();
```


Working with embedded documents
-------------------------------

This extension does not provide any special way to work with embedded documents (sub-documents).
General recommendation is avoiding it if possible.
For example: instead of:

```
{
    content: "some content",
    author: {
        name: author1,
        email: author1@domain.com
    }
}
```

use following:

```
{
    content: "some content",
    author_name: author1,
    author_email: author1@domain.com
}
```

Yii Model designed assuming single attribute is a scalar. Validation and attribute processing based on this suggestion.
Still any attribute can be an array of any depth and complexity, however you should handle its validation on your own.


Using GridFS
------------

This extension supports [MongoGridFS](http://docs.mongodb.org/manual/core/gridfs/) via
classes under namespace "\yii\mongodb\file".
There you will find specific Collection, Query and ActiveRecord classes.


Using the Cache component
-------------------------

To use the `Cache` component, in addition to configuring the connection as described above,
you also have to configure the `cache` component to be `yii\mongodb\Cache`:

```php
return [
    //....
    'components' => [
        // ...
        'cache' => [
            'class' => 'yii\mongodb\Cache',
        ],
    ]
];
```


Using the Session component
---------------------------

To use the `Session` component, in addition to configuring the connection as described above,
you also have to configure the `session` component to be `yii\mongodb\Session`:

```php
return [
    //....
    'components' => [
        // ...
        'session' => [
            'class' => 'yii\mongodb\Session',
        ],
    ]
];
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
                'mongoDbModel' => [
                    'class' => 'yii\mongodb\gii\model\Generator'
                ]
            ],
        ],
    ]
];
```

> Note: since MongoDB is schemaless, there is not much information, which generated code may base on. So generated code
  is very basic and definitely requires adjustments.