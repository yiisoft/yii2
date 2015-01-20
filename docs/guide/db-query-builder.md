Query Builder and Query
=======================

> Note: This section is under development.

Yii provides a basic database access layer as described in the [Database basics](db-dao.md) section.
The database access layer provides a low-level way to interact with the database. While useful in some situations,
it can be tedious and error-prone to write raw SQLs. An alternative approach is to use the Query Builder.
The Query Builder provides an object-oriented vehicle for generating queries to be executed.

A typical usage of the query builder looks like the following:

```php
$rows = (new \yii\db\Query())
    ->select('id, name')
    ->from('user')
    ->limit(10)
    ->all();

// which is equivalent to the following code:

$query = (new \yii\db\Query())
    ->select('id, name')
    ->from('user')
    ->limit(10);

// Create a command. You can get the actual SQL using $command->sql
$command = $query->createCommand();

// Execute the command:
$rows = $command->queryAll();
```

Query Methods
-------------

As you can see, [[yii\db\Query]] is the main player that you need to deal with. Behind the scenes,
`Query` is actually only responsible for representing various query information. The actual query
building logic is done by [[yii\db\QueryBuilder]] when you call the `createCommand()` method,
and the query execution is done by [[yii\db\Command]].

For convenience, [[yii\db\Query]] provides a set of commonly used query methods that will build
the query, execute it, and return the result. For example,

- [[yii\db\Query::all()|all()]]: builds the query, executes it and returns all results as an array.
- [[yii\db\Query::one()|one()]]: returns the first row of the result.
- [[yii\db\Query::column()|column()]]: returns the first column of the result.
- [[yii\db\Query::scalar()|scalar()]]: returns the first column in the first row of the result.
- [[yii\db\Query::exists()|exists()]]: returns a value indicating whether the query results in anything.
- [[yii\db\Query::count()|count()]]: returns the result of a `COUNT` query. Other similar methods
  include `sum($q)`, `average($q)`, `max($q)` and `min($q)`, which support the so-called aggregational data query. The `$q`
  parameter is mandatory for these methods and can be either the column name or expression.


Building Query
--------------

In the following, we will explain how to build various clauses in a SQL statement. For simplicity,
we use `$query` to represent a [[yii\db\Query]] object.


### `SELECT`

In order to form a basic `SELECT` query, you need to specify what columns to select and from what table:

```php
$query->select('id, name')
    ->from('user');
```

Select options can be specified as a comma-separated string, as in the above, or as an array.
The array syntax is especially useful when forming the selection dynamically:

```php
$query->select(['id', 'name'])
    ->from('user');
```

> Info: You should always use the array format if your `SELECT` clause contains SQL expressions.
> This is because a SQL expression like `CONCAT(first_name, last_name) AS full_name` may contain commas.
> If you list it together with other columns in a string, the expression may be split into several parts
> by commas, which is not what you want to see.

When specifying columns, you may include the table prefixes or column aliases, e.g., `user.id`, `user.id AS user_id`.
If you are using an array to specify the columns, you may also use the array keys to specify the column aliases,
e.g., `['user_id' => 'user.id', 'user_name' => 'user.name']`.

Starting from version 2.0.1, you may also select sub-queries as columns. For example,
 
```php
$subQuery = (new Query)->select('COUNT(*)')->from('user');
$query = (new Query)->select(['id', 'count' => $subQuery])->from('post');
// $query represents the following SQL:
// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
```

To select distinct rows, you may call `distinct()`, like the following:

```php
$query->select('user_id')->distinct()->from('post');
```

### `FROM`

To specify which table(s) to select data from, call `from()`:

```php
$query->select('*')->from('user');
```

You may specify multiple tables using a comma-separated string or an array.
Table names can contain schema prefixes (e.g. `'public.user'`) and/or table aliases (e.g. `'user u'`).
The method will automatically quote the table names unless it contains some parenthesis
(which means the table is given as a sub-query or DB expression). For example,

```php
$query->select('u.*, p.*')->from(['user u', 'post p']);
```

When the tables are specified as an array, you may also use the array keys as the table aliases
(if a table does not need an alias, do not use a string key). For example,

```php
$query->select('u.*, p.*')->from(['u' => 'user', 'p' => 'post']);
```

You may specify a sub-query using a `Query` object. In this case, the corresponding array key will be used
as the alias for the sub-query.

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');
$query->select('*')->from(['u' => $subQuery]);
```


### `WHERE`

Usually data is selected based upon certain criteria. Query Builder has some useful methods to specify these, the most powerful of which being `where`. It can be used in multiple ways.

The simplest way to apply a condition is to use a string:

```php
$query->where('status=:status', [':status' => $status]);
```

When using strings, make sure you're binding the query parameters, not creating a query by string concatenation. The above approach is safe to use, the following is not:

```php
$query->where("status=$status"); // Dangerous!
```

Instead of binding the status value immediately, you can do so using `params` or `addParams`:

```php
$query->where('status=:status');
$query->addParams([':status' => $status]);
```

Multiple conditions can simultaneously be set in `where` using the *hash format*:

```php
$query->where([
    'status' => 10,
    'type' => 2,
    'id' => [4, 8, 15, 16, 23, 42],
]);
```

That code will generate the following SQL:

```sql
WHERE (`status` = 10) AND (`type` = 2) AND (`id` IN (4, 8, 15, 16, 23, 42))
```

NULL is a special value in databases, and is handled smartly by the Query Builder. This code:

```php
$query->where(['status' => null]);
```

results in this WHERE clause:

```sql
WHERE (`status` IS NULL)
```

If you need `IS NOT NULL` you can use the following:

```php
$query->where(['not', ['col' => null]]);
```

You can also create sub-queries with `Query` objects like the following,

```php
$userQuery = (new Query)->select('id')->from('user');
$query->where(['id' => $userQuery]);
```

which will generate the following SQL:

```sql
WHERE `id` IN (SELECT `id` FROM `user`)
```


Another way to use the method is the operand format which is `[operator, operand1, operand2, ...]`.

Operator can be one of the following (see also [[yii\db\QueryInterface::where()]]):

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

Additionally you can specify anything as operator:

```php
$query->select('id')
    ->from('user')
    ->where(['>=', 'id', 10]);
```

It will result in:

```sql
SELECT id FROM user WHERE id >= 10;
```

If you are building parts of condition dynamically it's very convenient to use `andWhere()` and `orWhere()`:

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);
if (!empty($search)) {
    $query->andWhere(['like', 'title', $search]);
}
```

In case `$search` isn't empty the following SQL will be generated:

```sql
WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```

#### Building Filter Conditions

When building filter conditions based on user inputs, you usually want to specially handle "empty inputs"
by ignoring them in the filters. For example, you have an HTML form that takes username and email inputs.
If the user only enters something in the username input, you may want to build a query that only tries to
match the entered username. You may use the `filterWhere()` method to achieve this goal:

```php
// $username and $email are from user inputs
$query->filterWhere([
    'username' => $username,
    'email' => $email,
]);
```

The `filterWhere()` method is very similar to `where()`. The main difference is that `filterWhere()`
will remove empty values from the provided condition. So if `$email` is "empty", the resulting query
will be `...WHERE username=:username`; and if both `$username` and `$email` are "empty", the query
will have no `WHERE` part.

A value is *empty* if it is null, an empty string, a string consisting of whitespaces, or an empty array.

You may also use `andFilterWhere()` and `orFilterWhere()` to append more filter conditions.


### `ORDER BY`

For ordering results `orderBy` and `addOrderBy` could be used:

```php
$query->orderBy([
    'id' => SORT_ASC,
    'name' => SORT_DESC,
]);
```

Here we are ordering by `id` ascending and then by `name` descending.

### `GROUP BY` and `HAVING`

In order to add `GROUP BY` to generated SQL you can use the following:

```php
$query->groupBy('id, status');
```

If you want to add another field after using `groupBy`:

```php
$query->addGroupBy(['created_at', 'updated_at']);
```

To add a `HAVING` condition the corresponding `having` method and its `andHaving` and `orHaving` can be used. Parameters
for these are similar to the ones for `where` methods group:

```php
$query->having(['status' => $status]);
```

### `LIMIT` and `OFFSET`

To limit result to 10 rows `limit` can be used:

```php
$query->limit(10);
```

To skip 100 fist rows use:

```php
$query->offset(100);
```

### `JOIN`

The `JOIN` clauses are generated in the Query Builder by using the applicable join method:

- `innerJoin()`
- `leftJoin()`
- `rightJoin()`

This left join selects data from two related tables in one query:

```php
$query->select(['user.name AS author', 'post.title as title'])
    ->from('user')
    ->leftJoin('post', 'post.user_id = user.id');
```

In the code, the `leftJoin()` method's first parameter
specifies the table to join to. The second parameter defines the join condition.

If your database application supports other join types, you can use those via the  generic `join` method:

```php
$query->join('FULL OUTER JOIN', 'post', 'post.user_id = user.id');
```

The first argument is the join type to perform. The second is the table to join to, and the third is the condition.

Like `FROM`, you may also join with sub-queries. To do so, specify the sub-query as an array
which must contain one element. The array value must be a `Query` object representing the sub-query,
while the array key is the alias for the sub-query. For example,

```php
$query->leftJoin(['u' => $subQuery], 'u.id=author_id');
```


### `UNION`

`UNION` in SQL adds results of one query to results of another query. Columns returned by both queries should match.
In Yii in order to build it you can first form two query objects and then use the `union` method:

```php
$query = new Query();
$query->select("id, category_id as type, name")->from('post')->limit(10);

$anotherQuery = new Query();
$anotherQuery->select('id, type, name')->from('user')->limit(10);

$query->union($anotherQuery);
```


Batch Query
-----------

When working with large amounts of data, methods such as [[yii\db\Query::all()]] are not suitable
because they require loading all data into the memory. To keep the memory requirement low, Yii
provides the so-called batch query support. A batch query makes uses of the data cursor and fetches
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
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users is indexed by the "username" column
}

foreach ($query->each() as $username => $user) {
}
```
