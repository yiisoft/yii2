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
 * @var <?=ltrim($generator->searchModelClass, '\\'); ?> $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="<?=Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-search">

	<?='<?php'; ?> $form = ActiveForm::begin(array(
		'action' => array('index'),
		'method' => 'get',
	)); ?>

<?php
$count = 0;
foreach ($generator->getTableSchema()->getColumnNames() as $attribute) {
	if (++$count < 6) {
		echo "\t\t<?=" . $generator->generateActiveSearchField($attribute) . " ?>\n";
	} else {
		echo "\t\t<?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n";
	}
}
?>
		<div class="form-group">
			<?='<?php'; ?> echo Html::submitButton('Search', array('class' => 'btn btn-primary')); ?>
			<?='<?php'; ?> echo Html::resetButton('Reset', array('class' => 'btn btn-default')); ?>
		</div>

	<?='<?php'; ?> ActiveForm::end(); ?>

</div>
