Componentes de Aplicação
========================

Aplicações são [service locators](concept-service-locator.md). Elas hospedam um
conjunto de assim chamados *componentes de aplicação* que fornecem diferentes
serviços para o processamento de requisições. Por exemplo, o componente
`urlManager` é responsável pelo roteamento de requisições Web aos controllers
adequados; o componente `db` fornece serviços relacionados a bancos de dados; e
assim por diante.

Cada componente de aplicação tem um ID que o identifica de maneira única dentre
os outros componentes de uma mesma aplicação. Você pode acessar um componente de
aplicação através da expressão

```php
\Yii::$app->componentID
```

Por exemplo, você pode usar `\Yii::$app->db` para obter a [[yii\db\Connection|conexão do BD]],
e `\Yii::$app->cache` para obter o [[yii\caching\Cache|cache primário]] registrado
com a aplicação.

Um componente de aplicação é criado na primeira vez em que é acessado através
da expressão acima. Quaisquer acessos posteriores retornarão a mesma instância
do componente.

Componentes de aplicação podem ser quaisquer objetos. Você pode registrá-los
configurando a propriedade [[yii\base\Application::components]] nas
[configurações da aplicação](structure-applications.md#application-configurations).
Por exemplo,

```php
[
    'components' => [
        // registra o componente "cache" usando um nome de classe
        'cache' => 'yii\caching\ApcCache',

        // registra o componente "db" usando um array de configuração
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // registra o componente "search" usando uma função anônima
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Informação: Embora você possa registrar quantos componentes de aplicação você quiser,
  você deveria fazer isso com juízo. Componentes de aplicação são como variáveis
  globais. Usar componentes de aplicação demais pode tornar seu código
  potencialmente mais difícil de testar e manter. Em muitos casos, você pode
  simplesmente criar um componente local e utilizá-lo quando necessário.


## Components de Inicialização <span id="bootstrapping-components"></span>

Conforme mencionado acima, um componente de aplicação só será instanciado quando
ele estiver sendo acessado pela primeira vez. Se ele nunca for acessado durante
uma requisição, ele não será instanciado. No entanto, algumas vezes você pode
querer instanciar um componente de aplicação em todas as requisições, mesmo que
ele não seja explicitamente acessado. Para fazê-lo, você pode listar seu ID na
propriedade [[yii\base\Application::bootstrap|bootstrap]] da aplicação.

Por exemplo, a configuração de aplicação a seguir assegura-se que o componente
`log` sempre esteja carregado:

```php
[
    'bootstrap' => [
        'log',
    ],
    'components' => [
        'log' => [
            // configuração para o componente "log"
        ],
    ],
]
```


## Componentes de Aplicação do Core <span id="core-application-components"></span>

O yii define um conjunto de componentes de aplicação do **core** com IDs fixos
e configurações padrão. Por exemplo, o componente [[yii\web\Application::request|request]]
é usado para coletar as informações sobre uma requisição do usuário e resolvê-la
em uma [rota](runtime-routing.md); o componente [[yii\base\Application::db|db]]
representa uma conexão do banco de dados através da qual você pode realizar
consultas. É com a ajuda destes componentes de aplicação do core que as aplicações
Yii conseguem tratar as requisições dos usuários.

Segue abaixo uma lista dos componentes de aplicação pré-definidos do core. Você
pode configurá-los e personalizá-los como você faz com componentes de aplicação
normais. Quando você estiver configurando um componente de aplicação do core,
se você não especificar sua classe, a padrão será utilizada.

* [[yii\web\AssetManager|assetManager]]: gerencia os asset bundles e a publicação
  de assets. Por favor consulte a seção [Gerenciando Assets](structure-assets.md)
  para mais detalhes.
* [[yii\db\Connection|db]]: representa uma conexão do banco de dados através da
  qual você poderá realizar consultas. Perceba que quando você configura esse
  componente, você precisa especificar a classe do componente bem como as outras
  propriedades obrigatórios, tais como [[yii\db\Connection::dsn]]. Por favor
  consulte a seção [Data Access Objects](db-dao.md) (Objeto de Acesso a Dados)
  para mais detalhes.
* [[yii\base\Application::errorHandler|errorHandler]]: manipula erros e exceções
  do PHP. Por favor consulte a seção [Tratamento de Erros](runtime-handling-errors.md)
  para mais detalhes.
* [[yii\i18n\Formatter|formatter]]: formata dados quando são exibidos aos
  usuários finais. Por exemplo, um número pode ser exibido com um separador de
  milhares, uma data pode ser formatada em um formato longo. Por favor consulte
  a seção [Formatação de Dados](output-formatting.md) para mais detalhes.
* [[yii\i18n\I18N|i18n]]: suporta a tradução e formatação de mensagens. Por favor
  consulte a seção [Internacionalização](tutorial-i18n.md) para mais detalhes.
* [[yii\log\Dispatcher|log]]: gerencia alvos de logs. Por favor consulte a seção
  [Gerenciamento de Logs](runtime-logging.md) para mais detalhes.
* [[yii\swiftmailer\Mailer|mail]]: suporta a composição e envio de e-mails. Por
  favor consulte a seção [Enviando E-mails](tutorial-mailing.md) para mais
  detalhes.
* [[yii\base\Application::response|response]]: representa a resposta sendo enviada
  para os usuários finais. Por favor consulte a seção [Respostas](runtime-responses.md)
  para mais detalhes.
* [[yii\base\Application::request|request]]: representa a requisição recebida dos
  usuários finais. Por favor consulte a seção [Requisições](runtime-requests.md)
  para mais detalhes.
* [[yii\web\Session|session]]: representa as informações da sessão. Esse componente
  só está disponível em [[yii\web\Application|aplicações Web]]. Por favor consulte
  a seção [Sessões e Cookies](runtime-sessions-cookies.md) para mais detalhes.
* [[yii\web\UrlManager|urlManager]]: suporta a análise e criação de URLs. Por
  favor consulte a seção [Análise e Geração de URLs](runtime-routing.md)
  para mais detalhes.
* [[yii\web\User|user]]: representa as informações de autenticação do usuário.
  Esse componente só está disponível em [[yii\web\Application|aplicações Web]].
  Por favor consulte a seção [Autenticação](security-authentication.md) para
  mais detalhes.
* [[yii\web\View|view]]: suporta a renderização de views. Por favor consulte a
  seção [Views](structure-views.md) para mais detalhes.
