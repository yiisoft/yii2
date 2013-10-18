Model validation reference
==========================

This guide section doesn't describe how validation works but instead describes all Yii validators and their parameters.
In order to learn model validation basics please refer to [Model, Validation subsection](model.md#Validation).

Standard Yii validators
-----------------------

Standard Yii validators could be specified using aliases instead of referring to class names. Here's the list of all
validators budled with Yii with their most useful properties:

### `boolean`: [[BooleanValidator]]

Checks if the attribute value is a boolean value.

- `trueValue`, the value representing true status. _(1)_
- `falseValue`, the value representing false status. _(0)_
- `strict`, whether to compare the type of the value and `trueValue`/`falseValue`. _(false)_

### `captcha`: [[CaptchaValidator]]

Validates that the attribute value is the same as the verification code displayed in the CAPTCHA. Should be used together
with [[CaptchaAction]].

- `caseSensitive` whether the comparison is case sensitive. _(false)_
- `captchaAction` the route of the controller action that renders the CAPTCHA image. _('site/captcha')_

### `compare`: [[CompareValidator]]

Compares the specified attribute value with another value and validates if they are equal.

- `compareAttribute` the name of the attribute to be compared with. _(currentAttribute_repeat)_
- `compareValue` the constant value to be compared with.
- `operator` the operator for comparison. _('==')_

### `date`: [[DateValidator]]

Verifies if the attribute represents a date, time or datetime in a proper format.

- `format` the date format that the value being validated should follow according to [[http://www.php.net/manual/en/datetime.createfromformat.php]]. _('Y-m-d')_
- `timestampAttribute` the name of the attribute to receive the parsing result.

### `default`: [[DefaultValueValidator]]

Sets the attribute to be the specified default value.

- `value` the default value to be set to the specified attributes.

### `double`: [[NumberValidator]]

Validates that the attribute value is a number.

- `max` limit of the number. _(null)_
- `min` lower limit of the number. _(null)_

### `email`: [[EmailValidator]]

Validates that the attribute value is a valid email address.

- `allowName` whether to allow name in the email address (e.g. `John Smith <john.smith@example.com>`). _(false)_.
- `checkMX` whether to check the MX record for the email address. _(false)_
- `checkPort` whether to check port 25 for the email address. _(false)_
- `enableIDN` whether validation process should take into account IDN (internationalized domain names). _(false)_

### `exist`: [[ExistValidator]]

Validates that the attribute value exists in a table.

- `className` the ActiveRecord class name or alias of the class that should be used to look for the attribute value being
  validated. _(ActiveRecord class of the attribute being validated)_
- `attributeName` the ActiveRecord attribute name that should be used to look for the attribute value being validated.
  _(name of the attribute being validated)_

### `file`: [[FileValidator]]

Verifies if an attribute is receiving a valid uploaded file.

- `types` a list of file name extensions that are allowed to be uploaded. _(any)_
- `minSize` the minimum number of bytes required for the uploaded file.
- `maxSize` the maximum number of bytes required for the uploaded file.
- `maxFiles` the maximum file count the given attribute can hold. _(1)_

### `filter`: [[FilterValidator]]

Converts the attribute value according to a filter.

- `filter` PHP callback that defines a filter.

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

### `in`: [[RangeValidator]]

Validates that the attribute value is among a list of values.

- `range` list of valid values that the attribute value should be among.
- `strict` whether the comparison is strict (both type and value must be the same). _(false)_
- `not` whether to invert the validation logic. _(false)_

### `integer`: [[NumberValidator]]

Validates that the attribute value is an integer number.

- `max` limit of the number. _(null)_
- `min` lower limit of the number. _(null)_

### `match`: [[RegularExpressionValidator]]

Validates that the attribute value matches the specified pattern defined by regular expression.

- `pattern` the regular expression to be matched with.
- `not` whether to invert the validation logic. _(false)_

### `required`: [[RequiredValidator]]

Validates that the specified attribute does not have null or empty value.

- `requiredValue` the desired value that the attribute must have. _(any)_
- `strict` whether the comparison between the attribute value and [[requiredValue]] is strict. _(false)_

### `safe`: [[SafeValidator]]

Serves as a dummy validator whose main purpose is to mark the attributes to be safe for massive assignment.

### `string`: [[StringValidator]]

Validates that the attribute value is of certain length.

- `length` specifies the length limit of the value to be validated. Can be `exactly X`, `[min X]`, `[min X, max Y]`.
- `max`  maximum length. If not set, it means no maximum length limit.
- `min` minimum length. If not set, it means no minimum length limit.
- `encoding` the encoding of the string value to be validated. _([[\yii\base\Application::charset]])_

### `unique`: [[UniqueValidator]]

Validates that the attribute value is unique in the corresponding database table.

- `className` the ActiveRecord class name or alias of the class that should be used to look for the attribute value being
  validated. _(ActiveRecord class of the attribute being validated)_
- `attributeName` the ActiveRecord attribute name that should be used to look for the attribute value being validated.
  _(name of the attribute being validated)_

### `url`: [[UrlValidator]]

Validates that the attribute value is a valid http or https URL.

- `validSchemes` list of URI schemes which should be considered valid. _['http', 'https']_
- `defaultScheme` the default URI scheme. If the input doesn't contain the scheme part, the default scheme will be
  prepended to it. _(null)_
- `enableIDN` whether validation process should take into account IDN (internationalized domain names). _(false)_

Validating values out of model context
--------------------------------------

Sometimes you need to validate a value that is not bound to any model such as email. In Yii `Validator` class has
`validateValue` method that can help you with it. Not all validator classes have it implemented but the ones that can
operate without model do. In our case to validate an email we can do the following:

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();
if ($validator->validateValue($email)) {
	echo 'Email is valid.';
} else {
	echo 'Email is not valid.'
}
```

TBD: refer to http://www.yiiframework.com/wiki/56/ for the format
