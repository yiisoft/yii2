Caché de Páginas
================

La caché de páginas se refiere a guardar el contenido de toda una página en el almacenamiento de caché del servidor.
Posteriormente, cuando la misma página sea requerida de nuevo, su contenido será devuelto desde la caché en vez de
volver a generarlo desde cero.

El almacenamiento en caché de páginas está soportado por [[yii\filters\PageCache]], un [filtro de acción](structure-filters.md).
Puede ser utilizado de la siguiente forma en un controlador:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\PageCache',
            'only' => ['index'],
            'duration' => 60,
            'variations' => [
                \Yii::$app->language,
            ],
            'dependency' => [
                'class' => 'yii\caching\DbDependency',
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
        ],
    ];
}
```

El código anterior establece que el almacenamiento de páginas en caché debe ser utilizado sólo en la acción `index`; el
contenido de la página debería almacenarse durante un máximo de 60 segundos y ser variado por el idioma actual de la
aplicación; además, el almacenamiento de la página en caché debería ser invalidado si el número total de
artículos ha cambiado.

Como puedes ver, la caché de páginas es muy similar a la [caché de fragmentos](caching-fragment.md). Ambos soportan opciones
tales como `duration`, `dependencies`, `variations`, y `enabled`. Su principal diferencia es que la caché de páginas está
implementado como un [filtro de acción](structure-filters.md) mientras que la caché de fragmentos se hace en un [widget](structure-widgets.md).

Puedes usar la [caché de fragmentos](caching-fragment.md) así como el [contenido dinámico](caching-fragment.md#dynamic-content)
junto con la caché de páginas.
