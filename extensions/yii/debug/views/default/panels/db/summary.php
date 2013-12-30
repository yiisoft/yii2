<?php if ($queryCount): ?>
<div class="yii-debug-toolbar-block">
	<a href="$url" title="Executed <?php echo $queryCount; ?> database queries which took <?php echo $queryTime; ?>.">
		DB <span class="label"><?php echo $queryCount; ?></span> <span class="label"><?php echo $queryTime; ?></span>
	</a>
</div>
<?php endif; ?>