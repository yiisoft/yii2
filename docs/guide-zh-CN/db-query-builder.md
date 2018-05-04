查询构建器
=============

查询构建器建立在 [Database Access Objects](db-dao.md) 基础之上，可让你创建
程序化的、DBMS无关的SQL语句。相比于原生的SQL语句，查询构建器可以帮你
写出可读性更强的SQL相关的代码，并生成安全性更强的SQL语句。

使用查询构建器通常包含以下两个步骤：

1. 创建一个 [[yii\db\Query]] 对象来代表一条 SELECT SQL 语句的不同子句（例如 `SELECT`, `FROM`）。
2. 执行 [[yii\db\Query]] 的一个查询方法（例如：`all()`）从数据库当中检索数据。

如下所示代码是查询构造器的一个典型用法：

```php
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->all();
```

上面的代码将会生成并执行如下的SQL语句，其中 `:last_name` 参数绑定了
字符串 `'Smith'`。

```sql
SELECT `id`, `email` 
FROM `user`
WHERE `last_name` = :last_name
LIMIT 10
```

> Tip: 你平时更多的时候会使用 [[yii\db\Query]] 而不是 [yii\db\QueryBuilder]]。
  当你调用其中一个查询方法时，后者将会被前者隐式的调用。[[yii\db\QueryBuilder]]主要负责将
  DBMS 不相关的 [[yii\db\Query]] 对象转换成 DBMS 相关的 SQL 语句（例如，
  以不同的方式引用表或字段名称）。


## 创建查询 <span id="building-queries"></span>

为了创建一个 [[yii\db\Query]] 对象，你需要调用不同的查询构建方法来代表SQL语句的不同子句。
这些方法的名称集成了在SQL语句相应子句中使用的关键字。例如，为了指定 SQL 语句当中的
`FROM` 子句，你应该调用 `from()` 方法。所有的查询构建器方法返回的是查询对象本身，
也就是说，你可以把多个方法的调用串联起来。

接下来，我们会对这些查询构建器方法进行一一讲解：


### [[yii\db\Query::select()|select()]] <span id="select"></span>

[[yii\db\Query::select()|select()]] 方法用来指定 SQL 语句当中的 `SELECT` 子句。
你可以像下面的例子一样使用一个数组或者字符串来定义需要查询的字段。当 SQL 语句
是由查询对象生成的时候，被查询的字段名称将会自动的被引号括起来。
 
```php
$query->select(['id', 'email']);

// 等同于：

$query->select('id, email');
```

就像写原生 SQL 语句一样，被选取的字段可以包含表前缀，以及/或者字段别名。
例如： 

```php
$query->select(['user.id AS user_id', 'email']);

// 等同于：

$query->select('user.id AS user_id, email');
```

如果使用数组格式来指定字段，你可以使用数组的键值来表示字段的别名。
例如，上面的代码可以被重写为如下形式：

```php
$query->select(['user_id' => 'user.id', 'email']);
```

如果你在组建查询时没有调用 [[yii\db\Query::select()|select()]] 方法，那么选择的将是 `'*'` ，
也即选取的是所有的字段。

除了字段名称以外，你还可以选择数据库的表达式。当你使用到包含逗号的数据库表达式的时候，
你必须使用数组的格式，以避免自动的错误的引号添加。例如：

```php
$query->select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']); 
```

As with all places where raw SQL is involved, you may use the [DBMS agnostic quoting syntax](db-dao.md#quoting-table-and-column-names)
for table and column names when writing DB expressions in select.

从 2.0.1 的版本开始你就可以使用子查询了。在定义每一个子查询的时候，
你应该使用 [[yii\db\Query]] 对象。例如：
 
```php
$subQuery = (new Query())->select('COUNT(*)')->from('user');

// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
$query = (new Query())->select(['id', 'count' => $subQuery])->from('post');
```

你应该调用 [[yii\db\Query::distinct()|distinct()]] 方法来去除重复行，如下所示：

```php
// SELECT DISTINCT `user_id` ...
$query->select('user_id')->distinct();
```

你可以调用 [[yii\db\Query::addSelect()|addSelect()]] 方法来选取附加字段，例如：

```php
$query->select(['id', 'username'])
    ->addSelect(['email']);
```


### [[yii\db\Query::from()|from()]] <span id="from"></span>

[[yii\db\Query::from()|from()]] 方法指定了 SQL 语句当中的 `FROM` 子句。例如：

```php
// SELECT * FROM `user`
$query->from('user');
```

你可以通过字符串或者数组的形式来定义被查询的表名称。就像你写原生的 SQL 语句一样，
表名称里面可包含数据库前缀，以及/或者表别名。例如：

```php
$query->from(['public.user u', 'public.post p']);

// 等同于：

$query->from('public.user u, public.post p');
```

如果你使用的是数组的格式，那么你同样可以用数组的键值来定义表别名，如下所示：

```php
$query->from(['u' => 'public.user', 'p' => 'public.post']);
```

除了表名以外，你还可以从子查询中再次查询，这里的子查询是由 [[yii\db\Query]] 创建的对象。
例如：

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');

// SELECT * FROM (SELECT `id` FROM `user` WHERE status=1) u 
$query->from(['u' => $subQuery]);
```


### [[yii\db\Query::where()|where()]] <span id="where"></span>

[[yii\db\Query::where()|where()]] 方法定义了 SQL 语句当中的 `WHERE` 子句。
你可以使用如下三种格式来定义 `WHERE` 条件：

- 字符串格式，例如：`'status=1'`
- 哈希格式，例如： `['status' => 1, 'type' => 2]`
- 操作符格式，例如：`['like', 'name', 'test']`


#### 字符串格式 <span id="string-format"></span>

在定义非常简单的查询条件的时候，字符串格式是最合适的。
它看起来和原生 SQL 语句差不多。例如：

```php
$query->where('status=1');

// or use parameter binding to bind dynamic parameter values
$query->where('status=:status', [':status' => $status]);

// raw SQL using MySQL YEAR() function on a date field
$query->where('YEAR(somedate) = 2015');
```

千万不要像如下的例子一样直接在条件语句当中嵌入变量，特别是当这些变量来源于终端用户输入的时候，
因为这样我们的软件将很容易受到 SQL 注入的攻击。
 
```php
// 危险！千万别这样干，除非你非常的确定 $status 是一个整型数值。
$query->where("status=$status");
```

当使用参数绑定的时候，你可以调用 [[yii\db\Query::params()|params()]] 或者 [[yii\db\Query::addParams()|addParams()]] 方法
来分别绑定不同的参数。

```php
$query->where('status=:status')
    ->addParams([':status' => $status]);
```

As with all places where raw SQL is involved, you may use the [DBMS agnostic quoting syntax](db-dao.md#quoting-table-and-column-names)
for table and column names when writing conditions in string format. 

#### 哈希格式 <span id="hash-format"></span>

哈希格式最适合用来指定多个 `AND` 串联起来的简单的"等于断言"子条件。
它是以数组的形式来书写的，数组的键表示字段的名称，而数组的值则表示
这个字段需要匹配的值。例如：

```php
// ...WHERE (`status` = 10) AND (`type` IS NULL) AND (`id` IN (4, 8, 15))
$query->where([
    'status' => 10,
    'type' => null,
    'id' => [4, 8, 15],
]);
```

就像你所看到的一样，查询构建器非常的智能，能恰当地处理数值当中的空值和数组。

你也可以像下面那样在子查询当中使用哈希格式： 

```php
$userQuery = (new Query())->select('id')->from('user');

// ...WHERE `id` IN (SELECT `id` FROM `user`)
$query->where(['id' => $userQuery]);
```

Using the Hash Format, Yii internally uses parameter binding so in contrast to the [string format](#string-format), here
you do not have to add parameters manually.


#### 操作符格式 <span id="operator-format"></span>

操作符格式允许你指定类程序风格的任意条件语句，如下所示：

```php
[操作符, 操作数1, 操作数2, ...]
```

其中每个操作数可以是字符串格式、哈希格式或者嵌套的操作符格式，
而操作符可以是如下列表中的一个：

- `and`: 操作数会被 `AND` 关键字串联起来。例如，`['and', 'id=1', 'id=2']` 
  将会生成 `id=1 AND id=2`。如果操作数是一个数组，它也会按上述规则转换成
  字符串。例如，`['and', 'type=1', ['or', 'id=1', 'id=2']]` 
  将会生成 `type=1 AND (id=1 OR id=2)`。
  这个方法不会自动加引号或者转义。
  
- `or`: 用法和 `and` 操作符类似，这里就不再赘述。

- `between`: 第一个操作数为字段名称，第二个和第三个操作数代表的是这个字段
  的取值范围。例如，`['between', 'id', 1, 10]` 将会生成
  `id BETWEEN 1 AND 10`。

- `not between`: similar to `between` except the `BETWEEN` is replaced with `NOT BETWEEN`
  in the generated condition.

- `in`: 第一个操作数应为字段名称或者 DB 表达式。第二个操作符既可以是一个数组，
  也可以是一个  `Query` 对象。它会转换成`IN` 条件语句。如果第二个操作数是一个
  数组，那么它代表的是字段或 DB 表达式的取值范围。如果第二个操作数是 `Query` 
  对象，那么这个子查询的结果集将会作为第一个操作符的字段或者 DB 表达式的取值范围。
  例如， `['in', 'id', [1, 2, 3]]` 将生成 `id IN (1, 2, 3)`。
  该方法将正确地为字段名加引号以及为取值范围转义。`in` 操作符还支持组合字段，此时，
  操作数1应该是一个字段名数组，而操作数2应该是一个数组或者 `Query` 对象，
  代表这些字段的取值范围。

- `not in`: 用法和 `in` 操作符类似，这里就不再赘述。

- `like`: 第一个操作数应为一个字段名称或 DB 表达式，
  第二个操作数可以使字符串或数组，
  代表第一个操作数需要模糊查询的值。比如，`['like', 'name', 'tester']` 会生成
  `name LIKE '%tester%'`。 如果范围值是一个数组，那么将会生成用 `AND` 串联起来的
  多个 `like` 语句。例如，`['like', 'name', ['test', 'sample']]` 将会生成
  `name LIKE '%test%' AND name LIKE '%sample%'`。
  你也可以提供第三个可选的操作数来指定应该如何转义数值当中的特殊字符。
  该操作数是一个从需要被转义的特殊字符到转义副本的数组映射。
  如果没有提供这个操作数，将会使用默认的转义映射。如果需要禁用转义的功能，
  只需要将参数设置为 `false` 或者传入一个空数组即可。需要注意的是，
  当使用转义映射（又或者没有提供第三个操作数的时候），第二个操作数的值的前后
  将会被加上百分号。

> Note: 当使用 PostgreSQL 的时候你还可以使用 [`ilike`](http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE)，
> 该方法对大小写不敏感。

- `or like`: 用法和 `like` 操作符类似，区别在于当第二个操作数为数组时，
  会使用 `OR` 来串联多个 `LIKE` 条件语句。

- `not like`: 用法和 `like` 操作符类似，区别在于会使用 `NOT LIKE`
  来生成条件语句。

- `or not like`: 用法和 `not like` 操作符类似，区别在于会使用 `OR` 
  来串联多个 `NOT LIKE` 条件语句。

- `exists`: 需要一个操作数，该操作数必须是代表子查询 [[yii\db\Query]] 的一个实例，
  它将会构建一个 `EXISTS (sub-query)` 表达式。

- `not exists`: 用法和 `exists` 操作符类似，它将创建一个  `NOT EXISTS (sub-query)` 表达式。

- `>`, `<=`, 或者其他包含两个操作数的合法 DB 操作符: 第一个操作数必须为字段的名称，
  而第二个操作数则应为一个值。例如，`['>', 'age', 10]` 将会生成 `age>10`。

Using the Operator Format, Yii internally uses parameter binding so in contrast to the [string format](#string-format), here
you do not have to add parameters manually.


#### 附加条件 <span id="appending-conditions"></span>

你可以使用 [[yii\db\Query::andWhere()|andWhere()]] 或者 [[yii\db\Query::orWhere()|orWhere()]] 在原有条件的基础上
附加额外的条件。你可以多次调用这些方法来分别追加不同的条件。
例如，

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);

if (!empty($search)) {
    $query->andWhere(['like', 'title', $search]);
}
```

如果 `$search` 不为空，那么将会生成如下 SQL 语句：

```sql
... WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```


#### 过滤条件 <span id="filter-conditions"></span>

当 `WHERE` 条件来自于用户的输入时，你通常需要忽略用户输入的空值。
例如，在一个可以通过用户名或者邮箱搜索的表单当中，用户名或者邮箱
输入框没有输入任何东西，这种情况下你想要忽略掉对应的搜索条件，
那么你就可以使用 [[yii\db\Query::filterWhere()|filterWhere()]] 方法来实现这个目的：

```php
// $username 和 $email 来自于用户的输入
$query->filterWhere([
    'username' => $username,
    'email' => $email,		
]);
```

[[yii\db\Query::filterWhere()|filterWhere()]] 和 [[yii\db\Query::where()|where()]] 唯一的不同就在于，前者
将忽略在条件当中的[hash format](#hash-format)的空值。所以如果 `$email` 为空而 `$username` 
不为空，那么上面的代码最终将生产如下 SQL `...WHERE username=:username`。 

> Tip: 当一个值为 null、空数组、空字符串或者一个只包含空白字符时，那么它将被判定为空值。

类似于 [yii\db\Query::andWhere()|andWhere()]] 和 [[yii\db\Query::orWhere()|orWhere()]],
你可以使用 [[yii\db\Query::andFilterWhere()|andFilterWhere()]] 和 [[yii\db\Query::orFilterWhere()|orFilterWhere()]] 方法
来追加额外的过滤条件。

Additionally, there is [[yii\db\Query::andFilterCompare()]] that can intelligently determine operator based on what's
in the value:

```php
$query->andFilterCompare('name', 'John Doe');
$query->andFilterCompare('rating', '>9');
$query->andFilterCompare('value', '<=100');
```

You can also specify operator explicitly:

```php
$query->andFilterCompare('name', 'Doe', 'like');
```

### [[yii\db\Query::orderBy()|orderBy()]] <span id="order-by"></span>

[[yii\db\Query::orderBy()|orderBy()]] 方法是用来指定 SQL 语句当中的 `ORDER BY` 子句的。例如，

```php
// ... ORDER BY `id` ASC, `name` DESC
$query->orderBy([
    'id' => SORT_ASC,
    'name' => SORT_DESC,
]);
```

如上所示，数组当中的键指代的是字段名称，而数组当中的值则表示的是排序的方式。
PHP 的常量 `SORT_ASC` 指的是升序排列，`SORT_DESC` 指的则是降序排列。

如果 `ORDER BY` 仅仅包含简单的字段名称，你可以使用字符串来声明它，
就像写原生的 SQL 语句一样。例如，

```php
$query->orderBy('id ASC, name DESC');
```

> Note: 当 `ORDER BY` 语句包含一些 DB 表达式的时候，你应该使用数组的格式。

你可以调用 [yii\db\Query::addOrderBy()|addOrderBy()]] 来为 `ORDER BY` 片断添加额外的子句。
例如，

```php
$query->orderBy('id ASC')
    ->addOrderBy('name DESC');
```


### [[yii\db\Query::groupBy()|groupBy()]] <span id="group-by"></span>

[[yii\db\Query::groupBy()|groupBy()]] 方法是用来指定 SQL 语句当中的 `GROUP BY` 片断的。例如，

```php
// ... GROUP BY `id`, `status`
$query->groupBy(['id', 'status']);
```

如果 `GROUP BY` 仅仅包含简单的字段名称，你可以使用字符串来声明它，
就像写原生的 SQL 语句一样。例如，

```php
$query->groupBy('id, status');
```

> Note: 当 `GROUP BY` 语句包含一些 DB 表达式的时候，你应该使用数组的格式。

你可以调用 [yii\db\Query::addOrderBy()|addOrderBy()]] 来为 `GROUP BY` 
子句添加额外的字段。例如，

```php
$query->groupBy(['id', 'status'])
    ->addGroupBy('age');
```


### [[yii\db\Query::having()|having()]] <span id="having"></span>

[[yii\db\Query::having()|having()]] 方法是用来指定 SQL 语句当中的 `HAVING` 子句。它带有一个条件，
和 [where()](#where) 中指定条件的方法一样。例如，

```php
// ... HAVING `status` = 1
$query->having(['status' => 1]);
```

请查阅 [where()](#where) 的文档来获取更多有关于如何指定一个条件的细节。

你可以调用 [[yii\db\Query::andHaving()|andHaving()]] 或者 [[yii\db\Query::orHaving()|orHaving()]] 
方法来为 `HAVING` 子句追加额外的条件，例如，

```php
// ... HAVING (`status` = 1) AND (`age` > 30)
$query->having(['status' => 1])
    ->andHaving(['>', 'age', 30]);
```


### [[yii\db\Query::limit()|limit()]] 和 [[yii\db\Query::offset()|offset()]] <span id="limit-offset"></span>

[[yii\db\Query::limit()|limit()]] 和 [[yii\db\Query::offset()|offset()]] 是用来指定 SQL 语句当中
的 `LIMIT` 和 `OFFSET` 子句的。例如，
 
```php
// ... LIMIT 10 OFFSET 20
$query->limit(10)->offset(20);
```

如果你指定了一个无效的 limit 或者 offset（例如，一个负数），那么它将会被忽略掉。

> Tip: 在不支持 `LIMIT` 和 `OFFSET` 的 DBMS 中（例如，MSSQL），
  查询构建器将生成一条模拟 `LIMIT`/`OFFSET` 行为的 SQL 语句。


### [[yii\db\Query::join()|join()]] <span id="join"></span>

[yii\db\Query::join()|join()]] 是用来指定 SQL 语句当中的 `JOIN` 子句的。例如，
 
```php
// ... LEFT JOIN `post` ON `post`.`user_id` = `user`.`id`
$query->join('LEFT JOIN', 'post', 'post.user_id = user.id');
```

[[yii\db\Query::join()|join()]] 带有四个参数：
 
- `$type`: 连接类型，例如：`'INNER JOIN'`, `'LEFT JOIN'`。
- `$table`: 将要连接的表名称。
- `$on`: optional, the join condition, i.e., the `ON` fragment. Please refer to [where()](#where) for details
   about specifying a condition. Note, that the array syntax does **not** work for specifying a column based
   condition, e.g. `['user.id' => 'comment.userId']` will result in a condition where the user id must be equal
   to the string `'comment.userId'`. You should use the string syntax instead and specify the condition as
   `'user.id = comment.userId'`.
- `$params`: 可选参数，与连接条件绑定的参数。

你可以分别调用如下的快捷方法来指定 `INNER JOIN`, `LEFT JOIN` 和 `RIGHT JOIN`。

- [[yii\db\Query::innerJoin()|innerJoin()]]
- [[yii\db\Query::leftJoin()|leftJoin()]]
- [[yii\db\Query::rightJoin()|rightJoin()]]

例如，

```php
$query->leftJoin('post', 'post.user_id = user.id');
```

可以通过多次调用如上所述的连接方法来连接多张表，每连接一张表调用一次。

除了连接表以外，你还可以连接子查询。方法如下，将需要被连接的子查询指定
为一个 [[yii\db\Query]] 对象，例如，

```php
$subQuery = (new \yii\db\Query())->from('post');
$query->leftJoin(['u' => $subQuery], 'u.id = author_id');
```

在这个例子当中，你应该将子查询放到一个数组当中，而数组当中的键，则为这个子查询的别名。


### [[yii\db\Query::union()|union()]] <span id="union"></span>

[[yii\db\Query::union()|union()]] 方法是用来指定 SQL 语句当中的 `UNION` 子句的。例如，

```php
$query1 = (new \yii\db\Query())
    ->select("id, category_id AS type, name")
    ->from('post')
    ->limit(10);

$query2 = (new \yii\db\Query())
    ->select('id, type, name')
    ->from('user')
    ->limit(10);

$query1->union($query2);
```

你可以通过多次调用 [[yii\db\Query::union()|union()]] 方法来追加更多的 `UNION` 子句。


## 查询方法 <span id="query-methods"></span>

[[yii\db\Query]] 提供了一整套的用于不同查询目的的方法。

- [[yii\db\Query::all()|all()]]：将返回一个由行组成的数组，每一行是一个由名称和值构成的关联数组（译者注：省略键的数组称为索引数组）。
- [[yii\db\Query::one()|one()]]：返回结果集的第一行。
- [[yii\db\Query::column()|column()]]：返回结果集的第一列。
- [[yii\db\Query::scalar()|scalar()]]：返回结果集的第一行第一列的标量值。
- [[yii\db\Query::exists()|exists()]]：返回一个表示该查询是否包结果集的值。
- [[yii\db\Query::count()|count()]]：返回 `COUNT` 查询的结果。
- 其它集合查询方法：包括 [[yii\db\Query::sum()|sum($q)]], [[yii\db\Query::average()|average($q)]],
  [[yii\db\Query::max()|max($q)]], [[yii\db\Query::min()|min($q)]] 等。`$q` 是一个必选参数，
  既可以是一个字段名称，又可以是一个 DB 表达式。

例如，

```php
// SELECT `id`, `email` FROM `user`
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->all();
    
// SELECT * FROM `user` WHERE `username` LIKE `%test%`
$row = (new \yii\db\Query())
    ->from('user')
    ->where(['like', 'username', 'test'])
    ->one();
```

> Note: [[yii\db\Query::one()|one()]] 方法只返回查询结果当中的第一条数据，
  条件语句中不会加上 `LIMIT 1` 条件。如果你清楚的知道查询将会只返回一行或几行数据
  （例如， 如果你是通过某些主键来查询的），这很好也提倡这样做。但是，如果查询结果
  有机会返回大量的数据时，那么你应该显示调用 `limit(1)` 方法，以改善性能。
  例如， `(new \yii\db\Query())->from('user')->limit(1)->one()`。

所有的这些查询方法都有一个可选的参数 `$db`, 该参数指代的是 [[yii\db\Connection|DB connection]]，
执行一个 DB 查询时会用到。如果你省略了这个参数，那么 `db` [application component](structure-application-components.md) 将会被用作
默认的 DB 连接。 如下是另外一个使用 `count()` 查询的例子：

```php
// 执行 SQL: SELECT COUNT(*) FROM `user` WHERE `last_name`=:last_name
$count = (new \yii\db\Query())
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->count();
```

当你调用 [[yii\db\Query]] 当中的一个查询方法的时候，实际上内在的运作机制如下： 

* 在当前 [[yii\db\Query]] 的构造基础之上，调用 [[yii\db\QueryBuilder]] 来生成一条 SQL 语句；
* 利用生成的 SQL 语句创建一个 [[yii\db\Command]] 对象； 
* 调用 [[yii\db\Command]] 的查询方法（例如，`queryAll()`）来执行这条 SQL 语句，并检索数据。

有时候，你也许想要测试或者使用一个由 [[yii\db\Query]] 对象创建的 SQL 语句。
你可以使用以下的代码来达到目的：

```php
$command = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->createCommand();
    
// 打印 SQL 语句
echo $command->sql;
// 打印被绑定的参数
print_r($command->params);

// 返回查询结果的所有行
$rows = $command->queryAll();
```


### 索引查询结果 <span id="indexing-query-results"></span>

当你在调用 [[yii\db\Query::all()|all()]] 方法时，它将返回一个以连续的整型数值为索引的数组。
而有时候你可能希望使用一个特定的字段或者表达式的值来作为索引结果集数组。那么你可以在调用 [[yii\db\Query::all()|all()]] 
之前使用 [[yii\db\Query::indexBy()|indexBy()]] 方法来达到这个目的。
例如，

```php
// 返回 [100 => ['id' => 100, 'username' => '...', ...], 101 => [...], 103 => [...], ...]
$query = (new \yii\db\Query())
    ->from('user')
    ->limit(10)
    ->indexBy('id')
    ->all();
```

如需使用表达式的值做为索引，那么只需要传递一个匿名函数给 [[yii\db\Query::indexBy()|indexBy()]] 方法即可：

```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy(function ($row) {
        return $row['id'] . $row['username'];
    })->all();
```

该匿名函数将带有一个包含了当前行的数据的 `$row` 参数，并且返回用作当前行索引的
标量值（译者注：就是简单的数值或者字符串，而不是其他复杂结构，例如数组）。

> Note: In contrast to query methods like [[yii\db\Query::groupBy()|groupBy()]] or [[yii\db\Query::orderBy()|orderBy()]]
> which are converted to SQL and are part of the query, this method works after the data has been fetched from the database.
> That means that only those column names can be used that have been part of SELECT in your query.
> Also if you selected a column with table prefix, e.g. `customer.id`, the result set will only contain `id` so you have to call
> `->indexBy('id')` without table prefix.


### 批处理查询 <span id="batch-query"></span>

当需要处理大数据的时候，像 [[yii\db\Query::all()]] 这样的方法就不太合适了，
因为它们会把所有数据都读取到内存上。为了保持较低的内存需求， Yii 提供了一个
所谓的批处理查询的支持。批处理查询会利用数据游标
将数据以批为单位取出来。

批处理查询的用法如下：

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->orderBy('id');

foreach ($query->batch() as $users) {
    // $users 是一个包含100条或小于100条用户表数据的数组
}

// or if you want to iterate the row one by one
foreach ($query->each() as $user) {
    // $user 指代的是用户表当中的其中一行数据
}
```

[[yii\db\Query::batch()]] 和 [[yii\db\Query::each()]] 方法将会返回一个实现了`Iterator` 
接口 [[yii\db\BatchQueryResult]]  的对象，可以用在 `foreach` 结构当中使用。在第一次迭代取数据的时候，
数据库会执行一次 SQL 查询，然后在剩下的迭代中，将直接从结果集中批量获取数据。默认情况下，
一批的大小为 100，也就意味着一批获取的数据是 100 行。你可以通过给 `batch()` 
或者 `each()` 方法的第一个参数传值来改变每批行数的大小。

相对于 [[yii\db\Query::all()]] 方法，批处理查询每次只读取 100 行的数据到内存。
如果你在处理完这些数据后及时丢弃这些数据，那么批处理查询可以很好的帮助降低内存的占用率。

如果你通过 [[yii\db\Query::indexBy()]] 方法为查询结果指定了索引字段，
那么批处理查询将仍然保持相对应的索引方案，例如，


```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users 的 “username” 字段将会成为索引
}

foreach ($query->each() as $username => $user) {
}
```
