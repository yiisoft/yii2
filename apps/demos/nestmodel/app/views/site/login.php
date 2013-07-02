<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var app\models\LoginForm $model
 */
$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?php echo Html::encode($this->title); ?></h1>

<p>Please fill out the following fields to login:</p>

<?php $form = ActiveForm::begin(array('options' => array('class' => 'form-horizontal', 'id' => 'login-form'))); ?>
	<?php echo $form->field($model, 'username')->textInput(); ?>
	<?php echo $form->field($model, 'password')->passwordInput(); ?>
	<?php echo $form->field($model, 'rememberMe')->checkbox(); ?>
	<div class="form-actions">
		<?php echo Html::submitButton('Login', array('class' => 'btn btn-primary')); ?>
	</div>
<?php ActiveForm::end(); ?>
