Cache HTTP
============

Além do cache no servidor que nós descrevemos nas seções anteriores, aplicações web pode também aproveitar-se
de cache no cliente para economizar o tempo para gerar e transmitir o mesmo conteúdo de uma página.

Para usar o cache no cliente, vocế poderá configurar [[yii\filters\HttpCache] como um filtro para actions de um 
controller ao qual o resultado de sua renderização possa ser armazenado em cache no navegador do cliente. [[yii\filters\HttpCache|HttpCache]] funciona apenas para requisições `GET` e `HEAD`. Ele pode manipular três tipos de cache relacionados a cabeçalhos HTTP para estas requisições:

* [[yii\filters\HttpCache::lastModified|Last-Modified]]
* [[yii\filters\HttpCache::etagSeed|Etag]]
* [[yii\filters\HttpCache::cacheControlHeader|Cache-Control]]


## Cabeçalho de `Last-modified` <span id="last-modified"></span>

O cabeçalho `Last-modified` usa uma data(timestamp) para indicar se a página foi modificada desde que o cliente a armazenou em cache.

Você pode configurar a propriedade [[yii\filters\HttpCache::lastModified]] para permitir enviar o cabeçalho de `Last-modified`. A propriedade deve ser um <i>callable</i> PHP retornando uma data(timestamp) UNIX sobre o tempo de modificação. A declaração do <i>callable</i> PHP deve ser a seguinte,

```php
/**
 * @param Action $action O Objeto da action que está sendo manipulada no momento
 * @param array $params o valor da propriedade "params"
 * @return integer uma data(timestamp) UNIX timestamp representando o tempo da 
 * última modificação na página
 */
function ($action, $params)
```

A seguir um exemplo que faz o uso do cabeçalho `Last-modified`:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('post')->max('updated_at');
            },
        ],
    ];
}
```
O código acima afirma que o cache HTTP deve ser habilitado apenas para a action `index`. Este deve
gerar um cabeçalho HTTP `last-modified` baseado na última data de alteração dos posts. Quando um
navegador visitar a página `index` pela primeira vez, a página irá ser gerada no servidor e enviada para 
o navegador; Se o navegador visitar a mesma página novamente e não houver modificação dos posts durante este
período, o servidor não irá re-gerar a página, e o navegador irá usar a versão em cache no cliente.
Como um resultado a renderização do conteúdo na página não será executada no servidor.


## Cabeçalho `ETag` <span id="etag"></span>

O cabeçalho <i>"Entity Tag"</i> (ou `ETag` abreviado) usa um hash para representar o conteúdo de uma página.
Se a página for alterada, o hash irá mudar também. Ao comparar o hash mantido no cliente com o hash gerado no
servidor, o cache pode determinar se a página foi alterada e se deve ser re-transmitida.

Você pode configurar a propriedade [[yii\filters\HttpCache::etagSeed]] para habilitar o envio do cabeçalho `ETag`.
A propriedade deve ser um <i>callable</i> PHP retornando a <i>semente(seed)</i> para a geração do hash do Etag. A declaração do <i>callable</i> PHP deve ser como a seguinte,

```php
/**
 * @param Action $action o objeto da action que está sendo manipulada no momento
 * @param array $params o valor da propriedade "params"
 * @return string uma string usada como a semente(seed) para gerar um hash ETag
 */
function ($action, $params)
```

A Seguir um exemplo que faz o uso do cabeçalho `ETag`:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['view'],
            'etagSeed' => function ($action, $params) {
                $post = $this->findModel(\Yii::$app->request->get('id'));
                return serialize([$post->title, $post->content]);
            },
        ],
    ];
}
```

O código acima afirma que o cache de HTTP deve ser habilitado apenas para a action `view`. Este deve
gerar um cabeçalho HTTP `ETag` baseado no titulo e conteúdo do post requisitado. Quando um navegador visitar
a página `view` pela primeira vez, a página será gerada no servidor e enviada para ele; Se o navegador visitar 
a mesma página novamente e não houver alteração para o título e o conteúdo do post, o servidor não irá ré-gerar 
a página, e o navegador irá usar a versão que estiver no cache do cliente. Como um resultado a renderização do 
conteúdo na página não será executada no servidor.

ETags permite estratégias mais complexas e/ou mais precisas do que o uso do cabeçalho de `Last-modified`.
Por exemplo, um ETag pode ser invalidado se o site tiver sido alterado para um novo tema.

Gerações muito complexas de ETags podem contrariar o propósito de se usar `HttpCache` e introduzir despesas desnecessárias ao processamento, já que eles precisam ser re-avaliados a cada requisição. 
Tente encontrar uma expressão simples que invalida o cache se o conteúdo da página for modificado.

> Observação: Em complacência com a [RFC 7232](http://tools.ietf.org/html/rfc7232#section-2.4),
  `HttpCache` irá enviar os cabeçalhos `ETag` e `Last-Modified` se ambos forem assim configurados.
  E se o cliente envia ambos o cabeçalhos `If-None-Match` e `If-Modified-Since`, apenas o primeiro será 
  respeitado.


## Cabeçalho `Cache-Control` <span id="cache-control"></span>

O cabeçalho `Cache-Control` especifica politicas de cache gerais para as páginas. Você pode enviá-lo configurando a propriedade [[yii\filters\HttpCache::cacheControlHeader]] com o valor do cabeçalho. Por padrão, o seguinte cabeçalho será enviado:

```
Cache-Control: public, max-age=3600
```

## Limitador de Cache na Sessão <span id="session-cache-limiter"></span>

Quando uma página usa sessão, o PHP irá automaticamente enviar alguns cabeçalhos HTTP relacionados ao cache
como especificado na configuração do PHP INI `session.cache_limiter`. Estes cabeçalhos podem inteferirir ou 
desabilitar o cache que você deseja do `HttpCache`. Para previnir-se deste problema, por padrão `HttpCache`
irá desabilitar o envio destes cabeçalhos automaticamente. Se você quiser modificar estes comportamente, deve
configurara a propriedade [[yii\filters\HttpCache::sessionCacheLimiter]]. A propriedade pode receber um valor em uma string, incluindo `public`, `private`, `private_no_expire`, e `nocache`. Por favor referir-se ao manual do 
PHP sobre [session_cache_limiter()](http://www.php.net/manual/en/function.session-cache-limiter.php)
para explicações sobre estes valores.


## Implicações para SEO <span id="seo-implications"></span>

Os bots do motor de buscas tendem a respeitar cabeçalhos de cache. Ja que alguns rastreadores têm um limite sobre a quantidade de páginas por dominio que eles processam em um certo espaço de tempo, introduzir cabeçalhos de cache podem 
ajudar na indexação do seu site já que eles reduzem o número de páginas que precisam ser processadas.

