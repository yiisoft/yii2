Query Builder and Query
=======================

Yii provides a basic database access layer as described in the [Database basics](database-basics.md) section. The database access layer provides a low-level way to interact with the database. While useful in some situations, it can be tedious to rely too much upon direct SQL. An alternative approach that Yii provides is the Query Builder. The Query Builder provides an object-oriented vehicle for generating queries to be executed.

Here's a basic example:

```php
$query = new Query;

// Define the query:
$query->select('id, name')
	->from('tbl_user')
	->limit(10);

// Create a command. 
$command = $query->createCommand();
// You can get the actual SQL using $command->sql

// Execute the command:
$rows = $command->queryAll();
```

Basic selects
-------------

In order to form a basic `SELECT` query, you need to specify what columns to select and from what table:

```php
$query->select('id, name')
	->from('tbl_user');
```

Select options can be specified as a comma-separated string, as in the above, or as an array. The array syntax is especially useful when forming the selection dynamically:

```php
$columns = [];
$columns[] = 'id';
$columns[] = 'name';
$query->select($columns)
	->from('tbl_user');
```

Joins
-----

Joins are generated in the Query Builder by using the applicable join method:

- `innerJoin`
- `leftJoin`
- `rightJoin`

This left join selects data from two related tables in one query:

```php
$query->select(['tbl_user.name AS author', 'tbl_post.title as title'])	->from('tbl_user')
	->leftJoin('tbl_post', 'tbl_post.user_id = tbl_user.id'); 
```

In the code, the `leftJion` method's first parameter
specifies the table to join to. The second paramter defines the join condition.

If your database application supports other join types, you can use those via the  generic `join` method:

```php
$query->join('FULL OUTER JOIN', 'tbl_post', 'tbl_post.user_id = tbl_user.id');
```

The first argument is the join type to perform. The second is the table to join to, and the third is the condition.

Specifying SELECT conditions
---------------------

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

Another way to use the method is the operand format which is `[operator, operand1, operand2, ...]`.

Operator can be one of the following:

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
- `in`: operand 1 should be a column or DB expression, and operand 2 be an array representing
  the range of the values that the column or DB expression should be in. For example,
  `['in', 'id', [1, 2, 3]]` will generate `id IN (1, 2, 3)`.
  The method will properly quote the column name and escape values in the range.
- `not in`: similar to the `in` operator except that `IN` is replaced with `NOT IN` in the generated condition.
- `like`: operand 1 should be a column or DB expression, and operand 2 be a string or an array representing
  the values that the column or DB expression should be like.
  For example, `['like', 'name', '%tester%']` will generate `name LIKE '%tester%'`.
  When the value range is given as an array, multiple `LIKE` predicates will be generated and concatenated
  using `AND`. For example, `['like', 'name', ['%test%', '%sample%']]` will generate
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

$query->where(['status' => $status]);
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
$query->orderBy([
	'id' => SORT_ASC,
	'name' => SORT_DESC,
]);
```

Here we are ordering by `id` ascending and then by `name` descending.

Distinct
--------

If you want to get IDs of all users with posts you can use `DISTINCT`. With query builder it will look like the following:

```php
$query->select('user_id')->distinct()->from('tbl_post');
```

Group and Having
----------------

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

