<?php
/**
 * @var $this \yii\base\View
 * @var $content string
 */

use \yii\helpers\Html as h;
?>
<?php $this->beginPage(); ?>
<!doctype html>
<html lang="en">

<head>
	<meta charset="utf-8"/>
	<title><?php echo h::encode($this->title); ?></title>
	<?php $this->head(); ?>
</head>

<body>
	<?php $this->beginBody(); ?>
	<?php echo $content; ?>
	<?php $this->endBody(); ?>
</body>

</html>
<?php $this->endPage(); ?>
