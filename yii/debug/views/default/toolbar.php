<?php
/**
 * @var \yii\base\View $this
 * @var \yii\debug\Panel[] $panels
 * @var string $tag
 */
use yii\helpers\Html;
?>
<style>
	<?php echo $this->renderFile(__DIR__ . '/toolbar.css'); ?>
</style>
<div id="yii-debug-toolbar">
	<div class="yii-debug-toolbar-block debugger-link">
		<?php echo Html::a('Yii Debugger', array('index', 'tag' => $tag), array('class' => 'label')); ?>
	</div>
	<?php foreach ($panels as $panel): ?>
	<?php echo $panel->getSummary(); ?>
	<?php endforeach; ?>
</div>
