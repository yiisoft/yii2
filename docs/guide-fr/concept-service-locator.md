Localisateur de services
========================

Un localisateur de services est un objet que sait comment fournir toutes sortes de services (ou composants) dont une application peut avoir besoin. Dans le localisateur de services, chaque composant existe seulement sous forme d'une unique instance, identifiée de manière unique par un identifiant. Vous utilisez l'identifiant pour retrouver un composant du localisateur de services. 

Dans Yii, un localisateur de service est simplement une instance de [[yii\di\ServiceLocator]] ou d'une de ses classes filles.

Le localisateur de service le plus couramment utilisé dans Yii est l'objet *application*, auquel vous avez accès via `\Yii::$app`. Les services qu'il procure, tels les composants `request`, `response` et `urlManager`,  sont appelés *composants d'application*. Vous pouvez configurer ces trois composants, ou même les remplacer facilement avec votre propre implémentation, en utilisant les fonctionnalités procurées par le localisateur de services. 

En plus de l'objet application, chaque objet module est aussi un localisateur de services.

Pour utiliser un localisateur de service, la première étape est d'enregistrer le composant auprès de lui. Un composant peut être enregistré via la méthode [[yii\di\ServiceLocator::set()]]. Le code suivant montre différentes manières d'enregistrer des composants :

```php
use yii\di\ServiceLocator;
use yii\caching\FileCache;

$locator = new ServiceLocator;

// enregistre "cache" en utilisant un nom de classe qui peut être utilisé pour créer un composant
$locator->set('cache', 'yii\caching\ApcCache');

// enregistre "db" en utilisant un tableau de configuration qui peut être utilisé pour créer un composant
$locator->set('db', [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=demo',
    'username' => 'root',
    'password' => '',
]);

// enregistre "search" en utilisant une fonction anonyme qui construit un composant
$locator->set('search', function () {
    return new app\components\SolrService;
});

// enregistre "pageCache" en utilisant un composant
$locator->set('pageCache', new FileCache);
```

Une fois qu'un composant a été enregistré, vous pouvez y accéder via son identifiant, d'une des deux manières suivantes : 

```php
$cache = $locator->get('cache');
// ou en alternative 
$cache = $locator->cache;
```

Comme montré ci-dessus, [[yii\di\ServiceLocator]] vous permet d'accéder à un composant comme à une propriété en utilisant l'identifiant du composant.

Lorsque vous accédez à un composant pour la première fois, [[yii\di\ServiceLocator]] utilise l'information d'enregistrement du composant pour créer une nouvelle instance du composant et la retourner. Par la suite, si on accède à nouveau au composant, le localisateur de service retourne la même instance.

Vous pouvez utiliser [[yii\di\ServiceLocator::has()]] pour savoir si un identifiant de composant a déjà été enregistré. Si vous appelez [[yii\di\ServiceLocator::get()]] avec un identifiant invalide, une exception est levée. 


Comme les localisateurs de services sont souvent créés avec des [configurations](concept-configurations.md), une propriété accessible en écriture, et nommée [[yii\di\ServiceLocator::setComponents()|components]], est fournie. Cela vous permet de configurer et d'enregistrer plusieurs composants à la fois. Le code suivant montre un tableau de configuration qui peut être utilisé pour configurer un localisateur de services (p. ex. une [application](structure-applications.md)) avec les composants `db`, `cache`, `tz` et `search` :

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
            // ... other initializations ...
            return $solr;
        },
    ],
];
```

Dans ce qui précède, il y a une façon alternative de configurer le composant `search`. Au lieu d'écrire directement une fonction de rappel PHP qui construit une instance de `SolrService`, vous pouvez utiliser une méthode de classe statique pour retourner une telle fonction de rappel, comme c'est montré ci-dessous :

```php
class SolrServiceBuilder
{
    public static function build($ip)
    {
        return function () use ($ip) {
            $solr = new app\components\SolrService($ip);
            // ... autres initialisations ...
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

Cette approche alternative est à utiliser de préférence lorsque vous publiez une composant Yii qui encapsule quelques bibliothèques de tierces parties. Vous utilisez la méthode statique comme c'est montré ci-dessus pour représenter la logique complexe de construction de l'objet de tierce partie, et l'utilisateur de votre composant doit seulement appeler la méthode statique pour configurer le composant.

## Parcours d'un arbre <span id="tree-traversal"></span>

Les modules acceptent les inclusions arbitraires; une application Yii est essentiellement un arbre de modules. Comme chacun de ces modules est un localisateur de services, cela a du sens pour les enfants d'accéder à leur parent. 
Cela permet aux modules d'utiliser `$this->get('db')` au lieu de faire référence au localisateur de services racine `Yii::$app->get('db')`.
Un bénéficie supplémentaire pour le développeur est de pouvoir redéfinir la configuration dans un module.

Toute requête d'un service à l'intérieur d'un module est passée à son parent dans le cas où le module lui-même est incapable  de la satisfaire.

Notez que la configuration depuis des composants dans un module n'est jamais fusionnée avec celle depuis un composant du module parent. Le modèle de localisateur de services nous permet de définir des services nommés mais on ne peut supposer que des services du même nom utilisent les mêmes paramètres de configuration.
