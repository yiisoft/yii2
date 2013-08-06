<?php
/**
 * @var \yii\base\Exception $exception
 * @var \yii\base\ErrorHandler $handler
 */
?>
<div class="previous">
	<span class="arrow">&crarr;</span>
	<h2>
		<span>Caused by:</span>
		<?php if ($exception instanceof \yii\base\Exception): ?>
			<span><?php echo $handler->htmlEncode($exception->getName()); ?></span> &ndash;
			<?php echo $handler->addTypeLinks(get_class($exception)); ?>
		<?php else: ?>
			<span><?php echo $handler->htmlEncode(get_class($exception)); ?></span>
		<?php endif; ?>
	</h2>
	<h3><?php echo $handler->htmlEncode($exception->getMessage()); ?></h3>
	<p>in <span class="file"><?php echo $exception->getFile(); ?></span> at line <span class="line"><?php echo $exception->getLine(); ?></span></p>
	<?php echo $handler->renderPreviousExceptions($exception); ?>
</div>
