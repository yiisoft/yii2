<?php
/**
 * @var yii\base\View $this
 * @var yii\gii\generators\module\Generator $generator
 */
?>
<div class="<?php echo $generator->moduleID . '-default-index'; ?>">
	<h1><?php echo "<?php"; ?> echo $this->context->action->uniqueId; ?></h1>
	<p>
		This is the view content for action "<?php echo "<?php"; ?> echo $this->context->action->id; ?>".
		The action belongs to the controller "<?php echo "<?php"; ?> echo get_class($this->context); ?>"
		in the "<?php echo "<?php"; ?> echo $this->context->module->id; ?>" module.
	</p>
	<p>
		You may customize this page by editing the following file:<br>
		<code><?php echo "<?php"; ?> echo __FILE__; ?></code>
	</p>
</div>
