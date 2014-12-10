フォームを扱う
==============

> Note|注意: この節はまだ執筆中です。

Yii においてフォームを使用する主たる方法は [[yii\widgets\ActiveForm]] によるものです。
フォームがモデルに基づくものである場合はこの方法を優先すべきです。
これに加えて、[[yii\helpers\Html]] にはいくつかの有用なメソッドがあり、通常は、あらゆるフォームにボタンやヘルプテキストを追加するのに使うことが出来ます。

モデルに基づくフォームを作成する場合、最初のステップは、モデルそのものを定義することです。
モデルは、アクティブレコードクラス、あるいは、もっと汎用的な Model クラスから派生させることが出来ます。
このログインフォームの例では、汎用的なモデルを使用します。

```php
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;

    /**
     * @return array 検証規則
     */
    public function rules()
    {
        return [
            // username と password はともに必須
            [['username', 'password'], 'required'],
            // password は validatePassword() によって検証される
            ['password', 'validatePassword'],
        ];
    }

    /**
     * パスワードを検証する
     * このメソッドがパスワードのインライン検証に使用される
     */
    public function validatePassword()
    {
        $user = User::findByUsername($this->username);
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }

    /**
     * 提供された username と password でユーザをログインさせる。
     * @return boolean ユーザのログインが成功したかどうか
     */
    public function login()
    {
        if ($this->validate()) {
            $user = User::findByUsername($this->username);
            return true;
        } else {
            return false;
        }
    }
}
```

コントローラはこのモデルのインスタンスをビューに渡し、ビューでは [[yii\widgets\ActiveForm|ActiveForm]] ウィジェットが使われます。

```php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal'],
]) ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('ログイン', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
```

上記のコードでは、[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] がフォームのインスタンスを作成するだけでなく、フォームの開始をマークしています。
[[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] と [[yii\widgets\ActiveForm::end()|ActiveForm::end()]] の間に置かれた全てのコンテントが `<form>` タグによって囲まれます。
その他のウィジェットと同じように、ウィジェットをどのように構成すべきかに関するオプションを指定するために、`begin` メソッドに配列を渡すことが出来ます。
この例では、追加の CSS クラスと要素を特定するための ID が渡されて、開始 `<form>` タグに適用されています。

フォームの中で、フォームの要素を作成するために、ActiveForm ウィジェットの [[yii\widgets\ActiveForm::field()|ActiveForm::field()]] メソッドが呼ばれています。このメソッドは、要素のラベルと、適用できる JavaScript の検証メソッドがあれば、それらも追加します。
このメソッドの呼び出し結果を直接にエコーすると、結果は通常の (text の) インプットになります。
出力結果をカスタマイズするためには、このメソッドの呼び出しに追加のメソッドをチェーンします。

```php
<?= $form->field($model, 'password')->passwordInput() ?>

// または

<?= $form->field($model, 'username')->textInput()->hint('お名前を入力してください')->label('お名前') ?>
```

これで、フォームのフィールドによって定義されたテンプレートに従って、`<label>`、`<input>` など、全てのタグが生成されます。
これらのタグを自分で追加するためには、`Html` 経る派クラスを使うことが出来ます。

HTML5 のフィールドを使いたい場合は、次のように、インプットのタイプを直接に指定することが出来ます。

```php
<?= $form->field($model, 'email')->input('email') ?>
```

モデルの属性を指定するためにもっと洗練された方法を使うことも出来ます。
Specifying the attribute of the model can be done in more sophisticated ways. For example when an attribute may
take an array value when uploading multiple files or selecting multiple items you may specify it by appending `[]`
to the attribute name:

```php
// allow multiple files to be uploaded:
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// allow multiple items to be checked:
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

> **Tip**: in order to style required fields with asterisk you can use the following CSS:
>
```css
div.required label:after {
    content: " *";
    color: red;
}
```

Handling multiple models with a single form
-------------------------------------------

Sometimes you need to handle multiple models of the same kind in a single form. For example, multiple settings where
each setting is stored as name-value and is represented by `Setting` model. The
following shows how to implement it with Yii.

Let's start with controller action:

```php
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

In the code above we're using `indexBy` when retrieving models from the database to populate an array indexed by model ids.
These will be later used to identify form fields. `loadMultiple` fills multiple models with the form data coming from POST
and `validateMultiple` validates all models at once. In order to skip validation when saving we're passing `false` as
a parameter to `save`.

Now the form that's in `update` view:

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin();

foreach ($settings as $index => $setting) {
    echo Html::encode($setting->name) . ': ' . $form->field($setting, "[$index]value");
}

ActiveForm::end();
```

Here for each setting we are rendering name and an input with a value. It is important to add a proper index
to input name since that is how `loadMultiple` determines which model to fill with which values.
