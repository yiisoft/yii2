Working with forms
==================

The primary way of using forms in Yii is through [[yii\widgets\ActiveForm]]. This approach should be preferred when the form is based upon  a model. Additionally, there are some useful methods in [[\yii\helpers\Html]] that are typically used for adding buttons and help text to any form.

When creating model-based forms, the first step is to define the model itself. The model can be either based upon the Active Record class, or the more generic Model class. For this login example, a generic model will be used:

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
			['username, password', 'required'],
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

The controller will pass an instance of that model to the view, wherein the Active Form widget is used:

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

In the above code, `ActiveForm::begin()` not only creates a form instance, but also marks the beginning of the form. All of the content
placed between `ActiveForm::begin()` and `ActiveForm::end()` will be wrapped within the `<form>` tag. As with any widget, you can specify some options as to how the widget should be configured by passing an array to the `begin` method. In this case, an extra CSS class and identifying ID are passed to be used in the opening `<form>` tag.

In order to create a form element in the form, along with the element's label, and any application JavaScript validation, the `field` method of the Active Form widget is called. When the invocation of this method is echoed directly, the result is a regular (text) input. To
customize the output, you can chain additional methods to this call:

```php
<?= $form->field($model, 'password')->passwordInput() ?>

// or

<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
```

This will create all the `<label>`, `<input>` and other tags according to the template defined by the form field.
To add these tags yourself you can use the `Html` helper class. The following is equivalent to the code above:

```php
<?= Html::activeLabel($model, 'password') ?>
<?= Html::activePasswordInput($model, 'password') ?>
<?= Html::error($model, 'password') ?>

or

<?= Html::activeLabel($model, 'username', ['label' => 'name']) ?>
<?= Html::activeTextInput($model, 'username') ?>
<?= Html::error($model, 'username') ?>
<div class="hint-block">Please enter your name</div>
```
