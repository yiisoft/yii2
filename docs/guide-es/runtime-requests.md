Peticiones
==========

Las peticiones (requests) hechas a una aplicación son representadas como objetos [[yii\web\Request]] que proporcionan 
información como parámetros de la petición, cabeceras HTTP, cookies, etc. Dada una petición, se puede acceder al 
objeto request correspondiente a través del [componente de aplicación](structure-application-components.md) `request` 
que, por defecto, es una instancia de [[yii\web\Request]]. En esta sección se describirá como hacer uso de este 
componente en las aplicaciones.

## Parámetros de Request <span id="request-parameters"></span>

Para obtener los parámetros de la petición, se puede llamar a los métodos [[yii\web\Request::get()|get()]] y 
[[yii\web\Request::post()|post()]] del componente `request`. Estos devuelven los valores de `$_GET` y `$_POST`, 
respectivamente. Por ejemplo:

```php
$request = Yii::$app->request;

$get = $request->get(); 
// equivalente a: $get = $_GET;

$id = $request->get('id');
// equivalente a: $id = isset($_GET['id']) ? $_GET['id'] : null;

$id = $request->get('id', 1);
// equivalente a: $id = isset($_GET['id']) ? $_GET['id'] : 1;

$post = $request->post(); 
// equivalente a: $post = $_POST;

$name = $request->post('name');
// equivalente a: $name = isset($_POST['name']) ? $_POST['name'] : null;

$name = $request->post('name', '');
// equivalente a: $name = isset($_POST['name']) ? $_POST['name'] : '';
```

> Información: En lugar de acceder directamente a `$_GET` y `$_POST` para obtener los parámetros de la petición, es 
  recomendable que se obtengan mediante el componente `request` como en el ejemplo anterior. Esto facilitará la 
  creación de tests ya que se puede simular una componente de request con datos de peticiones personalizados.

Cuando se implementan [APIs RESTful](rest-quick-start.md), a menudo se necesita obtener parámetros enviados desde el 
formulario a través de PUT, PATCH u otros [métodos de request](runtime-requests.md#request-methods). Se pueden obtener 
estos parámetros llamando a los métodos [[yii\web\Request::getBodyParam()]]. Por ejemplo:

```php
$request = Yii::$app->request;

// devuelve todos los parámetros
$params = $request->bodyParams;

// devuelve el parámetro "id"
$param = $request->getBodyParam('id');
```

> Información: A diferencia de los parámetros `GET`, los parámetros enviados desde el formulario a través de `POST`, 
  `PUT`, `PATCH`, etc. se envían en el cuerpo de la petición. El componente `request` convierte los parámetros cuando 
  se acceda a él a través de los métodos descritos anteriormente. Se puede personalizar la manera en como los 
  parámetros se convierten configurando la propiedad [[yii\web\Request::parsers]].

## Métodos de Request <span id="request-methods"></span>

Se puede obtener el método HTTP usado por la petición actual a través de la expresión `Yii::$app->request->method`. Se 
proporcionan un conjunto de propiedades booleanas para comprobar si el método actual es de un cierto tipo. Por ejemplo:

```php
$request = Yii::$app->request;

if ($request->isAjax) { // la request es una request AJAX }
if ($request->isGet)  { // el método de la request es GET }
if ($request->isPost) { // el método de la request es POST }
if ($request->isPut)  { // el método de la request es PUT }
```

## URLs de Request <span id="request-urls"></span>

El componente `request` proporciona muchas maneras de inspeccionar la URL solicitada actualmente.

Asumiendo que la URL que se está solicitando es `http://example.com/admin/index.php/product?id=100`, se pueden obtener 
varias partes de la URL explicadas en los siguientes puntos:

* [[yii\web\Request::url|url]]: devuelve `/admin/index.php/product?id=100`, que es la URL sin la parte de información 
  del host.
* [[yii\web\Request::absoluteUrl|absoluteUrl]]: devuelve `http://example.com/admin/index.php/product?id=100`, que es 
  la URL entera, incluyendo la parte de información del host.
* [[yii\web\Request::hostInfo|hostInfo]]: devuelve `http://example.com`, que es la parte de información del host 
  dentro de la URL.
* [[yii\web\Request::pathInfo|pathInfo]]: devuelve `/product`, que es la parte posterior al script de entrada y 
  anterior al interrogante (query string)
* [[yii\web\Request::queryString|queryString]]: devuelve `id=100`, que es la parte posterior al interrogante.
* [[yii\web\Request::baseUrl|baseUrl]]: devuelve `/admin`, que es la parte posterior a la información del host y 
  anterior al nombre de script de entrada.
* [[yii\web\Request::scriptUrl|scriptUrl]]: devuelve `/admin/index.php`, que es la URL sin la información del la ruta 
  ni la query string.
* [[yii\web\Request::serverName|serverName]]: devuelve `example.com`, que es el nombre del host dentro de la URL.
* [[yii\web\Request::serverPort|serverPort]]: devuelve 80, que es el puerto que usa el servidor web.

## Cabeceras HTTP <span id="http-headers"></span> 

Se pueden obtener la información de las cabeceras HTTP a través de [[yii\web\HeaderCollection|header collection]] 
devueltas por la propiedad [[yii\web\Request::headers]]. Por ejemplo:

```php
// $headers es un objeto de yii\web\HeaderCollection 
$headers = Yii::$app->request->headers;

// devuelve el valor Accept de la cabecera
$accept = $headers->get('Accept');

if ($headers->has('User-Agent')) { // la cabecera contiene un User-Agent }
```

El componente `request` también proporciona soporte para acceder rápidamente a las cabeceras usadas más comúnmente, 
incluyendo:

* [[yii\web\Request::userAgent|userAgent]]: devuelve el valor de la cabecera `User-Agen`.
* [[yii\web\Request::contentType|contentType]]: devuelve el valor de la cabecera `Content-Type` que indica el tipo 
  MIME de los datos del cuerpo de la petición.
* [[yii\web\Request::acceptableContentTypes|acceptableContentTypes]]: devuelve los tipos de contenido MIME aceptado 
  por los usuarios, ordenados por puntuación de calidad. Los que tienen mejor puntuación, se devolverán primero.
* [[yii\web\Request::acceptableLanguages|acceptableLanguages]]: devuelve los idiomas aceptados por el usuario. Los 
  idiomas devueltos son ordenados según su orden de preferencia. El primer elemento representa el idioma preferido.

Si la aplicación soporta múltiples idiomas y se quiere mostrar las páginas en el idioma preferido por el usuario, se 
puede usar el método de negociación de idioma [[yii\web\Request::getPreferredLanguage()]]. Este método obtiene una 
lista de idiomas soportados por la aplicación, comparados con 
[[yii\web\Request::acceptableLanguages|acceptableLanguages]], y devuelve el idioma más apropiado.

> Consejo: También se puede usar el filtro [[yii\filters\ContentNegotiator|ContentNegotiator]] para determinar 
diatónicamente el content type y el idioma que debe usarse en la respuesta. El filtro implementa la negociación de 
contenido en la parte superior de las propiedades y métodos descritos anteriormente.

## Información del cliente <span id="client-information"></span>

Se puede obtener el nombre del host y la dirección IP de la máquina cliente a través de 
[[yii\web\Request::userHost|userHost]] y [[yii\web\Request::userIP|userIP]], respectivamente. Por ejemplo:

```php
$userHost = Yii::$app->request->userHost;
$userIP = Yii::$app->request->userIP;
```