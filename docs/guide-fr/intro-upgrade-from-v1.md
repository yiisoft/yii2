Mise à jour depuis la version 1.1
=================================

Il y a beaucoup de différences entre les versions 1.1 et 2.0 de Yii, le framework ayant été complètement réécrit pour la 2.0. 
En conséquence, la mise à jour depuis la version 1.1 n'est pas aussi triviale que la mise à jour entre deux versions mineures. Dans ce guide, vous
trouverez les principales différences entre les deux versions. 

Si vous n'avez pas utilisé Yii 1.1 avant, vous pouvez ignorer cette section et passer directement à la partie "[Mise en route] (start-installation.md)". 

Merci de noter que Yii 2.0 introduit plus de nouvelles fonctionnalités que celles abordées ici. Il est fortement recommandé 
de lire tout le guide de référence pour en apprendre davantage. Il y a des chances que 
certaines fonctionnalités, que vous aviez préalablement développées pour vous, fassent désormais partie du code du noyau.


Installation
------------

Yii 2.0 exploite pleinement [Composer] (https://getcomposer.org/), le gestionnaire de paquet PHP. L'installation 
du framework, ainsi que des extensions, sont gérées par Composer. Merci de lire la partie 
[Starting from Basic App](start-basic.md) pour apprendre comment installer Yii 2.0. Si vous voulez 
créer de nouvelles extensions, ou rendre vos extensions existantes 1.1 compatibles 2.0, merci de lire
la partie [Créer des extensions](extend-creating-extensions.md) de ce guide.


Pré-requis PHP
--------------

Yii 2.0 requiert PHP 5.4 ou plus, ce qui est une grosse amélioration par rapport à PHP 5.2 qui était requis pour Yii 1.1.

Par conséquent, il y a beaucoup de différences au niveau du langage pour lesquelles vous devriez prêter attention. 
Voici un résumé des principaux changements concernant PHP:

- [Espaces de noms](http://php.net/manual/fr/language.namespaces.php).
- [Fonctions anonymes](http://php.net/manual/fr/functions.anonymous.php).
- Syntaxe courte pour les tableaux : `[...elements...]` est utilisé au lieu de `array(...elements...)`.
- Syntaxe courte pour echo : `<?=` est utilisé dans les vues. Cela ne pose aucun problème à partir de PHP 5.4.
- [Classes SPL et interfaces](http://php.net/manual/fr/book.spl.php).
- [Late Static Bindings (résolution statique à la volée)](http://php.net/manual/fr/language.oop5.late-static-bindings.php).
- [Date et heure](http://php.net/manual/fr/book.datetime.php).
- [Traits](http://php.net/manual/fr/language.oop5.traits.php).
- [intl](http://php.net/manual/fr/book.intl.php). Yii 2.0 utilise l'extension PHP `intl`
  pour les fonctionnalités d'internationalisation.


Espaces de Noms
---------------

Le changement le plus évident dans Yii 2.0 est l'utilisation des espaces de noms. La majorité des classes du noyau
utilise les espace de noms, par exemple, `yii\web\Request`. Le préfixe «C» n'est plus utilisé dans les noms de classe.
Le schéma de nommage suit maintenant la structure des répertoires. Par exemple, `yii\web\Request` 
indique que le fichier de classe correspondant est `web/Request.php` dans le dossier du framework. 

(Vous pouvez utiliser n'importe quelle classe du noyau sans inclure explicitement le fichier correspondant, grâce au
chargeur de classe de Yii.)


Composants et objets 
-------------------- 

Yii 2.0 décompose la classe `CComponent` 1.1 en deux classes: [[yii\base\Object]] et [[yii\base\Component]]. 
Le classe [[yii\base\Object|Object]] est une classe de base légère qui permet de définir les [Propriétés de l'objet] (concept properties.md) 
via des accesseurs. La classe [[yii\base\Component|Component]] est une sous classe de [[yii\base\Object|Object]] et supporte 
les [Evénements] (concept events.md) et les [Comportements] (concept behaviors.md). 

Si votre classe n'a pas besoin des événements et des comportements, vous devriez envisager d'utiliser 
[[yii\base\Object|Object]] comme classe de base. C'est généralement le cas pour les classes qui représentent
une structures de données basique.


Configuration d'Objet
---------------------

La classe [[yii\base\Object|Object]] introduit une manière uniforme pour configurer les objets. Toute sous classe
de [[yii\base\Object|Object]] doit déclarer son constructeur (si besoin) de la manière suivante afin qu'elle 
puisse être configurée correctement:

```php
class MyClass extends \yii\base\Object
{
    public function __construct($param1, $param2, $config = [])
    {
        // ... initialisation avant que la configuration soit appliquée

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

        // ... initialization après que la configuration soit appliquée
    }
}
```

Dans ce qui précède, le dernier paramètre du constructeur doit être un tableau de configuration 
qui contient des entrées nom-valeur pour initialiser les propriétés à la fin du constructeur. 
Vous pouvez remplacer la méthode [[yii\base\Object::init()|init()]] pour le travail d'initialisation qui doit être fait après 
que la configuration ait été appliquée.

En suivant cette convention, vous serez en mesure de créer et de configurer de nouveaux objets 
en utilisant un tableau de configuration:

```php
$object = Yii::createObject([
    'class' => 'MyClass',
    'property1' => 'abc',
    'property2' => 'cde',
], [$param1, $param2]);
```

Plus de détails sur les configurations peuvent être trouvés dans la partie [Configurations d'objet](concept-configurations.md).


Evénements
----------

Avec Yii 1, les événements étaient créés par la définition d'une  méthode `on` (par exemple `onBeforeSave`). Avec Yii 2, vous pouvez maintenant utiliser n'importe quel nom de l'événement. Vous déclenchez un événement en appelant 
la méthode [[yii\base\Component::trigger()|trigger()]] :

```php
$event = new \yii\base\Event;
$component->trigger($eventName, $event);
```

Pour attacher un gestionnaire à un événement, utilisez la méthode [[yii\base\Component::on()|on()]]:

```php
$component->on($eventName, $handler);
// Pour détacher le gestionnaire, utilisez :
// $component->off($eventName, $handler);
```
Il y a de nombreuses améliorations dans la gestion des événements. Pour plus de détails, merci de lire la partie [Evénements](concept events.md).


Alias
-----

Yii 2.0 étend l'utilisation des alias aux fichiers/répertoires et aux URL. Yii 2.0 impose maintenant 
aux alias de commencer par le caractère `@`, pour différencier les alias de fichiers/répertoires ou URL. 
Par exemple, l'alias `@yii` fait référence au répertoire d'installation de Yii. Les alias ​​sont 
supportés dans la plupart du code de Yii. Par exemple, [[yii\caching\FileCache::cachePath]] peut prendre 
à la fois un alias et un chemin de répertoire normal.

Un alias est aussi étroitement liée aux espaces de noms des classes. Il est recommandé de définir
un alias pour chaque espace de nom racine, ce qui vous permet d'utiliser le chargeur automatique de classe de Yii sans 
sans devoir en faire d'avantage. Par exemple, vu que `@yii` fait référence au dossier d'installation de Yii, 
une classe comme `yii\web\Request` peut être chargée automatiquement. Si vous utilisez une librairie tierce, 
telle que Zend Framework, vous pouvez définir un alias de chemin `@Zend` qui fera référence au dossier
d'installation de Zend Framework. Une fois que vous avez fait cela, Yii sera aussi en mesure de charger automatiquement une classe de ce framework.

Pour en savoir plus, consultez la partie [Alias](concept-aliases.md).


Vues
----

Le changement le plus significatif à propos des vues dans Yii 2 est que la variable spéciale `$this` dans une vue ne fait plus référence au
contrôleur ou widget. Au lieu de cela, `$this` correspond maintenant à un objet *vue*, un nouveau concept 
introduit dans la version 2.0. L'objet *vue* est de type [[yii\web\View]], et représente la partie vue 
du modèle MVC. Si vous souhaitez accéder au contrôleur ou widget dans une vue, vous pouvez utiliser `$this->context`.

Pour afficher une vue depuis une autre vue, utilisez `$this->render()`, et non `$this->renderPartial()`. Le résultat retourné par la méthode `render()` doit être explictement envoyé à la sortie, en effet `render()` retournera la vue au lieu de l'afficher. Par exemple :

```php
echo $this->render('_item', ['item' => $item]);
```

Outre l'utilisation de PHP comme langage principal de gabarit, Yii 2.0 supporte également
deux moteurs de gabarit populaires : Smarty et Twig. Le moteur de gabarit Prado n'est plus supporté. 
Pour utiliser ces moteurs de gabarit, vous devez configurer le composant `view` de l'application en définissant la propriété
[[yii\base\View::$renderers|View::$renderers]]. Merci de lire la partie [Moteur de gabarit](tutorial-template-engines.md) pour en savoir plus.


Modèles
-------

Yii 2.0 utilise la classe [[yii\base\Model]] comme modèle de base, similaire à la classe `CModel` dans la version 1.1.
La classe `CFormModel` a été supprimée. Vous pouvez, à la place, étendre la classe [[yii\base\Model]] afin de créer une classe modèle pour un formulaire.

Yii 2.0 introduit une nouvelle méthode appelée [[yii\base\Model::scenarios()|scenarios()]] pour déclarer 
les scénarios pris en charge, et indiquer dans quel scénario un attribut doit être validé, peut être considéré comme sûr ou non, etc. Par exemple:

```php
public function scenarios()
{
    return [
        'backend' => ['email', 'role'],
        'frontend' => ['email', '!name'],
    ];
}
```

Dans ce qui précède, deux scénarios sont déclarés: `backend` et `frontend`. Pour le scénario `backend` les 
propriétés `email` et `role` sont sûres et peuvent être affectées massivement. Pour le scénario `frontend`, 
`email` peut être affectée massivement tandis que `role` ne peut pas. `email` et `rôle` doivent être validées en utilisant des règles.

La méthode [[yii\base\Model::rules()|rules()]] est toujours utilisée pour déclarer les règles de validation. Remarque : suite à l'introduction de la méthode [[yii\base\Model::scenarios()|scenarios()]], le validateur `unsafe` n'as plus de raison d'être.

Dans la plupart des cas, vous n'avez pas besoin de surcharger la méthode [[yii\base\Model::scenarios()|scenarios()]]
lorsque les scénarios existants sont déclarés via la méthode [[yii\base\Model::rules()|rules()]], et il n'y a pas besoin de déclarer de propriétés `unsafe`.

Pour en savoir plus sur les modèles, merci de lire la partie [Modèles](basic-models.md).


Contrôleurs
-----------

Yii 2.0 utilise la classe [[yii\web\Controller]] comme classe de base des contrôleurs, similaire à la classe `CWebController` dans la version Yii 1.1.
[[yii\base\Action]] est la classe de base pour les actions.

L'impact le plus évident de ces changements sur votre code est qu'une action de contrôleur doit retourner le contenu 
que vous voulez afficher au lieu de l'envoyer vers la sortie :

```php
public function actionView($id)
{
    $model = \app\models\Post::findOne($id);
    if ($model) {
        return $this->render('view', ['model' => $model]);
    } else {
        throw new \yii\web\NotFoundHttpException;
    }
}
```

Merci de lire la partie [Contrôleurs](structure-controllers.md) pour plus de détails.


Widgets
-------

Yii 2.0 utilise la classe [[yii\base\Widget]] comme classe de base pour les widgets, similaire à la classe `CWidget` de Yii 1.1.

Pour avoir un meilleur support du framework dans les EDI, Yii2 introduit une nouvelle syntaxe pour utiliser les widgets. Les methodes statiques
[[yii\base\Widget::begin()|begin()]], [[yii\base\Widget::end()|end()]], et [[yii\base\Widget::widget()|widget()]]
ont été créées et sont utilisables comme suit :

```php
use yii\widgets\Menu;
use yii\widgets\ActiveForm;

// Remarque : vous devez utiliser echo pour afficher le résultat
echo Menu::widget(['items' => $items]);

// Utilisation d'un tableau pour initialiser les propriétés de l'objet
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal'],
    'fieldConfig' => ['inputOptions' => ['class' => 'input-xlarge']],
]);
... champs du formulaire ici ...
ActiveForm::end();
```

Merci de lire la partie [Widgets](structure-widgets.md) pour en savoir plus.


Thèmes
------

Les thèmes fonctionnent tout à fait différemment dans la version 2.0. Ils sont maintenant basés sur un mécanisme de mappage de chemin qui mappe un chemin
de fichier de vue à un chemin de fichier de vue thématisée. Par exemple, si le mappage pour un thème est 
`['/web/views' => '/web/themes/basic']`, alors la version thématisée du fichier de vue 
`/web/views/site/index.php` sera `/web/themes/basic/site/index.php`. Pour cette raison, les thèmes peuvent maintenant 
être appliqués à n'importe quel fichier de vue, même une vue utilisée en dehors du contexte d'un contrôleur ou d'un widget. 

En outre, il n'y a plus de composant `CThemeManager`. A la place, `theme` est une propriété configurable du composant `view` 
de l'application. 

Merci de lire la partie [Thématisation](tutoriel theming.md) pour plus de détails.


Applications en ligne de commande
---------------------------------

Les applications en ligne de commande (console) sont désormais organisées en contrôleurs, comme les applications Web. ces contrôleurs
doivent étendre la classe [[yii\console\Controller]], similaire à la classe `CConsoleCommand` de la version 1.1.

Pour exécuter une commande console, utilisez `yii <route>`, où `<route>` correspond à une route vers un contrôleur
(par exemple `sitemap/index`). Les arguments anonymes supplémentaires sont passés comme paramètres à
l'action du contrôleur correspondant, alors que les arguments nommés sont analysés selon 
les options déclarées dans la méthode [[yii\console\Controller::options()]].

Yii 2.0 prend en charge la génération automatique d'aide à partir des blocs de commentaire.

Merci de lire la partie [Commandes console](tutoriel-console.md) pour plus de détails.


I18N
----

Yii 2.0 supprime les fonctionnalités internes de formattage de dates et nombres, en faveur du [module PHP PECL intl] (http://pecl.php.net/package/intl). 

La traduction de message est désormais effectuée via le composant d'application `i18n`. 
Ce composant gère un ensemble de sources de messages, ce qui vous permet d'utiliser différentes 
sources de messages en fonction de catégories.

Merci de lire la partie [Internationalisation](tutoriel i18n.md) pour plus de détails.


Filtres d'action
----------------

Les filtres d'action sont maintenant implémentés comme des comportements. Pour définir un nouveau filtre personnalisé, étendez la classe [[yii\base\ActionFilter]]. Pour utiliser un filtre, déclarez le
comme un comportement du contrôleur.  Par exemple, pour utilser le filtre [[yii\filters\AccessControl]], vous aurez le code suivant dans le contrôleur :

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => 'yii\filters\AccessControl',
            'rules' => [
                ['allow' => true, 'actions' => ['admin'], 'roles' => ['@']],
            ],
        ],
    ];
}
```

Merci de lire la partie [Filtres](runtime-filtering.md) pour plus de détails.


Ressources
----------

Yii 2.0 introduit un nouveau concept de packet de ressources (*asset bundle*) qui remplace le concept de gestionnaire de ressources (*asset manager*) de la version 1.1.

Un packet de ressources est une collection de fichiers (par exemple : fichier JavaScript, CSS, image, etc.)
dans un dossier. Chaque paquet est représenté par une classe étendant [[yii\web\AssetBundle]].
En *enregistrant* un packet via [[yii\web\AssetBundle::register()]], vous rendez les ressources du packet accessibles via le Web. Contrairement à Yii 1.1, la page *enregistrant* le paquet
contiendra automatiquement les références vers les fichiers déclarés dans le paquet.

Merci de lire la partie [Assets](structure-assets.md) pour plus de détails.


Helpers
-------

Yii 2.0 introduces many commonly used static helper classes, including.

* [[yii\helpers\Html]]
* [[yii\helpers\ArrayHelper]]
* [[yii\helpers\StringHelper]]
* [[yii\helpers\FileHelper]]
* [[yii\helpers\Json]]
* [[yii\helpers\Security]]

Please refer to the [Helper Overview](helper-overview.md) section for more details.

Forms
-----

Yii 2.0 introduces the *field* concept for building a form using [[yii\widgets\ActiveForm]]. A field
is a container consisting of a label, an input, an error message, and/or a hint text.
A field is represented as an [[yii\widgets\ActiveField|ActiveField]] object.
Using fields, you can build a form more cleanly than before:

```php
<?php $form = yii\widgets\ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>
<?php yii\widgets\ActiveForm::end(); ?>
```

Please refer to the [Creating Forms](input-forms.md) section for more details.


Query Builder
-------------

In 1.1, query building was scattered among several classes, including `CDbCommand`,
`CDbCriteria`, and `CDbCommandBuilder`. Yii 2.0 represents a DB query in terms of a [[yii\db\Query|Query]] object
that can be turned into a SQL statement with the help of [[yii\db\QueryBuilder|QueryBuilder]] behind the scene.
For example:

```php
$query = new \yii\db\Query();
$query->select('id, name')
      ->from('user')
      ->limit(10);

$command = $query->createCommand();
$sql = $command->sql;
$rows = $command->queryAll();
```

Best of all, such query building methods can also be used when working with [Active Record](db-active-record.md).

Please refer to the [Query Builder](db-query-builder.md) section for more details.


Active Record
-------------

Yii 2.0 introduces a lot of changes to [Active Record](db-active-record.md). The two most obvious ones involve
query building and relational query handling.

The `CDbCriteria` class in 1.1 is replaced by [[yii\db\ActiveQuery]] in Yii 2. That class extends from [[yii\db\Query]], and thus
inherits all query building methods. You call [[yii\db\ActiveRecord::find()]] to start building a query:

```php
// To retrieve all *active* customers and order them by their ID:
$customers = Customer::find()
    ->where(['status' => $active])
    ->orderBy('id')
    ->all();
```

To declare a relation, simply define a getter method that returns an [[yii\db\ActiveQuery|ActiveQuery]] object.
The property name defined by the getter represents the relation name. For example, the following code declares
an `orders` relation (in 1.1, you would have to declare relations in a central place `relations()`):

```php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany('Order', ['customer_id' => 'id']);
    }
}
```

Now you can use `$customer->orders` to access a customer's orders from the related table. You can also use the following code
to perform an on-the-fly relational query with a customized query condition:

```php
$orders = $customer->getOrders()->andWhere('status=1')->all();
```

When eager loading a relation, Yii 2.0 does it differently from 1.1. In particular, in 1.1 a JOIN query
would be created to select both the primary and the relational records. In Yii 2.0, two SQL statements are executed
without using JOIN: the first statement brings back the primary records and the second brings back the relational
records by filtering with the primary keys of the primary records.

Instead of returning [[yii\db\ActiveRecord|ActiveRecord]] objects, you may chain the [[yii\db\ActiveQuery::asArray()|asArray()]]
method when building a query to return a large number of records. This will cause the query result to be returned
as arrays, which can significantly reduce the needed CPU time and memory if large number of records . For example,

```php
$customers = Customer::find()->asArray()->all();
```

Another change is that you can't define attribute default values through public properties anymore.
If you need those, you should set them in the init method of your record class.

```php
public function init()
{
    parent::init();
    $this->status = self::STATUS_NEW;
}
```

There where some problems with overriding the constructor of an ActiveRecord class in 1.1. These are not present in
version 2.0 anymore. Note that when adding parameters to the constructor you might have to override [[yii\db\ActiveRecord::instantiate()]].

There are many other changes and enhancements to Active Record. Please refer to
the [Active Record](db-active-record.md) section for more details.


User and IdentityInterface
--------------------------

The `CWebUser` class in 1.1 is now replaced by [[yii\web\User]], and there is no more
`CUserIdentity` class. Instead, you should implement the [[yii\web\IdentityInterface]] which
is much more straightforward to use. The advanced application template provides such an example.

Please refer to the [Authentication](security-authentication.md), [Authorization](security-authorization.md), and [Advanced Application Technique](tutorial-advanced-app.md) sections for more details.


URL Management
--------------

URL management in Yii 2 is similar to that in 1.1. A major enhancement is that URL management now supports optional
parameters. For example, if you have a rule declared as follows, then it will match
both `post/popular` and `post/1/popular`. In 1.1, you would have had to use two rules to achieve
the same goal.

```php
[
    'pattern' => 'post/<page:\d+>/<tag>',
    'route' => 'post/index',
    'defaults' => ['page' => 1],
]
```

Please refer to the [Url manager docs](url.md) section for more details.

Using Yii 1.1 and 2.x together
------------------------------

If you have legacy Yii 1.1 code that you want to use together with Yii 2.0, please refer to
the [Using Yii 1.1 and 2.0 Together](extend-using-v1-v2.md) section.

