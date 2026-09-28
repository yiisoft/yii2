Générer du code avec Gii
========================

Cette section décrit comment utiliser [Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide) pour générer du code qui met automatiquement en œuvre des fonctionnalités courantes de sites Web. Utiliser Gii pour auto-générer du code consiste simplement à saisir les bonnes informations en suivant les instructions affichées sur les pages Web de Gii.

Au long de ce tutoriel, vous apprendrez comment :

* Activer Gii dans votre application
* Utiliser Gii pour générer des classes Active Record (enregistrement actif)
* Utiliser Gii pour générer du code mettant en œuvre les opérations CRUD pour une table de BDD
* Personnaliser le code généré par Gii


Démarrer Gii <span id="starting-gii"></span>
------------

[Gii](https://www.yiiframework.com/extension/yiisoft/yii2-gii/doc/guide) est fourni dans Yii en tant que [module](structure-modules.md). Vous pouvez activer Gii en le 
configurant dans la propriété [[yii\base\Application::modules|modules]] de l’application. En fonction de la manière dont vous avez créé votre application, il se peut que le code suivant soit déjà fourni dans le fichier de configuration `config/web.php`:

```php
$config = [ ... ];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}
```

La configuration ci-dessus établit que dans un [environnement de développement](concept-configurations.md#environment-constants), l’application doit inclure un module appelé `gii`, qui est de classe [[yii\gii\Module]].

Si vous vérifiez le [script de démarrage](structure-entry-scripts.md) `web/index.php` de votre application, vous y trouverez les lignes suivantes, qui en gros, font que `YII_ENV_DEV` est défini à `true` (vrai).

```php
defined('YII_ENV') or define('YII_ENV', 'dev');
```

Grâce à cette ligne, votre application est en mode développement, et active Gii, suivant la configuration  vue ci-dessus. Vous pouvez maintenant accéder à Gii via l’URL suivante :

```
https://hostname/index.php?r=gii
```

> Note : si vous accédez à Gii depuis une machine autre que localhost, l’accès sera refusé par défaut pour des raisons 
> de sécurité. Vous pouvez configurer Gii pour ajouter les adresses IP autorisées comme suit,
>
```php
'gii' => [
    'class' => 'yii\gii\Module',
    'allowedIPs' => ['127.0.0.1', '::1', '192.168.0.*', '192.168.178.20'] // ajustez cela suivant vos besoins
],
```

![Gii](images/start-gii.png)


Générer une Classe Active Record <span id="generating-ar"></span>
---------------------------------

Pour générer une classe Active Record avec Gii, sélectionnez le "Model Generator" (générateur de modèle), en cliquant sur le lien dans la page d'accueil de Gii, puis complétez le formulaire comme suit :

* Table Name: `country`
* Model Class: `Country`

![Générateur de Modèles](images/start-gii-model.png)

Ensuite, cliquez sur le bouton "Preview" (prévisualiser). Vous verrez que `models/Country.php` est listé comme fichier de classe à créer. Vous pouvez cliquer sur le nom du fichier de classe pour prévisualiser son contenu.

Si vous avez déjà créé le même fichier, il sera écrasé. Cliquez sur le bouton `diff`
à côté du nom de fichier pour voir les différences entre le fichier à générer et la version existante.

![Prévisualisation du générateur de modèle](images/start-gii-model-preview.png)

Pour écraser un fichier existant, cochez la case située à côté de "overwrite" (écraser), puis cliquez sur le bouton "Generate" (générer). Pour créer un nouveau fichier, il suffit de cliquer sur "Generate". 

En fin d'opération, vous verrez une page de confirmation indiquant que le code a été généré avec succès. Si vous aviez un fichier existant, vous verrez également un message indiquant qu’il a été écrasé par le code nouvellement généré.


Générer du Code CRUD <span id="generating-crud"></span>
--------------------

CRUD signifie Create, Read, Update, and Delete (Créer, Lire, Mettre à Jour et Supprimer), soit les quatre tâches communes concernant des données sur la plupart des sites Web. Pour créer les fonctionnalités CRUD en utilisant Gii, sélectionnez le "CRUD Generator" en cliquant sur le lien dans la page d'accueil de Gii. Pour l’exemple de "country", remplissez le formulaire résultant comme suit :

* Model Class: `app\models\Country`
* Search Model Class: `app\models\CountrySearch`
* Controller Class: `app\controllers\CountryController`

![CRUD Generator](images/start-gii-crud.png)

Ensuite, cliquez sur le bouton "Preview" (prévisualiser). Vous verrez une liste de fichiers à générer, comme ci-dessous.

![CRUD Generator Preview](images/start-gii-crud-preview.png)

Si vous aviez précédemment créé les fichiers  `controllers/CountryController.php` et
`views/country/index.php` (dans la section bases de données du guide), cochez la case "overwrite" (écraser) pour les remplacer.
(Les versions précédentes ne prenaient pas totalement en charge les fonctionnalités CRUD).


Essayer <span id="trying-it-out"></span>
-------------

Pour voir comment ça fonctionne, utilisez votre navigateur pour accéder à l’URL suivant :

```
https://hostname/index.php?r=country/index
```

Vous verrez une grille de données montrant les pays de la table de la base de données. Vous pouvez trier la table, ou lui appliquer des filtres en entrant des conditions de filtrage dans les entêtes de colonnes.


Pour chaque pays affiché dans la grille, vous pouvez choisir de visualiser les détails, le mettre à jour ou le supprimer.
Vous pouvez aussi cliquer sur le bouton "Create Country" (créer un pays) en haut de la grille pour que Yii vous présente un formulaire permettant de créer un nouveau pays.

![Grille de Données Pays](images/start-gii-country-grid.png)

![Mettre à Jour un Pays](images/start-gii-country-update.png)

Ce qui suit est la liste des fichiers générés par Gii, au cas où vous souhaiteriez investiguer la manière dont ces fonctionnalités sont mises en œuvre, ou les personnaliser :

* Contrôleur: `controllers/CountryController.php`
* Modèles: `models/Country.php` et `models/CountrySearch.php`
* Vues: `views/country/*.php`

> Info: Gii est conçu pour être un outil de génération de code hautement personnalisable et extensible. L’utiliser avec sagesse peut grandement accélérer le développement de vos applications. Pour plus de détails, merci de vous référer à la section [Gii](tool-gii.md).


Résumé <span id="summary"></span>
-------

Dans cette section, vous avez appris à utiliser Gii pour générer le code qui met en œuvre une fonctionnalité CRUD complète pour des contenus stockés dans une table de base de données.

