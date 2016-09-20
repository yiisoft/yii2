Collecte d'entrées tabulaires
=============================

Il arrive parfois que vous ayez besoin de manipuler plusieurs modèles de même sorte dans un formulaire unique. Par exemple, de multiples réglages où chacun des réglages est stocké sous forme de paire nom-valeur et est représenté par un modèle d'[enregistrement actif](db-active-record.md) `Setting`. Cette sorte de formulaire est aussi appelé « entrées tabulaires ». Par opposition à cela, la manipulation des modèles de différente sortes, est traitée dans la section [Formulaires complexes avec plusieurs modèles](input-multiple-models.md).

Ce qui suit montre comment mettre en œuvre les entrées tabulaires avec Yii. 

Il y a trois situations différentes à couvrir, qui doivent être traitées avec de légères différences :
- La mise à jour d'un jeu fixe d'enregistrement de la base de données.
- La création d'un jeu dynamique d'enregistrements.
- La mise à jour, création et suppression d'enregistrements sur une page.

Par contraste avec les formulaires de modèle unique expliqué précédemment, nous travaillons maintenant sur un tableau de modèles. Ce tableau est passé à la vue pour afficher les champs de saisie de chacun des modèles sous une forme ressemblant à un tableau. Nous allons utiliser les méthodes d'aide de [[yii\base\Model]] qui nous permettent le chargement et la validation de plusieurs modèles à la fois :

- [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] charge les données d'une requête `POST` dans un tableau de modèles.
- [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] valide un tableau de modèles.

### Mise à jour d'un jeu fixe d'enregistrements

Commençons par l'action du contrôleur :

```php
<?php

namespace app\controllers;

use Yii;
use yii\base\Model;
use yii\web\Controller;
use app\models\Setting;

class SettingsController extends Controller
{
    // ...

    public function actionUpdate()
    {
        $settings = Setting::find()->indexBy('id')->all();

        if (Model::loadMultiple($settings, Yii::$app->request->post()) && Model::validateMultiple($settings)) {
            foreach ($settings as $setting) {
                $setting->save(false);
            }
            return $this->redirect('index');
        }

        return $this->render('update', ['settings' => $settings]);
    }
}
```

Dans le code ci-dessus, nous utilisons [[yii\db\ActiveQuery::indexBy()|indexBy()]] lors de l'extraction de modèles depuis la base de données pour remplir un tableau indexé par les clés primaires des modèles. Celles-ci seront utilisées plus tard pour identifier les champs de formulaires. [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] remplit de multiples modèles avec les données du formulaire issues de la méthode `POST` et [[yii\base\Model::validateMultiple()|Model::validateMultiple()]] valide tous les modèles en une seule fois. Comme nous avons validé auparavant, nous passons maintenant `false` en paramètre à [[yii\db\ActiveRecord::save()|save()]] pour ne pas exécuter la validation deux fois. 

Maintenant, voyons le formulaire qui se trouve dans la vue `update` (mise à jour) :

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin();

foreach ($settings as $index => $setting) {
    echo $form->field($setting, "[$index]value")->label($setting->name);
}

ActiveForm::end();
```

Ici, pour chaque réglage, nous rendons un champ de saisie avec un nom indexé. Il est important d'ajouter un index approprié au nom d'un champ de saisie car c'est avec cela que [[yii\base\Model::loadMultiple()|Model::loadMultiple()]] détermine à quel modèle attribuer telles valeurs. 

### Création d'un jeu dynamique d'enregistrements

La création d'enregistrements est similaire à leur mise à jour, sauf que nous avons a instancier les modèles :

```php
public function actionCreate()
{
    $count = count(Yii::$app->request->post('Setting', []));
    $settings = [new Setting()];
    for($i = 1; $i < $count; $i++) {
        $settings[] = new Setting();
    }

    // ...
}
```

Ici nous créons un tableau initial de `$settings` (réglages) contenant un modèle par défaut de façon à ce qu'au moins un champ de texte soit visible dans la vue. De plus, nous ajoutons un modèle pour chacune des lignes d'entrée que nous recevons.

Dans la vue, nous pouvons utiliser JavaScript pour ajouter de nouvelles lignes dynamiquement. 

### Combinaison, mise à jour, création et suppression sur une page 

> Note: Cette section est en cours de création
>
> Elle est vide pour le moment.

TBD
