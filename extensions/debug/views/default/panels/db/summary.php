<?php if ($queryCount): ?>
<div class="yii-debug-toolbar-block">
	<a href="<?= $panel->getUrl() ?>" title="Executed <?php echo $queryCount; ?> database queries which took <?= $queryTime ?>.">
		DB <span class="label"><?= $queryCount ?></span> <span class="label"><?= $queryTime ?></span>
	</a>
</div>
<?php endif; ?>
