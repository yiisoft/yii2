Hello World
============

Cette section decrit la méthode pour créer une nouvelle page "Hello" dans votre application.
Pour ce faire, vous allez créer une [action](structure-controllers.md#creating-actions) et une [vue](structure-views.md):

* L'application distribuera la requête à l'action
* et à son tour, l'action générera un rendu de la vue qui affiche le mot "Hello" à l'utilisateur.

A travers ce tutoriel, vous apprendrez trois choses :

1. Comment créer une [action](structure-controllers.md) pour répondre aux requêtes,
2. comment créer une [vue](structure-views.md) pour composer le contenu de la réponse, et
3. comment une application distribue les requêtes aux [actions](structure-controllers.md#creating-actions).


Créer une Action <span id="creating-action"></span>
------------------

Pour la tâche "Hello", vous allez créer une [action](structure-controllers.md#creating-actions) `dire` qui reçoit un paramètre
`message` de la requête et affiche ce message à l'utilisateur. Si la requête ne fournit pas de paramètre `message`, l'action affichera le message par défaut "Hello".

> Info: Les [actions](structure-controllers.md#creating-actions) sont les objets auxquels les utilisateurs peuvent directement   se référer pour les exécuter. Les actions sont groupées par [contrôleurs](structure-controllers.md). Le résultat de l'exécution d'une action est la réponse que l'utilisateur recevra.

Les actions doivent être déclarées dans des [contrôleurs](structure-controllers.md). Par simplicité, vous pouvez déclarer l'action `dire` dans le contrôleur existant `SiteController`. Ce contrôleur  est défini dans le fichier classe `controllers/SiteController.php`. Voici le début de la nouvelle action :

```php
<?php

namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // ...code existant...

    public function actionDire($message = 'Hello')
    {
        return $this->render('dire', ['message' => $message]);
    }
}
```

Dans le code ci-dessous, l'action `dire` est définie par une méthode nommée `actionDire` dans la classe `SiteController`.
Yii utilise le préfixe `action` pour faire la différence entre des méthodes actions et des méthodes non-actions dans une classe contrôleur.
Le nom suivant le préfixe `action` est associé à l'ID de l'action.

Quand vous choisissez le nom de vos actions, gardez à l'esprit comment Yii traite les IDs d'action. Les références aux IDs d'actions sont toujours effectuées en minuscules. Si un ID d'action nécessite plusieurs mots, ils seront concaténés à l'aide de tirets (par exemple `creer-commentaire`). Les noms de méthodes actions sont associés aux IDs d'actions en retirant tout tiret des IDs, en mettant la première lettre de chaque mot en majuscule, et en ajoutant un préfixe  `action` au résultat. Par exemple,
l'ID d'action `creer-commentaire` correspond à l'action nommée `actionCreerCommentaire`.

La méthode action de notre exemple prend un paramètre `$message`, dont la valeur par défaut est `"Hello"` (de la même manière qu'on affecte une valeur par défaut à n'importe quel argument de fonction ou méthode en PHP). Quand l'application reçoit une requête et détermine que l'action `dire` est responsable de gérer ladite requête, l'application peuplera ce paramètre avec le paramètre du même nom trouvé dans la requête. En d'autres termes, si la requête contient un paramètre `message` ayant pour valeur `"Goodbye"`, la variable `$message` au sein de l'action recevra cette valeur.

Au sein de la méthode action, [[yii\web\Controller::render()|render()]] est appelé pour effectuer le rendu d'un fichier [vue](structure-views.md) appelé `dire`. Le paramètre `message` est également transmis à la vue afin qu'il puisse y être utilisé. Le résultat du rendu est renvoyé à l'utilisateur par la méthode action. Ce résultat sera reçu par l'application et présenté à l'utilisateur dans le navigateur (en tant qu'élément d'une page HTML complète). 


Créer une Vue <span id="creating-view"></span>
---------------

Les [vues](structure-views.md) sont des scripts qu'on écrit pour générer le contenu d'une réponse.
Pour la tâche "Hello", vous allez créer une vue `dire` qui affiche le paramètre `message` reçu de la méthode action, et passé par l'action à la vue :

```php
<?php
use yii\helpers\Html;
?>
<?= Html::encode($message) ?>
```

La vue `dire` doit être enregistrée dans le fichier `views/site/dire.php`. Quand la méthode [[yii\web\Controller::render()|render()]]
est appelée dans une action, elle cherchera un fichier PHP nommé `views/ControllerID/NomDeLaVue.php`.

Notez que dans le code ci-dessus, le paramètre `message` est [[yii\helpers\Html::encode()|Encodé-HTML]]
avant d'être affiché. Cela est nécessaire car le paramètre vient de l'utilisateur, le rendant vulnérable aux [attaques cross-site scripting (XSS)](http://fr.wikipedia.org/wiki/Cross-site_scripting) en intégrant du code Javascript malicieux dans le paramètre.

Bien entendu, vous pouvez insérer plus de contenu dans la vue `dire`. Le contenu peut être des tags HTMML, du texte brut, ou même des expressions PHP.
En réalité, la vue `dire` est simplement un script PHP exécuté par la méthode [[yii\web\Controller::render()|render()]].
Le contenu affiché par le script de vue sera renvoyé à l'application en tant que résultat de réponse. L'application renverra à son tour ce résultat à l'utilisateur.


Essayer <span id="trying-it-out"></span>
-------------

Après avoir créé l'action et la vue, vous pouvez accéder à la nouvelle page en accédant à l'URL suivant :

```
http://hostname/index.php?r=site/dire&message=Hello+World
```

![Hello World](images/start-hello-world.png)

Le résultat de cet URL sera une page affichant "Hello World". La page a les mêmes entête et pied de page que les autres pages de l'application. 

Si vous omettez le paramètre `message` dans l'URL, La page devrait simplement afficher "Hello". C'est parce que `message` est passé en paramètre de la méthode `actionDire()`, et quand il est omis, la valeur par défaut `"Hello"` sera employée à la place.

> Info: L nouvelle page a les mêmes entête et pied de page que les autres pages parce que la méthode [[yii\web\Controller::render()|render()]] intègrera automatiquement le résultat de la vue `dire` dans une pseudo [mise en page](structure-views.md#layouts) qui dans notre cas est située dans `views/layouts/main.php`.

Le paramètre `r` dans l'URL ci-dessus nécessite plus d'explications. Il signifie [route](runtime-routing.md), un ID unique commun toute l'application qui fait référence à une action. Le format de la route est `IDContrôleur/IDAction`. Quand l'application reçoit une requête, elle vérifie ce paramêtre, en utilisant la partie `IDContrôleur` pour déterminer quel classe contrôleur doit être instanciée pour traiter la requête. Ensuite, le contrôleur utilisera la partie `IDAction` pour déterminer quelle action doit être instanciée pour effectuer le vrait travail. Dans ce cas d'exemple, la route `site/dire`
sera comprise comme la classe contrôleur `SiteController` et l'action `dire`. Il en resultera que la méthode `SiteController::actionDire()` sera appelée pour traiter la requête.

> Info: De même que les actions, les contrôleurs ont des IDs qui les identifient de manière unique dans une application.
  Les IDs de contrôleurs emploie les mêmes règles de nommage que les IDs d'actions. Les noms de classes Contrôleurs dérivent
  des IDs de contrôleurs en retirant les tirets des IDs, en mettant la première lettre de chaque mot en majuscule,
  et en suffixant la chaîne résultante du mot `Controller`. Par exemple, l'ID de contrôlleur `poster-commentaire` correspond
  au nom de classe contrôleur `PosterCommentaireController`.


Résumé <span id="summary"></span>
-------

Dans cette section, vous avez touché aux parties contrôleur et vue du patron de conception MVC.
Vous avez créé une action au sein d'un contrôleur pour traiter une requête spécifique. Vous avez également créé une vue pour composer le contenu de la réponse. Dans ce simple exemple, aucun modèle n'a été impliqué car les seules données utilisées étaient le paramètre `message`.

Vous avez également appris ce que sont les routes dans Yii, qu'elles font office de pont entre les requêtes utilisateur et les actions des contrôleurs.

Dans la prochaine section, vous apprendrez comment créer un modèle, et ajouter une nouvelle page contenant un formulaire HTML.
