Data providers
==============

> Note: This section is under development.

Data provider abstracts data set via [[yii\data\DataProviderInterface]] and handles pagination and sorting.
It can be used by [grids, lists and other data widgets](output-data-widgets.md).

In Yii there are three built-in data providers: [[yii\data\ActiveDataProvider]], [[yii\data\ArrayDataProvider]] and
[[yii\data\SqlDataProvider]].

Active data provider
--------------------

`ActiveDataProvider` provides data by performing DB queries using [[yii\db\Query]] and [[yii\db\ActiveQuery]].

The following is an example of using it to provide ActiveRecord instances:

```php
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);

// get the posts in the current page
$posts = $provider->getModels();
```

And the following example shows how to use ActiveDataProvider without ActiveRecord:

```php
$query = new Query();
$provider = new ActiveDataProvider([
    'query' => $query->from('post'),
    'sort' => [
        // Set the default sort by name ASC and created_at DESC.
        'defaultOrder' => [
            'name' => SORT_ASC, 
            'created_at' => SORT_DESC
        ]
    ],
    'pagination' => [
        'pageSize' => 20,
    ],
]);

// get the posts in the current page
$posts = $provider->getModels();
```

Array data provider
-------------------

ArrayDataProvider implements a data provider based on a data array.

The [[yii\data\ArrayDataProvider::$allModels]] property contains all data models that may be sorted and/or paginated.
ArrayDataProvider will provide the data after sorting and/or pagination.
You may configure the [[yii\data\ArrayDataProvider::$sort]] and [[yii\data\ArrayDataProvider::$pagination]] properties to
customize the sorting and pagination behaviors.

Elements in the [[yii\data\ArrayDataProvider::$allModels]] array may be either objects (e.g. model objects)
or associative arrays (e.g. query results of DAO).
Make sure to set the [[yii\data\ArrayDataProvider::$key]] property to the name of the field that uniquely
identifies a data record or false if you do not have such a field.

Compared to `ActiveDataProvider`, `ArrayDataProvider` could be less efficient
because it needs to have [[yii\data\ArrayDataProvider::$allModels]] ready.

ArrayDataProvider may be used in the following way:

```php
$query = new Query();
$provider = new ArrayDataProvider([
    'allModels' => $query->from('post')->all(),
    'sort' => [
        'attributes' => ['id', 'username', 'email'],
    ],
    'pagination' => [
        'pageSize' => 10,
    ],
]);
// get the posts in the current page
$posts = $provider->getModels();
```

> Note: if you want to use the sorting feature, you must configure the [[sort]] property
so that the provider knows which columns can be sorted.

SQL data provider
-----------------

SqlDataProvider implements a data provider based on a plain SQL statement. It provides data in terms of arrays, each
representing a row of query result.

Like other data providers, SqlDataProvider also supports sorting and pagination. It does so by modifying the given
[[yii\data\SqlDataProvider::$sql]] statement with "ORDER BY" and "LIMIT" clauses. You may configure the
[[yii\data\SqlDataProvider::$sort]] and [[yii\data\SqlDataProvider::$pagination]] properties to customize sorting
and pagination behaviors.

`SqlDataProvider` may be used in the following way:

```php
$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM user WHERE status=:status
', [':status' => 1])->queryScalar();

$dataProvider = new SqlDataProvider([
    'sql' => 'SELECT * FROM user WHERE status=:status',
    'params' => [':status' => 1],
    'totalCount' => $count,
    'sort' => [
        'attributes' => [
            'age',
            'name' => [
                'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
                'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
                'default' => SORT_DESC,
                'label' => 'Name',
            ],
        ],
    ],
    'pagination' => [
        'pageSize' => 20,
    ],
]);

// get the user records in the current page
$models = $dataProvider->getModels();
```

> Note: if you want to use the pagination feature, you must configure the [[yii\data\SqlDataProvider::$totalCount]]
property to be the total number of rows (without pagination). And if you want to use the sorting feature,
you must configure the [[yii\data\SqlDataProvider::$sort]] property so that the provider knows which columns can
be sorted.


Implementing your own custom data provider
------------------------------------------

Yii allows you to introduce your own custom data providers. In order to do it you need to implement the following
`protected` methods:
                                                   
- `prepareModels` that prepares the data models that will be made available in the current page and returns them as an array.
- `prepareKeys` that accepts an array of currently available data models and returns keys associated with them.
- `prepareTotalCount` that returns a value indicating the total number of data models in the data provider.

Below is an example of a data provider that reads CSV efficiently:

```php
<?php
class CsvDataProvider extends \yii\data\BaseDataProvider
{
    /**
     * @var string name of the file to read
     */
    public $filename;
    
    /**
     * @var string|callable name of the key column or a callable returning it
     */
    public $key;
    
    /**
     * @var SplFileObject
     */
    protected $fileObject; // SplFileObject is very convenient for seeking to particular line in a file
    
 
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        // open file
        $this->fileObject = new SplFileObject($this->filename);
    }
 
    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        $models = [];
        $pagination = $this->getPagination();
 
        if ($pagination === false) {
            // in case there's no pagination, read all lines
            while (!$this->fileObject->eof()) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        } else {
            // in case there's pagination, read only a single page
            $pagination->totalCount = $this->getTotalCount();
            $this->fileObject->seek($pagination->getOffset());
            $limit = $pagination->getLimit();
 
            for ($count = 0; $count < $limit; ++$count) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        }
 
        return $models;
    }
 
    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
 
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
 
            return $keys;
        } else {
            return array_keys($models);
        }
    }
 
    /**
     * @inheritdoc
     */
    protected function prepareTotalCount()
    {
        $count = 0;
 
        while (!$this->fileObject->eof()) {
            $this->fileObject->next();
            ++$count;
        }
 
        return $count;
    }
}
```
 