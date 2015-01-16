jQuery UI Widgets
=================

> Note: This section is under development.

Yii includes support for the [jQuery UI](http://api.jqueryui.com/) library in an official extension. jQuery UI is
a curated set of user interface interactions, effects, widgets, and themes built on top of the jQuery JavaScript Library.

Installation
------------

The preferred way to install the extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yiisoft/yii2-jui "*"
```

or add

```
"yiisoft/yii2-jui": "*"
```

to the require section of your `composer.json` file.

Yii widgets
-----------

Most complex jQuery UI components are wrapped into Yii widgets to allow more robust syntax and integrate with
framework features. All widgets belong to `\yii\jui` namespace:

- [[yii\jui\Accordion|Accordion]]
- [[yii\jui\AutoComplete|AutoComplete]]
- [[yii\jui\DatePicker|DatePicker]]
- [[yii\jui\Dialog|Dialog]]
- [[yii\jui\Draggable|Draggable]]
- [[yii\jui\Droppable|Droppable]]
- [[yii\jui\Menu|Menu]]
- [[yii\jui\ProgressBar|ProgressBar]]
- [[yii\jui\Resizable|Resizable]]
- [[yii\jui\Selectable|Selectable]]
- [[yii\jui\Slider|Slider]]
- [[yii\jui\SliderInput|SliderInput]]
- [[yii\jui\Sortable|Sortable]]
- [[yii\jui\Spinner|Spinner]]
- [[yii\jui\Tabs|Tabs]]
