Models
======

Models are part of the [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.
They are objects representing business data, rules and logic.

You can create model classes by extending [[yii\base\Model]] or its child classes. The base class
[[yii\base\Model]] supports many useful features:

* [Attributes](#attributes): represent the business data and can be accessed like normal object properties
  or array elements;
* [Attribute labels](#attribute-labels): specify the display labels for attributes;
* [Massive assignment](#massive-assignment): supports populating multiple attributes in a single step;
* [Validation rules](#validation-rules): ensures input data based on the declared validation rules;
* [Data Exporting](#data-exporting): allows model data to be exported in terms of arrays with customizable formats.

The `Model` class is also the base class for more advanced models, such as [Active Record](db-active-record.md).
Please refer to the relevant documentation for more details about these advanced models.

> Info: You are not required to base your model classes on [[yii\base\Model]]. However, because there are many Yii
  components built to support [[yii\base\Model]], it is usually the preferable base class for a model.


## Attributes <span id="attributes"></span>

Models represent business data in terms of *attributes*. Each attribute is like a publicly accessible property
of a model. The method [[yii\base\Model::attributes()]] specifies what attributes a model class has.

You can access an attribute like accessing a normal object property:

```php
$model = new \app\models\ContactForm;

// "name" is an attribute of ContactForm
$model->name = 'example';
echo $model->name;
```

You can also access attributes like accessing array elements, thanks to the support for
[ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) and [Traversable](https://www.php.net/manual/en/class.traversable.php)
by [[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// accessing attributes like array elements
$model['name'] = 'example';
echo $model['name'];

// Model is traversable using foreach.
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```


### Defining Attributes <span id="defining-attributes"></span>

By default, if your model class extends directly from [[yii\base\Model]], all its *non-static public* member
variables are attributes. For example, the `ContactForm` model class below has four attributes: `name`, `email`,
`subject` and `body`. The `ContactForm` model is used to represent the input data received from an HTML form.

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


You may override [[yii\base\Model::attributes()]] to define attributes in a different way. The method should
return the names of the attributes in a model. For example, [[yii\db\ActiveRecord]] does so by returning
the column names of the associated database table as its attribute names. Note that you may also need to
override the magic methods such as `__get()`, `__set()` so that the attributes can be accessed like
normal object properties.


### Attribute Labels <span id="attribute-labels"></span>

When displaying values or getting input for attributes, you often need to display some labels associated
with attributes. For example, given an attribute named `firstName`, you may want to display a label `First Name`
which is more user-friendly when displayed to end users in places such as form inputs and error messages.

You can get the label of an attribute by calling [[yii\base\Model::getAttributeLabel()]]. For example,

```php
$model = new \app\models\ContactForm;

// displays "Name"
echo $model->getAttributeLabel('name');
```

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

You may even conditionally define attribute labels. For example, based on the [scenario](#scenarios) the model
is being used in, you may return different labels for the same attribute.

> Info: Strictly speaking, attribute labels are part of [views](structure-views.md). But declaring labels
  in models is often very convenient and can result in very clean and reusable code.


## Scenarios <span id="scenarios"></span>

A model may be used in different *scenarios*. For example, a `User` model may be used to collect user login inputs,
but it may also be used for the user registration purpose. In different scenarios, a model may use different
business rules and logic. For example, the `email` attribute may be required during user registration,
but not so during user login.

A model uses the [[yii\base\Model::scenario]] property to keep track of the scenario it is being used in.
By default, a model supports only a single scenario named `default`. The following code shows two ways of
setting the scenario of a model:

```php
// scenario is set as a property
$model = new User;
$model->scenario = User::SCENARIO_LOGIN;

// scenario is set through configuration
$model = new User(['scenario' => User::SCENARIO_LOGIN]);
```

By default, the scenarios supported by a model are determined by the [validation rules](#validation-rules) declared
in the model. However, you can customize this behavior by overriding the [[yii\base\Model::scenarios()]] method,
like the following:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        return [
            self::SCENARIO_LOGIN => ['username', 'password'],
            self::SCENARIO_REGISTER => ['username', 'email', 'password'],
        ];
    }
}
```

> Info: In the above and following examples, the model classes are extending from [[yii\db\ActiveRecord]]
  because the usage of multiple scenarios usually happens to [Active Record](db-active-record.md) classes.

The `scenarios()` method returns an array whose keys are the scenario names and values the corresponding
*active attributes*. An active attribute can be [massively assigned](#massive-assignment) and is subject
to [validation](#validation-rules). In the above example, the `username` and `password` attributes are active
in the `login` scenario; while in the `register` scenario, `email` is also active besides `username` and `password`.

The default implementation of `scenarios()` will return all scenarios found in the validation rule declaration
method [[yii\base\Model::rules()]]. When overriding `scenarios()`, if you want to introduce new scenarios
in addition to the default ones, you may write code like the following:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REGISTER = 'register';

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password'];
        $scenarios[self::SCENARIO_REGISTER] = ['username', 'email', 'password'];
        return $scenarios;
    }
}
```

The scenario feature is primarily used by [validation](#validation-rules) and [massive attribute assignment](#massive-assignment).
You can, however, use it for other purposes. For example, you may declare [attribute labels](#attribute-labels)
differently based on the current scenario.


## Validation Rules <span id="validation-rules"></span>

When the data for a model is received from end users, it should be validated to make sure it satisfies
certain rules (called *validation rules*, also known as *business rules*). For example, given a `ContactForm` model,
you may want to make sure all attributes are not empty and the `email` attribute contains a valid email address.
If the values for some attributes do not satisfy the corresponding business rules, appropriate error messages
should be displayed to help the user to fix the errors.

You may call [[yii\base\Model::validate()]] to validate the received data. The method will use
the validation rules declared in [[yii\base\Model::rules()]] to validate every relevant attribute. If no error
is found, it will return `true`. Otherwise, it will keep the errors in the [[yii\base\Model::errors]] property
and return `false`. For example,

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


To declare validation rules associated with a model, override the [[yii\base\Model::rules()]] method by returning
the rules that the model attributes should satisfy. The following example shows the validation rules declared
for the `ContactForm` model:

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

A rule can be used to validate one or multiple attributes, and an attribute may be validated by one or multiple rules.
Please refer to the [Validating Input](input-validation.md) section for more details on how to declare
validation rules.

Sometimes, you may want a rule to be applied only in certain [scenarios](#scenarios). To do so, you can
specify the `on` property of a rule, like the following:

```php
public function rules()
{
    return [
        // username, email and password are all required in "register" scenario
        [['username', 'email', 'password'], 'required', 'on' => self::SCENARIO_REGISTER],

        // username and password are required in "login" scenario
        [['username', 'password'], 'required', 'on' => self::SCENARIO_LOGIN],
        
        [['username'], 'string'], // username must always be a string, this rule applies to all scenarios
    ];
}
```

If you do not specify the `on` property, the rule would be applied in all scenarios. A rule is called
an *active rule* if it can be applied in the current [[yii\base\Model::scenario|scenario]].

An attribute will be validated if and only if it is an active attribute declared in `scenarios()` and
is associated with one or multiple active rules declared in `rules()`.


## Massive Assignment <span id="massive-assignment"></span>

Massive assignment is a convenient way of populating a model with user inputs using a single line of code.
It populates the attributes of a model by assigning the input data directly to the [[yii\base\Model::$attributes]]
property. The following two pieces of code are equivalent, both trying to assign the form data submitted by end users
to the attributes of the `ContactForm` model. Clearly, the former, which uses massive assignment, is much cleaner
and less error prone than the latter:

```php
$model = new \app\models\ContactForm;
$model->attributes = \Yii::$app->request->post('ContactForm');
```

```php
$model = new \app\models\ContactForm;
$data = \Yii::$app->request->post('ContactForm', []);
$model->name = isset($data['name']) ? $data['name'] : null;
$model->email = isset($data['email']) ? $data['email'] : null;
$model->subject = isset($data['subject']) ? $data['subject'] : null;
$model->body = isset($data['body']) ? $data['body'] : null;
```


### Safe Attributes <span id="safe-attributes"></span>

Massive assignment only applies to the so-called *safe attributes* which are the attributes listed in
[[yii\base\Model::scenarios()]] for the current [[yii\base\Model::scenario|scenario]] of a model.
For example, if the `User` model has the following scenario declaration, then when the current scenario
is `login`, only the `username` and `password` can be massively assigned. Any other attributes will
be kept untouched.

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password'],
        self::SCENARIO_REGISTER => ['username', 'email', 'password'],
    ];
}
```

> Info: The reason that massive assignment only applies to safe attributes is because you want to
  control which attributes can be modified by end user data. For example, if the `User` model
  has a `permission` attribute which determines the permission assigned to the user, you would
  like this attribute to be modifiable by administrators through a backend interface only.

Because the default implementation of [[yii\base\Model::scenarios()]] will return all scenarios and attributes
found in [[yii\base\Model::rules()]], if you do not override this method, it means an attribute is safe as long
as it appears in one of the active validation rules.

For this reason, a special validator aliased `safe` is provided so that you can declare an attribute
to be safe without actually validating it. For example, the following rules declare that both `title`
and `description` are safe attributes.

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### Unsafe Attributes <span id="unsafe-attributes"></span>

As described above, the [[yii\base\Model::scenarios()]] method serves for two purposes: determining which attributes
should be validated, and determining which attributes are safe. In some rare cases, you may want to validate
an attribute but do not want to mark it safe. You can do so by prefixing an exclamation mark `!` to the attribute
name when declaring it in `scenarios()`, like the `secret` attribute in the following:

```php
public function scenarios()
{
    return [
        self::SCENARIO_LOGIN => ['username', 'password', '!secret'],
    ];
}
```

When the model is in the `login` scenario, all three attributes will be validated. However, only the `username`
and `password` attributes can be massively assigned. To assign an input value to the `secret` attribute, you
have to do it explicitly as follows,

```php
$model->secret = $secret;
```

The same can be done in `rules()` method:

```php
public function rules()
{
    return [
        [['username', 'password', '!secret'], 'required', 'on' => 'login']
    ];
}
```

In this case attributes `username`, `password` and `secret` are required, but `secret` must be assigned explicitly.


## Data Exporting <span id="data-exporting"></span>

Models often need to be exported in different formats. For example, you may want to convert a collection of
models into JSON or Excel format. The exporting process can be broken down into two independent steps:

- models are converted into arrays;
- the arrays are converted into target formats.

You may just focus on the first step, because the second step can be achieved by generic
data formatters, such as [[yii\web\JsonResponseFormatter]].

The simplest way of converting a model into an array is to use the [[yii\base\Model::$attributes]] property.
For example,

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

By default, the [[yii\base\Model::$attributes]] property will return the values of *all* attributes
declared in [[yii\base\Model::attributes()]].

A more flexible and powerful way of converting a model into an array is to use the [[yii\base\Model::toArray()]]
method. Its default behavior is the same as that of [[yii\base\Model::$attributes]]. However, it allows you
to choose which data items, called *fields*, to be put in the resulting array and how they should be formatted.
In fact, it is the default way of exporting models in RESTful Web service development, as described in
the [Response Formatting](rest-response-formatting.md).


### Fields <span id="fields"></span>

A field is simply a named element in the array that is obtained by calling the [[yii\base\Model::toArray()]] method
of a model.

By default, field names are equivalent to attribute names. However, you can change this behavior by overriding
the [[yii\base\Model::fields()|fields()]] and/or [[yii\base\Model::extraFields()|extraFields()]] methods. Both methods
should return a list of field definitions. The fields defined by `fields()` are default fields, meaning that
`toArray()` will return these fields by default. The `extraFields()` method defines additionally available fields
which can also be returned by `toArray()` as long as you specify them via the `$expand` parameter. For example,
the following code will return all fields defined in `fields()` and the `prettyName` and `fullAddress` fields
if they are defined in `extraFields()`.

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

You can override `fields()` to add, remove, rename or redefine fields. The return value of `fields()`
should be an array. The array keys are the field names, and the array values are the corresponding
field definitions which can be either property/attribute names or anonymous functions returning the
corresponding field values. In the special case when a field name is the same as its defining attribute
name, you can omit the array key. For example,

```php
// explicitly list every field, best used when you want to make sure the changes
// in your DB table or model attributes do not cause your field changes (to keep API backward compatibility).
public function fields()
{
    return [
        // field name is the same as the attribute name
        'id',

        // field name is "email", the corresponding attribute name is "email_address"
        'email' => 'email_address',

        // field name is "name", its value is defined by a PHP callback
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// filter out some fields, best used when you want to inherit the parent implementation
// and exclude some sensitive fields.
public function fields()
{
    $fields = parent::fields();

    // remove fields that contain sensitive information
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: Because by default all attributes of a model will be included in the exported array, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.


## Best Practices <span id="best-practices"></span>

Models are the central places to represent business data, rules and logic. They often need to be reused
in different places. In a well-designed application, models are usually much fatter than
[controllers](structure-controllers.md).

In summary, models

* may contain attributes to represent business data;
* may contain validation rules to ensure the data validity and integrity;
* may contain methods implementing business logic;
* should NOT directly access request, session, or any other environmental data. These data should be injected
  by [controllers](structure-controllers.md) into models;
* should avoid embedding HTML or other presentational code - this is better done in [views](structure-views.md);
* avoid having too many [scenarios](#scenarios) in a single model.

You may usually consider the last recommendation above when you are developing large complex systems.
In these systems, models could be very fat because they are used in many places and may thus contain many sets
of rules and business logic. This often ends up in a nightmare in maintaining the model code
because a single touch of the code could affect several different places. To make the model code more maintainable,
you may take the following strategy:

* Define a set of base model classes that are shared by different [applications](structure-applications.md) or
  [modules](structure-modules.md). These model classes should contain minimal sets of rules and logic that
  are common among all their usages.
* In each [application](structure-applications.md) or [module](structure-modules.md) that uses a model,
  define a concrete model class by extending from the corresponding base model class. The concrete model classes
  should contain rules and logic that are specific for that application or module.

For example, in the [Advanced Project Template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), you may define a base model
class `common\models\Post`. Then for the front end application, you define and use a concrete model class
`frontend\models\Post` which extends from `common\models\Post`. And similarly for the back end application,
you define `backend\models\Post`. With this strategy, you will be sure that the code in `frontend\models\Post`
is only specific to the front end application, and if you make any change to it, you do not need to worry if
the change may break the back end application.
