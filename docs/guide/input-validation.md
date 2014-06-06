Validating Input
================

As a rule of thumb, you should never trust the data coming from end users and should always validate them
before putting them to good use. In the [Models](structure-models.md#validation) section, we have described
how validation works in general. In this section, we will give more details about validation.


## Validating Input

Assume you have a model that takes user input. In order to validate the input, you should define a set
of validation rules by overriding the [[yii\base\Model::rules()]] method, like the following,

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

The `rules()` method returns an array of rules, each of which is an array in the following format:

```php
[
    // required, specifies which attributes should be validated by this rule.
    // For single attribute, you can use the attribute name directly
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



When the `validate()` method is called, it does the following steps to perform validation:

1. Determine which attributes should be validated by checking the current [[yii\base\Model::scenario|scenario]]
   against the scenarios declared in [[yii\base\Model::scenarios()]]. These attributes are the active attributes.
2. Determine which rules should be applied by checking the current [[yii\base\Model::scenario|scenario]]
   against the rules declared in [[yii\base\Model::rules()]]. These rules are the active rules.
3. Use each active rule to validate each active attribute which is associated with the rule.

According to the above validation steps, an attribute will be validated if and only if it is
an active attribute declared in `scenarios()` and it is associated with one or multiple active rules
declared in `rules()`.

Yii provides a set of [built-in validators](tutorial-core-validators.md) to support commonly needed data
validation tasks. You may also create your own validators by extending [[yii\validators\Validator]] or
writing an inline validation method within model classes. For more details about the built-in validators
and how to create your own validators, please refer to the [Input Validation](input-validation.md) section.

### Declaring Validation Rules

### Error Messages

### Core Validators

### Conditional Validation

### Ad Hoc Validation

### Data Filtering



## Creating Validators

### Inline Validators

### Standalone Validators

### Empty Inputs and Array Inputs

### Client-Side Validation



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
