Roteamento e Criação de URL
===========================

Quando uma aplicação Yii começa a processar uma URL requerida, o primeiro passo 
necessário é obter a rota pela análise da URL. A rota é usada para instanciar o 
[controlador (controller) da ação](structure-controllers.md) correspondente para 
manipular a requisição. Todo este processo é chamado de *roteamento*.
 
O processo inverso do roteamento é chamada de *criação de URL*, onde é criado uma 
URL a partir de uma determinada rota e seus parâmetros. Quando a URL criada for 
exigida em outro momento, o processo de roteamento pode resolve-la de volta para 
a rota original e seus parâmetros.

O ponto central responsável pelo roteamento e pela criação de URL é o 
[[yii\web\UrlManager|gerenciador de URL]], na qual é registrado como o 
[componente da aplicação](structure-application-components.md) `urlManager`. O 
[[yii\web\UrlManager|gerenciador de URL]] fornece o método 
[[yii\web\UrlManager::parseRequest()|parseRequest()]] para analisar um requisição 
de entrada a fim de obter uma rota e seus parâmetros associados; e o método 
[[yii\web\UrlManager::createUrl()|createUrl()]] para criar uma URL a partir de 
uma rota e seus parâmetros associados.

Ao configurar o componente `urlManager` na configuração da aplicação, poderá 
fazer com que sua aplicação reconheça de forma arbitrária diversos formatos de 
URL sem modificar o código existente da aplicação. Por exemplo, você pode usar o 
código a seguir para criar uma URL a partir da ação `post/view`:

```php
use yii\helpers\Url;

// Url::to() chama UrlManager::createUrl() para criar uma URL
$url = Url::to(['post/view', 'id' => 100]);
```

Dependendo da configuração da propriedade `urlManager`, a URL criada pode ser 
parecida com um dos formatos a seguir (ou até mesmo com outro formato). E se a 
URL criada for requerida, ainda será analisada a fim de obter a rota e os valores 
dos parâmetros originais.

```
/index.php?r=post/view&id=100
/index.php/post/100
/posts/100
```


## Formatos de URL <span id="url-formats"></span>

O [[yii\web\UrlManager|gerenciador de URL]] suporta dois formatos de URL: o 
formato de URL padrão e o formato de URL amigável (pretty URL).

O formato de URL padrão usa um parâmetro chamado `r` para representar a rota e 
os demais parâmetros representam os parâmetros associados a rota. Por exemplo, a 
URL `/index.php?r=post/view&id=100` representa a rota `post/view` e o parâmetro 
`id` com o valor 100. O formato de URL padrão não exige qualquer tipo de 
configuração no [[yii\web\UrlManager|gerenciador de URL]] e trabalha em qualquer 
servidor Web.

O formato de URL amigável (pretty URL) usa um caminho adicional após o nome do 
script de entrada para representar a rota e seus parâmetros. Por exemplo, o 
caminho adicional na URL `/index.php/post/100` é `/post/100`, onde pode 
representar, em uma adequada [[yii\web\UrlManager::rules|regra de URL]], a rota 
`post/view` e o parâmetro `id` com o valor 100. Para usar o formato de URL 
amigável (pretty URL), você precisará escrever um conjunto de 
[[yii\web\UrlManager::rules|regras de URLs]] de acordo com a necessidade sobre 
como as URLs devem parecer.
 
Você pode alterar entre os dois formatos de URLs, alternando a propriedade 
[[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] do 
[[yii\web\UrlManager|gerenciador de URL]] sem alterar qualquer código na aplicação.


## Roteamento <span id="routing"></span>

O roteamento envolve duas etapas. Na primeira etapa, a requisição de entrada é 
transformada em uma rota e seus parâmetros. Na segunda etapa, a 
[ação do controller (controlador)](structure-controllers.md) correspondente a 
rota analisada será criada para processar a requisição.

Ao utilizar o formato de URL padrão, a análise de uma requisição para obter uma 
rota é tão simples como pegar o valor via `GET` do parâmetro `r`.

Ao utilizar o formato de URL amigável (pretty URL), o 
[[yii\web\UrlManager|gerenciado de URL]] examinará as 
[[yii\web\UrlManager::rules|regras de URLs]] registradas para encontrar alguma 
correspondência que poderá resolver a requisição em uma rota. Se tal regra não 
for encontrada, uma exceção [[yii\web\NotFoundHttpException]] será lançada.

Uma vez que a requisição analisada apresentar uma rota, é hora de criar a ação 
do controller (controlador) identificado pela rota.
A rota é dividida em várias partes pelo separador barra (“/”). Por exemplo, a 
rota `site/index` será dividida em duas partes: `site` e `index`. Cada parte é 
um ID que pode referenciar a um módulo, um controller (controlador) ou uma ação.
A partir da primeira parte da rota, a aplicação executará as seguintes etapas para 
criar o módulo (se existir), o controller (controlador) e a ação:

1. Define a aplicação como o módulo atual.
2. Verifica se o [[yii\base\Module::controllerMap|mapa do controller (controlador)]] 
   do módulo contém o ID atual. Caso exista, um objeto do controller (controlador) 
   será criado de acordo com a configuração do controller (controlador) encontrado 
   no mapa e a etapa 3 e 4 não serão executadas.   
3. Verifica se o ID referência a um módulo listado na propriedade 
   [[yii\base\Module::modules|modules]] do módulo atual. Caso exista, um módulo 
   será criado de acordo com as configurações encontradas na lista e a etapa 2 
   será executada como etapa seguinte do processo, no âmbito de usar o contexto 
   do módulo recém-criado.
4. Trata o ID como um ID do controller (controlador) e cria um objeto do controller 
   (controlador). Siga para a próxima etapa, como parte restante do processo.
5. O controller (controlador) procura o ID atual em seu 
   [[yii\base\Controller::actions()|mapa de ações]]. Caso exista, será criado uma 
   ação de acordo com a configuração encontrada no mapa. Caso contrário, o 
   controller (controlador) tentará criar uma ação inline que é definida por um 
   método da ação correspondente ao ID atual.

Nas etapas acima, se ocorrer qualquer erro, uma exceção [[yii\web\NotFoundHttpException]] 
será lançada, indicando a falha no processo de roteamento.


### Rota Padrão <span id="default-route"></span>

Quando uma requisição analisada apresentar uma rota vazia, a assim chamada 
*rota padrão* será usada em seu lugar. A rota padrão é `site/index` é utilizada 
como padrão, que referencia a ação `index` do controller (controlador) `site`.
Você pode personalizar esta configuração pela propriedade 
[[yii\web\Application::defaultRoute|defaultRoute]] na configuração da aplicação 
como mostrado a seguir:

```php
[
    // ...
    'defaultRoute' => 'main/index',
];
```


### Rota `catchAll` <span id="catchall-route"></span>

Às vezes você pode querer colocar sua aplicação Web em modo de manutenção 
temporariamente e exibir uma mesma página com informações para todas as requisições. 
Existem muitas maneiras de atingir este objetivo. Mas uma das maneiras mais simples 
é apenas configurar a propriedade [[yii\web\Application::catchAll]] na configuração 
da aplicação como mostrado a seguir:

```php
[
    // ...
    'catchAll' => ['site/offline'],
];
```

Na configuração acima, a ação `site/offline` será utilizado para lidar com todas 
as requisições recebidas.

A propriedade `catchAll` deve receber um array, o primeiro elemento especifica a 
rota e o restante dos elementos (pares de nomes e seus valores) especificam os 
parâmetros a serem [associados a ação](structure-controllers.md#action-parameters).


## Criando URLs <span id="creating-urls"></span>

O Yii fornece um método de ajuda [[yii\helpers\Url::to()]] para criar vários tipos 
de URLs a partir de determinadas rotas e seus parâmetros. Por exemplo,

```php
use yii\helpers\Url;

// cria uma URL para uma rota: /index.php?r=post/index
echo Url::to(['post/index']);

// cria uma URL para uma rota com parâmetros: /index.php?r=post/view&id=100
echo Url::to(['post/view', 'id' => 100]);

// cria uma URL ancorada: /index.php?r=post/view&id=100#content
echo Url::to(['post/view', 'id' => 100, '#' => 'content']);

// cria uma URL absoluta: http://www.example.com/index.php?r=post/index
echo Url::to(['post/index'], true);

// cria uma URL absoluta usando https: https://www.example.com/index.php?r=post/index
echo Url::to(['post/index'], 'https');
```

Observe que nos exemplos acima, assumimos o formato de URL padrão. Se o formato 
de URL amigável (pretty URL) estiver habilitado, as URLs criadas serão diferentes 
de acordo com as [[yii\web\UrlManager::rules|regra de URL]] em uso. 

A rota passada para o método [[yii\helpers\Url::to()]] é sensível ao contexto. 
Ele pode ser tanto uma rota *relativa* quanto uma rota *absoluta* que será 
normalizada de acordo com as regras a seguir:

- Se a rota for uma string vazia, a requisição atual [[yii\web\Controller::route|route]] será usada;
- Se a rota não contivér barras (“/”), ele será considerado um ID da ação do 
  controller (controlador) atual e será antecedido pelo valor da propriedade 
  [[\yii\web\Controller::uniqueId|uniqueId]] do controller (controlador) atual;
- Se a rota não contivér uma barra (“/”) inicial, será considerado como uma rota
  relativa ao módulo atual e será antecedido pelo valor da propriedade 
  [[\yii\base\Module::uniqueId|uniqueId]] do módulo atual.

A partir da versão 2.0.2, você pode especificar uma rota usando 
[alias](concept-aliases.md). Se for este o caso, a alias será a primeira a ser 
convertida em uma rota real e em seguida será transformada em uma rota absoluta 
de acordo com as regras informadas anteriormente.

Por exemplo, assumindo que o módulo atual é `admin` e o controller (controlador) 
atual é `post`,

```php
use yii\helpers\Url;

// rota atual requerida: /index.php?r=admin/post/index
echo Url::to(['']);

// uma rota relativa com apenas o ID da ação: /index.php?r=admin/post/index
echo Url::to(['index']);

// uma rota relativa: /index.php?r=admin/post/index
echo Url::to(['post/index']);

// uma rota absoluta: /index.php?r=post/index
echo Url::to(['/post/index']);

// /index.php?r=post/index     assumindo que a alias "@posts" foi definida como "/post/index"
echo Url::to(['@posts']);
```

O método [[yii\helpers\Url::to()]] é implementado através das chamadas dos métodos 
[[yii\web\UrlManager::createUrl()|createUrl()]] e 
[[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]] do 
[[yii\web\UrlManager|gerenciador de URL]].
Nas próximas subseções, iremos explicar como configurar o 
[[yii\web\UrlManager|gerenciador de URL]] para personalizar os formatos das URLs criadas.

O método [[yii\helpers\Url::to()]] também suporta a criação de URLs NÃO relacionadas 
a uma rota em particular. Neste caso, ao invés de passar um array como seu primeiro 
parâmetro, você pode passar uma string. Por exemplo,
 
```php
use yii\helpers\Url;

// rota atual requerida: /index.php?r=admin/post/index
echo Url::to();

// uma alias da URL: http://example.com
Yii::setAlias('@example', 'http://example.com/');
echo Url::to('@example');

// uma URL absoluta: http://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);
```

Além do método `to()`, a classe auxiliar [[yii\helpers\Url]] também fornece uma 
série de métodos referentes a criação de URLs. Por exemplo,

```php
use yii\helpers\Url;

// URL da página inicial: /index.php?r=site/index
echo Url::home();

// URL base, útil se a aplicação for implementada em uma subpasta da pasta raiz do servidor Web
echo Url::base();

// A URL canônica da requisição atual
// Veja mais detalhes em https://en.wikipedia.org/wiki/Canonical_link_element
echo Url::canonical();

// Obtêm a URL da requisição anterior para reutilizá-la em requisições futuras
Url::remember();
echo Url::previous();
``` 


## Usando URLs Amigáveis (Pretty URLs) <span id="using-pretty-urls"></span>

Para utilizar as URLs amigáveis (pretty URLs), configure o componente `urlManager` 
na configuração da aplicação conforme o exemplo a seguir:

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

A propriedade [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] é obrigatória 
para habilitar o formato de URL amigável (pretty URL).
O restante das propriedades são opcionais. No entanto, a configuração utilizada 
acima é a mais utilizada.

* [[yii\web\UrlManager::showScriptName|showScriptName]]: esta propriedade determina
  se o script de entrada deve ser incluído ou não nas URLs criadas. Por exemplo, 
  ao invés de criar uma URL `/index.php/post/100`, definindo esta propriedade 
  como `false`, a URL `/post/100` será gerada.
* [[yii\web\UrlManager::enableStrictParsing|enableStrictParsing]]: esta propriedade 
  habilita uma análise rigorosa (strict parsing) da requisição. Caso a análise 
  rigorosa estiver habilitada, a URL da requisição deve corresponder pelo menos 
  a uma das regras definidas pela propriedade [[yii\web\UrlManager::rules|rules]] 
  a fim de ser tratado como uma requisição válida, caso contrário a exceção 
  [[yii\web\NotFoundHttpException]] será lançada. Caso a análise rigorosa estiver 
  desabilitada, as regras definidas pela propriedade 
  [[yii\web\UrlManager::rules|rules]] NÃO serão verificadas e as informações 
  obtidas pela URL serão tratadas como a rota da requisição.
* [[yii\web\UrlManager::rules|rules]]: esta propriedade contém uma lista de 
  regras especificando como serão analisadas e criadas as URLs. Esta é a principal 
  propriedade que você deve trabalhar para criar URLs cujos formatos satisfaçam 
  a sua exigência em particular.

> Observação: A fim de esconder o nome do script de entrada nas URLs criadas, 
  além de definir a propriedade [[yii\web\UrlManager::showScriptName|showScriptName]] 
  como `false`, você também pode precisar configurar o seu servidor Web para 
  identificar corretamente o script PHP quando uma URL da requisição não 
  especificar um explicitamente. Caso você estejam utilizando um servidor Web 
  com Apache, você pode consultar a configuração recomendada, conforme descrito 
  na seção [Installation](start-installation.md#recommended-apache-configuration).


### Regras de URLs <span id="url-rules"></span>

Uma regra de URL é uma instância de [[yii\web\UrlRule]] ou de classes que as estendam. 
Cada regra de URL consiste de um padrão usado para combinar as partes do caminho 
das URLs, como uma rota e alguns parâmetros. Uma regra de URL pode ser usado para 
analisar uma URL da requisição somente se o padrão corresponder com a esta URL. 
Uma regra de URL pode ser usada para criar uma URL para corresponder a uma 
determinada rota e seus parâmetros.

Quando uma formato de URL amigável (pretty URL) estiver habilitada, o 
[[yii\web\UrlManager|gerenciador de URL]] utilizará as regras de URLs declaradas 
na propriedade [[yii\web\UrlManager::rules|rules]] para analisar as requisições 
e para criar URLs. Em particular, para analisar uma requisição, o 
[[yii\web\UrlManager|gerenciador de URL]] verificará as regras na ordem em que 
foram declaradas e só enxergará a *primeira* regra que corresponda a URL da 
requisição. A regra que foi correspondida é então utilizada para obter a rota e 
seus parâmetros a partir de sua análise. A mesma coisa acontece na criação de
URLs, o [[yii\web\UrlManager|gerenciador de URL]] enxergará apenas a primeira 
regra que corresponda a rota e seus parâmetros para serem utilizados na criação 
de uma URL.

Você pode configurar a propriedade [[yii\web\UrlManager::rules]] com um array 
composto de pares de chave-valor, onde a chave é o padrão da regra e o valor 
serão as rotas. Cada par de padrão-rota define uma regra de URL. Por exemplo, 
as [[yii\web\UrlManager::rules|regras]] a seguir configuram duas regras de URL.
A primeira regra corresponde a uma URL chamada `posts` sendo mapeado para utilizar 
a rota `post/index`.
A segunda regra corresponde a uma URL que combine com expressão regular 
`post/(\d+)` seguido de um parâmetro chamado `id` sendo mapeado para utilizar a
rota `post/view`.

```php
[
    'posts' => 'post/index', 
    'post/<id:\d+>' => 'post/view',
]
```

> Informação: O padrão em uma regra é usado para identificar o caminho de uma URL. 
  Por exemplo, o caminho da URL `/index.php/post/100?source=ad` é `post/100` 
  (as barras (“/”) iniciais e finais serão ignoradas) combinando com o padrão `post/(\d+)`.

Além de declarar regras de URL como pares de padrão-rota, você também pode declarar 
como array. Cada array é utilizado para configurar um único objeto da regra de URL. 
Isto se faz necessário quando você deseja configurar outras propriedades de uma 
regra de URL. Por exemplo,

```php
[
    // ...outras regras de URL...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

Por padrão, se você não especificar a opção `class` na configuração de uma regra, 
será utilizado a classe [[yii\web\UrlRule]].


### Parâmetros Nomeados <span id="named-parameters"></span>

Uma regra de URL pode ser associado a alguns parâmetros nomeados que são 
especificados no padrão `<ParamName:RegExp>`, onde o `ParamName` especifica o 
nome do parâmetro e o `RegExp` especifica uma expressão regular opcional usada 
para corresponder ao valor do parâmetro. Se o `RegExp` são for especificado, 
significará que o valor do parâmetro será uma string sem barras (“/”).

> Observação: Você apenas pode especificar expressões regulares para os parâmetros. 
  As demais partes de um padrão serão considerados como um texto simples.

Quando esta regra for utilizada para analisar uma URL, os parâmetros associados 
serão preenchidos com os valores que foram correspondidos pela regra e estes 
parâmetros serão disponibilizados logo a seguir no `$_GET` pelo componente da 
aplicação `request`.

Vamos utilizar alguns exemplos para demonstrar como os parâmetros nomeados 
funcionam. Supondo que declaramos as três regras a seguir:

```php
[
    'posts/<year:\d{4}>/<category>' => 'post/index',
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
]
```

Quando as regras forem utilizadas para analisar as URLs:

- `/index.php/posts` obterá a rota `post/index` usando a segunda regra;
- `/index.php/posts/2014/php` obterá a rota `post/index`, o parâmetro `year` 
  cujo o valor é 2014 e o parâmetro `category` cujo valor é `php` usando a primeira regra;
- `/index.php/post/100` obterá a rota `post/view` e o parâmetro `id` cujo valor  
  é 100 usando a terceira regra;
- `/index.php/posts/php` causará uma exceção [[yii\web\NotFoundHttpException]] 
  quando a propriedade [[yii\web\UrlManager::enableStrictParsing]] for `true`, 
  por não ter correspondido a nenhum dos padrões. Se a propriedade 
  [[yii\web\UrlManager::enableStrictParsing]] for `false` (o valor padrão), o 
  caminho `posts/php` será retornado como uma rota.
 
E quando as regras fores utilizadas para criar as URLs:

- `Url::to(['post/index'])` cria `/index.php/posts` usando a segunda regra;
- `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` cria `/index.php/posts/2014/php` usando a primeira regra;
- `Url::to(['post/view', 'id' => 100])` cria `/index.php/post/100` usando a terceira regra;
- `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` cria `/index.php/post/100?source=ad` usando a terceira regra.
  Pela razão do parâmetro `source` não foi especificado na regra, ele será acrescentado como uma query string na criação da URL.
- `Url::to(['post/index', 'category' => 'php'])` cria `/index.php/post/index?category=php` usando nenhuma das regras.
  Observe que, se nenhuma das regras forem aplicadas, a URL será criada simplesmente 
  como a rota sendo o caminho e todos os parâmetros como query string.


### Parametrizando Rotas <span id="parameterizing-routes"></span>

Você pode incorporar nomes de parâmetros na rota de uma regra de URL. Isto permite 
que uma regra de URL seja utilizada para combinar diversas rotas. Por exemplo, a 
regra a seguir incorpora os parâmetros `controller` e `action` nas rotas.

```php
[
    '<controller:(post|comment)>/<id:\d+>/<action:(create|update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/view',
    '<controller:(post|comment)>s' => '<controller>/index',
]
```

Para analisar uma URL `/index.php/comment/100/create`, a primeira regra será 
aplicada, na qual foi definida o parâmetro `controller` para ser `comment` e o 
parâmetro `action` para ser `create`. Sendo assim, a rota `<controller>/<action>` 
é resolvida como `comment/create`.
 
De forma similar, para criar uma URL com a rota `comment/index`, a terceira regra 
será aplicada, criando um URL `/index.php/comments`.

> Informação: Pela parametrização de rotas, é possível reduzir significativamente 
  o número de regras de URL, que também pode melhorar o desempenho do 
  [[yii\web\UrlManager|gerenciador de URL]]. 
  
Por padrão, todos os parâmetros declarados nas regras são obrigatórios. Se uma
URL da requisição não contiver um dos parâmetros em particular, ou se a URL está 
sendo criado sem um dos parâmetros em particular, a regra não será aplicada. Para 
fazer com que algum parâmetro em particular seja opcional, você pode configurar 
a propriedade [[yii\web\UrlRule::defaults|defaults]] da regra. Os parâmetros
listados nesta propriedade são opcionais e serão utilizados quando os mesmos não 
forem fornecidos.

A declaração da regra a seguir, ambos os parâmetros `page` e `tag` são opcionais 
e utilizarão o valor 1 e a string vazia, respectivamente, quando não forem fornecidos.

```php
[
    // ...outras regras...
    [
        'pattern' => 'posts/<page:\d+>/<tag>',
        'route' => 'post/index',
        'defaults' => ['page' => 1, 'tag' => ''],
    ],
]
```

A regra anterior pode ser usado para analisar ou criar qualquer uma das seguintes URLs:

* `/index.php/posts`: `page` é 1, `tag` é ''.
* `/index.php/posts/2`: `page` é 2, `tag` is ''.
* `/index.php/posts/2/news`: `page` é 2, `tag` é `'news'`.
* `/index.php/posts/news`: `page` é 1, `tag` é `'news'`.

Sem o uso dos parâmetros opcionais, você deveria criar 4 regras para alcançar o 
mesmo resultado.


### Regras com Domínios <span id="rules-with-server-names"></span>

É possível incluir domínios nos padrões das regras de URL. Isto é útil quando sua
aplicação se comporta de forma diferente em diferentes domínios. Por exemplo, a 
regra a seguir obtém a rota `admin/user/login` pela análise da URL 
`http://admin.example.com/login` e a rota `site/login` pela análise da URL 
`http://www.example.com/login`.

```php
[
    'http://admin.example.com/login' => 'admin/user/login',
    'http://www.example.com/login' => 'site/login',
]
```

Você também pode incorporar parâmetros nos domínios para extrair informações 
dinamicamente a partir deles. Por exemplo, a regra a seguir obtém a rota 
`post/index` e o parâmetro `language=en` pela análise da URL `http://en.example.com/posts`

```php
[
    'http://<language:\w+>.example.com/posts' => 'post/index',
]
```

> Observação: Regras com domínios NÃO devem ser incluídos com subpastas do script 
  de entrada em seus padrões. Por exemplo, se a aplicação estiver sob 
  `http://www.example.com/sandbox/blog`, você deve usar o padrão 
  `http://www.example.com/posts` ao invés de `http://www.example.com/sandbox/blog/posts`. 
  Isto permite que sua aplicação seja implantado sob qualquer diretório sem a 
  necessidade de alterar o código da aplicação.


### Sufixos da URL <span id="url-suffixes"></span>

Você pode querer adicionar sufixos nas URLs para diversos fins. Por exemplo, 
você pode adicionar o `.html` nas URLs para que se pareçam com páginas estáticas. 
Você também pode adicionar o `.json` nas URLs para indicar o tipo de conteúdo 
esperado na resposta. Você pode alcançar este objetivo configurando a propriedade 
[[yii\web\UrlManager::suffix]] na configuração da aplicação conforme o exemplo a seguir:

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

A configuração anterior permitirá que o [[yii\web\UrlManager|gerenciador de URL]] 
reconheçam as URLs solicitadas e que também criem URLs com o prefixo `.html`.

> Dica: Você pode definir `/` como o sufixo para que todas as URLs terminem com barra.

> Observação: Ao configurar um sufixo da URL e a URL da requisição não conter um, 
  será considerado como uma URL não válida. Isto é uma prática recomendada no 
  SEO (otimização para mecanismos de pesquisa, do *inglês search engine optimization*).

Ás vezes você poder querer utilizar diferentes sufixos para diferentes URLs.
Isto pode ser alcançado pela configuração da propriedade 
[[yii\web\UrlRule::suffix|suffix]] individualmente para cada regra de URL. 
Quando uma regra de URL possuir esta propriedade definida, sobrescreverá o 
sufixo que foi definido da camada do [[yii\web\UrlManager|gerenciador de URL]]. 
Por exemplo, a configuração a seguir contém uma regra de URL personalizada que 
usa o `.json` como sufixo ao invés do sufixo `.html` definido globalmente.

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.json',
                ],
            ],
        ],
    ],
]
```


### Métodos HTTP <span id="http-methods"></span>

Ao implementar RESTful API, é necessário que sejam obtidas rotas diferentes pela 
análise de uma mesma URL de acordo com o método HTTP utilizado. Isto pode ser 
alcançado facilmente adicionando o prefixo do método HTTP suportado, separando 
os nomes dos métodos por vírgulas. Por exemplo, a regra a seguir possui o mesmo 
padrão `post/<id:\d+>` com suporte a diferentes métodos HTTP. A análise de uma
requisição `PUT post/100` obterá a rota `post/create`, enquanto a requisição 
`GET post/100` obterá a rota `post/view`.

```php
[
    'PUT,POST post/<id:\d+>' => 'post/create',
    'DELETE post/<id:\d+>' => 'post/delete',
    'post/<id:\d+>' => 'post/view',
]
```

> Observação: Se uma regra de URL contiver método(s) HTTP, esta regra só será 
utilizada para análises de URLs. A regra será ignorada quando o 
[[yii\web\UrlManager|gerenciador de URL]] for chamado para criar URLs.

> Dica: Para simplificar o roteamento do RESTful APIs, o Yii fornece uma classe 
especial [[yii\rest\UrlRule]] de regras que é muito diferente. Esta classe 
suporta muitos recursos como a pluralização automática de IDs do controller 
(controlador). Para mais detalhes, por favor, consulte a seção 
[Routing](rest-routing.md) sobre o desenvolvimento de RESTful APIs.


### Regras Personalizadas <span id="customizing-rules"></span>

Nos exemplo anteriores, as regras de URL são declaradas principalmente no formato
de pares de padrão-rota. Este é um formato de atalho bastante utilizado. Em 
alguns cenários, você pode querer personalizar uma regra de URL configurando 
outras propriedades, tais como o [[yii\web\UrlRule::suffix]]. Isto pode ser 
feito utilizando um array de configuração para especificar uma regra. O exemplo 
a seguir foi retirado da subseção [Sufixos da URL](#url-suffixes),

```php
[
    // ...outras regras de URL...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

> Informações: Por padrão, se você não especificar a opção `class` na configuração 
  de uma regra, será usado como padrão a classe [[yii\web\UrlRule]].
  

### Adicionando Regras Dinamicamente <span id="adding-rules"></span>

As regras de URL podem ser adicionadas dinamicamente ao [[yii\web\UrlManager|gerenciador de URL]]. 
Esta técnica muitas vezes se faz necessária em [módulos](structure-modules.md) que 
são redistribuídos e que desejam gerenciar as suas próprias regras de URL. Para 
que estas regras sejam adicionadas dinamicamente e terem efeito durante o processo 
de roteamento, você pode adiciona-los durante a [inicialização (bootstrapping)](runtime-bootstrapping.md). 
Para os módulos, significa que deve implementar a interface [[yii\base\BootstrapInterface]] 
e adicionar as regras no método [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] 
conforme o exemplo a seguir:

```php
public function bootstrap($app)
{
    $app->getUrlManager()->addRules([
        // declare as regras aqui
    ], false);
}
```

Observe que você também deve listar estes módulos no [[yii\web\Application::bootstrap]]
para que eles sejam usados no processo de [inicialização (bootstrapping)](runtime-bootstrapping.md)


### Criando Classes de Regras <span id="creating-rules"></span>

Apesar do fato que a classe padrão [[yii\web\UrlRule]] é flexível o suficiente 
para a maior parte dos projetos, há situações em que você terá que criar a sua 
própria classe de regra. Por exemplo, em um site de venda de carros, você pode 
querer dar suporte a um formato de URL como `/Manufacturer/Model`, que tanto o 
`Manufacturer` quanto o `Model` devem coincidir com os dados armazenados em uma 
tabela do banco de dados. A classe de regra padrão não vai funcionar nesta 
situação pois vão se basear em padrões estaticamente declarados.

Podemos criar uma classe de regra de URL para resolver este formato.

```php
namespace app\components;

use yii\web\UrlRuleInterface;
use yii\base\Object;

class CarUrlRule extends Object implements UrlRuleInterface
{

    public function createUrl($manager, $route, $params)
    {
        if ($route === 'car/index') {
            if (isset($params['manufacturer'], $params['model'])) {
                return $params['manufacturer'] . '/' . $params['model'];
            } elseif (isset($params['manufacturer'])) {
                return $params['manufacturer'];
            }
        }
        return false;  // esta regra não se aplica
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            // checa o $matches[1] e $matches[3] para verificar
            // se coincidem com um *fabricante* e um *modelo* no banco de dados.
            // Caso coincida, define o $params['manufacturer'] e/ou $params['model']
            // e retorna ['car/index', $params]
        }
        return false;  // esta regra não se aplica
    }
}
```

E utilize esta nova classe de regra na configuração [[yii\web\UrlManager::rules]]:

```php
[
    // ...outras regras...
    
    [
        'class' => 'app\components\CarUrlRule', 
        // ...configurar outras propriedades...
    ],
]
```


## Considerando Performance <span id="performance-consideration"></span>

Ao desenvolver uma aplicação Web complexa, é importante otimizar as regras de URL 
para que leve menos tempo na análise de requisições e criação de URLs.

Utilizando rotas parametrizadas, você reduz o número de regras de URL, na qual 
pode melhorar significativamente o desempenho.

Na análise e criação de URLs, o [[yii\web\UrlManager|gerenciador de URL]] examina 
as regras de URL na ordem em que foram declaradas.
Portanto, você pode considerar ajustar a ordem destas regras, fazendo com que as
regras mais específicas e/ou mais comuns sejam colocadas antes que os menos.

Se algumas regras de URL compartilharem o mesmo prefixo em seus padrões ou rotas, 
você pode considerar utilizar o [[yii\web\GroupUrlRule]] para que sejam examinados 
de forma mais eficiente pelo [[yii\web\UrlManager|gerenciador de URL]] como um 
grupo. Normalmente é o caso de aplicações compostos por módulos, onde cada módulo
possui o seu próprio conjunto de regras de URL utilizando o ID do módulo como 
prefixo comum.
