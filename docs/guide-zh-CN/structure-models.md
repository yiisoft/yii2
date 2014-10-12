模型
Models
======

模型是 [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) 模式中的一部分，
是代表业务数据、规则和逻辑的对象。
Models are part of the [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) architecture.
They are objects representing business data, rules and logic.

可通过继承 [[yii\base\Model]] 或它的子类定义模型类，基类[[yii\base\Model]]支持许多实用的特性：
You can create model classes by extending [[yii\base\Model]] or its child classes. The base class
[[yii\base\Model]] supports many useful features:

* [属性](#attributes): 代表可像普通类属性或数组一样被访问的业务数据;
* [属性标签](#attribute-labels): 指定属性显示出来的标签;
* [块赋值](#massive-assignment): 支持一步给许多属性赋值;
* [验证规则](#validation-rules): 确保输入数据符合所申明的验证规则;
* [数据导出](#data-exporting): 允许模型数据导出为自定义格式的数组。
* [Attributes](#attributes): represent the business data and can be accessed like normal object properties
  or array elements;
* [Attribute labels](#attribute-labels): specify the display labels for attributes;
* [Massive assignment](#massive-assignment): supports populating multiple attributes in a single step;
* [Validation rules](#validation-rules): ensures input data based on the declared validation rules;
* [Data Exporting](#data-exporting): allows model data to be exported in terms of arrays with customizable formats.

`Model` 类也是更多高级模型如[Active Record 活动记录](db-active-record.md)的基类，
更多关于这些高级模型的详情请参考相关手册。
The `Model` class is also the base class for more advanced models, such as [Active Record](db-active-record.md).
Please refer to the relevant documentation for more details about these advanced models.

> 补充：模型并不强制一定要继承[[yii\base\Model]]，但是由于很多组件支持[[yii\base\Model]]，最好使用它做为模型基类。
> Info: You are not required to base your model classes on [[yii\base\Model]]. However, because there are many Yii
  components built to support [[yii\base\Model]], it is usually the preferable base class for a model.


## 属性 <a name="attributes"></a>
## Attributes <a name="attributes"></a>

模型通过 *属性* 来代表业务数据，每个属性像是模型的公有可访问属性，
[[yii\base\Model::attributes()]] 指定模型所拥有的属性。
Models represent business data in terms of *attributes*. Each attribute is like a publicly accessible property
of a model. The method [[yii\base\Model::attributes()]] specifies what attributes a model class has.

可像访问一个对象属性一样访问模型的属性:
You can access an attribute like accessing a normal object property:

```php
$model = new \app\models\ContactForm;

// "name" 是ContactForm模型的属性
$model->name = 'example';
echo $model->name;
```

也可像访问数组单元项一样访问属性，这要感谢[[yii\base\Model]]支持 [ArrayAccess 数组访问](http://php.net/manual/en/class.arrayaccess.php) 
和 [ArrayIterator 数组迭代器](http://php.net/manual/en/class.arrayiterator.php):
You can also access attributes like accessing array elements, thanks to the support for
[ArrayAccess](http://php.net/manual/en/class.arrayaccess.php) and [ArrayIterator](http://php.net/manual/en/class.arrayiterator.php)
by [[yii\base\Model]]:

```php
$model = new \app\models\ContactForm;

// 像访问数组单元项一样访问属性
$model['name'] = 'example';
echo $model['name'];

// 迭代器遍历模型
foreach ($model as $name => $value) {
    echo "$name: $value\n";
}
```


### 定义属性 <a name="defining-attributes"></a>
### Defining Attributes <a name="defining-attributes"></a>

默认情况下你的模型类直接从[[yii\base\Model]]继承，所有 *non-static public非静态公有* 成员变量都是属性。
例如，下述`ContactForm` 模型类有四个属性`name`, `email`, `subject` and `body`，
`ContactForm` 模型用来代表从HTML表单获取的输入数据。
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


另一种方式是可覆盖 [[yii\base\Model::attributes()]] 来定义属性，该方法返回模型的属性名。
例如 [[yii\db\ActiveRecord]] 返回对应数据表列名作为它的属性名，
注意可能需要覆盖魔术方法如`__get()`, `__set()`使属性像普通对象属性被访问。
You may override [[yii\base\Model::attributes()]] to define attributes in a different way. The method should
return the names of the attributes in a model. For example, [[yii\db\ActiveRecord]] does so by returning
the column names of the associated database table as its attribute names. Note that you may also need to
override the magic methods such as `__get()`, `__set()` so that the attributes can be accessed like
normal object properties.


### 属性标签 <a name="attribute-labels"></a>
### Attribute Labels <a name="attribute-labels"></a>

当属性显示或获取输入时，经常要显示属性相关标签，例如假定一个属性名为`firstName`，
在某些地方如表单输入或错误信息处，你可能想显示对终端用户来说更友好的 `First Name` 标签。
When displaying values or getting input for attributes, you often need to display some labels associated
with attributes. For example, given an attribute named `firstName`, you may want to display a label `First Name`
which is more user-friendly when displayed to end users in places such as form inputs and error messages.

可以调用 [[yii\base\Model::getAttributeLabel()]] 获取属性的标签，例如：
You can get the label of an attribute by calling [[yii\base\Model::getAttributeLabel()]]. For example,

```php
$model = new \app\models\ContactForm;

// 显示为 "Name"
echo $model->getAttributeLabel('name');
```

默认情况下，属性标签通过[[yii\base\Model::generateAttributeLabel()]]方法自动从属性名生成. 
它会自动将驼峰式大小写变量名转换为多个首字母大写的单词，例如 `username` 转换为 `Username`，
`firstName` 转换为 `First Name`。
By default, attribute labels are automatically generated from attribute names. The generation is done by
the method [[yii\base\Model::generateAttributeLabel()]]. It will turn camel-case variable names into
multiple words with the first letter in each word in upper case. For example, `username` becomes `Username`,
and `firstName` becomes `First Name`.

如果你不想用自动生成的标签，可以覆盖 [[yii\base\Model::attributeLabels()]] 方法明确指定属性标签，例如：
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

应用支持多语言的情况下，可翻译属性标签，
可在 [[yii\base\Model::attributeLabels()|attributeLabels()]] 方法中定义，如下所示:
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

甚至可以根据条件定义标签，例如通过使用模型的 [scenario场景](#scenarios)，
可对相同的属性返回不同的标签。
You may even conditionally define attribute labels. For example, based on the [scenario](#scenarios) the model
is being used in, you may return different labels for the same attribute.

> 补充：属性标签是 [视图](structure-views.md)一部分，但是在模型中申明标签通常非常方便，并可行程非常简洁可重用代码。
> Info: Strictly speaking, attribute labels are part of [views](structure-views.md). But declaring labels
  in models is often very convenient and can result in very clean and reusable code.


## 场景 <a name="scenarios"></a>
## Scenarios <a name="scenarios"></a>

模型可能在多个 *场景* 下使用，例如 `User` 模块可能会在收集用户登录输入，也可能会在用户注册时使用。
在不同的场景下，模型可能会使用不同的业务规则和逻辑，例如 `email` 属性在注册时强制要求有，但在登陆时不需要。
A model may be used in different *scenarios*. For example, a `User` model may be used to collect user login inputs,
but it may also be used for the user registration purpose. In different scenarios, a model may use different
business rules and logic. For example, the `email` attribute may be required during user registration,
but not so during user login.

模型使用 [[yii\base\Model::scenario]] 属性保持使用场景的跟踪，
默认情况下，模型支持一个名为 `default` 的场景，如下展示两种设置场景的方法:

```php
// 场景作为属性来设置
$model = new User;
$model->scenario = 'login';

// 场景通过构造初始化配置来设置
$model = new User(['scenario' => 'login']);
```

默认情况下，模型支持的场景由模型中申明的 [验证规则](#validation-rules) 来决定，
但你可以通过覆盖[[yii\base\Model::scenarios()]]方法来自定义行为，如下所示：
By default, the scenarios supported by a model are determined by the [validation rules](#validation-rules) declared
in the model. However, you can customize this behavior by overriding the [[yii\base\Model::scenarios()]] method,
like the following:

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

> 补充：在上述和下述的例子中，模型类都是继承[[yii\db\ActiveRecord]]，
  因为多场景的使用通常发生在[Active Record](db-active-record.md) 类中.
> Info: In the above and following examples, the model classes are extending from [[yii\db\ActiveRecord]]
  because the usage of multiple scenarios usually happens to [Active Record](db-active-record.md) classes.

`scenarios()` 方法返回一个数组，数组的键为场景名，值为对应的 *active attributes活动属性*。
活动属性可被 [块赋值](#massive-assignment) 并遵循[验证规则](#validation-rules)
在上述例子中，`username` 和 `password` 在`login`场景中启用，在 `register` 场景中, 
除了 `username` and `password` 外 `email` 也被启用。
The `scenarios()` method returns an array whose keys are the scenario names and values the corresponding
*active attributes*. An active attribute can be [massively assigned](#massive-assignment) and is subject
to [validation](#validation-rules). In the above example, the `username` and `password` attributes are active
in the `login` scenario; while in the `register` scenario, `email` is also active besides `username` and `password`.

`scenarios()` 方法默认实现会返回所有[[yii\base\Model::rules()]]方法申明的验证规则中的场景，
当覆盖`scenarios()`时，如果你想在默认场景外使用新场景，可以编写类似如下代码：
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

场景特性主要在[验证](#validation-rules) 和 [属性块赋值](#massive-assignment) 中使用。
你也可以用于其他目的，例如可基于不同的场景定义不同的 [属性标签](#attribute-labels)。
The scenario feature is primarily used by [validation](#validation-rules) and [massive attribute assignment](#massive-assignment).
You can, however, use it for other purposes. For example, you may declare [attribute labels](#attribute-labels)
differently based on the current scenario.


## 验证规则 <a name="validation-rules"></a>
## Validation Rules <a name="validation-rules"></a>

当模型接收到终端用户输入的数据，数据应当满足某种规则(称为 *验证规则*, 也称为 *业务规则*)。
例如假定`ContactForm`模型，你可能想确保所有属性不为空且 `email` 属性包含一个有效的邮箱地址，
如果某个属性的值不满足对应的业务规则，相应的错误信息应显示，以帮助用户修正错误。
When the data for a model is received from end users, it should be validated to make sure it satisfies
certain rules (called *validation rules*, also known as *business rules*). For example, given a `ContactForm` model,
you may want to make sure all attributes are not empty and the `email` attribute contains a valid email address.
If the values for some attributes do not satisfy the corresponding business rules, appropriate error messages
should be displayed to help the user to fix the errors.

可调用 [[yii\base\Model::validate()]] 来验证接收到的数据，
该方法使用[[yii\base\Model::rules()]]申明的验证规则来验证每个相关属性，
如果没有找到错误，会返回 true，否则它会将错误保存在 [[yii\base\Model::errors]] 属性中并返回false，例如：
You may call [[yii\base\Model::validate()]] to validate the received data. The method will use
the validation rules declared in [[yii\base\Model::rules()]] to validate every relevant attribute. If no error
is found, it will return true. Otherwise, it will keep the errors in the [[yii\base\Model::errors]] property
and return false. For example,

```php
$model = new \app\models\ContactForm;

// 用户输入数据赋值到模型属性
$model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // 所有输入数据都有效 all inputs are valid
} else {
    // 验证失败：$errors 是一个包含错误信息的数组
    $errors = $model->errors;
}
```


通过覆盖 [[yii\base\Model::rules()]] 方法指定模型属性应该满足的规则来申明模型相关验证规则。
下述例子显示`ContactForm`模型申明的验证规则:
To declare validation rules associated with a model, override the [[yii\base\Model::rules()]] method by returning
the rules that the model attributes should satisfy. The following example shows the validation rules declared
for the `ContactForm` model:

```php
public function rules()
{
    return [
        // name, email, subject 和 body 属性必须有值
        [['name', 'email', 'subject', 'body'], 'required'],

        // email 属性必须是一个有效的电子邮箱地址
        ['email', 'email'],
    ];
}
```

一条规则可用来验证一个或多个属性，一个属性可对应一条或多条规则。
更多关于如何申明验证规则的详情请参考 [验证输入](input-validation.md) 一节.
A rule can be used to validate one or multiple attributes, and an attribute may be validated by one or multiple rules.
Please refer to the [Validating Input](input-validation.md) section for more details on how to declare
validation rules.

有时你想一条规则只在某个 [场景](#scenarios) 下应用，为此你可以指定规则的 `on` 属性，如下所示:
Sometimes, you may want a rule to be applied only in certain [scenarios](#scenarios). To do so, you can
specify the `on` property of a rule, like the following:

```php
public function rules()
{
    return [
        // 在"register" 场景下 username, email 和 password 必须有值
        [['username', 'email', 'password'], 'required', 'on' => 'register'],

        // 在 "login" 场景下 username 和 password 必须有值
        [['username', 'password'], 'required', 'on' => 'login'],
    ];
}
```

如果没有指定 `on` 属性，规则会在所有场景下应用， 在当前[[yii\base\Model::scenario|scenario]]
下应用的规则称之为 *active rule活动规则*。
If you do not specify the `on` property, the rule would be applied in all scenarios. A rule is called
an *active rule* if it can be applied in the current [[yii\base\Model::scenario|scenario]].

一个属性只会属于`scenarios()`中定义的活动属性且在`rules()`申明对应一条或多条活动规则的情况下被验证。
An attribute will be validated if and only if it is an active attribute declared in `scenarios()` and
is associated with one or multiple active rules declared in `rules()`.


## 块赋值 <a name="massive-assignment"></a>
## Massive Assignment <a name="massive-assignment"></a>

块赋值只用一行代码将用户所有输入填充到一个模型，非常方便，
它直接将输入数据对应填充到 [[yii\base\Model::attributes]] 属性。
以下两段代码效果是相同的，都是将终端用户输入的表单数据赋值到 `ContactForm` 模型的属性，
明显地前一段块赋值的代码比后一段代码简洁且不易出错。
Massive assignment is a convenient way of populating a model with user inputs using a single line of code.
It populates the attributes of a model by assigning the input data directly to the [[yii\base\Model::attributes]]
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


### 安全属性 <a name="safe-attributes"></a>
### Safe Attributes <a name="safe-attributes"></a>

块赋值只应用在模型当前[[yii\base\Model::scenario|scenario]]场景[[yii\base\Model::scenarios()]]方法
列出的称之为 *安全属性* 的属性上，例如，如果`User`模型申明以下场景，
当当前场景为`login`时候，只有`username` and `password` 可被块赋值，其他属性不会被赋值。
Massive assignment only applies to the so-called *safe attributes* which are the attributes listed in
[[yii\base\Model::scenarios()]] for the current [[yii\base\Model::scenario|scenario]] of a model.
For example, if the `User` model has the following scenario declaration, then when the current scenario
is `login`, only the `username` and `password` can be massively assigned. Any other attributes will
be kept untouched.

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password'],
        'register' => ['username', 'email', 'password'],
    ];
}
```

> 补充: 块赋值只应用在安全属性上，因为你想控制哪些属性会被终端用户输入数据所修改，
  例如，如果 `User` 模型有一个`permission`属性对应用户的权限，
  你可能只想让这个属性在后台界面被管理员修改。
> Info: The reason that massive assignment only applies to safe attributes is because you want to
  control which attributes can be modified by end user data. For example, if the `User` model
  has a `permission` attribute which determines the permission assigned to the user, you would
  like this attribute to be modifiable by administrators through a backend interface only.

由于默认[[yii\base\Model::scenarios()]]的实现会返回[[yii\base\Model::rules()]]所有属性和数据，
如果不覆盖这个方法，表示所有只要出现在活动验证规则中的属性都是安全的。
Because the default implementation of [[yii\base\Model::scenarios()]] will return all scenarios and attributes
found in [[yii\base\Model::rules()]], if you do not override this method, it means an attribute is safe as long
as it appears in one of the active validation rules.

为此，提供一个特别的别名为 `safe` 的验证器来申明哪些属性是安全的不需要被验证，
如下示例的规则申明 `title` 和 `description` 都为安全属性。
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


### 非安全属性 <a name="unsafe-attributes"></a>
### Unsafe Attributes <a name="unsafe-attributes"></a>

如上所述，[[yii\base\Model::scenarios()]] 方法提供两个用处：定义哪些属性应被验证，定义哪些属性安全。
在某些情况下，你可能想验证一个属性但不想让他是安全的，可在`scenarios()`方法中属性名加一个惊叹号 `!`。
例如像如下的`secret`属性。
As described above, the [[yii\base\Model::scenarios()]] method serves for two purposes: determining which attributes
should be validated, and determining which attributes are safe. In some rare cases, you may want to validate
an attribute but do not want to mark it safe. You can do so by prefixing an exclamation mark `!` to the attribute
name when declaring it in `scenarios()`, like the `secret` attribute in the following:

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password', '!secret'],
    ];
}
```

当模型在 `login` 场景下，三个属性都会被验证，但只有 `username`和 `password` 属性会被块赋值，
要对`secret`属性赋值，必须像如下例子明确对它赋值。
When the model is in the `login` scenario, all three attributes will be validated. However, only the `username`
and `password` attributes can be massively assigned. To assign an input value to the `secret` attribute, you
have to do it explicitly as follows,

```php
$model->secret = $secret;
```


## 数据导出 <a name="data-exporting"></a>
## Data Exporting <a name="data-exporting"></a>

模型通常要导出成不同格式，例如，你可能想将模型的一个集合转成JSON或Excel格式，
导出过程可分解为两个步骤，第一步，模型转换成数组；第二步，数组转换成所需要的格式。
你只需要关注第一步，因为第二步可被通用的数据转换器如[[yii\web\JsonResponseFormatter]]来完成。
Models often need to be exported in different formats. For example, you may want to convert a collection of
models into JSON or Excel format. The exporting process can be broken down into two independent steps.
In the first step, models are converted into arrays; in the second step, the arrays are converted into
target formats. You may just focus on the first step, because the second step can be achieved by generic
data formatters, such as [[yii\web\JsonResponseFormatter]].

将模型转换为数组最简单的方式是使用 [[yii\base\Model::attributes]] 属性，例如：
The simplest way of converting a model into an array is to use the [[yii\base\Model::attributes]] property.
For example,

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

[[yii\base\Model::attributes]] 属性会返回 *所有* [[yii\base\Model::attributes()]] 申明的属性的值。
By default, the [[yii\base\Model::attributes]] property will return the values of *all* attributes
declared in [[yii\base\Model::attributes()]].

更灵活和强大的将模型转换为数组的方式是使用 [[yii\base\Model::toArray()]] 方法，
它的行为默认和 [[yii\base\Model::attributes]] 相同，
但是它允许你选择哪些称之为*字段*的数据项放入到结果数组中并同时被格式化。
实际上，它是导出模型到 RESTful 网页服务开发的默认方法，详情请参阅[响应格式](rest-response-formatting.md).
A more flexible and powerful way of converting a model into an array is to use the [[yii\base\Model::toArray()]]
method. Its default behavior is the same as that of [[yii\base\Model::attributes]]. However, it allows you
to choose which data items, called *fields*, to be put in the resulting array and how they should be formatted.
In fact, it is the default way of exporting models in RESTful Web service development, as described in
the [Response Formatting](rest-response-formatting.md).


### 字段 <a name="fields"></a>
### Fields <a name="fields"></a>

字段是模型通过调用[[yii\base\Model::toArray()]]生成的数组的单元名。
A field is simply a named element in the array that is obtained by calling the [[yii\base\Model::toArray()]] method
of a model.

默认情况下，字段名对应属性名，但是你可以通过覆盖
[[yii\base\Model::fields()|fields()]] 和/或 [[yii\base\Model::extraFields()|extraFields()]] 方法来改变这种行为，
两个方法都返回一个字段定义列表，`fields()` 方法定义的字段是默认字段，表示`toArray()`方法默认会返回这些字段。 
`extraFields()`方法定义额外可用字段，通过`toArray()`方法指定`$expand`参数来返回这些额外可用字段。
例如如下代码会返回`fields()`方法定义的所有字段和`extraFields()`方法定义的`prettyName` and `fullAddress`字段。
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

可通过覆盖 `fields()` 来增加、删除、重命名和重定义字段，`fields()` 方法返回值应为数组，
数组的键为字段名，数组的值为对应的可为属性名或匿名函数返回的字段定义对应的值。
特使情况下，如果字段名和属性定义名相同，可以省略数组键，例如：
You can override `fields()` to add, remove, rename or redefine fields. The return value of `fields()`
should be an array. The array keys are the field names, and the array values are the corresponding
field definitions which can be either property/attribute names or anonymous functions returning the
corresponding field values. In the special case when a field name is the same as its defining attribute
name, you can omit the array key. For example,

```php
// 明确列出每个字段，特别用于你想确保数据表或模型属性改变不会导致你的字段改变(保证后端的API兼容).
public function fields()
{
    return [
        // 字段名和属性名相同
        'id',

        // 字段名为 "email"，对应属性名为 "email_address"
        'email' => 'email_address',

        // 字段名为 "name", 值通过PHP代码返回
        'name' => function () {
            return $this->first_name . ' ' . $this->last_name;
        },
    ];
}

// 过滤掉一些字段，特别用于你想继承父类实现并不想用一些敏感字段
public function fields()
{
    $fields = parent::fields();

    // 去掉一些包含敏感信息的字段
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> 警告：由于模型的所有属性会被包含在导出数组，最好检查数据确保没包含敏感数据，
> 如果有敏感数据，应覆盖 `fields()` 方法过滤掉，在上述列子中，我们选择过滤掉
> `auth_key`, `password_hash` and `password_reset_token`。
> Warning: Because by default all attributes of a model will be included in the exported array, you should
> examine your data to make sure they do not contain sensitive information. If there is such information,
> you should override `fields()` to filter them out. In the above example, we choose
> to filter out `auth_key`, `password_hash` and `password_reset_token`.


## 最佳实践 <a name="best-practices"></a>
## Best Practices <a name="best-practices"></a>

模型是代表业务数据、规则和逻辑的中心地方，通常在很多地方重用，
在一个设计良好的应用中，模型通常比[控制器](structure-controllers.md)代码多。
Models are the central places to represent business data, rules and logic. They often need to be reused
in different places. In a well-designed application, models are usually much fatter than
[controllers](structure-controllers.md).

归纳起来，模型
In summary, models

* 可包含属性来展示业务数据;
* 可包含验证规则确保数据有效和完整;
* 可包含方法实现业务逻辑;
* 不应直接访问请求，session和其他环境数据，这些数据应该由[控制器](structure-controllers.md)传入到模型;
* 应避免嵌入HTML或其他展示代码，这些代码最好在 [视图](structure-views.md)中处理;
* 单个模型中避免太多的 [场景](#scenarios).
* may contain attributes to represent business data;
* may contain validation rules to ensure the data validity and integrity;
* may contain methods implementing business logic;
* should NOT directly access request, session, or any other environmental data. These data should be injected
  by [controllers](structure-controllers.md) into models;
* should avoid embedding HTML or other presentational code - this is better done in [views](structure-views.md);
* avoid having too many [scenarios](#scenarios) in a single model.

在开发大型复杂系统时应经常考虑最后一条建议，
在这些系统中，模型会很大并在很多地方使用，因此会包含需要规则集和业务逻辑，
最后维护这些模型代码成为一个噩梦，因为一个简单修改会影响好多地方，
为确保模型好维护，最好使用以下策略：
You may usually consider the last recommendation above when you are developing large complex systems.
In these systems, models could be very fat because they are used in many places and may thus contain many sets
of rules and business logic. This often ends up in a nightmare in maintaining the model code
because a single touch of the code could affect several different places. To make the mode code more maintainable,
you may take the following strategy:

* 定义可被多个 [应用主体](structure-applications.md) 或 [模块](structure-modules.md) 共享的模型基类集合。
  这些模型类应包含通用的最小规则集合和逻辑。
* 在每个使用模型的 [应用主体](structure-applications.md) 或 [模块](structure-modules.md)中，
  通过继承对应的模型基类来定义具体的模型类，具体模型类包含应用主体或模块指定的规则和逻辑。
* Define a set of base model classes that are shared by different [applications](structure-applications.md) or
  [modules](structure-modules.md). These model classes should contain minimal sets of rules and logic that
  are common among all their usages.
* In each [application](structure-applications.md) or [module](structure-modules.md) that uses a model,
  define a concrete model class by extending from the corresponding base model class. The concrete model classes
  should contain rules and logic that are specific for that application or module.

例如，在[高级应用模板](tutorial-advanced-app.md)，你可以定义一个模型基类`common\models\Post`，
然后在前台应用中，定义并使用一个继承`common\models\Post`的具体模型类`frontend\models\Post`，
在后台应用中可以类似地定义`backend\models\Post`。
通过这种策略，你清楚`frontend\models\Post`只对应前台应用，如果你修改它，就无需担忧修改会影响后台应用。
For example, in the [Advanced Application Template](tutorial-advanced-app.md), you may define a base model
class `common\models\Post`. Then for the front end application, you define and use a concrete model class
`frontend\models\Post` which extends from `common\models\Post`. And similarly for the back end application,
you define `backend\models\Post`. With this strategy, you will be sure that the code in `frontend\models\Post`
is only specific to the front end application, and if you make any change to it, you do not need to worry if
the change may break the back end application.
