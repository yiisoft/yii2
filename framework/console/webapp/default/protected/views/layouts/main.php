<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title><?php echo $this->context->pageTitle?></title>
	</head>
	<body>
		<h1><?php echo $this->context->pageTitle?></h1>
		<div class="content">
			<?php echo $content?>
		</div>
		<div class="footer">
			<?php echo \Yii::powered()?>
		</div>
	</body>
</html>