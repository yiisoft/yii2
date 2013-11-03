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
use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;

/**
 * @var yii\base\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var <?= ltrim($generator->searchModelClass, '\\') ?> $searchModel
 */

$this->title = '<?= Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))) ?>';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

	<h1><?= "<?= " ?>Html::encode($this->title) ?></h1>

	<?= "<?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>

	<p>
		<?= "<?= " ?>Html::a('Create <?= StringHelper::basename($generator->modelClass) ?>', ['create'], ['class' => 'btn btn-success']) ?>
	</p>

<?php if ($generator->indexWidgetType === 'grid'): ?>
	<?= "<?php " ?>echo GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],

<?php
$count = 0;
foreach ($generator->getTableSchema()->columns as $column) {
	$format = $generator->generateColumnFormat($column);
	if (++$count < 6) {
		echo "\t\t\t'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
	} else {
		echo "\t\t\t// '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
	}
}
?>

			['class' => 'yii\grid\ActionColumn'],
		],
	]); ?>
<?php else: ?>
	<?= "<?php " ?>echo ListView::widget([
		'dataProvider' => $dataProvider,
		'itemOptions' => ['class' => 'item'],
		'itemView' => function ($model, $key, $index, $widget) {
			return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
		},
	]); ?>
<?php endif; ?>

</div>
