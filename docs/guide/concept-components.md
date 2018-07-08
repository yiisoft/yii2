Components
==========

Components are the main building blocks of Yii applications. Components are instances of [[yii\base\Component]],
or an extended class. The three main features that components provide to other classes are:

* [Properties](concept-properties.md)
* [Events](concept-events.md)
* [Behaviors](concept-behaviors.md)
 
Separately and combined, these features make Yii classes much more customizable and easier to use. For example,
the included [[yii\jui\DatePicker|date picker widget]], a user interface component, can be used in a [view](structure-views.md)
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

The widget's properties are easily writable because the class extends [[yii\base\Component]].

While components are very powerful, they are a bit heavier than normal objects, due to the fact that
it takes extra memory and CPU time to support [event](concept-events.md) and [behavior](concept-behaviors.md) functionality in particular.
If your components do not need these two features, you may consider extending your component class from
[[yii\base\BaseObject]] instead of [[yii\base\Component]]. Doing so will make your components as efficient as normal PHP objects,
but with added support for [properties](concept-properties.md).

When extending your class from [[yii\base\Component]] or [[yii\base\BaseObject]], it is recommended that you follow
these conventions:

- If you override the constructor, specify a `$config` parameter as the constructor's *last* parameter, and then pass this parameter
  to the parent constructor.
- Always call the parent constructor *at the end* of your overriding constructor.
- If you override the [[yii\base\BaseObject::init()]] method, make sure you call the parent implementation of `init()` *at the beginning* of your `init()` method.

For example:

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

Following these guidelines will make your components [configurable](concept-configurations.md) when they are created. For example:

```php
$component = new MyClass(1, 2, ['prop1' => 3, 'prop2' => 4]);
// alternatively
$component = \Yii::createObject([
    '__class' => MyClass::class,
    'prop1' => 3,
    'prop2' => 4,
], [1, 2]);
```

> Info: While the approach of calling [[Yii::createObject()]] looks more complicated, it is more powerful because it is
> implemented on top of a [dependency injection container](concept-di-container.md).
  

The [[yii\base\BaseObject]] class enforces the following object lifecycle:

1. Pre-initialization within the constructor. You can set default property values here.
2. Object configuration via `$config`. The configuration may overwrite the default values set within the constructor.
3. Post-initialization within [[yii\base\BaseObject::init()|init()]]. You may override this method to perform sanity checks and normalization of the properties.
4. Object method calls.

The first three steps all happen within the object's constructor. This means that once you get a class instance (i.e., an object),
that object has already been initialized to a proper, reliable state.
