Models
======

Models are objects representing business data, rules and logic. They are part of
the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.

You can create model classes by extending [[yii\base\Model]] or its child classes. The base class
[[yii\base\Model]] supports many useful features:

* Attributes: represent the business data;
* Attribute labels: specify the display labels for attributes;
* Massive attribute assignment: supports populating multiple attributes in a single step;
* Data validation: validates input data based on the declared validation rules;
* Data export: allows exporting model data in terms of arrays without customizable formats;
* Array access: supports accessing model data like an associative array.

The `Model` class is also the base class for more advanced models, such as [Active Record](db-active-record.md).
Please refer to the relevant documentation for more details about these advanced models.

> Info: You are not required to base your model classes on [[yii\base\Model]]. However, because there are many Yii
  components built to support [[yii\base\Model]], it is usually the preferable base model classes.


## Attributes

Attributes are the properties that represent business data. By default, attributes are *non-static public*
member variables if your model class extends directly from [[yii\base\Model]].

The following code creates a `ContactForm` model class with four attributes: `name`, `email`, `subject` and `body`.

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;
}
```

Naturally, you can access an attribute like accessing a normal object property:

```php
$model = new \app\models\ContactForm;
$model->name = 'example';
echo $model->name;
```

You can also access attributes like accessing array elements, thanks to the support for
[ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.arrayiterator.php)
by [[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// accessing attributes like array elements
$model['name'] = 'example';
echo $model['name'];

// iterate attributes
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```

You may override [[yii\base\Model::attributes()]] if you want to support different ways of defining attributes.
For example, [[yii\db\ActiveRecord]] does so and defines attributes according to table columns. Note that you
may also need to override the magic methods such as `__get()`, `__set()` so that the attributes can be accessed
like normal object properties.


## Attribute Labels

When displaying values or getting input for attributes, you often need to display some labels associated
with attributes. For example, given an attribute named `firstName`, you may want to display a label `First Name`
which is more user-friendly when displayed to end users in places such as form inputs and error messages.

By default, attribute labels are automatically generated from attribute names. The generation is done by
the method [[yii\base\Model::generateAttributeLabel()]]. It will turn camel-case variable names into
multiple words with the first letter in each word in upper case. For example, `username` becomes `Username`,
and `firstName` becomes `First Name`.

If you do not want to use automatically generated labels, you may override [[yii\base\Model::attributeLabels()]]
to explicitly declare attribute labels. For example,

```php
namespace app\models;

use yii\base\Model;

class ContactForm extends Model
{
    public $name;
    public $email;
    public $subject;
    public $body;

    public function attributeLabels()
    {
        return [
            'name' => 'Your name',
            'email' => 'Your email address',
            'subject' => 'Subject',
            'body' => 'Content',
        ];
    }
}
```

For applications supporting multiple languages, you may want to translate attribute labels. This can be done
in the [[yii\base\Model::attributeLabels()|attributeLabels()]] method as well, like the following:

```php
public function attributeLabels()
{
    return [
        'name' => \Yii::t('app', 'Your name'),
        'email' => \Yii::t('app', 'Your email address'),
        'subject' => \Yii::t('app', 'Subject'),
        'body' => \Yii::t('app', 'Content'),
    ];
}
```

> Info: Strictly speaking, attribute labels are part of [views](structure-views.md). But declaring labels
  in models is often very convenient and can result in very clean and reusable code.


## Scenarios

A model may be used in different *scenarios*. For example, a `User` model may be used to collect user login inputs,
but it may also be used for the user registration purpose. In different scenarios, a model may use different
business rules and logic. For example, the `email` attribute may be required during user registration,
but not so during user login.

A model uses the [[yii\base\Model::scenario]] property to keep track of the scenario it is being used in.
By default, a model supports only a single scenario named `default`.

To support multiple scenarios, you may override the [[yii\base\Model::scenarios()]] method, like the following:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public function scenarios()
    {
        return [
            'login' => ['username', 'password'],
            'register' => ['username', 'email', 'password'],
        ];
    }
}
```

> Info: In the above and following examples, the model classes are extending from [[yii\db\ActiveRecord]]
  because the usage of multiple scenarios usually happens to [Active Record](db-active-record.md) classes.

The `scenarios()` method returns an array whose keys are the scenario names and values the corresponding
*active attributes*. An active attribute can be [massively assigned](#massive-assignment) and is subject
to [validation](#validation). In the above example, the `username` and `password` attributes are active
in the `login` scenario; while in the `register` scenario, `email` is also active besides `username` and `password`.

The default implementation of `scenarios()` will return all scenarios found in the validation rule declaration
method [[yii\base\Model::rules()]]. When overriding `scenarios()`, if you want to introduce new scenarios
in addition to the default ones, you may write code like the following:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['login'] = ['username', 'password'];
        $scenarios['register'] = ['username', 'email', 'password'];
        return $scenarios;
    }
}
```

The scenario feature is primarily used by [validation](#validation) and [massive attribute assignment](#massive-assignment).
You can, however, use it for other purposes. For example, you may declare [attribute labels](#attribute-labels)
differently based on the current scenario.


## Validation <a name="validation"></a>

When a model is used to collect user input data via its attributes, it usually needs to validate the affected attributes
to make sure they satisfy certain requirements, such as an attribute cannot be empty, an attribute must contain letters
only, etc. If errors are found in validation, they may be presented to the user to help him fix the errors.
The following example shows how the validation is performed:

```php
$model = new LoginForm();
$model->username = $_POST['username'];
$model->password = $_POST['password'];
if ($model->validate()) {
    // ... login the user ...
} else {
    $errors = $model->getErrors();
    // ... display the errors to the end user ...
}
```

The possible validation rules for a model should be listed in its `rules()` method. Each validation rule applies to one
or several attributes and is effective in one or several scenarios. A rule can be specified using a validator object - an
instance of a [[yii\validators\Validator]] child class, or an array with the following format:

```php
[
    ['attribute1', 'attribute2', ...],
    'validator class or alias',
    // specifies in which scenario(s) this rule is active.
    // if not given, it means it is active in all scenarios
    'on' => ['scenario1', 'scenario2', ...],
    // the following name-value pairs will be used
    // to initialize the validator properties
    'property1' => 'value1',
    'property2' => 'value2',
    // ...
]
```

When `validate()` is called, the actual validation rules executed are determined using both of the following criteria:

- the rule must be associated with at least one active attribute;
- the rule must be active for the current scenario.


### Creating your own validators (Inline validators)

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

### Conditional validation

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


## Massive Attribute Assignment <a name="massive-assignment"></a>

Attributes can be massively retrieved via the `attributes` property.
The following code will return *all* attributes in the `$post` model
as an array of name-value pairs.

```php
$post = Post::findOne(42);
if ($post) {
    $attributes = $post->attributes;
    var_dump($attributes);
}
```

Using the same `attributes` property you can massively assign data from associative array to model attributes:

```php
$post = new Post();
$attributes = [
    'title' => 'Massive assignment example',
    'content' => 'Never allow assigning attributes that are not meant to be assigned.',
];
$post->attributes = $attributes;
var_dump($post->attributes);
```

In the code above we're assigning corresponding data to model attributes named as array keys. The key difference from mass
retrieval that always works for all attributes is that in order to be assigned an attribute should be **safe** else
it will be ignored.



Sometimes, we want to mark an attribute as not safe for massive assignment (but we still want the attribute to be validated).
We may do so by prefixing an exclamation character to the attribute name when declaring it in `scenarios()`. For example:

```php
['username', 'password', '!secret']
```

In this example `username`, `password` and `secret` are *active* attributes but only `username` and `password` are
considered safe for massive assignment.

Identifying the active model scenario can be done using one of the following approaches:

```php
class EmployeeController extends \yii\web\Controller
{
    public function actionCreate($id = null)
    {
        // first way
        $employee = new Employee(['scenario' => 'managementPanel']);

        // second way
        $employee = new Employee();
        $employee->scenario = 'managementPanel';

        // third way
        $employee = Employee::find()->where('id = :id', [':id' => $id])->one();
        if ($employee !== null) {
            $employee->scenario = 'managementPanel';
        }
    }
}
```

The example above presumes that the model is based upon [Active Record](active-record.md). For basic form models,
scenarios are rarely needed, as the basic form model is normally tied directly to a single form and, as noted above,
the default implementation of the `scenarios()` returns every property with active validation rule making it always
available for mass assignment and validation.


Validation rules and mass assignment
------------------------------------

In Yii2 unlike Yii 1.x validation rules are separated from mass assignment. Validation
rules are described in `rules()` method of the model while what's safe for mass
assignment is described in `scenarios` method:

```php
class User extends ActiveRecord
{
    public function rules()
    {
        return [
            // rule applied when corresponding field is "safe"
            ['username', 'string', 'length' => [4, 32]],
            ['first_name', 'string', 'max' => 128],
            ['password', 'required'],

            // rule applied when scenario is "signup" no matter if field is "safe" or not
            ['hashcode', 'check', 'on' => 'signup'],
        ];
    }

    public function scenarios()
    {
        return [
            // on signup allow mass assignment of username
            'signup' => ['username', 'password'],
            'update' => ['username', 'first_name'],
        ];
    }
}
```

For the code above mass assignment will be allowed strictly according to `scenarios()`:

```php
$user = User::findOne(42);
$data = ['password' => '123'];
$user->attributes = $data;
print_r($user->attributes);
```

Will give you empty array because there's no default scenario defined in our `scenarios()`.

```php
$user = User::findOne(42);
$user->scenario = 'signup';
$data = [
    'username' => 'samdark',
    'password' => '123',
    'hashcode' => 'test',
];
$user->attributes = $data;
print_r($user->attributes);
```

Will give you the following:

```php
array(
    'username' => 'samdark',
    'first_name' => null,
    'password' => '123',
    'hashcode' => null, // it's not defined in scenarios method
)
```

In case of not defined `scenarios` method like the following:

```php
class User extends ActiveRecord
{
    public function rules()
    {
        return [
            ['username', 'string', 'length' => [4, 32]],
            ['first_name', 'string', 'max' => 128],
            ['password', 'required'],
        ];
    }
}
```

The code above assumes default scenario so mass assignment will be available for all fields with `rules` defined:

```php
$user = User::findOne(42);
$data = [
    'username' => 'samdark',
    'first_name' => 'Alexander',
    'last_name' => 'Makarov',
    'password' => '123',
];
$user->attributes = $data;
print_r($user->attributes);
```

Will give you the following:

```php
array(
    'username' => 'samdark',
    'first_name' => 'Alexander',
    'password' => '123',
)
```

If you want some fields to be unsafe for default scenario:

```php
class User extends ActiveRecord
{
    function rules()
    {
        return [
            ['username', 'string', 'length' => [4, 32]],
            ['first_name', 'string', 'max' => 128],
            ['password', 'required'],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['username', 'first_name', '!password']
        ];
    }
}
```

Mass assignment is still available by default:

```php
$user = User::findOne(42);
$data = [
    'username' => 'samdark',
    'first_name' => 'Alexander',
    'password' => '123',
];
$user->attributes = $data;
print_r($user->attributes);
```

The code above gives you:

```php
array(
    'username' => 'samdark',
    'first_name' => 'Alexander',
    'password' => null, // because of ! before field name in scenarios
)
```

## Data Exporting

## Best Practices

scenarios
validation rules
labels
