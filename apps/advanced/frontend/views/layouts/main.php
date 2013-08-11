<?php
use frontend\config\AppAsset;
use yii\helpers\Html;
use yii\widgets\Menu;
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
<div class="container">
	<?php $this->beginBody(); ?>
	<div class="masthead">
		<h3 class="muted">My Company</h3>

		<div class="navbar fullwidth">
			<div class="navbar-inner">
				<div class="container">
					<?php
						$menuItems = array(
							array('label' => 'Home', 'url' => array('/site/index')),
							array('label' => 'About', 'url' => array('/site/about')),
							array('label' => 'Contact', 'url' => array('/site/contact')),
						);
						if (Yii::$app->user->isGuest) {
							$menuItems[] = array('label' => 'Signup', 'url' => array('/site/signup'));
							$menuItems[] = array('label' => 'Login', 'url' => array('/site/login'));
						}
						else {
							$menuItems[] = array('label' => 'Logout (' . Yii::$app->user->identity->username .')' , 'url' => array('/site/logout'));
						}
						echo Menu::widget(array(
							'options' => array('class' => 'nav'),
							'items' => $menuItems,
						));
					?>
				</div>
			</div>
		</div>
		<!-- /.navbar -->
	</div>

	<?php echo Breadcrumbs::widget(array(
		'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : array(),
	)); ?>

	<?php echo Alert::widget()?>

	<?php echo $content; ?>

	<hr>

	<div class="footer">
		<p>&copy; My Company <?php echo date('Y'); ?></p>
		<p>
			<?php echo Yii::powered(); ?>
			Template by <a href="http://twitter.github.io/bootstrap/">Twitter Bootstrap</a>
		</p>
	</div>
	<?php $this->endBody(); ?>
</div>
</body>
</html>
<?php $this->endPage(); ?>
