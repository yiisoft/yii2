Pobieranie danych dla wielu modeli
==================================

Kiedy mamy do czynienia ze skomplikowanym zestawem danych, jest możliwe, że trzeba będzie użyć wielu różnych modeli, aby pobrać te dane od użytkownika.
Dla przykładu - zakładając, że dane logowania użytkownika zapisane są w tabeli `user`, podczas gdy dane profilu użytkownika są przechowywane w tabeli `profile`,
będziesz chciał pobrać dane od użytkownika za pomocą modeli `User` oraz `Profile`. 
Dzięki wsparciu modeli i formularzy przez Yii, możesz rozwiązać ten problem w sposób nie różniący się za bardzo od przetwarzania pojedynczego modelu.

W poniższym przykładzie pokażemy jak utworzyć formularz, który pozwoli Ci na zbieranie danych dla obydwu modeli: `User` oraz `Profile`.

Na początek, akcja w kontrolerze do zbierania danych użytkownika oraz danych profilowych może zostać napisana następująco:

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

W akcji `update`, najpierw ładujemy modele `$user` oraz `$profile`, które zostaną zaktualizowane danymi z bazy.
Następnie wywołujemy metodę [[yii\base\Model::load()|load()]], aby wypełnić te dwa modele danymi wprowadzonymi przez użytkownika.
Na końcu modele zostają poddane walidacji i, jeśli wszystko jest w porządku, zapisane.
W przeciwnym razie zostanie wyrenderowany widok `update`, który zawiera następujący kod:

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

Jak widzisz, w widoku `update` tworzymy pola tekstowe używając dwóch modeli: `$user` oraz `$profile`.
