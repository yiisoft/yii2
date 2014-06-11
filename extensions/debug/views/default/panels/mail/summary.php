<?php
/**
 * @var yii\debug\panels\MailPanel $panel
 * @var integer $mailCount
 */
if ($mailCount): ?>
<div class="yii-debug-toolbar-block">
    <a href="<?= $panel->getUrl() ?>">Mail <span class="label"><?= $mailCount ?></span></a>
</div>
<?php endif ?>
