<?php
/**
 * @var \yii\base\View $this
 * @var \yii\base\Exception $exception
 * @var string $previousHtml
 * @var \yii\base\ErrorHandler $context
 */
$context = $this->context;
?>
<div class="previous">
	<span class="arrow">&crarr;</span>
	<h2>
		<span>Caused by:</span>
		<?php if ($exception instanceof \yii\base\Exception): ?>
			<span><?php echo $context->htmlEncode($exception->getName()); ?></span> &ndash;
			<?php echo $context->addTypeLinks(get_class($exception)); ?>
		<?php else: ?>
			<span><?php echo $context->htmlEncode(get_class($exception)); ?></span>
		<?php endif; ?>
	</h2>
	<h3><?php echo $context->htmlEncode($exception->getMessage()); ?></h3>
	<p>in <span class="file"><?php echo $exception->getFile(); ?></span> at line <span class="line"><?php echo $exception->getLine(); ?></span></p>
	<?php echo $previousHtml; ?>
</div>
