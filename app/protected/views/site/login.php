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
	<?php echo $form->field($model, 'rememberMe')->checkbox(); ?>
	<?php echo Html::submitButton('Login'); ?>
<?php $this->endWidget(); ?>