<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\base\View $this
 * @var <?=ltrim($generator->modelClass, '\\'); ?> $model
 */

$this->title = $model-><?=$generator->getNameAttribute(); ?>;
$this->params['breadcrumbs'][] = array('label' => '<?=Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))); ?>', 'url' => array('index'));
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?=Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-view">

	<h1><?="<?php"; ?> echo Html::encode($this->title); ?></h1>

	<p>
		<?='<?php'; ?> echo Html::a('Update', array('update', <?=$urlParams; ?>), array('class' => 'btn btn-primary')); ?>
		<?='<?php'; ?> echo Html::a('Delete', array('delete', <?=$urlParams; ?>), array(
			'class' => 'btn btn-danger',
			'data-confirm' => Yii::t('app', 'Are you sure to delete this item?'),
			'data-method' => 'post',
		)); ?>
	</p>

	<?='<?php'; ?> echo DetailView::widget(array(
		'model' => $model,
		'attributes' => array(
<?php
foreach ($generator->getTableSchema()->columns as $column) {
	$format = $generator->generateColumnFormat($column);
	echo "\t\t\t'" . $column->name . ($format === 'text' ? '' : ':' . $format) . "',\n";
}
?>
		),
	)); ?>

</div>
