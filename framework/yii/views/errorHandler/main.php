<?php
/**
 * @var \yii\base\View $this
 * @var \Exception $e
 * @var string $request
 * @var integer $requestLinesCount
 * @var \yii\base\ErrorHandler $c
 */
$c = $this->context;
?>
<!doctype html>
<html lang="en-us">

<head>
	<meta charset="utf-8"/>

	<?php if ($e instanceof \yii\base\Exception): ?>
		<title><?php echo $c->htmlEncode($e->getName() . ' – ' . get_class($e)); ?></title>
	<?php else: ?>
		<title><?php echo $c->htmlEncode(get_class($e)); ?></title>
	<?php endif; ?>

	<link rel="stylesheet" href="/tmp/main.css"/>

	<script type="text/javascript" src="/tmp/highlight.js"></script>
	<script type="text/javascript" src="/tmp/sizzle.min.js"></script>
	<script type="text/javascript" src="/tmp/main.js"></script>
</head>

<body>
	<div class="header">
		<?php if ($e instanceof \yii\base\ErrorException): ?>
			<img src="/tmp/gears.png" alt="Gears"/>
			<h1>
				<span><?php echo $c->htmlEncode($e->getName()); ?></span>
				&ndash; <?php echo $c->addTypeLinks(get_class($e)); ?>
			</h1>
			<h2><?php echo $c->htmlEncode($e->getMessage()); ?></h2>
		<?php else: ?>
			<img src="/tmp/attention.png" alt="Attention"/>
			<h1>
				<span>Exception</span> &ndash; <?php echo $c->addTypeLinks(get_class($e)); ?>
				<?php if ($e instanceof \yii\base\HttpException): ?>
					&ndash; <?php echo $c->createHttpStatusLink($e->statusCode); ?>
				<?php endif; ?>
			</h1>
			<h2><?php echo $c->htmlEncode($e->getName()); ?></h2>
		<?php endif; ?>
	</div>

	<div class="call-stack">
		<ul>
			<?php echo $c->renderCallStackItem($e->getFile(), $e->getLine(), 1); ?>
			<?php for ($i = 1, $trace = $e->getTrace(), $length = count($trace); $i < $length; ++$i): ?>
				<?php echo $c->renderCallStackItem($trace[$i]['file'], $trace[$i]['line'], $i + 1); ?>
			<?php endfor; ?>
		</ul>
	</div>

	<div class="request">
		<div class="code">
			<pre><?php echo $c->htmlEncode($request); ?></pre>
		</div>
	</div>

	<div class="footer">
		<img src="/tmp/logo.png" alt="Yii Framework"/>
		<p class="timestamp"><?php echo date('Y-m-d, H:i:s'); ?></p>
		<p><?php echo $c->createServerInformationLink(); ?></p>
		<p><a href="http://yiiframework.com/">Yii Framework</a>/<?php echo $c->createFrameworkVersionLink(); ?></p>
	</div>
</body>

</html>
