Pagination
==========

Lorsqu'il y a trop de données à afficher sur une seule page, une stratégie courante consiste à les afficher en de multiples pages, et sur chacune des pages, à n'afficher qu'une fraction réduite des données. Cette stratégie est connue sous le nom de *pagination*.

Yii utilise un objet [[yii\data\Pagination]] pour représenter les informations d'un schéma de pagination. En particulier :

* [[yii\data\Pagination::$totalCount|nombre total (*total count*)]] spécifie le nombre total d'items de données. Notez que cela est ordinairement beaucoup plus élevé que le nombre d'items de données que l'on a besoin d'afficher sur une unique page.
* [[yii\data\Pagination::$pageSize|taille de la page (*page size*)]] spécifie combien d'items de données chaque page contient. La valeur par défaut est 20.
* [[yii\data\Pagination::$page|page courante (*current page*)]] donne la numéro de la page courante (qui commence à zéro). La valeur par défaut est 0, ce qui indique la première page. 

Avec un objet [[yii\data\Pagination]] pleinement spécifié, vous pouvez retrouver et afficher partiellement des données. Par exemple, si vous allez chercher des données dans une base de données, vous pouvez spécifier les clauses `OFFSET` et `LIMIT` de la requête de base de données avec les valeurs correspondantes fournies par l'objet pagination. Un exemple est présenté ci-dessous. 

```php
use yii\data\Pagination;

// construit une requêt de base de données pour obtenir tous les articles dont le *status* vaut 1
$query = Article::find()->where(['status' => 1]);

// obtient le nombre total d'articles (mais ne va pas chercher les données articles pour le moment)
$count = $query->count();

// crée un objet pagination en lui passant le nombre total d'items
$pagination = new Pagination(['totalCount' => $count]);

// limite la requête en utilisant l'objet pagination et va chercher les articles
$articles = $query->offset($pagination->offset)
    ->limit($pagination->limit)
    ->all();
```

Mais quelle page d'article est retournée par l'exemple ci-dessus ? Cela dépend d'un paramètre de la requête nommé `page`. Par défaut, l'objet pagination essaye de définir le paramètre `page` avec la valeur de la [[yii\data\Pagination::$page|page courante (*current page*)]]. Si le paramètre n'est pas fourni, il prend la valeur par défaut `0`.

Pour faciliter la construction des élément de l'interface utilisateur qui prennent en charge la pagination, Yii fournit le composant graphique  [[yii\widgets\LinkPager]] qui affiche une liste de boutons de page sur lesquels l'utilisateur peut cliquer pour préciser quelle page de données doit être affichée. Ce composant graphique accepte en paramètre un objet pagination afin de savoir quelle est la page courante et combien de boutons de page afficher. Par exemple :

```php
use yii\widgets\LinkPager;

echo LinkPager::widget([
    'pagination' => $pagination,
]);
```

Si vous voulez construire des éléments d'interface graphique à la main, vous pouvez utiliser [[yii\data\Pagination::createUrl()]] pour créer des URL qui conduisent à différentes pages. La méthode requiert un paramètre de page et crée une URL formatée correctement qui contient le paramètre de page. Par exemple :

```php
// spécifie la route que l'URL à créer doit utiliser,
// si vous ne la spécifiez pas, la route actuellement requise est utilisée
$pagination->route = 'article/index';

// affiche : /index.php?r=article%2Findex&page=100
echo $pagination->createUrl(100);

// affiche : /index.php?r=article%2Findex&page=101
echo $pagination->createUrl(101);
```

> Tip: vous pouvez personnaliser le nom du paramètre de requête `page` en configurant la propriété [[yii\data\Pagination::pageParam|pageParam]] lors de la création de l'objet pagination. 
