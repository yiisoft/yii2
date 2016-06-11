Обработка ошибок
================

<<<<<<< HEAD
<<<<<<< HEAD
> Раздел в разработке.
=======
В состав Yii входит встроенный [[yii\web\ErrorHandler|обработчик ошибок]], делающий работу с ошибками гораздо более
приятным занятием. А именно:
>>>>>>> master

* Все не фатальные ошибки PHP (то есть warning, notice) конвертируются в исключения, которые можно перехватывать.
* Исключения и фатальные ошибки PHP отображаются в режиме отладки с детальным стеком вызовов и исходным кодом.
* Можно использовать для отображения ошибок [действие контроллера](structure-controllers.md#actions).
* Поддерживаются различные форматы ответа.

По умолчанию [[yii\web\ErrorHandler|обработчик ошибок]] включен. Вы можете выключить его объявив константу
`YII_ENABLE_ERROR_HANDLER` со значением false во [входном скрипте](structure-entry-scripts.md) вашего приложения.


## Использование обработчика ошибок <span id="using-error-handler"></span>

[[yii\web\ErrorHandler|Обработчик ошибок]] регистрируется в качестве [компонента приложения](structure-application-components.md)
с именем `errorHandler`. Вы можете настраивать его следующим образом:

```php
return [
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
];
```

С приведённой выше конфигурацией на странице ошибки будет отображаться до 20 строк исходного кода.

Как уже было упомянуто, обработчик ошибок конвертирует все не фатальные ошибки PHP в перехватываемые исключения.
Это означает что можно поступать с ошибками следующим образом:

```php
use Yii;
<<<<<<< HEAD
=======
В состав Yii входит встроенный [[yii\web\ErrorHandler|обработчик ошибок]], делающий работу с ошибками гораздо более
приятным занятием. А именно:

* Все нефатальные ошибки PHP (то есть warning, notice) конвертируются в исключения, которые можно перехватывать.
* Исключения и фатальные ошибки PHP отображаются в режиме отладки с детальным стеком вызовов и исходным кодом.
* Можно использовать для отображения ошибок [действие контроллера](structure-controllers.md#actions).
* Поддерживаются различные форматы ответа.

По умолчанию [[yii\web\ErrorHandler|обработчик ошибок]] включен. Вы можете выключить его объявив константу
`YII_ENABLE_ERROR_HANDLER` со значением false во [входном скрипте](structure-entry-scripts.md) вашего приложения.


## Использование обработчика ошибок <span id="using-error-handler"></span>

[[yii\web\ErrorHandler|Обработчик ошибок]] регистрируется в качестве [компонента приложения](structure-application-components.md)
с именем `errorHandler`. Вы можете настраивать его следующим образом:

```php
return [
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
];
```

С приведённой выше конфигурацией на странице ошибки будет отображаться до 20 строк исходного кода.

Как уже было упомянуто, обработчик ошибок конвертирует все нефатальные ошибки PHP в перехватываемые исключения.
Это означает что можно поступать с ошибками следующим образом:

```php
use Yii;
use yii\base\ErrorException;
>>>>>>> yiichina/master
=======
use yii\base\ErrorException;
>>>>>>> master

try {
    10/0;
} catch (ErrorException $e) {
<<<<<<< HEAD
<<<<<<< HEAD
    Yii::warning("Попытка деления на ноль.");
=======
    Yii::warning("Деление на ноль.");
>>>>>>> yiichina/master
=======
    Yii::warning("Деление на ноль.");
>>>>>>> master
}

// можно продолжать выполнение
```

<<<<<<< HEAD
<<<<<<< HEAD
Как это видно из примера, вы можете обрабатывать ошибки, используя конструкцию `try`-`catch`.
=======
Если вам необходимо показать пользователю страницу с ошибкой, говорящей ему о том, что его запрос не верен или не
должен был быть сделан, вы можете выкинуть [[yii\web\HttpException|исключение HTTP]], такое как 
[[yii\web\NotFoundHttpException]]. Обработчик ошибок корректно выставит статус код HTTP для ответа и использует
подходящий вид страницы ошибки.

```php
use yii\web\NotFoundHttpException;
 
throw new NotFoundHttpException();
```

## Настройка отображения ошибок <span id="customizing-error-display"></span>

[[yii\web\ErrorHandler|Обработчик ошибок]] меняет отображение ошибок в зависимости от значения константы `YII_DEBUG`.
При `YII_DEBUG` равной true (режим отладки), обработчик ошибок будет отображать для облегчения отладки детальный стек
вызовов и исходный код. При `YII_DEBUG` равной false отображается только сообщение об ошибке, тем самым не позволяя
получить информацию о внутренностях приложения.
>>>>>>> master

> Info: Если исключение является наследником [[yii\base\UserException]], стек вызовов не отображается вне
  зависимости от значения `YII_DEBUG` так как такие исключения считаются ошибками пользователя и исправлять что-либо
  разработчику не требуется.

По умолчанию [[yii\web\ErrorHandler|обработчик ошибок]] показывает ошибки используя два [представления](structure-views.md):

* `@yii/views/errorHandler/error.php`: используется для отображения ошибок БЕЗ стека вызовов.
  При `YII_DEBUG` равной false используется только это преставление.
* `@yii/views/errorHandler/exception.php`: используется для отображения ошибок СО стеком вызовов.
 
Вы можете настроить свойства [[yii\web\ErrorHandler::errorView|errorView]] и [[yii\web\ErrorHandler::exceptionView|exceptionView]]
для того, чтобы использовать свои представления.
  
### Использование действий для отображения ошибок <span id="using-error-actions"></span>

<<<<<<< HEAD
Самый легкий способ создать свою страницу для отображения ошибок - использовать свое действие (action) для рендеринга сообщений об ошибке. 
Сначала нужно дать приложению понять, что вы хотите использовать свое действие для обработки ошибок. Для этого нужно сконфигурировать компонент `errorHandler` в конфигурационном файле приложения:
=======
Если вам необходимо показать пользователю страницу с ошибкой, говорящей ему о том, что его запрос не верен или не
должен был быть сделан, вы можете выкинуть [[yii\web\HttpException|исключение HTTP]], такое как 
[[yii\web\NotFoundHttpException]]. Обработчик ошибок корректно выставит статус код HTTP для ответа и использует
подходящий вид страницы ошибки.

```php
use yii\web\NotFoundHttpException;
 
throw new NotFoundHttpException();
```

## Настройка отобажения ошибок <span id="customizing-error-display"></span>

[[yii\web\ErrorHandler|Обработчик ошибок]] меняет отображение ошибок в зависимости от значения константы `YII_DEBUG`.
При `YII_DEBUG` равной true (режим отладки), обработчик ошибок будет отображать для облегчения отладки детальный стек
вызовов и исходный код. При `YII_DEBUG` равной false отображается только сообщение об ошибке, тем самым не позволяя
получить информацию о внутренностях приложения.

> Информация: Если исключение является наследником [[yii\base\UserException]], стек вызовов не отображается вне
  зависимости от значения `YII_DEBUG` так как такие исключения считаются ошибками пользователя и исправлять что-либо
  разработчику не требуется.

По умолчанию [[yii\web\ErrorHandler|обработчик ошибок]] показывает ошибки используя два [представления](structure-views.md):

* `@yii/views/errorHandler/error.php`: используется для отображения ошибок БЕЗ стека вызовов.
  При `YII_DEBUG` равной false используется только это преставление.
* `@yii/views/errorHandler/exception.php`: используется для отображения ошибок СО стеком вызовов.
 
Вы можете настроить свойства [[yii\web\ErrorHandler::errorView|errorView]] и [[yii\web\ErrorHandler::exceptionView|exceptionView]]
для того, чтобы использовать свои представления.
  
### Использование действий для отображения ошибок <span id="using-error-actions"></span>

Лучшим способом изменения отображения ошибок является использование [действий](structure-controllers.md) путём
конфигурирования свойства [[yii\web\ErrorHandler::errorAction|errorAction]] компонента `errorHandler`:
>>>>>>> yiichina/master
=======
Лучшим способом изменения отображения ошибок является использование [действий](structure-controllers.md) путём
конфигурирования свойства [[yii\web\ErrorHandler::errorAction|errorAction]] компонента `errorHandler`:
>>>>>>> master

```php
// ...
'components' => [
    // ...
    'errorHandler' => [
        'errorAction' => 'site/error',
    ],
]
```
<<<<<<< HEAD
<<<<<<< HEAD
С вышеуказанной конфигурацией, если происходит ошибка, Yii запустит действие `error` контроллера `site`. Это действие запрашивает у компонента `errorHandler`, было ли выброшено исключение, и, если да, отображает соответствующий вид, передавая ему объект исключения в качестве параметра.
=======
=======
>>>>>>> master

Свойство [[yii\web\ErrorHandler::errorAction|errorAction]] принимает [маршрут](structure-controllers.md#routes)
действия. Конфигурация выше означает, что для отображения ошибки без стека вызовов будет использовано действие `site/error`.

Само действие можно реализовать следующим образом:

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}
 ```
 
Приведённый выше код задаёт действие `error` используя класс [[yii\web\ErrorAction]], который рендерит ошибку используя
отображение `error`.

Вместо использования [[yii\web\ErrorAction]] вы можете создать действие `error` как обычный метод:
 
<<<<<<< HEAD
>>>>>>> yiichina/master
=======
>>>>>>> master

```php
public function actionError()
{
<<<<<<< HEAD
<<<<<<< HEAD
    $exception = \Yii::$app->errorHandler->exception;
=======
    $exception = Yii::$app->errorHandler->exception;
>>>>>>> yiichina/master
=======
    $exception = Yii::$app->errorHandler->exception;
>>>>>>> master
    if ($exception !== null) {
        return $this->render('error', ['exception' => $exception]);
    }
}
```

<<<<<<< HEAD
<<<<<<< HEAD
После создания `action` нужно создать соответствующий вид, который и будет отображать информацию об исключении. Объект исключения, передаваемый в вид, имеет 
 следующие свойства:
=======
Вы должны создать файл представления `views/site/error.php`. В этом файле, если используется [[yii\web\ErrorAction]],
вам доступны следующие переменные:

* `name`: имя ошибки;
* `message`: текст ошибки;
* `exception`: объект исключения, из которого можно получить дополнительную информацию, такую как статус HTTP,
  код ошибки, стек вызовов и т.д.
 
> Info: Если вы используете шаблоны приложения [basic](start-installation.md) или [advanced](tutorial-advanced-app.md),
  действие error и файл представления уже созданы за вас.
  
### Изменение формата ответа <span id="error-format"></span>

Обработчик ошибок отображает ошибки в соответствии с выбранным форматом [ответа](runtime-responses.md).
Если [[yii\web\Response::format|формат ответа]] задан как `html`, будут использованы представления для ошибок и
исключений, как описывалось ранее. Для остальных форматов ответа обработчик ошибок присваивает массив данных,
представляющий ошибку свойству [[yii\web\Response::data]]. Оно далее конвертируется в необходимый формат. Например,
если используется формат ответа `json`, вы получите подобный ответ:
>>>>>>> master

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

Изменить формат можно в обработчике события `beforeSend` компонента `response` в конфигурации приложения:

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

Приведённый код изменит формат ответа на подобный:

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

<<<<<<< HEAD
Объект `$exception` имеет те же свойства, которые были указаны ранее.
=======
Вы должны создать файл представления `views/site/error.php`. В этом файле, если используется [[yii\web\ErrorAction]],
вам доступны следующие переменные:

* `name`: имя ошибки;
* `message`: текст ошибки;
* `exception`: объект исключения, из которого можно получить дополнительную информацию, такую как статус HTTP,
  код ошибки, стек вызовов и т.д.
 
> Информация: Если вы используете шаблоны приложения [basic](start-installation.md) или [advanced](tutorial-advanced-app.md),
  действие error и файл представления уже созданы за вас.
  
### Изменение формата ответа <span id="error-format"></span>

Обработчик ошибок отображает ошибки в соответствии с выбранным форматом [ответа](runtime-responses.md).
Если [[yii\web\Response::format|формат ответа]] задан как `html`, будут использованы представления для ошибок и
исключений, как описывалось ранее. Для остальных форматов ответа обработчик ошибок присваивает массив данных,
представляющий ошибку свойству [[yii\web\Response::data]]. Оно далее конвертируется в необходимый формат. Например,
если используется формат ответа `json`, вы получите подобный ответ:

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

Изменить формат можно в обработчике события `beforeSend` компонента `response` в конфигурации приложения:

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

Приведённый код изменит формат ответа на подобный:

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

=======
>>>>>>> master
{
    "success": false,
    "data": {
        "name": "Not Found Exception",
        "message": "The requested resource was not found.",
        "code": 0,
        "status": 404
    }
}
```
<<<<<<< HEAD
>>>>>>> yiichina/master
=======
>>>>>>> master
