<?php
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var string $content
 */
$asset = yii\gii\GiiAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
<?php
NavBar::begin([
	'brandLabel' => Html::img($asset->baseUrl . '/logo.png'),
	'brandUrl' => ['default/index'],
	'options' => ['class' => 'navbar-inverse navbar-fixed-top'],
]);
echo Nav::widget([
	'options' => ['class' => 'nav navbar-nav pull-right'],
	'items' => [
		['label' => 'Home', 'url' => ['default/index']],
		['label' => 'Help', 'url' => 'http://www.yiiframework.com/doc/guide/topics.gii'],
		['label' => 'Application', 'url' => Yii::$app->homeUrl],
	],
]);
NavBar::end();
?>

<div class="container">
	<?= $content ?>
</div>

<footer class="footer">
	<div class="container">
		<p class="pull-left">A Product of <a href="http://www.yiisoft.com/">Yii Software LLC</a></p>
		<p class="pull-right"><?= Yii::powered() ?></p>
	</div>
</footer>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
