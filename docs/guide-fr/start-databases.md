Travailler avec les bases de données
======================

Cette section décrira comment créer une nouvelle page qui affiche des données pays récupérées dans une table de base 
de données nommée `country`. Pour ce faire, vous allez configurer une connexion à une base de données, créer une 
classe [Active Record](db-active-record.md), et définir une [action](structure-controllers.md), et créer une
[vue](structure-views.md).

Au long de ce tutoriel, vous apprendrez comment :

* Configurer une connexion à une base de données
* Définir une classe Active Record
* Requêter des données en utilisant la classe Active Record
* Afficher des données dans une vue paginée

Notez que pour finir cette section, vous aurez besoin d'avoir une connaissance basique des bases de données.
En particulier, vous devez savoir créer une base de données, et exécuter des déclarations SQL en utilisant un client de
gestion de bases de données.


Préparer la Base de Données <span id="preparing-database"></span>
--------------------

Pour commencer, créez une base de données appelée `yii2basic`, depuis laquelle vous irez chercher les données dans 
votre application.
Vous pouvez créer une base de données SQLite, MySQL, PostgreSQL, MSSQL ou Oracle, car Yii gère nativement de nombreuses
applications de base de données. Par simplicité, nous supposerons que vous utilisez MySQL dans les descriptions qui 
suivent.

Next, create a table named `country` in the base de données, and insert some sample data. You may run the following SQL statements to do so:

```sql
CREATE TABLE `country` (
  `code` CHAR(2) NOT NULL PRIMARY KEY,
  `name` CHAR(52) NOT NULL,
  `population` INT(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `country` VALUES ('AU','Australia',18886000);
INSERT INTO `country` VALUES ('BR','Brazil',170115000);
INSERT INTO `country` VALUES ('CA','Canada',1147000);
INSERT INTO `country` VALUES ('CN','China',1277558000);
INSERT INTO `country` VALUES ('DE','Germany',82164700);
INSERT INTO `country` VALUES ('FR','France',59225700);
INSERT INTO `country` VALUES ('GB','United Kingdom',59623400);
INSERT INTO `country` VALUES ('IN','India',1013662000);
INSERT INTO `country` VALUES ('RU','Russia',146934000);
INSERT INTO `country` VALUES ('US','United States',278357000);
```

A ce niveau, vous avez une base de données appelée `yii2basic`, et dedans, une table `country` comportant trois colonnes, contenant dix lignes de données.

Configurer une Connexion à la BDD <span id="configuring-db-connection"></span>
---------------------------

Avant de continuer, assurons nous que vous avez installé à la fois l'extension PHP 
[PDO](http://www.php.net/manual/fr/book.pdo.php) et le pilote PDO pour la base de données que vous utilisez (c'est
à dire `pdo_mysql` pour MySQL). C'est une exigence de base si votre application utilise une base de données 
relationnelle.

Une fois ces éléments installés, ouvrez le fichier `config/db.php` et modifiez les paramètres pour qu'ils correspondent à votre base de données. Par défaut, le fichier contient ce qui suit :

```php
<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=yii2basic',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

Le fichier `config/db.php` est un exemple type d'outil de [configuration](concept-configurations.md) basé sur un 
fichier. Ce fichier de configuration en particulier spécifie les paramètres nécessaires à la création et 
l'initialisation d'une instance de [[yii\db\Connection]] grâce à laquelle vous pouvez effectuer des requêtes SQL 
dans la base de données sous-jacente.

On peut accéder à connexion à la BDD configurée ci-dessus depuis le code de l'application vial'expression 
`Yii::$app->db`.

> Info: Le fichier `config/db.php` sera inclus par la configuration principale de l'application `config/web.php`, 
  qui spécifie comment l'instante d'[application](structure-applications.md) doit être initialisée.
  Pour plus d'informations, merci de vous référer à la section [Configurations](concept-configurations.md).


Créer un Active Record <span id="creating-active-record"></span>
-------------------------

Pour représenter et aller chercher des données dans la table `country`, créez une classe dérivée d'[Active Record](db-active-record.md) appelée `Country`, et enregistrez la dans le fichier `models/Country.php`.

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
}
```

La classe `Country` étend [[yii\db\ActiveRecord]]. Vous n'avez pas besoin d'y écrire le moindre code ! Simplement avec
le code ci-dessus, Yii devinera le nom de la table associée au nom de la class. 

> Info: Si aucune correspondance directe ne peut être faite à partir du nom de la classe, vous pouvez outrepasser la méthode [[yii\db\ActiveRecord::tableName()]] pour spécifier explicitement un nom de table.

A l'aide de la classe `Country`, vous pouvez facilement manipuler les données de la table `country`, comme dans les bribes suivantes :

```php
use app\models\Country;

// chercher toutes les lignes de la table pays et les trier par "name"
$countries = Country::find()->orderBy('name')->all();

// chercher la ligne dont la clef primaire est "US"
$country = Country::findOne('US');

// afficher "United States"
echo $country->name;

// remplace le nom du pays par "U.S.A." et le sauvegarde dans la base de données
$country->name = 'U.S.A.';
$country->save();
```

> Info: Active Record est un moyen puissant pour accéder et manipuler des données d'une base de manière orientée objet.
Vous pouvez trouver plus d'informations dans la section [Active Record](db-active-record.md). Sinon, vous pouvez 
également interagir avec une base de données en utilisant une méthode de plus bas niveau d'accès aux données appelée 
[Data Access Objects](db-dao.md).


Créer une Action <span id="creating-action"></span>
------------------

Pour exposer les données pays aux utilisateurs, vous devez créer une action. Plutôt que de placer la nouvelle action 
dans le contrôleur `site`, comme vous l'avez fait dans les sections précédentes, il est plus cohérent de créer un 
nouveau contrôleur spécifiquement pour toutes les actions liées aux données pays. Nommez ce contrôleur 
`CountryController`, et créez-y une action `index`, comme suit.

```php
<?php

namespace app\controllers;

use yii\web\Controller;
use yii\data\Pagination;
use app\models\Country;

class CountryController extends Controller
{
    public function actionIndex()
    {
        $query = Country::find();

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $query->count(),
        ]);

        $countries = $query->orderBy('name')
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'countries' => $countries,
            'pagination' => $pagination,
        ]);
    }
}
```

Enregistrez le code ci-dessus dans le fichier `controllers/CountryController.php`.

L'action `index` appelle `Country::find()`. Cette méthode Active Record construit une requête de BDD et récupère toutes
les données de la table `country`.
Pour limiter le nombre de pays retournés par chaque requête, la requête est paginée à l'aide d'un objet
[[yii\data\Pagination]]. L'objet `Pagination` dessert deux buts :

* Il ajuste les clauses `offset` et `limit` de la déclaration SQL représentée par la requête afin qu'elle en retourne
  qu'une page de données à la fois (au plus 5 colonnes par page).
* Il est utilisé dans la vue pour afficher un pagineur qui consiste en une liste de boutons de page, comme nous
  l'expliquerons dans la prochaine sous-section.

A la fin du code, l'action `index` effectue le rendu d'une vue nommée `index`, et lui transmet les données pays ainsi que les informations de pagination.


Créer une Vue <span id="creating-view"></span>
---------------

Dans le dossier `views`, commencez par créer un sous-dossier nommé `country`. Ce dossier sera utilisé pour contenir
toutes les vues rendues par le contrôleur `country`. Dans le dossier `views/country`, créez un fichier nommé
`index.php` contenant ce qui suit :

```php
<?php
use yii\helpers\Html;
use yii\widgets\LinkPager;
?>
<h1>Countries</h1>
<ul>
<?php foreach ($countries as $country): ?>
    <li>
        <?= Html::encode("{$country->name} ({$country->code})") ?>:
        <?= $country->population ?>
    </li>
<?php endforeach; ?>
</ul>

<?= LinkPager::widget(['pagination' => $pagination]) ?>
```

La vue a deux sections relatives à l'affichage des données pays. Dans la première partie, les données pays fournies
est parcourue et rendue sous forme de liste non ordonnée HTML.
Dans la deuxième partie, un widget [[yii\widgets\LinkPager]] est rendu en utilisant les informations de pagination transmises par l'action.
Le widget `LinkPager` affiche une liste de boutons de pages. Le fait de cliquer sur l'un deux rafraichit les données pays dans la page correspondante.


Essayer <span id="trying-it-out"></span>
-------------

Pour voir comment tout le code ci-dessus fonctionne, utilisez votre navigateur pour accéder à l'URL suivant :

```
http://hostname/index.php?r=country/index
```

![Liste de Pays](images/start-country-list.png)

Au début, vous verrez une page affichant cinq pays. En dessous des pays, vous verrez un pagineur avec quatre boutons.
Si vous cliquez sur le bouton "2", vous verrez la page afficher cinq autres pays de la base de données : la deuxième 
page d'enregistrements.
Observez plus attentivement et vous noterez que l'URL dans le navigateur devient

```
http://hostname/index.php?r=country/index&page=2
```

En coulisse, [[yii\data\Pagination|Pagination]] fournit toutes les fonctionnalités permettant de paginer un ensemble de données :

* Au départ, [[yii\data\Pagination|Pagination]] représente la première page, qui reflète la requête SELECT de country
  avec la clause `LIMIT 5 OFFSET 0`. Il en résulte que les cinq premiers pays seront trouvés et affichés.
* Le widget [[yii\widgets\LinkPager|LinkPager]] effectue le rendu des boutons de pages en utilisant les URLs créés par 
  [[yii\data\Pagination::createUrl()|Pagination]]. Les URLs contiendront le paramètre de requête `page`, qui représente
  les différents numéros de pages.
* Si vous cliquez sur le bouton de page "2", une nouvelle requête pour la route `country/index` sera déclenchée et 
  traitée.
  [[yii\data\Pagination|Pagination]] lit le paramètre de requête `page` dans l'URL et met le numéro de page à 2.
  La nouvelle requête de pays aura donc la clause `LIMIT 5 OFFSET 5` et retournera le cinq pays suivants pour être
  affichés.


Résumé <span id="summary"></span>
-------

Dans cette section, vous avez appris comment travailler avec une base de données. Vous avez également appris comment 
chercher et afficher des données dans des pages avec l'aide de [[yii\data\Pagination]] et [[yii\widgets\LinkPager]].

Dans la prochaine section, vous apprendrez comment utiliser le puissant outil de génération de code, appelé 
[Gii](tool-gii.md), pour vous aider à implémenter rapidement des fonctionnalités communément requises, telles que les 
opérations Créer, Lire, Mettre à Jour et Supprimer (CRUD : Create-Read-Update-Delete) pour travailler avec les données 
dans une table de base de données. En fait, le code que vous venez d'écrire peut être généré automatiquement dans Yii 
en utilisant l'outil Gii.
