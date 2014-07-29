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

> Note: To support [extending helper classes](#extending-helper-classes), Yii breaks each core helper class
  into two classes: a base class (e.g. `BaseArrayHelper`) and a concrete class (e.g. `ArrayHelper`).
  When you use a helper, you should only use the concrete version and never use the base class.


## Core Helper Classes

The following core helper classes are provided in the Yii releases:

- ArrayHelper
- Console
- FileHelper
- Html
- HtmlPurifier
- Image
- Inflector
- Json
- Markdown
- Security
- StringHelper
- Url
- VarDumper


## Extending Helper Classes

To custom a core helper class (e.g. `yii\helpers\ArrayHelper`), you should extend from its corresponding base class
(e.g. `yii\helpers\BaseArrayHelper`) and name your class the same as the corresponding concrete class
(e.g. `yii\helpers\ArrayHelper`), including its namespace.

The following example shows how to customize the [[yii\helpers\ArrayHelper::merge()|merge()]] method of the
[[yii\helpers\ArrayHelper]] class:

```php
namespace yii\helpers;

use yii\helpers\BaseArrayHelper;

class ArrayHelper extends BaseArrayHelper
{
    public static function merge($a, $b)
    {
        // your custom implementation
    }
}
```

Save your class in a file named `ArrayHelper.php`. The file can be in any directory, such as `@app/components`.

Next, in your application's [entry script](structure-entry-scripts.md), add the following line of code
after including the `yii.php` file:

```php
Yii::$classMap['yii\helpers\ArrayHelper'] = 'path/to/ArrayHelper.php';
```

The above line instructs the [Yii class autoloader](concept-autoloading.md) to load your version of the helper
class, instead of the one included in the Yii releases.
