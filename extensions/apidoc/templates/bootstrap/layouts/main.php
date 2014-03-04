<?php
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 */

\yii\apidoc\templates\bootstrap\assets\AssetBundle::register($this);

// Navbar hides initial content when jumping to in-page anchor
// https://github.com/twbs/bootstrap/issues/1768
$this->registerJs(<<<JS
	var shiftWindow = function() { scrollBy(0, -50) };
	if (location.hash) shiftWindow();
	window.addEventListener("hashchange", shiftWindow);
JS
,
	\yii\web\View::POS_HEAD
);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="language" content="en" />
	<?php $this->head() ?>
	<title><?= Html::encode($this->context->pageTitle) ?></title>
</head>
<body>

<?php $this->beginBody() ?>
<div class="wrap">
	<?php
	NavBar::begin([
		'brandLabel' => $this->context->pageTitle,
		'brandUrl' => './index.html',
		'options' => [
			'class' => 'navbar-inverse navbar-fixed-top',
		],
		'renderInnerContainer' => false,
		'view' => $this,
	]);
	$extItems = [];
	foreach ($this->context->extensions as $ext) {
		$extItems[] = [
			'label' => $ext,
			'url' => "./ext_{$ext}_index.html",
		];
	}
	$nav = [
		['label' => 'Class reference', 'url' => './index.html'],
//		['label' => 'Application API', 'url' => '/site/about'],
		['label' => 'Extensions', 'items' => $extItems],
	];
	if ($this->context->guideUrl !== null) {
		$nav[] = ['label' => 'Guide', 'url' => $this->context->guideUrl . 'guide_index.html'];
	}

	echo Nav::widget([
		'options' => ['class' => 'navbar-nav'],
		'items' => $nav,
		'view' => $this,
		'params' => [],
	]);
	NavBar::end();
	?>

	<?= $content ?>

</div>

<footer class="footer">
	<?php /* <p class="pull-left">&copy; My Company <?= date('Y') ?></p> */ ?>
	<p class="pull-right"><small>Page generated on <?= date('r') ?></small></p>
	<?= Yii::powered() ?>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>