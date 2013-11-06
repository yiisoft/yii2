<?php
use yii\helpers\Html;

/**
 * @var $this \yii\web\View
 * @var $content string
 * @var yii\gii\Generator[] $generators
 * @var yii\gii\Generator $activeGenerator
 */
$generators = Yii::$app->controller->module->generators;
$activeGenerator = Yii::$app->controller->generator;
?>
<?php $this->beginContent('@yii/gii/views/layouts/main.php'); ?>
<div class="row">
	<div class="col-lg-3">
		<div class="list-group">
			<?php
			foreach ($generators as $id => $generator) {
				$label = '<i class="glyphicon glyphicon-chevron-right"></i>' . Html::encode($generator->getName());
				echo Html::a($label, ['default/view', 'id' => $id], [
					'class' => $generator === $activeGenerator ? 'list-group-item active' : 'list-group-item',
				]);
			}
			?>
		</div>
	</div>
	<div class="col-lg-9">
		<?= $content ?>
	</div>
</div>
<?php $this->endContent(); ?>
