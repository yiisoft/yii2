模型
======

模型是 [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller) 模式中的一部分，
是代表业务数据、规则和逻辑的对象。

可通过继承 [[yii\base\Model]] 或它的子类定义模型类，
基类[[yii\base\Model]]支持许多实用的特性：

* [属性](#attributes): 代表可像普通类属性或数组
  一样被访问的业务数据;
* [属性标签](#attribute-labels): 指定属性显示出来的标签;
* [块赋值](#massive-assignment): 支持一步给许多属性赋值;
* [验证规则](#validation-rules): 确保输入数据符合所申明的验证规则;
* [数据导出](#data-exporting): 允许模型数据导出为自定义格式的数组。

`Model` 类也是更多高级模型如[Active Record 活动记录](db-active-record.md)的基类，
更多关于这些高级模型的详情请参考相关手册。

> Info: 模型并不强制一定要继承[[yii\base\Model]]，但是由于很多组件支持[[yii\base\Model]]，
  最好使用它做为模型基类。


## 属性 <span id="attributes"></span>

模型通过 *属性* 来代表业务数据，每个属性像是模型的公有可访问属性，
[[yii\base\Model::attributes()]] 指定模型所拥有的属性。

可像访问一个对象属性一样访问模型的属性:

```php
$model = new \app\models\ContactForm;

// "name" 是ContactForm模型的属性
$model->name = 'example';
echo $model->name;
```

也可像访问数组单元项一样访问属性，这要感谢[[yii\base\Model]]支持 
[ArrayAccess 数组访问](http://php.net/manual/en/class.arrayaccess.php) 
和 [ArrayIterator 数组迭代器](http://php.net/manual/en/class.arrayiterator.php):

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


### 定义属性 <span id="defining-attributes"></span>

默认情况下你的模型类直接从[[yii\base\Model]]继承，所有 *non-static public非静态公有* 成员变量都是属性。
例如，下述`ContactForm` 模型类有四个属性`name`, `email`, `subject` and `body`，
`ContactForm` 模型用来代表从HTML表单获取的输入数据。

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


另一种方式是可覆盖 [[yii\base\Model::attributes()]] 
来定义属性，该方法返回模型的属性名。
例如 [[yii\db\ActiveRecord]] 返回对应数据表列名作为它的属性名，
注意可能需要覆盖魔术方法如`__get()`,
`__set()`使属性像普通对象属性被访问。


### 属性标签 <span id="attribute-labels"></span>

当属性显示或获取输入时，经常要显示属性相关标签，
例如假定一个属性名为`firstName`，
在某些地方如表单输入或错误信息处，你可能想显示对终端用户来说更友好的 `First Name` 标签。

可以调用 [[yii\base\Model::getAttributeLabel()]] 获取属性的标签，例如：

```php
$model = new \app\models\ContactForm;

// 显示为 "Name"
echo $model->getAttributeLabel('name');
```

默认情况下，属性标签通过[[yii\base\Model::generateAttributeLabel()]]方法自动从属性名生成. 
它会自动将驼峰式大小写变量名转换为多个首字母大写的单词，
例如 `username` 转换为 `Username`，
`firstName` 转换为 `First Name`。

如果你不想用自动生成的标签，可以覆盖 [[yii\base\Model::attributeLabels()]] 方法明确指定属性标签，
例如：

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

> Info: 属性标签是 [视图](structure-views.md)一部分，
  但是在模型中声明标签通常非常方便，并可形成非常简洁可重用代码。


## 场景 <span id="scenarios"></span>

模型可能在多个 *场景* 下使用，例如 `User` 模块可能会在收集用户登录输入，
也可能会在用户注册时使用。在不同的场景下，
模型可能会使用不同的业务规则和逻辑，
例如 `email` 属性在注册时强制要求有，但在登陆时不需要。

模型使用 [[yii\base\Model::scenario]] 属性保持使用场景的跟踪，
默认情况下，模型支持一个名为 `default` 的场景，
如下展示两种设置场景的方法:

```php
// 场景作为属性来设置
$model = new User;
$model->scenario = 'login';

// 场景通过构造初始化配置来设置
$model = new User(['scenario' => 'login']);
```

默认情况下，模型支持的场景由模型中申明的 [验证规则](#validation-rules) 来决定，
但你可以通过覆盖[[yii\base\Model::scenarios()]]方法来自定义行为，
如下所示：

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

> Info: 在上述和下述的例子中，模型类都是继承[[yii\db\ActiveRecord]]，
  因为多场景的使用通常发生在[Active Record](db-active-record.md) 类中.

`scenarios()` 方法返回一个数组，数组的键为场景名，值为对应的 *active attributes活动属性*。
活动属性可被 [块赋值](#massive-assignment) 并遵循[验证规则](#validation-rules)
在上述例子中，`username` 和 `password` 在`login`场景中启用，在 `register` 场景中, 
除了 `username` and `password` 外 `email` 也被启用。

`scenarios()` 方法默认实现会返回所有[[yii\base\Model::rules()]]方法申明的验证规则中的场景，
当覆盖`scenarios()`时，如果你想在默认场景外使用新场景，
可以编写类似如下代码：

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

场景特性主要在[验证](#validation-rules) 和 [属性块赋值](#massive-assignment) 中使用。
你也可以用于其他目的，
例如可基于不同的场景定义不同的 [属性标签](#attribute-labels)。


## 验证规则 <span id="validation-rules"></span>

当模型接收到终端用户输入的数据，
数据应当满足某种规则(称为 *验证规则*, 也称为 *业务规则*)。
例如假定`ContactForm`模型，你可能想确保所有属性不为空且 `email` 属性包含一个有效的邮箱地址，
如果某个属性的值不满足对应的业务规则，
相应的错误信息应显示，以帮助用户修正错误。

可调用 [[yii\base\Model::validate()]] 来验证接收到的数据，
该方法使用[[yii\base\Model::rules()]]申明的验证规则来验证每个相关属性，
如果没有找到错误，会返回 true，
否则它会将错误保存在 [[yii\base\Model::errors]] 属性中并返回false，例如：

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


通过覆盖 [[yii\base\Model::rules()]] 方法指定模型
属性应该满足的规则来申明模型相关验证规则。
下述例子显示`ContactForm`模型申明的验证规则:

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
更多关于如何申明验证规则的详情请参考 
[验证输入](input-validation.md) 一节.

有时你想一条规则只在某个 [场景](#scenarios) 下应用，为此你可以指定规则的 `on` 属性，
如下所示:

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

一个属性只会属于`scenarios()`中定义的活动属性且在`rules()`
申明对应一条或多条活动规则的情况下被验证。


## 块赋值 <span id="massive-assignment"></span>

块赋值只用一行代码将用户所有输入填充到一个模型，非常方便，
它直接将输入数据对应填充到 [[yii\base\Model::attributes]] 属性。
以下两段代码效果是相同的，
都是将终端用户输入的表单数据赋值到 `ContactForm` 模型的属性，
明显地前一段块赋值的代码比后一段代码简洁且不易出错。

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


### 安全属性 <span id="safe-attributes"></span>

块赋值只应用在模型当前[[yii\base\Model::scenario|scenario]]
场景[[yii\base\Model::scenarios()]]方法
列出的称之为 *安全属性* 的属性上，例如，如果`User`模型申明以下场景，
当当前场景为`login`时候，只有`username` and `password` 可被块赋值，
其他属性不会被赋值。

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password'],
        'register' => ['username', 'email', 'password'],
    ];
}
```

> Info: 块赋值只应用在安全属性上，
  因为你想控制哪些属性会被终端用户输入数据所修改，
  例如，如果 `User` 模型有一个`permission`属性对应用户的权限，
  你可能只想让这个属性在后台界面被管理员修改。

由于默认[[yii\base\Model::scenarios()]]的实现会返回
[[yii\base\Model::rules()]]所有属性和数据，
如果不覆盖这个方法，表示所有只要出现在活动验证规则中的属性都是安全的。

为此，提供一个特别的别名为 `safe` 的验证器来申明
哪些属性是安全的不需要被验证，
如下示例的规则申明 `title` 和 `description` 都为安全属性。

```php
public function rules()
{
    return [
        [['title', 'description'], 'safe'],
    ];
}
```


### 非安全属性 <span id="unsafe-attributes"></span>

如上所述，[[yii\base\Model::scenarios()]] 方法提供两个用处：定义哪些属性应被验证，定义哪些属性安全。
在某些情况下，你可能想验证一个属性但不想让他是安全的，
可在`scenarios()`方法中属性名加一个惊叹号 `!`。
例如像如下的`secret`属性。

```php
public function scenarios()
{
    return [
        'login' => ['username', 'password', '!secret'],
    ];
}
```

当模型在 `login` 场景下，三个属性都会被验证，
但只有 `username`和 `password` 属性会被块赋值，
要对`secret`属性赋值，必须像如下例子明确对它赋值。

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


## 数据导出 <span id="data-exporting"></span>

模型通常要导出成不同格式，例如，你可能想将模型的一个集合转成JSON或Excel格式，
导出过程可分解为两个步骤：

- 模型转换成数组；
- 数组转换成所需要的格式。

你只需要关注第一步，因为第二步可被通用的
数据转换器如[[yii\web\JsonResponseFormatter]]来完成。

将模型转换为数组最简单的方式是使用 [[yii\base\Model::attributes]] 属性，
例如：

```php
$post = \app\models\Post::findOne(100);
$array = $post->attributes;
```

[[yii\base\Model::attributes]] 属性会返回 *所有* 
[[yii\base\Model::attributes()]] 申明的属性的值。

更灵活和强大的将模型转换为数组的方式是使用 [[yii\base\Model::toArray()]] 方法，
它的行为默认和 [[yii\base\Model::attributes]] 相同，
但是它允许你选择哪些称之为*字段*的数据项放入到结果数组中并同时被格式化。
实际上，它是导出模型到 RESTful 网页服务开发的默认方法，
详情请参阅[响应格式](rest-response-formatting.md).


### 字段 <span id="fields"></span>

字段是模型通过调用[[yii\base\Model::toArray()]]
生成的数组的单元名。

默认情况下，字段名对应属性名，但是你可以通过覆盖
[[yii\base\Model::fields()|fields()]] 和/或 
[[yii\base\Model::extraFields()|extraFields()]] 方法来改变这种行为，
两个方法都返回一个字段定义列表，`fields()` 方法定义的字段是默认字段，
表示`toArray()`方法默认会返回这些字段。 `extraFields()`方法定义额外可用字段，
通过`toArray()`方法指定`$expand`参数来返回这些额外可用字段。
例如如下代码会返回`fields()`方法定义的所有字段和`extraFields()`方法定义的`prettyName` and `fullAddress`字段。

```php
$array = $model->toArray([], ['prettyName', 'fullAddress']);
```

可通过覆盖 `fields()` 来增加、删除、重命名和重定义字段，
`fields()` 方法返回值应为数组，
数组的键为字段名，数组的值为对应的可为属性名或匿名函数返回的字段定义对应的值。
特使情况下，如果字段名和属性定义名相同，可以省略数组键，
例如：

```php
// 明确列出每个字段，特别用于你想确保数据表或模型
// 属性改变不会导致你的字段改变(保证后端的API兼容)。
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

// 过滤掉一些字段，特别用于
// 你想继承父类实现并不想用一些敏感字段
public function fields()
{
    $fields = parent::fields();

    // 去掉一些包含敏感信息的字段
    unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);

    return $fields;
}
```

> Warning: 由于模型的所有属性会被包含在导出数组，最好检查数据确保没包含敏感数据，
> 如果有敏感数据，应覆盖 `fields()` 方法过滤掉，
> 在上述列子中，我们选择过滤掉
> `auth_key`, `password_hash` and `password_reset_token`。


## 最佳实践 <span id="best-practices"></span>

模型是代表业务数据、规则和逻辑的中心地方，通常在很多地方重用，
在一个设计良好的应用中，模型通常比
[控制器](structure-controllers.md)代码多。

归纳起来，模型

* 可包含属性来展示业务数据;
* 可包含验证规则确保数据有效和完整;
* 可包含方法实现业务逻辑;
* 不应直接访问请求，session和其他环境数据，
  这些数据应该由[控制器](structure-controllers.md)传入到模型;
* 应避免嵌入HTML或其他展示代码，这些代码最好在 [视图](structure-views.md)中处理;
* 单个模型中避免太多的 [场景](#scenarios).

在开发大型复杂系统时应经常考虑最后一条建议，
在这些系统中，模型会很大并在很多地方使用，因此会包含需要规则集和业务逻辑，
最后维护这些模型代码成为一个噩梦，
因为一个简单修改会影响好多地方，
为确保模型好维护，最好使用以下策略：

* 定义可被多个 [应用主体](structure-applications.md) 
  或 [模块](structure-modules.md) 共享的模型基类集合。
  这些模型类应包含通用的最小规则集合和逻辑。
* 在每个使用模型的 [应用主体](structure-applications.md) 或 [模块](structure-modules.md)中，
  通过继承对应的模型基类来定义具体的模型类，
  具体模型类包含应用主体或模块指定的规则和逻辑。

例如，在[高级应用模板](tutorial-advanced-app.md)，
你可以定义一个模型基类`common\models\Post`，
然后在前台应用中，定义并使用一个继承`common\models\Post`的具体模型类`frontend\models\Post`，
在后台应用中可以类似地定义`backend\models\Post`。
通过这种策略，你清楚`frontend\models\Post`只对应前台应用，如果你修改它，
就无需担忧修改会影响后台应用。
