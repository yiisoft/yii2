Le constructeur de requêtes
===========================

Construit sur la base des [objets d'accès aux bases de données (DAO)](db-dao.md), le constructeur de requêtes vous permet de construire des requêtes SQL par programme qui sont indifférentes au système de gestion de base de données utilisé. Comparé à l'écriture d'instructions SQL brutes, l'utilisation du constructeur de requêtes vous aide à écrire du code relatif à SQL plus lisible et à générer des instructions SQL plus sûres. 

L'utilisation du constructeur de requêtes comprend ordinairement deux étapes :

1. Construire un objet [[yii\db\Query]] pour représenter différentes parties (p. ex. `SELECT`, `FROM`) d'une instruction SQL. 
2. Exécuter une méthode de requête (p. ex. `all()`) de [[yii\db\Query]] pour retrouver des données dans la base de données. 



Le code suivant montre une manière typique d'utiliser le constructeur de requêtes. 

```php
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->all();
```

Le code ci-dessus génère et exécute la requête SQL suivante, dans laquelle le paramètre `:last_name` est lié à la chaîne de caractères `'Smith'`.

```sql
SELECT `id`, `email` 
FROM `user`
WHERE `last_name` = :last_name
LIMIT 10
```


> Info: génélalement vous travaillez essentiellement avec [[yii\db\Query]] plutôt qu'avec [[yii\db\QueryBuilder]].
 Le dernier est implicitement invoqué par le premier lorsque vous appelez une des méthodes de requête. [[yii\db\QueryBuilder]] est la classe en charge de la génération des instructions SQL dépendantes du système de gestion de base de données (p. ex. entourer les noms de table/colonne par des marques de citation différemment) à partir d'objets [[yii\db\Query]] indifférents au système de gestion de base de données.




## Construction des requêtes <span id="building-queries"></span>

Pour construire un objet [[yii\db\Query]], vous appelez différentes méthodes de construction de requêtes pour spécifier différentes parties de la requête SQL.
 Les noms de ces méthodes ressemblent aux mots clés de SQL utilisés dans les parties correspondantes de l'instruction SQL.
Par exemple, pour spécifier la partie `FROM` d'une requête SQL, vous appelez la méthode [[yii\db\Query::from()|from()]].
 Toutes les méthodes de construction de requêtes retournent l'objet *query* lui-même, ce qui vous permet d'enchaîner plusieurs appels.

Dans ce qui suit, nous décrivons l'utilisation de chacune des méthodes de requête.


### [[yii\db\Query::select()|select()]] <span id="select"></span>

La méthode [[yii\db\Query::select()|select()]] spécifie le fragment `SELECT` d'une instruction SQL. Vous pouvez spécifier les colonnes à sélectionner soit sous forme de chaînes de caractères, soit sous forme de tableaux, comme ci-après. 
Les noms des colonnes sélectionnées sont automatiquement entourés des marques de citation lorsque l'instruction SQL
 est générée à partir de l'objet *query* (requête). 
 
```php
$query->select(['id', 'email']);

// équivalent à:

$query->select('id, email');
```

Les noms des colonnes sélectionnées peuvent inclure des préfixes de table et/ou des alias de colonne, comme vous le faites en écrivant une requête SQL brute.
Par exemple :

```php
$query->select(['user.id AS user_id', 'email']);

// équivalent à:

$query->select('user.id AS user_id, email');
```

Si vous utilisez le format tableau pour spécifier les colonnes, vous pouvez aussi utiliser les clés du tableau pour spécifier les alias de colonne. 
Par exemple, le code ci-dessus peut être réécrit comme ceci : 

```php
$query->select(['user_id' => 'user.id', 'email']);
```

Si vous n'appelez pas la méthode [[yii\db\Query::select()|select()]] en construisant une requête, `*` est sélectionné, 
ce qui signifie la sélection de *toutes* les colonnes. 

En plus des noms de colonne, vous pouvez aussi sélectionner des expression de base de données. Vous devez utiliser le format tableau en sélectionnant une expression de base de données qui contient des virgules pour éviter des entourages automatiques incorrects des noms par des marques de citation. 
Par exemple :

```php
$query->select(["CONCAT(first_name, ' ', last_name) AS full_name", 'email']); 
```

Comme en tout lieu où il est fait appel à du SQL brut, vous devez utiliser la [syntaxe des marques de citation indifférentes au système de gestion de base de données](db-dao.md#quoting-table-and-column-names) pour les noms de table et 
de colonne lorsque vous écrivez les expressions de base de données dans `select`. 

Depuis la version 2.0.1, vous pouvez aussi sélectionner des sous-requêtes. Vous devez spécifier chacune des sous-requêtes en termes d'objet [[yii\db\Query]]. 
Par exemple :
 
```php
$subQuery = (new Query())->select('COUNT(*)')->from('user');

// SELECT `id`, (SELECT COUNT(*) FROM `user`) AS `count` FROM `post`
$query = (new Query())->select(['id', 'count' => $subQuery])->from('post');
```

Pour sélectionner des lignes distinctes, vous pouvez appeler [[yii\db\Query::distinct()|distinct()]], comme ceci :

```php
// SELECT DISTINCT `user_id` ...
$query->select('user_id')->distinct();
```

Vous pouvez appeler [[yii\db\Query::addSelect()|addSelect()]] pour sélectionner des colonnes additionnelles. Par exemple :

```php
$query->select(['id', 'username'])
    ->addSelect(['email']);
```


### [[yii\db\Query::from()|from()]] <span id="from"></span>

La méthode [[yii\db\Query::from()|from()]] spécifie le fragment `FROM`d'une instruction. Par exemple :

```php
// SELECT * FROM `user`
$query->from('user');
```

Vous pouvez spécifier les tables à sélectionner soit sous forme de chaînes de caractères, soit sous forme de tableaux. Les noms de table peuvent contenir des préfixes et/ou des alias de table. 
Par exemple :

```php
$query->from(['public.user u', 'public.post p']);

// équivalent à :

$query->from('public.user u, public.post p');
```

Si vous utilisez le format tableau, vous pouvez aussi utiliser les clés du tableau pour spécifier les alias de table, comme suit : 

```php
$query->from(['u' => 'public.user', 'p' => 'public.post']);
```

En plus des noms de table, vous pouvez aussi sélectionner à partir de sous-requêtes en les spécifiant en termes d'objets [[yii\db\Query]].
Par exemple :

```php
$subQuery = (new Query())->select('id')->from('user')->where('status=1');

// SELECT * FROM (SELECT `id` FROM `user` WHERE status=1) u 
$query->from(['u' => $subQuery]);
```

#### Préfixes
Un [[yii\db\Connection::$tablePrefix|préfixe de table]] peut aussi être appliqué. Les instructions de mise en œuvre sont données à la section 
["Entourage des noms de table et de colonne par des marques de citation" du guide sur les objets d'accès aux bases de données" ](db-dao.md#quoting-table-and-column-names).

### [[yii\db\Query::where()|where()]] <span id="where"></span>
La méthode [[yii\db\Query::where()|where()]] spécifie le fragment `WHERE`d'une requête SQL. Vous pouvez utiliser un des quatre formats suivants pour spécifier une condition `WHERE` :

- format chaîne de caractères, p. ex. `'status=1'`
- format haché, p. ex. `['status' => 1, 'type' => 2]`
- format opérateur, p. ex. `['like', 'name', 'test']`
- format objet, p. ex. `new LikeCondition('name', 'LIKE', 'test')`


#### Format chaîne de caractères <span id="string-format"></span>

Le format chaîne de caractères est celui qui convient le mieux pour spécifier des conditions très simples ou si vous avez besoin d'utiliser les fonctions incorporées au système de gestion de base de données. 
Il fonctionne comme si vous écriviez une requête SQL brute. Par exemple : 

```php
$query->where('status=1');

// ou utilisez la liaison des paramètres pour lier des valeurs dynamiques des paramètres.
$query->where('status=:status', [':status' => $status]);

//  SQL brute utilisant la fonction MySQL YEAR() sur un champ de date
$query->where('YEAR(somedate) = 2015');
```

N'imbriquez PAS les variables directement dans la condition comme ce qui suit, spécialement si les valeurs des variables proviennent d'entrées utilisateur, parce que cela rendrait votre application SQL sujette aux attaques par injections SQL.
 

```php
// Dangereux! Ne faites PAS cela sauf si vous êtes tout à fait sûr que $status est un entier
$query->where("status=$status");
```

Lorsque vous utilisez la `liaison des paramètres`, vous pouvez appeler [[yii\db\Query::params()|params()]] ou [[yii\db\Query::addParams()|addParams()]] pour spécifier les paramètres séparément.


```php
$query->where('status=:status')
    ->addParams([':status' => $status]);
```

Comme dans tous les endroits ou il est fait appel à du SQL, vous pouvez utiliser la [syntaxe d'entourage par des marques de citation indifférente au système de gestion de base de données](db-dao.md#quoting-table-and-column-names) pour les noms de table et de colonne lorsque vous écrivez les conditions au format chaîne de caractères.


#### Format haché <span id="hash-format"></span>

Le format valeur de hachage convient le mieux pour spécifier de multiples sous-conditions concaténées par `AND`, chacune étant une simple assertion d'égalité. 
Il se présente sous forme de tableau dont les clés sont les noms des colonnes et les valeurs les valeurs correspondantes que les valeurs des colonnes devraient avoir. 
Par exemple : 

```php
// ...WHERE (`status` = 10) AND (`type` IS NULL) AND (`id` IN (4, 8, 15))
$query->where([
    'status' => 10,
    'type' => null,
    'id' => [4, 8, 15],
]);
```

Comme vous pouvez le voir, le constructeur de requêtes est assez intelligent pour manipuler correctement les valeurs qui sont soit nulles, soit des tableaux.

Vous pouvez utiliser aussi des sous-requêtes avec le format haché comme suit :

```php
$userQuery = (new Query())->select('id')->from('user');

// ...WHERE `id` IN (SELECT `id` FROM `user`)
$query->where(['id' => $userQuery]);
```

En utilisant le format haché, Yii, en interne, utilise la liaison des paramètres pour les valeurs de façon à ce que, contrairement au [format chaîne de caractères](#string-format), vous n'ayez pas à ajouter les paramètres à la main.
Cependant, notez que Yii ne procède pas à l'échappement des noms de colonne, c'est pourquoi si vous passez un nom de variable obtenu de l'utilisateur en tant que nom de colonne sans vérification, l'application devient vulnérable à l'injection SQL. 
Afin de maintenir l'application sûre, soit n'utilisez pas de variables comme nom de colonne, soit filtrez les variables par une liste blanche. 
Dans le cas où vous avez besoin d'obtenir un nom de colonne de l'utilisateur, lisez 
l'article du guide [Filtrage des données](output-data-widgets.md#filtering-data).
Ainsi l'exemple de code suivant est vulnérable :

```php
// Vulnerable code:
$column = $request->get('column');
$value = $request->get('value');
$query->where([$column => $value]);
// $value est sûre, mais le nom de colonne n'est pas encodé!
```

#### Format opérateur <span id="operator-format"></span>

Le format opérateur vous permet de spécifier des conditions arbitraires par programmation. Il accepte les formats suivants : 

```php
[operator, operand1, operand2, ...]
```

dans lequel chacun des opérandes peut être spécifié au format chaîne de caractères, au format haché ou au format opérateur de façon récursive, tandis que l'opérateur peut être un de ceux qui suivent : 

- `and`: les opérandes doivent être concaténés en utilisant `AND`. Par exemple, `['and', 'id=1', 'id=2']` génère `id=1 AND id=2`. 
  Si un opérande est un tableau, il est converti en une chaîne de caractères en utilisant les règles décrites ici. 
  Par exemple, `['and', 'type=1', ['or', 'id=1', 'id=2']]` génère `type=1 AND (id=1 OR id=2)`. 
  La méthode ne procède à aucun entourage par des marques de citation, ni à aucun échappement.



- `or`: similaire à l'opérateur `and` sauf que les opérandes sont concaténés en utilisant `OR`.

- `not`: ne réclame que l'opérande 1, qui est emballé dans  `NOT()`. Par exemple, `['not', 'id=1']` génère `NOT (id=1)`. L'opérande 1 peut aussi être un tableau pour décrire des expressions multiples. Par exemple `['not', ['status' => 'draft', 'name' => 'example']]` génère `NOT ((status='draft') AND (name='example'))`.

- `between`: l'opérande 1 doit être le nom de la colonne, et les opérandes 2 et 3 doivent être les valeurs de départ et de fin de la plage dans laquelle la colonne doit être. Par exemple, `['between', 'id', 1, 10]` génère `id BETWEEN 1 AND 10`.
  Dans le cas où vous avez besoin de construire une expression dans laquelle la valeur est entre deux colonnes (telle que `11 BETWEEN min_id AND max_id`), 
  vous devez utiliser  [[yii\db\conditions\BetweenColumnsCondition|BetweenColumnsCondition]]. 
  Reportez-vous au chapitre [Conditions – Format d'objet](#object-format) pour en savoir plus sur la définition des conditions d'objet.



- `not between`: similaire à `between` sauf que  `BETWEEN` est remplacé par `NOT BETWEEN` 
  dans la condition générée.

- `in`: l'opérande 1 doit être une colonne ou une expression de base de données. 
L'opérande 2 peut être soit un tableau, soit un objet `Query`. 
  Il génère une condition `IN`. Si l'opérande 2 est un tableau, il représente la plage des valeurs que la colonne ou l'expression de base de données peut prendre. 
  Si l'opérande 2 est un objet `Query`, une sous-requête est générée et utilisée comme plage pour la colonne ou l'expression de base de données. 
  Par exemple, `['in', 'id', [1, 2, 3]]` génère `id IN (1, 2, 3)`. 
  La méthode assure correctement l'entourage des noms de colonnes par des marques de citation et l'échappement des valeurs de la plage. 
  L'opérateur `in` prend aussi en charge les colonnes composites. 
  Dans ce cas, l'opérande 1 doit être un tableau des colonnes, tandis que l'opérateur 2 doit être un tableau de tableaux, ou un objet `Query` représentant la plage de colonnes.

- `not in`: similaire à l'opérateur `in` sauf que `IN` est remplacé par `NOT IN` dans la condition générée.

- `like`: l'opérande 1 doit être une colonne ou une expression de base de données, tandis que l'opérande 2 doit être une chaîne de caractères ou 
  un tableau représentant les valeurs que cette colonne ou cette expression de base de données peuvent être. 
  Par exemple, `['like', 'name', 'tester']` génère `name LIKE '%tester%'`. 
  Lorsque la plage de valeurs est donnée sous forme de tableau, de multiples prédicats `LIKE` sont générés et concaténés 
  en utilisant `AND`. 
  Par exemple, `['like', 'name', ['test', 'sample']]` génère `name LIKE '%test%' AND name LIKE '%sample%'`. 
  Vous pouvez également fournir un troisième paramètre facultatif pour spécifier comment échapper les caractères spéciaux dans les valeurs. 
  Les opérandes doivent être un tableau de correspondance 
  entre les caractères spéciaux et les contre-parties échappées. 
  Si cet opérande n'est pas fourni, une mise en correspondance par défaut est utilisée. 
  Vous pouvez utiliser `false` ou un tableau vide pour indiquer que les valeurs sont déjà échappées et qu'aucun échappement ne doit être appliqué. 
  Notez que lorsqu'un tableau de mise en correspondance pour l'échappement est utilisé (ou quand le troisième opérande n'est pas fourni), les valeurs sont automatiquement entourées par une paire de caractères `%`. 

  > Note: lors de l'utilisation de PostgreSQL vous pouvez aussi utiliser [`ilike`](https://www.postgresql.org/docs/8.3/static/functions-matching.html#FUNCTIONS-LIKE)   
  >à la place de `like` pour une mise en correspondance insensible à la casse.

- `or like`: similaire à l'opérateur `like` sauf que `OR`est utilisé pour concaténer les prédicats `LIKE` 
  quand l'opérande 2 est un tableau. 

- `not like`: similaire à l'opérateur `like` sauf que `LIKE` est remplacé par `NOT LIKE` 
  dans la condition générée.

- `or not like`: similaire à l'opérateur `not like` sauf que `OR` est utilisé pour concaténer 
  les prédicats `NOT LIKE`.

- `exists`: requiert un opérande que doit être une instance de [[yii\db\Query]] représentant la sous-requête.  
  Il construit une expression `EXISTS (sub-query)`.

- `not exists`: similaire à l'opérateur `exists` et construit une expression `NOT EXISTS (sub-query)`.

- `>`, `<=`, ou tout autre opérateur de base de données valide qui accepte deux opérandes : 
  le premier opérande doit être un nom de colonne, tandis que le second doit être une valeur. Par exemple, `['>', 'age', 10]` génère `age>10`.

En utilisant le format opérateur, Yii, en interne, utilise la liaison des paramètres afin, que contrairement au [format chaîne de caractères](#string-format), ici, vous n'avez pas besoin d'ajouter les paramètres à la main. 
Cependant, notez que Yii ne procède pas à l'échappement des noms de colonne, c'est pourquoi si vous passez un nom de variable obtenu de l'utilisateur en tant que nom de colonne sans vérification, l'application devient vulnérable à l'injection SQL. 
Afin de maintenir l'application sûre, soit n'utilisez pas de variables comme nom de colonne, soit filtrez les variables par une liste blanche. 
Dans le cas où vous avez besoin d'obtenir un nom de colonne de l'utilisateur, lisez 
l'article du guide [Filtrage des données](output-data-widgets.md#filtering-data).
Ainsi l'exemple de code suivant est vulnérable :

```php
// Code vulnérable:
$column = $request->get('column');
$value = $request->get('value');
$query->where(['=', $column, $value]);
// $value est sûre, mais le nom  $column n'est pas encodé !
```

#### Format objet <span id="object-format"></span>

Le format objet est disponible depuis 2.0.14 et est à la fois le moyen plus puissant et le plus complexe pour définir des conditions.
Vous devez le suivre si vous voulez construire votre propre abstraction au-dessus du constructeur de requêtes (query builder) ou si vous voulez mettre en œuvre vos propres conditions complexes.


Les instances de classes de condition sont immuables. 
Le seul but est de stocker des données de condition et de fournir des obtenteurs (getters) pour les constructeurs de conditions. 
La classe « constructeur de condition » (condition builder) est une classe qui contient la logique qui transforme les données stockées en condition dans une expression SQL.

En interne, les formats décrits plus haut sont implicitement convertis en format objet avant de construire le SQL brut, 
 aussi est-il possible de combiner les formats en une condition unique :

```php
$query->andWhere(new OrCondition([
    new InCondition('type', 'in', $types),
    ['like', 'name', '%good%'],
    'disabled=false'
]))
```

La conversion du format opérateur au format objet est accomplie en fonction de la propriété
[[yii\db\QueryBuilder::conditionClasses|QueryBuilder::conditionClasses]] , 
qui fait correspondre des noms d'opérateurs à des nom de classe représentatives :

- `AND`, `OR` -> `yii\db\conditions\ConjunctionCondition`
- `NOT` -> `yii\db\conditions\NotCondition`
- `IN`, `NOT IN` -> `yii\db\conditions\InCondition`
- `BETWEEN`, `NOT BETWEEN` -> `yii\db\conditions\BetweenCondition`

Et ainsi de suite.

L'utilisation du format objet rend possible de créer vos propres conditions ou de changer la manière dont celles par défaut sont construites.
Reportez-vous au chapitre  [Ajout de conditions et d'expressions personnalisées](#adding-custom-conditions-and-expressions) pour en savoir plus.


#### Ajout de conditions <span id="appending-conditions"></span>

Vous pouvez utiliser [[yii\db\Query::andWhere()|andWhere()]] ou [[yii\db\Query::orWhere()|orWhere()]] pour ajouter des conditions supplémentaires à une condition existante. 
Vous pouvez les appeler plusieurs fois pour ajouter plusieurs conditions séparément. 
Par exemple :

```php
$status = 10;
$search = 'yii';

$query->where(['status' => $status]);

if (!empty($search)) {
   $query->andWhere(['like', 'title', $search]);
}
```

Si `$search` n'est pas vide, la condition `WHERE` suivante est générée :

```sql
WHERE (`status` = 10) AND (`title` LIKE '%yii%')
```


#### Conditions de filtrage <span id="filter-conditions"></span>

Lors de la construction de conditions  `WHERE` basées sur des entrées de l'utilisateur final, 
vous voulez généralement ignorer les valeurs entrées qui sont vides. 
Par exemple, dans un formulaire de recherche par nom d'utilisateur ou par adresse de courriel, vous aimeriez ignorer la condition nom d'utilisateur/adresse de courriel si l'utilisateur n'a rien saisi dans les champs correspondants. 
Vous pouvez faire cela en utilisant la méthode [[yii\db\Query::filterWhere()|filterWhere()]] :

```php
// $username et $email sont entrées par l'utilisateur
$query->filterWhere([
    'username' => $username,
    'email' => $email,
]);
```

La seule différence entre [[yii\db\Query::filterWhere()|filterWhere()]] et [[yii\db\Query::where()|where()]] est que la première ignore les valeurs vides fournies dans la condition au [format haché](#hash-format). 
Ainsi si `$email` est vide alors que `$username` ne l'est pas, 
le code ci dessus produit la condition SQL `WHERE username=:username`.

> Info: une valeur est considérée comme vide si elle est nulle, un tableau vide, ou un chaîne de caractères vide, ou un chaîne de caractères constituée d'espaces uniquement.

Comme avec [[yii\db\Query::andWhere()|andWhere()]] et [[yii\db\Query::orWhere()|orWhere()]], 
vous pouvez utiliser [[yii\db\Query::andFilterWhere()|andFilterWhere()]] et [[yii\db\Query::orFilterWhere()|orFilterWhere()]] 
pour ajouter des conditions de filtrage supplémentaires à une condition existante.

En outre, il y a [[yii\db\Query::andFilterCompare()]] qui peut déterminer intelligemment l'opérateur 
en se basant sur ce qu'il y a dans les valeurs : 

```php
$query->andFilterCompare('name', 'John Doe');
$query->andFilterCompare('rating', '>9');
$query->andFilterCompare('value', '<=100');
```

Vous pouvez aussi utiliser un opérateur explicitement :

```php
$query->andFilterCompare('name', 'Doe', 'like');
```

Depuis Yii 2.0.1, il existe des méthodes similaires pour la condition  `HAVING` :

- [[yii\db\Query::filterHaving()|filterHaving()]]
- [[yii\db\Query::andFilterHaving()|andFilterHaving()]]
- [[yii\db\Query::orFilterHaving()|orFilterHaving()]]

### [[yii\db\Query::orderBy()|orderBy()]] <span id="order-by"></span>

La méthode [[yii\db\Query::orderBy()|orderBy()]] spécifie le fragment `ORDER BY` d'une requête SQL. Par exemple :

```php
// ... ORDER BY `id` ASC, `name` DESC
$query->orderBy([
    'id' => SORT_ASC,
    'name' => SORT_DESC,
]);
```
 
Dans le code ci-dessus, les clés du tableau sont des noms de colonnes, tandis que les valeurs sont les instructions de direction de tri. 
La constante PHP `SORT_ASC` spécifie un tri ascendant et `SORT_DESC`, un tri descendant.

Si `ORDER BY` ne fait appel qu'à des noms de colonnes simples, vous pouvez le spécifier en utilisant une chaîne de caractères, juste comme vous le faites en écrivant des instructions SQL brutes. 
Par exemple :

```php
$query->orderBy('id ASC, name DESC');
```

> Note: vous devez utiliser le format tableau si `ORDER BY` fait appel à une expression de base de données.

Vous pouvez appeler [[yii\db\Query::addOrderBy()|addOrderBy()]] pour ajouter des colonnes supplémentaires au fragment `ORDER BY`. 
Par exemple :

```php
$query->orderBy('id ASC')
    ->addOrderBy('name DESC');
```


### [[yii\db\Query::groupBy()|groupBy()]] <span id="group-by"></span>

La méthode [[yii\db\Query::groupBy()|groupBy()]] spécifie le fragment `GROUP BY` d'une requête SQL. Par exemple :

```php
// ... GROUP BY `id`, `status`
$query->groupBy(['id', 'status']);
```

Si `GROUP BY` ne fait appel qu'à des noms de colonnes simples, vous pouvez le spécifier en utilisant un chaîne de caractères, juste comme vous le faîtes en écrivant des instructions SQL brutes. 
Par exemple :

```php
$query->groupBy('id, status');
```

> Note: vous devez utiliser le format tableau si `GROUP BY` fait appel à une expression de base de données.
 
Vous pouvez appeler [[yii\db\Query::addGroupBy()|addGroupBy()]] pour ajouter des colonnes au fragment `GROUP BY`. 
Par exemple :

```php
$query->groupBy(['id', 'status'])
    ->addGroupBy('age');
```


### [[yii\db\Query::having()|having()]] <span id="having"></span>

La méthode [[yii\db\Query::having()|having()]] 
spécifie le fragment `HAVING` d'un requête SQL. Elle accepte une condition qui peut être spécifiée de la même manière que celle pour [where()](#where). Par exemple :

```php
// ... HAVING `status` = 1
$query->having(['status' => 1]);
```

Reportez-vous à la documentation de [where()](#where) pour plus de détails sur la manière de spécifier une condition. 

Vous pouvez appeler [[yii\db\Query::andHaving()|andHaving()]] ou [[yii\db\Query::orHaving()|orHaving()]] pour ajouter des conditions supplémentaires au fragment `HAVING` fragment. 
Par exemple :

```php
// ... HAVING (`status` = 1) AND (`age` > 30)
$query->having(['status' => 1])
    ->andHaving(['>', 'age', 30]);
```


### [[yii\db\Query::limit()|limit()]] et [[yii\db\Query::offset()|offset()]] <span id="limit-offset"></span>

Les méthodes [[yii\db\Query::limit()|limit()]] et [[yii\db\Query::offset()|offset()]] spécifient les fragments `LIMIT` et `OFFSET` d'une requête SQL. 
Par exemple : 

```php
// ... LIMIT 10 OFFSET 20
$query->limit(10)->offset(20);
```

Si vous spécifiez une limite ou un décalage (p. ex. une valeur négative), il est ignoré. 

> Info: pour les systèmes de gestion de base de données qui ne prennent pas en charge `LIMIT` et `OFFSET` (p. ex. MSSQL), 
le constructeur de requêtes génère une instruction SQL qui émule le comportement `LIMIT`/`OFFSET`.


### [[yii\db\Query::join()|join()]] <span id="join"></span>

La méthode [[yii\db\Query::join()|join()]] spécifie le fragment `JOIN` d'une requête SQL. Par exemple :

```php
// ... LEFT JOIN `post` ON `post`.`user_id` = `user`.`id`
$query->join('LEFT JOIN', 'post', 'post.user_id = user.id');
```

La méthode [[yii\db\Query::join()|join()]] accepte quatre paramètres :

- `$type`: type de jointure , p. ex. `'INNER JOIN'`, `'LEFT JOIN'`.
- `$table`: le nom de la table à joindre.
- `$on`: facultatif, la condition de jointure, c.-à-d. le fragment `ON`. 
  Reportez-vous à [where()](#where) pour des détails sur la manière de spécifier une condition. 
  Notez, que la syntaxe tableau ne fonctionne **PAS** pour spécifier une condition basée sur une colonne, p. ex. `['user.id' => 'comment.userId']` conduit à une condition 
  où l'identifiant utilisateur doit être égal à la chaîne de caractères `'comment.userId'`. 
  Vous devez utiliser la syntaxe chaîne de caractères à la place et spécifier la condition `'user.id = comment.userId'`.
- `$params`: facultatif, les paramètres à lier à la condition de jointure. 

Vous pouvez utiliser les méthodes raccourcies suivantes pour spécifier `INNER JOIN`, `LEFT JOIN` et `RIGHT JOIN`, respectivement.

- [[yii\db\Query::innerJoin()|innerJoin()]]
- [[yii\db\Query::leftJoin()|leftJoin()]]
- [[yii\db\Query::rightJoin()|rightJoin()]]

Par exemple :

```php
$query->leftJoin('post', 'post.user_id = user.id');
```

Pour joindre plusieurs tables, appelez les méthodes join ci-dessus plusieurs fois, une fois pour chacune des tables.

En plus de joindre des tables, vous pouvez aussi joindre des sous-requêtes. Pour faire cela, spécifiez les sous-requêtes à joindre sous forme d'objets [[yii\db\Query]]. 
Par exemple :

```php
$subQuery = (new \yii\db\Query())->from('post');
$query->leftJoin(['u' => $subQuery], 'u.id = author_id');
```

Dans ce cas, vous devez mettre la sous-requête dans un tableau et utiliser les clés du tableau pour spécifier les alias.


### [[yii\db\Query::union()|union()]] <span id="union"></span>

La méthode [[yii\db\Query::union()|union()]] spécifie le fragment `UNION` d'une requête SQL. Par exemple :

```php
$query1 = (new \yii\db\Query())
    ->select("id, category_id AS type, name")
    ->from('post')
    ->limit(10);

$query2 = (new \yii\db\Query())
    ->select('id, type, name')
    ->from('user')
    ->limit(10);

$query1->union($query2);
```

Vous pouvez appeler [[yii\db\Query::union()|union()]] plusieurs fois pour ajouter plus de fragments `UNION`. 


## Méthodes de requête <span id="query-methods"></span>

L'objet [[yii\db\Query]] fournit un jeu complet de méthodes pour différents objectifs de requêtes :

- [[yii\db\Query::all()|all()]]: retourne un tableau de lignes dont chacune des lignes est un tableau associatif de paires clé-valeur.
- [[yii\db\Query::one()|one()]]: retourne la première ligne du résultat. 
- [[yii\db\Query::column()|column()]]: retourne la première colonne du résultat. 
- [[yii\db\Query::scalar()|scalar()]]: retourne une valeur scalaire située au croisement de la première ligne et de la première colonne du résultat.
- [[yii\db\Query::exists()|exists()]]: retourne une valeur précisant si le résultat de la requête contient un résultat.
- [[yii\db\Query::count()|count()]]: retourne le résultat d'une requête `COUNT`..
- D'autres méthodes d'agrégation de requêtes, y compris [[yii\db\Query::sum()|sum($q)]], [[yii\db\Query::average()|average($q)]], [[yii\db\Query::max()|max($q)]], [[yii\db\Query::min()|min($q)]]. 
  Le paramètre `$q` est obligatoire pour ces méthodes 
  et peut être soit un nom de colonne, soit une expression de base de données.

Par exemple :

```php
// SELECT `id`, `email` FROM `user`
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->all();
    
// SELECT * FROM `user` WHERE `username` LIKE `%test%`
$row = (new \yii\db\Query())
    ->from('user')
    ->where(['like', 'username', 'test'])
    ->one();
```

> Note: la méthode [[yii\db\Query::one()|one()]] retourne seulement la première ligne du résultat de la requête. 
  Elle n'ajoute PAS `LIMIT 1` à l'instruction SQL générée. 
  Cela est bon et préférable si vous savez que la requête ne retourne qu'une seule 
  ou quelques lignes de données (p. ex. si vous effectuez une requête avec quelques clés primaires). 
  Néanmoins, si la requête peut potentiellement retourner de nombreuses lignes de données, vous devriez appeler `limit(1)` explicitement pour améliorer la performance, p. ex. `(new \yii\db\Query())->from('user')->limit(1)->one()`.

Toutes ces méthodes de requête acceptent un paramètre supplémentaire `$db` représentant la [[yii\db\Connection|connexion à la base de données]] 
qui doit être utilisée pour effectuer la requête. 
Si vous omettez ce paramètre, le [composant d'application](structure-application-components.md) `db` est utilisé en tant que connexion à la base de données. Ci-dessous, nous présentons un autre exemple utilisant la méthode [[yii\db\Query::count()|count()]] :

```php
// exécute SQL: SELECT COUNT(*) FROM `user` WHERE `last_name`=:last_name
$count = (new \yii\db\Query())
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->count();
```

Lorsque vous appelez une méthode de requête de [[yii\db\Query]], elle effectue réellement le travail suivant en interne :

* Appelle [[yii\db\QueryBuilder]] pour générer une instruction SQL basée sur la construction courante de [[yii\db\Query]] ;
* Crée un objet [[yii\db\Command]] avec l'instruction SQL générée ;
* Appelle une méthode de requête (p. ex. [[yii\db\Command::queryAll()|queryAll()]]) de [[yii\db\Command]] pour exécuter une instruction SQL et retrouver les données.

Parfois, vous voulez peut-être examiner ou utiliser une instruction SQL construite à partir d'un objet [[yii\db\Query]]. Vous pouvez faire cela avec le code suivant :


```php
$command = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['last_name' => 'Smith'])
    ->limit(10)
    ->createCommand();
    
// affiche l'instruction SQL
echo $command->sql;
// affiche les paramètres à lier 
print_r($command->params);

// retourne toutes les lignes du résultat de la requête 
$rows = $command->queryAll();
```


### Indexation des résultats de la requête <span id="indexing-query-results"></span>

Lorsque vous appelez [[yii\db\Query::all()|all()]], elle retourne un tableau de lignes qui sont indexées par des entiers consécutifs. 
Parfois, vous désirez peut-être les indexer différemment, comme les indexer par une colonne particulière ou par des expressions donnant une valeur. 
Vous pouvez le faire en appelant [[yii\db\Query::indexBy()|indexBy()]] avant [[yii\db\Query::all()|all()]]. 
Par exemple :

```php
// retourne [100 => ['id' => 100, 'username' => '...', ...], 101 => [...], 103 => [...], ...]
$query = (new \yii\db\Query())
    ->from('user')
    ->limit(10)
    ->indexBy('id')
    ->all();
```

Pour indexer par des valeurs d'expressions, passez une fonction anonyme à la méthode [[yii\db\Query::indexBy()|indexBy()]] :

```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy(function ($row) {
        return $row['id'] . $row['username'];
    })->all();
```

Le fonction anonyme accepte un paramètre `$row` qui contient les données de la ligne courante 
et retourne une valeur scalaire qui est utilisée comme la valeur d'index de la ligne courante.

> Note: contrairement aux méthodes de requête telles que [[yii\db\Query::groupBy()|groupBy()]] ou [[yii\db\Query::orderBy()|orderBy()]] 
qui sont converties en SQL et font partie de la requête, cette méthode ne fait son travail qu'après que les données ont été retrouvées dans la base de données. 
> Cela signifie que seuls les noms de colonne qui on fait partie du fragment SELECT dans votre requête peuvent être utilisés.
> De plus, si vous avez sélectionné une colonne avec un préfixe de table, p. ex. `customer.id`, le jeu de résultats ne contient que `id` c'est pourquoi vous devez appeler
> `->indexBy('id')` sans  préfixe de table.


### Requêtes par lots <span id="batch-query"></span>

Lorsque vous travaillez sur de grandes quantités de données, des méthodes telles que [[yii\db\Query::all()]] ne conviennent pas  car elles requièrent le chargement de toutes les données en mémoire du client.
 Pour résoudre cet problème Yii assure la prise en charge de requêtes par lots. 
Le serveur conserve les résultats de la requête, et le client utilise un curseur  pour itérer sur le jeu de résultats un lot à la fois. 


> Attention : il existe des limitations connues et des solutions de contournement pour la mise en œuvre des requêtes par lots par MySQL.

Les requêtes par lots peuvent être utilisées comme suit :

```php
use yii\db\Query;

$query = (new Query())
    ->from('user')
    ->orderBy('id');

foreach ($query->batch() as $users) {
    // $users est dans un tableau de 100 ou moins lignes de la table user. 
}

// ou si vous voulez itérer les lignes une par une 
foreach ($query->each() as $user) {
     //les données sont retrouvées du serveur en lots de 100,
    // $user représente une ligne de données de la table user.
}
```

Les méthodes [[yii\db\Query::batch()]] et [[yii\db\Query::each()]] retournent un objet [[yii\db\BatchQueryResult]] 
qui implémente l'interface `Iterator` et qui, par conséquent, peut être utilisé dans une construction `foreach`.
Durant la première itération, une requête SQL est faite à la base de données. Les données sont retrouvées en lots dans les itérations suivantes. 
Par défaut, la taille du lot est 100, ce qui signifie que 100 lignes sont retrouvées dans chacun des lots. 
Vous pouvez changer la taille du lot en passant le premier paramètre des méthodes `batch()` ou `each()`.

Comparée à la requête [[yii\db\Query::all()]], la requête par lots ne charge que 100 lignes de données à la fois en mémoire. Si vous traitez les données et les détruisez tout de suite, la requête par lots réduit l'utilisation de la mémoire. 

Si vous spécifiez l'indexation du résultat de la requête par une colonne via [[yii\db\Query::indexBy()]], 
la requête par lots conserve l'index approprié. Par exemple :

Par exemple :

```php
$query = (new \yii\db\Query())
    ->from('user')
    ->indexBy('username');

foreach ($query->batch() as $users) {
    // $users est indexé par la colonne "username"
}

foreach ($query->each() as $username => $user) {
    // ...
}
```

#### Limitations des requêtes par lots dans MySQL <span id="batch-query-mysql"></span>

La mise en œuvre des requêtes par lots de MySQL s'appuie sur la bibliothèque du pilote PDO. Par défaut, les requêtes MySQL sont 
[`mises en tampon`](https://www.php.net/manual/fr/mysqlinfo.concepts.buffering.php). 
Cela empêche d'utiliser le curseur pour obtenir les données, parce que cela n'empêche pas le jeu résultant complet d'être chargé dans la mémoire du client par le pilote.


> Note: lorsque `libmysqlclient` est utilisé (typique de PHP5), la limite mémoire de  PHP ne compte pas la mémoire utilisée par les jeux de résultats. 
Il peut sembler que les requêtes par lot fonctionnent correctement, mais en réalité l'intégralité du jeu de données est chargé dans la mémoire du client.
 

Pour désactiver la mise en tampon et réduire les exigences en mémoire client, la propriété connexion à PDOT
`PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` doit être définie à `false`. Cependant, jusqu'à ce que l'intégralité du jeu de données ait été retrouvé, aucune autre requête ne peut être faite via la même connexion. Cela peut empêcher  `ActiveRecord` 
d'effectuer une requête pour obtenir le schéma de table lorsqu'il le doit. 
Si cela n'est pas un problème (le schéma de table est déjà mis en cache), il est possible de commuter la connexion originale en mode sans mise en tampon, 
et de revenir en arrière lorsque la requête par lots est terminée.


```php
Yii::$app->db->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

// Effectue la requête par lots

Yii::$app->db->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
```

> Note: dans le cas de  MyISAM, pour toute la durée de la requête par lots, la table peut devenir verrouillée, retardant ainsi ou refusant l'accès en écriture pour une autre connexion. Lors de l'utilisation de requêtes sans mise en tampon, essayez de conserver le curseur ouvert pour un temps aussi court que possible.  

Si le schéma n'est pas mis en cache, ou s'il est nécessaire d'effectuer d'autres requêtes alors que la requête par lots est en cours de traitement, vous pouvez créer une connexion à la base de données séparée sans mise en tampon : 

```php
$unbufferedDb = new \yii\db\Connection([
    'dsn' => Yii::$app->db->dsn,
    'username' => Yii::$app->db->username,
    'password' => Yii::$app->db->password,
    'charset' => Yii::$app->db->charset,
]);
$unbufferedDb->open();
$unbufferedDb->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
```

Si vous voulez garantir que la `$unbufferedDb` a exactement les mêmes attributs PDO gue la `$db` originale avec mise en tampon mais que `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` est `false`, 
[envisagez une copie profonde de `$db`](https://github.com/yiisoft/yii2/issues/8420#issuecomment-301423833), 
définissez le à `false` manuellement.

Ensuite, les requêtes sont créées normalement. La nouvelle connexion est utilisée pour exécuter les requêtes par lots et retrouver des résultats soit par lots, soit un par un :

```php
// obtention des données par lots de 1000
foreach ($query->batch(1000, $unbufferedDb) as $users) {
    // ...
}


// les données sont retrouvées dans le serveur par lots de 1000, mais elles sont itérées une à une 
foreach ($query->each(1000, $unbufferedDb) as $user) {
    // ...
}
```

Lorsque la connexion n'est plus nécessaire et que le jeu de résultats a été retrouvé, on peut le fermer :

```php
$unbufferedDb->close();
```

> Note: une requête sans mise en tampon utilise moinsde mémoire du côté PHP, mais peut augmenter la charge du serveur MySQL. 
> Il est recommandé de concevoir votre propre code avec votre pratique en production pour des données massives supplémentaires,
> [par exemple, divisez la plage pour les clés entières, itérez sur elles avec des requêtes sans mise en tampon](https://github.com/yiisoft/yii2/issues/8420#issuecomment-296109257).






### Ajout de conditions et expressions personnalisées <span id="adding-custom-conditions-and-expressions"></span>

Comme cela a été mentionné au chapitre [Conditions – Format Object](#object-format), il est possible de créer des classe de condition personnalisées.
Pour l'exemple, créons une condition qui vérifie que des colonnes spécifiques sont inférieures à une valeur donnée.
En utilisant le format opérateur, ça devrait ressembler à ce qui suit : 

```php
[
    'and',
    '>', 'posts', $minLimit,
    '>', 'comments', $minLimit,
    '>', 'reactions', $minLimit,
    '>', 'subscriptions', $minLimit
]
```

Lorsqu'une telle condition est utilisée une seule fois, tout va bien. Dans le cas où elle est utilisée de multiples fois dans une unique requête, cela peut être grandement optimisé. 
Créons un objet condition personnalisé pour le démontrer.

Yii dispose d'une classe [[yii\db\conditions\ConditionInterface|ConditionInterface]], qui peut être utilisée pour marquer des classes qui représentent une condition. 
Elle nécessite la mise en œuvre de la méthode `fromArrayDefinition()`, afin de rendre possible la création d'une condition à partir du format tableau. 
Dans le cas où vous n'en n'avez pas besoin, vous pouvez mettre cette méthode en œuvre avec lancement d'une exception. 

Comme nous créons notre classe de condition personnalisée, nous pouvons construire une API qui s'adapte au mieux à notre tâche.

```php
namespace app\db\conditions;

class AllGreaterCondition implements \yii\db\conditions\ConditionInterface
{
    private $columns;
    private $value;

    /**
     * @param string[] $columns tableau de colonnes qui doivent être plus grande que $value
     * @param mixed $value la valeur à laquelle comparer chaque $column
     */
    public function __construct(array $columns, $value)
    {
        $this->columns = $columns;
        $this->value = $value;
    }
    
    public static function fromArrayDefinition($operator, $operands)
    {
        throw new InvalidArgumentException('Not implemented yet, but we will do it later');
    }
    
    public function getColumns() { return $this->columns; }
    public function getValue() { return $this->vaule; }
}
```

Ainsi nous pouvons créer un objet condition :

```php
$conditon = new AllGreaterCondition(['col1', 'col2'], 42);
```

Mais `QueryBuilder` (le constructeur de requêtes) ne sait toujours pas comment élaborer une condition SQL à partir de cet objet. 
Maintenant nous devons créer un constructeur pour cette condition. Il doit mettre en œuvre une méthode `build()`. 

```php
namespace app\db\conditions;

class AllGreaterConditionBuilder implements \yii\db\ExpressionBuilderInterface
{
    use \yii\db\Condition\ExpressionBuilderTrait; // Contient le constructeur et la propriété `queryBuilder`.

    /**
     * @param AllGreaterCondition $condition la condition à élaborer
     * @param array $params les paramètres de liaison.
     */ 
    public function build(ConditionInterface $condition, &$params)
    {
        $value = $condition->getValue();
        
        $conditions = [];
        foreach ($condition->getColumns() as $column) {
            $conditions[] = new SimpleCondition($column, '>', $value);
        }

        return $this->queryBuider->buildCondition(new AndCondition($conditions), $params);
    }
}
```

Ensuite, laissons simplement  [[yii\db\QueryBuilder|QueryBuilder]] prendre connaissance de notre nouvelle condition — établissons une correspondance entre lui et notre tableau  `expressionBuilders`. Cela peut se faire directement à partir de la configuration de l'application :

```php
'db' => [
    'class' => 'yii\db\mysql\Connection',
    // ...
    'queryBuilder' => [
        'expressionBuilders' => [
            'app\db\conditions\AllGreaterCondition' => 'app\db\conditions\AllGreaterConditionBuilder',
        ],
    ],
],
```

Maintenant nous sommes en mesure d'utiliser notre condition dans `where()`:

```php
$query->andWhere(new AllGreaterCondition(['posts', 'comments', 'reactions', 'subscriptions'], $minValue));
```

Si nous voulons rendre possible la création  de notre condition personnalisée en utilisant le format opérateur, nous devons le déclarer dans 
[[yii\db\QueryBuilder::conditionClasses|QueryBuilder::conditionClasses]]:

```php
'db' => [
    'class' => 'yii\db\mysql\Connection',
    // ...
    'queryBuilder' => [
        'expressionBuilders' => [
            'app\db\conditions\AllGreaterCondition' => 'app\db\conditions\AllGreaterConditionBuilder',
        ],
        'conditionClasses' => [
            'ALL>' => 'app\db\conditions\AllGreaterCondition',
        ],
    ],
],
```

Et créer une mise en œuvre réelle de la méthode  `AllGreaterCondition::fromArrayDefinition()`  
dans `app\db\conditions\AllGreaterCondition`:

```php
namespace app\db\conditions;

class AllGreaterCondition implements \yii\db\conditions\ConditionInterface
{
    // ... see the implementation above
     
    public static function fromArrayDefinition($operator, $operands)
    {
        return new static($operands[0], $operands[1]);
    }
}
```
    
À la suite de cela, nous pouvons créer notre condition personnalisée en utilisant un format opérateur plus court :

```php
$query->andWhere(['ALL>', ['posts', 'comments', 'reactions', 'subscriptions'], $minValue]);
```

Vous pouvez noter que deux conceptes ont été utilisés : Expressions et Conditions. Il y a une  [[yii\db\ExpressionInterface]] qui doit être utilisée pour marquer les objets qui requièrent une classe constructrice d'expression qui met en œuvre 
[[yii\db\ExpressionBuilderInterface]] pour être construite. Il existe également une  [[yii\db\condition\ConditionInterface]], qui étend 
[[yii\db\ExpressionInterface|ExpressionInterface]] et doit être utilisée pour des objets qui peuvent être créés à partir d'un tableau de définition comme cela a été expliqué plus haut, mais qui peuvent aussi bien nécessiter le constructeur.

Pour résumer:

- Expression – est un objet de transfert de donnèes — Data Transfer Object (DTO) — pour un jeu de données, qui peut être compilé en une instruction SQL  (un opérateur, une chaîne de caractères, un tableau, JSON, etc).
- Condition – est un super jeu d'expressions, qui agrège de multiples expressions (ou valeurs scalaires) qui peut être compilé en une unique condition SQL.

Vous pouvez créer votre propre classe qui met en œuvre l'interface  [[yii\db\ExpressionInterface|ExpressionInterface]] pour cacher la complexité de la transformation de données en instructions SQL. Vous en apprendrez plus sur d'autres exemples d'expressions dans le 
[prochain article](db-active-record.md);
