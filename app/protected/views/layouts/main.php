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
					<ul class="nav">
						<li><?php echo Html::a('Home', Yii::$app->homeUrl); ?></li>
						<li><?php echo Html::a('About', array('/site/about')); ?></li>
						<li><?php echo Html::a('Contact', array('/site/contact')); ?></li>
						<?php if (Yii::$app->user->isGuest): ?>
						<li><?php echo Html::a('Login', array('/site/login')); ?></li>
						<?php else: ?>
						<li><?php echo Html::a('Logout (' . Html::encode(Yii::$app->user->identity->username) . ')', array('/site/logout')); ?></li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
		<!-- /.navbar -->
	</div>

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