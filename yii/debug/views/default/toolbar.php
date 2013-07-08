<?php
/**
 * @var \yii\base\View $this
 * @var \yii\debug\Panel[] $panels
 * @var string $tag
 */
use yii\debug\panels\ConfigPanel;

$minJs = <<<EOD
document.getElementById('yii-debug-toolbar').style.display = 'none';
document.getElementById('yii-debug-toolbar-min').style.display = 'block';
if (window.localStorage) {
	localStorage.setItem('yii-debug-toolbar', 'minimized');
}
EOD;

$maxJs = <<<EOD
document.getElementById('yii-debug-toolbar-min').style.display = 'none';
document.getElementById('yii-debug-toolbar').style.display = 'block';
if (window.localStorage) {
	localStorage.setItem('yii-debug-toolbar', 'maximized');
}
EOD;

$url = $panels['request']->getUrl();
?>
<style>
	<?php echo $this->renderFile(__DIR__ . '/toolbar.css'); ?>
</style>
<div id="yii-debug-toolbar">
	<?php foreach ($panels as $panel): ?>
	<?php echo $panel->getSummary(); ?>
	<?php endforeach; ?>
	<a class="yii-debug-toolbar-toggler" href="javascript:void(0);" onclick="<?php echo $minJs; ?>">›</a>
</div>
<div id="yii-debug-toolbar-min">
	<a href="<?php echo $url; ?>" title="Open Yii Debugger" id="yii-debug-toolbar-logo">
		<img width="29" height="30" alt="" src="<?php echo ConfigPanel::getYiiLogo(); ?>">
	</a>
	<a class="yii-debug-toolbar-toggler" href="javascript:void(0);" onclick="<?php echo $maxJs; ?>">‹</a>
</div>
