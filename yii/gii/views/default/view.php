<?php

use yii\helpers\Html;

/**
 * @var $this \yii\base\View
 * @var $generator \yii\gii\Generator
 */

$this->title = $generator->getName();
?>
<div class="default-view">
	<h1><?php echo Html::encode($generator->getName()); ?></h1>
	<p><?php echo $generator->getDescription(); ?></p>

	<?php echo $generator->renderForm(); ?>

	<?php echo $generator->renderFileList(); ?>
</div>
