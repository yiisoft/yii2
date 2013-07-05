<?php
/**
 * @var \yii\base\View $this
 * @var string $content
 */
use yii\helpers\Html;

Yii::$app->getView()->registerAssetBundle('yii/bootstrap/responsive');
?>
<!DOCTYPE html>
<html>
<?php $this->beginPage(); ?>
<head>
	<title><?php echo Html::encode($this->title); ?></title>
	<?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>
<div class="container-fluid">
	<div class="row-fluid">
		<?php echo $content; ?>
	</div>
</div>
<?php $this->endBody(); ?>
</body>
<?php $this->endPage(); ?>
</html>
