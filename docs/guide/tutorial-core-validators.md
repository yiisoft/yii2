Core Validators
===============

Yii provides a set of commonly used core validators, found primarily under the `yii\validators` namespace.
Instead of using lengthy validator class names, you may use *aliases* to specify the use of these core
validators. For example, you can use the alias `required` to refer to the [[yii\validators\RequiredValidator]] class:

```php
public function rules()
{
    return [
        [['email', 'password'], 'required'],
    ];
}
```

The [[yii\validators\Validator::builtInValidators]] property declares all supported validator aliases.

In the following, we will describe the main usage and properties of every core validator.


## [[yii\validators\BooleanValidator|boolean]] <span id="boolean"></span>

```php
[
    // checks if "selected" is either 0 or 1, regardless of data type
    ['selected', 'boolean'],

    // checks if "deleted" is of boolean type, either true or false
    ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

This validator checks if the input value is a boolean.

- `trueValue`: the value representing *true*. Defaults to `'1'`.
- `falseValue`: the value representing *false*. Defaults to `'0'`.
- `strict`: whether the type of the input value should match that of `trueValue` and `falseValue`. Defaults to `false`.


> Note: Because data input submitted via HTML forms are all strings, you normally should leave the
  [[yii\validators\BooleanValidator::strict|strict]] property as false.


## [[yii\captcha\CaptchaValidator|captcha]] <span id="captcha"></span>

```php
[
    ['verificationCode', 'captcha'],
]
```

This validator is usually used together with [[yii\captcha\CaptchaAction]] and [[yii\captcha\Captcha]]
to make sure an input is the same as the verification code displayed by [[yii\captcha\Captcha|CAPTCHA]] widget.

- `caseSensitive`: whether the comparison of the verification code is case sensitive. Defaults to false.
- `captchaAction`: the [route](structure-controllers.md#routes) corresponding to the
  [[yii\captcha\CaptchaAction|CAPTCHA action]] that renders the CAPTCHA image. Defaults to `'site/captcha'`.
- `skipOnEmpty`: whether the validation can be skipped if the input is empty. Defaults to false,
  which means the input is required.


## [[yii\validators\CompareValidator|compare]] <span id="compare"></span>

```php
[
    // validates if the value of "password" attribute equals to that of "password_repeat"
    ['password', 'compare'],

    // validates if age is greater than or equal to 30
    ['age', 'compare', 'compareValue' => 30, 'operator' => '>='],
]
```

This validator compares the specified input value with another one and make sure if their relationship
is as specified by the `operator` property.

- `compareAttribute`: the name of the attribute whose value should be compared with. When the validator
  is being used to validate an attribute, the default value of this property would be the name of
  the attribute suffixed with `_repeat`. For example, if the attribute being validated is `password`,
  then this property will default to `password_repeat`.
- `compareValue`: a constant value that the input value should be compared with. When both
  of this property and `compareAttribute` are specified, this property will take precedence.
- `operator`: the comparison operator. Defaults to `==`, meaning checking if the input value is equal
  to that of `compareAttribute` or `compareValue`. The following operators are supported:
     * `==`: check if two values are equal. The comparison is done is non-strict mode.
     * `===`: check if two values are equal. The comparison is done is strict mode.
     * `!=`: check if two values are NOT equal. The comparison is done is non-strict mode.
     * `!==`: check if two values are NOT equal. The comparison is done is strict mode.
     * `>`: check if value being validated is greater than the value being compared with.
     * `>=`: check if value being validated is greater than or equal to the value being compared with.
     * `<`: check if value being validated is less than the value being compared with.
     * `<=`: check if value being validated is less than or equal to the value being compared with.


## [[yii\validators\DateValidator|date]] <span id="date"></span>

```php
[
    [['from_date', 'to_date'], 'date'],
]
```

This validator checks if the input value is a date, time or datetime in a proper format.
Optionally, it can convert the input value into a UNIX timestamp or other machine readable format and store it in an attribute
specified via [[yii\validators\DateValidator::timestampAttribute|timestampAttribute]].

- `format`: the date/time format that the value being validated should be in.
   This can be a date time pattern as described in the [ICU manual](http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax).
   Alternatively this can be a string prefixed with `php:` representing a format that can be recognized by the PHP
   `Datetime` class. Please refer to <http://php.net/manual/en/datetime.createfromformat.php> on supported formats.
   If this is not set, it will take the value of `Yii::$app->formatter->dateFormat`.
   See the [[yii\validators\DateValidator::$format|API documentation]] for more details.

- `timestampAttribute`: the name of the attribute to which this validator may assign the UNIX timestamp
  converted from the input date/time. This can be the same attribute as the one being validated. If this is the case,
  the original value will be overwritten with the timestamp value after validation.
  See ["Handling date input with the DatePicker"](https://github.com/yiisoft/yii2-jui/blob/master/docs/guide/topics-date-picker.md) for a usage example.

  Since version 2.0.4, a format and timezone can be specified for this attribute using
  [[yii\validators\DateValidator::$timestampAttributeFormat|$timestampAttributeFormat]] and
  [[yii\validators\DateValidator::$timestampAttributeTimeZone|$timestampAttributeTimeZone]].

- Since version 2.0.4 it is also possible to specify a [[yii\validators\DateValidator::$min|minimum]] or
  [[yii\validators\DateValidator::$max|maximum]] timestamp.

In case the input is optional you may also want to add a [default value filter](#default) in addition to the date validator
to ensure empty input is stored as `NULL`. Other wise you may end up with dates like `0000-00-00` in your database
or `1970-01-01` in the input field of a date picker.

```php
[
    [['from_date', 'to_date'], 'default', 'value' => null],
    [['from_date', 'to_date'], 'date'],
],
```

## [[yii\validators\DefaultValueValidator|default]] <span id="default"></span>

```php
[
    // set "age" to be null if it is empty
    ['age', 'default', 'value' => null],

    // set "country" to be "USA" if it is empty
    ['country', 'default', 'value' => 'USA'],

    // assign "from" and "to" with a date 3 days and 6 days from today, if they are empty
    [['from', 'to'], 'default', 'value' => function ($model, $attribute) {
        return date('Y-m-d', strtotime($attribute === 'to' ? '+3 days' : '+6 days'));
    }],
]
```

This validator does not validate data. Instead, it assigns a default value to the attributes being validated
if the attributes are empty.

- `value`: the default value or a PHP callable that returns the default value which will be assigned to
  the attributes being validated if they are empty. The signature of the PHP callable should be as follows,

```php
function foo($model, $attribute) {
    // ... compute $value ...
    return $value;
}
```

> Info: How to determine if a value is empty or not is a separate topic covered
  in the [Empty Values](input-validation.md#handling-empty-inputs) section.


## [[yii\validators\NumberValidator|double]] <span id="double"></span>

```php
[
    // checks if "salary" is a double number
    ['salary', 'double'],
]
```

This validator checks if the input value is a double number. It is equivalent to the [number](#number) validator.

- `max`: the upper limit (inclusive) of the value. If not set, it means the validator does not check the upper limit.
- `min`: the lower limit (inclusive) of the value. If not set, it means the validator does not check the lower limit.


## [[yii\validators\EachValidator|each]] <span id="each"></span>

> Info: This validator has been available since version 2.0.4.

```php
[
    // checks if every category ID is an integer
    ['categoryIDs', 'each', 'rule' => ['integer']],
]
```

This validator only works with an array attribute. It validates if *every* element of the array can be successfully
validated by a specified validation rule. In the above example, the `categoryIDs` attribute must take an array value
and each array element will be validated by the `integer` validation rule.

- `rule`: an array specifying a validation rule. The first element in the array specifies the class name or
  the alias of the validator. The rest of the name-value pairs in the array are used to configure the validator object.
- `allowMessageFromRule`: whether to use the error message returned by the embedded validation rule. Defaults to true.
  If false, it will use `message` as the error message.

> Note: If the attribute value is not an array, it is considered validation fails and the `message` will be returned
  as the error message.


## [[yii\validators\EmailValidator|email]] <span id="email"></span>

```php
[
    // checks if "email" is a valid email address
    ['email', 'email'],
]
```

This validator checks if the input value is a valid email address.

- `allowName`: whether to allow name in the email address (e.g. `John Smith <john.smith@example.com>`). Defaults to false.
- `checkDNS`, whether to check whether the email's domain exists and has either an A or MX record.
  Be aware that this check may fail due to temporary DNS problems, even if the email address is actually valid.
  Defaults to false.
- `enableIDN`, whether the validation process should take into account IDN (internationalized domain names).
  Defaults to false. Note that in order to use IDN validation you have to install and enable the `intl` PHP extension,
  or an exception would be thrown.


## [[yii\validators\ExistValidator|exist]] <span id="exist"></span>

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

This validator checks if the input value can be found in a table column represented by
an [Active Record](db-active-record.md) attribute. You can use `targetAttribute` to specify the
[Active Record](db-active-record.md) attribute and `targetClass` the corresponding [Active Record](db-active-record.md)
class. If you do not specify them, they will take the values of the attribute and the model class being validated.

You can use this validator to validate against a single column or multiple columns (i.e., the combination of
multiple attribute values should exist).

- `targetClass`: the name of the [Active Record](db-active-record.md) class that should be used
  to look for the input value being validated. If not set, the class of the model currently being validated will be used.
- `targetAttribute`: the name of the attribute in `targetClass` that should be used to validate the existence
  of the input value. If not set, it will use the name of the attribute currently being validated.
  You may use an array to validate the existence of multiple columns at the same time. The array values
  are the attributes that will be used to validate the existence, while the array keys are the attributes
  whose values are to be validated. If the key and the value are the same, you can just specify the value.
- `filter`: additional filter to be applied to the DB query used to check the existence of the input value.
  This can be a string or an array representing the additional query condition (refer to [[yii\db\Query::where()]]
  on the format of query condition), or an anonymous function with the signature `function ($query)`, where `$query`
  is the [[yii\db\Query|Query]] object that you can modify in the function.
- `allowArray`: whether to allow the input value to be an array. Defaults to false. If this property is true
  and the input is an array, then every element of the array must exist in the target column. Note that
  this property cannot be set true if you are validating against multiple columns by setting `targetAttribute` as an array.


## [[yii\validators\FileValidator|file]] <span id="file"></span>

```php
[
    // checks if "primaryImage" is an uploaded image file in PNG, JPG or GIF format.
    // the file size must be less than 1MB
    ['primaryImage', 'file', 'extensions' => ['png', 'jpg', 'gif'], 'maxSize' => 1024*1024],
]
```

This validator checks if the input is a valid uploaded file.

- `extensions`: a list of file name extensions that are allowed to be uploaded. This can be either
  an array or a string consisting of file extension names separated by space or comma (e.g. "gif, jpg").
  Extension names are case-insensitive. Defaults to null, meaning all file name
  extensions are allowed.
- `mimeTypes`: a list of file MIME types that are allowed to be uploaded. This can be either an array
  or a string consisting of file MIME types separated by space or comma (e.g. "image/jpeg, image/png").
  Mime type names are case-insensitive. Defaults to null, meaning all MIME types are allowed.
  For more details, please refer to [common media types](http://en.wikipedia.org/wiki/Internet_media_type#List_of_common_media_types).
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


## [[yii\validators\FilterValidator|filter]] <span id="filter"></span>

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


## [[yii\validators\ImageValidator|image]] <span id="image"></span>

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


## [[yii\validators\RangeValidator|in]] <span id="in"></span>

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


## [[yii\validators\NumberValidator|integer]] <span id="integer"></span>

```php
[
    // checks if "age" is an integer
    ['age', 'integer'],
]
```

This validator checks if the input value is an integer.

- `max`: the upper limit (inclusive) of the value. If not set, it means the validator does not check the upper limit.
- `min`: the lower limit (inclusive) of the value. If not set, it means the validator does not check the lower limit.


## [[yii\validators\RegularExpressionValidator|match]] <span id="match"></span>

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


## [[yii\validators\NumberValidator|number]] <span id="number"></span>

```php
[
    // checks if "salary" is a number
    ['salary', 'number'],
]
```

This validator checks if the input value is a number. It is equivalent to the [double](#double) validator.

- `max`: the upper limit (inclusive) of the value. If not set, it means the validator does not check the upper limit.
- `min`: the lower limit (inclusive) of the value. If not set, it means the validator does not check the lower limit.


## [[yii\validators\RequiredValidator|required]] <span id="required"></span>

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


## [[yii\validators\SafeValidator|safe]] <span id="safe"></span>

```php
[
    // marks "description" to be a safe attribute
    ['description', 'safe'],
]
```

This validator does not perform data validation. Instead, it is used to mark an attribute to be
a [safe attribute](structure-models.md#safe-attributes).


## [[yii\validators\StringValidator|string]] <span id="string"></span>

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


## [[yii\validators\FilterValidator|trim]] <span id="trim"></span>

```php
[
    // trims the white spaces surrounding "username" and "email"
    [['username', 'email'], 'trim'],
]
```

This validator does not perform data validation. Instead, it will trim the surrounding white spaces around
the input value. Note that if the input value is an array, it will be ignored by this validator.


## [[yii\validators\UniqueValidator|unique]] <span id="unique"></span>

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


## [[yii\validators\UrlValidator|url]] <span id="url"></span>

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

