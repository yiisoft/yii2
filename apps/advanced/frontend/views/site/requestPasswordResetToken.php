<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var common\models\User $model
 */
$this->title = 'Request password reset';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-request-password-reset">
	<h1><?php echo Html::encode($this->title); ?></h1>

	<p>Please fill out your email. A link to reset password will be sent there.</p>

	<div class="row">
		<div class="col-lg-5">
			<?php $form = ActiveForm::begin(array('id' => 'request-password-reset-form')); ?>
				<?php echo $form->field($model, 'email'); ?>
				<div class="form-group">
					<?php echo Html::submitButton('Send', array('class' => 'btn btn-primary')); ?>
				</div>
			<?php ActiveForm::end(); ?>
		</div>
	</div>
</div>
