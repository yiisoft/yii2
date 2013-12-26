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
			<span><?= $handler->htmlEncode($exception->getName()) ?></span> &ndash;
			<?= $handler->addTypeLinks(get_class($exception)) ?>
		<?php else: ?>
			<span><?= $handler->htmlEncode(get_class($exception)) ?></span>
		<?php endif; ?>
	</h2>
	<h3><?= nl2br($handler->htmlEncode($exception->getMessage())) ?></h3>
	<p>in <span class="file"><?= $exception->getFile() ?></span> at line <span class="line"><?= $exception->getLine() ?></span></p>
	<?= $handler->renderPreviousExceptions($exception) ?>
</div>
