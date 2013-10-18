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
		NavBar::begin([
			'brandLabel' => 'My Company',
			'brandUrl' => Yii::$app->homeUrl,
			'options' => [
				'class' => 'navbar-inverse navbar-fixed-top',
			],
		]);
		echo Nav::widget([
			'options' => ['class' => 'navbar-nav pull-right'],
			'items' => [
				['label' => 'Home', 'url' => ['/site/index']],
				['label' => 'About', 'url' => ['/site/about']],
				['label' => 'Contact', 'url' => ['/site/contact']],
				Yii::$app->user->isGuest ?
					['label' => 'Login', 'url' => ['/site/login']] :
					['label' => 'Logout (' . Yii::$app->user->identity->username .')' ,
						'url' => ['/site/logout'],
						'linkOptions' => ['data-method' => 'post']],
			],
		]);
		NavBar::end();
	?>

	<div class="container">
		<?php echo Breadcrumbs::widget([
			'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
		]); ?>
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
