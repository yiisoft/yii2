排序
=======

展示多条数据时，通常需要对数据按照用户指定的列进行排序。
Yii 使用 [[yii\data\Sort]] 对象来代表排序方案的有关信息。
特别地，

* [[yii\data\Sort::$attributes|attributes]] 指定 *属性*，数据按照其排序。
  一个属性可以就是简单的一个 [model attribute](structure-models.md#attributes)，
  也可以是结合了多个 model 属性或者 DB 列的复合属性。下面将给出更多细节。
* [[yii\data\Sort::$attributeOrders|attributeOrders]] 给出每个属性当前设置的
  排序方向。
* [[yii\data\Sort::$orders|orders]] 按照低级列的方式给出排序方向。

使用 [[yii\data\Sort]]，首先要声明什么属性能进行排序。
接着从 [[yii\data\Sort::$attributeOrders|attributeOrders]] 或者 [[yii\data\Sort::$orders|orders]] 取得当前设置的排序信息，
然后使用它们来自定义数据查询。例如，

```php
use yii\data\Sort;

$sort = new Sort([
    'attributes' => [
        'age',
        'name' => [
            'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
            'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
            'default' => SORT_DESC,
            'label' => 'Name',
        ],
    ],
]);

$articles = Article::find()
    ->where(['status' => 1])
    ->orderBy($sort->orders)
    ->all();
```

上述例子中，为 [[yii\data\Sort|Sort]] 对象声明了两个属性： `age` 和 `name`。

`age` 属性是 `Article` 与 Active Record 类中 `age` 属性对应的一个简单属性。
上述声明与下述等同：

```php
'age' => [
    'asc' => ['age' => SORT_ASC],
    'desc' => ['age' => SORT_DESC],
    'default' => SORT_ASC,
    'label' => Inflector::camel2words('age'),
]
```

`name` 属性是由 `Article` 的 `firsr_name` 和 `last_name` 定义的一个复合属性。
使用下面的数组结构来对它进行声明：

- `asc` 和 `desc` 元素指定了如何按照该属性进行升序和降序的排序。
  它们的值代表数据真正地应该按照什么列和方向进行排序。
  你可以指定一列或多列来指出到底是简单排序还是多重排序。
- `default` 元素指定了当一次请求时，属性应该按照什么方向来进行排序。
  它默认为升序方向，意味着如果之前没有进行排序，并且
  你请求按照该属性进行排序，那么数据将按照该属性来进行升序排序。
- `label` 元素指定了调用 [[yii\data\Sort::link()]] 来创建一个排序链接时应该使用什么标签。
  如果不设置，将调用 [[yii\helpers\Inflector::camel2words()]] 来通过属性名生成一个标签。
  注意，它并不是 HTML编码的。
  
> Info: 你可以将 [[yii\data\Sort::$orders|orders]] 的值直接提供给数据库查询来构建其 `ORDER BY` 子句。
  不要使用 [[yii\data\Sort::$attributeOrders|attributeOrders]]，
  因为一些属性可能是复合的，是不能被数据库查询识别的。

你可以调用 [[yii\data\Sort::link()]] 来生成一个超链接，用户可以通过点击它来请求按照指定的属性对数据进行排序。
你也可以调用 [[yii\data\Sort::createUrl()]] 来生成一个可排序的 URL。
例如，

```php
// 指定被创建的 URL 应该使用的路由
// 如果你没有指定，将使用当前被请求的路由
$sort->route = 'article/index';

// 显示链接，链接分别指向以 name 和 age 进行排序
echo $sort->link('name') . ' | ' . $sort->link('age');

// 显示: /index.php?r=article%2Findex&sort=age
echo $sort->createUrl('age');
```

[[yii\data\Sort]] 查看 `sort` 查询参数来决定哪一个属性正在被请求来进行排序。
当该参数不存在时，你可以通过 [[yii\data\Sort::defaultOrder]] 来指定默认的排序。
你也可以通过配置 [[yii\data\Sort::sortParam|sortParam]] 属性来自定义该查询参数的名字。
