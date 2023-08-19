Container de Injeção de Dependência 
==============================

Um container de injeção de dependência (DI) é um objeto que sabe como instanciar e configurar objetos e todas as suas dependências. O [artigo do Martin](https://martinfowler.com/articles/injection.html) explica bem porque o container de DI é útil. Aqui vamos explicar principalmente a utilização do container de DI fornecido pelo Yii.


Injeção de Dependência <span id="dependency-injection"></span>
--------------------

O Yii fornece o recurso container de DI através da classe [[yii\di\Container]]. Ela suporta os seguintes tipos de injeção de dependência:

* Injeção de Construtor;
* Injeção de setter e propriedade;
* Injeção de PHP callable.


### Injeção de Construtor <span id="constructor-injection"></span>

O container de DI suporta injeção de construtor com o auxílio dos *type hints* identificados nos parâmetros dos construtores. Os type hints informam ao container quais classes ou interfaces são dependentes no momento da criação de um novo objeto.
O container tentará pegar as instâncias das classes dependentes ou interfaces e depois injetá-las dentro do novo objeto através do construtor. Por exemplo:

```php
class Foo
{
    public function __construct(Bar $bar)
    {
    }
}
$foo = $container->get('Foo');
// que equivale a:
$bar = new Bar;
$foo = new Foo($bar);
```


### Injeção de Setter e Propriedade <span id="setter-and-property-injection"></span>

A injeção de setter e propriedade é suportado através de [configurações](concept-configurations.md).
Ao registrar uma dependência ou ao criar um novo objeto, você pode fornecer uma configuração que será utilizada pelo container para injetar as dependências através dos setters ou propriedades correspondentes.
Por exemplo:

```php
use yii\base\BaseObject;

class Foo extends BaseObject
{
    public $bar;

    private $_qux;

    public function getQux()
    {
        return $this->_qux;
    }

    public function setQux(Qux $qux)
    {
        $this->_qux = $qux;
    }
}

$container->get('Foo', [], [
    'bar' => $container->get('Bar'),
    'qux' => $container->get('Qux'),
]);
```

> Informação: O método [[yii\di\Container::get()]] recebe em seu terceiro parâmetro um array de configuração que deve ser aplicado ao objecto a ser criado. Se a classe implementa a interface [[yii\base\Configurable]] (por exemplo, [[yii\base\BaseObject]]), o array de configuração será passado como o último parâmetro para o construtor da classe; caso contrário, a configuração será aplicada *depois* que o objeto for criado.


### Injeção de PHP Callable <span id="php-callable-injection"></span>

Neste caso, o container usará um PHP callable registrado para criar novas instâncias da classe.
Cada vez que [[yii\di\Container::get()]] for chamado, o callable correspondente será invocado.
O callable é responsável por resolver as dependências e injetá-las de forma adequada para os objetos recém-criados. Por exemplo:

```php
$container->set('Foo', function ($container, $params, $config) {
    $foo = new Foo(new Bar);
    // ... Outras inicializações...
    return $foo;
});

$foo = $container->get('Foo');
```

Para ocultar a lógica complexa da construção de um novo objeto você pode usar um método estático de classe para retornar o PHP callable. Por exemplo:

```php
class FooBuilder
{
    public static function build($container, $params, $config)
    {
        return function () {
            $foo = new Foo(new Bar);
            // ... Outras inicializações...
            return $foo;
       };        
    }
}

$container->set('Foo', FooBuilder::build());

$foo = $container->get('Foo');
```

Como você pode ver, o PHP callable é retornado pelo método `FooBuilder::build()`. Ao fazê-lo, quem precisar configurar a classe `Foo` não precisará saber como ele é construído.


Registrando Dependências <span id="registering-dependencies"></span>
------------------------

Você pode usar [[yii\di\Container::set()]] para registrar dependências. O registro requer um nome de dependência, bem como uma definição de dependência. Um nome de dependência pode ser um nome de classe, um nome de interface, ou um alias; e a definição de dependência pode ser um nome de classe, um array de configuração ou um PHP callable.

```php
$container = new \yii\di\Container;

// registrar um nome de classe. Isso pode ser ignorado.
$container->set('yii\db\Connection');

// registrar uma interface
// Quando uma classe depende da interface, a classe correspondente
// será instanciada como o objeto dependente
$container->set('yii\mail\MailInterface', 'yii\swiftmailer\Mailer');

// registrar um alias. Você pode utilizar $container->get('foo')
// para criar uma instância de Connection
$container->set('foo', 'yii\db\Connection');

// registrar uma classe com configuração. A configuração
// será aplicada quando quando a classe for instanciada pelo get()
$container->set('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// registrar um alias com a configuração de classe
// neste caso, um elemento "class" é requerido para especificar a classe
$container->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);

// registrar um PHP callable
// O callable será executado sempre quando $container->get('db') for chamado
$container->set('db', function ($container, $params, $config) {
    return new \yii\db\Connection($config);
});

// registrar uma instância de componente
// $container->get('pageCache') retornará a mesma instância toda vez que for chamada
$container->set('pageCache', new FileCache);
```

> Dica: Se um nome de dependência é o mesmo que a definição de dependência correspondente, você não precisa registrá-lo no container de DI.

Um registro de dependência através de `set()` irá gerar uma instância a cada vez que a dependência for necessária. Você pode usar [[yii\di\Container::setSingleton()]] para registrar a dependência de forma a gerar apenas uma única instância:

```php
$container->setSingleton('yii\db\Connection', [
    'dsn' => 'mysql:host=127.0.0.1;dbname=demo',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```


Resolvendo Dependências <span id="resolving-dependencies"></span>
----------------------

Depois de registrar as dependências, você pode usar o container de DI para criar novos objetos e o container resolverá automaticamente as dependências instanciando e as injetando dentro do novo objeto criado. A resolução de dependência é recursiva, isso significa que
se uma dependência tem outras dependências, essas dependências também serão resolvidas automaticamente.

Você pode usar [[yii\di\Container::get()]] para criar novos objetos. O método recebe um nome de dependência, que pode ser um nome de classe, um nome de interface ou um alias. O nome da dependência pode ou não ser registrado através de `set()` ou `setSingleton()`. Você pode, opcionalmente, fornecer uma lista de parâmetros de construtor de classe e uma [configuração](concept-configurations.md) para configurara o novo objeto criado.
Por exemplo:

```php
// "db" é um alias registrado previamente
$db = $container->get('db');

// equivale a: $engine = new \app\components\SearchEngine($apiKey, ['type' => 1]);
$engine = $container->get('app\components\SearchEngine', [$apiKey], ['type' => 1]);
```

Nos bastidores, o container de DI faz muito mais do que apenas a criação de um novo objeto.
O container irá inspecionar primeiramente o construtor da classe para descobrir classes ou interfaces dependentes e automaticamente resolver estas dependências recursivamente.
O código abaixo mostra um exemplo mais sofisticado. A classe `UserLister` depende de um objeto que implementa a interface `UserFinderInterface`; A Classe `UserFinder` implementa esta interface e depende do objeto `Connection`. Todas estas dependências são declaradas através de type hint dos parâmetros do construtor da classe. Com o registro de dependência de propriedade, o container de DI é capaz de resolver estas dependências automaticamente e cria uma nova instância de `UserLister` simplesmente com `get('userLister')`.

```php
namespace app\models;

use yii\base\BaseObject;
use yii\db\Connection;
use yii\di\Container;

interface UserFinderInterface
{
    function findUser();
}

class UserFinder extends BaseObject implements UserFinderInterface
{
    public $db;

    public function __construct(Connection $db, $config = [])
    {
        $this->db = $db;
        parent::__construct($config);
    }

    public function findUser()
    {
    }
}

class UserLister extends BaseObject
{
    public $finder;

    public function __construct(UserFinderInterface $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }
}

$container = new Container;
$container->set('yii\db\Connection', [
    'dsn' => '...',
]);
$container->set('app\models\UserFinderInterface', [
    'class' => 'app\models\UserFinder',
]);
$container->set('userLister', 'app\models\UserLister');

$lister = $container->get('userLister');

// que é equivalente a:

$db = new \yii\db\Connection(['dsn' => '...']);
$finder = new UserFinder($db);
$lister = new UserLister($finder);
```


Uso Prático <span id="practical-usage"></span>
---------------

O Yii cria um container de DI quando você inclui o arquivo `Yii.php` no [script de entrada](structure-entry-scripts.md) de sua aplicação. O container de DI é acessível através do [[Yii::$container]]. Quando você executa o método [[Yii::createObject()]],  na verdade o que será realmente executado é o método [[yii\di\Container::get()|get()]] do container para criar um novo objeto.
Conforme já informado acima, o container de DI resolverá automaticamente as dependências (se existir) e as injeta dentro do novo objeto criado. Como o Yii utiliza [[Yii::createObject()]] na maior parte do seu código principal para criar novos objetos, isso significa que você pode personalizar os objetos globalmente lidando com [[Yii::$container]].

Por exemplo, você pode customizar globalmente o número padrão de botões de paginação do [[yii\widgets\LinkPager]]:

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

Agora, se você usar o widget na view (visão) com o seguinte código, a propriedade `maxButtonCount` será inicializado como 5 em lugar do valor padrão 10 como definido na class.

```php
echo \yii\widgets\LinkPager::widget();
```

Todavia, você ainda pode substituir o valor definido através container de DI:

```php
echo \yii\widgets\LinkPager::widget(['maxButtonCount' => 20]);
```

Outro exemplo é se beneficiar da injeção automática de construtor do container de DI. Assumindo que a sua classe controller (controlador) depende de alguns outros objetos, tais como um serviço de reserva de um hotel.

Você pode declarar a dependência através de um parâmetro de construtor e deixar o container DI resolver isto para você.

```php
namespace app\controllers;

use yii\web\Controller;
use app\components\BookingInterface;

class HotelController extends Controller
{
    protected $bookingService;

    public function __construct($id, $module, BookingInterface $bookingService, $config = [])
    {
        $this->bookingService = $bookingService;
        parent::__construct($id, $module, $config);
    }
}
```

Se você acessar este controller (controlador) a partir de um navegador, você vai ver um erro informando que `BookingInterface` não pode ser instanciado. Isso ocorre porque você precisa dizer ao container de DI como lidar com esta dependência:

```php
\Yii::$container->set('app\components\BookingInterface', 'app\components\BookingService');
```

Agora se você acessar o controller (controlador) novamente, uma instância de `app\components\BookingService` será criada e injetada como o terceiro parâmetro do construtor do controller (controlador).


Quando Registrar Dependência <span id="when-to-register-dependencies"></span>
-----------------------------

Em função de existirem dependências na criação de novos objetos, o seu registo deve ser feito o mais cedo possível. Seguem abaixo algumas práticas recomendadas:

* Se você é o desenvolvedor de uma aplicação, você pode registrar dependências no [script de entrada] (structure-entry-scripts.md) da sua aplicação ou em um script incluído no script de entrada.
 * Se você é um desenvolvedor de [extensão](structure-extensions.md), você pode registrar as dependências no bootstrapping (inicialização) da classe da sua extensão.


Resumo <span id="summary"></span>
-------

Ambas as injeção de dependência e [service locator](concept-service-locator.md) são padrões de projetos conhecidos que permitem a construção de software com alta coesão e baixo acoplamento. É altamente recomendável que você leia o
[Artigo do Martin](https://martinfowler.com/articles/injection.html) para obter uma compreensão mais profunda da injeção de dependência e service locator.

O Yii implementa o [service locator](concept-service-locator.md) no topo da injeção dependência container (DI).
Quando um service locator tenta criar uma nova instância de objeto, ele irá encaminhar a chamada para o container de DI.
Este último vai resolver as dependências automaticamente tal como descrito acima.

