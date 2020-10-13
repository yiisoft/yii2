助手类（Helpers）
==============

> Note: 这部分正在开发中。

Yii 提供许多类来简化常见编码，如对字条串或数组的操作，
HTML 代码生成，等等。这些助手类被编写在命名空间 `yii\helpers` 下，并且
全是静态类 （就是说它们只包含静态属性和静态方法，而且不能实例化）。

可以通过调用其中一个静态方法来使用助手类，如下：

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> Note: 为了支持 [自定义助手类](#customizing-helper-classes)，Yii 将每一个助手类
  分隔成两个类：一个基类 (例如 `BaseArrayHelper`) 和一个具体的类 (例如 `ArrayHelper`)。
  当使用助手类时，应该仅使用具体的类版本而不使用基类。


核心助手类（Core Helper Classes）
-----------------------------

Yii 发布版中提供以下核心助手类：

- [ArrayHelper](helper-array.md)
- Console
- FileHelper
- [Html](helper-html.md)
- HtmlPurifier
- Imagine（由 yii2-imagine 扩展提供）
- Inflector
- Json
- Markdown
- Security
- StringHelper
- [Url](helper-url.md)
- VarDumper


自定义助手类（Customizing Helper Classes） <span id="customizing-helper-classes"></span>
--------------------------------------

如果想要自定义一个核心助手类 (例如 [[yii\helpers\ArrayHelper]])，你应该创建一个新的类继承
helpers对应的基类 (例如 [[yii\helpers\BaseArrayHelper]]) 并同样的命
名你的这个类 (例如 [[yii\helpers\ArrayHelper]])，包括它的命名空间。这个类
会用来替换框架最初的实现。

下面示例显示了如何自定义 [[yii\helpers\ArrayHelper]] 类的
[[yii\helpers\ArrayHelper::merge()|merge()]] 方法：

```php
<?php

namespace yii\helpers;

class ArrayHelper extends BaseArrayHelper
{
    public static function merge($a, $b)
    {
        // 你自定义的实现
    }
}
```

将你的类保存在一个名为 `ArrayHelper.php` 的文件中。该文件可以在任何目录，例如 `@app/components`。

接下来，在你的应用程序 [入口脚本](structure-entry-scripts.md) 处，在引入的 `yii.php` 文件后面
添加以下代码行，用 [Yii 自动加载器](concept-autoloading.md) 来加载自定义类
代替框架的原始助手类：

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

注意，自定义助手类仅仅用于如果你想要更改助手类中
现有的函数的行为。如果你想为你的应用程序添加附加功能，最好为它创建一个单独的
助手类。
