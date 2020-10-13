Paginación
==========

Cuando hay muchos datos a mostrar en una sola página, una estrategia común es mostrarlos en varias
páginas y en cada una de ellas mostrar sólo una pequeña porción de datos. Esta estrategia es conocida como *paginación*.

Yii utiliza el objeto [[yii\data\Pagination]] para representar la información acerca del esquema de paginación. En particular,

* [[yii\data\Pagination::$totalCount|cuenta total]] especifica el número total de ítems de datos. Ten en cuenta que
  este es normalmente un número mucho mayor que el número de ítems necesarios a mostrar en una simple página.
* [[yii\data\Pagination::$pageSize|tamaño de página]] especifica cuántos ítems de datos contiene cada página. El valor
  por defecto es 20.
* [[yii\data\Pagination::$page|página actual]] da el número de la página actual (comenzando desde 0). El valor
  por defecto es 0, lo que sería la primera página.

Con un objeto [[yii\data\Pagination]] totalmente especificado, puedes obtener y mostrar datos en partes. Por ejemplo,
si estás recuperando datos de una base de datos, puedes especificar las cláusulas `OFFSET` y `LIMIT` de la consulta a la BD
correspondientes a los valores provistos por la paginación. A continuación hay un ejemplo, 

```php
use yii\data\Pagination;

// construye una consulta a la BD para obtener todos los artículos con status = 1
$query = Article::find()->where(['status' => 1]);

// obtiene el número total de artículos (pero no recupera los datos de los artículos todavía)
$count = $query->count();

// crea un objeto paginación con dicho total
$pagination = new Pagination(['totalCount' => $count]);

// limita la consulta utilizando la paginación y recupera los artículos
$articles = $query->offset($pagination->offset)
    ->limit($pagination->limit)
    ->all();
```

¿Qué página de artículos devolverá el ejemplo de arriba? Depende de si se le es pasado un parámetro  llamado `page`.
Por defecto, la paginación intentará definir la [[yii\data\Pagination::$page|página actual]] con
el valor del parámetro `page`. Si el parámetro no es provisto, entonces tomará por defecto el valor 0.

Para facilitar la construcción de elementos UI que soporten paginación, Yii provee el widget [[yii\widgets\LinkPager]],
que muestra una lista de botones de navegación que el usuario puede presionar para indicar qué página de datos debería mostrarse.
El widget toma un objeto de paginación y tal manera conoce cuál es la página actual y cuántos botones
debe mostrar. Por ejemplo,

```php
use yii\widgets\LinkPager;

echo LinkPager::widget([
    'pagination' => $pagination,
]);
```

Si quieres construir los elementos de UI manualmente, puedes utilizar [[yii\data\Pagination::createUrl()]] para generar URLs que
dirigirán a las distintas páginas. El método requiere un parámetro de página y generará una URL apropiadamente formada
contieniendo el parámetro de página. Por ejemplo,

```php
// especifica la ruta que la URL generada debería utilizar
// Si no lo especificas, se utilizará la ruta de la petición actual
$pagination->route = 'article/index';

// muestra: /index.php?r=article%2Findex&page=100
echo $pagination->createUrl(100);

// muestra: /index.php?r=article%2Findex&page=101
echo $pagination->createUrl(101);
```

> Tip: puedes personalizar el parámetro `page` de la consulta configurando
  la propiedad [[yii\data\Pagination::pageParam|pageParam]] al crear el objeto de la paginación.
