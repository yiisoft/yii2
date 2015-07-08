Работа с несколькими моделями
=============================

Когда имеешь дело со сложными данными, иногда может потребоваться использовать несколько разных моделей для обработки данных, введенных
пользователем. Для примера, предположим, что информация пользователя для входа хранится в таблице `user`, а данные профиля
хранятся в таблице `profile`, и вы можете захотеть обрабатывать входные данные о пользователе через модели `User` и `Profile`.
Учитывая поддержку Yii моделей и форм, вы можете решить данную задачу способом, не сильно отличающимся от обработки одинарной модели.

Далее мы покажем как можно создать форму, которая позволила бы вам собирать данные для обеих моделей `User` и `Profile`.

Действие контроллера для обработки данных пользователя и данных профиля может быть написано следующим образом,

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

В действии `update`, мы сначала загружаем из базы модели `$user` and `$profile`. Затем мы вызываем метод [[yii\base\Model::loadMultiple()]] 
для заполнения этих двух моделей данными, введенными пользователем. В случае успеха мы проверяем модели и сохраняем их. В противном случае 
мы рендерим представление `update`, которое содержит следующий контент:

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

Как вы можете видеть, в представлении `update` рендерятся поля ввода для двух моделей `$user` и `$profile`.