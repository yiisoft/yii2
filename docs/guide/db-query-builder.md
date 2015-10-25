Query Builder
=============

Built on top of [Database Access Objects](db-dao.md), query builder allows you to construct a SQL statement
in a programmatic and DBMS-agnostic way. Compared to writing raw SQL statements, using query builder will help you write 
more readable SQL-related code and generate more secure SQL statements.  

Using query builder usually involves two steps:

1. Build a [[yii\db\Query]] object to represent different parts (e.g. `SELECT`, `FROM`) of a SELECT SQL statement.
2. Execute a query method (e.g. `all()`) of [[yii\db\Query]] to retrieve data from the database.

The following code shows a typical way of using query builder:

```php
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->all();
```

The above code generates and executes the following SQL statement, where the `:last_name` parameter is bound with the
string `'Smith'`.

```sql
SELECT `id`, `email` 
FROM `user`
WHERE `last_name` = :last_name
LIMIT 10
```

> Info: You usually mainly work with [[yii\db\Query]] instead of [[yii\db\QueryBuilder]]. The latter is invoked
  by the former implicitly when you call one of the query methods. [[yii\db\QueryBuilder]] is the class responsible
  for generating DBMS-dependent SQL statements (e.g. quoting table/column names differently) from DBMS-independent
  [[yii\db\Query]] objects.


## Building Queries <span id="building-queries"></span>

To build a [[yii\db\Query]] object, you call different query building methods to specify different parts of
a SQL statement. The names of these methods resemble the SQL keywords used in the corresponding parts of the SQL
statement. For example, to specify the `FROM` part of a SQL statement, you would call the `from()` method.
All the query building methods return the query object itself, which allows you to chain multiple calls together.

In the following, we will describe the usage of each query building method.


### [[yii\db\Query::select()|select()]] <span id="select"></span>

The [[yii\db\Query::select()|select()]] method specifies the `SELECT` fragment of a SQL statement. You can specify 
columns to be selected in either an array or a string, like the following. The column names being selected will 
be automatically quoted when the SQL statement is being generated from a query object.
 
```php
$query->select(['id', 'email']);

// equivalent to:

$query->select('id, email');
```

The column names being selected may include table prefixes and/or column aliases, like you do when writing raw SQL statements. 
For example,

```php
$query->select(['user.id AS user_id', 'email']);

// equivalent to:

$query->select('user.id AS user_id, email');
```

If you are using the array format to specify columns, you can also use the array keys to specify the column aliases.
For example, the above code can be rewritten as follows,

```php
$query->select(['user_id' => 'user.id', 'email']);
```

If you do not call the [[yii\db\Query::select()|select()]] method when building a query, `*` will be selected, which
means selecting *all* columns.

Besides column names, you can also select DB expressions. You must use the array format when selecting a DB expression
that contains commas to avoid incorrect automatic name quoting. For example,

```php
$query->select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']); 
```

Starting from version 2.0.1, you may also select sub-queries. You should specify each sub-query in terms of 
a [[yii\db\Query]] object. For example,
 
```php
$subQuery = (new Query())->select('COUNT(*)')->from('user');

// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
$query = (new Query())->select(['id', 'count' => $subQuery])->from('post');
```

To select distinct rows, you may call [[yii\db\Query::distinct()|distinct()]], like the following:

```php
// SELECT DISTINCT `user_id` ...
$query->select('user_id')->distinct();
```

You can call [[yii\db\Query::addSelect()|addSelect()]] to select additional columns. For example,

```php
$query->select(['id', 'username'])
    ->addSelect(['email']);
```


### [[yii\db\Query::from()|from()]] <span id="from"></span>

The [[yii\db\Query::from()|from()]] method specifies the `FROM` fragment of a SQL statement. For example,

```php
// SELECT * FROM `user`
$query->from('user');
```

You can specify the table(s) being selected from in either a string or an array. The table names may contain
schema prefixes and/or table aliases, like you do when writing raw SQL statements. For example,

```php
$query->from(['public.user u', 'public.post p']);

// equivalent to:

$query->from('public.user u, public.post p');
```

If you are using the array format, you can also use the array keys to specify the table aliases, like the following:

```php
$query->from(['u' => 'public.user', 'p' => 'public.post']);
```

Besides table names, you can also select from sub-queries by specifying them in terms of [[yii\db\Query]] objects.
For example,

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');

// SELECT * FROM (SELECT `id` FROM `user` WHERE status=1) u 
$query->from(['u' => $subQuery]);
```


### [[yii\db\Query::where()|where()]] <span id="where"></span>

The [[yii\db\Query::where()|where()]] method specifies the `WHERE` fragment of a SQL statement. You can use one of
the three formats to specify a `WHERE` condition:

- string format, e.g., `'status=1'`
- hash format, e.g. `['status' => 1, 'type' => 2]`
- operator format, e.g. `['like', 'name', 'test']`


#### String Format <span id="string-format"></span>

String format is best used to specify very simple conditions. It works as if you are writing a raw SQL. For example,

```php
$query->where('status=1');

// or use parameter binding to bind dynamic parameter values
$query->where('status=:status', [':status' => $status]);
```

Do NOT embed variables directly in the condition like the following, especially if the variable values come from 
end user inputs, because this will make your application subject to SQL injection attacks.
 
```php
// Dangerous! Do NOT do this unless you are very certain $status must be an integer.
$query->where("status=$status");
```

When using parameter binding, you may call [[yii\db\Query::params()|params()]] or [[yii\db\Query::addParams()|addParams()]]
to specify parameters separately.

```php
$query->where('status=:status')
    ->addParams([':status' => $status]);
```


#### Hash Format <span id="hash-format"></span>

Hash format is best used to specify multiple `AND`-concatenated sub-conditions each being a simple equality assertion.
It is written as an array whose keys are column names and values the corresponding values that the columns should be.
For example,

```php
// ...WHERE (`status` = 10) AND (`type` IS NULL) AND (`id` IN (4, 8, 15))
$query->where([
    'status' => 10,
    'type' => null,
    'id' => [4, 8, 15],
]);
```

As you can see, the query builder is intelligent enough to properly handle values that are nulls or arrays.

You can also use sub-queries with hash format like the following:

```php
$userQuery = (new Query())->select('id')->from('user');

// ...WHERE `id` IN (SELECT `id` FROM `user`)
$query->where(['id' => $userQuery]);
```


#### Operator Format <span id="operator-format"></span>

Operator format allows you to specify arbitrary conditions in a programmatic way. It takes the following format:

```php
[operator, operand1, operand2, ...]
```

where the operands can each be specified in string format, hash format or operator format recursively, while
the operator can be one of the following:

- `and`: the operands should be concatenated together using `AND`. For example,
  `['and', 'id=1', 'id=2']` will generate `id=1 AND id=2`. If an operand is an array,
  it will be converted into a string using the rules described here. For example,
  `['and', 'type=1', ['or', 'id=1', 'id=2']]` will generate `type=1 AND (id=1 OR id=2)`.
  The method will NOT do any quoting or escaping.

- `or`: similar to the `and` operator except that the operands are concatenated using `OR`.

- `between`: operand 1 should be the column name, and operand 2 and 3 should be the
   starting and ending values of the range that the column is in.
   For example, `['between', 'id', 1, 10]` will generate `id BETWEEN 1 AND 10`.

- `not between`: similar to `between` except the `BETWEEN` is replaced with `NOT BETWEEN`
  in the generated condition.

- `in`: operand 1 should be a column or DB expression. Operand 2 can be either an array or a `Query` object.
  It will generate an `IN` condition. If Operand 2 is an array, it will represent the range of the values
  that the column or DB expression should be; If Operand 2 is a `Query` object, a sub-query will be generated
  and used as the range of the column or DB expression. For example,
  `['in', 'id', [1, 2, 3]]` will generate `id IN (1, 2, 3)`.
  The method will properly quote the column name and escape values in the range.
  The `in` operator also supports composite columns. In this case, operand 1 should be an array of the columns,
  while operand 2 should be an array of arrays or a `Query` object representing the range of the columns.

- `not in`: similar to the `in` operator except that `IN` is replaced with `NOT IN` in the generated condition.

- `like`: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
  the values that the column or DB expression should be like.
  For example, `['like', 'name', 'tester']` will generate `name LIKE '%tester%'`.
  When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
  using `AND`. For example, `['like', 'name', ['test', 'sample']]` will generate
  `name LIKE '%test%' AND name LIKE '%sample%'`.
  You may also provide an optional third operand to specify how to escape special characters in the values.
  The operand should be an array of mappings from the special characters to their
  escaped counterparts. If this operand is not provided, a default escape mapping will be used.
  You may use `false` or an empty array to indicate the values are already escaped and no escape
  should be applied. Note that when using an escape mapping (or the third operand is not provided),
  the values will be automatically enclosed within a pair of percentage characters.

  > Note: When using PostgreSQL you may also use [`ilike`](http://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE)
  > instead of `like` for case-insensitive matching.

- `or like`: similar to the `like` operator except that `OR` is used to concatenate the `LIKE`
  predicates when operand 2 is an array.

- `not like`: similar to the `like` operator except that `LIKE` is replaced with `NOT LIKE`
  in the generated condition.

- `or not like`: similar to the `not like` operator except that `OR` is used to concatenate
  the `NOT LIKE` predicates.

- `exists`: requires one operand which must be an instance of [[yii\db\Query]] representing the sub-query.
  It will build a `EXISTS (sub-query)` expression.

- `not exists`: similar to the `exists` operator and builds a `NOT EXISTS (sub-query)` expression.

- `>`, `<=`, or any other valid DB operator that takes two operands: the first operand must be a column name
  while the second operand a value. For example, `['>', 'age', 10]` will generate `age>10`.


#### Appending Conditions <span id="appending-conditions"></span>

You can use [[yii\db\Query::andWhere()|andWhere()]] or [[yii\db\Query::orWhere()|orWhere()]] to append
additional conditions to an existing one. You can call them multiple times to append multiple conditions
separately. For example,

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);

if (!empty($search)) {
    $query->andWhere(['like', 'title', $search]);
}
```

If `$search` is not empty, the following SQL statement will be generated:

```sql
... WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```


#### Filter Conditions <span id="filter-conditions"></span>

When building `WHERE` conditions based on input from end users, you usually want to ignore those empty input values.
For example, in a search form that allows you to search by username and email, you would like to ignore the username/email
condition if the user does not enter anything in the username/email input field. You can achieve this goal by
using the [[yii\db\Query::filterWhere()|filterWhere()]] method:

```php
// $username and $email are from user inputs
$query->filterWhere([
    'username' => $username,
    'email' => $email,
]);
```

The only difference between [[yii\db\Query::filterWhere()|filterWhere()]] and [[yii\db\Query::where()|where()]] 
is that the former will ignore empty values provided in the condition in [hash format](#hash-format). So if `$email`
is empty while `$username` is not, the above code will result in the SQL `...WHERE username=:username`.

> Info: A value is considered empty if it is null, an empty array, an empty string or a string consisting of whitespaces only.

Like [[yii\db\Query::andWhere()|andWhere()]] and [[yii\db\Query::orWhere()|orWhere()]], you can use
[[yii\db\Query::andFilterWhere()|andFilterWhere()]] and [[yii\db\Query::orFilterWhere()|orFilterWhere()]]
to append additional filter conditions to the existing one.


### [[yii\db\Query::orderBy()|orderBy()]] <span id="order-by"></span>

The [[yii\db\Query::orderBy()|orderBy()]] method specifies the `ORDER BY` fragment of a SQL statement. For example,
 
```php
// ... ORDER BY `id` ASC, `name` DESC
$query->orderBy([
    'id' => SORT_ASC,
    'name' => SORT_DESC,
]);
```
 
In the above code, the array keys are column names while the array values are the corresponding order-by directions.
The PHP constant `SORT_ASC` specifies ascending sort and `SORT_DESC` descending sort.

If `ORDER BY` only involves simple column names, you can specify it using a string, just like you do when writing 
raw SQL statements. For example,

```php
$query->orderBy('id ASC, name DESC');
```

> Note: You should use the array format if `ORDER BY` involves some DB expression. 

You can call [[yii\db\Query::addOrderBy()|addOrderBy()]] to add additional columns to the `ORDER BY` fragment.
For example,

```php
$query->orderBy('id ASC')
    ->addOrderBy('name DESC');
```


### [[yii\db\Query::groupBy()|groupBy()]] <span id="group-by"></span>

The [[yii\db\Query::groupBy()|groupBy()]] method specifies the `GROUP BY` fragment of a SQL statement. For example,

```php
// ... GROUP BY `id`, `status`
$query->groupBy(['id', 'status']);
```

If `GROUP BY` only involves simple column names, you can specify it using a string, just like you do when writing 
raw SQL statements. For example,

```php
$query->groupBy('id, status');
```

> Note: You should use the array format if `GROUP BY` involves some DB expression.
 
You can call [[yii\db\Query::addGroupBy()|addGroupBy()]] to add additional columns to the `GROUP BY` fragment.
For example,

```php
$query->groupBy(['id', 'status'])
    ->addGroupBy('age');
```


### [[yii\db\Query::having()|having()]] <span id="having"></span>

The [[yii\db\Query::having()|having()]] method specifies the `HAVING` fragment of a SQL statement. It takes
a condition which can be specified in the same way as that for [where()](#where). For example,

```php
// ... HAVING `status` = 1
$query->having(['status' => 1]);
```

Please refer to the documentation for [where()](#where) for more details about how to specify a condition.

You can call [[yii\db\Query::andHaving()|andHaving()]] or [[yii\db\Query::orHaving()|orHaving()]] to append
additional conditions to the `HAVING` fragment. For example,

```php
// ... HAVING (`status` = 1) AND (`age` > 30)
$query->having(['status' => 1])
    ->andHaving(['>', 'age', 30]);
```


### [[yii\db\Query::limit()|limit()]] and [[yii\db\Query::offset()|offset()]] <span id="limit-offset"></span>

The [[yii\db\Query::limit()|limit()]] and [[yii\db\Query::offset()|offset()]] methods specify the `LIMIT`
and `OFFSET` fragments of a SQL statement. For example,
 
```php
// ... LIMIT 10 OFFSET 20
$query->limit(10)->offset(20);
```

If you specify an invalid limit or offset (e.g. a negative value), it will be ignored.

> Info: For DBMS that do not support `LIMIT` and `OFFSET` (e.g. MSSQL), query builder will generate a SQL
  statement that emulates the `LIMIT`/`OFFSET` behavior.


### [[yii\db\Query::join()|join()]] <span id="join"></span>

The [[yii\db\Query::join()|join()]] method specifies the `JOIN` fragment of a SQL statement. For example,
 
```php
// ... LEFT JOIN `post` ON `post`.`user_id` = `user`.`id`
$query->join('LEFT JOIN', 'post', 'post.user_id = user.id');
```

The [[yii\db\Query::join()|join()]] method takes four parameters:
 
- `$type`: join type, e.g., `'INNER JOIN'`, `'LEFT JOIN'`.
- `$table`: the name of the table to be joined.
- `$on`: optional, the join condition, i.e., the `ON` fragment. Please refer to [where()](#where) for details
   about specifying a condition.
- `$params`: optional, the parameters to be bound to the join condition.

You can use the following shortcut methods to specify `INNER JOIN`, `LEFT JOIN` and `RIGHT JOIN`, respectively.

- [[yii\db\Query::innerJoin()|innerJoin()]]
- [[yii\db\Query::leftJoin()|leftJoin()]]
- [[yii\db\Query::rightJoin()|rightJoin()]]

For example,

```php
$query->leftJoin('post', 'post.user_id = user.id');
```

To join with multiple tables, call the above join methods multiple times, once for each table.

Besides joining with tables, you can also join with sub-queries. To do so, specify the sub-queries to be joined
as [[yii\db\Query]] objects. For example,

```php
$subQuery = (new \yii\db\Query())->from('post');
$query->leftJoin(['u' => $subQuery], 'u.id = author_id');
```

In this case, you should put the sub-query in an array and use the array key to specify the alias.


### [[yii\db\Query::union()|union()]] <span id="union"></span>

The [[yii\db\Query::union()|union()]] method specifies the `UNION` fragment of a SQL statement. For example,

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

You can call [[yii\db\Query::union()|union()]] multiple times to append more `UNION` fragments. 


## Query Methods <span id="query-methods"></span>

[[yii\db\Query]] provides a whole set of methods for different query purposes:

- [[yii\db\Query::all()|all()]]: returns an array of rows with each row being an associative array of name-value pairs.
- [[yii\db\Query::one()|one()]]: returns the first row of the result.
- [[yii\db\Query::column()|column()]]: returns the first column of the result.
- [[yii\db\Query::scalar()|scalar()]]: returns a scalar value located at the first row and first column of the result.
- [[yii\db\Query::exists()|exists()]]: returns a value indicating whether the query contains any result.
- [[yii\db\Query::count()|count()]]: returns the result of a `COUNT` query.
- Other aggregation query methods, including [[yii\db\Query::sum()|sum($q)]], [[yii\db\Query::average()|average($q)]],
  [[yii\db\Query::max()|max($q)]], [[yii\db\Query::min()|min($q)]]. The `$q` parameter is mandatory for these methods 
  and can be either a column name or a DB expression.

For example,

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

> Note: The [[yii\db\Query::one()|one()]] method only returns the first row of the query result. It does NOT
  add `LIMIT 1` to the generated SQL statement. This is fine and preferred if you know the query will return only one 
  or a few rows of data (e.g. if you are querying with some primary keys). However, if the query may potentially 
  result in many rows of data, you should call `limit(1)` explicitly to improve the performance, e.g.,
  `(new \yii\db\Query())->from('user')->limit(1)->one()`.

All these query methods take an optional `$db` parameter representing the [[yii\db\Connection|DB connection]] that
should be used to perform a DB query. If you omit this parameter, the `db` [application component](structure-application-components.md) will be used
as the DB connection. Below is another example using the `count()` query method:

```php
// executes SQL: SELECT COUNT(*) FROM `user` WHERE `last_name`=:last_name
$count = (new \yii\db\Query())
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->count();
```

When you call a query method of [[yii\db\Query]], it actually does the following work internally:

* Call [[yii\db\QueryBuilder]] to generate a SQL statement based on the current construct of [[yii\db\Query]];
* Create a [[yii\db\Command]] object with the generated SQL statement;
* Call a query method (e.g. `queryAll()`) of [[yii\db\Command]] to execute the SQL statement and retrieve the data.

Sometimes, you may want to examine or use the SQL statement built from a [[yii\db\Query]] object. You can
achieve this goal with the following code: 

```php
$command = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->createCommand();
    
// show the SQL statement
echo $command->sql;
// show the parameters to be bound
print_r($command->params);

// returns all rows of the query result
$rows = $command->queryAll();
```


### Indexing Query Results <span id="indexing-query-results"></span>

When you call [[yii\db\Query::all()|all()]], it will return an array of rows which are indexed by consecutive integers.
Sometimes you may want to index them differently, such as indexing by a particular column or expression values.
You can achieve this goal by calling [[yii\db\Query::indexBy()|indexBy()]] before [[yii\db\Query::all()|all()]].
For example,

```php
// returns [100 => ['id' => 100, 'username' => '...', ...], 101 => [...], 103 => [...], ...]
$query = (new \yii\db\Query())
    ->from('user')
    ->limit(10)
    ->indexBy('id')
    ->all();
```

To index by expression values, pass an anonymous function to the [[yii\db\Query::indexBy()|indexBy()]] method:

```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy(function ($row) {
        return $row['id'] . $row['username'];
    })->all();
```

The anonymous function takes a parameter `$row` which contains the current row data and should return a scalar
value which will be used as the index value for the current row.


### Batch Query <span id="batch-query"></span>

When working with large amounts of data, methods such as [[yii\db\Query::all()]] are not suitable
because they require loading all data into the memory. To keep the memory requirement low, Yii
provides the so-called batch query support. A batch query makes use of the data cursor and fetches
data in batches.

Batch query can be used like the following:

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->orderBy('id');

foreach ($query->batch() as $users) {
    // $users is an array of 100 or fewer rows from the user table
}

// or if you want to iterate the row one by one
foreach ($query->each() as $user) {
    // $user represents one row of data from the user table
}
```

The method [[yii\db\Query::batch()]] and [[yii\db\Query::each()]] return an [[yii\db\BatchQueryResult]] object
which implements the `Iterator` interface and thus can be used in the `foreach` construct.
During the first iteration, a SQL query is made to the database. Data are then fetched in batches
in the remaining iterations. By default, the batch size is 100, meaning 100 rows of data are being fetched in each batch.
You can change the batch size by passing the first parameter to the `batch()` or `each()` method.

Compared to the [[yii\db\Query::all()]], the batch query only loads 100 rows of data at a time into the memory.
If you process the data and then discard it right away, the batch query can help reduce memory usage.

If you specify the query result to be indexed by some column via [[yii\db\Query::indexBy()]], the batch query
will still keep the proper index. For example,

```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users is indexed by the "username" column
}

foreach ($query->each() as $username => $user) {
}
```
