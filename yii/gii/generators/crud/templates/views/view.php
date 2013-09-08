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

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\base\View $this
 * @var <?php echo ltrim($generator->modelClass, '\\'); ?> $model
 */

$this->title = $model-><?php echo $generator->getNameAttribute(); ?>;
?>
<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-view">

	<h1><?php echo "<?php"; ?> echo Html::encode($this->title); ?></h1>

	<?php echo '<?php'; ?> echo DetailView::widget(array(
		'model' => $model,
		'attributes' => array(
<?php
foreach ($model->getTableSchema()->columns as $column) {
	$format = $generator->generateColumnFormat($column);
	echo "\t\t\t'" . $column->name . ($format === 'text' ? '' : ':' . $format) . "',\n";
}
?>
		),
	)); ?>

</div>
