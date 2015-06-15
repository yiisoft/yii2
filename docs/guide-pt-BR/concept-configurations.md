Configurações
============

Configurações são amplamente utilizadas em Yii na criação de novos objetos ou inicializando objetos existentes.
Configurações geralmente incluem o nome da classe do objeto que está sendo criado, e uma lista de valores iniciais que devem ser atribuídos as  [propriedades](concept-properties.md) do objeto. Configurações também podem incluir uma lista de manipuladores que devem ser anexados aos [eventos](concept-events.md) do objeto e/ou uma lista de [behaviors](concept-behaviors.md) que também deve ser ligado ao objeto.

A seguir, uma configuração é usada para criar e inicializar uma conexão com o banco:

```php
$config = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];

$db = Yii::createObject($config);
```

O método [[Yii::createObject()]]recebe um array de configuração como argumento, e cria um objeto instanciando a classe informada na configuração. Quando o objeto for instanciado, o resto da configuração será usada para inicializar as propriedades, manipuladores de eventos, e behaviors do objeto.

Se você já tem um objeto, você pode utilizar [[Yii::configure()]] para inicializar as propriedades do objeto com o array de configuração:

```php
Yii::configure($object, $config);
```

Note que, neste caso, o array de configuração não deve conter o elemento `class`.

## Formato da configuração <span id="configuration-format"></span>

O formato de uma configuração pode ser descrita formalmente como:

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    'on eventName' => $eventHandler,
    'as behaviorName' => $behaviorConfig,
]
```

Onde

* O elemento `class` determina um nome de classe totalmente qualificado para o objeto que está sendo criado.
* O elemento `propertyName` determina os valores iniciais para a propriedade nomeada. As chaves são os nomes das propriedades, E os valores são os valores iniciais correspondentes. Apenas variáveis públicas e [propriedades](concept-properties.md) definidas por getters/setters podem ser configuradas.
* O elemento `on eventName` determina quais manipuladores devem ser anexados aos [eventos](concept-events.md) do objeto. Observe que as chaves do array são formadas prefixando a palavra `on `  ao nome do evento. Por favor consulte a seção [Eventos](concept-events.md) para formatos de manipulador de eventos suportados.
* O elemento `as behaviorName`  determina qual [behaviors](concept-behaviors.md) deve ser anexado ao objeto. Observe que as chaves do  array são formadas prefixando a palavra `as ` ao nome do behavior;  O valor, `$behaviorConfig`, representa a configuração para a criação do behavior, como uma configuração normal descrita aqui.

Abaixo está um exemplo mostrando uma configuração valores iniciais de propriedades, manipulador de evento e behaviors:

```php
[
    'class' => 'app\components\SearchEngine',
    'apiKey' => 'xxxxxxxx',
    'on search' => function ($event) {
        Yii::info("Keyword searched: " . $event->keyword);
    },
    'as indexer' => [
        'class' => 'app\components\IndexerBehavior',
        // ... property init values ...
    ],
]
```


## Usando Configurações <span id="using-configurations"></span>

Configurações são utilizadas em vários lugares no  Yii. No início desta secção, mostramos como criar um objeto utilizando configuração [[Yii::createObject()]]. Nesta Subseção, Nós descreveremos a configuração de aplicação e configuração de widget - dois principais usos de configurações.


### Configurações da aplicação <span id="application-configurations"></span>

A configuração de uma [aplicação](structure-applications.md) é provavelmente um dos mais complexos arrays no Yii.
Isto porque a classe [[yii\web\Application|application]] tem muitas propriedades e eventos configuráveis.
Mais importante, suas propriedades [[yii\web\Application::components|components]] podem receber um array de configuração para a criação de componentes que são registrados através da aplicação. O exemplo abaixo é um resumo do arquivo de configuração da aplicação para o [Basic Project Template](start-installation.md).

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
        ],
        'log' => [
            'class' => 'yii\log\Dispatcher',
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=stay2',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
];
```

A configuração não tem uma chave `class`. Isto porque ele é utilizado como um [script de entrada](structure-entry-scripts.md), onde o nome da classe já está informado,

```php
(new yii\web\Application($config))->run();
```

Mais detalhes sobre a configuração das propriedades  `componentes` de uma aplicação podem ser encontrados na seção [Aplicações](structure-applications.md) e na seção[Localizador de serviço](concept-service-locator.md).


### Configurações de Widget <span id="widget-configurations"></span>

Ao utilizar [widgets](structure-widgets.md), muitas vezes você precisa usar configurações para customizar as propriedades do widget.
Ambos os métodos [[yii\base\Widget::widget()]] e [[yii\base\Widget::begin()]] podem ser utilizados para criar um widget. Eles precisam de um array de configuração, como o exemplo abaixo,

```php
use yii\widgets\Menu;

echo Menu::widget([
    'activateItems' => false,
    'items' => [
        ['label' => 'Home', 'url' => ['site/index']],
        ['label' => 'Products', 'url' => ['product/index']],
        ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest],
    ],
]);
```

O código acima cria um  widget `Menu` e inicializa suas propriedades `activateItems` com false. A propriedade `items` também é configurada com os itens do menu para serem exibidos.

Observe que, como o nome da classe já está dado, o array de configuração não precisa da chave `class`.


## Arquivos de Configuração <span id="configuration-files"></span>

Quando uma configuração é muito complexa, uma prática comum é armazená-la em um ou mais arquivos PHP, conhecidos como *arquivos de configuração*. Um arquivo de configuração retorna um array PHP representando a configuração.
Por exemplo, você pode guardar uma configuração da aplicação em um arquivo chamado `web.php`, como a seguir,

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'components' => require(__DIR__ . '/components.php'),
];
```

Como a configuração de `componentes` é muito complexa, você o guarda em um arquivo separado chamado `components.php` e faz um "require" deste arquivo no `web.php` como mostrado acima. o conteúdo de `components.php` é como abaixo,

```php
return [
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
    ],
    'log' => [
        'class' => 'yii\log\Dispatcher',
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
            ],
        ],
    ],
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=stay2',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ],
];
```

Para pegar a configuração armazenada em um arquivo de configuração, simplismente faça um "require" deste arquivo, como o exemplo abaixo:

```php
$config = require('path/to/web.php');
(new yii\web\Application($config))->run();
```


## Configurações Padrões <span id="default-configurations"></span>

O método [[Yii::createObject()]] é implementado com base em um [container de injeção de dependência](concept-di-container.md).
Ele permite que você especifique um conjunto do chamado *configurações padrões* que será aplicado a todas as instâncias das classes especificadas quando elas forem criadas usando [[Yii::createObject()]]. As configurações padrões podem ser especificadas executando `Yii::$container->set()` na [inicialização (bootstrapping)](runtime-bootstrapping.md) do codigo.

Por exemplo, se você quiser personalizar [[yii\widgets\LinkPager]] de modo que todas as páginas mostrarão no máximo 5 botões (o valor padrão é 10), você pode utilizar o código abaixo para atingir esse objetivo,

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```
Sem usar as configurações padrão, você teria que configurar `maxButtonCount` em todos os lugares que utilizassem este recurso.

## Constantes de Ambiente <span id="environment-constants"></span>

Configurações frequentemente variam de acordo com o ambiente no qual a aplicação é executada. Por exemplo, no ambiente de desenvolvimento, você pode querer usar um banco de dados chamado `mydb_dev`, enquanto no servidor de produção você pode querer usar o banco de dados `mydb_prod`. Para facilitar a troca de ambientes, Yii fornece uma constante chamada `YII_ENV` que você pode definir no [script de entrada](structure-entry-scripts.md) da sua aplicação.
Por exemplo,

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

você pode definir `YII_ENV` como um dos seguintes valores:

- `prod`: ambiente de produção. A constante `YII_ENV_PROD` será avaliada como verdadeira.
  Este é o valor padrão da constante `YII_ENV` caso você não a defina.
- `dev`: ambiente de desenvolvimento. A constante `YII_ENV_DEV` será avaliada como verdadeira.
- `test`: ambiente de teste. A constante `YII_ENV_TEST` será avaliada como verdadeira.

Com estas constantes de ambientes, você pode especificar suas configurações de acordo com o ambiente atual. Por exemplo, sua configuração da aplicação pode conter o seguinte código para habilitar [debug toolbar e debugger](tool-debugger.md) no ambiente de desenvolvimento.

```php
$config = [...];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
