Ответы
======

Когда приложение заканчивает обработку [запроса](runtime-requests.md), оно генерирует объект [[yii\web\Response|ответа]]
и отправляет его пользователю. Объект ответа содержит такие данные, как HTTP-код состояния, HTTP-заголовки и тело ответа.
Конечная цель разработки Web-приложения состоит в создании объектов ответа на различные запросы.

В большинстве случаев вам придется иметь дело с [компонентом приложения](structure-application-components.md) `response`,
который по умолчанию является экземпляром класса [[yii\web\Response]]. Однако Yii также позволяет вам создавать собственные
объекты ответа и отправлять их пользователям. Это будет рассмотрено ниже.

В данном разделе мы опишем, как составлять ответы и отправлять их пользователям. 


## Код состояния <span id="status-code"></span>

Первое, что вы делаете при построении ответа, — определяете, был ли успешно обработан запрос. Это реализуется заданием
свойству [[yii\web\Response::statusCode]] значения, которое может быть одним из валидных
[HTTP-кодов состояния](https://tools.ietf.org/html/rfc2616#section-10). Например, чтобы показать, что запрос был
успешно обработан, вы можете установить значение кода состояния равным 200:

```php
Yii::$app->response->statusCode = 200;
```

Однако в большинстве случаев явная установка не требуется так как значение [[yii\web\Response::statusCode]] 
по умолчанию равно 200. Если вам нужно показать, что запрос не удался, вы можете выбросить соответствующее
HTTP-исключение:

```php
throw new \yii\web\NotFoundHttpException;
```

Когда [обработчик ошибок](runtime-handling-errors.md) поймает исключение, он извлечёт код состояния 
из исключения и назначит его ответу. Исключение [[yii\web\NotFoundHttpException]] в коде выше 
представляет HTTP-код состояния 404. В Yii предопределены следующие HTTP-исключения:

* [[yii\web\BadRequestHttpException]]: код состояния 400.
* [[yii\web\ConflictHttpException]]: код состояния 409.
* [[yii\web\ForbiddenHttpException]]: код состояния 403.
* [[yii\web\GoneHttpException]]: код состояния 410.
* [[yii\web\MethodNotAllowedHttpException]]: код состояния 405.
* [[yii\web\NotAcceptableHttpException]]: код состояния 406. 
* [[yii\web\NotFoundHttpException]]: код состояния 404.
* [[yii\web\ServerErrorHttpException]]: код состояния 500.
* [[yii\web\TooManyRequestsHttpException]]: код состояния 429.
* [[yii\web\UnauthorizedHttpException]]: код состояния 401.
* [[yii\web\UnsupportedMediaTypeHttpException]]: код состояния 415.

Если в приведённом выше списке нет исключения, которое вы хотите выбросить, вы можете создать его, расширив класс
[[yii\web\HttpException]], или выбросить его напрямую с кодом состояния, например:
 
```php
throw new \yii\web\HttpException(402);
```


## HTTP-заголовки <span id="http-headers"></span> 

Вы можете отправлять HTTP-заголовки, работая с [[yii\web\Response::headers|коллекцией заголовков]] компонента `response`:

```php
$headers = Yii::$app->response->headers;

// добавить заголовок Pragma. Уже имеющиеся Pragma-заголовки НЕ будут перезаписаны.
$headers->add('Pragma', 'no-cache');

// установить заголовок Pragma. Любые уже имеющиеся Pragma-заголовки будут сброшены.
$headers->set('Pragma', 'no-cache');

// удалить заголовок (или заголовки) Pragma и вернуть их значения массивом
$values = $headers->remove('Pragma');
```

> Info: названия заголовков не чувствительны к регистру символов. Заново зарегистрированные заголовки не отсылаются
  пользователю до вызова [[yii\web\Response::send()]].


## Тело ответа <span id="response-body"></span>

Большинство ответов должны иметь тело, содержащее то, что вы хотите показать пользователям.

Если у вас уже имеется отформатированная строка для тела, вы можете присвоить её свойству [[yii\web\Response::content]] 
объекта запроса:

```php
Yii::$app->response->content = 'hello world!';
```

Если ваши данные перед отправкой конечным пользователям нужно привести к определённому формату, вам следует установить значения 
двух свойств: [[yii\web\Response::format|format]] и [[yii\web\Response::data|data]]. Свойство [[yii\web\Response::format|format]]
определяет, в каком формате следует возвращать данные из [[yii\web\Response::data|data]]. Например:

```php
$response = Yii::$app->response;
$response->format = \yii\web\Response::FORMAT_JSON;
$response->data = ['message' => 'hello world'];
```

Yii из коробки имеет поддержку следующих форматов, каждый из которых реализован классом [[yii\web\ResponseFormatterInterface|форматтера]].
Вы можете настроить эти форматтеры или добавить новые через свойство [[yii\web\Response::formatters]].

* [[yii\web\Response::FORMAT_HTML|HTML]]: реализуется классом [[yii\web\HtmlResponseFormatter]].
* [[yii\web\Response::FORMAT_XML|XML]]: реализуется классом [[yii\web\XmlResponseFormatter]].
* [[yii\web\Response::FORMAT_JSON|JSON]]: реализуется классом [[yii\web\JsonResponseFormatter]].
* [[yii\web\Response::FORMAT_JSONP|JSONP]]: реализуется классом [[yii\web\JsonResponseFormatter]].

Хотя тело запроса может быть явно установлено показанным выше способом, в большинстве случаев вы можете задавать его неявно 
через возвращаемое значение методов [действий](structure-controllers.md). Типичный пример использования:
 
```php
public function actionIndex()
{
    return $this->render('index');
}
```

Действие `index` в коде выше возвращает результат рендеринга представления `index`. Возвращаемое значение будет взято 
компонентом `response`, отформатировано и затем отправлено пользователям.

Так как по умолчанию форматом ответа является [[yii\web\Response::FORMAT_HTML|HTML]], в методе действия следует
вернуть строку. Если вы хотите использовать другой формат ответа, необходимо настроить его перед отправкой данных:

```php
public function actionInfo()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    return [
        'message' => 'hello world',
        'code' => 100,
    ];
}
```

Как уже было сказано, кроме использования стандартного компонента приложения `response` вы также можете создавать свои
объекты ответа и отправлять их конечным пользователям. Вы можете сделать это, возвращая такой объект в методе действия:

```php
public function actionInfo()
{
    return \Yii::createObject([
        'class' => 'yii\web\Response',
        'format' => \yii\web\Response::FORMAT_JSON,
        'data' => [
            'message' => 'hello world',
            'code' => 100,
        ],
    ]);
}
```

> Note: создавая собственные объекты ответов, вы не сможете воспользоваться конфигурацией компонента `response`,
  настроенной вами в конфигурации приложения. Тем не менее, вы можете воспользоваться 
  [внедрением зависимости](concept-di-container.md), чтобы применить общую конфигурацию к вашим новым объектам ответа.


## Перенаправление браузера <span id="browser-redirection"></span>

Перенаправление браузера основано на отправке HTTP-заголовка `Location`. Так как данная возможность широко применяется,
Yii имеет средства для её использования.

Вы можете перенаправить браузер пользователя на URL-адрес, вызвав метод [[yii\web\Response::redirect()]]. Этот метод
использует указанный URL-адрес в качестве значения заголовка `Location` и возвращает сам объект ответа. В методе действия
вы можете вызвать короткую версию этого метода — [[yii\web\Controller::redirect()]]. Например:

```php
public function actionOld()
{
    return $this->redirect('https://example.com/new', 301);
}
```

В приведённом выше коде метод действия возвращает результат `redirect()`. Как говорилось выше, объект ответа,
возвращаемый методом действия, будет использоваться в качестве ответа конечным пользователям.

В коде, находящемся вне методов действий, следует использовать [[yii\web\Response::redirect()]] и непосредственно после
него — метод [[yii\web\Response::send()]]. Так можно быть уверенным, что к ответу не будет добавлено нежелательное
содержимое.

```php
\Yii::$app->response->redirect('https://example.com/new', 301)->send();
```

> Info: По умолчанию метод [[yii\web\Response::redirect()]] устанавливает код состояния ответа равным 302, сообщая
  браузеру, что запрашиваемый ресурс *временно* находится по другому URI-адресу. Вы можете передать код состояния 
  301, чтобы сообщить браузеру, что ресурс перемещён *навсегда*.

Если текущий запрос является AJAX-запросом, отправка заголовка `Location` не заставит браузер автоматически 
осуществить перенаправление. Чтобы решить эту задачу, метод [[yii\web\Response::redirect()]] устанавливает значение
заголовка `X-Redirect` равным URL для перенаправления. На стороне клиента вы можете написать JavaScript-код для чтения
значения этого заголовка и перенаправления браузера соответственно.

> Info: Yii поставляется с JavaScript-файлом `yii.js`, который предоставляет набор часто используемых
  JavaScript-утилит, включая и перенаправление браузера на основе заголовка `X-Redirect`. Следовательно, если вы
  используете этот JavaScript-файл (зарегистрировав пакет ресурсов [[yii\web\YiiAsset]]), вам не нужно писать
  дополнительный код для поддержки AJAX-перенаправления.


## Отправка файлов <span id="sending-files"></span>

Как и перенаправление браузера, отправка файлов является ещё одной возможностью, основанной на определённых HTTP-заголовках.
Yii предоставляет набор методов для решения различных задач по отправке файлов. Все они поддерживают HTTP-заголовок range.

* [[yii\web\Response::sendFile()]]: отправляет клиенту существующий файл.
* [[yii\web\Response::sendContentAsFile()]]: отправляет клиенту строку как файл.
* [[yii\web\Response::sendStreamAsFile()]]: отправляет клиенту существующий файловый поток как файл.

Эти методы имеют одинаковую сигнатуру и возвращают объект ответа. Если отправляемый файл очень велик, следует
использовать [[yii\web\Response::sendStreamAsFile()]], так как он более эффективно использует оперативную память.
Следующий пример показывает, как отправить файл в действии контроллера:

```php
public function actionDownload()
{
    return \Yii::$app->response->sendFile('path/to/file.txt');
}
```

Чтобы быть уверенным, что к ответу не будет добавлено никакое
нежелательное содержимое, при вызове метода [[yii\web\Response::sendFile()]] вне методов action, следует вызвать сразу после него [[yii\web\Response::send()]].

```php
\Yii::$app->response->sendFile('path/to/file.txt')->send();
```

Некоторые Web-серверы поддерживают особый режим отправки файлов, который называется *X-Sendfile*. Идея в том, чтобы 
перенаправить запрос файла Web-серверу, который отдаст файл пользователю самостоятельно. В результате Web-приложение
может завершиться раньше, пока Web-сервер ещё пересылает файл. Чтобы использовать эту возможность, воспользуйтесь
методом [[yii\web\Response::xSendFile()]]. Далее приведены ссылки на то, как включить `X-Sendfile` для популярных
Web-серверов:

- Apache: [X-Sendfile](https://tn123.org/mod_xsendfile)
- Lighttpd v1.4: [X-LIGHTTPD-send-file](https://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Lighttpd v1.5: [X-Sendfile](https://redmine.lighttpd.net/projects/lighttpd/wiki/X-LIGHTTPD-send-file)
- Nginx: [X-Accel-Redirect](https://www.nginx.com/resources/wiki/start/topics/examples/xsendfile/)
- Cherokee: [X-Sendfile and X-Accel-Redirect](https://www.cherokee-project.com/doc/other_goodies.html#x-sendfile)


## Отправка ответа <span id="sending-response"></span>

Содержимое ответа не отправляется пользователю до вызова метода [[yii\web\Response::send()]]. По умолчанию он вызывается
автоматически в конце метода [[yii\base\Application::run()]]. Однако чтобы ответ был отправлен немедленно, вы можете
вызвать этот метод явно.

Для отправки ответа метод [[yii\web\Response::send()]] выполняет следующие шаги:

1. Инициируется событие [[yii\web\Response::EVENT_BEFORE_SEND]].
2. Для форматирования [[yii\web\Response::data|данных ответа]] в [[yii\web\Response::content|содержимое ответа]]
   вызывается метод [[yii\web\Response::prepare()]] .
3. Инициируется событие [[yii\web\Response::EVENT_AFTER_PREPARE]].
4. Для отправки зарегистрированных HTTP-заголовков вызывается метод [[yii\web\Response::sendHeaders()]].
5. Для отправки тела ответа вызывается метод [[yii\web\Response::sendContent()]].
6. Инициируется событие [[yii\web\Response::EVENT_AFTER_SEND]].

Повторный вызов [[yii\web\Response::send()]] игнорируется. Это означает, что если ответ уже отправлен, то к нему уже
ничего не добавить.

Как видно, метод [[yii\web\Response::send()]] инициирует несколько полезных событий. Реагируя на
эти события, можно настраивать или декорировать ответ.
