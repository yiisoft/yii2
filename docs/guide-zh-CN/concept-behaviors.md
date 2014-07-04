行为（Behavior）
=========

行为（Behavior）是 [[yii\base\Behmixinsavior]] 或其子类的实例。 Behavior 也被称为
[Mixin（Mix In可以理解为包含若干个类的部分方法和属性的混合类，英文维基）](http://en.wikipedia.org/wiki/Mixin)，允许你增强已有
[[yii\base\Component|组件]] 类的功能，而无需改变类的继承结构。当一个行为被配属到组件之上是，他会向组件“注入”他的属性与方法，就好像这些方法原本就定义在组件里一样。此外，行为能响应由组件所触发的[事件](basic-events.md)，从而自定义或调整组件内默认的代码执行。


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
// bar() 是一个定义在行为类中的公共方法
$component->bar();
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

也可以一次性拆卸掉**所有的**行为：

```php
$component->detachBehaviors();
```


定义行为 <a name="defining-behaviors"></a>
------------------

To define a behavior, create a class by extending from [[yii\base\Behavior]] or its child class. For example,

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

The above code defines the behavior class `app\components\MyBehavior` which will provide two properties
`prop1` and `prop2`, and one method `foo()` to the component it is attached to. Note that property `prop2`
is defined via the getter `getProp2()` and the setter `setProp2()`. This is so because [[yii\base\Object]]
is an ancestor class of [[yii\base\Behavior]], which supports defining [properties](concept-properties.md) by getters/setters.

Within a behavior, you can access the component that the behavior is attached to through the [[yii\base\Behavior::owner]] property.

If a behavior needs to respond to the events triggered by the component it is attached to, it should override the
[[yii\base\Behavior::events()]] method. For example,

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

The [[yii\base\Behavior::events()|events()]] method should return a list of events and their corresponding handlers.
The above example declares that the [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] event and
its handler `beforeValidate()`. When specifying an event handler, you may use one of the following formats:

* a string that refers to the name of a method of the behavior class, like the example above;
* an array of an object or class name, and a method name, e.g., `[$object, 'methodName']`;
* an anonymous function.

The signature of an event handler should be as follows, where `$event` refers to the event parameter. Please refer
to the [Events](concept-events.md) section for more details about events.

```php
function ($event) {
}
```


使用 `TimestampBehavior` <a name="using-timestamp-behavior"></a>
-------------------------

To wrap up, let's take a look at [[yii\behaviors\TimestampBehavior]] - a behavior that supports automatically
updating the timestamp attributes of an [[yii\db\ActiveRecord|Active Record]] when it is being saved.

First, attach this behavior to the [[yii\db\ActiveRecord|Active Record]] class that you plan to use.

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

The behavior configuration above specifies that

* when the record is being inserted, the behavior should assign the current timestamp to
  the `created_at` and `updated_at` attributes;
* when the record is being updated, the behavior should assign the current timestamp to the `updated_at` attribute.

Now if you have a `User` object and try to save it, you will find its `created_at` and `updated_at` are automatically
filled with the current timestamp:

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // shows the current timestamp
```

The [[yii\behaviors\TimestampBehavior|TimestampBehavior]] also offers a useful method
[[yii\behaviors\TimestampBehavior::touch()|touch()]] which will assign the current timestamp
to a specified attribute and save it to the database:

```php
$user->touch('login_time');
```


与 Traits（特质）的对比 <a name="comparison-with-traits"></a>
----------------------

While behaviors are similar to [traits](http://www.php.net/traits) in that they both "inject" their
properties and methods to the primary class, they differ in many aspects. As explained below, they
both have pros and cons. They are more like complements rather than replacements to each other.


### Behavior 的好处 <a name="pros-for-behaviors"></a>

Behavior classes, like normal classes, support inheritance. Traits, on the other hand,
can be considered as language-supported copy and paste. They do not support inheritance.

Behaviors can be attached and detached to a component dynamically without requiring you to modify the component class.
To use a trait, you must modify the class using it.

Behaviors are configurable while traits are not.

Behaviors can customize the code execution of a component by responding to its events.

When there is name conflict among different behaviors attached to the same component, the conflict is
automatically resolved by respecting the behavior that is attached to the component first.
Name conflict caused by different traits requires you to manually resolve it by renaming the affected
properties or methods.


### Traits 的好处 <a name="pros-for-traits"></a>

Traits are much more efficient than behaviors because behaviors are objects which take both time and memory.

IDEs are more friendly to traits as they are language construct.

