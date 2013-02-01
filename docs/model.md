Model
=====

Attributes
----------

Attributes store the actual data represented by a model and can
be accessed like object member variables. For example, a `Post` model
may contain a `title` attribute and a `content` attribute which may be
accessed as follows:

~~~php
$post->title = 'Hello, world';
$post->content = 'Something interesting is happening';
echo $post->title;
echo $post->content;
~~~

A model should list all its available attributes in the `attributes()` method.

Attributes may be implemented in various ways. The [[\yii\base\Model]] class
implements attributes as public member variables of the class, while the
[[\yii\db\ActiveRecord]] class implements them as DB table columns. For example,

~~~php
// LoginForm has two attributes: username and password
class LoginForm extends \yii\base\Model
{
	public $username;
	public $password;
}

// Post is associated with the tbl_post DB table.
// Its attributes correspond to the columns in tbl_post
class Post extends \yii\db\ActiveRecord
{
	public function table()
	{
		return 'tbl_post';
	}
}
~~~


### Attribute Labels


Scenarios
---------

A model may be used in different scenarios. For example, a `User` model may be
used to collect user login inputs, and it may also be used for user registration
purpose. For this reason, each model has a property named `scenario` which stores
the name of the scenario that the model is currently being used in. As we will explain
in the next few sections, the concept of scenario is mainly used in validation and
massive attribute assignment.

Associated with each scenario is a list of attributes that are *active* in that
particular scenario. For example, in the `login` scenario, only the `username`
and `password` attributes are active; while in the `register` scenario,
additional attributes such as `email` are *active*.

Possible scenarios should be listed in the `scenarios()` method which returns an array
whose keys are the scenario names and whose values are the corresponding
active attribute lists. Below is an example:

~~~php
class User extends \yii\db\ActiveRecord
{
	public function table()
	{
		return 'tbl_user';
	}

	public function scenarios()
	{
		return array(
			'login' => array('username', 'password'),
			'register' => array('username', 'email', 'password'),
		);
	}
}
~~~

Sometimes, we want to mark that an attribute is not safe for massive assignment
(but we still want it to be validated). We may do so by prefixing an exclamation
character to the attribute name when declaring it in `scenarios()`. For example,

~~~php
array('username', 'password', '!secret')
~~~


Validation
----------

When a model is used to collect user input data via its attributes,
it usually needs to validate the affected attributes to make sure they
satisfy certain requirements, such as an attribute cannot be empty,
an attribute must contain letters only, etc. If errors are found in
validation, they may be presented to the user to help him fix the errors.
The following example shows how the validation is performed:

~~~php
$model = new LoginForm;
$model->username = $_POST['username'];
$model->password = $_POST['password'];
if ($model->validate()) {
	// ...login the user...
} else {
	$errors = $model->getErrors();
	// ...display the errors to the end user...
}
~~~

The possible validation rules for a model should be listed in its
`rules()` method. Each validation rule applies to one or several attributes
and is effective in one or several scenarios. A rule can be specified
using a validator object - an instance of a [[\yii\validators\Validator]]
child class, or an array with the following format:

~~~php
array(
	'attribute1, attribute2, ...',
	'validator class or alias',
	// specifies in which scenario(s) this rule is active.
	// if not given, it means it is active in all scenarios
	'on' => 'scenario1, scenario2, ...',
	// the following name-value pairs will be used
	// to initialize the validator properties...
	'name1' => 'value1',
	'name2' => 'value2',
	....
)
~~~

When `validate()` is called, the actual validation rules executed are
determined using both of the following criteria:

* the rules must be associated with at least one active attribute;
* the rules must be active for the current scenario.


### Active Attributes

An attribute is *active* if it is subject to some validations in the current scenario.


### Safe Attributes

An attribute is *safe* if it can be massively assigned in the current scenario.


Massive Access of Attributes
----------------------------


Massive Attribute Retrieval
---------------------------

Attributes can be massively retrieved via the `attributes` property.
The following code will return *all* attributes in the `$post` model
as an array of name-value pairs.

~~~php
$attributes = $post->attributes;
var_dump($attributes);
~~~


Massive Attribute Assignment
----------------------------




Safe Attributes
---------------

Safe attributes are those that can be massively assigned. For example,

Validation rules and mass assignment
------------------------------------

In Yii2 unlike Yii 1.x validation rules are separated from mass assignment. Validation
rules are described in `rules()` method of the model while what's safe for mass
assignment is described in `scenarios` method:

```php

function rules() {
 return array(
  // rule applied when corresponding field is "safe"
  array('username', 'length', 'min' => 2),
  array('first_name', 'length', 'min' => 2),
  array('password', 'required'),

  // rule applied when scenario is "signup" no matter if field is "safe" or not
  array('hashcode', 'check', 'on' => 'signup'),
 );
}

function scenarios() {
 return array(
  // on signup allow mass assignment of username
  'signup' => array('username', 'password'),
  'update' => array('username', 'first_name'),
 );
}

```

Note that everything is unsafe by default and you can't make field "safe"
without specifying scenario.