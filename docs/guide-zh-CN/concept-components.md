组件（Component）
==========

组件是 Yii 应用的主要基石之一。组件是 [[yii\base\Component]] 类或其子类的实例。三个它能提供，其他类不能的主要功能有：

* [属性（Property）](concept-properties.md)
* [事件（Event）](concept-events.md)
* [行为（Behavior）](concept-behaviors.md)
 
或单独使用，或彼此配合，总之这些功能的应用让 Yii 的类变得更加灵活和易用。就拿一个叫 [[yii\jui\DatePicker|日期选择器]]
的小部件来举例吧，这是个方便你在 [视图](structure-view.md) 中生成一个交互式日期选择器的 UI 组件，你们自己看这样的调用方式是不是很屌：

```php
use yii\jui\DatePicker;

echo DatePicker::widget([
    'language' => 'zh-CN',
    'name'  => 'country',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]);
```

正因为这个小部件继承自 [[yii\base\Component]]，所以它的各项属性改写起来就会很容易……

虽然组件非常屌爆，但是他们比常规的对象（Object）要稍微重量级一点点，因为他们要使用额外的内存和 CPU 时间来支持这些功能，尤其是
[事件](concept-events.md) 和 [行为](concept-behaviors.md) 这俩货。如果你的组件不需要这两项功能，你可以考虑继承 [[yii\base\Object]]
而不是 [[yii\base\Component]]。这样一来，你的组件就可以像普通 PHP 对象一样高效了。同时，它还依旧支持[属性（Property）](concept-properties.md)功能！

当你继承 [[yii\base\Component]] 或 [[yii\base\Object]] 时，我们推荐你使用如下的编码风格：

- 若你需要重写构造器（Constructor），指定一个 `$config` 参数，作为构造器的 *最后一个* 参数，然后把它传递给父类的构造器。（译者注：`parent::__construct($config = [])`，用于把属性配置信息传递回父类。可选参数放最后是 PSR 的规范之一）
- 永远在你重写的构造器 *结尾处* 调用一下父类的构造器。
- 如果你重写了 [[yii\base\Object::init()]] 方法，请确保你在 `init` 方法的 *开头处* 调用了父类的 `init` 方法。

例子如下：

```php
namespace yii\components\MyClass;

use yii\base\Object;

class MyClass extends Object
{
    public $prop1;
    public $prop2;

    public function __construct($param1, $param2, $config = [])
    {
        // ... 配置生效前的初始化过程

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... 配置生效后的初始化过程
    }
}
```

另外，为了让你的组件可以在创建实例时[能被正确配置](concept-configurations.md)，请遵照以下操作流程。举例：

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// 方法二：
$component = \Yii::createObject([
    'class' => MyClass::className(),
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> 补充：虽然调用 [[Yii::createObject()]] 的方法看起来更加复杂，但是这主要是因为它更加灵活强大，这货是基于高大上的[依赖注入容器](concept-di-container.md)的一种实现。
  

每个 [[yii\base\Object]] 类的生命周期是这样度过的：

1. 构造器内的预初始化过程。你可以在这儿给各属性设置缺省值。
2. 通过 `$config` 配置对象。配置的过程可能会覆盖掉先前在构造器内设置的默认值。
3. 在 [[yii\base\Object::init()|init()]] 方法内进行初始化的收尾工作。你可以通过重写此方法，进行一些良品检验呀，属性的标准化呀，之类的事情。
4. 对象方法调用。

前三步都是在对象的构造器内发生的。这意味着一旦你获得了一个对象实例，那么它已经初始化为了一个妥妥的状态，放心大胆的用吧。
