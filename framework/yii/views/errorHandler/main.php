<?php
/**
 * @var \yii\base\View $this
 * @var \Exception $e
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
		<img src="/tmp/attention.png" alt="Attention"/>
		<h1>
			<span>Exception</span> &ndash; <?php echo $c->addTypeLinks(get_class($e)); ?>
			<?php if ($e instanceof \yii\base\HttpException): ?>
				&ndash; <?php echo $c->createHttpStatusLink($e->statusCode); ?>
			<?php endif; ?>
		</h1>
		<h2><?php echo $c->htmlEncode($e->getName()); ?></h2>
	</div>

	<div class="call-stack">
		<ul>
			<?php echo $c->renderCallStackItem($e->getFile(), $e->getLine(), 1); ?>
			<?php for ($i = 1, $trace = $e->getTrace(), $length = count($trace); $i < $length; ++$i): ?>
				<?php echo $c->renderCallStackItem($trace[$i]['file'], $trace[$i]['line'], $i + 1); ?>
			<?php endfor; ?>
		</ul>
	</div>

	<?php /*
	<div class="request">
		<div id="code-wrap"></div>
			<div id="code-highlighter"></div>
			<div id="code-inner-wrap">
			<pre id="code">$_GET = [
	'show-post' => 100,
	'refresh-page' => 'yes',
	'ascending-sort' => 1,
];

$_POST = [
	'blog-post-form' => [
		'title' => 'hello',
		'author_id' => '12',
	],
];

$_SERVER = [
	'DOCUMENT_ROOT' => '/home/resurtm/work/data',
	'REMOTE_ADDR' => '::1',
	'REMOTE_PORT' => '52694',
	'SERVER_SOFTWARE' => 'PHP 5.4.3 Development Server',
	'SERVER_PROTOCOL' => 'HTTP/1.1',
	'SERVER_NAME' => 'localhost',
	'SERVER_PORT' => '8000',
	'REQUEST_URI' => '/index.php?post-form[title]=hello&post-form[author_id]=12',
	'REQUEST_METHOD' => 'GET',
	'SCRIPT_NAME' => '/index.php',
	'SCRIPT_FILENAME' => '/home/resurtm/work/data/index.php',
	'PHP_SELF' => '/index.php',
	'QUERY_STRING' => 'post-form[title]=hello&post-form[author_id]=12',
	'HTTP_HOST' => 'localhost:8000',
	'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0',
	'HTTP_ACCEPT_LANGUAGE' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
	'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
	'HTTP_CONNECTION' => 'keep-alive',
	'REQUEST_TIME_FLOAT' => 1369146454.0856,
	'REQUEST_TIME' => 1369146454,
];</pre>
		</div>
		</div>
	</div>*/ ?>

	<div class="footer">
		<img src="/tmp/logo.png" alt="Yii Framework"/>
		<p class="timestamp"><?php echo date('Y-m-d, H:i:s'); ?></p>
		<p><?php echo $c->createServerInformationLink(); ?></p>
		<p><a href="http://yiiframework.com/">Yii Framework</a>/<?php echo $c->createFrameworkVersionLink(); ?></p>
	</div>
</body>

</html>
