Contrôleurs
===========

Les contrôleurs font partie du modèle d'architecture [MVC](https://fr.wikipedia.org/wiki/Mod%C3%A8le-vue-contr%C3%B4leur) (Modèle Vue Contrôleur). Ce sont des objets dont la classe étend [[yii\base\Controller]]. Ils sont chargés de traiter les requêtes et de générer les réponses. En particulier, après que l'objet [application](structure-applications.md) leur a passé le contrôle, ils analysent les données de la requête entrante, les transmettent aux [modèles](structure-models.md), injectent le résultat des modèles dans les [vues](structure-views.md) et, pour finir, génèrent les réponses sortantes. 


## Actions <span id="actions"></span>

Les contrôleurs sont constitués d'*actions* qui sont les unités les plus élémentaires dont l'utilisateur final peut demander l'exécution. Un contrôleur comprend une ou plusieurs actions. 

L'exemple qui suit présente un contrôleur `post` avec deux actions : `view` et `create`:

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new Post;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
}
```

Dans l'action `view` (définie par la méthode `actionView()`), le code commence par charger le [modèle](structure-models.md) en fonction de l'identifiant (ID) du modèle requis. Si le chargement du modèle réussit, l'action l'affiche en utilisant une [vue](structure-views.md) nommée `view`. Autrement, elle lève une exception. 

Dans l'action  `create` (définie par le méthode `actionCreate()`), le code est similaire.  Elle commence par essayer de peupler une nouvelle instance du [modèle](structure-models.md) avec les données de la requête et sauvegarde le modèle. Si les deux opérations réussissent, elle redirige le navigateur vers l'action  `view` en lui passant l'identifiant (ID) du nouveau modèle. Autrement, elle affiche la vue `create` dans laquelle l'utilisateur peut saisir les entrées requises.


## Routes <span id="routes"></span>

L'utilisateur final demande l'exécution des actions via ce qu'on appelle des *routes*. Une route est une chaîne de caractères constituée des parties suivantes :

* un identifiant (ID) de module : cette partie n'est présente que si le contrôleur appartient à un [module](structure-modules.md) qui n'est pas en soi une application ;
* un [identifiant de contrôleur](#controller-ids) : une chaîne de caractères qui distingue le contrôleur des autres contrôleurs de la même application — ou du même module si le contrôleur appartient à un module ;
* un [identifiant d'action](#action-ids) : une chaîne de caractères qui distingue cette action des autres actions du même contrôleur.

Les routes se présentent dans le format suivant :

```
identifiant_de_contrôleur/identifiant_d_action
```

ou dans le format suivant si le contrôleur appartient à un module :

```php
identifiant_de_module/identifiant_de_contrôleur/identifiant_d_action
```

Ainsi si un utilisateur requiert l'URL `https://hostname/index.php?r=site/index`, l'action `index` dans le contrôleur `site` sera exécutée. Pour plus de détails sur la façon dont les routes sont résolues, reportez-vous à la section [Routage et génération d'URL](runtime-routing.md).


## Création des contrôleurs <span id="creating-controllers"></span>

Dans les [[yii\web\Application|applications Web]], les contrôleur doivent étendre la classe [[yii\web\Controller]] ou ses classes filles. De façon similaire, dans les [[yii\console\Application|applications de console]], les contrôleurs doivent étendre la classe [[yii\console\Controller]] ou ses classes filles. Le code qui suit définit un contrôleur nommé `site` :

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
}
```


### Identifiant des contrôleurs <span id="controller-ids"></span>

Généralement, un contrôleur est conçu pour gérer les requêtes concernant un type particulier de ressource. Pour cette raison, l'identifiant d'un contrôleur est souvent un nom faisant référence au type de ressources que ce contrôleur gère. 
Par exemple, vous pouvez utiliser `article` comme identifiant d'un contrôleur qui gère des données d'articles. 

Par défaut, l'identifiant d'un contrôleur ne peut contenir que les caractères suivants : lettres de l'alphabet anglais en bas de casse, chiffres, tiret  bas, trait d'union et barre oblique de division. Par exemple, `article` et `post-comment` sont tous deux des identifiants de contrôleur valides, tandis que `article?`, `PostComment` et `admin\post` ne le sont pas.
Un identifiant de contrôleur peut aussi contenir un préfixe de sous-dossier. Par exemple `admin/article` représente un contrôleur `article` dans le dossier `admin` dans l'[[yii\base\Application::controllerNamespace|espace de noms du contrôleur]].
Les caractères valides pour le préfixe des sous-dossiers incluent : les lettres de l'alphabet anglais dans les deux casses, les chiffres, le tiret bas et la barre oblique de division, parmi lesquels les barres obliques de division sont utilisées comme séparateurs pour les sous-dossiers à plusieurs niveaux (p. ex. `panels/admin`).

### Nommage des classes de contrôleur <span id="controller-class-naming"></span>

Les noms de classe de contrôleur peut être dérivés de l'identifiant du contrôleur selon la procédure suivante :

1. Mettre la première lettre de chacun des mots séparés par des trait d'union en capitale. Notez que si l'identifiant du contrôleur contient certaines barres obliques, cette règle ne s'applique qu'à la partie après la dernière barre oblique dans l'identifiant.
2. Retirer les traits d'union et remplacer toute barre oblique de division par une barre oblique inversée. 
3. Ajouter le suffixe  `Controller`.
4. Préfixer avec l'[[yii\base\Application::controllerNamespace|espace de noms du contrôleur]].

Ci-après sont présentés quelques exemples en supposant que l'[[yii\base\Application::controllerNamespace|espace de noms du contrôleur]] prend la valeur par défaut, soit `app\controllers`:

* `article` donne `app\controllers\ArticleController`;
* `post-comment` donne `app\controllers\PostCommentController`;
* `admin/post-comment` donne `app\controllers\admin\PostCommentController`;
* `adminPanels/post-comment` donne `app\controllers\adminPanels\PostCommentController`.

Les classes de contrôleur doivent être [auto-chargeables](concept-autoloading.md). Pour cette raison, dans les exemples qui précèdent, la classe de contrôleur  `article` doit être sauvegardée dans le fichier dont l'[alias](concept-aliases.md) est `@app/controllers/ArticleController.php`; tandis que la classe de contrôleur `admin/post-comment` doit se trouver dans `@app/controllers/admin/PostCommentController.php`.

> Info: dans le dernier exemple,  `admin/post-comment` montre comment placer un contrôleur dans un sous-dossier de l'[[yii\base\Application::controllerNamespace|espace de noms du contrôleur]]. Cela est utile lorsque vous voulez organiser vos contrôleurs en plusieurs catégories et que vous ne voulez pas utiliser de [modules](structure-modules.md).


### Table de mise en correspondance des contrôleurs <span id="controller-map"></span>

Vous pouvez configurer [[yii\base\Application::controllerMap|controller map (table de mise en correspondance des contrôleurs)]] pour outrepasser les contraintes concernant les identifiants de contrôleur et les noms de classe décrites plus haut. Cela est principalement utile lorsque vous utilisez des contrôleurs de tierces parties et que vous n'avez aucun contrôle sur le nommage de leur classe. 
Vous pouvez configurer [[yii\base\Application::controllerMap|controller map]] dans la [configuration de l'application](structure-applications.md#application-configurations). Par exemple :

```php
[
    'controllerMap' => [
        // declares "account" controller using a class name
        'account' => 'app\controllers\UserController',

        // declares "article" controller using a configuration array
        'article' => [
            'class' => 'app\controllers\PostController',
            'enableCsrfValidation' => false,
        ],
    ],
]
```


### Contrôleur par défaut <span id="default-controller"></span>

Chaque application possède un contrôleur par défaut spécifié via la propriété [[yii\base\Application::defaultRoute]]. Lorsqu'une requête ne précise aucune [route](#routes), c'est la route spécifiée par cette propriété qui est utilisée. Pour les [[yii\web\Application|applications Web]], sa valeur est `'site'`, tandis que pour les [[yii\console\Application|applications de console]], c'est `help`. Par conséquent, si une URL est de la forme `https://hostname/index.php`, c'est le contrôleur `site` qui prend la requête en charge.

Vous pouvez changer de contrôleur par défaut en utilisant la  [configuration d'application](structure-applications.md#application-configurations) suivante :

```php
[
    'defaultRoute' => 'main',
]
```


## Création d'actions <span id="creating-actions"></span>

Créer des actions est aussi simple que de définir ce qu'on appelle des *méthodes d'action* dans une classe de contrôleur. Une méthode d'action est une méthode *publique* dont le nom commence par le mot `action`. La valeur retournée par une méthode d'action représente les données de la réponse à envoyer à l'utilisateur final. Le code qui suit définit deux actions, `index` et `hello-world`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionHelloWorld()
    {
        return 'Hello World';
    }
}
```


### Identifiants d'action <span id="action-ids"></span>

Une action est souvent conçue pour effectuer une manipulation particulière d'une ressource. Pour cette raison, les identifiants d'action sont habituellement des verbes comme `view` (voir), `update` (mettre à jour), etc.

Par défaut, les identifiants d'action ne doivent contenir rien d'autre que les caractères suivants : les lettres de l'alphabet anglais en bas de casse, les chiffres, le tiret bas et le trait d'union. Vous pouvez utiliser le trait d'union pour séparer les mots. Par exemple : 
`view`, `update2`, et `comment-post` sont des identifiants d'action valides, tandis que `view?` et `Update` ne le sont pas.

Vous pouvez créer des actions sous deux formes : les actions en ligne (*inline*) et les actions autonomes (*standalone*). Une action en ligne est définie en tant que méthode dans un contrôleur, alors qu'une action autonome est une classe qui étend la classe [[yii\base\Action]] ou une des ses classes filles. La définition d'une action en ligne requiert moins d'efforts et est souvent préférée lorsqu'il n'y a pas d'intention de réutiliser cette action. Par contre, les actions autonomes sont essentiellement créées pour être utilisées dans différents contrôleurs ou pour être redistribuées dans des [extensions](structure-extensions.md).


### Actions en ligne <span id="inline-actions"></span>

Les actions en ligne sont les actions qui sont définies en terme de méthodes d'action comme nous l'avons décrit plus haut.

Les noms des méthodes d'action sont dérivés des identifiants d'action selon la procédure suivante :

1. Mettre la première lettre de chaque mot de l'identifiant en capitale.
2. Supprimer les traits d'union.
3. Préfixer le tout par le mot `action`.

Par exemple, `index` donne `actionIndex`, et `hello-world` donne `actionHelloWorld`.

> Note: les noms des méthodes d'action sont *sensibles à la casse*. Si vous avez une méthode nommée `ActionIndex`, elle ne sera pas considérée comme étant une méthode d'action et, par conséquent, la requête de l'action `index` aboutira à une exception. Notez également que les méthodes d'action doivent être publiques. Une méthode privée ou protégée ne définit PAS une action en ligne. 


Les actions en ligne sont les actions les plus communément définies parce qu'elle ne requièrent que peu d'efforts pour leur création. Néanmoins, si vous envisagez de réutiliser la même action en différents endroits, ou si vous voulez redistribuer cette action, vous devriez envisager de la définir en tant qu'*action autonome*.


### Actions autonomes <span id="standalone-actions"></span>

Les actions autonomes sont définies comme des classes d'action qui étendent la classe [[yii\base\Action]] ou une de ses classes filles.
Par exemple, dans les versions de Yii, il y a [[yii\web\ViewAction]] et [[yii\web\ErrorAction]], qui sont toutes les deux des actions autonomes.

Pour utiliser une action autonome, vous devez la déclarer dans la *table de mise en correspondance des actions* en redéfinissant les méthodes de la classe [[yii\base\Controller::actions()]] dans la classe de votre contrôleur de la manière suivante : 

```php
public function actions()
{
    return [
        // déclare une action "error" en utilisant un nom de classe
        'error' => 'yii\web\ErrorAction',

        // déclare une action  "view" action en utilisant un tableau de configuration
        'view' => [
            'class' => 'yii\web\ViewAction',
            'viewPrefix' => '',
        ],
    ];
}
```

Comme vous pouvez l'observer, les méthodes `actions()` doivent retourner un tableau dont les clés sont les identifiants d'action et les valeurs le nom de la classe d'action correspondant ou  des tableaux de [configuration](concept-configurations.md). Contrairement aux actions en ligne, les identifiants d'action autonomes peuvent comprendre n'importe quels caractères du moment qu'ils sont déclarés dans la méthode `actions()`.

Pour créer une classe d'action autonome, vous devez étendre la classe [[yii\base\Action]] ou une de ses classes filles, et implémenter une méthode publique nommée `run()`. Le rôle de la méthode `run()` est similaire à celui d'une méthode d'action. Par exemple :

```php
<?php
namespace app\components;

use yii\base\Action;

class HelloWorldAction extends Action
{
    public function run()
    {
        return "Hello World";
    }
}
```


### Valeur de retour d'une action <span id="action-results"></span>

Le valeur de retour d'une méthode d'action, ou celle de la méthode `run()` d'une action autonome, représente le résultat de l'action correspondante.

La valeur de retour peut être un objet [response](runtime-responses.md) qui sera transmis à l'utilisateur final en tant que réponse.

* Pour les [[yii\web\Application|applications Web]], la valeur de retour peut également être des données arbitraires qui seront assignées à l'objet [[yii\web\Response::data]] et converties ensuite en une chaîne de caractères représentant le corps de la réponse. 
* Pour les [[yii\console\Application|applications de console]], la valeur de retour peut aussi être un entier représentant l'[[yii\console\Response::exitStatus|état de sortie]] de l'exécution de la commande.

Dans les exemples ci-dessus, les valeurs de retour des actions sont toutes des chaînes de caractères qui seront traitées comme le corps de la réponse envoyée à l'utilisateur final. Les exemples qui suivent montrent comment une action peut rediriger le navigateur vers une nouvelle URL en retournant un objet *response* (parce que la méthode [[yii\web\Controller::redirect()|redirect()]] retourne un objet *response*) :

```php
public function actionForward()
{
    // redirect the user browser to https://example.com
    return $this->redirect('https://example.com');
}
```

### Paramètres d'action <span id="action-parameters"></span>

Les méthodes d'action pour les actions en ligne et la méthode `run()` d'une action autonome acceptent des paramètres appelés *paramètres d'action*. Leurs valeurs sont tirées des requêtes. Pour les [[yii\web\Application|applications Web]], la valeur de chacun des paramètres d'action est obtenue de la méthode `$_GET` en utilisant le nom du paramètre en tant que clé. Pour les [[yii\console\Application|applications de console]], les valeurs des  paramètres correspondent aux argument de la commande. 
Dans d'exemple qui suit, l'action `view` (une action en ligne) déclare deux paramètres : `$id` et `$version`.

```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public function actionView($id, $version = null)
    {
        // ...
    }
}
```

En fonction de la requête, les paramètres de l'action seront établis comme suit :

* `https://hostname/index.php?r=post/view&id=123`: le paramètre `$id` reçoit la valeur `'123'`,  tandis que le paramètre `$version` reste `null` (sa valeur par défaut) car la requête ne contient aucun paramètre `version`.
* `https://hostname/index.php?r=post/view&id=123&version=2`: les paramètres `$id` et `$version` reçoivent les valeurs `'123'` et `'2'`, respectivement.
* `https://hostname/index.php?r=post/view`: une exception [[yii\web\BadRequestHttpException]] est levée car le paramètre obligatoire `$id` n'est pas fourni par la requête.
* `https://hostname/index.php?r=post/view&id[]=123`: une exception [[yii\web\BadRequestHttpException]] est levée car le paramètre `$id` reçoit, de manière inattendue,  un tableau (`['123']`).

Si vous voulez que votre paramètre d'action accepte un tableau, il faut, dans la définition de la méthode, faire allusion à son type, avec `array`, comme ceci :

```php
public function actionView(array $id, $version = null)
{
    // ...
}
```

Désormais, si la requête est `https://hostname/index.php?r=post/view&id[]=123`, le paramètre `$id` accepte la valeur `['123']`. Si la requête est  `https://hostname/index.php?r=post/view&id=123`, le paramètre `$id` accepte également la valeur transmise par la requête parce que les valeurs scalaires sont automatiquement convertie en tableau (*array*).

Les exemples qui précèdent montrent essentiellement comment les paramètres d'action fonctionnent dans les applications Web. Pour les applications de console, reportez-vous à la section  [Commandes de console](tutorial-console.md) pour plus de détails.


### Action par défaut <span id="default-action"></span>

Chaque contrôleur dispose d'une action par défaut spécifiée par la propriété [[yii\base\Controller::defaultAction]].
Lorsqu'une [route](#routes) ne contient que l'identifiant du contrôleur, cela implique que l'action par défaut de ce contrôleur est requise. 

Par défaut, l'action par défaut est définie comme étant `index`. Si vous désirez changer cette valeur par défaut, contentez-vous de redéfinir cette propriété dans la classe du contrôleur, comme indiqué ci-après :

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $defaultAction = 'home';

    public function actionHome()
    {
        return $this->render('home');
    }
}
```


## Cycle de vie d'un contrôleur <span id="controller-lifecycle"></span>

Lors du traitement d'une requête, une [application](structure-applications.md) crée un contrôleur en se basant sur la [route](#routes) requise. Le contrôleur entame alors le cycle de vie suivant pour satisfaire la requête :

1. La méthode [[yii\base\Controller::init()]] est appelée après que le contrôleur est créé et configuré. 
2. Le contrôleur crée un objet *action* en se basant sur l'identifiant d'action de la requête : 
   * Si l'identifiant de l'action n'est pas spécifié, l'[[yii\base\Controller::defaultAction|identifiant de l'action par défaut]] est utilisé.
   * Si l'identifiant de l'action est trouvé dans la [[yii\base\Controller::actions()|table de mise en correspondance des actions]], une action autonome est créée.
   * Si l'identifiant de l'action est trouvé et qu'il correspond à une méthode d'action, une action en ligne est créée.
   * Dans les autres cas, une exception [[yii\base\InvalidRouteException]] est levée.
3. Le contrôleur appelle consécutivement la méthode `beforeAction()` de l'application, celle du module (si module si le contrôleur appartient à un module) et celle du contrôleur. 
   * Si l'un des appels retourne `false`, les appels aux  méthodes `beforeAction()` qui devraient suivre ne sont pas effectués et l'exécution de l'action est annulée.
   * Par défaut, chacun des appels à la méthode `beforeAction()` déclenche un événement  `beforeAction` auquel vous pouvez attacher un gestionnaire d'événement. 
4. Le contrôleur exécute l'action.
   * Les paramètres de l'action sont analysés et définis à partir des données transmises par la requête.
5. Le contrôleur appelle successivement la méthode  `afterAction()` du contrôleur, du module (si le contrôleur appartient à un module) et de l'application.
   * Par défaut, chacun des appels à la méthode `afterAction()` déclenche un événement `afterAction` auquel vous pouvez attacher un gestionnaire d'événement. 
6. L'application assigne le résultat de l'action à l'objet [response](runtime-responses.md).


## Meilleures pratiques <span id="best-practices"></span>

Dans une application bien conçue, les contrôleurs sont souvent très légers avec des actions qui ne contiennent que peu de code. Si votre contrôleur est plutôt compliqué, cela traduit la nécessité de remanier le code pour en déplacer certaines parties dans d'autres classes. 

Voici quelques meilleures pratiques spécifiques. Les contrôleurs :
* peuvent accéder aux données de la  [requête](runtime-requests.md) ;
* peuvent appeler les méthodes des [modèles](structure-models.md) et des autres composants de service avec les données de la requête ;
* peuvent utiliser des [vues](structure-views.md) pour composer leurs réponses ;
* ne devraient PAS traiter les données de la requête — cela devrait être fait dans la [couche modèle](structure-models.md) ;
* devraient éviter d'encapsuler du code HTML ou tout autre code relatif à la présentation — cela est plus avantageusement fait dans les [vues](structure-views.md).
