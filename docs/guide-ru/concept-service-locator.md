Service Locator
=============

Service Locator является объектом, предоставляющим всевозможные сервисы (или компоненты), которые могут понадобиться
приложению. В Service Locator каждый компонент представлен единственным экземпляром, имеющим уникальный ID.
Уникальный идентификатор (ID) может быть использован для получения компонента из Service Locator.

В Yii Service Locator является экземпляром класса [[yii\di\ServiceLocator]] или его дочернего класса.

Наиболее часто используемый Service Locator в Yii — это объект *приложения*, который можно получить через `\Yii::$app`.
Предоставляемые им службы такие, как компоненты `request`, `response`, `urlManager`, называют *компонентами приложения*.
Благодаря Service Locator вы легко можете настроить эти компоненты или даже заменить их собственными реализациями.

Помимо объекта приложения, объект каждого модуля также является Service Locator.

При использовании Service Locator первым шагом является регистрация компонентов. Компонент может быть зарегистрирован
с помощью метода [[yii\di\ServiceLocator::set()]]. Следующий код демонстрирует различные способы регистрации компонентов:

```php
use yii\di\ServiceLocator;
use yii\caching\FileCache;

$locator = new ServiceLocator;

// регистрирует "cache", используя имя класса, которое может быть использовано для создания компонента.
$locator->set('cache', 'yii\caching\ApcCache');

// регистрирует "db", используя конфигурационный массив, который может быть использован для создания компонента.
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// регистрирует "search", используя анонимную функцию, которая создаёт компонент
$locator->set('search', function () {
    return new app\components\SolrService;
});

// регистрирует "pageCache", используя компонент
$locator->set('pageCache', new FileCache);
```

После того, как компонент зарегистрирован, вы можете обращаться к нему по его ID одним из двух следующих способов:

```php
$cache = $locator->get('cache');
// или
$cache = $locator->cache;
```

Как видно выше, [[yii\di\ServiceLocator]] позволяет обратиться к компоненту как к свойству, используя его ID.
При первом обращении к компоненту, [[yii\di\ServiceLocator]] создаст новый экземпляр компонента на основе регистрационной
информации и вернёт его. При повторном обращении к компоненту Service Locator вернёт тот же экземпляр.


Чтобы проверить, был ли идентификатор компонента уже зарегистрирован, можно использовать [[yii\di\ServiceLocator::has()]].
Если вы вызовете [[yii\di\ServiceLocator::get()]] с несуществующим ID, будет выброшено исключение.


Поскольку Service Locator часто используется с [конфигурациями](concept-configurations.md), в нём имеется доступное
для записи свойство [[yii\di\ServiceLocator::setComponents()|components]]. Это позволяет настроить и зарегистрировать
сразу несколько компонентов. Следующий код демонстрирует конфигурационный массив, который может использоваться
для регистрации компонентов `db`, `cache`, `tz` и `search` в Service Locator (то есть в [приложении](structure-applications.md)):

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
        'tz' => function() {
            return new \DateTimeZone(Yii::$app->formatter->defaultTimeZone);
        },
        'search' => function () {
            $solr = new app\components\SolrService('127.0.0.1');
            // ... дополнительная инициализация ...
            return $solr;
        },
    ],
];
```

Есть альтернативный приведённому выше способ настройки компонента `search`. Вместо анонимной функции, которая
отдаёт экземпляр `SolrService`, можно использовать статический метод, возвращающий такую анонимную функцию:

```php
class SolrServiceBuilder
{
    public static function build($ip)
    {
        return function () use ($ip) {
            $solr = new app\components\SolrService($ip);
            // ... дополнительная инициализация ...
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

Это особенно полезно, если вы создаёте компонент для Yii, являющийся обёрткой над какой-либо сторонней библиотекой.
Подобный приведённому выше статический метод позволяет скрыть от конечного пользователя сложную логику настройки
сторонней библиотеки. Пользователю будет достаточно вызвать статический метод.

