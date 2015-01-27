Working with Forms
==================

The primary way of using forms in Yii is through [[yii\widgets\ActiveForm]]. This approach should be preferred when
the form is based upon a model. Additionally, there are some useful methods in [[yii\helpers\Html]] that are typically
used for adding buttons and help text to any form.

A form, that is displayed on the client side, will in most cases have a corresponding model which is used
to validate its input on the server side (Check the [Validating Input](input-validation.md) section for more details on validation).
When creating model-based forms, the first step is to define the model itself. The model can be either based upon
an [Active Record](db-active-record.md) class, representing some data from the database, or a generic Model class
(extending from [[yii\base\Model]]) to capture arbitrary input, for example a login form.
In the following example we show, how a generic Model is used for a login form:

```php
<?php

class LoginForm extends \yii\base\Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            // define validation rules here
        ];
    }
}
```

In the controller, we will pass an instance of that model to the view, wherein the [[yii\widgets\ActiveForm|ActiveForm]]
widget is used to display the form:

```php
<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
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
[[yii\widgets\ActiveForm::end()|ActiveForm::end()]] will be wrapped within the HTML `<form>` tag.
As with any widget, you can specify some options as to how the widget should be configured by passing an array to
the `begin` method. In this case, an extra CSS class and identifying ID are passed to be used in the opening `<form>` tag.
For all available options, please refer to the API documentation of [[yii\widgets\ActiveForm]].

In order to create a form element in the form, along with the element's label, and any applicable JavaScript validation,
the [[yii\widgets\ActiveForm::field()|ActiveForm::field()]] method is called, which returns an instance of [[yii\widgets\ActiveField]].
When the result of this method is echoed directly, the result is a regular (text) input.
To customize the output, you can chain additional methods of [[yii\widgets\ActiveField|ActiveField]] to this call:

```php
// a password input
<?= $form->field($model, 'password')->passwordInput() ?>
// adding a hint and a customized label
<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
// creating a HTML5 email input element
<?= $form->field($model, 'email')->input('email') ?>
```

This will create all the `<label>`, `<input>` and other tags according to the [[yii\widgets\ActiveField::$template|template]] defined by the form field.
The name of the input field is determined automatically from the models [[yii\base\Model::formName()|form name] and attribute,
e.g. the name for the input field for the `username` attribute in the above example will be `LoginForm[username]` which will result in an array
of all attributes for the login form to be available in `$_POST['LoginForm']` on the server side.

Specifying the attribute of the model can be done in more sophisticated ways. For example when an attribute may
take an array value when uploading multiple files or selecting multiple items you may specify it by appending `[]`
to the attribute name:

```php
// allow multiple files to be uploaded:
echo $form->field($model, 'uploadFile[]')->fileInput(['multiple'=>'multiple']);

// allow multiple items to be checked:
echo $form->field($model, 'items[]')->checkboxList(['a' => 'Item A', 'b' => 'Item B', 'c' => 'Item C']);
```

Additional HTML tags can be added using plain HTML or using the methods from the [[yii\helpers\Html|Html]]-helper
class like it is done in the above example with [[yii\helpers\Html::submitButton()|Html::submitButton()]].


> Tip: If you are using Twitter Bootstrap CSS in your application you may want to use
> [[yii\bootstrap\ActiveForm]] instead of [[yii\widgets\ActiveForm]], which is an extension of the
> ActiveForm class that adds some additional styling that works well with the bootstrap CSS framework.


> Tip: in order to style required fields with asterisk you can use the following CSS:
>
> ```css
> div.required label:after {
>     content: " *";
>     color: red;
> }
> ```


Handling multiple models with a single form
-------------------------------------------

> Note: This section is under development.

Sometimes you need to handle multiple models of the same kind in a single form. For example, multiple settings where
each setting is stored as name-value and is represented by `Setting` model. This kind of form is also often
referred to as "tabular input". In contrast to this, handling different models of different kind, is handled in the section
[Complex Forms with Multiple Models](input-multiple-models).


The following shows how to implement tabular input with Yii.

Let's start with controller action:

```php
<?php

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
