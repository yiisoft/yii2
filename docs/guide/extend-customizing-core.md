Helper Classes
==============

Yii provides many classes that help simplify  common coding tasks, such as string or array manipulations,
HTML code generation, and so forth. These helper classes are organized under the `yii\helpers` namespace and
are all static classes (meaning they contain only static properties and methods and should not be instantiated).


You use a helper class by directly calling one of its static methods:


```
use yii\helpers\ArrayHelper;

$c = ArrayHelper::merge($a, $b);
```

Extending Helper Classes
------------------------

To make helper classes easier to extend, Yii breaks each helper class into two classes: a base class (e.g. `BaseArrayHelper`)
and a concrete class (e.g. `ArrayHelper`). When you use a helper, you should only use the concrete version, never use the base class.

If you want to customize a helper, perform the following steps (using `ArrayHelper` as an example):

1. Name your class the same as the concrete class provided by Yii, including the namespace: `yii\helpers\ArrayHelper`
2. Extend your class from the base class: `class ArrayHelper extends \yii\helpers\BaseArrayHelper`.
3. In your class, override any method or property as needed, or add new methods or properties.
4. Tell your application to use your version of the helper class by including the following line of code in the bootstrap script:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = 'path/to/ArrayHelper.php';
```

Step 4 above will instruct the Yii class autoloader to load your version of the helper class instead of the one included in the Yii distribution.

> Tip: You can use `Yii::$classMap` to replace ANY core Yii class with your own customized version, not just helper classes.
