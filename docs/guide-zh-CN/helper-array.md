数组助手类（ArrayHelper）
======================

除了[PHP中丰富的数组函数集](http://php.net/manual/zh/book.array.php)，
Yii 数组助手类提供了额外的静态方法，让你更高效地处理数组。


## 获取值（Getting Values） <span id="getting-values"></span>

用原生PHP从一个对象、数组、或者包含这两者的一个复杂数据结构中获取数据是非常繁琐的。
你首先得使用 `isset` 检查 key 是否存在, 然后如果存在你就获取它，如果不存在，
则提供一个默认返回值：

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

Yii 提供了一个非常方便的方法来做这件事：

```php
$value = ArrayHelper::getValue($array, 'foo.bar.name');
```

方法的第一个参数是我们从哪里获取值。第二个参数指定了如何获取数据，
它可以是下述几种类型中的一个：

- 数组键名或者欲从中取值的对象的属性名称；
- 以点号分割的数组键名或者对象属性名称组成的字符串，上例中使用的参数类型就是该类型；
- 返回一个值的回调函数。

回调函数如下例所示：

```php
$fullName = ArrayHelper::getValue($user, function ($user, $defaultValue) {
    return $user->firstName . ' ' . $user->lastName;
});
```

第三个可选的参数如果没有给定值，则默认为 `null`，如下例所示：

```php
$username = ArrayHelper::getValue($comment, 'user.username', 'Unknown');
```


## 设定值（Setting values） <span id="setting-values"></span>

```php
$array = [
    'key' => [
        'in' => ['k' => 'value']
    ]
];

ArrayHelper::setValue($array, 'key.in', ['arr' => 'val']);
// 在 `$array` 中写入值的路径可以被指定为一个数组
ArrayHelper::setValue($array, ['key', 'in'], ['arr' => 'val']);
```

结果，`$array['key']['in']` 的初始值将被新值覆盖

```php
[
    'key' => [
        'in' => ['arr' => 'val']
    ]
]
```

如果路径包含一个不存在的键，它将被创建

```php
// 如果 `$array['key']['in']['arr0']` 不为空，则该值将被添加到数组中
ArrayHelper::setValue($array, 'key.in.arr0.arr1', 'val');

// 如果你想完全覆盖值 `$array['key']['in']['arr0']`
ArrayHelper::setValue($array, 'key.in.arr0', ['arr1' => 'val']);
```

结果将是

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


## 从数组中获取值（Take a value from an array） <span id="removing-values"></span>

如果你想获得一个值，然后立即从数组中删除它，你可以使用 `remove` 方法：

```php
$array = ['type' => 'A', 'options' => [1, 2]];
$type = ArrayHelper::remove($array, 'type');
```

执行代码后，`$array` 将包含 `['options' => [1, 2]]` 且 `$type` 将包含 `A`。
请注意，与 `getValue` 方法不同，`remove` 仅支持简单的键名称。


## 检查键名的存在（Checking Existence of Keys） <span id="checking-existence-of-keys"></span>

`ArrayHelper::keyExists` 工作原理和[array_key_exists](http://php.net/manual/en/function.array-key-exists.php)差不多，除了
它还可支持大小写不敏感的键名比较，比如：

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

## 检索列（Retrieving Columns） <span id="retrieving-columns"></span>

通常你要从多行数据或者多个对象构成的数组中获取某列的值，一个普通的例子是获取id值列表。

```php
$data = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$ids = ArrayHelper::getColumn($array, 'id');
```

结果将是 `['123', '345']`。

如果需要额外的转换或者取值的方法比较复杂，
第二参数可以指定一个匿名函数：

```php
$result = ArrayHelper::getColumn($array, function ($element) {
    return $element['id'];
});
```


## 重建数组索引（Re-indexing Arrays） <span id="reindexing-arrays"></span>

按一个指定的键名重新索引一个数组，可以用 `index` 方法。输入的数组应该是多维数组或者是一个对象数组。
键名（译者注：第二个参数）可以是子数组的键名、对象的属性名，
也可以是一个返回给定元素数组键值的匿名函数。

`$groups` 属性是一个键数组，
它将根据指定的键将输入数组分组为一个或多个子数组。

如果 `$key` 属性或其特定元素的值为 null，并且未定义 `$groups`，
则数组元素将被丢弃。否则，如果指定了 `$groups`，
则数组元素将被添加到没有任何键的结果数组中。

例如：

```php
$array = [
    ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
    ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
];
$result = ArrayHelper::index($array, 'id');
```

结果将是一个关联数组，其中键是 `id` 属性的值

```php
[
    '123' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    '345' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
    // 原始数组的第二个元素由于相同的 ID 而被最后一个元素覆盖
]
```

匿名函数作为 `$key` 传递，给出了相同的结果。

```php
$result = ArrayHelper::index($array, function ($element) {
    return $element['id'];
});
```

传递 `id` 作为第三个参数将 `id` 分配给 `$ array`：

```php
$result = ArrayHelper::index($array, null, 'id');
```

结果将是一个多维数组，它由第一级的 `id` 分组，并且不在第二级索引：

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

匿名函数也可用于分组数组中：

```php
$result = ArrayHelper::index($array, 'data', [function ($element) {
    return $element['id'];
}, 'device']);
```

结果将是一个多维数组，由第一级的 `id` 分组，第二级的 `device` 和第三级的
`data` 索引：

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

## 建立哈希表（Building Maps） <span id="building-maps"></span>

为了从一个多维数组或者一个对象数组中建立一个映射表（键值对），你可以使用
`map` 方法。`$from` 和 `$to` 参数分别指定了欲构建的映射表的键名和属性名。
根据需要，你可以按照一个分组字段 `$group` 将映射表进行分组，例如，

```php
$array = [
    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
);

$result = ArrayHelper::map($array, 'id', 'name');
// 结果是： 
// [
//     '123' => 'aaa',
//     '124' => 'bbb',
//     '345' => 'ccc',
// ]

$result = ArrayHelper::map($array, 'id', 'name', 'class');
// 结果是：
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


## 多维排序（Multidimensional Sorting） <span id="multidimensional-sorting"></span>

`multisort` 方法可用来对嵌套数组或者对象数组进行排序，可按一到多个键名排序，比如，

```php
$data = [
    ['age' => 30, 'name' => 'Alexander'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 19, 'name' => 'Barney'],
];
ArrayHelper::multisort($data, ['age', 'name'], [SORT_ASC, SORT_DESC]);
```

排序之后我们在 `$data` 中得到的值如下所示：

```php
[
    ['age' => 19, 'name' => 'Barney'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 30, 'name' => 'Alexander'],
];
```

第二个参数指定排序的键名，如果是单键名的话可以是字符串，如果是多键名则是一个数组，
或者是如下例所示的一个匿名函数：

```php
ArrayHelper::multisort($data, function($item) {
    return isset($item['age']) ? ['age', 'name'] : 'name';
});
```

第三个参数表示增降顺序。单键排序时，它可以是 `SORT_ASC` 或者 
`SORT_DESC` 之一。如果是按多个键名排序，你可以用一个数组为
各个键指定不同的顺序。

最后一个参数（译者注：第四个参数）是PHP的排序标识（sort flag），可使用的值和调用PHP
[sort()](http://php.net/manual/zh/function.sort.php) 函数时传递的值一样。


## 检测数组类型（Detecting Array Types） <span id="detecting-array-types"></span> 

想知道一个数组是索引数组还是联合数组很方便，这有个例子：

```php
// 不指定键名的数组
$indexed = ['Qiang', 'Paul'];
echo ArrayHelper::isIndexed($indexed);

// 所有键名都是字符串
$associative = ['framework' => 'Yii', 'version' => '2.0'];
echo ArrayHelper::isAssociative($associative);
```


## HTML 编码和解码值（HTML Encoding and Decoding Values） <span id="html-encoding-values"></span>

为了将字符串数组中的特殊字符做 HTML 编解码，你可以使用下列方法：

```php
$encoded = ArrayHelper::htmlEncode($data);
$decoded = ArrayHelper::htmlDecode($data);
```

默认情况只会对值做编码（译者注：原文中是编码，应为编解码）。通过给第二个参数传 `false` ，你也可以对键名做编码。
编码将默认使用应用程序的字符集，你可以通过第三个参数指定该字符集。


## 合并数组（Merging Arrays） <span id="merging-arrays"></span>

您可以使用 [[yii\helpers\ArrayHelper::merge()|ArrayHelper::merge()]] 将两个或多个数组合并成一个递归的数组。
如果每个数组都有一个具有相同字符串键值的元素，则后者将覆盖前者
（不同于 [array_merge_recursive()](http://php.net/manual/en/function.array-merge-recursive.php)）。
如果两个数组都有一个数组类型的元素并且具有相同的键，则将执行递归合并。
对于整数键的元素，来自后一个数组的元素将被附加到前一个数组。
您可以使用 [[yii\helpers\UnsetArrayValue]] 对象来取消前一个数组的值或
[[yii\helpers\ReplaceArrayValue]] 以强制替换先前的值而不是递归合并。

例如：

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

结果将是：

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


## 对象转换为数组（Converting Objects to Arrays） <span id="converting-objects-to-arrays"></span>

你经常要将一个对象或者对象的数组转换成一个数组，常见的情形是，为了通过 REST API 提供数据数组（或其他使用方式），
将 AR 模型（活动记录模型）转换成数组。如下代码可完成这个工作：

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

第一个参数包含我们想要转换的数据，在本例中，我们要转换一个叫 `Post` 的 AR 模型。

第二个参数是每个类的转换映射表，我们在此设置了一个 `Post` 模型的映射。
每个映射数组包含一组的映射，每个映射可以是：

- 一个要包含的照原样的字段名（和类中属性的名称一致）；
- 一个由你可随意取名的键名和你想从中取值的模型列名组成的键值对；
- 一个由你可随意取名的键名和有返回值的回调函数组成的键值对；

这上面的转换结果将会是：


```php
[
    'id' => 123,
    'title' => 'test',
    'createTime' => '2013-01-01 12:00AM',
    'length' => 301,
]
```

也可以在一个特定的类中实现 [[yii\base\Arrayable|Arrayable]] 接口，
从而为其对象提供默认的转换成数组的方法。

## 测试阵列（Testing against Arrays） <span id="testing-arrays"></span>

通常你需要检查一个元素是否在数组中，或者一组元素是另一个元素的子集。
虽然PHP提供 `in_array()`，这不支持子集或 `\Traversable` 对象。

为了支持这些测试，[[yii\base\ArrayHelper]] 提供了 [[yii\base\ArrayHelper::isIn()|isIn()]]
和 [[yii\base\ArrayHelper::isSubset()|isSubset()]] 与 [[in_array()]] 签名相同。

```php
// true
ArrayHelper::isIn('a', ['a']);
// true
ArrayHelper::isIn('a', new(ArrayObject['a']));

// true 
ArrayHelper::isSubset(new(ArrayObject['a', 'c']), new(ArrayObject['a', 'b', 'c'])

```
