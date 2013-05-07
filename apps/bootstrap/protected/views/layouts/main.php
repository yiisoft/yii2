<?php
/**
 * @var $this \yii\base\View
 * @var $content string
 */
use yii\helpers\Html;
$this->registerAssetBundle('app');
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

		<div class="navbar">
			<div class="navbar-inner">
				<div class="container">
					<?php $this->widget('yii\widgets\Menu', array(
						'options' => array('class' => 'nav'),
						'items' => array(
							array('label' => 'Home', 'url' => array('/site/index')),
							array('label' => 'About', 'url' => array('/site/about')),
							array('label' => 'Contact', 'url' => array('/site/contact')),
							Yii::$app->user->isGuest ?
								array('label' => 'Login', 'url' => array('/site/login')) :
								array('label' => 'Logout (' . Yii::$app->user->identity->username .')' , 'url' => array('/site/logout')),
						),
					)); ?>
				</div>
			</div>
		</div>
		<!-- /.navbar -->
	</div>

	<?php $this->widget('yii\widgets\Breadcrumbs', array(
		'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : array(),
	)); ?>
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
