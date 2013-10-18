Query Builder and Query
=======================

Yii provides a basic database access layer as was described in [Database basics](database-basics.md) section. Still it's
a bit too much to use SQL directly all the time. To solve the issue Yii provides a query builder that allows you to
work with the database in object-oriented style.

Basic query builder usage is the following:

```php
$query = new Query;

// Define query
$query->select('id, name')
 	->from('tbl_user')
 	->limit(10);

// Create a command. You can get the actual SQL using $command->sql
$command = $query->createCommand();
// Execute command
$rows = $command->queryAll();
```

Basic selects and joins
-----------------------

In order to form a `SELECT` query you need to specify what to select and where to select it from.

```php
$query->select('id, name')
	->from('tbl_user');
```

If you want to get IDs of all users with posts you can use `DISTINCT`. With query builder it will look like the following:

```php
$query->select('user_id')->distinct()->from('tbl_post');
```

Select options can be specified as array. It's especially useful when these are formed dynamically.

```php
$query->select(array('tbl_user.name AS author', 'tbl_post.title as title')) // <-- specified as array
	->from('tbl_user')
	->leftJoin('tbl_post', 'tbl_post.user_id = tbl_user.id'); // <-- join with another table
```

In the code above we've used `leftJoin` method to select from two related tables at the same time. Firsrt parameter
specifies table name and the second is the join condition. Query builder has the following methods to join tables:

- `innerJoin`
- `leftJoin`
- `rightJoin`

If your data storage supports more types you can use generic `join` method:

```php
$query->join('FULL OUTER JOIN', 'tbl_post', 'tbl_post.user_id = tbl_user.id');
```

Specifying conditions
---------------------

Usually you need data that matches some conditions. There are some useful methods to specify these and the most powerful
is `where`. There are multiple ways to use it.

The simplest is to specify condition in a string:

```php
$query->where('status=:status', array(
	':status' => $status,
));
```

When using this format make sure you're binding parameters and not creating a query by string concatenation.

Instead of binding status value immediately you can do it using `params` or `addParams`:

```php
$query->where('status=:status');

$query->addParams(array(
	':status' => $status,
));
```

There is another convenient way to use the method called hash format:

```php
$query->where(array(
	'status' => 10,
	'type' => 2,
	'id' => array(4, 8, 15, 16, 23, 42),
));
```

It will generate the following SQL:

```sql
WHERE (`status` = 10) AND (`type` = 2) AND (`id` IN (4, 8, 15, 16, 23, 42))
```

If you'll specify value as `null` such as the following:

```php
$query->where(array(
	'status' => null,
));
```

SQL generated will be:

```sql
WHERE (`status` IS NULL)
```

Another way to use the method is the operand format which is `array(operator, operand1, operand2, ...)`.

Operator can be one of the following:

- `and`: the operands should be concatenated together using `AND`. For example,
  `array('and', 'id=1', 'id=2')` will generate `id=1 AND id=2`. If an operand is an array,
  it will be converted into a string using the rules described here. For example,
  `array('and', 'type=1', array('or', 'id=1', 'id=2'))` will generate `type=1 AND (id=1 OR id=2)`.
  The method will NOT do any quoting or escaping.
- `or`: similar to the `and` operator except that the operands are concatenated using `OR`.
- `between`: operand 1 should be the column name, and operand 2 and 3 should be the
   starting and ending values of the range that the column is in.
   For example, `array('between', 'id', 1, 10)` will generate `id BETWEEN 1 AND 10`.
- `not between`: similar to `between` except the `BETWEEN` is replaced with `NOT BETWEEN`
  in the generated condition.
- `in`: operand 1 should be a column or DB expression, and operand 2 be an array representing
  the range of the values that the column or DB expression should be in. For example,
  `array('in', 'id', array(1, 2, 3))` will generate `id IN (1, 2, 3)`.
  The method will properly quote the column name and escape values in the range.
- `not in`: similar to the `in` operator except that `IN` is replaced with `NOT IN` in the generated condition.
- `like`: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
  the values that the column or DB expression should be like.
  For example, `array('like', 'name', '%tester%')` will generate `name LIKE '%tester%'`.
  When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
  using `AND`. For example, `array('like', 'name', array('%test%', '%sample%'))` will generate
  `name LIKE '%test%' AND name LIKE '%sample%'`.
  The method will properly quote the column name and escape values in the range.
- `or like`: similar to the `like` operator except that `OR` is used to concatenate the `LIKE`
  predicates when operand 2 is an array.
- `not like`: similar to the `like` operator except that `LIKE` is replaced with `NOT LIKE`
  in the generated condition.
- `or not like`: similar to the `not like` operator except that `OR` is used to concatenate
  the `NOT LIKE` predicates.

If you are building parts of condition dynamically it's very convenient to use `andWhere` and `orWhere`:

```php
$status = 10;
$search = 'yii';

$query->where(array('status' => $status));
if (!empty($search)) {
	$query->addWhere('like', 'title', $search);
}
```

In case `$search` isn't empty the following SQL will be generated:

```sql
WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```

Order
-----

For ordering results `orderBy` and `addOrderBy` could be used:

```php
$query->orderBy(array(
	'id' => Query::SORT_ASC,
	'name' => Query::SORT_DESC,
));
```

Here we are ordering by `id` ascending and then by `name` descending.

Group and Having
----------------

In order to add `GROUP BY` to generated SQL you can use the following:

```php
$query->groupBy('id, status');
```

If you want to add another field after using `groupBy`:

```php
$query->addGroupBy(array('created_at', 'updated_at'));
```

To add a `HAVING` condition the corresponding `having` method and its `andHaving` and `orHaving` can be used. Parameters
for these are similar to the ones for `where` methods group:

```php
$query->having(array('status' => $status));
```

Limit and offset
----------------

To limit result to 10 rows `limit` can be used:

```php
$query->limit(10);
```

To skip 100 fist rows use:

```php
$query->offset(100);
```

Union
-----

`UNION` in SQL adds results of one query to results of another query. Columns returned by both queries should match.
In Yii in order to build it you can first form two query objects and then use `union` method:

```php
$query = new Query;
$query->select("id, 'post' as type, name")->from('tbl_post')->limit(10);

$anotherQuery = new Query;
$query->select('id, 'user' as type, name')->from('tbl_user')->limit(10);

$query->union($anotherQuery);
```

