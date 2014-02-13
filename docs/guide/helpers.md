Helper Classes
==============

Yii provides many helper classes to simplify commonly needed coding tasks, such as string/array manipulations,
HTML code generation, etc. These helper classes are mostly organized under the `yii\helpers` namespace, and
they are all static classes (meaning they contain only static properties and methods and should not be instantiated).
You use a helper class by directly calling its static method, like the following,

~~~
use yii\helpers\ArrayHelper;

$c = ArrayHelper::merge($a, $b);
~~~

Extending Helper Classes
------------------------

Static classes are typically hard to customize because when you use them, you already hardcode the class names
in your code, and as a result your customized versions will not get used unless you do some global replacement in your code.

To solve this problem, Yii breaks each helper into two classes: one is base class (e.g. `BaseArrayHelper`)
and the other the concrete class (e.g. `ArrayHelper`). When you use a helper, you should only use the concrete version.

If you want to customize a helper, e.g., `ArrayHelper`, do the following steps:

1. Name your class the same as the concrete class provided by Yii, including the namespace part, e.g.,
   `yii\helpers\ArrayHelper`;
2. Extend your class from the base class, e.g., `class ArrayHelper extends \yii\helpers\BaseArrayHelper`;
3. In your class, override any method or property as your want, or add new methods or properties;
4. In your application that you plan to use your own version of the helper class, include the following
   line of code in the bootstrap script:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = 'path/to/ArrayHelper.php';
```

The Step 4 above will instruct Yii class autoloader to load your version of the helper instead of the one
included in the Yii distribution.
