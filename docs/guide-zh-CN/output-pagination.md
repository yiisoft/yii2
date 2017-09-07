分页
==========

当一次要在一个页面上显示很多数据时，
通常需要将其分成几部分，
每个部分都包含一些数据列表并且一次只显示一部分。
这些部分在网页上被称为 *分页*。
  
Yii 使用 [[yii\data\Pagination]] 对象来代表分页方案的有关信息。
特别地，

* [[yii\data\Pagination::$totalCount|total count]] 指定数据条目的总数。注意，这个数字通常远远大于需要在一个页面上展示的数据条目。
* [[yii\data\Pagination::$pageSize|page size]] 指定每页包含多少数据条目。默认值为20。
* [[yii\data\Pagination::$page|current page]] 给出当前的页码。默认值为0，表示第一页。

通过一个已经完全明确的 [[yii\data\Pagination]] 对象，
你可以部分地检索并且展示数据。
比如，如果你正在从数据库取回数据，
你可以使用分页对象提供的对应值来指定 DB 查询语句中的 `OFFSET` 和  `LIMIT` 子句。
下面是个例子，

```php
use yii\data\Pagination;

// 创建一个 DB 查询来获得所有 status 为 1 的文章
$query = Article::find()->where(['status' => 1]);

// 得到文章的总数（但是还没有从数据库取数据）
$count = $query->count();

// 使用总数来创建一个分页对象
$pagination = new Pagination(['totalCount' => $count]);

// 使用分页对象来填充 limit 子句并取得文章数据
$articles = $query->offset($pagination->offset)
    ->limit($pagination->limit)
    ->all();
```

上述例子中，文章的哪一页将被返回？它取决于是否给出一个名为 `page` 的参数。
默认情况下，分页对象将尝试将 [[yii\data\Pagination::$page|current page]] 设置为 `page` 参数的值。
如果没有提供该参数，那么它将默认为0。

为了促成创建支持分页的 UI 元素，Yii 提供了 [[yii\widgets\LinkPager]] 挂件来展示一栏页码按钮，
用户可以通过点击它来指示应该显示哪一页的数据。
该挂件接收一个分页对象，因此它知道当前的页数和应该展示多少页码按钮。
比如，

```php
use yii\widgets\LinkPager;

echo LinkPager::widget([
    'pagination' => $pagination,
]);
```

如果你想手动地创建 UI 元素，
你可以使用 [[yii\data\Pagination::createUrl()]] 来创建指向不同页面的 URLs 。
该方法需要一个页码来作为参数，
并且将创建一个包含页码并且格式正确的 URL，
例如，

```php
// 指定要被创建的 URL 应该使用的路由
// 如果不指定，则使用当前被请求的路由
$pagination->route = 'article/index';

// 显示: /index.php?r=article%2Findex&page=100
echo $pagination->createUrl(100);

// 显示: /index.php?r=article%2Findex&page=101
echo $pagination->createUrl(101);
```

> Tip: 创建分页对象时，你可以通过配置 [[yii\data\Pagination::pageParam|pageParam]] 属性来自定义查询参数 `page` 的名字。
