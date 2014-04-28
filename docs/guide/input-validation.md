Model validation reference
==========================

As a model both represents data and defines the business rules to which that data must adhere, comprehending data validation is key to using Yii. In order to learn model validation basics, please refer to [Model, Validation subsection](model.md#Validation).

This guide describes all of Yii's validators and their parameters.


Standard Yii validators
-----------------------

The standard Yii validators are defined in many Yii classes, found primarily within the `yii\validators` namespace. But you do not need to specify the full namespace for the standard Yii validators as Yii can recognize them from defined aliases. 

Here's the list of all validators bundled with the Yii framework, including  their most useful properties. The default value for each property is indicated in parentheses. Note that this does not present an exhaustive list of each validator's properties.  

### `boolean`: [[yii\validators\BooleanValidator|BooleanValidator]]

Checks if the attribute value is a boolean value.

- `trueValue`, the value representing true status. _(1)_
- `falseValue`, the value representing false status. _(0)_
- `strict`, whether to also compare the type of the value and `trueValue`/`falseValue`. _(false)_

### `captcha`: [[yii\captcha\CaptchaValidator|CaptchaValidator]]

Validates that the attribute value is the same as the verification code displayed in the CAPTCHA. Should be used together
with [[yii\captcha\CaptchaAction]].

- `caseSensitive`, whether the comparison is case sensitive. _(false)_
- `captchaAction`, the route of the controller action that renders the CAPTCHA image. _('site/captcha')_

### `compare`: [[yii\validators\CompareValidator|CompareValidator]]

Compares the specified attribute value with another value and validates if they are equal.

- `compareAttribute`, the name of the attribute to be compared with. _(currentAttributeName&#95;repeat)_
- `compareValue`, a constant value to be compared with.
- `operator`, the operator for the comparison. _('==')_

### `date`: [[yii\validators\DateValidator|DateValidator]]

Verifies if the attribute represents a date, time, or datetime in a proper format.

- `format`, the date format that the value being validated should follow according to
  [PHP date_create_from_format](http://www.php.net/manual/en/datetime.createfromformat.php). _('Y-m-d')_
- `timestampAttribute`, the name of the attribute that should receive the parsed result.

### `default`: [[yii\validators\DefaultValueValidator|DefaultValueValidator]]

Sets the attribute to be the specified default value.

- `value`, the default value to be assigned.

### `double`: [[yii\validators\NumberValidator|NumberValidator]]

Validates that the attribute value is a number, integer or decimal.

- `max`, the upper limit of the number (inclusive). _(null)_
- `min`, the lower limit of the number (inclusive). _(null)_

### `email`: [[yii\validators\EmailValidator|EmailValidator]]

Validates that the attribute value is a valid email address. By default, this validator checks if the attribute value is a syntactical valid email address, but the validator can be configured to check the address's domain for the address's existence.

- `allowName`, whether to allow the name in the email address (e.g. `John Smith <john.smith@example.com>`). _(false)_.
- `checkMX`, whether to check the MX record for the email address. _(false)_
- `checkPort`, whether to check port 25 for the email address. _(false)_
- `enableIDN`, whether the validation process should take into account IDN (internationalized domain names). _(false)_

### `exist`: [[yii\validators\ExistValidator|ExistValidator]]

Validates that the attribute value exists in a table.

- `targetClass`, the ActiveRecord class name or alias of the class that should be used to look for the attribute value being
  validated. _(ActiveRecord class of the attribute being validated)_
- `targetAttribute`, the ActiveRecord attribute name that should be used to look for the attribute value being validated.
  _(name of the attribute being validated)_

### `file`: [[yii\validators\FileValidator|FileValidator]]

Verifies if an attribute is receiving a valid uploaded file.

- `types`, an array of file name extensions that are allowed to be uploaded. _(any)_
- `minSize`, the minimum number of bytes required for the uploaded file.
- `maxSize`, the maximum number of bytes allowed for the uploaded file.
- `maxFiles`, the maximum number of files that the given attribute can hold. _(1)_

### `filter`: [[yii\validators\FilterValidator|FilterValidator]]

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

### `in`: [[yii\validators\RangeValidator|RangeValidator]]

Validates that the attribute value is among a list of values.

- `range`, a list of valid values that the attribute value should be among (inclusive).
- `strict`, whether the comparison should be strict (both the type and value must be the same). _(false)_
- `not`, whether to invert the validation logic. _(false)_

### `inline`: [[yii\validators\InlineValidator|InlineValidator]]

Uses a custom function to validate the attribute. You need to define a public method in your
model class that will evaluate the validity of the attribute. For example, if an attribute
needs to be divisible by 10, in the rules you would define: `['attributeName', 'isDivisibleByTen']`.

Then, your own method could look like this:

```php
public function isDivisibleByTen($attribute) {
    if (($this->$attribute % 10) != 0) {
         $this->addError($attribute, 'cannot divide value by 10');
    }
}
```

### `integer`: [[yii\validators\NumberValidator|NumberValidator]]

Validates that the attribute value is an integer.

- `max`, the upper limit of the number (inclusive). _(null)_
- `min`, the lower limit of the number (inclusive). _(null)_

### `match`: [[yii\validators\RegularExpressionValidator|RegularExpressionValidator]]

Validates that the attribute value matches the specified pattern defined by a regular expression.

- `pattern`, the regular expression to be matched.
- `not`, whether to invert the validation logic. _(false)_

### `number`: [[yii\validators\NumberValidator|NumberValidator]]

Validates that the attribute value is a number.

- `max`, the upper limit of the number (inclusive). _(null)_
- `min`, the lower limit of the number (inclusive). _(null)_

### `required`: [[yii\validators\RequiredValidator|RequiredValidator]]

Validates that the specified attribute does not have a null or empty value.

- `requiredValue`, the desired value that the attribute must have. _(any)_
- `strict`, whether the comparison between the attribute value and
  [[yii\validators\RequiredValidator::requiredValue|requiredValue]] must match both value and type. _(false)_

### `safe`: [[yii\validators\SafeValidator|SafeValidator]]

Serves as a dummy validator whose main purpose is to mark the attributes to be safe for massive assignment.

### `string`: [[yii\validators\StringValidator|StringValidator]]

Validates that the attribute value is of certain length.

- `length`, specifies the length limit of the value to be validated (inclusive). Can be `exactly X`, `[min X]`, `[min X, max Y]`.
- `max`, the upper length limit (inclusive). If not set, it means no maximum length limit.
- `min`, the lower length limit (inclusive). If not set, it means no minimum length limit.
- `encoding`, the encoding of the string value to be validated. _([[yii\base\Application::charset]])_

### `unique`: [[yii\validators\UniqueValidator|UniqueValidator]]

Validates that the attribute value is unique in the corresponding database table.

- `targetClass`, the ActiveRecord class name or alias of the class that should be used to look for the attribute value being
  validated. _(ActiveRecord class of the attribute being validated)_
- `targetAttribute`, the ActiveRecord attribute name that should be used to look for the attribute value being validated.
  _(name of the attribute being validated)_

### `url`: [[yii\validators\UrlValidator|UrlValidator]]

Validates that the attribute value is a valid http or https URL.

- `validSchemes`, an array of URI schemes that should be considered valid. _['http', 'https']_
- `defaultScheme`, the default URI scheme. If the input doesn't contain the scheme part, the default scheme will be
  prepended to it. _(null)_
- `enableIDN`, whether the validation process should take into account IDN (internationalized domain names). _(false)_

Validating values out of model context
--------------------------------------

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

TBD: refer to http://www.yiiframework.com/wiki/56/ for the format
