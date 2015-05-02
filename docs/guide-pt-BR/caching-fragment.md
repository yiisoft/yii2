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


### <i>Cache Alternante</i>(Toggling Caching) <span id="toggling-caching"></span>

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


## <i>Cache Aninhado</i>(Nested Caching) <span id="nested-caching"></span>

Cache de fragmentos pode ser aninhado. Isto é, um fragmento em cache pode estar contido em outro fragmento que também está em cache. Por exemplo, os comentários estão sendo armazenados em um cache de fragmento inserido em conjunto com o conteudo do post em um outro cache de fragmento. O código a seguir exibe como dois caches de fragmento podem ser aninhados.

```php
if ($this->beginCache($id1)) {

    // ...lógica de geração de conteúdo...

    if ($this->beginCache($id2, $options2)) {

        // ...lógica de geração de conteúdo...

        $this->endCache();
    }

    // ...lógica de geração de conteúdo...

    $this->endCache();
}
```

Diferentes opções de cache podem ser definidas para os caches aninhados. Por exemplo, o cache interior e o cache
exterior podem ter tempos diferentes de expiração. Mesmo quando um registro no cache exterior é invalidado, o cache
interior ainda pode permanecer válido. Entretanto, o inverso não pode acontecer. Se o cache exterior é identificado
como validado, ele continuará a servir a mesma copia em cache mesmo após o conteudo no cache interior ter sido
invalidado. Desta forma, você deve ser cuidadoso ao definir durações ou dependencias para os caches aninhados, 
já que os fragmentos interiores ultrapassados podem ser mantidos no fragmento externo.


## Conteúdo Dinâmico <span id="dynamic-content"></span>

Ao usar o cache de fragmentos, vocế pode encontrar-se na situação em que um grande fragmento de conteúdo é
relativamente estático exceto em alguns poucos lugares. Por exemplo, um cabeçalho de uma página pode exibir
a barra do menu principal junto ao nome do usuário logado. Outro problema é que o conteudo sendo armazenado em cache
pode conter código PHP que deve ser executado para cada requisição (ex. o código para registrar um <i>pacote de recursos estáticos</i>(asset bundles)). Ambos os problemas podem ser resolvidos com a funcionalidade, então chamada de *Conteúdos Dinâmicos*.

Um conteúdo dinâmico compreende um fragmento de uma saida que não deveria ser armazenada em cache mesmo que esteja encapsulada em um cache de fragmento. Para fazer o conteúdo dinâmico indefinidamente, este deve ser gerado pela execução de
algum código PHP em cada requisição, mesmo que o conteúdo encapsulado esteja sendo servido do cache. 

Vocế pode chamar [[yii\base\View::renderDynamic()]] dentro de um cache de fragmento para inserir conteúdo dinâmico no local desejado, como o seguinte,

```php
if ($this->beginCache($id1)) {

    // ...lógica de geração de conteúdo...

    echo $this->renderDynamic('return Yii::$app->user->identity->name;');

    // ...lógica de geração de conteúdo...

    $this->endCache();
}
```

O método [[yii\base\View::renderDynamic()|renderDynamic()]] recebe uma porção de código PHP como parâmetro.
O valor retornado pelo código PHP é tratado como conteúdo dinâmico. O mesmo código PHP será executado em
cada requisição, não importando se este esteja encapsulado em um fragmento em cache ou não.
