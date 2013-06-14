<?php
/**
 * @var \yii\base\Exception $exception
 * @var \yii\base\ErrorHandler $this
 */
?>
<div class="previous">
	<span class="arrow">&crarr;</span>
	<h2>
		<span>Caused by:</span>
		<?php if ($exception instanceof \yii\base\Exception): ?>
			<span><?php echo $this->htmlEncode($exception->getName()); ?></span> &ndash;
			<?php echo $this->addTypeLinks(get_class($exception)); ?>
		<?php else: ?>
			<span><?php echo $this->htmlEncode(get_class($exception)); ?></span>
		<?php endif; ?>
	</h2>
	<h3><?php echo $this->htmlEncode($exception->getMessage()); ?></h3>
	<p>in <span class="file"><?php echo $exception->getFile(); ?></span> at line <span class="line"><?php echo $exception->getLine(); ?></span></p>
	<?php echo $this->renderPreviousExceptions($exception); ?>
</div>
