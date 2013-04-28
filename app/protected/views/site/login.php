<?php
use yii\helpers\Html;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var app\models\LoginForm $model
 */
?>
<h1>Login</h1>

<p>Please fill out the following fields to login:</p>

<?php $form = $this->beginWidget('yii\widgets\ActiveForm'); ?>
	<?php echo $form->field($model, 'username')->textInput(); ?>
	<?php echo $form->field($model, 'password')->passwordInput(); ?>
	<?php
		$field = $form->field($model, 'username');
		echo $field->begin() . "\n"
			. $field->label() . "\n"
			. Html::activeTextInput($model, 'username') . "\n"
			. $field->error() . "\n"
			. $field->end();
	?>
	<?php echo Html::submitButton('Login'); ?>
<?php $this->endWidget(); ?>