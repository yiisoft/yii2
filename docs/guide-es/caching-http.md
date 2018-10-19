Caché HTTP
==========

Además del almacenamiento de caché en el servidor que hemos descrito en secciones anteriores, las aplicaciones Web
pueden hacer uso de la caché en el lado del cliente para así ahorrar tiempo y recursos para generar y transmitir el
mismo contenido una y otra vez.

Para usar la caché del lado del cliente, puedes configurar [[yii\filters\HttpCache]] como un filtro en el controlador
para aquellas acciones cuyo resultado deba estar almacenado en la caché en el lado del cliente. [[yii\filters\HttpCache|HttpCache]]
solo funciona en peticiones `GET` y `HEAD`. Puede manejar tres tipos de cabeceras (headers) HTTP relacionadas en este tipo de
consultas:

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## La Cabecera `Last-Modified` <span id="last-modified"></span>

La cabecera `Last-Modified` usa un sello de tiempo para indicar si la página ha sido modificada desde que el cliente la
almacena en la caché.

Puedes configurar la propiedad [[yii\filters\HttpCache::lastModified]] para activar el envío de la cabecera `Last-Modified`.
La propiedad debe ser una llamada de retorno (callable) PHP que devuelva un timestamp UNIX sobre el tiempo de modificación de
la página. El formato de la función de llamada de retorno debe ser el siguiente,

```php
/**
 * @param Action $action el objeto acción que se está controlando actualmente
 * @param array $params el valor de la propiedad "params"
 * @return int un sello de tiempo UNIX que representa el tiempo de modificación de la página
 */
function ($action, $params)
```

El siguiente es un ejemplo haciendo uso de la cabecera `Last-Modified`:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('post')->max('updated_at');
            },
        ],
    ];
}
```

El código anterior establece que la memoria caché HTTP debe ser habilitada únicamente por la acción `index`. Se debe
generar una cabecera HTTP `Last-Modified` basado en el último tiempo de actualización de los artículos. Cuando un
navegador visita la página `index` la primera vez, la página será generada en el servidor y enviada al navegador; Si el
navegador visita la misma página de nuevo y no ningún artículo modificado durante el período, el servidor no volverá a
regenerar la página, y el navegador usará la versión caché del lado del cliente. Como resultado, la representación del
lado del servidor y la transmisión del contenido de la página son ambos omitidos.


## La Cabecera `ETag` <span id="etag"></span>

La cabecera "Entity Tag" (o para abreviar `ETag`) usa un hash para representar el contenido de una página. Si la página
ha sido cambiada, el hash también cambiará. Al comparar el hash guardado en el lado del cliente con el hash generado en
el servidor, la caché puede determinar si la página ha cambiado y deber ser retransmitida.

Puedes configurar la propiedad [[yii\filters\HttpCache::etagSeed]] para activar el envío de la cabecera `ETag`.
La propiedad debe ser una función de retorno (callable) PHP que devuelva una semilla para la generación del hash de `ETag`.
El formato de la función de retorno es el siguiente:

```php
/**
 * @param Action $action el objeto acción que se está controlando actualmente
 * @param array $params el valor de la propiedad "params"
 * @return string una cadena usada como semilla para la generación del hash de ETag
 */
function ($action, $params)
```

El siguiente es un ejemplo de cómo usar la cabecera `ETag`:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['view'],
            'etagSeed' => function ($action, $params) {
                $post = $this->findModel(\Yii::$app->request->get('id'));
                return serialize([$post->title, $post->content]);
            },
        ],
    ];
}
```

El código anterior establece que la caché HTTP debe ser activada únicamente para la acción `view`. Debería generar una
cabecera HTTP `ETag` basándose en el título y contenido del artículo consultado. Cuando un navegador visita la página
`view` por primera vez, la página se generará en el servidor y será enviada al navegador; Si el navegador visita la
misma página de nuevo y no ha ocurrido un cambio en el título o contenido del artículo, el servidor no volverá a generar
la página, y el navegador usará la versión guardada en la caché del lado del cliente. Como resultado, la representación del
lado del servidor y la transmisión del contenido de la página son ambos omitidos.

ETags permiten estrategias de almacenamiento de caché más complejas y/o mucho más precisas que las cabeceras `Last-Modified`.
Por ejemplo, un ETag puede ser invalidado si el sitio Web ha cambiado de tema (theme).

La generación de un ETag que requiera muchos recursos puede echar por tierra el propósito de estar usando `HttpCache` e
introducir una sobrecarga innecesaria, ya que debe ser re-evaluada en cada solicitud (request). Trata de encontrar una
expresión sencilla para invalidar la caché si la página ha sido modificada.

> Note: En cumplimiento con [RFC 7232](http://tools.ietf.org/html/rfc7232#section-2.4),
  `HttpCache` enviará ambas cabeceras `ETag` y `Last-Modified` si ambas están configuradas. Y si el clientes envía tanto la cabecera `If-None-Match` como la cabecera `If-Modified-Since`, solo la primera será respetada.

## La Cabecera `Cache-Control` <span id="cache-control"></span>

La cabecera `Cache-Control` especifica la directiva general de la caché para páginas. Puedes enviarla configurando la
propiedad [[yii\filters\HttpCache::cacheControlHeader]] con el valor de la cabecera. Por defecto, la siguiente cabecera
será enviada:

```
Cache-Control: public, max-age=3600
```

## Limitador de la Sesión de Caché <span id="session-cache-limiter"></span>

Cuando una página utiliza la sesión, PHP enviará automáticamente cabeceras HTTP relacionadas con la caché tal y como se
especifican en `session.cache_limiter` de la configuración INI de PHP. Estas cabeceras pueden interferir o deshabilitar
el almacenamiento de caché que desees de `HttpCache`. Para evitar este problema, por defecto `HttpCache` deshabilitará
automáticamente el envío de estas cabeceras. Si deseas modificar este comportamiento, tienes que configurar la propiedad
[[yii\filters\HttpCache::sessionCacheLimiter]]. La propiedad puede tomar un valor de cadena, incluyendo `public`, `private`,
`private_no_expire`, and `nocache`. Por favor, consulta el manual PHP acerca de [session_cache_limiter()](http://www.php.net/manual/es/function.session-cache-limiter.php)
para una mejor explicación sobre esos valores.


## Implicaciones SEO <span id="seo-implications"></span>

Los robots de motores de búsqueda tienden a respetar las cabeceras de caché. Dado que algunos `crawlers` tienen limitado
el número de páginas que pueden rastrear por dominios dentro de un cierto período de tiempo, la introducción de cabeceras
de caché pueden ayudar a la indexación del sitio Web y reducir el número de páginas que deben ser procesadas.

