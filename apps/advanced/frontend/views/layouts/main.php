<?php
use frontend\config\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\widgets\Alert;

/**
 * @var $this \yii\base\View
 * @var $content string
 */
AppAsset::register($this);
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
			'brandLabel' => 'My Company',
			'brandUrl' => Yii::$app->homeUrl,
			'options' => array(
				'class' => 'navbar-inverse navbar-fixed-top',
			),
		));
		$menuItems = array(
			array('label' => 'Home', 'url' => array('/site/index')),
			array('label' => 'About', 'url' => array('/site/about')),
			array('label' => 'Contact', 'url' => array('/site/contact')),
		);
		if (Yii::$app->user->isGuest) {
			$menuItems[] = array('label' => 'Signup', 'url' => array('/site/signup'));
			$menuItems[] = array('label' => 'Login', 'url' => array('/site/login'));
		} else {
			$menuItems[] = array('label' => 'Logout (' . Yii::$app->user->identity->username .')' , 'url' => array('/site/logout'));
		}
		echo Nav::widget(array(
			'options' => array('class' => 'navbar-nav pull-right'),
			'items' => $menuItems,
		));
		NavBar::end();
	?>

	<div class="container">
	<?php echo Breadcrumbs::widget(array(
		'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : array(),
	)); ?>
	<?php echo Alert::widget()?>
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
