<?php
/**
 * @var \yii\web\View $this
 * @var string $content
 */
use yii\helpers\Html;

yii\debug\DebugAsset::register($this);
?>
<!DOCTYPE html>
<html>
<?php $this->beginPage() ?>
<head>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>
</body>
<?php $this->endPage() ?>
</html>
