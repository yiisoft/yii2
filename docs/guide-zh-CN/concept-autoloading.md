类自动加载（Autoloading）
======================

Yii 依靠[类自动加载机制](https://www.php.net/manual/zh/language.oop5.autoload.php)来定位和包含所需的类文件。
它提供一个高性能且完美支持[PSR-4 标准](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
的自动加载器。
该自动加载器会在引入框架文件 `Yii.php` 时安装好。

> Note: 为了简化叙述，本篇文档中我们只会提及类的自动加载。
  不过，要记得文中的描述同样也适用于接口和Trait（特质）的自动加载哦。


使用 Yii 自动加载器 <span id="using-yii-autoloader"></span>
-----------------

要使用 Yii  的类自动加载器，你需要在创建和命名类的时候遵循两个简单的规则：

* 每个类都必须置于命名空间之下 (比如 `foo\bar\MyClass`)。
* 每个类都必须保存为单独文件，且其完整路径能用以下算法取得：

```php
// $className 是一个开头包含反斜杠的完整类名（译注：请自行谷歌：fully qualified class name）
$classFile = Yii::getAlias('@' . str_replace('\\', '/', $className) . '.php');
```

举例来说，若某个类名为 `foo\bar\MyClass`，对应类的文件路径[别名](concept-aliases.md)会是 `@foo/bar/MyClass.php`。
为了让该别名能被正确解析为文件路径，`@foo` 或 `@foo/bar`
中的一个必须是[根别名](concept-aliases.md#defining-aliases)。

当我们使用[基本应用模版](start-installation.md)时，可以把你的类放置在顶级命名空间 `app` 下，这样它们就可以被 Yii 自动加载，
而无需定义一个新的别名。这是因为 `@app` 本身是一个[预定义别名](concept-aliases.md#predefined-aliases)，
且类似于 `app\components\MyClass` 这样的类名，
基于我们刚才所提到的算法，可以正确解析出 `AppBasePath/components/MyClass.php` 路径。

在[高级应用模版](tutorial-advanced-app.md)里，每一逻辑层级会使用他自己的根别名。
比如，前端层会使用 `@frontend` 而后端层会使用 `@backend`。
因此，你可以把前端的类放在 `frontend` 命名空间，而后端的类放在 `backend`。 
这样这些类就可以被 Yii 自动加载了。

要将自定义命名空间添加到自动加载器，您需要使用 [[Yii::setAlias()]] 为命名空间的根目录定义别名。
例如，要加载位于 `path/to/foo` 目录中 `foo` 命名空间中的类，您将调用 `Yii::setAlias('@foo', 'path/to/foo')`。

类映射表（Class Map） <span id="class-map"></span>
------------------

Yii 类自动加载器支持**类映射表**功能，该功能会建立一个从类的名字到类文件路径的映射。
当自动加载器加载一个文件时，他首先检查映射表里有没有该类。
如果有，对应的文件路径就直接加载了，省掉了进一步的检查。这让类的自动加载变得超级快。
事实上所有的 Yii 核心类都是这样加载的。

你可以用 `Yii::$classMap` 方法向映射表中添加类，

```php
Yii::$classMap['foo\bar\MyClass'] = 'path/to/MyClass.php';
```

[别名](concept-aliases.md)可以被用于指定类文件的路径。你应该在[引导启动](runtime-bootstrapping.md)的过程中设置类映射表，
这样映射表就可以在你使用具体类之前就准备好。


用其他自动加载器 <span id="using-other-autoloaders"></span>
-----------------------

因为 Yii 完全支持 Composer 管理依赖包，所以推荐你也同时安装 Composer 的自动加载器，
如果你用了一些自带自动加载器的第三方类库，
你应该也安装下它们。

当你同时使用其他自动加载器和 Yii 自动加载器时，应该在其他自动加载器安装成功**之后**，
再包含 `Yii.php` 文件。这将使 Yii 成为第一个响应任何类自动加载请求的自动加载器。
举例来说，以下代码提取自[基本应用模版](start-installation.md)的
[入口脚本](structure-entry-scripts.md) 。
第一行安装了 Composer 的自动加载器，第二行才是 Yii 的自动加载器：

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
```

你也可以只使用 Composer 的自动加载，而不用 Yii 的自动加载。
不过这样做的话，类的加载效率会下降，
且你必须遵循 Composer 所设定的规则，从而让你的类满足可以被自动加载的要求。

> Info: 若你不想要使用 Yii 的自动加载器，你必须创建一个你自己版本的 `Yii.php` 文件，
并把它包含进你的[入口脚本](structure-entry-scripts.md)里。


自动加载扩展类 <span id="autoloading-extension-classes"></span>
-----------------------------

Yii 自动加载器支持自动加载[扩展](structure-extensions.md)的类。唯一的要求是它需要在 `composer.json` 文件里正确地定义 `autoload` 部分。
请参考 [Composer 文档](https://getcomposer.org/doc/04-schema.md#autoload)，
来了解如何正确描述 `autoload` 的更多细节。

在你不使用 Yii 的自动加载器时，Composer 的自动加载器仍然可以帮你自动加载扩展内的类。
