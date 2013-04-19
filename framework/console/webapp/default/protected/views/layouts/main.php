<?php use yii\helpers\Html as Html; ?>
<!doctype html>
<html lang="<?php \Yii::$app->language?>">
	<head>
		<meta charset="utf-8" />
		<title><?php echo Html::encode($this->title)?></title>
	</head>
	<body>
		<h1><?php echo Html::encode($this->title)?></h1>
		<div class="content">
			<?php echo $content?>
		</div>
		<div class="footer">
			<?php echo \Yii::powered()?>
		</div>
	</body>
</html>