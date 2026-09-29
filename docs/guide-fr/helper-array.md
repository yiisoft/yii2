Classe assistante ArrayHelper
=============================

En plus du jeu riche de [fonctions de tableaux](https://www.php.net/manual/fr/book.array.php) qu'offre PHP, la classe assistante traitant les tableaux dans Yii fournit des méthodes statiques supplémentaires qui vous permettent de traiter les tableaux avec plus d'efficacité.


## Obtention de valeurs <span id="getting-values"></span>

Récupérer des valeurs d'un tableau ou d'un objet ou une structure complexe écrits tous deux en PHP standard est un processus assez répétitif. Vous devez d'abord vérifier que la clé existe avec `isset`, puis si c'est le cas, vous récupérez la valeur associée, sinon il vous faut fournir une valeur par défaut : 

```php
class User
{
    public $name = 'Alex';
}

$array = [
    'foo' => [
        'bar' => new User(),
    ]
];

$value = isset($array['foo']['bar']->name) ? $array['foo']['bar']->name : null;
```

Yii fournit une méthode très pratique pour faire cela :

```php
$value = ArrayHelper::getValue($array, 'foo.bar.name');
```

Le premier argument de la méthode indique de quelle source nous voulons récupérer une valeur. Le deuxième spécifie comment récupérer la donnée. Il peut s'agir d'un des éléments suivants :

- Nom d'une clé de tableau ou de la propriété d'un objet de laquelle récupérer une valeur.
- Un jeu de noms de clé de tableau ou de propriétés d'objet séparées par des points, comme dans l'exemple que nous venons de présenter ci-dessus.
- Une fonction de rappel qui retourne une valeur.

Le fonction de rappel doit être la suivante :

```php
$fullName = ArrayHelper::getValue($user, function ($user, $defaultValue) {
    return $user->firstName . ' ' . $user->lastName;
});
```

Le troisième argument facultatif est la valeur par défaut qui est `null` si on ne la spécifie pas. Il peut être utilisé comme ceci :

```php
$username = ArrayHelper::getValue($comment, 'user.username', 'Unknown');
```

Dans le cas où vous voulez récupérer la valeur tout en la retirant immédiatement du tableau, vous pouvez utiliser la méthode `remove` :

```php
$array = ['type' => 'A', 'options' => [1, 2]];
$type = ArrayHelper::remove($array, 'type');
```

Après exécution du code, `$array` contiendra `['options' => [1, 2]]` et `$type` sera `A`. Notez que contrairement à la méthode `getValue`, `remove` accepte seulement les noms de clé.


## Tester l'existence des clés <span id="checking-existence-of-keys"></span>

`ArrayHelper::keyExists` fonctionne comme [array_key_exists](https://www.php.net/manual/fr/function.array-key-exists.php) sauf qu'elle prend également en charge la comparaison de clés insensible à la casse. Par exemple,

```php
$data1 = [
    'userName' => 'Alex',
];

$data2 = [
    'username' => 'Carsten',
];

if (!ArrayHelper::keyExists('username', $data1, false) || !ArrayHelper::keyExists('username', $data2, false)) {
    echo "Veuillez fournir un nom d'utilisateur (username).";
}
```

## Récupération de colonnes <span id="retrieving-columns"></span>

Il arrive souvent que vous ayez à récupérer une colonne de valeurs d'un tableau de lignes de données ou d'objets. Un exemple courant est l'obtention d'une liste d'identifiants. 

```php
$array = [
    ['id' => '123', 'data' => 'abc'],
    ['id' => '345', 'data' => 'def'],
];
$ids = ArrayHelper::getColumn($array, 'id');
```

Le résultat sera `['123', '345']`.

Si des transformations supplémentaires sont nécessaires ou si la manière de récupérer les valeurs est complexe, le second argument peut être formulé sous forme de fonction anonyme :

```php
$result = ArrayHelper::getColumn($array, function ($element) {
    return $element['id'];
});
```


## Réindexation de tableaux <span id="reindexing-arrays"></span>

La méthode `index` peut être utilisées pour indexer un tableau selon une clé spécifiée. L'entrée doit être soit un tableau multidimensionnel, soit un tableau d'objets. `$key` peut être un nom de clé du sous-tableau, un nom de propriété d'objet ou une fonction anonyme qui doit retourner la valeur à utiliser comme clé. 

L'attribut `$groups` est un tableau de clés qui est utilisé pour regrouper le tableau d'entrée en un ou plusieurs sous-tableaux basés sur les clés spécifiées. 

Si l'argument `$key` ou sa valeur pour l'élément particulier est `null` alors que `$groups` n'est pas défini, l'élément du tableau est écarté. Autrement, si `$groups` est spécifié, l'élément du tableau est ajouté au tableau résultant sans aucune clé.

Par exemple :

```php
$array = [
    ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
    ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
];
$result = ArrayHelper::index($array, 'id');
```

Le résultat est un tableau associatif, dans lequel la clé est la valeur de l'attribut `id` : 

```php
[
    '123' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop'],
    '345' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
    // Le second élément du tableau d'origine est écrasé par le dernier élément parce que les identifiants sont identiques. 
]
```

Une fonction anonyme passée en tant que `$key`, conduit au même résultat :

```php
$result = ArrayHelper::index($array, function ($element) {
    return $element['id'];
});
```

Passer `id` comme troisième argument regroupe `$array` par `id`:

```php
$result = ArrayHelper::index($array, null, 'id');
```

Le résultat est un tableau multidimensionnel regroupé par `id` au premier niveau et non indexé au deuxième niveau : 

```php
[
    '123' => [
        ['id' => '123', 'data' => 'abc', 'device' => 'laptop']
    ],
    '345' => [ // all elements with this index are present in the result array
        ['id' => '345', 'data' => 'def', 'device' => 'tablet'],
        ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone'],
    ]
]
```

Une fonction anonyme peut également être utilisée dans le tableau de regroupement : 

```php
$result = ArrayHelper::index($array, 'data', [function ($element) {
    return $element['id'];
}, 'device']);
```

Le résultat est un tableau multidimensionnel regroupé par `id` au premier niveau, par `device` au deuxième niveau et par `data` au troisième niveau :

```php
[
    '123' => [
        'laptop' => [
            'abc' => ['id' => '123', 'data' => 'abc', 'device' => 'laptop']
        ]
    ],
    '345' => [
        'tablet' => [
            'def' => ['id' => '345', 'data' => 'def', 'device' => 'tablet']
        ],
        'smartphone' => [
            'hgi' => ['id' => '345', 'data' => 'hgi', 'device' => 'smartphone']
        ]
    ]
]
```

## Construction de tableaux de mise en correspondance <span id="building-maps"></span>

Afin de construire un tableau de mise en correspondance (paires clé-valeur) sur la base d'un tableau multidimensionnel ou d'un tableau d'objets, vous pouvez utiliser la méthode `map`. 
Les paramètres `$from` et `$to` spécifient les noms de clé ou les noms des propriétés pour construire le tableau de mise en correspondance. Le paramètre facultatif `$group` est un nom de clé ou de propriété qui permet de regrouper les éléments du tableau au premier niveau. Par exemple :

```php
$array = [
    ['id' => '123', 'name' => 'aaa', 'class' => 'x'],
    ['id' => '124', 'name' => 'bbb', 'class' => 'x'],
    ['id' => '345', 'name' => 'ccc', 'class' => 'y'],
];

$result = ArrayHelper::map($array, 'id', 'name');
// le résultat est :
// [
//     '123' => 'aaa',
//     '124' => 'bbb',
//     '345' => 'ccc',
// ]

$result = ArrayHelper::map($array, 'id', 'name', 'class');
// le résultat est :
// [
//     'x' => [
//         '123' => 'aaa',
//         '124' => 'bbb',
//     ],
//     'y' => [
//         '345' => 'ccc',
//     ],
// ]
```


## Tri multidimensionnel <span id="multidimensional-sorting"></span>

La méthode `multisort` facilite le tri d'un tableau d'objets ou de tableaux imbriqués selon une ou plusieurs clés. Par exemple :

```php
$data = [
    ['age' => 30, 'name' => 'Alexander'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 19, 'name' => 'Barney'],
];
ArrayHelper::multisort($data, ['age', 'name'], [SORT_ASC, SORT_DESC]);
```

Après le tri, `data` contient ce qui suit :

```php
[
    ['age' => 19, 'name' => 'Barney'],
    ['age' => 30, 'name' => 'Brian'],
    ['age' => 30, 'name' => 'Alexander'],
];
```

Le deuxième argument, qui spécifie les clés de tri peut être une chaîne de caractères si la clé est unique, un tableau dans le cas de clés multiples, ou une fonction anonyme telle que celle qui suit :

```php
ArrayHelper::multisort($data, function($item) {
    return isset($item['age']) ? ['age', 'name'] : 'name';
});
```

Le troisième argument précise la direction. Dans le cas d'un tri selon une clé unique, il s'agit soit de `SORT_ASC`, soit de `SORT_DESC`. Si le tri se fait selon des valeurs multiples, vous pouvez préciser des directions de tri différentes pour chacune des clés en présentant ces directions sous forme de tableau.

Le dernier argument est une option de tri de PHP qui peut prendre les mêmes valeurs que celles acceptées par la fonction [sort()](https://www.php.net/manual/fr/function.sort.php) de PHP.


## Détection des types de tableau <span id="detecting-array-types"></span>

Il est pratique de savoir si un tableau est indexé ou associatif. Voici un exemple :

```php
// aucune clé spécifiée
$indexed = ['Qiang', 'Paul'];
echo ArrayHelper::isIndexed($indexed);

// toutes les clés sont des chaînes de caractères
$associative = ['framework' => 'Yii', 'version' => '2.0'];
echo ArrayHelper::isAssociative($associative);
```


## Encodage et décodage de valeurs HTML <span id="html-encoding-values"></span>

Afin d'encoder ou décoder des caractères spéciaux dans un tableau de chaînes de caractères en/depuis des entités HTML, vous pouvez utiliser les fonctions suivantes :

```php
$encoded = ArrayHelper::htmlEncode($data);
$decoded = ArrayHelper::htmlDecode($data);
```

Seules les valeurs sont encodées par défaut. En passant un deuxième argument comme `false` vous pouvez également encoder les clés d'un tableau. L'encodage utilise le jeu de caractères de l'application et on peut le changer via un troisième argument.


## Fusion de tableaux <span id="merging-arrays"></span>

La fonction [[yii\helpers\ArrayHelper::merge()|ArrayHelper::merge()]] vous permet de fusionner deux, ou plus, tableaux en un seul de manière récursive. Si chacun des tableaux possède un élément avec la même chaîne clé valeur, le dernier écrase le premier (ce qui est un fonctionnement différent de [array_merge_recursive()](https://www.php.net/manual/fr/function.array-merge-recursive.php)).
La fusion récursive est entreprise si les deux tableaux possèdent un élément de type tableau avec la même clé. Pour des éléments dont la clé est un entier, les éléments du deuxième tableau sont ajoutés aux éléments du premier tableau. Vous pouvez utiliser l'objet [[yii\helpers\UnsetArrayValue]] pour supprimer la valeur du premier tableau ou [[yii\helpers\ReplaceArrayValue]] pour forcer le remplacement de la première valeur au lieu de la fusion récursive. 

Par exemple :

```php
$array1 = [
    'name' => 'Yii',
    'version' => '1.1',
    'ids' => [
        1,
    ],
    'validDomains' => [
        'example.com',
        'www.example.com',
    ],
    'emails' => [
        'admin' => 'admin@example.com',
        'dev' => 'dev@example.com',
    ],
];

$array2 = [
    'version' => '2.0',
    'ids' => [
        2,
    ],
    'validDomains' => new \yii\helpers\ReplaceArrayValue([
        'yiiframework.com',
        'www.yiiframework.com',
    ]),
    'emails' => [
        'dev' => new \yii\helpers\UnsetArrayValue(),
    ],
];

$result = ArrayHelper::merge($array1, $array2);
```

Le résultat est :

```php
[
    'name' => 'Yii',
    'version' => '2.0',
    'ids' => [
        1,
        2,
    ],
    'validDomains' => [
        'yiiframework.com',
        'www.yiiframework.com',
    ],
    'emails' => [
        'admin' => 'admin@example.com',
    ],
]
```


## Conversion d'objets en tableaux <span id="converting-objects-to-arrays"></span>

Il arrive souvent que vous ayez besoin de convertir un objet, ou un tableau d'objets, en tableau. Le cas le plus courant est la conversion de modèles d'enregistrements actifs afin de servir des tableaux de données via une API REST ou pour un autre usage. Le code suivant peut alors être utilisé :

```php
$posts = Post::find()->limit(10)->all();
$data = ArrayHelper::toArray($posts, [
    'app\models\Post' => [
        'id',
        'title',
        // the key name in array result => property name
        'createTime' => 'created_at',
        // the key name in array result => anonymous function
        'length' => function ($post) {
            return strlen($post->content);
        },
    ],
]);
```

Le premier argument contient les données à convertir. Dans notre cas, nous convertissons un modèle d'enregistrements actifs `Post`. 

The second argument est un tableau de mise en correspondance de conversions par classe. Nous définissons une mise en correspondance pour le modèle `Post`. Chaque tableau de mise en correspondance contient un jeu de mise en correspondance. Chaque mise en correspondance peut être :

- Un nom de champ à inclure tel quel.
- Une paire clé-valeur dans laquelle la clé est donnée sous forme de chaîne de caractères et la valeur sous forme du nom de la colonne dont on doit prendre la valeur. 
- Une paire clé-valeur dans laquelle la clé est donnée sous forme de chaîne de caractères et la valeur sous forme de fonction de rappel qui la retourne. 

Le résultat de la conversion ci-dessus pour un modèle unique est :


```php
[
    'id' => 123,
    'title' => 'test',
    'createTime' => '2013-01-01 12:00AM',
    'length' => 301,
]
```

Il est possible de fournir une manière par défaut de convertir un objet en tableau pour une classe spécifique en implémentant l'interface [[yii\base\Arrayable|Arrayable]] dans cette classe.

## Test de l'appartenance à un tableau <span id="testing-arrays"></span>

Souvent, vous devez savoir si un élément se trouve dans un tableau ou si un jeu d'éléments est un sous-ensemble d'un autre. Bien que PHP offre la fonction `in_array()`, cette dernière ne prend pas en charge les sous-ensembles ou les objets `\Traversable`.

Pour faciliter ce genre de tests, [[yii\helpers\ArrayHelper]] fournit les méthodes [[yii\helpers\ArrayHelper::isIn()|isIn()]]
et [[yii\helpers\ArrayHelper::isSubset()|isSubset()]] avec la même signature que [in_array()](https://www.php.net/manual/fr/function.in-array.php).

```php
// true
ArrayHelper::isIn('a', ['a']);
// true
ArrayHelper::isIn('a', new ArrayObject(['a']));

// true 
ArrayHelper::isSubset(new ArrayObject(['a', 'c']), new ArrayObject(['a', 'b', 'c']));
```
