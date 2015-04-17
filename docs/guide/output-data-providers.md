Data Providers
==============

In the [Pagination](output-pagination.md) and [Sorting](output-sorting.md) sections, we have described how to
allow end users to choose a particular page of data to display and sort them by some columns. Because the task
of paginating and sorting data is very common, Yii provides a set of *data provider* classes to encapsulate it.

A data provider is a class implementing [[yii\data\DataProviderInterface]]. It mainly supports retrieving paginated
and sorted data. It is usually used to work with [data widgets](output-data-widgets.md) so that end users can 
interactively paginate and sort data. 

The following data provider classes are included in the Yii releases:

* [[yii\data\ActiveDataProvider]]: uses [[yii\db\Query]] or [[yii\db\ActiveQuery]] to query data from databases
  and return them in terms of arrays or [Active Record](db-active-record.md) instances.
* [[yii\data\SqlDataProvider]]: executes a SQL statement and returns database data as arrays.
* [[yii\data\ArrayDataProvider]]: takes a big array and returns a slice of it based on the paginating and sorting
  specifications.

The usage of all these data providers share the following common pattern:

```php
// create the data provider by configuring its pagination and sort properties
$provider = new XyzDataProvider([
    'pagination' => [...],
    'sort' => [...],
]);

// retrieves paginated and sorted data
$models = $provider->getModels();

// get the number of data items in the current page
$count = $provider->getCount();

// get the total number of data items across all pages
$totalCount = $provider->getTotalCount();
```

The `pagination` and `sort` properties of data providers correspond to the configurations for
[[yii\data\Pagination]] and [[yii\data\Sort]], respectively.

[Data widgets](output-data-widgets.md), such as [[yii\grid\GridView]], have a property named `dataProvider` which
can take a data provider instance and display the data it provides. For example,

```php
echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

These data providers mainly vary in the way how the data source is specified. In the following subsections,
we will explain the detailed usage of each of these data providers.


## Active Data Provider <span id="active-data-provider"></span> 

To use [[yii\data\ActiveDataProvider]], you should configure its [[yii\data\ActiveDataProvider::query|query]] property.
It can take either a [[yii\db\Query]] or [[yii\db\ActiveQuery]] object. If the former, the data returned will be arrays;
if the latter, the data returned can be either arrays or [Active Record](db-active-record.md) instances.
For example,

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
    'sort' => [
        'defaultOrder' => [
            'created_at' => SORT_DESC
            'title' => SORT_ASC, 
        ]
    ],
]);

// returns an array of Post objects
$posts = $provider->getModels();
```

If `$query` in the above example is created using the following code, then the data provider will return raw arrays.

```php
use yii\db\Query;

$query = (new Query())->from('post')->where(['status' => 1]); 
```

> Note: If a query already specifies the `orderBy` clause, the new ordering instructions given by end users
  (through the `sort` configuration) will be appended to the existing `orderBy` clause. Any existing `limit`
  and `offset` clauses will be overwritten by the pagination request from end users (through the `pagination` configuration). 

By default, [[yii\data\ActiveDataProvider]] uses the `db` application component as the database connection. You may
use a different database connection by configuring the [[yii\data\ActiveDataProvider::db]] property. For example,


## SQL Data Provider <span id="sql-data-provider"></span>

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


## Array Data Provider <span id="array-data-provider"></span>

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
 
