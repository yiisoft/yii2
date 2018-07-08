Objets d'accès aux bases de données
===================================

Construits au dessus des [objets de bases de données PHP (PDO – PHP Data Objects)](http://www.php.net/manual/en/book.pdo.php), les objets d'accès aux bases de données de Yii (DAO – Database Access Objects) fournissent une API orientée objets pour accéder à des bases de données relationnelles. C'est la fondation pour d'autres méthodes d'accès aux bases de données plus avancées qui incluent le [constructeur de requêtes (*query builder*)](db-query-builder.md) et l'[enregistrement actif (*active record*)](db-active-record.md).

Lorsque vous utilisez les objets d'accès aux bases de données de Yii, vous manipulez des requêtes SQL et des tableaux PHP. En conséquence, cela reste le moyen le plus efficace pour accéder aux bases de données. Néanmoins, étant donné que la syntaxe du langage SQL varie selon le type de base de données, l'utilisation des objets d'accès aux bases de données de Yii signifie également que vous avez à faire un travail supplémentaire pour créer une application indifférente au type de base de données. 

Les objets d'accès aux bases de données de Yii prennent en charge les bases de données suivantes sans installation supplémentaire :

- [MySQL](http://www.mysql.com/)
- [MariaDB](https://mariadb.com/)
- [SQLite](http://sqlite.org/)
- [PostgreSQL](http://www.postgresql.org/): version 8.4 or higher.
- [Oracle](http://www.oracle.com/us/products/database/overview/index.html)
- [MSSQL](https://www.microsoft.com/en-us/sqlserver/default.aspx): version 2008 or higher.


## Création de connexions à une base de données <span id="creating-db-connections"></span>

Pour accéder à une base de données, vous devez d'abord vous y connecter en créant une instance de la classe [[yii\db\Connection]] :

```php
$db = new yii\db\Connection([
    'dsn' => 'mysql:host=localhost;dbname=example',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
]);
```

Comme souvent vous devez accéder à une base de données en plusieurs endroits, une pratique commune est de la configurer en terme de [composant d'application ](structure-application-components.md) comme ci-après :

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=example',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
        ],
    ],
    // ...
];
```

Vous pouvez ensuite accéder à la base de données via l'expression `Yii::$app->db`.

> Tip: vous pouvez configurer plusieurs composants d'application « base de données » si votre application a besoin d'accéder à plusieurs bases de données. 

Lorsque vous conifigurez une connexion à une base de données, vous devez toujours spécifier le nom de sa source de données (DSN – Data Source Name) via la propriété [[yii\db\Connection::dsn|dsn]]. Les formats des noms de source de données varient selon le type de base de données. Reportez-vous au [manuel de PHP](http://www.php.net/manual/en/function.PDO-construct.php) pour plus de détails. Ci-dessous, nous donnons quelques exemples :
 
* MySQL, MariaDB: `mysql:host=localhost;dbname=mydatabase`
* SQLite: `sqlite:/path/to/database/file`
* PostgreSQL: `pgsql:host=localhost;port=5432;dbname=mydatabase`
* MS SQL Server (via sqlsrv driver): `sqlsrv:Server=localhost;Database=mydatabase`
* MS SQL Server (via dblib driver): `dblib:host=localhost;dbname=mydatabase`
* MS SQL Server (via mssql driver): `mssql:host=localhost;dbname=mydatabase`
* Oracle: `oci:dbname=//localhost:1521/mydatabase`

Notez que si vous vous connectez à une base de données en utilisant ODBC (Open Database Connectivity), vous devez configurer la propriété [[yii\db\Connection::driverName]] afin que Yii connaisse le type réel de base de données. Par exemple :

```php
'db' => [
    'class' => 'yii\db\Connection',
    'driverName' => 'mysql',
    'dsn' => 'odbc:Driver={MySQL};Server=localhost;Database=test',
    'username' => 'root',
    'password' => '',
],
```

En plus de la propriété [[yii\db\Connection::dsn|dsn]], vous devez souvent configurer les propriétés [[yii\db\Connection::username|username (nom d'utilisateur)]] et [[yii\db\Connection::password|password (mot de passe)]]. Reportez-vous à [[yii\db\Connection]] pour une liste exhaustive des propriétés configurables.

> Info: lorsque vous créez une instance de connexion à une base de données, la connexion réelle à la base de données n'est pas établie tant que vous n'avez pas exécuté la première requête SQL ou appelé la méthode [[yii\db\Connection::open()|open()]] explicitement.

> Tip: parfois, vous désirez effectuer quelques requêtes juste après l'établissement de la connexion à la base de données pour initialiser quelques variables d'environnement (p. ex. pour définir le fuseau horaire ou le jeu de caractères). Vous pouvez le faire en enregistrant un gestionnaire d'événement pour l'événement [[yii\db\Connection::EVENT_AFTER_OPEN|afterOpen]] de la connexion à la base de données. Vous pouvez enregistrer le gestionnaire directement dans la configuration de l'application comme ceci :
> 
> ```php
> 'db' => [
>     // ...
>     'on afterOpen' => function($event) {
>         // $event->sender refers to the DB connection
>         $event->sender->createCommand("SET time_zone = 'UTC'")->execute();
>     }
> ],
> ```


## Execution de requêtes SQL <span id="executing-sql-queries"></span>

Une fois que vous avez une instance de connexion à la base de données, vous pouvez exécuter une requête SQL en suivant les étapes suivantes :

1. Créer une [[yii\db\Command|commande]] avec une requête SQL simple ;
2. Lier les paramètres (facultatif);
3. Appeler l'une des méthodes d'exécution SQL dans la [[yii\db\Command|commande]].

L'exemple qui suit montre différentes façons d'aller chercher des données dans une base de données :
 
```php
// retourne un jeu de lignes. Chaque ligne est un tableau associatif (couples clé-valeur) dont les clés sont des noms de colonnes 
// retourne un tableau vide si la requête ne retourne aucun résultat
$posts = Yii::$app->db->createCommand('SELECT * FROM post')
            ->queryAll();

// retourne une ligne unique (la première ligne) 
// retourne false si la requête ne retourne aucun résultat
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=1')
           ->queryOne();

// retourne une colonne unique (la première colonne) 
//retourne un tableau vide si la requête ne retourne aucun résultat
$titles = Yii::$app->db->createCommand('SELECT title FROM post')
             ->queryColumn();

// retourne une valeur scalaire
// retourne false si la requête ne retourne aucun résultat
$count = Yii::$app->db->createCommand('SELECT COUNT(*) FROM post')
             ->queryScalar();
```

> Note: pour préserver la précision, les données extraites des bases de données sont toutes représentées sous forme de chaînes de caractères, même si les colonnes sont de type numérique. 


### Liaison des paramètres <span id="binding-parameters"></span>

Lorsque vous créez une commande de base de données à partir d'une requête SQL avec des paramètres, vous devriez presque toujours utiliser l'approche de liaison des paramètres pour éviter les attaques par injection SQL. Par exemple : 

```php
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValue(':id', $_GET['id'])
           ->bindValue(':status', 1)
           ->queryOne();
```

Dans l'instruction SQL, vous pouvez incorporer une ou plusieurs valeurs à remplacer pour les paramètres (p. ex. `:id` dans l'exemple ci-dessus). Une valeur à remplacer pour un paramètre doit être une chaîne de caractères commençant par le caractère deux-points `:`. Vous pouvez ensuite appeler l'une des méthodes de liaison de paramètres suivantes pour lier les valeurs de paramètre :

* [[yii\db\Command::bindValue()|bindValue()]]: lie une unique valeur de paramètre
* [[yii\db\Command::bindValues()|bindValues()]]: lie plusieurs valeurs de paramètre en un seul appel
* [[yii\db\Command::bindParam()|bindParam()]]: similaire à [[yii\db\Command::bindValue()|bindValue()]] mais prend aussi en charge la liaison de références à des paramètres

L'exemple suivant montre les manières alternatives de lier des paramètres :

```php
$params = [':id' => $_GET['id'], ':status' => 1];

$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status')
           ->bindValues($params)
           ->queryOne();
           
$post = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id AND status=:status', $params)
           ->queryOne();
```

La liaison des paramètres est implémentée via des [instructions préparées](http://php.net/manual/en/mysqli.quickstart.prepared-statements.php). En plus d'empêcher les attaques par injection SQL, cela peut aussi améliorer la performance en préparant l'instruction SQL une seule fois et l'exécutant de multiples fois avec des paramètres différents. Par exemple :

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id');

$post1 = $command->bindValue(':id', 1)->queryOne();
$post2 = $command->bindValue(':id', 2)->queryOne();
// ...
```

Comme la méthode [[yii\db\Command::bindParam()|bindParam()]] prend en charge la liaison des paramètres par référence, le code ci-dessus peut aussi être écrit comme suit :

```php
$command = Yii::$app->db->createCommand('SELECT * FROM post WHERE id=:id')
              ->bindParam(':id', $id);

$id = 1;
$post1 = $command->queryOne();

$id = 2;
$post2 = $command->queryOne();
// ...
```

Notez que vous devez lier la valeur à remplacer à la variable `$id` avant l'exécution, et ensuite changer la valeur de cette variable avant chacune des exécutions subséquentes (cela est souvent réalisé dans une boucle). L'exécution de requêtes de cette façon peut être largement plus efficace que d'exécuter une nouvelle requête pour chacune des valeurs du paramètre). 


### Exécution de requête sans sélection <span id="non-select-queries"></span>

Les méthodes `queryXyz()` introduites dans les sections précédentes concernent toutes des requêtes SELECT qui retournent des données de la base de données. Pour les instructions qui ne retournent pas de donnée, vous devez appeler la méthode [[yii\db\Command::execute()]] à la place. Par exemple :

```php
Yii::$app->db->createCommand('UPDATE post SET status=1 WHERE id=1')
   ->execute();
```

La méthode [[yii\db\Command::execute()]] exécute retourne le nombre de lignes affectées par l'exécution de la requête SQL. 

Pour les requeêtes INSERT, UPDATE et DELETE, au lieu d'écrire des instructions SQL simples, vous pouvez appeler les méthodes [[yii\db\Command::insert()|insert()]], [[yii\db\Command::update()|update()]] ou [[yii\db\Command::delete()|delete()]], respectivement, pour construire les instructions SQL correspondantes. Ces méthodes entourent correctement les noms de tables et de colonnes par des marques de citation et lient les paramètres. Par exemple :

```php
// INSERT (table name, column values)
Yii::$app->db->createCommand()->insert('user', [
    'name' => 'Sam',
    'age' => 30,
])->execute();

// UPDATE (table name, column values, condition)
Yii::$app->db->createCommand()->update('user', ['status' => 1], 'age > 30')->execute();

// DELETE (table name, condition)
Yii::$app->db->createCommand()->delete('user', 'status = 0')->execute();
```

Vous pouvez aussi appeler [[yii\db\Command::batchInsert()|batchInsert()]] pour insérer plusieurs lignes en un seul coup, ce qui est bien plus efficace que d'insérer une ligne à la fois :

```php
// noms de table, noms de colonne, valeurs de colonne
Yii::$app->db->createCommand()->batchInsert('user', ['name', 'age'], [
    ['Tom', 30],
    ['Jane', 20],
    ['Linda', 25],
])->execute();
```

Notez que les méthodes mentionnées ci-dessus ne font que créer les requêtes, vous devez toujours appeler [[yii\db\Command::execute()|execute()]] pour les exécuter réellement. 


## Entourage de noms de table et de colonne par des marque de citation <span id="quoting-table-and-column-names"></span>

Lorsque l'on écrit du code indifférent au type de base de données, entourer correctement les noms table et de colonne avec des marques de citation et souvent un casse-tête parce que les différentes base de données utilisent des règles de marquage de citation différentes. Pour vous affranchir de cette difficulté, vous pouvez utiliser la syntaxe de citation introduite par Yii :

* `[[column name]]`: entourez un nom de colonne qui doit recevoir les marques de citation par des doubles crochets ; 
* `{{table name}}`: entourez un nom de table qui doit recevoir les marques de citation par des doubles accolades ;

Les objets d'accès aux base de données de Yii convertissent automatiquement de telles constructions en les noms de colonne ou de table correspondants en utilisant la syntaxe spécifique au système de gestion de la base de données. Par exemple :

```php
// exécute cette instruction SQL pour MySQL: SELECT COUNT(`id`) FROM `employee`
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{employee}}")
            ->queryScalar();
```


### Utilisation des préfixes de table <span id="using-table-prefix"></span>

La plupart des noms de table de base de données partagent un préfixe commun. Vous pouvez utiliser la fonctionnalité de gestion du préfixe de noms de table procurée par les objets d'accès aux bases de données de Yii.

Tout d'abord, spécifiez un préfixe de nom de table via la propriété [[yii\db\Connection::tablePrefix]] dans la configuration de l'application :

```php
return [
    // ...
    'components' => [
        // ...
        'db' => [
            // ...
            'tablePrefix' => 'tbl_',
        ],
    ],
];
```

Ensuite dans votre code, à chaque fois que vous faites référence à une table dont le nom commence par ce préfixe, utilisez la syntaxe `{{%table_name}}`. Le caractère pourcentage `%` est automatiquement remplacé par le préfixe que vous avez spécifié dans la configuration de la connexion à la base de données. Par exemple :

```php
// exécute cette instruction SQL pour MySQL: SELECT COUNT(`id`) FROM `tbl_employee`
$count = Yii::$app->db->createCommand("SELECT COUNT([[id]]) FROM {{%employee}}")
            ->queryScalar();
```


## Réalisation de transactions <span id="performing-transactions"></span>

Lorsque vous exécutez plusieurs requêtes liées en séquence, il arrive que vous ayez besoin de les envelopper dans une transactions pour garantir l'intégrité et la cohérence de votre base de données. Si une des requêtes échoue, la base de données est ramenée en arrière dans l'état dans lequel elle se trouvait avant qu'aucune de ces requêtes n'ait été exécutée. 
 
Le code suivant montre une façon typique d'utiliser les transactions :

```php
Yii::$app->db->transaction(function($db) {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... exécution des autres instruction SQL ...
});
```

Le code précédent est équivalent à celui qui suit, et qui vous donne plus de contrôle sur le code de gestion des erreurs :

```php
$db = Yii::$app->db;
$transaction = $db->beginTransaction();

try {
    $db->createCommand($sql1)->execute();
    $db->createCommand($sql2)->execute();
    // ... exécution des autres instruction SQL ...
    
    $transaction->commit();
    
} catch(\Exception $e) {

    $transaction->rollBack();
    
    throw $e;
}
```

En appelant la méthode [[yii\db\Connection::beginTransaction()|beginTransaction()]], une nouvelle transaction est démarrée. La transaction est représentée sous forme d'objet [[yii\db\Transaction]] stocké dans la variable `$transaction`. Ensuite, les requêtes à exécuter sont placées dans un bloc `try...catch...`. Si toutes les requêtes réussissent, la méthode [[yii\db\Transaction::commit()|commit()]] est appelée pour entériner la transaction. Autrement, si une exception a été levée et capturée, la méthode [[yii\db\Transaction::rollBack()|rollBack()]] est appelée pour défaire les changements faits par les requêtes de la transaction antérieures à celle qui a échoué. `throw $e` est alors à nouveau exécutée comme si l'exception n'avait jamais été capturée, ce qui permet au processus normal de gestion des erreurs de s'en occuper.


### Spécification de niveaux d'isolation <span id="specifying-isolation-levels"></span>

Yii prend aussi en charge la définition de [niveaux d'isolation] pour vos transactions. Par défaut, lors du démarrage d'une nouvelle transaction, il utilise le niveau d'isolation par défaut défini par votre système de base de données. Vous pouvez redéfinir le niveau d'isolation comme indiqué ci-après :

```php
$isolationLevel = \yii\db\Transaction::REPEATABLE_READ;

Yii::$app->db->transaction(function ($db) {
    ....
}, $isolationLevel);
 
// ou alternativement

$transaction = Yii::$app->db->beginTransaction($isolationLevel);
```

Yii fournit quatre constantes pour les niveaux d'isolation les plus courants :

- [[\yii\db\Transaction::READ_UNCOMMITTED]] – le niveau le plus faible, des lectures sales (*dirty reads*) , des lectures non répétables) (*non-repeatable reads*) et des lectures phantomes (*phantoms*) peuvent se produire. 
- [[\yii\db\Transaction::READ_COMMITTED]] – évite les lectures sales.
- [[\yii\db\Transaction::REPEATABLE_READ]] – évite les lectures sales et les lectures non répétables. 
- [[\yii\db\Transaction::SERIALIZABLE]] – le niveau le plus élevé, évite tous les problèmes évoqués ci-dessus.



En plus de l'utilisation des constantes présentées ci-dessus pour spécifier un niveau d'isolation, vous pouvez également utiliser des chaînes de caractères avec une syntaxe valide prise en charges par le système de gestion de base de données que vous utilisez. Par exemple, dans PostgreSQL, vous pouvez utiliser `SERIALIZABLE READ ONLY DEFERRABLE`. 

Notez que quelques systèmes de gestion de base de données autorisent la définition des niveaux d'isolation uniquement au niveau de la connexion tout entière. Toutes les transactions subséquentes auront donc le même niveau d'isolation même si vous n'en spécifiez aucun. En utilisant cette fonctionnalité, vous avez peut-être besoin de spécifier le niveau d'isolation de manière explicite pour éviter les conflits de définition. Au moment d'écrire ces lignes, seules MSSQL et SQLite sont affectées par cette limitation. 

> Note: SQLite ne prend en charge que deux niveaux d'isolation, c'est pourquoi vous ne pouvez utiliser que `READ UNCOMMITTED` et `SERIALIZABLE`. L'utilisation d'autres niveaux provoque la levée d'une exception. 

> Note: PostgreSQL n'autorise pas la définition du niveau d'isolation tant que la transaction n'a pas démarré, aussi ne pouvez-vous pas spécifier le niveau d'isolation directement en démarrant la transaction. Dans ce cas, vous devez appeler [[yii\db\Transaction::setIsolationLevel()]] après que la transaction a démarré. 

[isolation levels]: http://en.wikipedia.org/wiki/Isolation_%28database_systems%29#Isolation_levels


### Imbrication des transactions <span id="nesting-transactions"></span>

Si votre système de gestion de base de données prend en charge Savepoint, vous pouvez imbriquer plusieurs transactions comme montré ci-dessous :

```php
Yii::$app->db->transaction(function ($db) {
    //  transaction extérieure
    
    $db->transaction(function ($db) {
        //  transaction intérieure
    });
});
```

Ou en alternative,

```php
$db = Yii::$app->db;
$outerTransaction = $db->beginTransaction();
try {
    $db->createCommand($sql1)->execute();

    $innerTransaction = $db->beginTransaction();
    try {
        $db->createCommand($sql2)->execute();
        $innerTransaction->commit();
    } catch (\Exception $e) {
        $innerTransaction->rollBack();
        throw $e;
    }

    $outerTransaction->commit();
} catch (\Exception $e) {
    $outerTransaction->rollBack();
    throw $e;
}
```


## Réplication et éclatement lecture-écriture <span id="read-write-splitting"></span>

Beaucoup de systèmes de gestion de bases de données prennent en charge la [réplication de la base de données](http://en.wikipedia.org/wiki/Replication_(computing)#Database_replication) pour obtenir une meilleure disponibilité et des temps de réponse de serveur plus courts. Avec la réplication de la base de données, les données sont répliquées depuis les serveurs dits *serveurs maîtres* vers les serveurs dit *serveurs esclaves*. Toutes les écritures et les mises à jour ont lieu sur les serveurs maîtres, tandis que les lectures ont lieu sur les serveurs esclaves.

Pour tirer parti de la réplication des bases de données et réaliser l'éclatement lecture-écriture, vous pouvez configurer un composant [[yii\db\Connection]] comme le suivant :

```php
[
    'class' => 'yii\db\Connection',

    // configuration pour le maître
    'dsn' => 'dsn pour le serveur maître',
    'username' => 'master',
    'password' => '',

    //  configuration commune pour les esclaves
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // utilise un temps d'attente de connexion plus court
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // liste des configurations d'esclave
    'slaves' => [
        ['dsn' => 'dsn pour le serveur esclave 1'],
        ['dsn' => 'dsn pour le serveur esclave 2'],
        ['dsn' => 'dsn pour le serveur esclave 3'],
        ['dsn' => 'dsn pour le serveur esclave 4'],
    ],
]
```

La configuration ci-dessus spécifie une configuration avec un unique maître et de multiples esclaves. L'un des esclaves est connecté et utilisé pour effectuer des requêtes en lecture, tandis que le maître est utilisé pour effectuer les requêtes en écriture. Un tel éclatement lecture-écriture est accompli automatiquement avec cette configuration. Par exemple :

```php
// crée une instance de Connection en utilisant la configuration ci-dessus
Yii::$app->db = Yii::createObject($config);

// effectue une requête auprès d'un des esclaves
$rows = Yii::$app->db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();

// effectue une requête auprès du maître
Yii::$app->db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();
```

> Info: les requêtes effectuées en appelant [[yii\db\Command::execute()]] sont considérées comme des requêtes en écriture, tandis que toutes les autres requêtes faites via l'une des méthodes « *query* » sont des requêtes en lecture. Vous pouvez obtenir la connexion couramment active à un des esclaves via `Yii::$app->db->slave`.

Le composant `Connection` prend en charge l'équilibrage de charge et de basculement entre esclaves. Lorsque vous effectuez une requête en lecture par la première fois, le composant `Connection` sélectionne un esclave de façon aléatoire et essaye de s'y connecter. Si l'esclave set trouvé « mort », il en essaye un autre. Si aucun des esclaves n'est disponible, il se connecte au maître. En configurant un [[yii\db\Connection::serverStatusCache|cache d'état du serveur]], le composant mémorise le serveur « mort » et ainsi, pendant un [[yii\db\Connection::serverRetryInterval|certain intervalle de temps]], n'essaye plus de s'y connecter.

> Info: dans la configuration précédente, un temps d'attente de connexion de 10 secondes est spécifié pour chacun des esclaves. Cela signifie que, si un esclave ne peut être atteint pendant ces 10 secondes, il est considéré comme « mort ». Vous pouvez ajuster ce paramètre en fonction de votre environnement réel. 


Vous pouvez aussi configurer plusieurs maîtres avec plusieurs esclaves. Par exemple : 


```php
[
    'class' => 'yii\db\Connection',

    // configuration commune pour les maîtres
    'masterConfig' => [
        'username' => 'master',
        'password' => '',
        'attributes' => [
            // utilise un temps d'attente de connexion plus court 
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // liste des configurations de maître
    'masters' => [
        ['dsn' => 'dsn for master server 1'],
        ['dsn' => 'dsn for master server 2'],
    ],

    // configuration commune pour les esclaves
    'slaveConfig' => [
        'username' => 'slave',
        'password' => '',
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 10,
        ],
    ],

    // liste des configurations d'esclave 
    'slaves' => [
        ['dsn' => 'dsn for slave server 1'],
        ['dsn' => 'dsn for slave server 2'],
        ['dsn' => 'dsn for slave server 3'],
        ['dsn' => 'dsn for slave server 4'],
    ],
]
```

La configuration ci-dessus spécifie deux maîtres et quatre esclaves. Le composant `Connection` prend aussi en charge l'équilibrage de charge et le basculement entre maîtres juste comme il le fait pour les esclaves. Une différence est que, si aucun des maîtres n'est disponible, une exception est levée.

> Note: lorsque vous utilisez la propriété [[yii\db\Connection::masters|masters]] pour configurer un ou plusieurs maîtres, toutes les autres propriétés pour spécifier une connexion à une base de données (p. ex. `dsn`, `username`, `password`) avec l'objet `Connection` lui-même sont ignorées.


Par défaut, les transactions utilisent la connexion au maître. De plus, dans une transaction, toutes les opérations de base de données utilisent la connexion au maître. Par exemple :

```php
$db = Yii::$app->db;
// la transaction est démarrée sur la connexion au maître
$transaction = $db->beginTransaction();

try {
    //  les deux requêtes sont effectuées auprès du maître
    $rows = $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
    $db->createCommand("UPDATE user SET username='demo' WHERE id=1")->execute();

    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

Si vous voulez démarrer une transaction avec une connexion à un esclave, vous devez le faire explicitement, comme ceci :

```php
$transaction = Yii::$app->db->slave->beginTransaction();
```

Parfois, vous désirez forcer l'utilisation de la connexion au maître pour effectuer une requête en lecture . Cela est possible avec la méthode `useMaster()` :

```php
$rows = Yii::$app->db->useMaster(function ($db) {
    return $db->createCommand('SELECT * FROM user LIMIT 10')->queryAll();
});
```

Vous pouvez aussi définir directement `Yii::$app->db->enableSlaves` à `false` (faux) pour rediriger toutes les requêtes vers la connexion au maître.


## Travail avec le schéma de la base de données <span id="database-schema"></span>

Les objets d'accès aux bases de données de Yii DAO fournissent un jeu complet de méthodes pour vous permettre de manipuler le schéma de la base de données, comme créer de nouvelles tables, supprimer une colonne d'une table, etc. Ces méthodes sont listées ci-après :

* [[yii\db\Command::createTable()|createTable()]]: crée une table
* [[yii\db\Command::renameTable()|renameTable()]]: renomme une table
* [[yii\db\Command::dropTable()|dropTable()]]: supprime une table
* [[yii\db\Command::truncateTable()|truncateTable()]]: supprime toutes les lignes dans une table
* [[yii\db\Command::addColumn()|addColumn()]]: ajoute une colonne
* [[yii\db\Command::renameColumn()|renameColumn()]]: renomme une colonne
* [[yii\db\Command::dropColumn()|dropColumn()]]: supprime une colonne
* [[yii\db\Command::alterColumn()|alterColumn()]]: modifie une colonne
* [[yii\db\Command::addPrimaryKey()|addPrimaryKey()]]: ajoute une clé primaire
* [[yii\db\Command::dropPrimaryKey()|dropPrimaryKey()]]: supprime une clé primaire
* [[yii\db\Command::addForeignKey()|addForeignKey()]]: ajoute un clé étrangère
* [[yii\db\Command::dropForeignKey()|dropForeignKey()]]: supprime une clé étrangère
* [[yii\db\Command::createIndex()|createIndex()]]: crée un index
* [[yii\db\Command::dropIndex()|dropIndex()]]: supprime un index

Ces méthodes peuvent être utilisées comme suit :

```php
// CREATE TABLE
Yii::$app->db->createCommand()->createTable('post', [
    'id' => 'pk',
    'title' => 'string',
    'text' => 'text',
]);
```

Le tableau ci-dessus décrit le nom et le type des colonnes à créer. Pour les types de colonne, Yii fournit un jeu de types de donnée abstraits, qui permettent de définir un schéma de base de données indifférent au type de base de données. Ces types sont convertis en définition de types spécifiques au système de gestion de base de données qui dépendent de la base de données dans laquelle la table est créée. Reportez-vous à la documentation de l'API de la méthode [[yii\db\Command::createTable()|createTable()]] pour plus d'informations.

En plus de changer le schéma de la base de données, vous pouvez aussi retrouver les informations de définition d'une table via la méthode [[yii\db\Connection::getTableSchema()|getTableSchema()]] d'une connexion à une base de données. Par exemple :

```php
$table = Yii::$app->db->getTableSchema('post');
```

La méthode retourne un objet [[yii\db\TableSchema]] qui contient les information sur les colonnes de la table, les clés primaires, les clés étrangères, etc. Toutes ces informations sont essentiellement utilisées par le [constructeur de requêtes](db-query-builder.md) et par l'[enregistrement actif](db-active-record.md) pour vous aider à écrire du code indifférent au type de la base de données.
