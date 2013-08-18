<?php
/**
 * This is the template for generating an action view file.
 *
 * @var yii\base\View $this
 * @var yii\gii\generators\form\Generator $generator
 */
?>
<?php echo "<?php\n"; ?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var <?php echo $generator->modelClass; ?> $model
 * @var ActiveForm $form
 */
<?php echo "?>"; ?>

<div class="<?php echo str_replace('/', '-', trim($generator->viewName, '_')); ?>">

	<?php echo '<?php'; ?> $form = ActiveForm::begin(); ?>

	<?php foreach ($generator->getModelAttributes() as $attribute): ?>
	<?php echo '<?php'; ?> echo $form->field($model, '<?php echo $attribute; ?>'); ?>
	<?php endforeach; ?>

		<div class="form-group">
			<?php echo '<?php'; ?> echo Html::submitButton('Submit', array('class' => 'btn btn-primary')); ?>
		</div>
	<?php echo '<?php'; ?> ActiveForm::end(); ?>

</div><!-- <?php echo str_replace('/', '-', trim($generator->viewName, '-')); ?> -->
