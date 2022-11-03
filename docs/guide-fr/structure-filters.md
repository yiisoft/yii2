Filtres
=======

Les filtres sont des objets qui sont exécutés avant et/ou après les [actions de contrôleurs](structure-controllers.md#actions). Par exemple, un filtre de contrôle d'accès peut être exécuté avant les actions pour garantir qu'un utilisateur final particulier est autorisé à y accéder. Un filtre de compression de contenu peut être exécuté après les actions pour compresser la réponse avant de l'envoyer à l'utilisateur final. 

Un filtre peut être constitué d''un pré-filtre (logique de filtrage appliquée *avant* les actions) et/ou un post-filtre (logique appliquée *après* les actions). 


## Utilisation des filtres <span id="using-filters"></span>

Pour l'essentiel, les filtres sont des sortes de [comportements](concept-behaviors.md). Par conséquent, leur utilisation est identique à l' [utilisation des comportements](concept-behaviors.md#attaching-behaviors). Vous pouvez déclarer des filtres dans une classe de contrôleur en redéfinissant sa méthode [[yii\base\Controller::behaviors()|behaviors()]] de la manière suivante :

```php
public function behaviors()
{
    return [
        [
            'class' => 'yii\filters\HttpCache',
            'only' => ['index', 'view'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

Par défaut, les filtres déclarés dans une classe de contrôleur sont appliqués à *toutes* les action de ce contrôleur. Vous pouvez cependant, spécifier explicitement à quelles actions ils s'appliquent en configurant la propriété [[yii\base\ActionFilter::only|only]]. Dans l'exemple précédent, le filtre `HttpCache` s'applique uniquement aux actions `index` et `view`. Vous pouvez également configurer la propriété [[yii\base\ActionFilter::except|except]] pour empêcher quelques actions d'être filtrées. 

En plus des contrôleurs, vous pouvez également déclarer des filtres dans un [module](structure-modules.md) ou dans une [application](structure-applications.md). Lorsque vous faites cela, les filtres s'appliquent à *toutes* les actions de contrôleur qui appartiennent à ce module ou à cette application, sauf si vous configurez les propriétés des filtres [[yii\base\ActionFilter::only|only]] et [[yii\base\ActionFilter::except|except]] comme expliqué précédemment. 

> Note: lorsque vous déclarez des filtres dans des modules ou des applications, vous devriez utiliser des [routes](structure-controllers.md#routes) plutôt que des identifiants d'action dans les propriétés [[yii\base\ActionFilter::only|only]] et [[yii\base\ActionFilter::except|except]]. Cela tient au fait qu'un identifiant d'action seul ne peut pas pleinement spécifier une  action dans le cadre d'un module ou d'une application. 

Lorsque plusieurs filtres sont configurés pour une même action, ils sont appliqués en respectant les règles et l'ordre qui suivent :

* Pré-filtrage
    - Les filtres déclarés dans l'application sont appliqués dans l'ordre dans lequel ils sont listés dans la méthode `behaviors()`.
    - Les filtres déclarés dans le module sont appliqués dans l'ordre dans lequel ils sont listés dans la méthode `behaviors()`.
    - Les filtres déclarés dans le contrôleur sont appliqués dans l'ordre dans lequel ils sont listés dans la méthode `behaviors()`.
    - Si l'un quelconque des filtres annule l'exécution de l'action, les filtres subséquents (à la fois de pré-filtrage et de post-fitrage) ne sont pas appliqués.
* L'action est exécutée si les filtres de pré-filtrage réussissent.
* Post-filtrage
    - Les filtres déclarés dans le contrôleur sont appliqués dans l'ordre dans lequel ils sont listés dans la méthode `behaviors()`.
    - Les filtres déclarés dans le module sont appliqués dans l'ordre dans lequel ils sont listés dans la méthode `behaviors()`.
    - Les filtres déclarés dans l'application sont appliqués dans l'ordre dans lequel ils sont listés dans la méthode `behaviors()`.


## Création de filtres <span id="creating-filters"></span>

Pour créer un filtre d'action, vous devez étendre la classe [[yii\base\ActionFilter]] et redéfinir la méthode [[yii\base\ActionFilter::beforeAction()|beforeAction()]] et/ou la méthode [[yii\base\ActionFilter::afterAction()|afterAction()]]. La première est exécutée avant l'exécution de l'action, tandis que la seconde est exécutée après l'exécution de l'action. Le valeur de retour de la méthode [[yii\base\ActionFilter::beforeAction()|beforeAction()]] détermine si une action doit être exécutée ou pas. Si c'est `false` (faux), les filtres qui suivent sont ignorés et l'action n'est pas exécutée. 


L'exemple qui suit montre un filtre qui enregistre dans un journal le temps d'exécution de l'action :

```php
namespace app\components;

use Yii;
use yii\base\ActionFilter;

class ActionTimeFilter extends ActionFilter
{
    private $_startTime;

    public function beforeAction($action)
    {
        $this->_startTime = microtime(true);
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        $time = microtime(true) - $this->_startTime;
        Yii::debug("Action '{$action->uniqueId}' spent $time second.");
        return parent::afterAction($action, $result);
    }
}
```


## Filtres du noyau <span id="core-filters"></span>

Yii fournit un jeu de filtres couramment utilisés, que l'on trouve en premier lieu dans l'espace de noms `yii\filters`. Dans ce qui suit, nous introduisons brièvement ces filtres. 

### [[yii\filters\AccessControl|AccessControl]] <span id="access-control"></span>

*AccessControl* (contrôle d'accès) fournit un contrôle d'accès simple basé sur un jeu de [[yii\filters\AccessControl::rules|règles]]. En particulier, avant qu'une action ne soit exécutée, *AccessControl* examine les règles listées et trouve la première qui  correspond aux variables du contexte courant (comme l'adresse IP, l'état de connexion de l'utilisateur, etc.). La règle qui correspond détermine si l'exécution de l'action requise doit être autorisée ou refusée. Si aucune des règles ne correspond, l'accès est refusé. 

L'exemple suivant montre comment autoriser les utilisateurs authentifiés à accéder aux actions `create` et `update` tout en refusant l'accès à ces actions aux autres utilisateurs.

```php
use yii\filters\AccessControl;

public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'only' => ['create', 'update'],
            'rules' => [
                // autoriser les utilisateurs authentifiés
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
                // tout autre chose est interdite d'accès par défaut
            ],
        ],
    ];
}
```

Pour plus de détails sur le contrôle d'accès en général, reportez-vous à la section [Authorization](security-authorization.md).


### Filtres de méthodes d'authentification <span id="auth-method-filters"></span>

Les filtres de méthodes d'authentification sont utilisés pour authentifier un utilisateur qui utilise des méthodes d'authentification variées comme
[HTTP Basic Auth](https://en.wikipedia.org/wiki/Basic_access_authentication) ou [OAuth 2](https://oauth.net/2/). Les classes de filtre sont dans l'espace de noms `yii\filters\auth`.

L'exemple qui suit montre comment vous pouvez utiliser [[yii\filters\auth\HttpBasicAuth]] pour authentifier un utilisateur qui utilise un jeton d'accès basé sur la méthode  *HTTP Basic Auth*. Notez qu'afin que cela fonctionne, votre [[yii\web\User::identityClass|classe *identity* de l'utilisateur]] doit implémenter l'interface [[yii\web\IdentityInterface::findIdentityByAccessToken()|findIdentityByAccessToken()]].

```php
use yii\filters\auth\HttpBasicAuth;

public function behaviors()
{
    return [
        'basicAuth' => [
            'class' => HttpBasicAuth::class,
        ],
    ];
}
```

Les filtres de méthode d'authentification sont communément utilisés dans la mise en œuvre des API pleinement REST. Pour plus de détails, reportez-vous à la section [Authentification REST](rest-authentication.md).


### [[yii\filters\ContentNegotiator|ContentNegotiator]] <span id="content-negotiator"></span>

*ContentNegotiator* (négociateur de contenu) prend en charge la négociation des formats de réponse et la négociation de langue d'application. Il essaye de déterminer le format de la réponse et/ou la langue en examinant les paramètres de la méthode `GET` et ceux de l'entête HTTP `Accept`.

Dans l'exemple qui suit, le filtre *ContentNegotiator* est configuré pour prendre en charge JSON et XML en tant que formats de réponse, et anglais (États-Unis) et allemand en tant que langues. 

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

public function behaviors()
{
    return [
        [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ];
}
```

Les formats de réponse et les langues nécessitent souvent d'être déterminés bien plus tôt durant le [cycle de vie de l'application](structure-applications.md#application-lifecycle). Pour cette raison, *ContentNegotiator* est conçu de manière à être également utilisé en tant que [composant du processus d'amorçage](structure-applications.md#bootstrap). Par exemple, vous pouvez le configurer dans la [configuration de l'application](structure-applications.md#application-configurations) de la manière suivante :

```php
use yii\filters\ContentNegotiator;
use yii\web\Response;

[
    'bootstrap' => [
        [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
            'languages' => [
                'en-US',
                'de',
            ],
        ],
    ],
];
```

> Info: dans le cas où le type de contenu et la langue préférés ne peuvent être déterminés à partir de la requête, le premier format et la première langue listés dans [[formats]] et[[languages]], respectivement, sont utilisés.



### [[yii\filters\HttpCache|HttpCache]] <span id="http-cache"></span>

*HttpCache* met en œuvre  la mise en cache côté client en utilisant les entêtes HTTP `Last-Modified` (dernier modifié) et `Etag`.
Par exemple :

```php
use yii\filters\HttpCache;

public function behaviors()
{
    return [
        [
            'class' => HttpCache::class,
            'only' => ['index'],
            'lastModified' => function ($action, $params) {
                $q = new \yii\db\Query();
                return $q->from('user')->max('updated_at');
            },
        ],
    ];
}
```

Reportez-vous à  la section [Mise en cache HTTP](caching-http.md) pour plus de détails sur l'utilisation de  *HttpCache*.


### [[yii\filters\PageCache|PageCache]] <span id="page-cache"></span>

*PageCache* met en œuvre la mise en cache de pages entières côté serveur. Dans l'exemple qui suit, *PageCache0 est appliqué à l'action `index` pour mettre la page entière en cache pendant un maximum de 60 secondes ou jusqu'à un changement du nombre d'entrées dans la table `post`. Il stocke également différentes versions de la page en fonction de la langue choisie. 

```php
use yii\filters\PageCache;
use yii\caching\DbDependency;

public function behaviors()
{
    return [
        'pageCache' => [
            'class' => PageCache::class,
            'only' => ['index'],
            'duration' => 60,
            'dependency' => [
                'class' => DbDependency::class,
                'sql' => 'SELECT COUNT(*) FROM post',
            ],
            'variations' => [
                \Yii::$app->language,
            ]
        ],
    ];
}
```

Reportez-vous à la section [Page Caching](caching-page.md) pour plus de détails sur l'utilisation de  *PageCache*.


### [[yii\filters\RateLimiter|RateLimiter]] <span id="rate-limiter"></span>

*RateLimiter* met en œuvre un algorithme de limitation de débit basé sur  l'[algorithme leaky bucket](https://en.wikipedia.org/wiki/Leaky_bucket). On l'utilise en premier lieu dans la mise en œuvre des API pleinement REST. Reportez-vous à la section [limitation de débit](rest-rate-limiting.md) pour plus de détails sur l'utilisation de ce filtre.


### [[yii\filters\VerbFilter|VerbFilter]] <span id="verb-filter"></span>

*VerbFilter* vérifie si les méthodes de requête HTTP sont autorisées par l'action requise. Si ce n'est pas le cas, une exception HTTP 405 est levée. Dans l'exemple suivant, *VerbFilter* est déclaré pour spécifier un jeu typique de méthodes de requête pour des actions CRUD — Create (créer), Read (lire), Update (mettre à jour), DELETE (supprimer).

```php
use yii\filters\VerbFilter;

public function behaviors()
{
    return [
        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['get', 'post'],
                'update' => ['get', 'put', 'post'],
                'delete' => ['post', 'delete'],
            ],
        ],
    ];
}
```

### [[yii\filters\Cors|Cors]] <span id="cors"></span>

*Cross-origin resource sharing* [CORS](https://developer.mozilla.org/fr/docs/Web/HTTP/CORS) est un mécanisme qui permet à des ressource (e.g. fonts, JavaScript, etc.) d'être requises d'un autre domaine en dehors du domaine dont la ressource est originaire. En particulier, les appels AJAX de Javascript peuvent utiliser le mécanisme *XMLHttpRequest*. Autrement, de telles requêtes "cross-domain" (inter domaines) seraient interdites par les navigateurs, à cause de la politique de sécurité dite d'origine identique (*same origin*). *CORS* définit une manière par laquelle le navigateur et le serveur interagissent pour déterminer si, oui ou non, la requête *cross-origin* (inter-site) est autorisée. 

Le [[yii\filters\Cors|filtre Cors]] doit être défini avant les filtres d'authentification et/ou d'autorisation pour garantir que les entêtes CORS sont toujours envoyés.

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
        ],
    ], parent::behaviors());
}
```

Consultez également la section sur les [contrôleurs REST](rest-controllers.md#cors) si vous voulez ajouter le filtre CORS à une classe 
[[yii\rest\ActiveController]] dans votre API.

Les filtrages Cors peuvent être peaufinés via la propriété [[yii\filters\Cors::$cors|$cors]].

* `cors['Origin']`: un tableau utilisé pour définir les origines autorisées. Peut être `['*']` (tout le monde) ou `['https://www.myserver.net', 'https://www.myotherserver.com']`. Valeur par défaut  `['*']`.
* `cors['Access-Control-Request-Method']`: un tableau des verbes autorisés tel que `['GET', 'OPTIONS', 'HEAD']`.  Valeur par défaut `['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']`.
* `cors['Access-Control-Request-Headers']`: un tableau des entêtes autorisés. Peut être`['*']` tous les entêtes ou certains spécifiquement `['X-Request-With']`. Valeur par défaut `['*']`.
* `cors['Access-Control-Allow-Credentials']`: définit si la requête courante peut être faite en utilisant des identifiants de connexion.  Peut être `true` (vrai), `false` (faux) ou  `null` (non défini). Valeur par défaut `null`.
* `cors['Access-Control-Max-Age']`: définit la durée de vie des requêtes de pré-vérification (*preflight requests*). Valeur par défaut `86400`.

Par exemple, autoriser  CORS pour l'origine  `https://www.myserver.net` avec les méthodes `GET`, `HEAD` et `OPTIONS` :

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
        ],
    ], parent::behaviors());
}
```

Vous pouvez peaufiner les entêtes CORS en redéfinissant les paramètres par défaut action par action. Par exemple, ajouter les `Access-Control-Allow-Credentials`  (autoriser les identifiants de contrôle d'accès) pour l'action`login` pourrait être réalisé comme ceci :

```php
use yii\filters\Cors;
use yii\helpers\ArrayHelper;

public function behaviors()
{
    return ArrayHelper::merge([
        [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['https://www.myserver.net'],
                'Access-Control-Request-Method' => ['GET', 'HEAD', 'OPTIONS'],
            ],
            'actions' => [
                'login' => [
                    'Access-Control-Allow-Credentials' => true,
                ]
            ]
        ],
    ], parent::behaviors());
}
```
