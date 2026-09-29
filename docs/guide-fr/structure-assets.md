Ressources
==========

Une ressource dans Yii est un fichier qui peut être référencé dans une page Web. Ça peut être un fichier CSS, un fichier JavaScript, une image, un fichier vidéo, etc. 
Les ressources sont situées dans un dossier accessible du Web et sont servies directement par les serveurs Web.

Il est souvent préférable de gérer les ressources par programmation. 
Par exemple, lorsque vous utilisez l'objet graphique [[yii\jui\DatePicker]] dans une page, il inclut automatiquement les fichiers  CSS et JavaScript dont il a besoin,  au lieu de vous demander de les inclure à la main. 

De plus, lorsque vous mettez à jour l'objet graphique, il utilise une nouvelle version des fichiers de ressources. 
Dans ce tutoriel, nous décrivons les puissantes possibilités de la gestion des ressources de Yii. 


## Paquets de ressources <span id="asset-bundles"></span>

Yii gère les ressources sous forme de *paquets de ressources*. 
Un paquet de ressources est simplement une collection de ressources situées dans un dossier.
Lorsque vous enregistrez un paquet de ressources dans une [vue](structure-views.md), cette vue inclut les fichiers CSS et JavaScript du paquet dans la page Web rendue. 


## Définition de paquets de ressources <span id="defining-asset-bundles"></span>

Les paquets de ressources sont spécifiés comme des classes PHP qui étendent [[yii\web\AssetBundle]]. 
Le nom du paquet est simplement le nom pleinement qualifié de la classe PHP correspondante (sans la barre oblique inversée de tête).
Une classe de paquet de ressources doit être [auto-chargeable](concept-autoloading.md).
Généralement, elle spécifie où les ressources sont situées, quels fichiers CSS et JavaScript le paquet contient, et si le paquet dépend d'autres paquets de ressources. 

Le code suivant définit le paquet de ressources principal utilisé par le [modèle de projet *basic*](start-installation.md):

```php
<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        ['css/print.css', 'media' => 'print'],
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

La classe `AppAsset` ci-dessus spécifie que les fichiers de ressources sont situés dans le dossier `@webroot` qui correspond à l'URL `@web`; 
le paquet contient un unique fichier CSS `css/site.css` et aucun fichier JavaScript ; 
le paquet dépend de deux autres paquets : [[yii\web\YiiAsset]] et [[yii\bootstrap\BootstrapAsset]]. 
Des explications plus détaillées sur les propriétés d'[[yii\web\AssetBundle]] sont disponibles dans les ressources suivantes :

* [[yii\web\AssetBundle::sourcePath|sourcePath]] (chemin des sources): spécifie le dossier racine qui contient les fichiers de ressources dans ce paquet. 
Cette propriété doit être définie si le dossier 
racine n'est pas accessible du Web. 
Autrement, vous devez définir les propriétés [[yii\web\AssetBundle::basePath|basePath]] et  [[yii\web\AssetBundle::baseUrl|baseUrl]]. Des [alias de chemin](concept-aliases.md) sont utilisables ici. 
* [[yii\web\AssetBundle::basePath|basePath ]] (chemin de base): spécifie un dossier accessible du Web qui contient les fichiers de ressources dans ce paquet. 
Lorsque vous spécifiez la propriété[[yii\web\AssetBundle::sourcePath|sourcePath (chemin des sources)]], le [gestionnaire de ressources](#asset-manager) publie les ressources de ce paquet dans un dossier accessible du Web et redéfinit cette propriété en conséquence. 
Vous devez définir cette propriété si vos fichiers de ressources sont déjà
 dans un dossier accessible du Web et n'ont pas besoin d'être publiés. 
Les [alias de chemin](concept-aliases.md) sont utilisables ici.
* [[yii\web\AssetBundle::baseUrl|baseUrl ]] (URL de base): spécifie l'URL qui correspond au dossier
 [[yii\web\AssetBundle::basePath|basePath]]. 
Comme pour  [[yii\web\AssetBundle::basePath|basePath]] (chemin de base),
 si vous spécifiez la propriété [[yii\web\AssetBundle::sourcePath|sourcePath]], le [gestionnaire de ressources](#asset-manager) publie les ressources et redéfinit cette propriété en conséquence. Les [alias de chemin](concept-aliases.md) sont utilisables ici.
* [[yii\web\AssetBundle::css|css]]: un tableau listant les fichiers CSS contenu dans ce paquet de ressources. 
Notez que seul la barre oblique "/" doit être utilisée en tant que séparateur de dossier. Chaque fichier peut être spécifié en lui-même comme une chaîne de caractères ou dans un tableau avec les balises attributs et leur valeur.

* [[yii\web\AssetBundle::js|js]]: un tableau listant les fichiers JavaScript contenus dans ce paquet. 
Notez que seule la barre oblique de division "/" peut être utilisée en tant que séparateur de dossiers. 
Chaque fichier JavaScript peut être spécifié dans l'un des formats suivants :
  - Un chemin relatif représentant un fichier JavaScript local (p. ex. `js/main.js`). 
Le chemin réel du fichier peut être déterminé en préfixant le chemin relatif avec le [[yii\web\AssetManager::basePath| chemin de base]], 
et l'URL réelle du fichier peut être déterminée en préfixant le chemin relatif avec l'[[yii\web\AssetManager::baseUrl|URL de base]].
  - Une URL absolue représentant un fichier JavaScript externe. 
Par exemple , `https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js` ou
    `//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js`.
* [[yii\web\AssetBundle::depends|depends (dépendances)]]: 
un tableau listant les paquets de ressources dont ce paquet dépend (brièvement expliqué).
* [[yii\web\AssetBundle::jsOptions|jsOptions]]: spécifie les options qui sont passées à la méthode [[yii\web\View::registerJsFile()]] 
lorsqu'elle est appelée pour enregistrer *chacun des* fichiers JavaScript de ce paquet.
* [[yii\web\AssetBundle::cssOptions|cssOptions]]: spécifie les options qui sont passées à la méthode 
[[yii\web\View::registerCssFile()]] lorsqu'elle est appelée pour enregistrer *chacun des* fichiers CSS de ce paquet.
* [[yii\web\AssetBundle::publishOptions|publishOptions]]: spécifie les options qui sont passées à la méthode 
[[yii\web\AssetManager::publish()]] lorsqu'elle est appelée pour publier les fichiers de ressources sources dans un dossier accessible du Web.  
Cela n'est utilisé que si vous spécifiez la propriété  [[yii\web\AssetBundle::sourcePath|sourcePath]].


### Emplacement des ressources <span id="asset-locations"></span>

En se basant sur leur emplacement, les ressources peuvent être classifiées comme suit :

* Les ressources sources : les fichiers de ressources qui sont situés avec du code source PHP et qui ne peuvent être accéder directement depuis le Web.
 Afin de pouvoir être utilisées dans une page, elles doivent être copiées dans un dossier accessible du Web et transformées en ressources publiées.
Ce processus est appelé *publication des ressources* et il sera décrit en détail bientôt. 
* Les ressources publiées : les fichiers de ressources sont situés dans un dossier accessible du Web et peuvent par conséquent être accédés directement depuis le Web. 
* Les ressources externes : les fichiers de ressources sont situés sur un serveur Web différent de celui qui héberge l'application Web. 


Lors de la définition de classes de paquet de ressources, si vous spécifiez la propriété 
[[yii\web\AssetBundle::sourcePath|sourcePath (chemin des sources)]], cela veut dire que les ressources listées en utilisant des chemins relatifs sont considérées comme des ressources sources. 
Si vous ne spécifiez pas cette propriété, cela signifie que ces ressources sont des ressources publiées (vous devez en conséquence spécifier  [[yii\web\AssetBundle::basePath (chemin de base)|basePath]] et [[yii\web\AssetBundle::baseUrl|baseUrl (URL de base)]]
 pour faire connaître à Yii l'emplacement où elles se trouvent). 

Il est recommandé de placer les ressources appartenant à une application dans un dossier accessible du Web de manière à éviter une publication non nécessaire de ressources. 
C'est pourquoi `AppAsset` dans l'exemple précédent spécifie le [[yii\web\AssetBundle::basePath|chemin de base]] 
plutôt que le [[yii\web\AssetBundle::sourcePath|chemin des sources]].

Quant aux [extensions](structure-extensions.md), comme leurs ressources sont situées avec le code source dans des dossiers non accessibles depuis le Web, vous devez spécifier la propriété 
[[yii\web\AssetBundle::sourcePath|sourcePath]] 
lorsque vous définissez des classes de paquet de ressources pour elles.

> Note: n'utilisez pas  `@webroot/assets` en tant que [[yii\web\AssetBundle::sourcePath|chemin des sources]].
Ce dossier est utilisé par défaut par le 
[[yii\web\AssetManager|gestionnaire de ressources]] pour sauvegarder les fichiers de ressources publiés depuis leur emplacement source. 
Tout contenu dans ce dossier est considéré temporaire et sujet à suppression. 


### Dépendances de ressources <span id="asset-dependencies"></span>

Lorsque vous incluez plusieurs fichiers CSS ou JavaScript dans une page Web, ils doivent respecter un certain ordre pour éviter des problèmes de redéfinition. 
Par exemple, si vous utilisez l'objet graphique jQuery Ui dans une page Web, vous devez vous assurer que le fichier JavaScript jQuery est inclus avant le fichier  JavaScript  jQuery UI. 
Nous appelons un tel ordre : « dépendances entre ressources ».


Les dépendances entre ressources sont essentiellement spécifiées via la propriété 
[[yii\web\AssetBundle::depends]]. 
Dans l'exemple `AppAsset`, le paquet de ressources dépend de deux autres paquets de ressources : [[yii\web\YiiAsset]] et [[yii\bootstrap\BootstrapAsset]], 
ce qui veut dire que  les fichiers  CSS et JavaScript dans `AppAsset` sont inclus *après* les  fichiers contenus dans ces deux paquets de ressources dont ils dépendent. 

Les dépendances entre ressources sont transitives. Cela veut dire que si un paquet de ressources A dépend d'un paquet B qui lui-même dépend de C, A dépend de C également.


### Options des ressources <span id="asset-options"></span>

Vous pouvez spécifier les propriétés [[yii\web\AssetBundle::cssOptions|cssOptions]] et [[yii\web\AssetBundle::jsOptions|jsOptions]] 
pour personnaliser la manière dont les fichiers CSS et JavaScript sont inclus dans une page. 
Les valeurs de ces propriétés sont passées aux méthodes [[yii\web\View::registerCssFile()]] et  [[yii\web\View::registerJsFile()]], respectivement, lorsqu'elles sont appelées par la
 [vue](structure-views.md) pour inclure les fichiers CSS et JavaScript.

> Note: les options que vous définissez dans une classe de  paquet de ressources s'appliquent à  *chacun des* fichiers CSS/JavaScript du paquet.
Si vous voulez utiliser des options différentes entre fichiers, vous devez utiliser le format indiqué [[yii\web\AssetBundle::css|ci-dessus]]
 ou créer des paquets de ressources séparés et utiliser un jeu d'options dans chacun des paquets. 

Par exemple, pour inclure un fichier CSS sous condition que le navigateur soit IE9 ou inférieur, vous pouvez utiliser l'option suivante :

```php
public $cssOptions = ['condition' => 'lte IE9'];
```

Avec cela, le fichier  CSS du paquet pourra être inclus avec le code HTML suivant :

```html
<!--[if lte IE9]>
<link rel="stylesheet" href="path/to/foo.css">
<![endif]-->
```

Pour envelopper le lien CSS généré dans une balise  `<noscript>`, vous pouvez configurer `cssOptions` comme ceci :

```php
public $cssOptions = ['noscript' => true];
```

Pour inclure un fichier JavaScript dans la section d'entête d'une page (par défaut les fichiers  JavaScript sont inclus à la fin de la section body), utilisez l'option suivante : 

```php
public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
```

Par défaut, lorsqu'un paquet de ressources est publié, tous les contenus dans le dossier spécifié par la propriété [[yii\web\AssetBundle::sourcePath]]
 sont publiés. 
Vous pouvez personnaliser ce comportement en configurant la propriété [[yii\web\AssetBundle::publishOptions|publishOptions]]. 
Par exemple, pour publier seulement un ou quelques sous-dossiers du dossier spécifié par la propriété [[yii\web\AssetBundle::sourcePath]], 
vous pouvez procéder comme ceci dans la classe du paquet de ressources :

```php
<?php
namespace app\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle 
{
    public $sourcePath = '@bower/font-awesome'; 
    public $css = [ 
        'css/font-awesome.min.css', 
    ];
    public $publishOptions = [
        'only' => [
            'fonts/',
            'css/',
        ]
    ];
}  
```

L'exemple ci-dessus définit un paquet de ressources pour le [paquet "fontawesome"](https://fontawesome.com/). En spécifiant l'option de publication `only`, seuls les sous-dossiers `fonts` et  `css` sont publiés.


### Installation des ressources Bower et NPM  <span id="bower-npm-assets"></span>

La plupart des paquets JavaScript/CSS sont gérés par le gestionnaire de paquets [Bower](https://bower.io/) et/ou le gestionnaire de paquets [NPM](https://www.npmjs.com/). Dans le monde PHP, nous disposons de Composer, qui gère les dépendances, mais il est possible de charger des paquets Bower et NPM comme des paquets PHP en utilisant `composer.json`.

Pour cela, nous devons configurer quelque peu notre composer. Il y a deux options possibles :

___

#### En utilisant le dépôt asset-packagist

Cette façon de faire satisfera les exigences de la majorité des projets qui ont besoin de paquets Bower ou NPM.

> Note: depuis la version 2.0.13, les modèles de projet  Basic et Advanced sont tous deux configuré pour utiliser asset-packagist par défaut, c'est pourquoi, vous pouvez sauter cette section.

Dans le fichier `composer.json` de votre projet, ajoutez les lignes suivantes :

```json
"repositories": [
    {
        "type": "composer",
        "url": "https://asset-packagist.org"
    }
]
```

Ajustez les [aliases](concept-aliases.md) `@npm` et `@bower` dans la [configuration](concept-configurations.md) de votre application :

```php
$config = [
    ...
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    ...
];
```

Visitez [asset-packagist.org](https://asset-packagist.org) pour savoir comment il fonctionne.

#### En utilisant le fxp/composer-asset-plugin

Comparé à asset-packagist, composer-asset-plugin ne nécessite aucun changement dans la configuration de l'application. Au lieu de cela, il nécessite l'installation globale d'un greffon spécifique de Composer en exécutant la commande suivante :

```bash
composer global require "fxp/composer-asset-plugin:^1.4.1"
```

Cette commande installe  [composer asset plugin](https://github.com/fxpio/composer-asset-plugin) globalement, ce qui permet de gérer les dépendances des paquets Bower et NPM via Composer. Après l'installation du greffon, tout projet de votre ordinateur prendra en charge les paquets Bower et NPM via `composer.json`.

Ajoutez les lignes suivantes au fichier `composer.json` de votre projet pour préciser les dossiers où seront installés les paquets, si vous voulez les publier en utilisant Yii :

```json
"config": {
    "asset-installer-paths": {
        "npm-asset-library": "vendor/npm",
        "bower-asset-library": "vendor/bower"
    }
}
```

> Note: `fxp/composer-asset-plugin` ralentit significativement la commande `composer update` en comparaison avec asset-packagist.
 
____
 
Après avoir configuré Composer pour qu'il prenne en charge Bower et NPM :

1. Modifiez le fichier the `composer.json` de votre application ou extension et listez le paquet dans l'entrée `require`.
   Vous devez utiliser `bower-asset/PackageName` (pour les paquets Bower) ou `npm-asset/PackageName` (pour les paquets NPM) pour faire référence à la bibliothèque.
2. Exécutez `composer update`
3. Créez une classe de paquet de ressources et listez les fichiers JavaScript/CSS que vous envisagez d'utiliser dans votre application ou extension.
   Vous devez spécifier la propriété [[yii\web\AssetBundle::sourcePath|sourcePath]] comme `@bower/PackageName` ou `@npm/PackageName`.
   Cela parce que Composer installera le paquet Bower ou NPM dans le dossier correspondant à cet alias.

> Note: quelques paquets peuvent placer tous leurs fichiers distribués dans un sous-dossier. Si c'est le cas, vous devez spécifier le sous-dossier en tant que valeur de [[yii\web\AssetBundle::sourcePath|sourcePath]]. Par exemple, utilisez [[yii\web\JqueryAsset]] `@bower/jquery/dist` au lieu de `@bower/jquery`.


## Utilisation des paquets de ressources <span id="using-asset-bundles"></span>

Pour utiliser un paquet de ressources, enregistrez-le dans une [vue](structure-views.md) en appelant la méthode [[yii\web\AssetBundle::register()]]. Par exemple, dans un modèle de vue, vous pouvez enregistrer un paquet de ressources de la manière suivante :

```php
use app\assets\AppAsset;
AppAsset::register($this);  // $this représente l'objet *view* (vue)
```

> Info: la méthode [[yii\web\AssetBundle::register()]] retourne un objet paquet de ressources contenant les informations sur les ressources publiées, telles que le [[yii\web\AssetBundle::basePath|chemin de base]] ou l'[[yii\web\AssetBundle::baseUrl|URL de base]].

Si vous êtes en train d'enregistrer un paquet de ressources dans d'autres endroits, vous devez fournir l'objet *view* requis. Par exemple, pour enregistrer un paquet de ressources dans une classe d'[objet graphique](structure-widgets.md), vous pouvez obtenir l'objet *view* avec l'expression `$this->view`.

Lorsqu'un paquet de ressources est enregistré avec une vue, en arrière plan. Yii enregistre tous les paquets de ressources dont il dépend. Et si un paquet de ressources est situé dans un dossier inaccessible depuis le Web, il est publié dans un dossier accessible depuis le Web. Plus tard, lorsque la vue rend une page, elle génère les balises  `<link>` et `<script>` pour les fichiers  CSS et JavaScript listés dans le paquet de ressources enregistré. L'ordre des ces balises est déterminé par les dépendances entre paquets enregistrés et l'ordre des ressources listées dans les propriétés  [[yii\web\AssetBundle::css]] et [[yii\web\AssetBundle::js]].


### Paquets de ressources dynamiques <span id="dynamic-asset-bundles"></span>

Une classe PHP ordinaire de paquet de ressources peut comporter sa propre logique et peut ajuster ses paramètres internes dynamiquement.
Par exemple : il se peut que vous utilisiez une bibliothèque JavaScript sophistiquée  qui des ressources d'internationalisation dans des fichiers séparés pour chacune des langues. En conséquence de quoi, vous devez ajouter certains fichiers '.js' particuliers à votre page pour la fonction de traduction de la bibliothèque fonctionne. Cela peut être fait en redéfinissant la méthode [[yii\web\AssetBundle::init()]] :


```php
namespace app\assets;

use yii\web\AssetBundle;
use Yii;

class SophisticatedAssetBundle extends AssetBundle
{
    public $sourcePath = '/path/to/sophisticated/src';
    public $js = [
        'sophisticated.js' // fichier toujours utilisé
    ];

    public function init()
    {
        parent::init();
        $this->js[] = 'i18n/' . Yii::$app->language . '.js'; // fichier dynamique ajouté
    }
}
```

Un paquet de ressources particuliers peut aussi être ajusté via son instance retourné par [[yii\web\AssetBundle::register()]].
Par exemple :

```php
use app\assets\SophisticatedAssetBundle;
use Yii;

$bundle = SophisticatedAssetBundle::register(Yii::$app->view);
$bundle->js[] = 'i18n/' . Yii::$app->language . '.js'; // fichier dynamique ajouté
```

> Note : bien que l'ajustement dynamique des paquets de ressources soit pris e charge, c'est une **mauvaise** pratique qui peut conduire à des effets de bord inattendus et qui devrait être évité si possible. 

### Personnalisation des paquets de ressources <span id="customizing-asset-bundles"></span>

Yii gère les paquets de ressources à l'aide d'un composant d'application nommé  `assetManager` (gestionnaire de ressources) qui est mis œuvre par [[yii\web\AssetManager]]. En configurant la propriété [[yii\web\AssetManager::bundles]], il est possible de personnaliser le comportement d'un paquet de ressources. Par exemple, le paquet de ressources par défaut [[yii\web\JqueryAsset]] utilise le fichier `jquery.js` du paquet Bower installé. Pour améliorer la disponibilité et la performance, vous désirez peut-être utiliser une version hébergée par Google. Vous pouvez le faire en configurant `assetManager` dans la configuration de l'application comme ceci :

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => [
                    'sourcePath' => null,   // ne pas publier le paquet
                    'js' => [
                        '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
                    ]
                ],
            ],
        ],
    ],
];
```

Vous pouvez configurer de multiples paquets de ressources de manière similaire via [[yii\web\AssetManager::bundles]]. Les clés du tableau doivent être les nom des classes (sans la barre oblique inversée de tête) des paquets de ressources, et les valeurs du tableau doivent être les [tableaux de configuration](concept-configurations.md) correspondants.

> Tip: vous pouvez choisir quelles ressources utiliser dans un paquet en fonction d'une condition. L'exemple suivant montre comment utiliser  `jquery.js` dans l'environnement de développement et  `jquery.min.js` autrement :
>
> ```php
> 'yii\web\JqueryAsset' => [
>     'js' => [
>         YII_ENV_DEV ? 'jquery.js' : 'jquery.min.js'
>     ]
> ],
> ```

Vous pouvez désactiver un ou plusieurs paquets de ressources en associant `false` (faux) aux noms des paquets de ressources que vous voulez désactiver. Lorsque vous enregistrez un paquet de ressources dans une vue, aucun des paquets dont il dépend n'est enregistré, et la vue, elle non plus, n'inclut aucune des ressources du paquet dans la page qu'elle rend. Par exemple, pour désactiver [[yii\web\JqueryAsset]], vous pouvez utiliser la configuration suivante :

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'bundles' => [
                'yii\web\JqueryAsset' => false,
            ],
        ],
    ],
];
```

Vous pouvez aussi désactiver *tous* les paquets de ressources en définissant [[yii\web\AssetManager::bundles]] à la valeur `false`.


### Mise en correspondance des ressources <span id="asset-mapping"></span>

Parfois, vous désirez « corriger » des chemins de fichiers de ressources incorrects ou incompatibles utilisés par plusieurs paquets de ressources. Par exemple, un paquet A utilise  `jquery.min.js` version 1.11.1, et un paquet  B utilise  `jquery.js` version 2.1.1. Bien que vous puissiez corriger le problème en personnalisant chacun des paquets, une façon plus facile est d'utiliser la fonctionnalité *mise en correspondance des ressources* pour mettre en correspondance les ressources incorrectes avec celles désirées. Pour le faire, configurez la propriété [[yii\web\AssetManager::assetMap (table de mise en correspondance des ressources)]] comme indiqué ci-après :

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'assetMap' => [
                'jquery.js' => '//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js',
            ],
        ],
    ],
];
```

Les clés de la [[yii\web\AssetManager::assetMap|table de mise en correspondance des ressources]] sont les noms des ressources que vous voulez corriger, et les valeurs sont les chemins des ressources désirées. Lorsque vous enregistrez un paquet de ressources dans une vue, chacune des ressources relatives dans ses tableaux [[yii\web\AssetBundle::css|css]] et [[yii\web\AssetBundle::js|js]] sont examinées dans cette table. Si une des clés est trouvée comme étant la dernière partie d'un chemin de fichier de ressources (qui est préfixé par le [[yii\web\AssetBundle::chemin des sources si disponible)]], la valeur correspondante remplace la ressource et est enregistrée avec la vue.
For exemple, le fichier de ressources  `my/path/to/jquery.js` correspond à la clé `jquery.js`.

> Note: seules les ressources spécifiées en utilisant des chemins relatifs peuvent faire l'objet d'une mise en correspondance. Les chemins de ressources cibles doivent être soit des URL absolues, soit des chemins relatifs à  [[yii\web\AssetManager::basePath]].


### Publication des ressources <span id="asset-publishing"></span>

Comme mentionné plus haut, si un paquet de ressources est situé dans un dossier non accessible depuis le Web, ses ressources sont copiées dans un dossier Web lorsque le paquet est enregistré dans une vue. Ce processus est appelé *publication des ressources* et est accompli automatiquement par le  [[yii\web\AssetManager|gestionnaire de ressources]].

Par défaut, les ressources sont publiées dans le dossier `@webroot/assets` qui correspond à l'URL `@web/assets`. Vous pouvez personnaliser cet emplacement en configurant les propriétés [[yii\web\AssetManager::basePath|basePath]] et [[yii\web\AssetManager::baseUrl|baseUrl]].

Au lieu de publier les ressources en copiant les fichiers, vous pouvez envisager d'utiliser des liens symboliques, si votre système d'exploitation et votre serveur Web le permettent. Cette fonctionnalité peut être activée en définissant la propriété [[yii\web\AssetManager::linkAssets|linkAssets]] à `true` (vrai).

```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
        ],
    ],
];
```

Avec la configuration ci-dessus, le gestionnaire de ressources crée un lien symbolique vers le chemin des sources d'un paquet de ressources lors de sa publication. Cela est plus rapide que la copie de fichiers et peut également garantir que les ressources publiées sont toujours à jour.



### Fonctionnalité d'affranchissement du cache <span id="cache-busting"></span>

Pour les application Web tournant en mode production, une pratique courante consiste à activer la mise en cache HTTP pour les ressources statiques. Un inconvénient de cette pratique est que si vous modifiez une ressource et la republiez en production, le navigateur peut toujours utiliser l'ancienne version à cause de la mise en cache HTTP. Pour s'affranchir de cet inconvénient, vous pouvez utiliser la fonctionnalité d'affranchissement du cache qui a été introduite dans la version 2.0.3 en configurant le gestionnaire de ressources [[yii\web\AssetManager]] comme suit :
  
```php
return [
    // ...
    'components' => [
        'assetManager' => [
            'appendTimestamp' => true,
        ],
    ],
];
```

Ce faisant, l'horodatage de la dernière modification du fichier est ajoutée en fin d'URL de la ressource publiée. Par exemple, l'URL vers `yii.js` ressemble à  `/assets/5515a87c/yii.js?v=1423448645"`, où `v` représente l'horodatage de la dernière modification du fichier `yii.js`. Désormais, si vous modifiez une ressource, son URL change également ce qui force le navigateur à aller chercher la dernière version de la ressource.


## Paquets de ressources couramment utilisés <span id="common-asset-bundles"></span>

Le code du noyau de  Yii a défini de nombreux paquets de ressources. Parmi eux, les paquets suivants sont couramment utilisés et peuvent être référencés dans le code de votre application ou de votre extension.

- [[yii\web\YiiAsset]]: ce paquet comprend essentiellement le fichier `yii.js` qui met en œuvre un mécanisme d'organisation du code JavaScript en modules. Il fournit également une prise en charge spéciale des attributs `data-method` et `data-confirm` et autres fonctionnalités utiles. 
- [[yii\web\JqueryAsset]]: ce paquet comprend le fichier  `jquery.js` du paquet Bower de jQuery.
- [[yii\bootstrap\BootstrapAsset]]: ce paquet inclut le fichier CSS du framework Twitter Bootstrap.
- [[yii\bootstrap\BootstrapPluginAsset]]: ce paquet inclut le fichier JavaScript du framework Twitter Bootstrap pour la prise en charge des greffons JavaScript de Bootstrap.
- [[yii\jui\JuiAsset]]: ce paquet inclut les fichiers CSS et JavaScript de la bibliothèque  jQuery UI.

Si votre code dépend de jQuery, jQuery UI ou Bootstrap, vous devriez utiliser les paquets de ressources prédéfinis plutôt que de créer vos propres versions. Si les réglages par défaut des ces paquets de ressources prédéfinis ne répondent pas à vos besoins, vous pouvez les personnaliser  comme expliqué à la sous-section [Personnalisation des paquets de ressources](#customizing-asset-bundles). 


## Conversion de ressources <span id="asset-conversion"></span>

Au lieu d'écrire directement leur code CSS et/ou JavaScript, les développeurs l'écrivent souvent dans une syntaxe étendue et utilisent des outils spéciaux pour le convertir en CSS/JavaScript. Par exemple, pour le code CSS vous pouvez utiliser [LESS](https://lesscss.org/) ou [SCSS](https://sass-lang.com/); et pour JavaScript, vous pouvez utiliser [TypeScript](https://www.typescriptlang.org/).

Vous pouvez lister les fichiers de ressources écrits dans une syntaxe  étendue dans les propriétés [[yii\web\AssetBundle::css|css]] et [[yii\web\AssetBundle::js|js]] d'un paquet de ressources. 

```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.less',
    ];
    public $js = [
        'js/site.ts',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
```

Lorsque vous enregistrez une tel paquet de ressources dans une vue, le [[yii\web\AssetManager|gestionnaire de ressources]] exécute automatiquement l'outil de pré-traitement pour convertir les ressources, écrites dans une syntaxe reconnue, en CSS/JavaScript. Lorsque la vue rend finalement la page, elle inclut les fichiers CSS/JavaScript dans la page, au lieu des ressources originales écrites dans la syntaxe étendue. 

Yii utilise l'extension du nom de fichier pour identifier dans quelle syntaxe une ressource est écrite. Par défaut, il reconnaît les syntaxes et les extensions de nom suivants :

- [LESS](https://lesscss.org/): `.less`
- [SCSS](https://sass-lang.com/): `.scss`
- [Stylus](https://stylus-lang.com/): `.styl`
- [CoffeeScript](https://coffeescript.org/): `.coffee`
- [TypeScript](https://www.typescriptlang.org/): `.ts`

Yii se fie aux outils de pré-traitement installés pour convertir les ressources. Par exemple, pour utiliser [LESS](https://lesscss.org/), vous devriez utiliser la commande de pré-traitement `lessc`.

Vous pouvez personnaliser les commandes de pré-traitement et la syntaxe étendue prise en charge en configurant [[yii\web\AssetManager::converter]] comme ci-après :

```php
return [
    'components' => [
        'assetManager' => [
            'converter' => [
                'class' => 'yii\web\AssetConverter',
                'commands' => [
                    'less' => ['css', 'lessc {from} {to} --no-color'],
                    'ts' => ['js', 'tsc --out {to} {from}'],
                ],
            ],
        ],
    ],
];
```

Dans la syntaxe précédente, nous spécifions les syntaxes étendues prises en charge via la propriété [[yii\web\AssetConverter::commands]]. Les clés du tableau sont les extensions de nom de fichier (sans le point de tête), et les valeurs sont les extensions des fichiers de ressources résultants ainsi que les commandes pour effectuer les conversions. Les valeurs à remplacer `{from}` et `{to}` dans les commandes doivent être remplacées par les chemins de fichiers de ressources sources et les chemins de fichiers de ressources cibles.

> Info: il y a d'autres manières de travailler avec les ressources en syntaxe étendue, en plus de celle décrite ci-dessus. Par exemple, vous pouvez utiliser des outils de compilation comme [grunt](https://gruntjs.com/) pour surveiller et convertir automatiquement des ressources écrites en syntaxe étendue. Dans ce cas, vous devez lister les fichiers CSS/JavaScript résultants dans des paquets de ressources plutôt que les fichiers originaux. 


## Combinaison et compression de ressources <span id="combining-compressing-assets"></span>

Une page Web peut inclure plusieurs fichiers CSS et/ou JavaScript. Pour réduire le nombre de requêtes HTTP et la taille des fichiers téléchargés, une pratique courante est de combiner et compresser ces fichiers CSS/JavaScript multiples en un ou très peu de fichiers, et d'inclure ces fichiers compressés dans les pages Web à la place des fichiers originaux. 
 
> Info: la combinaison et la compression de ressources sont généralement nécessaires lorsqu'une application est dans le mode production. En mode développement, l'utilisation des fichiers CSS/JavaScript originaux est souvent plus pratique pour des raisons de débogage plus facile.

Dans ce qui est présenté ci-dessous, nous introduisons une approche pour combiner et compresser les fichiers de ressources sans avoir besoin de modifier le code existant. 

1. Identifier tous les paquets de ressources dans l'application que vous envisagez de combiner et de compresser.
2. Diviser ces paquets en un ou quelques groupes. Notez que chaque paquet ne peut appartenir qu'à un seul groupe. 
3. Combiner/compresser les fichiers CSS de chacun des groupes en un fichier unique. Faire de même avec les fichiers JavaScript. 
4. Définir un nouveau paquet de ressources pour chacun des groupes : 
   * Définir les propriétés [[yii\web\AssetBundle::css|css]] et [[yii\web\AssetBundle::js|js]] comme étant les fichiers CSS et JavaScript combinés, respectivement. 
   * Personnaliser les paquets de ressources dans chacun des groupes en définissant leurs propriétés [[yii\web\AssetBundle::css|css]] et 
     [[yii\web\AssetBundle::js|js]] comme étant vides, et en définissant leur propriété [[yii\web\AssetBundle::depends|depends]] comme étant le nouveau paquet de ressources créé pour le groupe.

En utilisant cette approche, lorsque vous enregistrez un paquet de ressources dans une vue, cela engendre un enregistrement automatique du nouveau paquet de ressources pour le groupe auquel le paquet original appartient. Et, en conséquence, les fichiers de ressources combinés/compressés sont inclus dans la page à la place des fichiers originaux. 


### Un exemple <span id="example"></span>

Examinons ensemble un exemple pour expliquer plus précisément l'approche ci-dessus. 

Supposons que votre application possède deux pages X et Y. La page X utilise les paquets de ressources A, B et C, tandis que la page Y utilise les paquets des ressources B, C et D. 

Vous avez deux possibilités pour diviser ces paquets de ressources. La première consiste à utiliser un groupe unique pour y inclure tous les paquets de ressources, la seconde est de mettre A dans un groupe X, D dans un groupe Y et (B,C) dans un groupe S. Laquelle des deux est la meilleure ? Cela dépend. La première possibilité offre l'avantage que les deux pages partagent les mêmes fichiers CSS et JavaScript combinés, ce qui rend la mise en cache HTTP plus efficace. Cependant, comme le groupe unique contient tous les paquets, la taille des fichiers combinés CSS et JavaScript est plus importante et accroît donc le temps de transmission initial. Par souci de simplification, dans cet exemple, nous utiliserons la première possibilité, c'est à dire, un groupe unique contenant tous les paquets. 

> Info: la division des paquets de ressources en groupes, n'est pas une tâche triviale. Cela requiert généralement une analyse du trafic réel des données des différentes ressources sur différentes pages. Au début, vous pouvez démarrer avec un groupe unique par souci de simplification. 

Utilisez les outils existants (p. ex. [Closure Compiler](https://developers.google.com/closure/compiler/), YUI Compressor](https://github.com/yui/yuicompressor/)) pour combiner et compresser les fichiers CSS et JavaScript dans tous les paquets. Notez que les fichiers doivent être combinés dans l'ordre qui permet de satisfaire toutes les dépendances entre paquets. Par exemple, si le paquet A dépend du paquet B, qui dépend lui-même du paquet C et du paquet D, alors vous devez lister les fichiers de ressources en commençant par C et D, suivi de B et, pour finir, A. 

Après avoir combiné et compressé, nous obtenons un fichier CSS et un fichier JavaScript. Supposons qu'ils s'appellent `all-xyz.css` et `all-xyz.js`, où `xyz` est un horodatage ou une valeur de hachage qui est utilisé pour rendre le nom de fichier unique afin d'éviter les problèmes de mise en cache HTTP.
 
Nous en sommes au dernier stade maintenant. Configurez le [[yii\web\AssetManager|gestionnaire de ressources]] dans la configuration de l'application comme indiqué ci-dessous :


```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => [
                'all' => [
                    'class' => 'yii\web\AssetBundle',
                    'basePath' => '@webroot/assets',
                    'baseUrl' => '@web/assets',
                    'css' => ['all-xyz.css'],
                    'js' => ['all-xyz.js'],
                ],
                'A' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'B' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'C' => ['css' => [], 'js' => [], 'depends' => ['all']],
                'D' => ['css' => [], 'js' => [], 'depends' => ['all']],
            ],
        ],
    ],
];
```

Comme c'est expliqué dans la sous-section [Personnalisation des paquets de ressources](#customizing-asset-bundles), la configuration ci-dessus modifie le comportement par défaut des chacun des paquets. En particulier, les paquets  A, B, C et D ne possèdent plus aucun fichier de ressources. Ils dépendent tous du paquet `all` qui contient les fichiers combinés `all-xyz.css` et `all-xyz.js`.
Par conséquent, pour la page X, au lieu d'inclure les fichiers sources originaux des paquets  A, B et C, seuls ces deux fichiers combinés sont inclus ; la même chose se passe par la page Y. 

Il y a un truc final pour rendre l'approche ci-dessus plus lisse. Au lieu de modifier directement le fichier de configuration de l'application, vous pouvez mettre le tableau de personnalisation dans un fichier séparé et l'inclure dans la configuration de l'application en fonction d'une condition. Par exemple :

```php
return [
    'components' => [
        'assetManager' => [
            'bundles' => require __DIR__ . '/' . (YII_ENV_PROD ? 'assets-prod.php' : 'assets-dev.php'),  
        ],
    ],
];
```

Cela veut dire que le tableau de  configuration du paquet de ressources est sauvegardé dans  `assets-prod.php` pour le mode production, et `assets-dev.php` pour les autres modes.


### Utilisation de la commande `asset`<span id="using-asset-command"></span>

Yii fournit une commande de console nommée `asset` pour automatiser l'approche que nous venons juste de décrire. 

Pour utiliser cette commande, vous devez d'abord créer un fichier de configuration pour décrire quels paquets de ressources seront combinés et comment ils seront regroupés. Vous pouvez utiliser la sous-commande `asset/template` pour créer d'abord un modèle, puis le modifier pour l'adapter à vos besoins. 

```
yii asset/template assets.php
```

La commande génère un fichier `assets.php` dans le dossier courant. Le contenu de ce fichier ressemble à ce qui suit :

```php
<?php
/**
 * Fichier de configuration pour la commande de console "yii asset".
 * Notez que dans l'environnement console, quelques alias de chemin comme  '@webroot' et '@web' peuvent ne pas exister.
 * Pensez à définir ces alias de chemin manquants. 
 */
return [
    // Ajuste la commande/fonction de rappel pour la compression des fichiers JavaScript :
    'jsCompressor' => 'java -jar compiler.jar --js {from} --js_output_file {to}',
    // Ajuste la commande/fonction de rappel pour la compression des fichiers   CSS :
    'cssCompressor' => 'java -jar yuicompressor.jar --type css {from} -o {to}',
    // La liste des paquets de ressources à compresser :
    'bundles' => [
        // 'yii\web\YiiAsset',
        // 'yii\web\JqueryAsset',
    ],
    // Paquets de ressources par la sortie de compression :
    'targets' => [
        'all' => [
            'class' => 'yii\web\AssetBundle',
            'basePath' => '@webroot/assets',
            'baseUrl' => '@web/assets',
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
        ],
    ],
    // Configuration du gestionnaire de ressources :
    'assetManager' => [
    ],
];
```

Vous devez modifier ce fichier et spécifier quels paquets vous envisagez de combiner dans l'option  `bundles`. Dans l'option `targets` vous devez spécifier comment les paquets sont divisés en groupes. Vous pouvez spécifier un ou plusieurs groupes, comme nous l'avons déjà dit.

> Note: comme les alias `@webroot` et `@web` ne sont pas disponibles dans l'application console, vous devez les définir explicitement dans la configuration.

Les fichiers JavaScript sont combinés, compressés et écrits dans `js/all-{hash}.js` où {hash} est une valeur à remplacer par la valeur de hachage du fichier résultant.

Les options `jsCompressor` et `cssCompressor` spécifient les commandes de console ou les fonctions de rappel PHP pour effectuer la combinaison/compression des fichiers JavaScript et CSS. Par défaut, Yii utilise [Closure Compiler](https://developers.google.com/closure/compiler/) pour combiner les fichiers JavaScript et [YUI Compressor](https://github.com/yui/yuicompressor/) pour combiner les fichiers CSS. Vous devez installer ces outils à la main ou ajuster ces options pour utiliser vos outils favoris.


Avec le fichier de configuration, vous pouvez exécuter la commande `asset` pour combiner et compresser les fichiers de ressources et générer un nouveau fichier de configuration de paquet de ressources `assets-prod.php`:
 
```
yii asset assets.php config/assets-prod.php
```

Le fichier de configuration peut être inclus dans la configuration de l'application comme décrit dans la dernière sous-section . 


> Info: l'utilisation de la commande `asset` n'est pas la seule option pour automatiser la combinaison et la compression des ressources. Vous pouvez utiliser l'excellent outil d'exécution de tâches [grunt](https://gruntjs.com/) pour arriver au même résultat. 


### Regroupement des paquets de ressources  <span id="grouping-asset-bundles"></span>

Dans la dernière sous-section présentée, nous avons expliqué comment combiner tous les paquets de ressources en un seul de manière à minimiser les requêtes HTTP pour les fichiers de ressources utilisés par l'application. Ce n'est pas toujours une pratique souhaitable. Par exemple, imaginez que votre application dispose d'une interface utilisateur (*frontend*) et d'une interface d'administration (*backend*), lesquelles utilisent un jeu différent de fichiers CSS et JavaScript. Dans un tel cas, combiner les paquets de ressources des deux interfaces en un seul  n'a pas beaucoup de sens, parce que les paquets de ressources pour l'interface utilisateur ne sont pas utilisés par l'interface d'administration, et parce que cela conduit à un gâchis de bande passante du réseau d'envoyer les ressources de l'interface d'administration lorsqu'une page du l'interface utilisateur est demandée. 
 
Pour résoudre ce problème, vous pouvez diviser les paquets de ressources en groupes et combiner les paquets de ressources de chacun des groupes. La configuration suivante montre comment vous pouvez grouper les paquets de ressources :
```php
return [
    ...
    // Specifie les paquets de sortie par groupe :
    'targets' => [
        'allShared' => [
            'js' => 'js/all-shared-{hash}.js',
            'css' => 'css/all-shared-{hash}.css',
            'depends' => [
                // Include all assets shared between 'backend' and 'frontend'
                'yii\web\YiiAsset',
                'app\assets\SharedAsset',
            ],
        ],
        'allBackEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [
                // Include only 'backend' assets:
                'app\assets\AdminAsset'
            ],
        ],
        'allFrontEnd' => [
            'js' => 'js/all-{hash}.js',
            'css' => 'css/all-{hash}.css',
            'depends' => [], // Include all remaining assets
        ],
    ],
    ...
];
```

Comme vous le voyez, les paquets de ressources sont divisés en trois groupes : `allShared`, `allBackEnd` et `allFrontEnd`. Ils dépendent tous d'un jeu approprié de paquets de ressources. Par exemple,  `allBackEnd` dépend de  `app\assets\AdminAsset`. En exécutant la commande  `asset` avec cette configuration, les paquets de ressources sont combinés en respectant les spécifications ci-dessus. 

> Info: vous pouvez laisser la configuration de  `depends` vide pour l'un des paquets cible. Ce faisant, ce paquet de ressources dépendra de tous les paquets de ressources dont aucun autre paquet de ressources ne dépend. 
