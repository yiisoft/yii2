<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

/** @var \yii\db\ActiveRecord $model */
$class = $generator->modelClass;
$model = new $class;
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
	$safeAttributes = $model->getTableSchema()->columnNames;
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var <?php echo ltrim($generator->modelClass, '\\'); ?> $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-form">

	<?php echo '<?php'; ?> $form = ActiveForm::begin(); ?>

<?php foreach ($safeAttributes as $attribute) {
	echo "\t\t<?php echo " . $generator->generateActiveField($model, $attribute) . " ?>\n\n";
} ?>
		<div class="form-group">
			<?php echo '<?php'; ?> echo Html::submitButton($model->isNewRecord ? 'Create' : 'Update', array('class' => 'btn btn-primary')); ?>
		</div>

	<?php echo '<?php'; ?> ActiveForm::end(); ?>

</div>
