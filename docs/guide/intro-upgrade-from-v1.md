Upgrading from Version 1.1
==========================

There are many differences between Yii version 2.0 and 1.1, because Yii is completely rewritten for 2.0.
As a result, upgrading from version 1.1 is not as trivial as upgrading between minor versions. In this chapter,
we will summarize the major differences between the two versions.

Please note that Yii 2.0 introduces many new features which are not covered in this summary. It is highly recommended
that you read through the whole definitive guide to learn about these features. Chances could be that
some features you previously have to develop by yourself are already part of the core code now.


Installation
------------

Yii 2.0 fully embraces [Composer](https://getcomposer.org/), a de facto PHP package manager. Installation
of the core framework as well as extensions are all installed through Composer. Please refer to
the [Starting from Basic App](start-basic.md) chapter to learn how to install Yii 2.0. If you want to
create new extensions or turn your existing 1.1 extensions into 2.0, please refer to
the [Creating Extensions](extend-creating-extensions.md) chapter.


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
The [[yii\base\Object|Object]] class is a lightweight base class that allows defining [object properties](basic-properties.md)
via getters and setters. The [[yii\base\Component|Component]] class extends from [[yii\base\Object|Object]] and supports
[events](basic-events.md) and [behaviors](basic-behaviors.md).

If your class does not need the event or behavior feature, you should consider using
[[yii\base\Object|Object]] as the base class. This is usually the case for classes that represent basic
data structures.


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

More details about configurations can be found in the [Object Configurations](basic-configs.md) chapter.


Events
------

There is no longer the need to define an `on`-method in order to define an event in Yii 2.0.
Instead, you can use whatever event names. You can trigger an event by calling
the [[yii\base\Component::trigger()|trigger()]] method:

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

And to attach a handler to an event, you can use the [[yii\base\Component::on()|on()]] method:

```php
$component->on($eventName, $handler);
// To detach the handler, use:
// $component->off($eventName, $handler);
```

There are many enhancements to the event features. For more details, please refer to the [Events](basic-events.md) chapter.


Path Aliases
------------

Yii 2.0 expands the usage of path aliases to both file/directory paths and URLs. It also requires
an alias name to start with a `@` character so that it can be differentiated from normal file/directory paths and URLs.
For example, the alias `@yii` refers to the Yii installation directory. Path aliases are
supported in most places in the Yii core code. For example, [[yii\caching\FileCache::cachePath]] can take
both a path alias and a normal directory path.

Path alias is also closely related with class namespaces. It is recommended that a path
alias be defined for each root namespace so that you can use Yii the class autoloader without
any further configuration. For example, because `@yii` refers to the Yii installation directory,
a class like `yii\web\Request` can be autoloaded by Yii. If you use a third party library
such as Zend Framework, you may define a path alias `@Zend` which refers to its installation
directory and Yii will be able to autoload any class in this library.

More on path aliases can be found in the [Path Aliases](basic-aliases.md) chapter.


Views
-----

The most significant change about views is that `$this` in a view no longer refers to
the current controller or widget. Instead, it refers to a *view* object which is a new concept
introduced in 2.0. The *view* object is of class [[yii\web\View]] which represents the view part
of the MVC pattern. In you want to access the controller or widget in a view, you should use `$this->context`.

To render a partial view within another view, you should use `$this->render()` now.
And you have to echo it explicitly because the `render()` method will return the rendering
result rather than directly displaying it. For example,

```php
echo $this->render('_item', ['item' => $item]);
```

Besides using PHP as the primary template language, Yii 2.0 is also equipped with official
support for two popular template engines: Smarty and Twig. The Prado template engine is no longer supported.
To use these template engines, you need to configure the `view` application component by setting the
[[yii\base\View::$renderers|View::$renderers]] property. Please refer to the [Template Engines](tutorial-template-engines.md)
chapter for more details.


Models
------

Yii 2.0 uses [[yii\base\Model]] as the base model class which is similar to `CModel` in 1.1.
The class `CFormModel` is dropped. Instead, you should extend [[yii\base\Model]] to create a form model class.

Yii 2.0 introduces a new method called [[yii\base\Model::scenarios()|scenarios()]] to declare
supported scenarios and under which scenario an attribute needs to be validated and can be considered as safe or not.
For example,

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!name'],
    ];
}
```

In the above, two scenarios are declared: `backend` and `frontend`. For the `backend` scenario, both of the
`email` and `role` attributes are safe and can be massively assigned; for the `frontend` scenario,
`email` can be massively assigned while `role` cannot. Both `email` and `role` should be validated.

The [[yii\base\Model::rules()|rules()]] method is still used to declare validation rules. Note that because
of the introduction of [[yii\base\Model::scenarios()|scenarios()]], there is no more `unsafe` validator.

In most cases, you do not need to override [[yii\base\Model::scenarios()|scenarios()]]
if the [[yii\base\Model::rules()|rules()]] method fully specifies the scenarios and there is no need to declare
`unsafe` attributes.

To learn more details about models, please refer to the [Models](basic-models.md) chapter.


Controllers
-----------

Yii 2.0 uses [[yii\web\Controller]] as the base controller class which is similar to `CWebController` in 1.1.
And [[yii\base\Action]] is the base class for action classes.

The most obvious change when you write code in a controller action is that you should return the content
that you want to render instead of echoing it. For example,

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

Please refer to the [Controllers](structure-controllers.md) chapter for more details about controllers.


Widgets
-------

Yii 2.0 uses [[yii\base\Widget]] as the base widget class which is similar to `CWidget` in 1.1.

To get better IDE support, Yii 2.0 introduces a new syntax for using widgets. The static methods
[[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]] and [[yii\base\Widget::widget()|widget()]]
are introduced and can be used as follows,

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

Please refer to the [Widgets](structure-widgets.md) chapter for more details.


Themes
------

Themes work completely different in 2.0. They are now based on a path mapping mechanism which maps a source
view file path to a themed view file path. For example, if the path map for a theme is
`['/web/views' => '/web/themes/basic']`, then the themed version for a view file
`/web/views/site/index.php` will be `/web/themes/basic/site/index.php`. For this reason, themes can now
be applied to any view file, even if a view rendered outside of the context of a controller or a widget.

Also, there is no more `CThemeManager`. Instead, `theme` is a configurable property of the `view`
application component.

Please refer to the [Theming](tutorial-theming.md) chapter for more details.


Console Applications
--------------------

Console applications are now organized as controllers, like Web applications. Console controllers
should extend from [[yii\console\Controller]] which is similar to `CConsoleCommand` in 1.1.

To run a console command, use `yii <route>`, where `<route>` stands for a controller route
(e.g. `sitemap/index`). Additional anonymous arguments are passed as the parameters to the
corresponding controller action method, while named arguments are parsed according to
the declarations in [[yii\console\Controller::options()]].

Yii 2.0 supports automatic generation of command help information from comment blocks.

Please refer to the [Console Commands](tutorial-console.md) chapter for more details.


I18N
----

Yii 2.0 removes date formatter and number formatter in favor of the PECL intl PHP module.

Message translation is now performed via the `i18n` application component.
The component manages a set of message sources, which allows you to use different message
sources based on message categories.

Please refer to the [Internationalization](tutorial-i18n.md) chapter for more details.


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

Please refer to the [Filtering](runtime-filtering.md) chapter for more details.


Assets
------

Yii 2.0 introduces a new concept called *asset bundle* which replaces the script package concept in 1.1.

An asset bundle is a collection of asset files (e.g. JavaScript files, CSS files, image files, etc.)
under a directory. Each asset bundle is represented as a class extending [[yii\web\AssetBundle]].
By registering an asset bundle via [[yii\web\AssetBundle::register()]], you will be able to make
the assets in that bundle accessible via Web, and the page registering the bundle will automatically
contain the references to the JavaScript and CSS files specified in that bundle.

Please refer to the [Managing Assets](output-assets.md) chapter for more details.


Helpers
-------

Yii 2.0 introduces many commonly used static helper classes, such as

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]
* [[yii\helpers\Security]]


Forms
-----

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
`CDbCriteria`, and `CDbCommandBuilder`. Yii 2.0 represents a DB query in terms of a [[yii\db\Query|Query]] object
which can be turned into a SQL statement with the help of [[yii\db\QueryBuilder|QueryBuilder]] behind the scene.
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

Please refer to the [Query Builder](db-query-builder.md) chapter for more details.


Active Record
-------------

Yii 2.0 introduces a lot of changes to [Active Record](db-active-record.md). Two most obvious ones are:
query building and relational query handling.

The `CDbCriteria` class in 1.1 is replaced by [[yii\db\ActiveQuery]] which extends from [[yii\db\Query]] and thus
inherits all query building methods. You call [[yii\db\ActiveRecord::find()]] to start building a query.
For example,

```php
// to retrieve all *active* customers and order them by their ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

To declare a relation, you simply define a getter method that returns an [[yii\db\ActiveQuery|ActiveQuery]] object.
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

You can use `$customer->orders` to access the customer's orders. You can also use the following code
to perform on-the-fly relational query with customized query conditions:

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

When eager loading a relation, Yii 2.0 does it differently from 1.1. In particular, in 1.1 a JOIN query
would be created to bring both the primary and the relational records; in 2.0, two SQL statements are executed
without using JOIN: the first statement brings back the primary records and the second brings back the relational
records by filtering with the primary keys of the primary records.

Instead of returning [[yii\db\ActiveRecord|ActiveRecord]] objects, you may chain the [[yii\db\ActiveQuery::asArray()|asArray()]]
method when building a query to return large number of records. This will cause the query result to be returned
as arrays, which can significantly reduce the needed CPU time and memory if large number of records . For example,

```php
$customers = Customer::find()->asArray()->all();
```

There are many other changes and enhancements to Active Record. Please refer to
the [Active Record](db-active-record.md) chapter for more details.


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


Using Yii 1.1 and 2.x together
------------------------------

If you have legacy Yii 1.1 code and you want to use it together with Yii 2.0, please refer to
the [Using Yii 1.1 and 2.0 Together](extend-using-v1-v2.md) chapter.

