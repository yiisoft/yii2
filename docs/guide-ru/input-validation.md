Проверка входящих данных
================

Как правило, вы никогда не должны доверять данным, полученные от пользователей и всегда проверять их прежде чем работать с ними и добавлять в базу данных.

Учитывая [модель](structure-models.md) данных которые должен заполнить пользователь, можно проверить эти данные на валидность воспользовавшись методом `[[yii\base\Model::validate()]]`. Метод возвращает логическое значение с результатом валидации ложь/истина. Если данные не валидны, ошибку можно получить воспользовавшись методом  `[[yii\base\Model::errors]]`. Рассмотрим пример,

```php
$model = new \app\models\ContactForm;

// модель заполненая пользовательским данными
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // все данные корректны
} else {
    // данные не корректны: $errors - массив содержащий сообщения об ощибках
    $errors = $model->errors;
}
```


## Правила проверки <a name="declaring-rules"></a>

Для того, чтобы  `validate()` действительно работала, нужно объявить правила проверки атрибутов.
Правила для проверки нужно указать в `[[yii\base\Model::rules()]]` методе. В следующем примере показано, как
правила для проверки `ContactForm` модели, нужно объявлять:

```php
public function rules()
{
    return [
        // атрибут required указывает, что name, email, subject, body обязательны для заполнения
        [['name', 'email', 'subject', 'body'], 'required'],

        // атрибут email указывает, что в переменной email должен быть корректный адрес электронной почты
        ['email', 'email'],
    ];
}
```

Метод должен `[[yii\base\Model::rules()|rules()]]` возвращать массив правил, каждый из которых является массивом в следующем формате:

```php
[
    // обязательный, указывает, какие атрибуты должны быть проверены по этому правилу.
    // Для одного атрибута, вы можете использовать имя атрибута не создавая массив
    ['attribute1', 'attribute2', ...],

    // обязательный, указывает тип правила.
    // Это может быть имя класса, псевдоним валидатора, или метод для проверки
    'validator',

    // необязательный, указывает, в каком случае(ях) это правило должно применяться
    // если не указан, это означает, что правило применяется ко всем сценариям
    // Вы также можете настроить "except" этот вариан применяет правило ко всем
    // сценариям кроме перечисленных
    'on' => ['scenario1', 'scenario2', ...],

    // необязательный, задает дополнительные конфигурации для объекта validator
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

Для каждого правила необходимо указать, по крайней мере, какие атрибуты относится к этому правило и тип правила.
Вы можете указать тип правила в одном из следующих форматов:

* Псевдонимы основного валидатора, например `required`, `in`, `date` и другие. Пожалуйста, обратитесь к списку
  [Основных валидаторов](tutorial-core-validators.md) за более подробной информацией.
* Название методов проверки в моделе класса, или анонимную функцию. Пожалуйста, обратитесь к списку
  [Встроенных валидаторов](#inline-validators) за более подробной информацией.
* Полное имя класса валидатора. Пожалуйста, обратитесь к списку [Автономных валидаторов](#standalone-validators)
  за более подробной информацией.

Правило может использоваться для проверки одного или нескольких атрибутов. Атрибут может быть проверен одним или несколькими правилами.
Правило может быть применено в определенных [сценариях](structure-models.md#scenarios) только указав свойство `on`.
Если вы не укажите свойство `on`, это означает, что правило будет применяться ко всем сценариям.

Когда вызывается  метод `validate()` для проверки, он выполняет следующие действия:

1. Determine which attributes should be validated by getting the attribute list from [[yii\base\Model::scenarios()]]
   using the current [[yii\base\Model::scenario|scenario]]. These attributes are called *active attributes*.
2. Determine which validation rules should be used by getting the rule list from [[yii\base\Model::rules()]]
   using the current [[yii\base\Model::scenario|scenario]]. These rules are called *active rules*.
3. Use each active rule to validate each active attribute which is associated with the rule.
   The validation rules are evaluated in the order they are listed.
   
According to the above validation steps, an attribute will be validated if and only if it is
an active attribute declared in `scenarios()` and is associated with one or multiple active rules
declared in `rules()`.


### Настройка сообщений об ошибках <a name="customizing-error-messages"></a>

Большинство валидаторов имеют сообщения об ошибках по умолчанию, которые будут добавлены к модели когда его атрибуты не проходят проверку.
Например, `[[yii\validators\RequiredValidator|required]]` валидатор добавил к модели сообщение об ошибке "Имя пользователя не может быть пустым." когда атрибут `username` не удовлетворил правила этого валидатора.

Вы можете настроить сообщение об ошибке для каждого правила, указав свойство `message` при объявлении правила, следующим образом:

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Please choose a username.'],
    ];
}
```

Некоторые валидаторы могут поддерживать дополнительные сообщения об ошибках, чтобы более точно описать причину ошибки.
Например, `[[yii\validators\NumberValidator|number]]` валидатор поддерживает
`[[yii\validators\NumberValidator::tooBig|tooBig]]` и `[[yii\validators\NumberValidator::tooSmall|tooSmall]]`
для описания ошибки валидации, когда проверяемое значение является слишком большим и слишком маленьким, соответственно.
Вы можете настроить эти сообщения об ошибках, как в настройках валидаторов, так и непосредственно в правила проверки.


### События валидации <a name="validation-events"></a>

Когда вызывается метод `[[yii\base\Model::validate()]]` он инициализирует вызов двух методов,
которые можно переопределить, чтобы настроить процесс проверки:

* `[[yii\base\Model::beforeValidate()]]`: выполнение по умолчанию вызовет `[[yii\base\Model::EVENT_BEFORE_VALIDATE]]`
  событие. Вы можете переопределить этот метод, или обрабатывать это событие, чтобы сделать некоторую предобработку данных (например, форматирование входных данных), метод вызывается до начала проверки. Этот метод должен возвращать логическое значение, указывающее, следует ли продолжать проверку или нет.
* `[[yii\base\Model::afterValidate()]]`: выполнение по умолчанию вызовет  `[[yii\base\Model::EVENT_AFTER_VALIDATE]]`
  событие. Вы можете либо переопределить этот метод или обрабатывать на это событие, чтобы сделать некоторую  постобработку данных(Например, отформатировать данные удобным для обработки образом), метод вызывает после проверки.

### Условные валидации <a name="conditional-validation"></a>

Для проверки атрибутов только при выполнении определенных условий, например если один атрибут зависит от значения другого атрибута можно использовать `[[yii\validators\Validator::when|when]]` свойство, чтобы определить такие условия. Например:

```php
[
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }],
]
```

Это свойство `[[yii\validators\Validator::when|when]]` принимает PHP callable функциию с следующим описанием:

```php
/**
 * @param Model $model модель используемая для проверки
 * @param string $attribute атрибут для проверки
 * @return boolean следует ли применять правило
 */
function ($model, $attribute)
```

Если вам нужна поддержка условной проверки на стороне клиента, вы должны настроить свойство метода
`[[yii\validators\Validator::whenClient|whenClient]]` которое принимает строку, представляющую JavaScript 
функцию, возвращаемое значение определяет, следует ли применять правило или нет. Например:

```php
[
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').val() == 'USA';
    }"],
]
```


### Фильтрация данных <a name="data-filtering"></a>

Пользователь частво вводит данные которые нужно предварительно отфильтровать или предварительно обработать.
Например, вы хотите обрезать пробелы вокруг `username`. Вы можете использовать правила проверки для 
достижения этой цели.

В следующих примерах показано, как обрезать пробелы в входных данных и превратить пустые входные данные в NULL
с помощью [trim](tutorial-core-validators.md#trim) и указать значения по умолчанию с помощью
[default](tutorial-core-validators.md#default) основного валидатора:

```php
[
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
]
```

Вы также можете использовать более сложные фильтрации данных с помощью анонимной функции
 подробнее об этом [filter](tutorial-core-validators.md#filter).

Как видите, эти правила проверки на самом деле не проверяет входные данные. Вместо этого,
они будут обрабатывают значения и обратно возвращать результат работы. Фильтры по сути выполняют предобработку входящих данных.


### Handling Empty Inputs <a name="handling-empty-inputs"></a>

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

> Note: Not all validators support this type of validation. An example is the [unique](tutorial-core-validators.md#unique)
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

After validation, you can check if the validation succeeded or not by calling the
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
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


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
            $this->addError($model, $attribute, 'The country must be either "USA" or "Web".');
        }
    }
}
```

If you want your validator to support validating a value without a model, you should also override
[[yii\validators\Validator::validate()]]. You may also override [[yii\validators\Validator::validateValue()]]
instead of `validateAttribute()` and `validate()` because by default the latter two methods are implemented
by calling `validateValue()`.


## Client-Side Validation <a name="client-side-validation"></a>

Client-side validation based on JavaScript is desirable when end users provide inputs via HTML forms, because
it allows users to find out input errors faster and thus provides a better user experience. You may use or implement
a validator that supports client-side validation *in addition to* server-side validation.

> Info: While client-side validation is desirable, it is not a must. Its main purpose is to provide users with a better
  experience. Similar to input data coming from end users, you should never trust client-side validation. For this reason,
  you should always perform server-side validation by calling [[yii\base\Model::validate()]], as
  described in the previous subsections.


### Using Client-Side Validation <a name="using-client-side-validation"></a>

Many [core validators](tutorial-core-validators.md) support client-side validation out-of-the-box. All you need to do
is just use [[yii\widgets\ActiveForm]] to build your HTML forms. For example, `LoginForm` below declares two
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


### Implementing Client-Side Validation <a name="implementing-client-side-validation"></a>

To create a validator that supports client-side validation, you should implement the
[[yii\validators\Validator::clientValidateAttribute()]] method which returns a piece of JavaScript code
that performs the validation on the client side. Within the JavaScript code, you may use the following
predefined variables:

- `attribute`: the name of the attribute being validated.
- `value`: the value being validated.
- `messages`: an array used to hold the validation error messages for the attribute.
- `deferred`: an array which deferred objects can be pushed into (explained in the next subsection).

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
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
if (!$.inArray(value, $statuses)) {
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

### Deferred Validation <a name="deferred-validation"></a>

If you need to perform asynchronous client-side validation, you can create [Deferred objects](http://api.jquery.com/category/deferred-object/).
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
is hit. The following example shows how to validate the dimensions of an uploaded image file on the client side.

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


### AJAX Validation <a name="ajax-validation"></a>

Some validations can only be done on the server side, because only the server has the necessary information.
For example, to validate if a username is unique or not, it is necessary to check the user table on the server side.
You can use AJAX-based validation in this case. It will trigger an AJAX request in the background to validate the
input while keeping the same user experience as the regular client-side validation.

To enable AJAX validation for the whole form, you have to set the
[[yii\widgets\ActiveForm::enableAjaxValidation]] property to be `true` and specify `id` to be a unique form identifier:

```php
<?php $form = yii\widgets\ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]); ?>
```

You may also turn AJAX validation on or off for individual input fields by configuring their
[[yii\widgets\ActiveField::enableAjaxValidation]] property.

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
