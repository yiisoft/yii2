输入验证
================

根据经验，您永远不应该信任从最终用户收到的数据，
并且应该在充分利用之前对其进行验证。

要给 [model](structure-models.md) 填充其所需的用户输入数据，你可以调用 [[yii\base\Model::validate()]] 
方法验证它们。该方法会返回一个布尔值，指明是否通过验证。若没有通过，你能通过 [[yii\base\Model::errors]] 
属性获取相应的报错信息。比如，

```php
$model = new \app\models\ContactForm();

// 根据用户的输入填充到模型的属性中
$model->load(\Yii::$app->request->post());
// 等效于下面这样：
// $model->attributes = \Yii::$app->request->post('ContactForm');

if ($model->validate()) {
    // 所有输入通过验证
} else {
    // 验证失败: $errors 是一个包含错误信息的数组
    $errors = $model->errors;
}
```


## 声明规则（Rules） <span id="declaring-rules"></span>

要让 `validate()` 方法起作用，你需要声明与需验证模型特性相关的验证规则。
为此，需要重写 [[yii\base\Model::rules()]] 方法。下面的例子展示了如何
声明用于验证 `ContactForm` 模型的相关验证规则：

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

[[yii\base\Model::rules()|rules()]] 方法应返回一个由规则所组成的数组，
每一个规则都呈现为以下这类格式的小数组：

```php
[
    // 必填，指出哪个属性由这个规则验证。
    // 如果是一个属性，可以直接写属性的名字
    // 不必写在数组里。
    ['attribute1', 'attribute2', ...],
    
    // 必填，指出规则的类型。
    // 可以是一个类名，验证器别名，或者是一个验证器方法名
    'validator',

    // 可选，指出这个规则在哪个（些）场景下生效
    // 如果没有给出，意味着这个规则在所有场景都生效
    // 如果你希望在所有场景下生效，但是在排除的场景里不生效。
    // 可以配置 "except" 选项
    'on' => ['scenario1', 'scenario2', ...],

    // 可选，为验证器对象指定额外的配置
    'property1' => 'value1', 'property2' => 'value2', ...
]
```

对于每个规则，你至少需要指定该规则适用于哪些特性，以及本规则的类型是什么。
你可以指定以下的规则类型之一：

* 核心验证器的昵称，比如 `required`、`in`、`date`，等等。请参考
  [核心验证器](tutorial-core-validators.md)章节查看完整的核心验证器列表。
* 模型类中的某个验证方法的名称，或者一个匿名方法。
  请参考[行内验证器](#inline-validators)小节了解更多。
* 验证器类的名称。
  请参考[独立验证器](#standalone-validators)小节了解更多。

一个规则可用于验证一个或多个模型特性，且一个特性可以被一个或多个规则所验证。
一个规则可以施用于特定[场景（scenario）](structure-models.md#scenarios)，只
要指定 `on` 选项。如果你不指定 `on` 选项，那么该规则会适配于所有场景。

当调用 `validate()` 方法时，它将运行以下几个具体的验证步骤：

1. 检查从声明自 [[yii\base\Model::scenarios()]] 方法的场景中所挑选出的当前[[yii\base\Model::scenario|场景]]的信息，
   从而确定出那些特性需要被验证。这些特性被称为激活特性。
2. 检查从声明自 [[yii\base\Model::rules()]] 方法的众多规则中所挑选出的适用于当前[[yii\base\Model::scenario|场景]]的规则，
   从而确定出需要验证哪些规则。这些规则被称为激活规则。
3. 用每个激活规则去验证每个
   与之关联的激活特性。

基于以上验证步骤，有且仅有声明在 `scenarios()` 
方法里的激活特性，且它还必须与一或多个声明自
`rules()` 里的激活规则相关联才会被验证。

> Note: 可以方便地给规则命名比如：
>
> ```php
> public function rules()
> {
>     return [
>         // ...
>         'password' => [['password'], 'string', 'max' => 60],
>     ];
> }
> ```
>
> 你可以在子模型中使用：
>
> ```php
> public function rules()
> {
>     $rules = parent::rules();
>     unset($rules['password']);
>     return $rules;
> }


### 自定义错误信息 <span id="customizing-error-messages"></span>

大多数的验证器都有默认的错误信息，当模型的某个特性验证失败的时候，该错误信息会被返回给模型。
比如，用 [[yii\validators\RequiredValidator|required]] 验证器的规则检验 `username` 
特性失败的话，会返还给模型 "Username cannot be blank." 信息。

你可以通过在声明规则的时候同时指定 `message` 属性，
来定制某个规则的错误信息，比如这样：

```php
public function rules()
{
    return [
        ['username', 'required', 'message' => 'Please choose a username.'],
    ];
}
```

一些验证器还支持用于针对不同原因的验证失败返回更加准确的额外错误信息。比如，[[yii\validators\NumberValidator|number]] 
验证器就支持 [[yii\validators\NumberValidator::tooBig|tooBig]] 和 [[yii\validators\NumberValidator::tooSmall|tooSmall]] 
两种错误消息用于分别返回输入值是太大还是太小。你也可以像配置验证器的
其他属性一样配置它们俩各自的错误信息。



### 验证事件 <span id="validation-events"></span>

当调用 [[yii\base\Model::validate()]] 方法的过程里，它同时会调用两个特殊的方法，
把它们重写掉可以实现自定义验证过程的目的：

* [[yii\base\Model::beforeValidate()]]：在默认的实现中会触发 [[yii\base\Model::EVENT_BEFORE_VALIDATE]] 事件。
  你可以重写该方法或者响应此事件，来在验证开始之前，先进行一些预处理的工作。
 （比如，标准化数据输入）该方法应该返回一个布尔值，用于标明验证是否通过。
* [[yii\base\Model::afterValidate()]]：在默认的实现中会触发 [[yii\base\Model::EVENT_AFTER_VALIDATE]] 事件。
  你可以重写该方法或者响应此事件，来在验证结束之后，
  再进行一些收尾的工作。



### 条件式验证 <span id="conditional-validation"></span>

若要只在某些条件满足时，才验证相关特性，比如：是否验证某特性取决于另一特性的值，
你可以通过[[yii\validators\Validator::when|when]] 
属性来定义相关条件。举例而言，

```php
    ['state', 'required', 'when' => function($model) {
        return $model->country == 'USA';
    }]
```

[[yii\validators\Validator::when|when]] 属性会读入一个如下所示结构的 PHP callable 函数对象：

```php
/**
 * @param Model $model 要验证的模型对象
 * @param string $attribute 待测特性名
 * @return bool 返回是否启用该规则
 */
function ($model, $attribute)
```

若你需要支持客户端的条件验证，你应该配置[[yii\validators\Validator::whenClient|whenClient]] 属性，
它会读入一条包含有 JavaScript 函数的字符串。
这个函数将被用于确定该客户端验证规则是否被启用。比如，

```php
    ['state', 'required', 'when' => function ($model) {
        return $model->country == 'USA';
    }, 'whenClient' => "function (attribute, value) {
        return $('#country').value == 'USA';
    }"]
```


### 数据预处理 <span id="data-filtering"></span>

用户输入经常需要进行数据过滤，或者叫预处理。比如你可能会需要先去掉 `username` 输入的收尾空格。
你可以通过使用验证规则来实现此目的。

下面的例子展示了如何去掉输入信息的首尾空格，并将空输入返回为 null。具体方法为通过调用 
[trim](tutorial-core-validators.md#trim) 和 [default](tutorial-core-validators.md#default) 核心验证器：

```php
return [
    [['username', 'email'], 'trim'],
    [['username', 'email'], 'default'],
];
```

也还可以用更加通用的 [filter（滤镜）](tutorial-core-validators.md#filter) 
核心验证器来执行更加复杂的数据过滤。

如你所见，这些验证规则并不真的对输入数据进行任何验证。而是，对输入数据进行一些处理，
然后把它们存回当前被验证的模型特性。

下面的代码示例展示了对用户输入的完整处理，
这将确保只将整数值存储在一个属性中：

```php
['age', 'trim'],
['age', 'default', 'value' => null],
['age', 'integer', 'integerOnly' => true, 'min' => 0],
['age', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],
```

以上代码将对输入执行以下操作：

1. 从输入值中去除前后空白。
2. 确保空输入在数据库中存储为 `null`；我们区分 `未设置` 值和实际值为 `0` 之间的区别。
   如果值不允许为 `null`，则可以在此处设置另一个默认值。
3. 如果该值不为空，则验证该值是否为大于0的整数。大多数验证器的
   [[yii\validators\Validator::$skipOnEmpty|$skipOnEmpty]] 属性都被设置为`true`。
4. 确保该值为整数类型，例如将字符串 `'42'` 转换为整数 `42`。在这里，我们将 
[[yii\validators\FilterValidator::$skipOnEmpty|$skipOnEmpty]] 设置为 `true`，默认情况下，在 
[[yii\validators\FilterValidator|filter]] 验证器里这个属性是 `false`。

### 处理空输入 <span id="handling-empty-inputs"></span>

当输入数据是通过 HTML 表单，你经常会需要给空的输入项赋默认值。你可以通过调整
[default](tutorial-core-validators.md#default) 验证器来实现这一点。举例来说，

```php
return [
    // 若 "username" 和 "email" 为空，则设为 null
    [['username', 'email'], 'default'],

    // 若 "level" 为空，则设其为 1
    ['level', 'default', 'value' => 1],
];
```

默认情况下，当输入项为空字符串，空数组，或 null 时，会被视为“空值”。
你也可以通过配置[[yii\validators\Validator::isEmpty]] 
属性来自定义空值的判定规则。比如，

```php
    ['agree', 'required', 'isEmpty' => function ($value) {
        return empty($value);
    }]
```

> Note: 对于绝大多数验证器而言，若其 [[yii\base\Validator::skipOnEmpty]] 属性为默认值
  true，则它们不会对空值进行任何处理。也就是当他们的关联特性接收到空值时，相关验证会被直接略过。在
  [核心验证器](tutorial-core-validators.md) 之中，只有 `captcha`（验证码），`default`（默认值），
 `filter`（滤镜），`required`（必填），以及 `trim`（去首尾空格），这几个验证器会处理空输入。


## 临时验证 <span id="ad-hoc-validation"></span>

有时，你需要对某些没有绑定任何模型类的值进行 **临时验证**。

若你只需要进行一种类型的验证 (e.g. 验证邮箱地址)，你可以调用所需验证器的
[[yii\validators\Validator::validate()|validate()]] 方法。像这样：

```php
$email = 'test@example.com';
$validator = new yii\validators\EmailValidator();

if ($validator->validate($email, $error)) {
    echo '有效的 Email 地址。';
} else {
    echo $error;
}
```

> Note: 不是所有的验证器都支持这种形式的验证。比如 [unique（唯一性）](tutorial-core-validators.md#unique) 核心验证器就就是一个例子，
  它的设计初衷就是只作用于模型类内部的。

若你需要针对一系列值执行多项验证，你可以使用 [[yii\base\DynamicModel]]
。它支持即时添加特性和验证规则的定义。它的使用规则是这样的：

```php
public function actionSearch($name, $email)
{
    $model = DynamicModel::validateData(compact('name', 'email'), [
        [['name', 'email'], 'string', 'max' => 128],
        ['email', 'email'],
    ]);

    if ($model->hasErrors()) {
        // 验证失败
    } else {
        // 验证成功
    }
}
```

[[yii\base\DynamicModel::validateData()]] 方法会创建一个 `DynamicModel` 的实例对象，
并通过给定数据定义模型特性（以 `name` 和 `email` 为例），
之后用给定规则调用 [[yii\base\Model::validate()]] 方法。

除此之外呢，你也可以用如下的更加“传统”的语法来执行临时数据验证：

```php
public function actionSearch($name, $email)
{
    $model = new DynamicModel(compact('name', 'email'));
    $model->addRule(['name', 'email'], 'string', ['max' => 128])
        ->addRule('email', 'email')
        ->validate();

    if ($model->hasErrors()) {
        // 验证失败
    } else {
        // 验证成功
    }
}
```

验证之后你可以通过调用 [[yii\base\DynamicModel::hasErrors()|hasErrors()]]
方法来检查验证通过与否，并通过 [[yii\base\DynamicModel::errors|errors]]
属性获得验证的错误信息，过程与普通模型类一致。
你也可以访问模型对象内定义的动态特性，就像：
`$model->name` 和 `$model->email`。


## 创建验证器（Validators） <span id="creating-validators"></span>

除了使用 Yii 的发布版里所包含的[核心验证器](tutorial-core-validators.md)之外，你也可以创建你自己的验证器。
自定义的验证器可以是**行内验证器**，也可以是**独立验证器**。


### 行内验证器（Inline Validators） <span id="inline-validators"></span>

行内验证器是一种以模型方法或匿名函数的形式定义的验证器。
这些方法/函数的结构如下：

```php
/**
 * @param string $attribute 当前被验证的特性
 * @param array $params 以名-值对形式提供的额外参数
 * @param \yii\validators\InlineValidator $validator 相关的 InlineValidator 实例。
 * 此参数自版本 2.0.11 起可用。
 */
function ($attribute, $params)
```

若某特性的验证失败了，该方法/函数应该调用 [[yii\base\Model::addError()]] 保存错误信息到模型内。
这样这些错误就能在之后的操作中，被读取并展现给终端用户。

下面是一些例子：

```php
use yii\base\Model;

class MyForm extends Model
{
    public $country;
    public $token;

    public function rules()
    {
        return [
            // 定义为模型方法 validateCountry() 的行内验证器
            ['country', 'validateCountry'],

            // 定义为匿名函数的行内验证器
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

> Note: 从 2.0.11 版本开始，你可以用 [[yii\validators\InlineValidator::addError()]] 方法添加错误信息到模型里。用这种方法
> 的话，错误信息可以通过 [[yii\i18n\I18N::format()]] 格式化。 你还可以在错误信息里分别用 `{attribute}` 和 `{value}` 来引用
> 属性的名字（不必手动去写）和属性的值：

> ```php
> $validator->addError($this, $attribute, 'The value "{value}" is not acceptable for {attribute}.');
> ```

> Note: 缺省状态下，行内验证器不会在关联特性的输入值为空或该特性已经在其他验证中失败的情况下起效。
  若你想要确保该验证器始终启用的话，你可以在定义规则时，酌情将 
  [[yii\validators\Validator::skipOnEmpty|skipOnEmpty]] 以及
  [[yii\validators\Validator::skipOnError|skipOnError]]属性设为 false，比如，
>
> ```php
> [
>     ['country', 'validateCountry', 'skipOnEmpty' => false, 'skipOnError' => false],
> ]
> ```


### 独立验证器（Standalone Validators） <span id="standalone-validators"></span>

独立验证器是继承自 [[yii\validators\Validator]] 或其子类的类。你可以通过重写
[[yii\validators\Validator::validateAttribute()]] 来实现它的验证规则。若特性验证失败，可以调用
[[yii\base\Model::addError()]] 以保存错误信息到模型内，
操作与 [inline validators](#inline-validators) 所需操作完全一样。比如，


比如上述行内验证器也可以转移到新的验证类 [[components/validators/CountryValidator]]。
这种情况下，我们可以用 [[yii\validators\Validator::addError()]] 来给模型设置自定义的错误信息。

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

若你想要验证器支持不使用 model 的数据验证，你还应该重写[[yii\validators\Validator::validate()]] 方法。
你也可以通过重写[[yii\validators\Validator::validateValue()]] 方法替代
`validateAttribute()` 和 `validate()`，因为默认状态下，
后两者的实现是通过调用 `validateValue()` 实现的。

下面就是一个怎样把自定义验证器在模型中使用的例子。

```php
namespace app\models;

use Yii;
use yii\base\Model;
use app\components\validators\CountryValidator;

class EntryForm extends Model
{
    public $name;
    public $email;
    public $country;

    public function rules()
    {
        return [
            [['name', 'email'], 'required'],
            ['country', CountryValidator::class],
            ['email', 'email'],
        ];
    }
}
```


## 多属性验证 <span id="multiple-attributes-validation"></span>

某些情况下验证器可以包含多个属性。考虑下面的情况：

```php
class MigrationForm extends \yii\base\Model
{
    /**
     * 一个成年人的最少花销
     */
    const MIN_ADULT_FUNDS = 3000;
    /**
     * 一个孩子的最小花销
     */
    const MIN_CHILD_FUNDS = 1500;

    public $personalSalary;
    public $spouseSalary;
    public $childrenCount;
    public $description;

    public function rules()
    {
        return [
            [['personalSalary', 'description'], 'required'],
            [['personalSalary', 'spouseSalary'], 'integer', 'min' => self::MIN_ADULT_FUNDS],
            ['childrenCount', 'integer', 'min' => 0, 'max' => 5],
            [['spouseSalary', 'childrenCount'], 'default', 'value' => 0],
            ['description', 'string'],
        ];
    }
}
```

### 创建验证器 <span id="multiple-attributes-validator"></span>

比如我们需要检查下家庭收入是否足够给孩子们花销。此时我们可以创建一个行内验证器
`validateChildrenFunds` 来解决这个问题，它仅仅在 `childrenCount` 大于 0 的时候才去检查。

请注意，我们不要把所有需要验证的属性 (`['personalSalary', 'spouseSalary', 'childrenCount']`) 都附加到
验证器上。因为这样做同一个验证器将会对每个属性都执行一遍验证（总共三次），但是实际上我们只需要对整个属性集
执行一次验证而已。

你可以使用属性集合里的任何一个（或者使用你认为最相关的那个属性）：

```php
['childrenCount', 'validateChildrenFunds', 'when' => function ($model) {
    return $model->childrenCount > 0;
}],
```

`validateChildrenFunds` 的实现可以是下面这样的：

```php
public function validateChildrenFunds($attribute, $params)
{
    $totalSalary = $this->personalSalary + $this->spouseSalary;
    // Double the minimal adult funds if spouse salary is specified
    $minAdultFunds = $this->spouseSalary ? self::MIN_ADULT_FUNDS * 2 : self::MIN_ADULT_FUNDS;
    $childFunds = $totalSalary - $minAdultFunds;
    if ($childFunds / $this->childrenCount < self::MIN_CHILD_FUNDS) {
        $this->addError('childrenCount', 'Your salary is not enough for children.');
    }
}
```

你可以忽略 `$attribute` 参数，因为这个验证过程不仅仅关联一个属性。


### 添加错误信息 <span id="multiple-attributes-errors"></span>

在添加错误信息的时候，如果是多个属性，可以根据自己想要的格式使用多种情况：

- 选择一个你认为最相关的字段把错误信息添加到它的属性里：

```php
$this->addError('childrenCount', 'Your salary is not enough for children.');
```

- 选择多个相关的属性乃至所有属性给它们添加同样的错误信息。在使用 `addError` 之前我们可以先把错误信息存储到
一个独立的变量里，这样可以减少代码重复性。

```php
$message = 'Your salary is not enough for children.';
$this->addError('personalSalary', $message);
$this->addError('wifeSalary', $message);
$this->addError('childrenCount', $message);
```

或者使用循环：

```php
$attributes = ['personalSalary', 'wifeSalary', 'childrenCount'];
foreach ($attributes as $attribute) {
    $this->addError($attribute, 'Your salary is not enough for children.');
}
```

- 添加通用错误信息（不相关于特定的属性）。我们可以用一个不存在的属性名添加错误信息
比如 `*`，因为这时是不检查属性的存在性的。

```php
$this->addError('*', 'Your salary is not enough for children.');
```

这种情况下，我们不会在表单域里看到错误信息。为了展示这个错误信息，我们可以在视图里使用错误汇总：

```php
<?= $form->errorSummary($model) ?>
```

> Note: 创建一次验证多个属性的验证器的参考说明在这里 [community cookbook](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-validator-multiple-attributes.md).

## 客户端验证 <span id="client-side-validation"></span>

当终端用户通过 HTML 表单提供输入数据时，基于 JavaScript 的客户端验证是可取的，
因为它允许用户更快地找出输入错误，从而提供更好的用户体验。你可以尝试使用或者自己实现一个 *除了支持服务端验证* 之外
还支持客户端验证的验证器。

> Info: 尽管客户端验证是值得的，但它不是必须的。客户端验证的主要目的是给终端用户提供
  一个较好的体验。正如 “永远不用相信终端用户的输入数据” 一样，你也不能完全信任客户端验证。
  基于这个考虑的话，正如前文描述所说，你应该永远在服务端通过调用 [[yii\base\Model::validate()]] 方法
  进行服务端验证。


### 使用客户端验证 <span id="using-client-side-validation"></span>

许多 [核心验证器](tutorial-core-validators.md) 支持开箱即用的客户端验证。你需要做的
就是使用 [[yii\widgets\ActiveForm]] 构建你的 HTML 表单。 比如，下面的 `LoginForm` 声明了两个
规则：一个使用 [required](tutorial-core-validators.md#required) 核心验证器，它支持客户端的验证，也支持服务端的 
验证；另一个使用 `validatePassword` 行内验证器，它只支持在服务端
验证。

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
            // username 和 password 都是必填项
            [['username', 'password'], 'required'],

            // password 用 validatePassword() 方法验证
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

下面的代码构建了包含 `username` 和 `password` 两个表单项的 HTML 表单。
如果不输入任何内容直接提交表单，你就会发现提示你输入内容的错误信息立刻出现，
而这并没有和服务端交互。

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php yii\widgets\ActiveForm::end(); ?>
```

幕后的运作过程是这样的： [[yii\widgets\ActiveForm]] 读取在模型中声明的规则，然后
生成验证器支持客户端验证对应的 JavaScript 代码。当用户
改变表单项或者提交整个表单的时候，客户端验证的 JavaScript 就会触发。

如果你想完全关闭客户端验证，你可以设置
[[yii\widgets\ActiveForm::enableClientValidation]] 属性为 `false` 。你也可以通过设置它们的
[[yii\widgets\ActiveField::enableClientValidation]] 属性为 `false` 来单独关闭某一个表单项。
当在表单项级别和表单级别都设置了 `enableClientValidation` 的时候，
前者（表单项）的级别优先生效。

> Info: 从 2.0.11 版本开始，所有继承 [[yii\validators\Validator]] 的验证器都可以通过
> - [[yii\validators\Validator::getClientOptions()]] 这个单独的方法接收客户端选项。可以这样使用：

> - 如果你想自己实现自定义的客户端验证但是保留服务端的
> 验证器选项；
> - 继承或者自定义符合你特殊的需求：
>
> ```php
> public function getClientOptions($model, $attribute)
> {
>     $options = parent::getClientOptions($model, $attribute);
>     // 修改 $options 
>
>     return $options;
> }
> ```

### 实现客户端验证 <span id="implementing-client-side-validation"></span>

为了创建一个支持客户端验证的验证器，你应该实现
[[yii\validators\Validator::clientValidateAttribute()]] 方法，该方法返回一段 JavaScript 代码
用来在客户端执行验证。在这段 JavaScript 代码里，你可以使用下面几个
预定义的变量：

- `attribute`：被验证的属性名。
- `value`：被验证的值。
- `messages`：一个给属性保存验证错误信息的数组。
- `deferred`：一个支持添加 deferred 对象的数组（下一部分再说）。

下面的例子，我们创建了一个 `StatusValidator` 验证器，它用来验证一个输入和存在的状态相比， 
是否是有效的状态输入。这个验证器支持服务端验证也支持客户端验证。

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
if ($.inArray(value, $statuses) === -1) {
    messages.push($message);
}
JS;
    }
}
```

> Tip: 上面给出的代码主要展示如何支持客户端验证。在实际使用中，
> 你可以使用 [in](tutorial-core-validators.md#in) 核心验证器来实现同样的目标。你也可以
> 像下面那样写验证规则：
>
> ```php
> [
>     ['status', 'in', 'range' => Status::find()->select('id')->asArray()->column()],
> ]
> ```

> Tip: 如果你想手动调整客户端的验证，比如动态地添加表单项或者做一些自定义的 UI 逻辑，请参考
> [Working with ActiveForm via JavaScript](https://github.com/samdark/yii2-cookbook/blob/master/book/forms-activeform-js.md)
> 在 Yii 2.0 Cookbook。

### Deferred 验证 <span id="deferred-validation"></span>

如果你需要执行异步客户端验证，你可以创建 [Deferred objects](https://api.jquery.com/category/deferred-object/)。 
比如要执行一段自定义的 AJAX 验证，可以使用下面的代码：

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

上面这个 `deferred` 变量是由 Yii 提供的，它是一个 Deferred 对象的数组。这个 `$.get()`
jQuery 方法用来产生一个 Deferred 对象然后推送到 `deferred` 数组里。

你也可以明确地创建一个 `deferred` 对象，当异步回调触发的时候调用它的 `resolve()` 方法。
下面的例子展示了如何在客户端验证一个上传图片的尺寸。

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

> Note: `resolve()` 方法必须在所有属性都验证完之后调用。不然表单不会
  完成整体的验证流程。

为了简单起见，`deferred` 数组封装了一个快捷方法 `add()`，它可以自动创建 Deferred 对象
然后把它添加到 `deferred` 数组里。用这个方法，你可以简化上面的例子：

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


## AJAX 验证 <span id="ajax-validation"></span>

一些验证器只能工作在服务端，因为只有服务端才有必要的信息。
比如，验证一个用户名是否唯一，需要在服务端检查用户表。
这时候你可以使用基于 AJAX 的验证。它会在背后触发一个 AJAX 请求用来验证输入项而且还能
保持和通常客户端验证一样的用户体验。

给一个单独的表单项开启 AJAX 验证，你只需要设置 [[yii\widgets\ActiveField::enableAjaxValidation|enableAjaxValidation]]
属性为 `true`，然后指定一个唯一的表单 `id`：

```php
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'registration-form',
]);

echo $form->field($model, 'username', ['enableAjaxValidation' => true]);

// ...

ActiveForm::end();
```

如果要给所有的表单项开启 AJAX 验证，可以在表单级别设置 [[yii\widgets\ActiveForm::enableAjaxValidation|enableAjaxValidation]]
属性为 `true` 就行：

```php
$form = ActiveForm::begin([
    'id' => 'contact-form',
    'enableAjaxValidation' => true,
]);
```

> Note: 当在表单项级别和表单级别都设置了 `enableAjaxValidation` 属性的时候，
  前者（表单项级别）优先生效。

你也需要在服务端准备处理这样的 AJAX 请求。
这个可以在控制器的动作里通过如下的代码片段来实现：

```php
if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
    Yii::$app->response->format = Response::FORMAT_JSON;
    return ActiveForm::validate($model);
}
```

上述代码将会检测当前的请求是否源自 AJAX。如果是的话，它将运行验证过程, 然后
返回一段 JSON 格式的错误信息来响应这次请求。

> Info: 你也可以用 [Deferred 验证](#deferred-validation) 执行 AJAX 验证。 
  话说回来，这里描述的 AJAX 验证还是比较系统并且需要较少的代码开销。

当 `enableClientValidation` 和 `enableAjaxValidation` 都设置为 `true` 时，只有客户端验证成功之后
才会触发 AJAX 的验证请求。注意，如果验证某个表单项的时候凑巧 `validateOnChange`，`validateOnBlur` 或者 `validateOnType`
其中之一设置了 `true`,那么这个表单项在单独通过这样的客户端验证时，
也会发起 AJAX 请求。
