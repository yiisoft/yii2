Caché de Fragmentos
===================

Caché de Fragmentos se refiere al almacenamiento en caché de un fragmento, o sección, de una página Web. Por ejemplo, si
una página muestra un sumario de la ventas anuales en una tabla, podrías guardar esta tabla en memoria caché para
eliminar el tiempo necesario para generar esta tabla en cada petición (request). El caché de fragmentos está construido
sobre el [caché de datos](caching-data.md).

Para usar el caché de fragmentos, utiliza el siguiente código en tu [vista](structure-views.md):


```php
if ($this->beginCache($id)) {

    // ... generar contenido aquí ...

    $this->endCache();
}
```

Es decir, encierra la lógica de la generación del contenido entre las llamadas a [[yii\base\View::beginCache()|beginCache()]] y
[[yii\base\View::endCache()|endCache()]]. Si el contenido se encuentra en la memoria caché, [[yii\base\View::beginCache()|beginCache()]]
mostrará el contenido y devolverá `false`, saltandose así la lógica de generación del contenido. De lo contrario, el
código de generación se ejecutaría y al alcanzar la llamada a [[yii\base\View::endCache()|endCache()]], el contenido
generado será capturado y almacenado en la memoria caché.

Como en [caché de datos](caching-data.md), un `$id` (clave) único es necesario para identificar un contenido guardado en
caché.


## Opciones de Caché <a name="caching-options"></a>

Puedes especificar opciones adicionales para el caché de fragmentos pasando la matriz (array) de opciones como segundo
parametro del método [[yii\base\View::beginCache()|beginCache()]]. Entre bastidores, esta matriz de opciones se utiliza
para configurar el widget [[yii\widgets\FragmentCache]] que es en realidad el que implementa la funcionalidad de caché
de fragmentos.

### Duración <a name="duration"></a>

Quizás la opción más utilizada en el caché de fragmentos es [[yii\widgets\FragmentCache::duration|duración]]. Ésta
especifica cuántos segundos el contenido puede permanecer como válido en la memoria caché. El siguiente código almacena
en caché el fragmento de contenido para una hora a lo sumo:

```php
if ($this->beginCache($id, ['duration' => 3600])) {

    // ... generar contenido aquí ...

    $this->endCache();
}
```

Si la opción no está activada, se tomará el valor por defecto 0, lo que significa que el contenido almacenado en caché
nunca expirará.


### Dependencias <a name="dependencies"></a>

Como en [caché de datos](caching-data.md#cache-dependencies), fragmento de contenido que está siendo almacenado en caché
también puede tener dependencias. Por ejemplo, el contenido de un artículo que se muestre depende de si el mensaje se
modifica o no.

Para especificar una dependencia, activa la opción [[yii\widgets\FragmentCache::dependency|dependencia]] (dependency),
que puede ser un objecto [[yii\caching\Dependency]] o la matriz de configuración para crear un objecto `Dependency`. El
siguiente código especifica que el caché de fragmento depende del cambio del valor de la columna `updated_at`:

```php
$dependency = [
    'class' => 'yii\caching\DbDependency',
    'sql' => 'SELECT MAX(updated_at) FROM post',
];

if ($this->beginCache($id, ['dependency' => $dependency])) {

    // ... generar contenido aquí ...

    $this->endCache();
}
```


### Variaciones <a name="variations"></a>

El contenido almacenado en caché puede variar de acuerdo a ciertos parámetros. Por ejemplo, para una aplicación Web que
soporte multiples idiomas, la misma pieza del código de la vista (View) puede generar el contenido almacenado en caché
en diferentes idiomas. Por lo tanto, es posible que desees hacer variaciones del mismo contenido almacenado en caché de
acuerdo con la actual selección del idioma en la aplicación.

Para especificar variacioens en la memoria caché, configura la opción [[yii\widgets\FragmentCache::variations|variaciones]]
(variations), la cual debere ser una matriz de valores escalares, cada uno de ellos representando un factor de variación.
Por ejemplo, para hacer que el contenido almacenado en caché varie por lenguaje, podrías usar el siguiente código:

```php
if ($this->beginCache($id, ['variations' => [Yii::$app->language]])) {

    // ... generar código aquí ...

    $this->endCache();
}
```


### Alternando el Almacenamiento en Caché <a name="toggling-caching"></a>

Puede que a veces quieras habilitar el caché de fragmentos únicamente cuando ciertas condiciones se cumplan. Por ejemplo,
para una página que muestra un formulario, tal vez quieras guardarlo en caché cuando es inicialmente solicitado (a
través de una petición GET). Cualquier muestra posterior (a través de una petición POST) del formulario no debería ser
almacenada en caché ya que el formulario puede que contenga entradas del usuario. Para hacerlo, podrias configurar la
opción de [[yii\widgets\FragmentCache::enabled|activado]] (enabled), de la siguiente manera:

```php
if ($this->beginCache($id, ['enabled' => Yii::$app->request->isGet])) {

    // ... generar contenido aquí ...

    $this->endCache();
}
```


## Almacenamiento en Caché Anidado <a name="nested-caching"></a>

El almacenamiento en caché de fragmentos se puede anidar. Es decir, un fragmento de caché puede ser encerrado dentro de
otro fragmento que también se almacena en caché. Por ejemplo, los comentarios se almacenan en una caché de fragmento
interno, y se almacenan conjuntamente con el contenido del artículo en un fragmento de caché exterior. El siguiente
código muestra cómo dos fragmentos de caché pueden ser anidados:

```php
if ($this->beginCache($id1)) {

    // ... lógica de generación de contenido externa ...

    if ($this->beginCache($id2, $options2)) {

        // ... lógica de generación de contenido anidada ...

        $this->endCache();
    }

    // ... lógica de generación de contenido externa ...

    $this->endCache();
}
```

Existen diferentes opciones de configuración de para los cachés anidados. Por ejemplo, las cachés internas y las cachés
externas pueden usar diferentes valores de duración. Aún cuando los datos almacenados en el caché externo sean invalidados,
el caché interno puede todavía proporcionar un fragmento válido. Sin embargo, no podría ser viceversa. Si el caché externo
es evaluado como válido, seguiría proporcionando la misma copia en caché incluso después de que el contenido  en el
caché interno haya sido invalidado. Por lo tanto, hay que tener mucho cuidado al configurar el tiempo de duración o las
dependencias de los cachés anidados, de lo contrario los fragmentos internos que ya estén obsoletes se pueden seguir
manteniendo en el fragmento externo.


## Contenido Dinámico <a name="dynamic-content"></a>

Cuando se usa el caché de fragmentos, podrías encontrarte en la situación que un fragmento grande de contenido es
relavitamente estático excepto en uno u otro lugar. Por ejemplo, el cabezal de una página (header) puede que muestre el
menú principal junto al nombre del usuario actual. Otro problema es que el contenido que está siendo almacenado en caché
puede que contenga código PHP que debe ser ejecutado en cada petición (request) (por ejemplo, el código para registrar
un paquete de recursos (asset bundle)). En ambos casos, podríamos resolver el problema con lo que llamamos la
característica de *contenido dinámico*.

Como *contenido dinámico* entendemos a un fragmento de salida que no debería ser guardado en caché incluso si está
encerrado dentro de un fragmento de caché. Para hacer el contenido dinámico todo el tiempo, éste ha de ser generado ejecutando
cierto código PHP en cada petición (request), incluso si el contenido está siendo mostrado desde el caché.

Puedes llamar a [[yii\base\View::renderDynamic()]] dentro de un fragmento almacenado en caché para insertar código
dinámico en el lugar deseado como, por ejemplo, de la siguiente manera,

```php
if ($this->beginCache($id1)) {

    // ... lógica de generación de contenido ...

    echo $this->renderDynamic('return Yii::$app->user->identity->name;');

    // ... lógica de generación de contenido ...

    $this->endCache();
}
```

El método [[yii\base\View::renderDynamic()|renderDynamic()]] toma una pieza de código PHP como su parámetro. El valor
devuelto del código PHP se trata como contenido dinámico. El mismo código PHP será ejecutado en cada petición (request),
sin importar que esté dentro de un fragmento que está siendo servido desde el caché o no.
