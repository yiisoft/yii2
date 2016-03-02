Cache de Dados
============

O Cache de Dados é responsável por armazenar uma ou mais variáveis PHP em um arquivo temporário para
ser recuperado posteriormente.
Este também é a fundação para funcionalidades mais avançadas do cache, como [cache de consulta](#query-caching)
e [cache de página](caching-page.md).

O código a seguir é um padrão de uso típico de cache de dados, onde `$cache` refere-se a
um [Componente de Cache](#cache-components):

```php
// tentar recuperar $data do cache
$data = $cache->get($key);

if ($data === false) {

    // $data não foi encontrado no cache, calculá-la do zero

    // armzaenar $data no cache para que esta possa ser recuperada na próxima vez
    $cache->set($key, $data);
}

// $data é acessível a partir daqui
```


## Componentes de Cache <span id="cache-components"></span>

O cache de dados se baseia nos, então chamados, *Componentes de Cache* que representam vários armazenamentos de cache,
como memória, arquivos, bancos de dados.

Componentes de Cache são normalmente registrados como [componentes de aplicação](structure-application-components.md) para que possam ser globalmente configuraveis e acessiveis. O código a seguir exibe como configurar o componente de aplicação `cache` para usar [memcached](http://memcached.org/) com dois servidores de cache:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\MemCache',
        'servers' => [
            [
                'host' => 'servidor1',
                'port' => 11211,
                'weight' => 100,
            ],
            [
                'host' => 'servidor2',
                'port' => 11211,
                'weight' => 50,
            ],
        ],
    ],
],
```

Você pode então, acessar o componente de cache acima usando a expressão `Yii::$app->cache`.

Já que todos os componentes de cache suportam as mesmas APIs, você pode trocar o componente de cache por outro 
reconfigurando-o nas configurações da aplicação sem modificar o código que usa o cache.
Por exemplo, você pode modificar a configuração acima para usar [[yii\caching\ApcCache|APC cache]]:


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Dica: você pode registrar múltiplos componentes de cache na aplicação. O componente chamado `cache` é usado 
  por padrão por muitas classes dependentes de cache (ex. [[yii\web\UrlManager]]).


### Sistemas de cache suportados <span id="supported-cache-storage"></span>

Yii suporta uma ampla gama de sistemas de cache. A seguir um sumario:

* [[yii\caching\ApcCache]]: usa a extensão do PHP [APC](http://php.net/manual/en/book.apc.php). Esta opção pode ser
  considerada a mais rápida ao se implementar o cache de uma aplicação densa e centralizada (ex. um
  servidor, sem balanceadores de carga dedicados, etc.).
* [[yii\caching\DbCache]]: usa uma tabela no banco de dados para armazenar os dados em cache. Para usar este cache
  você deve criar uma tabela como especificada em [[yii\caching\DbCache::cacheTable]].
* [[yii\caching\DummyCache]]: serve apenas como um substituto e não faz nenhum cache na realidade
  O propósito deste comoponente é simplificar o codigo que precisa checar se o cache está disponível.
  Por exemplo, durante o desenvolvimento or se o servidor não suporta cache, você pode configurar um
  componente de cache para usar este cache. Quando o suporte ao cache for habilitado, você pode trocar o
  para o componente correspondente. Em ambos os casos, você pode usar o mesmo código 
  `Yii::$app->cache->get($key)` para tentar recuperar os dados do cache sem se procupar que
  `Yii::$app->cache` possa ser `null`.
* [[yii\caching\FileCache]]: usa arquivos para armazenar os dados em cache. Este é particularmente indicado 
  para armazenar grandes quantidades de dados como o conteúdo da página.
* [[yii\caching\MemCache]]: usa o [memcache](http://php.net/manual/en/book.memcache.php) do PHP e as extensões
  [memcached](http://php.net/manual/en/book.memcached.php). Esta opção pode ser considerada a mais rápida
  ao se implementar o cache em aplicações distribuidas (ex. vários servidores, balanceadores de carga, etc.)
* [[yii\redis\Cache]]: implementa um componente de cache baseado em armazenamento chave-valor 
  [Redis](http://redis.io/) (requere redis versão 2.6.12 ou mais recente).
* [[yii\caching\WinCache]]: usa a extensão PHP [WinCache](http://iis.net/downloads/microsoft/wincache-extension)
  ([veja também](http://php.net/manual/en/book.wincache.php)).
* [[yii\caching\XCache]]: usa a extensão PHP [XCache](http://xcache.lighttpd.net/).
* [[yii\caching\ZendDataCache]]: usa
  [Cache de Dados Zend](http://files.zend.com/help/Zend-Server-6/zend-server.htm#data_cache_component.htm)
  como o meio de cache subjacente.


> Dica: Você pode usar vários tipos de cache na mesma aplicação. Uma estratégia comum é usar caches baseados 
  em memória para armazenar dados pequenos mas constantemente usados (ex. dados estatísticos), e usar caches
  baseados em arquivo ou banco da dados para armazenar dados que são maiores mas são menos usados 
  (ex. conteúdo da página).


## APIs De Cache <span id="cache-apis"></span>

Todos os componentes de caches extendem a mesma classe base [[yii\caching\Cache]] e assim suportam as seguintes APIs:

* [[yii\caching\Cache::get()|get()]]: recupera um registro no cache usando uma chave específica. 
  Retorna `false` caso o item não for encontrado no cache ou se o registro está expirado/invalidado.
* [[yii\caching\Cache::set()|set()]]: armazena um registro no cache identificado por uma chave.
* [[yii\caching\Cache::add()|add()]]: armazena um registro no cache identificado por uma chave se a chave não 
  for encontrada em cache.
* [[yii\caching\Cache::mget()|mget()]]: recupera múltiplos registros do cache com as chaves especificadas.
* [[yii\caching\Cache::mset()|mset()]]: armazena múltiplos registros no cachee. Cada item identificado por uma chave.
* [[yii\caching\Cache::madd()|madd()]]: armazena múltiplos registros no cachee. Cada item identificado por uma chave.
  Se a chave já existir em cache, o registro é ignorado.
* [[yii\caching\Cache::exists()|exists()]]: retorna se a chave específica é encontrada no cache.
* [[yii\caching\Cache::delete()|delete()]]: remove um registro do cache identificado por uma chave.
* [[yii\caching\Cache::flush()|flush()]]: remove todos os registros do cache.

> Note: Não armazene o valor boleano `false` diretamente, porque o método [[yii\caching\Cache::get()|get()]] retorna `false`para indicar que o registro não foi encontrado em cache. Você pode armazena `false` em um array e armazenar este em cache para evitar este problema.

Alguns tipos de cache como MemCache, APC, suportam recuperar em lote múltiplos registros em cache, o que poder reduzir
o custo de processamento envolvido ao recuperar informações em cache. The APIs [[yii\caching\Cache::mget()|mget()]]
e [[yii\caching\Cache::madd()|madd()]] são equipadas para explorar esta funcionalidade. Em caso do cache em questão não suportar esta funcionalidade, ele será simulado.

Como [[yii\caching\Cache]] implementa `ArrayAccess`, um componente de cache pode ser usado como um array. A seguir alguns exemplos:

```php
$cache['var1'] = $valor1;  // equivalente a: $cache->set('var1', $valor1);
$valor2 = $cache['var2'];  // equivalente a: $valor2 = $cache->get('var2');
```


### Chaves de Cache <span id="cache-keys"></span>

Cada registro armazenado no cache é identificado por uma chave única. Quando você armazena um registro em cache,
você deve especificar uma chave para ele. Mais tarde, quando você quiser recuperar o registro do cache, você deve 
fornecer a chave correspondente.

Você pode usar uma string ou um valor arbitrario como uma chave do cache. Quando a chave não for uma string, ela será
automaticamente serializada em uma string.

Uma estratégia comum ao definir uma chave de cache é incluir todos os fatores determinantes na forma de um array.
Por exemplo, [[yii\db\Schema]] usa a seguinte chave para armazenar a informação de um esquema de uma tabela do banco
de dados.

```php
[
    __CLASS__,              // nome da classe do esquema
    $this->db->dsn,         // nome da fonte de dados da conexão BD
    $this->db->username,    // usuario da conexão BD
    $name,                  // nome da tabela
];
```

Como você pode ver, a chave inclui toda a informação necessária para especificar unicamente uma tabela do banco.

Quando o cache de diferentes aplicações é armazenado no mesmo lugar, é aconselhavel especificar, para cada 
aplicação, um prefíxo único a chave do cache para evitar conflitos entre elas. Isto pode ser feito ao configurar
a propriedade [[yii\caching\Cache::keyPrefix]]. Por exemplo, na configuração da aplicação você pode escrever o seguinte código:

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'minhaapp',       // um prefíxo de chave único
    ],
],
```
Para assegurar interoperabilidade, apenas caracteres numéricos devem ser usados.
To ensure interoperability, only alphanumeric characters should be used.


### Expiração de Cache <span id="cache-expiration"></span>

Um registro armazenado em cache não será apagado a menos que seja removido por alguma política seja aplicada
(ex. espaço determinado para o cache esteja cheio e os registros mais antigos sejam removidos). Para alterar
estes comportamente, você pode fornecer um parametro de expiração ao chamar [[yii\caching\Cache::set()|set()]]
para armazenar um registro. O parametro indica por quantos segundos um registro pode permanecer válidado no cache.
Quando você chamar [[yii\caching\Cache::get()|get()]] para recuperar um registro, se o tempo de expiração houver passado, o método irá retornar `false`, indicando que o registro não foi encontrado no cache. Por exemplo,

```php
// Manter o registro em cache por até 45 segundos
$cache->set($chave, $registro, 45);

sleep(50);

$data = $cache->get($chave);
if ($registro === false) {
    // $registro está expirado ou não foi encontrado no sistema
}
```


### Dependências de Cache <span id="cache-dependencies"></span>

Além da definição de expiração, um registro em cache pode também ser invalidado por mudanças nas, então chamadas,
*dependências de cache*. Por exemplo, [[yii\caching\FileDependency]] representa a dependência na data de modificação
de um arquivo.
Quando esta dependência muda, significa que o arquivo correspondente foi mudado. Como um resultado, qualquer 
arquivo com data ultrapassada encontrado no cache deve ser invalidado e a chamada de [[yii\caching\Cache::get()|get()]]
retornará `false`.

Dependências de Cache são representadas como objetos de classes dependentes de [[yii\caching\Dependency]]. Quando você chamar [[yii\caching\Cache::set()|set()]] para armazenar um registro em cache, você pode passar um objeto de dependência. Por exemplo,

```php
// Criar uma dependência sobre a data de modificação do arquivo exemplo.txt.
$dependencia = new \yii\caching\FileDependency(['fileName' => 'exemplo.txt']);

// O registro irá expirar em 30 segundos.
// Ele também pode ser invalidado antes, caso o exemplo.txt seja modificado.
$cache->set($key, $data, 30, $dependency);

// O cache irá verificar se o registro expirou.
// E também irá vefiricar se a dependência associada foi alterada.
// Ele retornará false se qualquer uma dessas condições seja atingida.
$data = $cache->get($key);
```
Abaixo um sumário das dependências de cache disponíveis:

- [[yii\caching\ChainedDependency]]: a dependência muda caso alguma das dependencias na cadeia for alterada.
- [[yii\caching\DbDependency]]: a dependência muda caso o resultado da consulta especificada pela instrução SQL seja
  alterado.
- [[yii\caching\ExpressionDependency]]: a dependencia muda se o resultado da expressão PHP especificada for alterado.
- [[yii\caching\FileDependency]]: A dependencia muda se a data da última alteração do arquivo for alterada.
- [[yii\caching\TagDependency]]: associa um registro em cache com uma ou múltiplas tags. Você pode invalidar os
  registros em cache com a tag especificada ao chamar [[yii\caching\TagDependency::invalidate()]].


## Cache de Consulta <span id="query-caching"></span>

Cache de consulta é uma funcionalidade especial de cache construida com o cache de dados. Ela é fornecida para armazenar em cache consultas ao banco de dados.

O cache de consulta requere uma [[yii\db\Connection|DB connection]] e um [componente de aplicação](#cache-components) de `cache` válido.
A seguir um usu básico de cache de consulta, assumindo que `$bd` é uma instância de [[yii\db\Connection]]:

```php
$resultado = $bd->cache(function ($bd) {
  
    // O resultado da consulta SQL será entregue pelo cache
    // se o cache de consulta estiver sido habilitado e o resultado da consulta for encontrado em cache
    return $bd->createCommand('SELECT * FROM clientes WHERE id=1')->queryOne();

});
```

Cache de consulta pode ser usado pelo [DAO](db-dao.md) da mesma forma que um [ActiveRecord](db-active-record.md):

```php
$resultado = Cliente::getDb()->cache(function ($bd) {
    return Cliente::find()->where(['id' => 1])->one();
});
```

> Info: Alguns SGBDs (ex. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
  também suportam o cache de consulta no servidor. Você pode escolher usálo ao invés do mecanismo de cache 
  de consulta.
  O cache de consulta descrito acima tem a vantagem de que você pode especificar dependencias de cache flexíveis 
  e assim sendo potencialmente mais eficiente.


### Configurações <span id="query-caching-configs"></span>

Cache de consulta tem três opções configuraveis globalmente através de [[yii\db\Connection]]:

* [[yii\db\Connection::enableQueryCache|enableQueryCache]]: Configura se o cache de consulta está habilitado.
  O padrão é true. Note que para ter efetivamente o cache de consulta habilitado, você também deve ter um cache válido como especificado por [[yii\db\Connection::queryCache|queryCache]].
* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]]: representa o número de segundos que o resultado de uma  
  consulta pode se manter válido em cache. Você pode usar 0 para indicar que o resultado da consulta deve permancer no
  cache indefinidamente. Este é o valor padrão usado quando [[yii\db\Connection::cache()]] é chamado sem nenhuma
  especificação de duração.
* [[yii\db\Connection::queryCache|queryCache]]: representa a ID do componente de aplicação de cache.
  Seu padrão é `'cache'`. Cache de consulta é habilitado apenas se houver um componente de aplicacão de cache válido.


### Usando o Cache de Consulta <span id="query-caching-usages"></span>

Você pode usar [[yii\db\Connection::cache()]] se tiver múltiplas consultas SQL que precisam ser armazenadas no
cache de consulta. Use da seguinte maneira,

```php
$duracao = 60;     // armazenar os resultados em cache por 60 segundos
$dependencia = ...;  // alguma dependencia opcional

$result = $db->cache(function ($db) {

    // ... executar consultas SQL aqui ...

    return $result;

}, $duracao, $dependencia);
```

Qualquer consulta SQL na função anonima irá ser armazenada em cache pela duração especificada com a dependência informada. Se o resultado da consulta for encontrado em cache e for válido, a consulta não será necessária e o 
resultado será entregue pelo cache. Se você não especificar o parametro `$duracao`, o valor de 
[[yii\db\Connection::queryCacheDuration|queryCacheDuration]] será usado.

Ocasionalmente em `cache()`, você pode precisar desabilitar o cache de consulta para algumas consultas em particular. Você pode usar [[yii\db\Connection::noCache()]] neste caso.

```php
$result = $db->cache(function ($db) {

    // consultas SQL que usarão o cache de consulta

    $db->noCache(function ($db) {

        // consultas SQL que não usarão o cache de consulta

    });

    // ...

    return $result;
});
```

Se você apenas deseja usar o cache de consulta para apenas uma consulta, você pode chamar [[yii\db\Command::cache()]]
ao construir o comando. Por exemplo,

```php
// usar cache de consulta and definir duração do cache para 60 segundos
$customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->cache(60)->queryOne();
```

Você pode também usar [[yii\db\Command::noCache()]] para desabilitar o cache de consulta para um único comando, Por exemplo,

```php
$result = $db->cache(function ($db) {

    // consultas SQL que usam o cache de consulta

    // não usar cache de consulta para este comando
    $customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->noCache()->queryOne();

    // ...

    return $result;
});
```


### Limitações <span id="query-caching-limitations"></span>

O cache de consulta não funciona com resultados de consulta que contêm <i>manipuladores de recursos</i>(resource handlers). 
Por exemplo, ao usar o tipo de coluna `BLOB` em alguns SGBDs, o resultado da consulta irá retornar um <i>manipulador de recurso</i>(resource handler) para o registro na coluna.

Alguns armazenamentos em cache têm limitações de tamanho. Por exemplo, memcache limita o uso máximo de espaço de 1MB para cada registro. Então, se o tamanho do resultado de uma consulta exceder este limite, o cache irá falhar. 

