<?php
use yii\helpers\Html;

/**
 * @var $this \yii\base\View
 * @var $content string
 * @var yii\gii\Generator[] $generators
 * @var yii\gii\Generator $activeGenerator
 */
$generators = Yii::$app->controller->module->generators;
$activeGenerator = Yii::$app->controller->generator;
$this->title = 'Welcome to Gii';
?>
<div class="default-index">
	<div class="page-header">
		<h1>Welcome to Gii <small>a magic tool that can build up an application for you</small></h1>
	</div>

	<p class="lead">Start the fun with the following code generators:</p>

	<div class="row">
		<?php foreach (array_values($generators) as $i => $generator): ?>
		<div class="generator col-lg-4">
			<h3><?php echo Html::encode($generator->getName()); ?></h3>
			<p><?php echo $generator->getDescription(); ?></p>
			<p><?php echo Html::a('Start »', $generator->getUrl(), array('class' => 'btn btn-default')); ?></p>
		</div>
		<?php endforeach; ?>
	</div>


	<p><a class="btn btn-success" href="http://www.yiiframework.com/extensions/?tag=gii">Get More Generators</a></p>

</div>
