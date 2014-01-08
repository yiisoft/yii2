<?php
/**
 * @var \yii\web\View $this
 * @var \yii\debug\Panel[] $panels
 * @var string $tag
 * @var string $position
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
<div id="yii-debug-toolbar" class="yii-debug-toolbar-<?= $position ?>">
	<?php foreach ($panels as $panel): ?>
	<?= $panel->getSummary() ?>
	<?php endforeach; ?>
	<span class="yii-debug-toolbar-toggler" onclick="<?= $minJs ?>">›</span>
</div>
<div id="yii-debug-toolbar-min">
	<a href="<?= $url ?>" title="Open Yii Debugger" id="yii-debug-toolbar-logo">
		<img width="29" height="30" alt="" src="<?= ConfigPanel::getYiiLogo() ?>">
	</a>
	<span class="yii-debug-toolbar-toggler" onclick="<?= $maxJs ?>">‹</span>
</div>
