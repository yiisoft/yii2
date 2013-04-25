<?php
/**
 * @var $this \yii\base\View
 * @var $content string
 */
use yii\helpers\Html;
?>
<!DOCTYPE html>
<html>
<?php $this->beginPage(); ?>
<head>
	<title><?php echo Html::encode($this->title); ?></title>
	<?php $this->head(); ?>
</head>
<body>
<h1>Welcome</h1>
<?php $this->beginBody(); ?>
<?php echo $content; ?>
<?php $this->endBody(); ?>
</body>
<?php $this->endPage(); ?>
</html>
