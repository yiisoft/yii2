Envío de Emails
===============

> Note: Esta sección se encuentra en desarrollo.

Yii soporta composición y envío de emails. De cualquier modo, el núcleo del framework provee
sólo la funcionalidad de composición y una interfaz básica. En mecanismo de envío en sí debería
ser provisto por la extensión, dado que diferentes proyectos pueden requerir diferente implementación y esto
usualmente depende de servicios y librerías externas.

Para la mayoría de los casos, puedes utilizar la extensión oficial [yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer).


Configuración
-------------

La configuración del componente Mail depende de la extensión que hayas elegido.
En general, la configuración de tu aplicación debería verse así:

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


Uso Básico
----------

Una vez configurado el componente 'mailer', puedes utilizar el siguiente código para enviar un correo electrónico:

```php
Yii::$app->mailer->compose()
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Asunto del mensaje')
    ->setTextBody('Contenido en texto plano')
    ->setHtmlBody('<b>Contenido HTML</b>')
    ->send();
```

En el ejemplo anterior, el método `compose()` crea una instancia del mensaje de correo, el cual puede ser llenado y enviado.
En caso de ser necesario, puedes agregar una lógica más compleja en el proceso:

```php
$message = Yii::$app->mailer->compose();
if (Yii::$app->user->isGuest) {
    $message->setFrom('from@domain.com')
} else {
    $message->setFrom(Yii::$app->user->identity->email)
}
$message->setTo(Yii::$app->params['adminEmail'])
    ->setSubject('Asunto del mensaje')
    ->setTextBody('Contenido en texto plano')
    ->send();
```

> Note: cada extensión 'mailer' viene en dos grandes clases: 'Mailer' y 'Message'. 'Mailer' siempre conoce
  el nombre de clase especifico de 'Message'. No intentes instanciar el objeto 'Message' directamente -
  siempre utiliza el método `compose()` para ello.

Puedes también enviar varios mensajes al mismo tiempo:

```php
$messages = [];
foreach ($users as $user) {
    $messages[] = Yii::$app->mailer->compose()
        // ...
        ->setTo($user->email);
}
Yii::$app->mailer->sendMultiple($messages);
```

Algunas extensiones en particular pueden beneficiarse de este enfoque, utilizando mensaje simple de red, etc.


Componer el contenido del mensaje
---------------------------------

Yii permite componer el contenido de los mensajes de correo a través de archivos de vista especiales.
Por defecto, estos archivos deben estar ubicados en la ruta '@app/mail'.

Ejemplo de archivo de contenido de correo:

```php
<?php
use yii\helpers\Html;
use yii\helpers\Url;


/* @var $this \yii\web\View instancia del componente view */
/* @var $message \yii\mail\BaseMessage instancia del mensaje de correo recién creado */

?>
<h2>Este mensaje te permite visitar nuestro sitio con un sólo click</h2>
<?= Html::a('Ve a la página principal', Url::home('http')) ?>
```

Para componer el contenido del mensaje utilizando un archivo, simplemente pasa el nombre de la vista al método `compose()`:

```php
Yii::$app->mailer->compose('home-link') // el resultado del renderizado de la vista se transforma en el cuerpo del mensaje aquí
    ->setFrom('from@domain.com')
    ->setTo('to@domain.com')
    ->setSubject('Asunto del mensaje')
    ->send();
```

Puedes pasarle parámetros adicionales a la vista en el método `compose()`, los cuales estarán disponibles dentro de las vistas:

```php
Yii::$app->mailer->compose('greetings', [
    'user' => Yii::$app->user->identity,
    'advertisement' => $adContent,
]);
```

Puedes especificar diferentes archivos de vista para el contenido del mensaje en HTML y texto plano:

```php
Yii::$app->mailer->compose([
    'html' => 'contact-html',
    'text' => 'contact-text',
]);
```

Si especificas el nombre de la vista como un string, el resultado de su renderización será utilizado como cuerpo HTML, mientras
que el cuerpo en texto plano será compuesto removiendo todas las entidades HTML del anterior.

El resultado de la renderización de la vista puede ser envuelta en el layout, que puede ser definido utiliazando [[yii\mail\BaseMailer::htmlLayout]]
y [[yii\mail\BaseMailer::textLayout]]. Esto funciona igual a como funcionan los layouts en una aplicación web normal.
El layout puede utilizar estilos CSS u otros contenidos compartidos:

```php
<?php
use yii\helpers\Html;

/* @var $this \yii\web\View instancia del componente view */
/* @var $message \yii\mail\MessageInterface el mensaje siendo compuesto */
/* @var $content string el resultado de la renderización de la vista principal */
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
    <div class="footer">Saludos cordiales, el equipo de<?= Yii::$app->name ?></div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```


Adjuntar archivos
-----------------

Puedes adjuntar archivos al mensaje utilizando los métodos `attach()` y `attachContent()`:

```php
$message = Yii::$app->mailer->compose();

// Adjunta un archivo del sistema local de archivos:
$message->attach('/path/to/file.pdf');

// Crear adjuntos sobre la marcha
$message->attachContent('Contenido adjunto', ['fileName' => 'attach.txt', 'contentType' => 'text/plain']);
```


Incrustar imágenes
------------------

Puedes incrustar imágenes en el mensaje utilizando el método `embed()`. Este método devuelve el id del adjunto,
que debería ser utilizado como tag 'img'.
Este método es fácil de utilizar al componer mensajes a través de un archivo de vista:

```php
Yii::$app->mailer->compose('embed-email', ['imageFileName' => '/path/to/image.jpg'])
    // ...
    ->send();
```

Entonces, dentro de tu archivo de vista, puedes utilizar el siguiente código:

```php
<img src="<?= $message->embed($imageFileName); ?>">
```


Testear y depurar
-----------------

Un desarrollador a menudo necesita comprobar qué emails están siendo enviados por la aplicación, cuál es su contenido y otras cosas.
Yii concede dicha habilidad vía `yii\mail\BaseMailer::useFileTransport`. Si se habilita, esta opción hace que
los datos del mensaje sean guardados en archivos locales en vez de enviados. Esos archivos serán guardados bajo
`yii\mail\BaseMailer::fileTransportPath`, que por defecto es '@runtime/mail'.

> Note: puedes o bien guardar los mensajes en archivos, o enviarlos a sus receptores correspondientes, pero no puedes hacer las dos cosas al mismo tiempo.

Un archivo de mensaje puede ser abierto por un editor de texto común, de modo que puedas ver sus cabeceras, su contenido y demás.
Este mecanismo en sí puede comprobarse al depurar la aplicación o al ejecutar un test de unidad.

> Note: el archivo de contenido de mensaje es compuesto vía `\yii\mail\MessageInterface::toString()`, por lo que depende de la extensión
  actual de correo utilizada en tu aplicación.


Crear tu solución personalizada de correo
-----------------------------------------

Para crear tu propia solución de correo, necesitas crear 2 clases: una para 'Mailer' y
otra para 'Message'.
Puedes utilizar `yii\mail\BaseMailer` y `yii\mail\BaseMessage` como clases base de tu solución. Estas clases
ya contienen un lógica básica, la cual se describe en esta guía. De cualquier modo, su utilización no es obligatoria, es suficiente
con implementar las interfaces `yii\mail\MailerInterface` y `yii\mail\MessageInterface`.
Luego necesitas implementar todos los métodos abstractos para construir tu solución.
