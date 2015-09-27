ArrayHelper
===========

Additionally to the [rich set of PHP array functions](http://php.net/manual/en/book.array.php), the Yii array helper provides
extra static methods allowing you to deal with arrays more efficiently.


## Getting Values <span id="getting-values"></span>

Retrieving values from an array, an object or a complex structure consisting of both using standard PHP is quite
repetitive. You have to check if key exists with `isset` first, then if it does you're getting it, if not,
providing default value:

```php
class User
{
    public $name = 'Alex';
}

$array = [
    'foo' => [
        'bar' => new User(),
    ]
];

$value = isset($array['foo']['bar']->name) ? $array['foo']['bar']->name : null;
```

Yii provides a very convenient method to do it:

```php
$value = ArrayHelper::getValue($array, 'foo.bar.name');
```

First method argument is where we're getting value from. Second argument specifies how to get the data. It could be one
of the following:

- Name of array key or object property to retrieve value from.
- Set of dot separated array keys or object property names. The one we've used in the example above.
- A callback returning a value.

The callback should be the following:

```php
$fullName = ArrayHelper::getValue($user, function ($user, $defaultValue) {
    return $user->firstName . ' ' . $user->lastName;
});
```

Third optional argument is default value which is `null` if not specified. Could be used as follows:

```php
$username = ArrayHelper::getValue($comment, 'user.username', 'Unknown');
```

In case you want to get the value and then immediately remove it from array you can use `remove` method:

```php
$array = ['type' => 'A', 'options' => [1, 2]];
$type = ArrayHelper::remove($array, 'type');
```

After executing the code `$array` will contain `['options' => [1, 2]]` and `$type` will be `A`. Note that unlike
`getValue` method, `remove` supports simple key names only.


## Checking Existence of Keys <span id="checking-existence-of-keys"></span>

`ArrayHelper::keyExists` works the same way as [array_key_exists](http://php.net/manual/en/function.array-key-exists.php)
except that it also supports case-insensitive key comparison. For example,

```php
$data1 = [
    'userName' => 'Alex',
];

$data2 = [
    'username' => 'Carsten',
];

if (!ArrayHelper::keyExists('username', $data1, false) || !ArrayHelper::keyExists('username', $data2, false)) {
    echo "Please provide username.";
}
```

## Retrieving Columns <span id="retrieving-columns"></span>

Often you need to get a column of values from array of data rows or objects. Common example is getting a list of IDs.

```php
$data = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$ids = ArrayHelper::getColumn($array, 'id');
```

The result will be `['123', '345']`.

If additional transformations are required or the way of getting value is complex, second argument could be specified
as an anonymous function:

```php
$result = ArrayHelper::getColumn($array, function ($element) {
    return $element['id'];
});
```


## Re-indexing Arrays <span id="reindexing-arrays"></span>

In order to index an array according to a specified key, the `index` method can be used. The input array should be
multidimensional or an array of objects. The key can be a key name of the sub-array, a property name of object, or
an anonymous function which returns the key value given an array element.

If a key value is null, the corresponding array element will be discarded and not put in the result. For example,

```php
$array = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$result = ArrayHelper::index($array, 'id');
// the result is:
// [
//     '123' => ['id' => '123', 'data' => 'abc'],
//     '345' => ['id' => '345', 'data' => 'def'],
// ]

// using anonymous function
$result = ArrayHelper::index($array, function ($element) {
    return $element['id'];
});
```


## Building Maps <span id="building-maps"></span>

In order to build a map (key-value pairs) from a multidimensional array or an array of objects you can use `map` method.
The `$from` and `$to` parameters specify the key names or property names to set up the map. Optionally, one can further
group the map according to a grouping field `$group`. For example,

```php
$array = [
    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
);

$result = ArrayHelper::map($array, 'id', 'name');
// the result is:
// [
//     '123' => 'aaa',
//     '124' => 'bbb',
//     '345' => 'ccc',
// ]

$result = ArrayHelper::map($array, 'id', 'name', 'class');
// the result is:
// [
//     'x' => [
//         '123' => 'aaa',
//         '124' => 'bbb',
//     ],
//     'y' => [
//         '345' => 'ccc',
//     ],
// ]
```


## Multidimensional Sorting <span id="multidimensional-sorting"></span>

`multisort` method helps to sort an array of objects or nested arrays by one or several keys. For example,

```php
$data = [
    ['age' => 30, 'name' => 'Alexander'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 19, 'name' => 'Barney'],
];
ArrayHelper::multisort($data, ['age', 'name'], [SORT_ASC, SORT_DESC]);
```

After sorting we'll get the following in `$data`:

```php
[
    ['age' => 19, 'name' => 'Barney'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 30, 'name' => 'Alexander'],
];
```

Second argument that specifies keys to sort by can be a string if it's a single key, an array in case of multiple keys
or an anonymous function like the following one:

```php
ArrayHelper::multisort($data, function($item) {
    return isset($item['age']) ? ['age', 'name'] : 'name';
});
```

Third argument is direction. In case of sorting by a single key it could be either `SORT_ASC` or
`SORT_DESC`. If sorting by multiple values you can sort each value differently by providing an array of
sort direction.

Last argument is PHP sort flag that could take the same values as the ones passed to
PHP [sort()](http://php.net/manual/en/function.sort.php).


## Detecting Array Types <span id="detecting-array-types"></span>

It is handy to know whether an array is indexed or an associative. Here's an example:

```php
// no keys specified
$indexed = ['Qiang', 'Paul'];
echo ArrayHelper::isIndexed($indexed);

// all keys are strings
$associative = ['framework' => 'Yii', 'version' => '2.0'];
echo ArrayHelper::isAssociative($associative);
```


## HTML Encoding and Decoding Values <span id="html-encoding-values"></span>

In order to encode or decode special characters in an array of strings into HTML entities you can use the following:

```php
$encoded = ArrayHelper::htmlEncode($data);
$decoded = ArrayHelper::htmlDecode($data);
```

Only values will be encoded by default. By passing second argument as `false` you can encode array's keys as well.
Encoding will use application charset and could be changed via third argument.


## Merging Arrays <span id="merging-arrays"></span>

```php
  /**
    * Merges two or more arrays into one recursively.
    * If each array has an element with the same string key value, the latter
    * will overwrite the former (different from array_merge_recursive).
    * Recursive merging will be conducted if both arrays have an element of array
    * type and are having the same key.
    * For integer-keyed elements, the elements from the latter array will
    * be appended to the former array.
    * @param array $a array to be merged to
    * @param array $b array to be merged from. You can specify additional
    * arrays via third argument, fourth argument etc.
    * @return array the merged array (the original arrays are not changed.)
    */
    public static function merge($a, $b)
```


## Converting Objects to Arrays <span id="converting-objects-to-arrays"></span>

Often you need to convert an object or an array of objects into an array. The most common case is converting active record
models in order to serve data arrays via REST API or use it otherwise. The following code could be used to do it:

```php
$posts = Post::find()->limit(10)->all();
$data = ArrayHelper::toArray($posts, [
    'app\models\Post' => [
        'id',
        'title',
        // the key name in array result => property name
        'createTime' => 'created_at',
        // the key name in array result => anonymous function
        'length' => function ($post) {
            return strlen($post->content);
        },
    ],
]);
```

The first argument contains the data we want to convert. In our case we're converting a `Post` AR model.

The second argument is conversion mapping per class. We're setting a mapping for `Post` model.
Each mapping array contains a set of mappings. Each mapping could be:

- A field name to include as is.
- A key-value pair of desired array key name and model column name to take value from.
- A key-value pair of desired array key name and a callback which returns value.

The result of conversion above will be:


```php
[
    'id' => 123,
    'title' => 'test',
    'createTime' => '2013-01-01 12:00AM',
    'length' => 301,
]
```

It is possible to provide default way of converting object to array for a specific class by implementing
[[yii\base\Arrayable|Arrayable]] interface in that class.
