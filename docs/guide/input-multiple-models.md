Getting Data for Multiple Models
================================

When dealing with some complex data, it is possible that you may need to use multiple different models to collect
the user input. For example, assuming the user login information is stored in the `user` table while the user profile
information is stored in the `profile` table, you may want to collect the input data about a user through a `User` model 
and a `Profile` model. With the Yii model and form support, you can solve this problem in a way that is not much
different from handling a single model.

In the following, we will show how you can create a form that would allow you to collect data for both `User` and `Profile`
models.

First, the controller action for collecting the user and profile data can be written as follows, 

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
        $profile = Profile::findOne($id);
        
        if (!isset($user, $profile)) {
            throw new NotFoundHttpException("The user was not found.");
        }
        
        $user->scenario = 'update';
        $profile->scenario = 'update';
        
        if (Model::loadMultiple([$user, $profile], Yii::$app->request->post())) {
            if ($user->validate() && $profile->validate()) {
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

In the `update` action, we first load the `$user` and `$profile` models to be updated from the database. We then call 
[[yii\base\Model::loadMultiple()]] to populate these two models with the user input. If successful we will validate
the two models and save them. Otherwise we will render the `update` view which has the following content:

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'user-update-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($user, 'username') ?>

    ...other input fields...
    
    <?= $form->field($profile, 'website') ?>

    <?= Html::submitButton('Update', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>
```

As you can see, in the `update` view you would render input fields using two models `$user` and `$profile`.
