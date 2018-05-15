Mise en cache de données
========================

La mise en cache de données consiste à stocker quelques variables PHP dans un cache et à les y retrouver plus tard. 
C'est également la base pour des fonctionnalités de mise en cache plus avancées, comme la [mise en cache de requêtes](#query-caching) et la [mise en cache de pages](caching-page.md).

Le code qui suit est un modèle d'utilisation typique de la mise en cache de données, dans lequel `cache` fait référence à un [composant de mise en cache](#cache-components) :


```php
// tente de retrouver la donnée $data dans le cache
$data = $cache->get($key);

if ($data === false) {
    // la donnée $data n'a pas été trouvée dans le cache, on la recalcule
    $data = $this->calculateSomething();

    // stocke la donnée $data dans le cache de façon à la retrouver la prochaine fois
    $cache->set($key, $data);
}

// la donnée $data est disponible ici
```

Depuis la version 2.0.11, le [composant de mise en cache](#cache-components) fournit la méthode [[yii\caching\Cache::getOrSet()|getOrSet()]] qui simplifie le code pour l'obtention, le calcul et le stockage des données. Le code qui suit fait exactement la même chose que l'exemple précédent :

```php
$data = $cache->getOrSet($key, function () {
    return $this->calculateSomething();
});
```

Lorsque le cache possède une donnée associée à la clé `$key`, la valeur en cache est retournée. Autrement, la fonction anonyme passée est exécutée pour calculer cette valeur qui est mise en cache et retournée.

Si la fonction anonyme a besoin de quelques données en dehors de la portée courante, vous pouvez les passer en utilisant l'instruction `use`. Par exemple :

```php
$user_id = 42;
$data = $cache->getOrSet($key, function () use ($user_id) {
    return $this->calculateSomething($user_id);
});
```

> Note : la méthode [[yii\caching\Cache::getOrSet()|getOrSet()]] prend également en charge la durée et les dépendances.
  Reportez-vous à [Expiration de la mise en cache](#cache-expiration) et à [Dépendances de mise en cache](#cache-dependencies) pour en savoir plus.
  

## Composants de mise en cache <span id="cache-components"></span>

La mise en cache s'appuie sur ce qu'on appelle les *composants de mise en cache* qui représentent des supports de mise en cache tels que les mémoires, les fichiers et les bases de données.

Les composants de mise en cache sont généralement enregistrés en tant que [composants d'application](structure-application-components.md) de façon à ce qu'ils puissent être configurables et accessibles globalement. Le code qui suit montre comment configurer le composant d'application `cache` pour qu'il utilise [memcached](http://memcached.org/) avec deux serveurs de cache :

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\MemCache',
        'servers' => [
            [
                'host' => 'server1',
                'port' => 11211,
                'weight' => 100,
            ],
            [
                'host' => 'server2',
                'port' => 11211,
                'weight' => 50,
            ],
        ],
    ],
],
```

Vous pouvez accéder au composant de mise en cache configuré ci-dessus en utilisant l'expression `Yii::$app->cache`.

Comme tous les composants de mise en cache prennent en charge le même jeux d'API, vous pouvez remplacer le composant de mise en cache sous-jacent par un autre en le reconfigurant  dans la configuration de l'application, cela sans modifier le code qui utilise le cache. Par exemple, vous pouvez modifier le code ci-dessus pour utiliser [[yii\caching\ApcCache|APC cache]] :


```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
    ],
],
```

> Tip: vous pouvez enregistrer de multiples composants d'application de mise en cache. Le composant nommé `cache` est utilisé par défaut par de nombreuses classes dépendantes d'un cache (p. ex.[[yii\web\UrlManager]]). 


### Supports de stockage pour cache pris en charge <span id="supported-cache-storage"></span>

Yii prend en charge un large panel de supports de stockage pour cache. Ce qui suit est un résumé :

* [[yii\caching\ApcCache]]: utilise l'extension PHP [APC](http://php.net/manual/en/book.apc.php). Cette option peut être considérée comme la plus rapide lorsqu'on utilise un cache pour une grosse application centralisée (p. ex. un serveur, pas d'équilibrage de charge dédié, etc.).
* [[yii\caching\DbCache]]: utilise une table de base de données pour stocker les données en cache. Pour utiliser ce cache, vous devez créer une table comme spécifié dans [[yii\caching\DbCache::cacheTable]].
* [[yii\caching\DummyCache]]: tient lieu de cache à remplacer qui n'assure pas de mise en cache réelle. Le but de ce composant est de simplifier le code qui a besoin de vérifier la disponibilité du cache. Par exemple, lors du développement ou si le serveur ne dispose pas de la prise en charge d'un cache, vous pouvez configurer un composant de mise en cache pour qu'il utilise ce cache. Lorsque la prise en charge réelle de la mise en cache est activée, vous pouvez basculer sur le composant de mise en cache correspondant. Dans les deux cas, vous pouvez utiliser le même code `Yii::$app->cache->get($key)` pour essayer de retrouver les données du cache sans vous préoccuper du fait que `Yii::$app->cache` puisse être `null`.
* [[yii\caching\FileCache]]: utilise des fichiers standards pour stocker les données en cache. Cela est particulièrement adapté à la mise en cache de gros blocs de données, comme le contenu d'une page.
* [[yii\caching\MemCache]]: utilise le [memcache](http://php.net/manual/en/book.memcache.php) PHP et l'extension [memcached](http://php.net/manual/en/book.memcached.php). Cette option peut être considérée comme la plus rapide lorsqu'on utilise un cache dans des applications distribuées (p. ex. avec plusieurs serveurs, l'équilibrage de charge, etc.).
* [[yii\redis\Cache]]: met en œuvre un composant de mise en cache basé sur un stockage clé-valeur [Redis](http://redis.io/) 
  (une version de redis égale ou supérieure à 2.6.12 est nécessaire).
* [[yii\caching\WinCache]]: utilise le [WinCache](http://iis.net/downloads/microsoft/wincache-extension) PHP
  ([voir aussi l'extension](http://php.net/manual/en/book.wincache.php)).
* [[yii\caching\XCache]] _(deprecated)_: utilise l'extension PHP [XCache](http://xcache.lighttpd.net/).

> Tip: vous pouvez utiliser différents supports de stockage pour cache dans la même application. Une stratégie courante est d'utiliser un support de stockage pour cache basé sur la mémoire pour stocker des données de petite taille mais d'usage constant (p. ex. des données statistiques), et d'utiliser des supports de stockage pour cache basés sur des fichiers ou des bases de données pour stocker des données volumineuses et utilisées moins souvent (p. ex. des contenus de pages).


## Les API Cache <span id="cache-apis"></span>

Tous les composants de mise en cache dérivent de la même classe de base [[yii\caching\Cache]] et par conséquent prennent en charge les API suivantes :

* [[yii\caching\Cache::get()|get()]]: retrouve une donnée dans le cache identifiée par une clé spécifiée. Une valeur `false` (faux) est retournée si la donnée n'est pas trouvée dans le cache ou si elle a expiré ou été invalidée.
* [[yii\caching\Cache::set()|set()]]: stocke une donnée sous une clé dans le cache.
* [[yii\caching\Cache::add()|add()]]: stocke une donnée identifiée par une clé dans le cache si la clé n'existe pas déjà dans le cache.
* [[yii\caching\Cache::getOrSet()|getOrSet()]]: retrouve une donnée dans le cache identifiée par une clé spécifiée ou exécute la fonction de rappel passée, stocke la valeur retournée par cette fonction dans le cache sous cette clé et retourne la donnée. 
* [[yii\caching\Cache::multiGet()|multiGet()]]: retrouve de multiples données dans le cache identifiées par les clés spécifiées.
* [[yii\caching\Cache::multiSet()|multiSet()]]: stocke de multiples données dans le cache. Chaque donnée est identifiée par une clé.
* [[yii\caching\Cache::multiAdd()|multiAdd()]]: stocke de multiples données dans le cache. Chaque donnée est identifiée par une clé. Si la clé existe déjà dans le cache, la donnée est ignorée.
* [[yii\caching\Cache::exists()|exists()]]: retourne une valeur indiquant si la clé spécifiée existe dans le cache.
* [[yii\caching\Cache::delete()|delete()]]: retire du cache une donnée identifiée par une clé.
* [[yii\caching\Cache::clear()|clear()]]: retire toutes les données du cache.

> Note : ne mettez pas directement en cache une valeur booléenne `false` parce que la méthode [[yii\caching\Cache::get()|get()]] utilise  la valeur `false` pour indiquer que la donnée n'a pas été trouvée dans le cache. Au lieu de cela, vous pouvez placer cette donnée dans un tableau et mettre ce tableau en cache pour éviter le problème.

Quelques supports de cache, tels que MemCache, APC, prennent en charge la récupération de multiples valeurs en cache en mode « batch » (lot), ce qui réduit la surcharge occasionnée par la récupération des données en cache. Les API [[yii\caching\Cache::multiGet()|multiGet()]] et [[yii\caching\Cache::multiAdd()|multiAdd()]] sont fournies pour exploiter cette fonctionnalité. Dans le cas où le support de cache sous-jacent ne prend pas en charge cette fonctionnalité, elle est simulée.
Comme [[yii\caching\Cache]] implémente `ArrayAccess`, un composant de mise en cache peut être utilisé comme un tableau. En voici quelques exemples :

```php
$cache['var1'] = $value1;  // équivalent à : $cache->set('var1', $value1);
$value2 = $cache['var2'];  // équivalent à : $value2 = $cache->get('var2');
```


### Clés de cache <span id="cache-keys"></span>

Chacune des données stockée dans le cache est identifiée de manière unique par une clé. Lorsque vous stockez une donnée dans le cache, vous devez spécifier une clé qui lui est attribuée. Plus tard, pour récupérer la donnée, vous devez fournir cette clé.

Vous pouvez utiliser une chaîne de caractères ou une valeur arbitraire en tant que clé de cache. Lorsqu'il ne s'agit pas d'une chaîne de caractères, elle est automatiquement sérialisée sous forme de chaîne de caractères.

Une stratégie courante pour définir une clé de cache consiste à inclure tous les facteurs déterminants sous forme de tableau. Par exemple,[[yii\db\Schema]] utilise la clé suivante par mettre en cache les informations de schéma d'une table de base de données :

```php
[
    __CLASS__,              // schema class name
    $this->db->dsn,         // DB connection data source name
    $this->db->username,    // DB connection login user
    $name,                  // table name
];
```

Comme vous le constatez, la clé inclut toutes les informations nécessaires pour spécifier de manière unique une table de base de données. 

> Note : les valeurs stockées dans le cache via [[yii\caching\Cache::multiSet()|multiSet()]] ou [[yii\caching\Cache::multiAdd()|multiAdd()]] peuvent n'avoir que des clés sous forme de chaînes de caractères ou de nombres entiers. Si vous avez besoin de définir des clés plus complexes, stockez la valeur séparément via [[yii\caching\Cache::set()|set()]] ou [[yii\caching\Cache::add()|add()]].

Lorsque le même support de cache est utilisé par différentes applications, vous devriez spécifier un préfixe de clé de cache pour chacune des applications afin d'éviter les conflits de clés de cache. Cela peut être fait en configurant la propriété [[yii\caching\Cache::keyPrefix]]. Par exemple, dans la configuration de l'application vous pouvez entrer le code suivant :

```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\ApcCache',
        'keyPrefix' => 'myapp',       // a unique cache key prefix
    ],
],
```

Pour garantir l'interopérabilité, vous ne devez utiliser que des caractères alpha-numériques.


### Expiration de la mise en cache <span id="cache-expiration"></span>

Une donnée stockée dans le cache y restera à jamais sauf si elle en est retirée par l'application d'une quelconque politique de mise en cache (p. ex. l'espace de mise en cache est plein et les données les plus anciennes sont retirées). Pour modifier ce comportement, vous pouvez fournir un paramètre d'expiration lors de l'appel de la fonction [[yii\caching\Cache::set()|set()]] pour stocker une donnée. Le paramètre indique pour combien de secondes la donnée restera valide dans le cache. Lorsque vous appelez la fonction [[yii\caching\Cache::get()|get()]] pour récupérer une donnée, si cette dernière a expiré, la méthode retourne `false`, pour indiquer que la donnée n'a pas été trouvée dans le cache. Par exemple, 

```php
// conserve la donnée dans le cache pour un maximum de 45 secondes
$cache->set($key, $data, 45);

sleep(50);

$data = $cache->get($key);
if ($data === false) {
    // $data a expiré ou n'a pas été trouvée dans le cache
}
```

Depuis la version 2.0.11, vous pouvez définir une valeur [[yii\caching\Cache::$defaultDuration|defaultDuration]] dans la configuration de votre composant de mise en cache si vous préférez utiliser une durée de mise en cache personnalisée au lieu de la durée illimitée par défaut. Cela vous évitera d'avoir à passer la durée personnalisée à la fonction [[yii\caching\Cache::set()|set()]] à chaque fois.


### Dépendances de mise en cache <span id="cache-dependencies"></span>

En plus de la définition du temps d'expiration, les données mises en cache peuvent également être invalidées par modification de ce qu'on appelle les *dépendances de mise en cache*. 
Par exemple, [[yii\caching\FileDependency]] représente la dépendance à la date de modification d'un fichier. 
Lorsque cette dépendance est modifiée, cela signifie que le fichier correspondant est modifié. En conséquence, tout contenu de fichier périmé trouvé dans le cache devrait être invalidé et l'appel de la fonction [[yii\caching\Cache::get()|get()]] devrait retourner `false`.


Les dépendances de mise en cache sont représentées sous forme d'objets dérivés de [[yii\caching\Dependency]]. Lorsque vous appelez la fonction [[yii\caching\Cache::set()|set()]] pour stocker une donnée dans le cache, vous pouvez lui passer un objet de dépendance (« Dependency ») associé. Par exemple,

```php
// Crée une dépendance à la date de modification du fichier example.txt
$dependency = new \yii\caching\FileDependency(['fileName' => 'example.txt']);

// La donnée expirera dans 30 secondes.
// Elle sera également invalidée plus tôt si le fichier example.txt est modifié.
$cache->set($key, $data, 30, $dependency);

// Le cache vérifiera si la donnée a expiré.
// Il vérifiera également si la dépendance associée a été modifiée. 
// Il retournera `false` si l'une de ces conditions est vérifiée.
$data = $cache->get($key);
```

Ci-dessous nous présentons un résumé des dépendances de mise en cache disponibles :

- [[yii\caching\ChainedDependency]]: la dépendance est modifiée si l'une des dépendances de la chaîne est modifiée.
- [[yii\caching\DbDependency]]: la dépendance est modifiée si le résultat de le requête de l'instruction SQL spécifiée est modifié.
- [[yii\caching\ExpressionDependency]]: la dépendance est modifiée si le résultat de l'expression PHP spécifiée est modifié.
- [[yii\caching\FileDependency]]: la dépendance est modifiée si la date de dernière modification du fichier est modifiée.
- [[yii\caching\TagDependency]]: associe une donnée mise en cache à une ou plusieurs balises. Vous pouvez invalider la donnée mise en cache associée à la balise spécifiée en appelant [[yii\caching\TagDependency::invalidate()]].

> Note : évitez d'utiliser la méthode [[yii\caching\Cache::exists()|exists()]] avec des dépendances. Cela ne vérifie pas si la dépendance associée à la donnée mise en cache, s'il en existe une, a changé. Ainsi, un appel de la fonction [[yii\caching\Cache::get()|get()]] peut retourner `false` alors que l'appel de la fonction [[yii\caching\Cache::exists()|exists()]] retourne `true`.


## Mise en cache de requêtes <span id="query-caching"></span>

La mise en cache de requêtes est une fonctionnalité spéciale de la mise en cache construite sur la base de la mise en cache de données. Elle est fournie pour permettre la mise en cache du résultat de requêtes de base de données.

La mise en cache de requêtes nécessite une [[yii\db\Connection|connexion à une base de données]] et un  [composant d'application](#cache-components)`cache` valide.
L'utilisation de base de la mise en cache de requêtes est la suivante, en supposant que `$db` est une instance de [[yii\db\Connection]] :

```php
$result = $db->cache(function ($db) {

    // le résultat d'une requête SQL sera servi à partir du cache
    // si la mise en cache de requêtes est activée et si le résultat de la requête est trouvé dans le cache
    return $db->createCommand('SELECT * FROM customer WHERE id=1')->queryOne();

});
```

La mise en cache de requêtes peut être utilisée pour des [DAO](db-dao.md) ainsi que pour des [enregistrements actifs](db-active-record.md):

```php
$result = Customer::getDb()->cache(function ($db) {
    return Customer::find()->where(['id' => 1])->one();
});
```

> Info : quelques systèmes de gestion de bases de données (DBMS) (p. ex. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
  prennent également en charge la mise en cache de requêtes du côté serveur de base de données. Vous pouvez choisir d'utiliser l'un ou l'autre des ces mécanismes de mise en cache de requêtes. Les mises en cache de requêtes décrites ci-dessus offrent l'avantage de pouvoir spécifier des dépendances de mise en cache flexibles et sont potentiellement plus efficaces.


### Vidage du cache <span id="cache-flushing">

Lorsque vous avez besoin d'invalider toutes les données stockées dans le cache, vous pouvez appeler [[yii\caching\Cache::clear()]].

Vous pouvez aussi vider le cache depuis la console en appelant `yii cache/flush`.
 - `yii cache`: liste les caches disponibles dans une application
 - `yii cache/flush cache1 cache2`: vide les composants de mise en cache `cache1`, `cache2` (vous pouvez passer de multiples composants en les séparant par une virgule)
 - `yii cache/flush-all`: vide tous les composants de mise en cache de l'application

> Info : les applications en console utilisent un fichier de configuration séparé par défaut. Assurez-vous que vous utilisez le même composant de mise en cache dans les configurations de vos application web et console pour obtenir l'effet correct. 


### Configurations <span id="query-caching-configs"></span>

La mise en cache de requêtes dispose de trois options globales configurables via [[yii\db\Connection]] :

* [[yii\db\Connection::enableQueryCache|enableQueryCache]] : pour activer ou désactiver la mise en cache de requêtes.
  Valeur par défaut : `true`. Notez que pour activer effectivement la mise en cache de requêtes, vous devez également disposer d'un cache valide, tel que spécifié par [[yii\db\Connection::queryCache|queryCache]].
* [[yii\db\Connection::queryCacheDuration|queryCacheDuration]] : ceci représente le nombre de secondes durant lesquelles le résultat d'une requête reste valide dans le cache. Vous pouvez utiliser 0 pour indiquer que le résultat de la requête doit rester valide indéfiniment dans le cache. Cette propriété est la valeur par défaut utilisée lors de l'appel [[yii\db\Connection::cache()]] sans spécifier de durée.
* [[yii\db\Connection::queryCache|queryCache]] : ceci représente l'identifiant du composant d'application de mise en cache.
  Sa valeur par défaut est : `'cache'`. La mise en cache de requêtes est activée seulement s'il existe un composant d'application de mise en cache valide.


### Utilisations <span id="query-caching-usages"></span>

Vous pouvez utiliser [[yii\db\Connection::cache()]] si vous avez de multiples requêtes SQL qui doivent bénéficier de la mise en cache de requêtes. On l'utilise comme suit :

```php
$duration = 60;     // mettre le résultat de la requête en cache durant 60 secondes.
$dependency = ...;  // dépendance optionnelle

$result = $db->cache(function ($db) {

    // ... effectuer les requêtes SQL ici ...

    return $result;

}, $duration, $dependency);
```

Toutes les requêtes SQL dans la fonction anonyme sont mises en cache pour la durée spécifiée avec la dépendance spécifiée. Si le résultat d'une requête est trouvé valide dans le cache, la requête est ignorée et, à la place, le résultat est servi à partir du cache. Si vous ne spécifiez pas le paramètre `$duration`, la valeur de [[yii\db\Connection::queryCacheDuration|queryCacheDuration]] est utilisée en remplacement.

Parfois, dans `cache()`, il se peut que vous vouliez désactiver la mise en cache de requêtes pour des requêtes particulières. Dans un tel cas, vous pouvez utiliser [[yii\db\Connection::noCache()]].

```php
$result = $db->cache(function ($db) {

    // requêtes SQL qui utilisent la mise en cache de requêtes

    $db->noCache(function ($db) {

        // requêtes qui n'utilisent pas la mise en cache de requêtes

    });

    // ...

    return $result;
});
```

Si vous voulez seulement utiliser la mise en cache de requêtes pour une requête unique, vous pouvez appeler la fonction [[yii\db\Command::cache()]] lors de la construction de la commande. Par exemple :

```php
// utilise la mise en cache de requêtes et définit la durée de mise en cache de la requête à 60 secondes
$customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->cache(60)->queryOne();
```

Vous pouvez aussi utiliser la fonction [[yii\db\Command::noCache()]] pour désactiver la mise en cache de requêtes pour une commande unique. Par exemple :

```php
$result = $db->cache(function ($db) {

    // requêtes SQL qui utilisent la mise en cache de requêtes

    // ne pas utiliser la mise en cache de requêtes pour cette commande
    $customer = $db->createCommand('SELECT * FROM customer WHERE id=1')->noCache()->queryOne();

    // ...

    return $result;
});
```


### Limitations <span id="query-caching-limitations"></span>

La mise en cache de requêtes ne fonctionne pas avec des résultats de requêtes qui contiennent des gestionnaires de ressources. Par exemple, lorsque vous utilisez de type de colonne `BLOB` dans certains systèmes de gestion de bases de données (DBMS), la requête retourne un gestionnaire de ressources pour la donnée de la colonne.

Quelques supports de stockage pour cache sont limités en taille. Par exemple, avec memcache, chaque entrée est limitée en taille à 1 MO. En conséquence, si le résultat d'une requête dépasse cette taille, la mise en cache échoue.

