<?php
/* @var $this yii\web\View */
/* @var $diff mixed */
?>
<div class="default-diff">
    <?php if ($diff === false): ?>
        <div class="alert alert-danger">Diff is not supported for this file type.</div>
    <?php elseif (empty($diff)): ?>
        <div class="alert alert-success">Identical.</div>
    <?php else: ?>
        <div class="content"><?= $diff ?></div>
    <?php endif; ?>
</div>
