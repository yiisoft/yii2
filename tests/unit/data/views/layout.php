<?php
/**
 * @var $this \yii\base\View
 * @var $content string
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

<?php echo $content; ?>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>