<?php
/**
 * This is the template for generating an action view file.
 *
 * @var yii\web\View $this
 * @var yii\gii\generators\form\Generator $generator
 */

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var <?= $generator->modelClass ?> $model
 * @var ActiveForm $form
 */
<?= "?>" ?>

<div class="<?= str_replace('/', '-', trim($generator->viewName, '_')) ?>">

	<?= "<?php " ?>$form = ActiveForm::begin(); ?>

	<?php foreach ($generator->getModelAttributes() as $attribute): ?>
	<?= "<?= " ?>$form->field($model, '<?= $attribute ?>') ?>
	<?php endforeach; ?>

		<div class="form-group">
			<?= "<?= " ?>Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
		</div>
	<?= "<?php " ?>ActiveForm::end(); ?>

</div><!-- <?= str_replace('/', '-', trim($generator->viewName, '-')) ?> -->
