ArrayHelper
===========

[丰富设置的PHP](http://php.net/manual/zh/book.array.php)附加的数组函数，Yii 数组辅助函数提供了额外的静态方法让你更有效率的处理数组。

## Getting Values <span id="getting-values"></span>

## 获取值 <span id="getting-values"></span>

从一个数组中再获得值， 一个对象或者使用标准PHP是完全的反复由一个复杂结构组成.你不得不首先使用`isset`检查key是否存在,然后如果存在你就获取到了它，如果不存在，
预设默认值


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

Yii 提供了一个非常方便的方法来处理它:

```php
$value = ArrayHelper::getValue($array, 'foo.bar.name');
```

第一个方法参数是我们从哪里获取值.第二个参数定义如何去获取数据.它可以为一下的其中一个:

- 数组键名或者获得值对象的属性名称
- 使用点号分割的数组key或者对象的属性名称。这个我们已经在上面的案列使用过了
- 一个回调函数返回一个值

这个回调函数应该是下列的:


```php
$fullName = ArrayHelper::getValue($user, function ($user, $defaultValue) {
    return $user->firstName . ' ' . $user->lastName;
});
```

第三个可选择的参数是默认值，如果没有指定第三个参数，默认值是`null`.也可以使用下列的:

```php
$username = ArrayHelper::getValue($comment, 'user.username', 'Unknown');
```


假设你想要获取值和然后马上从数组中移除它,你可以使用`remove`方法:

```php
$array = ['type' => 'A', 'options' => [1, 2]];
$type = ArrayHelper::remove($array, 'type');
```

在执行了代码之后,`$array` 将会等于 `['options' => [1, 2]]` 和 `$type` 将会是 `A`.注意这和`getValue`方法不同,`remove`方法只支持简单的键名. 

## 检查键名存在<span id="checking-existence-of-keys"></span>

`ArrayHelper::keyExists` 工作原理和[array_key_exists](http://php.net/manual/en/function.array-key-exists.php)差不多,除了
`array_key_exists`同样支持区分大小写键名进行比较.比如:

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

## 检索列 <span id="retrieving-columns"></span>

你经常需要从由数据行组成的数组或者对象中获取一列值.通常情况下是根据许多id获取一列数据.

```php
$data = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$ids = ArrayHelper::getColumn($array, 'id');
```

结果将是 `['123', '345']`.

如果额外的数据装换是必须的或者获取数据的方式是复杂的,第二个参数可以是一个匿名函数:

```php
$result = ArrayHelper::getColumn($array, function ($element) {
    return $element['id'];
});
```


## 数组索引重建

为了索引一个数组是根据一个指定的键名,那`index`方法可以使用.输入的数组应该是多维的或者一个对象数组.这键名可以是每个子数组中的键名,一个对象的属性名,
或者一个匿名函数返回给定的键值数组元素。

如果一个键名的值是null,相应的数组元素将会是丢弃的和不会放入到结果中.例如,

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


## 建立 Maps <span id="building-maps"></span>


为了从一个多维数组或者一个对象数组中建立一个map(键值对),你可以使用`map`方法.`$from` 和 `$to` 参数指定键名或者属性名称来组建这个map.
视需要,一个可以进一步地根据一个分组字段`$group`来分组map.

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


## 多维排序 <span id="multidimensional-sorting"></span>

`multisort` method helps to sort an array of objects or nested arrays by one or several keys. For example,
`multisort` 方法帮助排序一个对象数组或者嵌套的数组,或者几个键名.比如,

```php
$data = [
    ['age' => 30, 'name' => 'Alexander'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 19, 'name' => 'Barney'],
];
ArrayHelper::multisort($data, ['age', 'name'], [SORT_ASC, SORT_DESC]);
```

排序之后我们将会在`$data`中获取下列的:

```php
[
    ['age' => 19, 'name' => 'Barney'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 30, 'name' => 'Alexander'],
];
```

第二个参数指定键名来排序,如果它是一个单一的键名,可以是一个字符串,假设很多键名,可以是一个数组,或者一个匿名函数像下列的一个:

```php
ArrayHelper::multisort($data, function($item) {
    return isset($item['age']) ? ['age', 'name'] : 'name';
});
```

Third argument is direction. In case of sorting by a single key it could be either `SORT_ASC` or
`SORT_DESC`. If sorting by multiple values you can sort each value differently by providing an array of
sort direction.

第三个参数是引导.假设根据一个单一的键名进行排序,它可以是`SORT_ASC`或者`SORT_DESC`中两者之中任一的.
如果是根据多个值进行排序，你可以排序每一个值通过提供一个排序引导数组。

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
