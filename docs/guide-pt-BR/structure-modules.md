Módulos
=======

Os módulos são unidades independentes de software que são compostos de 
[models (modelos)](structure-models.md), [views (visões)](structure-views.md), 
[controllers (controladores)](structure-controllers.md) e outros componentes de 
apoio. Os usuários finais podem acessar os controllers (controladores) de um 
módulo caso esteja implementado na [aplicação](structure-applications.md). Por 
estas razões, os módulos são muitas vezes enxergados como mini-aplicações. Os 
módulos diferem das [aplicações](structure-applications.md) pelo fato de não 
poderem ser implementados sozinhos e que devem residir dentro das aplicações.


## Criando Módulos <span id="creating-modules"></span>

Um módulo é organizado como um diretório que é chamado de 
[[yii\base\Module::basePath|caminho base]] do módulo. Dentro deste diretório, 
existem subdiretório, como o `controllers`, `models`, `views` que mantêm os 
controllers (controladores), models (modelos), views (visões) e outros códigos 
assim como em uma aplicação. O exemplo a seguir mostra o conteúdo dentro de um 
módulo:

```
forum/
    Module.php                   o arquivo da classe do módulo 
    controllers/                 contém os arquivos da classe controller
        DefaultController.php    o arquivo da classe controller padrão
    models/                      contém os arquivos da classe model
    views/                       contém a view do controller e os arquivos de layout
        layouts/                 contém os arquivos de layout
        default/                 contém os arquivos de view para o DefaultController
            index.php            o arquivo de view index 
```


### Classe do Módulo <span id="module-classes"></span>

Cada módulo deve ter uma única classe que estende de [[yii\base\Module]]. Esta 
classe deve estar localizada diretamente sob o [[yii\base\Module::basePath|caminho base]] 
do módulo e deve ser [autoloadable](concept-autoloading.md). Quando um módulo 
estiver sendo acessado, uma única instância da classe módulo correspondente será 
criada. Assim como as [instâncias da aplicação](structure-applications.md), as 
instâncias do módulo são usadas para compartilhar dados e componentes para os 
códigos dentro dos módulos.

Segue um exemplo de uma classe de módulo:

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->params['foo'] = 'bar';
        // ...  outros códigos de inicialização ...
    }
}
```

Se o método `init()` inicializar muitas propriedades do módulo, você poderá 
salva-los em um arquivo de [configuração](concept-configurations.md) e carregá-los 
como mostro no código a seguir no método `init()`:

```php
public function init()
{
    parent::init();
    // inicializa o módulo com as configurações carregadas de config.php
    \Yii::configure($this, require __DIR__ . '/config.php');
}
```

O arquivo de configuração `config.php` pode conter o conteúdo a seguir, que é 
semelhante ao de uma [configuração de aplicação](structure-applications.md#application-configurations).

```php
<?php
return [
    'components' => [
        // lista de configuração de componentes
    ],
    'params' => [
        // lista de parâmetros
    ],
];
```


### Controllers em Módulos <span id="controllers-in-modules"></span>

Ao criar controllers (controladores) em um módulo, uma convenção é colocar as 
classes dos controllers (controladores) sob o sub-namespace `controllers` do 
namespace do módulo da classe. Isto também significa que os arquivos da classe 
controller (controlador) devem ser colocadas no diretório `controllers` dentro 
do [[yii\base\Module::basePath|caminho base]] do módulo. Por exemplo, para criar 
um controller (controlador) `post` no módulo `forum` mostrado na última subseção, 
você deve declarar a classe controller (controlador) conforme o seguinte exemplo:

```php
namespace app\modules\forum\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    // ...
}
```

Você pode personalizar o namespace da classe do controller (controlador) 
configurando a propriedade [[yii\base\Module::controllerNamespace]]. No caso de 
alguns controllers (controladores) estiverem fora do namespace, você poderá 
torna-los acessíveis pela configuração da propriedade [[yii\base\Module::controllerMap]], 
de forma similar ao [que você fez na aplicação](structure-applications.md#controller-map).


### Views em Módulos <span id="views-in-modules"></span>

As views (visões) devem ser colocadas no diretório `views` dentro do 
[[yii\base\Module::basePath|caminho base]] do módulo. Para as views (visões) 
renderizadas por um controller (controlador) no módulo, devem ser colocadas sob 
o diretório `views/IDdoController`, onde o `IDdoController` refere-se ao 
[ID do controller (controlador)](structure-controllers.md#routes). Por exemplo, 
se a classe do controller (controlador) for `PostController`, o diretório será 
`views/post` dentro do [[yii\base\Module::basePath|caminho base]] do módulo.

Um módulo pode especificar um [layout](structure-views.md#layouts) que será 
aplicado pelas views (visões) renderizadas pelos controllers (controladores) do 
módulo. Por padrão, o layout deve ser colocado no diretório `views/layouts` e 
deve configurar a propriedade [[yii\base\Module::layout]] para apontar o nome do 
layout. Se você não configurar a propriedade `layout`, o layout da aplicação será 
usada em seu lugar.


## Usando os Módulos <span id="using-modules"></span>

Para usar um módulo em uma aplicação, basta configurar a aplicação, listando o 
módulo na propriedade [[yii\base\Application::modules|modules]] da aplicação. O 
código da [configuração da aplicação](structure-applications.md#application-configurations) 
a seguir faz com que o módulo `forum` seja aplicado:

```php
[
    'modules' => [
        'forum' => [
            'class' => 'app\modules\forum\Module',
            // ... outras configurações do módulo ...
        ],
    ],
]
```

A propriedade [[yii\base\Application::modules|modules]] é composto por um array 
de configurações de módulos. Cada chave do array representa um *ID do módulo* 
que identifica exclusivamente o módulo entre todos módulos da aplicação e o valor 
do array correspondente é uma [configuração](concept-configurations.md) para a 
criação do módulo.


### Rotas <span id="routes"></span>

Assim como acessar os controllers (controladores) em uma aplicação, as 
[rotas](structure-controllers.md#routes) são usadas para tratar os controllers 
(controladores) em um módulo. Uma rota para um controller (controlador) dentro 
de um módulo deve iniciar com o ID do módulo seguido pelo ID do controller 
(controlador) e pelo ID da ação. Por exemplo, se uma aplicação usar um modulo 
chamado `forum`, então a rota `forum/post/index` representará a ação `index` do 
controller (controlador) `post` no módulo. Se a rota conter apenas o ID do módulo, 
então a propriedade [[yii\base\Module::defaultRoute]], na qual o valor padrão é 
`default`, determinará qual controller/action deverá ser usado. Isto significa 
que a rota `forum` representará o controller (controlador) `default` no módulo `forum`.


### Acessando os Módulos <span id="accessing-modules"></span>

Dentro de um módulo, você poderá precisar muitas vezes obter a instância do 
[módulo da classe](#module-classes) para que você possa acessar o ID, os parâmetros, 
os componentes e etc do módulo. Você pode fazer isso usando a seguinte declaração:

```php
$module = MyModuleClass::getInstance();
```

O `MyModuleClass` refere-se ao nome da classe do módulo que você está interessado. 
O método `getInstance()` retornará a instância que foi solicitada pela requisição. 
Se o módulo não for solicitado pela requisição, o método retornará `null`. Observe 
que você não vai querer criar uma nova instância manualmente da classe do módulo 
pois será diferente do criado pelo Yii em resposta a uma requisição.

> Informação: Ao desenvolver um módulo, você não deve assumir que o módulo usará 
  um ID fixo. Isto porque um módulo pode ser associado a um ID arbitrário quando 
  usado em uma aplicação ou dentro de outro módulo. A fim de obter o ID do módulo, 
  você deve usar a abordagem anterior para obter primeiramente a instância do módulo 
  e em seguida obter o ID através de `$module->id`.

Você também pode acessar a instância do módulo usando as seguintes abordagens:

```php
// obter o módulo cujo ID é "forum"
$module = \Yii::$app->getModule('forum');

// obter o módulo pelo controller solicitado pela requisição
$module = \Yii::$app->controller->module;
```

A primeira abordagem só é útil quando você sabe o ID do módulo, enquanto a segunda 
abordagem é melhor utilizada quando você sabe sobre os controllers (controladores) 
que está sendo solicitado.

Uma vez tendo a instância do módulo, você pode acessar as propriedade e componentes 
registrados no módulo. Por exemplo,

```php
$maxPostCount = $module->params['maxPostCount'];
```


### Inicializando os Módulos <span id="bootstrapping-modules"></span>

Alguns módulos precisam ser executados a cada requisição. O módulo 
[[yii\debug\Module|debug]] é um exemplo desta necessidade. Para isto, você deverá 
listar os IDs deste módulos na propriedade [[yii\base\Application::bootstrap|bootstrap]] 
da aplicação. 

Por exemplo, a configuração da aplicação a seguir garante que o módulo `debug` 
seja sempre carregado:

```php
[
    'bootstrap' => [
        'debug',
    ],

    'modules' => [
        'debug' => 'yii\debug\Module',
    ],
]
```


## Módulos Aninhados <span id="nested-modules"></span>

Os módulos podem ser aninhados em níveis ilimitados. Isto é, um módulo pode conter 
um outro módulo que pode conter ainda um outro módulo. Nós chamamos dos anteriores 
de *módulo parente* enquanto os próximos de *módulo filho*. Os módulos filhos devem 
ser declarados na propriedade [[yii\base\Module::modules|modules]] de seus módulos 
parentes. Por exemplo,

```php
namespace app\modules\forum;

class Module extends \yii\base\Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                // você pode considerar em usar um namespace mais curto aqui!
                'class' => 'app\modules\forum\modules\admin\Module',
            ],
        ];
    }
}
```

Para um controller (controlador) dentro de um módulo aninhado, a rota deve incluir 
os IDs de todos os seus módulos ancestrais. Por exemplo, a rota 
`forum/admin/dashboard/index` representa a ação `index` do controller (controlador) 
`dashboard` no módulo `admin` que é um módulo filho do módulo `forum`.

> Informação: O método [[yii\base\Module::getModule()|getModule()]] retorna apenas 
  o módulo filho diretamente pertencente ao seu módulo parente. A propriedade 
  [[yii\base\Application::loadedModules]] mantém uma lista dos módulos carregados, 
  incluindo os módulos filhos e parentes aninhados, indexados pelos seus nomes de 
  classes.


## Boas Práticas <span id="best-practices"></span>

Os módulos são melhores usados em aplicações de larga escala, cujas características 
podem ser divididas em vários grupos, cada um constituídas por um conjuntos de 
recursos relacionados. Cada grupo de recurso pode ser desenvolvido em um módulo 
que é desenvolvido e mantido por um desenvolvedor ou equipe específica. 

Módulos também são uma boa maneira de reutilizar códigos no nível de grupo de 
recurso. Algumas recursos comumente utilizados, tais como gerenciamento de usuários, 
gerenciamento de comentários, podem ser todos desenvolvidos em módulos, para que 
possam ser utilizados em projetos futuros.
