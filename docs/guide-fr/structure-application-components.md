Composants d'application
======================
Les applications sont des  [(localisateurs de services (service locators)](concept-service-locator.md). Elles hébergent un jeu composants appelés « composants d'application » qui procurent différents services pour la prise en charge des requêtes. Par exemple, le composant `urlManager` (gestionnaire d'url) est chargé de router les requêtes Web vers les contrôleurs appropriés ; le composant  `db` (base de données) fournit les services relatifs à la base de données ; et ainsi de suite.

Chaque composant d'application possède un identifiant unique qui le distingue des autres composants d'application de la même application. Vous pouvez accéder à un composant d'application via l'expression :

```php
\Yii::$app->componentID
```

Par exemple, vous pouvez utiliser `\Yii::$app->db` pour obtenir la  [[yii\db\Connection|connexion à la base de données]], et `\Yii::$app->cache` pour accéder au  [[yii\caching\Cache|cache primaire]] enregistré dans l'application.

Un composant d'application est créé la première fois qu'on veut y accéder en utilisant l'expression ci-dessus. Les accès ultérieurs retournent la même instance du composant.

Les composants d'application peuvent être n'importe quel objet. Vous pouvez les enregistrer en configurant la propriété [[yii\base\Application::components]] dans la [configuration de l'application](structure-applications.md#application-configurations).

Par exemple,

```php
[
    'components' => [
        // enregistre le composant  "cache" à partir du nom de classe
        'cache' => 'yii\caching\ApcCache',

        // enregistre le composant "db" à l'aide d'un tableau de configuration
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=demo',
            'username' => 'root',
            'password' => '',
        ],

        // enregistre le composant "search" en utilisant une fonction anonyme
        'search' => function () {
            return new app\components\SolrService;
        },
    ],
]
```

> Info: bien que vous puissiez enregistrer autant de composants d'application que vous le désirez, vous devriez le faire avec discernement. Les composants d'application sont comme les variables globales, une utilisation trop importante de composants d'application est susceptible de rendre votre code plus difficile à tester et à maintenir. Dans beaucoup de cas, vous pouvez simplement créer un composant localement et l'utiliser lorsque vous en avez besoin. 


## Composants du processus d'amorçage <span id="bootstrapping-components"></span>

Comme il a été dit plus haut, un composant d'application n'est instancié que lorsqu'on y accède pour la première fois. S'il n'est pas du tout accédé dans le traitement de la requête, il n'est pas instancié. Parfois, vous désirez peut être instancier un composant d'application pour chacune des requêtes, même s'il n'est pas explicitement accédé. 
Pour cela, vous pouvez lister son identifiant (ID) dans la propriété [[yii\base\Application::bootstrap|bootstrap]] de l'application.

Vous pouvez également utiliser des « Closures » (Fermetures) pour amorcer des composants personnalisés. Il n'est pas nécessaire de retourner une instance de composant. Une « Closure » peut également être utilisée pour exécuter du code après l'instanciation de [[yii\base\Application]].

Par exemple, la configuration d'application suivante garantit que le composant `log` est toujours chargé.

```php
[
    'bootstrap' => [
        'log',
        function($app){
            return new ComponentX();
        },
        function($app){
            // some code
           return;
        }
    ],
    'components' => [
        'log' => [
            // configuration le composant "log" 
        ],
    ],
]
```

## Composants d'application du noyau <span id="core-application-components"></span>

Yii définit un jeu de composants d'application dit *core application components* (composants d'application du noyau ou du cœur) avec des identifiants fixés et des configurations par défaut.   Par exemple, le composant [[yii\web\Application::request|request (requête)]] est utilisé pour collecter les informations sur une requête  utilisateur et la résoudre en une [route](runtime-routing.md); le composant  [[yii\base\Application::db|db (base de données)]] représente une connexion à une base de données à l'aide de laquelle vous pouvez effectuer des requêtes de base de données. C'est à l'aide des ces composants d'application du noyau que les applications Yii sont en mesure de prendre en charge les requêtes des utilisateurs.

Vous trouverez ci-après la liste des composants d'application prédéfinis du noyau. Vous pouvez les configurer et les personnaliser comme tout composant d'application. Lorsque vous configurez une composant d'application du noyau, vous n'avez pas besoin de spécifier sa classe, celle par défaut est utilisée. 


* [[yii\web\AssetManager|assetManager (gestionnaire de ressources]]: gère les paquets de ressources et la publication des ressources. 
  Reportez-vous à la section [Ressources](structure-assets.md) pour plus de détails.
* [[yii\db\Connection|db (base de données)]]: représente une connexion à une base de données à l'aide de laquelle vous pouvez effectuer des requêtes de base de données. 
  Notez que lorsque vous configurez ce composant, vous devez spécifier la classe de composant tout comme les autres propriétés de composant, telle que [[yii\db\Connection::dsn]].
  Reportez-vous à la section [Objets d'accès aux bases de données](db-dao.md) pour plus de détails.
* [[yii\base\Application::errorHandler|errorHandler (gestionnaire d'erreurs) ]]: gère les erreurs PHP et les exceptions.
  Reportez-vous à la section [Gestion des erreurs](runtime-handling-errors.md) pour plus de détails.
* [[yii\i18n\Formatter|formatter ]]: formate les données lorsqu'elles sont présentées à l'utilisateur final. Par exemple, un nombre peut être affiché avec un séparateur de milliers, une date affichée dans un format long, etc.
  Reportez-vous à la section [Formatage des données](output-formatting.md) pour plus de détails.
* [[yii\i18n\I18N|i18n]]: prend en charge la traduction et le formatage des messages. 
  Reportez-vous à la section [Internationalisation](tutorial-i18n.md) pour plus de détails.
* [[yii\log\Dispatcher|log]]: gère les journaux cibles. 
  Reportez-vous à la section  [Journaux](runtime-logging.md) pour plus de détails.
* [[yii\swiftmailer\Mailer|mailer]]: prend en charge la composition et l'envoi des courriels.
  Reportez-vous à la section [Mailing](tutorial-mailing.md) pour plus de détails.
* [[yii\base\Application::response|response]]: représente la réponse qui est adressée à l'utilisateur final. 
  Reportez-vous à la section  [Réponses](runtime-responses.md) pour plus de détails.
* [[yii\base\Application::request|request]]: représente la requête reçue de l'utilisateur final.
  Reportez-vous à la section [Requests](runtime-requests.md) pour plus de détails.
* [[yii\web\Session|session]]: représente les informations de session. Ce composant n'est disponible que dans les [[yii\web\Application|applications Web]].
  Reportez-vous à la section [Sessions et Cookies](runtime-sessions-cookies.md) pour plus de détails.
* [[yii\web\UrlManager|urlManager (gestionnaire d'url)]]: prend en charge l'analyse des URL et leur création.
  Reportez-vous à la section  [Routage et création d'URL](runtime-routing.md) pour plus de détails.
* [[yii\web\User|user]]: représente les informations d'authentification de l'utilisateur. Ce composant n'est disponible que dans les [[yii\web\Application|applications Web]].
  Reportez-vous à la section [Authentification](security-authentication.md) pour plus de détails.
* [[yii\web\View|view]]: prend en charge le rendu des vues. 
  Reportez-vous à la section  [Vues](structure-views.md) pour plus de détails.
