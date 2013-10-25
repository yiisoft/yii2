Model
=====

In keeping with the MVC approach, a model in Yii is intended for storing or temporarily representing application data. Yii models have the following basic features:

- Attribute declaration: a model defines what is considered an attribute.
- Attribute labels: each attribute may be associated with a label for display purpose.
- Massive attribute assignment: the ability to populate multiple model attributes in one step.
- Scenario-based data validation.

Models in Yii extend from the [[\yii\base\Model]] class. Models are typically used to both hold data and define the validation rules for that data. The validation rules greatly simply the generation of models from complex web forms.
The Model class is also the base for more advanced models with additional functionality such as [Active Record](active-record.md).

Attributes
----------

Attributes store the actual data represented by a model and can
be accessed like object member variables. For example, a `Post` model
may contain a `title` attribute and a `content` attribute which may be
accessed as follows:

```php
$post = new Post;
$post->title = 'Hello, world';
$post->content = 'Something interesting is happening';
echo $post->title;
echo $post->content;
```

Since [[\yii\base\Model|Model]] implements the [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) interface,
you can also access the attributes like accessing array elements:

```php
$post = new Post;
$post['title'] = 'Hello, world';
$post['content'] = 'Something interesting is happening';
echo $post['title'];
echo $post['content'];
```

By default, [[\yii\base\Model|Model]] requires that attributes be declared as *public* and *non-static*
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

Derived model classes may use different ways to declare attributes by overriding the [[\yii\base\Model::attributes()|attributes()]]
method. For example, [[\yii\db\ActiveRecord]] defines attributes as the column names of the database table
that is associated with the class.


Attribute Labels
----------------

Attribute labels are mainly used for display purpose. For example, given an attribute `firstName`, we can declare
a label `First Name` which is more user-friendly and can be displayed to end users in places such as form labels,
error messages. Given an attribute name, you can obtain its label by calling [[\yii\base\Model::getAttributeLabel()]].

To declare attribute labels, you should override the [[\yii\base\Model::attributeLabels()]] method and return
a mapping from attribute names to attribute labels, like shown in the example below. If an attribute is not found
in this mapping, its label will be generated using the [[\yii\base\Model::generateAttributeLabel()]] method, which
in many cases, will generate reasonable labels (e.g. `username` to `Username`, `orderNumber` to `Order Number`).

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

A model may be used in different scenarios. For example, a `User` model may be used to collect user login inputs,
and it may also be used for user registration purpose. For this reason, each model has a property named `scenario`
which stores the name of the scenario that the model is currently being used in. As we will explain in the next
few sections, the concept of scenario is mainly used for data validation and massive attribute assignment.

Associated with each scenario is a list of attributes that are *active* in that particular scenario. For example,
in the `login` scenario, only the `username` and `password` attributes are active; while in the `register` scenario,
additional attributes such as `email` are *active*.

Possible scenarios should be listed in the `scenarios()` method which returns an array whose keys are the scenario
names and whose values are the corresponding active attribute lists. Below is an example:

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

Sometimes, we want to mark an attribute as not safe for massive assignment (but we still want it to be validated).
We may do so by prefixing an exclamation character to the attribute name when declaring it in `scenarios()`. For example,

```php
['username', 'password', '!secret']
```

Active model scenario could be set using one of the following ways:

```php
class EmployeeController extends \yii\web\Controller
{
	public function actionCreate($id = null)
	{
		// first way
		$employee = new Employee(['scenario' => 'managementPanel']);

		// second way
		$employee = new Employee;
		$employee->scenario = 'managementPanel';

		// third way
		$employee = Employee::find()->where('id = :id', [':id' => $id])->one();
		if ($employee !== null) {
			$employee->setScenario('managementPanel');
		}
	}
}
```

In the example above we are using [Active Record](active-record.md). For basic form models it's rarely needed to
use scenarios since form model is typically used for a single form.

Validation
----------

When a model is used to collect user input data via its attributes, it usually needs to validate the affected attributes
to make sure they satisfy certain requirements, such as an attribute cannot be empty, an attribute must contain letters
only, etc. If errors are found in validation, they may be presented to the user to help him fix the errors.
The following example shows how the validation is performed:

```php
$model = new LoginForm;
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
instance of a [[\yii\validators\Validator]] child class, or an array with the following format:

```php
[
	'attribute1, attribute2, ...',
	'validator class or alias',
	// specifies in which scenario(s) this rule is active.
	// if not given, it means it is active in all scenarios
	'on' => 'scenario1, scenario2, ...',
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


### Active Attributes

An attribute is *active* if it is subject to some validations in the current scenario.


### Safe Attributes

An attribute is *safe* if it can be massively assigned in the current scenario.


Massive Attribute Retrieval and Assignment
------------------------------------------

Attributes can be massively retrieved via the `attributes` property.
The following code will return *all* attributes in the `$post` model
as an array of name-value pairs.

```php
$attributes = $post->attributes;
var_dump($attributes);
```

Using the same `attributes` property you can massively assign data from associative array to model attributes:

```php
$attributes = [
	'title' => 'Model attributes',
	'create_time' => time(),
];
$post->attributes = $attributes;
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
function rules()
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

function scenarios()
{
	return [
		// on signup allow mass assignment of username
		'signup' => ['username', 'password'],
		'update' => ['username', 'first_name'],
	];
}
```

Note that everything is unsafe by default and you can't make field "safe" without specifying scenario.


See also
--------

- [Model validation reference](validation.md)
- [[\yii\base\Model]]
