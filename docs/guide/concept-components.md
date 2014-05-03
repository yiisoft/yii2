Components
==========

Components are the main building blocks in Yii applications. Components are instances of [[yii\base\Component]]
or it child class. They support features such as [properties](concept-properties.md), [events](concept-events.md) and
[behaviors](concept-behaviors.md), which makes them more customizable and easier to use. For example, you may use
the included [[yii\jui\DatePicker|date picker widget]], a user interface component, in a [view](structure-view.md)
to generate an interactive date picker:

```php
use yii\jui\DatePicker;

echo DatePicker::widget([
    'language' => 'ru',
    'name'  => 'country',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]);
```

While components are very powerful, they are a bit heavier compared to normal objects, due to the fact that
it takes extra memory and CPU time in order to support [events](concept-events.md) and [behaviors](concept-behaviors.md).
If your components do not need these two features, you may consider extending your component class from
[[yii\base\Object]] instead of [[yii\base\Component]], which will make your components as efficient as normal objects,
but with the extra support for [properties](concept-properties.md).

When extending your class from [[yii\base\Component]] or [[yii\base\Object]], it is recommended that you follow
these conventions:

- If you override the constructor, specify a `$config` parameter as its *last* parameter and pass this parameter
  to the parent constructor.
- Call parent constructor at the end of the constructor.
- If you override the [[yii\base\Object::init()]] method, make sure you call the parent implementation.

For example,

```php
namespace yii\components\MyClass;

use yii\base\Object;

class MyClass extends Object
{
    public $prop1;
    public $prop2;

    public function __construct($param1, $param2, $config = [])
    {
        // ... initialization before configuration is applied

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... initialization after configuration is applied
    }
}
```

This will make your components [configurable](concept-configs.md) when they are being created. For example,

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// alternatively
$component = \Yii::createObject([
    'class' => MyClass::className(),
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> Info: While the call of [[Yii::createObject()]] looks more complicated, it is more powerful due to
  the fact that it is implemented on top of a [dependency injection container](concept-di-container.md).
  

The [[yii\base\Object]] class enforces the following object lifecycle:

1. Pre-initialization within constructor. You can set default property values here.
2. Configuring object with `$config`. The configuration may overwrite the default values set above.
3. Post-initialization within [[yii\base\Object::init()|init()]]. You may override this method
   and do sanity check and normalization of the properties.
4. Object method calls.

The first three steps all happen within the object constructor. This means, once you get an object instance,
it has already been initialized to a proper state that you can work on.
