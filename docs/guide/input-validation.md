Validating Input
================

As a rule of thumb, you should never trust the data received from end users and should always validate them
before putting them to good use.

Given a [model](structure-models.md) populated with user inputs, you can validate the inputs by calling the
[[yii\base\Model::validate()]] method. The method will return a boolean value indicating whether the validation
succeeds or not. If not, you may get the error messages from the [[yii\base\Model::errors]] property. For example,

```php
$model = new \app\models\ContactForm;

// populate model attributes with user inputs
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // all inputs are valid
} else {
    // validation failed: $errors is an array containing error messages
    $errors = $model->errors;
}
```

Behind the scene, the `validate()` method does the following steps to perform validation:

1. Determine which attributes should be validated by getting the attribute list from [[yii\base\Model::scenarios()]]
   using the current [[yii\base\Model::scenario|scenario]]. These attributes are called *active attributes*.
2. Determine which validation rules should be used by getting the rule list from [[yii\base\Model::rules()]]
   using the current [[yii\base\Model::scenario|scenario]]. These rules are called *active rules*.
3. Use each active rule to validate each active attribute associated with that rule. If the rule fails,
   keep an error message for the attribute in the model.


## Declaring Rules <a name="declaring-rules"></a>

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
    // without having it in an array instead of an array
    ['attribute1', 'attribute2', ...],

    // required, specifies the type of this rule.
    // It can be a class name, validator alias, or a validation method name
    'validator',

    // optional, specifies in which scenario(s) this rule should be applied
    // if not given, it means the rule applies to all scenarios
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
* the name of a validator class. Please refer to the [Standalone Validators](#standalone-validators)
  subsection for more details.

A rule can be used to validate one or multiple attributes, and an attribute may be validated by one or multiple rules.
A rule may be applied in certain [scenarios](structure-models.md#scenarios) only by specifying the `on` option.
If you do not specify an `on` option, it means the rule will be applied to all scenarios.

When the `validate()` method is called, it does the following steps to perform validation:

1. Determine which attributes should be validated by checking the current [[yii\base\Model::scenario|scenario]]
   against the scenarios declared in [[yii\base\Model::scenarios()]]. These attributes are the active attributes.
2. Determine which rules should be applied by checking the current [[yii\base\Model::scenario|scenario]]
   against the rules declared in [[yii\base\Model::rules()]]. These rules are the active rules.
3. Use each active rule to validate each active attribute which is associated with the rule.

According to the above validation steps, an attribute will be validated if and only if it is
an active attribute declared in `scenarios()` and is associated with one or multiple active rules
declared in `rules()`.


### Customizing Error Messages <a name="customizing-error-messages"></a>

Most validators have default error messages that will be added to the model being validated when its attributes
fail the validation. For example, the [[yii\validators\RequiredValidator|required]] validator will add
a message "Username cannot be blank." to a model when its `username` attribute fails the rule using this validator.

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


### Conditional Validation <a name="conditional-validation"></a>

To validate attributes only when certain conditions apply, e.g. the validation of one attribute depends
on the value of another attribute you can use the [[yii\validators\Validator::when|when]] property
to define such conditions. For example,

```php
[
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }],
]
```

The [[yii\validators\Validator::when|when]] property takes a PHP callable with the following signature:

```php
/**
 * @param Model $model the model being validated
 * @param string $attribute the attribute being validated
 * @return boolean whether the rule should be applied
 */
function ($model, $attribute)
```

If you also need to support client-side conditional validation, you should configure
the [[yii\validators\Validator::whenClient|whenClient]] property which takes a string representing a JavaScript
function whose return value determines whether to apply the rule or not. For example,

```php
[
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').value == 'USA';
    }"],
]
```


### Data Filtering <a name="data-filtering"></a>

User inputs often need to be filtered or preprocessed. For example, you may want to trim the spaces around the
`username` input. You may use validation rules to achieve this goal. The following rule declaration shows
how to trim the spaces in the input by using the [trim](tutorial-core-validators.md#trim) core validator:

```php
[
    ['username', 'trim'],
]
```

You may also use the more general [filter](tutorial-core-validators.md#filter) validator if your data filtering
need is more complex than space trimming.

As you can see, these validation rules do not really validate the inputs. Instead, they will process the values
and save them back to the attributes being validated.


## Ad Hoc Validation <a name="ad-hoc-validation"></a>

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

> Note: Not all validators support such kind of validation. An example is the [unique](tutorial-core-validators.md#unique)
  core validator which is designed to work with a model only.

If you need to perform multiple validations against several values, you can use [[yii\base\DynamicModel]]
which supports declaring both attributes and rules on the fly. Its usage is like the following:

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
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
    $model = new DynamicModel(compact('name', 'email'));
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

After validation, you can check if the validation succeeds or not by calling the
[[yii\base\DynamicModel::hasErrors()|hasErrors()]] method, and then get the validation errors from the
[[yii\base\DynamicModel::errors|errors]] property, like you do with a normal model.
You may also access the dynamic attributes defined through the model instance, e.g.,
`$model->name` and `$model->email`.


## Creating Validators <a name="creating-validators"></a>

Besides using the [core validators](tutorial-core-validators.md) included in the Yii releases, you may also
create your own validators. You may create inline validators or standalone validators.


### Inline Validators <a name="inline-validators"></a>

An inline validator is one defined in terms of a model method or an anonymous function. The signature of
the method/function is:

```php
/**
 * @param string $attribute the attribute currently being validated
 * @param array $params the additional name-value pairs given in the rule
 */
function ($model, $attribute)
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
            ['token', function ($attribute, $params) {
                if (!ctype_alnum($this->$attribute)) {
                    $this->addError($attribute, 'The token must contain letters or digits.');
                }
            }],
        ];
    }

    public function validateType($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```


### Standalone Validators <a name="standalone-validators"></a>

A standalone validator is a class extending [[yii\validators\Validator]] or its child class. You may implement
its validation logic by overriding the [[yii\validators\Validator::validateAttribute()]] method. If an attribute
fails the validation, call [[yii\base\Model::addError()]] to save the error message in the model, like you do
with [inline validators](#inline-validators). For example,

```php
namespace app\components;

use yii\validators\Validator;

class CountryValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        if (!in_array($model->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```

If you want your validator to support validating a value without a model, you should also override
[[yii\validators\Validator::validate()]]. You may also override [[yii\validators\Validator::validateValue()]]
instead of `validateAttribute()` and `validate()` because by default the latter two methods are implemented
by calling `validateValue()`.


### Handling Empty and Array Inputs <a name="handling-empty-array-inputs"></a>



### Client-Side Validation <a name="client-side-validation"></a>
