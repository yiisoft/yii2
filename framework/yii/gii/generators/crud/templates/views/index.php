<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @var yii\base\View $this
 * @var yii\gii\generators\crud\Generator $generator
 */

/** @var \yii\db\ActiveRecord $model */
$class = $generator->modelClass;
$pks = $class::primaryKey();
if (count($pks) === 1) {
	$viewUrl = "array('view', 'id' => \$model->{$pks[0]})";
} else {
	$params = array();
	foreach ($pks as $pk) {
		$params[] = "'$pk' => \$model->$pk";
	}
	$viewUrl = "array('view', " . implode(', ', $params) . ')';
}

$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>

use yii\helpers\Html;
use <?php echo $generator->indexWidgetType === 'grid' ? 'yii\grid\GridView' : 'yii\widgets\ListView'; ?>;

/**
 * @var yii\base\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 */

$this->title = '<?php echo Inflector::pluralize(Inflector::camel2words(StringHelper::basename($generator->modelClass))); ?>';
?>
<div class="<?php echo Inflector::camel2id(StringHelper::basename($generator->modelClass)); ?>-index">

	<h1><?php echo "<?php"; ?> echo Html::encode($this->title); ?></h1>

<?php if ($generator->indexWidgetType === 'grid'): ?>

<?php else: ?>
<?php echo "\t<?php"; ?> echo ListView::widget(array(
		'dataProvider' => $dataProvider,
		'itemView' => function ($model, $key, $index, $widget) {
			return Html::a(Html::encode($model-><?php echo $nameAttribute; ?>), <?php echo $viewUrl; ?>);
		},
	)); ?>
<?php endif; ?>

</div>
