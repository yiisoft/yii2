Meilleures pratiques de sécurité
================================

Ci-dessous, nous passons en revue les principes de sécurité courants et décrivons comment éviter les menaces lorsque vous développez une application Yii.

Principes de base
-----------------

Il y a deux principes essentiels quand on en vient à traiter de la sécurité des applications quelles qu'elles soient :

1. Filtrer les entrées.
2. Échapper les sorties.


### Filtrer les entrées

Filtrer les entrées signifie que les entrées ne doivent jamais être considérées comme sures et que vous devriez toujours vérifier qu'une valeur que vous avez obtenue fait réellement partie de celles qui sont autorisées. Par exemple, si nous savons qu'un tri doit être fait sur la base de trois champs `title`, `created_at` et `status`, et que ces champs sont fournis sous forme d'entrées de l'utilisateur, il vaut mieux vérifier les valeurs exactement là où nous les recevons. En terme de PHP de base, ça devrait ressembler à ceci :

```php
$sortBy = $_GET['sort'];
if (!in_array($sortBy, ['title', 'created_at', 'status'])) {
	throw new Exception('Invalid sort value.');
}
```

Dans Yii, le plus probablement, vous utilisez la [validation de formulaire](input-validation.md) pour faire de telles vérifications. 


### Échapper les sorties

Échapper les sorties signifie que, selon le contexte dans lequel vous utilisez les données, elles doivent être échappées c.-à-d. dans le contexte de HTML vous devez échapper les caractères  `<`, `>` et autres caractères similaires. Dans le contexte de JavaScript ou de SQL, il s'agira d'un jeu différent de caractères. Comme échapper tout à la main serait propice aux erreurs, Yii fournit des outils variés pour effectuer l'échappement dans différents contextes. 

Éviter les injections SQL
----------------------------

Les injections SQL se produisent lorsque le texte d'une requête est formé en concaténant des chaînes non échappées comme la suivante :

```php
$username = $_GET['username'];
$sql = "SELECT * FROM user WHERE username = '$username'";
```

Au lieu de fournir un nom d'utilisateur réel, l'attaquant pourrait donner à votre application quelque chose comme  `'; DROP TABLE user; --`.
Ce qui aboutirait à la requête  SQL suivante :

```sql
SELECT * FROM user WHERE username = ''; DROP TABLE user; --'
```

Cela est une requête tout à fait valide qui recherche les utilisateurs avec un nom vide et détruit probablement la table `user`, ce qui conduit à un site Web cassé et à une perte de données (vous faites des sauvegardes régulières, pas vrai ?).

Dans Yii la plupart des requêtes de base de données se produisent via la classe [Active Record](db-active-record.md) qui utilise correctement des instructions PDO préparées en interne. En cas d'instructions préparées, il n'est pas possible de manipuler la requête comme nous le montrons ci-dessus.

Cependant, parfois, vous avez encore besoin de [requêtes brutes](db-dao.md) ou du  [constructeur de requêtes](db-query-builder.md). Dans ce cas, vous devriez passer les données de manière sure. Si les données sont utilisées pour des valeurs de colonnes, il est préférable d'utiliser des instructions préparées :

```php
// query builder
$userIDs = (new Query())
    ->select('id')
    ->from('user')
    ->where('status=:status', [':status' => $status])
    ->all();

// DAO
$userIDs = $connection
    ->createCommand('SELECT id FROM user where status=:status')
    ->bindValues([':status' => $status])
    ->queryColumn();
```

Si les données sont utilisées pour spécifier des noms de colonne ou des noms de table, la meilleure chose à faire est d'autoriser uniquement des jeux prédéfinis de valeurs : 
 
```php
function actionList($orderBy = null)
{
    if (!in_array($orderBy, ['name', 'status'])) {
        throw new BadRequestHttpException('Only name and status are allowed to order by.')
    }
    
    // ...
}
```

Dans le cas où cela n'est pas possible, les noms de colonne et de table doivent être échappés. Yii a recours à une syntaxe spéciale pour un tel échappement qui permet de le faire d'une manière identique pour toutes les bases de données prises en charge :

```php
$sql = "SELECT COUNT([[$column]]) FROM {{table}}";
$rowCount = $connection->createCommand($sql)->queryScalar();
```

Vous pouvez obtenir tous les détails sur cette syntaxe dans la section [Échappement des noms de colonne et de table](db-dao.md#quoting-table-and-column-names).


Éviter le XSS
----------------

Le XSS ou scriptage inter site se produit lorsque la sortie n'est pas échappée correctement lors de l'envoi de code HTML au navigateur. Par exemple, si l'utilisateur peut entrer son nom, et qu'au lieu de saisir `Alexander` il saisit `<script>alert('Hello!');</script>`, chaque page qui émet sont nom sans échappement exécute le code JavaScript `alert('Hello!');` ce qui se traduit par une boîte d'alerte jaillissant dans le navigateur. Selon le site web, au lieu de quelque chose d'aussi innocent, le script pourrait envoyer des messages en votre nom ou même effectuer des transactions bancaires. L'évitement de XSS est tout à fait facile. Il y a en général deux cas :

1. Vous voulez que vos données soient transmises sous forme de texte simple. 
2. Vous voulez que vos données soient transmises sous forme de code HTML.

Si vous désirez seulement du texte simple, l'échappement est aussi simple à réaliser que ce qui suit :


```php
<?= \yii\helpers\Html::encode($username) ?>
```

Si ce doit être du code HTML vous pouvez obtenir de l'aide de HtmlPurifier:

```php
<?= \yii\helpers\HtmlPurifier::process($description) ?>
```

Notez que le processus  de HtmlPurifier est très lourd, c'est pourquoi vous devez envisager la mise en cache.

Éviter le CSRF
-----------------

La CSRF est une abréviation de cross-site request forgery (falsification de requête inter sites). L'idée est que beaucoup d'applications partent du principe que les requêtes provenant d'un navigateur sont fabriquées par l'utilisateur lui-même. Cela peut être faux. 

Par exemple,  un site web `an.example.com` a une URL  `/logout`, qui, lorsqu'elle est accédée en utilisant une simple requête GET, déconnecte l'utilisateur. Tant qu'il s'agit d'une requête de l'utilisateur lui-même, tout va bien. Mais, un jour, des gens mal intentionnés, postent `<img src="http://an.example.com/logout">` sur un forum que l'utilisateur visite fréquemment. Le navigateur ne fait pas de différence entre la requête d'une image et celle d'une page. C'est pourquoi, lorsque l'utilisateur ouvre une page avec une telle balise `img`, le navigateur envoie la requête GET vers cette URL, et l'utilisateur est déconnecté du site  `an.example.com`. 

C'est l'idée de base. D'aucuns diront que déconnecter un utilisateur n'a rien de très sérieux, mais les gens mal intentionnés peuvent faire bien plus, à partir de cette idée.  Imaginez qu'un site web possède une URL  `http://an.example.com/purse/transfer?to=anotherUser&amount=2000`. Accéder à cette URL en utilisant une requête GET, provoque le transfert de 2000 € d'un compte autorisé à l'utilisateur vers un autre compte `anotherUser`. Nous savons que le navigateur envoie toujours une requête GET pour charger une image. Nous pouvons donc modifier le code pour que seules des requêtes POST soient acceptées sur cette URL. Malheureusement, cela ne nous est pas d'un grand secours parce qu'un attaquant peut placer un peu le JavaScript à la place de la balise `<img>`, ce qui permet d'envoyer des requêtes POST sur cette URL:

Afin d'éviter la falsification des requêtes inter-sites vous devez toujours :

1. Suivre la spécification  HTTP c.-à-d. GET ne doit pas changer l'état de l'application. 
2. Tenir la protection Yii CSRF activée.

Parfois vous avez besoin de désactiver la validation CSRF pour un contrôleur ou une action. Cela peut être fait en définissant sa propriété :

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        // la validation CSRF ne sera pas appliquée à cette action ainsi qu'aux autres.
    }

}
```

Pour désactiver la validation CSRF pour des actions personnalisées vous pouvez faire :

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function beforeAction($action)
    {
        // ...définit `$this->enableCsrfValidation` ici en se basant sur quelques conditions...
        // appelle la méthode  du parent qui vérifie CSRF si une telle propriété est vraie
        return parent::beforeAction($action);
    }
}
```


Éviter l'exposition de fichiers
-------------------------------------

Par défaut, le racine du serveur web est censé pointer sur le dossier `web`, là où se trouve le fichier `index.php`. Dans le cas d'un hébergement partagé, il peut être impossible de réaliser cela et vous pouvez vous retrouver avec tout le code, configurations et journaux sous la racine du serveur web. 

Si c'est le cas, n'oubliez-pas de refuser l'accès à tout sauf au dossier `web`. Si cela n'est pas possible, envisagez d'héberger votre application ailleurs. 

Éviter les informations et des outils de débogage en mode production
----------------------------------------------------------------------

En mode  débogage, Yii présente les erreurs de façon très verbeuse, ce qui s'avère très utile en développement. Le problème est que des erreurs aussi verbeuses sont pleines de renseignement pour l'attaquant lui aussi et peuvent révéler la structure de la base de données, les valeurs de configuration et des parties de votre code. Ne faites jamais tourner vos applications avec  `YII_DEBUG` définit à  `true` dans votre fichier `index.php`.

Vous ne devriez jamais activer  Gii en production. Il pourrait être utilisé pour obtenir des informations sur la structure de la base de données, sur le code et tout simplement réécrire du code avec celui généré par Gii. 

La barre de débogage devrait être neutralisée en production sauf si vous en avez réellement besoin. Elle expose toute l'application et les détails de configuration. Si vous avez absolument besoin de cette barre, vérifier que cet accès est correctement réservé à votre adresse IP seulement.

Utilisation de connexions sécurisées via TLS
--------------------------------------------

Yii fournit des fonctionnalités qui comptent sur les témoins de connexion et/ou sur les sessions PHP. Cela peut créer des vulnérabilités dans le cas où votre connexion est compromise. Le risque est réduit si l'application utilise une connexion sécurisée via TLS. 
Reportez-vous à la documentation de votre serveur web pour des instructions sur la manière de la configurer. Vous pouvez aussi jeter un coup d'œil aux exemples de configuration du projet H5BP : 

- [Nginx](https://github.com/h5bp/server-configs-nginx)
- [Apache](https://github.com/h5bp/server-configs-apache).
- [IIS](https://github.com/h5bp/server-configs-iis).
- [Lighttpd](https://github.com/h5bp/server-configs-lighttpd).
