Конфигурации
========

Конфигурации широко используются в Yii при создании новых объектов или при инициализации уже существующих объектов. 
Обычно Конфигурации включают в себя названия классов создаваемых объектов и список первоначальных значений,
которые должны быть присвоены [свойствам](concept-properties.md) объекта. Также в Конфигурациях можно указать список
[обработчиков событий](concept-events.md) объекта, и/или список [поведений](concept-behaviors.md) объекта.

Пример Конфигурации подключения к базе данных и дальнейшей инициализации подключения: 

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

Метод [[Yii::createObject()]] принимает в качестве аргумента массив с Конфигурацией и создаёт объект указанного в них класса.
При этом оставшаяся часть Конфигурации используется для инициализации свойств, обработчиков событий и поведений объекта.

Если объект уже создан, вы можете использовать [[Yii::configure()]] для того, чтобы инициализировать свойства объекта
массивом с Конфигурацией:

```php
Yii::configure($object, $config);
```

Обратите внимание, что в этом случае массив с Конфигурацией не должен содержать ключ `class`.


## Формат Конфигурации <a name="configuration-format"></a>

Формат Конфигурации выглядит следующим образом:

```php
[
    'class' => 'ClassName',
    'propertyName' => 'propertyValue',
    'on eventName' => $eventHandler,
    'as behaviorName' => $behaviorConfig,
]
```

где

* Элемент `class` указывает абсолютное имя класса создаваемого объекта.
* Элементы `propertyName` указывают первоначальные значения свойств создаваемого объекта. Ключи являются именами свойств
  создаваемого объекта, а значения — начальными значениями свойств создаваемого объекта.
  Таким способом могут быть установлены только публичные переменные объекта и его [свойства](concept-properties.md),
  созданные через геттеры и сеттеры.
* Элементы `on eventName` указывают на то, какие обработчики должны быть прикреплены к [событиям](concept-events.md) объекта.
  Обратите внимание, что ключи массива начинаются с `on `. Чтобы узнать весь список поддерживаемых видов
  обработчиков событий обратитесь в раздел [события](concept-events.md)
* Элементы `as behaviorName` указывают на то, какие [поведения](concept-behaviors.md) должны быть внедрены в объект.
  Обратите внимание, что ключи массива начинаются с `as `; а `$behaviorConfig` представляет собой Конфигурацию для создания [поведения](concept-behaviors.md), такою же как и обычную Конфигурацию о которой идет речь.

Пример Конфигурации с установкой первоначальных значений свойств объекта, обработчика событий и поведения:

```php
[
    'class' => 'app\components\SearchEngine',
    'apiKey' => 'xxxxxxxx',
    'on search' => function ($event) {
        Yii::info("Keyword searched: " . $event->keyword);
    },
    'as indexer' => [
        'class' => 'app\components\IndexerBehavior',
        // ... начальные значения свойств ...
    ],
]
```


## Использование Конфигурации <a name="using-configurations"></a>

Конфигурации повсеместно используются в Yii. В самом начале данной главы мы узнали как
создать объект с необходимыми параметрами используя метод [[Yii::createObject()]].
В данном разделе речь пойдет о Конфигурации приложения и Конфигурациях виджетов — двух основных способов
использования Конфигурации. 


### Конфигурация приложения <a name="application-configurations"></a>

Конфигурация [приложения](structure-applications.md) пожалуй самый сложный из используемых в фреймворке.
Причина в том, что класс [[yii\web\Application|application]] содержит большое количество конфигурируемых
свойств и событий. Более того, свойство приложения [[yii\web\Application::components|components]]
может принимать массив с Конфигурацией для создания компонентов, регистрируемых на уровне приложения.
Пример Конфигурации приложения для [шаблона приложения basic](start-basic.md).

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

Ключ `class` в данной Конфигурации не указывается. Причина в том, что класс вызывается по полному имени во
[входном скрипте](structure-entry-scripts.md):

```php
(new yii\web\Application($config))->run();
```

За более подробной документацией о настройках свойства `components` в Конфигурации приложения обратитесь к главам
[приложения](structure-applications.md) и [Service Locator](concept-service-locator.md).


### Конфигурации виджетов <a name="widget-configurations"></a>

При использовании [виджетов](structure-widgets.md) часто возникает необходимость изменить параметры виджета с помощью
Конфигурации. Для создания виджета можно использовать два метода: [[yii\base\Widget::widget()]] и 
[[yii\base\Widget::beginWidget()]]. Оба метода принимают Конфигурацию в виде PHP массива:

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

Данный код создает виджет `Menu` и устанавливает параметр виджета `activeItems` в значение false.
Также устанавливается параметр `items`, состоящий из элементов меню.

Обратите внимание что параметр `class` НЕ передается, так как полное имя уже указано.


## Конфигурационные файлы <a name="configuration-files"></a>

Если Конфигурация очень сложная, то её, как правило, разделяют по нескольким PHP файлам. Такие файлы называют
*Конфигурационными файлами*. Конфигурационный файл возвращает массив PHP являющийся Конфигурацией.
Например, Конфигурацию приложения можно хранить в отдельном файле `web.php`, как показано ниже:

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require(__DIR__ . '/../vendor/yiisoft/extensions.php'),
    'components' => require(__DIR__ . '/components.php'),
];
```

Параметр `components` также имеет сложную конфигурацию, поэтому можно его хранить в файле `components.php`
и подключать в файл `web.php` используя `require` как и показано выше. 
Содержимое файла `components.php`:

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

Чтобы получить Конфигурацию, хранящуюся в файле, достаточно подключить файл с помощью `require`:

```php
$config = require('path/to/web.php');
(new yii\web\Application($config))->run();
```


## Значения Конфигурации по-умолчанию <a name="default-configurations"></a>

Метод [[Yii::createObject()]] реализован с использованием [dependency injection container](concept-di-container.md).
Это позволяет задавать так называемые *значения Конфигурации по-умолчанию*, которые будут применены ко ВСЕМ экземплярам классов во время их инициализации методом [[Yii::createObject()]]. Значения Конфигурации по-умолчанию указываются с помощью метода `Yii::$container->set()` на этапе [предварительной загрузки](runtime-bootstrapping.md).

Например, если мы хотим изменить виджет [[yii\widgets\LinkPager]] так, чтобы все виджеты данного вида показывали максимум
5 кнопок на странице вместо 10 (как это установлено изначально), можно использовать следующий код:

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```

Без использования значений Конфигурации по-умолчанию, при использовании LinkPager,  Вам пришлось бы каждый раз
задавать значение `maxButtonCount`.


## Константы окружения <a name="environment-constants"></a>

Конфигурации могут различаться в зависимости от режима, в котором происходит запуск приложения. Например,
в режиме разработчика(development) вы используете базу данных `mydb_dev`, а в эксплуатационном(production) режиме базу данных
`mydb_prod`. Для упрощения смены окружений в Yii существует константа `YII_ENV`.  Вы можете указать её во 
[входном скрипте](structure-entry-scripts.md) своего приложения:

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

`YII_ENV` может принимать следующие значения:

- `prod`: контекст production, т.е. эксплуатационный режим сервера. Константа `YII_ENV_PROD` установлена в true.
   Значение по умолчанию.
- `dev`: контекст development, т.е. режим для разработки. Константа `YII_ENV_DEV` установлена в true.
- `test`: контекст testing, т.е. режим для тестирования. Константа `YII_ENV_TEST` установлена в true.

Используя эти константы, вы можете задать в Конфигурации значения параметров зависящие от текущего режима.
Например, чтобы включить [отладочную панель и отладчик](tool-debugger.md) в эксплуатационном(development) режиме, Вы можете использовать следующий код в Конфигурации приложения: 

```php
$config = [...];

if (YII_ENV_DEV) {
    // значения параметров Конфигурации для режима разработки('dev')
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
