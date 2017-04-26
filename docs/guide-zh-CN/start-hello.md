说声 Hello
============

本章描述了如何在你的应用中创建一个新的 “Hello” 页面。
为了实现这一目标，
将会创建一个[动作](structure-controllers.md#creating-actions)和一个[视图](structure-views.md)：

* 应用将会分派页面请求给动作
* 动作将会依次渲染视图呈现 “Hello” 给最终用户

贯穿整个章节，你将会掌握三件事：

1. 如何创建一个[动作](structure-controllers.md)去响应请求，
2. 如何创建一个[视图](structure-views.md)去构造响应内容，
3. 以及一个应用如何分派请求给[动作](structure-controllers.md#creating-actions)。


创建动作 <span id="creating-action"></span>
------------------

对于“Hello”任务，需要创建一个 `say` [动作](structure-controllers.md#creating-actions)，
从请求中接收 `message` 参数并显示给最终用户。如果请求没有提供 `message` 参数，
动作将显示默认参数 “Hello”。

> 注意：[动作](structure-controllers.md#creating-actions)是最终用户可以直接访问并执行的对象。
  动作被组织在[控制器](structure-controllers.md)中。
  一个动作的执行结果就是最终用户收到的响应内容。

动作必须声明在[控制器](structure-controllers.md)中。为了简单起见，
你可以在现存的 `SiteController` 控制器里声明 `say` 动作。这个控制器定义在 `controllers/SiteController.php` 类文件中。
以下是一个动作的声明：

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...现存的代码...

    public function actionSay($message = 'Hello')
    {
        return $this->render('say', ['message' => $message]);
    }
}
```

在上述 `SiteController` 代码中，`say` 动作被定义为 `actionSay` 方法。
Yii 使用 `action` 前缀区分普通方法和动作。
`action` 前缀后面的名称被映射为动作的 ID。

涉及到给动作命名时，你应该理解 Yii 如何处理操作 ID。
动作 ID 总是被以小写处理，如果一个操作 ID 由多个单词组成，
单词之间将由破折号连接（如 `create-comment`）。动作 ID 映射为方法名时移除了破折号，
将每个单词首字母大写，并加上 `action` 前缀。 
比如：动作 ID `create-comment` 对应方法名 `actionCreateComment`。

上述代码中的动作方法接受一个参数 `$message`，
它的默认值是 `“Hello”`（就像你设置 PHP 中其它函数或方法的默认值一样）。
当应用接收到请求并确定由 `say` 动作来响应请求时，应用将从请求的参数中寻找对应值传入进来。
换句话说，如果请求包含一个 `message` 参数，它的值是 `“Goodybye”`，
动作方法中的 `$message` 变量也将赋值为 `“Goodbye”`。

在动作方法中，[[yii\web\Controller::render()|render()]] 被用来渲染一个名为
`say` 的[视图](structure-views.md)文件。
`message` 参数也被传入视图，这样就可以在里面使用。动作方法会返回渲染结果。
结果会被应用接收并显示给最终用户的浏览器（作为整页 HTML 的一部分）。


创建视图 <span id="creating-view"></span>
---------------

[视图](structure-views.md)是你用来生成响应内容的脚本。为了说 “Hello”，
你需要创建一个 `say` 视图，以便显示从动作方法中传来的 `message` 参数。

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

`say` 视图应该存为 `views/site/say.php` 文件。当一个动作中调用了 [[yii\web\Controller::render()|render()]] 方法时，
它将会寻找名为 `views/控制器 ID/视图名.php` 的PHP文件。。

注意以上代码，`message` 参数在输出之前被 [[yii\helpers\Html::encode()|HTML-encoded]] 方法处理过。
这很有必要，当参数来自于最终用户时，
参数中可能隐含的恶意 JavaScript 代码会导致
[跨站脚本（XSS）攻击](http://en.wikipedia.org/wiki/Cross-site_scripting)。

当然了，你大概会在 `say` 视图里放入更多内容。内容可以由 HTML 标签，纯文本，
甚至 PHP 语句组成。实际上 `say` 视图就是一个由 [[yii\web\Controller::render()|render()]] 执行的 PHP 脚本。
视图脚本输出的内容将会作为响应结果返回给应用。应用将依次输出结果给最终用户。


试运行 <span id="trying-it-out"></span>
-------------

创建完动作和视图后，你就可以通过下面的 URL 访问新页面了：

```
http://hostname/index.php?r=site/say&message=Hello+World
```

![Hello World](images/start-hello-world.png)

这个 URL 将会输出包含 “Hello World” 的页面，页面和应用里的其它页面使用同样的头部和尾部。

如果你省略 URL 中的 `message` 参数，将会看到页面只显示 “Hello”。
这是因为 `message` 被作为一个参数传给 `actionSay()` 方法，当省略它时，参数将使用默认的 `“Hello”` 代替。

> 注意：新页面和其它页面使用同样的头部和尾部是因为 [[yii\web\Controller::render()|render()]] 方法会
  自动把 `say` 视图执行的结果嵌入称为[布局](structure-views.md#layouts)的文件中，
  位于 `views/layouts/main.php`。

上面 URL 中的参数 `r` 需要更多解释。
它代表[路由](runtime-routing.md)，是整个应用级的，
指向特定动作的独立 ID。路由格式是 `控制器 ID/操作 ID`。应用接受请求的时候会检查参数，
使用控制器 ID 去确定哪个控制器应该被用来处理请求。
然后相应控制器将使用动作 ID 去确定哪个操作方法将被用来做具体工作。
在本例子中，路由 `site/say` 将被解析至 `SiteController` 控制器和其中的 `say` 动作。
因此 `SiteController::actionSay()` 方法将被调用处理请求。

> 注意：与动作一样，一个应用中控制器同样有唯一的 ID。
  控制器 ID 和动作 ID 使用同样的命名规则。
  控制器的类名源自于控制器 ID，移除了破折号
  ，每个单词首字母大写，并加上 `Controller` 后缀。
  例子：控制器 ID `post-comment` 对应控制器类名 `PostCommentController`。


总结 <span id="summary"></span>
-------

通过本章节你接触了 MVC 设计模式中的控制器和视图部分。
创建了一个动作作为控制器的一部分去处理特定请求。然后又创建了一个视图去构造响应内容。
在这个小例子中，没有模型调用，唯一涉及到数据的地方是 `message` 参数。

你同样学习了 Yii 路由的相关内容，它是用户请求与控制器动作之间的桥梁。

在下一章节中，你将学习如何创建一个模型，以及添加一个包含 HTML 表单的页面。
