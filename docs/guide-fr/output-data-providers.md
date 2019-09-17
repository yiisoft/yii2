Fournisseurs de données
=======================

Dans les sections [Pagination](output-pagination.md) et [Tri](output-sorting.md), nous avons décrit comment permettre à l'utilisateur de choisir une page particulière de données à afficher et de trier ces données en fonction de certaines colonnes. Comme les tâches de pagination et de tri sont très courantes, Yii met à votre disposition un jeu de classes *fournisseurs de données* pour les encapsuler.

Un fournisseur de données est une classe qui implémente l'interface [[yii\data\DataProviderInterface]]. Il prend en essentiellement en charge l'extraction de données paginées et triées. Il fonctionne ordinairement avec des [composants graphiques de données](output-data-widgets.md) pour que l'utilisateur final puisse paginer et trier les données de manière interactive.

Les classes fournisseurs de données suivantes sont incluses dans les versions publiées de Yii :

* [[yii\data\ActiveDataProvider]]: utilise [[yii\db\Query]] ou [[yii\db\ActiveQuery]] pour demander des données à des bases de données et les retourner sous forme de tableaux ou d'instances d'[enregistrement actif](db-active-record.md).
* [[yii\data\SqlDataProvider]]: exécute une instruction SQL et retourne les données sous forme de tableaux. 
* [[yii\data\ArrayDataProvider]]: prend un gros tableau et en retourne une tranche en se basant sur les spécifications de pagination et de tri.

Tous ces fournisseurs de données sont utilisés selon un schéma commun :

```php
// créer le fournisseur de données en configurant ses propriétés de pagination et de tri 
$provider = new XyzDataProvider([
    'pagination' => [...],
    'sort' => [...],
]);

// retrouver les données paginées et triées
$models = $provider->getModels();

// obtenir le nombre d'items de données dans la page courante
$count = $provider->getCount();

// obtenir le nombre total d'items de données de l'ensemble des pages 
$totalCount = $provider->getTotalCount();
```

Vous spécifiez les comportements de pagination et de tri d'un fournisseur de données en configurant ses propriétés [[yii\data\BaseDataProvider::pagination|pagination]] et [[yii\data\BaseDataProvider::sort|sort (tri)]] qui correspondent aux configurations de [[yii\data\Pagination]] et [[yii\data\Sort]], respectivement. Vous pouvez également les configurer à `false` pour désactiver la pagination et/ou le tri.

Les [composants graphiques de données](output-data-widgets.md), tels que [[yii\grid\GridView]], disposent d'une propriété nommée `dataProvider` qui accepte une instance de fournisseur de données et affiche les données qu'il fournit. Par exemple :

```php
echo yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

Ces fournisseurs de données varient essentiellement en fonction de la manière dont la source de données est spécifiée. Dans les sections qui suivent, nous expliquons l'utilisation détaillée de chacun des ces fournisseurs de données. 


## Fournisseur de données actif <span id="active-data-provider"></span> 

Pour utiliser le [[yii\data\ActiveDataProvider|fournisseur de données actif (classe *ActiveDataProvider*)]], vous devez configurer sa propriété [[yii\data\ActiveDataProvider::query|query]]. Elle accepte soit un objet [[yii\db\Query]], soit un objet [[yii\db\ActiveQuery]]. Avec le premier, les données peuvent être soit des tableaux, soit des instances d'[enregistrement actif](db-active-record.md). Par exemple :


```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'defaultOrder' => [
            'created_at' => SORT_DESC,
            'title' => SORT_ASC, 
        ]
    ],
]);

// retourne un tableau d'objets Post 
$posts = $provider->getModels();
```

Si la requête `$query` de l'exemple ci-dessus est créée en utilisant le code suivant, alors le fournisseur de données retourne des tableaux bruts.

```php
use yii\db\Query;

$query = (new Query())->from('post')->where(['status' => 1]); 
```

> Note: si une requête spécifie déjà la clause `orderBy`, les nouvelles instructions de tri données par l'utilisateur final (via la configuration `sort`) sont ajoutées à la clause `orderBy` existante. Toute clause `limit` et `offset` existante est écrasée par la requête de pagination de l'utilisateur final (via la configuration `pagination`).
Par défaut, [[yii\data\ActiveDataProvider]] utilise le composant d'application `db` comme connexion à la base de données. Vous pouvez utiliser une connexion différente en configurant la propriété [[yii\data\ActiveDataProvider::db]].


## Fournisseur de données SQL <span id="sql-data-provider"></span>

[[yii\data\SqlDataProvider]] travaille avec des instructions SQL brutes pour aller chercher les données. Selon les spécifications de [[yii\data\SqlDataProvider::sort|sort]] et de [[yii\data\SqlDataProvider::pagination|pagination]], le fournisseur ajuste les clauses `ORDER BY` et `LIMIT` de l'instruction SQL en conséquence pour n'aller chercher que la page de données requise dans l'ordre désiré.

Pour utiliser [[yii\data\SqlDataProvider]], vous devez spécifier la propriété [[yii\data\SqlDataProvider::sql|sql]], ainsi que la propriété [[yii\data\SqlDataProvider::totalCount|totalCount]]. Par exemple :

```php
use yii\data\SqlDataProvider;

$count = Yii::$app->db->createCommand('
    SELECT COUNT(*) FROM post WHERE status=:status
', [':status' => 1])->queryScalar();

$provider = new SqlDataProvider([
    'sql' => 'SELECT * FROM post WHERE status=:status',
    'params' => [':status' => 1],
    'totalCount' => $count,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => [
            'title',
            'view_count',
            'created_at',
        ],
    ],
]);

// retourne un tableau de lignes de données
$models = $provider->getModels();
```

> Info: la propriété [[yii\data\SqlDataProvider::totalCount|totalCount]] est requise seulement si vous avez besoin de paginer les données.Cela est dû au fait que l'instruction SQL spécifiée via [[yii\data\SqlDataProvider::sql|sql]] est modifiée par le fournisseur pour ne retourner que la page de données couramment requise. Le fournisseur a donc besoin de connaître le nombre total d'items de données pour calculer correctement le nombre de pages disponibles.


## Fournisseur de données tableau <span id="array-data-provider"></span>

L'utilisation de [[yii\data\ArrayDataProvider]] est préférable lorsque vous travaillez avec un grand tableau. Le fournisseur vous permet de retourner une page des données du tableau, triées selon une ou plusieurs colonnes. Pour utiliser [[yii\data\ArrayDataProvider]], vous devez spécifier la propriété [[yii\data\ArrayDataProvider::allModels|*allModels* (tous les modèles)]] comme un grand tableau. Les éléments dans le grand tableau peuvent être, soit des tableaux associatifs (p. ex. des résultats de requête d'[objets d'accès aux données (DAO)](db-dao.md)) ou des objets (p. ex. les instances d'[Active Record](db-active-record.md). Par exemple :

```php
use yii\data\ArrayDataProvider;

$data = [
    ['id' => 1, 'name' => 'name 1', ...],
    ['id' => 2, 'name' => 'name 2', ...],
    ...
    ['id' => 100, 'name' => 'name 100', ...],
];

$provider = new ArrayDataProvider([
    'allModels' => $data,
    'pagination' => [
        'pageSize' => 10,
    ],
    'sort' => [
        'attributes' => ['id', 'name'],
    ],
]);

// obtient les lignes de la page couramment requise
$rows = $provider->getModels();
``` 

> Note: comparé au [fournisseur de données actif](#active-data-provider) et au fournisseur de données SQL](#sql-data-provider), le fournisseur de données tableau est moins efficient car il requiert de charger *toutes* les données en mémoire.


## Travail avec les clés de données <span id="working-with-keys"></span>

Lorsque vous utilisez les items de données retournés par le fournisseur de données, vous avez souvent besoin d'identifier chacun des items de données par une clé unique. Par exemple, si les items de donnés représentent des informations sur un client, vous désirez peut-être utiliser l'identifiant du client en tant que clé pour chacun de lots d'informations sur un client. Les fournisseurs de données peuvent retourner une liste de telles clés correspondant aux items de données retournés par [[yii\data\DataProviderInterface::getModels()]]. Par exemple :

```php
use yii\data\ActiveDataProvider;

$query = Post::find()->where(['status' => 1]);

$provider = new ActiveDataProvider([
    'query' => $query,
]);

// retourne un tableau d'objets Post
$posts = $provider->getModels();

// retourne les valeurs des clés primaires correspondant à $posts
```

Dans l'exemple ci-dessus, comme vous fournissez un objet [[yii\db\ActiveQuery]] à [[yii\data\ActiveDataProvider]]. Il est suffisamment intelligent pour retourner les valeurs de la clé primaire en tant que clés. Vous pouvez aussi spécifier comment les valeurs de la clé sont calculées en configurant [[yii\data\ActiveDataProvider::key]] avec un nom de colonne ou une fonction de rappel qui calcule les valeurs de la clé. Par exemple :

```php
// utilise la colonne "slug" comme valeurs de la clé
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => 'slug',
]);

// utilise le résultat de md5(id) comme valeurs de la clé
$provider = new ActiveDataProvider([
    'query' => Post::find(),
    'key' => function ($model) {
        return md5($model->id);
    }
]);
```


## Création d'un fournisseur de données personnalisé <span id="custom-data-provider"></span>

Pour créer votre fournisseur de données personnalisé, vous devez implémenter [[yii\data\DataProviderInterface]]. Une manière plus facile est d'étendre [[yii\data\BaseDataProvider]],ce qui vous permet de vous concentrer sur la logique centrale du fournisseur de données. En particulier, vous devez essentiellement implémenter les méthodes suivantes :
 
- [[yii\data\BaseDataProvider::prepareModels()|prepareModels()]]: prépare les modèles de données qui seront disponibles dans la page courante et les retourne sous forme de tableau. 
- [[yii\data\BaseDataProvider::prepareKeys()|prepareKeys()]]: accepte un tableau de modèles de données couramment disponibles et retourne les clés qui leur sont associés.
- [[yii\data\BaseDataProvider::prepareTotalCount()|prepareTotalCount]]: retourne une valeur indiquant le nombre total de modèles de données dans le fournisseur.

Nous présentons ci-dessous un exemple de fournisseur de données que lit des données CSV efficacement :

```php
<?php
use yii\data\BaseDataProvider;

class CsvDataProvider extends BaseDataProvider
{
    /**
     * @var string name of the CSV file to read
     */
    public $filename;
    
    /**
     * @var string|callable nom de la colonne clé ou fonction de rappel la retournant
     */
    public $key;
    
    /**
     * @var SplFileObject
     */
    protected $fileObject; // SplFileObject est très pratique pour rechercher une ligne particulière dans un fichier
    
 
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        // open file
        $this->fileObject = new SplFileObject($this->filename);
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $models = [];
        $pagination = $this->getPagination();
 
        if ($pagination === false) {
            // dans le cas où il n'y a pas de pagination, lit toutes les lignes
            while (!$this->fileObject->eof()) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        } else {
            // s'il y a une pagination, ne lit qu'une seule page
            $pagination->totalCount = $this->getTotalCount();
            $this->fileObject->seek($pagination->getOffset());
            $limit = $pagination->getLimit();
 
            for ($count = 0; $count < $limit; ++$count) {
                $models[] = $this->fileObject->fgetcsv();
                $this->fileObject->next();
            }
        }
 
        return $models;
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
 
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
 
            return $keys;
        } else {
            return array_keys($models);
        }
    }
 
    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        $count = 0;
 
        while (!$this->fileObject->eof()) {
            $this->fileObject->next();
            ++$count;
        }
 
        return $count;
    }
}
```
