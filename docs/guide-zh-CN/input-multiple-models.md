多模型的复合表单
==================================

当处理负载的数据时，可能会发生需要使用多个模型来收集用户的输入。例如，假设用户登录信息保存在`user`表中，但是用户详细信息却保存在`profile`表中，你也许会使用`User`模型和`Profile`模型来接受用户的输入。在Yii模型与表单的支持下，你可以非常方便用不同于单个模型的方式来解决该问题。

接下来，我们会使用一个例子阐述如何创建一个表单，它允许你同时为`User`和`Profile`模型收集数据。

首先，控制user和profile接收数据的控制器如下：

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

在`update`动作中，我们首先加载`$user`和`$profile`模型并设置他们为更新模式。然后我们使用[[yii\base\Model::load()]]方法将用户输入的数据填充到模型中。如果数据顺利填充了，我们会验证这两个模型之后在保存它们。但是，请注意我们使用了`save(false)`来避免验证通过的数据在模型中被重复验证。如果填充不成功，我们将会显示下面的`update`视图：

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

如上，在`update`视图中你需要给予输入框两个模型`$user`和`$profile`。