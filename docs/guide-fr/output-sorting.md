Tri
===

Lors de l'affichage de multiples lignes de données, on a souvent besoin des trier les données en fonction des valeurs de certaines colonnes spécifiées par l'utilisateur. Yii utilise l'objet [[yii\data\Sort]] pour représenter les information sur le schéma de triage. En particulier :

* [[yii\data\Sort::$attributes|attributes]] spécifie les *attributs* grâce auxquels les données peuvent être triées. Un attribut peut être aussi simple qu'un [attribut de modèle](structure-models.md#attributes). Il peut aussi être un composite combinant le multiples attributs de modèles ou de colonnes de base de données. Nous apportons des informations plus détaillées dans la suite de cette page.
* [[yii\data\Sort::$attributeOrders|attributeOrders]] fournit la direction de l'ordre de tri pour chacun des attributs.
* [[yii\data\Sort::$orders|orders]] fournit les directions de tri en terme de colonnes de bas niveau. 

Pour utiliser [[yii\data\Sort]], commencez par déclarer quels attributs peuvent être triés. Puis retrouvez les informations d'ordre de tri courantes de [[yii\data\Sort::$attributeOrders|attributeOrders]] ou [[yii\data\Sort::$orders|orders]], et utilisez-les pour personnaliser votre requête de données. Par exemple :

```php
use yii\data\Sort;

$sort = new Sort([
    'attributes' => [
        'age',
        'name' => [
            'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
            'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
            'default' => SORT_DESC,
            'label' => 'Name',
        ],
    ],
]);

$articles = Article::find()
    ->where(['status' => 1])
    ->orderBy($sort->orders)
    ->all();
```

Dans l'exemple qui précède, deux attributs sont déclarés pour l'objet [[yii\data\Sort|Sort]] : `age` et `name`. 

L'attribut `age` est un attribut *simple* correspondant à l'attribut `age` de la classe d'enregistrement actif `Article`. Il équivaut à la déclaration suivante :

```php
'age' => [
    'asc' => ['age' => SORT_ASC],
    'desc' => ['age' => SORT_DESC],
    'default' => SORT_ASC,
    'label' => Inflector::camel2words('age'),
]
```

L'attribut `name` est un attribut *composite* défini par `first_name` et `last_name` de la classe `Article`. Il est déclaré en utilisant la structure de tableau suivante :

- Les éléments `asc` et `desc` spécifient comment trier selon l'attribut dans la direction croissante et décroissante, respectivement. Leurs valeurs représentent les colonnes réelles et les directions dans lesquelles les données sont triées. Vous pouvez spécifier une ou plusieurs colonnes pour préciser un tri simple ou un tri composite.
- L'élément `default` spécifie la direction dans laquelle l'attribut doit être trié lorsqu'il est initialement requis. Sa valeur par défaut est l'ordre croissant, ce qui signifie que si les données n'ont pas été triées auparavant et que vous demandez leur tri par cet attribut, elles sont triées par cette attribut dans la direction croissante.
- L'élément `label` spécifie quelle étiquette doit être utilisée lors de l'appel de [[yii\data\Sort::link()]] pour créer un lien de tri. Si cet élément n'est pas spécifié, la fonction [[yii\helpers\Inflector::camel2words()]] est appelée pour générer une étiquette à partir du nom de l'attribut. Notez que cette étiquette n'est pas encodée HTML.

> Info: vous pouvez fournir la valeur de [[yii\data\Sort::$orders|orders]] à la requête de base de données pour construire sa clause `ORDER BY`. N'utilisez pas [[yii\data\Sort::$attributeOrders|attributeOrders]], parce que certains attributs peuvent être composites et ne peuvent pas être reconnus par la requête de base de données.

Vous pouvez appeler [[yii\data\Sort::link()]] pour générer un hyperlien sur lequel l'utilisateur peut cliquer pour demander le tri des données selon l'attribut spécifié. Vous pouvez aussi appeler [[yii\data\Sort::createUrl()]] pour créer une URL susceptible d'être triée. Par exemple :

```php
// spécifie la route que l'URL à créer doit utiliser,
// si vous ne la spécifiez pas, la route couramment requise est utilisée 
$sort->route = 'article/index';

// affiche des liens conduisant à trier par *name* (nom) et *age*, respectivement
echo $sort->link('name') . ' | ' . $sort->link('age');

// affiche : /index.php?r=article%2Findex&sort=age
echo $sort->createUrl('age');
```

[[yii\data\Sort]] vérifie le paramètre `sort` pour savoir quels attributs sont requis pour le tri. Vous pouvez spécifier un ordre de tri par défaut via [[yii\data\Sort::defaultOrder]] lorsque le paramètre de requête est absent. Vous pouvez aussi personnaliser le nom du paramètre de requête en configurant la porpriété [[yii\data\Sort::sortParam|sortParam]].
