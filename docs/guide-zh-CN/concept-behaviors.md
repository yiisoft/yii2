行为
=========

行为是 [[yii\base\Behavior]] 或其子类的实例。行为，也称为 [mixins](http://en.wikipedia.org/wiki/Mixin)，可以无须改变类继承关系即可增强一个已有的 [[yii\base\Component|组件]] 类功能。当行为附加到组件后，它将“注入”它的方法和属性到组件，然后可以像访问组件内定义的方法和属性一样访问它们。此外，行为通过组件能响应被触发的[事件](basic-events.md)，从而自定义或调整组件正常执行的代码。


定义行为
-----------

要定义行为，通过继承 [[yii\base\Behavior]] 或其子类来建立一个类。如：

```php
namespace app\components;

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

以上代码定义了行为类 `app\components\MyBehavior` 并为要附加行为的组件提供了两个属性 `prop1` 、 `prop2` 和一个方法 `foo()` 。注意属性 `prop2` 是通过 getter `getProp2()` 和 setter `setProp2()` 定义的。能这样用是因为 [[yii\base\BaseObject]] 是 [[yii\base\Behavior]] 的祖先类，此祖先类支持用 getter 和 setter 方法定义[属性](basic-properties.md)

> Tip: 在行为内部可以通过 [[yii\base\Behavior::owner]] 属性访问行为已附加的组件。


处理事件
-------------

如果要让行为响应对应组件的事件触发，就应覆写 [[yii\base\Behavior::events()]] 方法，如：

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // 其它代码

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // 处理器方法逻辑
    }
}
```

[[yii\base\Behavior::events()|events()]] 方法返回事件列表和相应的处理器。上例声明了 [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] 事件和它的处理器 `beforeValidate()` 。当指定一个事件处理器时，要使用以下格式之一：

* 指向行为类的方法名的字符串，如上例所示；
* 对象或类名和方法名的数组，如 `[$object, 'methodName']`；
* 匿名方法。

处理器的格式如下，其中 `$event` 指向事件参数。关于事件的更多细节请参考[事件](basic-events.md)：

```php
function ($event) {
}
```


附加行为
----------

可以静态或动态地附加行为到[[yii\base\Component|组件]]。前者在实践中更常见。

要静态附加行为，覆写行为要附加的组件类的 [[yii\base\Component::behaviors()|behaviors()]] 方法即可。[[yii\base\Component::behaviors()|behaviors()]] 方法应该返回行为[配置](basic-configs.md)列表。每个行为配置可以是行为类名也可以是配置数组。如：

```php
namespace app\models;

use yii\db\ActiveRecord;
use app\components\MyBehavior;

class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            // 匿名行为，只有行为类名
            MyBehavior::className(),

            // 命名行为，只有行为类名
            'myBehavior2' => MyBehavior::className(),

            // 匿名行为，配置数组
            [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // 命名行为，配置数组
            'myBehavior4' => [
                'class' => MyBehavior::className(),
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

通过指定行为配置数组相应的键可以给行为关联一个名称。这种行为称为**命名行为**。上例中，有两个命名行为：`myBehavior2` 和 `myBehavior4` 。如果行为没有指定名称就是**匿名行为**。

要动态附加行为，在对应组件里调用 [[yii\base\Component::attachBehavior()]] 方法即可，如：

```php
use app\components\MyBehavior;

// 附加行为对象
$component->attachBehavior('myBehavior1', new MyBehavior);

// 附加行为类
$component->attachBehavior('myBehavior2', MyBehavior::className());

// 附加配置数组
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::className(),
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```

可以通过 [[yii\base\Component::attachBehaviors()]] 方法一次附加多个行为：

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior,  // 命名行为
    MyBehavior::className(),          // 匿名行为
]);
```

还可以通过[配置](concept-configurations.md)去附加行为：

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

详情请参考[配置](concept-configurations.md#configuration-format)章节。


使用行为
---------------

使用行为，必须像前文描述的一样先把它附加到 [[yii\base\Component|component]] 类或其子类。一旦行为附加到组件，就可以直接使用它。

行为附加到组件后，可以通过组件访问一个行为的**公共**成员变量或 getter 和 setter 方法定义的[属性](concept-properties.md)：

```php
// "prop1" 是定义在行为类的属性
echo $component->prop1;
$component->prop1 = $value;
```

类似地也可以调用行为的**公共**方法：

```php
// foo() 是定义在行为类的公共方法
$component->foo();
```

如你所见，尽管 `$component` 未定义 `prop1` 和 `foo()` ，它们用起来也像组件自己定义的一样。

如果两个行为都定义了一样的属性或方法，并且它们都附加到同一个组件，那么**首先**附加上的行为在属性或方法被访问时有优先权。

附加行为到组件时的命名行为，可以使用这个名称来访问行为对象，如下所示：

```php
$behavior = $component->getBehavior('myBehavior');
```

也能获取附加到这个组件的所有行为：

```php
$behaviors = $component->getBehaviors();
```


移除行为
------------

要移除行为，可以调用 [[yii\base\Component::detachBehavior()]] 方法用行为相关联的名字实现：

```php
$component->detachBehavior('myBehavior1');
```

也可以移除**全部**行为：

```php
$component->detachBehaviors();
```


使用 `TimestampBehavior`
----------------------------

最后以 [[yii\behaviors\TimestampBehavior]] 的讲解来结尾，这个行为支持在 [[yii\db\ActiveRecord|Active Record]] 存储时自动更新它的时间戳属性。

首先，附加这个行为到计划使用该行为的 [[yii\db\ActiveRecord|Active Record]] 类：

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

以上指定的行为数组：

* 当记录插入时，行为将当前时间戳赋值给 `created_at` 和 `updated_at` 属性；
* 当记录更新时，行为将当前时间戳赋值给 `updated_at` 属性。


保存 `User` 对象，将会发现它的 `created_at` 和 `updated_at` 属性自动填充了当前时间戳：

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // 显示当前时间戳
```

[[yii\behaviors\TimestampBehavior|TimestampBehavior]] 行为还提供了一个有用的方法 [[yii\behaviors\TimestampBehavior::touch()|touch()]]，这个方法能将当前时间戳赋值给指定属性并保存到数据库：

```php
$user->touch('login_time');
```


与 PHP traits 的比较
-------------------

尽管行为在 "注入" 属性和方法到主类方面类似于 [traits](http://www.php.net/traits) ，它们在很多方面却不相同。如上所述，它们各有利弊。它们更像是互补的而不是相互替代。


### 行为的优势

行为类像普通类支持继承。另一方面，traits 可以视为 PHP 语言支持的复制粘贴功能，它不支持继承。

行为无须修改组件类就可动态附加到组件或移除。要使用 traits，必须修改使用它的类。

行为是可配置的而 traits 不能。

行为以响应事件来自定义组件的代码执行。

当不同行为附加到同一组件产生命名冲突时，这个冲突通过先附加行为的优先权自动解决。而由不同 traits 引发的命名冲突需要通过手工重命名冲突属性或方法来解决。


### traits 的优势

traits 比起行为更高效，因为行为是对象，消耗时间和内存。

IDE 对 traits 更友好，因为它们是语言结构。


