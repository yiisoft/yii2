<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

/**
 * @var $this \yii\base\View
 * @var $content string
 */
app\config\AppAsset::register($this);
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="<?php echo Yii::$app->charset; ?>"/>
	<title><?php echo Html::encode($this->title); ?></title>
	<?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
	<?php
		NavBar::begin(array(
			'brandLabel' => 'My Company',
			'brandUrl' => Yii::$app->homeUrl,
			'options' => array(
				'class' => 'navbar-inverse navbar-fixed-top',
			),
		));
		echo Nav::widget(array(
			'options' => array('class' => 'navbar-nav pull-right'),
			'items' => array(
				array('label' => 'Home', 'url' => array('/site/index')),
				array('label' => 'About', 'url' => array('/site/about')),
				array('label' => 'Contact', 'url' => array('/site/contact')),
				Yii::$app->user->isGuest ?
					array('label' => 'Login', 'url' => array('/site/login')) :
					array('label' => 'Logout (' . Yii::$app->user->identity->username .')' , 'url' => array('/site/logout')),
			),
		));
		NavBar::end();
	?>

	<div class="container">
		<?php echo Breadcrumbs::widget(array(
			'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : array(),
		)); ?>
		<?php echo $content; ?>
	</div>

	<footer class="footer">
		<div class="container">
			<p class="pull-left">&copy; My Company <?php echo date('Y'); ?></p>
			<p class="pull-right"><?php echo Yii::powered(); ?></p>
		</div>
	</footer>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
