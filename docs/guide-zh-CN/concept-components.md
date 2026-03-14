组件（Component）
==============

组件是 Yii 应用的主要基石。是 [[yii\base\Component]] 类或其子类的实例。
三个用以区分它和其它类的主要功能有：

* [属性（Property）](concept-properties.md)
* [事件（Event）](concept-events.md)
* [行为（Behavior）](concept-behaviors.md)
 
或单独使用，或彼此配合，这些功能的应用让 Yii 的类变得更加灵活和易用。
以小部件 [[yii\jui\DatePicker|日期选择器]] 来举例，
这是个方便你在 [视图](structure-view.md) 中生成一个交互式日期选择器的 UI 组件：

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

这个小部件继承自 [[yii\base\Component]]，它的各项属性改写起来会很容易。

正是因为组件功能的强大，他们比常规的对象（Object）稍微重量级一点，因为他们要使用额外的内存和 CPU 时间来处理
[事件](concept-events.md) 和 [行为](concept-behaviors.md) 。
如果你不需要这两项功能，可以继承 [[yii\base\Object]] 
而不是 [[yii\base\Component]]。这样组件可以像普通 PHP 对象一样高效，
同时还支持[属性（Property）](concept-properties.md)功能。

当继承 [[yii\base\Component]] 或 [[yii\base\Object]] 时，
推荐你使用如下的编码风格：

- 若你需要重写构造方法（Constructor），传入 `$config` 作为构造器方法*最后一个*参数，
  然后把它传递给父类的构造方法。
- 永远在你重写的构造方法*结尾处*调用一下父类的构造方法。
- 如果你重写了 [[yii\base\BaseObject::init()]] 方法，请确保你在 `init` 方法的*开头处*调用了父类的 `init` 方法。

例子如下：

```php
<?php

namespace yii\components\MyClass;

use yii\base\BaseObject;

class MyClass extends BaseObject
{
    public $prop1;
    public $prop2;

    public function __construct($param1, $param2, $config = [])
    {
        // ... 在应用配置之前初始化

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... 应用配置后进行初始化
    }
}
```

另外，为了让组件可以在创建实例时[能被正确配置](concept-configurations.md)，请遵照以下操作流程：

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// 方法二：
$component = \Yii::createObject([
    'class' => MyClass::class,
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> Info: 尽管调用 [[Yii::createObject()]] 的方法看起来更加复杂，但这主要因为它更加灵活强大，
> 它是基于[依赖注入容器](concept-di-container.md)实现的。
  

[[yii\base\BaseObject]] 类执行时的生命周期如下：

1. 构造方法内的预初始化过程。你可以在这儿给各属性设置缺省值。
2. 通过 `$config` 配置对象。配置的过程可能会覆盖掉先前在构造方法内设置的默认值。
3. 在 [[yii\base\BaseObject::init()|init()]] 方法内进行初始化后的收尾工作。你可以通过重写此方法，进行一些良品检验，属性的初始化之类的工作。
4. 对象方法调用。

前三步都是在对象的构造方法内发生的。这意味着一旦你获得了一个对象实例（即一个对象），
那么它就已经初始化就绪可供使用。
