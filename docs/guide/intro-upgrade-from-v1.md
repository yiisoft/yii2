Upgrading from Version 1.1
==========================

There are many differences between versions 1.1 and 2.0 of Yii as the framework was completely rewritten for 2.0.
As a result, upgrading from version 1.1 is not as trivial as upgrading between minor versions. In this guide you'll
find the major differences between the two versions.

If you have not used Yii 1.1 before, you can safely skip this section and turn directly to "[Getting started](start-installation.md)".

Please note that Yii 2.0 introduces more new features than are covered in this summary. It is highly recommended
that you read through the whole definitive guide to learn about them all. Chances are that
some features you previously had to develop for yourself are now part of the core code.


Installation
------------

Yii 2.0 fully embraces [Composer](https://getcomposer.org/), the de facto PHP package manager. Installation
of the core framework, as well as extensions, are handled through Composer. Please refer to
the [Installing Yii](start-installation.md) section to learn how to install Yii 2.0. If you want to
create new extensions, or turn your existing 1.1 extensions into 2.0-compatible extensions, please refer to
the [Creating Extensions](structure-extensions.md#creating-extensions) section of the guide.


PHP Requirements
----------------

Yii 2.0 requires PHP 5.4 or above, which is a huge improvement over PHP version 5.2 that is required by Yii 1.1.
As a result, there are many differences on the language level that you should pay attention to.
Below is a summary of the major changes regarding PHP:

- [Namespaces](http://php.net/manual/en/language.namespaces.php).
- [Anonymous functions](http://php.net/manual/en/functions.anonymous.php).
- Short array syntax `[...elements...]` is used instead of `array(...elements...)`.
- Short echo tags `<?=` are used in view files. This is safe to use starting from PHP 5.4.
- [SPL classes and interfaces](http://php.net/manual/en/book.spl.php).
- [Late Static Bindings](http://php.net/manual/en/language.oop5.late-static-bindings.php).
- [Date and Time](http://php.net/manual/en/book.datetime.php).
- [Traits](http://php.net/manual/en/language.oop5.traits.php).
- [intl](http://php.net/manual/en/book.intl.php). Yii 2.0 makes use of the `intl` PHP extension
  to support internationalization features.


Namespace
---------

The most obvious change in Yii 2.0 is the use of namespaces. Almost every core class
is namespaced, e.g., `yii\web\Request`. The "C" prefix is no longer used in class names.
The naming scheme now follows the directory structure. For example, `yii\web\Request`
indicates that the corresponding class file is `web/Request.php` under the Yii framework folder.

(You can use any core class without explicitly including that class file, thanks to the Yii
class loader.)


Component and Object
--------------------

Yii 2.0 breaks the `CComponent` class in 1.1 into two classes: [[yii\base\BaseObject]] and [[yii\base\Component]].
The [[yii\base\BaseObject|BaseObject]] class is a lightweight base class that allows defining [object properties](concept-properties.md)
via getters and setters. The [[yii\base\Component|Component]] class extends from [[yii\base\BaseObject|BaseObject]] and supports
[events](concept-events.md) and [behaviors](concept-behaviors.md).

If your class does not need the event or behavior feature, you should consider using
[[yii\base\BaseObject|BaseObject]] as the base class. This is usually the case for classes that represent basic
data structures.


Object Configuration
--------------------

The [[yii\base\BaseObject|BaseObject]] class introduces a uniform way of configuring objects. Any descendant class
of [[yii\base\BaseObject|BaseObject]] should declare its constructor (if needed) in the following way so that
it can be properly configured:

```php
class MyClass extends \yii\base\BaseObject
{
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

In the above, the last parameter of the constructor must take a configuration array
that contains name-value pairs for initializing the properties at the end of the constructor.
You can override the [[yii\base\BaseObject::init()|init()]] method to do initialization work that should be done after
the configuration has been applied.

By following this convention, you will be able to create and configure new objects
using a configuration array:

```php
$object = Yii::createObject([
    '__class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

More details about configurations can be found in the [Configurations](concept-configurations.md) section.


Events
------

In Yii 1, events were created by defining an `on`-method (e.g., `onBeforeSave`). In Yii 2, you can now use any event name. You trigger an event by calling
the [[yii\base\Component::trigger()|trigger()]] method:

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

To attach a handler to an event, use the [[yii\base\Component::on()|on()]] method:

```php
$component->on($eventName, $handler);
// To detach the handler, use:
// $component->off($eventName, $handler);
```

There are many enhancements to the event features. For more details, please refer to the [Events](concept-events.md) section.


Path Aliases
------------

Yii 2.0 expands the usage of path aliases to both file/directory paths and URLs. Yii 2.0 also now requires
an alias name to start with the `@` character, to differentiate aliases from normal file/directory paths or URLs.
For example, the alias `@yii` refers to the Yii installation directory. Path aliases are
supported in most places in the Yii core code. For example, [[yii\caching\FileCache::cachePath]] can take
both a path alias and a normal directory path.

A path alias is also closely related to a class namespace. It is recommended that a path
alias be defined for each root namespace, thereby allowing you to use Yii class autoloader without
any further configuration. For example, because `@yii` refers to the Yii installation directory,
a class like `yii\web\Request` can be autoloaded. If you use a third party library,
such as the Zend Framework, you may define a path alias `@Zend` that refers to that framework's installation
directory. Once you've done that, Yii will be able to autoload any class in that Zend Framework library, too.

More on path aliases can be found in the [Aliases](concept-aliases.md) section.


Views
-----

The most significant change about views in Yii 2 is that the special variable `$this` in a view no longer refers to
the current controller or widget. Instead, `$this` now refers to a *view* object, a new concept
introduced in 2.0. The *view* object is of type [[yii\web\View]], which represents the view part
of the MVC pattern. If you want to access the controller or widget in a view, you can use `$this->context`.

To render a partial view within another view, you use `$this->render()`, not `$this->renderPartial()`. The call to `render` also now has to be explicitly echoed, as the `render()` method returns the rendering
result, rather than directly displaying it. For example:

```php
echo $this->render('_item', ['item' => $item]);
```

Besides using PHP as the primary template language, Yii 2.0 is also equipped with official
support for two popular template engines: Smarty and Twig. The Prado template engine is no longer supported.
To use these template engines, you need to configure the `view` application component by setting the
[[yii\base\View::$renderers|View::$renderers]] property. Please refer to the [Template Engines](tutorial-template-engines.md)
section for more details.


Models
------

Yii 2.0 uses [[yii\base\Model]] as the base model, similar to `CModel` in 1.1.
The class `CFormModel` has been dropped entirely. Instead, in Yii 2 you should extend [[yii\base\Model]] to create a form model class.

Yii 2.0 introduces a new method called [[yii\base\Model::scenarios()|scenarios()]] to declare
supported scenarios, and to indicate under which scenario an attribute needs to be validated, can be considered as safe or not, etc. For example:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

In the above, two scenarios are declared: `backend` and `frontend`. For the `backend` scenario, both the
`email` and `role` attributes are safe, and can be massively assigned. For the `frontend` scenario,
`email` can be massively assigned while `role` cannot. Both `email` and `role` should be validated using rules.

The [[yii\base\Model::rules()|rules()]] method is still used to declare the validation rules. Note that due to the introduction of [[yii\base\Model::scenarios()|scenarios()]], there is no longer an `unsafe` validator.

In most cases, you do not need to override [[yii\base\Model::scenarios()|scenarios()]]
if the [[yii\base\Model::rules()|rules()]] method fully specifies the scenarios that will exist, and if there is no need to declare
`unsafe` attributes.

To learn more details about models, please refer to the [Models](structure-models.md) section.


Controllers
-----------

Yii 2.0 uses [[yii\web\Controller]] as the base controller class, which is similar to `CController` in Yii 1.1.
[[yii\base\Action]] is the base class for action classes.

The most obvious impact of these changes on your code is that a controller action should return the content
that you want to render instead of echoing it:

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Please refer to the [Controllers](structure-controllers.md) section for more details about controllers.


Widgets
-------

Yii 2.0 uses [[yii\base\Widget]] as the base widget class, similar to `CWidget` in Yii 1.1.

To get better support for the framework in IDEs, Yii 2.0 introduces a new syntax for using widgets. The static methods
[[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]], and [[yii\base\Widget::widget()|widget()]]
have been introduced, to be used like so:

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Note that you have to "echo" the result to display it
echo Menu::widget(['items' => $items]);

// Passing an array to initialize the object properties
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... form input fields here ...
ActiveForm::end();
```

Please refer to the [Widgets](structure-widgets.md) section for more details.


Themes
------

Themes work completely differently in 2.0. They are now based on a path mapping mechanism that maps a source
view file path to a themed view file path. For example, if the path map for a theme is
`['/web/views' => '/web/themes/basic']`, then the themed version for the view file
`/web/views/site/index.php` will be `/web/themes/basic/site/index.php`. For this reason, themes can now
be applied to any view file, even a view rendered outside of the context of a controller or a widget.

Also, there is no more `CThemeManager` component. Instead, `theme` is a configurable property of the `view`
application component.

Please refer to the [Theming](output-theming.md) section for more details.


Console Applications
--------------------

Console applications are now organized as controllers, like Web applications. Console controllers
should extend from [[yii\console\Controller]], similar to `CConsoleCommand` in 1.1.

To run a console command, use `yii <route>`, where `<route>` stands for a controller route
(e.g. `sitemap/index`). Additional anonymous arguments are passed as the parameters to the
corresponding controller action method, while named arguments are parsed according to
the declarations in [[yii\console\Controller::options()]].

Yii 2.0 supports automatic generation of command help information from comment blocks.

Please refer to the [Console Commands](tutorial-console.md) section for more details.


I18N
----

Yii 2.0 removes the built-in date formatter and number formatter pieces in favor of the [PECL intl PHP module](http://pecl.php.net/package/intl).

Message translation is now performed via the `i18n` application component.
This component manages a set of message sources, which allows you to use different message
sources based on message categories.

Please refer to the [Internationalization](tutorial-i18n.md) section for more details.


Action Filters
--------------

Action filters are implemented via behaviors now. To define a new, custom filter, extend from [[yii\base\ActionFilter]]. To use a filter, attach the filter class to the controller
as a behavior. For example, to use the [[yii\filters\AccessControl]] filter, you would have the following
code in a controller:

```php
public function behaviors()
{
    return [
        'access' => [
            '__class' => \yii\filters\AccessControl::class,
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

Please refer to the [Filtering](structure-filters.md) section for more details.


Assets
------

Yii 2.0 introduces a new concept called *asset bundle* that replaces the script package concept found in Yii 1.1.

An asset bundle is a collection of asset files (e.g. JavaScript files, CSS files, image files, etc.)
within a directory. Each asset bundle is represented as a class extending [[yii\web\AssetBundle]].
By registering an asset bundle via [[yii\web\AssetBundle::register()]], you make
the assets in that bundle accessible via the Web. Unlike in Yii 1, the page registering the bundle will automatically
contain the references to the JavaScript and CSS files specified in that bundle.

Please refer to the [Managing Assets](structure-assets.md) section for more details.


Helpers
-------

Yii 2.0 introduces many commonly used static helper classes, including.

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]

Please refer to the [Helper Overview](helper-overview.md) section for more details.

Forms
-----

Yii 2.0 introduces the *field* concept for building a form using [[yii\widgets\ActiveForm]]. A field
is a container consisting of a label, an input, an error message, and/or a hint text.
A field is represented as an [[yii\widgets\ActiveField|ActiveField]] object.
Using fields, you can build a form more cleanly than before:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Please refer to the [Creating Forms](input-forms.md) section for more details.


Query Builder
-------------

In 1.1, query building was scattered among several classes, including `CDbCommand`,
`CDbCriteria`, and `CDbCommandBuilder`. Yii 2.0 represents a DB query in terms of a [[yii\db\Query|Query]] object
that can be turned into a SQL statement with the help of [[yii\db\QueryBuilder|QueryBuilder]] behind the scene.
For example:

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

Best of all, such query building methods can also be used when working with [Active Record](db-active-record.md).

Please refer to the [Query Builder](db-query-builder.md) section for more details.


Active Record
-------------

Yii 2.0 introduces a lot of changes to [Active Record](db-active-record.md). The two most obvious ones involve
query building and relational query handling.

The `CDbCriteria` class in 1.1 is replaced by [[yii\db\ActiveQuery]] in Yii 2. That class extends from [[yii\db\Query]], and thus
inherits all query building methods. You call [[yii\db\ActiveRecord::find()]] to start building a query:

```php
// To retrieve all *active* customers and order them by their ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

To declare a relation, simply define a getter method that returns an [[yii\db\ActiveQuery|ActiveQuery]] object.
The property name defined by the getter represents the relation name. For example, the following code declares
an `orders` relation (in 1.1, you would have to declare relations in a central place `relations()`):

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

Now you can use `$customer->orders` to access a customer's orders from the related table. You can also use the following code
to perform an on-the-fly relational query with a customized query condition:

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

When eager loading a relation, Yii 2.0 does it differently from 1.1. In particular, in 1.1 a JOIN query
would be created to select both the primary and the relational records. In Yii 2.0, two SQL statements are executed
without using JOIN: the first statement brings back the primary records and the second brings back the relational
records by filtering with the primary keys of the primary records.

Instead of returning [[yii\db\ActiveRecord|ActiveRecord]] objects, you may chain the [[yii\db\ActiveQuery::asArray()|asArray()]]
method when building a query to return a large number of records. This will cause the query result to be returned
as arrays, which can significantly reduce the needed CPU time and memory if large number of records . For example:

```php
$customers = Customer::find()->asArray()->all();
```

Another change is that you can't define attribute default values through public properties anymore.
If you need those, you should set them in the init method of your record class.

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NEW;
}
```

There were some problems with overriding the constructor of an ActiveRecord class in 1.1. These are not present in
version 2.0 anymore. Note that when adding parameters to the constructor you might have to override [[yii\db\ActiveRecord::instantiate()]].

There are many other changes and enhancements to Active Record. Please refer to
the [Active Record](db-active-record.md) section for more details.


Active Record Behaviors
-----------------------

In 2.0, we have dropped the base behavior class `CActiveRecordBehavior`. If you want to create an Active Record Behavior,
you will have to extend directly from `yii\base\Behavior`. If the behavior class needs to respond to some events
of the owner, you have to override the `events()` method like the following:

```php
namespace app\components;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class MyBehavior extends Behavior
{
    // ...

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function beforeValidate($event)
    {
        // ...
    }
}
```


User and IdentityInterface
--------------------------

The `CWebUser` class in 1.1 is now replaced by [[yii\web\User]], and there is no more
`CUserIdentity` class. Instead, you should implement the [[yii\web\IdentityInterface]] which
is much more straightforward to use. The advanced project template provides such an example.

Please refer to the [Authentication](security-authentication.md), [Authorization](security-authorization.md), and [Advanced Project Template](https://www.yiiframework.com/extension/yiisoft/yii2-app-advanced/doc/guide) sections for more details.


URL Management
--------------

URL management in Yii 2 is similar to that in 1.1. A major enhancement is that URL management now supports optional
parameters. For example, if you have a rule declared as follows, then it will match
both `post/popular` and `post/1/popular`. In 1.1, you would have had to use two rules to achieve
the same goal.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Please refer to the [Url manager docs](runtime-routing.md) section for more details.

An important change in the naming convention for routes is that camel case names of controllers
and actions are now converted to lower case where each word is separated by a hypen, e.g. the controller
id for the `CamelCaseController` will be `camel-case`.
See the section about [controller IDs](structure-controllers.md#controller-ids) and [action IDs](structure-controllers.md#action-ids) for more details.


Using Yii 1.1 and 2.x together
------------------------------

If you have legacy Yii 1.1 code that you want to use together with Yii 2.0, please refer to
the [Using Yii 1.1 and 2.0 Together](tutorial-yii-integration.md#using-both-yii2-yii1) section.

