Aplicações
==========

Aplicações são objetos que regem a estrutura e ciclo de vida gerais de
aplicações em Yii. Cada aplicação contém um único objeto Application que é criado no
[script de entrada](structure-entry-scripts.md) e que pode ser acessado
globalmente pela expressão `\Yii::$app`.

> Informação: Dependendo do contexto, quando dizemos "uma aplicação", pode significar
  tanto um objeto Application quanto um sistema.

Existem dois tipos de aplicações: [[yii\web\Application|aplicações Web]] e
[[yii\console\Application|aplicações console]]. Como o próprio nome indica,
o primeiro manipula requisições Web enquanto o segundo trata requisições de
comandos do console.


## Configurações da Aplicação <span id="application-configurations"></span>

Quando um [script de entrada](structure-entry-scripts.md) cria uma aplicação, ele
carregará uma [configuração](concept-configurations.md) e a aplicará à aplicação,
da seguinte forma:

```php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// carrega a configuração da aplicação
$config = require __DIR__ . '/../config/web.php';

// instancia e configura a aplicação
(new yii\web\Application($config))->run();
```

Tal como [configurações](concept-configurations.md) normais, as configurações da
aplicação especificam como inicializar as propriedades de objetos Application.
Uma vez que geralmente são muito complexas, elas normalmente são mantidas em 
[arquivos de configuração](concept-configurations.md#configuration-files),
como o arquivo `web.php` no exemplo acima.


## Propriedades da Aplicação <span id="application-properties"></span>

Existem muitas propriedades importantes da aplicação que deveriam ser configuradas.
Essas propriedades tipicamente descrevem o ambiente em que as aplicaçõe estão
rodando. Por exemplo, as aplicações precisam saber como carregar os
[controllers](structure-controllers.md), onde armazenar os arquivos temporários,
etc. A seguir resumiremos essas propriedades.


### Propriedades Obrigatórias <span id="required-properties"></span>

Em qualquer aplicação, você deve pelo menos configurar duas propriedades:
[[yii\base\Application::id|id]] e [[yii\base\Application::basePath|basePath]].


#### [[yii\base\Application::id|id]] <span id="id"></span>

A propriedade [[yii\base\Application::id|id]] especifica um ID único que diferencia
uma aplicação das outras. É usado principalmente programaticamente. Apesar de não
ser obrigatório, para melhor interoperabilidade recomenda-se que você só use
caracteres alfanuméricos ao especificar um ID de aplicação.


#### [[yii\base\Application::basePath|basePath]] <span id="basePath"></span>

A propriedade [[yii\base\Application::basePath|basePath]] especifica o diretório
raiz de um sistema. É o diretório que contém todo o código fonte protegido de um
sistema. Sob este diretório, você normalmente verá subdiretórios tais como
`models`, `views` e `controllers`, que contém o código fonte correspondente ao
padrão MVC.

Você pode configurar a propriedade [[yii\base\Application::basePath|basePath]]
usando um [alias de caminho](concept-aliases.md). Em ambas as formas, o diretório
correspondente precisa existir, doutra forma será lançada uma exceção. O caminho
será normnalizado chamando-se a função `realpath()`.

A propriedade [[yii\base\Application::basePath|basePath]] frequentemente é
usada para derivar outros caminhos importantes (por exemplo, o diretório de
runtime). Por esse motivo, um alias de caminho `@app` é pré-definido para
representar esse caminho. Assim os caminhos derivados podem ser formados usando
esse alias (por exemplo, `@app/runtime` para referenciar o diretório runtime).


### Propriedades Importantes <span id="important-properties"></span>

As propriedades descritas nesta subseção frequentemente precisam ser
configuradas porque elas variam em diferentes aplicações.


#### [[yii\base\Application::aliases|aliases]] <span id="aliases"></span>

Esta propriedade permite que você defina um conjunto de
[aliases](concept-aliases.md) em termos de um array. As chaves do array representam
os nomes de alias, e os valores são as definições correspondentes. Por exemplo:

```php
[
    'aliases' => [
        '@name1' => 'path/to/path1',
        '@name2' => 'path/to/path2',
    ],
]
```

Esta propriedade é fornecida para que você possa definir aliases na configuração 
da aplicação ao invés de chamar o método [[Yii::setAlias()]].


#### [[yii\base\Application::bootstrap|bootstrap]] <span id="bootstrap"></span>

Esta é uma propriedade muito útil. Ela permite que você especifique um array de
componentes que devem ser executados durante o [[yii\base\Application::bootstrap()|processo de inicialização]]
da aplicação. Por exemplo, se você quer que um [módulo](structure-modules.md)
personalize as [regras de URL](runtime-routing.md), você pode listar seu
ID como um elemento nesta propriedade.

Cada componente listado nesta propriedade deve ser especificado em um dos
seguintes formatos:

- o ID de um componente de aplicação conforme especifcado via [components](#components).
- o ID de um módulo conforme especificado via [modules](#modules).
- o nome de uma classe.
- um array de configuração.
- uma função anônima que cria e retorna um componente.

Por exemplo:

```php
[
    'bootstrap' => [
        // o ID de uma aplicação ou de um módulo
        'demo',

        // um nome de classe
        'app\components\Profiler',

        // um array de configuração
        [
            'class' => 'app\components\Profiler',
            'level' => 3,
        ],

        // uma função anônima
        function () {
            return new app\components\Profiler();
        }
    ],
]
```

> Informação: Se o ID de um módulo é o mesmo que o ID de um componente da aplicação,
  o componente será usado durante o processo de inicialização. Se você quiser
  usar o módulo ao invés dele, você pode especificá-lo usando uma função anônima
  conforme a seguir:
> ```php
[
    function () {
        return Yii::$app->getModule('user');
    },
]
```


Durante o processo de inicialização, cada componente será instanciado. Se a classe
do componente implementa [[yii\base\BootstrapInterface]], seu método [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]]
também será chamado.

Outro exemplo prático está na configuração do [Template Básico de Projetos](start-installation.md),
onde os módulos `debug` e `gii` estão configurados como componentes de inicialização
quando a aplicação está rodando no ambiente de desenvolvimento:

```php
if (YII_ENV_DEV) {
    // ajustes da configuração para o ambiente 'dev'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}
```

> Observação: Colocar componentes demais em `bootstrap` degradará o desempenho de sua
  aplicação, porque para cada requisição o mesmo conjunto de componentes precisará
  ser carregado. Desta forma, use os componentes de inicialização com juízo.


#### [[yii\web\Application::catchAll|catchAll]] <span id="catchAll"></span>

Essa propriedade só é suportada por [[yii\web\Application|aplicações Web]]. Ela
especifica uma [action de um controller](structure-controllers.md) que deve
manipular todas as requisições. Isso é geralmente usado quando a aplicação está
em modo de manutenção e precisa tratar todas as requisições através de uma
única action.

A configuração é um array, cujo primeiro elemento especifica a rota para a action.
O restante dos elementos do array (pares de chave-valor) especificam os parâmetros
que devem ser atrelados à action. Por exemplo:

```php
[
    'catchAll' => [
        'offline/notice',
        'param1' => 'value1',
        'param2' => 'value2',
    ],
]
```


#### [[yii\base\Application::components|components]] <span id="components"></span>

Essa é a propriedade mais importante. Ela permite que você registre uma lista
de componentes chamados [componentes de aplicação](structure-application-components.md)
que você pode usar em outros lugares. Por exemplo:

```php
[
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
    ],
]
```

Cada componente da aplicação é especiifcado como um par de chave-valor em um array.
A chave representa o ID do componente, enquanto o valor representa o nome ou a
[configuração](concept-configurations.md) da classe do componente.

Você pode registrar qualquer componente com uma aplicação, e o componente depois
poderá ser acessado globalmente através da expressão `\Yii::$app->IDdoComponente`.

Por favor leia a seção [Componentes de Aplicação](structure-application-components.md)
para mais detalhes.


#### [[yii\base\Application::controllerMap|controllerMap]] <span id="controllerMap"></span>

Essa propriedade permite que você mapeie um ID de um controller a uma classe
de controller arbitrária. Por padrão, o Yii mapeia os IDs de controllers a classes
de controllers baseado em uma [convenção](#controllerNamespace) (por exemplo, o
ID `post` será mapeado para `app\controllers\PostController`). Ao configurar essa
propriedade, você pode quebrar a convenção de controllers em específico. No exemplo
a seguir, `account` será meapeado para `app\controllers\UserController`,
enquanto `article` será mapeado para `app\controllers\PostController`.

```php
[
    'controllerMap' => [
        'account' => 'app\controllers\UserController',
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]
```

As chaves do array dessa propriedade representam os IDs dos controllers, enquanto
os valores do array representam o nome ou as [configurações](concept-configurations.md)
da classe do controller.


#### [[yii\base\Application::controllerNamespace|controllerNamespace]] <span id="controllerNamespace"></span>

Essa propriedade especifica o namespace padrão sob o qual as classes dos
controllers deverão ser localizadas. Seu valor padrão é `app\controllers`. Se um
ID de um controller for `post`, por convenção o nome da classe de controller
correspondente (sem namespace) seria `PostController`, e o nome da classe
completo e qualificado seria `app\controllers\PostController`.

As classes de controllers também podem estar localizadas em subdiretórios do
diretório correspondente ao namespace. Por exemplo, dado um ID de controller
`admin/post`, a classe completa e qualificada correspondente seria
`app\controllers\admin\PostController`.

É importante que as classes completas e qualificadas possam ser [carregadas automaticamente](concept-autoloading.md)
e que o namespace das suas classes de controller correspondam ao valor dessa
propriedade. Doutra forma, você receberia um erro de "Página Não Encontrada" ao
acessar a aplicação.

Caso você queira quebrar a convenção conforme descrito acima, você pode configurar
a propriedade [controllerMap](#controllerMap).


#### [[yii\base\Application::language|language]] <span id="language"></span>

Essa propriedade especifica o idioma no qual a aplicação deve exibir o conteúdo
aos usuários finais. O valor padrão dessa propriedade é `en`, significando inglês.
Você deve configurar essa propriedade se a sua aplicação suportar múltiplos
idiomas.

O valor dessa propriedade determina vários aspectos da [internacionalização](tutorial-i18n.md),
incluindo tradução de mensagens, formato de datas, formato de números, etc. Por
exemplo, o widget [[yii\jui\DatePicker]] usará o valor dessa propriedade por
padrão para determinar em qual idioma o calendário deverá ser exibido e
como a data deve ser formatada.

Recomenda-se que você especifique um idioma em termos de um [código de idioma IETF](https://en.wikipedia.org/wiki/IETF_language_tag).
Por exenplo, `en` corresponde ao inglês, enquanto `en-US` significa inglês dos
Estados Unidos.

Mais detalhes sobre essa propriedade podem ser encontrados na seção
[Internacionalização](tutorial-i18n.md).


#### [[yii\base\Application::modules|modules]] <span id="modules"></span>

Essa propriedade especifica os [módulos](structure-modules.md) que uma aplicação
contém.

A propriedade recebe um array de classes de módulos ou [configurações](concept-configurations.md)
com as chaves do array sendo os IDs dos módulos. Por exemplo:

```php
[
    'modules' => [
        // um módulo "booking" especificado com a classe do módulo
        'booking' => 'app\modules\booking\BookingModule',

        // um módulo "comment" especificado com um array de configurações
        'comment' => [
            'class' => 'app\modules\comment\CommentModule',
            'db' => 'db',
        ],
    ],
]
```

Por favor consulte a seção [Módulos](structure-modules.md) para mais detalhes.


#### [[yii\base\Application::name|name]] <span id="name"></span>

Essa propriedade especifica o nome da aplicação que pode ser exibido aos
usuários finais. Ao contrário da propriedade [[yii\base\Application::id|id]] que
deveria receber um valor único, o valor desta propriedade serve principalmente
para fins de exibição e não precisa ser único.

Você nem sempre precisa configurar essa propriedade se nenhuma parte do código
a estiver usando.


#### [[yii\base\Application::params|params]] <span id="params"></span>

Essa propriedade especifica um array de parâmetros da aplicação que podem ser
acessados globalmente. Ao invés de usar números e strings fixos espalhados por
toda parte no seu código, é uma boa prática defini-los como parâmetros da
aplicação em um único lugar e usá-los nos lugares onde for necessário. Por
exemplo, você pode definir o tamanho de uma miniatura de imagem como um parâmetro
conforme a seguir:

```php
[
    'params' => [
        'thumbnail.size' => [128, 128],
    ],
]
```

Então no seu código onde você precisar usar o valor do tamanho, você pode
simplesmente usar o código conforme a seguir:

```php
$size = \Yii::$app->params['thumbnail.size'];
$width = \Yii::$app->params['thumbnail.size'][0];
```

Mais tarde, se você decidir mudar o tamanho da miniatura, você só precisa
modificá-lo na configuração da aplicação sem tocar em quaisquer códigos
dependentes.


#### [[yii\base\Application::sourceLanguage|sourceLanguage]] <span id="sourceLanguage"></span>

Essa propriedade especifica o idioma no qual o código da aplicação foi escrito.
O valor padrão é `'en-US'`, significando inglês dos Estados Unidos. Você deve
configurar essa propriedade se o conteúdo do texto no seu código não estiver
em inglês.

Conforme a propriedade [language](#language), você deve configurar essa propriedade
em termos de um [código de idioma IETF](https://en.wikipedia.org/wiki/IETF_language_tag).
Por exemplo, `en` corresponde ao inglês, enquanto `en-US` significa inglês dos
Estados Unidos.

Mais detalhes sobre essa propriedade podem ser encontrados na seção
[Internacionalização](tutorial-i18n.md).


#### [[yii\base\Application::timeZone|timeZone]] <span id="timeZone"></span>

Essa propriedade é disponibilizada como uma maneira alternativa de definir a
timezone do PHP em tempo de execução. Ao confiugrar essa propriedade, você está
essencialmente chamando a função
[date_default_timezone_set()](https://www.php.net/manual/en/function.date-default-timezone-set.php)
do PHP. Por exemplo:

```php
[
    'timeZone' => 'America/Los_Angeles',
]
```


#### [[yii\base\Application::version|version]] <span id="version"></span>

Essa propriedade especifica a versão da aplicação. Seu valor padrão é `'1.0'`.
Você não precisa configurar esta propriedade se nenhuma parte do seu código
estiver utilizando-a.


### Propriedades Úteis <span id="useful-properties"></span>

As propriedades descritas nesta subseção não são comumente configuradas porque
seus valores padrão estipulam convenções comuns. No entanto, você pode ainda
configurá-las no caso de querer quebrar as convenções.


#### [[yii\base\Application::charset|charset]] <span id="charset"></span>

Essa propriedade especifica o charset que a aplicação usa. O valor padrão é
`'UTF-8'`, que deveria ser mantido como está para a maioria das aplicações, a
menos que você esteja trabalhando com sistemas legados que usam muitos dados que
não são unicode.


#### [[yii\base\Application::defaultRoute|defaultRoute]] <span id="defaultRoute"></span>

Essa propriedade especifica a [rota](runtime-routing.md) que uma aplicação deveria
usar quando uma requisição não especifica uma. A rota pode consistir de um ID de
módulo, ID de controller e/ou ID de action. Por exemplo, `help`, `post/create`,
`admin/post/create`. Se não for passado um ID de action, ele assumirá o valor
conforme especificado em [[yii\base\Controller::defaultAction]].

Para [[yii\web\Application|aplicações Web]], o valor padrão dessa propriedade é
`'site'`, o que significa que deve usar o controller `SiteController` e sua
action padrão. Como resultado disso, se você acessar a aplicação sem especificar
uma rota, ele exibirá o resultado de `app\controllers\SiteController::actionIndex()`.

Para [[yii\console\Application|aplicações do console]], o valor padrão é `'help'`,
o que significado que deve usar o comando do core
[[yii\console\controllers\HelpController::actionIndex()]]. Como resultado, se
você executar o comando `yii` sem fornecer quaisquer argumentos, ele exibirá a
informação de ajuda.


#### [[yii\base\Application::extensions|extensions]] <span id="extensions"></span>

Essa propriedade especifica a lista de [extensões](structure-extensions.md) que
estão instaladas e são usadas pela aplicação. Por padrão, ela receberá o array
retornado pelo arquivo `@vendor/yiisoft/extensions.php`. O arquivo `extensions.php`
é gerado e mantido automaticamente quando você usa o [Composer](https://getcomposer.org)
para instalar extensões. Então na maioria dos casos você não precisa configurar
essa propriedade.

No caso especial de você querer manter extensões manualmente, você pode configurar
essa propriedade da seguinte forma:

```php
[
    'extensions' => [
        [
            'name' => 'extension name',
            'version' => 'version number',
            'bootstrap' => 'BootstrapClassName',  // opcional, também pode ser um array de configuração
            'alias' => [  // opcional
                '@alias1' => 'to/path1',
                '@alias2' => 'to/path2',
            ],
        ],

        // ... mais extensões conforme acima ...

    ],
]
```

Como você pode ver, a propriedade recebe um array de especificações de extensões.
Cada extensão é especificada com um array composto pelos elementos `name` e
`version`. Se uma extensão precisa executar durante o 
[processo de inicialização](runtime-bootstrapping.md), um elemento `bootstrap` pode ser
especificado com um nome de uma classe de inicialização ou um array de
[configuração](concept-configurations.md). Uma extensão também pode definir
alguns [aliases](concept-aliases.md).


#### [[yii\base\Application::layout|layout]] <span id="layout"></span>

Essa propriedade especifica o nome do layout padrão que deverá ser usado ao
renderizar uma [view](structure-views.md). O valor padrão é `'main'`, significando
que o arquivo de layout `main.php` sob o [caminho dos layouts](#layoutPath) deverá
ser usado. Se tanto o [caminho do layout](#layoutPath) quanto o
[caminho da view](#viewPath) estiverem recebendo os valores padrão, o arquivo de
layout padrão pode ser representado como o alias de caminho `@app/views/layouts/main.php`.

Você pode configurar esta propriedade como `false` se você quiser desativar o
layout por padrão, embora isso seja muito raro.


#### [[yii\base\Application::layoutPath|layoutPath]] <span id="layoutPath"></span>

Essa propriedade especifica o caminho onde os arquivos de layout devem ser
procurados. O valor padrão é o subdiretório `layouts` dentro do diretório do
[caminho das views](#viewPath). Se o [caminho das views](#viewPath) estiver
recebendo seu valor padrão, o caminho padrão dos layouts pode ser representado
como o alias de caminho `@app/views/layouts`.

Você pode configurá-la como um diretório ou um [alias](concept-aliases.md) de
caminho.


#### [[yii\base\Application::runtimePath|runtimePath]] <span id="runtimePath"></span>

Essa propriedade especifica o caminho onde os arquivos temporários, tais como
arquivos de log e de cache, podem ser gerados. O valor padrão é o diretório
representado pelo alias `@app/runtime`.

Você pode configurá-la como um diretório ou [alias](concept-aliases.md) de
caminho. Perceba que o caminho de runtime precisa ter permissão de escrita para
o processo que executa a aplicação. E o caminho deveria ser protegido para não
ser acessado pelos usuários finais, porque os arquivos temporários dentro dele
podem conter informações sensíveis.

Para simplificar o acesso a esse caminho, o Yii possui um alias de caminho
pré-definido chamado `@runtime` para ele.


#### [[yii\base\Application::viewPath|viewPath]] <span id="viewPath"></span>

Essa propriedade especifica o diretório raiz onde os arquivos de views estão
localizados. O valor padrão do diretório é representado pelo alias `@app/views`.
Você pode configurá-lo como um diretório ou [alias](concept-aliases.md) de
caminho.


#### [[yii\base\Application::vendorPath|vendorPath]] <span id="vendorPath"></span>

Essa propriedade especifica o diretório vendor gerenciado pelo [Composer](https://getcomposer.org).
Ele contém todas as bibliotecas de terceiros usadas pela sua aplicação, incluindo
o framework do Yii. O valor padrão é o diretório representado pelo alias `@app/vendor`.

Você pode configurar essa propriedade como um diretório ou [alias](concept-aliases.md)
de caminho. Quando você modificar essa propriedade, assegure-se de ajustar a
configuração do Composer de acordo.

Para simplificar o acesso a esse caminho, o Yii tem um alias de caminho pré-definido
para ele chamado de `@vendor`.


#### [[yii\console\Application::enableCoreCommands|enableCoreCommands]] <span id="enableCoreCommands"></span>

Essa propriedade só é suportada por [[yii\console\Application|aplicações do console]].
Ela especifica se os comandos do core inclusos no pacote do Yii devem estar
ativos. O valor padrão é `true`.


## Eventos da Aplicação <span id="application-events"></span>

Uma aplicação dispara muitos eventos durante o ciclo de vida de manipulação de
uma requisição. Você pode vincular manipuladores a esses eventos nas
configurações da aplicação do seguinte modo,

```php
[
    'on beforeRequest' => function ($event) {
        // ...
    },
]
```

A sintaxe de uso de `on eventName` é descrita na seção
[Configurações](concept-configurations.md#configuration-format).

Alternativamente, você pode vincular manipuladores de evento durante o
[processo de inicialização](runtime-bootstrapping.md) após a instância da aplicação
ser criada. Por exemplo:

```php
\Yii::$app->on(\yii\base\Application::EVENT_BEFORE_REQUEST, function ($event) {
    // ...
});
```

### [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]] <span id="beforeRequest"></span>

Este evento é disparado *antes* de uma aplicação manipular uma requisição. O nome
do evento é `beforeRequest`.

Quando esse evento é disparado, a instância da aplicação foi configurada e
inicializada. Então é um bom lugar para inserir código personalizado por meio
do mecanismo de eventos para interceptar o processo de tratamento da requisição.
Por exemplo, no manipulador de eventos, você pode definir dinamicamente a
propriedade [[yii\base\Application::language]] baseado em alguns parâmetros.


### [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]] <span id="afterRequest"></span>

Este evento é disparado *depois* que uma aplicação finaliza o tratamento da
requisição, mas *antes* de enviar a resposta. O nome do evento é `afterRequest`.

Quando este evento é disparado, o tratamento da requisição está completo e você
pode aproveitar essa ocasião para fazer um pós-processamento da requisição ou
personalizar a resposta.

Perceba que o componente [[yii\web\Response|response]] também dispara alguns
eventos enquanto está enviando conteúdo de resposta para os usuários finais. 
Esses eventos são disparados *depois* deste evento.


### [[yii\base\Application::EVENT_BEFORE_ACTION|EVENT_BEFORE_ACTION]] <span id="beforeAction"></span>

Este evento é disparado *antes* de executar cada [action de controller](structure-controllers.md).
O nome do evento é `beforeAction`.

O parâmetro do evento é uma instância de [[yii\base\ActionEvent]]. Um manipulador
de evento pode definir o valor da propriedade [[yii\base\ActionEvent::isValid]]
como `false` para interromper a execução da action. Por exemplo:

```php
[
    'on beforeAction' => function ($event) {
        if (alguma condição) {
            $event->isValid = false;
        } else {
        }
    },
]
```

Perceba que o mesmo evento `beforeAction` também é disparado pelos [módulos](structure-modules.md)
e [controllers](structure-controllers.md). Os objetos Application são os primeiros
a disparar este evento, seguidos pelos módulos (se houver algum) e finalmente pelos
controllers. Se um manipulador de evento definir [[yii\base\ActionEvent::isValid]]
como `false`, todos os eventos seguintes NÃO serão disparados.


### [[yii\base\Application::EVENT_AFTER_ACTION|EVENT_AFTER_ACTION]] <span id="afterAction"></span>

Este evento é disparado *depois* de executar cada [action de controller](structure-controllers.md).
O nome do evento é `afterAction`.

O parâmetro do evento é uma instância de [[yii\base\ActionEvent]]. Através da
propriedade [[yii\base\ActionEvent::result]], um manipulador de evento pode
acessar ou modificar o resultado da action. Por exemplo:

```php
[
    'on afterAction' => function ($event) {
        if (alguma condição) {
            // modifica $event->result
        } else {
        }
    },
]
```

Perceba que o mesmo evento `afterAction` também é disparado pelos [módulos](structure-modules.md)
e [controllers](structure-controllers.md). Estes objetos disparam esse evento
na order inversa da do `beforeAction`. Ou seja, os controllers são os primeiros
objetos a disparar este evento, seguidos pelos módulos (se houver algum) e
finalmente pelos objetos Application.


## Ciclo de Vida da  Aplicação <span id="application-lifecycle"></span>

Quando um [script de entrada](structure-entry-scripts.md) estiver sendo executado
para manipular uma requisição, uma aplicação passará pelo seguinte ciclo de vida:

1. O script de entrada carrega a configuração da aplicação como um array.
2. O script de entrada cria uma nova instância da aplicação:
  * [[yii\base\Application::preInit()|preInit()]] é chamado, que configura algumas
    propriedades da aplicação de alta prioridade, tais como
    [[yii\base\Application::basePath|basePath]].
  * Registra o [[yii\base\Application::errorHandler|manipulador de erros]].
  * Configura as propriedades da aplicação.
  * [[yii\base\Application::init()|init()]] é chamado, que por sua vez chama
    [[yii\base\Application::bootstrap()|bootstrap()]] para rodar os componentes
    de inicialização.
3. O script de entrada chama [[yii\base\Application::run()]] para executar a aplicação:
  * Dispara o evento [[yii\base\Application::EVENT_BEFORE_REQUEST|EVENT_BEFORE_REQUEST]].
  * Trata a requisição: resolve a requisição em uma [rota](runtime-routing.md)
    e os parâmetros associados; cria os objetos do módulo, do controller e da
    action conforme especificado pela rota; e executa a action.
  * Dispara o evento [[yii\base\Application::EVENT_AFTER_REQUEST|EVENT_AFTER_REQUEST]].
  * Envia a resposta para o usuário final.
4. O script de entrada recebe o status de saída da aplicação e completa o
   processamento da requisição.
