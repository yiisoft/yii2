Controllers (Controladores)
===========

Os controllers (controladores) fazem parte da arquitetura [MVC](http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).
São objetos de classes que estendem de [[yii\base\Controller]] e são responsáveis
pelo processamento das requisições e por gerar respostas. Em particular, após 
assumir o controle de [applications](structure-applications.md), controllers
analisarão os dados de entradas obtidos pela requisição, passarão estes dados 
para os [models](structure-models.md) (modelos), incluirão os resultados dos models 
(modelos) nas [views](structure-views.md) (visões) e finalmente gerarão as respostas 
de saída.


## Actions (Ações) <span id="actions"></span>

Os controllers são compostos por unidades básicas chamadas de *ações* que podem 
ser tratados pelos usuários finais a fim de realizar a sua execução.

No exemplo a seguir mostra um controller `post` com duas ações: `view` e `create`:

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Post;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
}
```

Na ação `view` (definido pelo método `actionView()`), o primeiro código carrega o
[model](structure-models.md) conforme o ID solicitado; Se o model for devidamente 
carregado, a ação irá exibi-lo utilizado a [view](structure-views.md) chamada de `view`.
Caso contrário, a ação lançará uma exceção.

Na ação `create` (definido pelo método `actionCreate()`), o código é parecido. 
Primeiro ele tenta popular o [model](structure-models.md) usando os dados da requisição
em seguida os salva. Se ambos forem bem sucedidos, a ação redirecionará o navegador
para a ação `view` com o novo ID criado pelo model. Caso contrário, a ação exibirá
a view `create` na qual os usuário poderão fornecer os dados necessários.


## Routes (Rotas) <span id="routes"></span>

Os usuários finais abordarão as ações por meio de *rotas*. Uma rota é uma string composta
pelas seguintes partes:

* um ID do módulo: serve apenas se o controller pertencer a um [módulo](structure-modules.md) que não seja da aplicação;
* um ID do controller: uma string que identifica exclusivamente o controller dentre todos os controllers da mesma aplicação (ou do mesmo módulo, caso o controller pertença a um módulo);
* um ID da ação: uma string que identifica exclusivamente uma ação dentre todas as ações de um mesmo controller.

As rotas seguem o seguinte formato:

```
IDdoController/IDdoAction
```

ou o seguinte formato se o controller estiver em um módulo:

```php
IDdoModule/IDdoController/IDdoAction
```

Portanto, se um usuário fizer uma requisição com a URL `http://hostname/index.php?r=site/index`, 
a ação `index` do controller `site` será executada. Para mais detalhes sobre como
as ações são resolvidas pelas rotas, por favor consulte a seção [Roteamento e Criação de URL](runtime-routing.md).


## Criando Controllers <span id="creating-controllers"></span>

Em [[yii\web\Application|aplicações Web]], os controllers devem estender de [[yii\web\Controller]] 
ou de suas classes filhas. De forma semelhante, em [[yii\console\Application|aplicaçoes console]], 
os controllers devem estender de [[yii\console\Controller]] ou de suas classes filhos. O código a seguir define um controller `site`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### IDs dos Controllers <span id="controller-ids"></span>

Normalmente, um controller é projetado para tratar as requisições relativos a 
um determinado tipo de recurso. Por esta razão, os IDs dos controllers geralmente
são substantivos que referenciam-se ao tipo de recurso que será tratado.
Por exemplo, você pode usar o `article` como o ID do um controller para tratar
dados de artigos.

Por padrão, os IDs dos controllers devem conter apenas esses caracteres: 
letras inglesas em caixa baixa, números, underscores (underline), hífens e barras. 
Por exemplo, `article` e `post-comment` são ambos IDs de controllers válidos,
enquanto `article?`, `PostComment`, `admin\post` não são.

Um ID de controller também pode conter um prefixo para o subdiretório. Por exemplo,
`admin/article` representa um controller `article` em um subdiretório `admin` sob 
o [[yii\base\Application::controllerNamespace|namespace do controller]]
Os caracteres válidos para os prefixos de subdiretórios incluem: letras inglesas 
em caixa alto ou caixa baixa, números, underscores (underline) e barras, onde as 
barras são usadas para separar os níveis dos subdiretórios (por exemplo, `panels/admin`).


### Nomenclatura da Classe do Controller <span id="controller-class-naming"></span>

Os nomes da classes dos controllers podem ser derivadas dos IDs dos controllers 
de acordo com as seguintes procedimentos:

1. Colocar em caixa alta a primeira letra de cada palavra separadas por traço. 
   Observe que se o ID do controller possuir barras, a regra é aplicada apenas na
   parte após a última barra no ID.
2. Remover os traços e substituir todas as barras por barras invertidas.
3. Adicionar `Controller` como sufixo.
4. Preceder ao [[yii\base\Application::controllerNamespace|namespace do controller]].

Segue alguns exemplos, assumindo que o [[yii\base\Application::controllerNamespace|namespace do controller]]
tenha por padrão o valor `app\controllers`:

* `article` torna-se `app\controllers\ArticleController`;
* `post-comment` torna-se `app\controllers\PostCommentController`;
* `admin/post-comment` torna-se `app\controllers\admin\PostCommentController`;
* `adminPanels/post-comment` torna-se `app\controllers\adminPanels\PostCommentController`.

As classes dos controllers devem ser [autoloadable](concept-autoloading.md). 
Por esta razão, nos exemplos anteriores, o controller `article` deve ser salvo 
no arquivo cuja [alias](concept-aliases.md) é `@app/controllers/ArticleController.php`;
enquanto o controller `admin/post2-comment` deve ser salvo no `@app/controllers/admin/Post2CommentController.php`.

> Informação: No último exemplo `admin/post2-comment`, mostra como você pode colocar 
um controller em um subdiretório do [[yii\base\Application::controllerNamespace|namespace controller]]. Isto é útil quando você quiser organizar seus controllers em diversas 
categorias e não quiser usar [módulos](structure-modules.md).


### Mapeando Controllers <span id="controller-map"></span>

Você pode configurar um [[yii\base\Application::controllerMap|mapeamento de controllers]] 
para superar as barreiras impostas pelos IDs de controllers e pelos nomes de classes
descritos acima. Isto é útil principalmente quando quiser esconder controllers
de terceiros na qual você não tem controle sobre seus nomes de classes.

Você pode configurar o [[yii\base\Application::controllerMap|mapeamento de controllers]] 
na [configuração da aplicação](structure-applications.md#application-configurations). Por exemplo:

```php
[
    'controllerMap' => [
        // declara o controller "account" usando um nome de classe
        'account' => 'app\controllers\UserController',

        // declara o controller "article" usando uma configuração em array
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]
```


### Controller Padrão <span id="default-controller"></span>

Cada aplicação tem um controller padrão que é especificado pela propriedade [[yii\base\Application::defaultRoute]].
Quando uma requisição não especificar uma [rota](#id-da-rota), será utilizada a 
rota especificada pela propriedade. 
Para as [[yii\web\Application|aplicações Web]], este valor é `'site'`, enquanto 
para as [[yii\console\Application|aplicações console]] é `help`. Portanto, se uma 
URL for `http://hostname/index.php`, o controller `site` será utilizado nesta requisição.

Você pode alterar o controller padrão como a seguinte [configuração da aplicação](structure-applications.md#application-configurations):

```php
[
    'defaultRoute' => 'main',
]
```


## Criando Ações <span id="creating-actions"></span>

Criar ações pode ser tão simples como a definição dos chamados *métodos de ação* 
em uma classe controller. Um método de ação é um método *público* cujo nome inicia 
com a palavra `action`. O valor de retorno representa os dados de resposta a serem 
enviados aos usuário finais. O código a seguir define duas ações, `index` e `hello-world`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHelloWorld()
    {
        return 'Hello World';
    }
}
```


### IDs das Ações <span id="action-ids"></span>

Uma ação muitas vezes é projetada para realizar uma manipulação em particular sobre
um recurso. Por esta razão, os IDs das ações geralmente são verbos, tais como `view`, `update`, etc.

Por padrão, os IDs das ações devem conter apenas esses caracteres: letras inglesas 
em caixa baixa, números, underscores (underline) e traços. Os traços em um ID da
ação são usados para separar palavras. Por exemplo, `view`, `update2` e `comment-post` 
são IDs válidos, enquanto `view?` e `Update` não são.

Você pode criar ações de duas maneiras: ações inline (em sequência) e
ações standalone (autônomas). Uma ação inline é definida pelo método
de uma classe controller, enquanto uma ação standalone é uma classe que estende de 
[[yii\base\Action]] ou de uma classe-filha. As ações inline exigem menos esforço
para serem criadas e muitas vezes as preferidas quando não se tem a intenção de 
reutilizar estas ações. Ações standalone, por outro lado, são criados principalmente 
para serem utilizados em diferentes controllers ou para serem distribuídos como
[extensions](structure-extensions.md).


### Ações Inline <span id="inline-actions"></span>

As ações inline referem-se a os chamados métodos de ação, que foram descritos anteriormente.

Os nomes dos métodos de ações são derivadas dos IDs das ações de acordo com os
seguintes procedimentos:

1. Colocar em caixa alta a primeira letra de cada palavra do ID da ação;
2. Remover os traços;
3. Adicionar o prefixo `action`.

Por exemplo, `index` torna-se `actionIndex` e `hello-world` torna-se `actionHelloWorld`.

> Observação: Os nomes dos métodos de ações são *case-sensitive*. Se você tiver 
  um método chamado `ActionIndex`, não será considerado como um método de ação e 
  como resultado, o pedido para a ação `index` lançará uma exceção. Observe também
  que os métodos de ações devem ser públicas. Um método privado ou protegido NÃO 
  será definido como ação inline.
  
As ações inline normalmente são as mais utilizadas pois demandam pouco esforço 
para serem criadas. No entanto, se você deseja reutilizar algumas ações em diferentes
lugares ou se deseja distribuir uma ação, deve considerar defini-la como uma *ação standalone*.


### Ações Standalone <span id="standalone-actions"></span>

Ações standalone são definidas por classes de ações que estendem de [[yii\base\Action]]
ou de uma classe-filha. 
Por example, nas versões do Yii, existe a [[yii\web\ViewAction]] e a [[yii\web\ErrorAction]], ambas são ações standalone.

Para usar uma ação standalone, você deve *mapear as ações* sobrescrevendo o método
[[yii\base\Controller::actions()]] em suas classes controllers como o seguinte:

```php
public function actions()
{
    return [
        // declara a ação "error" usando um nome de classe
        'error' => 'yii\web\ErrorAction',

        // declara a ação "view" usando uma configuração em array
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

Como pode ver, o método `actions()` deve retornar um array cujas chaves são os IDs
das ações e os valores correspondentes ao nome da classe da ação ou [configurações](concept-configurations.md). Ao contrário das ações inline, os IDs das ações standalone
podem conter caracteres arbitrários desde que sejam mapeados no método `actions()`.


Para criar uma classe de ação standalone, você deve estender de [[yii\base\Action]] ou de duas classes filhas e implementar um método público chamado `run()`. A regra para o método `run()` 
é semelhante ao de um método de ação. Por exemplo,

```php
<?php
namespace app\components;

use yii\base\Action;

class HelloWorldAction extends Action
{
    public function run()
    {
        return "Hello World";
    }
}
```


### Resultados da Ação <span id="action-results"></span>

O valor de retorno do método de ação ou do método `run()` de uma ação standalone 
são importantes. Eles representam o resultado da ação correspondente.

O valor de retorno pode ser um objeto de [resposta](runtime-responses.md) que 
será enviado como resposta aos usuários finais.

* Para [[yii\web\Application|aplicações Web]], o valor de retorno também poder
  ser algum dado arbitrário que será atribuído à propriedade [[yii\web\Response::data]] 
  e ainda ser convertido em uma string para representar o corpo da resposta.
* Para [[yii\console\Application|aplicações console]], o valor de retorno também 
  poder ser um inteiro representando o [[yii\console\Response::exitStatus|exit status]] 
  (status de saída) da execução do comando.

Nos exemplos acima, todos os resultados são strings que serão tratados como o 
corpo das respostas para serem enviados aos usuários finais. No exemplo a seguir,
mostra como uma ação pode redirecionar o navegador do usuário para uma nova URL 
retornando um objeto de resposta (o método [[yii\web\Controller::redirect()|redirect()]] 
retorna um objeto de resposta):

```php
public function actionForward()
{
    // redireciona o navegador do usuário para http://example.com
    return $this->redirect('http://example.com');
}
```


### Parâmetros da Ação <span id="action-parameters"></span>

Os métodos de ações para as ações inline e os métodos `run()` para as ações
standalone podem receber parâmetros, chamados *parâmetros da ação*.
Seus valores são obtidos a partir das requisições. Para 
[[yii\web\Application|aplicações Web]], o valor de cada parâmetro da ação são 
obtidos pelo `$_GET` usando o nome do parâmetro como chave; para 
[[yii\console\Application|aplicações console]], eles correspondem aos argumentos
da linha de comando.

No exemplo a seguir, a ação `view` (uma ação inline) possui dois parâmetros declarados:
`$id` e `$version`.

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public function actionView($id, $version = null)
    {
        // ...
    }
}
```

A seguir, os parâmetros da ação serão populados em diferentes requisições:

* `http://hostname/index.php?r=post/view&id=123`: o parâmetro `$id` receberá
  o valor `'123'`, enquanto o `$version` continuará com o valor nulo porque não
  existe o parâmetro `version` na URL.
* `http://hostname/index.php?r=post/view&id=123&version=2`: os parâmetros `$id` 
  e `$version` serão receberão os valores `'123'` e `'2'`, respectivamente.
* `http://hostname/index.php?r=post/view`: uma exceção [[yii\web\BadRequestHttpException]] 
  será lançada porque o parâmetro obrigatório `$id` não foi informado na requisição.
* `http://hostname/index.php?r=post/view&id[]=123`: uma exceção [[yii\web\BadRequestHttpException]] 
  será lançada porque o parâmetro `$id` foi informado com um valor array `['123']` 
  na qual não era esperado.

Se você quiser que um parâmetro da ação aceite valores arrays, deverá declara-lo 
explicitamente com `array`, como mostro a seguir:

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

Agora, se a requisição for `http://hostname/index.php?r=post/view&id[]=123`, o 
parâmetro `$id` receberá o valor `['123']`. Se a requisição for 
`http://hostname/index.php?r=post/view&id=123`, o parâmetro `$id` ainda receberá 
um array como valor pois o valor escalar `'123'` será convertido automaticamente 
em um array.

Os exemplo acima mostram, principalmente, como os parâmetros da ação trabalham em
aplicações Web. Para aplicações console, por favor, consulte a seção 
[Comandos de Console](tutorial-console.md) para mais detalhes.


### Default Action <span id="default-action"></span>

Cada controller tem uma ação padrão especificado pela propriedade 
[[yii\base\Controller::defaultAction]].
Quando uma [rota](#id-da-rota) contém apenas o ID do controller, implica que a 
ação padrão do controller seja solicitada.

Por padrão, a ação padrão é definida como `index`. Se quiser alterar o valor padrão,
simplesmente sobrescreva esta propriedade na classe controller, como o seguinte:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'home';

    public function actionHome()
    {
        return $this->render('home');
    }
}
```


## Ciclo de Vida do Controller <span id="controller-lifecycle"></span>

Ao processar uma requisição, a [aplicação](structure-applications.md) criará 
um controller baseada na [rota](#routes) solicitada. O controller, então, se submeterá
ao seguinte ciclo de vida para concluir a requisição:

1. O método [[yii\base\Controller::init()]] é chamado após o controller ser criado e configurado.
2. O controller cria um objeto da ação baseada no ID da ação solicitada:
   * Se o ID da ação não for especificado, o [[yii\base\Controller::defaultAction|ID da ação padrão]] será utilizada.
   * Se o ID da ação for encontrada no [[yii\base\Controller::actions()|mapeamento das ações]], uma ação standalone será criada;
   * Se o ID da ação for encontrada para corresponder a um método de ação, uma ação inline será criada;
   * Caso contrário, uma exceção [[yii\base\InvalidRouteException]] será lançada.
3. De forma sequencial, o controller chama o método `beforeAction()` da aplicação, o módulo (se o controller pertencer a um módulo) e o controller.
   * Se uma das chamadas retornar false, o restante dos métodos subsequentes `beforeAction()` serão ignoradas e a execução da ação será cancelada.
   * Por padrão, cada método `beforeAction()` desencadeia a execução de um evento chamado `beforeAction` na qual você pode associar a uma função (handler).
4. O controller executa a ação:
   * Os parâmetros da ação serão analizados e populados a partir dos dados obtidos pela requisição;
5. De forma sequencial, o controller chama o método `afterAction()` do controller, o módulo (se o controller pertencer a um módulo) e a aplicação.
   * Por padrão, cada método `afterAction()` desencadeia a execução de um evento chamado `afterAction` na qual você pode associar a uma função (handler).
6. A aplicação obterá o resultado da ação e irá associá-lo na [resposta](runtime-responses.md).


## Boas Práticas <span id="best-practices"></span>

Em uma aplicação bem projetada, frequentemente os controllers são bem pequenos na 
qual cada ação possui poucas linhas de códigos.
Se o controller for um pouco complicado, geralmente indica que terá que refaze-lo 
e passar algum código para outro classe.

Segue algumas boas práticas em destaque. Os controllers:

* podem acessar os dados de uma [requisição](runtime-requests.md);
* podem chamar os métodos dos [models](structure-models.md) e outros componentes
  de serviço com dados da requisição;
* podem usar as [views](structure-views.md) para compor as respostas;
* NÃO devem processar os dados da requisição - isto deve ser feito na [camada model (modelo)](structure-models.md);
* devem evitar inserir códigos HTML ou outro código de apresentação - é melhor 
  que sejam feitos nas [views](structure-views.md).
