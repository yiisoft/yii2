<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\base\View $this
 * @var <?php echo ltrim($generator->searchModelClass, '\\'); ?> $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-search">

	<?php echo '<?php'; ?> $form = ActiveForm::begin(array('method' => 'get')); ?>

<?php
$count = 0;
foreach ($generator->getTableSchema()->getColumnNames() as $attribute) {
	if (++$count < 6) {
		echo "\t\t<?php echo " . $generator->generateActiveSearchField($attribute) . " ?>\n";
	} else {
		echo "\t\t<?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n";
	}
}
?>
		<div class="form-group">
			<?php echo '<?php'; ?> echo Html::submitButton('Search', array('class' => 'btn btn-primary')); ?>
			<?php echo '<?php'; ?> echo Html::resetButton('Reset', array('class' => 'btn btn-default')); ?>
		</div>

	<?php echo '<?php'; ?> ActiveForm::end(); ?>

</div>
