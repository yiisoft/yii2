コアバリデータ
==============

Yii は、一般的に使われる一連のコアバリデータを提供しています。
コアバリデータは、主として、`yii\validators` 名前空間の下にあります。
長ったらしいバリデータクラス名を使う代りに、*エイリアス* を使って使用するコアバリデータを指定することが出来ます。
例えば、[[yii\validators\RequiredValidator]] クラスを参照するのに `required` というエイリアスを使うことが出来ます。

```php
public function rules()
{
    return [
        [['email', 'password'], 'required'],
    ];
}
```

[[yii\validators\Validator::builtInValidators]] プロパティがサポートされている全てのコアバリデータのエイリアスを宣言しています。

以下では、全てのコアバリデータについて、主な使用方法とプロパティを説明します。


## [[yii\validators\BooleanValidator|boolean]] <a name="boolean"></a>

```php
[
    // データ型にかかわらず、"selected" が 0 または 1 であることを検証する
    ['selected', 'boolean'],

    // "deleted" が boolean 型であり、true か false であることを検証する
    ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

このバリデータは、入力値が真偽値であることを検証します。

- `trueValue`: *true* を表す値。デフォルト値は `'1'`。
- `falseValue`: *false* を表す値。デフォルト値は `'0'`。
- `strict`: 入力値の型が `trueValue` と `falseValue` の型と一致しなければならないかどうか。デフォルト値は `false`。


> Note|注意: HTML フォームで送信されたデータ入力値は全て文字列であるため、通常は、[[yii\validators\BooleanValidator::strict|strict]] プロパティは false のままにすべきです。


## [[yii\captcha\CaptchaValidator|captcha]] <a name="captcha"></a>

```php
[
    ['verificationCode', 'captcha'],
]
```

このバリデータは、通常、[[yii\captcha\CaptchaAction]] および [[yii\captcha\Captcha]] と一緒に使われ、入力値が [[yii\captcha\Captcha|CAPTCHA]] ウィジェットによって表示された検証コードと同じであることを検証します。

- `caseSensitive`: 検証コードの比較で大文字と小文字を区別するかどうか。デフォルト値は false。
- `captchaAction`: CAPTCHA 画像を表示する [[yii\captcha\CaptchaAction|CAPTCHA action]] に対応する [ルート](structure-controllers.md#routes)。デフォルト値は `'site/captcha'`。
- `skipOnEmpty`: 入力値が空のときに検証をスキップできるかどうか。デフォルト値は false で、入力が必須であることを意味します。
  

## [[yii\validators\CompareValidator|compare]] <a name="compare"></a>

```php
[
    // "password" 属性の値が "password_repeat" 属性の値と同じであることを検証する
    ['password', 'compare'],

    // "age" が 30 以上であることを検証する
    ['age', 'compare', 'compareValue' => 30, 'operator' => '>='],
]
```

このバリデータは指定された入力値を他の値と比較し、両者の関係が `operator` プロパティで指定されたものであることを確認します。

- `compareAttribute`: その値が比較対象となる属性の名前。
  このバリデータが属性を検証するのに使用されるとき、このプロパティのデフォルト値はその属性の名前に接尾辞 `_repeat` を付けた名前になります。
  例えば、検証される属性が `password` であれば、このプロパティのデフォルト値は `password_repeat` となります。
- `compareValue`: 入力値が比較される定数値。
  このプロパティと `compareAttribute` の両方が指定された場合は、このプロパティが優先されます。
- `operator`: 比較演算子。デフォルト値は `==` で、入力値が `compareAttribute` の値または `compareValue` と等しいことを検証することを意味します。
  次の演算子がサポートされています。
     * `==`: 二つの値が等しいことを検証。厳密でない比較を行う。
     * `===`: 二つの値が等しいことを検証。厳密な比較を行う。
     * `!=`: 二つの値が等しくないことを検証。厳密でない比較を行う。
     * `!==`: 二つの値が等しくないことを検証。厳密な比較を行う。
     * `>`: 検証される値が比較される値よりも大きいことを検証する。
     * `>=`: 検証される値が比較される値よりも大きいか等しいことを検証する。
     * `<`: 検証される値が比較される値よりも小さいことを検証する。
     * `<=`: 検証される値が比較される値よりも小さいか等しいことを検証する。


## [[yii\validators\DateValidator|date]] <a name="date"></a>

```php
[
    [['from_date', 'to_date'], 'date'],
]
```

このバリデータは、入力値が正しい書式の date、time、または datetime であることを検証します。
オプションとして、入力値を UNIX タイムスタンプに変換して、[[yii\validators\DateValidator::timestampAttribute|timestampAttribute]] によって指定された属性に保存することも出来ます。

- `format`: 検証される値が従っているべき日付・時刻の書式。
  これには [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax) で記述されている日付・時刻のパターンを使うことが出来ます。
  あるいは、PHP の `Datetime` クラスによって認識される書式に接頭辞 `php:` を付けた文字列でも構いません。
  サポートされている書式については、<http://php.net/manual/ja/datetime.createfromformat.php> を参照してください。
  このプロパティが設定されていないときは、`Yii::$app->formatter->dateFormat` の値を取ります。
- `timestampAttribute`: このバリデータが入力された日付・時刻から変換した UNIX タイムスタンプを代入することが出来る属性の名前。

入力が必須でない場合には、date バリデータに加えて、default バリデータ (フィルタ) を追加すれば、空の入力値が `NULL` として保存されることを保証することが出来ます。
そうしないと、データベースに `0000-00-00` という日付が保存されたり、デートピッカーの入力フィールドが `1970-01-01` になったりしてしまいます。

```php
[['from_date', 'to_date'], 'default', 'value' => null],
```

## [[yii\validators\DefaultValueValidator|default]] <a name="default"></a>

```php
[
    // 空のときは "age" を null にする
    ['age', 'default', 'value' => null],

    // 空のときは "country" を "USA" にする
    ['country', 'default', 'value' => 'USA'],

    // 空のときは "from" と "to" に今日から三日後・六日後の日付にする
    [['from', 'to'], 'default', 'value' => function ($model, $attribute) {
        return date('Y-m-d', strtotime($attribute === 'to' ? '+3 days' : '+6 days'));
    }],
]
```

このバリデータはデータを検証しません。
その代りに、検証される属性が空のときに、その属性にデフォルト値を割り当てます。

- `value`: デフォルト値、または、デフォルト値を返す PHP コーラブル。
  検証される属性が空のときにこのデフォルト値が割り当てられます。
  PHP コーラブルのシグニチャは、次のものでなければなりません。

```php
function foo($model, $attribute) {
    // ... $value を計算 ...
    return $value;
}
```

> Info: 値が空であるか否かを決定する方法については、独立したトピックとして、[空の入力値を扱う](input-validation.md#handling-empty-inputs) の節でカバーされています。


## [[yii\validators\NumberValidator|double]] <a name="double"></a>

```php
[
    // "salary" が実数であることを検証
    ['salary', 'double'],
]
```

このバリデータは、入力値が実数値であることを検証します。
[number](#number) バリデータと等価です。

- `max`: 上限値 (その値を含む)。
  設定されていない場合は、バリデータが上限値をチェックしないことを意味します。
- `min`: 下限値 (その値を含む)。
  設定されていない場合は、バリデータが下限値をチェックしないことを意味します。


## [[yii\validators\EmailValidator|email]] <a name="email"></a>

```php
[
    // "email" が有効なメールアドレスであることを検証
    ['email', 'email'],
]
```

このバリデータは、入力値が有効なメールアドレスであることを検証します。

- `allowName`: メールアドレスに表示名を許容するか否か (例えば、`John Smith <john.smith@example.com>`)。デフォルト値は false。
- `checkDNS`: メールのドメインが存在して A または MX レコードを持っているかどうかをチェックするか否か。
  このチェックは、メールアドレスが実際には有効なものでも、一時的な DNS の問題によって失敗する場合があることに注意してください。
  デフォルト値は false。
- `enableIDN`: 検証のプロセスが IDN (国際化ドメイン名) を考慮に入れるか否か。
  デフォルト値は false。
  IDN のバリデーションを使用するためには、`intl` PHP 拡張をインストールして有効化する必要があることに注意してください。そうしないと、例外が投げられます。


## [[yii\validators\ExistValidator|exist]] <a name="exist"></a>

```php
[
    // a1 の値が属性 "a1" で表されるカラムに存在する必要がある
    ['a1', 'exist'],

    // a1 の値が属性 "a2" で表されるカラムに存在する必要がある
    ['a1', 'exist', 'targetAttribute' => 'a2'],

    // a1 の値が "a1" のカラム、a2 の値が "a2" のカラムに存在する必要がある
    // 両者はともにエラーメッセージを受け取る
    [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']],

    // a1 の値が "a1" のカラム、a2 の値が "a2" のカラムに存在する必要がある
    // a1 のみがエラーメッセージを受け取る
    ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']],

    // a2 の値が "a2" のカラム、a1 の値が "a3" のカラムに存在する必要がある
    // a1 がエラーメッセージを受け取る
    ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']],

    // a1 の値が "a1" のカラムに存在する必要がある
    // a1 が配列である場合は、その全ての要素が "a1" のカラムに存在する必要がある
    ['a1', 'exist', 'allowArray' => true],
]
```

このバリデータは、入力値がテーブルのカラムに存在することを検証します。
[アクティブレコード](db-active-record.md) モデルの属性に対してのみ働きます。
一つのカラムに対しても、複数のカラムに対しても、バリデーションをサポートします。

- `targetClass`: 検証される入力値を探すために使用される [アクティブレコード](db-active-record.md) クラスの名前。
  設定されていない場合は、現在検証されているモデルのクラスが使用されます。
- `targetAttribute`: 入力値の存在を検証するために使用される `targetClass` の属性の名前。
  設定されていない場合は、現在検証されている属性の名前が使用されます。
  複数のカラムの存在を同時に検証するために配列を使うことが出来ます。
  配列の値は存在を検証するのに使用される属性であり、配列のキーはその値が検証される属性です。
  キーと値が同じ場合は、値だけを指定することが出来ます。
- `filter`: 入力値の存在をチェックするのに使用される DB クエリに適用される追加のフィルタ。
  これには、文字列、または、追加のクエリ条件を表現する配列を使うことが出来ます
  (クエリ条件の書式については、[[yii\db\Query::where()]] を参照してください)。
  または、`function ($query)` というシグニチャを持つ無名関数でも構いません。
  `$query` は関数の中で修正できる [[yii\db\Query|Query]] オブジェクトです。
- `allowArray`: 入力値が配列であることを許容するか否か。
  デフォルト値は false。
  このプロパティが true で入力値が配列であった場合は、配列の全ての要素がターゲットのカラムに存在しなければなりません。
  `targetAttribute` を配列で指定して複数のカラムに対して検証しようとしている場合は、このプロパティを true に設定することが出来ないことに注意してください。


## [[yii\validators\FileValidator|file]] <a name="file"></a>

```php
[
    // "primaryImage" が PNG、JPG、または GIF 形式のアップロードされた
    // 画像ファイルであり、ファイルサイズが 1MB 未満であることを検証
    ['primaryImage', 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024*1024],
]
```

このバリデータは、入力が有効なアップロードされたファイルであることを検証します。

- `extensions`: a list of file name extensions that are allowed to be uploaded. This can be either
  an array or a string consisting of file extension names separated by space or comma (e.g. "gif, jpg").
  Extension names are case-insensitive. Defaults to null, meaning all file name
  extensions are allowed.
- `mimeTypes`: a list of file MIME types that are allowed to be uploaded. This can be either an array
  or a string consisting of file MIME types separated by space or comma (e.g. "image/jpeg, image/png").
  Mime type names are case-insensitive. Defaults to null, meaning all MIME types are allowed.
- `minSize`: the minimum number of bytes required for the uploaded file. Defaults to null, meaning no lower limit.
- `maxSize`: the maximum number of bytes allowed for the uploaded file. Defaults to null, meaning no upper limit.
- `maxFiles`: the maximum number of files that the given attribute can hold. Defaults to 1, meaning
  the input must be a single uploaded file. If it is greater than 1, then the input must be an array
  consisting of at most `maxFiles` number of uploaded files.
- `checkExtensionByMimeType`: whether to check the file extension by the file's MIME type. If the extension produced by
  MIME type check differs from the uploaded file extension, the file will be considered as invalid. Defaults to true,
  meaning perform such check.

`FileValidator` is used together with [[yii\web\UploadedFile]]. Please refer to the [Uploading Files](input-file-upload.md)
section for complete coverage about uploading files and performing validation about the uploaded files.


## [[yii\validators\FilterValidator|filter]] <a name="filter"></a>

```php
[
    // trim "username" and "email" inputs
    [['username', 'email'], 'filter', 'filter' => 'trim', 'skipOnArray' => true],

    // normalize "phone" input
    ['phone', 'filter', 'filter' => function ($value) {
        // normalize phone input here
        return $value;
    }],
]
```

This validator does not validate data. Instead, it applies a filter on the input value and assigns it
back to the attribute being validated.

- `filter`: a PHP callback that defines a filter. This can be a global function name, an anonymous function, etc.
  The function signature must be `function ($value) { return $newValue; }`. This property must be set.
- `skipOnArray`: whether to skip the filter if the input value is an array. Defaults to false.
  Note that if the filter cannot handle array input, you should set this property to be true. Otherwise some
  PHP error might occur.

> Tip: If you want to trim input values, you may directly use the [trim](#trim) validator.

> Tip: There are many PHP functions that have the signature expected for the `filter` callback.
> For example to apply type casting (using e.g. [intval](http://php.net/manual/en/function.intval.php),
> [boolval](http://php.net/manual/en/function.boolval.php), ...) to ensure a specific type for an attribute,
> you can simply specify the function names of the filter without the need to wrap them in a closure:
>
> ```php
> ['property', 'filter', 'filter' => 'boolval'],
> ['property', 'filter', 'filter' => 'intval'],
> ```


## [[yii\validators\ImageValidator|image]] <a name="image"></a>

```php
[
    // checks if "primaryImage" is a valid image with proper size
    ['primaryImage', 'image', 'extensions' => 'png, jpg',
        'minWidth' => 100, 'maxWidth' => 1000,
        'minHeight' => 100, 'maxHeight' => 1000,
    ],
]
```

This validator checks if the input value represents a valid image file. It extends from the [file](#file) validator
and thus inherits all its properties. Besides, it supports the following additional properties specific for image
validation purpose:

- `minWidth`: the minimum width of the image. Defaults to null, meaning no lower limit.
- `maxWidth`: the maximum width of the image. Defaults to null, meaning no upper limit.
- `minHeight`: the minimum height of the image. Defaults to null, meaning no lower limit.
- `maxHeight`: the maximum height of the image. Defaults to null, meaning no upper limit.


## [[yii\validators\RangeValidator|in]] <a name="in"></a>

```php
[
    // checks if "level" is 1, 2 or 3
    ['level', 'in', 'range' => [1, 2, 3]],
]
```

This validator checks if the input value can be found among the given list of values.

- `range`: a list of given values within which the input value should be looked for.
- `strict`: whether the comparison between the input value and the given values should be strict
  (both the type and value must be the same). Defaults to false.
- `not`: whether the validation result should be inverted. Defaults to false. When this property is set true,
  the validator checks if the input value is NOT among the given list of values.
- `allowArray`: whether to allow the input value to be an array. When this is true and the input value is an array,
  every element in the array must be found in the given list of values, or the validation would fail.


## [[yii\validators\NumberValidator|integer]] <a name="integer"></a>

```php
[
    // checks if "age" is an integer
    ['age', 'integer'],
]
```

This validator checks if the input value is an integer.

- `max`: the upper limit (inclusive) of the value. If not set, it means the validator does not check the upper limit.
- `min`: the lower limit (inclusive) of the value. If not set, it means the validator does not check the lower limit.


## [[yii\validators\RegularExpressionValidator|match]] <a name="match"></a>

```php
[
    // checks if "username" starts with a letter and contains only word characters
    ['username', 'match', 'pattern' => '/^[a-z]\w*$/i']
]
```

This validator checks if the input value matches the specified regular expression.

- `pattern`: the regular expression that the input value should match. This property must be set,
  or an exception will be thrown.
- `not`: whether to invert the validation result. Defaults to false, meaning the validation succeeds
   only if the input value matches the pattern. If this is set true, the validation is considered
   successful only if the input value does NOT match the pattern.


## [[yii\validators\NumberValidator|number]] <a name="number"></a>

```php
[
    // checks if "salary" is a number
    ['salary', 'number'],
]
```

This validator checks if the input value is a number. It is equivalent to the [double](#double) validator.

- `max`: the upper limit (inclusive) of the value. If not set, it means the validator does not check the upper limit.
- `min`: the lower limit (inclusive) of the value. If not set, it means the validator does not check the lower limit.


## [[yii\validators\RequiredValidator|required]] <a name="required"></a>

```php
[
    // checks if both "username" and "password" are not empty
    [['username', 'password'], 'required'],
]
```

This validator checks if the input value is provided and not empty.

- `requiredValue`: the desired value that the input should be. If not set, it means the input should not be empty.
- `strict`: whether to check data types when validating a value. Defaults to false.
  When `requiredValue` is not set, if this property is true, the validator will check if the input value is
  not strictly null; If this property is false, the validator will use a loose rule to determine a value is empty or not.
  When `requiredValue` is set, the comparison between the input and `requiredValue` will also check data types
  if this property is true.

> Info: How to determine if a value is empty or not is a separate topic covered
  in the [Empty Values](input-validation.md#handling-empty-inputs) section.


## [[yii\validators\SafeValidator|safe]] <a name="safe"></a>

```php
[
    // marks "description" to be a safe attribute
    ['description', 'safe'],
]
```

This validator does not perform data validation. Instead, it is used to mark an attribute to be
a [safe attribute](structure-models.md#safe-attributes).


## [[yii\validators\StringValidator|string]] <a name="string"></a>

```php
[
    // checks if "username" is a string whose length is between 4 and 24
    ['username', 'string', 'length' => [4, 24]],
]
```

This validator checks if the input value is a valid string with certain length.

- `length`: specifies the length limit of the input string being validated. This can be specified
   in one of the following forms:
     * an integer: the exact length that the string should be of;
     * an array of one element: the minimum length of the input string (e.g. `[8]`). This will overwrite `min`.
     * an array of two elements: the minimum and maximum lengths of the input string (e.g. `[8, 128]`).
     This will overwrite both `min` and `max`.
- `min`: the minimum length of the input string. If not set, it means no minimum length limit.
- `max`: the maximum length of the input string. If not set, it means no maximum length limit.
- `encoding`: the encoding of the input string to be validated. If not set, it will use the application's
  [[yii\base\Application::charset|charset]] value which defaults to `UTF-8`.


## [[yii\validators\FilterValidator|trim]] <a name="trim"></a>

```php
[
    // trims the white spaces surrounding "username" and "email"
    [['username', 'email'], 'trim'],
]
```

This validator does not perform data validation. Instead, it will trim the surrounding white spaces around
the input value. Note that if the input value is an array, it will be ignored by this validator.


## [[yii\validators\UniqueValidator|unique]] <a name="unique"></a>

```php
[
    // a1 needs to be unique in the column represented by the "a1" attribute
    ['a1', 'unique'],

    // a1 needs to be unique, but column a2 will be used to check the uniqueness of the a1 value
    ['a1', 'unique', 'targetAttribute' => 'a2'],

    // a1 and a2 need to be unique together, and they both will receive error message
    [['a1', 'a2'], 'unique', 'targetAttribute' => ['a1', 'a2']],

    // a1 and a2 need to be unique together, only a1 will receive error message
    ['a1', 'unique', 'targetAttribute' => ['a1', 'a2']],

    // a1 needs to be unique by checking the uniqueness of both a2 and a3 (using a1 value)
    ['a1', 'unique', 'targetAttribute' => ['a2', 'a1' => 'a3']],
]
```

This validator checks if the input value is unique in a table column. It only works
with [Active Record](db-active-record.md) model attributes. It supports validation against
either a single column or multiple columns.

- `targetClass`: the name of the [Active Record](db-active-record.md) class that should be used
  to look for the input value being validated. If not set, the class of the model currently being validated will be used.
- `targetAttribute`: the name of the attribute in `targetClass` that should be used to validate the uniqueness
  of the input value. If not set, it will use the name of the attribute currently being validated.
  You may use an array to validate the uniqueness of multiple columns at the same time. The array values
  are the attributes that will be used to validate the uniqueness, while the array keys are the attributes
  whose values are to be validated. If the key and the value are the same, you can just specify the value.
- `filter`: additional filter to be applied to the DB query used to check the uniqueness of the input value.
  This can be a string or an array representing the additional query condition (refer to [[yii\db\Query::where()]]
  on the format of query condition), or an anonymous function with the signature `function ($query)`, where `$query`
  is the [[yii\db\Query|Query]] object that you can modify in the function.


## [[yii\validators\UrlValidator|url]] <a name="url"></a>

```php
[
    // checks if "website" is a valid URL. Prepend "http://" to the "website" attribute
    // if it does not have a URI scheme
    ['website', 'url', 'defaultScheme' => 'http'],
]
```

This validator checks if the input value is a valid URL.

- `validSchemes`: an array specifying the URI schemes that should be considered valid. Defaults to `['http', 'https']`,
  meaning both `http` and `https` URLs are considered to be valid.
- `defaultScheme`: the default URI scheme to be prepended to the input if it does not have the scheme part.
  Defaults to null, meaning do not modify the input value.
- `enableIDN`: whether the validator should take into account IDN (internationalized domain names).
  Defaults to false. Note that in order to use IDN validation you have to install and enable the `intl` PHP
  extension, otherwise an exception would be thrown.

