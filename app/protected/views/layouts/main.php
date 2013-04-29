<?php
/**
 * @var $this \yii\base\View
 * @var $content string
 */
use yii\helpers\Html;
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo Html::encode($this->title); ?></title>
	<?php $this->head(); ?>
</head>
<body>
<h1>Welcome</h1>
<?php $this->beginBody(); ?>
<?php echo $content; ?>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>