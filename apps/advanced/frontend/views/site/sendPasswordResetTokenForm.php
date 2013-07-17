<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var frontend\models\SendPasswordResetTokenForm $model
 */
$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<h1><?php echo Html::encode($this->title); ?></h1>

<p>Please fill out your email. A link to reset password will be sent there.</p>

<?php $form = ActiveForm::begin(array('options' => array('class' => 'form-horizontal'))); ?>
	<?php echo $form->field($model, 'email')->textInput(); ?>
	<div class="form-actions">
		<?php echo Html::submitButton('Send', array('class' => 'btn btn-primary')); ?>
	</div>
<?php ActiveForm::end(); ?>
