Proveedores de datos
====================

En las secciones sobre [paginación](output-pagination.md) y [ordenación](output-sorting.md) se
describe como permitir a los usuarios finales elegir que se muestre una página de datos en
particular, y ordenar los datos por algunas columnas.  Como la tarea de paginar y ordenar datos
es muy común, Yii proporciona un conjunto de clases *proveedoras de datos* para encapsularla.

Un proveedor de datos es una clase que implementa la interfaz [[yii\data\DataProviderInterface]].
Básicamente se encarga de obtener datos paginados y ordenados.  Normalmente se usa junto con
[_widgets_ de datos](output-data-widgets.md) para que los usuarios finales puedan paginar y
ordenar datos de forma interactiva.

Yii incluye las siguientes clases proveedoras de datos:

* [[yii\data\ActiveDataProvider]]: usa [[yii\db\Query]] o [[yii\db\ActiveQuery]] para consultar datos de bases de datos y devolverlos como _arrays_ o instancias [Active Record](db-active-record.md).
* [[yii\data\SqlDataProvider]]: ejecuta una sentencia SQL y devuelve los datos de la base de datos como _arrays_.
* [[yii\data\ArrayDataProvider]]: toma un _array_ grande y devuelve una rodaja de él basándose en las especificaciones de paginación y ordenación.

El uso de todos estos proveedores de datos comparte el siguiente patrón común:

```php
// Crear el proveedor de datos configurando sus propiedades de paginación y ordenación
$provider = new XyzDataProvider([
    'pagination' => [...],
    'sort' => [...],
]);

// Obtener los datos paginados y ordenados
$models = $provider->getModels();

// Obtener el número de elementos de la página actual
$count = $provider->getCount();

// Obtener el número total de elementos entre todas las páginas
$totalCount = $provider->getTotalCount();
```

Se puede especificar los comportamientos de paginación y ordenación de un proveedor de datos
configurando sus propiedades [[yii\data\BaseDataProvider::pagination|pagination]] y
[[yii\data\BaseDataProvider::sort|sort]], que corresponden a las configuraciones para
[[yii\data\Pagination]] y [[yii\data\Sort]] respectivamente.  También se pueden configurar a
`false` para inhabilitar las funciones de paginación y/u ordenación.

Los [_widgets_ de datos](output-data-widgets.md), como [[yii\grid\GridView]], tienen una
propiedad llamada `dataProvider` que puede tomar una instancia de un proveedor de datos y
mostrar los datos que proporciona.  Por ejemplo,

```php
echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

Estos proveedores de datos varían principalmente en la manera en que se especifica la fuente de
datos.  En las siguientes secciones se explica el uso detallado de cada uno de estos proveedores
de datos.


## Proveedor de datos activo <span id="active-data-provider"></span>

Para usar [[yii\data\ActiveDataProvider]], hay que configurar su propiedad
[[yii\data\ActiveDataProvider::query|query]].
Puede tomar un objeto [[yii\db\Query] o [[yii\db\ActiveQuery]].  En el primer caso, los datos
devueltos serán _arrays_.  En el segundo, los datos devueltos pueden ser _arrays_ o instancias de
[Active Record](db-active-record.md).  Por ejemplo:


```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['state_id' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'defaultOrder' => [
            'created_at' => SORT_DESC,
            'title' => SORT_ASC,
        ]
    ],
]);

// Devuelve un array de objetos Post
$posts = $provider->getModels();
```

En el ejemplo anterior, si `$query` se crea el siguiente código, el proveedor de datos
devolverá _arrays_ en bruto.

```php
use yii\db\Query;

$query = (new Query())->from('post')->where(['state' => 1]);
```

> Note: Si una consulta ya tiene la cláusula `orderBy`, las nuevas instrucciones de ordenación
  dadas por los usuarios finales (mediante la configuración de `sort`) se añadirán a la cláusula
  `orderBy` previa.  Las cláusulas `limit` y `offset` que pueda haber se sobrescribirán por la
  petición de paginación de los usuarios finales (mediante la configuración de `pagination`).

Por omisión, [[yii\data\ActiveDataProvider]] usa el componente `db` de la aplicación como
conexión con la base de datos.  Se puede indicar una conexión con base de datos diferente
configurando la propiedad [[yii\data\ActiveDataProvider::db]].


## Proveedor de datos SQL <span id="sql-data-provider"></span>

[[yii\data\SqlDataProvider]] funciona con una sentencia SQL en bruto, que se usa para obtener
los datos requeridos.
Basándose en las especificaciones de [[yii\data\SqlDataProvider::sort|sort]] y
[[yii\data\SqlDataProvider::pagination|pagination]], el proveedor ajustará las cláusulas
`ORDER BY` y `LIMIT` de la sentencia SQL acordemente para obtener sólo la página de datos
solicitados en el orden deseado.

Para usar [[yii\data\SqlDataProvider]], hay que especificar las propiedades
[[yii\data\SqlDataProvider::sql|sql]] y [[yii\data\SqlDataProvider::totalCount|totalCount]].
Por ejemplo:

```php
use yii\data\SqlDataProvider;

$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM post WHERE status=:status
', [':status' => 1])->queryScalar();

$provider = new SqlDataProvider([
    'sql' => 'SELECT * FROM post WHERE status=:status',
    'params' => [':status' => 1],
    'totalCount' => $count,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => [
            'title',
            'view_count',
            'created_at',
        ],
    ],
]);

// Devuelve un array de filas de datos
$models = $provider->getModels();
```

> Info: La propiedad [[yii\data\SqlDataProvider::totalCount|totalCount]] se requiere sólo si se
  necesita paginar los datos.  Esto es porque el proveedor modificará la sentencia SQL
  especificada vía [[yii\data\SqlDataProvider::sql|sql]] para que devuelva sólo la pagina de
  datos solicitada.  El proveedor sigue necesitando saber el número total de elementos de datos
  para calcular correctamente el número de páginas.


## Proveedor de datos de _arrays_ <span id="array-data-provider"></span>

Se recomienda usar [[yii\data\ArrayDataProvider]] cuando se trabaja con un _array_ grande.
El proveedor permite devolver una página de los datos del _array_ ordenados por una o varias
columnas.  Para usar [[yii\data\ArrayDataProvider]], hay que especificar la propiedad
[[yii\data\ArrayDataProvider::allModels|allModels]] como el _array_ grande.  Los elementos
del _array_ grande pueden ser _arrays_ asociativos (por ejemplo resultados de consultas de
[DAO](db-dao.md) u objetos (por ejemplo instancias de [Active Record](db-active-record.md).
Por ejemplo:

```php
use yii\data\ArrayDataProvider;

$data = [
    ['id' => 1, 'name' => 'name 1', ...],
    ['id' => 2, 'name' => 'name 2', ...],
    ...
    ['id' => 100, 'name' => 'name 100', ...],
];

$provider = new ArrayDataProvider([
    'allModels' => $data,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => ['id', 'name'],
    ],
]);

// Obtener las filas de la página solicitada
$rows = $provider->getModels();
```

> Note: En comparación con [Active Data Provider](#active-data-provider) y
  [SQL Data Provider](#sql-data-provider), Array Data Provider es menos eficiente porque
  requiere cargar *todos* los datos en memoria.


## Trabajar con las claves de los datos <span id="working-with-keys"></span>

Al utilizar los elementos de datos devueltos por un proveedor de datos, con frecuencia
necesita identificar cada elemento de datos con una clave única.
Por ejemplo, si los elementos de datos representan información de los clientes, puede querer
usar el ID de cliente como la clave de cada conjunto de datos de un cliente.
Los proveedores de datos pueden devolver una lista de estas claves correspondientes a los
elementos de datos devueltos por [[yii\data\DataProviderInterface::getModels()]].
Por ejemplo:

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
]);

// Devuelve un array de objetos Post
$posts = $provider->getModels();

// Devuelve los valores de las claves primarias correspondientes a $posts
$ids = $provider->getKeys();
```

En el ejemplo superior, como se le proporciona a [[yii\data\ActiveDataProvider]] un objeto
[[yii\db\ActiveQuery]], es lo suficientemente inteligente como para devolver los valores de
las claves primarias como las claves.  También puede indicar explícitamente cómo se deben
calcular los valores de la clave configurando [[yii\data\ActiveDataProvider::key]] con un
nombre de columna o un invocable que calcule los valores de la clave.  Por ejemplo:

```php
// Utiliza la columna «slug» como valores de la clave
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => 'slug',
]);

// Utiliza el resultado de md5(id) como valores de la clave
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => function ($model) {
        return md5($model->id);
    }
]);
```


## Creación de un proveedor de datos personalizado <span id="custom-data-provider"></span>

Para crear su propio proveedor de datos personalizado, debe implementar
[[yii\data\DataProviderInterface]].
Una manera más fácil es extender [[yii\data\BaseDataProvider]], que le permite centrarse
en la lógica central del proveedor de datos.  En particular, esencialmente necesita
implementar los siguientes métodos:

- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]: prepara los modelos
  de datos que estarán disponibles en la página actual y los devuelve como un _array_.
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]: acepta un _array_ de
  modelos de datos disponibles actualmente y devuelve las claves asociadas a ellos.
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]: devuelve un valor
  que indica el número total de modelos de datos en el proveedor de datos.

Debajo se muestra un ejemplo de un proveedor de datos que lee datos CSV eficientemente:

```php
<?php
use yii\data\BaseDataProvider;

class CsvDataProvider extends BaseDataProvider
{
    /**
     * @var string nombre del fichero CSV a leer
     */
    public $filename;

    /**
     * @var string|callable nombre de la columna clave o un invocable que la devuelva
     */
    public $key;

    /**
     * @var SplFileObject
     */
    protected $fileObject;  // SplFileObject es muy práctico para buscar una línea concreta en un fichero


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Abrir el fichero
        $this->fileObject = new SplFileObject($this->filename);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $models = [];
        $pagination = $this->getPagination();

        if ($pagination === false) {
            // En caso de que no haya paginación, leer todas las líneas
            while (!$this->fileObject->eof()) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        } else {
            // En caso de que haya paginación, leer sólo una única página
            $pagination->totalCount = $this->getTotalCount();
            $this->fileObject->seek($pagination->getOffset());
            $limit = $pagination->getLimit();

            for ($count = 0; $count < $limit; ++$count) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        }

        return $models;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];

            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        }

        return array_keys($models);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        $count = 0;

        while (!$this->fileObject->eof()) {
            $this->fileObject->next();
            ++$count;
        }

        return $count;
    }
}
```

## Filtrar proveedores de datos usando filtros de datos <span id="filtering-data-providers-using-data-filters"></span>

Si bien puede construir condiciones para un proveedor de datos activo manualmente tal
y como se describe en las secciones [Filtering Data](output-data-widgets.md#filtering-data)
y [Separate Filter Form](output-data-widgets.md#separate-filter-form) de la guía de
_widgets_ de datos, Yii tiene filtros de datos que son muy útiles si necesita
condiciones de filtro flexibles.  Los filtros de datos se pueden usar así:

```php
$filter = new ActiveDataFilter([
    'searchModel' => 'app\models\PostSearch'
]);

$filterCondition = null;

// Puede cargar los filtros de datos de cualquier fuente.
// Por ejemplo, si prefiere JSON en el cuerpo de la petición,
// use Yii::$app->request->getBodyParams() aquí abajo:
if ($filter->load(\Yii::$app->request->get())) {
    $filterCondition = $filter->build();
    if ($filterCondition === false) {
        // Serializer recibiría errores
        return $filter;
    }
}

$query = Post::find();
if ($filterCondition !== null) {
    $query->andWhere($filterCondition);
}

return new ActiveDataProvider([
    'query' => $query,
]);
```

El propósito del modelo `PostSearch` es definir por qué propiedades y valores se permite filtrar:

```php
use yii\base\Model;

class PostSearch extends Model
{
    public $id;
    public $title;

    public function rules()
    {
        return [
            ['id', 'integer'],
            ['title', 'string', 'min' => 2, 'max' => 200],
        ];
    }
}
```

Los filtros de datos son bastante flexibles.  Puede personalizar cómo se construyen
las condiciones y qué operadores se permiten.
Para más detalles consulte la documentación de la API en [[\yii\data\DataFilter]].
