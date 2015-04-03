Almacenamiento de Datos en Caché
================================

El almacenamiento de datos en caché trata del almacenamiento de alguna variable PHP en caché y recuperarla más tarde del mismo. También es la base de algunas de las características avanzadas de almacenamiento en caché, tales como [el almacenamiento en caché de consultas a la base de datos](#query-caching) y [el almacenamiento en caché de contenido](caching-page.md).

El siguiente código muestra el típico patrón de uso para el almacenamiento en caché, donde la variable `$cache` se refiere al [componente caché](#cache-components):

```php
// intenta recuperar $data de la caché
$data = $cache->get($key);

if ($data === false) {

    // $data no ha sido encontrada en la caché, calcularla desde cero

    // guardar $data en caché para así recuperarla la próxima vez
    $cache->set($key, $data);
}

// $data está disponible aquí
```


## Componentes de Caché <span id="cache-components"></span>

El almacenamiento de datos en caché depende de los llamados *cache components* (componentes de caché) los cuales
representan diferentes tipos de almacenamiento en caché, como por ejemplo en memoria, en archivos o en base de datos.

Los Componentes de Caché están normalmente registrados como [componentes de la aplicación](structure-application-components.md) para que de esta forma puedan
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
el código que utiliza la caché. Por ejemplo, podrías modificar la configuración anterior para usar [[yii\caching\ApcCache|APC cache]]:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Consejo: Puedes registrar múltiples componentes de aplicación de caché. El componente llamado `cache` es usado por defecto por muchas clases caché-dependiente (ej. [[yii\web\UrlManager]]).


### Almacenamientos de Caché Soportados <span id="supported-cache-storage"></span>

Yii proporciona varios componentes de caché que pueden almacenar datos en diferentes medios. A continuación
se muestra un listado con los componentes de caché disponibles:

* [[yii\caching\ApcCache]]: utiliza la extensión de PHP [APC](http://php.net/manual/es/book.apc.php). Esta opción puede ser considerada como la más rápida de entre todas las disponibles para una aplicación centralizada. (ej. un servidor, no dedicado al balance de carga, etc).
* [[yii\caching\DbCache]]: utiliza una tabla de base de datos para almacenar los datos. Por defecto, se creará y usará como base de datos [SQLite3](http://sqlite.org/) en el directorio runtime. Se puede especificar explícitamente que base de datos va a ser utilizada configurando la propiedad `db`.
* [[yii\caching\DummyCache]]: dummy cache (caché tonta) que no almacena en caché nada. El propósito de este componente es simplificar el código necesario para chequear la disponibilidad de caché. Por ejemplo, durante el desarrollo o si el servidor no tiene soporte de caché actualmente, puede utilizarse este componente de caché. Cuando este disponible un soporte en caché, puede cambiarse el componente correspondiente. En ambos casos, puede utilizarse el mismo código `Yii::$app->cache->get($key)` para recuperar un dato sin la preocupación de que `Yii::$app->cache` pueda ser `null`.
* [[yii\caching\FileCache]]: utiliza un fichero estándar para almacenar los datos. Esto es adecuado para almacenar grandes bloques de datos (como páginas).
* [[yii\caching\MemCache]]: utiliza las extensiones de PHP [memcache](http://php.net/manual/es/book.memcache.php) y [memcached](http://php.net/manual/es/book.memcached.php). Esta opción puede ser considerada como la más rápida cuando la caché es manejada en una aplicación distribuida (ej. con varios servidores, con balance de carga, etc..)
* [[yii\redis\Cache]]: implementa un componente de caché basado en [Redis](http://redis.io/) que almacenan pares clave-valor (requiere la versión 2.6.12 de redis).
* [[yii\caching\WinCache]]: utiliza la extensión de PHP [WinCache](http://iis.net/downloads/microsoft/wincache-extension) ([ver también](http://php.net/manual/es/book.wincache.php)).
* [[yii\caching\XCache]]: utiliza la extensión de PHP [XCache](http://xcache.lighttpd.net/).
* [[yii\caching\ZendDataCache]]: utiliza [Zend Data Cache](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm) como el medio fundamental de caché.

> Consejo: Puedes utilizar diferentes tipos de almacenamiento de caché en la misma aplicación. Una estrategia común es la de usar almacenamiento de caché en memoria para almacenar datos que son pequeños pero que son utilizados constantemente (ej. datos estadísticos), y utilizar el almacenamiento de caché en archivos o en base de datos para guardar datos que son grandes y utilizados con menor frecuencia (ej. contenido de página).


## API de Caché <span id="cache-apis"></span>

Todos los componentes de almacenamiento de caché provienen de la misma clase "padre" [[yii\caching\Cache]] y por lo tanto soportan la siguiente API:

* [[yii\caching\Cache::get()|get()]]: recupera un elemento de datos de la memoria caché con una clave especificada.
  Un valor nulo será devuelto si el elemento de datos no ha sido encontrado en la memoria caché o si ha expirado o ha sido invalidado.
* [[yii\caching\Cache::set()|set()]]: almacena un elemento de datos identificado por una clave en la memoria caché.
* [[yii\caching\Cache::add()|add()]]: almacena un elemento de datos identificado por una clave en la memoria caché si la clave no se encuentra en la memoria caché.
* [[yii\caching\Cache::mget()|mget()]]: recupera varios elementos de datos de la memoria caché con las claves especificadas.
* [[yii\caching\Cache::mset()|mset()]]: almacena múltiples elementos de datos en la memoria caché. Cada elemento se identifica por una clave.
* [[yii\caching\Cache::madd()|madd()]]: almacena múltiples elementos de datos en la memoria caché. Cada elemento se identifica con una clave. Si una clave ya existe en la caché, el elemento será omitido.
* [[yii\caching\Cache::exists()|exists()]]: devuelve un valor que indica si la clave especificada se encuentra en la memoria caché.
* [[yii\caching\Cache::delete()|delete()]]: elimina un elemento de datos identificado por una clave de la caché.
* [[yii\caching\Cache::flush()|flush()]]: elimina todos los elementos de datos de la cache.

> Nota: No Almacenes el valor boolean `false` en caché directamente porque el método [[yii\caching\Cache::get()|get()]] devuelve
el valor `false` para indicar que el dato no ha sido encontrado en la caché. Puedes poner `false` dentro de un array y cachear
este array para evitar este problema.

Algunos sistemas de almacenamiento de caché, como por ejemplo MemCache, APC, pueden recuperar múltiples valores almacenados en modo de lote (batch), lo que puede reducir considerablemente la sobrecarga que implica la recuperación de datos almacenados en la caché. Las API [[yii\caching\Cache::mget()|mget()]] y  [[yii\caching\Cache::madd()|madd()]]
se proporcionan para utilizar esta característica. En el caso de que el sistema de memoria caché no lo soportara, ésta sería simulada.

Puesto que [[yii\caching\Cache]] implementa `ArrayAccess`, un componente de caché puede ser usado como un array.
El siguiente código muestra unos ejemplos:

```php
$cache['var1'] = $value1;  // equivalente a: $cache->set('var1', $value1);
$value2 = $cache['var2'];  // equivalente a: $value2 = $cache->get('var2');
```


### Claves de Caché <span id="cache-keys"></span>

Cada elemento de datos almacenado en caché se identifica por una clave. Cuando se almacena un elemento de datos en la memoria caché, se debe especificar una clave. Más tarde, cuando se recupera el elemento de datos de la memoria caché, se debe proporcionar la clave correspondiente.

Puedes utilizar una cadena o un valor arbitrario como una clave de caché. Cuando una clave no es una cadena de texto, ésta será automáticamente serializada en una cadena.

Una estrategia común para definir una clave de caché es incluir en ella todos los factores determinantes en términos de un array. Por ejemplo, [[yii\db\Schema]] utiliza la siguiente clave para almacenar en caché la información del esquema de una tabla de base de datos:

```php
[
    __CLASS__,              // nombre de la clase del esquema
    $this->db->dsn,         // nombre del origen de datos de la conexión BD
    $this->db->username,    // usuario para la conexión BD
    $name,                  // nombre de la tabla
];
```

Como puedes ver, la clave incluye toda la información necesaria para especificar de una forma exclusiva una tabla de base de datos.

Cuando en un mismo almacenamiento en caché es utilizado por diferentes aplicaciones, se debería especificar un prefijo único para las claves de la caché por cada una de las aplicaciones para así evitar conflictos. Esto puede hacerse mediante la configuración de la propiedad [[yii\caching\Cache::keyPrefix]]. Por ejemplo, en la configuración de la aplicación podrías escribir el siguiente código:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'myapp',       // un prefijo de clave de caché único
    ],
],
```

Para garantizar la interoperabilidad, deberían utilizarse sólo caracteres alfanuméricos.


### Caducidad de Caché <span id="cache-expiration"></span>

Un elemento de datos almacenado en la memoria caché permanecerá en ella para siempre, a menos que sea removida de alguna manera debido a alguna directiva de caché (ej. el espacio de almacenamiento en caché está lleno y los datos más antiguos se eliminan). Para cambiar este comportamiento, podrías proporcionar un parámetro de caducidad al llamar [[yii\caching\Cache::set()|set()]] para guardar el elemento de datos. El parámetro nos indica por cuántos segundos el elemento se mantendrá válido en memoria caché. Cuando llames [[yii\caching\Cache::get()|get()]] para recuperar el elemento, si el tiempo de caducidad ha pasado, el método devolverá `false`, indicando que el elemento de datos no ha sido encontrado en la memoria caché. Por ejemplo,

```php
// guardar los datos en memoria caché al menos 45 segundos
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data ha caducado o no ha sido encontrado en la memoria caché
}
```


### Dependencias de Caché <span id="cache-dependencies"></span>

Además de configurar el tiempo de caducidad, los datos almacenados en caché pueden también ser invalidados conforme a algunos cambios en la caché de dependencias. Por ejemplo, [[yii\caching\FileDependency]] representa la dependencia del tiempo de modificación del archivo. Cuando esta dependencia cambia, significa que el archivo correspondiente ha cambiado. Como resultado, cualquier contenido anticuado que sea encontrado en la caché debería ser invalidado y la llamada a [[yii\caching\Cache::get()|get()]] debería retornar falso.

Una dependencia es representada como una instancia de [[yii\caching\Dependency]] o su clase hija. Cuando llamas [[yii\caching\Cache::set()|set()]] para almacenar un elemento de datos en la caché, puedes pasar el objeto de dependencia asociado. Por ejemplo,

```php
// Crear una dependencia sobre el tiempo de modificación del archivo example.txt.
$dependency = new \yii\caching\FileDependency(['fileName' => 'example.txt']);

// Los datos expirarán en 30 segundos.
// También podría ser invalidada antes si example.txt es modificado.
$cache->set($key, $data, 30, $dependency);

// La caché comprobará si los datos han expirado.
// También comprobará si la dependencia ha cambiado.
// Devolverá false si se encuentran algunas de esas condiciones.
$data = $cache->get($key);
```

Aquí abajo se muestra un sumario de las dependencias disponibles:

- [[yii\caching\ChainedDependency]]: la dependencia cambia si cualquiera de las dependencias en la cadena cambia.
- [[yii\caching\DbDependency]]: la dependencia cambia si el resultado de la consulta de la sentencia SQL especificada cambia.
- [[yii\caching\ExpressionDependency]]: la dependencia cambia si el resultado de la expresión de PHP especificada cambia.
- [[yii\caching\FileDependency]]:  la dependencia cambia si se modifica la última fecha de modificación del archivo.
- [[yii\caching\TagDependency]]: marca un elemento de datos en caché con un nombre de grupo. Puedes invalidar los elementos de datos almacenados en caché
  con el mismo nombre del grupo a la vez llamando a [[yii\caching\TagDependency::invalidate()]].


## Consultas en Caché <span id="query-caching"></span>

Las consultas en caché es una característica especial de caché construido sobre el almacenamiento de caché de datos. Se
proporciona para almacenar en caché el resultado de consultas a la base de datos.

Las consultas en caché requieren una [[yii\db\Connection|DB connection]] y un componente de aplicación caché válido. El uso básico de las consultas en memoria caché es el siguiente, asumiendo que `db` es una instancia de [[yii\db\Connection]]:

```php
$result = $db->cache(function ($db) {

    // el resultado de la consulta SQL será servida de la caché
    // si el cacheo de consultas está habilitado y el resultado de la consulta se encuentra en la caché
    return $db->createCommand('SELECT * FROM customer WHERE id=1')->queryOne();

});
```

El cacheo de consultas puede ser usado tanto para [DAO](db-dao.md) como para [ActiveRecord](db-active-record.md):

```php
$result = Customer::getDb()->cache(function ($db) {
    return Customer::find()->where(['id' => 1])->one();
});
```

> Nota: Algunos DBMS (ej. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html)) también soporta el almacenamiento en caché desde el mismo servidor de la BD. Puedes optar por utilizar cualquiera de los mecanismos de memoria caché. El almacenamiento en caché de consultas previamente descrito tiene la ventaja que de que se puede especificar dependencias de caché de una forma flexible y son potencialmente mucho más eficientes.


### Configuraciones <span id="query-caching-configs"></span>

Las consultas en caché tienen tres opciones configurables globales a través de [[yii\db\Connection]]:

* [[yii\db\Connection::enableQueryCache|enableQueryCache]]: activa o desactiva el cacheo de consultas.
  Por defecto es true. Tenga en cuenta que para activar el cacheo de consultas, también necesitas tener una caché válida, especificada por [[yii\db\Connection::queryCache|queryCache]].
* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: representa el número de segundos que un resultado de la consulta permanecerá válida en la memoria caché. Puedes usar 0 para indicar que el resultado de la consulta debe permanecer en la caché para siempre. Esta propiedad es el valor usado por defecto cuando [[yii\db\Connection::cache()]] es llamada sin especificar una duración.
* [[yii\db\Connection::queryCache|queryCache]]: representa el ID del componente de aplicación de caché.
  Por defecto es `'cache'`. El almacenamiento en caché de consultas se habilita sólo si hay un componente de la aplicación de caché válida.


### Usos <span id="query-caching-usages"></span>

Puedes usar [[yii\db\Connection::cache()]] si tienes multiples consultas SQL que necesitas a aprovechar el cacheo de consultas. El uso es de la siguiente manera,

```php
$duration = 60;     // resultado de la consulta de caché durante 60 segundos.
$dependency = ...;  // dependencia opcional

$result = $db->cache(function ($db) {

    // ... realiza consultas SQL aquí ...

    return $result;

}, $duration, $dependency);
```

Cualquier consulta SQL en una función anónima será cacheada durante el tiempo indicado con la dependencia especificada.
Si el resultado de la consulta se encuentra válida en la caché, la consulta se omitirá y el resultado se servirá de la caché en su lugar. Si no especificar el parámetro `$duration`, el valor [[yii\db\Connection::queryCacheDuration|queryCacheDuration]] será usado en su lugar.

A veces dentro de `cache()`, puedes querer desactivar el cacheo de consultas para algunas consultas especificas. Puedes usar [[yii\db\Connection::noCache()]] en este caso.

```php
$result = $db->cache(function ($db) {

    // consultas SQL que usan el cacheo de consultas

    $db->noCache(function ($db) {

        // consultas SQL que no usan el cacheo de consultas

    });

    // ...

    return $result;
});
```

Si lo deseas puedes usar el cacheo de consultas para una simple consulta, puedes llamar a [[yii\db\Command::cache()]] cuando construyas el comando. Por ejemplo,

```php
// usa el cacheo de consultas y asigna la duración de la consulta de caché por 60 segundos
$customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->cache(60)->queryOne();
```

También puedes usar [[yii\db\Command::noCache()]] para desactivar el cacheo de consultas de un simple comando. Por ejemplo,

```php
$result = $db->cache(function ($db) {

    // consultas SQL que usan cacheo de consultas

    // no usa cacheo de consultas para este comando
    $customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->noCache()->queryOne();

    // ...

    return $result;
});
```


### Limitaciones <span id="query-caching-limitations"></span>

El almacenamiento en caché de consultas no funciona con los resultados de consulta que contienen controladores de recursos.
Por ejemplo, cuando se utiliza el tipo de columna `BLOB` en algunos DBMS, el resultado de la consulta devolverá un recurso para manejar los datos de la columna.

Algunos sistemas de almacenamiento caché tienen limitación de tamaño. Por ejemplo, memcache limita el tamaño máximo de cada entrada a 1MB. Por lo tanto, si el tamaño de un resultado de la consulta excede ese límite, el almacenamiento en caché fallará.
