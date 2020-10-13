Cache de Página
============

O Cache de página é responsável por armazenar em cache o conteúdo de uma página inteira no servidor. Mais tarde, 
quando a mesma página é requisitada novamente, seu conteúdo será servido do cache em vez de ela ser gerada novamente
do zero.

O Cache de página é implementado pela classe [[yii\filters\PageCache]], um [filtro de ações](structure-filters.md).
Esta pode ser usada da seguinte maneira em uma classe de controller:

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

O código acima afirma que o cache da página deve ser usado apenas para a ação `index`; o conteúdo da página deve 
ser armazenado em cache por, no máximo, 60 segundos e deve variar de acordo com a linguagem atual da aplicação;
e esta página em cache deve ser invalidada se o número total de *posts* for alterado.

Como você pode observar, o cache de página é bastante similar ao [cache de fragmentos](caching-fragment.md). Ambos suportam opções como `duration`, `dependencies`, `variations`, e `enabled`. Sua principal diferença é que o cache de página é implementado como um [filtro de ações](structure-filters.md) enquanto que o cache de fragmentos é um [widget](structure-widgets.md).

Você pode usar o [cache de fragmentos](caching-fragment.md) ou [conteúdo dinâmico](caching-fragment.md#dynamic-content)
em conjunto com o cache de página.

