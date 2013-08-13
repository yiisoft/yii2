<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var common\models\User $model
 */
$this->title = 'Reset password';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password">
	<h1><?php echo Html::encode($this->title); ?></h1>

	<p>Please choose your new password:</p>

	<div class="row">
		<div class="col-lg-5">
			<?php $form = ActiveForm::begin(array('id' => 'reset-password-form')); ?>
				<?php echo $form->field($model, 'password')->passwordInput(); ?>
				<div class="form-actions">
					<?php echo Html::submitButton('Save', array('class' => 'btn btn-primary')); ?>
				</div>
			<?php ActiveForm::end(); ?>
		</div>
	</div>
</div>