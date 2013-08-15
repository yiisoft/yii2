<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var yii\widgets\ActiveForm $form
 */
?>
<div class="controller-form">
	<div class="row">
		<div class="col-lg-6">
			<?php $form = ActiveForm::begin(array('id' => 'login-form')); ?>
			<?php echo $form->field($model, 'controller')->hint('
				Controller ID is case-sensitive and can contain module ID(s). For example:
				<ul>
					<li><code>order</code> generates <code>OrderController.php</code></li>
					<li><code>order-item</code> generates <code>OrderItemController.php</code></li>
					<li><code>admin/user</code> generates <code>UserController.php</code> within the <code>admin</code> module.</li>
				</ul>
			'); ?>
			<?php echo $form->field($model, 'baseClass')->hint('
				This is the class that the new controller class will extend from.
				Please make sure the class exists and can be autoloaded.
			'); ?>
			<?php echo $form->field($model, 'actions')->hint('
				Provide one or multiple action IDs to generate empty action method(s) in the controller.
				Separate multiple action IDs with commas or spaces.
			'); ?>
			<div class="form-actions">
				<?php echo Html::submitButton('Preview', array('class' => 'btn btn-primary')); ?>
			</div>
			<?php ActiveForm::end(); ?>
		</div>
	</div>
</div>
