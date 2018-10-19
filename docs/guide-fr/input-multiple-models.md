Obtenir des données pour plusieurs modèles
==========================================

Lorsque vous avez affaire à des données complexes, il est possible que vous ayez besoin d'utiliser plusieurs modèles différents pour collecter des saisies de l'utilisateur. Par exemple, en supposant que les informations de connexion de l'utilisateur sont stockées dans la table `user` tandis que les informations de son profil sont stockées dans la table `profil`, vous désirez peut-être collecter les données de l'utilisateur via un modèle `User` et un modèle `Profile`. Avec la prise en charge par Yii des modèles et des formulaires, vous pouvez résoudre ce problème d'une façon qui ne diffère qu'assez peu de celle consistant à utiliser un modèle unique. 

Dans ce qui suit, nous montrons comment créer un formulaire que permet la collecte de données pour les deux modèles, `User` et `Profile`, à la fois.

Tout d'abord, l'action de contrôleur pour la collecte des données de connexion (user) et des données de profil, peut être écrite comme suit :

```php
namespace app\controllers;

use Yii;
use yii\base\Model;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\User;
use app\models\Profile;

class UserController extends Controller
{
    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException("The user was not found.");
        }
        
        $profile = Profile::findOne($user->profile_id);
        
        if (!$profile) {
            throw new NotFoundHttpException("The user has no profile.");
        }
        
        $user->scenario = 'update';
        $profile->scenario = 'update';
        
        if ($user->load(Yii::$app->request->post()) && $profile->load(Yii::$app->request->post())) {
            $isValid = $user->validate();
            $isValid = $profile->validate() && $isValid;
            if ($isValid) {
                $user->save(false);
                $profile->save(false);
                return $this->redirect(['user/view', 'id' => $id]);
            }
        }
        
        return $this->render('update', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }
}
```

Dans l'action `update`, nous commençons par charger les données des modèles, `$user` et `$profile`, à mettre à jour dans la base de données. Puis nous appelons [[yii\base\Model::load()]] pour remplir les deux modèles avec les entrées de l'utilisateur. Si tout se passe bien, nous validons les deux modèles et les sauvegardons. Autrement, nous rendons la vue `update` avec le contenu suivant :

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'user-update-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($user, 'username') ?>

    ...autres champs de saisie...
    
    <?= $form->field($profile, 'website') ?>

    <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>
```

Comme vous le voyez, la vue `update` rend les champs de saisie de deux modèles `$user` et `$profile`.
