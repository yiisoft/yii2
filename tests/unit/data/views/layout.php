<?php
/**
 * @var \yii\web\View $this
 * @var string $content
 */
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html>
<head>
	<title>Test</title>
	<?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>

<?= $content ?>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
