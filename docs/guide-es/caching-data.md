Almacenamiento de Datos en Caché
================================

El almacenamiento de datos en caché trata del almacenamiento de alguna variable PHP en caché y recuperarla más tarde del
mismo. También es la base de algunas de las características avanzadas de almacenamiento en caché, tales como
[el almacenamiento en caché de consultas a la base de datos](#query-caching) y
[el almacenamiento en caché de contenido](caching-content.md).

El siguiente código muestra el típico patrón de uso para el almacenamiento en caché, donde la variable `$cache` se
refiere al [componente caché](#cache-components):

```php
// probar de recuerar $data del caché
$data = $cache->get($key);

if ($data === false) {

    // $data no ha sido encontrada en caché, calcularla desde cero

    // guardar $data en caché para así recuperarla la próxima vez
    $cache->set($key, $data);
}

// $data está disponible aquí
```


## Componentes de Caché <a name="cache-components"></a>

El almacenamiento de datos en caché depende de los llamados *cache components* (componentes de caché) los cuales
representan diferentes tipos de almacenamiento en caché, como por ejemplo en memoria, en archivos o en base de datos.

Los Componentes de Caché estan normalmente registrados como componentes de la aplicación para que de esta forma puedan
ser configurados y accesibles globalmente. El siguiente código muestra cómo configurar el componente de aplicación
`cache` para usar [memcached](http://memcached.org/) con dos servidores caché:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\MemCache',
        'servers' => [
            [
                'host' => 'server1',
                'port' => 11211,
                'weight' => 100,
            ],
            [
                'host' => 'server2',
                'port' => 11211,
                'weight' => 50,
            ],
        ],
    ],
],
```

Puedes acceder al componente de caché usando la expresión  `Yii::$app->cache`.

Debido a que todos los componentes de caché soportan el mismo conjunto de APIs, podrías cambiar el componente de caché
subyacente por otro diferente mediante su reconfiguración en la configuración de la aplicación sin tener que modificar
el código que utiliza el caché. Por ejemplo, podrías modificar la configuración anterior para usar [[yii\caching\ApcCache|APC cache]]:


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Nota: Puedes registrar multiples componentes de aplicación de caché. El componente llamado `cache` es usado por defecto
  por muchas caché-dependiente clases (e.g. [[yii\web\UrlManager]]).


### Almacenamientos de Caché Soportados <a name="supported-cache-storage"></a>

Yii proporciona varios componentes de caché que pueden almacenar datos en diferentes medios. A continuación
se muestra un listado con los componentes de caché disponibles:

* [[yii\caching\ApcCache]]: utiliza la extensión de PHP [APC](http://php.net/manual/en/book.apc.php). Esta opción puede
  ser considerada como la más rápida de entre todas las disponibles para una aplicación centralizada. (p. ej. un servidor,
  no dedicado balance de carga, etc).
* [[yii\caching\DbCache]]: utiliza una tabla de base de datos para almacenar los datos. Por defecto, se creará y usará
  como base de datos [SQLite3](http://sqlite.org/) en el directorio runtime. Se puede especificar explicitamente que base
  de datos va a ser utilizada configurando la propiedad `db`.
* [[yii\caching\DummyCache]]: dummy cache (caché tonta) que no almacena en caché nada. El propósito de este componente
  es simplificar el código necesario para chequear la disponibilidad de caché. Por ejemplo, durante el desarrollo o
  si el servidor no tiene soporte de caché actualmente, puede utilizarse este componente de caché. Cuando este disponible
  un soporte en caché, puede cambiarse el componente correspondiente. En ambos casos, puede utilizarse el mismo código
  `Yii::$app->cache->get($key)` para recuperar un dato sin la preocupación de que `Yii::$app->cache` pueda ser `null`.
* [[yii\caching\FileCache]]: utiliza un fichero estándar para almacenar los datos. Esto es adecuado para almacenar
  grandes bloques de datos (como páginas).
* [[yii\caching\MemCache]]: utiliza las extensiones de PHP [memcache](http://php.net/manual/en/book.memcache.php)
  y [memcached](http://php.net/manual/en/book.memcached.php). Esta opción puede ser considerada como la más rápida
  cuando la caché es manejada en una aplicación distribuida (p. ej. con varios servidores, con balance de carga, etc..)
* [[yii\redis\Cache]]: implementa un componente de caché basado en [Redis](http://redis.io/) que almacenan pares
  clave-valor (requiere la versión 2.6.12 de redis).
* [[yii\caching\WinCache]]: utiliza la extensión de PHP [WinCache](http://iis.net/downloads/microsoft/wincache-extension)
  ([ver también](http://php.net/manual/en/book.wincache.php)).
* [[yii\caching\XCache]]: utiliza la extensión de PHP [XCache](http://xcache.lighttpd.net/).
* [[yii\caching\ZendDataCache]]: utiliza
  [Zend Data Cache](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm)
  como el medio fundamental de caché.

> Nota: Puedes utililizar diferentes tipos de almacenamiento de caché en la misma aplicación. Una estrategia común es la
  de usar almacenamiento de caché en memoria par almacenar datos que son pequeños pero que son utilizados constantemente
  (por ejemplo, datos estadísticos), y utilizar el almacenamiento de caché en archivos o en base de datos para guardar
  datos que son grandes y utilizados con menor frecuencia (por ejemplo, contenido de página).

## API de Caché <a name="cache-apis"></a>

Todos los componentes de almacenamiento de caché provienen de la misma clase "padre" [[yii\caching\Cache]] y por lo tanto
soportan la siguiente API:

* [[yii\caching\Cache::get()|get()]]: recupera un elemento de datos de la memoria caché con una clave especificada.
  Un valor nulo será devuelto si el elemento de datos no ha sido encontrado en la memoria caché o si ha expirado o ha sido
  invalidado.
* [[yii\caching\Cache::set()|set()]]: almacena un elemento de datos identificado por una clave en la memoria caché.
* [[yii\caching\Cache::add()|add()]]: almacena un elemento de datos identificado por una clave en la memoria caché si la
  clave no se encuentra en la memoria caché.
* [[yii\caching\Cache::mget()|mget()]]: recupera varios elementos de datos de la memoria caché con las claves especificadas.
* [[yii\caching\Cache::mset()|mset()]]: almacena múltiples elementos de datos en la memoria caché. Cada elemento se
  identifica por una clave.
* [[yii\caching\Cache::madd()|madd()]]: stores multiple data items in cache. Each item is identified by a key.
  If a key already exists in the cache, the data item will be skipped.
* [[yii\caching\Cache::exists()|exists()]]: devuelve un valor que indica si la clave especificada se encuentra en la
  memoria caché.
* [[yii\caching\Cache::delete()|delete()]]: elimina un elemento de datos identificado por una clave de la caché.
* [[yii\caching\Cache::flush()|flush()]]: elimina todos los elementos de datos de la cache.

Algunos sistemas de almacenamiento de caché, como por ejemplo MemCache, APC, pueden recuperar multiples valores
almacenados en modo de lote (batch), lo que puede reducir considerablemente la sobrecarga que implica la recuperación
de datos almacenados en el caché. Las API [[yii\caching\Cache::mget()|mget()]] y  [[yii\caching\Cache::madd()|madd()]]
se proporcionan para utilizar esta característia. En el caso de que el sistema de memoria caché no lo soportara, ésta
sería simulada.

Puesto que [[yii\caching\Cache]] implementa `ArrayAccess`, un componente de caché puede ser usado como una matriz (array).
El siguiente código muestra unos ejemplos:

```php
$cache['var1'] = $value1;  // equivalente a: $cache->set('var1', $value1);
$value2 = $cache['var2'];  // equivalente a: $value2 = $cache->get('var2');
```


### Claves de Caché <a name="cache-keys"></a>

Cada elemento de datos almacenado en caché se identifica por una clave. Cuando se almacena un elemento de datos en la
memoria caché, se debe especificar una clave. Más tarde, cuando se recupera el elemento de datos de la memoria caché,
se debe proporcionar la tecla correspondiente.

Puedes utilizar una cadena o un valor arbitrario como una clave de caché. Cuando una clave no es una cadena de texto,
ésta será automáticamente serializada en una cadena.

Una estrategia común para definir una clave de caché es incluir en ella todos los factores determinantes en términos de
una matriz. Por ejemplo, [[yii\db\Schema]] utiliza la siguiente clave para almacenar en caché la información del esquema
de una tabla de base de datos:

```php
[
    __CLASS__,              // nombre de la clase del esquema
    $this->db->dsn,         // nombre del origen de datos de la conexión BD
    $this->db->username,    // usuario para la conexión BD
    $name,                  // nombre de la tabla
];
```

Como puedes ver, la clave incluye toda la información necesaria para especificar de una forma exclusiva una tabla de
base de datos.

Cuando el un mismo almacenamiento en caché es utilizado por diferentes aplicaciones, se debería especificar un prefijo
único para las claves de de caché por cada una de las aplicaciones para así evitar conflictos. Esto puede hacerse
mediante la configuración de la propiedad [[yii\caching\Cache::keyPrefix]]. Por ejemplo, en la configuración de la
aplicación podrías escribir el siguiente código:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'myapp',       // un prefixo de clave de caché único
    ],
],
```

Para garantizar la interoperabilidad, deberían utilizarse sólo caracteres alfanuméricos.


### Caducidad de Caché <a name="cache-expiration"></a>

Un elemento de datos almacenado en la memoria caché permanecerá en ella para siempre, a menos que sea removida de alguna
manera debido a alguna directiva de caché (por ejemplo, el espacio de almacenamiento en caché está lleno y los datos
más antiguos se eliminan). Para cambiar este comportamiento, podrías proporcionar un parámetro de caducidad al llamar
[[yii\caching\Cache::set()|set()]] para guardar el elemento de datos. El parámetro nos indica por cuántos segundos el
elemento se mantendrá válido en memoria caché. Cuando llames [[yii\caching\Cache::get()|get()]] para recuperar el
elemento, si el tiempo de caducidad ha pasado, el método devolverá `false`, indicando que el elemento de datos no ha
sido encontrado en la memoria caché. Por ejemplo,

```php
// guardar los datos en memoria caché al menos 45 segundos
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data ha caducado o no ha sido encontrado en la memoria caché
}
```


### Dependecias de Caché <a name="cache-dependencies"></a>

Además de configurar el tiempo de expiración, los datos almacenados en caché pueden también ser invalidados conforme
a algunos cambios en las dependencias (cache dependencies). Por ejemplo, [[yii\caching\FileDependency]] representa
la dependencia del tiempo de modificación del archivo. Cuando esta dependencia cambia, significa que el archivo
correspondiente ha cambiado. Como resultado, cualquier contenido anticuado que sea encontrado en el caché debería
ser invalidado y la llamada a [[yii\caching\Cache::get()|get()]] debería devolver `null`.

Una dependencia es representada como una instancia de [[yii\caching\Dependency]] o su clase hija. Cuando llamas
[[yii\caching\Cache::set()|set()]] para almacenar un elemento de datos en el caché, puedes pasar el objeto de dependencia
asociado. Por ejemplo,


```php
// Crear una dependencia sobre el tiempo de modificación del archivo example.txt.
$dependency = new \yii\caching\FileDependency(['fileName' => 'example.txt']);

// Los datos expirarán en 30 segundos.
// También podría ser invalidada antes si example.txt es modificado.
$cache->set($key, $data, 30, $dependency);

// El caché chequeará si los datos han expirado.
// También chequeará si la dependencia ha cambiado.
// Devolerá false si se encuentran algunas de esas condiciones.
$data = $cache->get($key);
```

Aquí abajo se muestra un sumario de las dependencias disponibles:

- [[yii\caching\ChainedDependency]]: la dependencia cambia si cualquiera de las dependencias en la cadena cambia.
- [[yii\caching\DbDependency]]: la dependencia cambia si el resultado de la consulta de la sentencia SQL especificada cambia.
- [[yii\caching\ExpressionDependency]]: la dependencia cambia si el resultado de la expresión de PHP especificada cambia.
- [[yii\caching\FileDependency]]:  la dependencia cambia si se modifica la última fecha de modificación del archivo.
- [[yii\caching\TagDependency]]: marca un elemento de datos en caché con un nombre de grupo. Puedes invalidar los elementos de datos almacenados en caché
  con el mismo nombre del grupo a la vez llamando a [[yii\caching\TagDependency::invalidate()]].


## Consultas en Caché <a name="query-caching"></a>

Las consultas en caché es una característica especial de caché construido sobre el almacenamiento de caché de datos. Se
proporciona para almacenar en caché el resultado de consultas a la base de datos.

Las consultas en caché requieren una [[yii\db\Connection|conexión a BD]] y un componente de aplicación
caché válido. El uso básico de las consultas en memoria caché es el siguiente, asumiendo `db` es una instancia [[yii\db\Connection]]:

```php
$duration = 60;     // guardar en caché el resultado de la consulta por 60 segundos.
$dependency = ...;  // dependencia opcional

$db->beginCache($duration, $dependency);

// ...realiza consultas a la BD aquí...

$db->endCache();
```

Como puedes ver, cualquier consulta SQL entre las llamadas `beginCache()` y `endCache()` serán guardadas en la memoria caché.
Si el resultado de la misma consulta se encuentra vigente en la memoria caché, la consulta se omitirá y el resultado
se servirá de la memoria caché en su lugar.

El almacenamiento en caché de consultas se puede usar para [DAO](db-dao.md), así como para [ActiveRecord](db-active-record.md).

> Nota: Algunos DBMS (por ejemplo, [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html)) también soporta
  el almacenamiento en caché desde el mismo servidor de la BD. Puedes optar por utilizar cualquiera de los mecanismos
  de memoria caché. El almacenamiento en caché de consultas previamente descrito tiene la ventaja que de que se puede
  especificar dependencias de caché de una forma flexible y son potencialmente mucho más eficientes.

### Configuraciones <a name="query-caching-configs"></a>

Las consultas en caché tienen dos opciones configurables a traves de [[yii\db\Connection]]:

* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: esto representa el número de segundos que un resultado
  de la consulta permanecerá válido en la memoria caché. La duración será sobrescrita si se llama a
  [[yii\db\Connection::beginCache()]] con un parámetro explícito de duración.
* [[yii\db\Connection::queryCache|queryCache]]: representa el ID del componente de aplicación de caché.
  Por defecto es `'cache'`. El almacenamiento en caché de consultas se habilita sólo cuando hay un componente de la
  aplicación de caché válido.

### Limitaciones <a name="query-caching-limitations"></a>

El almacenamiento en caché de consultas no funciona con los resultados de consulta que contienen controladores de recursos.
Por ejemplo, cuando se utiliza el tipo de columna `BLOB` en algunos DBMS, el resultado de la consulta devolverá un recurso
para manejar los datos de la columna.

Algunos sistemas de almacenamiento caché tienen limitación de tamaño. Por ejemplo, memcache limita el tamaño máximo
de cada entrada a 1MB. Por lo tanto, si el tamaño de un resultado de la consulta excede ese límite, el almacenamiento
en caché fallará.
