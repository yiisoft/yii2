Widgets de datos
================

Yii proporciona un conjunto de [widgets](structure-widgets.md) que se pueden usar para mostrar datos.
Mientras que el _widget_ [DetailView](#detail-view) se puede usar para mostrar los datos de un único
registro, [ListView](#list-view) y [GridView](#grid-view) se pueden usar para mostrar una lista o
tabla de registros de datos proporcionando funcionalidades como paginación, ordenación y filtro.


DetailView <span id="detail-view"></span>
----------

El _widget_ [[yii\widgets\DetailView|DetailView]] muestra los detalles de un único
[[yii\widgets\DetailView::$model|modelo]] de datos.

Se recomienda su uso para mostrar un modelo en un formato estándar (por ejemplo, cada atributo del
modelo se muestra como una fila en una tabla).  El modelo puede ser tanto una instancia o subclase
de [[\yii\base\Model]] como un [active record](db-active-record.md) o un _array_ asociativo.

DetailView usa la propiedad [[yii\widgets\DetailView::$attributes|$attributes]] para determinar
qué atributos del modelo se deben mostrar y cómo se deben formatear.
En la [sección sobre formateadores](output-formatting.html) se pueden ver las opciones de formato
disponibles.

Un uso típico de DetailView sería así:

```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',                                           // atributo title (en texto plano)
        'description:html',                                // atributo description formateado como HTML
        [                                                  // nombre del propietario del modelo
            'label' => 'Owner',
            'value' => $model->owner->name,
            'contentOptions' => ['class' => 'bg-red'],     // atributos HTML para personalizar el valor
            'captionOptions' => ['tooltip' => 'Tooltip'],  // atributos HTML para personalizar la etiqueta
        ],
        'created_at:datetime',                             // fecha de creación formateada como datetime
    ],
]);
```

Recuerde que a diferencia de [[yii\widgets\GridView|GridView]], que procesa un conjunto de modelos,
[[yii\widgets\DetailView|DetailView]] sólo procesa uno.  Así que la mayoría de las veces no hay
necesidad de usar funciones anónimas ya que `$model` es el único modelo a mostrar y está disponible
en la vista como una variable.

Sin embargo, en algunos casos el uso de una función anónima puede ser útil.  Por ejemplo cuando
`visible` está especificado y se desea impedir el cálculo de `value` en case de que evalúe a `false`:

```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        [
            'attribute' => 'owner',
            'value' => function ($model) {
                return $model->owner->name;
            },
            'visible' => \Yii::$app->user->can('posts.owner.view'),
        ],
    ],
]);
```


ListView <span id="list-view"></span>
--------

El _widget_ [[yii\widgets\ListView|ListView]] se usa para mostrar datos de un
[proveedor de datos](output-data-providers.md).
Cada modelo de datos se representa usando el [[yii\widgets\ListView::$itemView|fichero de vista]]
indicado.
Como proporciona de serie funcionalidades tales como paginación, ordenación y filtro,
es útil tanto para mostrar información al usuario final como para crear una interfaz
de usuario de gestión de datos.

Un uso típico es el siguiente:

```php
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);

echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
]);
```

El fichero de vista `_post` podría contener lo siguiente:

```php
<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>
<div class="tarea">
    <h2><?= Html::encode($model->title) ?></h2>

    <?= HtmlPurifier::process($model->text) ?>
</div>
```

En el fichero de vista anterior, el modelo de datos actual está disponible como `$model`.
Además están disponibles las siguientes variables:

- `$key`: mixto, el valor de la clave asociada a este elemento de datos.
- `$index`: entero, el índice empezando por cero del elemento de datos en el array de elementos devuelto por el proveedor de datos.
- `$widget`: ListView, esta instancia del _widget_.

Si se necesita pasar datos adicionales a cada vista, se puede usar la propiedad
[[yii\widgets\ListView::$viewParams|$viewParams]] para pasar parejas clave-valor como las siguientes:

```php
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
    'viewParams' => [
        'fullView' => true,
        'context' => 'main-page',
        // ...
    ],
]);
```

Entonces éstas también estarán disponibles en la vista como variables.


GridView <span id="grid-view"></span>
--------

La cuadrícula de datos o [[yii\grid\GridView|GridView]] es uno de los _widgets_ de Yii
más potentes.  Es extremadamente útil si necesita construir rápidamente la sección de
administración del sistema.  Recibe los datos de un [proveedor de datos](output-data-providers.md)
y representa cada fila usando un conjunto de [[yii\grid\GridView::columns|columnas]]
que presentan los datos en forma de tabla.

Cada fila de la tabla representa los datos de un único elemento de datos, y una columna
normalmente representa un atributo del elemento (algunas columnas pueden corresponder a
expresiones complejas de los atributos o a un texto estático).

El mínimo código necesario para usar GridView es como sigue:

```php
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

El código anterior primero crea un proveedor de datos y a continuación usa GridView
para mostrar cada atributo en cada fila tomados del proveedor de datos.  La tabla
mostrada está equipada de serie con las funcionalidades de ordenación y paginación.


### Columnas de la cuadrícula <span id="grid-columns"></span>

Las columnas de la tabla se configuran en términos de clase [[yii\grid\Column]], que
se configuran en la propiedad [[yii\grid\GridView::columns|columns]] de la configuración
del GridView.
Dependiendo del tipo y ajustes de las columnas éstas pueden presentar los datos de
diferentes maneras.
La clase predefinida es [[yii\grid\DataColumn]], que representa un atributo del modelo
por el que se puede ordenar y filtrar.


```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        // Columnas sencillas definidas por los datos contenidos en $dataProvider.
        // Se usarán los datos de la columna del modelo.
        'id',
        'username',
        // Un ejemplo más complejo.
        [
            'class' => 'yii\grid\DataColumn',  // Se puede omitir, ya que es la predefinida.
            'value' => function ($data) {
                return $data->name;  // $data['name'] para datos de un array, por ejemplo al usar SqlDataProvider.
            },
        ],
    ],
]);
```

Observe que si no se especifica la parte [[yii\grid\GridView::columns|columns]] de la
configuración, Yii intenta mostrar todas las columnas posibles del modelo del proveedor
de datos.


### Clases de columna <span id="column-classes"></span>

Las columnas de la cuadrícula se pueden personalizar usando diferentes clases de columna:

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- aquí
            // puede configurar propiedades adicionales aquí
        ],
```

Además de las clases de columna proporcionadas por Yii que se revisarán más abajo,
puede crear sus propias clases de columna.

Cada clase de columna extiende [[yii\grid\Column]] de modo que hay algunas opciones
comunes que puede establecer al configurar las columnas de una cuadrícula.

- [[yii\grid\Column::header|header]] permite establecer el contenida para la fila cabecera
- [[yii\grid\Column::footer|footer]] permite establece el contenido de la fila al pie
- [[yii\grid\Column::visible|visible]] define si la columna debería ser visible.
- [[yii\grid\Column::content|content]] le permite pasar una función PHP válida que devuelva datos para una fila.  El formato es el siguiente:

  ```php
  function ($model, $key, $index, $column) {
      return 'una cadena';
  }
  ```

Puede indicar varias opciones HTML del contenedor pasando _arrays_ a:

- [[yii\grid\Column::headerOptions|headerOptions]]
- [[yii\grid\Column::footerOptions|footerOptions]]
- [[yii\grid\Column::filterOptions|filterOptions]]
- [[yii\grid\Column::contentOptions|contentOptions]]

