Sessions et témoins de connexion
================================

Les sessions et les témoins de connexion permettent à des données d'être conservées à travers des requêtes multiples. Avec le langage PHP simple, vous pouvez y accéder via les variables globales `$_SESSION` et `$_COOKIE`, respectivement. Yii encapsule les sessions et les témoins de connexion sous forme d'objets et, par conséquent, vous permet d'y accéder d'une manière orientée objet avec des améliorations utiles. 

## Sessions <span id="sessions"></span>

Comme pour les [requêtes](runtime-requests.md) et les [réponses](runtime-responses.md), vous pouvez accéder aux sessions via le [composant d'application](structure-application-components.md) `session` qui, par défaut, est une instance de la classe [[yii\web\Session]].


### Ouverture et fermeture d'une session <span id="opening-closing-sessions"></span>

Pour ouvrir et fermer une session, vous pouvez procéder comme suit :

```php
$session = Yii::$app->session;

// vérifie si une session est déjà ouverte
if ($session->isActive) ...

// ouvre une session
$session->open();

// ferme une session
$session->close();

// détruit toutes les données enregistrées dans une session.
$session->destroy();
```

Vous pouvez appeler les méthodes  [[yii\web\Session::open()|open()]] et [[yii\web\Session::close()|close()]] plusieurs fois sans causer d'erreur ; en interne les méthodes commencent par vérifier si la session n'est pas déjà ouverte. 


### Accès aux données de  session <span id="access-session-data"></span>

Pour accéder aux données stockées dans une session, vous pouvez procéder comme indiqué ci-après :

```php
$session = Yii::$app->session;

// obtient une variable de session. Les utilisations suivantes sont équivalentes :
$language = $session->get('language');
$language = $session['language'];
$language = isset($_SESSION['language']) ? $_SESSION['language'] : null;

// définit une variable de session variable. Les utilisations suivantes sont équivalentes :
$session->set('language', 'en-US');
$session['language'] = 'en-US';
$_SESSION['language'] = 'en-US';

// supprime une variable session. Les utilisations suivantes sont équivalentes :
$session->remove('language');
unset($session['language']);
unset($_SESSION['language']);

// vérifie si une session possède la variable 'language'. Les utilisations suivantes sont équivalentes :
if ($session->has('language')) ...
if (isset($session['language'])) ...
if (isset($_SESSION['language'])) ...

// boucle sur toutes les sessions. Les utilisations suivantes sont équivalentes :
foreach ($session as $name => $value) ...
foreach ($_SESSION as $name => $value) ...
```

> Info: lorsque vous accédez aux données d'une session via le composant  `session`, une session est automatiquement ouverte si elle ne l'a pas déjà été.  Cela est différent de l'accès aux données via la variable globale `$_SESSION`, qui réclame un appel préalable  explicite de `session_start()`.

Lorsque vous travaillez avec les données de session qui sont des tableaux, le composant `session` possède une limitation qui vous empêche de modifier directement un des élément de ces tableaux. Par exemple :

```php
$session = Yii::$app->session;

// le code suivant ne fonctionne PAS
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// le code suivant fonctionne :
$session['captcha'] = [
    'number' => 5,
    'lifetime' => 3600,
];

// le code suivant fonctionne également :
echo $session['captcha']['lifetime'];
```

Vous pouvez utiliser une des solutions de contournement suivantes pour résoudre ce problème :


```php
$session = Yii::$app->session;

// utiliser directement  $_SESSION (assurez-vous que  Yii::$app->session->open() a été appelée)
$_SESSION['captcha']['number'] = 5;
$_SESSION['captcha']['lifetime'] = 3600;

// obtenir le tableau complet d'abord, le modifier et le sauvegarder
$captcha = $session['captcha'];
$captcha['number'] = 5;
$captcha['lifetime'] = 3600;
$session['captcha'] = $captcha;

// utiliser un  ArrayObject au lieu d'un tableau
$session['captcha'] = new \ArrayObject;
...
$session['captcha']['number'] = 5;
$session['captcha']['lifetime'] = 3600;

// stocker les données du tableau par une clé avec un préfixe commun 
$session['captcha.number'] = 5;
$session['captcha.lifetime'] = 3600;
```

Pour une meilleure performance et une meilleure lisibilité du code, nous recommandons la dernière solution de contournement. Elle consiste, au lieu de stocker un tableau comme une donnée de session unique, à stocker chacun des éléments du tableau comme une variable de session qui partage le même préfixe de clé avec le reste des éléments de ce tableau.


### Stockage de session personnalisé <span id="custom-session-storage"></span>

La classe par défaut [[yii\web\Session]] stocke les données de session sous forme de fichiers sur le serveur. Yii fournit également des classes de session qui mettent en œuvre des procédés de stockage différents. En voici la liste :

* [[yii\web\DbSession]]: stocke les données de session dans une base de données. 
* [[yii\web\CacheSession]]: stocke les données de session dans un cache avec l'aide d'un [composant cache](caching-data.md#cache-components) configuré.
* [[yii\redis\Session]]: stocke les données de session en utilisant le médium de stockage [redis](https://redis.io/) as the storage medium.
* [[yii\mongodb\Session]]: stocke les données de session dans une base de données de documents [MongoDB](https://www.mongodb.com/).

Toutes ces classes de session prennent en charge le même jeu de méthodes d'API. En conséquence, vous pouvez changer de support de stockage sans avoir à modifier le code de votre application qui utilise ces sessions. 

> Note: si vous voulez accéder aux données de session via `$_SESSION` quand vous êtes en train d'utiliser une session à stockage personnalisé, vous devez vous assurer que cette session a été préalablement démarrée via  [[yii\web\Session::open()]]. Cela est dû au fait que les gestionnaires de stockage des sessions personnalisées sont enregistrés à l'intérieur de cette méthode. 

> Note : si vous utilisez un stockage de session personnalisé, vous devez configurer le collecteur de déchets de session explicitement. 
Quelques installations de PHP (p. ex. Debian) utilisent une probabilité de collecteur de déchets de 0 et nettoient les fichiers de session hors ligne dans une tâche de cron. Ce processus ne s'applique pas à votre stockage personnalisé, c'est pourquoi vous devez configurer  
  [[yii\web\Session::$GCProbability]] pour utiliser une valeur non nulle.

Pour savoir comment configurer et utiliser ces classes de composant, reportez-vous à leur documentation d'API. Ci-dessous, nous présentons un exemple de configuration de [[yii\web\DbSession]] dans la configuration de l'application pour utiliser une base de données en tant que support de stockage d'une session :


```php
return [
    'components' => [
        'session' => [
            'class' => 'yii\web\DbSession',
            // 'db' => 'mydb',  // l'identifiant du composant d'application de la connexion à la base de données. Valeur par défaut : 'db'.
            // 'sessionTable' => 'my_session', // nom de la table 'session' . Valeur par défaut : 'session'.
        ],
    ],
];
```

Vous devez aussi créer la base de données suivante pour stocker les données de session :

```sql
CREATE TABLE session
(
    id CHAR(40) NOT NULL PRIMARY KEY,
    expire INTEGER,
    data BLOB
)
```

où 'BLOB' fait référence au type « grand objet binaire » (binary large objet — BLOB) de votre système de gestion de base de données (DBMS) préféré. Ci-dessous, vous trouverez les types de BLOB qui peuvent être utilisés par quelques DBMS populaires :
- MySQL: LONGBLOB
- PostgreSQL: BYTEA
- MSSQL: BLOB

> Note: en fonction des réglages de `session.hash_function` dans votre fichier php.ini, vous devez peut-être ajuster la longueur de la colonne `id`. Par exemple, si  `session.hash_function=sha256`, vous devez utiliser une longueur de 64 au lieu de 40. 

Cela peut être accompli d'une façon alternative avec la migration suivante :

```php
<?php

use yii\db\Migration;

class m170529_050554_create_table_session extends Migration
{
    public function up()
    {
        $this->createTable('{{%session}}', [
            'id' => $this->char(64)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->binary()
        ]);
        $this->addPrimaryKey('pk-id', '{{%session}}', 'id');
    }

    public function down()
    {
        $this->dropTable('{{%session}}');
    }
}
```
  

### Donnés flash <span id="flash-data"></span>

Les données flash sont une sorte de données de session spéciale qui, une fois définies dans une requête, ne restent disponibles que durant la requête suivante et sont détruites automatiquement ensuite. Les données flash sont le plus communément utilisées pour mettre en œuvre des messages qui doivent être présentés  une seule  fois, comme les messages de confirmation affichés après une soumission réussie de formulaire. 

Vous pouvez définir des données flash et y accéder via le composant d'application `session`. Par exemple :

```php
$session = Yii::$app->session;

// Request #1
// définit un message flash nommé "commentDeleted"
$session->setFlash('commentDeleted', 'Vous avez réussi la suppression de votre commentaire.');

// Request #2
// affiche le message  flash nommé "commentDeleted"
echo $session->getFlash('commentDeleted');

// Request #3
// $result est faux puisque le message flash a été automatiquement supprimé
$result = $session->hasFlash('commentDeleted');
```

Comme les données de session ordinaires, vous pouvez stocker des données arbitraires sous forme de données flash.

Vous pouvez appeler [[yii\web\Session::setFlash()]], cela écrase toute donnée flash préexistante qui a le même nom. Pour ajouter une nouvelle donnée flash à un message existant, vous pouvez utiliser [[yii\web\Session::addFlash()]] à la place. Par exemple :


```php
$session = Yii::$app->session;

// Request #1
// ajoute un message flash nommé  "alerts"
$session->addFlash('alerts', 'Vous avez réussi la suppression de votre commentaire');
$session->addFlash('alerts', 'Vous avez réussi l'ajout d'un ami.');
$session->addFlash('alerts', 'Vous êtes promu.');

// Request #2
// $alerts est un tableau de messages flash nommé "alerts"
$alerts = $session->getFlash('alerts');
```

> Note: évitez d'utiliser [[yii\web\Session::setFlash()]] en même temps que  [[yii\web\Session::addFlash()]] pour des données flash de même nom. C'est parce que la deuxième méthode transforme automatiquement les données flash en tableau pour pouvoir y ajouter des données. En conséquence, quand vous appelez [[yii\web\Session::getFlash()]], vous pouvez parfois recevoir un tableau ou une chaîne de caractères selon l'ordre dans lequel ces méthodes ont été appelées. 

> Tip: pour afficher des messages Flash vous pouvez utiliser l'objet graphique  [[yii\bootstrap\Alert|bootstrap Alert]] de la manière suivante :
>
> ```php
> echo Alert::widget([
>    'options' => ['class' => 'alert-info'],
>    'body' => Yii::$app->session->getFlash('postDeleted'),
> ]);
> ```


## Témoins de connexion <span id="cookies"></span>

Yii représente chacun des témoins de connexion sous forme d'objet de classe [[yii\web\Cookie]]. Les objets [[yii\web\Request]] et [[yii\web\Response]] contiennent une collection de témoins de connexion via la propriété nommée  `cookies`. La collection de témoins de connexion dans le premier de ces objets est celle soumise dans une requête, tandis que celle du deuxième objet représente les témoins de connexion envoyés à l'utilisateur. 

La partie de l'application qui traite la requête et la réponse directement est le contrôleur. Par conséquent, les témoins de connexion doivent être lus et envoyés dans le contrôleur. 

### Lecture des  témoins de connexion <span id="reading-cookies"></span>

Vous pouvez obtenir les témoins de connexion de la requête courante en utilisant le code suivant :

```php
// obtient la collection de témoins de connexion (yii\web\CookieCollection) du composant "request" 
$cookies = Yii::$app->request->cookies;

// obtient la valeur du témoin de connexion  "language". Si le témoin de connexion n'existe pas, retourne  "en" par défaut.
$language = $cookies->getValue('language', 'en');

// une façon alternative d'obtenir la valeur du témoin de connexion "language" 
if (($cookie = $cookies->get('language')) !== null) {
    $language = $cookie->value;
}

// vous pouvez aussi utiliser  $cookies comme un tableau
if (isset($cookies['language'])) {
    $language = $cookies['language']->value;
}

// vérifie si un témoin de connexion "language" existe
if ($cookies->has('language')) ...
if (isset($cookies['language'])) ...
```


### Envoi de  témoins de connexion <span id="sending-cookies"></span>

Vous pouvez envoyer des témoins de connexion à l'utilisateur final avec le code suivant :

```php
// obtient la collection de témoins de connexion (yii\web\CookieCollection) du composant "response" 
$cookies = Yii::$app->response->cookies;

// ajoute un témoin de connexion à la réponse à envoyer
$cookies->add(new \yii\web\Cookie([
    'name' => 'language',
    'value' => 'zh-CN',
]));

// supprime un cookie
$cookies->remove('language');
// équivalent à
unset($cookies['language']);
```

En plus des propriétés  [[yii\web\Cookie::name|name (nom)]], [[yii\web\Cookie::value|value (valeur)]] montrées dans les exemples ci-dessus, la classe  [[yii\web\Cookie]] définit également d'autres propriétés pour représenter complètement toutes les informations de témoin de connexion disponibles, comme les propriétés [[yii\web\Cookie::domain|domain (domaine)]], [[yii\web\Cookie::expire|expire (date d'expiration)]]. Vous pouvez configurer ces propriété selon vos besoins pour préparer un témoin de connexion et ensuite l'ajouter à la collection de témoins de connexion de la réponse.

> Note: pour une meilleure sécurité, la valeur par défaut de la propriété [[yii\web\Cookie::httpOnly]] est définie à `true`. Cela permet de limiter le risque qu'un script client n'accède à un témoin de connexion protégé (si le navigateur le prend en charge). Reportez-vous à l'[article de wiki httpOnly](https://owasp.org/www-community/HttpOnly) pour plus de détails.


### Validation des témoins de connexion <span id="cookie-validation"></span>

Lorsque vous lisez ou envoyez des témoins de connexion via les composants `request` et `response` comme expliqué dans les sous-sections qui précèdent, vous appréciez la sécurité additionnelle de validation des témoins de connexion qui protège vos témoins de connexion de la modification côté client. Cela est réalisé en signant chacun des témoins de connexion  avec une valeur de hachage qui permet à l'application de dire si un témoin de connexion a été modifié ou pas du côté client. Si c'est le cas, le témoin de connexion n'est PLUS accessible via la [[yii\web\Request::cookies|collection de témoins de connexion]] du composant  `request`.

> Note: la validation des témoins de connexion ne protège que contre les effets de la modification des valeurs de témoins de connexion. Néanmoins, si un témoin de connexion ne peut être validé, vous pouvez continuer à y accéder via la variable globale `$_COOKIE`. Ceci est dû au fait que les bibliothèques de tierces parties peuvent manipuler les témoins de connexion d'une façon qui leur est propre, sans forcément impliquer la validation des témoins de connexion. 

La validation des témoins de connexion est activée par défaut. Vous pouvez la désactiver en définissant la propriété  [[yii\web\Request::enableCookieValidation]] à `false` (faux) mais nous vous recommandons fortement de ne pas le faire. 

> Note: les témoins de connexion qui sont lus/écrits directement via `$_COOKIE` et `setcookie()` ne seront PAS validés.

Quand vous utilisez la validation des témoins de connexion, vous devez spécifier une  [[yii\web\Request::cookieValidationKey |clé de validation des témoins de connexion]] gui sera utilisée pour générer la valeur de hachage dont nous avons parlé plus haut. Vous pouvez faire ça en configurant le composant  `request` dans la configuration de l'application configuration comme indiqué ci-après :

```php
return [
    'components' => [
        'request' => [
            'cookieValidationKey' => 'entrez une clé secrète ici',
        ],
    ],
];
```

> Info: la [[yii\web\Request::cookieValidationKey|clé de validation des témoins de connexion (cookieValidationKey)]] est un élément critique de la sécurité de votre application. Elle ne devrait être connue que des personnes à qui vous faites confiance. Ne le stockez pas dans le système de gestion des version. 
