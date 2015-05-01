Cache de Fragmentos
================

Cache de fragmentos é responsável por armazenar em cache um fragmento de uma página web. Por exemplo, se uma
página exibe o sumario de vendas anuais em uma tabela, você pode armazenar esta tabela em cache para eliminar
o tempo necessário para gerar esta tabela para em cada requisição. O Cache de Fragmentos é construido a partir 
do [cache de dados](caching-data.md).

Para usar o cache de fragmentos, use o seguinte modelo em uma [view](structure-views.md):

```php
if ($this->beginCache($id)) {

    // ... gere o conteúdo aqui ...

    $this->endCache();
}
```
Ou seja, encapsule a lógica de geração do conteudo entre as chamadas [[yii\base\View::beginCache()|beginCache()]]
e [[yii\base\View::endCache()|endCache()]]. Se o conteúdo for encontrado em cache, [[yii\base\View::beginCache()|beginCache()]] irá renderizar o conteúdo em cache e retornará falso, e assim não executará a lógica de geração de conteúdo.
Caso contrário, o conteúdo será gerado, e quando [[yii\base\View::endCache()|endCache()]] for chamado, o conteúdo gerado será capturado e armazenado no cache.

Assim como [cache de dados](caching-data.md), uma `$id` única é necessária para identificar um conteúdo no cache.


## Opções do Cache <span id="caching-options"></span>

Você poderá especificar opções adicionais sobre o cache de fragmentos passando um array de opções como o segundo parâmetro do método [[yii\base\View::beginCache()|beginCache()]]. Por trás dos panos, este array de opções será usado para configurar um widget [[yii\widgets\FragmentCache]] que implementa, por sua vez, a funcionalidade de cache de fragmentos.

### Duração <span id="duration"></span>

Esta talvez a opção mais frequentemente usada com cache de fragmentos seja a 
[[yii\widgets\FragmentCache::duration|duration]].
Ela especifica por quantos segundos o conteúdo pode permanecer válido no cache. O código a seguir armazena em cache o fragmento do conteúdo por até uma hora:

```php
if ($this->beginCache($id, ['duration' => 3600])) {

    // ... gerar o conteúdo aqui ...

    $this->endCache();
}
```

Se a opção não for definida, o padrão definido é 60, que significa que o conteúdo em cache expirará em 60 segundosnds.

### Dependências <span id="dependencies"></span>

Assim como [cache de dados](caching-data.md#cache-dependencies), o fragmento de conteúdo sendo armazenado em cache pode ter dependencias.
Por exemplo, o conteúdo de um post sendo exibido depende de ele ter sido ou não modificado.

Para especificar uma dependencia, defina a opção [[yii\widgets\FragmentCache::dependency|dependency]], que pode
ser um objeto [[yii\caching\Dependency]] ou um array de configuração para criar um objeto de dependência.
O código a seguir especifica que o conteudo do fragmento depende do valor da coluna `atualizado_em`:

```php
$dependency = [
    'class' => 'yii\caching\DbDependency',
    'sql' => 'SELECT MAX(atualizado_em) FROM post',
];

if ($this->beginCache($id, ['dependency' => $dependency])) {

    // ... gere o conteúdo aqui ...

    $this->endCache();
}
```


### Variações <span id="variations"></span>

O Conteúdo armazenado em cache pode variar de acordo com alguns parâmetros. Por exemplo, para uma aplicação web
suportando múltiplas linguas, a mesma porção de código de uma view pode gerar conteúdo em diferentes línguagem. 
Desta forma, você pode desejar que o código em cache exiba um conteúdo diferente para a línguagem exibida na requisição.

Para especificar variações de cache, defina a opção [[yii\widgets\FragmentCache::variations|variations]],
que pode ser um array de valores escalares, cada um representando um fator de variação particular.
Por exemplo, para fazer o conteúdo em cache variar em função da linguagem, você pode usar o seguinte código:

```php
if ($this->beginCache($id, ['variations' => [Yii::$app->language]])) {

    // ... gerar o conteúdo aqui ...

    $this->endCache();
}
```


### <i>Cache Alternativo</i>(Toggling Caching) <span id="toggling-caching"></span>

Em alguns casos, você pode precisar habilitar o cache de fragmentos somente quando certas condições se aplicam.
Por exemplo, para uma página exibindo um formulário, e você deseja armazenar o formulário em cache apenas na
primeira requisição (via requisição GET). Qualquer exibição subsequente (via requisição POST) ao formulário não
deve ser armazenada em cache porque o formulário pode conter os dados submetidos pelo usuário. Para assim fazê-lo,
você pode definir a opção [[yii\widgets\FragmentCache::enabled|enabled]], da seguinte maneira:

```php
if ($this->beginCache($id, ['enabled' => Yii::$app->request->isGet])) {

    // ... gerar conteudo aqui ...

    $this->endCache();
}
```


## Nested Caching <span id="nested-caching"></span>

Fragment caching can be nested. That is, a cached fragment can be enclosed within another fragment which is also cached.
For example, the comments are cached in an inner fragment cache, and they are cached together with the
post content in an outer fragment cache. The following code shows how two fragment caches can be nested:

```php
if ($this->beginCache($id1)) {

    // ...content generation logic...

    if ($this->beginCache($id2, $options2)) {

        // ...content generation logic...

        $this->endCache();
    }

    // ...content generation logic...

    $this->endCache();
}
```

Different caching options can be set for the nested caches. For example, the inner caches and the outer caches
can use different cache duration values. Even when the data cached in the outer cache is invalidated, the inner
cache may still provide the valid inner fragment. However, it is not true vice versa. If the outer cache is
evaluated to be valid, it will continue to provide the same cached copy even after the content in the
inner cache has been invalidated. Therefore, you must be careful in setting the durations or the dependencies
of the nested caches, otherwise the outdated inner fragments may be kept in the outer fragment.


## Dynamic Content <span id="dynamic-content"></span>

When using fragment caching, you may encounter the situation where a large fragment of content is relatively
static except at one or a few places. For example, a page header may display the main menu bar together with
the name of the current user. Another problem is that the content being cached may contain PHP code that
must be executed for every request (e.g. the code for registering an asset bundle). Both problems can be solved
by the so-called *dynamic content* feature.

A dynamic content means a fragment of output that should not be cached even if it is enclosed within
a fragment cache. To make the content dynamic all the time, it has to be generated by executing some PHP code
for every request, even if the enclosing content is being served from cache.

You may call [[yii\base\View::renderDynamic()]] within a cached fragment to insert dynamic content
at the desired place, like the following,

```php
if ($this->beginCache($id1)) {

    // ...content generation logic...

    echo $this->renderDynamic('return Yii::$app->user->identity->name;');

    // ...content generation logic...

    $this->endCache();
}
```

The [[yii\base\View::renderDynamic()|renderDynamic()]] method takes a piece of PHP code as its parameter.
The return value of the PHP code is treated as the dynamic content. The same PHP code will be executed
for every request, no matter the enclosing fragment is being served from cached or not.
