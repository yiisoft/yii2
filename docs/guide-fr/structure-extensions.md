Extensions
==========

Les extensions sont des paquets logiciels distribuables, spécialement conçus pour être utilisés dans des applications, et qui procurent des fonctionnalités prêtes à l'emploi. Par exemple, l'extension [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug) ajoute une barre de débogage très pratique au pied de chaque page dans votre application pour vous aider à comprendre plus aisément comment les pages sont générées. Vous pouvez utiliser des extensions pour accélérer votre processus de développement. Vous pouvez aussi empaqueter votre code sous forme d'extensions pour partager votre travail avec d'autres personnes.

> Info: nous utilisons le terme "extension" pour faire référence à des paquets logiciels spécifiques à Yii. Quant aux paquets à but plus général, qui peuvent être utilisés en dehors de Yii, nous y faisons référence en utilisant les termes « paquet » ou « bibliothèque ». 


## Utilisation des extensions <span id="using-extensions"></span>

Pour utiliser une extension, vous devez d'abord l'installer. La plupart des extensions sont distribuées en tant que paquets [Composer](https://getcomposer.org/) qui peuvent être installés en suivant les deux étapes suivantes : 

1. Modifier le fichier `composer.json` de votre application et spécifier quelles extensions (paquets Composer) vous désirez installer.
2. Exécuter la commande `composer install` pour installer les extensions spécifiées.

Notez que devez installer [Composer](https://getcomposer.org/) si vous ne l'avez pas déjà fait.

Par défaut, Composer installe les paquets enregistrés sur [Packagist](https://packagist.org/) — le plus grand dépôt pour les paquets Composer Open Source. Vous pouvez rechercher des extensions sur Packagist. Vous pouvez aussi [créer votre propre dépôt](https://getcomposer.org/doc/05-repositories.md#repository) et configurer Composer pour l'utiliser. Ceci est utile si vous développez des extensions privées que vous ne voulez partager que dans vos propres projets seulement.

Les extensions installées par Composer sont stockées dans le dossier `BasePath/vendor`, où `BasePath` fait référence au [chemin de base](structure-applications.md#basePath) de l'application.  Comme Composer est un gestionnaire de dépendances, quand il installe un paquet, il installe aussi automatiquement tous les paquets dont le paquet dépend. 

Par exemple, pour installer l'extension `yiisoft/yii2-imagine`, modifier votre fichier `composer.json` comme indiqué ci-après :

```json
{
    // ...

    "require": {
        // ... autres dépendances

        "yiisoft/yii2-imagine": "~2.0.0"
    }
}
```

Après l'installation, vous devriez apercevoir le dossier  `yiisoft/yii2-imagine` dans le dossier `BasePath/vendor`. Vous devriez également apercevoir un autre dossier `imagine/imagine` contenant les paquets dont l'extension dépend et qui ont été installés.

> Info: l'extension `yiisoft/yii2-imagine` est une extension du noyau développée et maintenue par l'équipe de développement de Yii. Toutes les extensions du noyau sont hébergées sur [Packagist](https://packagist.org/) et nommées selon le format `yiisoft/yii2-xyz`, où `xyz` varie selon l'extension. 
  
Vous pouvez désormais utiliser les extensions installées comme si elles faisaient partie de votre application. L'exemple suivant montre comment vous pouvez utiliser la classe `yii\imagine\Image` que l'extension `yiisoft/yii2-imagine` fournit :

```php
use Yii;
use yii\imagine\Image;

// generate a thumbnail image
Image::thumbnail('@webroot/img/test-image.jpg', 120, 120)
    ->save(Yii::getAlias('@runtime/thumb-test-image.jpg'), ['quality' => 50]);
```

> Info: les classes d'extension sont chargées automatiquement par  la [classe de chargement automatique de Yii (*autoloader*)](concept-autoloading.md).


### Installation manuelle d'extensions <span id="installing-extensions-manually"></span>

Dans quelques cas rares, vous désirez installer quelques, ou toutes les, extensions manuellement, plutôt que de vous en remettre à Composer. Pour le faire, vous devez :

1. Télécharger les archives des extensions et les décompresser dans le dossier `vendor`.
2. Installer la classe *autoloader* procurée par les extensions, si elles en possèdent.
3. Télécharger et installer toutes les extensions dont vos extensions dépendent selon les instructions.
 
Si une extension ne possède pas de classe *autoloader* mais obéit à la [norme PSR-4](http://www.php-fig.org/psr/psr-4/), vous pouvez utiliser la classe *autoloader* procurée par Yii pour charger automatiquement les classes d'extension. Tout ce que vous avez à faire, c'est de déclarer un [alias racine](concept-aliases.md#defining-aliases) pour le dossier racine de l'extension. Par exemple, en supposant que vous avez installé une extension dans le dossier `vendor/mycompany/myext`, et que les classes d'extension sont sous l'espace de noms `myext`, alors vous pouvez inclure le code suivant dans la configuration de votre application :

```php
[
    'aliases' => [
        '@myext' => '@vendor/mycompany/myext',
    ],
]
```


## Création d'extensions <span id="creating-extensions"></span>

Vous pouvez envisager de créer une extension lorsque vous ressentez l'envie de partager votre code avec d'autres personnes. Une extension pour contenir n'importe quel code à votre goût, comme une classe d'aide, un objet graphique, un module, etc.

Il est recommandé de créer une extension sous la forme d'un [paquet Composer](https://getcomposer.org/) de façon à ce qu'elle puisse être installée facilement par d'autres utilisateurs, comme nous l'avons expliqué dans la sous-section précédente. 

Ci-dessous, nous présentons les étapes de base à suivre pour créer une extension en tant que paquet Composer. 

1. Créer un projet pour votre extension et l'héberger dans un dépôt VCS, tel que [github.com](https://github.com). Le travail de développement et de maintenance pour cette extension doit être fait sur ce dépôt. 
2. Dans le dossier racine du projet, créez un fichier nommé `composer.json` comme le réclame Composer. Reportez-vous à la sous-section suivante pour plus de détails.
3. Enregistrez votre extension dans un dépôt Composer tel que [Packagist](https://packagist.org/), afin que les autres utilisateurs puissent la trouver et l'installer avec Composer.


### `composer.json` <span id="composer-json"></span>

Tout paquet Composer doit disposer d'un fichier `composer.json` dans son dossier racine. Ce fichier contient les méta-données à propos du paquet. Vous pouvez trouver une spécification complète de ce fichier dans le [manuel de Composer](https://getcomposer.org/doc/01-basic-usage.md#composer-json-project-setup).
L'exemple suivant montre le fichier `composer.json` de l'extension `yiisoft/yii2-imagine` :

```json
{
    // package name (nom du paquet)
    "name": "yiisoft/yii2-imagine",

    // package type (type du paquet)
    "type": "yii2-extension",

    "description": "l'intégration d'Imagine pour le framework Yii ",
    "keywords": ["yii2", "imagine", "image", "helper"],
    "license": "BSD-3-Clause",
    "support": {
        "issues": "https://github.com/yiisoft/yii2/issues?labels=ext%3Aimagine",
        "forum": "http://www.yiiframework.com/forum/",
        "wiki": "http://www.yiiframework.com/wiki/",
        "irc": "irc://irc.freenode.net/yii",
        "source": "https://github.com/yiisoft/yii2"
    },
    "authors": [
        {
            "name": "Antonio Ramirez",
            "email": "amigo.cobos@gmail.com"
        }
    ],

    // dépendances du paquet
    "require": {
        "yiisoft/yii2": "~2.0.0",
        "imagine/imagine": "v0.5.0"
    },

    // class autoloading specs
    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```


#### Nommage des paquets <span id="package-name"></span>

Chaque paquet Composer doit avoir un nom de paquet qui le distingue des autres paquets. Le format d'un nom de paquet est  `vendorName/projectName`. Par exemple, dans le nom de paquet `yiisoft/yii2-imagine`, le nom de vendeur et le nom du projet sont, respectivement, `yiisoft` et`yii2-imagine`.

N'utilisez PAS  `yiisoft` comme nom de vendeur car il est réservé pour le noyau de Yii. 

Nous recommandons que vous préfixiez votre nom de projet par `yii2-` pour les paquets qui sont des extensions de Yii, par exemple,`myname/yii2-mywidget`. Cela permet aux utilisateurs de distinguer plus facilement les extensions de Yii 2. 

#### Types de paquet <span id="package-type"></span>

Il est important de spécifier le type de paquet de votre extension comme `yii2-extension` afin que le paquet puisse être reconnu comme une extension de Yii lors de son installation. 

Losqu'un utilisateur exécute `composer install` pour installer une extension, le fichier  `vendor/yiisoft/extensions.php` est automatiquement mis à jour pour inclure les informations sur la nouvelle extension. Grâce à  ce fichier, les application Yii peuvent connaître quelles extensions sont installées (l'information est accessible via [[yii\base\Application::extensions]]).


#### Dépendances <span id="dependencies"></span>

Bien sûr, votre extension dépend de Yii. C'est pourquoi, vous devez lister  (`yiisoft/yii2`) dans l'entrée `require` dans `composer.json`. Si votre extension dépend aussi d'autres extensions ou bibliothèques de tierces parties, vous devez les lister également. Assurez-vous que vous de lister également les contraintes de versions appropriées (p. ex. `1.*`, `@stable`) pour chacun des paquets dont votre extension dépend. Utilisez des dépendances stables lorsque votre extension est publiée dans une version stable. 

La plupart des paquets JavaScript/CSS sont gérés par [Bower](http://bower.io/) et/ou [NPM](https://www.npmjs.org/),
plutôt que par Composer. Yii utilise le [greffon *assets* de Composer(https://github.com/francoispluchino/composer-asset-plugin) pour activer la gestion de ce genre de paquets par Composer. Si votre extension dépend d'un paquet Bower, vous pouvez simplement lister la dépendance dans  `composer.json` comme ceci : 

```json
{
    // paquets dépendances
    "require": {
        "bower-asset/jquery": ">=1.11.*"
    }
}
```

Le code ci-dessus établit que l'extension dépend de paquet Bower `jquery`. En général, vous pouvez utiliser le nom `bower-asset/PackageName` — où `PackageName` est le nom du paquet — pour faire référence à un paquet Bower dans `composer.json`, et utiliser `npm-asset/PackageName` pour faire référence à un paquet NPM. Quand Composer installe un paquet Bower ou NPM, par défaut, le contenu du paquet est installé dans le dossier `@vendor/bower/PackageName` ou `@vendor/npm/Packages`, respectivement. On peut aussi faire référence à ces dossier en utilisant les alias plus courts  `@bower/PackageName` et `@npm/PackageName`.

Pour plus de détails sur la gestion des ressources, reportez-vous à la section sur  les [Ressources](structure-assets.md#bower-npm-assets).


#### Chargement automatique des classes <span id="class-autoloading"></span>

Afin que vos classes soient chargées automatiquement par la classe *autoloader* de Yii ou celle de Composer, vous devez spécifier l'entrée  `autoload` dans le fichier `composer.json`, comme précisé ci-après :

```json
{
    // ....

    "autoload": {
        "psr-4": {
            "yii\\imagine\\": ""
        }
    }
}
```

Vous pouvez lister un ou plusieurs espaces de noms racines et leur chemin de fichier correspondant.

Lorsque l'extension est installée dans une application, Yii crée un [alias](concept-aliases.md#extension-aliases) pour chacun des espaces de noms racines. Cet alias fait référence au dossier correspondant à l'espace de noms. Par exemple, la déclaration `autoload` ci-dessus correspond à un alias nommé `@yii/imagine`.


### Pratiques recommandées <span id="recommended-practices"></span>

Parce que les extensions sont prévues pour être utilisées par d'autres personnes, vous avez souvent besoin de faire un effort supplémentaire pendant le développement. Ci-dessous nous introduisons quelques pratiques courantes et recommandées pour créer des extensions de haute qualité. 


#### Espaces de noms <span id="namespaces"></span>

Pour éviter les collisions de noms et rendre le chargement des classes de votre extension automatique, vous devez utiliser des espaces de noms et nommer les classes de votre extension en respectant la [norme PSR-4](http://www.php-fig.org/psr/psr-4/) ou la [norme PSR-0](http://www.php-fig.org/psr/psr-0/).

Vos noms de classe doivent commencer par  `vendorName\extensionName`, où `extensionName` est similaire au nom du projet dans le nom du paquet sauf qu'il doit contenir le préfixe `yii2-`. Par exemple, pour l'extension `yiisoft/yii2-imagine`, nous utilisons l'espace de noms `yii\imagine` pour ses classes. 

N'utilisez pas `yii`, `yii2` ou `yiisoft` en tant que nom de vendeur. Ces noms sont réservés au code du noyau de Yii.


#### Classes d'amorçage <span id="bootstrapping-classes"></span>

Parfois, vous désirez que votre extension exécute un certain code durant le [processus d'amorçage](runtime-bootstrapping.md) d'une application. Par exemple, votre extension peut vouloir répondre à l'événement `beginRequest` pour ajuster quelques réglages d'environnement. Bien que vous puissiez donner des instructions aux utilisateurs de l'extension pour qu'ils attachent explicitement votre gestionnaire d'événement dans l'extension à l'événement `beginRequest`, c'est mieux de le faire automatiquement.

Pour ce faire, vous pouvez créer une classe dite *classe du processus d'amorçage* en implémentant l'interface [[yii\base\BootstrapInterface]].
Par exemple :

```php
namespace myname\mywidget;

use yii\base\BootstrapInterface;
use yii\base\Application;

class MyBootstrapClass implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function () {
             // do something here
        });
    }
}
```
ensuite, listez cette classe dans le fichier `composer.json` de votre extension de cette manière :

```json
{
    // ...

    "extra": {
        "bootstrap": "myname\\mywidget\\MyBootstrapClass"
    }
}
```

Lorsque l'extension est installée dans l'application, Yii instancie automatiquement la classe d'amorçage et appelle sa méthode [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] durant le processus de démarrage pour chacune des requêtes. 


#### Travail avec des bases de données <span id="working-with-databases"></span>

Votre extension peut avoir besoin d'accéder à des bases de données. Ne partez pas du principe que les applications qui utilisent votre extension utilisent toujours `Yii::$db` en tant que connexion à la base de données. Déclarez plutôt une propriété `db` pour les classes qui requièrent un accès à une base de données. Cette propriété permettra aux utilisateurs de votre extension de personnaliser la connexion qu'ils souhaitent que votre extension utilise. Pour un exemple, reportez-vous à la classe [[yii\caching\DbCache]] et voyez comment elle déclare et utilise la propriété`db`.

Si votre extension a besoin de créer des tables de base de données spécifiques, ou de faire des changements dans le schéma de la base de données, vous devez :

- fournir des [migrations](db-migrations.md) pour manipuler le schéma de base de données, plutôt que d'utiliser des fichiers SQL ;
- essayer de rendre les migrations applicables à différents systèmes de gestion de bases de données ; 
- éviter d'utiliser  [Active Record](db-active-record.md) dans les migrations.


#### Utilisation des ressources <span id="using-assets"></span>

Si votre extension est un objet graphique ou un module, il est probable qu'elle ait besoin de quelques [ressources](structure-assets.md) pour fonctionner. Par exemple, un module peut afficher quelques pages qui contiennent des images, du code JavaScript et/ou CSS. Comme les fichiers d'une extension sont tous dans le même dossier, qui n'est pas accessible depuis le Web lorsque l'extension est installée dans une application, vous avez deux possibilités pour rendre ces ressources accessibles depuis le Web. 

- demander aux utilisateurs de l'extension de copier les ressources manuellement dans un dossier spécifique accessible depuis le Web ; 
- déclarer un [paquet de ressources](structure-assets.md) et compter sur le mécanisme de publication automatique des ressources pour copier les fichiers listés dans le paquet de ressources dans un dossier accessible depuis le Web. 

Nous recommandons la deuxième approche de façon à ce que votre extension puisse être plus facilement utilisée par d'autres personnes. Reportez-vous à la section [Ressources](structure-assets.md) pour plus de détails sur la manière de travailler avec des ressources en général. 


#### Internationalisation et Localisation <span id="i18n-l10n"></span>

Votre extension peut être utilisée par des applications prenant en charge différentes langues ! Par conséquent, si votre extension affiche des contenus pour l'utilisateur final, vous devez essayer de traiter à la fois [internationalisation et localisation](tutorial-i18n.md). Plus spécialement :

- Si l'extension affiche des messages pour l'utilisateur final, les messages doivent être enveloppés dans la méthode `Yii::t()` afin de pouvoir être traduits. Les messages à l'attention des développeurs (comme les messages d'exceptions internes) n'ont pas besoin d'être traduits. 
-Si l'extension affiche des nombres, des dates, etc., ils doivent être formatés en utilisant [[yii\i18n\Formatter]] avec les règles de formatage appropriées. 

Pour plus de détails, reportez-vous à la section [Internationalisation](tutorial-i18n.md).


#### Tests <span id="testing"></span>

Vous souhaitez que votre extension s'exécute sans créer de problème à ses utilisateurs. Pour atteindre ce but vous devez la tester avant de la publier. 

Il est recommandé que créiez des cas de test variés pour tester votre extension plutôt que de vous fier à des tests manuels. À chaque fois que vous vous apprêterez à publier une nouvelle version de votre extension, vous n'aurez plus qu'à exécuter ces cas de test pour garantir que tout est en ordre. Yii fournit une prise en charge des tests qui peut vous aider à écrire facilement des unités de test, des tests d'acceptation et des tests de fonctionnalités. Pour plus de détails, reportez-vous à la section [Tests](test-overview.md).


#### Numérotation des versions <span id="versioning"></span>

Vous devriez donner à chacune des versions publiées de votre extension un numéro (p. ex. `1.0.1`). Nous recommandons de suivre la pratique de la [numérotation sémantique des versions](http://semver.org) lors de la détermination d'un numéro de version. 


#### Publication <span id="releasing"></span>

Pour permettre aux autres personnes de connaître votre extension, vous devez la publier. Si c'est la première fois que vous publiez l'extension, vous devez l'enregistrer sur un dépôt Composer tel que [Packagist](https://packagist.org/). Ensuite, tout ce que vous avez à faire, c'est de créer une balise de version (p. ex. `v1.0.1`) sur le dépôt VCS de votre extension et de notifier au dépôt Composer la nouvelle version. Les gens seront capables de trouver votre nouvelle version et, soit de l'installer, soit de la mettre à jour via le dépôt Composer. 

Dans les versions de votre extension, en plus des fichiers de code, vous devez envisager d'inclure ce qui suit par aider les gens à connaître votre extension et à l'utiliser :
* Un ficher *readme* (lisez-moi) dans le dossier racine du paquet : il doit décrire ce que fait votre extension, comment l'installer et l'utiliser. Nous vous recommandons de l'écrire dans le format [Markdown](http://daringfireball.net/projects/markdown/) et de nommer ce fichier `readme.md`.
* Un fichier *changelog* (journal des modifications) dans le dossier racine du paquet : il liste les changements apportés dans chacune des versions. Ce fichier peut être écrit dans le format Markdown et nommé `changelog.md`.
* Un fichier *upgrade* (mise à jour) dans le dossier racine du paquet : il donne les instructions sur la manière de mettre l'extension à jour en partant d'une version précédente.   Ce fichier peut être écrit dans le format Markdown et nommé `upgrade.md`.
* Tutorials, demos, screenshots, etc.: ces derniers sont nécessaires si votre extension fournit de nombreuses fonctionnalités qui ne peuvent être couvertes dans le fichier readme. 
* Une documentation de l'API : votre code doit être bien documenté pour permettre aux autres personnes de le lire plus facilement et de le comprendre. Vous pouvez faire référence au [fichier de la classe BaseObject](https://github.com/yiisoft/yii2/blob/master/framework/base/BaseObject.php) pour savoir comment documenter votre code. 
 
> Info: les commentaires de votre code peuvent être écrits dans le format Markdown. L'extension `yiisoft/yii2-apidoc` vous fournit un outil pour générer une documentation d'API agréable et basée sur les commentaires de votre code. 

> Info: bien que cela ne soit pas une exigence, nous suggérons que votre extension respecte un certain style de codage. Vous pouvez vous reporter au document [style du codage du noyau du framework](https://github.com/yiisoft/yii2/wiki/Core-framework-code-style).


## Extensions du noyau <span id="core-extensions"></span>

Yii fournit les extensions du noyau suivantes qui sont développées et maintenues par l'équipe de développement de Yii. Elles sont toutes enregistrées sur[Packagist](https://packagist.org/) et peuvent être facilement installées comme décrit dans la sous-section [Utilisation des extensions](#using-extensions).

- [yiisoft/yii2-apidoc](https://github.com/yiisoft/yii2-apidoc) : fournit un générateur  d'API extensible et de haute performance. Elle est aussi utilisée pour générer l'API du noyau du framework.  
- [yiisoft/yii2-authclient](https://github.com/yiisoft/yii2-authclient) : fournit un jeu de clients d'authentification courants tels que  Facebook OAuth2 client, GitHub OAuth2 client.
- [yiisoft/yii2-bootstrap](https://github.com/yiisoft/yii2-bootstrap) : fournit un jeu d'objets graphiques qui encapsulent les composants et les greffons de [Bootstrap](http://getbootstrap.com/).
- [yiisoft/yii2-codeception](https://github.com/yiisoft/yii2-codeception): fournit la prise en charge des fonctionnalités de test basées sur [Codeception](http://codeception.com/).
- [yiisoft/yii2-debug](https://github.com/yiisoft/yii2-debug): fournit la prise en charge du débogage des applications Yii. Lorsque cette extension est utilisée, une barre de débogage apparaît au pied de chacune des pages. Cette extension fournit aussi un jeu de pages autonomes pour afficher des informations de débogage plus détaillées. 
- [yiisoft/yii2-elasticsearch](https://github.com/yiisoft/yii2-elasticsearch) : fournit la prise en charge d'[Elasticsearch](http://www.elasticsearch.org/). Elle inclut un moteur de requêtes/recherches de base et met en œuvre le motif [Active Record](db-active-record.md) qui permet de stocker des enregistrement actifs dans Elasticsearch.
- [yiisoft/yii2-faker](https://github.com/yiisoft/yii2-faker) : fournit la prise en charge de [Faker](https://github.com/fzaninotto/Faker) pour générer des données factices pour vous.
- [yiisoft/yii2-gii](https://github.com/yiisoft/yii2-gii) : fournit un générateur de code basé sur le Web qui est hautement extensible et peut être utilisé pour générer rapidement des modèles, des formulaires, des modules, des requêtes CRUD, etc. 
- [yiisoft/yii2-httpclient](https://github.com/yiisoft/yii2-httpclient): provides an HTTP client.
- [yiisoft/yii2-imagine](https://github.com/yiisoft/yii2-imagine) : fournit des fonctionnalités couramment utilisées de manipulation d'images basées sur [Imagine](http://imagine.readthedocs.org/).
- [yiisoft/yii2-jui](https://github.com/yiisoft/yii2-jui) : fournit un jeu d'objets graphiques qui encapsulent les interactions et les objets graphiques de [JQuery UI](http://jqueryui.com/).
- [yiisoft/yii2-mongodb](https://github.com/yiisoft/yii2-mongodb) : fournit la prise en charge de [MongoDB](http://www.mongodb.org/). Elle inclut des fonctionnalités telles que les requêtes de base, les enregistrements actifs, les migrations, la mise en cache, la génération de code, etc.
- [yiisoft/yii2-redis](https://github.com/yiisoft/yii2-redis) : fournit la prise en charge de [redis](http://redis.io/). Elle inclut des fonctionnalités telles que les requêtes de base, les enregistrements actifs, la mise en cache, etc.
- [yiisoft/yii2-smarty](https://github.com/yiisoft/yii2-smarty) : fournit un moteur de modèles basé sur  [Smarty](http://www.smarty.net/).
- [yiisoft/yii2-sphinx](https://github.com/yiisoft/yii2-sphinx) : fournit la prise en charge de [Sphinx](http://sphinxsearch.com). Elle inclut des fonctionnalités telles que les requêtes de base, les enregistrements actifs, la génération de code, etc.
- [yiisoft/yii2-swiftmailer](https://github.com/yiisoft/yii2-swiftmailer) : fournit les fonctionnalités d'envoi de courriels basées sur [swiftmailer](http://swiftmailer.org/).
- [yiisoft/yii2-twig](https://github.com/yiisoft/yii2-twig) : fournit un moteur de modèles basé sur [Twig](http://twig.sensiolabs.org/).
