<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use <?php echo $generator->indexWidgetType === 'grid' ? 'yii\grid\GridView' : 'yii\widgets\ListView'; ?>;

/**
 * @var yii\base\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var <?php echo ltrim($generator->searchModelClass, '\\'); ?> $searchModel
 */

$this->title = '<?php echo Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))); ?>';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-index">

	<h1><?php echo "<?php"; ?> echo Html::encode($this->title); ?></h1>

	<?php echo '<?php'; ?> echo $this->render('_search', array('model' => $searchModel)); ?>

	<hr>

	<div>
		<?php echo '<?php'; ?> echo Html::a('Create <?php echo StringHelper::basename($generator->modelClass); ?>', array('create'), array('class' => 'btn btn-danger')); ?>
	</div>

<?php if ($generator->indexWidgetType === 'grid'): ?>
	<?php echo "<?php"; ?> echo GridView::widget(array(
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => array(
<?php
$count = 0;
foreach ($generator->getTableSchema()->columns as $column) {
	$format = $generator->generateColumnFormat($column);
	if (++$count < 6) {
		echo "\t\t\t'" . $column->name . ($format === 'text' ? '' : ':' . $format) . "',\n";
	} else {
		echo "\t\t\t// '" . $column->name . ($format === 'text' ? '' : ':' . $format) . "',\n";
	}
}
?>

			array(
				'class' => 'yii\grid\ActionColumn',
			),
		),
	)); ?>
<?php else: ?>
	<?php echo "<?php"; ?> echo ListView::widget(array(
		'dataProvider' => $dataProvider,
		'itemOptions' => array(
			'class' => 'item',
		),
		'itemView' => function ($model, $key, $index, $widget) {
			return Html::a(Html::encode($model-><?php echo $nameAttribute; ?>), array('view', <?php echo $urlParams; ?>));
		},
	)); ?>
<?php endif; ?>

</div>
