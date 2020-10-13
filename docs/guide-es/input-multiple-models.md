Obtención de datos para los modelos de múltiples
================================

Cuando se trata de algunos datos complejos, es posible que puede que tenga que utilizar varios modelos diferentes para recopilar
la entrada del usuario. Por ejemplo, suponiendo que la información de inicio de sesión del usuario se almacena en la tabla `user`, 
mientras que el perfil de usuario la información se almacena en la tabla `Profile`, es posible que desee para recoger los datos
de entrada sobre un usuario a través de un modelo `User` y un modelo `Profile`. Con el modelo de Yii y apoyo formulario, 
puede solucionar este problema de una manera que no es mucho diferente de la manipulación de un solo modelo.

En lo que sigue, vamos a mostrar cómo se puede crear un formulario que permitirá recoger datos tanto para los modelos `User` y 
`Profile`.

En primer lugar, la acción del controlador para la recogida de los datos del usuario y del perfil se puede escribir de la 
siguiente manera,

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

En la acción `update`, primero cargamos los modelos `User` y `Profile` que se actualicen desde la base de datos. Luego llamamos
[[yii\base\Model::load()]] para llenar estos dos modelos con la entrada del usuario. Si tiene éxito, se validará
los dos modelos y guardarlos. De lo contrario vamos a renderizar la vista `update` que tiene el siguiente contenido:

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

Como se puede ver, en el `update` vista que haría que los campos de entrada utilizando dos modelos `User` y `Profile`.
