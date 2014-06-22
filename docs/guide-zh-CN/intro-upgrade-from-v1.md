从 Yii 1.1 升级
===============

因为 2.0 框架是完完全全的重写，所以在 1.1 和 2.0 两个版本之间，存在了很多不同之处。因此，从 1.1 版本升级的过程并不像小版本间的跨越那么简单，在本手册中你将会了解两个版本间主要的不同之处。

如果你原先没有用过 Yii 1.1， 你可以直接跳过本章，直接从"[入门篇](start-installation.md)"开始读起。

请注意，Yii 2.0 引入了很多这篇总结文章并没有涉及到的新功能。我们强烈建议你通读整部权威手册，来了解所有新功能新特色。这样你将有机会发现一些以前你可能要自己开发的功能，而现在他们很可能已经被包含在核心代码中了也说不定。


安装
------------

Yii 2.0 完全拥抱 [Composer](https://getcomposer.org/) 的使用，它其实是实际上的 PHP
包管理器。核心框架以及扩展的安装，都通过 Composer 来处理。想要了解更多如何安装 Yii 2.0
请参阅本指南的 [安装 Yii](start-installation.md) 章节。如果你想创建新的扩展，或者把你已有的 Yii 1.1 的扩展改写成兼容
2.0 的版本，你可以参考 [创建扩展](extend-creating-extensions.md) 章节。


PHP 需求
----------------

Yii 2.0 requires PHP 5.4 or above, which is a huge improvement over PHP version 5.2 that is required by Yii 1.1.
As a result, there are many differences on the language level that you should pay attention to.
Below is a summary of the major changes regarding PHP:

- [命名空间](http://php.net/manual/zh/language.namespaces.php)
- [匿名函数](http://php.net/manual/zh/functions.anonymous.php)
- 数组短语法 `[...元素...]` 用于取代 `array(...元素...)`
- 短格式的 echo 标签 `<?=` 现被用于视图文件，它自 PHP 5.4 起总会被识别并且合法，而不管 short_open_tag 的设置是什么，可以安全地调用
- [SPL 类和接口](http://php.net/manual/zh/book.spl.php)
- [延迟静态绑定](http://php.net/manual/zh/language.oop5.late-static-bindings.php)
- [日期和时间](http://php.net/manual/zh/book.datetime.php)
- [Traits（术语翻译未定：特征或特质）](http://php.net/manual/zh/language.oop5.traits.php)
- [intl](http://php.net/manual/zh/book.intl.php) Yii 2.0 使用 `intl` PHP 扩展来支持国际化的相关功能


命名空间
---------

Yii 2.0 里最明显的改动就数命名空间的使用了。几乎每一个核心类都引入了命名空间，比如
`yii\web\Request`。原本用于类名前缀的字母“C”已经不再使用了。当前的命名规范与目录结构相吻合。比如，
`yii\web\Request` 就表明对应的类文件是 Yii 框架文件夹下的 `web/Request.php` 文件。

（有了 Yii 的类自动加载器，你不需要明确包含那个具体文件的情况下，也能照常使用全部核心类。）


组件（Component）与对象（Object）
--------------------

Yii 2.0 把 1.1里的 `CComponent` 类拆分成了两个类： [[yii\base\Object]] 和 [[yii\base\Component]]。[[yii\base\Object|Object]]
类是一个轻量级的基类，你可以通过 getters 和 setters 来定义 [object 的属性](concept-properties.md)。[[yii\base\Component|Component]]
类继承自 [[yii\base\Object|Object]]，同时还进一步支持 [事件](concept-events.md) 和 [行为](concept-behaviors.md)。

如果你的类不需要用到事件或行为的功能，你应该考虑使用 [[yii\base\Object|Object]] 类作为基类。这通常是需要代表基本数据结构的类。


配置对象
--------------------

[[yii\base\Object|Object]] 类引入了一种统一的配置对象的方法。所有 [[yii\base\Object|Object]]
的子类都应该用以下方法声明它的构造器（如果需要的话），以正确配置它自身：

```php
class MyClass extends \yii\base\Object
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... 配置生效前的初始化过程

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ...配置生效后的初始化过程
    }
}
```

在上面的例子里，构造器的最后一个参数必须输入一个配置数组，包含一系列用于在构造器的结尾初始化相关属性的键值对。你可以重写
[[yii\base\Object::init()|init()]] 方法来执行一些需要在配置生效后进行的初始化工作。

你可以通过遵循以下约定俗成的编码习惯，来使用配置数组创建并配置新的对象：

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

更多有关配置的细节可以在 [对象配置](concept-configurations.md) 章节找到。


事件（Event）
------

在 Yii 1 里，我们通常通过定义 `on` 开头的方法 (比如 `onBeforeSave`)，来创建事件。而在 Yii 2 中，你可以使用任意的事件名了。同时通过调用
[[yii\base\Component::trigger()|trigger()]] 方法来触发相关事件：

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

要给事件附加一个事件句柄（Event Handler 或者叫事件处理器），需要使用 [[yii\base\Component::on()|on()]] 方法：

```php
$component->on($eventName, $handler);
// 要解除相关句柄，使用 off 方法：
// $component->off($eventName, $handler);
```

其实事件功能还有更多改进之处。要了解它们，请查看 [事件（Event）](concept-events.md) 章节。


路径别名（Path Alias）
------------

Yii 2.0 扩展类路径别名的应用，文件/目录路径和 URLs 都可以使用路径别名啦。Yii 2.0
中路径别名必须以 `@` 符号开头，以区别于普通文件目录路径或 URL。
比如，`@yii` 就是指向 Yii 安装目录别名。路径别名现在被绝大多数的 Yii 核心代码所支持。比如
[[yii\caching\FileCache::cachePath]] 就同时支持输入一个路径别名或一个普通的目录地址。

路径别名也和类的命名空间密切相关。建议给每一个根命名空间定义一个路径别名，从而无须额外配置，便可启动 Yii
的类自动加载机制。比如，因为有 `@yii` 指向 Yii 安装目录，那类似 `yii\web\Request`
的类就能被 Yii 自动加载。同理，若你用了一个第三方的类库，比如 Zend 框架，你只需定义一个名为 `@Zend`
的路径别名，去指向该框架的安装目录。之后，Yii 就可以自动加载任意 Zend Framework Library 中的类了。

更多路径别名信息请参阅[路径别名](concept-aliases.md)章节。


视图（View）
-----

Yii 2 的视图最显著的改动是视图内的特殊变量 `$this` 不再指向当前控制器或小部件，而是指向 *视图* 对象。它是一个 2.0
中引入的全新概念。*视图* 对象为 [[yii\web\View]] 的实例，他代表了 MVC 模式中的视图部分。如果你想要在视图中访问一个控制器或者小部件，你可以使用
`$this->context`。

要在其他视图里渲染一个局部视图，你要用 `$this->render()`，而不是 `$this->renderPartial()`。`render` 的调用也发成了变化，因为
`render()` 现在只返回渲染结果，而不是直接显示它，所以现在你必须显式地把它 **echo** 出来。像这样：

```php
echo $this->render('_item', ['item' => $item]);
```

除了使用 PHP 作为主要的模板语言，Yii 2.0 也装备了两种时髦模板引擎的官方支持：Smarty 和 Twig。过去的 Prado
模板引擎不再被支持。要使用这些模板引擎，你需要配置 `view` 应用组件，给它设置 [[yii\base\View::$renderers|View::$renderers]]
属性。具体请参阅[模板引擎](tutorial-template-engines.md)章节。


模型（Model）
------

Yii 2.0使用 [[yii\base\Model]] 作为模型基类，类似于1.1的 `CModel` 。`CFormModel`
被完全弃用了，现在要创建表单模型类，可以通过继承 [[yii\base\Model]] 类来实现。

Yii 2.0 引进了名为 [[yii\base\Model::scenarios()|scenarios()]]
的新方法来声明支持的场景，并注明在哪个场景下某属性赋值必须验证，可否被视为安全赋值，等等。如：

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!role'],
    ];
}
```

上面的代码声明了两个场景：`backend` 和 `frontend` 。对于 `backend` 场景，`email` 和 `role`
属性值都是安全的，且能进行批量赋值；对于 `frontend` 场景，`email` 能批量赋值而 `role` 不能。而 `email` 和 `role` 都必须通过规则验证。

[[yii\base\Model::rules()|rules()]] 方法仍用于声明验证规则。注意，由于引进了 [[yii\base\Model::scenarios()|scenarios()]]
，现在已经没有 `unsafe` 验证器了。

大多数情况下，如果 [[yii\base\Model::rules()|rules()]] 方法内已经完整地指定场景了，那就不必覆写 [[yii\base\Model::scenarios()|scenarios()]]
，也不必声明 `unsafe` 属性值。

要了解更多有关模型的细节，请参考[模型](structure-models.md)章节。

控制器（Controller）
-----------

Yii 2.0 使用 [[yii\web\Controller]] 作为控制器的基类，类似于 1.1 的 `CWebController`。使用 [[yii\base\Action]]
作为操作类的基类。

这些变化最明显的影响是，当你在写控制器操作的代码时，你应该返回（return）要渲染的内容而不是输出（echo）它：

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

请查看 [控制器（Controller）](structure-controllers.md) 章节了解有关控制器的更多细节。


小部件（Widget）
-------

Yii 2.0 使用 [[yii\base\Widget]] 作为小部件基类，类似于1.1的 `CWidget`

为了让 IDE 更好地支持框架，Yii 2.0 引进了一个调用小部件的新语法。就是引入了 [[yii\base\Widget::begin()|begin()]]， [[yii\base\Widget::end()|end()]]
和 [[yii\base\Widget::widget()|widget()]] 三个静态方法，用法如下：

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// 注意必须 **"echo"** 结果以显示内容
echo Menu::widget(['items' => $items]);

// 传递一个用于初始化对象属性的数组
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... 表单输入栏都在这里 ...
ActiveForm::end();
```

更多细节请参阅 [小部件](structure-widgets.md)章节。


主题（Theme）
------

Themes work completely differently in 2.0. They are now based on a path mapping mechanism that maps a source
view file path to a themed view file path. For example, if the path map for a theme is
`['/web/views' => '/web/themes/basic']`, then the themed version for the view file
`/web/views/site/index.php` will be `/web/themes/basic/site/index.php`. For this reason, themes can now
be applied to any view file, even a view rendered outside of the context of a controller or a widget.

Also, there is no more `CThemeManager` component. Instead, `theme` is a configurable property of the `view`
application component.

更多细节请参考[主题](output-theming.md)章节。


控制台应用（Console Application）
--------------------

Console applications are now organized as controllers, like Web applications. Console controllers
should extend from [[yii\console\Controller]], similar to `CConsoleCommand` in 1.1.

To run a console command, use `yii <route>`, where `<route>` stands for a controller route
(e.g. `sitemap/index`). Additional anonymous arguments are passed as the parameters to the
corresponding controller action method, while named arguments are parsed according to
the declarations in [[yii\console\Controller::options()]].

Yii 2.0 supports automatic generation of command help information from comment blocks.

更多细节请参阅[控制台命令](tutorial-console.md)章节。


国际化（I18N）
----

Yii 2.0 移除了原来内置的日期格式器和数字格式器，为了方便 [PECL intl PHP module](http://pecl.php.net/package/intl) （PHP 的国际化扩展）模块的使用。

消息翻译现在由 `i18n` 应用组件执行。该组件管理一系列消息源，允许使用基于消息类别的不同消息源。

更多细节请参阅[国际化（Internationalization）](tutorial-i18n.md)章节。


操作过滤器（Action Filters）
--------------

操作的过滤现在通过行为（behavior）来实现。要定义一个新的，自定义的过滤器，请继承 [[yii\base\ActionFilter]]
类。要使用一个过滤器，需要把过滤器类作为一个 `behavior` 绑定到控制器上。比如，要使用 [[yii\filters\AccessControl]]
过滤器，你需要在控制器内添加如下代码：

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

更多细节请参考[过滤器](structure-filters.md)章节。


前端资源（Assets）
------

Yii 2.0 introduces a new concept called *asset bundle* that replaces the script package concept found in Yii 1.1.

An asset bundle is a collection of asset files (e.g. JavaScript files, CSS files, image files, etc.)
within a directory. Each asset bundle is represented as a class extending [[yii\web\AssetBundle]].
By registering an asset bundle via [[yii\web\AssetBundle::register()]], you make
the assets in that bundle accessible via the Web. Unlike in Yii 1, the page registering the bundle will automatically
contain the references to the JavaScript and CSS files specified in that bundle.

更多细节请参阅 [前端资源管理（Asset）](structure-assets.md) 章节。


助手类（Helpers）
-------

Yii 2.0 很多常用的静态助手类，包括：

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]
* [[yii\helpers\Security]]

请参考 [助手一览](helper-overview.md) 章节来了解更多。

表单
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

请参考 [创建表单](input-forms.md) 章节来了解更多细节。


查询生成器（Query Builder）
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

请参考[查询生成器（Query Builder）](db-query-builder.md) 章节了解更多内容。


活动记录（Active Record）
-------------

Yii 2.0 的[活动记录](db-active-record.md)改动了很多。两个最显而易见的改动分别涉及构建查询（query
building）和关联查询的处理（relational query handling）。

在 1.1 中的 `CDbCriteria` 类在 Yii 2 中被 [[yii\db\ActiveQuery]] （活动查询）所替代。这个类是继承自
[[yii\db\Query]]，且继承了所有的
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

There where some problems with overriding the constructor of an ActiveRecord class in 1.1. These are not present in
version 2.0 anymore. Note that when adding parameters to the constructor you might have to override [[yii\db\ActiveRecord::instantiate()]].

活动记录方面还有很多其他的变化与改进，请参考 [活动记录](db-active-record.md) 章节以了解更多细节。


用户及身份验证接口（IdentityInterface）
-------------------------------------

1.1 中的 `CWebUser` 类现在被 [[yii\web\User]] 所取代，随之 `CUserIdentity` 类也不在了。与之相对的，为达到相同目的，你可以实现
[[yii\web\IdentityInterface]] 接口，它使用起来更直观。在高级应用模版里提供了一个这么样的一个例子。

要了解更多细节请参考 [认证（Authentication）](security-authentication.md)，[授权（Authorization）](security-authorization.md) 以及
[高级应用模版](tutorial-advanced-app.md) 这三个章节。


URL 管理
--------

Yii 2.0 的 URL 管理跟 1.1 中很像。一个主要的改进是现在的 URL 管理支持 **可选参数** 了。比如，如果你在 2.0 中定义了一个下面这样的规则，那么它可以同时匹配
`post/popular` 和 `post/1/popular` 两种 URL。而在 1.1 中为达成相同效果，必须要使用两条规则。

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

请参考 [URL 解析和生成](runtime-url-handling.md) 章节，以了解更多细节。.

同时使用 Yii 1.1 和 2.x
----------------------

如果你遗留有一些 Yii 1.1 的代码，需要跟 Yii 2.0 一起使用，你可以参考 [1.1 和 2.0 共用](extend-using-v1-v2.md) 章节。
