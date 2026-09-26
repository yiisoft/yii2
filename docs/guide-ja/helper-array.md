配列ヘルパ
==========

[PHP の充実した配列関数](https://www.php.net/manual/ja/book.array.php) への追加として、
Yii の配列ヘルパは、配列をさらに効率的に扱うことを可能にするスタティックなメソッドを提供しています。


## 値を取得する <span id="getting-values"></span>

配列、オブジェクト、またはその両方から成る複雑な構造から標準的な PHP を使って値を取得することは、非常に面倒くさい仕事です。
最初に `isset` でキーの存在をチェックしなければならず、次に、キーが存在していれば値を取得し、存在していなければ、
デフォルト値を提供しなければなりません。

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

Yii はこのための非常に便利なメソッドを提供しています。

```php
$value = ArrayHelper::getValue($array, 'foo.bar.name');
```

メソッドの最初の引数は、どこから値を取得しようとしているかを指定します。
二番目の引数は、データの取得の仕方を指定します。これは、以下の一つとすることが出来ます。

- 値を読み出すべき配列のキーまたはオブジェクトのプロパティの名前。
- ドットで分割された配列のキーまたはオブジェクトのプロパティ名のセット。上の例で使用した形式です。
- 値を返すコールバック。

コールバックは次の形式でなければなりません。

```php
$fullName = ArrayHelper::getValue($user, function ($user, $defaultValue) {
    return $user->firstName . ' ' . $user->lastName;
});
```

三番目のオプションの引数はデフォルト値であり、指定されない場合は `null` となります。以下のようにして使用します。

```php
$username = ArrayHelper::getValue($comment, 'user.username', 'Unknown');
```


## 値を設定する <span id="setting-values"></span>

```php
$array = [
    'key' => [
        'in' => ['k' => 'value']
    ]
];

ArrayHelper::setValue($array, 'key.in', ['arr' => 'val']);
// `$array` で値を書くためのパスは配列として指定することも出来ます
ArrayHelper::setValue($array, ['key', 'in'], ['arr' => 'val']);
```

結果として、`$array['key']['in']` の初期値は新しい値によって上書きされます。

```php
[
    'key' => [
        'in' => ['arr' => 'val']
    ]
]
```

パスが存在しないキーを含んでいる場合は、キーが作成されます。

```php
// `$array['key']['in']['arr0']` が空でなければ、値が配列に追加される
ArrayHelper::setValue($array, 'key.in.arr0.arr1', 'val');

// `$array['key']['in']['arr0']` の値を完全に上書きしたい場合は
ArrayHelper::setValue($array, 'key.in.arr0', ['arr1' => 'val']);
```

結果は以下のようになります

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


## 配列から値を取り除く <span id="removing-values"></span>

値を取得して、その直後にそれを配列から削除したい場合は、`remove` メソッドを使うことが出来ます。

```php
$array = ['type' => 'A', 'options' => [1, 2]];
$type = ArrayHelper::remove($array, 'type');
```

このコードを実行した後では、`$array` には `['options' => [1, 2]]` が含まれ、`$type` は `A` となります。
`getValue` メソッドとは違って、`remove` は単純なキー名だけをサポートすることに注意してください。


## キーの存在をチェックする <span id="checking-existence-of-keys"></span>

`ArrayHelper::keyExists` は、大文字と小文字を区別しないキーの比較をサポートすることを除いて、
[array_key_exists](https://www.php.net/manual/ja/function.array-key-exists.php) と同じ動作をします。例えば、

```php
$data1 = [
    'userName' => 'Alex',
];

$data2 = [
    'username' => 'Carsten',
];

if (!ArrayHelper::keyExists('username', $data1, false) || !ArrayHelper::keyExists('username', $data2, false)) {
    echo "username を提供してください。";
}
```

## カラムを取得する <span id="retrieving-columns"></span>

データ行またはオブジェクトの配列から、あるカラムの値を取得する必要があることがよくあります。良くある例は、ID のリストの取得です。

```php
$array = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$ids = ArrayHelper::getColumn($array, 'id');
```

結果は `['123', '345']` となります。

追加の変形が要求されたり、値の取得方法が複雑であったりする場合は、
無名関数を二番目の引数として指定することが出来ます。

```php
$result = ArrayHelper::getColumn($array, function ($element) {
    return $element['id'];
});
```


## 配列を再インデックスする <span id="reindexing-arrays"></span>

指定されたキーに従って配列にインデックスを付けるために、`index` メソッドを使うことが出来ます。
入力値は、多次元配列であるか、オブジェクトの配列でなければなりません。
`$key` は、サブ配列のキーの名前、オブジェクトのプロパティの名前、または、キーとして使用される値を返す無名関数とすることが出来ます。

`$groups` 属性はキーの配列であす。これは、入力値の配列を一つまたは複数のサブ配列にグループ化するために
キーとして使用されます。

特定の要素の `$key` 属性またはその値が `null` であるとき、`$groups` が定義されていない場合は、その要素は破棄されて、結果には入りません。
そうではなく、`$groups` が指定されている場合は、
配列の要素はキー無しで結果の配列に追加されます。

例えば、

```php
$array = [
    ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
    ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
];
$result = ArrayHelper::index($array, 'id');
```

結果は、`id` 属性の値をキーとする連想配列になります。

```php
[
    '123' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    '345' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
    // 元の配列の2番目の要素は、同じ id であるため、最後の要素によって上書きされます
]
```

`$key` として無名関数を渡しても同じ結果になります。

```php
$result = ArrayHelper::index($array, function ($element) {
    return $element['id'];
});
```

`id` を3番目の引数として渡すと、`$array` を `id` によってグループ化することが出来ます。

```php
$result = ArrayHelper::index($array, null, 'id');
```

結果は、最初のレベルが `id` でグループ化され、第2のレベルはインデックスされていない連想配列になります。

```php
[
    '123' => [
        ['id' => '123', 'data' => 'abc', 'device' => 'laptop']
    ],
    '345' => [ // このインデックスを持つ全ての要素が結果の配列に入る
        ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
        ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
    ]
]
```

無名関数を配列のグループ化に使うことも出来ます。

```php
$result = ArrayHelper::index($array, 'data', [function ($element) {
    return $element['id'];
}, 'device']);
```

結果は、最初のレベルが `id` でグループ化され、第2のレベルが `device` でグループ化され、
第3のレベルが `data` でインデックスされた連想配列になります。

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

## マップを作成する <span id="building-maps"></span>

多次元配列またはオブジェクトの配列からマップ (キー・値 のペア) を作成するためには `map` メソッドを使うことが出来ます。
`$from` と `$to` のパラメータで、マップを構成するキー名またはプロパティ名を指定します。
オプションで、グループ化のためのフィールド `$group` に従って、マップをグループ化することも出来ます。例えば、

```php
$array = [
    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
];

$result = ArrayHelper::map($array, 'id', 'name');
// 結果は次のようになります
// [
//     '123' => 'aaa',
//     '124' => 'bbb',
//     '345' => 'ccc',
// ]

$result = ArrayHelper::map($array, 'id', 'name', 'class');
// 結果は次のようになります
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


## 多次元配列の並べ替え <span id="multidimensional-sorting"></span>

`multisort` メソッドは、オブジェクトの配列または入れ子にされた配列を、一つまたは複数のキーによって並べ替えることを手助けします。例えば、

```php
$data = [
    ['age' => 30, 'name' => 'Alexander'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 19, 'name' => 'Barney'],
];
ArrayHelper::multisort($data, ['age', 'name'], [SORT_ASC, SORT_DESC]);
```

並べ替えの後には、`$data` に次のデータが入っています。

```php
[
    ['age' => 19, 'name' => 'Barney'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 30, 'name' => 'Alexander'],
];
```

並べ替えで参照するキーを指定する二番目の引数は、一つのキーであれば文字列、複数のキーであれば配列を取ることが出来ます。
さらに、次のような無名関数でも構いません。

```php
ArrayHelper::multisort($data, function($item) {
    // 存在していれば 'age' で、さもなくば 'name' でソート
    return isset($item['age']) ? ['age', 'name'] : 'name';
});
```

三番目の引数は並べ替えの順序です。
一つのキーによる並べ替えの場合は、`SORT_ASC` か `SORT_DESC` のいずれかです。
複数の値による並べ替えの場合は、並べ替えの順序の配列を渡して、値ごとに違う順序で並べ替えることが出来ます。

最後の引数は並べ替えのフラグで、
PHP の [sort()](https://www.php.net/manual/ja/function.sort.php) 関数に渡されるのと同じ値を取ることが出来ます。


## 配列の型を検出する <span id="detecting-array-types"></span>

配列が添字配列であるか連想配列であるかを知ることが出来ると便利です。例を挙げましょう。

```php
// キーは指定されていない
$indexed = ['Qiang', 'Paul'];
echo ArrayHelper::isIndexed($indexed);

// 全てのキーは文字列
$associative = ['framework' => 'Yii', 'version' => '2.0'];
echo ArrayHelper::isAssociative($associative);
```


## 値を HTML エンコード / デコードする <span id="html-encoding-values"></span>

文字列の配列の中にある特殊文字を HTML エンティティにエンコード、または、HTML エンティティからデコードするために、下記の関数を使うことが出来ます。

```php
$encoded = ArrayHelper::htmlEncode($data);
$decoded = ArrayHelper::htmlDecode($data);
```

デフォルトでは、値だけがエンコードされます。二番目の引数を `false` として渡すことによって、配列のキーもエンコードすることが出来ます。
エンコードにはアプリケーションの文字セットが使用されますが、三番目の引数によってそれを変更することも出来ます。


## 配列をマージする <span id="merging-arrays"></span>

[[yii\helpers\ArrayHelper::merge()|ArrayHelper::merge()]] を使って、二つまたはそれ以上の配列を再帰的に一つの配列にマージすることが出来ます。
各配列に同じ文字列のキー値を持つ要素がある場合は、
([array_merge_recursive()](https://www.php.net/manual/ja/function.array-merge-recursive.php) とは違って)後のものが前のものを上書きします。
両方の配列が、同じキーを持つ配列型の要素を持っている場合は、再帰的なマージが実行されます。
添字型の要素については、後の配列の要素が前の配列の要素の後に追加されます。
[[yii\helpers\UnsetArrayValue]] オブジェクトを使って前の配列にある値を非設定に指定したり、
[[yii\helpers\ReplaceArrayValue]] オブジェクトを使って再帰的なマージでなく前の値の上書きを強制したりすることが出来ます。

例えば、

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

結果は次のようになります。

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


## オブジェクトを配列に変換する <span id="converting-objects-to-arrays"></span>

オブジェクトまたはオブジェクトの配列を配列に変換する必要があることがよくあります。
最もよくあるのは、REST API によってデータ配列を提供するなどの目的で、アクティブ・レコード・モデルを変換する場合です。そうするために、次のコードを使うことが出来ます。

```php
$posts = Post::find()->limit(10)->all();
$data = ArrayHelper::toArray($posts, [
    'app\models\Post' => [
        'id',
        'title',
        // 結果配列のキー名 => プロパティの値
        'createTime' => 'created_at',
        // 結果配列のキー名 => 無名関数が返す値
        'length' => function ($post) {
            return strlen($post->content);
        },
    ],
]);
```

最初の引数が変換したいデータです。この例では、`Post` AR モデルを変換しようとしています。

二番目の引数は、クラスごとの変換マップです。ここでは、`Post` モデルの変換マップを設定しています。
変換マップの配列が、一連のマップを含んでいます。各マップは以下のいずれかの形式を取ります。

- フィールド名 - そのままインクルードされる。
- キー/値 のペア - 配列のキー名にしたい文字列と、値を取得すべきモデルのカラムの名前。
- キー/値 のペア - 配列のキー名にしたい文字列と、値を返すコールバック。

単一のモデルに対する上記の変換の結果は以下のようになります。


```php
[
    'id' => 123,
    'title' => 'test',
    'createTime' => '2013-01-01 12:00AM',
    'length' => 301,
]
```

特定のクラスについて、配列に変換するデフォルトの方法を提供するためには、
そのクラスの [[yii\base\Arrayable|Arrayable]] インタフェイスを実装することが出来ます。

## 配列の中にあるかどうか調べる <span id="testing-arrays"></span>

ある要素が配列の中に存在するかどうか、また、一連の要素が配列のサブセットであるかどうか、ということを調べる必要がある場合がよくあります。
PHP は `in_array()` を提供していますが、これはサブセットや `\Traversable` なオブジェクトをサポートしていません。

この種のチェックを助けるために、[[yii\helpers\ArrayHelper]] は [[yii\helpers\ArrayHelper::isIn()|isIn()]]
および [[yii\helpers\ArrayHelper::isSubset()|isSubset()]] を
[in_array()](https://www.php.net/manual/ja/function.in-array.php) と同じシグニチャで提供しています。

```php
// true
ArrayHelper::isIn('a', ['a']);
// true
ArrayHelper::isIn('a', new ArrayObject(['a']));

// true
ArrayHelper::isSubset(new ArrayObject(['a', 'c']), new ArrayObject(['a', 'b', 'c']));
```
