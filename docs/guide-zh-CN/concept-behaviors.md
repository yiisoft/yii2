行为
===

行为是 [[yii\base\Behavior]] 或其子类的实例。
行为，也称为 [mixins](https://zh.wikipedia.org/wiki/Mixin)，
可以无须改变类继承关系即可增强一个已有的 [[yii\base\Component|组件]] 类功能。
当行为附加到组件后，它将“注入”它的方法和属性到组件，
然后可以像访问组件内定义的方法和属性一样访问它们。
此外，行为通过组件能响应被触发的[事件](basic-events.md)，从而自定义或调整组件正常执行的代码。


定义行为 <span id="defining-behaviors"></span>
------

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

以上代码定义了行为类 `app\components\MyBehavior` 并为要附加行为的组件提供了两个属性 `prop1` 、 `prop2` 和一个方法 `foo()` 。
注意属性 `prop2` 是通过 getter `getProp2()` 和 setter `setProp2()` 定义的。
能这样用是因为 [[yii\base\Object]] 是 [[yii\base\Behavior]] 的祖先类，此祖先类支持用 getter 和 setter 方法定义[属性](basic-properties.md)

因为这是一个行为类，当它附加到一个组件时，该组件也将具有 `prop1` 和 `prop2` 属性和 `foo()` 方法。

> Tip: 在行为内部可以通过 [[yii\base\Behavior::owner]] 属性访问行为已附加的组件。

> Note: 如果 [[yii\base\Behavior::__get()]] 和/或 [[yii\base\Behavior::__set()]] 行为方法被覆盖，
> 则需要覆盖 [[yii\base\Behavior::canGetProperty()]] 和/或 [[yii\base\Behavior::canSetProperty()]]。

处理事件
-------

如果要让行为响应对应组件的事件触发，
就应覆写 [[yii\base\Behavior::events()]] 方法，如：

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

[[yii\base\Behavior::events()|events()]] 方法返回事件列表和相应的处理器。
上例声明了 [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] 事件和它的处理器 `beforeValidate()` 。
当指定一个事件处理器时，要使用以下格式之一：

* 指向行为类的方法名的字符串，如上例所示；
* 对象或类名和方法名的数组，如 `[$object, 'methodName']`；
* 匿名方法。

处理器的格式如下，其中 `$event` 指向事件参数。
关于事件的更多细节请参考[事件](basic-events.md)：

```php
function ($event) {
}
```

附加行为 <span id="attaching-behaviors"></span>
----------

可以静态或动态地附加行为到[[yii\base\Component|组件]]。前者在实践中更常见。

要静态附加行为，覆写行为要附加的组件类的 [[yii\base\Component::behaviors()|behaviors()]] 方法即可。
[[yii\base\Component::behaviors()|behaviors()]] 方法应该返回行为[配置](basic-configs.md)列表。
每个行为配置可以是行为类名也可以是配置数组。如：

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
            MyBehavior::class,

            // 命名行为，只有行为类名
            'myBehavior2' => MyBehavior::class,

            // 匿名行为，配置数组
            [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],

            // 命名行为，配置数组
            'myBehavior4' => [
                'class' => MyBehavior::class,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ]
        ];
    }
}
```

通过指定行为配置数组相应的键可以给行为关联一个名称。这种行为称为**命名行为**。
上例中，有两个命名行为：`myBehavior2` 和 `myBehavior4` 。如果行为没有指定名称就是**匿名行为**。


要动态附加行为，在对应组件里调用 [[yii\base\Component::attachBehavior()]] 方法即可，如：

```php
use app\components\MyBehavior;

// 附加行为对象
$component->attachBehavior('myBehavior1', new MyBehavior);

// 附加行为类
$component->attachBehavior('myBehavior2', MyBehavior::class);

// 附加配置数组
$component->attachBehavior('myBehavior3', [
    'class' => MyBehavior::class,
    'prop1' => 'value1',
    'prop2' => 'value2',
]);
```

可以通过 [[yii\base\Component::attachBehaviors()]] 方法一次附加多个行为：

```php
$component->attachBehaviors([
    'myBehavior1' => new MyBehavior,  // 命名行为
    MyBehavior::class,          // 匿名行为
]);
```

还可以通过[配置](concept-configurations.md)去附加行为：

```php
[
    'as myBehavior2' => MyBehavior::class,

    'as myBehavior3' => [
        'class' => MyBehavior::class,
        'prop1' => 'value1',
        'prop2' => 'value2',
    ],
]
```

详情请参考
[配置](concept-configurations.md#configuration-format)章节。

使用行为 <span id="using-behaviors"></span>
-------

使用行为，必须像前文描述的一样先把它附加到 [[yii\base\Component|component]] 类或其子类。一旦行为附加到组件，就可以直接使用它。

行为附加到组件后，可以通过组件访问一个行为的**公共**成员变量
或 getter 和 setter 方法定义的[属性](concept-properties.md)：

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

如你所见，尽管 `$component` 未定义 `prop1` 和 `foo()` ，
它们用起来也像组件自己定义的一样。

如果两个行为都定义了一样的属性或方法，并且它们都附加到同一个组件，
那么**首先**附加上的行为在属性或方法被访问时有优先权。

附加行为到组件时的命名行为，可以使用这个名称来访问行为对象，
如下所示：

```php
$behavior = $component->getBehavior('myBehavior');
```

也能获取附加到这个组件的所有行为：

```php
$behaviors = $component->getBehaviors();
```


移除行为 <span id="detaching-behaviors"></span>
-------

要移除行为，可以调用 [[yii\base\Component::detachBehavior()]] 方法用行为相关联的名字实现：

```php
$component->detachBehavior('myBehavior1');
```

也可以移除*全部*行为：

```php
$component->detachBehaviors();
```


使用 `TimestampBehavior` <span id="using-timestamp-behavior"></span>
-----------------------

最后以 [[yii\behaviors\TimestampBehavior]] 的讲解来结尾，
这个行为支持在 [[yii\db\ActiveRecord|Active Record]] 
存储时自动更新它的时间戳属性。

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
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                // 'value' => new Expression('NOW()'),
            ],
        ];
    }
}
```

以上指定的行为数组：

* 当记录插入时，行为将当前时间戳赋值给 
  `created_at` 和 `updated_at` 属性；
* 当记录更新时，行为将当前时间戳赋值给 `updated_at` 属性。

> Note: 对于上述实现使用MySQL数据库，请将列 (`created_at`, `updated_at`) 定义为 int(11) 作为 UNIX 时间戳。

有了以上这段代码，如果你有一个 `User` 对象并且试图保存它，你会发现它的 `created_at` 和 `updated_at`
被当前的UNIX时间戳自动填充：

```php
$user = new User;
$user->email = 'test@example.com';
$user->save();
echo $user->created_at;  // 显示当前时间戳
```

[[yii\behaviors\TimestampBehavior|TimestampBehavior]] 行为还提供了一个有用的方法 
[[yii\behaviors\TimestampBehavior::touch()|touch()]]，
这个方法能将当前时间戳赋值给指定属性并保存到数据库：

```php
$user->touch('login_time');
```

其它行为
-------

有几种内置和外部行为可用：

- [[yii\behaviors\BlameableBehavior]] - 使用当前用户 ID 自动填充指定的属性。
- [[yii\behaviors\SluggableBehavior]] - 自动填充指定的属性，其值可以在 URL
  中用作 slug。
- [[yii\behaviors\AttributeBehavior]] - 在发生特定事件时自动为 ActiveRecord 对象的一个或多个属性
  指定一个指定的值。
- [yii2tech\ar\softdelete\SoftDeleteBehavior](https://github.com/yii2tech/ar-softdelete) - 提供软删除和软恢复 ActiveRecord 的
  方法。即将记录标记为已删除的设置标记或状态。
- [yii2tech\ar\position\PositionBehavior](https://github.com/yii2tech/ar-position) - 允许通过提供重新排序方法来
  管理整数字段中的记录顺序。

比较行为与 Traits <span id="comparison-with-traits"></span>
----------------------

虽然行为类似于 [traits](https://www.php.net/manual/zh/language.oop5.traits.php)，它们都将自己的属性和方法“注入”到主类中，
但它们在许多方面有所不同。如下所述，他们都有优点和缺点。
它们更像互补类而非替代类。


### 使用行为的原因 <span id="pros-for-behaviors"></span>

行为类像普通类支持继承。另一方面，traits 可以视为 PHP 语言支持的复制粘贴功能，
它不支持继承。

行为无须修改组件类就可动态附加到组件或移除。
要使用 traits，必须修改使用它的类。

行为是可配置的，而 traits 则不可行。

行为可以通过响应事件来定制组件的代码执行。

当附属于同一组件的不同行为之间可能存在名称冲突时，
通过优先考虑附加到该组件的行为，
自动解决冲突。由不同 traits 引起的名称冲突需要通过
重命名受影响的属性或方法进行手动解决。


### 使用 Traits 的原因 <span id="pros-for-traits"></span>

Traits 比行为更有效，因为行为是既需要时间又需要内存的对象。

因为 IDE 是一种本地语言结构，所以它们对 Traits 更友好。

