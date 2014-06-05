Validating Input
================

In the [Models](structure-models.md#validation) section, we have described how data validation works
in general. In this section, we will mainly focus on describing core validators, how to define your
own validators, and different ways of using validators.


## Error Messages

## Core Validators

Yii provides a set of commonly used validators, found primarily within the `yii\validators` namespace.

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


### [[yii\validators\BooleanValidator|boolean]] <a name="boolean"></a>

This validator checks if the input value is a boolean.

- `trueValue`: the value representing *true*. Defaults to `'1'`.
- `falseValue`: the value representing *false*. Defaults to `'0'`.
- `strict`: whether the type of the input value should match that of `trueValue` and `falseValue`. Defaults to `false`.

```php
[
    // checks if "selected" is either 0 or 1, regardless of data type
    ['selected', 'boolean'],

    // checks if "deleted" is of boolean type, either true or false
    ['deleted', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
]
```

> Note: Because data input submitted via HTML forms are all strings, you normally should leave the
  [[yii\validators\BooleanValidator::strict|strict]] property as false.


### [[yii\captcha\CaptchaValidator|captcha]] <a name="captcha"></a>

This validator is usually used together with [[yii\captcha\CaptchaAction]] and [[yii\captcha\Captcha]]
to make sure an input is the same as the verification code displayed by [[yii\captcha\Captcha|CAPTCHA]] widget.

- `caseSensitive`: whether the comparison of the verification code is case sensitive. Defaults to false.
- `captchaAction`: the [route](structure-controllers.md#routes) corresponding to the
  [[yii\captcha\CaptchaAction|CAPTCHA action]] that renders the CAPTCHA image. Defaults to `'site/captcha'`.
- `skipOnEmpty`: whether the validation can be skipped if the input is empty. Defaults to false,
  which means the input is required.
  
```php
[
    ['verificationCode', 'captcha'],
]
```


### [[yii\validators\CompareValidator|compare]] <a name="compare"></a>

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
  
```php
[
    // validates if the value of "password" attribute equals to that of "password_repeat"
    ['password', 'compare'],

    // validates if age is greater than or equal to 30
    ['age', 'compare', 'compareValue' => 30, 'operator' => '>='],
]
```


### [[yii\validators\DateValidator|date]] <a name="date"></a>

Verifies if the attribute represents a date, time, or datetime in a proper format.

- `format`, the date format that the value being validated should follow according to
  [PHP date_create_from_format](http://www.php.net/manual/en/datetime.createfromformat.php). _('Y-m-d')_
- `timestampAttribute`, the name of the attribute that should receive the parsed result.

### [[yii\validators\DefaultValueValidator|default]] <a name="default"></a>

Sets the attribute to be the specified default value.

- `value`, the default value to be assigned.

### [[yii\validators\NumberValidator|double]] <a name="double"></a>

Validates that the attribute value is a number, integer or decimal.

- `max`, the upper limit of the number (inclusive). _(null)_
- `min`, the lower limit of the number (inclusive). _(null)_

### [[yii\validators\EmailValidator|email]] <a name="email"></a>

Validates that the attribute value is a valid email address. By default, this validator checks if the attribute value is a syntactical valid email address, but the validator can be configured to check the address's domain for the address's existence.

- `allowName`, whether to allow the name in the email address (e.g. `John Smith <john.smith@example.com>`). _(false)_.
- `checkMX`, whether to check the MX record for the email address. _(false)_
- `checkPort`, whether to check port 25 for the email address. _(false)_
- `enableIDN`, whether the validation process should take into account IDN (internationalized domain names). _(false)_

### [[yii\validators\ExistValidator|exist]] <a name="exist"></a>

Validates that the attribute value exists in a table.

- `targetClass`, the ActiveRecord class name or alias of the class that should be used to look for the attribute value being
  validated. _(ActiveRecord class of the attribute being validated)_
- `targetAttribute`, the ActiveRecord attribute name that should be used to look for the attribute value being validated.
  _(name of the attribute being validated)_

### [[yii\validators\FileValidator|file]] <a name="file"></a>

Verifies if an attribute is receiving a valid uploaded file.

- `types`, an array of file name extensions that are allowed to be uploaded. _(any)_
- `minSize`, the minimum number of bytes required for the uploaded file.
- `maxSize`, the maximum number of bytes allowed for the uploaded file.
- `maxFiles`, the maximum number of files that the given attribute can hold. _(1)_

### [[yii\validators\FilterValidator|filter]] <a name="filter"></a>

Converts the attribute value by sending it through a filter.

- `filter`, a PHP callback that defines a filter.

Typically a callback is either the name of PHP function:

```php
['password', 'filter', 'filter' => 'trim'],
```

Or an anonymous function:

```php
['text', 'filter', 'filter' => function ($value) {
    // here we are removing all swear words from text
    return $newValue;
}],
```

### [[yii\validators\ImageValidator|image]] <a name="image"></a>

### [[yii\validators\RangeValidator|in]] <a name="in"></a>

Validates that the attribute value is among a list of values.

- `range`, a list of valid values that the attribute value should be among (inclusive).
- `strict`, whether the comparison should be strict (both the type and value must be the same). _(false)_
- `not`, whether to invert the validation logic. _(false)_


### [[yii\validators\NumberValidator|integer]] <a name="integer"></a>

Validates that the attribute value is an integer.

- `max`, the upper limit of the number (inclusive). _(null)_
- `min`, the lower limit of the number (inclusive). _(null)_

### [[yii\validators\RegularExpressionValidator|match]] <a name="match"></a>

Validates that the attribute value matches the specified pattern defined by a regular expression.

- `pattern`, the regular expression to be matched.
- `not`, whether to invert the validation logic. _(false)_

### [[yii\validators\NumberValidator|number]] <a name="number"></a>

Validates that the attribute value is a number.

- `max`, the upper limit of the number (inclusive). _(null)_
- `min`, the lower limit of the number (inclusive). _(null)_

### [[yii\validators\RequiredValidator|required]] <a name="required"></a>

Validates that the specified attribute does not have a null or empty value.

- `requiredValue`, the desired value that the attribute must have. _(any)_
- `strict`, whether the comparison between the attribute value and
  [[yii\validators\RequiredValidator::requiredValue|requiredValue]] must match both value and type. _(false)_

### [[yii\validators\SafeValidator|safe]] <a name="safe"></a>

Serves as a dummy validator whose main purpose is to mark the attributes to be safe for massive assignment.

### [[yii\validators\StringValidator|string]] <a name="string"></a>

Validates that the attribute value is of certain length.

- `length`, specifies the length limit of the value to be validated (inclusive). Can be `exactly X`, `[min X]`, `[min X, max Y]`.
- `max`, the upper length limit (inclusive). If not set, it means no maximum length limit.
- `min`, the lower length limit (inclusive). If not set, it means no minimum length limit.
- `encoding`, the encoding of the string value to be validated. _([[yii\base\Application::charset]])_

### [[yii\validators\FilterValidator|trim]] <a name="trim"></a>

### [[yii\validators\UniqueValidator|unique]] <a name="unique"></a>

Validates that the attribute value is unique in the corresponding database table.

- `targetClass`, the ActiveRecord class name or alias of the class that should be used to look for the attribute value being
  validated. _(ActiveRecord class of the attribute being validated)_
- `targetAttribute`, the ActiveRecord attribute name that should be used to look for the attribute value being validated.
  _(name of the attribute being validated)_

### [[yii\validators\UrlValidator|url]] <a name="url"></a>

Validates that the attribute value is a valid http or https URL.

- `validSchemes`, an array of URI schemes that should be considered valid. _['http', 'https']_
- `defaultScheme`, the default URI scheme. If the input doesn't contain the scheme part, the default scheme will be
  prepended to it. _(null)_
- `enableIDN`, whether the validation process should take into account IDN (internationalized domain names). _(false)_


## Creating Validators

If none of the built in validators fit your needs, you can create your own validator by creating a method in you model class.
This method will be wrapped by an [[yii\validators\InlineValidator|InlineValidator]] an be called upon validation.
You will do the validation of the attribute and [[yii\base\Model::addError()|add errors]] to the model when validation fails.

The method has the following signature `public function myValidator($attribute, $params)` while you are free to choose the name.

Here is an example implementation of a validator validating the age of a user:

```php
public function validateAge($attribute, $params)
{
    $value = $this->$attribute;
    if (strtotime($value) > strtotime('now - ' . $params['min'] . ' years')) {
        $this->addError($attribute, 'You must be at least ' . $params['min'] . ' years old to register for this service.');
    }
}

public function rules()
{
    return [
        // ...
        [['birthdate'], 'validateAge', 'params' => ['min' => '12']],
    ];
}
```

You may also set other properties of the [[yii\validators\InlineValidator|InlineValidator]] in the rules definition,
for example the [[yii\validators\InlineValidator::$skipOnEmpty|skipOnEmpty]] property:

```php
[['birthdate'], 'validateAge', 'params' => ['min' => '12'], 'skipOnEmpty' => false],
```


### Inline Validators

### Standalone Validators

## Client-Side Validation

## Conditional Validation


To validate attributes only when certain conditions apply, e.g. the validation of
one field depends on the value of another field you can use [[yii\validators\Validator::when|the `when` property]]
to define such conditions:

```php
['state', 'required', 'when' => function($model) { return $model->country == Country::USA; }],
['stateOthers', 'required', 'when' => function($model) { return $model->country != Country::USA; }],
['mother', 'required', 'when' => function($model) { return $model->age < 18 && $model->married != true; }],
```

For better readability the conditions can also be written like this:

```php
public function rules()
{
    $usa = function($model) { return $model->country == Country::USA; };
    $notUsa = function($model) { return $model->country != Country::USA; };
    $child = function($model) { return $model->age < 18 && $model->married != true; };
    return [
        ['state', 'required', 'when' => $usa],
        ['stateOthers', 'required', 'when' => $notUsa], // note that it is not possible to write !$usa
        ['mother', 'required', 'when' => $child],
    ];
}
```

When you need conditional validation logic on client-side (`enableClientValidation` is true), don't forget
to add `whenClient`:

```php
public function rules()
{
    $usa = [
        'server-side' => function($model) { return $model->country == Country::USA; },
        'client-side' => "function (attribute, value) {return $('#country').value == 'USA';}"
    ];

    return [
        ['state', 'required', 'when' => $usa['server-side'], 'whenClient' => $usa['client-side']],
    ];
}
```

This guide describes all of Yii's validators and their parameters.


## Data Filtering

## Ad Hoc Validation


Sometimes you need to validate a value that is not bound to any model, such as a standalone email address. The `Validator` class has a
`validateValue` method that can help you in these scenarios. Not all validator classes have implemented this method, but the ones that have implemented `validateValue` can be used without a model. For example, to validate an email stored in a string, you can do the following:

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();
if ($validator->validate($email, $error)) {
    echo 'Email is valid.';
} else {
    echo $error;
}
```

DynamicModel is a model class primarily used to support ad hoc data validation.

The typical usage of DynamicModel is as follows,

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
        [['name', 'email'], 'string', 'max' => 128]],
        ['email', 'email'],
    ]);
    if ($model->hasErrors()) {
        // validation fails
    } else {
        // validation succeeds
    }
}
```

The above example shows how to validate `$name` and `$email` with the help of DynamicModel.
The [[validateData()]] method creates an instance of DynamicModel, defines the attributes
using the given data (`name` and `email` in this example), and then calls [[Model::validate()]].

You can check the validation result by [[hasErrors()]], like you do with a normal model.
You may also access the dynamic attributes defined through the model instance, e.g.,
`$model->name` and `$model->email`.

Alternatively, you may use the following more "classic" syntax to perform ad-hoc data validation:

```php
$model = new DynamicModel(compact('name', 'email'));
$model->addRule(['name', 'email'], 'string', ['max' => 128])
    ->addRule('email', 'email')
    ->validate();
```

DynamicModel implements the above ad-hoc data validation feature by supporting the so-called
"dynamic attributes". It basically allows an attribute to be defined dynamically through its constructor
or [[defineAttribute()]].
