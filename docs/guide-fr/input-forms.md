Création de formulaires
=======================

La manière primaire d'utiliser des formulaires dans Yii de faire appel aux [[yii\widgets\ActiveForm]]. Cette approche doit être privilégiée lorsque le formulaire est basé sur un modèle. En plus, il existe quelques méthodes utiles dans [[yii\helpers\Html]] qui sont typiquement utilisées pour ajouter des boutons et des textes d'aides de toute forme.

Un formulaire, qui est affiché du côté client, possède dans la plupart des cas, un [modèle](structure-models.md) correspondant qui est utilisé pour valider ses entrées du côté serveur (lisez la section [Validation des entrées](input-validation.md) pour plus de détails sur la validation). Lors de la création de formulaires basés sur un modèle, la première étape est de définir le modèle lui-même. Le modèle peut être soit basé sur une classe d'[enregistrement actif](db-active-record.md) représentant quelques données de la base de données, soit sur une classe de modèle générique qui étend la classe [[yii\base\Model]]) pour capturer des entrées arbitraires, par exemple un formulaire de connexion. Dans l'exemple suivant, nous montrons comment utiliser un modèle générique pour un formulaire de connexion :

```php
<?php

class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // les règles de validation sont définies ici
        ];
    }
}
```

Dans le contrôleur, nous passons une instance de ce modèle à la vue, dans laquelle le composant graphique [[yii\widgets\ActiveForm|ActiveForm]] est utilisé pour afficher le formulaire :

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
```

Dans le code précédent, [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] ne crée pas seulement une instance de formulaire, mais il marque également le début du formulaire. Tout le contenu placé entre [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] et [[yii\widgets\ActiveForm::end()|ActiveForm::end()]] sera enveloppé dans la balise HTML `<form>`. Comme avec tout composant graphique, vous pouvez spécifier quelques options sur la façon dont le composant graphique est configuré en passant un tableau à la méthode `begin`. Dans ce cas précis, une classe CSS supplémentaire et un identifiant sont passés pour être utilisés dans l'ouverture de balise `<form>`. Pour connaître toutes les options disponibles, reportez-vous à la documentation de l'API de [[yii\widgets\ActiveForm]].

Afin de créer un élément *form* dans le formulaire, avec l'élément *label* et toute validation JavaScript applicable, la méthode [[yii\widgets\ActiveForm::field()|ActiveForm::field()]] est appelée. Elle retourne une instance de [[yii\widgets\ActiveField]]. Lorsque le résultat de cette méthode est renvoyé en écho directement, le résultat est un champ de saisie de texte régulier. Pour personnaliser la sortie, vous pouvez enchaîner des méthodes additionnelles de [[yii\widgets\ActiveField|ActiveField]] à cet appel :

```php
// un champ de saisie du mot de passe
<?= $form->field($model, 'password')->passwordInput() ?>
// ajoute une invite et une étiquette personnalisée
<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
// crée un élément HTML5 de saisie d'une adresse de courriel
<?= $form->field($model, 'email')->input('email') ?>
```

Cela crée toutes les balises `<label>`, `<input>` et autres, selon le [[yii\widgets\ActiveField::$template|modèle]] défini par le champ de formulaire. Le nom du champ de saisie est déterminé automatiquement à partir du [[yii\base\Model::formName()|nom de formulaire]] du modèle et du nom d'attribut. Par exemple, le nom du champ de saisie de l'attribut `username` dans l'exemple ci-dessus est `LoginForm[username]`. Cette règle de nommage aboutit à un tableau de tous les attributs du formulaire de connexion dans `$_POST['LoginForm']` côté serveur.

> Tip: si vous avez seulement un modèle dans un formulaire et que vous voulez simplifier le nom des champs de saisie, vous pouvez sauter la partie tableau en redéfinissant la méthode [[yii\base\Model::formName()|formName()]] du modèle pour qu'elle retourne une chaîne vide. Cela peut s'avérer utile pour les modèles de filtres utilisés dans le composant graphique [GridView](output-data-widgets.md#grid-view) pour créer des URL plus élégantes.

Spécifier l'attribut de modèle peut se faire de façon plus sophistiquée. Par exemple, lorsqu'un attribut peut prendre une valeur de tableau lors du chargement sur le serveur de multiples fichiers ou lors de la sélection de multiples items, vous pouvez le spécifier en ajoutant `[]` au nom d'attribut : 

```php
// permet à de multiples fichiers d'être chargés sur le serveur :
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// permet à de multiples items d'être cochés :
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

Soyez prudent lorsque vous nommez des éléments de formulaire tels que des boutons de soumission. Selon la [documentation de jQuery](https://api.jquery.com/submit/), certains noms sont réservés car ils peuvent créer des conflits :

> Les éléments *forms* et leurs éléments enfants ne devraient par utiliser des noms de champ de saisie, ou des identifiants que entrent en conflit avec les propriétés d'un élément de *form*, tels que `submit`, `length`, ou `method`. Les conflits de noms peuvent créer des échecs troublants. Pour une liste complètes des règles et pour vérifier votre code HTML à propos de ces problèmes, reportez-vous à [DOMLint](https://kangax.github.io/domlint/).

Des balises additionnelles HTML peuvent être ajoutées au formulaire en utilisant du HTML simple ou en utilisant les méthodes de la classe [[yii\helpers\Html|Html]]-helper comme cela est fait dans l'exemple ci-dessus avec le [[yii\helpers\Html::submitButton()|bouton de soumission]].


> Tip: si vous utilisez la base structurée *Twitter Bootstrap CSS* dans votre application, vous désirez peut-être utiliser [[yii\bootstrap\ActiveForm]] à la place de [[yii\widgets\ActiveForm]]. La première étend la deuxième et utilise les styles propres à Bootstrap lors de la génération des champs de saisie du formulaire.


> Tip: afin de styler les champs requis avec une astérisque, vous pouvez utiliser le CSS suivant :
>
> ```css
> div.required label.control-label:after {
>     content: " *";
>     color: red;
> }
> ```

Création d'une liste déroulante <span id="creating-activeform-dropdownlist"></span>
-------------------------------

Vous pouvez utiliser la méthode [dropDownList()](https://www.yiiframework.com/doc-2.0/yii-widgets-activefield.html#dropDownList()-detail) de ActiveForm pour créer une liste déroulante :


```php
use app\models\ProductCategory;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\Product */

echo $form->field($model, 'product_category')->dropdownList(
    ProductCategory::find()->select(['category_name', 'id'])->indexBy('id')->column(),
    ['prompt'=>'Select Category']
);
```

La valeur du champ de saisie de votre modèle est automatiquement pré-selectionnée 

Travail avec Pjax <span id="working-with-pjax"></span>
-----------------------

Le composant graphique [[yii\widgets\Pjax|Pjax]] vous permet de mettre à jour une certaine section d'une page plutôt que de recharger la page entière. Vous pouvez l'utiliser pour mettre à jour seulement le formulaire et remplacer son contenu après la soumission.

Vous pouvez configurer [[yii\widgets\Pjax::$formSelector|$formSelector]] pour spécifier quelles soumissions de formulaire peuvent déclencher pjax. Si cette propriété n'est pas définie, tous les formulaires avec l'attribut `data-pjax` dans le contenu englobé par Pjax déclenchent des requêtes pjax. 

```php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

Pjax::begin([
    // Pjax options
]);
    $form = ActiveForm::begin([
        'options' => ['data' => ['pjax' => true]],
        // plus d'options d'ActiveForm
    ]);

        // contenu de ActiveForm

    ActiveForm::end();
Pjax::end();
```
> Tip: soyez prudent avec les liens à l'intérieur du composant graphique [[yii\widgets\Pjax|Pjax]] car la réponse est également rendue dans le composant graphique. Pour éviter cela, utilisez l'attribut HTML `data-pjax="0"`.

#### Valeurs dans les boutons de soumission et dans les chargement de fichiers sur le serveur

Il y a des problèmes connus avec l'utilisation de `jQuery.serializeArray()` lorsqu'on manipule des [fichiers](https://github.com/jquery/jquery/issues/2321) et des [valeurs de boutons de soumission](https://github.com/jquery/jquery/issues/2321) qui ne peuvent être résolus et sont plutôt rendus obsolète en faveur de la classe `FormData` introduite en HTML5. 

Cela siginifie que la seule prise en charge officielle pour les fichiers et les valeurs de boutons de soumission avec ajax, ou en utilisant le composant graphique  [[yii\widgets\Pjax|Pjax]], dépend de la [prise en charge par le navigateur](https://developer.mozilla.org/fr/docs/Web/API/FormData#compatibilit%C3%A9_des_navigateurs) de la classe `FormData`.

Lectures d'approfondissement <span id="further-reading"></span>
----------------------------

La section suivante, [Validation des entrées](input-validation.md) prend en charge la validation des données soumises par le formulaire du côté serveur ainsi que la validation ajax et du côté client. 

Pour en apprendre plus sur les utilisations complexes de formulaires, vous pouvez lire les sections suivantes :

- [Collecte des champs de saisie tabulaires](input-tabular-input.md), pour collecter des données à destination de multiples modèles du même genre.
- [Obtention de données pour de multiples modèles](input-multiple-models.md), pour manipuler plusieurs modèles différents dans le même formulaire.
- [Chargement de fichiers sur le serveur](input-file-upload.md), sur la manière d'utiliser les formulaires pour charger des fichiers sur le serveur.
