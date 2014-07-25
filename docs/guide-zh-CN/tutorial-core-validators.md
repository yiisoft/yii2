核心验证器（Core Validators）
===============

Yii 提供一系列常用的核心验证器，主要存在于 `yii\validators` 命名空间之下。为了避免使用冗长的类名，你可以直接用**昵称**来指定相应的核心验证器。比如你可以用 `required` 昵称代指 [[yii\validators\RequiredValidator]] 类：

```php
public function rules()
{
    return [
        [['email', 'password'], 'required'],
    ];
}
```

[[yii\validators\Validator::builtInValidators]] 属性声明了所有被支持的验证器昵称。

下面，我们将详细介绍每一款验证器的主要用法和属性。


## [[yii\validators\BooleanValidator|boolean（布尔型）]] <a name="boolean"></a>

```php
[
    // 检查 "selected" 是否为 0 或 1，无视数据类型
    ['selected', 'boolean'],

    // 检查 "deleted" 是否为布尔类型，即 true 或 false
    ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

该验证器检查输入值是否为一个布尔值。

- `trueValue`： 代表**真**的值。默认为 `'1'`。
- `falseValue`：代表**假**的值。默认为 `'0'`。
- `strict`：是否要求待测输入必须严格匹配 `trueValue` 或 `falseValue`。默认为 `false`。


> 注意：因为通过 HTML 表单传递的输入数据都是字符串类型，所以一般情况下你都需要保持
  [[yii\validators\BooleanValidator::strict|strict]] 属性为假。


## [[yii\captcha\CaptchaValidator|captcha（验证码）]] <a name="captcha"></a>

```php
[
    ['verificationCode', 'captcha'],
]
```

该验证器通常配合 [[yii\captcha\CaptchaAction]] 以及 [[yii\captcha\Captcha]]
使用，以确保某一输入与 [[yii\captcha\Captcha|CAPTCHA]] 小部件所显示的验证代码（verification code）相同。

- `caseSensitive`：对验证代码的比对是否要求大小写敏感。默认为 false。
- `captchaAction`：指向用于渲染 CAPTCHA 图片的 [[yii\captcha\CaptchaAction|CAPTCHA action]] 的 [路由](structure-controllers.md#routes)。默认为 `'site/captcha'`。
- `skipOnEmpty`：当输入为空时，是否跳过验证。默认为 false，也就是输入值为必需项。
  

## [[yii\validators\CompareValidator|compare（比较）]] <a name="compare"></a>

```php
[
    // 检查 "password" 特性的值是否与 "password_repeat" 的值相同
    ['password', 'compare'],

    // 检查年龄是否大于等于 30
    ['age', 'compare', 'compareValue' => 30, 'operator' => '>='],
]
```

该验证器比较两个特定输入值之间的关系是否与 `operator` 属性所指定的相同。

- `compareAttribute`：用于与原特性相比较的特性名称。当该验证器被用于验证某目标特性时，该属性会默认为目标属性加后缀 `_repeat`。举例来说，若目标特性为 `password`，则该属性默认为 `password_repeat`。
- `compareValue`：用于与输入值相比较的常量值。当该属性与 `compareAttribute` 属性同时被指定时，该属性优先被使用。
- `operator`：比较操作符。默认为 `==`，意味着检查输入值是否与 `compareAttribute` 或 `compareValue` 的值相等。该属性支持如下操作符：
     * `==`：检查两值是否相等。比对为非严格模式。
     * `===`：检查两值是否相等。比对为严格模式。
     * `!=`：检查两值是否不等。 比对为非严格模式。
     * `!==`：检查两值是否不等。比对为严格模式。
     * `>`：检查待测目标值是否大于给定被测值。
     * `>=`：检查待测目标值是否大于等于给定被测值。
     * `<`：检查待测目标值是否小于给定被测值。
     * `<=`：检查待测目标值是否小于等于给定被测值。


## [[yii\validators\DateValidator|date（日期）]] <a name="date"></a>

```php
[
    [['from', 'to'], 'date'],
]
```

该验证器检查输入值是否为适当格式的 date，time，或者 datetime。另外，它还可以帮你把输入值转换为一个 UNIX 时间戳并保存到 [[yii\validators\DateValidator::timestampAttribute|timestampAttribute]] 属性所指定的特性里。

- `format`：待测的 日期/时间 格式。请参考
  [date_create_from_format() 相关的 PHP 手册](http://www.php.net/manual/zh/datetime.createfromformat.php)
  了解设定格式字符串的更多细节。默认值为 `'Y-m-d'`。
- `timestampAttribute`：用于保存由输入时间/日期所转换的 UNIX 时间戳的特性名称。


## [[yii\validators\DefaultValueValidator|default（默认值）]] <a name="default"></a>

```php
[
    // 若 "age" 为空，则将其设为 null
    ['age', 'default', 'value' => null],

    // 若 "country" 为空，则将其设为 "USA"
    ['country', 'default', 'value' => 'USA'],

    // 若 "from" 和 "to" 为空，则分别给他们分配自今天起，3 天后和 6 天后的日期。
    [['from', 'to'], 'default', 'value' => function ($model, $attribute) {
        return date('Y-m-d', strtotime($attribute === 'to' ? '+3 days' ：'+6 days'));
    }],
]
```

该验证器并不检查数据。而是，给为空的待测特性分配默认值。

- `value`：默认值，或一个返回默认值的 PHP Callable 对象（即回调函数）。它们会分配给检测为空的待测特性。PHP 回调方法的样式如下：

```php
function foo($model, $attribute) {
    // ... compute $value ...
    return $value;
}
```

> 补充：如何判断待测值是否为空，被写在另外一个话题的[处理空输入](input-validation.md#handling-empty-inputs)章节。


## [[yii\validators\NumberValidator|double（双精度浮点型）]] <a name="double"></a>

```php
[
    // 检查 "salary" 是否为浮点数
    ['salary', 'double'],
]
```

该验证器检查输入值是否为双精度浮点数。他等效于 [number](#number) 验证器。

- `max`：上限值（含界点）。若不设置，则验证器不检查上限。
- `min`：下限值（含界点）。若不设置，则验证器不检查下限。


## [[yii\validators\EmailValidator|email（电子邮件）]] <a name="email"></a>

```php
[
    // 检查 "email" 是否为有效的邮箱地址
    ['email', 'email'],
]
```

该验证器检查输入值是否为有效的邮箱地址。

- `allowName`：检查是否允许带名称的电子邮件地址 (e.g. `张三 <John.san@example.com>`)。 默认为 false。
- `checkDNS`：检查邮箱域名是否存在，且有没有对应的 A 或 MX 记录。不过要知道，有的时候该项检查可能会因为临时性 DNS 故障而失败，哪怕它其实是有效的。默认为 false。
- `enableIDN`：验证过程是否应该考虑 IDN（internationalized domain names，国际化域名，也称多语种域名，比如中文域名）。
  默认为 false。要注意但是为使用 IDN 验证功能，请先确保安装并开启 `intl` PHP 扩展，不然会导致抛出异常。


## [[yii\validators\ExistValidator|exist（存在）]] <a name="exist"></a>

```php
[
    // a1 needs to exist in the column represented by the "a1" attribute
    ['a1', 'exist'],

    // a1 needs to exist, but its value will use a2 to check for the existence
    ['a1', 'exist', 'targetAttribute' => 'a2'],

    // a1 and a2 need to exist together, and they both will receive error message
    [['a1', 'a2'], 'exist', 'targetAttribute' => ['a1', 'a2']],

    // a1 and a2 need to exist together, only a1 will receive error message
    ['a1', 'exist', 'targetAttribute' => ['a1', 'a2']],

    // a1 needs to exist by checking the existence of both a2 and a3 (using a1 value)
    ['a1', 'exist', 'targetAttribute' => ['a2', 'a1' => 'a3']],

    // a1 needs to exist. If a1 is an array, then every element of it must exist.
    ['a1', 'exist', 'allowArray' => true],
]
```

该验证器检查输入值是否在表字段中存在。它只对[活动记录](db-active-record.md)模型的特性起作用。它支持对一个或多过字段的验证。

- `targetClass`：the name of the [Active Record](db-active-record.md) class that should be used
  to look for the input value being validated. If not set, the class of the model currently being validated will be used.
- `targetAttribute`：the name of the attribute in `targetClass` that should be used to validate the existence
  of the input value. If not set, it will use the name of the attribute currently being validated.
  You may use an array to validate the existence of multiple columns at the same time. The array values
  are the attributes that will be used to validate the existence, while the array keys are the attributes
  whose values are to be validated. If the key and the value are the same, you can just specify the value.
- `filter`：additional filter to be applied to the DB query used to check the existence of the input value.
  This can be a string or an array representing the additional query condition (refer to [[yii\db\Query::where()]]
  on the format of query condition), or an anonymous function with the signature `function ($query)`, where `$query`
  is the [[yii\db\Query|Query]] object that you can modify in the function.
- `allowArray`：whether to allow the input value to be an array. 默认为 false. If this property is true
  and the input is an array, then every element of the array must exist in the target column. Note that
  this property cannot be set true if you are validating against multiple columns by setting `targetAttribute` as an array.


## [[yii\validators\FileValidator|file]] <a name="file"></a>

```php
[
    // checks if "primaryImage" is an uploaded image file in PNG, JPG or GIF format.
    // the file size must be less than 1MB
    ['primaryImage', 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024*1024],
]
```

This validator checks if the input is a valid uploaded file.

- `extensions`：a list of file name extensions that are allowed to be uploaded. This can be either
  an array or a string consisting of file extension names separated by space or comma (e.g. "gif, jpg").
  Extension names are case-insensitive. 默认为 null, meaning all file name
  extensions are allowed.
- `mimeTypes`：a list of file MIME types that are allowed to be uploaded. This can be either an array
  or a string consisting of file MIME types separated by space or comma (e.g. "image/jpeg, image/png").
  Mime type names are case-insensitive. 默认为 null, meaning all MIME types are allowed.
- `minSize`：the minimum number of bytes required for the uploaded file. 默认为 null, meaning no lower limit.
- `maxSize`：the maximum number of bytes allowed for the uploaded file. 默认为 null, meaning no upper limit.
- `maxFiles`：the maximum number of files that the given attribute can hold. 默认为 1, meaning
  the input must be a single uploaded file. If it is greater than 1, then the input must be an array
  consisting of at most `maxFiles` number of uploaded files.
- `checkExtensionByMimeType`：whether to check the file extension by the file's MIME type. If the extension produced by
  MIME type check differs from the uploaded file extension, the file will be considered as invalid. 默认为 true,
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

- `filter`：a PHP callback that defines a filter. This can be a global function name, an anonymous function, etc.
  The function signature must be `function ($value) { return $newValue; }`. This property must be set.
- `skipOnArray`：whether to skip the filter if the input value is an array. 默认为 false.
  Note that if the filter cannot handle array input, you should set this property to be true. Otherwise some
  PHP error might occur.

> Tip：If you want to trim input values, you may directly use [trim](#trim) validator.


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

- `minWidth`：the minimum width of the image. 默认为 null, meaning no lower limit.
- `maxWidth`：the maximum width of the image. 默认为 null, meaning no upper limit.
- `minHeight`：the minimum height of the image. 默认为 null, meaning no lower limit.
- `maxHeight`：the maximum height of the image. 默认为 null, meaning no upper limit.


## [[yii\validators\RangeValidator|in]] <a name="in"></a>

```php
[
    // checks if "level" is 1, 2 or 3
    ['level', 'in', 'range' => [1, 2, 3]],
]
```

This validator checks if the input value can be found among the given list of values.

- `range`：a list of given values within which the input value should be looked for.
- `strict`：whether the comparison between the input value and the given values should be strict
  (both the type and value must be the same). 默认为 false.
- `not`：whether the validation result should be inverted. 默认为 false. When this property is set true,
  the validator checks if the input value is NOT among the given list of values.
- `allowArray`：whether to allow the input value to be an array. When this is true and the input value is an array,
  every element in the array must be found in the given list of values, or the validation would fail.


## [[yii\validators\NumberValidator|integer]] <a name="integer"></a>

```php
[
    // checks if "age" is an integer
    ['age', 'integer'],
]
```

This validator checks if the input value is an integer.

- `max`：the upper limit (inclusive) of the value. If not set, it means the validator does not check the upper limit.
- `min`：the lower limit (inclusive) of the value. If not set, it means the validator does not check the lower limit.


## [[yii\validators\RegularExpressionValidator|match]] <a name="match"></a>

```php
[
    // checks if "username" starts with a letter and contains only word characters
    ['username', 'match', 'pattern' => '/^[a-z]\w*$/i']
]
```

This validator checks if the input value matches the specified regular expression.

- `pattern`：the regular expression that the input value should match. This property must be set,
  or an exception will be thrown.
- `not`：whether to invert the validation result. 默认为 false, meaning the validation succeeds
   only if the input value matches the pattern. If this is set true, the validation is considered
   successful only if the input value does NOT match the pattern.


## [[yii\validators\NumberValidator|number]] <a name="number"></a>

```php
[
    // checks if "salary" is a number
    ['salary', 'number'],
]
```

This validator checks if the input value is a number. It is equivalent to the [double](#double] validator.

- `max`：the upper limit (inclusive) of the value. If not set, it means the validator does not check the upper limit.
- `min`：the lower limit (inclusive) of the value. If not set, it means the validator does not check the lower limit.


## [[yii\validators\RequiredValidator|required]] <a name="required"></a>

```php
[
    // checks if both "username" and "password" are not empty
    [['username', 'password'], 'required'],
]
```

This validator checks if the input value is provided and not empty.

- `requiredValue`：the desired value that the input should be. If not set, it means the input should not be empty.
- `strict`：whether to check data types when validating a value. 默认为 false.
  When `requiredValue` is not set, if this property is true, the validator will check if the input value is
  not strictly null; If this property is false, the validator will use a loose rule to determine a value is empty or not.
  When `requiredValue` is set, the comparison between the input and `requiredValue` will also check data types
  if this property is true.

> Info：How to determine if a value is empty or not is a separate topic covered
  in the [Empty Values](input-validation.md#empty-values) section.


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

This validator checks if the input value

Validates that the attribute value is of certain length.

- `length`：specifies the length limit of the input string being validated. This can be specified
   in one of the following forms:
     * an integer：the exact length that the string should be of;
     * an array of one element：the minimum length of the input string (e.g. `[8]`). This will overwrite `min`.
     * an array of two elements：the minimum and maximum lengths of the input string (e.g. `[8, 128]`)`.
     This will overwrite both `min` and `max`.
- `min`：the minimum length of the input string. If not set, it means no minimum length limit.
- `max`：the maximum length of the input string. If not set, it means no maximum length limit.
- `encoding`：the encoding of the input string to be validated. If not set, it will use the application's
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

- `targetClass`：the name of the [Active Record](db-active-record.md) class that should be used
  to look for the input value being validated. If not set, the class of the model currently being validated will be used.
- `targetAttribute`：the name of the attribute in `targetClass` that should be used to validate the uniqueness
  of the input value. If not set, it will use the name of the attribute currently being validated.
  You may use an array to validate the uniqueness of multiple columns at the same time. The array values
  are the attributes that will be used to validate the uniqueness, while the array keys are the attributes
  whose values are to be validated. If the key and the value are the same, you can just specify the value.
- `filter`：additional filter to be applied to the DB query used to check the uniqueness of the input value.
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

- `validSchemes`：an array specifying the URI schemes that should be considered valid. 默认为 `['http', 'https']`,
  meaning both `http` and `https` URLs are considered to be valid.
- `defaultScheme`：the default URI scheme to be prepended to the input if it does not have the scheme part.
  默认为 null, meaning do not modify the input value.
- `enableIDN`：whether the validator should take into account IDN (internationalized domain names).
  默认为 false. Note that in order to use IDN validation you have to install and enable the `intl` PHP
  extension, otherwise an exception would be thrown.

