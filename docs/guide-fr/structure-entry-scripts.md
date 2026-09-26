Scripts d'entrée
=============

Le script d'entrée est le premier rencontré dans le processus d'amorçage de l'application. Une application (qu'elle
soit une application Web ou une application console) a un unique script d'entrée. Les utilisateurs font des 
requêtes au script d'entrée qui instancie un objet *Application*  et lui transmet les requêtes.

Les scripts d'entrée des applications Web doivent être placés dans des dossiers accessibles par le Web pour que les 
utilisateurs puissent y accéder. Ils sont souvent nommés `index.php`, mais peuvent également avoir tout autre nom,
du moment que les serveurs Web peuvent les trouver.

Les scripts d'entrée des applications console sont généralement placés dans le [répertoire de base](structure-applications.md)
des applications et sont nommés `yii` (avec le suffixe `.php`). Ils doivent être rendus exécutables afin que les 
utilisateurs puissent lancer des applications console grâce à la commande `./yii <route> [arguments] [options]`.

Les scripts d'entrée effectuent principalement les tâches suivantes :

* Définir des constantes globales;
* Enregistrer le [chargeur automatique Composer](https://getcomposer.org/doc/01-basic-usage.md#autoloading);
* Inclure le fichier de classe de [[Yii]];
* Charger la configuration de l'application;
* Créer et configurer une instance d'[application](structure-applications.md);
* Appeler [[yii\base\Application::run()]] pour traiter la requête entrante.


## Applications Web <span id="web-applications"></span>

Ce qui suit est le code du script d'entrée du [Modèle Basique d'Application Web](start-installation.md).

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


## Applications Console <span id="console-applications"></span>

De même, le code qui suit est le code du script de démarrage d'une application console :

```php
#!/usr/bin/env php
<?php
/**
 * Yii console bootstrap file.
 *
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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


## Définir des Constantes <span id="defining-constants"></span>

Les scripts d'entrée sont l'endroit idéal pour définir des constantes globales. Yii prend en charge les trois constantes suivantes :

* `YII_DEBUG` : spécifie si une application tourne en mode de débogage. Si elle est en mode de débogage, une 
  application enregistrera des journaux plus détaillés, et révélera des piles d'appels d'erreurs détaillées si des exceptions
  sont levées. C'est pour cette raison que le mode de débogage doit être utilisé principalement pendant la phase
  de développement. La valeur par défaut de `YII_DEBUG` est `false` (faux).
* `YII_ENV` : spécifie dans quel environnement l'application est en train de tourner. Cela est décrit plus en détails
  dans la section [Configurations](concept-configurations.md#environment-constants). La valeur par défaut de `YII_ENV` 
  est `'prod'`, ce qui signifie que l'application tourne dans l'environnement de production.
* `YII_ENABLE_ERROR_HANDLER` : spécifie si le gestionnaire d'erreurs fourni par Yii doit être activé. La valeur par 
  défaut de cette constante est `true` (vrai).

Quand on définit une constante, on utilise souvent le code suivant :

```php
defined('YII_DEBUG') or define('YII_DEBUG', true);
```

qui est l'équivalent du code suivant :

```php
if (!defined('YII_DEBUG')) {
    define('YII_DEBUG', true);
}
```

Clairement, le premier est plus succinct et plus aisé à comprendre.

Les définitions de constantes doivent être faites au tout début d'un script d'entrée pour qu'elles puissent prendre 
effet quand d'autres fichiers PHP sont inclus.
