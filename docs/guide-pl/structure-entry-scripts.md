Skrypty wejściowe
=================

Skrypty wejściowe są pierwszym krokiem procesu bootstrapowania aplikacji. Aplikacja (zarówno Web
jak i konsolowa) posiada pojedynczy skrypt wejściowy. Użytkownicy końcowi wysyłają żądania do skryptów 
wejściowych, które inicjują instancje aplikacji i przekazują do nich te żądania.

Skrypty wejściowe dla aplikacji Web muszą znajdować się w folderach dostępnych dla Web, aby użytkownicy końcowi mogli je wywołać.
Zwykle nazywane są `index.php`, ale mogą mieć inne nazwy pod warunkiem, że serwery Web potrafią je zlokalizować.

Skrypty wejściowe dla aplikacji konsolowych trzymane są zwykle w [ścieżce głównej](structure-applications.md)
aplikacji i nazywane `yii` (z sufiksem `.php`). Powinny być wykonywalne, aby użytkownicy 
mogli uruchomić aplikacje konsolowe za pomocą komendy `./yii <ścieżka> [argumenty] [opcje]`.

Skrypty wejściowe wykonują głównie następującą pracę:

* Definiują globalne stałe,
* Rejestrują [autoloader Composera](https://getcomposer.org/doc/01-basic-usage.md#autoloading),
* Dołączają plik klasy [[Yii]],
* Ładują konfigurację aplikacji,
* Tworzą i konfigurują instancję [aplikacji](structure-applications.md),
* Wywołują [[yii\base\Application::run()|run()]], aby przetworzyć wysłane żądanie.


## Aplikacje Web <span id="web-applications"></span>

Poniżej znajdziesz kod skryptu wejściowego dla [Podstawowego projektu szablonu Web](start-installation.md).

```php
<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// register Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// include Yii class file
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

// load application configuration
$config = require __DIR__ . '/../config/web.php';

// create, configure and run application
(new yii\web\Application($config))->run();
```


## Aplikacje konsoli <span id="console-applications"></span>

Podobnie poniżej kod skryptu wejściowego dla aplikacji konsolowej:

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

// register Composer autoloader
require __DIR__ . '/vendor/autoload.php';

// include Yii class file
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

// load application configuration
$config = require __DIR__ . '/config/console.php';

$application = new yii\console\Application($config);
$exitCode = $application->run();
exit($exitCode);
```


## Definiowanie stałych <span id="defining-constants"></span>

Skrypty wejściowe są najlepszym miejscem do definiowania globalnych stałych. Yii wspiera następujące trzy stałe:

* `YII_DEBUG`: określa czy aplikacja działa w trybie debugowania. Podczas tego trybu aplikacja 
  przetrzymuje więcej informacji w logach i zdradza szczegóły stosu błędów, kiedy rzucony jest wyjątek. Z tego powodu
  tryb debugowania powinien być używany głównie podczas fazy deweloperskiej. Domyślną wartością `YII_DEBUG` jest `false`.
* `YII_ENV`: określa środowisko, w którym aplikacja działa. Opisane jest to bardziej szczegółowo 
  w sekcji [Konfiguracje](concept-configurations.md#environment-constants).
  Domyślną wartością `YII_ENV` jest `'prod'`, co oznacza, że aplikacja jest uruchomiona w środowisku produkcyjnym.
* `YII_ENABLE_ERROR_HANDLER`: określa czy uruchomić obsługę błędów przygotowaną przez Yii. Domyślną wartością tej stałej jest `true`.

Podczas definiowania stałej często korzystamy z poniższego kodu:

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

co odpowiada:

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

Jak widać pierwszy sposób jest bardziej zwięzły i łatwiejszy do zrozumienia.

Definiowanie stałych powinno odbyć się na samym początku skryptu wejściowego, aby odniosło skutek podczas dołączania pozostałych plików PHP.
