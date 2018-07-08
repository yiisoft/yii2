Enregistrement actif (*Active Record*)
===================================== 

L'[enregistrement actif](http://en.wikipedia.org/wiki/Active_record_pattern) fournit une interface orientée objet pour accéder aux données stockées dans une base de données et les manipuler. Une classe d'enregistrement actif est associée à une table de base de données, une instance de cette classe représente une ligne de cette table, et un *attribut* d'une instance d'enregistrement actif représente la valeur d'une colonne particulière dans cette ligne. Au lieu d'écrire des instructions SQL brutes, vous pouvez accéder aux attributs de l'objet enregistrement actif et appeler ses méthodes pour accéder aux données stockées dans les tables de la base de données et les manipuler. 

Par exemple, supposons que `Customer` soit une classe d'enregistrement actif associée à la table `customer` et que `name` soit une colonne de la table `customer`. Vous pouvez écrire le code suivant pour insérer une nouvelle ligne dans la table `customer` :

```php
$customer = new Customer();
$customer->name = 'Qiang';
$customer->save();
```

Le code ci-dessus est équivalent à l'utilisation de l'instruction SQL brute suivante pour MySQL, qui est moins intuitive, plus propice aux erreurs, et peut même poser des problèmes de compatibilité sur vous utilisez un système de gestion de base données différent. 

```php
$db->createCommand('INSERT INTO `customer` (`name`) VALUES (:name)', [
    ':name' => 'Qiang',
])->execute();
```

Yii assure la prise en charge de l'enregistrement actif (*Active Record*) pour les bases de données relationnelles suivantes :

* MySQL 4.1 ou versions postérieures : via [[yii\db\ActiveRecord]]
* PostgreSQL 7.3 ou versions postérieures : via [[yii\db\ActiveRecord]]
* SQLite 2 et 3 : via [[yii\db\ActiveRecord]]
* Microsoft SQL Server 2008 ou versions postérieures : via [[yii\db\ActiveRecord]]
* Oracle : via [[yii\db\ActiveRecord]]
* Sphinx : via [[yii\sphinx\ActiveRecord]], requiert l'extension `yii2-sphinx`
* ElasticSearch : via [[yii\elasticsearch\ActiveRecord]], requiert l'extension `yii2-elasticsearch`

De plus, Yii prend aussi en charge l'enregistrement actif (*Active Record*) avec les bases de données non SQL suivantes :

* Redis 2.6.12 ou versions postérieures : via [[yii\redis\ActiveRecord]], requiert l'extension `yii2-redis`
* MongoDB 1.3.0 ou versions postérieures: via [[yii\mongodb\ActiveRecord]], requiert l'extension `yii2-mongodb`

Dans ce tutoriel, nous décrivons essentiellement l'utilisation de l'enregistrement actif pour des bases de données relationnelles. Cependant, la majeure partie du contenu décrit ici est aussi applicable aux bases de données non SQL.


## Déclaration des classes d'enregistrement actif (*Active Record*) <span id="declaring-ar-classes"></span>

Pour commencer, déclarez une classe d'enregistrement actif en étendant la classe [[yii\db\ActiveRecord]]. Comme chacune des classes d'enregistrement actif est associée à une table de la base de données, dans cette classe, vous devez redéfinir la méthode [[yii\db\ActiveRecord::tableName()|tableName()]]
pour spécifier à quelle table cette classe est associée. Dans l'exemple qui suit, nous déclarons une classe d'enregistrement actif nommée `Customer` pour la table de base de données `customer`.

```php
namespace app\models;

use yii\db\ActiveRecord;

class Customer extends ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    
    /**
     * @return string le nom de la table associée à cette classe d'enregistrement actif.
     */
    public static function tableName()
    {
        return 'customer';
    }
}
```

Les instances d'une classe d'enregistrement actif (*Active Record*) sont considérées comme des [modèles](structure-models.md). Pour cette raison, nous plaçons les classes d'enregistrement actif dans l'espace de noms `app\models`(ou autres espaces de noms prévus pour contenir des classes de modèles). 

Comme la classe [[yii\db\ActiveRecord]] étend la classe [[yii\base\Model]], elle hérite de *toutes* les fonctionnalités d'un [modèle](structure-models.md), comme les attributs, les règles de validation, la sérialisation des données, etc.


## Connexion aux bases de données <span id="db-connection"></span>

Par défaut, l'enregistrement actif utilise le [composant d'application](structure-application-components.md) `db` en tant que [[yii\db\Connection|DB connexion à une base de données]] pour accéder aux données de la base de données et les manipuler. Comme expliqué dans la section [Objets d'accès aux bases de données](db-dao.md), vous pouvez configurer le composant `db` dans la configuration de l'application comme montré ci-dessous :

```php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=testdb',
            'username' => 'demo',
            'password' => 'demo',
        ],
    ],
];
```

Si vous voulez utiliser une connexion de base de données autre que le composant `db`, vous devez redéfinir la méthode [[yii\db\ActiveRecord::getDb()|getDb()]] :


```php
class Customer extends ActiveRecord
{
    // ...

    public static function getDb()
    {
        // utilise le composant d'application "db2"
        return \Yii::$app->db2;
    }
}
```


## Requête de données <span id="querying-data"></span>

Après avoir déclaré une classe d'enregistrement actif, vous pouvez l'utiliser pour faire une requête de données de la table correspondante dans la base de données. Ce processus s'accomplit en général en trois étapes :

1. Créer un nouvel objet *query* (requête) en appelant la méthode [[yii\db\ActiveRecord::find()]] ;
2. Construire l'objet *query* en appelant des [méthodes de construction de requête](db-query-builder.md#building-queries);
3. Appeler une [méthode de requête](db-query-builder.md#query-methods) pour retrouver les données en terme d'instances d'enregistrement actif. 

Comme vous pouvez le voir, cela est très similaire à la procédure avec le [constructeur de requêtes](db-query-builder.md). La seule différence est que, au lieu d'utiliser l'opérateur `new` pour créer un objet *query* (requête), vous appelez la méthode [[yii\db\ActiveRecord::find()]] pour retourner un nouvel objet *query* qui est de la classe [[yii\db\ActiveQuery]].

Ce-dessous, nous donnons quelques exemples qui montrent comment utiliser l'*Active Query* (requête active) pour demander des données : 

```php
// retourne un client (*customer*) unique dont l'identifiant est 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::find()
    ->where(['id' => 123])
    ->one();

// retourne tous les clients actifs et les classes par leur identifiant
// SELECT * FROM `customer` WHERE `status` = 1 ORDER BY `id`
$customers = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->orderBy('id')
    ->all();

// retourne le nombre de clients actifs 
// SELECT COUNT(*) FROM `customer` WHERE `status` = 1
$count = Customer::find()
    ->where(['status' => Customer::STATUS_ACTIVE])
    ->count();

// retourne tous les clients dans un tableau indexé par l'identifiant du client 
// SELECT * FROM `customer`
$customers = Customer::find()
    ->indexBy('id')
    ->all();
```

Dans le code ci-dessus, `$customer` est un objet `Customer` tandis que `$customers` est un tableau d'objets `Customer`. Ils sont tous remplis par les données retrouvées dans la table `customer`.

> Info: comme la classe [[yii\db\ActiveQuery]] étend la classe [[yii\db\Query]], vous pouvez utiliser *toutes* les méthodes de construction et de requête comme décrit dans la section sur le [constructeur de requête](db-query-builder.md).

Parce que faire une requête de données par les valeurs de clés primaires ou par jeu de valeurs de colonne est une tâche assez courante, Yii fournit une prise en charge de méthodes raccourcis pour cela :

- [[yii\db\ActiveRecord::findOne()]]: retourne une instance d'enregistrement actif remplie avec la première ligne du résultat de la requête.
- [[yii\db\ActiveRecord::findAll()]]: retourne un tableau d'instances d'enregistrement actif rempli avec *tous* les résultats de la requête.

Les deux méthodes acceptent un des formats de paramètres suivants :

- une valeur scalaire : la valeur est traitée comme la valeur de la clé primaire à rechercher. Yii détermine automatiquement quelle colonne est la colonne de clé primaire en lisant les informations du schéma de la base de données.
- un tableau de valeurs scalaires : le tableau est traité comme les valeurs de clé primaire désirées à rechercher.
- un tableau associatif : les clés sont les noms de colonne et les valeurs sont les valeurs de colonne désirées à rechercher. Reportez-vous au [format haché](db-query-builder.md#hash-format) pour plus de détails.
  
Le code qui suit montre comment ces méthodes peuvent être utilisées :

```php
// retourne un client unique dont l'identifiant est 123
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// retourne les clients dont les identifiants sont 100, 101, 123 ou 124
// SELECT * FROM `customer` WHERE `id` IN (100, 101, 123, 124)
$customers = Customer::findAll([100, 101, 123, 124]);

// retourne un client actif dont l'identifiant est 123
// SELECT * FROM `customer` WHERE `id` = 123 AND `status` = 1
$customer = Customer::findOne([
    'id' => 123,
    'status' => Customer::STATUS_ACTIVE,
]);

// retourne tous les clients inactifs 
// SELECT * FROM `customer` WHERE `status` = 0
$customers = Customer::findAll([
    'status' => Customer::STATUS_INACTIVE,
]);
```

> Note: ni [[yii\db\ActiveRecord::findOne()]], ni [[yii\db\ActiveQuery::one()]] n'ajoutent `LIMIT 1` à l'instruction SQL générée. Si votre requête peut retourner plusieurs lignes de données, vous devez appeler `limit(1)` explicitement pour améliorer la performance, p. ex., `Customer::find()->limit(1)->one()`.

En plus d'utiliser les méthodes de construction de requête, vous pouvez aussi écrire du SQL brut pour effectuer une requête de données et vous servir des résultats pour remplir des objets enregistrements actifs. Vous pouvez le faire en appelant la méthode [[yii\db\ActiveRecord::findBySql()]] :

```php
// retourne tous les clients inactifs
$sql = 'SELECT * FROM customer WHERE status=:status';
$customers = Customer::findBySql($sql, [':status' => Customer::STATUS_INACTIVE])->all();
```

N'appelez pas de méthodes de construction de requêtes supplémentaires après avoir appelé [[yii\db\ActiveRecord::findBySql()|findBySql()]] car elles seront ignorées. 

## Accès aux données <span id="accessing-data"></span>

Comme nous l'avons mentionné plus haut, les données extraites de la base de données servent à remplir des instances de la classe d'enregistrement actif et chacune des lignes du résultat de la requête correspond à une instance unique de la classe d'enregistrement actif. Vous pouvez accéder accéder aux valeurs des colonnes en accédant aux attributs des instances de la classe d'enregistrement actif, par exemple : 

```php
// "id" et "email" sont les noms des colonnes de la table "customer"
$customer = Customer::findOne(123);
$id = $customer->id;
$email = $customer->email;
```

> Note: les attributs de l'instance de la classe d'enregistrement actif sont nommés d'après les noms des colonnes de la table associée en restant sensible à la casse. Yii définit automatiquement un attribut dans l'objet enregistrement actif pour chacune des colonnes de la table associée. Vous ne devez PAS déclarer à nouveau l'un quelconque des ces attributs. 

Comme les attributs de l'instance d'enregistrement actif sont nommés d'après le nom des colonnes, vous pouvez vous retrouver en train d'écrire du code PHP tel que `$customer->first_name`, qui utilise le caractère *souligné* pour séparer les mots dans les noms d'attributs si vos colonnes de table sont nommées de cette manière. Si vous êtes attaché à la cohérence du style de codage, vous devriez renommer vos colonnes de tables en conséquence (p. ex. en utilisant la notation en dos de chameau). 

### Transformation des données <span id="data-transformation"></span>

Il arrive souvent que les données entrées et/ou affichées soient dans un format qui diffère de celui utilisé pour stocker les données dans la base. Par exemple, dans la base de données, vous stockez la date d'anniversaire des clients sous la forme d'horodatages UNIX (bien que ce soit pas une conception des meilleures), tandis que dans la plupart des cas, vous avez envie de manipuler les dates d'anniversaire sous la forme de chaînes de caractères dans le format `'YYYY/MM/DD'`. Pour le faire, vous pouvez définir des méthodes de *transformation de données* dans la classe d'enregistrement actif comme ceci : 

```php
class Customer extends ActiveRecord
{
    // ...

    public function getBirthdayText()
    {
        return date('Y/m/d', $this->birthday);
    }
    
    public function setBirthdayText($value)
    {
        $this->birthday = strtotime($value);
    }
}
```

Désormais, dans votre code PHP, au lieu d'accéder à `$customer->birthday`, vous devez accéder à `$customer->birthdayText`, ce qui vous permet d'entrer et d'afficher les dates d'anniversaire dans le format `'YYYY/MM/DD'`.

> Tip: l'exemple qui précède montre une manière générique de transformer des données dans différents formats. Si vous travaillez avec des valeurs de dates, vous pouvez utiliser [DateValidator](tutorial-core-validators.md#date) et [[yii\jui\DatePicker|DatePicker]], qui sont plus faciles à utiliser et plus puissants.


### Retrouver des données dans des tableaux <span id="data-in-arrays"></span>

Alors que retrouver des données en termes d'objets enregistrements actifs est souple et pratique, cela n'est pas toujours souhaitable lorsque vous devez extraire une grande quantité de données à cause de l'empreinte mémoire très importante. Dans ce cas, vous pouvez retrouver les données en utilisant des tableaux PHP en appelant [[yii\db\ActiveQuery::asArray()|asArray()]] avant d'exécuter une méthode de requête :

```php
// retourne tous les clients
// chacun des clients est retourné sous forme de tableau associatif
$customers = Customer::find()
    ->asArray()
    ->all();
```

> Note: bien que cette méthode économise de la mémoire et améliore la performance, elle est plus proche de la couche d'abstraction basse de la base de données et perd la plupart des fonctionnalité de l'objet enregistrement actif. Une distinction très importante réside dans le type de données des valeurs des colonnes. Lorsque vous retournez des données dans une instance d'enregistrement actif, les valeurs des colonnes sont automatiquement typées en fonction du type réel des colonnes ; par contre, lorsque vous retournez des données dans des tableaux, les valeurs des colonnes sont des chaînes de caractères (parce qu'elles résultent de PDO sans aucun traitement), indépendamment du type réel de ces colonnes.

### Retrouver des données dans des lots <span id="data-in-batches"></span>

Dans la section sur le [constructeur de requêtes](db-query-builder.md), nous avons expliqué que vous pouvez utiliser des *requêtes par lots* pour minimiser l'utilisation de la mémoire lorsque vous demandez de grandes quantités de données de la base de données. Vous pouvez utiliser la même technique avec l'enregistrement actif. Par exemple :

```php
// va chercher 10 clients (customer) à la fois
foreach (Customer::find()->batch(10) as $customers) {
    // $customers est un tableau de 10 (ou moins) objets Customer 
}

// va chercher 10 clients (customers) à la fois et itère sur chacun d'eux 
foreach (Customer::find()->each(10) as $customer) {
    // $customer est un objet Customer 
}

// requête par lots avec chargement précoce 
foreach (Customer::find()->with('orders')->each() as $customer) {
    // $customer est un objet Customer avec la relation 'orders' remplie
}
```


## Sauvegarde des données <span id="inserting-updating-data"></span>

En utilisant l'enregistrement actif, vous pouvez sauvegarder facilement les données dans la base de données en suivant les étapes suivantes :

1. Préparer une instance de la classe d'enregistrement actif
2. Assigner de nouvelles valeurs aux attributs de cette instance
3. Appeler [[yii\db\ActiveRecord::save()]] pour sauvegarder les données dans la base de données.

Par exemple :

```php
// insère une nouvelle ligne de données
$customer = new Customer();
$customer->name = 'James';
$customer->email = 'james@example.com';
$customer->save();

// met à jour une ligne de données existante
$customer = Customer::findOne(123);
$customer->email = 'james@newexample.com';
$customer->save();
```

La méthode [[yii\db\ActiveRecord::save()|save()]] peut soit insérer, soit mettre à jour une ligne de données, selon l'état de l'instance de l'enregistrement actif. Si l'instance est en train d'être créée via l'opérateur `new`, appeler [[yii\db\ActiveRecord::save()|save()]] provoque l'insertion d'une nouvelle ligne de données ; si l'instance est le résultat d'une méthode de requête, appeler [[yii\db\ActiveRecord::save()|save()]] met à jour la ligne associée à l'instance. 

Vous pouvez différentier les deux états d'une instance d'enregistrement actif en testant la valeur de sa propriété [[yii\db\ActiveRecord::isNewRecord|isNewRecord]]. Cette propriété est aussi utilisée par [[yii\db\ActiveRecord::save()|save()]] en interne, comme ceci :

```php
public function save($runValidation = true, $attributeNames = null)
{
    if ($this->getIsNewRecord()) {
        return $this->insert($runValidation, $attributeNames);
    } else {
        return $this->update($runValidation, $attributeNames) !== false;
    }
}
```

> Tip: vous pouvez appeler [[yii\db\ActiveRecord::insert()|insert()]] ou [[yii\db\ActiveRecord::update()|update()]] directement pour insérer ou mettre à jour une ligne.
  

### Validation des données <span id="data-validation"></span>

Comme la classe [[yii\db\ActiveRecord]] étend la classe [[yii\base\Model]], elle partage la même fonctionnalité de [validation des données](input-validation.md). Vous pouvez déclarer les règles de validation en redéfinissant la méthode [[yii\db\ActiveRecord::rules()|rules()]] et effectuer la validation des données en appelant la méthode [[yii\db\ActiveRecord::validate()|validate()]].

Lorsque vous appelez la méthode [[yii\db\ActiveRecord::save()|save()]], par défaut, elle appelle automatiquement la méthode [[yii\db\ActiveRecord::validate()|validate()]]. C'est seulement si la validation réussit, que les données sont effectivement sauvegardées ; autrement elle retourne simplement `false`, et vous pouvez tester la propriété [[yii\db\ActiveRecord::errors|errors]] pour retrouver les messages d'erreurs de validation.

> Tip: si vous êtes sûr que vos données n'ont pas besoin d'être validées (p. ex. vos données proviennent de sources fiables), vous pouvez appeler `save(false)` pour omettre la validation.


### Assignation massive <span id="massive-assignment"></span>

Comme les [modèles](structure-models.md) habituels, les instances d'enregistrement actif profitent de la [fonctionnalité d'assignation massive](structure-models.md#massive-assignment). L'utilisation de cette fonctionnalité vous permet d'assigner plusieurs attributs d'un enregistrement actif en une seule instruction PHP, comme c'est montré ci-dessous. N'oubliez cependant pas que, seuls les [attributs sûrs](structure-models.md#safe-attributes) sont assignables en masse. 

```php
$values = [
    'name' => 'James',
    'email' => 'james@example.com',
];

$customer = new Customer();

$customer->attributes = $values;
$customer->save();
```


### Mise à jour des compteurs <span id="updating-counters"></span>

C'est une tâche courante que d'incrémenter ou décrémenter une colonne dans une table de base de données. Nous appelons ces colonnes « colonnes compteurs*. Vous pouvez utiliser la méthode [[yii\db\ActiveRecord::updateCounters()|updateCounters()]] pour mettre à jour une ou plusieurs colonnes de comptage. Par exemple : 

```php
$post = Post::findOne(100);

// UPDATE `post` SET `view_count` = `view_count` + 1 WHERE `id` = 100
$post->updateCounters(['view_count' => 1]);
```

> Note: si vous utilisez la méthode [[yii\db\ActiveRecord::save()]] pour mettre à jour une colonne compteur, vous pouvez vous retrouver avec un résultat erroné car il est probable que le même compteur soit sauvegardé par de multiples requêtes qui lisent et écrivent la même valeur de compteur.


### Attributs sales (*Dirty Attributes*) <span id="dirty-attributes"></span>

Lorsque vous appelez la méthode [[yii\db\ActiveRecord::save()|save()]] pour sauvegarder une instance d'enregistrement actif, seuls les attributs dit *attributs sales* sont sauvegardés. Un attribut est considéré comme *sale* si sa valeur a été modifiée depuis qu'il a été chargé depuis la base de données ou sauvegardé dans la base de données le plus récemment. Notez que la validation des données est assurée sans se préoccuper de savoir si l'instance d'enregistrement actif possède des attributs sales ou pas. 

L'enregistrement actif tient à jour la liste des attributs sales. Il le fait en conservant une version antérieure des valeurs d'attribut et en les comparant avec les dernières. Vous pouvez appeler la méthode [[yii\db\ActiveRecord::getDirtyAttributes()]] pour obtenir les attributs qui sont couramment sales. Vous pouvez aussi appeler la méthode [[yii\db\ActiveRecord::markAttributeDirty()]] pour marquer explicitement un attribut comme sale. 

Si vous êtes intéressé par les valeurs d'attribut antérieurs à leur plus récente modification, vous pouvez appeler la méthode [[yii\db\ActiveRecord::getOldAttributes()|getOldAttributes()]] ou la méthode [[yii\db\ActiveRecord::getOldAttribute()|getOldAttribute()]].

> Note: la comparaison entre les anciennes et les nouvelles valeurs est faite en utilisant l'opérateur `===` , ainsi une valeur est considérée comme sale si le type est différent même si la valeur reste la même. Cela est souvent le cas lorsque le modèle reçoit des entrées utilisateur de formulaires HTML ou chacune des valeurs est représentée par une chaîne de caractères. Pour garantir le type correct pour p. ex. des valeurs entières, vous devez appliquer un [filtre de validation](input-validation.md#data-filtering):
> `['attributeName', 'filter', 'filter' => 'intval']`. Cela fonctionne pour toutes les fonctions de transformation de type de PHP comme [intval()](http://php.net/manual/en/function.intval.php), [floatval()](http://php.net/manual/en/function.floatval.php), [boolval](http://php.net/manual/en/function.boolval.php), etc...

### Valeurs d'attribut par défaut <span id="default-attribute-values"></span>

Quelques unes de vos colonnes de tables peuvent avoir des valeurs par défaut définies dans la base de données. Parfois, vous voulez peut-être pré-remplir votre formulaire Web pour un enregistrement actif à partir des valeurs par défaut. Pour éviter d'écrire la même valeur par défaut à nouveau, vous pouvez appeler la méthode [[yii\db\ActiveRecord::loadDefaultValues()|loadDefaultValues()]] pour remplir les attributs de l'enregistrement actif avec les valeurs par défaut prédéfinies dans la base de données :

```php
$customer = new Customer();
$customer->loadDefaultValues();
// $customer->xyz recevra la valeur par défaut déclarée lors de la définition de la colonne « xyz » column
```


### Mise à jour de plusieurs lignes <span id="updating-multiple-rows"></span>

Les méthodes décrites ci-dessus fonctionnent toutes sur des instances individuelles d'enregistrement actif. Pour mettre à jour plusieurs lignes à la fois, vous devez appeler la méthode statique [[yii\db\ActiveRecord::updateAll()|updateAll()]].

```php
// UPDATE `customer` SET `status` = 1 WHERE `email` LIKE `%@example.com%`
Customer::updateAll(['status' => Customer::STATUS_ACTIVE], ['like', 'email', '@example.com']);
```

De façon similaire, vous pouvez appeler [[yii\db\ActiveRecord::updateAllCounters()|updateAllCounters()]] pour mettre à jour les colonnes compteurs de plusieurs lignes à la fois.

```php
// UPDATE `customer` SET `age` = `age` + 1
Customer::updateAllCounters(['age' => 1]);
```


## Suppression de données <span id="deleting-data"></span>

Pour supprimer une ligne unique de données, commencez par retrouver l'instance d'enregistrement actif correspondant à cette ligne et appelez la méthode [[yii\db\ActiveRecord::delete()]].

```php
$customer = Customer::findOne(123);
$customer->delete();
```

Vous pouvez appeler [[yii\db\ActiveRecord::deleteAll()]] pour effacer plusieurs ou toutes les lignes de données. Par exemple :

```php
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

> Note: soyez très prudent lorsque vous appelez [[yii\db\ActiveRecord::deleteAll()|deleteAll()]] parce que cela peut effacer totalement toutes les données de votre table si vous faites une erreur en spécifiant la condition. 


## Cycles de vie de l'enregistrement actif <span id="ar-life-cycles"></span>

Il est important que vous compreniez les cycles de vie d'un enregistrement actif lorsqu'il est utilisé à des fins différentes. Lors de chaque cycle de vie, une certaine séquence d'invocation de méthodes a lieu, et vous pouvez redéfinir ces méthodes pour avoir une chance de personnaliser le cycle de vie. Vous pouvez également répondre à certains événements de l'enregistrement actif déclenchés durant un cycle de vie pour injecter votre code personnalisé. Ces événements sont particulièrement utiles lorsque vous développez des [comportements](concept-behaviors.md) d'enregistrement actif qui ont besoin de personnaliser les cycles de vies d'enregistrement actifs. 

Dans l'exemple précédent, nous résumons les différents cycles de vie d'enregistrement actif et les méthodes/événements à qui il est fait appel dans ces cycles.


### Cycle de vie d'une nouvelle instance <span id="new-instance-life-cycle"></span>

Losque vous créez un nouvel enregistrement actif via l'opérateur `new`, le cycle suivant se réalise :

1. Constructeur de la classe.
2. [[yii\db\ActiveRecord::init()|init()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]].


### Cycle de vie lors d'une requête de données <span id="querying-data-life-cycle"></span>

Lorsque vous effectuez une requête de données via l'une des [méthodes de requête](#querying-data), chacun des enregistrements actifs nouvellement rempli entreprend le cycle suivant :

1. Constructeur de la classe.
2. [[yii\db\ActiveRecord::init()|init()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_INIT|EVENT_INIT]].
3. [[yii\db\ActiveRecord::afterFind()|afterFind()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_AFTER_FIND|EVENT_AFTER_FIND]].


### Cycle de vie lors d'une sauvegarde de données <span id="saving-data-life-cycle"></span>

En appelant [[yii\db\ActiveRecord::save()|save()]] pour insérer ou mettre à jour une instance d'enregistrement actif, le cycle de vie suivant se réalise :

1. [[yii\db\ActiveRecord::beforeValidate()|beforeValidate()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_BEFORE_VALIDATE|EVENT_BEFORE_VALIDATE]] . Si la méthode retourne `false` (faux), ou si [[yii\base\ModelEvent::isValid]] est `false`, les étapes suivantes sont sautées.
2. Effectue la validation des données. Si la validation échoue, les étapes après l'étape 3 saut sautées. 
3. [[yii\db\ActiveRecord::afterValidate()|afterValidate()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_AFTER_VALIDATE|EVENT_AFTER_VALIDATE]].
4. [[yii\db\ActiveRecord::beforeSave()|beforeSave()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_BEFORE_INSERT|EVENT_BEFORE_INSERT]] 
   ou un événement [[yii\db\ActiveRecord::EVENT_BEFORE_UPDATE|EVENT_BEFORE_UPDATE]]. Si la méthode retourne `false` ou si [[yii\base\ModelEvent::isValid]] est `false`, les étapes suivantes sont sautées. 
5. Effectue l'insertion ou la mise à jour réelle.
6. [[yii\db\ActiveRecord::afterSave()|afterSave()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_AFTER_INSERT|EVENT_AFTER_INSERT]] ou un événement [[yii\db\ActiveRecord::EVENT_AFTER_UPDATE|EVENT_AFTER_UPDATE]].
   

### Cycle de vie lors d'une suppression de données <span id="deleting-data-life-cycle"></span>

En appelant [[yii\db\ActiveRecord::delete()|delete()]] pour supprimer une instance d'enregistrement actif, le cycle suivant se déroule :

1. [[yii\db\ActiveRecord::beforeDelete()|beforeDelete()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_BEFORE_DELETE|EVENT_BEFORE_DELETE]]. Si la méthode retourne `false` ou si [[yii\base\ModelEvent::isValid]] est `false`, les étapes suivantes sont sautées. 
2. Effectue la suppression réelle des données.
3. [[yii\db\ActiveRecord::afterDelete()|afterDelete()]]: déclenche un événement [[yii\db\ActiveRecord::EVENT_AFTER_DELETE|EVENT_AFTER_DELETE]].


> Note: l'appel de l'une des méthodes suivantes n'initie AUCUN des cycles vus ci-dessus parce qu'elles travaillent directement sur la base de données et pas sur la base d'un enregistrement actif :
>
> - [[yii\db\ActiveRecord::updateAll()]] 
> - [[yii\db\ActiveRecord::deleteAll()]]
> - [[yii\db\ActiveRecord::updateCounters()]] 
> - [[yii\db\ActiveRecord::updateAllCounters()]] 

### Cycle de vie lors du rafraîchissement des données <span id="refreshing-data-life-cycle"></span>

En appelant [[yii\db\ActiveRecord::refresh()|refresh()]] pour rafraîchir une instance d'enregistrement actif, l'événement [[yii\db\ActiveRecord::EVENT_AFTER_REFRESH|EVENT_AFTER_REFRESH]] est déclenché si le rafraîchissement réussit et si la méthode retourne `true`.


## Travail avec des transactions <span id="transactional-operations"></span>

Il y a deux façons d'utiliser les [transactions](db-dao.md#performing-transactions) lorsque l'on travaille avec un enregistrement actif. 

La première façon consiste à enfermer explicitement les appels des différents méthodes dans un bloc transactionnel, comme ci-dessous :

```php
$customer = Customer::findOne(123);

Customer::getDb()->transaction(function($db) use ($customer) {
    $customer->id = 200;
    $customer->save();
    // ...autres opérations de base de données...
});

// ou en alternative

$transaction = Customer::getDb()->beginTransaction();
try {
    $customer->id = 200;
    $customer->save();
    // ...other DB operations...
    $transaction->commit();
} catch(\Exception $e) {
    $transaction->rollBack();
    throw $e;
}
```

La deuxième façon consiste à lister les opérations de base de données qui nécessitent une prise en charge transactionnelle dans la méthode [[yii\db\ActiveRecord::transactions()]]. Par exemple :

```php
class Customer extends ActiveRecord
{
    public function transactions()
    {
        return [
            'admin' => self::OP_INSERT,
            'api' => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            // ce qui précède est équivalent à ce qui suit :
            // 'api' => self::OP_ALL,
        ];
    }
}
```

La méthode [[yii\db\ActiveRecord::transactions()]] doit retourner un tableau dont les clés sont les noms de [scenario](structure-models.md#scenarios) et les valeurs les opérations correspondantes qui doivent être enfermées dans des transactions. Vous devez utiliser les constantes suivantes pour faire référence aux différentes opérations de base de données :

* [[yii\db\ActiveRecord::OP_INSERT|OP_INSERT]]: opération d'insertion réalisée par [[yii\db\ActiveRecord::insert()|insert()]];
* [[yii\db\ActiveRecord::OP_UPDATE|OP_UPDATE]]: opération de mise à jour réalisée par [[yii\db\ActiveRecord::update()|update()]];
* [[yii\db\ActiveRecord::OP_DELETE|OP_DELETE]]: opération de suppression réalisée par [[yii\db\ActiveRecord::delete()|delete()]].

Utilisez l'opérateur `|` pour concaténer les constantes précédentes pour indiquer de multiples opérations. Vous pouvez également utiliser la constante raccourci [[yii\db\ActiveRecord::OP_ALL|OP_ALL]] pour faire référence à l'ensemble des trois opération ci-dessus.

Les transactions qui sont créées en utilisant cette méthode sont démarrées avant d'appeler [[yii\db\ActiveRecord::beforeSave()|beforeSave()]] et sont entérinées après que la méthode [[yii\db\ActiveRecord::afterSave()|afterSave()]] a été exécutée.

## Verrous optimistes <span id="optimistic-locks"></span>

Le verrouillage optimiste est une manière d'empêcher les conflits qui peuvent survenir lorsqu'une même ligne de données est mise à jour par plusieurs utilisateurs. Par exemple, les utilisateurs A et B sont tous deux, simultanément, en train de modifier le même article de wiki. Après que l'utilisateur A a sauvegardé ses modifications, l'utilisateur B clique sur le bouton « Sauvegarder » dans le but de sauvegarder ses modifications lui aussi. Comme l'utilisateur B est en train de travailler sur une version périmée de l'article, il serait souhaitable de disposer d'un moyen de l'empêcher de sauvegarder sa version de l'article et de lui montrer un message d'explication.

Le verrouillage optimiste résout le problème évoqué ci-dessus en utilisant une colonne pour enregistrer le numéro de version de chacune des lignes. Lorsqu'une ligne est sauvegardée avec un numéro de version périmée, une exception [[yii\db\StaleObjectException]] est levée, ce qui empêche la sauvegarde de la ligne. Le verrouillage optimiste, n'est seulement pris en charge que lorsque vous mettez à jour ou supprimez une ligne de données existante en utilisant les méthodes [[yii\db\ActiveRecord::update()]] ou [[yii\db\ActiveRecord::delete()]],respectivement.

Pour utiliser le verrouillage optimiste :

1. Créez une colonne dans la table de base de données associée à la classe d'enregistrement actif pour stocker le numéro de version de chacune des lignes. Le colonne doit être du type *big integer* (dans MySQL ce doit être `BIGINT DEFAULT 0`).
2. Redéfinissez la méthode [[yii\db\ActiveRecord::optimisticLock()]] pour qu'elle retourne le nom de cette colonne.
3. Dans le formulaire Web qui reçoit les entrées de l'utilisateur, ajoutez un champ caché pour stocker le numéro de version courant de la ligne en modification. Assurez-vous que votre attribut *version* dispose de règles de validation et valide correctement. 
4. Dans l'action de contrôleur qui met la ligne à jour en utilisant l'enregistrement actif, utiliser une structure *try-catch* pour l'exception [[yii\db\StaleObjectException]]. Mettez en œuvre la logique requise (p. ex. fusionner les modification, avertir des données douteuses) pour résoudre le conflit.
Par exemple, supposons que la colonne du numéro de version est nommée `version`. Vous pouvez mettre en œuvre le verrouillage optimiste avec un code similaire au suivant :

```php
// ------ view code -------

use yii\helpers\Html;

// ...autres champs de saisie
echo Html::activeHiddenInput($model, 'version');


// ------ controller code -------

use yii\db\StaleObjectException;

public function actionUpdate($id)
{
    $model = $this->findModel($id);

    try {
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    } catch (StaleObjectException $e) {
        // logique de résolution du conflit 
    }
}
```


## Travail avec des données relationnelles <span id="relational-data"></span>

En plus de travailler avec des tables de base de données individuelles, l'enregistrement actif permet aussi de rassembler des données en relation, les rendant ainsi immédiatement accessibles via les données primaires. Par exemple, la donnée client est en relation avec les données commandes parce qu'un client peut avoir passé une ou plusieurs commandes. Avec les déclarations appropriées de cette relation, vous serez capable d'accéder aux commandes d'un client en utilisant l'expression `$customer->orders` qui vous renvoie les informations sur les commandes du client en terme de tableau d'instances `Order` (Commande) d'enregistrement actif.


### Déclaration de relations <span id="declaring-relations"></span>

Pour travailler avec des données relationnelles en utilisant l'enregistrement actif, vous devez d'abord déclarer les relations dans les classes d'enregistrement actif. La tâche est aussi simple que de déclarer une *méthode de relation* pour chacune des relations concernées, comme ceci :

```php
class Customer extends ActiveRecord
{
    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    // ...

    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
```

Dans le code ci-dessus, nous avons déclaré une relation `orders` (commandes) pour la classe `Customer` (client), et une relation `customer` (client) pour la classe `Order` (commande). 

Chacune des méthodes de relation doit être nommée sous la forme `getXyz`. Nous appelons `xyz` (la première lettre est en bas de casse) le *nom de la relation*. Notez que les noms de relation sont *sensibles à la casse*.

En déclarant une relation, vous devez spécifier les informations suivantes :

- la multiplicité de la relation : spécifiée en appelant soit la méthode [[yii\db\ActiveRecord::hasMany()|hasMany()]], soit la méthode [[yii\db\ActiveRecord::hasOne()|hasOne()]]. Dans l'exemple ci-dessus vous pouvez facilement déduire en lisant la déclaration des relations qu'un client a beaucoup de commandes, tandis qu'une commande n'a qu'un client.
- le nom de la classe d'enregistrement actif : spécifié comme le premier paramètre de [[yii\db\ActiveRecord::hasMany()|hasMany()]] ou de [[yii\db\ActiveRecord::hasOne()|hasOne()]]. Une pratique conseillée est d'appeler `Xyz::class` pour obtenir la chaîne de caractères représentant le nom de la classe de manière à bénéficier de l'auto-complètement de l'EDI et de la détection d'erreur dans l'étape de compilation. 
- Le lien entre les deux types de données : spécifie le(s) colonne(s) via lesquelles les deux types de données sont en relation. Les valeurs du tableau sont les colonnes des données primaires (représentées par la classe d'enregistrement actif dont vous déclarez les relations), tandis que les clés sont les colonnes des données en relation. 

Une règle simple pour vous rappeler cela est, comme vous le voyez dans l'exemple ci-dessus, d'écrire la colonne qui appartient à l'enregistrement actif en relation juste à coté de lui. Vous voyez là que l'identifiant du client (`customer_id`) est une propriété de `Order` et `id` est une propriété de `Customer`.

### Accès aux données relationnelles  <span id="accessing-relational-data"></span>

Après avoir déclaré des relations, vous pouvez accéder aux données relationnelles via le nom des relations. Tout se passe comme si vous accédiez à une [propriété](concept-properties.md) d'un objet défini par la méthode de relation. Pour cette raison, nous appelons cette propriété *propriété de relation*. Par exemple :

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
// $orders est un tableau d'objets Order 
$orders = $customer->orders;
```

> Info: lorsque vous déclarez une relation nommée `xyz` via une méthode d'obtention `getXyz()`, vous êtes capable d'accéder à `xyz` comme à un [objet property](concept-properties.md). Notez que le nom est sensible à la casse. 

Si une relation est déclarée avec la méthode [[yii\db\ActiveRecord::hasMany()|hasMany()]], l'accès à cette propriété de relation retourne un tableau des instances de l'enregistrement actif en relation ; si une relation est déclarée avec la méthode [[yii\db\ActiveRecord::hasOne()|hasOne()]], l'accès à la propriété de relation retourne l'instance de l'enregistrement actif en relation, ou `null` si aucune donnée en relation n'est trouvée. 

Lorsque vous accédez à une propriété de relation pour la première fois, une instruction SQL est exécutée comme le montre l'exemple précédent. Si la même propriété fait l'objet d'un nouvel accès, le résultat précédent est retourné sans exécuter à nouveau l'instruction SQL. Pour forcer l'exécution à nouveau de l'instruction SQL, vous devez d'abord annuler la définition de la propriété de relation : `unset($customer->orders)`.

> Note: bien que ce concept semble similaire à la fonctionnalité [propriété d'objet](concept-properties.md), il y a une différence importante. Pour les propriétés normales d'objet, la valeur est du même type que la méthode d'obtention de définition. Une méthode de relation cependant retourne toujours une instance d'[[yii\db\ActiveRecord]] ou un tableau de telles instances.
> 
> ```php
> $customer->orders; // est un tableau d'objets `Order` 
> $customer->getOrders(); // retourne une instance d'ActiveQuery
> ```
> 
> Cela est utile for créer des requêtes personnalisées, ce qui est décrit dans la section suivante. 


### Requête relationnelle dynamique <span id="dynamic-relational-query"></span>

Parce qu'une méthode de relation retourne une instance d'[[yii\db\ActiveQuery]], vous pouvez continuer à construire cette requête en utilisant les méthodes de construction avant de l'exécuter. Par exemple :

```php
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getOrders()
    ->where(['>', 'subtotal', 200])
    ->orderBy('id')
    ->all();
```

Contrairement à l'accès à une propriété de relation, chaque fois que vous effectuez une requête relationnelle dynamique via une méthode de relation, une instruction SQL est exécutée, même si la même requête relationnelle dynamique a été effectuée auparavant.

Parfois, vous voulez peut-être paramétrer une déclaration de relation de manière à ce que vous puissiez effectuer des requêtes relationnelles dynamiques plus facilement. Par exemple, vous pouvez déclarer une relation `bigOrders` comme ceci :, 

```php
class Customer extends ActiveRecord
{
    public function getBigOrders($threshold = 100)
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])
            ->where('subtotal > :threshold', [':threshold' => $threshold])
            ->orderBy('id');
    }
}
```

Par la suite, vous serez en mesure d'effectuer les requêtes relationnelles suivantes : 

```php
// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 200 ORDER BY `id`
$orders = $customer->getBigOrders(200)->all();

// SELECT * FROM `order` WHERE `customer_id` = 123 AND `subtotal` > 100 ORDER BY `id`
$orders = $customer->bigOrders;
```


### Relations via une table de jointure <span id="junction-table"></span>

Dans la modélisation de base de données, lorsque la multiplicité entre deux tables en relation est *many-to-many* (de plusieurs à plusieurs), une [table de jointure](https://en.wikipedia.org/wiki/Junction_table) est en général introduite. Par exemple, la table `order` (commande) et la table `item` peuvent être en relation via une table de jointure nommée `order_item` (item_de_commande). Une commande correspond ensuite à de multiples items de commande, tandis qu'un item de produit correspond lui-aussi à de multiples items de commande (*order items*). 

Lors de la déclaration de telles relations, vous devez appeler soit [[yii\db\ActiveQuery::via()|via()]], soit [[yii\db\ActiveQuery::viaTable()|viaTable()]], pour spécifier la table de jointure. La différence entre [[yii\db\ActiveQuery::via()|via()]] et [[yii\db\ActiveQuery::viaTable()|viaTable()]] est que la première spécifie la table de jointure en termes de noms de relation existante, tandis que la deuxième utilise directement la table de jointure. Par exemple : 

```php
class Order extends ActiveRecord
{
    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->viaTable('order_item', ['order_id' => 'id']);
    }
}
```

ou autrement,

```php
class Order extends ActiveRecord
{
    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems');
    }
}
```

L'utilisation de relations déclarées avec une table de jointure est la même que celle de relations normales. Par exemple :

```php
// SELECT * FROM `order` WHERE `id` = 100
$order = Order::findOne(100);

// SELECT * FROM `order_item` WHERE `order_id` = 100
// SELECT * FROM `item` WHERE `item_id` IN (...)
// retourne un tableau d'objets Item 
$items = $order->items;
```


### Chargement paresseux et chargement précoce <span id="lazy-eager-loading"></span>

Dans la sous-section [Accès aux données relationnelles](#accessing-relational-data), nous avons expliqué que vous pouvez accéder à une propriété de relation d'une instance d'enregistrement actif comme si vous accédiez à une propriété normale d'objet. Une instruction SQL est exécutée seulement lorsque vous accédez à cette propriété pour la première fois. Nous appelons une telle méthode d'accès à des données relationnelles, *chargement paresseux*. Par exemple :

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$orders = $customer->orders;

// pas de SQL exécuté
$orders2 = $customer->orders;
```

Le chargement paresseux est très pratique à utiliser. Néanmoins, il peut souffrir d'un problème de performance lorsque vous avez besoin d'accéder à la même propriété de relation sur de multiples instances d'enregistrement actif. Examinons l'exemple de code suivant. Combien d'instruction SQL sont-elles exécutées ?

```php
// SELECT * FROM `customer` LIMIT 100
$customers = Customer::find()->limit(100)->all();

foreach ($customers as $customer) {
    // SELECT * FROM `order` WHERE `customer_id` = ...
    $orders = $customer->orders;
}
```

Comme vous pouvez le constater dans le fragment de code ci-dessus, 101 instruction SQL sont exécutées ! Cela tient au fait que, à chaque fois que vous accédez à la propriété de relation `orders` d'un objet client différent dans la boucle for, une instruction SQL est exécutée. 

Pour résoudre ce problème de performance, vous pouvez utiliser ce qu'on appelle le *chargement précoce* comme montré ci-dessous :

```php
// SELECT * FROM `customer` LIMIT 100;
// SELECT * FROM `orders` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->with('orders')
    ->limit(100)
    ->all();

foreach ($customers as $customer) {
    // aucune instruction SQL exécutée
    $orders = $customer->orders;
}
```

En appelant [[yii\db\ActiveQuery::with()]], vous donner comme instruction à l'enregistrement actif de rapporter les commandes (*orders*) pour les 100 premiers clients (*customers*) en une seule instruction SQL. En conséquence, vous réduisez le nombre d'instructions SQL de 101 à 2 !

Vous pouvez charger précocement une ou plusieurs relations. Vous pouvez même charger précocement des *relations imbriquées*. Une relation imbriquée est une relation qui est déclarée dans une classe d'enregistrement actif. Par exemple, `Customer` est en relation avec `Order` via la relation `orders`, et `Order` est en relation avec `Item` via la relation `items`. Lorsque vous effectuez une requête pour `Customer`, vous pouvez charger précocement `items` en utilisant la notation de relation imbriquée `orders.items`. 

Le code suivant montre différentes utilisations de [[yii\db\ActiveQuery::with()|with()]]. Nous supposons que la classe `Customer` possède deux relations `orders` (commandes) et `country` (pays), tandis que la classe `Order` possède une relation `items`.

```php
// chargement précoce à la fois de "orders" et de "country"
$customers = Customer::find()->with('orders', 'country')->all();
// équivalent au tableau de syntaxe ci-dessous
$customers = Customer::find()->with(['orders', 'country'])->all();
// aucune instruction SQL exécutée 
$orders= $customers[0]->orders;
// aucune instruction SQL exécutée
$country = $customers[0]->country;

// chargement précoce de "orders" et de la relation imbriquée "orders.items"
$customers = Customer::find()->with('orders.items')->all();
// accés aux items de la première commande du premier client
// aucune instruction SQL exécutée
$items = $customers[0]->orders[0]->items;
```

Vous pouvez charger précocement des relations imbriquées en profondeur, telles que `a.b.c.d`. Toutes les relations parentes sont chargées précocement. C'est à dire, que lorsque vous appelez [[yii\db\ActiveQuery::with()|with()]] en utilisant `a.b.c.d`, vous chargez précocement `a`, `a.b`, `a.b.c` et `a.b.c.d`.

> Info: en général, lors du chargement précoce de `N` relations parmi lesquelles `M` relations sont définies par une [table de jointure](#junction-table), `N+M+1` instructions SQL sont exécutées au total. Notez qu'une relation imbriquée `a.b.c.d` possède 4 relations.

Lorsque vous chargez précocement une relation, vous pouvez personnaliser le requête relationnelle correspondante en utilisant une fonction anonyme. Par exemple :

```php
// trouve les clients et rapporte leur pays et leurs commandes actives 
// SELECT * FROM `customer`
// SELECT * FROM `country` WHERE `id` IN (...)
// SELECT * FROM `order` WHERE `customer_id` IN (...) AND `status` = 1
$customers = Customer::find()->with([
    'country',
    'orders' => function ($query) {
        $query->andWhere(['status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

Lors de la personnalisation de la requête relationnelle pour une relation, vous devez spécifier le nom de la relation comme une clé de tableau et utiliser une fonction anonyme comme valeur de tableau correspondante. La fonction anonyme accepte une paramètre `$query` qui représente l'objet [[yii\db\ActiveQuery]] utilisé pour effectuer la requête relationnelle pour la relation. Dans le code ci-dessus, nous modifions la requête relationnelle en ajoutant une condition additionnelle à propos de l'état de la commande (*order*).

> Note: si vous appelez [[yii\db\Query::select()|select()]] tout en chargeant précocement les relations, vous devez vous assurer que les colonnes référencées dans la déclaration de la relation sont sélectionnées. Autrement, les modèles en relation peuvent ne pas être chargés correctement. Par exemple :
>
> ```php
> $orders = Order::find()->select(['id', 'amount'])->with('customer')->all();
> // $orders[0]->customer est toujours nul. Pour régler le problème, vous devez faire ce qui suit :
> $orders = Order::find()->select(['id', 'amount', 'customer_id'])->with('customer')->all();
> ```


### Jointure avec des relations <span id="joining-with-relations"></span>

> Note: le contenu décrit dans cette sous-section ne s'applique qu'aux bases de données relationnelles, telles que MySQL, PostgreSQL, etc.

Les requêtes relationnelles que nous avons décrites jusqu'à présent ne font référence qu'aux colonnes de table primaires lorsque nous faisons une requête des données primaires. En réalité, nous avons souvent besoin de faire référence à des colonnes dans les tables en relation. Par exemple, vous désirez peut-être rapporter les clients qui ont au moins une commande active. Pour résoudre ce problème, nous pouvons construire une requête avec jointure comme suit :

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id`
// WHERE `order`.`status` = 1
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()
    ->select('customer.*')
    ->leftJoin('order', '`order`.`customer_id` = `customer`.`id`')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->with('orders')
    ->all();
```

> Note: il est important de supprimer les ambiguïtés sur les noms de colonnes lorsque vous construisez les requêtes relationnelles faisant appel à des instructions SQL JOIN. Une pratique courante est de préfixer les noms de colonnes par le nom des tables correspondantes. 

Néanmoins, une meilleure approche consiste à exploiter les déclarations de relations existantes en appelant [[yii\db\ActiveQuery::joinWith()]] :

```php
$customers = Customer::find()
    ->joinWith('orders')
    ->where(['order.status' => Order::STATUS_ACTIVE])
    ->all();
```

Les deux approches exécutent le même jeu d'instructions SQL. La deuxième approche est plus propre et plus légère cependant. 

Par défaut, [[yii\db\ActiveQuery::joinWith()|joinWith()]] utilise `LEFT JOIN` pour joindre la table primaire avec les tables en relation. Vous pouvez spécifier une jointure différente (p .ex. `RIGHT JOIN`) via sont troisième paramètre `$joinType`. Si le type de jointure que vous désirez est `INNER JOIN`, vous pouvez simplement appeler [[yii\db\ActiveQuery::innerJoinWith()|innerJoinWith()]], à la place.

L'appel de [[yii\db\ActiveQuery::joinWith()|joinWith()]] [charge précocement](#lazy-eager-loading) les données en relation par défaut. Si vous ne voulez pas charger les données en relation, vous pouvez spécifier son deuxième paramètre `$eagerLoading` comme étant `false`. 

Comme avec [[yii\db\ActiveQuery::with()|with()]], vous pouvez joindre une ou plusieurs relations ; vous pouvez personnaliser les requêtes de relation à la volée ; vous pouvez joindre des relations imbriquées ; et vous pouvez mélanger l'utilisation de [[yii\db\ActiveQuery::with()|with()]] et celle de [[yii\db\ActiveQuery::joinWith()|joinWith()]]. Par exemple :

```php
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->andWhere(['>', 'subtotal', 100]);
    },
])->with('country')
    ->all();
```

Parfois, en joignant deux tables, vous désirez peut-être spécifier quelques conditions supplémentaires dans la partie `ON` de la requête JOIN. Cela peut être réalisé en appelant la méthode [[yii\db\ActiveQuery::onCondition()]] comme ceci :

```php
// SELECT `customer`.* FROM `customer`
// LEFT JOIN `order` ON `order`.`customer_id` = `customer`.`id` AND `order`.`status` = 1 
// 
// SELECT * FROM `order` WHERE `customer_id` IN (...)
$customers = Customer::find()->joinWith([
    'orders' => function ($query) {
        $query->onCondition(['order.status' => Order::STATUS_ACTIVE]);
    },
])->all();
```

La requête ci-dessus retourne *tous* les clients, et pour chacun des clients, toutes les commandes actives. Notez que cela est différent de notre exemple précédent qui ne retourne que les clients qui ont au moins une commande active. 

> Info: quand [[yii\db\ActiveQuery]] est spécifiée avec une condition via une jointure [[yii\db\ActiveQuery::onCondition()|onCondition()]], la condition est placée dans la partie `ON` si la requête fait appel à une requête JOIN. Si la requête ne fait pas appel à JOIN, la *on-condition* est automatiquement ajoutée à la partie `WHERE` de la requête. Par conséquent elle peut ne contenir que des conditions incluant des colonnes de la table en relation. 

#### Alias de table de relation <span id="relation-table-aliases"></span>

Comme noté auparavant, lorsque vous utilisez une requête JOIN, vous devez lever les ambiguïtés sur le nom des colonnes. Pour cela, un alias est souvent défini pour une table. Définir un alias pour la requête relationnelle serait possible en personnalisant le requête de relation de la manière suivante :

```php
$query->joinWith([
    'orders' => function ($q) {
        $q->from(['o' => Order::tableName()]);
    },
])
```

Cela paraît cependant très compliqué et implique soit de coder en dur les noms de tables des objets en relation, soit d'appeler `Order::tableName()`. Depuis la version 2.0.7, Yii fournit un raccourci pour cela. Vous pouvez maintenant définir et utiliser l'alias pour la table de relation comme ceci :

```php
// joint la relation orders et trie les résultats par orders.id
$query->joinWith(['orders o'])->orderBy('o.id');
```

### Relations inverses <span id="inverse-relations"></span>

Les déclarations de relations sont souvent réciproques entre deux classes d'enregistrement actif. Par exemple, `Customer` est en relation avec `Order` via la relation `orders`, et `Order` est en relation inverse avec `Customer` via la relation `customer`.

```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}

class Order extends ActiveRecord
{
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
```

Considérons maintenant ce fragment de code :

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// SELECT * FROM `customer` WHERE `id` = 123
$customer2 = $order->customer;

// displays "not the same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

On aurait tendance à penser que `$customer` et `$customer2` sont identiques, mais ils ne le sont pas ! En réalité, ils contiennent les mêmes données de client, mais ce sont des objets différents. En accédant à `$order->customer`, une instruction SQL supplémentaire est exécutée pour remplir un nouvel objet `$customer2`.

Pour éviter l'exécution redondante de la dernière instruction SQL dans l'exemple ci-dessus, nous devons dire à Yii que `customer` est une  *relation inverse* de `orders` en appelant la méthode [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] comme ci-après :


```php
class Customer extends ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->inverseOf('customer');
    }
}
```

Avec cette déclaration de relation modifiée, nous avons :

```php
// SELECT * FROM `customer` WHERE `id` = 123
$customer = Customer::findOne(123);

// SELECT * FROM `order` WHERE `customer_id` = 123
$order = $customer->orders[0];

// aucune instruction SQL n'est exécutée
$customer2 = $order->customer;

// affiche "same"
echo $customer2 === $customer ? 'same' : 'not the same';
```

> Note: les relations inverses ne peuvent être définies pour des relations faisant appel à une [table de jointure](#junction-table). C'est à dire que, si une relation est définie avec [[yii\db\ActiveQuery::via()|via()]] ou avec [[yii\db\ActiveQuery::viaTable()|viaTable()]], vous ne devez pas appeler [[yii\db\ActiveQuery::inverseOf()|inverseOf()]] ensuite.


## Sauvegarde des relations <span id="saving-relations"></span>

En travaillant avec des données relationnelles, vous avez souvent besoin d'établir de créer des relations entre différentes données ou de supprimer des relations existantes. Cela requiert de définir les valeurs appropriées pour les colonnes qui définissent ces relations. En utilisant l'enregistrement actif, vous pouvez vous retrouver en train d'écrire le code de la façon suivante :

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

// défninition de l'attribut qui définit la relation "customer" dans Order
$order->customer_id = $customer->id;
$order->save();
```

L'enregistrement actif fournit la méthode [[yii\db\ActiveRecord::link()|link()]]qui vous permet d'accomplir cette tâche plus élégamment :

```php
$customer = Customer::findOne(123);
$order = new Order();
$order->subtotal = 100;
// ...

$order->link('customer', $customer);
```

La méthode [[yii\db\ActiveRecord::link()|link()]] requiert que vous spécifiiez le nom de la relation et l'instance d'enregistrement actif cible avec laquelle le relation doit être établie. La méthode modifie les valeurs des attributs qui lient deux instances d'enregistrement actif et les sauvegarde dans la base de données. Dans l'exemple ci-dessus, elle définit l'attribut `customer_id` de l'instance `Order` comme étant la valeur de l'attribut `id` de l'instance `Customer` et le sauvegarde ensuite dans la base de données.

> Note: vous ne pouvez pas lier deux instances d'enregistrement actif nouvellement créées. 

L'avantage d'utiliser [[yii\db\ActiveRecord::link()|link()]] est même plus évident lorsqu'une relation est définie via une [table de jointure](#junction-table). Par exemple, vous pouvez utiliser le code suivant pour lier une instance de `Order` à une instance de `Item` :

```php
$order->link('items', $item);
```

Le code ci-dessus insère automatiquement une ligne dans la table de jointure `order_item` pour mettre la commande en relation avec l'item. 

> Info: la méthode [[yii\db\ActiveRecord::link()|link()]] n'effectue AUCUNE validation de données lors de la sauvegarde de l'instance d'enregistrement actif affectée. Il est de votre responsabilité de valider toutes les données entrées avant d'appeler cette méthode. 

L'opération opposée à [[yii\db\ActiveRecord::link()|link()]] est [[yii\db\ActiveRecord::unlink()|unlink()]] qui casse une relation existante entre deux instances d'enregistrement actif. Par exemple :

```php
$customer = Customer::find()->with('orders')->where(['id' => 123])->one();
$customer->unlink('orders', $customer->orders[0]);
```

Par défaut, la méthode [[yii\db\ActiveRecord::unlink()|unlink()]] définit la valeur de la (des) clé(s) qui spécifie(nt) la relation existante à `null`. Vous pouvez cependant, choisir de supprimer la ligne de la table qui contient la valeur de clé étrangère en passant à la méthode la valeur `true` pour le paramètre `$delete`. 
 
Lorsqu'une table de jointure est impliquée dans une relation, appeler [[yii\db\ActiveRecord::unlink()|unlink()]] provoque l'effacement des clés étrangères dans la table de jointure, ou l'effacement de la ligne correspondante dans la table de jointure si `#delete` vaut `true`.


## Relations inter bases de données <span id="cross-database-relations"></span> 

L'enregistrement actif vous permet de déclarer des relations entre les classes d'enregistrement actif qui sont mise en œuvre par différentes bases de données. Les bases de données peuvent être de types différents (p. ex. MySQL and PostgreSQL, ou MS SQL et MongoDB), et elles peuvent s'exécuter sur des serveurs différents. Vous pouvez utiliser la même syntaxe pour effectuer des requêtes relationnelles. Par exemple :

```php
// Customer est associé à la table "customer" dans la base de données relationnelle (e.g. MySQL)
class Customer extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function getComments()
    {
        // a customer has many comments
        return $this->hasMany(Comment::class, ['customer_id' => 'id']);
    }
}

// Comment est associé à la collection "comment" dans une base de données MongoDB
class Comment extends \yii\mongodb\ActiveRecord
{
    public static function collectionName()
    {
        return 'comment';
    }

    public function getCustomer()
    {
        // un commentaire (comment) a un client (customer)
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}

$customers = Customer::find()->with('comments')->all();
```

Vous pouvez utiliser la plupart des fonctionnalités de requêtes relationnelles qui ont été décrites dans cette section.
 
> Note: l'utilisation de [[yii\db\ActiveQuery::joinWith()|joinWith()]] est limitée aux bases de données qui permettent les requête JOIN inter bases. Pour cette raison, vous ne pouvez pas utiliser cette méthode dans l'exemple ci-dessus car MongoDB ne prend pas JOIN en charge. 


## Personnalisation des classes de requête <span id="customizing-query-classes"></span>

Par défaut, toutes les requêtes d'enregistrement actif sont prises en charge par [[yii\db\ActiveQuery]]. Pour utiliser une classe de requête personnalisée dans une classe d'enregistrement actif, vous devez redéfinir la méthode [[yii\db\ActiveRecord::find()]] et retourner une instance de votre classe de requête personnalisée .Par exemple :
 
```php
namespace app\models;

use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class Comment extends ActiveRecord
{
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
}

class CommentQuery extends ActiveQuery
{
    // ...
}
```

Désormais, à chaque fois que vous effectuez une requête (p. ex. `find()`, `findOne()`) ou définissez une relation (p. ex. `hasOne()`) avec `Comment`, vous travaillez avec une instance de `CommentQuery` au lieu d'une instance d'`ActiveQuery`.

> Tip: dans les gros projets, il est recommandé que vous utilisiez des classes de requête personnalisées pour contenir la majeure partie de code relatif aux requêtes de manière à ce que les classe d'enregistrement actif puissent être maintenues propres. 

Vous pouvez personnaliser une classe de requête de plusieurs manières créatives pour améliorer votre expérience de la construction de requêtes. Par exemple, vous pouvez définir de nouvelles méthodes de construction de requête dans des classes de requête personnalisées :

```php
class CommentQuery extends ActiveQuery
{
    public function active($state = true)
    {
        return $this->andWhere(['active' => $state]);
    }
}
```

> Note: au lieu d'appeler [[yii\db\ActiveQuery::where()|where()]], vous devez ordinairement appeler [[yii\db\ActiveQuery::andWhere()|andWhere()]] ou [[yii\db\ActiveQuery::orWhere()|orWhere()]] pour ajouter des conditions additionnelles lors de la définition de nouvelles méthodes de construction de requête afin que les conditions existantes ne soient pas redéfinies.

Cela vous permet d'écrire le code de construction de requêtes comme suit :
 
```php
$comments = Comment::find()->active()->all();
$inactiveComments = Comment::find()->active(false)->all();
```

Vous pouvez aussi utiliser les méthodes de construction de requêtes en définissant des relations avec `Comment` ou en effectuant une requête relationnelle : 

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getActiveComments()
    {
        return $this->hasMany(Comment::class, ['customer_id' => 'id'])->active();
    }
}

$customers = Customer::find()->with('activeComments')->all();

// ou alternativement
 
$customers = Customer::find()->with([
    'comments' => function($q) {
        $q->active();
    }
])->all();
```

> Info: dans Yii 1.1, il existe un concept appelé *scope*. Scope n'est plus pris en charge directement par Yii 2.0, et vous devez utiliser des classes de requête personnalisée et des méthodes de requêtes pour remplir le même objectif. 


## Sélection de champs supplémentaires

Quand un enregistrement actif est rempli avec les résultats d'une requête, ses attributs sont remplis par les valeurs des colonnes correspondantes du jeu de données reçu. 

Il vous est possible d'aller chercher des colonnes ou des valeurs additionnelles à partir d'une requête et des les stocker dans l'enregistrement actif. Par exemple, supposons que nous ayons une table nommée `room`, qui contient des informations sur les chambres (rooms) disponibles dans l'hôtel. Chacune des chambres stocke des informations sur ses dimensions géométriques en utilisant des champs `length` (longueur), `width` (largeur) , `height` (hauteur). Imaginons que vous ayez besoin de retrouver une liste des chambres disponibles en les classant par volume décroissant. Vous ne pouvez pas calculer le volume en PHP parce que vous avez besoin de trier les enregistrements par cette valeur, mais vous voulez peut-être aussi que `volume` soit affiché dans la liste. Pour atteindre ce but, vous devez déclarer un champ supplémentaire dans la classe d'enregistrement actif `Room` qui contiendra la valeur de `volume` :


```php
class Room extends \yii\db\ActiveRecord
{
    public $volume;

    // ...
}
```

Ensuite, vous devez composer une requête qui calcule le volume de la chambre et effectue le tri : 

```php
$rooms = Room::find()
    ->select([
        '{{room}}.*', // selectionne toutes les colonnes 
        '([[length]] * [[width]] * [[height]]) AS volume', // calcule un volume
    ])
    ->orderBy('volume DESC') // applique le tri
    ->all();

foreach ($rooms as $room) {
    echo $room->volume; // contient la valeur calculée par SQL
}
```

La possibilité de sélectionner des champs supplémentaires peut être exceptionnellement utile pour l'agrégation de requêtes. Supposons que vous ayez besoin d'afficher une liste des clients avec le nombre total de commandes qu'ils ont passées. Tout d'abord, vous devez déclarer une classe `Customer` avec une relation `orders` et un champ supplémentaire pour le stockage du nombre de commandes :

```php
class Customer extends \yii\db\ActiveRecord
{
    public $ordersCount;

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```

Ensuite vous pouvez composer une requête qui joint les commandes et calcule leur nombre :

```php
$customers = Customer::find()
    ->select([
        '{{customer}}.*', // selectionne tous les champs de customer 
        'COUNT({{order}}.id) AS ordersCount' // calcule le nombre de commandes (orders)
    ])
    ->joinWith('orders') // garantit la jointure de la table
    ->groupBy('{{customer}}.id') // groupe les résultats pour garantir que la fonction d'agrégation fonctionne 
    ->all();
```

Un inconvénient de l'utilisation de cette méthode est que si l'information n'est pas chargée dans la requête SQL, elle doit être calculée séparément, ce qui signifie aussi que l'enregistrement nouvellement sauvegardé ne contient les informations d'aucun champ supplémentaire : 

```php
$room = new Room();
$room->length = 100;
$room->width = 50;
$room->height = 2;

$room->volume; // cette valeur est `null` puisqu'elle n'a pas encore été déclarée
```

En utilisant les méthodes magiques [[yii\db\BaseActiveRecord::__get()|__get()]] et [[yii\db\BaseActiveRecord::__set()|__set()]] nous pouvons émuler le comportement d'une propriété :

```php
class Room extends \yii\db\ActiveRecord
{
    private $_volume;
    
    public function setVolume($volume)
    {
        $this->_volume = (float) $volume;
    }
    
    public function getVolume()
    {
        if (empty($this->length) || empty($this->width) || empty($this->height)) {
            return null;
        }
        
        if ($this->_volume === null) {
            $this->setVolume(
                $this->length * $this->width * $this->height
            );
        }
        
        return $this->_volume;
    }

    // ...
}
```

Lorsque la requête *select* ne fournit pas le volume, le modèle est pas capable de le calculer automatiquement en utilisant les attributs du modèle. 

De façon similaire, il peut être utilisé sur des champs supplémentaires en fonction des données relationnelles : 

```php
class Customer extends \yii\db\ActiveRecord
{
    private $_ordersCount;
    
    public function setOrdersCount($count)
    {
        $this->_ordersCount = (int) $count;
    }
    
    public function getOrdersCount()
    {
        if ($this->isNewRecord) {
            return null; // this avoid calling a query searching for null primary keys
        }
        
        if ($this->_ordersCount === null) {
            $this->setOrdersCount(count($this->orders));
        }

        return $this->_ordersCount;
    }

    // ...

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}
```

