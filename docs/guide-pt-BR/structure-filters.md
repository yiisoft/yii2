Filtros
=======

Os filtros são objetos que são executados antes e/ou depois das 
[ações do controller (controlador)](structure-controllers.md#actions). Por exemplo, 
um filtro de controle de acesso pode ser executado antes das ações para garantir 
que um determinado usuário final tenha autorização de acessá-lo; um filtro de 
compressão de conteúdo pode ser executado depois das ações para comprimir o 
conteúdo da resposta antes de enviá-los aos usuários finais.

Um filtro pode ser composto por um pré-filtro (lógicas de filtragem que são 
aplicadas *antes* que as ações) e/ou um pós-filtro (lógica aplicada *depois* 
que as ações).


## Usando os Filtros <span id="using-filters"></span>

Os filtros são, essencialmente, um tipo especial de 
[behaviors (comportamento)](concept-behaviors.md). No entanto, o uso dos filtros 
é igual ao [uso dos behaviors](concept-behaviors.md#attaching-behaviors). Você 
pode declarar os filtros em uma classe controller (controlador) sobrescrevendo o 
método [[yii\base\Controller::behaviors()|behaviors()]] conforme o exemplo a seguir:

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index', 'view'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

Por padrão, os filtros declarados em uma classe controller (controlador) serão 
aplicados em *todas* as ações deste controller (controlador). Você pode, no 
entanto, especificar explicitamente em quais ações os filtros serão aplicados 
pela configuração da propriedade [[yii\base\ActionFilter::only|only]]. No exemplo 
anterior, o filtro `HttpCache` só se aplica às ações `index` e `view`. Você também 
pode configurar a propriedade [[yii\base\ActionFilter::except|except]] para montar 
um blacklist, a fim de  barrar algumas ações que estão sendo filtradas.

Além dos controllers (controladores), você também poderá declarar filtros em um 
[módulo](structure-modules.md) ou na [aplicação](structure-applications.md).
Quando você faz isso, os filtros serão aplicados em *todos* as ações do controller 
(controlador) que pertençam a esse módulo ou a essa aplicação, a menos que você 
configure as propriedades [[yii\base\ActionFilter::only|only]] e 
[[yii\base\ActionFilter::except|except]] do filtro conforme descrito anteriormente.

> Observação: Ao declarar os filtros em módulos ou em aplicações, você deve usar 
[rotas](structure-controllers.md#routes) ao invés de IDs das ações nas propriedades 
[[yii\base\ActionFilter::only|only]] e [[yii\base\ActionFilter::except|except]]. 
Isto porque os IDs das ações não podem, por si só, especificar totalmente as ações 
no escopo de um módulo ou de uma aplicação.

Quando muitos filtros são configurados para uma única ação, devem ser aplicados 
de acordo com as seguintes regras:

* Pré-filtragem:
    - Aplica os filtros declarados na aplicação na ordem que foram listados no método `behaviors()`.
    - Aplica os filtros declarados no módulo na ordem que foram listados no método `behaviors()`.
    - Aplica os filtros declarados no controller (controlador) na ordem que foram listados no método `behaviors()`.
    - Se qualquer um dos filtros cancelarem a execução da ação, os filtros (tanto os pré-filtros quanto os pós-filtros) subsequentes não serão aplicados.
* Executa a ação se passar pela pré-filtragem.
* Pós-filtragem
    - Aplica os filtros declarados no controller (controlador) na ordem inversa ao que foram listados no método `behaviors()`.
    - Aplica os filtros declarados nos módulos na ordem inversa ao que foram listados no método `behaviors()`.
    - Aplica os filtros declarados na aplicação na ordem inversa ao que foram listados no método `behaviors()`.


## Criando Filtros <span id="creating-filters"></span>

Para criar um novo filtro de ação, deve estender a classe [[yii\base\ActionFilter]] 
e sobrescrever os métodos [[yii\base\ActionFilter::beforeAction()|beforeAction()]] 
e/ou [[yii\base\ActionFilter::afterAction()|afterAction()]]. O primeiro método 
será executado antes que uma ação seja executada enquanto o outro método será 
executado após uma ação seja executada. 
O valor de retorno no método [[yii\base\ActionFilter::beforeAction()|beforeAction()]] 
determina se uma ação deve ser executada ou não. Se retornar `false`, os filtros 
subsequentes serão ignorados e a ação não será executada.

O exemplo a seguir mostra um filtro que guarda o log do tempo de execução das ações:

```php
namespace app\components;

use Yii;
use yii\base\ActionFilter;

class ActionTimeFilter extends ActionFilter
{
    private $_startTime;

    public function beforeAction($action)
    {
        $this->_startTime = microtime(true);
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        $time = microtime(true) - $this->_startTime;
        Yii::trace("Action '{$action->uniqueId}' spent $time second.");
        return parent::afterAction($action, $result);
    }
}
```


## Filtros Nativos <span id="core-filters"></span>

O Yii fornece um conjunto de filtros que normalmente são usados, localizados sob 
o namespace `yii\filters`. A seguir, iremos realizar uma breve apresentação 
destes filtros.


### [[yii\filters\AccessControl|AccessControl]] <span id="access-control"></span>

O filtro AccessControl fornece um controle de acesso simples, baseado em um 
conjunto de [[yii\filters\AccessControl::rules|regras]].
Em particular, antes que uma ação seja executada, o AccessControl analisará as 
regras listadas e localizará o primeiro que corresponda às variáveis do contexto 
atual (como o IP do usuário, o status do login, etc). A regra correspondente 
determinará se vai permitir ou não a execução da ação solicitada. Se nenhuma 
regra for localizada, o acesso será negado.

O exemplo a seguir mostra como faz para permitir aos usuários autenticados 
acessarem as ações `create` e `update` enquanto todos os outros não autenticados 
não consigam acessá-las. 

```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::className(),
            'only' => ['create', 'update'],
            'rules' => [
                // permite aos usuários autenticados
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // todos os outros usuários são negados por padrão 
            ],
        ],
    ];
}
```

De modo geral, para mais detalhes sobre o controle de acesso, por favor, consulte 
a seção [Autorização](security-authorization.md).


### Métodos de Autenticação por Filtros <span id="auth-method-filters"></span>

O método de autenticação por filtros são usados para autenticar um usuário usando 
vários métodos, tais como 
[HTTP Basic Auth](http://en.wikipedia.org/wiki/Basic_access_authentication), 
[OAuth 2](http://oauth.net/2/). Todas estas classes de filtros estão localizadas 
sob o namespace `yii\filters\auth`.

O exemplo a seguir mostra como você pode usar o filtro 
[[yii\filters\auth\HttpBasicAuth]] para autenticar um usuário usando um acesso 
baseado em token pelo método HTTP Basic Auth. Observe que, para isto funcionar, 
sua [[yii\web\User::identityClass|classe de identidade do usuário]] deve 
implementar o método [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]].

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    return [
        'basicAuth' => [
            'class' => HttpBasicAuth::className(),
        ],
    ];
}
```

Os métodos de autenticação por filtros geralmente são utilizados na implementação 
de APIs RESTful. Para mais detalhes, por favor, consulte a seção RESTful 
[Autenticação](rest-authentication.md).


### [[yii\filters\ContentNegotiator|ContentNegotiator]] <span id="content-negotiator"></span>

O filtro ContentNegotiator suporta a identificação de formatos de respostas e o 
idioma da aplicação. Este filtro tentar determinar o formato de resposta e o 
idioma analisando os parâmetros `GET` e o `Accept` do cabeçalho HTTP.

No exemplo a seguir, o ContentNegotiator está sendo configurado para suportar os 
formatos de resposta JSON e XML, e os idiomas Inglês (Estados Unidos) e Alemão.

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

public function behaviors()
{
    return [
        [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ];
}
```

Os formatos de resposta e os idiomas muitas vezes precisam ser determinados muito 
mais cedo no [ciclo de vida da aplicação](structure-applications.md#application-lifecycle). 
Por este motivo, o ContentNegotiator foi projetado para ser usado de outras formas, 
onde pode ser usado tanto como um 
[componente de inicialização](structure-applications.md#bootstrap) quanto um filtro. 
Por exemplo, você pode configura-lo na 
[configuração da aplicação](structure-applications.md#application-configurations) 
conforme o exemplo a seguir:

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

[
    'bootstrap' => [
        [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ],
];
```

> Informação: Nos casos do formato de resposta e do idioma não serem determinados 
  pela requisição, o primeiro formato e idioma listados em [[formats]] e 
  [[languages]] serão utilizados.


### [[yii\filters\HttpCache|HttpCache]] <span id="http-cache"></span>

O filtro HttpCache implementa no lado do cliente (client-side) o cache pela 
utilização dos parâmetros `Last-Modified` e `Etag` do cabeçalho HTTP.
Por exemplo,

```php
use yii\filters\HttpCache;

public function behaviors()
{
    return [
        [
            'class' => HttpCache::className(),
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

Por favor, consulte a seção [Cache HTTP](caching-http.md) para mais detalhes 
sobre o uso do HttpCache.


### [[yii\filters\PageCache|PageCache]] <span id="page-cache"></span>

O filtro PageCache implementa no lado do servidor (server-side) o cache das 
páginas. No exemplo a seguir, o PageCache é aplicado para a ação `index` guardar 
o cache da página inteira por no máximo 60 segundos ou até que a quantidade de 
registros na tabela `post` seja alterada. Este filtro também guarda diferentes 
versões da página, dependendo do idioma da aplicação escolhido.

```php
use yii\filters\PageCache;
use yii\caching\DbDependency;

public function behaviors()
{
    return [
        'pageCache' => [
            'class' => PageCache::className(),
            'only' => ['index'],
            'duration' => 60,
            'dependency' => [
                'class' => DbDependency::className(),
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
            'variations' => [
                \Yii::$app->language,
            ]
        ],
    ];
}
```

Por favor, consulte a seção [Cache de Página](caching-page.md) para mais 
detalhes sobre o uso do PageCache.


### [[yii\filters\RateLimiter|RateLimiter]] <span id="rate-limiter"></span>

O filtro RateLimiter implementa um limitador de acesso baseado no 
[algoritmo do balde furado (leaky bucket)](http://en.wikipedia.org/wiki/Leaky_bucket).
É usado principalmente na implementação de APIs RESTful. Por favor, consulte a 
seção [Limitador de Acesso](rest-rate-limiting.md) para mais detalhes sobre o 
uso deste filtro.


### [[yii\filters\VerbFilter|VerbFilter]] <span id="verb-filter"></span>

O filtro VerbFilter verifica se os métodos de requisição HTTP são permitidos para 
as ações solicitadas. Se não for, será lançada uma exceção HTTP 405. No exemplo 
a seguir, o VerbFilter é declarado para especificar um conjunto de métodos de 
requisição permitidos para as ações CRUD.

```php
use yii\filters\VerbFilter;

public function behaviors()
{
    return [
        'verbs' => [
            'class' => VerbFilter::className(),
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['get', 'post'],
                'update' => ['get', 'put', 'post'],
                'delete' => ['post', 'delete'],
            ],
        ],
    ];
}
```

### [[yii\filters\Cors|Cors]] <span id="cors"></span>

O compartilhamento de recursos cross-origin 
[CORS](https://developer.mozilla.org/fr/docs/HTTP/Access_control_CORS) é um 
mecanismo que permite vários recursos (por exemplo, fontes, JavaScript, etc) 
na página Web sejam solicitados por outros domínios. Em particular, as chamadas 
AJAX do JavaScript podem usar o mecanismo XMLHttpRequest. Estas chamadas 
"cross-domain" são proibidas pelos navegadores Web, por desrespeitarem a 
politica de segurança de origem. 
O CORS define um modo em que o navegador e o servidor possam interagir para 
determinar se deve ou não permitir as requisições cross-origin.

O [[yii\filters\Cors|filtro Cors]] deve ser definido antes dos filtros de 
Autenticação/Autorização para garantir que os cabeçalhos CORS sejam sempre 
enviados.

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::className(),
        ],
    ], parent::behaviors());
}
```

A filtragem da classe Cors pode ser ajustado pela propriedade `cors`.

* `cors['Origin']`: array usado para definir as origens permitidas. Pode ser 
  `['*']` (qualquer um) ou `['http://www.myserver.net', 'http://www.myotherserver.com']`.
  O padrão é `['*']`.
* `cors['Access-Control-Request-Method']`: array com os métodos de requisição 
  permitidos, tais como `['GET', 'OPTIONS', 'HEAD']`. O padrão é 
  `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`.
* `cors['Access-Control-Request-Headers']`: array com os cabeçalhos permitidos. 
  Pode ser `['*']` para todos os cabeçalhos ou um especifico como `['X-Request-With']`. 
  O padrão é `['*']`.
* `cors['Access-Control-Allow-Credentials']`: define se a requisição atual pode  
  ser feita usando credenciais. Pode ser `true`, `false` ou `null` (não definida). 
  O padrão é `null`.
* `cors['Access-Control-Max-Age']`: define o tempo de vida do pré-processamento 
  (pre-flight) da requisição. O padrão é `86400`.

Por exemplo, permitindo CORS para a origem: `http://www.myserver.net` com os 
métodos `GET`, `HEAD` e `OPTIONS`:

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
        ],
    ], parent::behaviors());
}
```

Você pode ajustar os cabeçalhos do CORS sobrescrevendo os parâmetros padrão para 
cada ação. Por exemplo, para adicionar o parâmetro `Access-Control-Allow-Credentials` 
somente na ação `login`, você poderia fazer conforme a seguir:

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['http://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
            'actions' => [
                'login' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ]
        ],
    ], parent::behaviors());
}
```
