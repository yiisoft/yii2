Creating Forms
==============

The primary way of using forms in Yii is through [[yii\widgets\ActiveForm]]. This approach should be preferred when
the form is based upon a model. Additionally, there are some useful methods in [[yii\helpers\Html]] that are typically
used for adding buttons and help text to any form.

A form, that is displayed on the client side, will in most cases have a corresponding [model](structure-models.md) which is used
to validate its input on the server side (Check the [Validating Input](input-validation.md) section for more details on validation).
When creating model-based forms, the first step is to define the model itself. The model can be either based upon
an [Active Record](db-active-record.md) class, representing some data from the database, or a generic Model class
(extending from [[yii\base\Model]]) to capture arbitrary input, for example a login form.
In the following example, we show how a generic model can be used for a login form:

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
The name of the input field is determined automatically from the model's [[yii\base\Model::formName()|form name]] and the attribute name.
For example, the name for the input field for the `username` attribute in the above example will be `LoginForm[username]`. This naming rule will result in an array
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

Additional HTML tags can be added to the form using plain HTML or using the methods from the [[yii\helpers\Html|Html]]-helper
class like it is done in the above example with [[yii\helpers\Html::submitButton()|Html::submitButton()]].


> Tip: If you are using Twitter Bootstrap CSS in your application you may want to use
> [[yii\bootstrap\ActiveForm]] instead of [[yii\widgets\ActiveForm]]. The former extends from the latter and
> uses Bootstrap-specific styles when generating form input fields.


> Tip: In order to style required fields with asterisks, you can use the following CSS:
>
> ```css
> div.required label:after {
>     content: " *";
>     color: red;
> }
> ```

The next section [Validating Input](input-validation.md) handles the validation of the submitted form data on the server
side as well as ajax- and client side validation.

To read about more complex usage of forms, you may want to check out the following sections:

- [Collecting Tabular Input](input-tabular-input.md) for collecting data for multiple models of the same kind.
- [Getting Data for Multiple Models](input-multiple-models.md) for handling multiple different models in the same form.
- [Uploading Files](input-file-upload.md) on how to use forms for uploading files.
