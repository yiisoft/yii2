收发邮件
========

> Note: 本节正在开发中。

Yii 支持组成和发送电子邮件。然而，该框架提供的只有内容组成功能和基本接口。
实际的邮件发送机制可以通过扩展提供，
因为不同的项目可能需要不同的实现方式，
它通常取决于外部服务和库。

大多数情况下你可以使用 [yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer) 官方扩展。


配置
-------

邮件组件配置取决于你所使用的扩展。
一般来说你的应用程序配置应如下：

```php
return [
    //....
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
    ],
];
```


基本用法
---------

一旦 “mailer” 组件被配置，可以使用下面的代码来发送邮件：

```php
Yii::$app->mailer->compose()
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Message subject')
    ->setTextBody('Plain text content')
    ->setHtmlBody('<b>HTML content</b>')
    ->send();
```

在上面的例子中所述的 `compose()` 方法创建了电子邮件消息，这是填充和发送的一个实例。
如果需要的话在这个过程中你可以用上更复杂的逻辑：

```php
$message = Yii::$app->mailer->compose();
if (Yii::$app->user->isGuest) {
    $message->setFrom('from@domain.com')
} else {
    $message->setFrom(Yii::$app->user->identity->email)
}
$message->setTo(Yii::$app->params['adminEmail'])
    ->setSubject('Message subject')
    ->setTextBody('Plain text content')
    ->send();
```

> Note: 每个 “mailer” 的扩展也有两个主要类别：“Mailer” 
  和 “Message”。 “Mailer” 总是知道类名和具体的 “Message”。
  不要试图直接实例 “Message” 对象 - 而是始终使用 `compose()` 方法。

你也可以一次发送几封邮件：

```php
$messages = [];
foreach ($users as $user) {
    $messages[] = Yii::$app->mailer->compose()
        // ...
        ->setTo($user->email);
}
Yii::$app->mailer->sendMultiple($messages);
```

一些特定的扩展可能会受益于这种方法，使用单一的网络消息等。


撰写邮件内容
------------

Yii 允许通过特殊的视图文件来撰写实际的邮件内容。默认情况下，
这些文件应该位于 “@app/mail” 路径。

一个邮件视图内容的例子：

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\BaseMessage instance of newly created mail message */

?>
<h2>This message allows you to visit our site home page by one click</h2>
<?= Html::a('Go to home page', Url::home('http')) ?>
```

为了通过视图文件撰写正文可传递视图名称到 `compose()` 方法中：

```php
Yii::$app->mailer->compose('home-link') // 渲染一个视图作为邮件内容
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Message subject')
    ->send();
```

你也可以在 `compose()` 方法中传递一些视图所需参数，这些参数可以在视图文件中使用：

```php
Yii::$app->mailer->compose('greetings', [
    'user' => Yii::$app->user->identity,
    'advertisement' => $adContent,
]);
```

你可以指定不同的视图文件的 HTML 和纯文本邮件内容：

```php
Yii::$app->mailer->compose([
    'html' => 'contact-html',
    'text' => 'contact-text',
]);
```

如果指定视图名称为纯字符串，它的渲染结果将被用来作为 HTML Body，
同时纯文本正文将被删除所有 HTML 实体。

视图渲染结果可以被包裹进布局，可使用 [[yii\mail\BaseMailer::htmlLayout]] 和 [[yii\mail\BaseMailer::textLayout]] 来设置。
它的运行方式跟常规应用程序的布局是一样的。
布局可用于设置邮件 CSS 样式或其他共享内容：

```php
<?php
use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>" />
    <style type="text/css">
        .heading {...}
        .list {...}
        .footer {...}
    </style>
    <?php $this->head() ?>
</head>
<body>
    <?php $this->beginBody() ?>
    <?= $content ?>
    <div class="footer">With kind regards, <?= Yii::$app->name ?> team</div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```


文件附件
---------

你可以使用 `attach()` 和 `attachContent()` 方法来添加附件的信息：

```php
$message = Yii::$app->mailer->compose();

// 附件来自本地文件
$message->attach('/path/to/source/file.pdf');

// 动态创建一个文件附件
$message->attachContent('Attachment content', ['fileName' => 'attach.txt', 'contentType' => 'text/plain']);
```


嵌入图片
---------

你可以使用 `embed()` 方法将图片插入到邮件内容。
此方法返回会图片 ID ，这将用在“img”标签中。
当通过视图文件来写信时，这种方法易于使用：

```php
Yii::$app->mailer->compose('embed-email', ['imageFileName' => '/path/to/image.jpg'])
    // ...
    ->send();
```

然后在该视图文件中，你可以使用下面的代码：

```php
<img src="<?= $message->embed($imageFileName); ?>">
```


测试和调试
-----------

开发人员常常要检查一下，有什么电子邮件是由应用程序发送的，他们的内容是什么等。
这可通过 `yii\mail\BaseMailer::useFileTransport` 来检查。
如果开启这个选项，会把邮件信息保存在本地文件而不是发送它们。
这些文件保存在 `yii\mail\BaseMailer::fileTransportPath` 中，默认在 '@runtime/mail' 。

> Tip: 你可以保存这些信息到本地文件或者把它们发送出去，但不能同时两者都做。

邮件信息文件可以在一个普通的文本编辑器中打开，这样你就可以浏览实际的邮件标题，内容等。
这种机制可以用来调试应用程序或运行单元测试。

> Tip: 该邮件信息文件是会被 `\yii\mail\MessageInterface::toString()` 转成字符串保存的，
  它依赖于实际在应用程序中使用的邮件扩展。


创建自己的邮件解决方案
------------------------

为了创建你自己的邮件解决方案，你需要创建两个类，一个用于 “Mailer”，另一个用于 “Message”。
你可以使用 `yii\mail\BaseMailer` 和 `yii\mail\BaseMessage` 作为基类。
这些类已经实现了基本的逻辑，这在本指南中有介绍。
然而，它们的使用不是必须的，
它实现了 `yii\mail\MailerInterface` 和 `yii\mail\MessageInterface` 接口。
然后，你需要实现所有 abstract 方法来构建解决方案。
