<?php
use yii\bootstrap\NavBar;
use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var $this \yii\base\View
 * @var $content string
 */
$asset = yii\gii\GiiAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title><?php echo Html::encode($this->title); ?></title>
	<?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
<?php
NavBar::begin(array(
	'brandLabel' => Html::img($asset->baseUrl . '/logo.png'),
	'brandUrl' => array('default/index'),
	'options' => array(
		'class' => 'navbar-inverse navbar-fixed-top',
	),
));
echo Nav::widget(array(
	'options' => array('class' => 'nav navbar-nav pull-right'),
	'items' => array(
		array('label' => 'Home', 'url' => array('default/index')),
		array('label' => 'Help', 'url' => 'http://www.yiiframework.com/doc/guide/topics.gii'),
		array('label' => 'Application', 'url' => Yii::$app->homeUrl),
	),
));
NavBar::end();
?>

<div class="container">
	<?php echo $content; ?>
</div>

<footer class="footer">
	<div class="container">
		<p class="pull-left">A Product of <a href="http://www.yiisoft.com/">Yii Software LLC</a></p>
		<p class="pull-right"><?php echo Yii::powered(); ?></p>
	</div>
</footer>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
