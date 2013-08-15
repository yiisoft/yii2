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

Yii 2.0 breaks the `CComponent` class in 1.1 into two classes: `Object` and `Component`.
The `Object` class is a lightweight base class that allows defining class properties
via getters and setters. The `Component` class extends from `Object` and supports
the event feature and the behavior feature.

If your class does not need the event or behavior feature, you should consider using
`Object` as the base class. This is usually the case for classes that represent basic
data structures.


Object Configuration
--------------------

The `Object` class introduces a uniform way of configuring objects. Any descendant class
of `Object` should declare its constructor (if needed) in the following way so that
it can be properly configured:

```php
class MyClass extends \yii\Object
{
    public function __construct($param1, $param2, $config = array())
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
You can override the `init()` method to do initialization work that should be done after
the configuration is applied.

By following this convention, you will be able to create and configure a new object
using a configuration array like the following:

```php
$object = Yii::createObject(array(
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
), $param1, $param2);
```



Events
------

There is no longer the need to define an `on`-method in order to define an event in Yii 2.0.
Instead, you can use whatever event names. To attach a handler to an event, you should
use the `on` method now:

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


View
----

Yii 2.0 introduces a `View` class to represent the view part of the MVC pattern.
It can be configured globally through the "view" application component. It is also
accessible in any view file via `$this`. This is one of the biggest changes compared to 1.1:
**`$this` in a view file no longer refers to the controller or widget object.**
It refers to the view object that is used to render the view file. To access the controller
or the widget object, you have to use `$this->context` now.

Because you can access the view object through the "view" application component,
you can now render a view file like the following anywhere in your code, not necessarily
in controllers or widgets:

```php
$content = Yii::$app->view->renderFile($viewFile, $params);
// You can also explicitly create a new View instance to do the rendering
// $view = new View;
// $view->renderFile($viewFile, $params);
```


Also, there is no more `CClientScript` in Yii 2.0. The `View` class has taken over its role
with significant improvements. For more details, please see the "assets" subsection.

While Yii 2.0 continues to use PHP as its main template language, it comes with built-in
support for two popular template engines: Smarty and Twig. The Prado template engine is
no longer supported. To use these template engines, you just need to use `tpl` as the file
extension for your Smarty views, or `twig` for Twig views. You may also configure the
`View::renderers` property to use other template engines.


Models
------

A model is now associated with a form name returned by its `formName()` method. This is
mainly used when using HTML forms to collect user inputs for a model. Previously in 1.1,
this is usually hardcoded as the class name of the model.

A new methods called `load()` and `Model::loadMultiple()` is introduced to simplify the data population from user inputs
to a model. For example,

```php
$model = new Post;
if ($model->load($_POST)) {...}
// which is equivalent to:
if (isset($_POST['Post'])) {
    $model->attributes = $_POST['Post'];
}

$model->save();

$postTags = array();
$tagsCount = count($_POST['PostTag']);
while($tagsCount-- > 0){
    $postTags[] = new PostTag(array('post_id' => $model->id));
}
Model::loadMultiple($postTags, $_POST);
```

Yii 2.0 introduces a new method called `scenarios()` to declare which attributes require
validation under which scenario. Child classes should overwrite `scenarios()` to return
a list of scenarios and the corresponding attributes that need to be validated when
`validate()` is called. For example,

```php
public function scenarios()
{
    return array(
        'backend' => array('email', 'role'),
        'frontend' => array('email', '!name'),
    );
}
```


This method also determines which attributes are safe and which are not. In particular,
given a scenario, if an attribute appears in the corresponding attribute list in `scenarios()`
and the name is not prefixed with `!`, it is considered *safe*.

Because of the above change, Yii 2.0 no longer has "safe" and "unsafe" validators.

If your model only has one scenario (very common), you do not have to overwrite `scenarios()`,
and everything will still work like the 1.1 way.


Controllers
-----------

The `render()` and `renderPartial()` methods now return the rendering results instead of directly
sending them out. You have to `echo` them explicitly, e.g., `echo $this->render(...);`.


Widgets
-------

Using a widget is more straightforward in 2.0. You mainly use the `begin()`, `end()` and `widget()`
methods of the `Widget` class. For example,

```php
// Note that you have to "echo" the result to display it
echo \yii\widgets\Menu::widget(array('items' => $items));

// Passing an array to initialize the object properties
$form = \yii\widgets\ActiveForm::begin(array(
	'options' => array('class' => 'form-horizontal'),
	'fieldConfig' => array('inputOptions' => array('class' => 'input-xlarge')),
));
... form inputs here ...
\yii\widgets\ActiveForm::end();
```

Previously in 1.1, you would have to enter the widget class names as strings via the `beginWidget()`,
`endWidget()` and `widget()` methods of `CBaseController`. The approach above gets better IDE support.


Themes
------

Themes work completely different in 2.0. They are now based on a path map to "translate" a source
view into a themed view. For example, if the path map for a theme is
`array('/web/views' => '/web/themes/basic')`, then the themed version for a view file
`/web/views/site/index.php` will be `/web/themes/basic/site/index.php`.

For this reason, theme can now be applied to any view file, even if a view rendered outside
of the context of a controller or a widget.

There is no more `CThemeManager`. Instead, `theme` is a configurable property of the "view"
application component.


Console Applications
--------------------

Console applications are now composed by controllers, like Web applications. In fact,
console controllers and Web controllers share the same base controller class.

Each console controller is like `CConsoleCommand` in 1.1. It consists of one or several
actions. You use the `yii <route>` command to execute a console command, where `<route>`
stands for a controller route (e.g. `sitemap/index`). Additional anonymous arguments
are passed as the parameters to the corresponding controller action method, and named arguments
are treated as global options declared in `globalOptions()`.

Yii 2.0 supports automatic generation of command help information from comment blocks.


I18N
----

Yii 2.0 removes date formatter and number formatter in favor of the PECL intl PHP module.

Message translation is still supported, but managed via the "i18n" application component.
The component manages a set of message sources, which allows you to use different message
sources based on message categories. For more information, see the class documentation for `I18N`.


Action Filters
--------------

Action filters are implemented via behaviors now. You should extend from `ActionFilter` to
define a new filter. To use a filter, you should attach the filter class to the controller
as a behavior. For example, to use the `AccessControl` filter, you should have the following
code in a controller:

```php
public function behaviors()
{
    return array(
        'access' => array(
            'class' => 'yii\web\AccessControl',
            'rules' => array(
                array('allow' => true, 'actions' => array('admin'), 'roles' => array('@')),
            ),
        ),
    );
}
```



Assets
------

Yii 2.0 introduces a new concept called *asset bundle*. It is similar to script
packages (managed by `CClientScript`) in 1.1, but with better support.

An asset bundle is a collection of asset files (e.g. JavaScript files, CSS files, image files, etc.)
under a directory. Each asset bundle is represented as a class extending `AssetBundle`.
By registering an asset bundle via `AssetBundle::register()`, you will be able to make
the assets in that bundle accessible via Web, and the current page will automatically
contain the references to the JavaScript and CSS files specified in that bundle.



Static Helpers
--------------

Yii 2.0 introduces many commonly used static helper classes, such as `Html`, `ArrayHelper`,
`StringHelper`. These classes are designed to be easily extended. Note that static classes
are usually hard to extend because of the fixed class name references. But Yii 2.0
introduces the class map (via `Yii::$classMap`) to overcome this difficulty.


`ActiveForm`
------------

Yii 2.0 introduces the *field* concept for building a form using `ActiveForm`. A field
is a container consisting of a label, an input, an error message, and/or a hint text.
It is represented as an `ActiveField` object. Using fields, you can build a form more cleanly than before:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
	<?php echo $form->field($model, 'username'); ?>
	<?php echo $form->field($model, 'password')->passwordInput(); ?>
	<div class="form-actions">
		<?php echo Html::submitButton('Login'); ?>
	</div>
<?php yii\widgets\ActiveForm::end(); ?>
```



Query Builder
-------------

In 1.1, query building is scattered among several classes, including `CDbCommand`,
`CDbCriteria`, and `CDbCommandBuilder`. Yii 2.0 uses `Query` to represent a DB query
and `QueryBuilder` to generate SQL statements from query objects. For example:

```php
$query = new \yii\db\Query;
$query->select('id, name')
      ->from('tbl_user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```


Best of all, such query building methods can be used together with `ActiveRecord`,
as explained in the next sub-section.


ActiveRecord
------------

ActiveRecord has undergone significant changes in Yii 2.0. The most important one
is the relational ActiveRecord query. In 1.1, you have to declare the relations
in the `relations()` method. In 2.0, this is done via getter methods that return
an `ActiveQuery` object. For example, the following method declares an "orders" relation:

```php
class Customer extends \yii\db\ActiveRecord
{
	public function getOrders()
	{
		return $this->hasMany('Order', array('customer_id' => 'id'));
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
use the `find()` method:

```php
// to retrieve all *active* customers and order them by their ID:
$customers = Customer::find()
	->where(array('status' => $active))
	->orderBy('id')
	->all();
// return the customer whose PK is 1
$customer = Customer::find(1);
```


The `find()` method returns an instance of `ActiveQuery` which is a subclass of `Query`.
Therefore, you can use all query methods of `Query`.

Instead of returning ActiveRecord objects, you may call `ActiveQuery::asArray()` to
return results in terms of arrays. This is more efficient and is especially useful
when you need to return a large number of records:

```php
$customers = Customer::find()->asArray()->all();
```

By default, ActiveRecord now only saves dirty attributes. In 1.1, all attributes
are saved to database when you call `save()`, regardless of having changed or not,
unless you explicitly list the attributes to save.


Auto-quoting Table and Column Names
------------------------------------

Yii 2.0 supports automatic quoting of database table and column names. A name enclosed
within double curly brackets is treated as a table name, and a name enclosed within
double square brackets is treated as a column name. They will be quoted according to
the database driver being used:

```php
$command = $connection->createCommand('SELECT [[id]] FROM {{posts}}');
echo $command->sql;  // MySQL: SELECT `id` FROM `posts`
```

This feature is especially useful if you are developing an application that supports
different DBMS.


User and Identity
-----------------

The `CWebUser` class in 1.1 is now replaced by `\yii\Web\User`, and there is no more
`CUserIdentity` class. Instead, you should implement the `Identity` interface which
is much more straightforward to implement. The bootstrap application provides such an example.


URL Management
--------------

URL management is similar to 1.1. A major enhancement is that it now supports optional
parameters. For example, if you have rule declared as follows, then it will match
both `post/popular` and `post/1/popular`. In 1.1, you would have to use two rules to achieve
the same goal.

```php
array(
	'pattern' => 'post/<page:\d+>/<tag>',
	'route' => 'post/index',
	'defaults' => array('page' => 1),
)
```



Response
--------

Extensions
----------

Integration with Composer
-------------------------

TBD

