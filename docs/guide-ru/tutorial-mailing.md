Отправка почты
=======

> Note: Этот раздел находится в стадии разработки.

Yii позволяет оформлять и посылать E-mail сообщения. Однако, ядро фреимворка предоставляет только
функциональность оформления и основной интерфейс. Фактический механизм отправки почты должен быть предоставлен с помощью расширения, потому что различным проектам могут потребоваться различные реализации и обычно они зависят от внешних сервисов и бибилотек.

Для наиболее распространенных ситуаций вы можете использовать официальное расширение [yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer).


Настройка
-------------

Настройка почтового компонента зависит от расширения, которое вы выбрали.
В целом настройка вашего приложения должна выглядеть так:

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


Основы использования
-----------

Когда 'mailer' компонент настроен, вы можете использовать следующий код, чтобы отправить почтовое сообщение:

```php
Yii::$app->mailer->compose()
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Тема сообщения')
    ->setTextBody('Текст сообщения')
    ->setHtmlBody('<b>текст сообщения в формате HTML</b>')
    ->send();
```

В показанном выше примере метод `compose()` создает экземпляр почтового сообщения, который затем заполняется и отправляется.
Вы можете использовать более сложную логику в этом процессе, если вам понадобится:

```php
$message = Yii::$app->mailer->compose();
if (Yii::$app->user->isGuest) {
    $message->setFrom('from@domain.com')
} else {
    $message->setFrom(Yii::$app->user->identity->email)
}
$message->setTo(Yii::$app->params['adminEmail'])
    ->setSubject('Тема сообщения')
    ->setTextBody('Текст сообщения')
    ->send();
```

> Note: каждое 'mailer' расширение имеет два главных класса: 'Mailer' и 'Message'. 'Mailer' всегда знает имя класса и специфику 'Message'. Не пытайтесь создать экземпляр объекта 'Message' напрямую -
  всегда используйте для этого метод `compose()`.

Вы также можете послать несколько сообщений за раз:

```php
$messages = [];
foreach ($users as $user) {
    $messages[] = Yii::$app->mailer->compose()
        // ...
        ->setTo($user->email);
}
Yii::$app->mailer->sendMultiple($messages);
```

В некоторых почтовых расширениях этот подход может быть полезен, так как использует одиночное сетевое сообщение.


Компоновка почтовых сообщений
----------------------

Yii предоставляет возможность оформления содержания почтовых сообщений через специальные файлы виды.
По умолчанию эти файлы должны быть расположены в директории '@app/mail'.

Пример содержания почтового файла вида:

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

Для того, чтобы оформить содержание сообщения через файл вида, просто передайте название файла вида в `compose()` метод:

```php
Yii::$app->mailer->compose('home-link') // здесь устанавливается результат рендеринга вида в тело сообщения
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Message subject')
    ->send();
```

Вы можете передать допольнительный параметр, относящийся к виду в `compose()` метод, который будет доступен внутри файла вида:

```php
Yii::$app->mailer->compose('greetings', [
    'user' => Yii::$app->user->identity,
    'advertisement' => $adContent,
]);
```

Вы можете указать разные файлы видов для HTML и простого текста в содержании сообщения:

```php
Yii::$app->mailer->compose([
    'html' => 'contact-html',
    'text' => 'contact-text',
]);
```

Если вы укажете название вида как строку, результат рендеринга в теле сообщения будет использоваться как HTML, в то время как при обычном тексте в теле сообщения при компоновке будут удаляться все HTML теги.

Результат рендеринга вида может быть вставлен в макет (layout), который может быть установлен, используя [[yii\mail\BaseMailer::htmlLayout]]
и [[yii\mail\BaseMailer::textLayout]]. Это будет работать аналогично макетам в обычном веб приложении.
Макет может использовать CSS стили или другие общие элементы страниц для использования в сообщении:

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


Прикрепление файлов
---------------

Вы можете прикрепить вложения к сообщению с помощью методов `attach()` и `attachContent()`:

```php
$message = Yii::$app->mailer->compose();

// Прикрепление файла из локальной файловой системы:
$message->attach('/path/to/source/file.pdf');

// Прикрепить файл на лету
$message->attachContent('Attachment content', ['fileName' => 'attach.txt', 'contentType' => 'text/plain']);
```


Вложение изображений
----------------

Вы можете вставить изображения в содержание сообщения через `embed()` метод. Этот метод возвращает id прикрепленной картинки,
которые должны быть доступны в 'img' тегах.
Этот метод легко использовать, когда сообщение составляется через файлы представления:

```php
Yii::$app->mailer->compose('embed-email', ['imageFileName' => '/path/to/image.jpg'])
    // ...
    ->send();
```

Внутри файла представления вы можете использовать следующий код:

```php
<img src="<?= $message->embed($imageFileName); ?>">
```


Тестирование и отладка
---------------------

Разработчикам часто надо проверять, что почтовые сообщения отправляются из приложения, их содержание и так далее.
Такая возможность предоставляется в Yii через `yii\mail\BaseMailer::useFileTransport`. Если это опция включена, то она принудительно сохраняет данные почтовых сообщений в локальный файл вместо его отправки. Эти файлы будут сохранены в директории
`yii\mail\BaseMailer::fileTransportPath`, которая по умолчанию '@runtime/mail'.

> Note: вы можете либо сохранить сообщения в файл, либо послать его фактическим получателям, но не используйте оба варианта одновременно.

Файл почтового сообщения может быть открыт обычным текстовым редактором, также вы можете просматривать фактические заголовки сообщений, их содержание и так далее.
Этот механизм может понадобиться во время отладки приложения, либо прогонки unit тестов.

> Note: содержание файла почтового сообщения формируется через `\yii\mail\MessageInterface::toString()`, правда это зависит от почтового расширения, которое вы используете в своем приложении.


Создание вашего собственного решения
-------------------------------

Для того, чтобы создать свое собственное решение, вам надо будет создать два класса: одно для 'Mailer' и другое для 'Message'.
Вы можете использовать `yii\mail\BaseMailer` и `yii\mail\BaseMessage` как базовые классы для вашего решения. Эти классы уже содержат базовую логику, которая описана в этом руководстве. Однако, их использование не обязательно, достаточно унаследоваться от `yii\mail\MailerInterface` и `yii\mail\MessageInterface` интерфейсов.
Затем вам необходимо обеспечить выполнение всех абстрактных методов этих интерфейсов для построения вашего решения.
