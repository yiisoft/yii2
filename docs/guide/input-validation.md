Validating Input
================

> Note: This section is under development.

In the [Models](structure-models.md#validation) section, we have described how data validation works
in general. In this section, we will mainly focus on describing core validators, how to define your
own validators, and different ways of using validators.


## Declaring Validation Rules

## Data Validation

### Getting Error Messages

### Empty Values

### Array Values

## Data Filtering



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
