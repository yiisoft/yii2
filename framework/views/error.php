<?php
/**
 * @var \Exception $exception
 * @var \yii\base\ErrorHandler $context
 */
$context = $this->context;
$title = $context->htmlEncode($exception instanceof \yii\base\Exception ? $exception->getName() : get_class($exception));
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo $title?></title>

	<style>
	body {
		font: normal 9pt "Verdana";
		color: #000;
		background: #fff;
	}

	h1 {
		font: normal 18pt "Verdana";
		color: #f00;
		margin-bottom: .5em;
	}

	h2 {
		font: normal 14pt "Verdana";
		color: #800000;
		margin-bottom: .5em;
	}

	h3 {
		font: bold 11pt "Verdana";
	}

	p {
		font: normal 9pt "Verdana";
		color: #000;
	}

	.version {
		color: gray;
		font-size: 8pt;
		border-top: 1px solid #aaa;
		padding-top: 1em;
		margin-bottom: 1em;
	}
	</style>
</head>

<body>
	<h1><?php echo $title?></h1>
	<h2><?php echo nl2br($context->htmlEncode($exception->getMessage()))?></h2>
	<p>
		The above error occurred while the Web server was processing your request.
	</p>
	<p>
		Please contact us if you think this is a server error. Thank you.
	</p>
	<div class="version">
		<?php echo date('Y-m-d H:i:s', time())?>
		<?php echo YII_DEBUG ? $context->versionInfo : ''?>
	</div>
</body>
</html>
