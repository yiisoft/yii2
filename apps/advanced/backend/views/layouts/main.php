<?php
use backend\config\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;

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
		$menuItems = [
			['label' => 'Home', 'url' => ['/site/index']],
		];
		if (Yii::$app->user->isGuest) {
			$menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
		} else {
			$menuItems[] = ['label' => 'Logout (' . Yii::$app->user->identity->username .')' , 'url' => ['/site/logout']];
		}
		echo Nav::widget([
			'options' => ['class' => 'navbar-nav pull-right'],
			'items' => $menuItems,
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
