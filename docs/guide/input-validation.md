Validating Input
================

As a rule of thumb, you should never trust the data received from end users and should always validate it
before putting it to good use.

Given a [model](structure-models.md) populated with user inputs, you can validate the inputs by calling the
[[yii\base\Model::validate()]] method. The method will return a boolean value indicating whether the validation
succeeded or not. If not, you may get the error messages from the [[yii\base\Model::errors]] property. For example,

```php
$model = new \app\models\ContactForm();

// populate model attributes with user inputs
$model->load(\Yii::$app->request->post());
// which is equivalent to the following:
// $model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // all inputs are valid
} else {
    // validation failed: $errors is an array containing error messages
    $errors = $model->errors;
}
```


## Declaring Rules <span id="declaring-rules"></span>

To make `validate()` really work, you should declare validation rules for the attributes you plan to validate.
This should be done by overriding the [[yii\base\Model::rules()]] method. The following example shows how
the validation rules for the `ContactForm` model are declared:

```php
public function rules()
{
    return [
        // the name, email, subject and body attributes are required
        [['name', 'email', 'subject', 'body'], 'required'],

        // the email attribute should be a valid email address
        ['email', 'email'],
    ];
}
```

The [[yii\base\Model::rules()|rules()]] method should return an array of rules, each of which is an array
of the following format:

```php
[
    // required, specifies which attributes should be validated by this rule.
    // For a single attribute, you can use the attribute name directly
    // without having it in an array
    ['attribute1', 'attribute2', ...],

    // required, specifies the type of this rule.
    // It can be a class name, validator alias, or a validation method name
    'validator',

    // optional, specifies in which scenario(s) this rule should be applied
    // if not given, it means the rule applies to all scenarios
    // You may also configure the "except" option if you want to apply the rule
    // to all scenarios except the listed ones
    'on' => ['scenario1', 'scenario2', ...],

    // optional, specifies additional configurations for the validator object
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

For each rule you must specify at least which attributes the rule applies to and what is the type of the rule.
You can specify the rule type in one of the following forms:

* the alias of a core validator, such as `required`, `in`, `date`, etc. Please refer to
  the [Core Validators](tutorial-core-validators.md) for the complete list of core validators.
* the name of a validation method in the model class, or an anonymous function. Please refer to the
  [Inline Validators](#inline-validators) subsection for more details.
* a fully qualified validator class name. Please refer to the [Standalone Validators](#standalone-validators)
  subsection for more details.

A rule can be used to validate one or multiple attributes, and an attribute may be validated by one or multiple rules.
A rule may be applied in certain [scenarios](structure-models.md#scenarios) only by specifying the `on` option.
If you do not specify an `on` option, it means the rule will be applied to all scenarios.

When the `validate()` method is called, it does the following steps to perform validation:

1. Determine which attributes should be validated by getting the attribute list from [[yii\base\Model::scenarios()]]
   using the current [[yii\base\Model::scenario|scenario]]. These attributes are called *active attributes*.
2. Determine which validation rules should be used by getting the rule list from [[yii\base\Model::rules()]]
   using the current [[yii\base\Model::scenario|scenario]]. These rules are called *active rules*.
3. Use each active rule to validate each active attribute which is associated with the rule.
   The validation rules are evaluated in the order they are listed.

According to the above validation steps, an attribute will be validated if and only if it is
an active attribute declared in `scenarios()` and is associated with one or multiple active rules
declared in `rules()`.

> Note: It is handy to give names to rules i.e.
>
> ```php
> public function rules()
> {
>     return [
>         // ...
>         'password' => [['password'], 'string', 'max' => 60],
>     ];
> }
> ```
>
> You can use it in a child model:
>
> ```php
> public function rules()
> {
>     $rules = parent::rules();
>     unset($rules['password']);
>     return $rules;
> }


### Customizing Error Messages <span id="customizing-error-messages"></span>

Most validators have default error messages that will be added to the model being validated when its attributes
fail the validation. For example, the [[yii\validators\RequiredValidator|required]] validator will add
a message "Username cannot be blank." to a model when the `username` attribute fails the rule using this validator.

You can customize the error message of a rule by specifying the `message` property when declaring the rule,
like the following,

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Please choose a username.'],
    ];
}
```

Some validators may support additional error messages to more precisely describe different causes of
validation failures. For example, the [[yii\validators\NumberValidator|number]] validator supports
[[yii\validators\NumberValidator::tooBig|tooBig]] and [[yii\validators\NumberValidator::tooSmall|tooSmall]]
to describe the validation failure when the value being validated is too big and too small, respectively.
You may configure these error messages like configuring other properties of validators in a validation rule.


### Validation Events <span id="validation-events"></span>

When [[yii\base\Model::validate()]] is called, it will call two methods that you may override to customize
the validation process:

* [[yii\base\Model::beforeValidate()]]: the default implementation will trigger a [[yii\base\Model::EVENT_BEFORE_VALIDATE]]
  event. You may either override this method or respond to this event to do some preprocessing work
  (e.g. normalizing data inputs) before the validation occurs. The method should return a boolean value indicating
  whether the validation should proceed or not.
* [[yii\base\Model::afterValidate()]]: the default implementation will trigger a [[yii\base\Model::EVENT_AFTER_VALIDATE]]
  event. You may either override this method or respond to this event to do some postprocessing work after
  the validation is completed.


### Conditional Validation <span id="conditional-validation"></span>

To validate attributes only when certain conditions apply, e.g. the validation of one attribute depends
on the value of another attribute you can use the [[yii\validators\Validator::when|when]] property
to define such conditions. For example,

```php
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }]
```

The [[yii\validators\Validator::when|when]] property takes a PHP callable with the following signature:

```php
/**
 * @param Model $model the model being validated
 * @param string $attribute the attribute being validated
 * @return bool whether the rule should be applied
 */
function ($model, $attribute)
```

If you also need to support client-side conditional validation, you should configure
the [[yii\validators\Validator::whenClient|whenClient]] property which takes a string representing a JavaScript
function whose return value determines whether to apply the rule or not. For example,

```php
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').val() == 'USA';
    }"]
```


### Data Filtering <span id="data-filtering"></span>

User inputs often need to be filtered or preprocessed. For example, you may want to trim the spaces around the
`username` input. You may use validation rules to achieve this goal.

The following examples shows how to trim the spaces in the inputs and turn empty inputs into nulls by using
the [trim](tutorial-core-validators.md#trim) and [default](tutorial-core-validators.md#default) core validators:

```php
return [
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
];
```

You may also use the more general [filter](tutorial-core-validators.md#filter) validator to perform more complex
data filtering.

As you can see, these validation rules do not really validate the inputs. Instead, they will process the values
and save them back to the attributes being validated.

A complete processing of user input is shown in the following example code, which will ensure only integer
values are stored in an attribute:

```php
['age', 'trim'],
['age', 'default', 'value' => null],
['age', 'integer', 'min' => 0],
['age', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],
```

The above code will perform the following operations on the input:

1. Trim whitespace from the input value.
2. Make sure empty input is stored as `null` in the database; we differentiate between a value being "not set"
   and the actual value `0`. If `null` is not allowed you can set another default value here.
3. Validate that the value is an integer greater than 0 if it is not empty. Normal validators have
   [[yii\validators\Validator::$skipOnEmpty|$skipOnEmpty]] set to `true`.
4. Make sure the value is of type integer, e.g. casting a string `'42'` to integer `42`.
   Here we set [[yii\validators\FilterValidator::$skipOnEmpty|$skipOnEmpty]] to `true`, which is `false` by default
   on the [[yii\validators\FilterValidator|filter]] validator.

### Handling Empty Inputs <span id="handling-empty-inputs"></span>

When input data are submitted from HTML forms, you often need to assign some default values to the inputs
if they are empty. You can do so by using the [default](tutorial-core-validators.md#default) validator. For example,

```php
return [
    // set "username" and "email" as null if they are empty
    [['username', 'email'], 'default'],

    // set "level" to be 1 if it is empty
    ['level', 'default', 'value' => 1],
];
```

By default, an input is considered empty if its value is an empty string, an empty array or a `null`.
You may customize the default empty detection logic by configuring the [[yii\validators\Validator::isEmpty]] property
with a PHP callable. For example,

```php
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }]
```

> Note: Most validators do not handle empty inputs if their [[yii\validators\Validator::skipOnEmpty]] property takes
  the default value `true`. They will simply be skipped during validation if their associated attributes receive empty
  inputs. Among the [core validators](tutorial-core-validators.md), only the `captcha`, `default`, `filter`,
  `required`, and `trim` validators will handle empty inputs.


## Ad Hoc Validation <span id="ad-hoc-validation"></span>

Sometimes you need to do *ad hoc validation* for values that are not bound to any model.

If you only need to perform one type of validation (e.g. validating email addresses), you may call
the [[yii\validators\Validator::validate()|validate()]] method of the desired validator, like the following:

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo 'Email is valid.';
} else {
    echo $error;
}
```

> Note: Not all validators support this type of validation. An example is the [unique](tutorial-core-validators.md#unique)
  core validator which is designed to work with a model only.

> Note: The [[yii\base\Validator::skipOnEmpty]] property is used for [[yii\base\Model]] validation only. Using it without a model has no effect.

If you need to perform multiple validations against several values, you can use [[yii\base\DynamicModel]]
which supports declaring both attributes and rules on the fly. Its usage is like the following:

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(['name' => $name, 'email' => $email], [
        [['name', 'email'], 'string', 'max' => 128],
        ['email', 'email'],
    ]);

    if ($model->hasErrors()) {
        // validation fails
    } else {
        // validation succeeds
    }
}
```

The [[yii\base\DynamicModel::validateData()]] method creates an instance of `DynamicModel`, defines the attributes
using the given data (`name` and `email` in this example), and then calls [[yii\base\Model::validate()]]
with the given rules.

Alternatively, you may use the following more "classic" syntax to perform ad hoc data validation:

```php
public function actionSearch($name, $email)
{
    $model = new DynamicModel(['name' => $name, 'email' => $email]);
    $model->addRule(['name', 'email'], 'string', ['max' => 128])
        ->addRule('email', 'email')
        ->validate();

    if ($model->hasErrors()) {
        // validation fails
    } else {
        // validation succeeds
    }
}
```

After validation, you can check if the validation succeeded or not by calling the
[[yii\base\DynamicModel::hasErrors()|hasErrors()]] method, and then get the validation errors from the
[[yii\base\DynamicModel::errors|errors]] property, like you do with a normal model.
You may also access the dynamic attributes defined through the model instance, e.g.,
`$model->name` and `$model->email`.


## Creating Validators <span id="creating-validators"></span>

Besides using the [core validators](tutorial-core-validators.md) included in the Yii releases, you may also
create your own validators. You may create inline validators or standalone validators.


### Inline Validators <span id="inline-validators"></span>

An inline validator is one defined in terms of a model method or an anonymous function. The signature of
the method/function is:

```php
/**
 * @param string $attribute the attribute currently being validated
 * @param mixed $params the value of the "params" given in the rule
 * @param \yii\validators\InlineValidator $validator related InlineValidator instance.
 * This parameter is available since version 2.0.11.
 * @param mixed $current the currently validated value of attribute.
 * This parameter is available since version 2.0.36.
 */
function ($attribute, $params, $validator, $current)
```

If an attribute fails the validation, the method/function should call [[yii\base\Model::addError()]] to save
the error message in the model so that it can be retrieved back later to present to end users.

Below are some examples:

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // an inline validator defined as the model method validateCountry()
            ['country', 'validateCountry'],

            // an inline validator defined as an anonymous function
            ['token', function ($attribute, $params, $validator) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'The token must contain letters or digits.');
                }
            }],
        ];
    }

    public function validateCountry($attribute, $params, $validator)
    {
        if (!in_array($this->$attribute, ['USA', 'Indonesia'])) {
            $this->addError($attribute, 'The country must be either "USA" or "Indonesia".');
        }
    }
}
```

> Note: Since version 2.0.11 you can use [[yii\validators\InlineValidator::addError()]] for adding errors instead. That way the error
> message can be formatted using [[yii\i18n\I18N::format()]] right away. Use `{attribute}` and `{value}` in the error
> message to refer to an attribute label (no need to get it manually) and attribute value accordingly:
>
> ```php
> $validator->addError($this, $attribute, 'The value "{value}" is not acceptable for {attribute}.');
> ```

> Note: By default, inline validators will not be applied if their associated attributes receive empty inputs
  or if they have already failed some validation rules. If you want to make sure a rule is always applied,
  you may configure the [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] and/or [[yii\validators\Validator::skipOnError|skipOnError]]
  properties to be `false` in the rule declarations. For example:
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### Standalone Validators <span id="standalone-validators"></span>

A standalone validator is a class extending [[yii\validators\Validator]] or its child class. You may implement
its validation logic by overriding the [[yii\validators\Validator::validateAttribute()]] method. If an attribute
fails the validation, call [[yii\base\Model::addError()]] to save the error message in the model, like you do
with [inline validators](#inline-validators).


For example, the inline validator above could be moved into new [[components/validators/CountryValidator]] class.
In this case we can use [[yii\validators\Validator::addError()]] to set customized message for the model.

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Indonesia'])) {
            $this->addError($model, $attribute, 'The country must be either "{country1}" or "{country2}".', ['country1' => 'USA', 'country2' => 'Indonesia']);
        }
    }
}
```

If you want your validator to support validating a value without a model, you should also override
[[yii\validators\Validator::validate()]]. You may also override [[yii\validators\Validator::validateValue()]]
instead of `validateAttribute()` and `validate()` because by default the latter two methods are implemented
by calling `validateValue()`.

Below is an example of how you could use the above validator class within your model.

```php
namespace app\models;

use Yii;
use yii\base\Model;
use app\components\validators\CountryValidator;

class EntryForm extends Model
{
    public $name;
    public $email;
    public $country;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['country', CountryValidator::class],
            ['email', 'email'],
        ];
    }
}
```


## Multiple Attributes Validation <span id="multiple-attributes-validation"></span>

Sometimes validators involve multiple attributes. Consider the following form:

```php
class MigrationForm extends \yii\base\Model
{
    /**
     * Minimal funds amount for one adult person
     */
    const MIN_ADULT_FUNDS = 3000;
    /**
     * Minimal funds amount for one child
     */
    const MIN_CHILD_FUNDS = 1500;

    public $personalSalary;
    public $spouseSalary;
    public $childrenCount;
    public $description;

    public function rules()
    {
        return [
            [['personalSalary', 'description'], 'required'],
            [['personalSalary', 'spouseSalary'], 'integer', 'min' => self::MIN_ADULT_FUNDS],
            ['childrenCount', 'integer', 'min' => 0, 'max' => 5],
            [['spouseSalary', 'childrenCount'], 'default', 'value' => 0],
            ['description', 'string'],
        ];
    }
}
```

### Creating validator <span id="multiple-attributes-validator"></span>

Let's say we need to check if the family income is enough for children. We can create inline validator
`validateChildrenFunds` for that which will run only when `childrenCount` is more than 0.

Note that we can't use all validated attributes (`['personalSalary', 'spouseSalary', 'childrenCount']`) when attaching
validator. This is because the same validator will run for each attribute (3 times in total) and we only need to run it
once for the whole attribute set.

You can use any of these attributes instead (or use what you think is the most relevant):

```php
['childrenCount', 'validateChildrenFunds', 'when' => function ($model) {
    return $model->childrenCount > 0;
}],
```

Implementation of `validateChildrenFunds` can be like this:

```php
public function validateChildrenFunds($attribute, $params)
{
    $totalSalary = $this->personalSalary + $this->spouseSalary;
    // Double the minimal adult funds if spouse salary is specified
    $minAdultFunds = $this->spouseSalary ? self::MIN_ADULT_FUNDS * 2 : self::MIN_ADULT_FUNDS;
    $childFunds = $totalSalary - $minAdultFunds;
    if ($childFunds / $this->childrenCount < self::MIN_CHILD_FUNDS) {
        $this->addError('childrenCount', 'Your salary is not enough for children.');
    }
}
```

You can ignore `$attribute` parameter because validation is not related to just one attribute.


### Adding errors <span id="multiple-attributes-errors"></span>

Adding error in case of multiple attributes can vary depending on desired form design:

- Select the most relevant field in your opinion and add error to it's attribute:

```php
$this->addError('childrenCount', 'Your salary is not enough for children.');
```

- Select multiple important relevant attributes or all attributes and add the same error message to them. We can store
message in separate variable before passing it to `addError` to keep code DRY.

```php
$message = 'Your salary is not enough for children.';
$this->addError('personalSalary', $message);
$this->addError('wifeSalary', $message);
$this->addError('childrenCount', $message);
```

Or use a loop:

```php
$attributes = ['personalSalary', 'wifeSalary', 'childrenCount'];
foreach ($attributes as $attribute) {
    $this->addError($attribute, 'Your salary is not enough for children.');
}
```

- Add a common error (not related to particular attribute). We can use the not existing attribute name for adding
error, for example `*`, because attribute existence is not checked at that point.

```php
$this->addError('*', 'Your salary is not enough for children.');
```

As a result, we will not see error message near form fields. To display it, we can include the error summary in view:

```php
<?= $form->errorSummary($model) ?>
```

> Note: Creating validator which validates multiple attributes at once is well described in the [community cookbook](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-validator-multiple-attributes.md).

## Client-Side Validation <span id="client-side-validation"></span>

Client-side validation based on JavaScript is desirable when end users provide inputs via HTML forms, because
it allows users to find out input errors faster and thus provides a better user experience. You may use or implement
a validator that supports client-side validation *in addition to* server-side validation.

> Info: While client-side validation is desirable, it is not a must. Its main purpose is to provide users with a better
  experience. Similar to input data coming from end users, you should never trust client-side validation. For this reason,
  you should always perform server-side validation by calling [[yii\base\Model::validate()]], as
  described in the previous subsections.


### Using Client-Side Validation <span id="using-client-side-validation"></span>

Many [core validators](tutorial-core-validators.md) support client-side validation out-of-the-box. All you need to do
is just use [[yii\widgets\ActiveForm]] to build your HTML forms. For example, `LoginForm` below declares two
rules: one uses the [required](tutorial-core-validators.md#required) core validator which is supported on both
client and server-sides; the other uses the `validatePassword` inline validator which is only supported on the server
side.

```php
namespace app\models;

use yii\base\Model;
use app\models\User;

class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],

            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword()
    {
        $user = User::findByUsername($this->username);

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }
}
```

The HTML form built by the following code contains two input fields `username` and `password`.
If you submit the form without entering anything, you will find the error messages requiring you
to enter something appear right away without any communication with the server.

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

Behind the scene, [[yii\widgets\ActiveForm]] will read the validation rules declared in the model
and generate appropriate JavaScript code for validators that support client-side validation. When a user
changes the value of an input field or submit the form, the client-side validation JavaScript will be triggered.

If you want to turn off client-side validation completely, you may configure the
[[yii\widgets\ActiveForm::enableClientValidation]] property to be `false`. You may also turn off client-side
validation of individual input fields by configuring their [[yii\widgets\ActiveField::enableClientValidation]]
property to be false. When `enableClientValidation` is configured at both the input field level and the form level,
the former will take precedence.

> Info: Since version 2.0.11 all validators extending from [[yii\validators\Validator]] receive client-side options
> from separate method - [[yii\validators\Validator::getClientOptions()]]. You can use it:
>
> - if you want to implement your own custom client-side validation but leave the synchronization with server-side
> validator options;
> - to extend or customize to fit your specific needs:
>
> ```php
> public function getClientOptions($model, $attribute)
> {
>     $options = parent::getClientOptions($model, $attribute);
>     // Modify $options here
>
>     return $options;
> }
> ```

### Implementing Client-Side Validation <span id="implementing-client-side-validation"></span>

To create a validator that supports client-side validation, you should implement the
[[yii\validators\Validator::clientValidateAttribute()]] method which returns a piece of JavaScript code
that performs the validation on the client-side. Within the JavaScript code, you may use the following
predefined variables:

- `attribute`: the name of the attribute being validated.
- `value`: the value being validated.
- `messages`: an array used to hold the validation error messages for the attribute.
- `deferred`: an array which deferred objects can be pushed into (explained in the next subsection).

In the following example, we create a `StatusValidator` which validates if an input is a valid status input
against the existing status data. The validator supports both server-side and client-side validation.

```php
namespace app\components;

use yii\validators\Validator;
use app\models\Status;

class StatusValidator extends Validator
{
    public function init()
    {
        parent::init();
        $this->message = 'Invalid status input.';
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!Status::find()->where(['id' => $value])->exists()) {
            $model->addError($attribute, $this->message);
        }
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $statuses = json_encode(Status::find()->select('id')->asArray()->column());
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
if ($.inArray(value, $statuses) === -1) {
    messages.push($message);
}
JS;
    }
}
```

> Tip: The above code is given mainly to demonstrate how to support client-side validation. In practice,
> you may use the [in](tutorial-core-validators.md#in) core validator to achieve the same goal. You may
> write the validation rule like the following:
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

> Tip: If you need to work with client validation manually i.e. dynamically add fields or do some custom UI logic, refer
> to [Working with ActiveForm via JavaScript](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-activeform-js.md)
> in Yii 2.0 Cookbook.

### Deferred Validation <span id="deferred-validation"></span>

If you need to perform asynchronous client-side validation, you can create [Deferred objects](https://api.jquery.com/category/deferred-object/).
For example, to perform a custom AJAX validation, you can use the following code:

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.push($.get("/check", {value: value}).done(function(data) {
            if ('' !== data) {
                messages.push(data);
            }
        }));
JS;
}
```

In the above, the `deferred` variable is provided by Yii, which is an array of Deferred objects. The `$.get()`
jQuery method creates a Deferred object which is pushed to the `deferred` array.

You can also explicitly create a Deferred object and call its `resolve()` method when the asynchronous callback
is hit. The following example shows how to validate the dimensions of an uploaded image file on the client-side.

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        var def = $.Deferred();
        var img = new Image();
        img.onload = function() {
            if (this.width > 150) {
                messages.push('Image too wide!!');
            }
            def.resolve();
        }
        var reader = new FileReader();
        reader.onloadend = function() {
            img.src = reader.result;
        }
        reader.readAsDataURL(file);

        deferred.push(def);
JS;
}
```

> Note: The `resolve()` method must be called after the attribute has been validated. Otherwise the main form
  validation will not complete.

For simplicity, the `deferred` array is equipped with a shortcut method `add()` which automatically creates a Deferred
object and adds it to the `deferred` array. Using this method, you can simplify the above example as follows,

```php
public function clientValidateAttribute($model, $attribute, $view)
{
    return <<<JS
        deferred.add(function(def) {
            var img = new Image();
            img.onload = function() {
                if (this.width > 150) {
                    messages.push('Image too wide!!');
                }
                def.resolve();
            }
            var reader = new FileReader();
            reader.onloadend = function() {
                img.src = reader.result;
            }
            reader.readAsDataURL(file);
        });
JS;
}
```


## AJAX Validation <span id="ajax-validation"></span>

Some validations can only be done on the server-side, because only the server has the necessary information.
For example, to validate if a username is unique or not, it is necessary to check the user table on the server-side.
You can use AJAX-based validation in this case. It will trigger an AJAX request in the background to validate the
input while keeping the same user experience as the regular client-side validation.

To enable AJAX validation for a single input field, configure the [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]]
property of that field to be `true` and specify a unique form `id`:

```php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'registration-form',
]);

echo $form->field($model, 'username', ['enableAjaxValidation' => true]);

// ...

ActiveForm::end();
```

To enable AJAX validation for all inputs of the form, configure [[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]]
to be `true` at the form level:

```php
$form = ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]);
```

> Note: When the `enableAjaxValidation` property is configured at both the input field level and the form level,
  the former will take precedence.

You also need to prepare the server so that it can handle the AJAX validation requests.
This can be achieved by a code snippet like the following in the controller actions:

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

The above code will check whether the current request is an AJAX. If yes, it will respond to
this request by running the validation and returning the errors in JSON format.

> Info: You can also use [Deferred Validation](#deferred-validation) to perform AJAX validation.
  However, the AJAX validation feature described here is more systematic and requires less coding effort.

When both `enableClientValidation` and `enableAjaxValidation` are set to `true`, AJAX validation request will be triggered
only after the successful client validation. Note that in case of validating a single field that happens if either
`validateOnChange`, `validateOnBlur` or `validateOnType` is set to `true`, AJAX request will be sent when the field in
question alone successfully passes client validation. 
