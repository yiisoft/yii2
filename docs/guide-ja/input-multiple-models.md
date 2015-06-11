複数のモデルのデータを取得する
==============================

複雑なデータを扱う場合には、複数の異なるモデルを使用してユーザの入力を収集する必要があることがあり得ます。
例えば、ユーザのログイン情報は `user` テーブルに保存されているけれども、ユーザのプロファイル情報は `profile` テーブルに保存されているという場合を考えて見ると、ユーザに関して入力されたデータを `User` モデルと `Profile` モデルによって収集しなければならないでしょう。
Yii のモデルとフォームのサポートを使えば、単一のモデルを扱うのとそれほど違いのない方法によってこの問題を解決することが出来ます。

下記において、`User` と `Profile` の二つのモデルのデータを収集することが出来るフォームをどのようにして作成することが出来るかを示します。

最初に、ユーザとプロファイルのデータを収集するためのコントローラアクションは、次のように書くことが出来ます。

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
            throw new NotFoundHttpException("ユーザが見つかりませんでした。");
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

この `update` アクションでは、最初に、更新の対象になる `$user` と `$profile` のモデルをデータベースからロードします。
次に [[yii\base\Model::loadMultiple()]] を呼んで、これら二つのモデルにユーザ入力を代入します。
代入が成功すれば、二つのモデルを検証して保存します。
そうでない場合は、次の内容を持つ `update` ビューをレンダリングします。

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

    <?= Html::submitButton('更新', ['class' => 'btn btn-primary']) ?>
<?php ActiveForm::end() ?>
```

ご覧のように、`update` ビューでは、二つのモデル、すなわち `$user` と `$profile` を使ってインプットフィールドをレンダリングすることになります。
