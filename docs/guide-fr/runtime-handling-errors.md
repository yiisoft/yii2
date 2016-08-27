Gestion des erreurs
===================

Yii inclut un [[yii\web\ErrorHandler|gestionnaire d'erreur]] pré-construit qui rend la gestion des erreurs bien plus agréable qu'auparavant. En particulier, le gestionnaire d'erreurs de Yii possède les fonctionnalités suivantes pour améliorer la gestion des erreurs.

* Toutes les erreurs PHP non fatales (p. ex. avertissements, notifications) sont converties en exceptions susceptibles d'être interceptées. 
* Les exceptions et les erreurs fatales sont affichées avec les informations détaillées de la pile des appels et les lignes de code source en mode *debug*.
* Prise en charge de l'utilisation d'une  [action de contrôleur](structure-controllers.md#actions) dédiée à l'affichage des erreurs.
* Prise en charge de différents formats de réponse d'erreur. 

Le  [[yii\web\ErrorHandler|gestionnaire d'erreur]] est activé par défaut. Vous pouvez le désactiver en définissant la constante `YII_ENABLE_ERROR_HANDLER` à `false` (faux) dans le [script d'entrée](structure-entry-scripts.md) de votre application.


## Utilisation du gestionnaire d'erreurs <span id="using-error-handler"></span>

Le [[yii\web\ErrorHandler|gestionnaire d'erreurs]] est enregistré en tant que [composant d'application](structure-application-components.md) nommé `errorHandler`. Vous pouvez le configurer dans la configuration de l'application comme indiqué ci-dessous : 

```php
return [
    'components' => [
        'errorHandler' => [
            'maxSourceLines' => 20,
        ],
    ],
];
```

Avec la configuration qui précède, le nombre de lignes de code source à afficher dans les pages d'exception est limité à 20. 

Comme cela a déjà été dit, le gestionnaire d'erreur transforme toutes les erreurs PHP non fatales en exception susceptibles d'être interceptées. Cela signifie que vous pouvez utiliser le code suivant pour vous servir de cette gestion d'erreurs :

```php
use Yii;
use yii\base\ErrorException;

try {
    10/0;
} catch (ErrorException $e) {
    Yii::warning("Division by zero.");
}

// l'exécution continue...
```

Si vous désirez afficher une page d'erreur disant à l'utilisateur que sa requête est invalide ou inattendue, vous pouvez simplement lever une [[yii\web\HttpException|exception HTTP]], comme l'exception [[yii\web\NotFoundHttpException]]. Le gestionnaire d'erreurs définit alors correctement le code d'état HTTP de la réponse et utilise une vue d'erreur appropriée pour afficher le message d'erreur. 

```php
use yii\web\NotFoundHttpException;

throw new NotFoundHttpException();
```


## Personnalisation de l'affichage des erreurs <span id="customizing-error-display"></span>

Le [[yii\web\ErrorHandler|gestionnaire d'erreurs]] ajuste l'affichage de l'erreur en tenant compte de la valeur de la constante  `YII_DEBUG`. Quand `YII_DEBUG` est égale à `true` (vrai) (ce qui signifie que le mode *debug* est activé), le gestionnaire d'erreurs affiche les exceptions avec le détail de la pile des appels et les lignes de code apportant de l'aide au débogage. Quand  `YII_DEBUG` est égale à `false` (faux), seule le message d'erreur est affiché pour ne pas révéler des informations sensibles sur l'application.

> Info: si une exception est un descendant de la classe [[yii\base\UserException]], aucune pile des appels n'est affichée, et ceci indépendamment de la valeur `YII_DEBUG`. Cela tient au fait que de telles exceptions résultent d'erreurs commises par l'utilisateur et que les développeurs n'ont rien à corriger. 

Par défaut, le [[yii\web\ErrorHandler|gestionnaire d'erreurs]] affiche les erreurs en utilisant deux [vues](structure-views.md):

* `@yii/views/errorHandler/error.php`: utilisée lorsque les erreurs doivent être affichées SANS les informations sur la pile des appels. Quand `YII_DEBUG` est égale à `false`, c'est la seule vue d'erreur à afficher.
* `@yii/views/errorHandler/exception.php`: utilisée lorsque les erreurs doivent être affichées AVEC les informations sur la pile des appels. 

Vous pouvez configurer les propriétés [[yii\web\ErrorHandler::errorView|errorView]] et [[yii\web\ErrorHandler::exceptionView|exceptionView]] du gestionnaire d'erreur pour utiliser vos propres vues afin de personnaliser l'affichage des erreurs. 


### Utilisation des actions d'erreurs <span id="using-error-actions"></span>

Une meilleure manière de personnaliser l'affichage des erreurs est d'utiliser des [actions](structure-controllers.md) d'erreur dédiées. Pour cela, commencez par configurer la propriété [[yii\web\ErrorHandler::errorAction|errorAction]] du composant `errorHandler` comme indiqué ci-après :

```php
return [
    'components' => [
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ]
];
```

La propriété [[yii\web\ErrorHandler::errorAction|errorAction]] accepte une [route](structure-controllers.md#routes) vers une action. La configuration ci-dessus établit que lorsqu'une erreur doit être affichée sans information de la pile des appels, l'action `site/error` doit être exécutée.

Vous pouvez créer une action `site/error` comme ceci :

```php
namespace app\controllers;

use Yii;
use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
}
```

Le code ci-dessus définit l'action `error` en utilisant la classe [[yii\web\ErrorAction]] qui rend une erreur en utilisant une vue nommée `error`.

En plus d'utiliser  [[yii\web\ErrorAction]], vous pouvez aussi définir l'action `error` en utilisant une méthode d'action similaire à la suivante :

```php
public function actionError()
{
    $exception = Yii::$app->errorHandler->exception;
    if ($exception !== null) {
        return $this->render('error', ['exception' => $exception]);
    }
}
```

Vous devez maintenant créer un fichier de vue `views/site/error.php`. Dans ce fichier de vue, vous pouvez accéder aux variables suivantes si l'action d'erreur est définie en tant que [[yii\web\ErrorAction]]:

* `name`: le nom de l'erreur ;
* `message`: le message d'erreur ;
* `exception`: l'objet exception via lequel vous pouvez retrouver encore plus d'informations utiles telles que le code d'état HTTP, le code d'erreur, la pile des appels de l'erreur, etc. 

> Info: si vous êtes en train d'utiliser le [modèle de projet *basic*](start-installation.md) ou le [modèle de projet avancé](https://github.com/yiisoft/yii2-app-advanced/blob/master/docs/guide/README.md), l'action d'erreur est la vue d'erreur sont déjà définies pour vous. 

> Note: si vous avez besoin de rediriger dans un gestionnaire d'erreur, faites-le de la manière suivante :
>
> ```php
> Yii::$app->getResponse()->redirect($url)->send();
> return;
> ```


### Personnalisation du format de la réponse d'erreur  <span id="error-format"></span>

Le gestionnaire d'erreurs affiche les erreurs en respectant le réglage de format de la [réponse](runtime-responses.md). Si le [[yii\web\Response::format|format de la réponse]] est `html`, il utilise la vue d'erreur ou d'exception pour afficher les erreurs, comme c'est expliqué dans la sous-section précédente. Pour les autres formats de réponse, le gestionnaire d'erreurs assigne la représentation de l'erreur sous forme de tableau à la propriété [[yii\web\Response::data]] qui est ensuite convertie dans le format désiré. Par exemple, si le format de la réponse est `json`, vous pourriez voir une réponse similaire à la suivante : 

```
HTTP/1.1 404 Not Found
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "name": "Not Found Exception",
    "message": "The requested resource was not found.",
    "code": 0,
    "status": 404
}
```

Vous pouvez personnaliser le format de réponse d'erreur en répondant à l'événement `beforeSend` du composant `response` dans la configuration de l'application :

```php
return [
    // ...
    'components' => [
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if ($response->data !== null) {
                    $response->data = [
                        'success' => $response->isSuccessful,
                        'data' => $response->data,
                    ];
                    $response->statusCode = 200;
                }
            },
        ],
    ],
];
```

Le code précédent formate la réponse d'erreur comme suit : 

```
HTTP/1.1 200 OK
Date: Sun, 02 Mar 2014 05:31:43 GMT
Server: Apache/2.2.26 (Unix) DAV/2 PHP/5.4.20 mod_ssl/2.2.26 OpenSSL/0.9.8y
Transfer-Encoding: chunked
Content-Type: application/json; charset=UTF-8

{
    "success": false,
    "data": {
        "name": "Not Found Exception",
        "message": "The requested resource was not found.",
        "code": 0,
        "status": 404
    }
}
```
