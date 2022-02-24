Constructor de Consultas
========================

> Note: Esta sección está en desarrollo.

Yii proporciona una capa de acceso básico a bases de datos como se describe en la sección
[Objetos de Acceso a Bases de Datos](db-dao.md). La capa de acceso a bases de datos proporciona un método de bajo
nivel (low-level) para interaccionar con la base de datos. Aunque a veces puede ser útil la escritura de sentencias
SQLs puras, en otras situaciones puede ser pesado y propenso a errores. Otra manera de tratar con bases de datos puede
ser el uso de Constructores de Consultas (Query Builder). El Constructor de Consultas proporciona un medio orientado a
objetos para generar las consultas que se ejecutarán.

Un uso típico de Constructor de Consultas puede ser el siguiente:

```php
$rows = (new \yii\db\Query())
    ->select('id, name')
    ->from('user')
    ->limit(10)
    ->all();

// que es equivalente al siguiente código:

$query = (new \yii\db\Query())
    ->select('id, name')
    ->from('user')
    ->limit(10);

//  Crear un comando. Se puede obtener la consulta SQL actual utilizando $command->sql
$command = $query->createCommand();

// Ejecutar el comando:
$rows = $command->queryAll();
```

Métodos de Consulta
-------------------

Como se puede observar, primero se debe tratar con [[yii\db\Query]]. En realidad, `Query` sólo se encarga de
representar diversa información de la consulta. La lógica para generar la consulta se efectúa mediante
[[yii\db\QueryBuilder]] cuando se llama al método `createCommand()`, y la ejecución de la consulta la efectúa
[[yii\db\Command]].

Se ha establecido, por convenio, que [[yii\db\Query]] proporcione un conjunto de métodos de consulta comunes que
construirán la consulta, la ejecutarán, y devolverán el resultado. Por ejemplo:

- [[yii\db\Query::all()|all()]]: construye la consulta, la ejecuta y devuelve todos los resultados en formato de array.
- [[yii\db\Query::one()|one()]]: devuelve la primera fila del resultado.
- [[yii\db\Query::column()|column()]]: devuelve la primera columna del resultado.
- [[yii\db\Query::scalar()|scalar()]]: devuelve la primera columna en la primera fila del resultado.
- [[yii\db\Query::exists()|exists()]]: devuelve un valor indicando si la el resultado devuelve algo.
- [[yii\db\Query::count()|count()]]: devuelve el resultado de la consulta `COUNT`. Otros métodos similares incluidos
  son `sum($q)`, `average($q)`, `max($q)`, `min($q)`, que soportan las llamadas funciones de agregación. El parámetro
  `$q` es obligatorio en estos métodos y puede ser el nombre de la columna o expresión.

Construcción de Consultas
-------------------------

A continuación se explicará como construir una sentencia SQL que incluya varias clausulas. Para simplificarlo, usamos
`$query` para representar el objeto [[yii\db\Query]]:

### `SELECT`

Para formar una consulta `SELECT` básica, se necesita especificar que columnas y de que tablas se seleccionarán:

```php
$query->select('id, name')
    ->from('user');
```

Las opciones de select se pueden especificar como una cadena de texto (string) separada por comas o como un array. La
sintaxis del array es especialmente útil cuando se forma la selección dinámicamente.

```php
$query->select(['id', 'name'])
    ->from('user');
```

> Info: Se debe usar siempre el formato array si la clausula `SELECT` contiene expresiones SQL. Esto se debe a
  que una expresión SQL como `CONCAT(first_name, last_name) AS full_name` puede contener comas. Si se junta con otra
  cadena de texto de otra columna, puede ser que la expresión se divida en varias partes por comas, esto puede
  conllevar a errores.

Cuando se especifican columnas, se pueden incluir los prefijos de las tablas o alias de columnas, ej.  `user.id`,
`user.id AS user_id`. Si se usa un array para especificar las columnas, también se pueden usar las claves del array
para especificar los alias de columna, ej. `['user_id' => 'user.id', 'user_name' => 'user.name']`.

A partir de la versión 2.0.1, también se pueden seleccionar subconsultas como columnas. Por ejemplo:

```php
$subQuery = (new Query)->select('COUNT(*)')->from('user');
$query = (new Query)->select(['id', 'count' => $subQuery])->from('post');
// $query representa la siguiente sentencia SQL:
// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
```

Para seleccionar filas distintas, se puede llamar a `distinct()`, como se muestra a continuación:

```php
$query->select('user_id')->distinct()->from('post');
```

### `FROM`

Para especificar de que tabla(s) se quieren seleccionar los datos, se llama a `from()`:

```php
$query->select('*')->from('user');
```

Se pueden especificar múltiples tablas usando una cadena de texto separado por comas o un array. Los nombres de tablas
pueden contener prefijos de esquema (ej. `'public.user'`) y/o alias de tablas (ej. `'user u'). El método
entrecomillara automáticamente los nombres de tablas a menos que contengan algún paréntesis (que significa que se
proporciona la tabla como una subconsulta o una expresión de BD). Por ejemplo:

```php
$query->select('u.*, p.*')->from(['user u', 'post p']);
```

Cuando se especifican las tablas como un array, también se pueden usar las claves de los arrays como alias de tablas
(si una tabla no necesita alias, no se usa una clave en formato texto). Por ejemplo:

```php
$query->select('u.*, p.*')->from(['u' => 'user', 'p' => 'post']);
```

Se puede especificar una subconsulta usando un objeto `Query`. En este caso, la clave del array correspondiente se
usará como alias para la subconsulta.

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');
$query->select('*')->from(['u' => $subQuery]);
```

### `WHERE`

Habitualmente se seleccionan los datos basándose en ciertos criterios. El Constructor de Consultas tiene algunos
métodos útiles para especificarlos, el más poderoso de estos es `where`, y se puede usar de múltiples formas.

La manera más simple para aplicar una condición es usar una cadena de texto:

```php
$query->where('status=:status', [':status' => $status]);
```

Cuando se usan cadenas de texto, hay que asegurarse que se unen los parámetros de la consulta, no crear una consulta
mediante concatenación de cadenas de texto. El enfoque anterior es seguro, el que se muestra a continuación, no lo es:

```php
$query->where("status=$status"); // Peligroso!
```

En lugar de enlazar los valores de estado inmediatamente, se puede hacer usando `params` o `addParams`:

```php
$query->where('status=:status');
$query->addParams([':status' => $status]);
```

Se pueden establecer múltiples condiciones en `where` usando el *formato hash*.

```php
$query->where([
    'status' => 10,
    'type' => 2,
    'id' => [4, 8, 15, 16, 23, 42],
]);
```

El código generará la el siguiente SQL:

```sql
WHERE (`status` = 10) AND (`type` = 2) AND (`id` IN (4, 8, 15, 16, 23, 42))
```

El valor NULO es un valor especial en las bases de datos, y el Constructor de Consultas lo gestiona inteligentemente.
Este código:

```php
$query->where(['status' => null]);
```

da como resultado la siguiente cláusula WHERE:

```sql
WHERE (`status` IS NULL)
```

También se pueden crear subconsultas con objetos de tipo `Query` como en el siguiente ejemplo:

```php
$userQuery = (new Query)->select('id')->from('user');
$query->where(['id' => $userQuery]);
```

que generará el siguiente código SQL:

```sql
WHERE `id` IN (SELECT `id` FROM `user`)
```

Otra manera de usar el método es el formato de operando que es `[operator, operand1, operand2, ...]`.

El operando puede ser uno de los siguientes (ver también [[yii\db\QueryInterface::where()]]):

- `and`: los operandos deben concatenerase usando `AND`. por ejemplo, `['and', 'id=1', 'id=2']` generará
  `id=1 AND id=2`. Si el operando es un array, se convertirá en una cadena de texto usando las reglas aquí descritas.
  Por ejemplo, `['and', 'type=1', ['or', 'id=1', 'id=2']]` generará `type=1 AND (id=1 OR id=2)`. El método no
  ejecutará ningún filtrado ni entrecomillado.

- `or`: similar al operando `and` exceptuando que los operando son concatenados usando `OR`.

- `between`: el operando 1 debe ser el nombre de columna, y los operandos 2 y 3 deben ser los valores iniciales y
  finales del rango en el que se encuentra la columna. Por ejemplo, `['between', 'id', 1, 10]` generará
  `id BETWEEN 1 AND 10`.

- `not between`: similar a `between` exceptuando que  `BETWEEN` se reemplaza por `NOT BETWEEN` en la condición
  generada.

- `in`: el operando 1 debe ser una columna o una expresión de BD. El operando 2 puede ser un array o un objeto de tipo
  `Query`. Generará una condición `IN`. Si el operando 2 es un array, representará el rango de valores que puede
  albergar la columna o la expresión de BD; Si el operando 2 es un objeto de tipo `Query`, se generará una subconsulta
  y se usará como rango de la columna o de la expresión de BD. Por ejemplo, `['in', 'id', [1, 2, 3]]` generará
  `id IN (1, 2, 3)`. El método entrecomillará adecuadamente el nombre de columna y filtrará los valores del rango. El
  operando `in` también soporta columnas compuestas. En este caso, el operando 1 debe se un array de columnas,
  mientras que el operando 2 debe ser un array de arrays o un objeto de tipo `Query` que represente el rango de las
  columnas.

- `not in`: similar que el operando `in` exceptuando que `IN` se reemplaza por `NOT IN` en la condición generada.

- `like`: el operando 1 debe ser una columna o una expresión de BD, y el operando 2 debe ser una cadena de texto o un
  array que represente los valores a los que tienen que asemejarse la columna o la expresión de BD.Por ejemplo,
  `['like', 'name', 'tester']` generará `name LIKE '%tester%'`. Cuando se da el valor rango como un array, se
  generarán múltiples predicados `LIKE` y se concatenaran usando `AND`. Por ejemplo,
  `['like', 'name', ['test', 'sample']]` generará `name LIKE '%test%' AND name LIKE '%sample%'`. También se puede
  proporcionar un tercer operando opcional para especificar como deben filtrarse los caracteres especiales en los
  valores. El operando debe se un array que mapeen los caracteres especiales a sus caracteres filtrados asociados. Si
  no se proporciona este operando, se aplicará el mapeo de filtrado predeterminado. Se puede usar `false` o un array
  vacío para indicar que los valores ya están filtrados y no se necesita aplicar ningún filtro. Hay que tener en
  cuenta que cuando se usa un el mapeo de filtrado (o no se especifica el tercer operando), los valores se encerraran
  automáticamente entre un par de caracteres de porcentaje.

> Note: Cuando se usa PostgreSQL también se puede usar
[`ilike`](https://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE) en lugar de `like` para
filtrar resultados insensibles a mayúsculas (case-insensitive).

- `or like`: similar al operando `like` exceptuando que se usa `OR` para concatenar los predicados `LIKE` cuando haya
  un segundo operando en un array.

- `not like`: similar al operando `like` exceptuando que se usa `LIKE` en lugar de `NOT LIKE` en las condiciones
  generadas.

- `or not like`: similar al operando `not like` exceptuando que se usa `OR` para concatenar los predicados `NOT LIKE`.

- `exists`: requiere un operando que debe ser una instancia de [[yii\db\Query]] que represente la subconsulta. Esto
  generará una expresión `EXISTS (sub-query)`.

- `not exists`: similar al operando `exists` y genera una expresión `NOT EXISTS (sub-query)`.

Adicionalmente se puede especificar cualquier cosa como operando:

```php
$query->select('id')
    ->from('user')
    ->where(['>=', 'id', 10]);
```

Cuyo resultado será:

```sql
SELECT id FROM user WHERE id >= 10;
```

Si se construyen partes de una condición dinámicamente, es muy convenientes usar `andWhere()` y `orWhere()`:

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);
if (!empty($search)) {
    $query->andWhere(['like', 'title', $search]);
}
```

En el caso que `$search` no este vacío, se generará el siguiente código SQL:

```sql
WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```

#### Construcción de Condiciones de Filtro

Cuando se generan condiciones de filtro basadas en datos recibidos de usuarios (inputs), a menudo se quieren gestionar
de forma especial las "datos vacíos" para ignorarlos en los filtros. Por ejemplo, teniendo un formulario HTML que
obtiene el nombre de usuario y la dirección de correo electrónico. Si el usuario solo rellena el campo de nombre de
usuario, se puede querer generar una consulta para saber si el nombre de usuario recibido es valido. Se puede usar
`filterWhere()` para conseguirlo:

```php
// $username y $email son campos de formulario rellenados por usuarios
$query->filterWhere([
    'username' => $username,
    'email' => $email,
]);
```

El método `filterWhere()` es muy similar al método `where()`. La principal diferencia es que el `filterWhere()`
eliminará los valores vacíos de las condiciones proporcionadas. Por lo tanto si `$email` es "vació", la consulta
resultante será `...WHERE username=:username`; y si tanto `$username` como `$email` son "vacías", la consulta no
tendrá `WHERE`.

Decimos que un valor es *vacío* si es nulo, una cadena de texto vacía, una cadena de texto que consista en espacios en
blanco o un array vacío.

También se pueden usar `andFilterWhere()` y `orFilterWhere()` para añadir más condiciones de filtro.

### `ORDER BY`

Se pueden usar `orderBy` y `addOrderBy` para ordenar resultados:

```php
$query->orderBy([
    'id' => SORT_ASC,
    'name' => SORT_DESC,
]);
```

Aquí estamos ordenando por `id` ascendente y después por `name` descendente.

### `GROUP BY` and `HAVING`

Para añadir `GROUP BY` al SQL generado se puede usar el siguiente código:

```php
$query->groupBy('id, status');
```

Si se quieren añadir otro campo después de usar `groupBy`:

```php
$query->addGroupBy(['created_at', 'updated_at']);
```

Para añadir la condición `HAVING` se pueden usar los métodos `having` y `andHaving` y `orHaving`. Los parámetros para
ellos son similares a los del grupo de métodos `where`:

```php
$query->having(['status' => $status]);
```

### `LIMIT` and `OFFSET`

Para limitar el resultado a 10 filas se puede usar `limit`:

```php
$query->limit(10);
```

Para saltarse las 100 primeras filas, se puede usar:

```php
$query->offset(100);
```

### `JOIN`

Las clausulas `JOIN` se generan en el Constructor de Consultas usando el método join aplicable:

- `innerJoin()`
- `leftJoin()`
- `rightJoin()`

Este left join selecciona los datos desde dos tablas relacionadas en una consulta:

```php
$query->select(['user.name AS author', 'post.title as title'])
    ->from('user')
    ->leftJoin('post', 'post.user_id = user.id');
```

En el código, el primer parámetro del método `leftjoin` especifica la tabla a la que aplicar el join. El segundo
parámetro, define la condición del join.

Si la aplicación de bases de datos soporta otros tipos de joins, se pueden usar mediante el método `join` genérico:

```php
$query->join('FULL OUTER JOIN', 'post', 'post.user_id = user.id');
```

El primer argumento es el tipo de join a realizar. El segundo es la tabla a la que aplicar el join, y el tercero es la condición:

Como en `FROM`, también se pueden efectuar joins con subconsultas. Para hacerlo, se debe especificar la subconsulta
como un array que tiene que contener un elemento. El valor del array tiene que ser un objeto de tipo `Query` que
represente la subconsulta, mientras que la clave del array es el alias de la subconsulta. Por ejemplo:

```php
$query->leftJoin(['u' => $subQuery], 'u.id=author_id');
```

### `UNION`

En SQL `UNION` agrega resultados de una consulta a otra consulta. Las columnas devueltas por ambas consultas deben
coincidir. En Yii para construirla, primero se pueden formar dos objetos de tipo query y después usar el método
`union`:

```php
$query = new Query();
$query->select("id, category_id as type, name")->from('post')->limit(10);

$anotherQuery = new Query();
$anotherQuery->select('id, type, name')->from('user')->limit(10);

$query->union($anotherQuery);
```

Consulta por Lotes
---------------

Cuando se trabaja con grandes cantidades de datos, los métodos como [[yii\db\Query::all()]] no son adecuados ya que
requieren la carga de todos los datos en memoria. Para mantener los requerimientos de memoria reducidos, Yii
proporciona soporte a las llamadas consultas por lotes (batch query). Una consulta por lotes usa un cursor de datos y
recupera los datos en bloques.

Las consultas por lotes se pueden usar del siguiente modo:

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->orderBy('id');

foreach ($query->batch() as $users) {
    // $users is an array of 100 or fewer rows from the user table
}

// o si se quieren iterar las filas una a una
foreach ($query->each() as $user) {
    // $user representa uno fila de datos de la tabla user
}
```

Los métodos [[yii\db\Query::batch()]] y [[yii\db\Query::each()]] devuelven un objeto [[yii\db\BatchQueryResult]] que
implementa una interfaz `Iterator` y así se puede usar en el constructor `foreach`. Durante la primera iteración, se
efectúa una consulta SQL a la base de datos. Desde entonces, los datos se recuperan por lotes en las iteraciones. El
tamaño predeterminado de los lotes es 100, que significa que se recuperan 100 filas de datos en cada lote. Se puede
modificar el tamaño de los lotes pasando pasando un primer parámetro a los métodos `batch()` o `each()`.

En comparación con [[yii\db\Query::all()]], las consultas por lotes sólo cargan 100 filas de datos en memoria cada
vez. Si el procesan los datos y después se descartan inmediatamente, las consultas por lotes, pueden ayudar a mantener
el uso de memora bajo un limite.

Si se especifica que el resultado de la consulta tiene que ser indexado por alguna columna mediante
[[yii\db\Query::indexBy()]], las consultas por lotes seguirán manteniendo el indice adecuado. Por ejemplo,

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users esta indexado en la columna "username"
}

foreach ($query->each() as $username => $user) {
}
```
