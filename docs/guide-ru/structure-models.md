<!--Models-->
Модели
======

<!--
Models are part of the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.
They are objects representing business data, rules and logic.
-->
Модели являются частью архитектуры [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) (Модель-Вид-Контроллер). Они представляют собой объекты бизнес данных, правил и логики.

<!--
You can create model classes by extending [[yii\base\Model]] or its child classes. The base class
[[yii\base\Model]] supports many useful features:
-->
Вы можете создавать классы моделей путём расширения класса [[yii\base\Model]] или его дочерних классов. Базовый класс [[yii\base\Model]] поддерживает много полезных функций:

<!--
* [Attributes](#attributes): represent the business data and can be accessed like normal object properties
  or array elements;
* [Attribute labels](#attribute-labels): specify the display labels for attributes;
* [Massive assignment](#massive-assignment): supports populating multiple attributes in a single step;
* [Validation rules](#validation-rules): ensures input data based on the declared validation rules;
* [Data Exporting](#data-exporting): allows model data to be exported in terms of arrays with customizable formats.
-->
* [Атрибуты](#attributes): представляют собой рабочие данные и могут быть доступны как обычные свойства объекта или элементы массыва;
* [Метки атрибутов](#attribute-labels): задают отображение атрибута;
* [Массовое присвоение](#massive-assignment): поддержка заполнения нескольких атрибутов в один шаг;
* [Правила проверки](#validation-rules): обеспечивают ввод данных на основе заявленных правил проверки;
* [Экспорт Данных](#data-exporting): разрешает данным модели быть экспортированными в массивы с настройкой форматов.

<!--
The `Model` class is also the base class for more advanced models, such as [Active Record](db-active-record.md).
Please refer to the relevant documentation for more details about these advanced models.
-->
Класс `Model` также является базовым классом для многих расширенных моделей, таких как [Active Record](db-active-record.md). Пожалуйста, обратитесь к соответствующей документации для более подробной информации об этих расширенных моделях.

<!--
> Info: You are not required to base your model classes on [[yii\base\Model]]. However, because there are many Yii
  components built to support [[yii\base\Model]], it is usually the preferable base class for a model.
-->
> Для справки: Вы не обязаны основывать свои классы моделей на [[yii\base\Model]]. Однако, поскольку в yii есть много компонентов, созданных для поддержки [[yii\base\Model]], обычно так делать предпочтительнее для базового класса модели.

## Атрибуты <span id="attributes"></span>
<!-- Attributes -->
<!--
Models represent business data in terms of *attributes*. Each attribute is like a publicly accessible property
of a model. The method [[yii\base\Model::attributes()]] specifies what attributes a model class has.
-->
Модели предоставляют рабочие данные в терминах *атрибутах*. Каждый атрибут представляет собой публично доступное свойство модели. Метод [[yii\base\Model::attributes()]] определяет какие атрибуты имеет класс модели.

<!--
You can access an attribute like accessing a normal object property:
-->
Вы можете получить доступ к атрибуту как к обычному свойству объекта:

```php
$model = new \app\models\ContactForm;

// "name" - это атрибут модели ContactForm
$model->name = 'example';
echo $model->name;
```

<!--
You can also access attributes like accessing array elements, thanks to the support for
[ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.arrayiterator.php)
by [[yii\base\Model]]:
-->
Также возможно получить доступ к атрибутам как к элементам массива, спасибо поддержке [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) и [ArrayIterator](http://php.net/manual/en/class.arrayiterator.php)
в [[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// доступ к атрибутам как к элементам массива
$model['name'] = 'example';
echo $model['name'];

// перебор атрибутов
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```


### Определение Атрибутов <span id="defining-attributes"></span>
<!-- Defining Attributes  -->
<!--
By default, if your model class extends directly from [[yii\base\Model]], all its *non-static public* member
variables are attributes. For example, the `ContactForm` model class below has four attributes: `name`, `email`,
`subject` and `body`. The `ContactForm` model is used to represent the input data received from an HTML form.
-->
По умолчанию, если ваш класс модели расширяется напрямую от [[yii\base\Model]], то все *не статичные публичные* переменные являются атрибутами. Например, у класса модели `ContactForm` , который находится ниже, четыре атрибута: `name`, `email`, `subject` и `body`. Модель `ContactForm` используется для представления входных данных, полученных из HTML формы.

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

<!--
You may override [[yii\base\Model::attributes()]] to define attributes in a different way. The method should
return the names of the attributes in a model. For example, [[yii\db\ActiveRecord]] does so by returning
the column names of the associated database table as its attribute names. Note that you may also need to
override the magic methods such as `__get()`, `__set()` so that the attributes can be accessed like
normal object properties.
-->
Вы можете переопределить метод [[yii\base\Model::attributes()]], чтобы определять атрибуты другим способом. Метод должен возвращать имена атрибутов в модели. Например [[yii\db\ActiveRecord]] делает так, возвращая имена столбцов из связанной таблицы базы данных в качестве имён атрибутов. Также может понадобиться переопределить магические методы, такие как `__get()`, `__set()` для того, что бы атрибуты могли быть доступны как обычные свойства объекта.


### Метки атрибутов <span id="attribute-labels"></span>
<!-- Attribute Labels -->
<!--
When displaying values or getting input for attributes, you often need to display some labels associated
with attributes. For example, given an attribute named `firstName`, you may want to display a label `First Name`
which is more user-friendly when displayed to end users in places such as form inputs and error messages.
-->
При отображении значений или при получении ввода значений атрибутов, часто требуется отобразить некоторые надписи, связанные с атрибутами. Например, если атрибут назван `firstName`, Вы можете отобразить его как `First Name`, что является более удобным для пользователя, в тех случаях, когда атрибут отображается конечным пользователям в таких местах, как форма входа и сообщения об ошибках.

<!--
You can get the label of an attribute by calling [[yii\base\Model::getAttributeLabel()]]. For example,
-->
Вы можете получить метку атрибута, вызвав [[yii\base\Model::getAttributeLabel()]]. Например,

```php
$model = new \app\models\ContactForm;

// отобразит "Name"
echo $model->getAttributeLabel('name');
```

<!--
By default, attribute labels are automatically generated from attribute names. The generation is done by
the method [[yii\base\Model::generateAttributeLabel()]]. It will turn camel-case variable names into
multiple words with the first letter in each word in upper case. For example, `username` becomes `Username`,
and `firstName` becomes `First Name`.
-->
По умолчанию, метки атрибутов автоматически генерируются из названия атрибута. Генерация выполняется методом [[yii\base\Model::generateAttributeLabel()]]. Он превращает первую букву каждого слова в верхний регистр, если имена переменных состоят из нескольких слов. Например, `username` станет `Username`, а `firstName` станет `First Name`.

<!--
If you do not want to use automatically generated labels, you may override [[yii\base\Model::attributeLabels()]]
to explicitly declare attribute labels. For example,
-->
Если Вы не хотите использовать автоматически сгенерированные метки, Вы можете переопределить метод [[yii\base\Model::attributeLabels()]] чтобы явно объявить метку атрибута. Например,

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

<!--
For applications supporting multiple languages, you may want to translate attribute labels. This can be done
in the [[yii\base\Model::attributeLabels()|attributeLabels()]] method as well, like the following:
-->
Для приложений поддерживающих мультиязычность, Вы можете перевести метки атрибутов. Это можно сделать в методе [[yii\base\Model::attributeLabels()|attributeLabels()]] как показано ниже:

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

<!--
You may even conditionally define attribute labels. For example, based on the [scenario](#scenarios) the model
is being used in, you may return different labels for the same attribute.
-->
Можно даже условно определять метки атрибутов. Например, на основе [сценариев](#scenarios) и использованной в нём модели , Вы можете возвращать различные метки для одного и того же атрибута.

<!--
> Info: Strictly speaking, attribute labels are part of [views](structure-views.md). But declaring labels
  in models is often very convenient and can result in very clean and reusable code.
-->
> Для справки: Строго говоря, метки атрибутов являются частью [видов](structure-views.md). Но объявление меток в моделях часто очень удобно и приводит к чистоте кода и повторному его использованию.

## Сценарии <span id="scenarios"></span>
<!-- Scenarios  -->
<!--
A model may be used in different *scenarios*. For example, a `User` model may be used to collect user login inputs,
but it may also be used for the user registration purpose. In different scenarios, a model may use different
business rules and logic. For example, the `email` attribute may be required during user registration,
but not so during user login.
-->
Модель может быть использованна в различных *сценариях*. Например, модель `User` может быть использованна для коллекции входных логинов пользователей, а также может быть использованна для цели регистрации пользователей.  	
В различных сценариях, модель может использовать различные бизнес-правила и логику. Например, атрибут `email` может потребоваться во время регистрации пользователя, но не во время входа пользователя в систему.

<!--
A model uses the [[yii\base\Model::scenario]] property to keep track of the scenario it is being used in.
By default, a model supports only a single scenario named `default`. The following code shows two ways of
setting the scenario of a model:
-->
Модель использует свойство [[yii\base\Model::scenario]], чтобы отслеживать сценарий, в котором она используется. По умолчанию, модель поддерживает только один сценарий с именем `default`. В следующем коде показано два способа установки сценария модели:

```php
// сценарий задается как свойство
$model = new User;
$model->scenario = 'login';

// сценарий задается через конфигурацию
$model = new User(['scenario' => 'login']);
```

<!--
By default, the scenarios supported by a model are determined by the [validation rules](#validation-rules) declared
in the model. However, you can customize this behavior by overriding the [[yii\base\Model::scenarios()]] method,
like the following:
-->
По умолчанию сценарии, поддерживаемые моделью, определяются [правилами валидации](#validation-rules) объявленными
в модели. Однако, Вы можете изменить это поведение путем переопределения метода [[yii\base\Model::scenarios()]] как показано ниже:

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

<!--
> Info: In the above and following examples, the model classes are extending from [[yii\db\ActiveRecord]]
  because the usage of multiple scenarios usually happens to [Active Record](db-active-record.md) classes.
-->
> Для справки: В приведенном выше и следующих примерах, классы моделей расширяются от [[yii\db\ActiveRecord]] потому, что использование нескольких сценариев обычно происходит от классов [Active Record](db-active-record.md).

<!--
The `scenarios()` method returns an array whose keys are the scenario names and values the corresponding
*active attributes*. An active attribute can be [massively assigned](#massive-assignment) and is subject
to [validation](#validation-rules). In the above example, the `username` and `password` attributes are active
in the `login` scenario; while in the `register` scenario, `email` is also active besides `username` and `password`.
-->
Метод `scenarios()` возвращает массив, ключами которого являются имена сценариев, а значения - соответствующие *активные атрибуты*. Активные атрибуты могут быть [массово присвоены](#massive-assignment) и подлежат [валидации](#validation-rules). В приведенном выше примере, атрибуты `username` и `password` это активные атрибуты сценария `login`, а в сценарии `register` так же активным атрибутом является `email` вместе с `username` и `password`.

<!--
The default implementation of `scenarios()` will return all scenarios found in the validation rule declaration
method [[yii\base\Model::rules()]]. When overriding `scenarios()`, if you want to introduce new scenarios
in addition to the default ones, you may write code like the following:
-->
По умолчанию реализация `scenarios()` вернёт все найденные сценарии в правилах валидации задекларированных в методе [[yii\base\Model::rules()]]. При переопределении метода `scenarios()`, если Вы хотите ввести новые сценарии помимо стандартных, Вы можете написать код на основе следующего примера:

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

<!--
The scenario feature is primarily used by [validation](#validation-rules) and [massive attribute assignment](#massive-assignment).
You can, however, use it for other purposes. For example, you may declare [attribute labels](#attribute-labels)
differently based on the current scenario.
-->
Возможности сценариев в основном используются [валидацией](#validation-rules) и [массовым присвоением атрибутов](#massive-assignment). Однако, Вы можете использовать их и для других целей. Например, Вы можете различным образом объявлять [метки атрибутов](#attribute-labels) на основе текущего сценария.


##  Правила валидации <span id="validation-rules"></span>
<!-- Validation Rules -->
<!--
When the data for a model is received from end users, it should be validated to make sure it satisfies
certain rules (called *validation rules*, also known as *business rules*). For example, given a `ContactForm` model,
you may want to make sure all attributes are not empty and the `email` attribute contains a valid email address.
If the values for some attributes do not satisfy the corresponding business rules, appropriate error messages
should be displayed to help the user to fix the errors.
-->
Когда данные модели, получены от конечных пользователей, они должны быть проверены, для того чтобы убедиться, что данные удовлетворяют определенным правилам (так называемым *правилам валидации* также известными как *бизнес-правила*). Например, дана модель `ContactForm`, возможно Вы захотите убедиться, что все атрибуты являются не пустыми значениями, а атрибут `email` содержит допустимый адрес электронной почты. Если значения нескольких атрибутов не удовлетворяют соответствующим бизнес-правилам, то должны быть показаны соответствующие сообщения об ошибках, чтобы помочь конечному пользователю исправить допущенные ошибки.

<!--
You may call [[yii\base\Model::validate()]] to validate the received data. The method will use
the validation rules declared in [[yii\base\Model::rules()]] to validate every relevant attribute. If no error
is found, it will return true. Otherwise, it will keep the errors in the [[yii\base\Model::errors]] property
and return false. For example,
-->
Вы можете вызвать [[yii\base\Model::validate()]] для проверки полученных данных. Данный метод будет использовать
правила валидации определённые в [[yii\base\Model::rules()]] для проверки каждого соответствующего атрибута. Если ошибок не найдено, то возвращается True, в противном случае возвращается false, а ошибки содержит свойство [[yii\base\Model::errors]]. Например,

```php
$model = new \app\models\ContactForm;

// модель заполнения атрибутов данными, вводимыми пользователем
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // все данные верны
} else {
    // проверка не удалась:  $errors - это массив содержащий сообщения об ошибках
    $errors = $model->errors;
}
```

<!--
To declare validation rules associated with a model, override the [[yii\base\Model::rules()]] method by returning
the rules that the model attributes should satisfy. The following example shows the validation rules declared
for the `ContactForm` model:
-->
Объявляем правила валидации связанные с моделью, переопределяем метод [[yii\base\Model::rules()]] возврата правил атрибутов модели которые следует удовлетворить. В следующем примере показаны правила проверки объявленные в модели `ContactForm`:

```php
public function rules()
{
    return [
        // name, email, subject и body атрибуты обязательны
        [['name', 'email', 'subject', 'body'], 'required'],

        // атрибут email должен быть правильным email адресом
        ['email', 'email'],
    ];
}
```

<!--
A rule can be used to validate one or multiple attributes, and an attribute may be validated by one or multiple rules.
Please refer to the [Validating Input](input-validation.md) section for more details on how to declare
validation rules.
-->
Правило может использоваться для проверки одного или нескольких атрибутов, также и атрибут может быть проверен одним или несколькими правилами. Пожалуйста, обратитесь к разделу [Проверка входных значений](input-validation.md) для более подробной информации о том, как объявлять правила проверки.

<!--
Sometimes, you may want a rule to be applied only in certain [scenarios](#scenarios). To do so, you can
specify the `on` property of a rule, like the following:
-->
Иногда необходимо, чтобы правила применялись только в определенных [сценариях](#scenarios). Чтобы это сделать необходимо указать свойство `on` в правилах, следующим образом:

```php
public function rules()
{
    return [
        // username, email и password требуются в сценарии "register"
        [['username', 'email', 'password'], 'required', 'on' => 'register'],

        // username и password требуются в сценарии "login"
        [['username', 'password'], 'required', 'on' => 'login'],
    ];
}
```

<!--
If you do not specify the `on` property, the rule would be applied in all scenarios. A rule is called
an *active rule* if it can be applied in the current [[yii\base\Model::scenario|scenario]].
-->
Если не указать свойство `on`, то правило применяется во всех сценариях. Правило называется *активным правилом* если оно может быть применено в текущем сценарии [[yii\base\Model::scenario|scenario]].

<!--
An attribute will be validated if and only if it is an active attribute declared in `scenarios()` and
is associated with one or multiple active rules declared in `rules()`.
-->
Атрибут будет проверяться тогда и только тогда если он является активным атрибутом объявленным в `scenarios()` и
связаным с одним или несколькими активными правилами, объявленными в `rules()`.

## Массовое Присвоение <span id="massive-assignment"></span>
<!--Massive Assignment-->

<!--
Massive assignment is a convenient way of populating a model with user inputs using a single line of code.
It populates the attributes of a model by assigning the input data directly to the [[yii\base\Model::$attributes]]
property. The following two pieces of code are equivalent, both trying to assign the form data submitted by end users
to the attributes of the `ContactForm` model. Clearly, the former, which uses massive assignment, is much cleaner
and less error prone than the latter:
-->
Массовое присвоение - это удобный способ заполнения модели данными вводимыми пользователем с помощью одной строки кода. Он заполняет атрибуты модели путем присвоения входных данных непосредственно свойству [[yii\base\Model::$attributes]]. Следующие два куска кода эквивалентны, они оба пытаются присвоить данные из формы представленные конечными пользователями атрибутам модели `ContactForm`. Ясно, что первый код гораздо чище и менее подвержен ошибкам, чем второй:

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


### Безопасные Атрибуты <span id="safe-attributes"></span>
<!--Safe Attributes-->

<!--
Massive assignment only applies to the so-called *safe attributes* which are the attributes listed in
[[yii\base\Model::scenarios()]] for the current [[yii\base\Model::scenario|scenario]] of a model.
For example, if the `User` model has the following scenario declaration, then when the current scenario
is `login`, only the `username` and `password` can be massively assigned. Any other attributes will
be kept untouched.
-->
Массовое присвоение применяется только к так называемым *безопасным атрибутам*, которые являются атрибутами, перечисленными в [[yii\base\Model::scenarios()]] в текущем сценарии [[yii\base\Model::scenario|scenario]] модели. Например, если модель `User` имеет следующий заданный сценарий, в данном случае это сценарий `login`, то только `username` и `password` могут быть массово присвоены. Любые другие атрибуты остануться нетронутыми.

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password'],
        'register' => ['username', 'email', 'password'],
    ];
}
```

<!--
> Info: The reason that massive assignment only applies to safe attributes is because you want to
  control which attributes can be modified by end user data. For example, if the `User` model
  has a `permission` attribute which determines the permission assigned to the user, you would
  like this attribute to be modifiable by administrators through a backend interface only.
-->
> Для справки: Причиной того, что массовое присвоение атрибутов применяется только к безопасным атрибутам, является то, что необходимо контролировать какие атрибуты могут быть изменены конечными пользователями. Например, если модель `User` имеет атрибут `permission`, который определяет разрешения, назначенные пользователю, то необходимо быть уверенным, что данный атрибут может быть изменён только администраторами через бэкэнд-интерфейс.

<!--
Because the default implementation of [[yii\base\Model::scenarios()]] will return all scenarios and attributes
found in [[yii\base\Model::rules()]], if you do not override this method, it means an attribute is safe as long
as it appears in one of the active validation rules.
-->
По умолчанию реализация [[yii\base\Model::scenarios()]] будет возвращать все сценарии и атрибуты найденные в [[yii\base\Model::rules()]], если не переопределить этот метод, это будет означать, что атрибуты являются безопасными до тех пор пока они не появятся в одном из активных правил проверки.

<!--
For this reason, a special validator aliased `safe` is provided so that you can declare an attribute
to be safe without actually validating it. For example, the following rules declare that both `title`
and `description` are safe attributes.
-->
По этой причине существует специальный валидатор с псевдонимом `safe`, он предоставляет возможность объявить атрибут безопасным без фактической его проверки. Например, следующие правила определяют, что оба атрибута `title` и `description` являются безопасными атрибутами.

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### Небезопасные атрибуты <span id="unsafe-attributes"></span>
<!--Unsafe Attributes-->
<!--
As described above, the [[yii\base\Model::scenarios()]] method serves for two purposes: determining which attributes
should be validated, and determining which attributes are safe. In some rare cases, you may want to validate
an attribute but do not want to mark it safe. You can do so by prefixing an exclamation mark `!` to the attribute
name when declaring it in `scenarios()`, like the `secret` attribute in the following:
-->
Как сказано выше, метод [[yii\base\Model::scenarios()]] служит двум целям: определения, какие атрибуты должны быть проверены, и определения, какие атрибуты являются безопасными (т.е. не требуют проверки). В некоторых случаях необходимо проверить атрибут не объявляя его безопасным. Вы можете сделать это с помощью префикса восклицательный знак `!` в имени атрибута при объявлении его в `scenarios()` как атрибут `secret` в следующем примере:

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password', '!secret'],
    ];
}
```

<!--
When the model is in the `login` scenario, all three attributes will be validated. However, only the `username`
and `password` attributes can be massively assigned. To assign an input value to the `secret` attribute, you
have to do it explicitly as follows,
-->
Когда модель будет присутствовать в сценарии `login`, то все три эти атрибута будут проверены. Однако, только атрибуты `username` и `password` могут быть массово присвоены. Назначить входное значение атрибуту `secret` нужно явно следующим образом,

```php
$model->secret = $secret;
```


## Экспорт Данных <span id="data-exporting"></span>
<!--Data Exporting-->

<!--
Models often need to be exported in different formats. For example, you may want to convert a collection of
models into JSON or Excel format. The exporting process can be broken down into two independent steps.
In the first step, models are converted into arrays; in the second step, the arrays are converted into
target formats. You may just focus on the first step, because the second step can be achieved by generic
data formatters, such as [[yii\web\JsonResponseFormatter]].
-->
Часто нужно экспортировать модели в различные форматы. Например, может потребоваться преобразовать коллекцию моделей в JSON или Excel формат. Процесс экспорта может быть разбит на два самостоятельных шага. На первом этапе модели преобразуются в массивы; на втором этапе массивы преобразуются в целевые форматы. Вы можете сосредоточиться только на первом шаге потому, что второй шаг может быть достигнут путем универсального инструмента форматирования данных, такого как [[yii\web\JsonResponseFormatter]].

<!--
The simplest way of converting a model into an array is to use the [[yii\base\Model::$attributes]] property.
For example,
-->
Самый простой способ преобразования модели в массив - использовать свойство [[yii\base\Model::$attributes]].
Например,

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

<!--
By default, the [[yii\base\Model::$attributes]] property will return the values of *all* attributes
declared in [[yii\base\Model::attributes()]].
-->
По умолчанию, свойство [[yii\base\Model::$attributes]] возвращает значения *всех* атрибутов объявленных в [[yii\base\Model::attributes()]].

<!--
A more flexible and powerful way of converting a model into an array is to use the [[yii\base\Model::toArray()]]
method. Its default behavior is the same as that of [[yii\base\Model::$attributes]]. However, it allows you
to choose which data items, called *fields*, to be put in the resulting array and how they should be formatted.
In fact, it is the default way of exporting models in RESTful Web service development, as described in
the [Response Formatting](rest-response-formatting.md).
-->
Более гибкий и мощный способ конвертирования модели в массив - использовать метод [[yii\base\Model::toArray()]]. Его поведение по умолчанию такое же как и у [[yii\base\Model::$attributes]]. Тем не менее, он позволяет выбрать, какие элементы данных, называемые *полями*, поставить в результирующий массив и как они должны быть отформатированы. На самом деле, этот способ экспорта моделей по умолчанию применяется при разработке в RESTful Web service, как описано в [Response Formatting](rest-response-formatting.md).

### Поля <span id="fields"></span>
<!--Fields-->

<!--
A field is simply a named element in the array that is obtained by calling the [[yii\base\Model::toArray()]] method
of a model.
-->
Поле - это просто именованный элемент в массиве, который может быть получен вызовом метода [[yii\base\Model::toArray()]] модели.

<!--
By default, field names are equivalent to attribute names. However, you can change this behavior by overriding
the [[yii\base\Model::fields()|fields()]] and/or [[yii\base\Model::extraFields()|extraFields()]] methods. Both methods
should return a list of field definitions. The fields defined by `fields()` are default fields, meaning that
`toArray()` will return these fields by default. The `extraFields()` method defines additionally available fields
which can also be returned by `toArray()` as long as you specify them via the `$expand` parameter. For example,
the following code will return all fields defined in `fields()` and the `prettyName` and `fullAddress` fields
if they are defined in `extraFields()`.
-->
По умолчанию имена полей эквивалентны именам атрибутов. Однако, это поведение можно изменить, переопределив методы
[[yii\base\Model::fields()|fields()]] и/или [[yii\base\Model::extraFields()|extraFields()]]. Оба метода должны возвращать список определенных полей. Поля определённые `fields()` являются полями по умолчанию, это означает, что `toArray()` будет возвращать эти поля по умолчанию. Метод `extraFields()` определяет дополнительно доступные поля, которые также могут быть возвращены `toArray()` так много, как Вы укажите их через параметр `$expand`. Например, следующий код будет возвращать все поля определённые в `fields()`, а также поля `prettyName` и `fullAddress`, если они определены в `extraFields()`.

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

<!--
You can override `fields()` to add, remove, rename or redefine fields. The return value of `fields()`
should be an array. The array keys are the field names, and the array values are the corresponding
field definitions which can be either property/attribute names or anonymous functions returning the
corresponding field values. In the special case when a field name is the same as its defining attribute
name, you can omit the array key. For example,
-->
Вы можете переопределить `fields()` чтобы добавить, удалить, переименовать или переопределить поля. Возвращаемым значением `fields()` должен быть массив. Ключами массива являются имена полей, а значениями - соответствующие определения полей, которые могут быть либо именами свойств/атрибутов, либо анонимными функциями, возвращающими соответствующие значения полей. В частном случае, когда имя поля совпадает с именем его атрибута, возможно опустить ключ массива. Например,

<!--
// explicitly list every field, best used when you want to make sure the changes
// in your DB table or model attributes do not cause your field changes (to keep API backward compatibility).

        // field name is "email", the corresponding attribute name is "email_address"
        // field name is "name", its value is defined by a PHP callback
        
// filter out some fields, best used when you want to inherit the parent implementation
// and blacklist some sensitive fields.
    // remove fields that contain sensitive information
-->

```php
// использовать явное перечисление всех полей, лучше всего тогда, когда вы хотите убедиться,
// что изменения в вашей таблице базы данных или атрибуте модели не вызывают изменение вашего поля
// (для поддержания обратной совместимости API интерфейса).

public function fields()
{
    return [
        // здесь имя поля совпадает с именем атрибута
        'id',

        // здесь имя поля - "email", соответствующее ему имя атрибута - "email_address"
        'email' => 'email_address',

        // здесь имя поля - "name", а значение определяется обратным вызовом PHP
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// использовать фильтрование нескольких полей, лучше тогда, когда вы хотите наследовать
// родительскую реализацию и черный список некоторых "чувствительных" полей.

public function fields()
{
    $fields = parent::fields();

    // удаляем поля, содержащие конфиденциальную информацию
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

<!--
> Warning: Because by default all attributes of a model will be included in the exported array, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.
-->
> Внимание: по умолчанию все атрибуты модели будут включены в экспортируемый массив, вы должны проверить ваши данные и > убедиться, что они не содержат конфиденциальной информации. Если такая информация присутствует, вы должны
> переопределить `fields()` и отфильтровать поля. В приведенном выше примере мы выбираем и отфильтровываем `auth_key`,
> `password_hash` и `password_reset_token`.

## Лучшие приёмы разработки моделей <span id="best-practices"></span>
<!--Best Practices-->

<!--
Models are the central places to represent business data, rules and logic. They often need to be reused
in different places. In a well-designed application, models are usually much fatter than
[controllers](structure-controllers.md).
-->
Модели являются центральным местом представления бизнес-данных, правил и логики. Они часто повторно используются в разных местах. В хорошо спроектированном приложении, модели, как правило, намного больше, чем [контроллеры](structure-controllers.md).

<!--
In summary, models
-->
В целом, модели

<!--
* may contain attributes to represent business data;
* may contain validation rules to ensure the data validity and integrity;
* may contain methods implementing business logic;
* should NOT directly access request, session, or any other environmental data. These data should be injected
  by [controllers](structure-controllers.md) into models;
* should avoid embedding HTML or other presentational code - this is better done in [views](structure-views.md);
* avoid having too many [scenarios](#scenarios) in a single model.
-->
* могут содержать атрибуты для представления бизнес-данных;
* могут содержать правила проверки для обеспечения целостности и достоверности данных;
* могут содержать методы с реализацией бизнес-логики;
* не следует напрямую задавать запрос на доступ, либо сессии, либо любые другие данные об окружающей среде. Эти данные должны быть введены [контроллерами](structure-controllers.md) в модели;
* следует избегать встраивания HTML или другого отображаемого кода - это лучше делать в [видах](structure-views.md);
* избегайте слишком большого количества [сценариев](#scenarios) в одной модели.

<!--
You may usually consider the last recommendation above when you are developing large complex systems.
In these systems, models could be very fat because they are used in many places and may thus contain many sets
of rules and business logic. This often ends up in a nightmare in maintaining the model code
because a single touch of the code could affect several different places. To make the model code more maintainable,
you may take the following strategy:
-->
Рекомендации выше обычно учитываются при разработке больших сложных систем. В таких системах, модели могут быть очень большими, в связи стем, что они используются во многих местах и поэтому могут содержать множество наборов правил и бизнес-логики. Это часто заканчивается кошмаром при поддержании кода модели, поскольку одним касанием кода можно повлиять на несколько разных мест. Чтобы сделать код модели более легким в обслуживании, Вы можете предпринять следующую стратегию:

<!--
* Define a set of base model classes that are shared by different [applications](structure-applications.md) or
  [modules](structure-modules.md). These model classes should contain minimal sets of rules and logic that
  are common among all their usages.
* In each [application](structure-applications.md) or [module](structure-modules.md) that uses a model,
  define a concrete model class by extending from the corresponding base model class. The concrete model classes
  should contain rules and logic that are specific for that application or module.
-->
* Определить набор базовых классов моделей, которые являются общими для разных [приложений](structure-applications.md) или [модулей](structure-modules.md). Эти классы моделей должны содержать минимальный набор правил и логики, которые являются общими среди всех используемых приложений или модулей.
* В каждом [приложении](structure-applications.md) или [модуле](structure-modules.md) в котором используется модель, определить конкретный класс модели (или классы моделей), отходящий от соответствующего базового класса модели. Конкретный класс модели должен содержать правила и логику, которые являются специфическими для данного приложения или модуля.

<!--
For example, in the [Advanced Project Template](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), you may define a base model
class `common\models\Post`. Then for the front end application, you define and use a concrete model class
`frontend\models\Post` which extends from `common\models\Post`. And similarly for the back end application,
you define `backend\models\Post`. With this strategy, you will be sure that the code in `frontend\models\Post`
is only specific to the front end application, and if you make any change to it, you do not need to worry if
the change may break the back end application.
-->
Например, в [Дополнительном Шаблоне Проекта](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), Вы можете определить базовым классом модели `common\models\Post`. Тогда для frontend приложения, Вы определяете и используете конкретный класс модели `frontend\models\Post`, который расширяется от `common\models\Post`. И аналогичным образом для backend приложения, Вы определяете `backend\models\Post`. С помощью такой стратегии, можно быть уверенным, что код в `frontend\models\Post` используется только для конкретного frontend приложения, и если делаются любые изменения в нём, то не нужно беспокоиться, что изменения могут сломать backend приложение.
