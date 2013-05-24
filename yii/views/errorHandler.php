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

	<title></title>

	<link rel="stylesheet" href="/tmp/main.css"/>

	<script type="text/javascript" src="/tmp/highlight.js"></script>
	<script type="text/javascript" src="/tmp/sizzle.min.js"></script>
	<script type="text/javascript" src="/tmp/main.js"></script>
</head>

<?php ob_start(); ob_implicit_flush(false); ?>
	<div class="code-wrap">
		<div class="error-line" style="top: <?php echo 18 * (1 + 1); ?>px;"></div>
		<div class="hover-line"></div>
		<div class="code">
			<span class="lines">
				10<br/>11<br/>12<br/>13<br/>14<br/>15<br/>16<br/>17<br/>18<br/>19<br/>20<br/>21<br/>22<br/>
				23<br/>24<br/>25<br/>26<br/>27<br/>28<br/>29<br/>30<br/>31<br/>32<br/>33<br/>34
			</span>
			<pre>	{
		return array(
			'captcha' => array(
				'class' => 'yii\web\CaptchaAction',
			),
		);
	}

	public function actionIndex()
	{
//		throw new \yii\base\HttpException(500);
		$x = null;
		$x->y = 1;

		echo $this->render('index');
	}

	public function actionLogin()
	{
		$model = new LoginForm();
		if ($this->populate($_POST, $model) && $model->login()) {
			Yii::$app->response->redirect(array('site/index'));
		} else {
			echo $this->render('login', array(
				'model' => $model,</pre>
		</div>
	</div>
<?php $codeBlock = ob_get_clean(); ?>

<body>
	<div class="header">
		<img src="/tmp/attention.png" alt="Attention"/>
		<h1><span>Exception</span> &ndash; <a href="#">yii</a>\<a href="#">base</a>\<a href="#">HttpException</a> &ndash; 404</h1>
		<h2>Requested user cannot be found!</h2>
	</div>

	<div class="traceback">
		<ul>
			<li class="application trace-back-item">
				<div class="li-wrap">
					<div class="li">
						<span class="number">1.</span>
						<span class="text">in C:\_work\jetbrains\yii2\apps\bootstrap\protected\controllers\SiteController.php</span>
						<span class="at">at line</span>
						<span class="line">22</span>
					</div>
				</div>
				<?php echo $codeBlock; ?>
			</li>
			<li class="trace-back-item">
				<div class="li-wrap">
					<div class="li">
						<span class="number">2.</span>
						<span class="text">at C:\_work\jetbrains\yii2\yii\base\InlineAction.php &ndash;</span>
						<span class="call"><a href="#">call_user_func_array</a>()</span>
						<span class="at">at line</span>
						<span class="line">47</span>
					</div>
				</div>
				<?php echo $codeBlock; ?>
			</li>
			<li class="trace-back-item">
				<div class="li-wrap">
					<div class="li">
						<span class="number">3.</span>
						<span class="text">at C:\_work\jetbrains\yii2\yii\base\Controller.php &ndash;</span>
						<span class="call"><a href="#">yii</a>\<a href="#">base</a>\<a href="#">InlineAction</a>→<a href="#">runWithParams</a>()</span>
						<span class="at">at line</span>
						<span class="line">117</span>
					</div>
				</div>
				<?php echo $codeBlock; ?>
			</li>
			<li class="trace-back-item">
				<div class="li-wrap">
					<div class="li">
						<span class="number">4.</span>
						<span class="text">at C:\_work\jetbrains\yii2\yii\web\Application.php &ndash;</span>
						<span class="call"><a href="#">yii</a>\<a href="#">base</a>\<a href="#">Module</a>→<a href="#">runAction</a>()</span>
						<span class="at">at line</span>
						<span class="line">35</span>
					</div>
				</div>
				<?php echo $codeBlock; ?>
			</li>
			<li class="trace-back-item">
				<div class="li-wrap">
					<div class="li">
						<span class="number">5.</span>
						<span class="text">at C:\_work\jetbrains\yii2\yii\web\Application.php &ndash;</span>
						<span class="call"><a href="#">yii</a>\<a href="#">base</a>\<a href="#">Module</a>→<a href="#">runAction</a>()</span>
						<span class="at">at line</span>
						<span class="line">35</span>
					</div>
				</div>
				<?php echo $codeBlock; ?>
			</li>
			<li class="trace-back-item">
				<div class="li-wrap">
					<div class="li">
						<span class="number">6.</span>
						<span class="text">at C:\_work\jetbrains\yii2\yii\base\Application.php &ndash;</span>
						<span class="call"><a href="#">yii</a>\<a href="#">web</a>\<a href="#">Application</a>→<a href="#">processRequest</a>()</span>
						<span class="at">at line</span>
						<span class="line">146</span>
					</div>
				</div>
				<?php echo $codeBlock; ?>
			</li>
			<li class="application trace-back-item">
				<div class="li-wrap">
					<div class="li">
						<span class="number">7.</span>
						<span class="text">at C:\_work\jetbrains\yii2\apps\bootstrap\index.php &ndash; </span>
						<span class="call"><a href="#">yii</a>\<a href="#">base</a>\<a href="#">Application</a>→<a href="#">run</a>()</span>
						<span class="at">at line</span>
						<span class="line">14</span>
					</div>
				</div>
				<?php echo $codeBlock; ?>
			</li>
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
		<p class="timestamp">2013-05-16, 19:14:12</p>
		<p><a href="http://php.net/manual/en/features.commandline.webserver.php">PHP 5.4.3 Development Server</a></p>
		<p><a href="http://yiiframework.com/">Yii Framework</a>/<a href="#">2.0-dev</a></p>
	</div>
</body>

</html>
