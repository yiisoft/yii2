Mailing
=======

> Note: This section is under development.

Yii supports composition and sending of the email messages. However, the framework core provides
only the content composition functionality and basic interface. Actual mail sending mechanism should
be provided by the extension, because different projects may require its different implementation and
it usually depends on the external services and libraries.

For the most common cases you can use [yii2-swiftmailer](https://github.com/yiisoft/yii2/tree/master/extensions/swiftmailer) official extension.


Configuration
-------------

Mail component configuration depends on the extension you have chosen.
In general your application configuration should look like:

```php
return [
    //....
    'components' => [
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
    ],
];
```


Basic usage
-----------

Once 'mail' component is configured, you can use the following code to send an email message:

```php
Yii::$app->mail->compose()
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Message subject')
    ->setTextBody('Plain text content')
    ->setHtmlBody('<b>HTML content</b>')
    ->send();
```

In above example method `compose()` creates an instance of the mail message, which then is populated and sent.
You may put more complex logic in this process if needed:

```php
$message = Yii::$app->mail->compose();
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

> Note: each 'mail' extension comes in 2 major classes: 'Mailer' and 'Message'. 'Mailer' always knows
  the class name and specific of the 'Message'. Do not attempt ot instantiate 'Message' object directly -
  always use `compose()` method for it.

You may also send several messages at once:

```php
$messages = [];
foreach ($users as $user) {
    $messages[] = Yii::$app->mail->compose()
        // ...
        ->setTo($user->email);
}
Yii::$app->mail->sendMultiple($messages);
```

Some particular mail extensions may benefit from this approach, using single network message etc.


Composing mail content
----------------------

Yii allows composition of the actual mail messages content via special view files.
By default these files should be located at '@app/mail' path.

Example mail view file content:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var \yii\web\View $this view component instance
 * @var \yii\mail\BaseMessage $message instance of newly created mail message
 */
?>
<h2>This message allows you to visit out site home page by one click</h2>
<?= Html::a('Go to home page', Url::home('http')) ?>
```

In order to compose message content via view file simply pass view name to the `compose()` method:

```php
Yii::$app->mail->compose('homelink') // message body becomes a view rendering result here
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Message subject')
    ->send();
```

You may pass additional view parameters to `compose()` method, which will be available inside the view files:

```php
Yii::$app->mail->compose('greetings', [
    'user' => Yii::$app->user->identity,
    'advertisement' => $adContent,
]);
```


File attachment
---------------


Embed images
------------


Creating your own mail extension
--------------------------------
