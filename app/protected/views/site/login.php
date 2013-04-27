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
	<?php
		$field = $form->beginField($model, 'username');
		echo $field->label() . "\n" . $field->textInput() . "\n" . $field->error() . "\n";
		$form->endField();

		$field = $form->beginField($model, 'password');
		echo $field->label() . "\n" . $field->textInput() . "\n" . $field->error() . "\n";
		$form->endField();
	?>
	<?php echo Html::submitButton('Login'); ?>
<?php $this->endWidget(); ?>