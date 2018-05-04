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


## Setting values <span id="setting-values"></span>

```php
$array = [
    'key' => [
        'in' => ['k' => 'value']
    ]
];

ArrayHelper::setValue($array, 'key.in', ['arr' => 'val']);
// the path to write the value in `$array` can be specified as an array
ArrayHelper::setValue($array, ['key', 'in'], ['arr' => 'val']);
```

As a result, initial value of `$array['key']['in']` will be overwritten by new value

```php
[
    'key' => [
        'in' => ['arr' => 'val']
    ]
]
```

If the path contains a nonexistent key, it will be created

```php
// if `$array['key']['in']['arr0']` is not empty, the value will be added to the array
ArrayHelper::setValue($array, 'key.in.arr0.arr1', 'val');

// if you want to completely override the value `$array['key']['in']['arr0']`
ArrayHelper::setValue($array, 'key.in.arr0', ['arr1' => 'val']);
```

The result will be

```php
[
    'key' => [
        'in' => [
            'k' => 'value',
            'arr0' => ['arr1' => 'val']
        ]
    ]
]
```


## Take a value from an array <span id="removing-values"></span>

In case you want to get a value and then immediately remove it from an array you can use `remove` method:

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
$array = [
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

In order to index an array according to a specified key, the `index` method can be used. The input should be either
multidimensional array or an array of objects. The `$key` can be either a key name of the sub-array, a property name of
object, or an anonymous function that must return the value that will be used as a key.

The `$groups` attribute is an array of keys, that will be used to group the input array into one or more sub-arrays
based on keys specified.

If the `$key` attribute or its value for the particular element is `null` and `$groups` is not defined, the array
element will be discarded. Otherwise, if `$groups` is specified, array element will be added to the result array
without any key.

For example:

```php
$array = [
    ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
    ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
];
$result = ArrayHelper::index($array, 'id');
```

The result will be an associative array, where the key is the value of `id` attribute:

```php
[
    '123' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    '345' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
    // The second element of an original array is overwritten by the last element because of the same id
]
```

Anonymous function, passed as a `$key`, gives the same result:

```php
$result = ArrayHelper::index($array, function ($element) {
    return $element['id'];
});
```

Passing `id` as a third argument will group `$array` by `id`:

```php
$result = ArrayHelper::index($array, null, 'id');
```

The result will be a multidimensional array grouped by `id` on the first level and not indexed on the second level:

```php
[
    '123' => [
        ['id' => '123', 'data' => 'abc', 'device' => 'laptop']
    ],
    '345' => [ // all elements with this index are present in the result array
        ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
        ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
    ]
]
```

An anonymous function can be used in the grouping array as well:

```php
$result = ArrayHelper::index($array, 'data', [function ($element) {
    return $element['id'];
}, 'device']);
```

The result will be a multidimensional array grouped by `id` on the first level, by `device` on the second level and
indexed by `data` on the third level:

```php
[
    '123' => [
        'laptop' => [
            'abc' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop']
        ]
    ],
    '345' => [
        'tablet' => [
            'def' => ['id' => '345', 'data' => 'def', 'device' => 'tablet']
        ],
        'smartphone' => [
            'hgi' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
        ]
    ]
]
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
];

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

You can use [[yii\helpers\ArrayHelper::merge()|ArrayHelper::merge()]] to merge two or more arrays into one recursively.
If each array has an element with the same string key value, the latter will overwrite the former
(different from [array_merge_recursive()](http://php.net/manual/en/function.array-merge-recursive.php)).
Recursive merging will be conducted if both arrays have an element of array type and are having the same key.
For integer-keyed elements, the elements from the latter array will be appended to the former array.
You can use [[yii\helpers\UnsetArrayValue]] object to unset value from previous array or
[[yii\helpers\ReplaceArrayValue]] to force replace former value instead of recursive merging.

For example:

```php
$array1 = [
    'name' => 'Yii',
    'version' => '1.1',
    'ids' => [
        1,
    ],
    'validDomains' => [
        'example.com',
        'www.example.com',
    ],
    'emails' => [
        'admin' => 'admin@example.com',
        'dev' => 'dev@example.com',
    ],
];

$array2 = [
    'version' => '2.0',
    'ids' => [
        2,
    ],
    'validDomains' => new \yii\helpers\ReplaceArrayValue([
        'yiiframework.com',
        'www.yiiframework.com',
    ]),
    'emails' => [
        'dev' => new \yii\helpers\UnsetArrayValue(),
    ],
];

$result = ArrayHelper::merge($array1, $array2);
```

The result will be:

```php
[
    'name' => 'Yii',
    'version' => '2.0',
    'ids' => [
        1,
        2,
    ],
    'validDomains' => [
        'yiiframework.com',
        'www.yiiframework.com',
    ],
    'emails' => [
        'admin' => 'admin@example.com',
    ],
]
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

The result of conversion above for single model will be:


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

## Testing against Arrays <span id="testing-arrays"></span>

Often you need to check if an element is in an array or a set of elements is a subset of another.
While PHP offers `in_array()`, this does not support subsets or `\Traversable` objects.

To aid these kinds of tests, [[yii\helpers\ArrayHelper]] provides [[yii\helpers\ArrayHelper::isIn()|isIn()]]
and [[yii\helpers\ArrayHelper::isSubset()|isSubset()]] with the same signature as
[in_array()](http://php.net/manual/en/function.in-array.php).

```php
// true
ArrayHelper::isIn('a', ['a']);
// true
ArrayHelper::isIn('a', new ArrayObject(['a']));

// true 
ArrayHelper::isSubset(new ArrayObject(['a', 'c']), new ArrayObject(['a', 'b', 'c']));
```
