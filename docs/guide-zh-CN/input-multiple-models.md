多模型的复合表单
==================================

当需要处理复杂数据，很可能你需要使用多个不同的模型来收集用户提交的数据。
举例来说，假设用户登录信息保存在 `user` 表，但是用户基本信息保存在 `profile` 表，
你可能需要同时使用 `User` 模型和 `Profile` 模型来获取用户登录信息和基本信息。
使用 Yii 提供的模型和表单支持，解决这样的问题和处理单一模型并不会有太大的区别。


下面，我们将为你展示怎样创建一个表单并同时处理 `User` 和 `Profile` 这两个模型。


首先，控制器中收集用户提交数据的动作(action)可以按照下面写的这样，

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
            throw new NotFoundHttpException("没有找到用户登录信息。");
        }
        
        $profile = Profile::findOne($user->profile_id);
        
        if (!$profile) {
            throw new NotFoundHttpException("没有找到用户基本信息。");
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

在 `update` 动作中，我们首先从数据库中获取需要更新的 `$user` 和 `$profile` 这两个模型。
我们可以调用 [[yii\base\Model::load()]] 方法把用户提交数据填充到两个模型中。
如果加载成功，验证两个表单并保存 &mdash; 请注意我们使用了 `save(false)` 方法用来忽略内部保存时的二次验证，
因为用户输入的数据已经手动验证过了。
如果填充数据失败，直接显示下面的 `update` 视图内容：

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

    <?= Html::submitButton('更新数据', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>
```

你可以看到，在 `update` 视图中，我们同时显示了两个模型 `$user` 和 `$profile` 的属性的输入栏。
