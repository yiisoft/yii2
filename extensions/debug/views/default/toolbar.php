<?php
/**
 * @var \yii\web\View $this
 * @var \yii\debug\Panel[] $panels
 * @var string $tag
 * @var string $position
 */
use yii\helpers\Url;

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

$firstPanel = reset($panels);
$url = $firstPanel->getUrl();
?>
<div id="yii-debug-toolbar" class="yii-debug-toolbar-<?= $position ?>">
    <div class="yii-debug-toolbar-block title">
        <a href="<?= Url::toRoute(['index']) ?>">
            <img width="29" height="30" alt="" src="<?= \yii\debug\Module::getYiiLogo() ?>">
            Yii Debugger
        </a>
    </div>

	<?php foreach ($panels as $panel): ?>
	    <?= $panel->getSummary() ?>
	<?php endforeach; ?>
	<span class="yii-debug-toolbar-toggler" onclick="<?= $minJs ?>">›</span>
</div>
<div id="yii-debug-toolbar-min">
	<a href="<?= $url ?>" title="Open Yii Debugger" id="yii-debug-toolbar-logo">
		<img width="29" height="30" alt="" src="<?= \yii\debug\Module::getYiiLogo() ?>">
	</a>
	<span class="yii-debug-toolbar-toggler" onclick="<?= $maxJs ?>">‹</span>
</div>
