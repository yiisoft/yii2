Настройки
========

Настройки широко используются в Yii при создании новых объектов или при инициализации уже существующих объектов. 
Обычно настройки включают в себя названия классов создаваемых объектов и список первоначальных значений,
которые должны быть присвоены [свойствам](concept-properties.md) объекта. Также в настройках можно указать список
[обработчиков событий](concept-events.md) объекта, и/или список [поведений](concept-behaviors.md) объекта.

Пример настроек подключения к базе данных и дальнейшей инициализации подключения: 

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

Метод [[Yii::createObject()]] принимает в качестве аргумента массив настроек и создаёт объект указанного в них класса.
При этом оставшаяся часть конфигурации используется для инициализации свойств, обработчиков событий и поведений объекта.

Если объект уже создан, вы можете использовать [[Yii::configure()]] для того, чтобы инициализировать свойства объекта
массивом настроек:

```php
Yii::configure($object, $config);
```

Обратите внимание, что в этом случае массив с настройками не должен содержать ключ `class`.


## Формат настроек <a name="configuration-format"></a>

Формат настроек выглядит следующим образом:

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
* Элементы `on eventName` указывают какие обработчики должны быть установлены для [событий](concept-events.md) объекта.
  Обратите внимание, что ключи массива начинаются с `on `. Чтобы узнать весь список поддерживаемых видов
  обработчиков событий обратитесь в раздел [события](concept-events.md)
* Элементы `as behaviorName` указывают какие [поведения](concept-behaviors.md) должны быть установлены для объекта.
  Обратите внимание, что ключи массива начинаются с `as `. `$behaviorConfig` это массив для настройки
  поведения, этот массив такой же как тот, о котором идет речь.

Пример настроек с установкой первоначальных значений свойств объекта, обработчика событий и поведения:

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


## Использование настроек <a name="using-configurations"></a>

Возможность настройки широко используется в Yii. В самом начале данной главы мы узнали как
создать объект с необходимыми параметрами используя метод [[Yii::createObject()]].
В данном разделе речь пойдет о настройках приложения и настройках виджетов — двух основных способов
использования настроек. 


### Настройки приложения <a name="application-configurations"></a>

Настройки [приложения](structure-applications.md) пожалуй самые сложные из используемых в фреймворке.
Причина в том, что класс [[yii\web\Application|application]] содержит большое количество настраиваемых
свойств и событий. Более того, свойство приложения [[yii\web\Application::components|components]]
может принимать массив настроек для создания компонентов, регистрируемых на уровне приложения.
Пример настроек приложения для [шаблона приложения basic](start-basic.md).

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

Ключ `class` в данных настройках не указывается. Причина в том, что класс вызывается по полному имени во
[входном скрипте](structure-entry-scripts.md):

```php
(new yii\web\Application($config))->run();
```

Для более подробной документации о настройке свойства приложения `components` обратитесь к главам
[приложения](structure-applications.md) и [Service Locator](concept-service-locator.md).


### Настройки виджетов <a name="widget-configurations"></a>

При использовании [виджетов](structure-widgets.md) часто возникает необходимость изменить параметры виджета с помощью
настроек. Для создания виджета можно использовать два метода: [[yii\base\Widget::widget()]] и 
[[yii\base\Widget::beginWidget()]]. Оба метода принимают настройки в виде массива:

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


## Файлы Настроек <a name="configuration-files"></a>

Если настройки сложные, то их, как правило, разделяют по нескольким PHP файлам. Такие файлы называют
*файлами настроек* или *конфигурационными файлами*. Файл настроек возвращает массив PHP с настройками.
Например, настройки приложения можно хранить в отдельном файле `web.php`, как показано ниже:

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

Чтобы получить настройки, хранимые в файле, достаточно подключить файл с помощью `require`:

```php
$config = require('path/to/web.php');
(new yii\web\Application($config))->run();
```


## Настройки по умолчанию <a name="default-configurations"></a>

Метод [[Yii::createObject()]] реализован с использованием [dependency injection container](concept-di-container.md).
Это позволяет указывать так называемые *настройки по умолчанию*, которые будут применены ко ВСЕМ экземплярам классов
во время их инициализации методом [[Yii::createObject()]]. Настройки по умолчанию указываются с помощью метода
`Yii::$container->set()` на этапе [предварительной загрузки](runtime-bootstrapping.md).

Например, если мы хотим изменить виджет [[yii\widgets\LinkPager]] так, чтобы все виджеты данного вида показывали максимум
5 кнопок на странице вместо 10 (как это установлено изначально), можно использовать следующий код:

```php
\Yii::$container->set('yii\widgets\LinkPager', [
    'maxButtonCount' => 5,
]);
```

Если бы мы не установили настройки по умолчанию, то тогда нам нужно было бы каждый раз при использовании LinkPager
указывать настройки `maxButtonCount`.


## Константы окружения <a name="environment-constants"></a>

Настройки могут различаться в зависимости от окружения, в котором происходит запуск приложения. Например,
в окружении разработчика вы используете базу данных `mydb_dev`, а на продакшн сервере базу данных
`mydb_prod`. Для упрощения смены окружений в Yii существует константа `YII_ENV`.  Вы можете указать её во 
[входном скрипте](structure-entry-scripts.md) своего приложения:

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

`YII_ENV` может принимать следующие значения:

- `prod`: production окружение, т.е. окружение для конечного сервера. Константа `YII_ENV_PROD` установлена в true.
   Значение по умолчанию.
- `dev`: development окружение, т.е. окружение для разработки. Константа `YII_ENV_DEV` установлена в true.
- `test`: testing окружение, т.е. окружения для тестирования. Константа `YII_ENV_TEST` установлена в true.

Используя эти константы вы можете изменить настройки в соответствии с вашим окружением.
Например, чтобы включить [отладочную панель и отладчик](tool-debugger.md) в development окружении вы можете использовать 
следующие настройки приложения: 

```php
$config = [...];

if (YII_ENV_DEV) {
    // настройки для 'dev' окружения
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';
}

return $config;
```
