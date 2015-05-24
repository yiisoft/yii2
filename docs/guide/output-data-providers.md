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

You specify the pagination and sorting behaviors of a data provider by configuring its 
[[yii\data\BaseDataProvider::pagination|pagination]] and [[yii\data\BaseDataProvider::sort|sort]] properties
which correspond to the configurations for [[yii\data\Pagination]] and [[yii\data\Sort]], respectively.
You may also configure them to be false to disable pagination and/or sorting features.

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
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'defaultOrder' => [
            'created_at' => SORT_DESC,
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
use a different database connection by configuring the [[yii\data\ActiveDataProvider::db]] property.


## SQL Data Provider <span id="sql-data-provider"></span>

[[yii\data\SqlDataProvider]] works with a raw SQL statement which is used to fetch the needed
data. Based on the specifications of [[yii\data\SqlDataProvider::sort|sort]] and 
[[yii\data\SqlDataProvider::pagination|pagination]], the provider will adjust the `ORDER BY` and `LIMIT`
clauses of the SQL statement accordingly to fetch only the requested page of data in the desired order.

To use [[yii\data\SqlDataProvider]], you should specify the [[yii\data\SqlDataProvider::sql|sql]] property as well
as the [[yii\data\SqlDataProvider::totalCount|totalCount]] property. For example,

```php
use yii\data\SqlDataProvider;

$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM post WHERE status=:status
', [':status' => 1])->queryScalar();

$provider = new SqlDataProvider([
    'sql' => 'SELECT * FROM post WHERE status=:status',
    'params' => [':status' => 1],
    'totalCount' => $count,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => [
            'title',
            'view_count',
            'created_at',
        ],
    ],
]);

// returns an array of data rows
$models = $provider->getModels();
```

> Info: The [[yii\data\SqlDataProvider::totalCount|totalCount]] property is required only if you need to
  paginate the data. This is because the SQL statement specified via [[yii\data\SqlDataProvider::sql|sql]]
  will be modified by the provider to return only the currently requested page of data. The provider still
  needs to know the total number of data items in order to correctly calculate the number of pages available.


## Array Data Provider <span id="array-data-provider"></span>

[[yii\data\ArrayDataProvider]] is best used when working with a big array. The provider allows you to return
a page of the array data sorted by one or multiple columns. To use [[yii\data\ArrayDataProvider]], you should
specify the [[yii\data\ArrayDataProvider::allModels|allModels]] property as the big array.
Elements in the big array can be either associative arrays
(e.g. query results of [DAO](db-dao.md)) or objects (e.g. [Active Record](db-active-record.md) instances).
For example,

```php
use yii\data\ArrayDataProvider;

$data = [
    ['id' => 1, 'name' => 'name 1', ...],
    ['id' => 2, 'name' => 'name 2', ...],
    ...
    ['id' => 100, 'name' => 'name 100', ...],
];

$provider = new ArrayDataProvider([
    'allModels' => $data,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => ['id', 'name'],
    ],
]);

// get the rows in the currently requested page
$rows = $provider->getModels();
``` 

> Note: Compared to [Active Data Provider](#active-data-provider) and [SQL Data Provider](#sql-data-provider),
  array data provider is less efficient because it requires loading *all* data into the memory.


## Working with Data Keys <span id="working-with-keys"></span>

When using the data items returned by a data provider, you often need to identify each data item with a unique key.
For example, if the data items represent customer information, you may want to use the customer ID as the key
for each customer data. Data providers can return a list of such keys corresponding with the data items returned 
by [[yii\data\DataProviderInterface::getModels()]]. For example,

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => Post::find(),
]);

// returns an array of Post objects
$posts = $provider->getModels();

// returns the primary key values corresponding to $posts
$ids = $provider->getKeys();
```

In the above example, because you provide to [[yii\data\ActiveDataProvider]] an [[yii\db\ActiveQuery]] object,
it is intelligent enough to return primary key values as the keys. You may also explicitly specify how the key
values should be calculated by configuring [[yii\data\ActiveDataProvider::key]] with a column name or
a callable calculating key values. For example,

```php
// use "slug" column as key values
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => 'slug',
]);

// use the result of md5(id) as key values
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => function ($model) {
        return md5($model->id);
    }
]);
```


## Creating Custom Data Provider <span id="custom-data-provider"></span>

To create your own custom data provider classes, you should implement [[yii\data\DataProviderInterface]].
An easier way is to extend from [[yii\data\BaseDataProvider]] which allows you to focus on the core data provider
logic. In particular, you mainly need to implement the following methods:
                                                   
- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]: prepares the data models that will be made 
  available in the current page and returns them as an array.
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]: accepts an array of currently available data models
  and returns keys associated with them.
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]: returns a value indicating the total number 
  of data models in the data provider.

Below is an example of a data provider that reads CSV data efficiently:

```php
<?php
use yii\data\BaseDataProvider;

class CsvDataProvider extends BaseDataProvider
{
    /**
     * @var string name of the CSV file to read
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
