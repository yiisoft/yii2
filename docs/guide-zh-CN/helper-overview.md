Helpers
=======

> 注意：这部分正在开发中。

Yii 提供许多类来简化常见编码，如对字条串或数组的操作，
HTML 代码生成，等等。这些助手类被编写在命名空间 `yii\helpers` 下，并且
全是静态类 （就是说它们只包含静态属性和静态方法，而且不能实例化）。

可以通过调用其中一个静态方法来使用助手类，如下：

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> 注意：为了支持 [customizing helper classes](#customizing-helper-classes)，Yii 将每一个助手类
  分隔成两个类：一个基类 (e.g. `BaseArrayHelper`) 和一个 concrete 类 (e.g. `ArrayHelper`).
  当使用助手类时，应该仅使用 concrete 类版本而不使用基类。


Core Helper Classes
-------------------

Yii 发布版中提供以下核心助手类：

- [ArrayHelper](helper-array.md)
- Console
- FileHelper
- [Html](helper-html.md)
- HtmlPurifier
- Image
- Inflector
- Json
- Markdown
- Security
- StringHelper
- [Url](helper-url.md)
- VarDumper


Customizing Helper Classes <span id="customizing-helper-classes"></span>
--------------------------

To customize a core helper class (e.g. [[yii\helpers\ArrayHelper]]), you should create a new class extending
from the helpers corresponding base class (e.g. [[yii\helpers\BaseArrayHelper]]) and name your class the same
as the corresponding concrete class (e.g. [[yii\helpers\ArrayHelper]]), 包括它的命名空间。This class
will then be set up to replace the original implementation of the framework.

下面示例显示了如何自定义 [[yii\helpers\ArrayHelper]] 类的
[[yii\helpers\ArrayHelper::merge()|merge()]] 方法：

```php
<?php

namespace yii\helpers;

class ArrayHelper extends BaseArrayHelper
{
    public static function merge($a, $b)
    {
        // your custom implementation
    }
}
```

将你的类保存在一个名为 `ArrayHelper.php` 的文件中。该文件可以在任何目录，例如 `@app/components`。

Next, in your application's [entry script](structure-entry-scripts.md), add the following line of code
after including the `yii.php` file to tell the [Yii class autoloader](concept-autoloading.md) to load your custom
class instead of the original helper class from the framework:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

Note that customizing of helper classes is only useful if you want to change the behavior of an existing function
of the helpers. 如果你想为你的应用程序添加附加功能，最好为它创建一个单独的
助手类。
