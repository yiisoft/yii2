Objets d'interface graphique
==============================

Les objets d'interface graphique (*widgets*) sont des blocs de construction réutilisables dans des [vues](structure-views.md) pour créer des éléments d'interface utilisateur complexes et configurables d'une manière orientée objet. Par exemple, un composant d'interface graphique de sélection de date peut générer un sélecteur de date original qui permet aux utilisateurs de sélectionner une date en tant qu'entrée. Tout ce que vous avez besoin de faire, c'est d'insérer le code dans une vue comme indiqué ci-dessous :

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget(['name' => 'date']) ?>
```

Il existe un grand nombre d'objets d'interface graphique fournis avec Yii, tels que les[[yii\widgets\ActiveForm|active form]], [[yii\widgets\Menu|menu]], [jQuery UI widgets](widget-jui.md), [Twitter Bootstrap widgets](widget-bootstrap.md). Dans ce qui suit, nous introduisons les connaissances de base sur les objets d'interface graphique. Reportez-vous à la documentation de la classe dans l'API si vous désirez en apprendre davantage sur un objet d'interface graphique particulier. 


## Utilisation des objets d'interface graphique <span id="using-widgets"></span>

Les objets d'interface graphique sont utilisés en premier lieu dans des [vues](structure-views.md). Vous pouvez appeler la méthode [[yii\base\Widget::widget()]] pour utiliser un objet d'interface graphique dans une vue. Cette méthode accepte un tableau de [configuration](concept-configurations.md) pour initialiser l'objet graphique d'interface et retourne le résultat du rendu de cet objet. Par exemple, le code suivant insère un objet d'interface graphique de sélection de date  qui est configuré dans la langue *russe* et conserve l'entrée dans l'attribut  `from_date` du `$model`.

```php
<?php
use yii\jui\DatePicker;
?>
<?= DatePicker::widget([
    'model' => $model,
    'attribute' => 'from_date',
    'language' => 'ru',
    'clientOptions' => [
        'dateFormat' => 'yy-mm-dd',
    ],
]) ?>
```

Quelques objets d'interface graphique peuvent accepter un bloc de contenu qui doit être compris entre l'appel des méthodes [[yii\base\Widget::begin()]] et  [[yii\base\Widget::end()]]. Par exemple, le code suivant utilise l'objet d'interface graphique [[yii\widgets\ActiveForm]] pour générer une ouverture de balise `<form>` à l'endroit de l'appel de  `begin()` et une  fermeture de la même balise à l'endroit de l'appel de `end()`. Tout ce qui se trouve entre les deux est rendu tel quel. 

```php
<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>

<?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Login') ?>
    </div>

<?php ActiveForm::end(); ?>
```

Notez que contrairement à  la méthode [[yii\base\Widget::widget()]] qui retourne le résultat du rendu d'un objet d'interface graphique, la méthode [[yii\base\Widget::begin()]] retourne une instance de l'objet d'interface graphique que vous pouvez utiliser pour construire le contenu de l'objet d'interface. 

> Note: quelques objets d'interface graphique utilisent [la mise en tampon de sortie](http://php.net/manual/en/book.outcontrol.php) 
> pour ajuster le contenu inclus quand la méthode [[yii\base\Widget::end()]] est appelée. 
> Pour cette raison, l'appel des méthodes [[yii\base\Widget::begin()]] and
> [[yii\base\Widget::end()]]  est attendu dans le même fichier de vue.
> Ne pas respecter cette règle peut conduire à des résultats inattendus. 


### Configuration des variables globales par défaut

Les variables globales par défaut pour un objet d'interface graphique peuvent être configurées via le conteneur d'injection de dépendances (*DI container*) :

```php
\Yii::$container->set('yii\widgets\LinkPager', ['maxButtonCount' => 5]);
```

Voir  la section ["Utilisation pratique " dans le Guide du conteneur d'injection de dépendances](concept-di-container.md#practical-usage) pour les détails.


## Création d'objets d'interface graphique <span id="creating-widgets"></span>

Pour créer un objet d'interface graphique, étendez la classe [[yii\base\Widget]] et redéfinissez sa méthode  [[yii\base\Widget::init()]] et/ou sa méthode [[yii\base\Widget::run()]]. Ordinairement, la méthode `init()` devrait contenir le code qui normalise les propriétés de l'objet d'interface graphique, tandis que la méthode `run()` devrait contenir le  code qui génère le résultat du rendu de cet objet d'interface graphique. Le résultat du rendu peut être "renvoyé en écho" directement ou retourné comme une chaîne de caractères par la méthode `run()`.

Dans l'exemple suivant, `HelloWidget` encode en HTML et affiche le contenu assigné à sa propriété `message`.
Si la propriété n'est pas définie, il affiche  "Hello World" par defaut.

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class HelloWidget extends Widget
{
    public $message;

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = 'Hello World';
        }
    }

    public function run()
    {
        return Html::encode($this->message);
    }
}
```

Pour utiliser cet objet d'interface graphique, contentez-vous d'insérer le code suivant dans une vue :

```php
<?php
use app\components\HelloWidget;
?>
<?= HelloWidget::widget(['message' => 'Good morning']) ?>
```

Ce-dessous, nous présentons une variante de  `HelloWidget` qui prend le contenu inséré entre les appels des méthodes `begin()` et `end()`, l'encode en HTML et l'affiche.

```php
namespace app\components;

use yii\base\Widget;
use yii\helpers\Html;

class HelloWidget extends Widget
{
    public function init()
    {
        parent::init();
        ob_start();
    }

    public function run()
    {
        $content = ob_get_clean();
        return Html::encode($content);
    }
}
```

Comme vous pouvez le voir, le tampon de sortie de PHP est démarré dans `init()` de manière à ce que toute sortie entre les appels de `init()` et de  `run()`
puisse être capturée, traitée et retournée dans `run()`.

> Info: lorsque vous appelez [[yii\base\Widget::begin()]], une nouvelle instance de l'objet d'interface graphique est créé et sa méthode  `init()` est appelée à la fin de la construction de l'objet. Lorsque vous appelez [[yii\base\Widget::end()]], la méthode  `run()` est appelée et sa valeur de retour est renvoyée en écho par `end()`.

Le code suivant montre comment utiliser cette nouvelle variante de  `HelloWidget`:

```php
<?php
use app\components\HelloWidget;
?>
<?php HelloWidget::begin(); ?>

    content that may contain <tag>'s

<?php HelloWidget::end(); ?>
```

Parfois, un objet d'interface graphique peut avoir à rendre un gros bloc de contenu. Bien que vous puissiez incorporer le contenu dans la méthode `run()`, une meilleure approche consiste à le mettre dans une  [vue](structure-views.md) et à appeler la méthode [[yii\base\Widget::render()]] pour obtenir son rendu. Par exemple :

```php
public function run()
{
    return $this->render('hello');
}
```

Par défaut, les vues pour un objet d'interface graphique doivent être stockées dans le dossier `WidgetPath/views`, où `WidgetPath` représente le dossier contenant la classe de l'objet d'interface graphique. Par conséquent, l'exemple ci-dessus rend le fichier de vue `@app/components/views/hello.php`, en supposant que la classe de l'objet d'interface graphique est située dans le dossier `@app/components`. Vous pouvez redéfinir la méthode [[yii\base\Widget::getViewPath()]] pour personnaliser le dossier qui contient les fichiers de vue des objets d'interface graphique. 


## Meilleures pratiques <span id="best-practices"></span>

Les objets d'interface graphique sont une manière orientée objets de réutiliser du code de vues. 

Lors de la création d'objets d'interface graphique, vous devriez continuer de suivre le modèle d'architecture MVC. En général, vous devriez conserver la logique dans les classes d'objets d'interface graphique et la présentation dans les [vues](structure-views.md).

Les objets d'interface graphique devraient également être conçus pour être auto-suffisants. Cela veut dire que, lors de l'utilisation d'un tel objet, vous devriez être en mesure de vous contenter de le placer dans une vue sans rien faire d'autre. Cela peut s'avérer délicat si un objet d'interface graphique requiert des ressources externes, comme du CSS, du Javascript, des images, etc. Heureusement, Yii fournit une prise en charge des [paquets de ressources](structure-assets.md) que vous pouvez utiliser pour résoudre le problème. 


Quand un objet d'interface graphique contient du code de vue seulement, il est très similaire à une [vue](structure-views.md). En fait, dans ce cas, la seule différence est que l'objet d'interface graphique est une classe redistribuable, tandis qu'une vue est juste un simple script PHP que vous préférez conserver dans votre application. 
