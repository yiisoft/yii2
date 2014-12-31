Working with Forms
==================

> Note: This section is under development.

The primary way of using forms in Yii is through [[yii\widgets\ActiveForm]]. This approach should be preferred when
the form is based upon  a model. Additionally, there are some useful methods in [[yii\helpers\Html]] that are typically
used for adding buttons and help text to any form.

When creating model-based forms, the first step is to define the model itself. The model can be either based upon the
Active Record class, or the more generic Model class. For this login example, a generic model will be used:

```php
use yii\base\Model;

class LoginForm extends Model
{
    public $username;
    public $password;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        $user = User::findByUsername($this->username);
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError('password', 'Incorrect username or password.');
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
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

The controller will pass an instance of that model to the view, wherein the [[yii\widgets\ActiveForm|ActiveForm]] widget is used:

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
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>
```

In the above code, [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] not only creates a form instance, but also marks the beginning of the form.
All of the content placed between [[yii\widgets\ActiveForm::begin()|ActiveForm::begin()]] and
[[yii\widgets\ActiveForm::end()|ActiveForm::end()]] will be wrapped within the `<form>` tag.
As with any widget, you can specify some options as to how the widget should be configured by passing an array to
the `begin` method. In this case, an extra CSS class and identifying ID are passed to be used in the opening `<form>` tag.

In order to create a form element in the form, along with the element's label, and any applicable JavaScript validation,
the [[yii\widgets\ActiveForm::field()|ActiveForm::field()]] method of the Active Form widget is called.
When the invocation of this method is echoed directly, the result is a regular (text) input.
To customize the output, you can chain additional methods to this call:

```php
<?= $form->field($model, 'password')->passwordInput() ?>

// or

<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
```

This will create all the `<label>`, `<input>` and other tags according to the template defined by the form field.
To add these tags yourself you can use the `Html` helper class.

If you want to use one of HTML5 fields you may specify input type directly like the following:

```php
<?= $form->field($model, 'email')->input('email') ?>
```

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
