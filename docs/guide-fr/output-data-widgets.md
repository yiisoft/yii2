Composants graphiques d'affichage de données
============================================

Yii fournit un jeu de [composants graphiques](structure-widgets.md) utilisables pour afficher des données. Tandis que le componsant graphique [DetailView](#detail-view) (vue détaillée) peut être utilisé pour afficher un enregistrement unique, les composants graphiques [ListView](#list-view) (vue en liste) et [GridView](#grid-view) (vue en grille) peuvent être utilisés pour afficher plusieurs enregistrements en liste ou en grille assortis de fonctionnalités telles que la pagination, le tri et le filtrage.


Vue détaillée (classe *DetailView*) <span id="detail-view"></span>
----------------------------------

Le composant graphique [[yii\widgets\DetailView|DetailView]] (vue détaillée) affiche les détails d'un [[yii\widgets\DetailView::$model|modèle]] de données unique.

Il est le plus adapté à l'affichage d'un modèle dans un format courant (p. ex. chacun des attributs du modèle est affiché en tant que ligne d'une grille). Le modèle peut être, soit une instance, ou une classe fille, de [[\yii\base\Model]] telle que la classe [ActiveRecord](db-active-record.md), soit un tableau associatif.

*DetailView* utilise la propriété [[yii\widgets\DetailView::$attributes|$attributes]] pour déterminer quels attributs du modèle doivent être affichés et comment ils doivent être formatés. Reportez-vous à la section [formatage des données](output-formatting.md) pour des informations sur les options de formatage.

Une utilisation typique de *DetailView* ressemble à ce qui suit : 

```php
echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'title',               // attribut title (en texte simple)
        'description:html',    // attribut description formaté en HTML
        [                      // le nom du propriétaire du modèle
            'label' => 'Owner',
            'value' => $model->owner->name,
        ],
        'created_at:datetime', // date de création formaté comme une date/temps
    ],
]);
```

Vue en liste (class *ListView*)<span id="list-view"></span>
------------------------------

Le composant graphique [[yii\widgets\ListView|ListView]] (vue en liste) est utilisé pour afficher des données issues d'un [fournisseur de données](output-data-providers.md). Chacun des modèles est rendu en utilisant le composant [[yii\widgets\ListView::$itemView|ListView]] (vue en liste) spécifié. Comme ce composant fournit des fonctionnalités telles que la pagination, le tri et le filtrage de base, il est pratique, à la fois pour afficher des informations et pour créer des interfaces utilisateur de gestion des données.

Typiquement, on l'utilise comme ceci :

```php
use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
]);
```

Le fichier de vue,  `_post`, contient ce qui suit : 

```php
<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>
<div class="post">
    <h2><?= Html::encode($model->title) ?></h2>

    <?= HtmlPurifier::process($model->text) ?>
</div>
```

Dans le fichier ci-dessus, le modèle de données courant est disponible comme `$model`. En outre, les variables suivantes sont disponibles :

- `$key`: mixed, la valeur de la clé associée à l'item de données
- `$index`: integer, l'index commençant à zéro de l'item de données dans le tableau d'items retourné par le fournisseur de données.
- `$widget`: ListView, l'instance de ce composant graphique.

Si vous avez besoin de passer des données additionnelles à chacune des vues, vous pouvez utiliser la propriété [[yii\widgets\ListView::$viewParams|$viewParams]] pour passer des paires clé valeur, comme ceci :

```php
echo ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_post',
    'viewParams' => [
        'fullView' => true,
        'context' => 'main-page',
        // ...
    ],
]);
```

Celles-ci sont alors disponibles aussi dans la vue en tant que variables. 


Vue en grille (classe *GridView*)<span id="grid-view"></span>
--------------------------------

La vue en grille, ou composant [[yii\grid\GridView|GridView]], est un des composants les plus puissants de Yii. Ce composant est extrêmement utile si vous devez rapidement construire l'interface d'administration du système. Il accepte des données d'un [fournisseur de données](output-data-providers.md) et rend chacune des lignes en utilisant un jeu de [[yii\grid\GridView::columns|columns]] (colonnes), présentant ainsi l'ensemble des données sous forme d'une grille.

Chacune des lignes de la grille représente un item unique de données, et une colonne représente ordinairement un attribut de l'item (quelques colonnes peuvent correspondre à des expressions complexes utilisant les attributs ou à un texte statique).

Le code minimal pour utiliser le composant *GridView* se présente comme suit :

```php
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

$dataProvider = new ActiveDataProvider([
    'query' => Post::find(),
    'pagination' => [
        'pageSize' => 20,
    ],
]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
]);
```

Le code précédent crée un fournisseur de données, puis utilise le composant *GridView* pour afficher chacun des attributs dans une ligne en le prélevant dans le fournisseur de données. La grille affichée est doté de fonctionnalités de pagination et de tri sans autre intervention. 


### Colonnes de la grille

Les colonnes de la grille sont exprimées en terme de classe [[yii\grid\Column]], qui sont configurées dans la propriété [[yii\grid\GridView::columns|columns]] (colonnes) de la configuration du composant *GridView*. En fonction du type de colonne et des réglages, celles-ci sont en mesure de présenter les données différemment. La classe par défaut est [[yii\grid\DataColumn]] (colonne de données), qui représente un attribut de modèle et peut être triée et filtrée. 

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        // colonnes simples définies par les données contenues dans le fournisseur de données
        // les données de la colonne du modèle sont utilisées
        'id',
        'username',
        // un exemple plus complexe
        [
            'class' => 'yii\grid\DataColumn', // peut être omis car c'est la valeur par défaut
            'value' => function ($data) {
                return $data->name; // $data['name'] pour une donnée tableau p. ex. en utilisant SqlDataProvider.
            },
        ],
    ],
]);
```

Notez que si la partie [[yii\grid\GridView::columns|columns]] de la configuration n'est pas spécifiée, Yii essaye de montrer toutes les colonnes possibles du modèle du fournisseur de données.


### Classes de colonne

Les colonnes du composant *GridView* peuvent être personnalisées en utilisant différentes classes de colonnes : 

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn', // <-- ici
            // vous pouvez configurer des propriété additionnelles ici
        ],
```

En plus des classes de colonne fournies par Yii que nous allons passer en revue ci-après, vous pouvez créer vos propres classes de colonne. 

Chacune des classes de colonne étend la classe [[yii\grid\Column]] afin que quelques options communes soient disponibles lors de la configuration des colonnes.

- [[yii\grid\Column::header|header]] permet de définir une ligne d'entête
- [[yii\grid\Column::footer|footer]] permet de définir le contenu d'une ligne de pied de grille
- [[yii\grid\Column::visible|visible]] définit si la colonne doit être visible.
- [[yii\grid\Column::content|content]] vous permet de passer une fonction de rappel PHP valide qui retourne les données d'une ligne. Le format est le suivant :

  ```php
  function ($model, $key, $index, $column) {
      return 'a string';
  }
  ```

Vous pouvez spécifier différentes options HTML de conteneurs en passant des tableaux à :

- [[yii\grid\Column::headerOptions|headerOptions]]
- [[yii\grid\Column::footerOptions|footerOptions]]
- [[yii\grid\Column::filterOptions|filterOptions]]
- [[yii\grid\Column::contentOptions|contentOptions]]


#### Colonne de données (*DataColumn*) <span id="data-column"></span>

La classe [[yii\grid\DataColumn|DataColumn]] (colonne de données) est utilisée pour afficher et trier des données. C'est le type de colonne par défaut, c'est pourquoi la spécification de la classe peut être omise.

Le réglage principal de la colonne de données est celui de sa propriété [[yii\grid\DataColumn::format|format]]. Ses valeurs correspondent aux méthodes du [composant d'application](structure-application-components.md) `formatter` qui est de classe [[\yii\i18n\Formatter|Formatter]] par défaut :

```php
echo GridView::widget([
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'text'
        ],
        [
            'attribute' => 'birthday',
            'format' => ['date', 'php:Y-m-d']
        ],
    ],
]);La valeur de la colonne est passée en tant que premier argument
```

Dans cet exemple, `text` correspond à la méthode [[\yii\i18n\Formatter::asText()]]. La valeur de la colonne est passée en tant que premier argument. Dans la deuxième définition de colonne, `date` correspond à la méthode [[\yii\i18n\Formatter::asDate()]]. La valeur de la colonne est passée en tant que premier argument tandis que 'php:Y-m-d' est utilisé en tant que valeur du deuxième argument.

Pour une liste complète de tous les formateurs, reportez-vous à la section [Formatage des données](output-formatting.md).

Pour configurer des colonnes de données, il y a aussi un format raccourci qui est décrit dans la documentation de l'API de [[yii\grid\GridView::columns|columns]].


#### Colonne d'actions (*ActionColumn*)

La classe [[yii\grid\ActionColumn|ActionColumn]] (colonne d'action) affiche des boutons d'action tels que mise à jour ou supprimer pour chacune des lignes. 

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\ActionColumn',
            // vous pouvez configurer des propriétés additionnelles ici
        ],
```

Les propriétés additionnelles configurables sont :

- [[yii\grid\ActionColumn::controller|controller]] qui est l'identifiant du contrôleur qui prend en charge l'action. Si cette propriété n'est pas définie, le contrôleur courant est utilisé. 
- [[yii\grid\ActionColumn::template|template]] qui définit le modèle utilisé pour composer chacune des cellules dans la colonne d'actions. Les marqueurs (textes à l'intérieur d'accolades) sont traités comme des identifiants d'action (aussi appelé *noms de bouton* dans le contexte d'une colonne d'actions. Il sont remplacés par les fonctions de rappel correspondantes spécifiées dans la propriété [[yii\grid\ActionColumn::$buttons|buttons]]. Par exemple, le marqueur `{view}` sera remplacé par le résultat de la fonction de rappel `buttons['view']`. Si une fonction de rappel n'est pas trouvée, le texte est remplacé par une chaîne vide. Les marqueurs par défaut sont `{view} {update} et {delete}`.
- [[yii\grid\ActionColumn::buttons|buttons]] est un tableau de fonctions de rappel pour le rendu des boutons. Les clés du tableau sont les noms des boutons (sans les accolades), et les valeurs sont les fonctions de rappel de rendu des boutons. Les fonctions de rappel ont la signature suivante :

  ```php
  function ($url, $model, $key) {
      // retourne le code HTML du bouton
  }
  ```

  dans le code qui précède, `$url` est l'URL que la colonne crée pour le bouton, `$model` est l'objet modèle qui est en train d'être rendu pour la ligne courante, et `$key` est la clé du modèle dans le tableau du fournisseur de données.

- [[yii\grid\ActionColumn::urlCreator|urlCreator]] est une fonction de rappel qui crée une URL de bouton en utilisant les informations spécifiées sur le modèle. La signature de la fonction de rappel doit être le même que celle de [[yii\grid\ActionColumn::createUrl()]]. Si cette propriété n'est pas définie, les URL de bouton sont créées en utilisant [[yii\grid\ActionColumn::createUrl()]].
- [[yii\grid\ActionColumn::visibleButtons|visibleButtons]] est un tableau des conditions de visibilité pour chacun des boutons. Les clés du tableau sont les noms des boutons (sans les accolades), et les valeurs sont les valeurs booleénnes `true` ou `false` (vrai ou faux) ou la fonction anonyme. Lorsque le nom du bouton n'est pas spécifié dans ce tableau, il est montré par défaut. Les fonctions de rappel utilisent la signature suivante :

  ```php
  function ($model, $key, $index) {
      return $model->status === 'editable';
  }
  ```

  Ou vous pouvez passer une valeur booléenne :

  ```php
  [
      'update' => \Yii::$app->user->can('update')
  ]
  ```

#### Colonne boîte à cocher (*CheckboxColumn*)

La classe [[yii\grid\CheckboxColumn|CheckboxColumn]] (colonne de boîtes à cocher) affiche une colonne de boîtes à cocher.

Pour ajouter une colonne de boîtes à cocher à la vue en grille (*GridView*), ajoutez la configuration de [[yii\grid\GridView::$columns|columns]] comme ceci :

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        // ...
        [
            'class' => 'yii\grid\CheckboxColumn',
            // vous pouvez configurer des propriétés additionnelles ici
        ],
    ],
```

L'utilisateur peut cliquer sur les boîtes à cocher pour sélectionner des lignes dans la grille. Les lignes sélectionnées peuvent être obtenues en appelant le code JavaScript suivant :

```javascript
var keys = $('#grid').yiiGridView('getSelectedRows');
// keys est un tableau constitué des clés associées aux lignes sélectionnées. 

```

#### Colonne série (*SerialColumn*)

La classe [[yii\grid\SerialColumn|SerialColumn]] (colonne série) rend les numéros de ligne en commençant à `1` et en continuant.

L'utilisation est aussi simple que ce que nous présentons ci-après :

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'], // <-- ici
        // ...
```


### Tri des données

> Note: cette section est en cours de développement. 
>
> - https://github.com/yiisoft/yii2/issues/1576

### Filtrage des données

Pour filtrer les données, la vue en grille (*GridView*) requiert un [modèle](structure-models.md) qui représente le critère de recherche qui est ordinairement pris dans les champs du filtre dans la vue en grille. Une pratique courante lorsqu'on utilise des [enregistrements actifs](db-active-record.md) est de créer une classe modèle de recherche qui fournit les fonctionnalités nécessaires (elle peut être générée pour vous par [Gii](start-gii.md)). Cette classe définit les règles de validation pour la recherche et fournit une méthode `search()` (recherche) qui retourne le fournisseur de données avec une requête ajustée qui respecte les critères de recherche.

Pour ajouter la fonctionnalité de recherche au modèle `Post`, nous pouvons créer un modèle `PostSearch` comme celui de l'exemple suivant :

```php
<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class PostSearch extends Post
{
    public function rules()
    {
        // seuls les champs dans rules() peuvent être recherchés
        return [
            [['id'], 'integer'],
            [['title', 'creation_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypasse l'implémentation de scenarios() dans la classe parent
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Post::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // charge les données du formulaire de recherche et valide 
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // ajuste la requête en ajoutant les filtres 
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', $this->title])
              ->andFilterWhere(['like', 'creation_date', $this->creation_date]);

        return $dataProvider;
    }
}
```

> Tip: reportez-vous au [Constructeur de requêtes ](db-query-builder.md) (*Query Builder*) et en particulier aux [conditions de filtrage](db-query-builder.md#filter-conditions) pour savoir comment construire la requête de filtrage.

Vous pouvez utiliser cette fonction dans le contrôleur pour obtenir le fournisseur de données de la vue en grille :

```php
$searchModel = new PostSearch();
$dataProvider = $searchModel->search(Yii::$app->request->get());

return $this->render('myview', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
]);
```

Et dans la vue, vous assignez ensuite le fournisseur de données (`$dataProvider`) et le modèle de recherche (`$searchModel`) à la vue en grille (*GridView*) :

```php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        // ...
    ],
]);
```

### Formulaire de filtrage séparé

La plupart du temps, utiliser les filtres de l'entête de la vue en grille suffit, mais dans le cas où vous avez besoin d'un formulaire de filtrage séparé, vous pouvez facilement l'ajouter aussi. Vous pouvez créer une vue partielle `_search.php` avec le contenu suivant : 

```php
<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\PostSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="post-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'title') ?>

    <?= $form->field($model, 'creation_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::submitButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
```

et l'inclure dans la vue `index.php`, ainsi :

```php
<?= $this->render('_search', ['model' => $searchModel]) ?>
```

> Note: si vous utilisez Gii pour générer le code des méthodes CRUD, le formulaire de filtrage séparé (`_search.php`) est généré par défaut, mais est commenté dans la vue `index.php`. Il vous suffit de supprimer la marque du commentaire pour l'utiliser !

Un formulaire de filtrage séparé est utile quand vous avez besoin de filtrer selon des champs qui ne sont pas visibles dans la vue en grille, ou pour des conditions particulières de filtrage, telles qu'une plage de dates. Pour filtrer selon une plage de dates, nous pouvons ajouter les attributs non DB `createdFrom` et`createdTo` au modèle de recherche :

```php
class PostSearch extends Post
{
    /**
     * @var string
     */
    public $createdFrom;

    /**
     * @var string
     */
    public $createdTo;
}
```

Étendez les conditions de la requête dans la méthode `search()` comme ceci :

```php
$query->andFilterWhere(['>=', 'creation_date', $this->createdFrom])
      ->andFilterWhere(['<=', 'creation_date', $this->createdTo]);
```

Et ajoutez les champs représentatifs au formulaire de filtrage : 

```php
<?= $form->field($model, 'creationFrom') ?>

<?= $form->field($model, 'creationTo') ?>
```

### Travail avec des relations entre modèles

Lorsque vous affichez des enregistrements actifs dans la vue en grille, vous pouvez rencontrer le cas où vous affichez des valeurs de colonne en relation telles que le nom de l'auteur de l'article (post) au lieu d'afficher simplement son identifiant (`id`). Vous pouvez le faire en définissant le nom de l'attribut dans [[yii\grid\GridView::$columns]] comme étant `author.name` lorsque le modèle de l'article (`Post`) possède une relation nommée `author` (auteur) et que le modèle possède un attribut nommé `name` (nom). La vue en grille affiche alors le nom de l'auteur mais le tri et le filtrage ne sont pas actifs par défaut. Vous devez ajuster le modèle `PostSearch` que nous avons introduit dans la section précédente pour y ajouter cette fonctionnalité.

Pour activer le tri sur une colonne en relation, vous devez joindre la table en relation et ajouter la règle de tri au composant *Sort* du fournisseur de données :

```php
$query = Post::find();
$dataProvider = new ActiveDataProvider([
    'query' => $query,
]);

// joingnez avec la relation nommée `author` qui est une relation avec la table `users`
// et définissez l'alias à `author`
$query->joinWith(['author' => function($query) { $query->from(['author' => 'users']); }]);
// depuis la version 2.0.7, l'écriture ci-dessus peut être simplifiée en $query->joinWith('author AS author');
// active le tri pour la colonne en relation 
$dataProvider->sort->attributes['author.name'] = [
    'asc' => ['author.name' => SORT_ASC],
    'desc' => ['author.name' => SORT_DESC],
];

// ...
```

Le filtrage nécessite aussi l'appel de la fonction *joinWith* ci-dessus. Vous devez également autoriser la recherche sur la colonne dans les attributs et les règles comme ceci :

```php
public function attributes()
{
    // ajoute les champs en relation avec les attributs susceptibles d'être cherchés
    return array_merge(parent::attributes(), ['author.name']);
}

public function rules()
{
    return [
        [['id'], 'integer'],
        [['title', 'creation_date', 'author.name'], 'safe'],
    ];
}
```

Dans `search()`, il vous suffit ensuite d'ajouter une autre condition de filtrage avec :

```php
$query->andFilterWhere(['LIKE', 'author.name', $this->getAttribute('author.name')]);
```

> Info: dans ce qui précède, nous utilisons la même chaîne de caractères pour le nom de la relation et pour l'alias de table ; cependant, lorsque votre nom de relation et votre alias diffèrent, vous devez faire attention aux endroits où vous utilisez l'alias et à ceux où vous utilisez le nom de la relation. Une règle simple pour cela est d'utiliser l'alias partout où cela sert à construire le requête de base de données et le nom de la relation dans toutes les autres définitions telles que `attributes()` et `rules()` etc.
>
> Par exemple, si vous utilisez l'alias `au` pour la table auteur en relation, l'instruction *joinWith* ressemble à ceci : 
>
> ```php
> $query->joinWith(['author au']);
> ```
>
> Il est également possible d'appeler simplement `$query->joinWith(['author']);` lorsque l'alias est défini dans la définition de la relation. 
>
> L'alias doit être utilisé dans la condition de filtrage mais le nom d'attribut reste le même :
>
> ```php
> $query->andFilterWhere(['LIKE', 'au.name', $this->getAttribute('author.name')]);
> ```
>
> La même chose est vraie pour la définition du tri :
>
> ```php
> $dataProvider->sort->attributes['author.name'] = [
>      'asc' => ['au.name' => SORT_ASC],
>      'desc' => ['au.name' => SORT_DESC],
> ];
> ```
>
> Également, lorsque vous spécifiez la propriété [[yii\data\Sort::defaultOrder|defaultOrder]] (ordre de tri par défaut) pour le tri, vous avez besoin d'utiliser le nom de la relation au lieu de l'alias :
>
> ```php
> $dataProvider->sort->defaultOrder = ['author.name' => SORT_ASC];
> ```

> Info: pour plus d'informations sur `joinWith` et sur les requêtes effectuées en arrière-plan, consultez la documentation sur l'enregistrement actif à la section [Jointure avec des relations](db-active-record.md#joining-with-relations).

#### Utilisation de vues SQL pour le filtrage, le tri et l'affichage des données

Il existe une autre approche qui peut être plus rapide et plus utile – les vues SQL. Par exemple, si vous avez besoin d'afficher la vue en grille avec des utilisateurs et leur profil, vous pouvez le faire de cette manière :

```sql
CREATE OR REPLACE VIEW vw_user_info AS
    SELECT user.*, user_profile.lastname, user_profile.firstname
    FROM user, user_profile
    WHERE user.id = user_profile.user_id
```

Ensuite vous devez créer l'enregistrement actif qui représente cette vue : 

```php

namespace app\models\views\grid;

use yii\db\ActiveRecord;

class UserView extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'vw_user_info';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // définissez vos règle ici
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            // définissez vos étiquettes d'attribut ici
        ];
    }


}
```

Après cela, vous pouvez utiliser l'enregistrement actif *UserView* dans vos modèle de recherche, sans spécification additionnelle d'attribut de tri et de filtrage. Tous les attributs fonctionneront directement. Notez que cette approche a ses avantages et ses inconvénients :

- vous n'avez pas besoin de spécifier des conditions de tri et de filtrage. Tout fonctionne d'emblée ;
- cela peut être beaucoup plus rapide à cause de la taille des données et du nombre de requêtes SQL effectuées (pour chacune des relations vous n'avez pas besoin de requête supplémentaire) ;
- comme cela n'est qu'une simple mise en relation de l'interface utilisateur avec la vue SQL, il lui manque un peu de la logique qui apparaît dans vos entités, ainsi, si vous avez des méthodes comme `isActive`, `isDeleted` ou autres qui influencent l'interface utilisateur, vous devez les dupliquer dans cette classe également.


### Plusieurs vues en grille par page

Vous pouvez utiliser plus d'une vue en grille sur une page unique mais quelques éléments de configuration additionnels sont nécessaires afin qu'elles n'entrent pas en interférence entre elles. Lorsque vous utilisez plusieurs instances de la vue en grille, vous devez configurer des noms de paramètre différents pour les liens de tri et de pagination générés de manière à ce que chacune des vues en grille possède ses propres liens de tri et de pagination. Vous faites cela en définissant les paramètres [[yii\data\Sort::sortParam|sortParam]] (tri) et [[yii\data\Pagination::pageParam|pageParam]] (page) des instances [[yii\data\BaseDataProvider::$sort|sort]] et [[yii\data\BaseDataProvider::$pagination|pagination]] du fournisseur de données.

Supposez que vous vouliez lister les modèles `Post` et `User` pour lesquels vous avez déjà préparé deux fournisseurs de données `$userProvider` et `$postProvider`:

```php
use yii\grid\GridView;

$userProvider->pagination->pageParam = 'user-page';
$userProvider->sort->sortParam = 'user-sort';

$postProvider->pagination->pageParam = 'post-page';
$postProvider->sort->sortParam = 'post-sort';

echo '<h1>Users</h1>';
echo GridView::widget([
    'dataProvider' => $userProvider,
]);

echo '<h1>Posts</h1>';
echo GridView::widget([
    'dataProvider' => $postProvider,
]);
```

### Utilisation de la vue en grille avec Pjax

Le composant graphique [[yii\widgets\Pjax|Pjax]] vous permet de mettre à jour une certaine section de votre page plutôt que d'avoir à recharger la page toute entière. Vous pouvez l'utiliser pour mettre uniquement à jour le contenu de la [[yii\grid\GridView|GridView]] (vue en grille) lors de l'utilisation de filtres.

```php
use yii\widgets\Pjax;
use yii\grid\GridView;

Pjax::begin([
    // PJax options
]);
    Gridview::widget([
        // GridView options
    ]);
Pjax::end();
```

Pjax fonctionne également pour les liens à l'intérieur du composant graphique [[yii\widgets\Pjax|Pjax]] et pour les liens spécifiés par [[yii\widgets\Pjax::$linkSelector|Pjax::$linkSelector]]. Mais cela peut être un problème pour les liens d'une [[yii\grid\ActionColumn|ActionColumn]] (colonne d'action). Pour empêcher cela, ajoutez l'attribut HTML `data-pjax="0"` aux liens lorsque vous définissez la propriété [[yii\grid\ActionColumn::$buttons|ActionColumn::$buttons]].

#### Vue en grille et vue en liste avec Pjax dans Gii

Depuis la version 2.0.5, le générateur d'actions CRUD de [Gii](start-gii.md) dispose d'une option appelée `$enablePjax` qui peut être utilisée, soit via l'interface web, soit en ligne de commande.

```php
yii gii/crud --controllerClass="backend\\controllers\PostController" \
  --modelClass="common\\models\\Post" \
  --enablePjax=1
```

Qui génère un composant graphique [[yii\widgets\Pjax|Pjax]] enveloppant les composants graphiques [[yii\grid\GridView|GridView]] ou [[yii\widgets\ListView|ListView]].

Lectures complémentaires
------------------------

- [Rendering Data in Yii 2 with GridView and ListView](https://www.sitepoint.com/rendering-data-in-yii-2-with-gridview-and-listview/) d'Arno Slatius.
