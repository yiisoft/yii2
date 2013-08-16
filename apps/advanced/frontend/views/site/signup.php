<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 * @var common\models\User $model
 */
$this->title = 'Signup';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-signup">
	<h1><?php echo Html::encode($this->title); ?></h1>

	<p>Please fill out the following fields to signup:</p>

	<div class="row">
		<div class="col-lg-5">
			<?php $form = ActiveForm::begin(array('id' => 'form-signup')); ?>
				<?php echo $form->field($model, 'username'); ?>
				<?php echo $form->field($model, 'email'); ?>
				<?php echo $form->field($model, 'password')->passwordInput(); ?>
				<div class="form-group">
					<?php echo Html::submitButton('Signup', array('class' => 'btn btn-primary')); ?>
				</div>
			<?php ActiveForm::end(); ?>
		</div>
	</div>
</div>
