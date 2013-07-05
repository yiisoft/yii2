<?php

use yii\helpers\Html;

/**
 * @var \yii\base\View $this
 * @var string $tag
 * @var \yii\debug\Panel[] $panels
 * @var \yii\debug\Panel $activePanel
 */
?>
<?php foreach ($panels as $panel): ?>
<?php echo Html::a(Html::encode($panel->getName()), array('debug/default/index', 'tag' => $tag, 'panel' => $panel->id)); ?><br>
<?php endforeach; ?>

<?php echo $activePanel->getDetail(); ?>
