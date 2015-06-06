配列ヘルパ
==========

[PHP の充実した配列関数](http://php.net/manual/ja/book.array.php) への追加として、Yii の配列ヘルパは、配列をさらに効率的に扱うことを可能にするスタティックなメソッドを提供しています。


## 値を取得する <span id="getting-values"></span>

配列、オブジェクト、またはその両方から成る複雑な構造から標準的な PHP を使って値を取得することは、非常に面倒くさい仕事です。
最初に `isset` でキーの存在をチェックしなければならず、次に、キーが存在していれば値を取得し、存在していなければ、デフォルト値を提供しなければなりません。

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

三番目のオプションの引数はデフォルト値であり、指定されない場合は `null` となります。
以下のようにして使用します。

```php
$username = ArrayHelper::getValue($comment, 'user.username', 'Unknown');
```

値を取得して、その直後にそれを配列から削除したい場合は、`remove` メソッドを使うことが出来ます。

```php
$array = ['type' => 'A', 'options' => [1, 2]];
$type = ArrayHelper::remove($array, 'type');
```

このコードを実行した後では、`$array` には `['options' => [1, 2]]` が含まれ、`$type` は `A` となります。
`getValue` メソッドとは違って、`remove` は単純なキー名だけをサポートすることに注意してください。


## キーの存在をチェックする <span id="checking-existence-of-keys"></span>

`ArrayHelper::keyExists` は、大文字と小文字を区別しないキーの比較をサポートすることを除いて、[array_key_exists](http://php.net/manual/ja/function.array-key-exists.php) と同じ動作をします。
例えば、

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

データ行またはオブジェクトの配列から、あるカラムの値を取得する必要があることがよくあります。
良くある例は、ID のリストの取得です。

```php
$data = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$ids = ArrayHelper::getColumn($array, 'id');
```

結果は `['123', '345']` となります。

追加の変形が要求されたり、値の取得方法が複雑であったりする場合は、無名関数を二番目の引数として指定することが出来ます。

```php
$result = ArrayHelper::getColumn($array, function ($element) {
    return $element['id'];
});
```


## 配列を再インデックスする <span id="reindexing-arrays"></span>

指定されたキーに従って配列にインデックスを付けるために、`index` メソッドを使うことが出来ます。
入力値の配列は、多次元配列であるか、オブジェクトの配列でなければなりません。
キーは、サブ配列のキーの名前、オブジェクトのプロパティの名前、または、配列要素を与えられてキーの値を返す無名関数とすることが出来ます。

キーの値が null である場合、対応する配列要素は破棄されて、結果には入りません。
例えば、

```php
$array = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$result = ArrayHelper::index($array, 'id');
// 結果は次のようになります
// [
//     '123' => ['id' => '123', 'data' => 'abc'],
//     '345' => ['id' => '345', 'data' => 'def'],
// ]

// 無名関数を使う
$result = ArrayHelper::index($array, function ($element) {
    return $element['id'];
});
```


## マップを作成する <span id="building-maps"></span>

多次元配列またはオブジェクトの配列からマップ (キー-値 のペア) を作成するためには `map` メソッドを使うことが出来ます。
`$from` と `$to` のパラメータで、マップを構成するキー名またはプロパティ名を指定します。
オプションで、グループ化のためのフィールド `$group` に従って、マップをグループ化することも出来ます。
例えば、

```php
$array = [
    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
);

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

`multisort` メソッドは、オブジェクトの配列または入れ子にされた配列を、一つまたは複数のキーによって並べ替えることを手助けします。
例えば、

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
    return isset($item['age']) ? ['age', 'name'] : 'name';
});
```

三番目の引数は並べ替えの順序です。
一つのキーによる並べ替えの場合は、`SORT_ASC` か `SORT_DESC` のいずれかです。
複数の値による並べ替えの場合は、並べ替えの順序の配列を渡して、値ごとに違う順序で並べ替えることが出来ます。

最後の引数は並べ替えのフラグで、PHP の [sort()](http://php.net/manual/ja/function.sort.php) 関数に渡されるのと同じ値を取ることが出来ます。


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

デフォルトでは、値だけがエンコードされます。
二番目の引数を `false` として渡すことによって、配列のキーもエンコードすることが出来ます。
エンコードにはアプリケーションの文字セットが使用されますが、三番目の引数によってそれを変更することも出来ます。


## 配列をマージする <span id="merging-arrays"></span>

```php
  /**
    * 二つ以上の配列を再帰的に一つの配列にマージします。
    * 各配列に同じ文字列のキー値を持つ要素がある場合は、(array_merge_recursive とは違って)
    * 後のものが前のものを上書きします。
    * 両方の配列が、同じキーを持つ配列型の要素を持っている場合は、再帰的なマージが実行されます。
    * 添字型の要素については、後の配列の要素が前の配列の要素の後に追加されます。
    * @param array $a マージ先の配列
    * @param array $b マージ元の配列。追加の配列を三番目の引数、四番目の引数、、、として指定可能です。
    * @return array マージされた配列 (元の配列は変更されません。)
    */
    public static function merge($a, $b)
```


## オブジェクトを配列に変換する <span id="converting-objects-to-arrays"></span>

オブジェクトまたはオブジェクトの配列を配列に変換する必要があることがよくあります。
最もよくあるのは、REST API によってデータ配列を提供するなどの目的で、アクティブレコードモデルを変換する場合です。
そうするために、次のコードを使うことが出来ます。

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

二番目の引数は、クラスごとの変換マップです。
ここでは、`Post` モデルの変換マップを設定しています。
変換マップの配列が、一連のマップを含んでいます。各マップは以下のいずれかの形式を取ります。

- フィールド名 - そのままインクルードされる。
- キー/値 のペア - 配列のキー名にしたい文字列と、値を取得すべきモデルのカラムの名前。
- キー/値 のペア - 配列のキー名にしたい文字列と、値を返すコールバック。

変換の結果は以下のようになります。


```php
[
    'id' => 123,
    'title' => 'test',
    'createTime' => '2013-01-01 12:00AM',
    'length' => 301,
]
```

特定のクラスについて、配列に変換するデフォルトの方法を提供するためには、そのクラスの [[yii\base\Arrayable|Arrayable]] インタフェイスを実装することが出来ます。
