Model
=====

In keeping with the MVC approach, a model in Yii is intended for storing or temporarily representing application data, as well as defining the busines rules by which the data must abide.

Yii models have the following basic features:

- Attribute declaration: a model defines what is considered an attribute.
- Attribute labels: each attribute may be associated with a label for display purpose.
- Massive attribute assignment: the ability to populate multiple model attributes in one step.
- Scenario-based data validation.

Models in Yii extend from the [[yii\base\Model]] class. Models are typically used to both hold data and define
the validation rules for that data (aka, the business logic). The business logic greatly simplifies the generation
of models from complex web forms by providing validation and error reporting.

The Model class is also the base class for more advanced models with additional functionality, such
as [Active Record](active-record.md).

Attributes
----------

The actual data represented by a model is stored in the model's *attributes*. Model attributes can
be accessed like the member variables of any object. For example, a `Post` model
may contain a `title` attribute and a `content` attribute, accessible as follows:

```php
$post = new Post();
$post->title = 'Hello, world';
$post->content = 'Something interesting is happening.';
echo $post->title;
echo $post->content;
```

Since [[yii\base\Model|Model]] implements the [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) interface,
you can also access the attributes as if they were array elements:

```php
$post = new Post();
$post['title'] = 'Hello, world';
$post['content'] = 'Something interesting is happening';
echo $post['title'];
echo $post['content'];
```

By default, [[yii\base\Model|Model]] requires that attributes be declared as *public* and *non-static*
class member variables. In the following example, the `LoginForm` model class declares two attributes:
`username` and `password`.

```php
// LoginForm has two attributes: username and password
class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;
}
```

Derived model classes may declare attributes in different ways, by overriding the [[yii\base\Model::attributes()|attributes()]]
method. For example, [[yii\db\ActiveRecord]] defines attributes using the column names of the database table
that is associated with the class.


Attribute Labels
----------------

Attribute labels are mainly used for display purpose. For example, given an attribute `firstName`, we can declare
a label `First Name` that is more user-friendly when displayed to end users in places such as form labels and
error messages. Given an attribute name, you can obtain its label by calling [[yii\base\Model::getAttributeLabel()]].

To declare attribute labels, override the [[yii\base\Model::attributeLabels()]] method. The overridden method returns
a mapping of attribute names to attribute labels, as shown in the example below. If an attribute is not found
in this mapping, its label will be generated using the [[yii\base\Model::generateAttributeLabel()]] method.
In many cases, [[yii\base\Model::generateAttributeLabel()]] will generate reasonable labels (e.g. `username` to `Username`,
`orderNumber` to `Order Number`).

```php
// LoginForm has two attributes: username and password
class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;

    public function attributeLabels()
    {
        return [
            'username' => 'Your name',
            'password' => 'Your password',
        ];
    }
}
```

Scenarios
---------

A model may be used in different *scenarios*. For example, a `User` model may be used to collect user login inputs,
but it may also be used for user registration purposes. In the one scenario, every piece of data is required;
in the other, only the username and password would be.

To easily implement the business logic for different scenarios, each model has a property named `scenario`
that stores the name of the scenario that the model is currently being used in. As will be explained in the next
few sections, the concept of scenarios is mainly used for data validation and massive attribute assignment.

Associated with each scenario is a list of attributes that are *active* in that particular scenario. For example,
in the `login` scenario, only the `username` and `password` attributes are active; while in the `register` scenario,
additional attributes such as `email` are *active*. When an attribute is *active* this means that it is subject to validation.

Possible scenarios should be listed in the `scenarios()` method. This method returns an array whose keys are the scenario
names and whose values are lists of attributes that should be active in that scenario:

```php
class User extends \yii\db\ActiveRecord
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

If `scenarios` method is not defined, default scenario is applied. That means attributes with validation rules are
considered *active*.

If you want to keep the default scenario available besides your own scenarios, use inheritance to include it:
```php
class User extends \yii\db\ActiveRecord
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


Validation
----------

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


Massive Attribute Retrieval and Assignment
------------------------------------------

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

See also
--------

- [Model validation reference](validation.md)
- [[yii\base\Model]]
