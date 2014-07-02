别名（Aliases）
=======

别名（译者注：指路径/URL 别名，简称别名）用作代表文件路径和 URL，主要为了避免在代码中硬编码一些绝对路径和 
URL。一个别名必须以 `@` 字符开头，以区别于传统的文件/目录路径或 URL。举栗，别名 `@yii` 
指的是 Yii 框架本身的安装目录，而 `@web` 表示的是当前运行应用的根 URL（base URL）。


定义别名 <a name="defining-aliases"></a>
----------------

你可以调用 [[Yii::setAlias()]] 来给指定路径/URL 定义别名。栗子：

```php
// 文件路径的别名
Yii::setAlias('@foo', '/path/to/foo');

// URL 的别名
Yii::setAlias('@bar', 'http://www.example.com');
```

> 注意：别名所指向的文件路径或 URL 不一定是真实存在的文件或资源哦。

用一个别名，你能通过在后面接续斜杠 `/` 以及若干路径片段得到一个新的别名（无需调用 
[[Yii::setAlias()]]）。我们把通过 [[Yii::setAlias()]] 定义的别名成为根别名 
*root aliases*，而用他们衍生出去的别名成为衍生别名 *derived aliases*。比如，`@foo` 就是跟别名，而 `@foo/bar/file.php` 
是一个衍生别名。

你还可以用别名定义新别名（根别名与衍生别名均可）：

```php
Yii::setAlias('@foobar', '@foo/bar');
```

根别名通常在 [引导（bootstrapping）](runtime-bootstrapping.md) 阶段定义。比如你可以在 
[入口脚本](structure-entry-scripts.md) 里调用 [[Yii::setAlias()]]。为了方便起见呢，[应用主体（Application）](structure-applications.md) 
提供了一个名为 `aliases` 的可写属性，你可以在应用[配置文件](concept-configurations.md)中设置它，就像这样：

```php
return [
    // ...
    'aliases' => [
        '@foo' => '/path/to/foo',
        '@bar' => 'http://www.example.com',
    ],
];
```


解析别名 <a name="resolving-aliases"></a>
-----------------

你可以调用 [[Yii::getAlias()]] 命令来解析一个根别名到他所对应的文件路径或 URL。同样的页面也可以用于解析衍生别名。比如：

```php
echo Yii::getAlias('@foo');               // 显示：/path/to/foo
echo Yii::getAlias('@bar');               // 显示：http://www.example.com
echo Yii::getAlias('@foo/bar/file.php');  // 显示：/path/to/foo/bar/file.php
```

由衍生别名所代指的路径/URL 是通过替换掉衍生别名中的根别名部分得到的。

> 注意：[[Yii::getAlias()]] 不检查结果路径/URL 所指向的资源是否真实存在。

根别名可能也会包含斜杠 `/` 字符。[[Yii::getAlias()]] 足够聪明，能知道一个别名中的哪个部分是根别名，因此能正确解析文件路径/URL。比如：

```php
Yii::setAlias('@foo', '/path/to/foo');
Yii::setAlias('@foo/bar', '/path2/bar');
Yii::getAlias('@foo/test/file.php');  // 显示：/path/to/foo/test/file.php
Yii::getAlias('@foo/bar/file.php');   // 显示：/path2/bar/file.php
```

若 `@foo/bar` 未被定义为根别名，最后一行语句会显示为 `/path/to/foo/bar/file.php`。


使用别名 <a name="using-aliases"></a>
-------------

别名在 Yii 的很多地方都会被正确识别，而无需调用 [[Yii::getAlias()]] 
来把它们转换为路径/URL。比如，[[yii\caching\FileCache::cachePath]] 能同时接受文件路径或是代表文件路径的别名，多亏了 `@` 前缀，它区分开了文件路径与别名。

```php
use yii\caching\FileCache;

$cache = new FileCache([
    'cachePath' => '@runtime/cache',
]);
```

请关注下 API 文档了解属性或方法参数是否支持别名。


预定义的别名 <a name="predefined-aliases"></a>
------------------

Yii 预定义了一系列别名来简化频繁引用常用路径和 URL的需求。
在核心框架中已经预定义有以下别名：

- `@yii` - `BaseYii.php` 文件所在的目录（也被称为框架安装目录）
- `@app` - 当前运行的应用 [[yii\base\Application::basePath|根路径（base path）]] 
- `@runtime` - 当前运行的应用的 [[yii\base\Application::runtimePath|运行环境（runtime）路径]] 
- `@vendor` - [[yii\base\Application::vendorPath|Composer 供应商目录]]
- `@webroot` - 当前运行应用的 Web 入口目录
- `@web` - 当前运行应用的根 URL

`@yii` 别名是在[入口脚本](structure-entry-scripts.md)里包含 `Yii.php` 文件时定义的，其他的别名都是在[配置应用](concept-configurations.md)的时候，于应用的构造器内定义的。


扩展的别名 <a name="extension-aliases"></a>
-----------------

每一个通过 Composer 安装的 [扩展](structure-extensions.md) 都自动添加了一个别名。该别名会以该扩展在 `composer.json` 
文件中所声明的根命名空间为名，且他直接代指该包的根目录。比如，如果你安装有 `yiisoft/yii2-jui` 扩展，你会自动得到 
`@yii/jui` 别名，它定义于[引导启动](runtime-bootstrapping.md)阶段：

```php
Yii::setAlias('@yii/jui', 'VendorPath/yiisoft/yii2-jui');
```