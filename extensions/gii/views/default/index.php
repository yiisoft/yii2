<?php
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \yii\gii\Generator[] $generators
 * @var string $content
 */
$generators = Yii::$app->controller->module->generators;
$this->title = 'Welcome to Gii';
?>
<div class="default-index">
	<div class="page-header">
		<h1>Welcome to Gii <small>a magic tool that can write code for you</small></h1>
	</div>

	<p class="lead">Start the fun with the following code generators:</p>

	<div class="row">
		<?php foreach ($generators as $id => $generator): ?>
		<div class="generator col-lg-4">
			<h3><?= Html::encode($generator->getName()) ?></h3>
			<p><?= $generator->getDescription() ?></p>
			<p><?= Html::a('Start »', ['default/view', 'id' => $id], ['class' => 'btn btn-default']) ?></p>
		</div>
		<?php endforeach; ?>
	</div>

	<p><a class="btn btn-success" href="http://www.yiiframework.com/extensions/?tag=gii">Get More Generators</a></p>

</div>
