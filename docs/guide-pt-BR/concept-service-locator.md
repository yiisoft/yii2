Service Locator
===============

Um service locator é um objeto que sabe como fornecer todos os tipos de serviços (ou componentes) que uma aplicação pode precisar. Num service locator, existe uma única instância de cada componente, exclusivamente identificados por um ID.
Você usa o ID para recuperar um componente do service locator.

No Yii, um service locator é simplesmente uma instância da classe [[yii\di\ServiceLocator]] ou de classes que as estendam.

O service locator mais comumente utilizado no Yii é o objeto *application*, que pode ser acessado através de `\Yii::$app`. Os serviços que ele fornece são chamados de *componentes de aplicação*, tais como os componentes `request`, `response`, e `urlManager`. Você pode configurar esses componentes, ou mesmo substituí-los com suas próprias implementações, facilmente através de funcionalidades fornecidas pelo service locator.

Além do objeto *application*, cada objeto *module* também é um service locator.
Para usar um service locator, o primeiro passo é registrar os componentes nele. Um componente pode ser registrado com [[yii\di\ServiceLocator::set()]]. O código abaixo mostra os diferentes modos de registrar um componente:

```php
use yii\di\ServiceLocator;
use yii\caching\FileCache;

$locator = new ServiceLocator;

// registra "cache" utilizando um nome de classe que pode ser usado para criar um componente
$locator->set('cache', 'yii\caching\ApcCache');

// Registra "db" utilizando um array de configuração que pode ser usado para criar um componente
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// registra "search" utilizando uma função anônima que cria um componente
$locator->set('search', function () {
    return new app\components\SolrService;
});

// registra "pageCache" utilizando um componente
$locator->set('pageCache', new FileCache);
```

Uma vez que um componente tenha sido registado, você pode acessá-lo utilizando seu ID, em uma das duas maneiras abaixo:

```php
$cache = $locator->get('cache');
// ou alternativamente
$cache = $locator->cache;
```

Como mostrado acima, [[yii\di\ServiceLocator]] permite-lhe acessar um componente como uma propriedade usando o ID do componente. Quando você acessa um componente pela primeira vez, [[yii\di\ServiceLocator]] usará as informações de registro do componente para criar uma nova instância do componente e retorná-lo. Mais tarde, se o componente for acessado novamente, o service locator irá retornar a mesma instância.

Você pode utilizar [[yii\di\ServiceLocator::has()]] para checar se um ID de componente já está registrado.
Se você executar [[yii\di\ServiceLocator::get()]] com um ID inválido, uma exceção será lançada.


Uma vez que service locators geralmente são criados com [configurações](concept-configurations.md), uma propriedade chamada [[yii\di\ServiceLocator::setComponents()|components]] é fornecida. Isso permite que você possa configurar e registrar vários componentes de uma só vez. O código a seguir mostra um array de configuração que pode ser utilizado para configurar um service locator (por exemplo. uma [aplicação](structure-applications.md)) com o "db", "cache" e "search" components:

```php
return [
    // ...
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],
        'cache' => 'yii\caching\ApcCache',
        'search' => function () {
            $solr = new app\components\SolrService('127.0.0.1');
            // ... outras inicializações ...
            return $solr;
        },
    ],
];
```

No código acima, existe um caminho alternativo para configurar o componente "search". Em vez de escrever diretamente um PHP callback que cria uma instância de `SolrService`, você pode usar um método estático de classe para retornar semelhante a um callback, como mostrado abaixo:

```php
class SolrServiceBuilder
{
    public static function build($ip)
    {
        return function () use ($ip) {
            $solr = new app\components\SolrService($ip);
            // ... outras inicializações ...
            return $solr;
        };
    }
}

return [
    // ...
    'components' => [
        // ...
        'search' => SolrServiceBuilder::build('127.0.0.1'),
    ],
];
```

Esta abordagem alternativa é mais preferível quando você disponibiliza um componente Yii que encapsula alguma biblioteca de terceiros. Você usa o método estático como mostrado acima para representar a lógica complexa da construção do objeto de terceiros e o usuário do seu componente só precisa chamar o método estático para configurar o componente.

