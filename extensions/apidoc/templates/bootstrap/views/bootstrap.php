<?php
use yii\apidoc\templates\bootstrap\SideNavWidget;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\StringHelper;
use yii\widgets\Menu;

/**
 * @var yii\web\View $this
 */

\yii\apidoc\templates\bootstrap\assets\AssetBundle::register($this);

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
		'padded' => false,
		'view' => $this,
	]);
	echo Nav::widget([
		'options' => ['class' => 'navbar-nav'],
		'items' => [
			['label' => 'Class reference', 'url' => './index.html'],
//			['label' => 'Application API', 'url' => '/site/about'],
//			['label' => 'Guide', 'url' => './guide_index.html'],
		],
		'view' => $this,
	]);
	NavBar::end();
	?>
<!--	<div class="container">-->
		<div class="row">
			<div class="col-md-2">
				<?php
				ksort($types);
				$nav = [];
				foreach($types as $i=>$class) {
					$namespace = $class->namespace;
					if (empty($namespace)) {
						$namespace = 'Not namespaced classes';
					}
					if (!isset($nav[$namespace])) {
						$nav[$namespace] = [
							'label' => $namespace,
							'url' => '#',
							'items' => [],
						];
					}
					$nav[$namespace]['items'][] = [
						'label' => StringHelper::basename($class->name),
						'url' => './' . $this->context->generateUrl($class->name),
						'active' => isset($type) && ($class->name == $type->name),
					];
				} ?>
				<?= SideNavWidget::widget([
					'id' => 'navigation',
					'items' => $nav,
		//			'route' => 'wtf',
					'view' => $this,
				])?>
			</div>
			<div class="col-md-9" role="main">
				<?= $content ?>
			</div>
		</div>
<!--	</div>-->

</div>

<footer class="footer">
	<?php /* <p class="pull-left">&copy; My Company <?= date('Y') ?></p> */ ?>
	<p class="pull-right"><?= Yii::powered() ?></p>
</footer>

<script type="text/javascript">
	/*<![CDATA[*/
	$("a.toggle").on('click', function() {
		var $this = $(this);
		if ($this.hasClass('properties-hidden')) {
			$this.text($this.text().replace(/Show/,'Hide'));
			$this.parents(".summary").find(".inherited").show();
			$this.removeClass('properties-hidden');
		} else {
			$this.text($this.text().replace(/Hide/,'Show'));
			$this.parents(".summary").find(".inherited").hide();
			$this.addClass('properties-hidden');
		}
		return false;
	});
	/*
	 $(".sourceCode a.show").toggle(function(){
	 $(this).text($(this).text().replace(/show/,'hide'));
	 $(this).parents(".sourceCode").find("div.code").show();
	 },function(){
	 $(this).text($(this).text().replace(/hide/,'show'));
	 $(this).parents(".sourceCode").find("div.code").hide();
	 });
	 $("a.sourceLink").click(function(){
	 $(this).attr('target','_blank');
	 });
	 */
	/*]]>*/
</script>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>