Vues
=====

Les vues font partie du modèle d'architecture [MVC](https://fr.wikipedia.org/wiki/Mod%C3%A8le-vue-contr%C3%B4leur) (Modèle Vue Contrôleur).
Elles sont chargées de présenter les données à l'utilisateur final. Dans une application Web, les vues sont ordinairement créées en termes de *modèles de vue* qui sont des script PHP contenant principalement du code HTML et du code PHP relatif à la présentation. 

Elles sont gérées par le [[yii\web\View|view]] [composant application](structure-application-components.md) qui fournit des méthodes d'usage courant pour faciliter la composition des vues et leur rendu. Par souci de simplicité, nous appellerons *vues* les modèles de vue et les fichiers de modèle de vue.


## Création des vues <span id="creating-views"></span>

Comme nous l'avons dit ci-dessus, une vue n'est rien d'autre qu'un script PHP incluant du code HTML et du code PHP. Le script ci-dessous correspond à la vue d'un formulaire de connexion. Comme vous pouvez le voir le code PHP est utilisé pour générer le contenu dynamique, dont par exemple le titre de la page et le formulaire, tandis que le code HTML les organise en une page présentable. 

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Veuillez remplir les champs suivants pour vous connecter:</p>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Login') ?>
<?php ActiveForm::end(); ?>
```

À l'intérieur d'une vue, vous avez accès à `$this` qui fait référence au [[yii\web\View|composant view (vue)]] responsable de le gestion et du rendu de ce modèle de vue. 

En plus de `$this`, il peut aussi y avoir d'autres variables prédéfinies dans une vue, telles que `$model` dans l'exemple précédent. Ces variables représentent les données qui sont *poussées* dans la vue par les [contrôleurs](structure-controllers.md) ou par d'autres objets qui déclenche le  [rendu d'une vue](#rendering-views).

> Tip: les variables prédéfinies sont listées dans un bloc de commentaires au début d'une vue de manière à être reconnues par les EDI. C'est également une bonne manière de documenter vos vues. 


### Sécurité <span id="security"></span>

Lors de la création de vues qui génèrent des pages HTML, il est important que vous encodiez et/ou filtriez les données en provenance de l'utilisateur final avant des les présenter. Autrement, votre application serait sujette aux [attaques par injection de scripts (*cross-site scripting*)](https://fr.wikipedia.org/wiki/Cross-site_scripting).

Pour afficher du texte simple, commencez par l'encoder en appelant la méthode [[yii\helpers\Html::encode()]]. Par exemple, le code suivant encode le nom d'utilisateur (*username*) avant de l'afficher :

```php
<?php
use yii\helpers\Html;
?>

<div class="username">
    <?= Html::encode($user->name) ?>
</div>
```

Pour afficher un contenu HTML, utilisez l'objet [[yii\helpers\HtmlPurifier]] pour d'abord en filtrer le contenu. Par exemple, le code suivant filtre le contenu de la variable *post* avant de l'afficher :


```php
<?php
use yii\helpers\HtmlPurifier;
?>

<div class="post">
    <?= HtmlPurifier::process($post->text) ?>
</div>
```

> Tip: bien que l'objet  HTMLPurifier effectue un excellent travail en rendant vos sorties sûres, il n'est pas rapide. Vous devriez envisager de mettre le résultat en [cache](caching-overview.md) lorsque votre application requiert une performance élevée. 


### Organisation des vues <span id="organizing-views"></span>

Comme les [contrôleurs](structure-controllers.md) et les  [modèles](structure-models.md), il existe certaines conventions pour organiser les vues. 

* Pour les vues rendues par un contrôleur, elles devraient être placées par défaut dans le dossier  `@app/views/ControllerID` où `ControllerID` doit être remplacé par l'[identifiant du contrôleur](structure-controllers.md#routes). Par exemple, si la classe du contrôleur est  `PostController`, le dossier est `@app/views/post`; si c'est  `PostCommentController` le dossier est `@app/views/post-comment`. Dans le cas où le contrôleur appartient à un module, le dossier s'appelle `views/ControllerID` et se trouve dans le [[yii\base\Module::basePath|dossier de base du module]].
* Pour les vues rendues dans un [objet graphique](structure-widgets.md), elles devraient être placées par défaut dans le dossier `WidgetPath/views` où  `WidgetPath` est le dossier contenant le fichier de la classe de l'objet graphique. 
* Pour les vues rendues par d'autres objets, il est recommandé d'adopter une convention similaire à celle utilisée pour les objets graphiques. 

Vous pouvez personnaliser ces dossiers par défaut en redéfinissant la méthode [[yii\base\ViewContextInterface::getViewPath()]] des contrôleurs ou des objets graphiques.


## Rendu des vues <span id="rendering-views"></span>

Vous pouvez rendre les vues dans des [contrôleurs](structure-controllers.md), des [objets graphiques](structure-widgets.md), ou dans d'autres endroits en appelant les méthodes de rendu des vues. Ces méthodes partagent un signature similaire comme montré ci-dessous :
```
/**
 * @param string $view nom de la vue ou chemin du fichier, selon la méthode réelle de rendu
 * @param array $params les données injectées dans la vue
 * @return string le résultat du rendu
 */
methodName($view, $params = [])
```


### Rendu des vues dans des contrôleurs <span id="rendering-in-controllers"></span>

Dans les [contrôleurs](structure-controllers.md), vous pouvez appeler la méthode de contrôleur suivante pour rendre une vue :

* [[yii\base\Controller::render()|render()]]: rend une [vue nommée](#named-views) et applique une [disposition](#layouts)
  au résultat du rendu.
* [[yii\base\Controller::renderPartial()|renderPartial()]]: rend une [vue nommée](#named-views) sans disposition.
* [[yii\web\Controller::renderAjax()|renderAjax()]]: rend une [vue nommée ](#named-views) sans disposition et injecte tous les scripts et fichiers JS/CSS enregistrés. Cette méthode est ordinairement utilisée en réponse à une requête Web AJAX.
* [[yii\base\Controller::renderFile()|renderFile()]]: rend une vue spécifiée en terme de chemin ou d'[alias](concept-aliases.md) de fichier de vue.
* [[yii\base\Controller::renderContent()|renderContent()]]: rend un chaîne statique en l'injectant dans la [disposition](#layouts) courante. Cette méthode est disponible depuis la version 2.0.1.

Par exemple :

```php
namespace app\controllers;

use Yii;
use app\models\Post;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PostController extends Controller
{
    public function actionView($id)
    {
        $model = Post::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        // rend une vue nommée  "view" et lui applique une disposition de page
        return $this->render('view', [
            'model' => $model,
        ]);
    }
}
```


### Rendu des vues dans les objets graphiques <span id="rendering-in-widgets"></span>

Dans les [objets graphiques](structure-widgets.md), vous pouvez appeler les méthodes suivantes de la classe *widget* pour rendre une vue : 

* [[yii\base\Widget::render()|render()]]: rend une [vue nommée](#named-views).
* [[yii\base\Widget::renderFile()|renderFile()]]: rend une vue spécifiée en terme de chemin ou d'[alias](concept-aliases.md) de fichier de vue.

Par exemple :

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class ListWidget extends Widget
{
    public $items = [];

    public function run()
    {
        // rend  une vue nommée "list"
        return $this->render('list', [
            'items' => $this->items,
        ]);
    }
}
```


### Rendu des vues dans des vues <span id="rendering-in-views"></span>

Vous pouvez rendre une vue dans une autre vue en appelant les méthodes suivantes du  [[yii\base\View|composant view]]:

* [[yii\base\View::render()|render()]]: rend une [vue nommée](#named-views).
* [[yii\web\View::renderAjax()|renderAjax()]]: rend une  [vue nommée](#named-views) et injecte tous les fichiers et scripts JS/CSS enregistrés. On l'utilise ordinairement en réponse à une requête Web AJAX.
* [[yii\base\View::renderFile()|renderFile()]]: rend une vue spécifiée en terme de chemin ou d'[alias](concept-aliases.md) de fichier de vue.

Par exemple, le code suivant dans une vue, rend le fichier de vue `_overview.php` qui se trouve dans le même dossier que la vue courante. Rappelez-vous que `$this` dans une vue fait référence au composant [[yii\base\View|view]] :

```php
<?= $this->render('_overview') ?>
```


### Rendu de vues en d'autres endroits <span id="rendering-in-other-places"></span>

Dans n'importe quel endroit, vous pouvez accéder au composant d'application [[yii\base\View|view]] à l'aide de l'expression `Yii::$app->view` et ensuite appeler une de ses méthodes mentionnées plus haut pour rendre une vue. Par exemple :

```php
// displays the view file "@app/views/site/license.php"
echo \Yii::$app->view->renderFile('@app/views/site/license.php');
```


### Vues nommées <span id="named-views"></span>

Lorsque vous rendez une vue, vous pouvez spécifier la vue en utilisant soit un nom de vue, soit un chemin/alias de fichier de vue. Dans la plupart des cas, vous utilisez le nom car il est plus concis et plus souple. Nous appelons les vues spécifiées par leur nom, des *vues nommées*. 

Un nom de vue est résolu en le chemin de fichier correspondant en appliquant les règles suivantes : 

* Un nom de vue peut omettre l'extension du nom de fichier. Dans ce cas, `.php` est utilisé par défaut en tant qu'extension. Par exemple, le nom de vue  `about` correspond au nom de fichier `about.php`.
* Si le nom de vue commence par une double barre de division `//`, le chemin de fichier correspondant est `@app/views/ViewName` où `ViewName` est le nom de la vue. Dans ce cas la vue est recherchée dans le dossier [[yii\base\Application::viewPath|chemin des vues de l'application]]. Par exemple, `//site/about` est résolu en `@app/views/site/about.php`.
* Si le nom de la vue commence par une unique barre de division `/`, le chemin de fichier de la vue est formé en préfixant le nom de vue avec [[yii\base\Module::viewPath|chemin des vues]] du [module](structure-modules.md) actif courant . Si aucun module n'est actif, `@app/views/ViewName` — où `ViewName` est le nom de la vue — est utilisé. Par exemple, `/user/create` est résolu en `@app/modules/user/views/user/create.php`, si le module actif courant est `user` et en `@app/views/user/create.php`si aucun module n'est actif.
* Si la vue est rendue avec un  [[yii\base\View::context|contexte]] et que le contexte implémente  [[yii\base\ViewContextInterface]],le chemin de fichier de vue est formé en préfixant le nom de vue avec le [[yii\base\ViewContextInterface::getViewPath()|chemin des vues]] du contexte. Cela s'applique principalement aux vues rendues dans des contrôleurs et dans des objets graphiques. Par exemple,  `about` est résolu en `@app/views/site/about.php` si le contexte est le contrôleur `SiteController`.
* Si une vue est rendue dans une autre vue, le dossier contenant le nom de la nouvelle vue est préfixé avec le chemin du dossier contenant l'autre vue. Par exemple, la vue `item` est résolue en  `@app/views/post/item.php` lorsqu'elle est rendue dans `@app/views/post/index.php`.

Selon les règles précédentes, l'appel de `$this->render('view')` dans le contrôleur  `app\controllers\PostController` rend réellement le fichier de vue `@app/views/post/view.php`, tandis que l'appel de `$this->render('_overview')` dans cette vue rend le fichier de vue `@app/views/post/_overview.php`.


### Accès aux données dans les vues <span id="accessing-data-in-views"></span>

Il existe deux approches pour accéder aux données à l'intérieur d'une vue : *pousser* et *tirer*.

En passant les données en tant que second paramètre des méthodes de rendu de vues, vous utilisez la méthode *pousser*. Les données doivent être présentées sous forme de tableau clé-valeur. Lorsque la vue est rendue, la fonction PHP `extract()` est appelée sur ce tableau pour que le tableau soit restitué sous forme de variables dans la vue. Par exemple, le code suivant de rendu d'une vue dans un contrôleur *pousse* deux variables dans la vue  `report` :

`$foo = 1` et `$bar = 2`.

```php
echo $this->render('report', [
    'foo' => 1,
    'bar' => 2,
]);
```

L'approche *tirer* retrouve les données de manière plus active à partir du [[yii\base\View|composant view]] ou à partir d'autres objets accessibles dans les vues  (p. ex. `Yii::$app`). En utilisant le code exemple qui suit, vous pouvez, dans une vue, obtenir l'objet contrôleur `$this->context`.  Et, en conséquence, il vous est possible d'accéder à n'importe quelle propriété ou méthode du contrôleur, comme la propriété `id` du contrôleur :

```php
The controller ID is: <?= $this->context->id ?>
```

L'approche *pousser* est en général le moyen préféré d'accéder aux données dans les vues, parce qu'elle rend les vues moins dépendantes des objets de contexte. Son revers, et que vous devez construire le tableau de données à chaque fois, ce qui peut devenir ennuyeux et sujet aux erreurs si la vue est rendue en divers endroits. 

### Partage de données entre vues <span id="sharing-data-among-views"></span>

Le  [[yii\base\View|composant view ]] dispose de la propriété [[yii\base\View::params|params]] que vous pouvez utiliser pour partager des données entre vues. 

Par exempe, dans une vue  `about` (à propos), vous pouvez avoir le code suivant qui spécifie le segment courant du *fil d'Ariane*. 

```php
$this->params['breadcrumbs'][] = 'About Us';
```

Ainsi, dans le fichier de la [disposition](#layouts), qui est également une vue, vous pouvez afficher le *fil d'Ariane*  en utilisant les données passées par [[yii\base\View::params|params]] :

```php
<?= yii\widgets\Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]) ?>
```


## Dispositions <span id="layouts"></span>

Les dispositions (*layouts*) sont des types  spéciaux de vues qui représentent les parties communes de multiples vues. Par exemple, les pages de la plupart des applications Web  partagent le même entête et le même pied de page. Bien que vous puissiez répéter le même entête et le même pied de page dans chacune des vues, il est préférable de le faire une fois dans une disposition et d'inclure le résultat du rendu d'une vue de contenu  à l'endroit approprié de la disposition. 


### Création de dispositions <span id="creating-layouts"></span>

Parce que les dispositions sont aussi des vues, elles peuvent être créées de manière similaire aux vues ordinaires. Par défaut, les dispositions sont stockées dans le dossier `@app/views/layouts`. Les dispositions utilisées dans un [module](structure-modules.md) doivent être stockées dans le dossier `views/layouts` du [[yii\base\Module::basePath|dossier de base du module]]. Vous pouvez personnaliser le dossier par défaut des dispositions en configurant la propriété [[yii\base\Module::layoutPath]] de l'application ou du module.

L'exemple qui suit montre à quoi ressemble une disposition. Notez que dans un but illustratif, nous avons grandement simplifié le code à l'intérieur de cette disposition. En pratique, vous désirerez ajouter à ce code plus de contenu, comme des balises head, un menu principal, etc. 

```php
<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $content string */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <header>My Company</header>
    <?= $content ?>
    <footer>&copy; 2014 by My Company</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

Comme vous pouvez le voir, la disposition génère les balises HTML qui sont communes à toutes les pages. Dans la section `<body>` la disposition rend  la variable `$content` qui représente le résultat de rendu d'une vue de contenu qui est poussée dans la disposition par l'appel à la fonction [[yii\base\Controller::render()]].

La plupart des dispositions devraient appeler les méthodes suivantes, comme illustré dans l'exemple précédent. Ces méthodes déclenchent essentiellement des événements concernant le processus de rendu de manière à ce que des balises et des scripts enregistrés dans d'autres endroits puissent être injectés à l'endroit où ces méthodes sont appelées. 

- [[yii\base\View::beginPage()|beginPage()]]: cette méthode doit être appelée au tout début de la disposition. Elle déclenche l'événement  [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]] qui signale le début d'une page.
- [[yii\base\View::endPage()|endPage()]]: cette méthode doit être appelée à la fin de la disposition. Elle déclenche l'événement [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]] qui signale la fin d'une page.
- [[yii\web\View::head()|head()]]: cette méthode doit être appelée dans la section `<head>` d'une page HTML. Elle génère une valeur à remplacer qui sera remplacée par le code d'entête HTML (p. ex. des balises liens, des balises meta, etc.) lorsqu'une page termine son processus de rendu. 
- [[yii\web\View::beginBody()|beginBody()]]: cette méthode doit être appelée au début de la section `<body>`. Elle déclenche l'événement [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]] et génère une valeur à remplacer qui sera remplacée par le code HTML enregistré (p. ex. Javascript) dont la cible est le début du corps de la page. 
- [[yii\web\View::endBody()|endBody()]]: cette méthode doit être appelée à la fin de la section `<body>`. Elle déclenche l'événement [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]] et génère une valeur à remplacer qui sera remplacée par le code HTML enregistré (p. ex. Javascript) dont la cible est la fin du corps de la page.

### Accès aux données dans les dispositions <span id="accessing-data-in-layouts"></span>

Dans une disposition, vous avez accès à deux variables prédéfinies : `$this` et `$content`. La première fait référence au composant [[yii\base\View|view]], comme dans les vues ordinaires, tandis que la seconde contient le résultat de rendu d'une vue de contenu qui est rendue par l'appel de la méthode  [[yii\base\Controller::render()|render()]] dans un contrôleur.

Si vous voulez accéder à d'autres données dans les dispositions, vous devez utiliser l'approche *tirer* comme c'est expliqué à la sous-section  [Accès aux données dans les vues](#accessing-data-in-views). Si vous voulez passer des données d'une vue de contenu à une disposition, vous pouvez utiliser la méthode décrite à la sous-section [Partage de données entre vues](#sharing-data-among-views).


### Utilisation des dispositions <span id="using-layouts"></span>

Comme c'est décrit à la sous-section [Rendu des vues dans les contrôleurs](#rendering-in-controllers), lorsque vous rendez une vue en appelant la méthode  [[yii\base\Controller::render()|render()]] dans un contrôleur, une disposition est appliquée au résultat du rendu. Par défaut, la disposition  `@app/views/layouts/main.php` est utilisée. 

Vous pouvez utiliser une disposition différente en configurant soit [[yii\base\Application::layout]], soit [[yii\base\Controller::layout]]. La première gouverne la disposition utilisée par tous les contrôleurs, tandis que la deuxième redéfinit la première pour les contrôleurs individuels. Par exemple, le code suivant fait que le contrôleur `post` utilise `@app/views/layouts/post.php` en tant qu disposition lorsqu'il rend ses vues. Les autres contrôleurs, en supposant que leur propriété  `layout` n'est pas modifiée, continuent d'utiliser la disposition par défaut `@app/views/layouts/main.php`.
 
```php
namespace app\controllers;

use yii\web\Controller;

class PostController extends Controller
{
    public $layout = 'post';
    
    // ...
}
```

Pour les contrôleurs appartenant à un module ,vous pouvez également configurer la propriété  [[yii\base\Module::layout|layout]] pour utiliser une disposition particulière pour ces contrôleurs. 

Comme la propriété `layout` peut être configurée à différents niveaux (contrôleurs, modules, application), en arrière plan, Yii opère en deux étapes pour déterminer quel est le fichier de disposition réel qui doit être utilisé pour un contrôleur donné.  

Dans la première étape, il détermine la valeurs de la disposition et le module du contexte :

- Si la propriété  [[yii\base\Controller::layout]] du contrôleur n'est pas nulle, il l'utilise en tant que valeur de disposition et le [[yii\base\Controller::module|module]] du contrôleur en tant que module du contexte. 
- Si  la propriété [[yii\base\Controller::layout|layout]] est nulle, il cherche, à travers tous les modules ancêtres (y compris l'application elle-même) du contrôleur, le premier module dont la propriété [[yii\base\Module::layout|layout]] n'est pas nulle. Il utilise alors ce module et la valeur de sa  [[yii\base\Module::layout|disposition]] comme module du contexte et valeur de disposition, respectivement. Si un tel module n'est pas trouvé, cela signifie qu'aucune disposition n'est appliquée. 
  
Dans la seconde étape, il détermine le fichier de disposition réel en fonction de la valeur de disposition et du module du contexte déterminés dans la première étape. La valeur de disposition peut être :

- Un alias de chemin (p. ex. `@app/views/layouts/main`).
- Un chemin absolu (p. ex. `/main`): la valeur de disposition commence par une barre oblique de division. Le fichier réel de disposition est recherché dans le [[yii\base\Application::layoutPath|chemin des disposition (*layoutPath*)]] (par défaut `@app/views/layouts`).
- Un chemin relatif (p. ex. `main`): le fichier réel de disposition est recherché dans le [[yii\base\Module::layoutPath|chemin des dispositions (*layoutPath*)]] du module du contexte (par défaut`views/layouts`) dans le [[yii\base\Module::basePath|dossier de base du module]].
- La valeur booléenne `false`: aucune disposition n'est appliquée.

Si la valeur de disposition ne contient pas d'extension de fichier, l'extension par défaut `.php` est utilisée. 


### Dispositions imbriquées <span id="nested-layouts"></span>

Parfois, vous désirez imbriquer une disposition dans une autre. Par exemple, dans les différentes sections d'un site Web, vous voulez utiliser des dispositions différentes, bien que ces dispositions partagent la même disposition de base qui génère la structure d'ensemble des pages HTML5. Vous pouvez réaliser cela en appelant la méthode [[yii\base\View::beginContent()|beginContent()]] et la méthode 
[[yii\base\View::endContent()|endContent()]] dans les dispositions filles comme illustré ci-après :
```php
<?php $this->beginContent('@app/views/layouts/base.php'); ?>

...contenu de la disposition fille ici...

<?php $this->endContent(); ?>
```

Comme on le voit ci-dessus, le contenu de la disposition fille doit être  situé entre les appels des méthodes [[yii\base\View::beginContent()|beginContent()]] et [[yii\base\View::endContent()|endContent()]]. Le paramètre passé à la méthode [[yii\base\View::beginContent()|beginContent()]] spécifie quelle est la disposition parente. Ce peut être un fichier de disposition ou un alias. En utilisant l'approche ci-dessus, vous pouvez imbriquer des dispositions sur plusieurs niveaux. 



### Utilisation des blocs <span id="using-blocks"></span>

Les blocs vous permettent de spécifier le contenu de la vue à un endroit et l'afficher ailleurs. Ils sont souvent utilisés conjointement avec les dispositions. Par exemple, vous pouvez définir un bloc dans une vue de contenu et l'afficher dans la disposition. 

Pour définir un bloc, il faut appeler les méthodes  [[yii\base\View::beginBlock()|beginBlock()]] et [[yii\base\View::endBlock()|endBlock()]]. Vous pouvez accéder au bloc via son identifiant avec `$view->blocks[$blockID]`, où `$blockID` représente l'identifiant unique que vous assignez au bloc lorsque vous le définissez. 

L'exemple suivant montre comment utiliser les blocs pour personnaliser des parties spécifiques dans la disposition d'une vue de contenu. 

Tout d'abord, dans une vue de contenu, définissez un ou de multiples blocs :

```php
...

<?php $this->beginBlock('block1'); ?>

...contenu de block1...

<?php $this->endBlock(); ?>

...

<?php $this->beginBlock('block3'); ?>

...contenu de block3...

<?php $this->endBlock(); ?>
```

Ensuite, dans la vue de la disposition, rendez les blocs s'ils sont disponibles, ou affichez un contenu par défaut si le bloc n'est pas défini. 

```php
...
<?php if (isset($this->blocks['block1'])): ?>
    <?= $this->blocks['block1'] ?>
<?php else: ?>
    ... contenu par défaut de  block1 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block2'])): ?>
    <?= $this->blocks['block2'] ?>
<?php else: ?>
    ... contenu par défaut de block2 ...
<?php endif; ?>

...

<?php if (isset($this->blocks['block3'])): ?>
    <?= $this->blocks['block3'] ?>
<?php else: ?>
    ... contenu par défaut de  block3 ...
<?php endif; ?>
...
```


## Utilisation des composants view <span id="using-view-components"></span>

Les composants [[yii\base\View|view]] fournissent de nombreuses fonctionnalités relatives aux vues. Bien que vous puissiez créer des composants *view* en créant des instances de la classe [[yii\base\View]] ou de ses classes filles, dans la plupart des cas, vous utilisez principalement le composant d'application `view` . Vous pouvez configurer ce composant dans les [configuration d'application](structure-applications.md#application-configurations), comme l'illustre l'exemple qui suit :

```php
[
    // ...
    'components' => [
        'view' => [
            'class' => 'app\components\View',
        ],
        // ...
    ],
]
```

Les composants View fournissent les fonctionnalités utiles suivantes relatives aux vues, chacune décrite en détails dans une section séparée :

* [gestion des thèmes](output-theming.md): vous permet des développer et de changer les thèmes pour votre site Web.
* [mise en cache de fragments](caching-fragment.md): vous permet de mettre en cache un fragment de votre page Web.
* [gestion des scripts client](output-client-scripts.md): prend en charge l'enregistrement et le rendu de code CSS et JavaScript. 
* [gestion de paquets de ressources](structure-assets.md): prend en charge l'enregistrement et le rendu de [paquets de ressources](structure-assets.md).
* [moteurs de modèle alternatif](tutorial-template-engines.md): vous permet d'utiliser d'autres moteur de modèles tels que [Twig](https://twig.symfony.com/) et [Smarty](https://www.smarty.net/).

Vous pouvez aussi utiliser les fonctionnalités suivantes qui, bien que mineures, sont néanmoins utiles, pour développer vos pages Web. 


### Définition du titre des pages <span id="setting-page-titles"></span>

Chaque page Web doit avoir un titre. Normalement la balise titre est affichée dans une  [disposition](#layouts). Cependant, en pratique, le titre est souvent déterminé dans les vues de contenu plutôt que dans les dispositions. Pour résoudre ce problème,[[yii\web\View]] met à votre disposition la propriété [[yii\web\View::title|title]] qui vous permet de passer l'information de titre de la vue de contenu à la disposition.

Pour utiliser cette fonctionnalité, dans chacune des vues de contenu, vous pouvez définir le titre de la page de la manière suivante :
```php
<?php
$this->title = 'Le titre de ma page';
?>
```

Ensuite dans la disposition, assurez-vous qui vous avez placé le code suivant dans la section `<head>` :

```php
<title><?= Html::encode($this->title) ?></title>
```


### Enregistrement des balises "meta" <span id="registering-meta-tags"></span>

Généralement, les pages Web, ont besoin de générer des balises "meta" variées dont ont besoin diverses parties. Comme le titre des pages, les balises "meta" apparaissent dans la section `<head>` et sont généralement générées dans les dispositions.

Si vous désirez spécifier quelles balises "meta" générer dans les vues de contenu, vous pouvez appeler [[yii\web\View::registerMetaTag()]] dans une vue de contenu comme illustrer ci-après :

```php
<?php
$this->registerMetaTag(['name' => 'keywords', 'content' => 'yii, framework, php']);
?>
```

Le code ci-dessus enregistre une balise "meta" "mot clé" dans le composant view. La balise "meta" enregistrée est rendue après que le rendu de la disposition est terminé. Le code HTML suivant est généré et inséré à l'endroit où vous appelez  [[yii\web\View::head()]] dans la disposition :

```php
<meta name="keywords" content="yii, framework, php">
```

Notez que si vous appelez [[yii\web\View::registerMetaTag()]] à de multiples reprises, elle enregistrera de multiples balises meta, que les balises soient les mêmes ou pas.

Pour garantir qu'il n'y a qu'une instance d'un type de balise meta, vous pouvez spécifier une clé en tant que deuxième paramètre lors de l'appel de la méthode. 
Par exemple, le code suivant, enregistre deux balises "meta" « description ». Cependant, seule la seconde sera rendue. 
F

```php
$this->registerMetaTag(['name' => 'description', 'content' => 'This is my cool website made with Yii!'], 'description');
$this->registerMetaTag(['name' => 'description', 'content' => 'This website is about funny raccoons.'], 'description');
```


### Enregistrement de balises liens <span id="registering-link-tags"></span>

Comme les [balises meta](#registering-meta-tags), les balises liens sont utiles dans de nombreux cas, comme la personnalisation de favicon, le pointage sur les flux RSS ou la délégation d'OpenID à un autre serveur. Vous pouvez travailler avec les balises liens comme avec les balises "meta" en utilisant [[yii\web\View::registerLinkTag()]]. Par exemple, dans une vue de contenu, vous pouvez enregistrer une balise lien de la manière suivante :

```php
$this->registerLinkTag([
    'title' => 'Live News for Yii',
    'rel' => 'alternate',
    'type' => 'application/rss+xml',
    'href' => 'https://www.yiiframework.com/rss.xml/',
]);
```

Le code suivant produit le résultat suivant :

```html
<link title="Live News for Yii" rel="alternate" type="application/rss+xml" href="https://www.yiiframework.com/rss.xml/">
```

Comme avec  [[yii\web\View::registerMetaTag()|registerMetaTag()]], vous pouvez spécifier un clé lors de l'appel de [[yii\web\View::registerLinkTag()|registerLinkTag()]] pour éviter de générer des liens identiques.


## Événements de vues <span id="view-events"></span>

Les [[yii\base\View|composants View ]] déclenchent plusieurs événements durant le processus de rendu des vues. Vous pouvez répondre à ces événements pour injecter du contenu dans des vues ou traiter les résultats du rendu avant leur transmission à l'utilisateur final. 

- [[yii\base\View::EVENT_BEFORE_RENDER|EVENT_BEFORE_RENDER]]: déclenché au début du rendu d'un fichier dans un contrôleur. Les gestionnaires de cet événement peuvent définir [[yii\base\ViewEvent::isValid]] à `false` (faux) pour arrêter le processus de rendu. 
- [[yii\base\View::EVENT_AFTER_RENDER|EVENT_AFTER_RENDER]]: déclenché après le rendu d'un fichier par appel de [[yii\base\View::afterRender()]]. Les gestionnaires de cet événement peuvent obtenir le résultat du rendu via  [[yii\base\ViewEvent::output]] et peuvent modifier cette propriété pour modifier le résultat du rendu.
- [[yii\base\View::EVENT_BEGIN_PAGE|EVENT_BEGIN_PAGE]]: déclenché par l'appel de [[yii\base\View::beginPage()]] dans une disposition.
- [[yii\base\View::EVENT_END_PAGE|EVENT_END_PAGE]]: déclenché par l'appel de [[yii\base\View::endPage()]] dans une disposition.
- [[yii\web\View::EVENT_BEGIN_BODY|EVENT_BEGIN_BODY]]: déclenché par l'appel de [[yii\web\View::beginBody()]] dans une disposition.
- [[yii\web\View::EVENT_END_BODY|EVENT_END_BODY]]: déclenché par l'appel de [[yii\web\View::endBody()]] dans une disposition.

Par exemple, le code suivant injecte la date courante à la fin du corps de la page.

```php
\Yii::$app->view->on(View::EVENT_END_BODY, function () {
    echo date('Y-m-d');
});
```


## Rendu des pages statiques <span id="rendering-static-pages"></span>

Les pages statiques font références aux pages dont le contenu principal est essentiellement statique sans recours à des données dynamiques poussées par les contrôleurs. 

Vous pouvez renvoyer des pages statiques en plaçant leur code dans des vues, et en utilisant un code similaire à ce qui suit dans un contrôleur :

```php
public function actionAbout()
{
    return $this->render('about');
}
```

Si un site Web contient beaucoup de pages statiques, ce serait très ennuyeux de répéter un code similaire de nombreuses fois. Pour résoudre ce problème, vous pouvez introduire une  [action autonome](structure-controllers.md#standalone-actions) appelée  [[yii\web\ViewAction]] dans un contrôleur. Par exemple :

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function actions()
    {
        return [
            'page' => [
                'class' => 'yii\web\ViewAction',
            ],
        ];
    }
}
```
Maintenant, si vous créez une vue nommée `about` dans le dossier `@app/views/site/pages`, vous pourrez afficher cette vue via l'URL suivante :
```
http://localhost/index.php?r=site%2Fpage&view=about
```

Le paramètre `view` de la méthode  `GET` dit à [[yii\web\ViewAction]] quelle est la vue requise. L'action recherche alors cette vue dans le dossier `@app/views/site/pages`. Vous pouvez configurer la propriété [[yii\web\ViewAction::viewPrefix]] pour changer le dossier dans lequel la vue est recherchée.


## Meilleures pratiques <span id="best-practices"></span>

Les vues sont chargées de présenter les modèles dans le format désiré par l'utilisateur final. En général :

* Elles devraient essentiellement contenir du code relatif à la présentation, tel que le code HTML, du code PHP simple pour parcourir, formater et rendre les données. 
* Elles ne devraient pas contenir de code qui effectue des requêtes de base de données. Un tel code devrait être placé dans les modèles.
* Elles devraient éviter d'accéder directement aux données de la requête, telles que `$_GET`, `$_POST`. C'est le rôle des contrôleurs.  Si les données de la requête sont nécessaires, elles devraient être poussées dans les vues par les contrôleurs.
* Elles peuvent lire les propriétés des modèles, mais ne devraient pas les modifier.

Pour rendre les vues plus gérables, évitez de créer des vues qui sont trop complexes ou qui contiennent trop de code redondant. Vous pouvez utiliser les techniques suivantes pour atteindre cet but :

* Utiliser des [dispositions](#layouts) pour représenter les sections communes de présentation (p. ex. l'entête de page, le pied de page). 
* Diviser une vue complexe en plusieurs vues plus réduites. Les vues plus réduites peuvent être rendue et assemblées dans une plus grande en utilisant les méthodes de rendu que nous avons décrites. 
* Créer et utiliser des [objets graphiques](structure-widgets.md) en tant que blocs de construction des vues.
* Créer et utiliser des classes d'aide pour transformer et formater les données dans les vues.

