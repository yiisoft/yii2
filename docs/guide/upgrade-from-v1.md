Upgrading from Yii 1.1
======================

In this chapter, we list the major changes introduced in Yii 2.0 since version 1.1.
We hope this list will make it easier for you to upgrade from Yii 1.1 and quickly
master Yii 2.0 based on your existing Yii knowledge.


Namespace
---------

The most obvious change in Yii 2.0 is the use of namespaces. Almost every core class
is namespaced, e.g., `yii\web\Request`. The "C" prefix is no longer used in class names.
The naming of the namespaces follows the directory structure. For example, `yii\web\Request`
indicates the corresponding class file is `web/Request.php` under the Yii framework folder.
You can use any core class without explicitly including that class file, thanks to the Yii
class loader.


Component and Object
--------------------

Yii 2.0 breaks the `CComponent` class in 1.1 into two classes: [[yii\base\Object]] and [[yii\base\Component]].
The [[yii\base\Object|Object]] class is a lightweight base class that allows defining class properties
via getters and setters. The [[yii\base\Component|Component]] class extends from [[yii\base\Object|Object]] and supports
the event feature and the behavior feature.

If your class does not need the event or behavior feature, you should consider using
`Object` as the base class. This is usually the case for classes that represent basic
data structures.

More details about Object and component can be found in the [Basic concepts section](basics.md).


Object Configuration
--------------------

The [[yii\base\Object|Object]] class introduces a uniform way of configuring objects. Any descendant class
of [[yii\base\Object|Object]] should declare its constructor (if needed) in the following way so that
it can be properly configured:

```php
class MyClass extends \yii\base\Object
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
which contains name-value pairs for initializing the properties at the end of the constructor.
You can override the [[yii\base\Object::init()|init()]] method to do initialization work that should be done after
the configuration is applied.

By following this convention, you will be able to create and configure a new object
using a configuration array like the following:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

More on configuration can be found in the [Basic concepts section](basics.md).


Events
------

There is no longer the need to define an `on`-method in order to define an event in Yii 2.0.
Instead, you can use whatever event names. To attach a handler to an event, you should now
use the `on` method:

```php
$component->on($eventName, $handler);
// To detach the handler, use:
// $component->off($eventName, $handler);
```


When you attach a handler, you can now associate it with some parameters which can be later
accessed via the event parameter by the handler:

```php
$component->on($eventName, $handler, $params);
```


Because of this change, you can now use "global" events. Simply trigger and attach handlers to
an event of the application instance:

```php
Yii::$app->on($eventName, $handler);
....
// this will trigger the event and cause $handler to be invoked.
Yii::$app->trigger($eventName);
```

If you need to handle all instances of a class instead of the object you can attach a handler like the following:

```php
Event::on(ActiveRecord::className(), ActiveRecord::EVENT_AFTER_INSERT, function ($event) {
    Yii::trace(get_class($event->sender) . ' is inserted.');
});
```

The code above defines a handler that will be triggered for every Active Record object's `EVENT_AFTER_INSERT` event.

See [Event handling section](events.md) for more details.


Path Alias
----------

Yii 2.0 expands the usage of path aliases to both file/directory paths and URLs. An alias
must start with a `@` character so that it can be differentiated from file/directory paths and URLs.
For example, the alias `@yii` refers to the Yii installation directory. Path aliases are
supported in most places in the Yii core code. For example, `FileCache::cachePath` can take
both a path alias and a normal directory path.

Path alias is also closely related with class namespaces. It is recommended that a path
alias be defined for each root namespace so that you can use Yii the class autoloader without
any further configuration. For example, because `@yii` refers to the Yii installation directory,
a class like `yii\web\Request` can be autoloaded by Yii. If you use a third party library
such as Zend Framework, you may define a path alias `@Zend` which refers to its installation
directory and Yii will be able to autoload any class in this library.

More on path aliases can be found in the [Basic concepts section](basics.md).


View
----

Yii 2.0 introduces a [[yii\web\View|View]] class to represent the view part of the MVC pattern.
It can be configured globally through the "view" application component. It is also
accessible in any view file via `$this`. This is one of the biggest changes compared to 1.1:
**`$this` in a view file no longer refers to the controller or widget object.**
It refers to the view object that is used to render the view file. To access the controller
or the widget object, you have to use `$this->context` now.

For partial views, the [[yii\web\View|View]] class now includes a `render()` function. This creates another significant change in the usage of views compared to 1.1:
**`$this->render(...)` does not output the processed content; you must echo it yourself.**

```php
echo $this->render('_item', ['item' => $item]);
```

Because you can access the view object through the "view" application component,
you can now render a view file like the following anywhere in your code, not necessarily
in controllers or widgets:

```php
$content = Yii::$app->view->renderFile($viewFile, $params);
// You can also explicitly create a new View instance to do the rendering
// $view = new View();
// $view->renderFile($viewFile, $params);
```

Also, there is no more `CClientScript` in Yii 2.0. The [[yii\web\View|View]] class has taken over its role
with significant improvements. For more details, please see the "assets" subsection.

While Yii 2.0 continues to use PHP as its main template language, it comes with two official extensions
adding support for two popular template engines: Smarty and Twig. The Prado template engine is
no longer supported. To use these template engines, you just need to use `tpl` as the file
extension for your Smarty views, or `twig` for Twig views. You may also configure the
[[yii\web\View::$renderers|View::$renderers]] property to use other template engines. See [Using template engines](template.md) section
of the guide for more details.

See [View section](view.md) for more details.


Models
------

A model is now associated with a form name returned by its [[yii\base\Model::formName()|formName()]] method. This is
mainly used when using HTML forms to collect user inputs for a model. Previously in 1.1,
this is usually hardcoded as the class name of the model.

New methods called [[yii\base\Model::load()|load()] and [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] are
introduced to simplify the data population from user inputs to a model. For example,

```php
$model = new Post();
if ($model->load($_POST)) {...}
// which is equivalent to:
if (isset($_POST['Post'])) {
    $model->attributes = $_POST['Post'];
}

$model->save();

$postTags = [];
$tagsCount = count($_POST['PostTag']);
while ($tagsCount-- > 0) {
    $postTags[] = new PostTag(['post_id' => $model->id]);
}
Model::loadMultiple($postTags, $_POST);
```

Yii 2.0 introduces a new method called [[yii\base\Model::scenarios()|scenarios()]] to declare which attributes require
validation under which scenario. Child classes should overwrite [[yii\base\Model::scenarios()|scenarios()]] to return
a list of scenarios and the corresponding attributes that need to be validated when
[[yii\base\Model::validate()|validate()]] is called. For example,

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!name'],
    ];
}
```


This method also determines which attributes are safe and which are not. In particular,
given a scenario, if an attribute appears in the corresponding attribute list in [[yii\base\Model::scenarios()|scenarios()]]
and the name is not prefixed with `!`, it is considered *safe*.

Because of the above change, Yii 2.0 no longer has "unsafe" validator.

If your model only has one scenario (very common), you do not have to overwrite [[yii\base\Model::scenarios()|scenarios()]],
and everything will still work like the 1.1 way.

To learn more about Yii 2.0 models refer to [Models](model.md) section of the guide.


Controllers
-----------

The [[yii\base\Controller::render()|render()]] and [[yii\base\Controller::renderPartial()|renderPartial()]] methods
now return the rendering results instead of directly sending them out.
You have to `echo` them explicitly, e.g., `echo $this->render(...);`.

To learn more about Yii 2.0 controllers refer to [Controller](controller.md) section of the guide.


Widgets
-------

Using a widget is more straightforward in 2.0. You mainly use the
[[yii\base\Widget::begin()|begin()]],
[[yii\base\Widget::end()|end()]] and
[[yii\base\Widget::widget()|widget()]]
methods of the [[yii\base\Widget|Widget]] class. For example,

```php
// Note that you have to "echo" the result to display it
echo \yii\widgets\Menu::widget(['items' => $items]);

// Passing an array to initialize the object properties
$form = \yii\widgets\ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... form inputs here ...
\yii\widgets\ActiveForm::end();
```

Previously in 1.1, you would have to enter the widget class names as strings via the `beginWidget()`,
`endWidget()` and `widget()` methods of `CBaseController`. The approach above gets better IDE support.

For more on widgets see the [View section](view.md#widgets).


Themes
------

Themes work completely different in 2.0. They are now based on a path map to "translate" a source
view into a themed view. For example, if the path map for a theme is
`['/web/views' => '/web/themes/basic']`, then the themed version for a view file
`/web/views/site/index.php` will be `/web/themes/basic/site/index.php`.

For this reason, theme can now be applied to any view file, even if a view rendered outside
of the context of a controller or a widget.

There is no more `CThemeManager`. Instead, `theme` is a configurable property of the "view"
application component.

For more on themes see the [Theming section](theming.md).


Console Applications
--------------------

Console applications are now composed by controllers, like Web applications. In fact,
console controllers and Web controllers share the same base controller class.

Each console controller is like `CConsoleCommand` in 1.1. It consists of one or several
actions. You use the `yii <route>` command to execute a console command, where `<route>`
stands for a controller route (e.g. `sitemap/index`). Additional anonymous arguments
are passed as the parameters to the corresponding controller action method, and named arguments
are treated as options declared in `options($id)`.

Yii 2.0 supports automatic generation of command help information from comment blocks.

For more on console applications see the [Console section](console.md).


I18N
----

Yii 2.0 removes date formatter and number formatter in favor of the PECL intl PHP module.

Message translation is still supported, but managed via the "i18n" application component.
The component manages a set of message sources, which allows you to use different message
sources based on message categories. For more information, see the class documentation for [I18N](i18n.md).


Action Filters
--------------

Action filters are implemented via behaviors now. You should extend from [[yii\base\ActionFilter]] to
define a new filter. To use a filter, you should attach the filter class to the controller
as a behavior. For example, to use the [[yii\filters\AccessControl]] filter, you should have the following
code in a controller:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

For more on action filters see the [Controller section](controller.md#action-filters).


Assets
------

Yii 2.0 introduces a new concept called *asset bundle*. It is similar to script
packages (managed by `CClientScript`) in 1.1, but with better support.

An asset bundle is a collection of asset files (e.g. JavaScript files, CSS files, image files, etc.)
under a directory. Each asset bundle is represented as a class extending [[yii\web\AssetBundle]].
By registering an asset bundle via [[yii\web\AssetBundle::register()]], you will be able to make
the assets in that bundle accessible via Web, and the current page will automatically
contain the references to the JavaScript and CSS files specified in that bundle.

To learn more about assets see the [asset manager documentation](assets.md).

Static Helpers
--------------

Yii 2.0 introduces many commonly used static helper classes, such as
[[yii\helpers\Html|Html]],
[[yii\helpers\ArrayHelper|ArrayHelper]],
[[yii\helpers\StringHelper|StringHelper]].
[[yii\helpers\FileHelper|FileHelper]],
[[yii\helpers\Json|Json]],
[[yii\helpers\Security|Security]],
These classes are designed to be easily extended. Note that static classes
are usually hard to extend because of the fixed class name references. But Yii 2.0
introduces the class map (via [[Yii::$classMap]]) to overcome this difficulty.


ActiveForm
----------

Yii 2.0 introduces the *field* concept for building a form using [[yii\widgets\ActiveForm]]. A field
is a container consisting of a label, an input, an error message, and/or a hint text.
It is represented as an [[yii\widgets\ActiveField|ActiveField]] object.
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


Query Builder
-------------

In 1.1, query building is scattered among several classes, including `CDbCommand`,
`CDbCriteria`, and `CDbCommandBuilder`. Yii 2.0 uses [[yii\db\Query|Query]] to represent a DB query
and [[yii\db\QueryBuilder|QueryBuilder]] to generate SQL statements from query objects. For example:

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

Best of all, such query building methods can be used together with [[yii\db\ActiveRecord|ActiveRecord]],
as explained in the next sub-section.


ActiveRecord
------------

[[yii\db\ActiveRecord|ActiveRecord]] has undergone significant changes in Yii 2.0. The most important one
is the relational ActiveRecord query. In 1.1, you have to declare the relations
in the `relations()` method. In 2.0, this is done via getter methods that return
an [[yii\db\ActiveQuery|ActiveQuery]] object. For example, the following method declares an "orders" relation:

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

You can use `$customer->orders` to access the customer's orders. You can also
use `$customer->getOrders()->andWhere('status=1')->all()` to perform on-the-fly
relational query with customized query conditions.

When loading relational records in an eager way, Yii 2.0 does it differently from 1.1.
In particular, in 1.1 a JOIN query would be used to bring both the primary and the relational
records; while in 2.0, two SQL statements are executed without using JOIN: the first
statement brings back the primary records and the second brings back the relational records
by filtering with the primary keys of the primary records.


Yii 2.0 no longer uses the `model()` method when performing queries. Instead, you
use the [[yii\db\ActiveRecord::find()|find()]] method:

```php
// to retrieve all *active* customers and order them by their ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
// return the customer whose PK is 1
$customer = Customer::findOne(1);
```


The [[yii\db\ActiveRecord::find()|find()]] method returns an instance of [[yii\db\ActiveQuery|ActiveQuery]]
which is a subclass of [[yii\db\Query]]. Therefore, you can use all query methods of [[yii\db\Query]].

Instead of returning ActiveRecord objects, you may call [[yii\db\ActiveQuery::asArray()|ActiveQuery::asArray()]] to
return results in terms of arrays. This is more efficient and is especially useful
when you need to return a large number of records:

```php
$customers = Customer::find()->asArray()->all();
```

By default, ActiveRecord now only saves dirty attributes. In 1.1, all attributes
are saved to database when you call `save()`, regardless of having changed or not,
unless you explicitly list the attributes to save.

Scopes are now defined in a custom [[yii\db\ActiveQuery|ActiveQuery]] class instead of model directly.

See [active record docs](active-record.md) for more details.


Auto-quoting Table and Column Names
------------------------------------

Yii 2.0 supports automatic quoting of database table and column names. A name enclosed
within double curly brackets i.e. `{{tablename}}` is treated as a table name, and a name enclosed within
double square brackets i.e. `[[fieldname]]` is treated as a column name. They will be quoted according to
the database driver being used:

```php
$command = $connection->createCommand('SELECT [[id]] FROM {{posts}}');
echo $command->sql;  // MySQL: SELECT `id` FROM `posts`
```

This feature is especially useful if you are developing an application that supports
different DBMS.


User and IdentityInterface
--------------------------

The `CWebUser` class in 1.1 is now replaced by [[yii\web\User]], and there is no more
`CUserIdentity` class. Instead, you should implement the [[yii\web\IdentityInterface]] which
is much more straightforward to implement. The advanced application template provides such an example.


URL Management
--------------

URL management is similar to 1.1. A major enhancement is that it now supports optional
parameters. For example, if you have rule declared as follows, then it will match
both `post/popular` and `post/1/popular`. In 1.1, you would have to use two rules to achieve
the same goal.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

More details in the [Url manager docs](url.md).

Response
--------

TBD

Extensions
----------

Yii 1.1 extensions are not compatible with 2.0 so you have to port or rewrite these. In order to get more info about
extensions in 2.0 [referer to corresponding guide section](extensions.md).

Integration with Composer
-------------------------

Yii is fully inegrated with Composer, a well known package manager for PHP, that resolves dependencies, helps keeping
your code up to date by allowing updating it with a single console command and manages autoloading for third party
libraries no matter which autoloading these libraries are using.

In order to learn more refer to [composer](composer.md) and [installation](installation.md) sections of the guide.

Using Yii 1.1 and 2.x together
------------------------------

Check the guide on [using Yii together with 3rd-Party Systems](using-3rd-party-libraries.md) on this topic.
