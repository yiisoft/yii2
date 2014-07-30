输入验证
================

一般说来，程序猿永远不应该信任从最终用户直接接收到的数据，并且使用它们之前应始终先验证其可靠性。

要给 [model](structure-models.md) 填充其所需的用户输入数据，你可以调用 [[yii\base\Model::validate()]] 方法验证它们。该方法会返回一个布尔值，指明是否通过验证。若没有通过，你能通过 [[yii\base\Model::errors]] 属性获取相应的报错信息。比如，

```php
$model = new \app\models\ContactForm;

// 用用户输入来填充模型的特性
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // 若所有输入都是有效的
} else {
    // 有效性验证失败：$errors 属性就是存储错误信息的数组
    $errors = $model->errors;
}
```

`validate()` 方法，在幕后为执行验证操作，进行了以下步骤：

1. 通过从 [[yii\base\Model::scenarios()]] 方法返回基于当前 [[yii\base\Model::scenario|场景（scenario）]] 的特性属性列表，算出哪些特性应该进行有效性验证。这些属性被称作 *active attributes*（激活特性）。
2. 通过从 [[yii\base\Model::rules()]] 方法返回基于当前 [[yii\base\Model::scenario|场景（scenario）]] 的验证规则列表，这些规则被称作 *active rules*（激活规则）。
3. 用每个激活规则去验证每个与之关联的激活特性。若失败，则记录下对应模型特性的错误信息。


## 声明规则（Rules） <a name="declaring-rules"></a>

要让 `validate()` 方法起作用，你需要声明与需验证模型特性相关的验证规则。为此，需要重写 [[yii\base\Model::rules()]] 方法。下面的例子展示了如何声明用于验证 `ContactForm` 模型的相关验证规则：

```php
public function rules()
{
    return [
        // name，email，subject 和 body 特性是 `require`（必填）的
        [['name', 'email', 'subject', 'body'], 'required'],

        // email 特性必须是一个有效的 email 地址
        ['email', 'email'],
    ];
}
```

[[yii\base\Model::rules()|rules()]] 方法应返回一个由规则所组成的数组，每一个规则都呈现为以下这类格式的小数组：

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


### 自定义错误信息 <a name="customizing-error-messages"></a>

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


### 验证事件 <a name="validation-events"></a>

When [[yii\base\Model::validate()]] is called, it will call two methods that you may override to customize
the validation process:

* [[yii\base\Model::beforeValidate()]]: the default implementation will trigger a [[yii\base\Model::EVENT_BEFORE_VALIDATE]]
  event. You may either override this method or respond to this event to do some preprocessing work
  (e.g. normalizing data inputs) before the validation occurs. The method should return a boolean value indicating
  whether the validation should proceed or not.
* [[yii\base\Model::afterValidate()]]: the default implementation will trigger a [[yii\base\Model::EVENT_AFTER_VALIDATE]]
  event. You may either override this method or respond to this event to do some postprocessing work after
  the validation is completed.


### 条件式验证 <a name="conditional-validation"></a>

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


### 数据过滤 <a name="data-filtering"></a>

User inputs often need to be filtered or preprocessed. For example, you may want to trim the spaces around the
`username` input. You may use validation rules to achieve this goal.

The following examples shows how to trim the spaces in the inputs and turn empty inputs into nulls by using
the [trim](tutorial-core-validators.md#trim) and [default](tutorial-core-validators.md#default) core validators:

```php
[
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
]
```

You may also use the more general [filter](tutorial-core-validators.md#filter) validator to perform more complex
data filtering.

As you can see, these validation rules do not really validate the inputs. Instead, they will process the values
and save them back to the attributes being validated.


### 处理空输入 <a name="handling-empty-inputs"></a>

When input data are submitted from HTML forms, you often need to assign some default values to the inputs
if they are empty. You can do so by using the [default](tutorial-core-validators.md#default) validator. For example,

```php
[
    // set "username" and "email" as null if they are empty
    [['username', 'email'], 'default'],

    // set "level" to be 1 if it is empty
    ['level', 'default', 'value' => 1],
]
```

By default, an input is considered empty if its value is an empty string, an empty array or a null.
You may customize the default empty detection logic by configuring the the [[yii\validators\Validator::isEmpty]] property
with a PHP callable. For example,

```php
[
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }],
]
```

> Note: Most validators do not handle empty inputs if their [[yii\base\Validator::skipOnEmpty]] property takes
  the default value true. They will simply be skipped during validation if their associated attributes receive empty
  inputs. Among the [core validators](tutorial-core-validators.md), only the `captcha`, `default`, `filter`,
  `required`, and `trim` validators will handle empty inputs.


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


## 创建验证器（Validators） <a name="creating-validators"></a>

Besides using the [core validators](tutorial-core-validators.md) included in the Yii releases, you may also
create your own validators. You may create inline validators or standalone validators.


### 行内验证器（Inline Validators） <a name="inline-validators"></a>

An inline validator is one defined in terms of a model method or an anonymous function. The signature of
the method/function is:

```php
/**
 * @param string $attribute the attribute currently being validated
 * @param array $params the additional name-value pairs given in the rule
 */
function ($attribute, $params)
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

    public function validateCountry($attribute, $params)
    {
        if (!in_array($this->$attribute, ['USA', 'Web'])) {
            $this->addError($attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```

> Note: By default, inline validators will not be applied if their associated attributes receive empty inputs
  or if they have already failed some validation rules. If you want to make sure a rule is always applied,
  you may configure the [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] and/or [[yii\validators\Validator::skipOnError|skipOnError]]
  properties to be false in the rule declarations. For example:
> ```php
[
    ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
]
```


### 独立验证器（Standalone Validators） <a name="standalone-validators"></a>

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


## 客户端验证器（Client-Side Validation） <a name="client-side-validation"></a>

Client-side validation based on JavaScript is desirable when end users provide inputs via HTML forms, because
it allows users to find out input errors faster and thus provides better user experience. You may use or implement
a validator that supports client-side validation *in addition to* server-side validation.

> Info: While client-side validation is desirable, it is not a must. It main purpose is to provider users better
  experience. Like input data coming from end users, you should never trust client-side validation. For this reason,
  you should always perform server-side validation by calling [[yii\base\Model::validate()]], like
  described in the previous subsections.


### 使用客户端验证 <a name="using-client-side-validation"></a>

Many [core validators](tutorial-core-validators.md) support client-side validation out-of-box. All you need to do
is just to use [[yii\widgets\ActiveForm]] to build your HTML forms. For example, `LoginForm` below declares two
rules: one uses the [required](tutorial-core-validators.md#required) core validator which is supported on both
client and server sides; the other uses the `validatePassword` inline validator which is only supported on the server
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
[[yii\widgets\ActiveForm::enableClientValidation]] property to be false. You may also turn off client-side
validation of individual input fields by configuring their [[yii\widgets\ActiveField::enableClientValidation]]
property to be false.


### 实现客户端验证 <a name="implementing-client-side-validation"></a>

To create a validator that supports client-side validation, you should implement the
[[yii\validators\Validator::clientValidateAttribute()]] method which returns a piece of JavaScript code
that performs the validation on the client side. Within the JavaScript code, you may use the following
predefined variables:

- `attribute`: the name of the attribute being validated.
- `value`: the value being validated.
- `messages`: an array used to hold the validation error messages for the attribute.

In the following example, we create a `StatusValidator` which validates if an input is a valid status input
against the existing status data. The validator supports both server side and client side validation.

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
        $message = json_encode($this->message);
        return <<<JS
if (!$.inArray(value, $statuses)) {
    messages.push($message);
}
JS;
    }
}
```

> Tip: The above code is given mainly to demonstrate how to support client-side validation. In practice,
  you may use the [in](tutorial-core-validators.md#in) core validator to achieve the same goal. You may
  write the validation rule like the following:
> ```php
[
    ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
]
```
