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

In the `update` action, we first load the `$user` and `$profile` models to be updated from the database. We then call 
[[yii\base\Model::load()]] to populate these two models with the user input. If loading is successful, we will validate
the two models and then save them &mdash; please note that we use `save(false)` to skip over validations inside the models
as the user input data have already been validated. If loading is not successful, we will render the `update` view which
has the following content:

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
