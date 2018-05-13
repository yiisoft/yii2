数据提供者
==============

在 [Pagination](output-pagination.md) 和 [Sorting](output-sorting.md) 部分,
我们已经介绍了如何允许终端用户选择一个特定的数据页面，根据一些字段对它们进行展现与排序。
因为分页和排序数据的任务是很常见的，所以Yii提供了一组封装好的*data provider*类。

数据提供者是一个实现了 [[yii\data\DataProviderInterface]] 接口的类。
它主要用于获取分页和数据排序。它经常用在 [data widgets](output-data-widgets.md) 
小物件里，方便终端用户进行分页与数据排序。

下面的数据提供者类都包含在Yii的发布版本里面：

* [[yii\data\ActiveDataProvider]]：使用 [[yii\db\Query]] 或者 [[yii\db\ActiveQuery]] 从数据库查询数据并且以数组项的方式或者
  [Active Record](db-active-record.md) 实例的方式返回。
* [[yii\data\SqlDataProvider]]：执行一段SQL语句并且将数据库数据作为数组返回。
* [[yii\data\ArrayDataProvider]]：将一个大的数组依据分页和排序规格返回一部分数据。


所有的这些数据提供者遵守以下模式：

```php
// 根据配置的分页以及排序属性来创建一个数据提供者
$provider = new XyzDataProvider([
    'pagination' => [...],
    'sort' => [...],
]);

// 获取分页和排序数据
$models = $provider->getModels();

// 在当前页获取数据项的数目
$count = $provider->getCount();

// 获取所有页面的数据项的总数
$totalCount = $provider->getTotalCount();
```

你可以通过配置 [[yii\data\BaseDataProvider::pagination|pagination]] 和 
[[yii\data\BaseDataProvider::sort|sort]]的属性来设定数据提供者的分页和排序行为。
属性分别对应于 [[yii\data\Pagination]] 和 [[yii\data\Sort]]。
你也可以对它们配置false来禁用分页和排序特性。

[Data widgets](output-data-widgets.md),诸如 [[yii\grid\GridView]]，有一个属性名叫 `dataProvider` ，这个属性能够提供一个
数据提供者的示例并且可以显示所提供的数据，例如，

```php
echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

这些数据提供者的主要区别在于数据源的指定方式上。在下面的部分，
我们将详细介绍这些数据提供者的使用方法。


## 活动数据提供者 <span id="active-data-provider"></span>

为了使用 [[yii\data\ActiveDataProvider]]，你应该配置其 [[yii\data\ActiveDataProvider::query|query]] 的属性。
它既可以是一个 [[yii\db\Query]] 对象，又可以是一个 [[yii\db\ActiveQuery]] 对象。假如是前者，返回的数据将是数组；
如果是后者，返回的数据可以是数组也可以是 [Active Record](db-active-record.md) 对象。
例如，

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

// 返回一个Post实例的数组
$posts = $provider->getModels();
```

假如在上面的例子中，`$query` 用下面的代码来创建，则数据提供者将返回原始数组。

```php
use yii\db\Query;

$query = (new Query())->from('post')->where(['status' => 1]);
```

> Note: 假如查询已经指定了 `orderBy` 从句，则终端用户给定的新的排序说明（通过 `sort` 来配置的）
  将被添加到已经存在的从句中。任何已经存在的 `limit` 和 `offset` 从句都将被终端用户所请求的分页
  （通过 `pagination` 所配置的）所重写。

默认情况下，[[yii\data\ActiveDataProvider]]使用 `db` 应用组件来作为数据库连接。你可以通过配置 [[yii\data\ActiveDataProvider::db]]
的属性来使用不同数据库连接。


## SQL数据提供者 <span id="sql-data-provider"></span>

[[yii\data\SqlDataProvider]] 应用的时候需要结合需要获取数据的SQL语句。
基于 [[yii\data\SqlDataProvider::sort|sort]] 和
[[yii\data\SqlDataProvider::pagination|pagination]] 规格，
数据提供者会根据所请求的数据页面（期望的顺序）来调整 `ORDER BY` 和 `LIMIT` 的SQL从句。

为了使用 [[yii\data\SqlDataProvider]]，你应该指定 [[yii\data\SqlDataProvider::sql|sql]] 属性以及
[[yii\data\SqlDataProvider::totalCount|totalCount]] 属性，例如，

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

// 返回包含每一行的数组
$models = $provider->getModels();
```

> 说明：[[yii\data\SqlDataProvider::totalCount|totalCount]] 的属性只有你需要
  分页数据的时候才会用到。这是因为通过 [[yii\data\SqlDataProvider::sql|sql]] 
  指定的SQL语句将被数据提供者所修改并且只返回当
  前页面数据。数据提供者为了正确计算可用页面的数量仍旧需要知道数据项的总数。


## 数组数据提供者 <span id="array-data-provider"></span>

[[yii\data\ArrayDataProvider]] 非常适用于大的数组。数据提供者允许你返回一个
经过一个或者多个字段排序的数组数据页面。为了使用 [[yii\data\ArrayDataProvider]]，
你应该指定 [[yii\data\ArrayDataProvider::allModels|allModels]] 属性作为一个大的数组。
这个大数组的元素既可以是一些关联数组（例如：[DAO](db-dao.md)查询出来的结果）
也可以是一些对象（例如：[Active Record](db-active-record.md)实例）
例如,

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

// 获取当前请求页的每一行数据
$rows = $provider->getModels();
```

> Note: 数组数据提供者与 [Active Data Provider](#active-data-provider) 和 [SQL Data Provider](#sql-data-provider) 这两者进行比较的话，
  会发现数组数据提供者没有后面那两个高效，这是因为数组数据提供者需要加载*所有*的数据到内存中。


## 数据键的使用 <span id="working-with-keys"></span>

当使用通过数据提供者返回的数据项的时候，你经常需要使用一个唯一键来标识每一个数据项。
举个例子，假如数据项代表的是一些自定义的信息，你可能会使用自定义ID作为键去标识每一个自定义数据。
数据提供者能够返回一个通过 [[yii\data\DataProviderInterface::getModels()]] 返回的键与数据项相对应的列表。
例如，

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => Post::find(),
]);

// 返回包含Post对象的数组
$posts = $provider->getModels();

// 返回与$posts相对应的主键值
$ids = $provider->getKeys();
```

在上面的例子中，因为你提供给 [[yii\data\ActiveDataProvider]] 一个 [[yii\db\ActiveQuery]] 对象，
它是足够智能地返回一些主键值作为键。你也可以明确指出键值应该怎样被计算出来，
计算的方式是通过使用一个字段名或者一个可调用的计算键值来配置。
例如，

```php
// 使用 "slug" 字段作为键值
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => 'slug',
]);

// 使用md5(id)的结果作为键值
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => function ($model) {
        return md5($model->id);
    }
]);
```


## 创建自定义数据提供者 <span id="custom-data-provider"></span>

为了创建自定义的数据提供者类，你应该实现 [[yii\data\DataProviderInterface]] 接口。
一个简单的方式是从 [[yii\data\BaseDataProvider]] 去扩展，这种方式允许你关注数据提供者的核心逻辑。
这时，你主要需要实现下面的一些方法：

- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]：准备好在当前页面可用的数据模型，
  并且作为一个数组返回它们。
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]：接受一个当前可用的数据模型的数组，
  并且返回一些与它们相关联的键。
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]: 在数据提供者中返回一个
  标识出数据模型总数的值。

下面是一个数据提供者的例子，这个数据提供者可以高效地读取CSV数据：

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
