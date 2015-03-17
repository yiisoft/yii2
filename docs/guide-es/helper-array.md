ArrayHelper
===========

Adicionalmente al [rico conjunto de funciones para arrays de PHP](http://php.net/manual/es/book.array.php) Yii array helper proporciona
métodos estáticos adicionales permitiendo trabajar con arrays de manera más eficiente.


## Devolviendo Valores <span id="getting-values"></span>

Recuperar valores de un array, un objeto o una estructura compleja usando PHP estándar es bastante
repetitivo. Tienes que comprobar primero si una clave existe con `isset`, después devolver el valor si existe, si no,
devolver un valor por defecto:

```php
class User
{
    public $name = 'Alex';
}

$array = [
    'foo' => [
        'bar' => new User(),
    ]
];

$value = isset($array['foo']['bar']->name) ? $array['foo']['bar']->name : null;
```

Yii proviene de un método muy conveniente para hacerlo:

```php
$value = ArrayHelper::getValue($array, 'foo.bar.name');
```

El primer argumento del método es de donde vamos a obtener el valor. El segundo argumento especifica como devolver el dato. Puede ser
de la siguiente manera:

- Nombre de la clave del array o de la propiedad del objeto para recuperar el valor.
- Conjunto de puntos separados por las claves del array o los nombres de las propiedades del objeto. Esto se ha usado en el ejemplo anterior.
- Un callback que devuelve un valor.

El callback se debería usar de la siguiente manera:

```php
$fullName = ArrayHelper::getValue($user, function ($user, $defaultValue) {
    return $user->firstName . ' ' . $user->lastName;
});
```

El tercer argumento opcional es el valor por defecto el cual es `null` si no se especifica. Podría ser utilizado de la siguiente manera:

```php
$username = ArrayHelper::getValue($comment, 'user.username', 'Unknown');
```

En caso de que quieras coger un valor y luego removerlo inmediatamente del array puedes usar el método `remove`:

```php
$array = ['type' => 'A', 'options' => [1, 2]];
$type = ArrayHelper::remove($array, 'type');
```

Después de ejecutar el código el `$array` contendrá `['options' => [1, 2]]` y `$type` debe ser `A`. Tenga en cuenta que a diferencia del método
`getValue`, `remove` solo soporta nombres clave simples.


## Comprobando la Existencia de Claves <span id="checking-existence-of-keys"></span>

`ArrayHelper::keyExists` funciona de la misma manera que [array_key_exists](http://php.net/manual/es/function.array-key-exists.php)
excepto que también soporta case-insensitive para la comparación de claves. Por ejemplo,

```php
$data1 = [
    'userName' => 'Alex',
];

$data2 = [
    'username' => 'Carsten',
];

if (!ArrayHelper::keyExists('username', $data1, false) || !ArrayHelper::keyExists('username', $data2, false)) {
    echo "Please provide username.";
}
```

## Recuperando Columnas <span id="retrieving-columns"></span>

A menudo necesitas obtener unos valores de una columna de las filas de datos u objetos de un array. Un ejemplo común es obtener una lista de IDs.

```php
$data = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$ids = ArrayHelper::getColumn($array, 'id');
```

El resultado será `['123', '345']`.

Si se requieren transformaciones adicionales o la manera de obtener el valor es complejo, se podría especificar como segundo argumento
una función anónima :

```php
$result = ArrayHelper::getColumn($array, function ($element) {
    return $element['id'];
});
```


## Re-indexar Arrays <span id="reindexing-arrays"></span>

Con el fin de indexar un array según una clave especificada, se puede usar el método `index`. La entrada del array debe ser
multidimensional o un array de objetos. La clave puede ser un nombre clave del sub-array, un nombre de una propiedad del objeto, o
una función anónima que retorne el valor de la clave dado el elemento del array.

Si el valor de la clave es null, el correspondiente elemento del array será desechado y no se pondrá en el resultado. Por ejemplo,

```php
$array = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$result = ArrayHelper::index($array, 'id');
// el resultado es:
// [
//     '123' => ['id' => '123', 'data' => 'abc'],
//     '345' => ['id' => '345', 'data' => 'def'],
// ]

// usando función anónima
$result = ArrayHelper::index($array, function ($element) {
    return $element['id'];
});
```


## Construyendo Mapas (Maps) <span id="building-maps"></span>

Con el fin de construir un mapa (pareja clave-valor) de un array multidimensional o un array de objetos puedes usar el método `map`.
Los parámetros `$from` y `$to`  especifican los nombres de las claves o los nombres de las propiedades que serán configuradas en el mapa. Opcionalmente, se puede
agrupar en el mapa de acuerdo al campo de agrupamiento `$group`. Por ejemplo,

```php
$array = [
    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
);

$result = ArrayHelper::map($array, 'id', 'name');
// el resultado es:
// [
//     '123' => 'aaa',
//     '124' => 'bbb',
//     '345' => 'ccc',
// ]

$result = ArrayHelper::map($array, 'id', 'name', 'class');
// el resultado es:
// [
//     'x' => [
//         '123' => 'aaa',
//         '124' => 'bbb',
//     ],
//     'y' => [
//         '345' => 'ccc',
//     ],
// ]
```


## Ordenamiento Multidimensional <span id="multidimensional-sorting"></span>

El método `multisort` ayuda a ordenar un array de objetos o arrays anidados por una o varias claves. Por ejemplo,

```php
$data = [
    ['age' => 30, 'name' => 'Alexander'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 19, 'name' => 'Barney'],
];
ArrayHelper::multisort($data, ['age', 'name'], [SORT_ASC, SORT_DESC]);
```

Después del ordenado obtendremos lo siguiente en `$data`:

```php
[
    ['age' => 19, 'name' => 'Barney'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 30, 'name' => 'Alexander'],
];
```

El segundo argumento que especifica las claves para ordenar puede ser una cadena si se trata de una clave, un array en caso de que tenga múltiples claves
o una función anónima como la siguiente

```php
ArrayHelper::multisort($data, function($item) {
    return isset($item['age']) ? ['age', 'name'] : 'name';
});
```

El tercer argumento es la dirección. En caso de ordenar por una clave podría ser `SORT_ASC` o
`SORT_DESC`. Si ordenas por múltiples valores puedes ordenar cada valor diferentemente proporcionando un array de
direcciones de ordenación.

El último argumento es un PHP sort flag que toma los mismos valores que los pasados a
PHP [sort()](http://php.net/manual/es/function.sort.php).


## Detectando Tipos de Array <span id="detecting-array-types"></span>

Es muy útil saber si un array es indexado o asociativo. He aquí un ejemplo:

```php
// sin claves especificadas
$indexed = ['Qiang', 'Paul'];
echo ArrayHelper::isIndexed($indexed);

// todas las claves son strings
$associative = ['framework' => 'Yii', 'version' => '2.0'];
echo ArrayHelper::isAssociative($associative);
```


## Codificación y Decodificación de Valores HTML <span id="html-encoding-values"></span>

Con el fin de codificar o decodificar caracteres especiales en un array de strings con entidades HTML puedes usar lo siguiente:

```php
$encoded = ArrayHelper::htmlEncode($data);
$decoded = ArrayHelper::htmlDecode($data);
```

Solo los valores se codifican por defecto. Pasando como segundo argumento `false` puedes codificar un array de claves también.
La codificación utilizará el charset de la aplicación y podría ser cambiado pasandole un tercer argumento.


## Fusionando Arrays <span id="merging-arrays"></span>

```php
  /**
     * Merges two or more arrays into one recursively.
     * If each array has an element with the same string key value, the latter
     * will overwrite the former (different from array_merge_recursive).
     * Recursive merging will be conducted if both arrays have an element of array
     * type and are having the same key.
     * For integer-keyed elements, the elements from the latter array will
     * be appended to the former array.
     * @param array $a array to be merged to
     * @param array $b array to be merged from. You can specify additional
     * arrays via third argument, fourth argument etc.
     * @return array the merged array (the original arrays are not changed.)
     */
    public static function merge($a, $b)
```


## Convirtiendo Objetos a Arrays <span id="converting-objects-to-arrays"></span>

A menudo necesitas convertir un objeto o un array de objetos a un array. El caso más común es convertir los modelos de
active record con el fin de servir los arrays de datos vía API REST o utilizarlos de otra manera. El siguiente código
se podría utilizar para hacerlo:

```php
$posts = Post::find()->limit(10)->all();
$data = ArrayHelper::toArray($post, [
    'app\models\Post' => [
        'id',
        'title',
        // el nombre de la clave del resultado del array => nombre de la propiedad
        'createTime' => 'created_at',
        // el nombre de la clave del resultado del array => función anónima
        'length' => function ($post) {
            return strlen($post->content);
        },
    ],
]);
```

El primer argumento contiene el dato que queremos convertir. En nuestro caso queremos convertir un modelo AR `Post`.

El segundo argumento es el mapeo de conversión por clase. Estamos configurando un mapeo para el modelo `Post`.
Cada array de mapeo contiene un conjunto de mapeos. Cada mapeo podría ser:

- Un campo nombre para incluir como está.
- Un par clave-valor del array deseado con un nombre clave y el nombre de la columna del modelo que tomará el valor.
- Un par clave-valor del array deseado con un nombre clave y una función anónima que retorne el valor.

El resultado de la conversión anterior será:


```php
[
    'id' => 123,
    'title' => 'test',
    'createTime' => '2013-01-01 12:00AM',
    'length' => 301,
]
```

Es posible proporcionar una manera predeterminada de convertir un objeto a un array para una clase especifica
mediante la implementación de la interfaz [[yii\base\Arrayable|Arrayable]] en esa clase.
