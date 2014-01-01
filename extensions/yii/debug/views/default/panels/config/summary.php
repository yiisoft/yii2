<div class="yii-debug-toolbar-block">
	<a href="<?php echo $panel->getUrl(); ?>">
		<img width="29" height="30" alt="" src="<?php echo $panel->getYiiLogo();?>">
		<span><?php echo $data['application']['yii'];?></span>
	</a>
</div>
<div class="yii-debug-toolbar-block">
	<a href="<?php echo $this->context->createUrl('phpinfo');?>" title="Show phpinfo()">PHP <?php echo $data['php']['version'];?></a>
</div>