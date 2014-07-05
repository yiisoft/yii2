行为（Behavior）
=========

行为（Behavior）是 [[yii\base\Behmixinsavior]] 或其子类的实例。 Behavior 也被称为
[Mixin（Mix In可以理解为包含若干个类的部分方法和属性的混合类，英文维基）](http://en.wikipedia.org/wiki/Mixin)，允许你增强已有
[[yii\base\Component|组件]] 类的功能，而无需改变类的继承结构。当一个行为被配属到组件之上是，他会向组件“注入”他的属性与方法，就好像这些方法原本就定义在组件里一样。此外，行为能响应由组件所触发的[事件](basic-events.md)，从而自定义或调整组件内默认的代码执行。

> 译者注：mixin直译为‘混入’，trait直译为‘特质’，为避免翻译上的问题，今后我们还是采用英文术语。


使用行为 <a name="using-behaviors"></a>
---------------

要使用行为，你首先需要把它配属到某个[[yii\base\Component|组件]]上。我们会在接下来的章节内讲解如何配属一个行为。

行为被配属到组件之后，它的用法是很直截了当的。

可以通过行为所配属的组件，访问它的 *public* 成员变量或由 getter 和/或 setter 定义的[属性](concept-properties.md)，就像这样：

```php
// "prop1" 是一个定义在行为类中的属性
echo $component->prop1;
$component->prop1 = $value;
```

与之相似的，你也可以调用行为类的 *public* 方法，

```php
// foo() 是一个定义在行为类中的公共方法
$component->foo();
```

如你所见，尽管 `$component` 并没有定义 `prop1` 和 `bar()`，它们依旧好像是组件自身定义的一部分一样。

如果两个行为都定义了一样的属性或方法，并且它们都配属到同一个组件，那么先附加上的行为在属性或方法被访问时就有优先权。

当行为配属到组件时可以关联一个行为名。此时就能使用这个名称来访问行为对象，如下所示：

```php
$behavior = $component->getBehavior('myBehavior');
```

也能获取组件所配属的所有行为：

```php
$behaviors = $component->getBehaviors();
```


配属行为 <a name="attaching-behaviors"></a>
-------------------

可以选择静态或动态地配属行为到 [[yii\base\Component|组件]]。在具体实践中，前者更常见。

要静态配属行为，重写目标组件类的 [[yii\base\Component::behaviors()|behaviors()]] 方法即可。如：

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // 匿名行为 => 行为类名
            MyBehavior::className(),

            // 命名行为 => 行为类名
            'myBehavior2' => MyBehavior::className(),

            // 匿名行为 => 配置数组
            [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // 命名行为 => 配置数组
            'myBehavior4' => [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

[[yii\base\Component::behaviors()|behaviors()]] 方法应该返回一个包含所有行为[配置信息](concept-configurations.md)的列表。每个行为的配置信息可以是行为的类名也可以是其配置数组。

通过为行为配置信息指定相应的键名，可以给行为关联一个名称。这种行为称为**命名行为**。在上例中存在两个命名行为：`myBehavior2` 和 `myBehavior4` 。同理如果行为没有关联名称就是**匿名行为**。

要动态地配属行为，只需调用目标组件的 [[yii\base\Component::attachBehavior()]] 方法即可，如：

```php
use app\components\MyBehavior;

// 配属一个行为对象
$component->attachBehavior('myBehavior1', new MyBehavior);

// 配属行为类
$component->attachBehavior('myBehavior2', MyBehavior::className());

// 配属一个配置数组
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::className(),
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```

你也可以通过 [[yii\base\Component::attachBehaviors()]] 方法一次性配属多个行为。比如：

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior,  // 一个命名行为
    MyBehavior::className(),          // 一个匿名行为
]);
```

如下所示，你也可以通过[配置数组](concept-configurations.md)配属行为。更多细节，请参考[配置（Configs）](concept-configurations.md#configuration-format)章节。

```php
[
    'as myBehavior2' => MyBehavior::className(),

    'as myBehavior3' => [
        'class' => MyBehavior::className(),
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```


拆卸行为 <a name="detaching-behaviors"></a>
-------------------

要拆卸行为，可以用行为的键名调用 [[yii\base\Component::detachBehavior()]] 方法：

```php
$component->detachBehavior('myBehavior1');
```

也可以一次性拆卸掉**所有**的行为：

```php
$component->detachBehaviors();
```


定义行为 <a name="defining-behaviors"></a>
------------------

要定义一个行为，只需创建新类，继承 [[yii\base\Behavior]] 或其子类。比如，

```php
namespace app\components;

use yii\base\Model;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    public $prop1;

    private $_prop2;

    public function getProp2()
    {
        return $this->_prop2;
    }

    public function setProp2($value)
    {
        $this->_prop2 = $value;
    }

    public function foo()
    {
        // ...
    }
}
```

以上代码定义了行为类 `app\components\MyBehavior` 并为要附加行为的组件提供了两个属性 `prop1` 、 `prop2` 和一个方法 `foo()`。注意，属性 `prop2` 是通过 getter `getProp2()` 和 setter `setProp2()` 定义的。能这样用是因为 [[yii\base\Behavior]] 的祖先类是 [[yii\base\Object]]，此祖先类支持用 getter 和 setter 方法定义[属性](concept-properties.md)。

在行为类之中，你可以通过 [[yii\base\Behavior::owner]] 属性访问行为的组件。

如果你的行为需要响应其所配属的组件中触发的事件，它需要重写 [[yii\base\Behavior::events()]] 方法。像这样，

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```

[[yii\base\Behavior::events()|events()]] 方法需返回一个事件列表和它们相应的处理器。上例声明了 [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] 事件和它的处理器 `beforeValidate()` 。当指定一个事件处理器时，要使用以下格式之一：

* 一条指向行为类的方法名的字符串，如上例所示；
* 一个包含对象或类名，以及方法名的数组，如 `[$object, 'methodName']`；
* 一个匿名函数

事件处理器方法的特征格式如下，其中 `$event` 指向事件参数。关于事件的更多细节请参考[事件](concept-events.md)。

```php
function ($event) {
}
```


使用 `TimestampBehavior` <a name="using-timestamp-behavior"></a>
-------------------------

最后让我们来看看 [[yii\behaviors\TimestampBehavior]] 作为具体实践案例，这个行为支持在 [[yii\db\ActiveRecord|Active Record]] 保存时自动更新它的时间戳类型的 attribute（特性）。

首先，配属这个行为到目标 [[yii\db\ActiveRecord|Active Record]] 类：

```php
namespace app\models\User;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class User extends ActiveRecord
{
    // ...

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
}
```

以上行为的配置指定了下面的两条规则：

* 当记录插入时，行为应该要当前时间戳赋值给 `created_at` 和 `updated_at` 属性；
* 当记录更新时，行为应该要当前时间戳赋值给 `updated_at` 属性。

放置上述代码之后，如果有一个 `User` 对象且需要保存，你会发现它的 `created_at` 和 `updated_at` 属性已经自动填充了当前时间戳：

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // shows the current timestamp
```

[[yii\behaviors\TimestampBehavior|TimestampBehavior]] 同时提供一个很好用的名为 [[yii\behaviors\TimestampBehavior::touch()|touch()]] 的方法，该方法会把当前时间戳赋值给指定 attribute 并将其存入数据库：

```php
$user->touch('login_time');
```


与 Traits 的对比 <a name="comparison-with-traits"></a>
----------------------

尽管行为在 "注入" 属性和方法到主类方面类似于 [traits](http://www.php.net/traits) ，它们在很多方面却不相同。如上所述，它们各有利弊。它们更像是互补的而不是相互替代。


### Behavior 的优势 <a name="pros-for-behaviors"></a>

Behavior 类像普通类支持继承。另一方面，Traits 可以视之为一种 PHP 提供语言级支持的复制粘贴功能，它不支持继承。

Behavior 无须修改组件类就可动态配属到组件或拆除。要使用 trait，就必须修改引用它的类本身。

Behavior 是可配置的而 traits 不行。

Behaviors 可以通过响应事件来自定义组件代码的执行。

当不同 Behavior 附加到同一组件产生命名冲突时，这个冲突会以“先附加行为的优先”的方式自动解决。而由不同 traits 引发的命名冲突需要通过手工重命名冲突属性或方法来解决。


### Traits 的优势 <a name="pros-for-traits"></a>

Trait 比 behaviors 性能好很多很多，因为行为本身就是对象，他需要占用双倍的时间和内存。

作为语言架构的一部分，IDE 对 Trait 更加友好

