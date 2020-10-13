Конфигурации
============

Конфигурации широко используются в Yii при создании новых объектов или при инициализации уже существующих объектов. 
Обычно конфигурации включают в себя названия классов создаваемых объектов и список первоначальных значений,
которые должны быть присвоены [свойствам](concept-properties.md) объекта. Также в конфигурациях можно указать список
[обработчиков событий](concept-events.md) объекта и/или список [поведений](concept-behaviors.md) объекта.

Пример конфигурации подключения к базе данных и дальнейшей инициализации подключения: 

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

Метод [[Yii::createObject()]] принимает в качестве аргумента массив с конфигурацией и создаёт объект указанного в них класса.
При этом оставшаяся часть конфигурации используется для инициализации свойств, обработчиков событий и поведений объекта.

Если объект уже создан, вы можете использовать [[Yii::configure()]] для того, чтобы инициализировать свойства объекта
массивом с конфигурацией:

```php
Yii::configure($object, $config);
```

Обратите внимание, что в этом случае массив с конфигурацией не должен содержать ключ `class`.


## Формат конфигурации <span id="configuration-format"></span>

Формат конфигурации выглядит следующим образом:

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
  Обратите внимание, что ключи массива начинаются с `as `; а `$behaviorConfig` представляет собой конфигурацию для
  создания [поведения](concept-behaviors.md), похожую на все остальные конфигурации.

Пример конфигурации с установкой первоначальных значений свойств объекта, обработчика событий и поведения:

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


## Использование конфигурации <span id="using-configurations"></span>

Конфигурации повсеместно используются в Yii. В самом начале данной главы мы узнали как
создать объект с необходимыми параметрами, используя метод [[Yii::createObject()]].
В данном разделе речь пойдет о конфигурации приложения и конфигурациях виджетов — двух основных способов
использования конфигурации. 


### Конфигурация приложения <span id="application-configurations"></span>

Конфигурация [приложения](structure-applications.md), пожалуй, самая сложная из используемых в фреймворке.
Причина в том, что класс [[yii\web\Application|application]] содержит большое количество конфигурируемых
свойств и событий. Более того, свойство приложения [[yii\web\Application::components|components]]
может принимать массив с конфигурацией для создания компонентов, регистрируемых на уровне приложения.
Пример конфигурации приложения для [шаблона приложения basic](start-installation.md).

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
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

Ключ `class` в данной конфигурации не указывается. Причина в том, что класс вызывается по полному имени во
[входном скрипте](structure-entry-scripts.md):

```php
(new yii\web\Application($config))->run();
```

За более подробной документацией о настройках свойства `components` в конфигурации приложения обратитесь к главам
[приложения](structure-applications.md) и [Service Locator](concept-service-locator.md).

Начиная с версии 2.0.11, можно настраивать [контейнер зависимостей](concept-di-container.md) через конфигурацию
приложения. Для этого используется свойство `container`:

```php
$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'container' => [
        'definitions' => [
            'yii\widgets\LinkPager' => ['maxButtonCount' => 5]
        ],
        'singletons' => [
            // Конфигурация для единожды создающихся объектов
        ]
    ]
];
```

Чтобы узнать о возможных значениях `definitions` и `singletons`, а также о реальных примерах использования,
прочитайте подраздел [Более сложное практическое применение](concept-di-container.md#advanced-practical-usage) раздела
[Контейнер внедрения зависимостей](concept-di-container.md).


### Конфигурации виджетов <span id="widget-configurations"></span>

При использовании [виджетов](structure-widgets.md) часто возникает необходимость изменить параметры виджета с помощью
конфигурации. Для создания виджета можно использовать два метода: [[yii\base\Widget::widget()]] и 
[[yii\base\Widget::begin()]]. Оба метода принимают конфигурацию в виде PHP-массива:

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

Данный код создает виджет `Menu` и устанавливает параметр виджета `activeItems` в значение `false`.
Также устанавливается параметр `items`, состоящий из элементов меню.

Обратите внимание, что параметр `class` НЕ передается, так как полное имя уже указано.


## Конфигурационные файлы <span id="configuration-files"></span>

Если конфигурация очень сложная, то её, как правило, разделяют по нескольким PHP-файлам. Такие файлы называют
*конфигурационными файлами*. Конфигурационный файл возвращает массив PHP, являющийся конфигурацией.
Например, конфигурацию приложения можно хранить в отдельном файле `web.php`, как показано ниже:

```php
return [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'components' => require __DIR__ . '/components.php',
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

Чтобы получить конфигурацию, хранящуюся в файле, достаточно подключить файл с помощью `require`:

```php
$config = require 'path/to/web.php';
(new yii\web\Application($config))->run();
```


## Значения конфигурации по умолчанию <span id="default-configurations"></span>

Метод [[Yii::createObject()]] реализован с использованием [контейнера внедрения зависимостей](concept-di-container.md).
Это позволяет задавать так называемые *значения конфигурации по умолчанию*, которые будут применены ко ВСЕМ экземплярам классов во время их инициализации методом [[Yii::createObject()]]. Значения конфигурации по умолчанию указываются с помощью метода `Yii::$container->set()` на этапе [предварительной загрузки](runtime-bootstrapping.md).

Например, если мы хотим изменить виджет [[yii\widgets\LinkPager]] так, чтобы все виджеты данного вида показывали максимум
5 кнопок на странице вместо 10 (как это установлено изначально), можно использовать следующий код:

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```

Без использования значений конфигурации по умолчанию, при использовании LinkPager, вам пришлось бы каждый раз
задавать значение `maxButtonCount`.


## Константы окружения <span id="environment-constants"></span>

Конфигурации могут различаться в зависимости от режима, в котором происходит запуск приложения. Например,
в окружении разработчика (development) вы используете базу данных `mydb_dev`, а в эксплуатационном (production) окружении
базу данных `mydb_prod`. Для упрощения смены окружений в Yii существует константа `YII_ENV`.  Вы можете указать её во 
[входном скрипте](structure-entry-scripts.md) своего приложения:

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

`YII_ENV` может принимать следующие значения:

- `prod`: окружение production, т.е. эксплуатационный режим сервера. Константа `YII_ENV_PROD` установлена в `true`.
   Значение по умолчанию.
- `dev`: окружение development, т.е. режим для разработки. Константа `YII_ENV_DEV` установлена в `true`.
- `test`: окружение testing, т.е. режим для тестирования. Константа `YII_ENV_TEST` установлена в `true`.

Используя эти константы, вы можете задать в конфигурации значения параметров зависящие от текущего окружения.
Например, чтобы включить [отладочную панель и отладчик](tool-debugger.md) в режиме разработки, вы можете использовать
следующий код в конфигурации приложения: 

```php
$config = [...];

if (YII_ENV_DEV) {
    // значения параметров конфигурации для окружения разработки 'dev'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
