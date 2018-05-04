Routage et création d'URL 
=========================

Lorsqu'une application Yii commence à traiter une URL objet d'une requête, sa première étape consiste à analyser cette URL pour la résoudre en une [route](structure-controllers.md#routes). La route est ensuite utilisée pour instancier l'[action de contrôleur](structure-controllers.md) correspondante pour la prise en charge de la requête. Ce processus est appelé *routage*.
 
Le processus inverse du routage, qui consiste à créer une URL à partir d'une route et des paramètres associés de la requête,  est appelé *création d'URL*. Lorsque l'URL créée est ensuite requise, le processus de routage est capable de la résoudre en la route originale avec les paramètres de requête. 
  
L'élément central en charge du routage et de la création d'URL est le [[yii\web\UrlManager|gestionnaire d'URL]], qui est enregistré en tant que  [composant d'application](structure-application-components.md) sous le nom `urlManager`. Le [[yii\web\UrlManager|gestionnaire d'URL]] fournit la méthode [[yii\web\UrlManager::parseRequest()|parseRequest()]] pour analyser une requête entrante et la résoudre en une route et les paramètres de requête associés, et la méthode [[yii\web\UrlManager::createUrl()|createUrl()]] pour créer une URL en partant d'une route avec ses paramètres de requête associés. 
 
En configurant le composant  `urlManager` dans la configuration de l'application, vous pouvez laisser votre application reconnaître les formats d'URL arbitraires sans modifier le code existant de votre application. Par exemple, vous pouvez utiliser le code suivant pour créer une URL pour l'action `post/view` :

```php
use yii\helpers\Url;

// Url::to() appelle UrlManager::createUrl() pour créer une URL
$url = Url::to(['post/view', 'id' => 100]);
```

Selon la configuration de `urlManager`, l'URL créée peut ressenmbler à l'une des URL suivantes (ou autre formats). Et si l'URL est requise plus tard, elle sera toujours analysée pour revenir à la route originale et aux valeurs des paramètres de la requête.

```
/index.php?r=post%2Fview&id=100
/index.php/post/100
/posts/100
```


## Formats d'URL  <span id="url-formats"></span>

Le [[yii\web\UrlManager|gestionnaire d'URL]] prend en charge deux formats d'URL : le format d'URL par défaut et le format d'URL élégantes.

Le format d'URL par défaut utilise un paramètre de requête nommé `r` qui représente la route et les paramètres de requête normaux associés à la route. Par exemple, l'URL `/index.php?r=post/view&id=100` represente la route `post/view` et le paramètre de requête `id` dont la valeur est 100. Le format d'URL par défaut ne requiert aucune configuration du [[yii\web\UrlManager|gestionnaire d'URL] et fonctionne dans toutes les configurations de serveur Web. 

Le format d'URL élégantes utilise le chemin additionnel qui suit le nom du script d'entrée pour représenter la route et les paramètres de requête associés. Par exemple, le chemin additionnel dans l'URL `/index.php/post/100` est `/post/100` qui, avec une [[yii\web\UrlManager::rules|règle d'URL]] appropriée, peut représenter la route `post/view` et le paramètre des requête  `id` avec une valeur de 100 . Pour utiliser le format d'URL élégantes, vous devez définir un jeu de [[yii\web\UrlManager::rules|règles d'URL]] en cohérence avec les exigences réelles sur la présentation d'une URL. 
 
Vous pouvez passer d'un format d'URL à l'autre en inversant la propriété [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] du  [[yii\web\UrlManager|gestionnaire d'URL]] sans changer quoi que ce soit au code de votre application. 


## Routage <span id="routing"></span>

Le routage se fait en deux étapes. Dans la première étape, la requête entrante est analysée et résolue en une route et les paramètres de requête associés. Dans la seconde étape, l'[action de contrôleur](structure-controllers.md#actions) correspondant à la route analysée est créée pour prendre la requête en charge. 

Lors de l'utilisation du format d'URL par défaut, la résolution d'une requête en route est aussi simple que d'obtenir le paramètre nommé `r` de la méthode `GET`.

Lors de l'utilisation du format d'URL élégantes, le [[yii\web\UrlManager|gestionnaire d'URL] examine les [[yii\web\UrlManager::rules|règles d'URL]] enregistrées pour trouver une règle qui correspond et résoudre la requête en une route. Si une telle règle n'est pas trouvée, une exception  [[yii\web\NotFoundHttpException]] est levée. 

Une fois que la requête est résolue en une route, il est temps de créer l'action de contrôleur identifiée par la route. La route est éclatée en de multiples parties par des barres oblique de division. Par exemple, `site/index` est éclatée en  `site` et `index`. Chacune des parties est considérée comme un identifiant qui peut faire référence à un module, un contrôleur ou une action. En partant de la première partie dans la route, l'application entreprend les étapes suivantes pour créer un module (s'il en existe un), un contrôleur et une action. 

1. Définit l'application comme étant le module courant.
2. Vérifie si la [[yii\base\Module::controllerMap|table de mise en correspondance des contrôleurs]] du module courant contient l'identifiant courant. Si c'est le cas, un objet *controller* est créé en respectant la configuration du contrôleur trouvé dans la table de mise en correspondance, et on passe à l'étape 5 pour prendre en compte le reste de la route. 
3. Vérifie si l'identifiant fait référence à un module listé dans la propriété [[yii\base\Module::modules|modules]] du module courant. Si c'est le cas, un module est créé en respectant la configuration trouvée dans la liste des modules et on passe à l'étape 2 pour prendre en compte le reste de la route dans le contexte du nouveau module. 
4. Traite l'identifiant comme un [identifiant de contrôleur](structure-controllers.md#controller-ids), crée un objet *controller* et passe à l'étape suivante avec le reste de la route. 
5. Le contrôleur recherche l'identifiant courant dans sa [[yii\base\Controller::actions()|table de mise en correspondance des actions]]. s'il le trouve, il crée une action respectant la configuration trouvée dans la table de mise en correspondance. Autrement, le contrôleur essaye de créer une action en ligne dont le nom de méthode correspond à l' [identifiant d'action](structure-controllers.md#action-ids) courant.

Si une erreur se produit dans l'une des étapes décrites ci-dessus, une exception  [[yii\web\NotFoundHttpException]] est levée, indiquant l'échec du processus de routage. 


### Route par défaut <span id="default-route"></span>

Quand une requête est analysée et résolue en une route vide, la route dite *route par défaut* est utilisée à sa place. Par défaut, la route par défaut est `site/index`,  qui fait référence à l'action `index` du contrôleur `site`. Vous pouvez la personnaliser en configurant la propriété [[yii\web\Application::defaultRoute|defaultRoute]] de l'application dans la configuration de l'application comme indiqué ci-dessous :

```php
[
    // ...
    'defaultRoute' => 'main/index',
];
```


### La route `attrape-tout` <span id="catchall-route"></span>

Parfois, vous désirez mettre votre application Web en mode maintenance temporairement et afficher la même page d'information pour toutes les requêtes. Il y a plusieurs moyens de faire cela. L'une des manières les plus simples est de configurer la propriété [[yii\web\Application::catchAll]] dans la configuration de l'application comme indiqué ci-dessous :

```php
[
    // ...
    'catchAll' => ['site/offline'],
];
```

Avec la configuration ci-dessus, l'action `site/offline` est utilisée pour prendre toutes les requêtes entrantes en charge. 

La propriété  `catchAll` accepte un tableau dont le premier élément spécifie une route et le reste des éléments des couples clé-valeur pour les paramètres  [liés à l'action](structure-controllers.md#action-parameters).

> Info: le paneau de débogage de l'environnement de développement ne fonctionne pas lorsque cette propriété est activée.


## Création d'URL <span id="creating-urls"></span>

Yii fournit une méthode d'aide [[yii\helpers\Url::to()]] pour créer différentes sortes d'URL à partir de routes données et de leurs paramètres de requête associés. Par exemple : 

```php
use yii\helpers\Url;

// crée une URL d'une route: /index.php?r=post%2Findex
echo Url::to(['post/index']);

// crée une URL d'une route avec paramètres : /index.php?r=post%2Fview&id=100
echo Url::to(['post/view', 'id' => 100]);

// crée une  URL avec ancre : /index.php?r=post%2Fview&id=100#content
echo Url::to(['post/view', 'id' => 100, '#' => 'content']);

// crée une URL absolue : http://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], true);

// crée une URL absolue en utilisant le schéam https : https://www.example.com/index.php?r=post%2Findex
echo Url::to(['post/index'], 'https');
```

Notez que dans l'exemple ci-dessus, nous supposons que le format d'URL est le format par défaut. Si le format d'URL élégantes est activé, les URL créées sont différentes et respectent les [[yii\web\UrlManager::rules|règles d'URL]] en cours d'utilisation. 

La route passée à la méthode [[yii\helpers\Url::to()]] est sensible au contexte. Elle peut être soit *relative*, soit *absolue* et normalisée en respect des règles suivantes :

- Si la route est une chaîne vide, la [[yii\web\Controller::route|route]] couramment requise est utilisée ;
- Si la route ne contient aucune barre oblique de division, elle est considérée comme un identifiant d'action du contrôleur courant et est préfixée par la valeur de l'identifiant [[\yii\web\Controller::uniqueId|uniqueId]] du contrôleur courant ;
- Si la route n'a pas de barre oblique de division, elle est considérée comme une route relative au module courant et préfixée par la valeur de l'identifiant [[\yii\base\Module::uniqueId|uniqueId]] du module courant.

À partir de la version 2.0.2, vous pouvez spécifier une route en terme d'[alias](concept-aliases.md). Si c'est le cas, l'alias est d'abord converti en la route réelle qui est ensuite transformée en route absolue dans le respect des règles précédentes. 

Par exemple, en supposant que le module courant est `admin` et que le contrôleur courant est `post`,

```php
use yii\helpers\Url;

// route couramment requise : /index.php?r=admin%2Fpost%2Findex
echo Url::to(['']);

// une route relative avec un identifiant d'action seulement : /index.php?r=admin%2Fpost%2Findex
echo Url::to(['index']);

// une route relative : /index.php?r=admin%2Fpost%2Findex
echo Url::to(['post/index']);

// une route absoulue : /index.php?r=post%2Findex
echo Url::to(['/post/index']);

// /index.php?r=post%2Findex     suppose que l'alias "@posts" est défini comme  "/post/index"
echo Url::to(['@posts']);
```

La méthode [[yii\helpers\Url::to()]] est mise en œuvre en appelant les méthodes [[yii\web\UrlManager::createUrl()|createUrl()]] et  [[yii\web\UrlManager::createAbsoluteUrl()|createAbsoluteUrl()]] du [[yii\web\UrlManager|gestionnaire d'URL]]. Dans les quelques sous-sections suivantes, nous expliquons comment configurer le [[yii\web\UrlManager|gestionnaire d'URL]] pour personnaliser le format des URL créées. 

La méthode [[yii\helpers\Url::to()]] prend aussi en charge la création d'URL qui n'ont PAS de relation avec des routes particulières. Au lieu de passer un tableau comme premier paramètre, vous devez, dans ce cas,  passer une chaîne de caractères. Par exemple :
 
```php
use yii\helpers\Url;

// URL couramment requise : /index.php?r=admin%2Fpost%2Findex
echo Url::to();

// un alias d'URL: http://example.com
Yii::setAlias('@example', 'http://example.com/');
echo Url::to('@example');

// une URL absolue : http://example.com/images/logo.gif
echo Url::to('/images/logo.gif', true);
```

En plus de la méthode `to()`, la classe d'aide [[yii\helpers\Url]] fournit aussi plusieurs méthode pratiques de création d'URL. Par exemple :

```php
use yii\helpers\Url;

// URL de page d'accueil: /index.php?r=site%2Findex
echo Url::home();

// URL de base, utile si l'application est déployée dans un sous-dossier du dossier Web racine
echo Url::base();

// l'URL canonique de l'URL couramment requise 
// see https://en.wikipedia.org/wiki/Canonical_link_element
echo Url::canonical();

// mémories l'URL couramment requise et la retrouve dans les requêtes subséquentes
Url::remember();
echo Url::previous();
```


## Utilisation des URL élégantes <span id="using-pretty-urls"></span>

Pour utiliser les URL élégantes, configurez le composant `urlManager` dans la configuration de l'application comme indiqué ci-dessous :

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

La propriété [[yii\web\UrlManager::enablePrettyUrl|enablePrettyUrl]] est obligatoire car elle active/désactive le format d'URL élégantes. Le reste des propriétés est facultatif. Néanmoins, leur configuration montrée plus haut est couramment utilisée. 

* [[yii\web\UrlManager::showScriptName|showScriptName]]: cette propriété détermine si le script d'entrée doit être inclus dans l'URL créée. Par exemple, au lieu de créer une URL `/index.php/post/100`, en définissant cette propriété à `false`, l'URL `/post/100` est générée. 
* [[yii\web\UrlManager::enableStrictParsing|enableStrictParsing]]: cette propriété détermine si l'analyse stricte est activée . Si c'est le cas, l'URL entrante doit correspondre à au moins une des [[yii\web\UrlManager::rules|règles]] afin d'être traitée comme une requête valide, sinon une exception [[yii\web\NotFoundHttpException]] est levée. Si l'analyse stricte est désactivée, lorsqu'aucune  [[yii\web\UrlManager::rules|règle]] ne correspond à l'URL requise, la partie chemin de l'URL est considérée comme étant la route requise. 
* [[yii\web\UrlManager::rules|rules]]: cette propriété contient une liste de règles spécifiant comme analyser et créer des URL. C'est la propriété principale avec laquelle vous devez travailler afin de créer des URL dont le format satisfait les exigences particulières de votre application. 

> Note: afin de cacher le nom du script d'entrée dans l'URL créée, en plus de définir la propriété [[yii\web\UrlManager::showScriptName|showScriptName]] à `false`, vous pouvez aussi configurer votre serveur Web de manière à ce qu'il puisse identifier correctement quel script PHP doit être exécuté lorsqu'une URL requise n'en précise aucun explicitement. Si vous utilisez le serveur Apache, vous pouvez vous reporter à la configuration recommandée décrite dans la section [Installation](start-installation.md#recommended-apache-configuration).


### Règles d'URL  <span id="url-rules"></span>

Une règle d'URL est une instance de la classe [[yii\web\UrlRule]] ou de ses classes filles. Chaque règle d'URL consiste en un motif utilisé pour être mis en correspondance avec la partie chemin de l'URL, une route, et quelques paramètres de requête. Une règle d'URL peut être utilisée pour analyser une requête si son motif correspond à l'URL requise. Une règle d'URL peut être utilisée pour créer une URL si sa route et le nom de ses paramètres de requête correspondent à ceux qui sont fournis. 

Quand le format d'URL élégantes est activé, le [[yii\web\UrlManager|gestionnaire d'URL]] utilise les règles d'URL déclarées dans sa propriété 
[[yii\web\UrlManager::rules|rules]] pour analyser les requêtes entrantes et créer des URL. En particulier, pour analyser une requête entrante, le [[yii\web\UrlManager|gestionnaire d'URL]] examine les règles dans l'ordre de leur déclaration et cherche la *première* règle qui correspond à l'URL requise. La règle correspondante est ensuite utilisée pour analyser l'URL et la résoudre en une route et ses paramètres de requête associés. De façon similaire, pour créer une URL, le [[yii\web\UrlManager|gestionnaire d'URL]] cherche la première règle qui correspond à la route donnée et aux paramètres et l'utilise pour créer l'URL. 

Vous pouvez configurer la propriété [[yii\web\UrlManager::rules]] sous forme de tableau dont les clés sont les motifs et les valeurs, les routes correspondantes. Chacune des paires motif-route construit une règle d'URL. Par exemple,  la configuration des [[yii\web\UrlManager::rules|règles]] suivantes déclare deux règles d'URL. La première correspond à l'URL `posts` et la met en correspondance avec la route   `post/index`. La seconde correspond à une URL qui correspond à l'expression régulière  `post/(\d+)` et la met en correspondance avec la route `post/view` et le paramètre nommé `id`.

```php
[
    'posts' => 'post/index', 
    'post/<id:\d+>' => 'post/view',
]
```

> Info: le motif dans une règle est utilisé pour correspondre à la partie chemin d'une URL.  Par exemple, la partie chemin de `/index.php/post/100?source=ad` est `post/100` (les barres obliques de division de début et de fin sont ignorées) et correspond au motif `post/(\d+)`.

En plus de déclarer des règles d'URL sous forme de paires motif-route, vous pouvez aussi les déclarer  sous forme de tableaux de configuration. Chacun des tableaux de configuration est utilisé pour configurer un simple objet règle d'URL. C'est souvent nécessaire lorsque vous voulez configurer d'autres propriétés d'une règle d'URL. Par exemple :

```php
[
    // ...autres règels d'url ...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

Par défaut, si vous ne spécifiez pas l'option `class` pour une configuration de règle, elle prend la valeur par défaut [[yii\web\UrlRule]].


### Paramètres nommés <span id="named-parameters"></span>

Une règle d'URL peut être associée à quelques paramètres de requête nommés qui sont spécifiés dans le motif et respectent le format `<ParamName:RegExp>`, où  `ParamName` spécifie le nom du paramètre et  `RegExp` est une expression régulière facultative utilisée pour établir la correspondance avec une valeur de paramètre. Si `RegExp` n'est pas spécifié, cela signifie que la valeur du paramètre doit être une chaîne de caractères sans aucune barre oblique de division. 

> Note: vous pouvez seulement spécifier des expressions régulières pour les paramètres. La partie restante du motif est considérée être du texte simple.

Lorsqu'une règle est utilisée pour analyser une URL, elle remplit les paramètres associés avec les valeurs des parties de l'URL qui leur correspondent, et ces paramètres sont rendus disponibles dans `$_GET` et plus tard dans le composant d'application `request`. Lorsque la règle est utilisée pour créer une URL, elle prend les valeurs des paramètres fournis et les insère à l'endroit où ces paramètres sont déclarés.

Prenons quelques exemples pour illustrer comment les paramètres nommés fonctionnent. Supposons que nous ayons déclaré les règles d'URL suivantes :

```php
[
    'posts/<year:\d{4}>/<category>' => 'post/index',
    'posts' => 'post/index',
    'post/<id:\d+>' => 'post/view',
]
```

Lorsque les règles sont utilisées pour analyser des URL :

- `/index.php/posts` est analysée et résolue en la route `post/index` en utilisant la deuxième règle ;
- `/index.php/posts/2014/php` est analysée et résolue en la route `post/index`, le paramètre  `year` dont la valeur est  2014 et le paramètre `category` dont la valeur est  `php` en utilisant la première règle ;
- `/index.php/post/100` est analysée et résolue en la route `post/view` et le paramètre `id` dont la valeur est 100 en utilisant la troisième règle ;
- `/index.php/posts/php` provoque la levée d'une exception [[yii\web\NotFoundHttpException]] quand la propriété [[yii\web\UrlManager::enableStrictParsing]]
  est définie à `true`, parce qu'elle ne correspond à aucun des motifs. Si  [[yii\web\UrlManager::enableStrictParsing]] est définie à  `false` (la valeur par défaut), la partie chemin `posts/php` est retournée en tant que route.
 
Et quand les règles sont utilisées pour créer des URL : 

- `Url::to(['post/index'])` crée `/index.php/posts` en utilisant la deuxième règle ;
- `Url::to(['post/index', 'year' => 2014, 'category' => 'php'])` crée `/index.php/posts/2014/php` en utilisant la première règle ;
- `Url::to(['post/view', 'id' => 100])` crée `/index.php/post/100` en utilisant la troisième règle ;
- `Url::to(['post/view', 'id' => 100, 'source' => 'ad'])` crée `/index.php/post/100?source=ad` en utilisant la troisième règle.
  Comme le paramètre `source` n'est pas spécifié dans la règle, il est ajouté en tant que paramètre de requête à l'URL créée.
- `Url::to(['post/index', 'category' => 'php'])` crée `/index.php/post/index?category=php` en utilisant aucune des règles.
  Notez que, aucune des règles n'étant utilisée, l'URL est créée en ajoutant simplement la route en tant que partie chemin et tous les paramètres en tant que partie de la chaîne de requête.

### Paramétrage des routes <span id="parameterizing-routes"></span>

Vous pouvez inclure les noms des paramètres dans la route d'une règle d'URL. Cela permet à une règle d'URL d'être utilisée pour correspondre à de multiples routes. Par exemple, les règles suivantes incluent les paramètres `controller` et `action` dans les routes.

```php
[
    '<controller:(post|comment)>/<id:\d+>/<action:(create|update|delete)>' => '<controller>/<action>',
    '<controller:(post|comment)>/<id:\d+>' => '<controller>/view',
    '<controller:(post|comment)>s' => '<controller>/index',
]
```

Pour analyser l'URL `/index.php/comment/100/create`, la première règle s'applique et définit le paramètre `controller`  comme étant `comment` et le paramètre  `action` comme étant `create`. La route `<controller>/<action>` est par conséquent résolue comme `comment/create`.
 
De façon similaire, pour créer une URL à partir de la route `comment/index`, la troisième règle s'applique, ce qui donne l'URL `/index.php/comments`.

> Info: en paramétrant les routes, il est possible de réduire grandement le nombre de règles d'URL, ce qui peut accroître significativement la performance du  [[yii\web\UrlManager|gestionnaire d'URL]]. 
  
Par défaut, tous les paramètres déclarés dans une règle sont requis. Si une URL requise ne contient pas un paramètre particulier, ou si une URL est créée sans un paramètre particulier, la règle ne s'applique pas. Pour rendre certains paramètres facultatifs, vous pouvez configurer la propriété [[yii\web\UrlRule::defaults|defaults]] de la règle. Les paramètres listés dans cette propriété sont facultatifs et prennent les valeurs spécifiées lorsqu'elles ne sont pas fournies.

Dans la déclaration suivante d'une règle, les paramètres `page` et `tag` sont tous les deux facultatifs et prennent la valeur 1 et vide, respectivement quand ils ne sont pas fournis. 

```php
[
    // ...autres règles...
    [
        'pattern' => 'posts/<page:\d+>/<tag>',
        'route' => 'post/index',
        'defaults' => ['page' => 1, 'tag' => ''],
    ],
]
```

La règle ci-dessus peut être utilisée pour analyser ou créer l'une quelconque des URL suivantes : 

* `/index.php/posts`: `page` est 1, `tag` est ''.
* `/index.php/posts/2`: `page` est 2, `tag` est ''.
* `/index.php/posts/2/news`: `page` est 2, `tag` est `'news'`.
* `/index.php/posts/news`: `page` est 1, `tag` est `'news'`.

Sans les paramètres facultatifs, vous devriez créer quatre règles pour arriver au même résultat.


### Règles avec des noms de serveur <span id="rules-with-server-names"></span>

Il est possible d'inclure des noms de serveur Web dans le motif d'une règle d'URL. Cela est principalement utilisé lorsque votre application doit se comporter différemment selon le nom du serveur Web. Par exemple, les règles suivantes analysent et résolvent l'URL `http://admin.example.com/login` en la route `admin/user/login` et `http://www.example.com/login` en la route `site/login`.

```php
[
    'http://admin.example.com/login' => 'admin/user/login',
    'http://www.example.com/login' => 'site/login',
]
```

Vous pouvez aussi inclure des paramètres  dans les noms de serveurs pour en extraire de l'information dynamique. Par exemple, la règle suivante analyse et résout l'URL `http://en.example.com/posts` en la route `post/index` et le paramètre  `language=en`.

```php
[
    'http://<language:\w+>.example.com/posts' => 'post/index',
]
```

> Note: les règles avec des noms de serveur ne doivent pas comprendre le sous-dossier du script d'entrée dans leur motif. Par exemple, si l'application est sous  `http://www.example.com/sandbox/blog`, alors vous devez utiliser le motif `http://www.example.com/posts` au lieu de  `http://www.example.com/sandbox/blog/posts`. Cela permet à votre application d'être déployée sous n'importe quel dossier sans avoir à changer son code. 

### Suffixes d'URL  <span id="url-suffixes"></span>

Vous désirez peut-être ajouter des suffixes aux URL pour des raisons variées. Par exemple, vous pouvez ajouter `.html` aux URL de manière à ce qu'elles ressemblent à des URL de pages HTML statiques. Vous pouvez aussi y ajouter `.json` pour indiquer le type de contenu attendu  pour la réponse. Vous pouvez faire cela en configurant la propriété [[yii\web\UrlManager::suffix]] dans la configuration de l'application comme ceci :

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
            ],
        ],
    ],
]
```

La configuration ci-dessus permet au [[yii\web\UrlManager|gestionnaire d'URL]] de reconnaître les URL requises et aussi de créer des URL avec le suffixe `.html`.

> Tip: vous pouvez définir `/` en tant que suffixe des URL de manière à ce que tous les URL se terminent par la barre oblique de division. 

> Note: lorsque vous configurez un suffixe d'URL, si une URL requise ne contient pas ce suffixe, elle est considérée comme une URL non reconnue. Cela est une pratique recommandée pour l'optimisation des moteurs de recherche (SE0 – Search Engine Optimization). 
  
Parfois vous désirez utiliser des suffixes différents pour différentes URL. Cela peut être fait en configurant la propriété [[yii\web\UrlRule::suffix|suffix]] des règles d'URL individuelles. Lorsqu'une URL a cette propriété définie, elle écrase la valeur définie au niveau du [[yii\web\UrlManager|gestionnaire d'URL]]. Par exemple, la configuration suivante contient une règle d'URL personnalisée  qui utilise  `.json` en tant que suffixe à la place du suffixe défini globalement `.html`.

```php
[
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'suffix' => '.html',
            'rules' => [
                // ...
                [
                    'pattern' => 'posts',
                    'route' => 'post/index',
                    'suffix' => '.json',
                ],
            ],
        ],
    ],
]
```


### Méthodes HTTP  <span id="http-methods"></span>

En mettant en œuvre des API pleinement REST, il est couramment nécessaire que la même URL puisse être résolue en différentes routes selon la méthode HTTP utilisée par la requête. Cela peut être fait facilement en préfixant les motifs des règles avec les méthodes HTTP prises en charge. Si une règle prend en charge plusieurs méthodes HTTP, il faut séparer les noms de méthode par une virgule. Par exemple, les règles suivantes ont le même motif `post/<id:\d+>` mais des méthodes HTTP différentes. Un requête de `PUT post/100` est résolue en la route `post/create`, tandis que la requête de `GET post/100` en la route `post/view`.

```php
[
    'PUT,POST post/<id:\d+>' => 'post/create',
    'DELETE post/<id:\d+>' => 'post/delete',
    'post/<id:\d+>' => 'post/view',
]
```

> Note: si une règle d'URL contient des méthodes  HTTP dans son motif, la règle n'est utilisée qu'à des fins d'analyse résolution. Elle est ignorée quand le [[yii\web\UrlManager|gestionnaire d'URL]]  est sollicité pour créer une URL.

> Tip: pour simplifier le routage des API pleinement REST, Yii fournit la classe spéciale de règle d'URL [[yii\rest\UrlRule]] qui est très efficace et prend en charge quelques fonctionnalités originales comme la pluralisation automatique des identifiants de contrôleur. Pour plus de détails, reportez-vous à la section [Routage](rest-routing.md) sur le développement d'API pleinement REST. 

### Personnalisation des règles <span id="customizing-rules"></span>

Dans l'exemple précédent, les règles d'URL sont essentiellement déclarées en terme de paires motif-route. Cela est un format raccourci communément utilisé. Dans certains scénarios, vous désirez personnaliser une règle d'URL en configurant ses autres propriétés, telles que [[yii\web\UrlRule::suffix]]. Cela peut être fait en utilisant un tableau complet de configuration pour spécifier une règle. L'exemple suivant est tiré de la sous-section [suffixes d'URL](#url-suffixes) :

```php
[
    // ...other url rules...
    
    [
        'pattern' => 'posts',
        'route' => 'post/index',
        'suffix' => '.json',
    ],
]
```

> Info: Par défaut, si vous ne spécifiez pas l'option  `class` dans la configuration d'une règle, elle prend la valeur par défaut  [[yii\web\UrlRule]].
  

### Ajout dynamique de règles <span id="adding-rules"></span>

Des règles d'URL peuvent être ajoutées dynamiquement au [[yii\web\UrlManager|gestionnaire d'URL]]. Cela est souvent nécessaire pour les [modules](structure-modules.md) distribuables qui veulent gérer leurs propres règles d'URL. Pour que les règles ajoutées dynamiquement prennent effet dans de processus de routage, vous devez les ajouter dans l'étape d'[amorçage](runtime-bootstrapping.md). Pour les modules, cela signifie qu'ils doivent implémenter l'interface  [[yii\base\BootstrapInterface]] et ajouter les règles dans leur méthode [[yii\base\BootstrapInterface::bootstrap()|bootstrap()]] comme l'exemple suivant le montre :

```php
public function bootstrap($app)
{
    $app->getUrlManager()->addRules([
        // rule declarations here
    ], false);
}
```

Notez que vous devez également lister ces modules dans la propriété [[yii\web\Application::bootstrap]] afin qu'ils puissent participer au processus d'[amorçage](runtime-bootstrapping.md).


### Création des classes règles <span id="creating-rules"></span>

En dépit du fait que la classe par défaut [[yii\web\UrlRule]] est suffisamment flexible pour la majorité des projets, il y a des situations dans lesquelles vous devez créer votre propres classes de règle. Par exemple, dans un site Web de vendeur de voitures, vous désirerez peut-être prendre en charge des formats d'URL du type `/Manufacturer/Model`, où `Manufacturer` et `Model` doivent correspondre à quelques données stockées dans une base de données. La classe de règle par défaut ne fonctionne pas dans ce cas car elle s'appuie sur des motifs déclarés de manière statique. 

Vous pouvez créer les classes de règle d'URL suivantes pour résoudre ce problème : 

```php
namespace app\components;

use yii\web\UrlRuleInterface;
use yii\base\BaseObject;

class CarUrlRule extends BaseObject implements UrlRuleInterface
{

    public function createUrl($manager, $route, $params)
    {
        if ($route === 'car/index') {
            if (isset($params['manufacturer'], $params['model'])) {
                return $params['manufacturer'] . '/' . $params['model'];
            } elseif (isset($params['manufacturer'])) {
                return $params['manufacturer'];
            }
        }
        return false;  // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        $pathInfo = $request->getPathInfo();
        if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches)) {
            // vérifie  $matches[1] et $matches[3] pour voir si
            // elles correspondent à un  manufacturer et à un model dans la base de données
            // si oui, définit $params['manufacturer'] et/ou $params['model']
            // et retourne  ['car/index', $params]
        }
        return false;  // cette règle ne s'applique pas
    }
}
```

Et utilisez la nouvelle classe de règle dans la configuration de [[yii\web\UrlManager::rules]] :

```php
[
    // ...other rules...
    
    [
        'class' => 'app\components\CarUrlRule', 
        // ...configure d'autres propriétés...
    ],
]
```


## Considérations de performance  <span id="performance-consideration"></span>

Lors du développement d'une application Web, il est important d'optimiser les règles d'URL  afin que l'analyse des requêtes et la création d'URL prennent moins de temps.

En utilisant les routes paramétrées, vous pouvez réduire le nombre de règles d'URL, ce qui accroît significativement la performance. 

Lors de l'analyse d'URL ou de la création d'URL, le [[yii\web\UrlManager|gestionnaire d'URL]] examine les règles d'URL dans l'ordre de leur déclaration. En conséquence, vous devez envisager d'ajuster cet ordre afin que les règles les plus spécifiques et/ou utilisées couramment soient placée avant les règles les moins utilisées. 

Si quelques règles d'URL partagent le même préfixe dans leur motif ou dans leur route, vous pouvez envisager d'utiliser [[yii\web\GroupUrlRule]] pour qu'elles puissent être examinées plus efficacement par le [[yii\web\UrlManager|gestionnaire d'URL]] en tant que groupe. Cela est souvent le cas quand votre application est composée de modules, chacun ayant son propre jeu de règles d'URL avec l'identifiant de module comme préfixe commun. 
