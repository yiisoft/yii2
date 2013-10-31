Working with forms
==================

The primary way of using forms in Yii is [[\yii\widgets\ActiveForm]]. It should be preferred when you have a model
behind a form. Additionally there are some useful methods in [[\yii\helpers\Html]] that are typically used for adding
buttons and help text.

First step creating a form is to create a model. It can be either Active Record or regular Model. Let's use regular
login model as an example:

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

In controller we're passing model to view where Active Form is used:

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

In the code above `ActiveForm::begin()` not only creates form instance but marks the beginning of the form. All the content
that is located between `ActiveForm::begin()` and `ActiveForm::end()` will be wrapped with appropriate `<form>` tag.
Same as with any other widget you can specify some options passing an array to `begin` method. In our case we're adding
extra CSS class and specifying ID that will be used in the tag.

In order to insert a form field along with its label all necessary validation JavaScript we're calling `field` method
and it gives back `\yii\widgets\ActiveField`. It it's echoed directly it creates a regular input. In case you want to
customize it you can add a chain of additional methods:

```php
<?= $form->field($model, 'password')->passwordInput() ?>

// or

<?= $form->field($model, 'username')->textInput()->hint('Please enter your name')->label('Name') ?>
```
