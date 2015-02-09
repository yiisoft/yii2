Запросы
========

Запросы, сделанные к приложению, представлены в терминах [[yii\web\Request]] объектов, которые предоставляют информацию о параметрах запроса, HTTP заголовках, cookies и т.д. Для получения доступа к текущему запросу вы должны обратиться к объекту `request` [application component](structure-application-components.md), который по умолчанию является экземпляром [[yii\web\Request]].


## Параметры запроса <span id="request-parameters"></span>

Чтобы получить параметры запроса, вы должны вызвать методы [[yii\web\Request::get()|get()]] и [[yii\web\Request::post()|post()]] компонента `request`. Они возвращают значения переменных `$_GET` и `$_POST` соответственно. Например,

```php
$request = Yii::$app->request;

$get = $request->get(); 
// эквивалентно: $get = $_GET;

$id = $request->get('id');   
// эквивалентно: $id = isset($_GET['id']) ? $_GET['id'] : null;

$id = $request->get('id', 1);   
// эквивалентно: $id = isset($_GET['id']) ? $_GET['id'] : 1;

$post = $request->post(); 
// эквивалентно: $post = $_POST;

$name = $request->post('name');   
// эквивалентно: $name = isset($_POST['name']) ? $_POST['name'] : null;

$name = $request->post('name', '');   
// эквивалентно: $name = isset($_POST['name']) ? $_POST['name'] : '';
```

> Информация: Вместо того, чтобы обращаться напрямую к переменным `$_GET` и `$_POST` для получения параметров запроса, рекомендуется
  чтобы вы обращались к ним через компонент `request` как было показано выше. Это упростит написание тестов, поскольку вы можете создать mock компонент запроса с не настоящими данными запроса.

При реализации [RESTful API](rest-quick-start.md), зачастую вам требуется получить параметры, которые были отправлены через PUT, PATCH или другие [методы запроса](#request-methods). Вы можете получить эти параметры, вызвав метод [[yii\web\Request::getBodyParam()]]. Например,

```php
$request = Yii::$app->request;

// возвращает все параметры
$params = $request->bodyParams;

// возвращает параметр "id"
$param = $request->getBodyParam('id');
```

> Информация: В отличии от `GET` параметров, параметры, которые были переданы через `POST`, `PUT`, `PATCH` и д.р. отправляются в теле запроса.
  Компонент `request` будет обрабатывать эти параметры, когда вы попробуете к ним обратиться через методы, описанные выше.
  Вы можете настроить способ обработки этих параметров через настройку свойства [[yii\web\Request::parsers]].
  

## Методы запроса <span id="request-methods"></span>

Вы можете получить названия HTTP метода, используемого в текущем запросе, обратившись к выражению  `Yii::$app->request->method`.
Также имеется целый набор логических свойств для проверки соответствует ли текущий метод определённому типу запроса.
Например,

```php
$request = Yii::$app->request;

if ($request->isAjax) { // является ли текущий запрос AJAX запросом }
if ($request->isGet)  { // является ли текущий запрос GET запросом }
if ($request->isPost) { // является ли текущий запрос POST запросом }
if ($request->isPut)  { // является ли текущий запрос PUT запросом }
```

## URL запроса <span id="request-urls"></span>

Компонент `request` предоставляет множество способов изучения текущего запрашиваемого URL. 

Если предположить, что URL запроса будет `http://example.com/admin/index.php/product?id=100`, то вы можете получить различные части этого адреса так как это показано ниже:

* [[yii\web\Request::url|url]]: вернёт адрес `/admin/index.php/product?id=100`, который содержит URL без информации об имени хоста. 
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: вернёт адрес `http://example.com/admin/index.php/product?id=100`,
  который содержит полный URL, включая имя хоста.
* [[yii\web\Request::hostInfo|hostInfo]]: вернёт адрес `http://example.com`, который содержит только имя хоста.
* [[yii\web\Request::pathInfo|pathInfo]]: вернёт адрес `/product`, который содержит часть между адресом начального скрипта и параметрами запроса, которые идут после знака вопроса.
* [[yii\web\Request::queryString|queryString]]: вернёт адрес `id=100`, который содержит часть URL после знака вопроса. 
* [[yii\web\Request::baseUrl|baseUrl]]: вернёт адрес `/admin`, который является частью URL после информации о хосте и перед именем входного скрипта.
* [[yii\web\Request::scriptUrl|scriptUrl]]: вернёт адрес `/admin/index.php`, который содержит URL без информации о хосте и параметрах запроса.
* [[yii\web\Request::serverName|serverName]]: вернёт адрес `example.com`, который содержит имя хоста в URL.
* [[yii\web\Request::serverPort|serverPort]]: вернёт 80, что является адресом порта, который использует веб-сервер.


## HTTP заголовки <span id="http-headers"></span> 

Вы можете получить информацию о HTTP заголовках через [[yii\web\HeaderCollection|header collection]], возвращаемыми свойством [[yii\web\Request::headers]]. Например,

```php
// переменная $headers является объектом yii\web\HeaderCollection 
$headers = Yii::$app->request->headers;

// возвращает значения заголовка Accept
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { // есть ли в запросе заголовок User-Agent }
```

Компонент `request` также предоставляет доступ к некоторым часто используемым заголовкам, включая

* [[yii\web\Request::userAgent|userAgent]]: возвращает значение заголовка `User-Agent`.
* [[yii\web\Request::contentType|contentType]]: возвращает значение заголовка `Content-Type`, который указывает на MIME тип данных в теле запроса.
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: возвращает список MIME типов данных, которые принимаются пользователем.
  Возвращаемый список типов будет отсортирован по показателю качества. Типы с более высокими показателями будут первыми в списке.
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: возвращает языки, которые поддерживает пользователь.
  Список языков будет отсортирован по уровню предпочтения. Наиболее предпочитаемый язык будет первым в списке.

Если ваше приложение поддерживает множество языков и вы хотите показать страницу на языке, который предпочитает пользователь, 
то вы можете воспользоваться языковым методом согласования (negotiation) [[yii\web\Request::getPreferredLanguage()]].
Этот метод принимает список поддерживаемых языков в вашем приложении, сравнивает их с [[yii\web\Request::acceptableLanguages|acceptableLanguages]] 
и возвращает наиболее подходящий язык.

> Подсказка: Вы также можете использовать фильтр [[yii\filters\ContentNegotiator|ContentNegotiator]] для динамического определения
  какой тип содержимого и язык должен использоваться в ответе. Фильтр реализует согласование содержимого на основе свойств и методов, описанных выше.


## Информация о клиенте <span id="client-information"></span>

Вы можете получить имя хоста и IP адрес пользователя через свойства [[yii\web\Request::userHost|userHost]]
и [[yii\web\Request::userIP|userIP]] соответственно. Например,

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```
