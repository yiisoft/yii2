<div class="previous">
	<h1><span class="arrow">&crarr;</span><span>Caused by: </span><?php
		/**
		 * @var \yii\base\View $this
		 * @var \yii\base\Exception $exception
		 * @var string $previousHtml
		 * @var \yii\base\ErrorHandler $context
		 */
		$context = $this->context;
		if ($exception instanceof \yii\base\Exception) {
			echo '<span>' . $context->htmlEncode($exception->getName()) . '</span>';
			echo ' &ndash; ' . $context->addTypeLinks(get_class($exception));
		} else {
			echo '<span>' . $context->htmlEncode(get_class($exception)) . '</span>';
		}
	?></h1>
	<h2><?php echo $context->htmlEncode($exception->getMessage()); ?></h2>
	<p>In <span class="file"><?php echo $exception->getFile(); ?></span> at line <span class="line"><?php echo $exception->getLine(); ?></span></p>
	<?php echo $previousHtml; ?>
</div>