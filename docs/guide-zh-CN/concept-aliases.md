别名（Aliases）
=============

别名用来表示文件路径和 URL，这样就避免了在代码中硬编码一些绝对路径和 URL。
一个别名必须以 `@` 字符开头，以区别于传统的文件路径和 URL。
没有前导 `@` 定义的别名将以 `@` 字符作为前缀。

Yii 预定义了大量可用的别名。例如，别名 `@yii` 指的是 Yii 框架本身的安装目录，
而 `@web` 表示的是当前运行应用的根 URL。

定义别名（Defining Aliases） <span id="defining-aliases"></span>
-------------------------

你可以调用 [[Yii::setAlias()]] 来给文件路径或 URL 定义别名：

```php
// 文件路径的别名
Yii::setAlias('@foo', '/path/to/foo');

// URL 的别名
Yii::setAlias('@bar', 'https://www.example.com');

// 包含 \foo\Bar 类的具体文件的别名
Yii::setAlias('@foo/Bar.php', '/definitely/not/foo/Bar.php');
```

> Note: 别名所指向的文件路径或 URL 不一定是真实存在的文件或资源。

可以通过在一个别名后面加斜杠 `/` 和一至多个路径分段生成新别名（无需调用 [[Yii::setAlias()]]）。
们把通过 [[Yii::setAlias()]] 定义的别名称为**根别名**，
而用他们衍生出去的别名成为**衍生别名**。例如，`@foo` 就是根别名，
而 `@foo/bar/file.php` 是一个衍生别名。

你还可以用别名去定义新别名（根别名与衍生别名均可）：

```php
Yii::setAlias('@foobar', '@foo/bar');
```

根别名通常在[引导](runtime-bootstrapping.md)阶段定义。
比如你可以在[入口脚本](structure-entry-scripts.md)里调用 [[Yii::setAlias()]]。为了方便起见，
[应用](structure-applications.md)提供了一个名为 `aliases` 的可写属性，
你可以在应用[配置](concept-configurations.md)中设置它，就像这样：

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/path/to/foo',
        '@bar' => 'https://www.example.com',
    ],
];
```


解析别名（Resolving Aliases） <span id="resolving-aliases"></span>
--------------------------

你可以调用 [[Yii::getAlias()]] 命令来解析根别名到对应的文件路径或 URL。
同样的页面也可以用于解析衍生别名。例如：

```php
echo Yii::getAlias('@foo');               // 输出：/path/to/foo
echo Yii::getAlias('@bar');               // 输出：https://www.example.com
echo Yii::getAlias('@foo/bar/file.php');  // 输出：/path/to/foo/bar/file.php
```

由衍生别名所解析出的文件路径和 URL 
是通过替换掉衍生别名中的根别名部分得到的。

> Note: [[Yii::getAlias()]] 并不检查结果路径/URL 所指向的资源是否真实存在。


根别名可能也会包含斜杠 `/`。
[[Yii::getAlias()]] 足够智能到判断一个别名中的哪部分是根别名，因此能正确解析文件路径/URL。
例如：

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
echo Yii::getAlias('@foo/test/file.php');  // 输出：/path/to/foo/test/file.php
echo Yii::getAlias('@foo/bar/file.php');   // 输出：/path2/bar/file.php
```

若 `@foo/bar` 未被定义为根别名，最后一行语句会显示为 `/path/to/foo/bar/file.php`。


使用别名（Using Aliases） <span id="using-aliases"></span>
----------------------

别名在 Yii 的很多地方都会被正确识别，
无需调用 [[Yii::getAlias()]] 来把它们转换为路径/URL。
例如，[[yii\caching\FileCache::cachePath]] 能同时接受文件路径或是指向文件路径的别名，
因为通过 `@` 前缀能区分它们。

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

请关注 API 文档了解特定属性或方法参数是否支持别名。


预定义的别名（Predefined Aliases） <span id="predefined-aliases"></span>
------------------------------

Yii 预定义了一系列别名来简化常用路径和 URL 的使用：

- `@yii`，`BaseYii.php` 文件所在的目录（也被称为框架安装目录）。
- `@app`，当前运行的应用 [[yii\base\Application::basePath|根路径（base path）]]。
- `@runtime`，当前运行的应用的 [[yii\base\Application::runtimePath|运行环境（runtime）路径]]。默认为 `@app/runtime`。
- `@webroot`，当前运行的Web应用程序的Web根目录。
  它是根据包含 [入口脚本](structure-entry-scripts.md) 的目录确定的。
- `@web`，当前运行的Web应用程序的 base URL。它的值与 [[yii\web\Request::baseUrl]] 相同。
- `@vendor`，[[yii\base\Application::vendorPath|Composer vendor 目录]]。
- `@bower`，包含 [bower 包](https://bower.io/) 的根目录。默认为 `@vendor/bower`。
- `@npm`，包含 [npm 包](https://www.npmjs.com/) 的根目录。默认为 `@vendor/npm`。

`@yii` 别名是在[入口脚本](structure-entry-scripts.md)里包含 `Yii.php` 文件时定义的，
其他的别名都是在[配置应用](concept-configurations.md)的时候，
于应用的构造方法内定义的。

> Note: `@web` 和 `@webroot` 别名，因为它们的描述表明是在 [[yii\web\Application|Web application]] 中定义的，因此默认情况下不适用于 [[yii\console\Application|Console application]] 应用程序。

扩展的别名（Extension Aliases） <span id="extension-aliases"></span>
----------------------------

每一个通过 Composer 安装的 [扩展](structure-extensions.md) 都自动添加了一个别名。
该别名会以该扩展在 `composer.json` 文件中所声明的根命名空间为名，
且他直接代指该包的根目录。例如，如果你安装有 `yiisoft/yii2-jui` 扩展，会自动得到 `@yii/jui` 别名，
它定义于[引导启动](runtime-bootstrapping.md)阶段：

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```
