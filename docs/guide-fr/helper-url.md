Classe assistante Url
=====================

La classe assistante Url fournit un jeu de méthodes statiques pour gérer les URL.


## Obtenir des URL communes <span id="getting-common-urls"></span>

Vous pouvez utiliser deux méthodes pour obtenir des URL communes : l'URL de la page d'accueil et l'URL de base de la requête courante. Pour obtenir l'URL de la page d'accueil, utilisez ce qui suit :

```php
$relativeHomeUrl = Url::home();
$absoluteHomeUrl = Url::home(true);
$httpsAbsoluteHomeUrl = Url::home('https');
```

Si aucun paramètre n'est passé, l'URL générée est relative. Vous pouvez passer `true` pour obtenir une URL absolue pour le schéma courant ou spécifier un schéma explicitement (`https`, `http`).

Pour obtenir l'URL de base de la requête courante utilisez ceci :
 
```php
$relativeBaseUrl = Url::base();
$absoluteBaseUrl = Url::base(true);
$httpsAbsoluteBaseUrl = Url::base('https');
```

L'unique paramètre de la méthode fonctionne comme pour `Url::home()`.


## Création d'URL <span id="creating-urls"></span>

En vue de créer une URL pour une route donnée, utilisez la méthode `Url::toRoute()`. La méthode utilise [[\yii\web\UrlManager]] pour créer une URL :

```php
$url = Url::toRoute(['product/view', 'id' => 42]);
```
 
Vous pouvez spécifier la route sous forme de chaîne de caractère, p. ex. `site/index`. Vous pouvez également utiliser un tableau si vous désirez spécifier des paramètres de requête supplémentaires pour l'URL créée. Le format du tableau doit être :

```php
// génère : /index.php?r=site%2Findex&param1=value1&param2=value2
['site/index', 'param1' => 'value1', 'param2' => 'value2']
```

Si vous voulez créer une URL avec une ancre, vous pouvez utiliser le format de tableau avec un paramètre `#`. Par exemple :

```php
// génère: /index.php?r=site%2Findex&param1=value1#name
['site/index', 'param1' => 'value1', '#' => 'name']
```

Une route peut être ,soit absolue, soit relative. Une route absolue commence par une barre oblique de division (p. ex. `/site/index`) tandis que route relative commence sans ce caractère (p. ex. `site/index` ou `index`). Une route relative peut être convertie en une route absolue en utilisant une des règles suivantes :
- Si la route est une chaîne de caractères vide, la [[\yii\web\Controller::route|route]] est utilisée ;
- Si la route ne contient aucune barre oblique de division (p. ex. `index`), elle est considérée être un identifiant d'action dans le contrôleur courant et sera préfixée par l'identifiant du contrôleur ([[\yii\web\Controller::uniqueId]]);
- Si la route ne commence pas par une barre oblique de division (p. ex. `site/index`), elle est considérée être une route relative au module courant et sera préfixée par l'identifiant du module ([[\yii\base\Module::uniqueId|uniqueId]]).
  
Depuis la version 2.0.2, vous pouvez spécifier une route sous forme d'[alias](concept-aliases.md). Si c'est le cas, l'alias sera d'abord converti en la route réelle puis transformé en une route absolue en respectant les règles ci-dessus.

Voci quelques exemple d'utilisation de cette méthode :

```php
// /index.php?r=site%2Findex
echo Url::toRoute('site/index');

// /index.php?r=site%2Findex&src=ref1#name
echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post%2Fedit&id=100     assume the alias "@postEdit" is defined as "post/edit"
echo Url::toRoute(['@postEdit', 'id' => 100]);

// https://www.example.com/index.php?r=site%2Findex
echo Url::toRoute('site/index', true);

// https://www.example.com/index.php?r=site%2Findex
echo Url::toRoute('site/index', 'https');
```

Il existe une autre méthode `Url::to()` très similaire à  [[toRoute()]]. La seule différence est que cette méthode requiert la spécification d'une route sous forme de tableau seulement. Si une chaîne de caractères est données, elle est traitée comme une URL.

Le premier argument peut être :
         
- un tableau : [[toRoute()]] sera appelée pour générer l'URL. Par exemple :
  `['site/index']`, `['post/index', 'page' => 2]`. Reportez-vous à la méthode [[toRoute()]] pour plus de détails sur la manière de spécifier une route.
- une chaîne de caractères commençant par `@`: elle est traitée commme un alias, et la chaine aliasée correspondante est retournée ;
- une chaîne de caractères vide : l'URL couramment requise est retournée ;
- une chaîne de caractères normale : elle est retournée telle que.

Lorsque `$scheme` est spécifié (soit une chaîne de caractères, soit `true`), une URL absolue avec l'information hôte tirée de [[\yii\web\UrlManager::hostInfo]]) est retournée. Si`$url` est déjà une URL absolue, son schéma est remplacé par celui qui est spécifié. 

Voici quelques exemples d'utilisation :

```php
// /index.php?r=site%2Findex
echo Url::to(['site/index']);

// /index.php?r=site%2Findex&src=ref1#name
echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);

// /index.php?r=post%2Fedit&id=100     assume the alias "@postEdit" is defined as "post/edit"
echo Url::to(['@postEdit', 'id' => 100]);

// l'URL couramment requise
echo Url::to();

// /images/logo.gif
echo Url::to('@web/images/logo.gif');

// images/logo.gif
echo Url::to('images/logo.gif');

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', true);

// https://www.example.com/images/logo.gif
echo Url::to('@web/images/logo.gif', 'https');
```

Depuis la version 2.0.3, vous pouvez utiliser [[yii\helpers\Url::current()]] pour créer une URL basée sur la route couramment requise et sur les paramètres de la méthode GET. Vous pouvez modifier ou retirer quelques uns des paramètres GET et en ajouter d'autres en passant le paramètre `$params` à la méthode. Par exemple :

```php
// suppose que $_GET = ['id' => 123, 'src' => 'google'],et que la route courante est "post/view"

// /index.php?r=post%2Fview&id=123&src=google
echo Url::current();

// /index.php?r=post%2Fview&id=123
echo Url::current(['src' => null]);
// /index.php?r=post%2Fview&id=100&src=google
echo Url::current(['id' => 100]);
```


## Se souvenir d'URL <span id="remember-urls"></span>

Il y a des cas dans lesquels vous avez besoin de mémoriser une URL et ensuite de l'utiliser durant le traitement d'une des requêtes séquentielles. Cela peut être fait comme suit :

```php
// se souvenir de l'URL courante 
Url::remember();

// Se souvenir de l'URL spécifiée. Voir Url::to() pour le format des arguments.
Url::remember(['product/view', 'id' => 42]);

// Se souvenir de  l'URL spécifiée avec un nom
Url::remember(['product/view', 'id' => 42], 'product');
```

Dans la prochaine requête, vous pouvez récupérer l'URL mémorisée comme ceci :

```php
$url = Url::previous();
$productUrl = Url::previous('product');
```
                        
## Vérification des URL relatives <span id="checking-relative-urls"></span>

Pour savoir si une URL est relative, c.-à-d. n'a pas de partie « hôte », vous pouvez utiliser le code suivant : 
                             
```php
$isRelative = Url::isRelative('test/it');
```
