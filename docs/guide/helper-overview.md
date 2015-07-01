Helpers
=======

> Note: This section is under development.

Yii provides many classes that help simplify common coding tasks, such as string or array manipulations,
HTML code generation, and so on. These helper classes are organized under the `yii\helpers` namespace and
are all static classes (meaning they contain only static properties and methods and should not be instantiated).

You use a helper class by directly calling one of its static methods, like the following:

```php
use yii\helpers\Html;

echo Html::encode('Test > test');
```

> Note: To support [customizing helper classes](#customizing-helper-classes), Yii breaks each core helper class
  into two classes: a base class (e.g. `BaseArrayHelper`) and a concrete class (e.g. `ArrayHelper`).
  When you use a helper, you should only use the concrete version and never use the base class.


Core Helper Classes
-------------------

The following core helper classes are provided in the Yii releases:

- [ArrayHelper](helper-array.md)
- Console
- FileHelper
- FormatConverter
- [Html](helper-html.md)
- HtmlPurifier
- Imagine (provided by yii2-imagine extension)
- Inflector
- Json
- Markdown
- StringHelper
- [Url](helper-url.md)
- VarDumper


Customizing Helper Classes <span id="customizing-helper-classes"></span>
--------------------------

To customize a core helper class (e.g. [[yii\helpers\ArrayHelper]]), you should create a new class extending
from the helpers corresponding base class (e.g. [[yii\helpers\BaseArrayHelper]]) and name your class the same
as the corresponding concrete class (e.g. [[yii\helpers\ArrayHelper]]), including its namespace. This class
will then be set up to replace the original implementation of the framework.

The following example shows how to customize the [[yii\helpers\ArrayHelper::merge()|merge()]] method of the
[[yii\helpers\ArrayHelper]] class:

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

Save your class in a file named `ArrayHelper.php`. The file can be in any directory, for example `@app/components`.

Next, in your application's [entry script](structure-entry-scripts.md), add the following line of code
after including the `yii.php` file to tell the [Yii class autoloader](concept-autoloading.md) to load your custom
class instead of the original helper class from the framework:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = '@app/components/ArrayHelper.php';
```

Note that customizing of helper classes is only useful if you want to change the behavior of an existing function
of the helpers. If you want to add additional functions to use in your application you may better create a separate
helper for that.
